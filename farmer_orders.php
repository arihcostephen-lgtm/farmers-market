<?php
session_start();
ob_start();
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/inc/language.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}
$farmerEmail = mysqli_real_escape_string($db, $_SESSION['email']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderId = (int) $_POST['order_id'];
    $status = max(0, min(3, (int) $_POST['status']));
    $deliveryUpdate = mysqli_real_escape_string($db, trim($_POST['delivery_update'] ?? ''));
    mysqli_query($db, "UPDATE order_list o INNER JOIN products p ON p.product_id=o.or_category SET o.status='$status', o.delivery_update='$deliveryUpdate', o.updated_at=NOW() WHERE o.or_id='$orderId' AND p.seller_email='$farmerEmail'");
}
$orderQuery = mysqli_query($db, "SELECT o.*, p.product_name FROM order_list o INNER JOIN products p ON p.product_id=o.or_category WHERE p.seller_email='$farmerEmail' ORDER BY o.or_id DESC");
$statuses = ['Pending', 'Confirmed', 'Fulfilled', 'Cancelled'];
$classes = ['warning', 'info', 'success', 'danger'];
?>
<!doctype html>
<html lang="<?php echo $currentLanguage === 'lg' ? 'lg' : 'en'; ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Farmer Orders | Farmers Market</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-5"><div class="card shadow-sm"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0">Farmer Orders</h3><a href="farmerDashboard.php?do=Home" class="btn btn-outline-secondary">Back to Dashboard</a></div>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Product</th><th>Buyer</th><th>Quantity</th><th>Total</th><th>Delivery</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php if ($orderQuery && mysqli_num_rows($orderQuery) > 0) { while ($order = mysqli_fetch_assoc($orderQuery)) { $orderStatus = (int) $order['status']; ?>
<tr><td><?php echo htmlspecialchars($order['product_name'] ?: $order['or_name']); ?></td><td><?php echo htmlspecialchars($order['user_id']); ?><br><?php echo htmlspecialchars($order['user_phone']); ?></td><td><?php echo number_format((int) ($order['quantity'] ?? 1)); ?> <?php echo htmlspecialchars($order['order_unit'] ?? 'kilogram'); ?></td><td>UGX <?php echo number_format((float) ($order['price'] ?? 0), 2); ?></td><td><?php echo nl2br(htmlspecialchars($order['delivery_location'] ?? 'Not provided')); ?><?php if (!empty($order['delivery_notes'])) { ?><br><small><?php echo nl2br(htmlspecialchars($order['delivery_notes'])); ?></small><?php } ?></td><td><span class="badge text-bg-<?php echo $classes[$orderStatus] ?? 'secondary'; ?>"><?php echo $statuses[$orderStatus] ?? 'Pending'; ?></span><br><small><?php echo nl2br(htmlspecialchars($order['delivery_update'] ?? '')); ?></small></td><td><form method="post" class="d-flex flex-column gap-2" style="min-width:180px"><input type="hidden" name="order_id" value="<?php echo (int) $order['or_id']; ?>"><select name="status" class="form-select form-select-sm"><?php foreach ($statuses as $statusIndex => $statusLabel) { ?><option value="<?php echo $statusIndex; ?>" <?php echo $statusIndex === $orderStatus ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option><?php } ?></select><textarea name="delivery_update" class="form-control form-control-sm" rows="2" placeholder="Delivery update"><?php echo htmlspecialchars($order['delivery_update'] ?? ''); ?></textarea><button name="update_order" class="btn btn-sm btn-success">Save Update</button></form></td></tr>
<?php } } else { ?><tr><td colspan="7" class="text-center text-muted">No orders yet.</td></tr><?php } ?></tbody></table></div>
</div></div></div></body></html><?php ob_end_flush(); ?>
