<?php include "inc/header.php"; ?>
<?php
$reports = mysqli_query($db, "SELECT r.report_id, COALESCE(u.user_name, r.supervisor_name) AS supervisor_name, r.title, r.report_body, r.created_at FROM supervisor_reports r LEFT JOIN users u ON u.user_id = r.supervisor_id AND u.role = 5 ORDER BY r.created_at DESC");
?>
<div class="page-content">
    <div class="welcome-panel">
        <span class="small-label"><i class="bx bx-file me-1"></i>Supervisor reports</span>
        <h1 class="mt-3 mb-1">Field reports</h1>
        <p class="mb-0">Review operational reports submitted by supervisors.</p>
    </div>
    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table">
                <thead>
                    <tr><th>Report</th><th>Supervisor</th><th>Details</th><th>Submitted</th></tr>
                </thead>
                <tbody>
                    <?php if ($reports && mysqli_num_rows($reports) > 0): while ($report = mysqli_fetch_assoc($reports)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($report['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($report['supervisor_name']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($report['report_body'])); ?></td>
                            <td><small><?php echo date('M j, Y g:i a', strtotime($report['created_at'])); ?></small></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No supervisor reports submitted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "inc/footer.php"; ?>
