<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
if ($managerRole !== 4) {
    header('Location: dashboard.php');
    exit;
}

$units = ['all', 'kilogram', 'litre', 'gram', 'piece', 'each'];
$ruleId = (int) ($_POST['rule_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ruleName = trim($_POST['rule_name'] ?? '');
    $ratePercent = (float) ($_POST['rate_percent'] ?? -1);
    $minQty = (int) ($_POST['min_quantity'] ?? -1);
    $maxQty = trim($_POST['max_quantity'] ?? '') === '' ? null : (int) $_POST['max_quantity'];
    $appliesTo = (int) ($_POST['applies_to'] ?? 0) > 0 ? (string) (int) $_POST['applies_to'] : 'all';
    $appliesUnit = in_array($_POST['applies_unit'] ?? 'all', $units, true) ? $_POST['applies_unit'] : 'all';

    if (in_array($action, ['create', 'update'], true) && ($ruleName === '' || $ratePercent < 0 || $ratePercent > 100 || $minQty < 0 || ($maxQty !== null && $maxQty < $minQty))) {
        $notice = 'Check the rule name, rate, and quantity range before saving.';
    } elseif ($action === 'create' || ($action === 'update' && $ruleId > 0)) {
        if ($action === 'create') {
            $statement = mysqli_prepare($db, 'INSERT INTO tax_rules (rule_name, rate_percent, min_quantity, max_quantity, applies_to, applies_unit, status) VALUES (?, ?, ?, ?, ?, ?, 1)');
            if ($statement) {
                mysqli_stmt_bind_param($statement, 'sdiiss', $ruleName, $ratePercent, $minQty, $maxQty, $appliesTo, $appliesUnit);
            }
        } else {
            $statement = mysqli_prepare($db, 'UPDATE tax_rules SET rule_name = ?, rate_percent = ?, min_quantity = ?, max_quantity = ?, applies_to = ?, applies_unit = ? WHERE rule_id = ?');
            if ($statement) {
                mysqli_stmt_bind_param($statement, 'sdiissi', $ruleName, $ratePercent, $minQty, $maxQty, $appliesTo, $appliesUnit, $ruleId);
            }
        }
        $saved = $statement && mysqli_stmt_execute($statement);
        $notice = $saved ? ($action === 'create' ? 'Tax rule saved successfully.' : 'Tax rule updated successfully.') : 'Unable to save tax rule: ' . mysqli_error($db);
        if ($statement) {
            mysqli_stmt_close($statement);
        }
    } elseif ($action === 'toggle' && $ruleId > 0) {
        $statement = mysqli_prepare($db, 'UPDATE tax_rules SET status = IF(status = 1, 0, 1) WHERE rule_id = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'i', $ruleId);
        }
        $changed = $statement && mysqli_stmt_execute($statement);
        $notice = $changed ? 'Tax rule status updated.' : 'Unable to update tax rule: ' . mysqli_error($db);
        if ($statement) {
            mysqli_stmt_close($statement);
        }
    } elseif ($action === 'delete' && $ruleId > 0) {
        $statement = mysqli_prepare($db, 'DELETE FROM tax_rules WHERE rule_id = ?');
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'i', $ruleId);
        }
        $deleted = $statement && mysqli_stmt_execute($statement);
        $notice = $deleted ? 'Tax rule deleted.' : 'Unable to delete tax rule: ' . mysqli_error($db);
        if ($statement) {
            mysqli_stmt_close($statement);
        }
    }
}

