<?php
session_start();
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/inc/mobile_money.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$orderId = (int) ($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$orderQuery = mysqli_prepare($db, "SELECT or_id, total_amount, price, user_phone, payment_status FROM order_list WHERE or_id=? AND user_id=? LIMIT 1");
mysqli_stmt_bind_param($orderQuery, 'ii', $orderId, $userId);
mysqli_stmt_execute($orderQuery);
$order = mysqli_stmt_get_result($orderQuery)->fetch_assoc();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
  $provider = $_POST['provider'] ?? '';
  $phone = uganda_phone($_POST['phone'] ?? $order['user_phone']);
  $amount = (float) ($order['total_amount'] ?: $order['price']);
  if (($order['payment_status'] ?? 'unpaid') === 'paid') $error = 'This order has already been paid.';
  elseif (!in_array($provider, ['mtn_uganda', 'airtel_uganda', 'ussd'], true) || $phone === '') $error = 'Select a payment method and enter a valid Ugandan phone number.';
  else {
    $reference = 'FM-' . $orderId . '-' . bin2hex(random_bytes(6));
    $status = 'pending';
    $insert = mysqli_prepare($db, "INSERT INTO payment_transactions (order_id,user_id,provider,amount,phone,reference,status) VALUES (?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($insert, 'iisdsss', $orderId, $userId, $provider, $amount, $phone, $reference, $status);
    if (!mysqli_stmt_execute($insert)) $error = 'Unable to create the payment request.';
    elseif ($provider === 'ussd') $message = 'USSD request recorded. Dial ' . payment_config('USSD_SHORT_CODE', '*165#') . ' and complete payment using reference ' . $reference . '.';
    else {
      $result = $provider === 'mtn_uganda' ? start_mtn_payment($amount, $phone, $reference) : start_airtel_payment($amount, $phone, $reference);
      if (($result['status'] < 200 || $result['status'] >= 300) && $result['status'] !== 202) $error = 'The provider could not start the payment. Please try again or use USSD.';
      else { mysqli_query($db, "UPDATE order_list SET payment_status='pending', updated_at=NOW() WHERE or_id=$orderId"); $message = 'Payment prompt sent to ' . htmlspecialchars($phone) . '. Approve it on your phone; this page will update after confirmation.'; }
    }
  }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Uganda Mobile Money Payment</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5"><div class="card mx-auto shadow-sm" style="max-width:540px"><div class="card-body"><h2>Pay for order #<?php echo $orderId; ?></h2><?php if (!$order) { ?><div class="alert alert-danger">Order not found.</div><?php } else { ?><p class="lead">UGX <?php echo number_format((float) ($order['total_amount'] ?: $order['price']), 2); ?></p><?php if ($message) { ?><div class="alert alert-success"><?php echo $message; ?></div><?php } ?><?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?><form method="post"><input type="hidden" name="order_id" value="<?php echo $orderId; ?>"><label class="form-label" for="phone">Ugandan mobile number</label><input class="form-control mb-3" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? $order['user_phone']); ?>" placeholder="07XXXXXXXX" required><label class="form-label" for="provider">Payment method</label><select class="form-select mb-3" id="provider" name="provider" required><option value="mtn_uganda">MTN MoMo Uganda</option><option value="airtel_uganda">Airtel Money Uganda</option><option value="ussd">Manual USSD fallback</option></select><button class="btn btn-success w-100" type="submit">Continue Payment</button></form><?php } ?><a class="btn btn-link mt-3" href="order_history.php">Back to orders</a></div></div></main></body></html>
