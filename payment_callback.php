<?php
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/inc/mobile_money.php';
$payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$callbackSecret = payment_config('PAYMENT_CALLBACK_SECRET');
if ($callbackSecret !== '' && !hash_equals($callbackSecret, (string) ($_GET['secret'] ?? $_SERVER['HTTP_X_PAYMENT_CALLBACK_SECRET'] ?? ''))) {
  http_response_code(401); echo json_encode(['error' => 'Unauthorised callback']); exit;
}
$reference = trim($payload['reference'] ?? $payload['externalId'] ?? $payload['transaction']['id'] ?? $_GET['reference'] ?? '');
$providerStatus = strtolower((string) ($payload['status'] ?? $payload['transaction']['status'] ?? ''));
$successful = in_array($providerStatus, ['success', 'successful', 'completed'], true);
if ($reference === '') { http_response_code(400); echo json_encode(['error' => 'Missing payment reference']); exit; }
$payment = mysqli_prepare($db, "SELECT payment_id, batch_id, order_id, phone, amount, status FROM payment_transactions WHERE reference=? OR provider_reference=? LIMIT 1");
mysqli_stmt_bind_param($payment, 'ss', $reference, $reference);
mysqli_stmt_execute($payment);
$record = mysqli_stmt_get_result($payment)->fetch_assoc();
if (!$record) { http_response_code(404); echo json_encode(['error' => 'Unknown payment']); exit; }
if ($record['status'] !== 'successful' && $successful) {
  $update = mysqli_prepare($db, "UPDATE payment_transactions SET status='successful', provider_reference=?, provider_response=?, updated_at=NOW() WHERE payment_id=? AND status<>'successful'");
  $raw = json_encode($payload);
  mysqli_stmt_bind_param($update, 'ssi', $reference, $raw, $record['payment_id']);
  mysqli_stmt_execute($update);
  if (!empty($record['batch_id'])) {
    mysqli_query($db, "UPDATE payment_batches SET status='successful', updated_at=NOW() WHERE batch_id=" . (int) $record['batch_id']);
    mysqli_query($db, "UPDATE order_list o INNER JOIN payment_batch_orders bo ON bo.order_id=o.or_id SET o.payment_status='paid', o.status=1, o.updated_at=NOW() WHERE bo.batch_id=" . (int) $record['batch_id']);
  } else {
    mysqli_query($db, "UPDATE order_list SET payment_status='paid', status=1, updated_at=NOW() WHERE or_id=" . (int) $record['order_id']);
  }
  send_payment_sms($record['phone'], 'Farmers Market: payment of UGX ' . number_format((float) $record['amount'], 2) . ' received for order #' . (int) $record['order_id'] . '.');
} elseif (in_array($providerStatus, ['failed', 'rejected'], true)) {
  mysqli_query($db, "UPDATE payment_transactions SET status='failed', provider_response='" . mysqli_real_escape_string($db, json_encode($payload)) . "', updated_at=NOW() WHERE payment_id=" . (int) $record['payment_id']);
  if (!empty($record['batch_id'])) {
    mysqli_query($db, "UPDATE payment_batches SET status='failed', updated_at=NOW() WHERE batch_id=" . (int) $record['batch_id']);
    mysqli_query($db, "UPDATE order_list o INNER JOIN payment_batch_orders bo ON bo.order_id=o.or_id SET o.payment_status='failed', o.updated_at=NOW() WHERE bo.batch_id=" . (int) $record['batch_id']);
  } else {
    mysqli_query($db, "UPDATE order_list SET payment_status='failed', updated_at=NOW() WHERE or_id=" . (int) $record['order_id']);
  }
}
header('Content-Type: application/json'); echo json_encode(['received' => true]);
