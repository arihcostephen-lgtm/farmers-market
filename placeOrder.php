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
    $deliveryLocationInput = trim($_POST['delivery_location'] ?? '');
    $deliveryLocation = mysqli_real_escape_string($db, $deliveryLocationInput);
    $deliveryNotes = mysqli_real_escape_string($db, trim($_POST['delivery_notes'] ?? ''));
    if ($deliveryLocationInput === '') {
      $orderError = 'Please provide a delivery location.';
    }
  $db->begin_transaction();
  $productQuery = $db->query("SELECT product_name, price, product_unit, stock_quantity FROM products WHERE product_id = $productId AND status != 0 FOR UPDATE");
  $product = $productQuery ? $productQuery->fetch_assoc() : null;
  if (empty($orderError) && $product && $quantity <= (int) $product['stock_quantity']) {
    $subtotal = (float) $product['price'] * $quantity;
    $taxRule = $db->query("SELECT * FROM tax_rules WHERE status = 1 AND min_quantity <= $quantity AND (max_quantity IS NULL OR max_quantity >= $quantity) ORDER BY rate_percent DESC LIMIT 1")->fetch_assoc();
    $taxRate = isset($taxRule['rate_percent']) ? (float) $taxRule['rate_percent'] : 0.00;
    $taxAmount = round($subtotal * ($taxRate / 100), 2);
    $totalPrice = round($subtotal + $taxAmount, 2);
    $productName = mysqli_real_escape_string($db, $product['product_name']);
    $orderUnit = mysqli_real_escape_string($db, $product['product_unit'] ?? 'kilogram');
    $insertSql = "INSERT INTO order_list (user_id, user_phone, delivery_location, delivery_notes, or_name, or_category, price, tax_amount, total_amount, quantity, order_unit, status, join_date) VALUES ('$userId', '$userPhone', '$deliveryLocation', '$deliveryNotes', '$productName', '$productId', '$subtotal', '$taxAmount', '$totalPrice', '$quantity', '$orderUnit', 0, NOW())";
    if ($db->query($insertSql) && $db->query("UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $productId AND stock_quantity >= $quantity")) {
      $db->commit();
      header("Location: customerDashboard.php");
      exit;
    }
    }
  $db->rollback();
  if (empty($orderError)) {
    $orderError = 'The requested quantity is not available.';
  }
}

$productId = isset($_GET['product']) ? (int) $_GET['product'] : 0;
if ($productId > 0 && !isset($_POST['place_order'])) {
  $db->query("UPDATE products SET view_count = view_count + 1 WHERE product_id = $productId AND status != 0");
}
$product = $db->query("SELECT product_name, price, product_unit, stock_quantity FROM products WHERE product_id = $productId AND status != 0 LIMIT 1")->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Place Order | Local Farm Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    function updateOrderTotal() {
      const quantity = Number(document.getElementById('quantity')?.value || 0);
      const unitPrice = <?php echo json_encode((float) ($product['price'] ?? 0)); ?>;
      const total = document.getElementById('orderTotal');
      if (total) total.textContent = (quantity * unitPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
  </script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Place Your Order</h3>
        <?php if (!empty($orderError)) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($orderError); ?></div><?php } ?>
        <?php if ($product) { ?>
          <p><strong>Product:</strong> <?php echo htmlspecialchars($product['product_name']); ?></p>
          <p><strong>Price per <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?>:</strong> UGX <?php echo number_format($product['price'], 2); ?></p>
          <p><strong>Available:</strong> <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?><?php if ((int) $product['stock_quantity'] > 0) { ?> (maximum order)<?php } ?></p>
          <p><strong>Status:</strong> <?php echo (int) $product['stock_quantity'] > 0 ? '<span class="badge bg-success">In stock</span>' : '<span class="badge bg-danger">Out of stock</span>'; ?></p>
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <label for="quantity" class="form-label">Quantity (<?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?>)</label>
            <input type="number" id="quantity" name="quantity" class="form-control mb-3" min="1" max="<?php echo (int) $product['stock_quantity']; ?>" value="1" required <?php echo (int) $product['stock_quantity'] < 1 ? 'disabled' : ''; ?>>
            <div class="form-text mb-3">Total: UGX <span id="orderTotal"><?php echo number_format((float) $product['price'], 2, '.', ''); ?></span></div>
            <label for="delivery_location" class="form-label">Delivery location</label>
            <textarea id="delivery_location" name="delivery_location" class="form-control mb-3" rows="2" placeholder="Enter the address or location where your order should be delivered" required><?php echo htmlspecialchars($_POST['delivery_location'] ?? ''); ?></textarea>
            <label for="delivery_notes" class="form-label">Delivery notes (optional)</label>
            <textarea id="delivery_notes" name="delivery_notes" class="form-control mb-3" rows="2"><?php echo htmlspecialchars($_POST['delivery_notes'] ?? ''); ?></textarea>
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
  <script>document.getElementById('quantity')?.addEventListener('input', updateOrderTotal);</script>
</body>
</html>
