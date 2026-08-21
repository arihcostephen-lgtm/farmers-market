<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$systemSummary = [
    'customers' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM users WHERE role = 3"))['total'],
    'farmers' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM users WHERE role = 2"))['total'],
    'products' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM products WHERE status = 1"))['total'],
    'orders' => (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM order_list"))['total'],
    'taxes' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(tax_amount),0) AS total FROM order_list"))['total'],
    'revenue' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(total_amount),0) AS total FROM order_list"))['total'],
    'staff' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(salary),0) AS total FROM staff_payroll WHERE status = 1"))['total'],
    'costs' => (float) mysqli_fetch_assoc(mysqli_query($db, "SELECT COALESCE(SUM(amount),0) AS total FROM extra_costs"))['total'],
];
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Reports</div>
    <h2 class="mb-0 mt-2">System-wide performance overview</h2>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($systemSummary as $key => $value): ?>
        <div class="col-md-3">
            <div class="card metric p-4 h-100">
                <div class="small text-uppercase text-muted"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></div>
                <h3 class="mt-2 mb-0"><?php echo $key === 'taxes' || $key === 'revenue' || $key === 'staff' || $key === 'costs' ? 'UGX ' . number_format((float) $value, 2) : number_format((int) $value); ?></h3>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card p-4">
    <h5 class="mb-3">Operational notes</h5>
    <ul class="mb-0">
        <li>Managers and supervisors can create and disable staff accounts.</li>
        <li>Farmer approvals and subscriptions are managed centrally from the manager dashboard.</li>
        <li>Quantity-based tax rules are applied automatically during sale checkout.</li>
        <li>Payroll and other extra operating costs are tracked and summarized in reports.</li>
    </ul>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
