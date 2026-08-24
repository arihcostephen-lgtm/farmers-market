<?php
session_start();
ob_start();
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/admin/inc/email.php';
require_once __DIR__ . '/inc/language.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$productId = (int) ($_GET['product'] ?? $_POST['product_id'] ?? 0);
$productResult = mysqli_query($db, "SELECT product_id, product_name, seller_email FROM products WHERE product_id='$productId' AND status=1 LIMIT 1");
$product = $productResult ? mysqli_fetch_assoc($productResult) : null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry']) && $product) {
    $subject = mysqli_real_escape_string($db, trim($_POST['subject'] ?? ''));
    $inquiryText = mysqli_real_escape_string($db, trim($_POST['message'] ?? ''));
    if ($subject === '' || $inquiryText === '') {
        $error = 'Please enter a subject and message.';
    } else {
        $buyerId = (int) $_SESSION['user_id'];
        $buyerEmail = mysqli_real_escape_string($db, $_SESSION['user_email'] ?? '');
        $insert = "INSERT INTO product_inquiries (product_id, buyer_id, buyer_email, subject, message, status, created_at) VALUES ('$productId', '$buyerId', '$buyerEmail', '$subject', '$inquiryText', 0, NOW())";
        if (mysqli_query($db, $insert)) {
            farmers_market_send_email(
                $db,
                $product['seller_email'],
                'New inquiry about ' . $product['product_name'],
                "A customer submitted a new product inquiry.\n\n"
                    . "Product: " . $product['product_name'] . "\n"
                    . "Subject: " . $_POST['subject'] . "\n\n"
                    . $_POST['message'],
                $_SESSION['user_email'] ?? ''
            );
            $message = 'Your inquiry was sent. It is now pending a response.';
        } else {
            $error = 'Unable to send your inquiry right now.';
        }
    }
}
?>
<!doctype html>
<html lang="<?php echo $currentLanguage === 'lg' ? 'lg' : 'en'; ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Product Inquiry | Farmers Market</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5"><div class="card shadow-sm"><div class="card-body">
  <?php if ($product) { ?>
    <h3>Ask about <?php echo htmlspecialchars($product['product_name']); ?></h3>
    <?php if ($message) { ?><div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div><?php } ?>
    <?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
    <?php if (!$message) { ?><form method="post"><input type="hidden" name="product_id" value="<?php echo $productId; ?>"><div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" required></div><div class="mb-3"><label class="form-label">Your inquiry</label><textarea name="message" class="form-control" rows="5" required></textarea></div><button type="submit" name="submit_inquiry" class="btn btn-success">Send Inquiry</button><a href="customerDashboard.php" class="btn btn-outline-secondary ms-2">Back</a></form><?php } else { ?><a href="customerDashboard.php" class="btn btn-success">Back to Marketplace</a><?php } ?>
  <?php } else { ?><div class="alert alert-warning">This product is no longer available.</div><a href="customerDashboard.php" class="btn btn-outline-secondary">Back</a><?php } ?>
</div></div></div>
</body></html>
<?php ob_end_flush(); ?>
