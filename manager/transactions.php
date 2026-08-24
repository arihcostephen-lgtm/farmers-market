<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$transactionSummary = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS order_count, COALESCE(SUM(quantity), 0) AS total_quantity, COALESCE(SUM(price), 0) AS subtotal_total, COALESCE(SUM(tax_amount), 0) AS tax_total FROM order_list"));
$transactionSummary['revenue_total'] = ((float) ($transactionSummary['subtotal_total'] ?? 0)) + ((float) ($transactionSummary['tax_total'] ?? 0));
$orders = mysqli_query($db, "SELECT o.*, u.user_name FROM order_list o LEFT JOIN users u ON u.user_id = o.user_id ORDER BY o.join_date DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">System operations</div>
    <h2 class="mb-0 mt-2">Monitor transactions across the market</h2>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card p-4 h-100">
            <div class="small text-uppercase text-muted">Orders</div>
            <h3 class="mt-2 mb-0"><?php echo number_format((int) ($transactionSummary['order_count'] ?? 0)); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100">
            <div class="small text-uppercase text-muted">Items sold</div>
            <h3 class="mt-2 mb-0"><?php echo number_format((int) ($transactionSummary['total_quantity'] ?? 0)); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100">
            <div class="small text-uppercase text-muted">Subtotal</div>
            <h3 class="mt-2 mb-0">UGX <?php echo number_format((float) ($transactionSummary['subtotal_total'] ?? 0), 2); ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 h-100">
            <div class="small text-uppercase text-muted">Revenue</div>
            <h3 class="mt-2 mb-0">UGX <?php echo number_format((float) ($transactionSummary['revenue_total'] ?? 0), 2); ?></h3>
        </div>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <?php $orderTotal = ((float) ($order['price'] ?? 0)) + ((float) ($order['tax_amount'] ?? 0)); ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['or_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name'] ?: $order['user_phone'] ?: 'Guest'); ?></td>
                        <td><?php echo (int) $order['quantity']; ?></td>
                        <td>UGX <?php echo number_format((float) $order['price'], 2); ?></td>
                        <td>UGX <?php echo number_format((float) $order['tax_amount'], 2); ?></td>
                        <td>UGX <?php echo number_format($orderTotal, 2); ?></td>
                        <td>
                            <?php if ((int) $order['status'] === 1): ?><span class="badge bg-success">Confirmed</span><?php elseif ((int) $order['status'] === 2): ?><span class="badge bg-danger">Rejected</span><?php elseif ((int) $order['status'] === 5): ?><span class="badge bg-secondary">Delivered</span><?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <tr class="table-secondary fw-bold">
                    <td colspan="2">Total</td>
                    <td><?php echo number_format((int) ($transactionSummary['total_quantity'] ?? 0)); ?></td>
                    <td>UGX <?php echo number_format((float) ($transactionSummary['subtotal_total'] ?? 0), 2); ?></td>
                    <td>UGX <?php echo number_format((float) ($transactionSummary['tax_total'] ?? 0), 2); ?></td>
                    <td>UGX <?php echo number_format((float) ($transactionSummary['revenue_total'] ?? 0), 2); ?></td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
