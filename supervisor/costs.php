<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
$isSupervisor = $managerRole === 5;
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost'])) {
    $name = trim($_POST['cost_name'] ?? '');
    $amountInput = trim($_POST['amount'] ?? '');
    $amount = is_numeric($amountInput) ? (float) $amountInput : -1;
    $notes = trim($_POST['notes'] ?? '');
    if (!$isSupervisor && $currentUserId > 0 && $name !== '' && $amount >= 0) {
        $insertStatement = mysqli_prepare($db, 'INSERT INTO extra_costs (cost_name, amount, notes, created_by, created_at) VALUES (?, ?, ?, ?, NOW())');
        if ($insertStatement) {
            mysqli_stmt_bind_param($insertStatement, 'sdsi', $name, $amount, $notes, $currentUserId);
            $insert = mysqli_stmt_execute($insertStatement);
            $insertError = mysqli_stmt_error($insertStatement);
            mysqli_stmt_close($insertStatement);
        } else {
            $insert = false;
            $insertError = mysqli_error($db);
        }
        $notice = $insert ? 'Extra cost recorded.' : 'Unable to save cost: ' . $insertError;
    } else {
        $notice = !$isSupervisor ? 'Enter a valid cost name and amount.' : 'Only the manager can add direct extra costs.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_fund_request'])) {
    $name = trim($_POST['cost_name'] ?? '');
    $amountInput = trim($_POST['amount'] ?? '');
    $amount = is_numeric($amountInput) ? (float) $amountInput : -1;
    $reason = trim($_POST['reason'] ?? '');
    $requestedBy = $currentUserId;
    $requestedByName = $managerName;

    if ($isSupervisor && $requestedBy > 0 && $name !== '' && $reason !== '' && $amount > 0) {
        $insertStatement = mysqli_prepare($db, "INSERT INTO extra_cost_requests (requested_by, requested_by_name, cost_name, amount, reason, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if ($insertStatement) {
            mysqli_stmt_bind_param($insertStatement, 'issds', $requestedBy, $requestedByName, $name, $amount, $reason);
            $insert = mysqli_stmt_execute($insertStatement);
            $insertError = mysqli_stmt_error($insertStatement);
            mysqli_stmt_close($insertStatement);
        } else {
            $insert = false;
            $insertError = mysqli_error($db);
        }
        $notice = $insert ? 'Fund request submitted to the manager for approval.' : 'Unable to submit the fund request: ' . $insertError;
    } else {
        $notice = 'Provide a valid cost name, amount, and reason before submitting.';
    }
}

