<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
$error = '';
$managerId = (int) $_SESSION['user_id'];

if (isset($_POST['save_plan'])) {
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $planName = mysqli_real_escape_string($db, trim($_POST['plan_name'] ?? ''));
    $description = mysqli_real_escape_string($db, trim($_POST['description'] ?? ''));
    $amount = (float) ($_POST['amount'] ?? 0);
    $durationDays = max(1, (int) ($_POST['duration_days'] ?? 30));
    $status = isset($_POST['status']) ? 1 : 0;
    if ($planName === '' || $amount < 0) {
        $error = 'Enter a valid plan name and amount.';
    } elseif ($planId > 0) {
        mysqli_query($db, "UPDATE subscription_plans SET plan_name='$planName', description='$description', amount='$amount', duration_days=$durationDays, status=$status, updated_at=NOW() WHERE plan_id=$planId");
        $notice = 'Subscription plan updated.';
    } else {
        mysqli_query($db, "INSERT INTO subscription_plans (plan_name, description, amount, duration_days, status, created_by, created_at) VALUES ('$planName', '$description', '$amount', $durationDays, $status, $managerId, NOW())");
        $notice = 'Subscription plan created.';
    }
}
if (isset($_GET['deactivate_plan']) && !empty($_GET['plan_id'])) {
    $planId = (int) $_GET['plan_id'];
    mysqli_query($db, "UPDATE subscription_plans SET status=0, updated_at=NOW() WHERE plan_id=$planId");
    $notice = 'Subscription plan deactivated.';
}
if (isset($_GET['approve']) && !empty($_GET['id'])) {
    $farmerId = (int) $_GET['id'];
    $pendingSubscription = mysqli_query($db, "SELECT id FROM farmer_subscriptions WHERE farmer_id=$farmerId AND status=0 LIMIT 1");
    if ($pendingSubscription && mysqli_num_rows($pendingSubscription) > 0) {
        $update = mysqli_query($db, "UPDATE users SET status = 1 WHERE role = 2 AND user_id = $farmerId");
        mysqli_query($db, "UPDATE farmer_subscriptions SET status=1, approved_by=$managerId, approved_at=NOW() WHERE farmer_id=$farmerId");
        $notice = $update ? 'Farmer approved and subscription activated.' : 'Farmer could not be activated.';
    } else {
        $error = 'This farmer has not submitted a subscription payment request yet.';
    }
}
if (isset($_GET['reject']) && !empty($_GET['id'])) {
    $farmerId = (int) $_GET['id'];
    mysqli_query($db, "UPDATE users SET status = 0 WHERE role = 2 AND user_id = $farmerId");
    $notice = 'Farmer application rejected.';
}

$farmers = mysqli_query($db, "SELECT u.user_id, u.user_name, u.user_email, u.user_phone, u.status, fs.subscription_name, fs.amount, fs.status AS subscription_status FROM users u LEFT JOIN farmer_subscriptions fs ON fs.farmer_id = u.user_id WHERE u.role = 2 ORDER BY u.user_name ASC");
$plans = mysqli_query($db, "SELECT * FROM subscription_plans ORDER BY status DESC, amount ASC, plan_name ASC");
$editPlan = null;
if (isset($_GET['edit_plan']) && !empty($_GET['plan_id'])) {
    $editPlanQuery = mysqli_query($db, "SELECT * FROM subscription_plans WHERE plan_id=" . (int) $_GET['plan_id'] . " LIMIT 1");
    $editPlan = $editPlanQuery ? mysqli_fetch_assoc($editPlanQuery) : null;
}
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Farmer management</div>
    <h2 class="mb-0 mt-2">Subscriptions and approval workflow</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card p-4">
            <h5 class="mb-3"><?php echo $editPlan ? 'Edit subscription plan' : 'Create subscription plan'; ?></h5>
            <form method="post">
                <input type="hidden" name="plan_id" value="<?php echo (int) ($editPlan['plan_id'] ?? 0); ?>">
                <div class="mb-3"><label class="form-label">Plan name</label><input class="form-control" name="plan_name" required value="<?php echo htmlspecialchars($editPlan['plan_name'] ?? ''); ?>"></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($editPlan['description'] ?? ''); ?></textarea></div>
                <div class="row g-3">
                    <div class="col-7"><label class="form-label">Amount (UGX)</label><input class="form-control" type="number" min="0" step="0.01" name="amount" required value="<?php echo htmlspecialchars($editPlan['amount'] ?? '50000'); ?>"></div>
                    <div class="col-5"><label class="form-label">Days</label><input class="form-control" type="number" min="1" name="duration_days" required value="<?php echo htmlspecialchars($editPlan['duration_days'] ?? '30'); ?>"></div>
                </div>
                <div class="form-check my-3"><input class="form-check-input" type="checkbox" name="status" id="planStatus" <?php echo !$editPlan || (int) $editPlan['status'] === 1 ? 'checked' : ''; ?>><label class="form-check-label" for="planStatus">Available to farmers</label></div>
                <button class="btn btn-success" name="save_plan" type="submit"><?php echo $editPlan ? 'Update plan' : 'Create plan'; ?></button>
                <?php if ($editPlan): ?><a href="farmers.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card p-4">
            <h5 class="mb-3">Subscription plans</h5>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Plan</th><th>Price</th><th>Duration</th><th>Status</th><th></th></tr></thead><tbody>
            <?php if ($plans && mysqli_num_rows($plans) > 0): while ($plan = mysqli_fetch_assoc($plans)): ?><tr>
                <td><strong><?php echo htmlspecialchars($plan['plan_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($plan['description'] ?: 'No description'); ?></small></td>
                <td>UGX <?php echo number_format((float) $plan['amount'], 2); ?></td>
                <td><?php echo (int) $plan['duration_days']; ?> days</td>
                <td><?php echo (int) $plan['status'] === 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="farmers.php?edit_plan=1&plan_id=<?php echo (int) $plan['plan_id']; ?>">Edit</a><?php if ((int) $plan['status'] === 1): ?><a class="btn btn-sm btn-outline-danger" href="farmers.php?deactivate_plan=1&plan_id=<?php echo (int) $plan['plan_id']; ?>">Deactivate</a><?php endif; ?></td>
            </tr><?php endwhile; else: ?><tr><td colspan="5" class="text-center text-muted">No plans created yet.</td></tr><?php endif; ?>
            </tbody></table></div>
        </div>
    </div>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Farmer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subscription</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($farmer = mysqli_fetch_assoc($farmers)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($farmer['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($farmer['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($farmer['user_phone'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($farmer['subscription_name'] ?: 'No active plan'); ?><?php if (!empty($farmer['amount'])): ?> · UGX <?php echo number_format((float) $farmer['amount'], 2); ?><?php endif; ?></td>
                        <td>
                            <?php if ((int) $farmer['status'] === 1): ?><span class="badge bg-success">Approved</span><?php elseif ((int) $farmer['status'] === 0): ?><span class="badge bg-danger">Rejected</span><?php else: ?><span class="badge bg-warning text-dark">Pending</span><?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="farmers.php?approve=1&id=<?php echo (int) $farmer['user_id']; ?>" class="btn btn-success">Approve</a>
                                <a href="farmers.php?reject=1&id=<?php echo (int) $farmer['user_id']; ?>" class="btn btn-outline-danger">Reject</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
