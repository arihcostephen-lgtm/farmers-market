<?php
session_start();
ob_start();
include "admin/inc/db.php";

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['place_order'])) {
    $productId = (int) $_POST['product_id'];
    $userId = (int) $_SESSION['user_id'];
    $userPhone = mysqli_real_escape_string($db, $_SESSION['user_phone'] ?? '');
    $productQuery = $db->query("SELECT product_name, price, seller_email FROM products WHERE product_id = $productId AND status = 1 LIMIT 1");
    if ($product = $productQuery->fetch_assoc()) {
      $insertSql = "INSERT INTO order_list (user_id, user_phone, or_name, or_category, price, status, join_date) VALUES ('$userId', '$userPhone', '{$product['product_name']}', '$productId', '{$product['price']}', 0, NOW())";
        $db->query($insertSql);
        header("Location: customerDashboard.php");
        exit;
    }
}

$productId = isset($_GET['product']) ? (int) $_GET['product'] : 0;
$product = $db->query("SELECT product_name, price FROM products WHERE product_id = $productId AND status = 1 LIMIT 1")->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Place Order | Local Farm Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Place Your Order</h3>
        <?php if ($product) { ?>
          <p><strong>Product:</strong> <?php echo htmlspecialchars($product['product_name']); ?></p>
          <p><strong>Price:</strong> UGX<?php echo number_format($product['price'], 2); ?></p>
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <button type="submit" name="place_order" class="btn btn-success">Confirm Order</button>
            <a href="customerDashboard.php" class="btn btn-outline-secondary">Cancel</a>
          </form>
        <?php } else { ?>
          <div class="alert alert-warning">The selected product is no longer available.</div>
        <?php } ?>
      </div>
    </div>
  </div>
</body>
</html>
