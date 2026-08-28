<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
$error = '';
$managerId = (int) $_SESSION['user_id'];
$formFarmId = 0;
$formVisitDate = '';
$formVisitStatus = 'Scheduled';
$formVisitNotes = '';

if ($managerRole === 5) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_visit'])) {
        $farmId = (int) ($_POST['farm_id'] ?? 0);
        $formFarmId = $farmId;
        $formVisitDate = trim($_POST['visit_date'] ?? '');
        $formVisitStatus = in_array($_POST['visit_status'] ?? '', ['Scheduled', 'Completed', 'Cancelled'], true) ? $_POST['visit_status'] : 'Scheduled';
        $formVisitNotes = trim($_POST['visit_notes'] ?? '');
        $validDate = DateTime::createFromFormat('Y-m-d', $_POST['visit_date'] ?? '');
        if ($farmId < 1 || !$validDate || $validDate->format('Y-m-d') !== $formVisitDate || $formVisitDate < date('Y-m-d')) {
            $error = 'Select a valid farm and visit date.';
        } else {
            $farmCheck = mysqli_prepare($db, 'SELECT farm_id FROM farmer WHERE farm_id = ? AND status = 1 LIMIT 1');
            $farmIsActive = false;
            if ($farmCheck) {
                mysqli_stmt_bind_param($farmCheck, 'i', $farmId);
                mysqli_stmt_execute($farmCheck);
                mysqli_stmt_store_result($farmCheck);
                $farmIsActive = mysqli_stmt_num_rows($farmCheck) > 0;
                mysqli_stmt_close($farmCheck);
            }
            if (!$farmIsActive) {
                $visit = false;
                $visitRows = 0;
                $visitError = 'The selected farm is not active or could not be found.';
            } else {
                $visitStatement = mysqli_prepare($db, 'INSERT INTO farm_visits (farm_id, supervisor_id, visit_date, status, notes) VALUES (?, ?, ?, ?, ?)');
                if ($visitStatement) {
                    mysqli_stmt_bind_param($visitStatement, 'iisss', $farmId, $managerId, $formVisitDate, $formVisitStatus, $formVisitNotes);
                    $visit = mysqli_stmt_execute($visitStatement);
                    $visitError = mysqli_stmt_error($visitStatement);
                    $visitRows = mysqli_stmt_affected_rows($visitStatement);
                    mysqli_stmt_close($visitStatement);
                } else {
                    $visit = false;
                    $visitRows = 0;
                    $visitError = mysqli_error($db);
                }
            }
            if ($visit && $visitRows > 0) {
                $notice = 'Farm visit saved successfully.';
                $formFarmId = 0;
                $formVisitDate = '';
                $formVisitStatus = 'Scheduled';
                $formVisitNotes = '';
            } else {
                $error = 'Unable to save the farm visit: ' . $visitError;
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_visit'])) {
        $visitId = (int) ($_POST['visit_id'] ?? 0);
        $visitStatus = in_array($_POST['visit_status'] ?? '', ['Scheduled', 'Completed', 'Cancelled'], true) ? $_POST['visit_status'] : '';
        $visitNotes = trim($_POST['visit_notes'] ?? '');
        if ($visitId < 1 || $visitStatus === '') {
            $error = 'Choose a valid visit status.';
        } else {
            $updateStatement = mysqli_prepare($db, 'UPDATE farm_visits SET status = ?, notes = ?, updated_at = NOW() WHERE visit_id = ? AND supervisor_id = ?');
            if ($updateStatement) {
                mysqli_stmt_bind_param($updateStatement, 'ssii', $visitStatus, $visitNotes, $visitId, $managerId);
                $update = mysqli_stmt_execute($updateStatement);
                $updateError = mysqli_stmt_error($updateStatement);
                mysqli_stmt_close($updateStatement);
            } else {
                $update = false;
                $updateError = mysqli_error($db);
            }
            if ($update) {
                $notice = 'Farm visit updated successfully.';
            } else {
                $error = 'Unable to update this farm visit: ' . $updateError;
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_visit'])) {
        $visitId = (int) ($_POST['visit_id'] ?? 0);
        $deleteStatement = mysqli_prepare($db, 'DELETE FROM farm_visits WHERE visit_id = ? AND supervisor_id = ?');
        if ($deleteStatement) {
            mysqli_stmt_bind_param($deleteStatement, 'ii', $visitId, $managerId);
            $delete = mysqli_stmt_execute($deleteStatement);
            $deleteRows = mysqli_stmt_affected_rows($deleteStatement);
            mysqli_stmt_close($deleteStatement);
        } else {
            $delete = false;
            $deleteRows = 0;
        }
        if ($delete && $deleteRows > 0) {
            $notice = 'Farm visit removed.';
        } else {
            $error = 'Unable to remove this farm visit.';
        }
    }
    $farmDirectory = mysqli_query($db, "SELECT f.farm_id, f.farm_name, f.farm_email, f.farm_phone, f.farm_address, f.farm_document, u.user_name AS owner_name FROM farmer f LEFT JOIN users u ON u.user_email=f.farm_email AND u.role=2 AND u.status=1 WHERE f.status=1 ORDER BY f.farm_name ASC");
    $visitHistory = mysqli_query($db, "SELECT v.visit_id, v.farm_id, v.supervisor_id, v.visit_date, v.status, v.notes, v.created_at, v.updated_at, CONCAT(COALESCE(f.farm_name, CONCAT('Farm #', v.farm_id)), ' - ', COALESCE(u.user_name, f.farm_email, 'Farmer not linked')) AS farm_name, f.farm_phone, f.farm_address FROM farm_visits v LEFT JOIN farmer f ON f.farm_id=v.farm_id LEFT JOIN users u ON u.user_email=f.farm_email AND u.role=2 AND u.status=1 WHERE v.supervisor_id='$managerId' ORDER BY (v.status = 'Scheduled') DESC, (v.visit_date >= CURDATE()) DESC, v.visit_date ASC, v.visit_id DESC");
    if (!$farmDirectory || !$visitHistory) {
        $error = 'Farm visits could not be loaded: ' . mysqli_error($db);
    }
    $visitStatsQuery = mysqli_query($db, "SELECT COUNT(*) AS total_visits, SUM(status = 'Scheduled') AS scheduled_visits, SUM(status = 'Completed') AS completed_visits, SUM(status = 'Cancelled') AS cancelled_visits FROM farm_visits WHERE supervisor_id='$managerId'");
    $visitStats = $visitStatsQuery ? mysqli_fetch_assoc($visitStatsQuery) : ['total_visits' => 0, 'scheduled_visits' => 0, 'completed_visits' => 0, 'cancelled_visits' => 0];
    ?>
    <div class="page-header">
        <div class="text-uppercase small fw-semibold opacity-75">Field operations</div>
        <h2 class="mb-0 mt-2">Farm visits</h2>
    </div>
    <?php if ($notice !== ''): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card p-4">
                <h5 class="mb-1"><i class="fa-solid fa-calendar-plus text-success me-2"></i>Schedule a visit</h5>
                <p class="text-muted mb-3">Choose a farmer and record the visit plan.</p>
                <form method="post">
                    <div class="mb-3"><label class="form-label" for="farmId">Farm</label><select class="form-select" name="farm_id" id="farmId" required><option value="">Select a farm</option><?php if ($farmDirectory): while ($farm = mysqli_fetch_assoc($farmDirectory)): ?><option value="<?php echo (int) $farm['farm_id']; ?>" <?php echo $formFarmId === (int) $farm['farm_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($farm['farm_name']); ?><?php echo $farm['farm_phone'] ? ' - ' . htmlspecialchars($farm['farm_phone']) : ''; ?></option><?php endwhile; endif; ?></select></div>
                    <div class="mb-3"><label class="form-label" for="visitDate">Visit date</label><input class="form-control" type="date" name="visit_date" id="visitDate" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($formVisitDate); ?>" required></div>
                    <div class="mb-3"><label class="form-label" for="visitStatus">Status</label><select class="form-select" name="visit_status" id="visitStatus"><option value="Scheduled" <?php echo $formVisitStatus === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option><option value="Completed" <?php echo $formVisitStatus === 'Completed' ? 'selected' : ''; ?>>Completed</option><option value="Cancelled" <?php echo $formVisitStatus === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option></select></div>
                    <div class="mb-3"><label class="form-label" for="visitNotes">Notes</label><textarea class="form-control" name="visit_notes" id="visitNotes" rows="4" placeholder="Add objectives, findings, or follow-up actions"><?php echo htmlspecialchars($formVisitNotes); ?></textarea></div>
                    <button type="submit" name="save_visit" class="btn btn-success"><i class="fa-solid fa-calendar-check me-2"></i>Save Visit</button>
                </form>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card p-4 h-100">
                <h5 class="mb-1"><i class="fa-solid fa-map-location-dot text-success me-2"></i>Farmer directory</h5>
                <p class="text-muted mb-3">Active farms available for field visits.</p>
                <div class="table-responsive"><table class="table table-hover data-table"><thead><tr><th>Farm name</th><th>Farmer contact</th><th>Location</th><th>Document</th></tr></thead><tbody><?php if ($farmDirectory && mysqli_num_rows($farmDirectory) > 0): mysqli_data_seek($farmDirectory, 0); while ($farm = mysqli_fetch_assoc($farmDirectory)): ?><tr><td><strong><?php echo htmlspecialchars($farm['farm_name']); ?></strong></td><td><?php echo htmlspecialchars($farm['farm_phone'] ?: 'Phone not recorded'); ?><small class="d-block text-muted"><?php echo htmlspecialchars($farm['farm_email'] ?: 'Email not recorded'); ?></small></td><td><?php echo htmlspecialchars($farm['farm_address'] ?: 'Address not recorded'); ?></td><td><?php if (!empty($farm['farm_document'])): ?><a class="btn btn-sm btn-outline-primary" href="../<?php echo htmlspecialchars($farm['farm_document']); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye me-1"></i>View</a><?php else: ?><span class="text-muted">Not provided</span><?php endif; ?></td></tr><?php endwhile; else: ?><tr><td colspan="4" class="text-center text-muted">No active farms found.</td></tr><?php endif; ?></tbody></table></div>
            </div>
        </div>
    </div>
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="mb-1"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>My planned visits and history</h5><small class="text-muted"><?php echo (int) $visitStats['scheduled_visits']; ?> scheduled · <?php echo (int) $visitStats['completed_visits']; ?> completed · <?php echo (int) $visitStats['cancelled_visits']; ?> cancelled</small></div><span class="badge bg-success"><?php echo (int) $visitStats['total_visits']; ?> total</span></div>
        <div class="table-responsive"><table class="table table-hover data-table"><thead><tr><th>Farm</th><th>Date</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead><tbody><?php if ($visitHistory && mysqli_num_rows($visitHistory) > 0): while ($visit = mysqli_fetch_assoc($visitHistory)): ?><tr><td><strong><?php echo htmlspecialchars($visit['farm_name']); ?></strong><small class="d-block text-muted"><?php echo htmlspecialchars($visit['farm_address'] ?: 'Address not recorded'); ?></small></td><td><?php echo date('M j, Y', strtotime($visit['visit_date'])); ?></td><td><span class="badge bg-<?php echo $visit['status'] === 'Completed' ? 'success' : ($visit['status'] === 'Cancelled' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($visit['status']); ?></span></td><td><?php echo nl2br(htmlspecialchars($visit['notes'] ?: 'No notes recorded')); ?></td><td><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVisit<?php echo (int) $visit['visit_id']; ?>" title="Update visit"><i class="fa-solid fa-pen-to-square"></i></button><form method="post" onsubmit="return confirm('Remove this visit?');"><input type="hidden" name="visit_id" value="<?php echo (int) $visit['visit_id']; ?>"><button type="submit" name="delete_visit" class="btn btn-sm btn-outline-danger" title="Remove visit"><i class="fa-solid fa-trash"></i></button></form></div><div class="modal fade" id="editVisit<?php echo (int) $visit['visit_id']; ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Update farm visit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="post"><div class="modal-body"><input type="hidden" name="visit_id" value="<?php echo (int) $visit['visit_id']; ?>"><div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="visit_status" required><option value="Scheduled" <?php echo $visit['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option><option value="Completed" <?php echo $visit['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option><option value="Cancelled" <?php echo $visit['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option></select></div><div><label class="form-label">Notes and follow-up</label><textarea class="form-control" name="visit_notes" rows="4"><?php echo htmlspecialchars($visit['notes'] ?? ''); ?></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_visit" class="btn btn-success btn-sm">Save update</button></div></form></div></div></div></td></tr><?php endwhile; else: ?><tr><td colspan="5" class="text-center text-muted py-4">No visits scheduled yet.</td></tr><?php endif; ?></tbody></table></div>
    </div>
    <?php include __DIR__ . '/inc/footer.php'; exit;
}

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
        $planStatement = mysqli_prepare($db, 'UPDATE subscription_plans SET plan_name = ?, description = ?, amount = ?, duration_days = ?, status = ?, updated_at = NOW() WHERE plan_id = ?');
        if ($planStatement) {
            mysqli_stmt_bind_param($planStatement, 'ssdiii', $planName, $description, $amount, $durationDays, $status, $planId);
            $savedPlan = mysqli_stmt_execute($planStatement);
            $planError = mysqli_stmt_error($planStatement);
            mysqli_stmt_close($planStatement);
        } else {
            $savedPlan = false;
            $planError = mysqli_error($db);
        }
        $notice = $savedPlan ? 'Subscription plan updated.' : 'Unable to update subscription plan: ' . $planError;
    } else {
        $planStatement = mysqli_prepare($db, 'INSERT INTO subscription_plans (plan_name, description, amount, duration_days, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        if ($planStatement) {
            mysqli_stmt_bind_param($planStatement, 'ssdiii', $planName, $description, $amount, $durationDays, $status, $managerId);
            $savedPlan = mysqli_stmt_execute($planStatement);
            $planError = mysqli_stmt_error($planStatement);
            mysqli_stmt_close($planStatement);
        } else {
            $savedPlan = false;
            $planError = mysqli_error($db);
        }
        $notice = $savedPlan ? 'Subscription plan created.' : 'Unable to create subscription plan: ' . $planError;
    }
}
if (isset($_GET['deactivate_plan']) && !empty($_GET['plan_id'])) {
    $planId = (int) $_GET['plan_id'];
    $planStatement = mysqli_prepare($db, 'UPDATE subscription_plans SET status = 0, updated_at = NOW() WHERE plan_id = ?');
    if ($planStatement) {
        mysqli_stmt_bind_param($planStatement, 'i', $planId);
        $deactivated = mysqli_stmt_execute($planStatement);
        $planError = mysqli_stmt_error($planStatement);
        mysqli_stmt_close($planStatement);
    } else {
        $deactivated = false;
        $planError = mysqli_error($db);
    }
    $notice = $deactivated ? 'Subscription plan deactivated.' : 'Unable to deactivate subscription plan: ' . $planError;
}
if (isset($_GET['approve']) && !empty($_GET['id'])) {
    $farmerId = (int) $_GET['id'];
    $pendingSubscription = mysqli_query($db, "SELECT id FROM farmer_subscriptions WHERE farmer_id=$farmerId AND status=0 LIMIT 1");
    if ($pendingSubscription && mysqli_num_rows($pendingSubscription) > 0) {
        mysqli_begin_transaction($db);
        $update = mysqli_query($db, "UPDATE users SET status = 1 WHERE role = 2 AND user_id = $farmerId");
        $subscriptionUpdate = $update && mysqli_query($db, "UPDATE farmer_subscriptions SET status=1, approved_by=$managerId, approved_at=NOW() WHERE farmer_id=$farmerId");
        if ($subscriptionUpdate) {
            mysqli_commit($db);
            $notice = 'Farmer approved and subscription activated.';
        } else {
            mysqli_rollback($db);
            $error = 'Farmer approval could not be saved: ' . mysqli_error($db);
        }
    } else {
        $error = 'This farmer has not submitted a subscription payment request yet.';
    }
}
if (isset($_GET['reject']) && !empty($_GET['id'])) {
    $farmerId = (int) $_GET['id'];
    $rejected = mysqli_query($db, "UPDATE users SET status = 0 WHERE role = 2 AND user_id = $farmerId");
    $notice = $rejected ? 'Farmer application rejected.' : 'Unable to reject farmer application: ' . mysqli_error($db);
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
