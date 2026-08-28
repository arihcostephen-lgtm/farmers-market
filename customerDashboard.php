<?php
include "inc/header.php";

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = (int) $_SESSION['user_id'];
$customerName = htmlspecialchars($_SESSION['user_name'] ?? 'Customer');
$customerEmail = $_SESSION['user_email'] ?? '';
$customerPhone = $_SESSION['user_phone'] ?? '';

$productsSql = "SELECT p.*, c.cat_name, u.user_name AS farmer_name FROM products p LEFT JOIN category c ON c.cat_id = p.category_id LEFT JOIN users u ON u.user_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci WHERE p.status != 0 ORDER BY p.product_name ASC";
$productsResult = $db->query($productsSql);
$productsCount = $productsResult->num_rows;

$ordersSql = "SELECT * FROM order_list WHERE user_id = '$customerId' ORDER BY or_id DESC";
$ordersResult = $db->query($ordersSql);
$ordersCount = $ordersResult->num_rows;

$commentsCount = 0;
if ($customerEmail) {
    $commentsCount = (int) $db->query("SELECT COUNT(*) AS total FROM comments WHERE user_id = '" . $db->real_escape_string($customerEmail) . "'")->fetch_assoc()['total'];
}

$categories = $db->query("SELECT * FROM category WHERE is_parent = 1 AND status = 1 ORDER BY cat_name ASC");
$message = '';
$inquiryMessage = '';
$inquiryError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $inquiryProductId = (int) ($_POST['inquiry_product_id'] ?? 0);
    $inquirySubjectInput = trim($_POST['inquiry_subject'] ?? '');
    $inquiryTextInput = trim($_POST['inquiry_message'] ?? '');
    $inquiryProductQuery = $db->query("SELECT product_id, product_name, seller_email FROM products WHERE product_id = $inquiryProductId AND status = 1 LIMIT 1");
    $inquiryProduct = $inquiryProductQuery ? $inquiryProductQuery->fetch_assoc() : null;
    if (!$inquiryProduct || $inquirySubjectInput === '' || $inquiryTextInput === '') {
        $inquiryError = 'Select a product and enter both a subject and message.';
    } else {
        $inquirySubject = $db->real_escape_string($inquirySubjectInput);
        $inquiryText = $db->real_escape_string($inquiryTextInput);
        $buyerEmail = $db->real_escape_string($customerEmail);
        $insertInquiry = "INSERT INTO product_inquiries (product_id, buyer_id, buyer_email, subject, message, status, created_at) VALUES ($inquiryProductId, $customerId, '$buyerEmail', '$inquirySubject', '$inquiryText', 0, NOW())";
        if ($db->query($insertInquiry)) {
            farmers_market_send_email($db, $inquiryProduct['seller_email'], 'New inquiry about ' . $inquiryProduct['product_name'], "A customer submitted a new product inquiry.\n\nProduct: " . $inquiryProduct['product_name'] . "\nSubject: " . $inquirySubjectInput . "\n\n" . $inquiryTextInput, $customerEmail);
            $inquiryMessage = 'Your inquiry was sent and is pending a response.';
        } else {
            $inquiryError = 'Unable to save your inquiry: ' . $db->error;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $subject = $db->real_escape_string(trim($_POST['comment_subject']));
    $commentText = $db->real_escape_string(trim($_POST['comment_text']));
    $commentStatus = 0;
    $commentUserId = $db->real_escape_string($customerEmail ?: $customerId);
    $commentNumber = $db->real_escape_string($customerPhone ?: '');
    if ($subject && $commentText) {
        $insertComment = "INSERT INTO comments (user_id, user_number, subject, comments, status, cmt_date) VALUES ('{$commentUserId}','{$commentNumber}','{$subject}','{$commentText}','{$commentStatus}', now())";
        if ($db->query($insertComment)) {
            $message = 'Comment submitted successfully. Thank you!';
            $commentsCount++;
        } else {
            $message = 'Unable to save your comment. Please try again.';
        }
    }
}

$recentComments = $db->query("SELECT * FROM comments WHERE user_id = '" . $db->real_escape_string($customerEmail ?: $customerId) . "' ORDER BY cmt_date DESC LIMIT 5");
$inquiryHistory = $db->query("SELECT i.*, p.product_name FROM product_inquiries i LEFT JOIN products p ON p.product_id = i.product_id WHERE i.buyer_id = '$customerId' ORDER BY i.created_at DESC");
$inquiryLabels = ['Pending', 'Responded', 'Resolved'];
$inquiryClasses = ['warning', 'info', 'success'];
?>

<style>
    body { background: #06130d; color: #e9fff4; font-family: Roboto, sans-serif; }
    .customer-dashboard { min-height: 100vh; padding: 30px 0; }
    .dashboard-shell { background: rgba(6,19,13,0.95); border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.25); }
    .dashboard-sidebar { background: #06130d; border-right: 1px solid rgba(255,255,255,0.06); }
    .dashboard-sidebar h4 { color: #e9fff4; font-weight: 500; letter-spacing: .2px; }
    .dashboard-sidebar .sidebar-caption { color: #9ef7b8; font-size: .78rem; letter-spacing: .08em; text-transform: uppercase; }
    .dashboard-sidebar .nav { gap: 6px; }
    .dashboard-sidebar .nav-link {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 46px;
        padding: 10px 12px;
        color: #cfeee0;
        border: 1px solid transparent;
        border-radius: 6px;
        transition: color .15s ease, background .15s ease, border-color .15s ease;
    }
    .dashboard-sidebar .nav-link .sidebar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        color: #9fe9c1;
        font-size: 1rem;
    }
    .dashboard-sidebar .nav-link .sidebar-label { flex: 1; }
    .dashboard-sidebar .nav-link:hover,
    .dashboard-sidebar .nav-link.active {
        background: linear-gradient(90deg, #0b8a4a 0%, #0b5b33 100%);
        border-color: rgba(158, 247, 184, 0.18);
        color: #ffffff;
        box-shadow: inset 0 2px 0 rgba(0,0,0,0.12);
    }
    .dashboard-sidebar .nav-link:hover .sidebar-icon,
    .dashboard-sidebar .nav-link.active .sidebar-icon { color: #ffffff; }
    .dashboard-card { background: #0d261a; border: 1px solid rgba(16,184,129,0.14); }
    .dashboard-card .card-header { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .dashboard-card .btn-primary { background: #0f8a45; border-color: #0f8a45; }
    .dashboard-card .btn-primary:hover { background: #0b6e37; border-color: #0b6e37; }
    .dashboard-card .form-control, .dashboard-card .form-select { background: rgba(255,255,255,0.06); color: #e9fff4; border: 1px solid rgba(255,255,255,0.1); }
    .dashboard-card .form-control:focus, .dashboard-card .form-select:focus { background: rgba(255,255,255,0.08); color: #ffffff; border-color: #0f8a45; box-shadow: none; }
    .product-card { background: #092214; border: 1px solid rgba(16,184,129,0.14); }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 18px 30px rgba(0,0,0,0.25); }
    .product-card .badge { background: rgba(16,184,129,0.17); color: #b7ffcf; }
    .section-title { color: #d8ffe6; }
    .badge-status { background: #0f8a45; color: #fff; }
    .comment-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }
    .product-tag { color: #9ffdd3; }
    .text-muted { color: rgba(233,255,244,0.72) !important; }
    .customer-dashboard,
    .customer-dashboard .dashboard-shell,
    .customer-dashboard .dashboard-card,
    .customer-dashboard .product-card {
        color: #e9fff4;
    }
    .customer-dashboard h1,
    .customer-dashboard h2,
    .customer-dashboard h3,
    .customer-dashboard h4,
    .customer-dashboard h5,
    .customer-dashboard h6,
    .customer-dashboard label,
    .customer-dashboard .form-label,
    .customer-dashboard .card-header,
    .customer-dashboard .card-body,
    .customer-dashboard .form-check-label {
        color: #e9fff4 !important;
    }
    .customer-dashboard .text-muted,
    .customer-dashboard small.text-muted,
    .customer-dashboard .product-card p.text-muted {
        color: #b8d8c5 !important;
    }
    .customer-dashboard .product-tag,
    .customer-dashboard .section-title {
        color: #9ef7b8 !important;
    }
    .customer-dashboard a:not(.nav-link) {
        color: #9ef7b8;
    }
    .customer-dashboard a:not(.nav-link):hover {
        color: #c9ffd9;
    }
    .customer-dashboard .form-control,
    .customer-dashboard .form-select {
        color: #f2fff7 !important;
    }
    .customer-dashboard .form-control::placeholder {
        color: #a9c7b5 !important;
        opacity: 1;
    }
    .customer-dashboard .form-select option {
        background: #0d261a;
        color: #e9fff4;
    }
    .customer-dashboard .alert-secondary {
        background: rgba(158, 247, 184, 0.12);
        border-color: rgba(158, 247, 184, 0.22);
        color: #dfffe9 !important;
    }
    @media (max-width: 991px) {
        .dashboard-sidebar { border-right: none; }
    }
</style>

<div class="container-fluid customer-dashboard">
    <div class="dashboard-shell row gx-4">
        <aside class="col-lg-3 p-4 dashboard-sidebar">
            <div class="mb-4 text-center">
                <h4 class="mb-1"><?php echo t('Hello'); ?>, <?php echo $customerName; ?></h4>
                <p class="sidebar-caption mb-0"><?php echo t('Customer dashboard'); ?></p>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link active" href="#overview"><span class="sidebar-icon"><i class="fas fa-chart-pie"></i></span><span class="sidebar-label"><?php echo t('Dashboard Overview'); ?></span></a>
                <a class="nav-link" href="#view-cart"><span class="sidebar-icon"><i class="fas fa-shopping-cart"></i></span><span class="sidebar-label"><?php echo t('View Cart'); ?></span></a>
                <a class="nav-link" href="#check-products"><span class="sidebar-icon"><i class="fas fa-box-open"></i></span><span class="sidebar-label"><?php echo t('Check Products'); ?></span></a>
                <a class="nav-link" href="#browse"><span class="sidebar-icon"><i class="fas fa-search"></i></span><span class="sidebar-label"><?php echo t('Browse Marketplace'); ?></span></a>
                <a class="nav-link" href="#add-comments"><span class="sidebar-icon"><i class="fas fa-comments"></i></span><span class="sidebar-label"><?php echo t('Add Comments'); ?></span></a>
                 <a class="nav-link" href="order_history.php"><span class="sidebar-icon"><i class="fas fa-history"></i></span><span class="sidebar-label"><?php echo t('Order History'); ?></span></a>
                 <a class="nav-link" href="#inquiry-history"><span class="sidebar-icon"><i class="fas fa-message"></i></span><span class="sidebar-label"><?php echo t('Inquiry History'); ?></span></a>
            </nav>
        </aside>

        <main class="col-lg-9 p-4">
            <?php if ($message): ?>
                <div class="alert alert-success bg-success bg-opacity-10 border border-success text-white"><?php echo $message; ?></div>
            <?php endif; ?>

            <section id="overview" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Available Products'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($productsCount); ?></h3>
                                <span class="product-tag"><?php echo t('Browse fresh farm items.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Your Orders'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($ordersCount); ?></h3>
                                <span class="product-tag"><?php echo t('Quick access to your order list.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Comments Posted'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($commentsCount); ?></h3>
                                <span class="product-tag"><?php echo t('Share feedback with farmers.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="view-cart" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Your Cart'); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($ordersCount === 0): ?>
                            <div class="alert alert-secondary text-white"><?php echo t('Your cart is empty. Start browsing products to add items.'); ?></div>
                        <?php else: ?>
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <div class="d-flex align-items-center justify-content-between mb-3 comment-box p-3 rounded">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($order['or_name']); ?></h6>
                                        <div class="text-muted"><?php echo t('Quantity'); ?>: <?php echo number_format((int) ($order['quantity'] ?? 1)); ?> <?php echo htmlspecialchars($order['order_unit'] ?? 'kilogram'); ?> • <?php echo t('Category'); ?>: <?php echo htmlspecialchars($order['or_category']); ?> • <?php echo t('Ordered on'); ?> <?php echo htmlspecialchars(date('M j, Y', strtotime($order['join_date']))); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="h6 mb-1 text-success">UGX <?php echo number_format($order['price'], 2); ?> <?php echo t('total'); ?></div>
                                        <span class="badge badge-status"><?php echo t($order['status'] == 1 ? 'Active' : ($order['status'] == 2 ? 'Pending' : 'Inactive')); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <a href="order_history.php" class="btn btn-sm btn-primary mt-2"><?php echo t('View Full Order History'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="check-products" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Check Available Products'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="product-list">
                            <?php if ($productsCount === 0): ?>
                                <div class="alert alert-secondary text-white"><?php echo t('No products are available at the moment.'); ?></div>
                            <?php else: ?>
                                <?php
                                $productsResult->data_seek(0);
                                while ($product = $productsResult->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="card product-card h-100 p-3">
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="text-white"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($product['description'] ?: t('Fresh farm produce')); ?></p>
                                                    <small class="text-muted d-block mb-2"><?php echo t('Available'); ?>: <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></small>
                                                    <?php echo (int) $product['stock_quantity'] > 0 ? '<span class="badge bg-success mb-2">' . t('In stock') . '</span>' : '<span class="badge bg-danger mb-2">' . t('Out of stock') . '</span>'; ?>
                                                    <?php if (!empty($product['is_negotiable'])): ?><span class="badge bg-warning text-dark mb-2"><?php echo t('Price is negotiable'); ?></span><?php endif; ?>
                                                    <?php if (!empty($product['harvest_date'])): ?><small class="text-muted d-block mb-2"><?php echo t('Harvest date'); ?>: <?php echo htmlspecialchars(date('d M Y', strtotime($product['harvest_date']))); ?></small><?php endif; ?>
                                                    <?php if (!empty($product['seasonal_availability'])): ?><small class="text-muted d-block mb-2"><?php echo t('Season'); ?>: <?php echo htmlspecialchars($product['seasonal_availability']); ?></small><?php endif; ?>
                                                    <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($product['cat_name'] ?: t('Uncategorized')); ?></small>
                                                    <div class="badge mb-2"><?php echo htmlspecialchars($product['farmer_name'] ?: t('Local Farm Market')); ?></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <span class="text-success h6">UGX <?php echo number_format($product['price'], 2); ?> per <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></span>
                                                    <div class="d-flex gap-2">
                                                        <?php if (filter_var($product['seller_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                                            <a href="mailto:<?php echo htmlspecialchars($product['seller_email']); ?>?subject=<?php echo rawurlencode('Question about ' . $product['product_name']); ?>&body=<?php echo rawurlencode('Hello, I have a question about your product: ' . $product['product_name']); ?>" class="btn btn-sm btn-outline-success"><?php echo t('Contact Farmer'); ?></a>
                                                        <?php } ?>
                                                        <a href="#product-inquiry" data-inquiry-product="<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-outline-info"><?php echo t('Ask'); ?></a>
                                                        <a href="placeOrder.php?product=<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-primary" <?php echo (int) $product['stock_quantity'] < 1 ? 'aria-disabled="true" style="pointer-events:none;opacity:.5"' : ''; ?>><?php echo t('Add to Cart'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="browse" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Browse Marketplace'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Search Products'); ?></label>
                                <input id="searchProducts" type="text" class="form-control" placeholder="<?php echo t('Search product name...'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Filter by Category'); ?></label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="all"><?php echo t('All Categories'); ?></option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['cat_name']); ?>"><?php echo htmlspecialchars($category['cat_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-3" id="browse-results">
                            <?php $productsResult->data_seek(0); while ($product = $productsResult->fetch_assoc()): ?>
                                <div class="col-md-6 browse-card" data-name="<?php echo strtolower(htmlspecialchars($product['product_name'])); ?>" data-category="<?php echo strtolower(htmlspecialchars($product['cat_name'] ?? '')); ?>">
                                    <div class="card product-card p-3">
                                        <div class="card-body">
                                            <h6 class="text-white"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($product['description'] ?: t('Fresh farm produce')); ?></p>
                                            <small class="text-muted d-block mb-2"><?php echo t('Available'); ?>: <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></small>
                                            <?php echo (int) $product['stock_quantity'] > 0 ? '<span class="badge bg-success mb-2">' . t('In stock') . '</span>' : '<span class="badge bg-danger mb-2">' . t('Out of stock') . '</span>'; ?>
                                            <?php if (!empty($product['is_negotiable'])): ?><span class="badge bg-warning text-dark mb-2"><?php echo t('Price is negotiable'); ?></span><?php endif; ?>
                                            <?php if (!empty($product['harvest_date'])): ?><small class="text-muted d-block mb-2"><?php echo t('Harvest date'); ?>: <?php echo htmlspecialchars(date('d M Y', strtotime($product['harvest_date']))); ?></small><?php endif; ?>
                                            <?php if (!empty($product['seasonal_availability'])): ?><small class="text-muted d-block mb-2"><?php echo t('Season'); ?>: <?php echo htmlspecialchars($product['seasonal_availability']); ?></small><?php endif; ?>
                                            <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($product['cat_name'] ?: t('Uncategorized')); ?></small>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-success">UGX <?php echo number_format($product['price'], 2); ?> per <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></span>
                                                <div class="d-flex gap-2">
                                                    <?php if (filter_var($product['seller_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($product['seller_email']); ?>?subject=<?php echo rawurlencode('Question about ' . $product['product_name']); ?>&body=<?php echo rawurlencode('Hello, I have a question about your product: ' . $product['product_name']); ?>" class="btn btn-sm btn-outline-success"><?php echo t('Contact Farmer'); ?></a>
                                                    <?php } ?>
                                                    <a href="#product-inquiry" data-inquiry-product="<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-outline-info"><?php echo t('Ask'); ?></a>
                                                    <a href="placeOrder.php?product=<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-primary" <?php echo (int) $product['stock_quantity'] < 1 ? 'aria-disabled="true" style="pointer-events:none;opacity:.5"' : ''; ?>><?php echo t('Order'); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="product-inquiry" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><?php echo t('Product Inquiry'); ?></h5></div>
                    <div class="card-body">
                        <?php if ($inquiryMessage): ?><div class="alert alert-success bg-success bg-opacity-10 border border-success text-white"><?php echo htmlspecialchars($inquiryMessage); ?></div><?php endif; ?>
                        <?php if ($inquiryError): ?><div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-white"><?php echo htmlspecialchars($inquiryError); ?></div><?php endif; ?>
                        <form method="post" id="inquiryForm">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label" for="inquiryProduct"><?php echo t('Product'); ?></label><select class="form-select" name="inquiry_product_id" id="inquiryProduct" required><option value=""><?php echo t('Select a product'); ?></option><?php $productsResult->data_seek(0); while ($inquiryProductOption = $productsResult->fetch_assoc()): ?><option value="<?php echo (int) $inquiryProductOption['product_id']; ?>" <?php echo ((int) ($_POST['inquiry_product_id'] ?? 0) === (int) $inquiryProductOption['product_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($inquiryProductOption['product_name']); ?></option><?php endwhile; ?></select></div>
                                <div class="col-md-8"><label class="form-label" for="inquirySubject"><?php echo t('Subject'); ?></label><input class="form-control" type="text" name="inquiry_subject" id="inquirySubject" value="<?php echo htmlspecialchars($_POST['inquiry_subject'] ?? ''); ?>" required></div>
                                <div class="col-12"><label class="form-label" for="inquiryMessage"><?php echo t('Your inquiry'); ?></label><textarea class="form-control" name="inquiry_message" id="inquiryMessage" rows="4" required><?php echo htmlspecialchars($_POST['inquiry_message'] ?? ''); ?></textarea></div>
                                <div class="col-12"><button type="submit" name="submit_inquiry" class="btn btn-success"><i class="fas fa-paper-plane me-2"></i><?php echo t('Send Inquiry'); ?></button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="inquiry-history" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><?php echo t('Inquiry History'); ?></h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th><?php echo t('Product'); ?></th><th><?php echo t('Subject'); ?></th><th><?php echo t('Message'); ?></th><th><?php echo t('Status'); ?></th><th><?php echo t('Response'); ?></th><th><?php echo t('Date'); ?></th></tr></thead>
                                <tbody>
                                    <?php if ($inquiryHistory && $inquiryHistory->num_rows > 0): while ($inquiry = $inquiryHistory->fetch_assoc()): $inquiryStatus = (int) $inquiry['status']; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inquiry['product_name'] ?: t('Unavailable')); ?></td>
                                            <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></td>
                                            <td><span class="badge text-bg-<?php echo $inquiryClasses[$inquiryStatus] ?? 'secondary'; ?>"><?php echo htmlspecialchars($inquiryLabels[$inquiryStatus] ?? t('Pending')); ?></span></td>
                                            <td><?php echo nl2br(htmlspecialchars($inquiry['response'] ?: t('Awaiting response'))); ?></td>
                                            <td><small><?php echo htmlspecialchars(date('M j, Y', strtotime($inquiry['created_at']))); ?></small></td>
                                        </tr>
                                    <?php endwhile; else: ?><tr><td colspan="6" class="text-center text-muted py-4"><?php echo t('No inquiries yet.'); ?></td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="add-comments" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Add Comments'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('Subject'); ?></label>
                                <input type="text" name="comment_subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('Comment'); ?></label>
                                <textarea name="comment_text" rows="5" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-primary"><?php echo t('Submit Comment'); ?></button>
                        </form>
                        <?php if ($recentComments && $recentComments->num_rows > 0): ?>
                            <div class="mt-4">
                                <h6 class="section-title"><?php echo t('Recent Comments'); ?></h6>
                                <?php while ($comment = $recentComments->fetch_assoc()): ?>
                                    <div class="comment-box p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong><?php echo htmlspecialchars($comment['subject']); ?></strong>
                                            <span class="badge badge-status"><?php echo t($comment['status'] == 2 ? 'Pending' : 'Published'); ?></span>
                                        </div>
                                        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($comment['comments'])); ?></p>
                                        <small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($comment['cmt_date']))); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    document.getElementById('searchProducts').addEventListener('input', filterBrowse);
    document.getElementById('categoryFilter').addEventListener('change', filterBrowse);
    function filterBrowse() {
        const text = document.getElementById('searchProducts').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value.toLowerCase();
        document.querySelectorAll('#browse-results .browse-card').forEach(card => {
            const name = card.dataset.name || '';
            const cat = card.dataset.category || '';
            const matchesText = name.includes(text);
            const matchesCat = category === 'all' || cat === category;
            card.style.display = matchesText && matchesCat ? 'block' : 'none';
        });
    }
    document.querySelectorAll('[data-inquiry-product]').forEach(function (button) {
        button.addEventListener('click', function () {
            const productSelect = document.getElementById('inquiryProduct');
            const subject = document.getElementById('inquirySubject');
            if (productSelect && subject) {
                productSelect.value = button.dataset.inquiryProduct;
                subject.value = 'Question about ' + productSelect.options[productSelect.selectedIndex].text;
                subject.focus();
            }
        });
    });
</script>

<?php include "inc/footer.php"; ?>