if (!$isSupervisor && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $decision = isset($_POST['approve_request']) ? 'approved' : 'rejected';
    $managerId = (int) ($_SESSION['user_id'] ?? 0);
    $request = mysqli_query($db, "SELECT request_id, requested_by, cost_name, amount, reason, status FROM extra_cost_requests WHERE request_id = '$requestId' LIMIT 1");

    if ($request && mysqli_num_rows($request) > 0) {
        $requestData = mysqli_fetch_assoc($request);
        mysqli_begin_transaction($db);
        $update = mysqli_query($db, "UPDATE extra_cost_requests SET status = 'approved', approved_by = '$managerId', approved_at = NOW() WHERE request_id = '$requestId' AND status = 'pending'");

        if ($update && mysqli_affected_rows($db) === 1 && (float) $requestData['amount'] > 0) {
            $approvedCost = mysqli_query($db, "INSERT INTO extra_costs (cost_name, amount, notes, created_by, created_at) VALUES ('" . mysqli_real_escape_string($db, $requestData['cost_name']) . "', '" . (float) $requestData['amount'] . "', '" . mysqli_real_escape_string($db, $requestData['reason']) . "', '$managerId', NOW())");
            if ($approvedCost) {
                mysqli_commit($db);
                $notice = 'Fund request approved and added to extra costs.';
            } else {
                mysqli_rollback($db);
                $notice = 'Unable to add the approved cost: ' . mysqli_error($db);
            }
        } elseif ($update && mysqli_affected_rows($db) === 1) {
            mysqli_commit($db);
            $notice = 'Fund request approved.';
        } else {
            mysqli_rollback($db);
            $notice = 'This request was already processed or could not be updated.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $managerId = (int) ($_SESSION['user_id'] ?? 0);
    $update = mysqli_query($db, "UPDATE extra_cost_requests SET status = 'rejected', approved_by = '$managerId', approved_at = NOW() WHERE request_id = '$requestId' AND status = 'pending'");
    $notice = $update && mysqli_affected_rows($db) === 1 ? 'Fund request rejected.' : 'Unable to reject this request or it was already processed.';
}

$costs = mysqli_query($db, "SELECT ec.cost_id, ec.cost_name, ec.amount, ec.notes, ec.created_by, ec.created_at, COALESCE(u.user_name, CONCAT('Account ', ec.created_by)) AS recorded_by_name FROM extra_costs ec LEFT JOIN users u ON u.user_id = ec.created_by AND u.role IN (1, 4, 5) ORDER BY ec.created_at DESC, ec.cost_id DESC");
$costRows = [];
if ($costs) {
    while ($cost = mysqli_fetch_assoc($costs)) {
        $costRows[] = $cost;
    }
} else {
    $notice = 'Extra costs could not be loaded: ' . mysqli_error($db);
}
$costTotalQuery = mysqli_query($db, "SELECT COALESCE(SUM(amount), 0) AS total FROM extra_costs");
$costTotal = $costTotalQuery ? (float) mysqli_fetch_assoc($costTotalQuery)['total'] : 0;
$costRequests = mysqli_query($db, $isSupervisor
    ? "SELECT r.request_id, r.requested_by, r.requested_by_name, r.cost_name, r.amount, r.reason, r.status, r.approved_by, r.approved_at, r.created_at, COALESCE(u.user_name, r.requested_by_name) AS requester_name, approver.user_name AS approver_name FROM extra_cost_requests r LEFT JOIN users u ON u.user_id = r.requested_by AND u.role = 5 LEFT JOIN users approver ON approver.user_id = r.approved_by WHERE r.requested_by = '$currentUserId' ORDER BY r.created_at DESC, r.request_id DESC"
    : "SELECT r.request_id, r.requested_by, r.requested_by_name, r.cost_name, r.amount, r.reason, r.status, r.approved_by, r.approved_at, r.created_at, COALESCE(u.user_name, r.requested_by_name) AS requester_name, approver.user_name AS approver_name FROM extra_cost_requests r LEFT JOIN users u ON u.user_id = r.requested_by AND u.role = 5 LEFT JOIN users approver ON approver.user_id = r.approved_by ORDER BY r.created_at DESC, r.request_id DESC");
if (!$costRequests) {
    $notice = 'Fund requests could not be loaded: ' . mysqli_error($db);
}
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Operations</div>
    <h2 class="mb-0 mt-2"><?php echo $isSupervisor ? 'Request extra funds' : 'Manage extra costs'; ?></h2>
</div>

<?php if ($notice !== ''): ?>
    <div class="alert alert-<?php echo strpos($notice, 'Unable') === 0 || strpos($notice, 'Provide') === 0 || strpos($notice, 'already') !== false || strpos($notice, 'could not') !== false || strpos($notice, 'already processed') !== false ? 'warning' : 'success'; ?>"><?php echo htmlspecialchars($notice); ?></div>
<?php endif; ?>

<?php if ($isSupervisor): ?>
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Request funds for an activity</h5>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4"><input class="form-control" name="cost_name" placeholder="Activity or cost name" required></div>
                <div class="col-md-3"><input class="form-control" name="amount" type="number" step="0.01" min="0.01" placeholder="Amount" required></div>
                <div class="col-md-5"><textarea class="form-control" name="reason" rows="3" placeholder="State the reason for the request" required></textarea></div>
                <div class="col-12"><button type="submit" name="submit_fund_request" class="btn btn-success">Submit request</button></div>
            </div>
        </form>
    </div>
<?php else: ?>
    <div class="card p-4 mb-4">
        <h5 class="mb-3">Add direct extra cost</h5>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4"><input class="form-control" name="cost_name" placeholder="Cost name" required></div>
                <div class="col-md-3"><input class="form-control" name="amount" type="number" step="0.01" min="0" placeholder="Amount" required></div>
                <div class="col-md-5"><input class="form-control" name="notes" placeholder="Notes"></div>
                <div class="col-12"><button type="submit" name="add_cost" class="btn btn-success">Save cost</button></div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (!$isSupervisor): ?>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Fund requests awaiting approval</h5>
            <span class="badge bg-warning text-dark">Manager review</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Supervisor</th>
                        <th>Activity</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($costRequests && mysqli_num_rows($costRequests) > 0): while ($request = mysqli_fetch_assoc($costRequests)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['requester_name'] ?: $request['requested_by_name']); ?></td>
                            <td><?php echo htmlspecialchars($request['cost_name']); ?></td>
                            <td>UGX <?php echo number_format((float) $request['amount'], 2); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($request['reason'])); ?></td>
                            <td><span class="badge bg-<?php echo $request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span></td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <div class="d-flex gap-2">
                                        <form method="post" class="d-inline-block">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $request['request_id']; ?>">
                                            <button type="submit" name="approve_request" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="post" class="d-inline-block">
                                            <input type="hidden" name="request_id" value="<?php echo (int) $request['request_id']; ?>">
                                            <button type="submit" name="reject_request" class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?><tr><td colspan="6" class="text-center text-muted py-4">No fund requests have been submitted yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">My fund requests</h5>
            <span class="badge bg-success">Supervisor view</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($costRequests && mysqli_num_rows($costRequests) > 0): while ($request = mysqli_fetch_assoc($costRequests)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['cost_name']); ?></td>
                            <td>UGX <?php echo number_format((float) $request['amount'], 2); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($request['reason'])); ?></td>
                            <td><span class="badge bg-<?php echo $request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars(ucfirst($request['status'])); ?></span></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                        </tr>
                    <?php endwhile; else: ?><tr><td colspan="5" class="text-center text-muted py-4">You have not submitted any fund requests yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><?php echo $isSupervisor ? 'Approved extra costs' : 'Extra costs ledger'; ?></h5>
        <div class="text-end"><span class="badge bg-primary"><?php echo count($costRows); ?> records</span><div class="small text-muted mt-1">Total: UGX <?php echo number_format($costTotal, 2); ?></div></div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Cost</th>
                    <th>Amount</th>
                    <th>Notes</th>
                    <th>Recorded by</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($costRows) > 0): foreach ($costRows as $cost): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cost['cost_name']); ?></td>
                        <td>UGX <?php echo number_format((float) $cost['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($cost['notes'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($cost['recorded_by_name'] ?: 'Account ' . (int) $cost['created_by']); ?></td>
                        <td><?php echo htmlspecialchars($cost['created_at']); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No extra costs have been recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
