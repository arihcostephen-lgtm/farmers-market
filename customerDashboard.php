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

$productsSql = "SELECT c.*, u.user_name AS farmer_name FROM category c LEFT JOIN users u ON u.user_email = c.seller_email WHERE c.status = 1 ORDER BY c.cat_name ASC";
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $subject = $db->real_escape_string(trim($_POST['comment_subject']));
    $commentText = $db->real_escape_string(trim($_POST['comment_text']));
    $commentStatus = 2;
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
?>

<style>
    body { background: #06130d; color: #e9fff4; }
    .customer-dashboard { min-height: 100vh; padding: 30px 0; }
    .dashboard-shell { background: rgba(6,19,13,0.95); border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.25); }
    .dashboard-sidebar { background: #082616; border-right: 1px solid rgba(255,255,255,0.06); }
    .dashboard-sidebar .nav-link { color: #b8f8c6; border-radius: 12px; margin-bottom: 8px; }
    .dashboard-sidebar .nav-link:hover, .dashboard-sidebar .nav-link.active { background: linear-gradient(135deg, #0e5f35 0%, #0b8a4a 100%); color: #ffffff; }
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
    @media (max-width: 991px) {
        .dashboard-sidebar { border-right: none; }
    }
</style>

<div class="container-fluid customer-dashboard">
    <div class="dashboard-shell row gx-4">
        <aside class="col-lg-3 p-4 dashboard-sidebar">
            <div class="mb-4 text-center">
                <h4 class="mb-1">Hello, <?php echo $customerName; ?></h4>
                <p class="text-muted">Customer dashboard</p>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link active" href="#overview"><i class="fas fa-chart-pie me-2"></i>Dashboard Overview</a>
                <a class="nav-link" href="#view-cart"><i class="fas fa-shopping-cart me-2"></i>View Cart</a>
                <a class="nav-link" href="#check-products"><i class="fas fa-box-open me-2"></i>Check Products</a>
                <a class="nav-link" href="#browse"><i class="fas fa-search me-2"></i>Browse Marketplace</a>
                <a class="nav-link" href="#add-comments"><i class="fas fa-comments me-2"></i>Add Comments</a>
                <a class="nav-link" href="order_history.php"><i class="fas fa-history me-2"></i>Order History</a>
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
                                <h6 class="section-title">Available Products</h6>
                                <h3 class="mt-3"><?php echo number_format($productsCount); ?></h3>
                                <span class="product-tag">Browse fresh farm items.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title">Your Orders</h6>
                                <h3 class="mt-3"><?php echo number_format($ordersCount); ?></h3>
                                <span class="product-tag">Quick access to your order list.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title">Comments Posted</h6>
                                <h3 class="mt-3"><?php echo number_format($commentsCount); ?></h3>
                                <span class="product-tag">Share feedback with farmers.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="view-cart" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Your Cart</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($ordersCount === 0): ?>
                            <div class="alert alert-secondary text-white">Your cart is empty. Start browsing products to add items.</div>
                        <?php else: ?>
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <div class="d-flex align-items-center justify-content-between mb-3 comment-box p-3 rounded">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($order['or_name']); ?></h6>
                                        <div class="text-muted">Category: <?php echo htmlspecialchars($order['or_category']); ?> • Ordered on <?php echo htmlspecialchars(date('M j, Y', strtotime($order['join_date']))); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="h6 mb-1 text-success">UGX <?php echo number_format($order['price'], 2); ?></div>
                                        <span class="badge badge-status"><?php echo $order['status'] == 1 ? 'Active' : ($order['status'] == 2 ? 'Pending' : 'Inactive'); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <a href="order_history.php" class="btn btn-sm btn-primary mt-2">View Full Order History</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="check-products" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Check Available Products</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="product-list">
                            <?php if ($productsCount === 0): ?>
                                <div class="alert alert-secondary text-white">No products are available at the moment.</div>
                            <?php else: ?>
                                <?php
                                $productsResult->data_seek(0);
                                while ($product = $productsResult->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="card product-card h-100 p-3">
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="text-white"><?php echo htmlspecialchars($product['cat_name']); ?></h6>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($product['cat_desc'] ?: 'Fresh farm produce'); ?></p>
                                                    <div class="badge mb-2"><?php echo htmlspecialchars($product['farmer_name'] ?: 'Local Farm Market'); ?></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <span class="text-success h6">UGX <?php echo number_format($product['price'], 2); ?></span>
                                                    <a href="placeOrder.php?product=<?php echo (int) $product['cat_id']; ?>" class="btn btn-sm btn-primary">Add to Cart</a>
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
                        <h5 class="mb-0">Browse Marketplace</h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">Search Products</label>
                                <input id="searchProducts" type="text" class="form-control" placeholder="Search product name...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Filter by Category</label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="all">All Categories</option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['cat_name']); ?>"><?php echo htmlspecialchars($category['cat_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-3" id="browse-results">
                            <?php $productsResult->data_seek(0); while ($product = $productsResult->fetch_assoc()): ?>
                                <div class="col-md-6 browse-card" data-name="<?php echo strtolower(htmlspecialchars($product['cat_name'])); ?>" data-category="<?php echo strtolower(htmlspecialchars($product['cat_name'])); ?>">
                                    <div class="card product-card p-3">
                                        <div class="card-body">
                                            <h6 class="text-white"><?php echo htmlspecialchars($product['cat_name']); ?></h6>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($product['cat_desc'] ?: 'Fresh farm produce'); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-success">UGX <?php echo number_format($product['price'], 2); ?></span>
                                                <a href="placeOrder.php?product=<?php echo (int) $product['cat_id']; ?>" class="btn btn-sm btn-primary">Order</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="add-comments" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Add Comments</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="comment_subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comment</label>
                                <textarea name="comment_text" rows="5" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-primary">Submit Comment</button>
                        </form>
                        <?php if ($recentComments && $recentComments->num_rows > 0): ?>
                            <div class="mt-4">
                                <h6 class="section-title">Recent Comments</h6>
                                <?php while ($comment = $recentComments->fetch_assoc()): ?>
                                    <div class="comment-box p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong><?php echo htmlspecialchars($comment['subject']); ?></strong>
                                            <span class="badge badge-status"><?php echo htmlspecialchars($comment['status'] == 2 ? 'Pending' : 'Published'); ?></span>
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
</script>

<?php include "inc/footer.php"; ?>