$categories = mysqli_query($db, "SELECT cat_id, cat_name FROM category WHERE status = 1 ORDER BY cat_name");
$taxRules = mysqli_query($db, "SELECT tr.*, c.cat_name FROM tax_rules tr LEFT JOIN category c ON tr.applies_to = CAST(c.cat_id AS CHAR) ORDER BY tr.created_at DESC, tr.rule_id DESC");
$editingRule = null;
if (isset($_GET['edit_id'])) {
    $editId = (int) $_GET['edit_id'];
    $editQuery = mysqli_query($db, "SELECT * FROM tax_rules WHERE rule_id = $editId LIMIT 1");
    $editingRule = $editQuery ? mysqli_fetch_assoc($editQuery) : null;
}
if (!$categories || !$taxRules) {
    $notice = 'Tax rules could not be loaded: ' . mysqli_error($db);
}
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Taxation</div>
    <h2 class="mb-0 mt-2">Apply tax rules by quantity</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-<?php echo strpos($notice, 'Unable') === 0 || strpos($notice, 'could not') !== false || strpos($notice, 'Check') === 0 ? 'warning' : 'success'; ?>"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-3"><?php echo $editingRule ? 'Edit tax rule' : 'Add tax rule'; ?></h5>
    <form method="post">
        <input type="hidden" name="action" value="<?php echo $editingRule ? 'update' : 'create'; ?>">
        <?php if ($editingRule): ?><input type="hidden" name="rule_id" value="<?php echo (int) $editingRule['rule_id']; ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-3"><input class="form-control" name="rule_name" placeholder="Rule name" value="<?php echo htmlspecialchars($editingRule['rule_name'] ?? ''); ?>" required></div>
            <div class="col-md-2"><input class="form-control" name="rate_percent" type="number" step="0.01" min="0" max="100" placeholder="%" value="<?php echo htmlspecialchars($editingRule['rate_percent'] ?? ''); ?>" required></div>
            <div class="col-md-2"><input class="form-control" name="min_quantity" type="number" min="0" value="<?php echo (int) ($editingRule['min_quantity'] ?? 0); ?>" required></div>
            <div class="col-md-2"><input class="form-control" name="max_quantity" type="number" min="0" placeholder="Max qty" value="<?php echo htmlspecialchars($editingRule['max_quantity'] ?? ''); ?>"></div>
            <div class="col-md-2"><select class="form-select" name="applies_to"><option value="all">All categories</option><?php if ($categories): while ($category = mysqli_fetch_assoc($categories)): ?><option value="<?php echo (int) $category['cat_id']; ?>" <?php echo isset($editingRule['applies_to']) && (string) $editingRule['applies_to'] === (string) $category['cat_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['cat_name']); ?></option><?php endwhile; endif; ?></select></div>
            <div class="col-md-2"><select class="form-select" name="applies_unit"><?php foreach ($units as $unit): ?><option value="<?php echo htmlspecialchars($unit); ?>" <?php echo ($editingRule['applies_unit'] ?? 'all') === $unit ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit === 'all' ? 'All units' : ucfirst($unit)); ?></option><?php endforeach; ?></select></div>
            <div class="col-md-1"><button type="submit" class="btn btn-success w-100"><?php echo $editingRule ? 'Update' : 'Save'; ?></button></div>
            <?php if ($editingRule): ?><div class="col-12"><a href="taxes.php" class="btn btn-sm btn-link px-0">Cancel editing</a></div><?php endif; ?>
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
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($taxRules && mysqli_num_rows($taxRules) > 0): while ($rule = mysqli_fetch_assoc($taxRules)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rule['rule_name']); ?></td>
                        <td><?php echo number_format((float) $rule['rate_percent'], 2); ?>%</td>
                        <td><?php echo (int) $rule['min_quantity']; ?></td>
                        <td><?php echo $rule['max_quantity'] ? (int) $rule['max_quantity'] : 'Unlimited'; ?></td>
                        <td><?php echo htmlspecialchars($rule['cat_name'] ?: ($rule['applies_to'] === 'all' ? 'All categories' : $rule['applies_to'])); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($rule['applies_unit'] ?? 'all')); ?></td>
                        <td><span class="badge bg-<?php echo (int) $rule['status'] === 1 ? 'success' : 'secondary'; ?>"><?php echo (int) $rule['status'] === 1 ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <a href="taxes.php?edit_id=<?php echo (int) $rule['rule_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="post" class="d-inline-flex gap-1">
                                <input type="hidden" name="rule_id" value="<?php echo (int) $rule['rule_id']; ?>">
                                <input type="hidden" name="action" value="toggle">
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><?php echo (int) $rule['status'] === 1 ? 'Disable' : 'Enable'; ?></button>
                            </form>
                            <form method="post" class="d-inline-flex" onsubmit="return confirm('Delete this tax rule?');">
                                <input type="hidden" name="rule_id" value="<?php echo (int) $rule['rule_id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; else: ?><tr><td colspan="8" class="text-center text-muted py-4">No tax rules have been added yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
