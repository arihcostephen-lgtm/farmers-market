<?php include __DIR__ . '/inc/header.php'; ?>
<?php
require_once __DIR__ . '/../inc/report_attachments.php';
$notice = '';
$isSupervisor = $managerRole === 5;

if ($isSupervisor && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_report'])) {
    $title = trim($_POST['title'] ?? '');
    $reportBody = trim($_POST['report_body'] ?? '');
    $supervisorId = (int) ($_SESSION['user_id'] ?? 0);
    $supervisorName = $managerName;
    if ($title !== '' && $reportBody !== '') {
        $reportStatement = mysqli_prepare($db, 'INSERT INTO supervisor_reports (supervisor_id, supervisor_name, title, report_body) VALUES (?, ?, ?, ?)');
        if ($reportStatement) {
            mysqli_stmt_bind_param($reportStatement, 'isss', $supervisorId, $supervisorName, $title, $reportBody);
            $insertReport = mysqli_stmt_execute($reportStatement);
            $reportError = mysqli_stmt_error($reportStatement);
            $reportId = mysqli_stmt_insert_id($reportStatement);
            mysqli_stmt_close($reportStatement);
        } else {
            $insertReport = false;
            $reportError = mysqli_error($db);
            $reportId = 0;
        }
        if ($insertReport && $reportId > 0) {
            $attachmentNotice = save_report_attachments($db, $reportId, $supervisorId, $_FILES['attachments'] ?? [], __DIR__ . '/../uploads/docs');
            $notice = $attachmentNotice === '' ? 'Report submitted to the manager.' : 'Report submitted, but ' . strtolower($attachmentNotice);
        } else {
            $notice = 'Unable to submit the report: ' . $reportError;
        }
    } else {
        $notice = 'Enter a title and report details before submitting.';
    }
}

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
$reportViewerId = (int) ($_SESSION['user_id'] ?? 0);
$reportQuerySql = $isSupervisor
    ? "SELECT r.report_id, COALESCE(u.user_name, r.supervisor_name) AS supervisor_name, r.title, r.report_body, r.created_at FROM supervisor_reports r LEFT JOIN users u ON u.user_id = r.supervisor_id AND u.role = 5 WHERE r.supervisor_id='$reportViewerId' ORDER BY r.created_at DESC"
    : "SELECT r.report_id, COALESCE(u.user_name, r.supervisor_name) AS supervisor_name, r.title, r.report_body, r.created_at FROM supervisor_reports r LEFT JOIN users u ON u.user_id = r.supervisor_id AND u.role = 5 ORDER BY r.created_at DESC";
$supervisorReports = mysqli_query($db, $reportQuerySql);
if (!$supervisorReports) {
    $notice = 'Reports could not be loaded: ' . mysqli_error($db);
}
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Reports</div>
    <h2 class="mb-0 mt-2">System-wide performance overview</h2>
</div>

<?php if ($notice !== ''): ?>
    <div class="alert alert-<?php echo strpos($notice, 'Unable') === 0 || strpos($notice, 'Enter') === 0 ? 'warning' : 'success'; ?>"><i class="fa-solid fa-circle-info me-2"></i><?php echo htmlspecialchars($notice); ?></div>
<?php endif; ?>

<?php if ($isSupervisor): ?>
    <div class="card p-4 mb-4">
        <h5 class="mb-1"><i class="fa-solid fa-pen-to-square text-success me-2"></i>Write field report</h5>
        <p class="text-muted mb-3">Submit operational findings for manager review.</p>
        <form method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-12"><label class="form-label" for="reportTitle">Report title</label><input class="form-control" id="reportTitle" name="title" maxlength="200" required placeholder="Example: Kyambogo farm visit summary"></div>
                <div class="col-12"><label class="form-label" for="reportBody">Report details</label><textarea class="form-control" id="reportBody" name="report_body" rows="7" required placeholder="Record findings, actions, risks, or recommendations."></textarea></div>
                <div class="col-12"><label class="form-label" for="reportAttachments">Documents and images</label><input class="form-control" id="reportAttachments" name="attachments[]" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp" multiple><small class="form-text text-muted">Attach up to 5 PDF, Word, JPG, PNG, GIF, or WEBP files. Maximum 10 MB each.</small></div>
                <div class="col-12"><button type="submit" name="submit_report" class="btn btn-success"><i class="fa-solid fa-paper-plane me-2"></i>Submit Report</button></div>
            </div>
        </form>
    </div>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>My submitted reports</h5><span class="badge bg-success">Private view</span></div>
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead><tr><th>Report</th><th>Details</th><th>Submitted</th></tr></thead>
                <tbody>
                    <?php if ($supervisorReports && mysqli_num_rows($supervisorReports) > 0): while ($report = mysqli_fetch_assoc($supervisorReports)): ?>
                        <?php $attachments = report_attachment_rows($db, $report['report_id']); ?><tr><td><strong><?php echo htmlspecialchars($report['title']); ?></strong></td><td><?php echo nl2br(htmlspecialchars($report['report_body'])); ?><?php if ($attachments): ?><div class="mt-2"><?php foreach ($attachments as $attachment): ?><a class="d-block small text-success" href="../uploads/docs/<?php echo htmlspecialchars($attachment['attachment_path']); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip me-1"></i><?php echo htmlspecialchars($attachment['attachment_name']); ?></a><?php endforeach; ?></div><?php endif; ?></td><td><small><?php echo date('M j, Y g:i a', strtotime($report['created_at'])); ?></small></td></tr>
                    <?php endwhile; else: ?><tr><td colspan="3" class="text-center text-muted py-4">You have not submitted any reports yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0"><i class="fa-solid fa-file-lines text-success me-2"></i>Supervisor reports</h5><span class="badge bg-success">Manager view</span></div>
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead><tr><th>Report</th><th>Supervisor</th><th>Details</th><th>Submitted</th></tr></thead>
                <tbody>
                    <?php if ($supervisorReports && mysqli_num_rows($supervisorReports) > 0): while ($report = mysqli_fetch_assoc($supervisorReports)): ?>
                        <?php $attachments = report_attachment_rows($db, $report['report_id']); ?><tr><td><strong><?php echo htmlspecialchars($report['title']); ?></strong></td><td><?php echo htmlspecialchars($report['supervisor_name']); ?></td><td class="report-details"><?php echo nl2br(htmlspecialchars($report['report_body'])); ?><?php if ($attachments): ?><div class="mt-2"><?php foreach ($attachments as $attachment): ?><a class="d-block small text-success" href="../uploads/docs/<?php echo htmlspecialchars($attachment['attachment_path']); ?>" target="_blank" rel="noopener"><i class="fa-solid fa-paperclip me-1"></i><?php echo htmlspecialchars($attachment['attachment_name']); ?></a><?php endforeach; ?></div><?php endif; ?></td><td><small><?php echo date('M j, Y g:i a', strtotime($report['created_at'])); ?></small></td></tr>
                    <?php endwhile; else: ?><tr><td colspan="4" class="text-center text-muted py-4">No supervisor reports submitted yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

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
