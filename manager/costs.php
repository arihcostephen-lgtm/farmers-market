<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost'])) {
    $name = mysqli_real_escape_string($db, trim($_POST['cost_name']));
    $amount = (float) $_POST['amount'];
    $notes = mysqli_real_escape_string($db, trim($_POST['notes']));
    if ($name !== '') {
        $insert = mysqli_query($db, "INSERT INTO extra_costs (cost_name, amount, notes, created_by, created_at) VALUES ('$name', '$amount', '$notes', '{$_SESSION['user_id']}', NOW())");
        $notice = $insert ? 'Extra cost recorded.' : 'Unable to save cost.';
    }
}
$costs = mysqli_query($db, "SELECT * FROM extra_costs ORDER BY created_at DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Operations</div>
    <h2 class="mb-0 mt-2">Manage extra costs</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-3">Add extra cost</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="cost_name" placeholder="Cost name" required></div>
            <div class="col-md-3"><input class="form-control" name="amount" type="number" step="0.01" min="0" placeholder="Amount" required></div>
            <div class="col-md-5"><input class="form-control" name="notes" placeholder="Notes"></div>
            <div class="col-12"><button type="submit" name="add_cost" class="btn btn-success">Save cost</button></div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Cost</th>
                    <th>Amount</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cost = mysqli_fetch_assoc($costs)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cost['cost_name']); ?></td>
                        <td>UGX <?php echo number_format((float) $cost['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($cost['notes'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($cost['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
