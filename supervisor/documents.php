<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || !in_array((int) ($_SESSION['role'] ?? 0), [4, 5], true)) {
    header('Location: ../admin/index.php');
    exit;
}
require_once __DIR__ . '/../admin/inc/db.php';
$managerRole = (int) $_SESSION['role'];
$managerName = !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Manager';
if ($managerRole !== 5) {
    header('Location: dashboard.php');
    exit;
}

$notice = $_SESSION['document_notice'] ?? '';
unset($_SESSION['document_notice']);
$error = '';
$uploadRoot = realpath(__DIR__ . '/../uploads/docs/');
$basePath = $uploadRoot ? str_replace('\\', '/', $uploadRoot) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_document'])) {
    $relativePath = str_replace('\\', '/', trim($_POST['document_path'] ?? ''));
    $safePath = $uploadRoot ? realpath($uploadRoot . DIRECTORY_SEPARATOR . $relativePath) : false;
    $safePath = $safePath ? str_replace('\\', '/', $safePath) : false;
    $normalizedBase = rtrim(str_replace('\\', '/', $basePath), '/');
    $normalizedSafe = $safePath ? rtrim($safePath, '/') : false;
    $isInsideUploadRoot = $normalizedSafe && strcasecmp($normalizedSafe, $normalizedBase) !== 0 && strncasecmp($normalizedSafe, $normalizedBase . '/', strlen($normalizedBase) + 1) === 0;
    $decision = $_POST['decision'] ?? '';
    $adminApprovalQuery = mysqli_query($db, "SELECT review_id FROM admin_document_reviews WHERE document_path='" . mysqli_real_escape_string($db, $relativePath) . "' AND status='approved' LIMIT 1");
    $adminApproved = $adminApprovalQuery && mysqli_num_rows($adminApprovalQuery) > 0;

    if (!$uploadRoot || !$isInsideUploadRoot || !is_file($safePath) || !$adminApproved || !in_array($decision, ['approved', 'rejected'], true)) {
        $error = !$adminApproved ? 'This document must be reviewed and approved by an admin first.' : 'That document could not be reviewed.';
    } else {
        $documentPath = mysqli_real_escape_string($db, $relativePath);
        $reviewedBy = (int) $_SESSION['user_id'];
        $saveReview = mysqli_query($db, "INSERT INTO supervisor_document_reviews (document_path, status, reviewed_by, reviewed_at) VALUES ('$documentPath', '$decision', $reviewedBy, NOW()) ON DUPLICATE KEY UPDATE status = '$decision', reviewed_by = $reviewedBy, reviewed_at = NOW()");
        if ($saveReview) {
            $actorName = mysqli_real_escape_string($db, $managerName);
            $action = $decision === 'approved' ? 'document_approved' : 'document_rejected';
            $activitySaved = mysqli_query($db, "INSERT INTO supervisor_activity_log (actor_id, actor_name, action_type, target_type, target_id, notes) VALUES ($reviewedBy, '$actorName', '$action', 'document', NULL, '$documentPath')");
            if ($activitySaved) {
                $_SESSION['document_notice'] = 'Document ' . $decision . ' successfully.';
                header('Location: documents.php');
                exit;
            }
            $error = 'The document review was saved, but its activity log failed: ' . mysqli_error($db);
        } else {
            $error = 'The review decision could not be saved: ' . mysqli_error($db);
        }
    }
}

