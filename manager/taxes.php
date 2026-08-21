<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tax'])) {
    $ruleName = mysqli_real_escape_string($db, trim($_POST['rule_name']));
    $ratePercent = (float) $_POST['rate_percent'];
    $minQty = (int) $_POST['min_quantity'];
    $maxQty = !empty($_POST['max_quantity']) ? (int) $_POST['max_quantity'] : null;
    $appliesTo = mysqli_real_escape_string($db, trim($_POST['applies_to']));

    if ($ruleName !== '') {
        $insert = mysqli_query($db, "INSERT INTO tax_rules (rule_name, rate_percent, min_quantity, max_quantity, applies_to, status, created_at) VALUES ('$ruleName', '$ratePercent', '$minQty', " . ($maxQty === null ? 'NULL' : "'$maxQty'") . ", '$appliesTo', 1, NOW())");
        $notice = $insert ? 'Tax rule saved successfully.' : 'Unable to save tax rule.';
    }
}

$taxRules = mysqli_query($db, "SELECT * FROM tax_rules ORDER BY created_at DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Taxation</div>
    <h2 class="mb-0 mt-2">Apply tax rules by quantity</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-3">Add tax rule</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3"><input class="form-control" name="rule_name" placeholder="Rule name" required></div>
            <div class="col-md-2"><input class="form-control" name="rate_percent" type="number" step="0.01" min="0" max="100" placeholder="%" required></div>
            <div class="col-md-2"><input class="form-control" name="min_quantity" type="number" min="0" value="0" required></div>
            <div class="col-md-2"><input class="form-control" name="max_quantity" type="number" min="0" placeholder="Max qty"></div>
            <div class="col-md-2"><input class="form-control" name="applies_to" placeholder="Category/product" value="all"></div>
            <div class="col-md-1"><button type="submit" name="save_tax" class="btn btn-success w-100">Save</button></div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Rule</th>
                    <th>Rate</th>
                    <th>Min Qty</th>
                    <th>Max Qty</th>
                    <th>Applies To</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($rule = mysqli_fetch_assoc($taxRules)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rule['rule_name']); ?></td>
                        <td><?php echo number_format((float) $rule['rate_percent'], 2); ?>%</td>
                        <td><?php echo (int) $rule['min_quantity']; ?></td>
                        <td><?php echo $rule['max_quantity'] ? (int) $rule['max_quantity'] : 'Unlimited'; ?></td>
                        <td><?php echo htmlspecialchars($rule['applies_to']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
