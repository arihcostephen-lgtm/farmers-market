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
  $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $userId = (int) $_SESSION['user_id'];
    $userPhone = mysqli_real_escape_string($db, $_SESSION['user_phone'] ?? '');
  $db->begin_transaction();
  $productQuery = $db->query("SELECT product_name, price, stock_quantity FROM products WHERE product_id = $productId AND status = 1 FOR UPDATE");
  $product = $productQuery ? $productQuery->fetch_assoc() : null;
  if ($product && $quantity <= (int) $product['stock_quantity']) {
    $totalPrice = (float) $product['price'] * $quantity;
    $productName = mysqli_real_escape_string($db, $product['product_name']);
    $insertSql = "INSERT INTO order_list (user_id, user_phone, or_name, or_category, price, quantity, status, join_date) VALUES ('$userId', '$userPhone', '$productName', '$productId', '$totalPrice', '$quantity', 0, NOW())";
    if ($db->query($insertSql) && $db->query("UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $productId AND stock_quantity >= $quantity")) {
      $db->commit();
      header("Location: customerDashboard.php");
      exit;
    }
    }
  $db->rollback();
  $orderError = 'The requested quantity is not available.';
}

$productId = isset($_GET['product']) ? (int) $_GET['product'] : 0;
$product = $db->query("SELECT product_name, price, stock_quantity FROM products WHERE product_id = $productId AND status = 1 LIMIT 1")->fetch_assoc();
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
        <?php if (!empty($orderError)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($orderError); ?></div><?php } ?>
        <?php if ($product) { ?>
          <p><strong>Product:</strong> <?php echo htmlspecialchars($product['product_name']); ?></p>
          <p><strong>Price per item:</strong> UGX<?php echo number_format($product['price'], 2); ?></p>
          <p><strong>Available:</strong> <?php echo number_format((int) $product['stock_quantity']); ?></p>
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control mb-3" min="1" max="<?php echo (int) $product['stock_quantity']; ?>" value="1" required <?php echo (int) $product['stock_quantity'] < 1 ? 'disabled' : ''; ?>>
            <?php if ((int) $product['stock_quantity'] < 1) { ?><div class="alert alert-warning">This product is out of stock.</div><?php } ?>
            <button type="submit" name="place_order" class="btn btn-success" <?php echo (int) $product['stock_quantity'] < 1 ? 'disabled' : ''; ?>>Confirm Order</button>
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