$documents = [];
$adminApprovedDocuments = [];
$adminReviewQuery = mysqli_query($db, "SELECT document_path, status, reviewed_at FROM admin_document_reviews WHERE status = 'approved'");
if ($adminReviewQuery) {
    while ($adminReview = mysqli_fetch_assoc($adminReviewQuery)) {
        $normalizedPath = ltrim(str_replace('\\', '/', trim($adminReview['document_path'])), '/');
        $adminApprovedDocuments[$normalizedPath] = $adminReview;
    }
}
if ($uploadRoot && is_dir($uploadRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relative = str_replace('\\', '/', substr(str_replace('\\', '/', $file->getPathname()), strlen($basePath) + 1));
            $relative = ltrim($relative, '/');
            if (isset($adminApprovedDocuments[$relative])) {
                $documents[] = ['path' => $relative, 'name' => $file->getFilename(), 'modified' => $file->getMTime(), 'size' => $file->getSize()];
            }
        }
    }
}
usort($documents, function ($first, $second) { return $second['modified'] <=> $first['modified']; });
include __DIR__ . '/inc/header.php';
$reviewRows = [];
$reviewQuery = mysqli_query($db, "SELECT document_path, status, reviewed_by, reviewed_at FROM supervisor_document_reviews");
if ($reviewQuery) {
    while ($review = mysqli_fetch_assoc($reviewQuery)) {
        $reviewRows[$review['document_path']] = $review;
    }
} else {
    $error = 'Document review history could not be loaded: ' . mysqli_error($db);
}
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div><div class="text-uppercase small fw-semibold opacity-75">Field compliance</div><h2 class="mb-0 mt-2">Approve farmer documents</h2></div>
    <span class="badge rounded-pill bg-light text-success px-3 py-2"><?php echo count($documents); ?> uploaded</span>
</div>
<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2"><div><h5 class="mb-1">Document review queue</h5><small class="text-muted">Check each upload against the farmer visit findings before approving it.</small></div><a href="dashboard.php" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-arrow-left me-1"></i>Back to dashboard</a></div>
    <div class="table-responsive"><table class="table table-hover align-middle data-table"><thead><tr><th>Document</th><th>Uploaded</th><th>Size</th><th>Admin review</th><th>Supervisor decision</th><th>Reviewed</th><th class="text-end">Actions</th></tr></thead><tbody>
    <?php if (count($documents) > 0): foreach ($documents as $document): $review = $reviewRows[$document['path']] ?? null; $url = '../uploads/docs/' . implode('/', array_map('rawurlencode', explode('/', $document['path']))); ?><tr>
        <td><a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none"><i class="fa-solid fa-file me-2 text-success"></i><?php echo htmlspecialchars($document['name']); ?></a><div class="small text-muted"><?php echo htmlspecialchars(dirname($document['path'])); ?></div></td>
        <td><?php echo date('M j, Y', $document['modified']); ?></td>
        <td><?php echo number_format($document['size'] / 1024, 1); ?> KB</td>
        <td><span class="badge bg-success">Approved by admin</span><small class="d-block text-muted mt-1"><?php echo htmlspecialchars(date('M j, Y g:i a', strtotime($adminApprovedDocuments[$document['path']]['reviewed_at']))); ?></small></td>
        <td><?php if (!$review): ?><span class="badge bg-warning text-dark">Awaiting decision</span><?php elseif ($review['status'] === 'approved'): ?><span class="badge bg-success">Approved</span><?php else: ?><span class="badge bg-danger">Rejected</span><?php endif; ?></td>
        <td><?php echo $review ? '<small class="text-muted">' . htmlspecialchars(date('M j, Y g:i a', strtotime($review['reviewed_at']))) . '</small>' : '<span class="text-muted">Not reviewed</span>'; ?></td>
        <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener">View</a><form method="post" action="documents.php" class="d-inline"><input type="hidden" name="document_path" value="<?php echo htmlspecialchars($document['path'], ENT_QUOTES); ?>"><input type="hidden" name="review_document" value="1"><button class="btn btn-sm btn-success" name="decision" value="approved" type="submit" data-review-document>Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="rejected" type="submit" data-review-document>Reject</button></form></td>
    </tr><?php endforeach; else: ?><tr><td colspan="7" class="text-center text-muted py-4">No admin-approved documents are awaiting supervisor review.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<script>
    document.querySelectorAll('[data-review-document]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!window.confirm('Record this document as ' + button.value + '?')) event.preventDefault();
        });
    });
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
