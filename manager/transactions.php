<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$orders = mysqli_query($db, "SELECT o.*, u.user_name FROM order_list o LEFT JOIN users u ON u.user_id = o.user_id ORDER BY o.join_date DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">System operations</div>
    <h2 class="mb-0 mt-2">Monitor transactions across the market</h2>
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
                    <tr>
                        <td><?php echo htmlspecialchars($order['or_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name'] ?: $order['user_phone'] ?: 'Guest'); ?></td>
                        <td><?php echo (int) $order['quantity']; ?></td>
                        <td>UGX <?php echo number_format((float) $order['price'], 2); ?></td>
                        <td>UGX <?php echo number_format((float) $order['tax_amount'], 2); ?></td>
                        <td>UGX <?php echo number_format((float) $order['total_amount'], 2); ?></td>
                        <td>
                            <?php if ((int) $order['status'] === 1): ?><span class="badge bg-success">Confirmed</span><?php elseif ((int) $order['status'] === 2): ?><span class="badge bg-danger">Rejected</span><?php elseif ((int) $order['status'] === 5): ?><span class="badge bg-secondary">Delivered</span><?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
