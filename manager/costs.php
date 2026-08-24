<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
$isSupervisor = $managerRole === 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost'])) {
    $name = mysqli_real_escape_string($db, trim($_POST['cost_name'] ?? ''));
    $amount = (float) ($_POST['amount'] ?? 0);
    $notes = mysqli_real_escape_string($db, trim($_POST['notes'] ?? ''));
    if (!$isSupervisor && $name !== '' && $amount >= 0) {
        $insert = mysqli_query($db, "INSERT INTO extra_costs (cost_name, amount, notes, created_by, created_at) VALUES ('$name', '$amount', '$notes', '{$_SESSION['user_id']}', NOW())");
        $notice = $insert ? 'Extra cost recorded.' : 'Unable to save cost.';
    } else {
        $notice = 'Only the manager can add direct extra costs.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_fund_request'])) {
    $name = mysqli_real_escape_string($db, trim($_POST['cost_name'] ?? ''));
    $amount = (float) ($_POST['amount'] ?? 0);
    $reason = mysqli_real_escape_string($db, trim($_POST['reason'] ?? ''));
    $requestedBy = (int) ($_SESSION['user_id'] ?? 0);
    $requestedByName = mysqli_real_escape_string($db, $managerName);

    if ($isSupervisor && $name !== '' && $reason !== '' && $amount > 0) {
        $insert = mysqli_query($db, "INSERT INTO extra_cost_requests (requested_by, requested_by_name, cost_name, amount, reason, status, created_at) VALUES ('$requestedBy', '$requestedByName', '$name', '$amount', '$reason', 'pending', NOW())");
        $notice = $insert ? 'Fund request submitted to the manager for approval.' : 'Unable to submit the fund request.';
    } else {
        $notice = 'Provide a valid cost name, amount, and reason before submitting.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_request'])) {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $decision = isset($_POST['approve_request']) ? 'approved' : 'rejected';
    $managerId = (int) ($_SESSION['user_id'] ?? 0);
    $request = mysqli_query($db, "SELECT * FROM extra_cost_requests WHERE request_id = '$requestId' LIMIT 1");

    if ($request && mysqli_num_rows($request) > 0) {
        $requestData = mysqli_fetch_assoc($request);
        $update = mysqli_query($db, "UPDATE extra_cost_requests SET status = 'approved', approved_by = '$managerId', approved_at = NOW() WHERE request_id = '$requestId' AND status = 'pending'");

        if ($update && (float) $requestData['amount'] > 0) {
            mysqli_query($db, "INSERT INTO extra_costs (cost_name, amount, notes, created_by, created_at) VALUES ('" . mysqli_real_escape_string($db, $requestData['cost_name']) . "', '" . (float) $requestData['amount'] . "', '" . mysqli_real_escape_string($db, $requestData['reason']) . "', '" . (int) $requestData['requested_by'] . "', NOW())");
            $notice = 'Fund request approved and added to extra costs.';
        } elseif ($update) {
            $notice = 'Fund request approved.';
        } else {
            $notice = 'This request was already processed.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $managerId = (int) ($_SESSION['user_id'] ?? 0);
    $update = mysqli_query($db, "UPDATE extra_cost_requests SET status = 'rejected', approved_by = '$managerId', approved_at = NOW() WHERE request_id = '$requestId' AND status = 'pending'");
    $notice = $update ? 'Fund request rejected.' : 'Unable to reject this request.';
}

$costs = mysqli_query($db, "SELECT * FROM extra_costs ORDER BY created_at DESC");
$costRequests = mysqli_query($db, $isSupervisor
    ? "SELECT * FROM extra_cost_requests WHERE requested_by = '{$_SESSION['user_id']}' ORDER BY created_at DESC"
    : "SELECT r.*, u.user_name AS requester_name FROM extra_cost_requests r LEFT JOIN users u ON u.user_id = r.requested_by ORDER BY r.created_at DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Operations</div>
    <h2 class="mb-0 mt-2"><?php echo $isSupervisor ? 'Request extra funds' : 'Manage extra costs'; ?></h2>
</div>

<?php if ($notice !== ''): ?>
    <div class="alert alert-<?php echo strpos($notice, 'Unable') === 0 || strpos($notice, 'Provide') === 0 || strpos($notice, 'already') !== false ? 'warning' : 'success'; ?>"><?php echo htmlspecialchars($notice); ?></div>
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
        <span class="badge bg-primary"><?php echo mysqli_num_rows($costs); ?> records</span>
    </div>
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
