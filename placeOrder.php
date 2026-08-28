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
  $productQuery = $db->query("SELECT product_name, price, product_unit, stock_quantity, category_id FROM products WHERE product_id = $productId AND status != 0 FOR UPDATE");
  $product = $productQuery ? $productQuery->fetch_assoc() : null;
  if (empty($orderError) && $product && $quantity <= (int) $product['stock_quantity']) {
    $subtotal = (float) $product['price'] * $quantity;
    $productUnit = mysqli_real_escape_string($db, $product['product_unit'] ?? 'kilogram');
    $categoryId = (int) ($product['category_id'] ?? 0);
      $taxQuery = $db->query("SELECT rate_percent FROM tax_rules WHERE status = 1 AND min_quantity <= $quantity AND (max_quantity IS NULL OR max_quantity >= $quantity) AND (applies_to = 'all' OR applies_to = '$categoryId') AND (applies_unit = 'all' OR applies_unit = '$productUnit') ORDER BY (applies_to = '$categoryId') DESC, (applies_unit = '$productUnit') DESC, rate_percent DESC LIMIT 1");
      $taxRule = $taxQuery ? $taxQuery->fetch_assoc() : null;
    $taxRate = isset($taxRule['rate_percent']) ? (float) $taxRule['rate_percent'] : 0.00;
    $taxAmount = round($subtotal * ($taxRate / 100), 2);
    $totalPrice = round($subtotal + $taxAmount, 2);
    $productName = mysqli_real_escape_string($db, $product['product_name']);
    $orderUnit = $productUnit;
    $insertSql = "INSERT INTO order_list (user_id, user_phone, delivery_location, delivery_notes, or_name, or_category, price, tax_amount, total_amount, quantity, order_unit, status, join_date) VALUES ('$userId', '$userPhone', '$deliveryLocation', '$deliveryNotes', '$productName', '$productId', '$subtotal', '$taxAmount', '$totalPrice', '$quantity', '$orderUnit', 0, NOW())";
    if ($db->query($insertSql) && $db->query("UPDATE products SET stock_quantity = stock_quantity - $quantity WHERE product_id = $productId AND stock_quantity >= $quantity")) {
      $newOrderId = $db->insert_id;
      $db->commit();
      header("Location: payment.php?order_id=" . (int) $newOrderId);
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
$product = $db->query("SELECT product_name, price, product_unit, stock_quantity, category_id FROM products WHERE product_id = $productId AND status != 0 LIMIT 1")->fetch_assoc();
  $previewQuantity = max(1, (int) ($_POST['quantity'] ?? 1));
$taxRule = null;
  $taxRules = [];
if ($product) {
  $previewUnit = mysqli_real_escape_string($db, $product['product_unit'] ?? 'kilogram');
  $previewCategory = (int) ($product['category_id'] ?? 0);
    $taxQuery = $db->query("SELECT min_quantity, max_quantity, rate_percent FROM tax_rules WHERE status = 1 AND (applies_to = 'all' OR applies_to = '$previewCategory') AND (applies_unit = 'all' OR applies_unit = '$previewUnit') ORDER BY (applies_to = '$previewCategory') DESC, (applies_unit = '$previewUnit') DESC, rate_percent DESC");
    if ($taxQuery) {
      while ($taxRow = $taxQuery->fetch_assoc()) {
        $taxRules[] = ['min' => (int) $taxRow['min_quantity'], 'max' => $taxRow['max_quantity'] === null ? null : (int) $taxRow['max_quantity'], 'rate' => (float) $taxRow['rate_percent']];
      }
    }
    foreach ($taxRules as $taxCandidate) {
      if ($taxCandidate['min'] <= $previewQuantity && ($taxCandidate['max'] === null || $taxCandidate['max'] >= $previewQuantity)) {
        $taxRule = $taxCandidate;
        break;
      }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Place Order | Local Farm Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/css/place-order.css" rel="stylesheet">
</head>
<body class="checkout-page">
  <main class="container checkout-shell py-4 py-md-5">
    <header class="checkout-header checkout-reveal">
      <div><div class="checkout-kicker">Local farm market / checkout</div><h1 class="checkout-title mb-2">Place your order</h1><p class="checkout-subtitle mb-0">Choose your quantity, confirm the delivery details, and continue to secure payment.</p></div>
      <a href="customerDashboard.php" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Back to marketplace</a>
    </header>
    <?php if (!empty($orderError)) { ?><div class="alert checkout-alert checkout-reveal" role="alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($orderError); ?></div><?php } ?>
    <?php if ($product) { ?>
      <div class="row g-4 align-items-start">
        <div class="col-lg-5 checkout-reveal">
          <section class="checkout-panel product-summary p-4">
            <div class="summary-icon mb-4"><i class="fa-solid fa-basket-shopping"></i></div>
            <div class="checkout-kicker mb-2">Selected produce</div>
            <h2 class="mb-3"><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <div class="summary-line"><span>Price</span><strong>UGX <?php echo number_format($product['price'], 2); ?> / <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></strong></div>
            <div class="summary-line"><span>Available stock</span><strong><?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></strong></div>
            <div class="mt-4"><span class="badge stock-badge rounded-pill px-3 py-2"><i class="fa-solid fa-circle-check me-1"></i><?php echo (int) $product['stock_quantity'] > 0 ? 'In stock' : 'Out of stock'; ?></span></div>
          </section>
        </div>
        <div class="col-lg-7 checkout-reveal">
          <section class="checkout-panel p-4 p-md-5">
            <h3 class="mb-1">Delivery details</h3><p class="checkout-page text-muted mb-4">Your order will be sent for payment after submission.</p>
            <form method="post" id="placeOrderForm" data-price="<?php echo htmlspecialchars((float) ($product['price'] ?? 0)); ?>" data-tax-rules="<?php echo htmlspecialchars(json_encode($taxRules), ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
              <label for="quantity" class="form-label">Quantity (<?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?>)</label>
              <div class="quantity-control mb-2"><button type="button" class="btn" data-quantity-change="decrease" aria-label="Decrease quantity"><i class="fa-solid fa-minus"></i></button><input type="number" id="quantity" name="quantity" class="form-control" min="1" max="<?php echo (int) $product['stock_quantity']; ?>" value="1" required <?php echo (int) $product['stock_quantity'] < 1 ? 'disabled' : ''; ?>><button type="button" class="btn" data-quantity-change="increase" aria-label="Increase quantity"><i class="fa-solid fa-plus"></i></button></div>
              <div class="form-text mb-4">Maximum available: <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></div>
              <label for="delivery_location" class="form-label">Delivery location</label>
              <textarea id="delivery_location" name="delivery_location" class="form-control mb-4" rows="3" placeholder="Enter the address or location where your order should be delivered" required><?php echo htmlspecialchars($_POST['delivery_location'] ?? ''); ?></textarea>
              <label for="delivery_notes" class="form-label">Delivery notes <span class="text-muted fw-normal">(optional)</span></label>
              <textarea id="delivery_notes" name="delivery_notes" class="form-control" rows="3" placeholder="Landmark, preferred time, or handling instructions"><?php echo htmlspecialchars($_POST['delivery_notes'] ?? ''); ?></textarea>
              <div class="total-panel"><div class="total-row"><span>Subtotal</span><strong>UGX <span id="orderSubtotal"><?php echo number_format((float) $product['price'], 2, '.', ''); ?></span></strong></div><div class="total-row"><span>Tax</span><strong>UGX <span id="orderTax">0.00</span></strong></div><div class="total-row total-final"><span>Total due</span><strong>UGX <span id="orderTotal"><?php echo number_format((float) $product['price'], 2, '.', ''); ?></span></strong></div></div>
              <?php if ((int) $product['stock_quantity'] < 1) { ?><div class="alert checkout-alert mt-4 mb-0">This product is currently out of stock.</div><?php } else { ?><button type="submit" name="place_order" class="btn btn-success btn-lg w-100 mt-4"><i class="fa-solid fa-lock me-2"></i>Continue to payment</button><?php } ?>
            </form>
          </section>
        </div>
      </div>
    <?php } else { ?>
      <div class="checkout-panel p-5 text-center checkout-reveal"><i class="fa-solid fa-box-open fs-1 text-warning mb-3"></i><h2>Product unavailable</h2><p class="checkout-subtitle mx-auto">The selected product is no longer available.</p><a href="customerDashboard.php" class="btn btn-success">Return to marketplace</a></div>
    <?php } ?>
  </main>
  <script src="assets/js/place-order.js"></script>
</body>
</html>
