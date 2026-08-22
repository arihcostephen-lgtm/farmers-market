<?php
include __DIR__ . '/inc/header.php';

$notice = '';
$error = '';
$uploadRoot = realpath(__DIR__ . '/../uploads/docs/');
$basePath = $uploadRoot ? str_replace('\\', '/', $uploadRoot) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_document'])) {
    $relativePath = str_replace('\\', '/', trim($_POST['document_path'] ?? ''));
    $safePath = $uploadRoot ? realpath($uploadRoot . DIRECTORY_SEPARATOR . $relativePath) : false;
    $safePath = $safePath ? str_replace('\\', '/', $safePath) : false;
    $decision = $_POST['decision'] ?? '';

    if (!$safePath || !is_file($safePath) || strpos($safePath, $basePath . '/') !== 0 || !in_array($decision, ['approved', 'rejected'], true)) {
        $error = 'That document could not be reviewed.';
    } else {
        $documentPath = mysqli_real_escape_string($db, $relativePath);
        $reviewedBy = (int) $_SESSION['user_id'];
        $saveReview = mysqli_query($db, "INSERT INTO supervisor_document_reviews (document_path, status, reviewed_by, reviewed_at) VALUES ('$documentPath', '$decision', $reviewedBy, NOW()) ON DUPLICATE KEY UPDATE status = '$decision', reviewed_by = $reviewedBy, reviewed_at = NOW()");
        if ($saveReview) {
            $actorName = mysqli_real_escape_string($db, $managerName);
            $action = $decision === 'approved' ? 'document_approved' : 'document_rejected';
            mysqli_query($db, "INSERT INTO manager_activity_log (actor_id, actor_name, action_type, target_type, notes) VALUES ($reviewedBy, '$actorName', '$action', 'document', '$documentPath')");
            $notice = 'Document ' . $decision . '.';
        } else {
            $error = 'The review decision could not be saved.';
        }
    }
}

$documents = [];
if ($uploadRoot && is_dir($uploadRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relative = str_replace('\\', '/', substr(str_replace('\\', '/', $file->getPathname()), strlen($basePath) + 1));
            $documents[] = ['path' => $relative, 'name' => $file->getFilename(), 'modified' => $file->getMTime(), 'size' => $file->getSize()];
        }
    }
}
usort($documents, function ($first, $second) { return $second['modified'] <=> $first['modified']; });
$reviewRows = [];
$reviewQuery = mysqli_query($db, "SELECT document_path, status, reviewed_at FROM supervisor_document_reviews");
if ($reviewQuery) {
    while ($review = mysqli_fetch_assoc($reviewQuery)) {
        $reviewRows[$review['document_path']] = $review;
    }
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
    <div class="table-responsive"><table class="table table-hover align-middle data-table"><thead><tr><th>Document</th><th>Uploaded</th><th>Size</th><th>Decision</th><th class="text-end">Review</th></tr></thead><tbody>
    <?php if (count($documents) > 0): foreach ($documents as $document): $review = $reviewRows[$document['path']] ?? null; $url = '../uploads/docs/' . implode('/', array_map('rawurlencode', explode('/', $document['path']))); ?><tr>
        <td><a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none"><i class="fa-solid fa-file me-2 text-success"></i><?php echo htmlspecialchars($document['name']); ?></a><div class="small text-muted"><?php echo htmlspecialchars(dirname($document['path'])); ?></div></td>
        <td><?php echo date('M j, Y', $document['modified']); ?></td>
        <td><?php echo number_format($document['size'] / 1024, 1); ?> KB</td>
        <td><?php if (!$review): ?><span class="badge bg-warning text-dark">Pending</span><?php elseif ($review['status'] === 'approved'): ?><span class="badge bg-success">Approved</span><?php else: ?><span class="badge bg-danger">Rejected</span><?php endif; ?></td>
        <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener">View</a><form method="post" class="d-inline"><input type="hidden" name="document_path" value="<?php echo htmlspecialchars($document['path']); ?>"><button class="btn btn-sm btn-success" name="decision" value="approved" type="submit" data-review-document>Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="rejected" type="submit" data-review-document>Reject</button><input type="hidden" name="review_document" value="1"></form></td>
    </tr><?php endforeach; else: ?><tr><td colspan="5" class="text-center text-muted py-4">No farmer documents have been uploaded yet.</td></tr><?php endif; ?>
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
