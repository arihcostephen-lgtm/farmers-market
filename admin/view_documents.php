<?php include "inc/header.php"; ?>

<?php
$docFile = __DIR__ . '/docs_content.html';
$docs = file_exists($docFile) ? file_get_contents($docFile) : '<p>No documentation available.</p>';
$uploadDir = __DIR__ . '/../uploads/docs/';
$uploadRoot = realpath($uploadDir);
$adminNotice = '';
$adminError = '';
$documents = [];
if (is_dir($uploadDir)) {
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadDir, FilesystemIterator::SKIP_DOTS));
  foreach ($iterator as $file) {
    if ($file->isFile()) {
      $relative = $uploadRoot ? str_replace('\\', '/', substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $uploadRoot)) + 1)) : '';
      $documents[] = ['path' => $file->getPathname(), 'relative' => $relative, 'modified' => $file->getMTime()];
    }
  }
  usort($documents, function($a, $b) { return $b['modified'] - $a['modified']; });
}

// Sanitize display of existing docs to remove scripts
function sanitize_display($html) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($doc);
    foreach (['//script','//style','//iframe','//object','//embed','//link'] as $q) {
        $nodes = $xpath->query($q);
        foreach ($nodes as $n) $n->parentNode->removeChild($n);
    }
    $body = '';
    $bodyNode = $doc->getElementsByTagName('body')->item(0);
    if ($bodyNode) {
      foreach ($bodyNode->childNodes as $child) {
        $body .= $doc->saveHTML($child);
      }
    }
    return $body;
}

// Handle image delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
  $file = str_replace('\\', '/', $_POST['file'] ?? '');
  $path = realpath(__DIR__ . '/../uploads/docs/' . $file);
  $baseDir = realpath(__DIR__ . '/../uploads/docs/');
  if ($path && strpos($path, $baseDir) === 0 && file_exists($path)) {
    unlink($path);
  }
  // refresh list
  header('Location: view_documents.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_document'])) {
  $relative = str_replace('\\', '/', trim($_POST['document_path'] ?? ''));
  $decision = $_POST['decision'] ?? '';
  $safePath = $uploadRoot ? realpath($uploadRoot . DIRECTORY_SEPARATOR . $relative) : false;
  $basePath = $uploadRoot ? rtrim(str_replace('\\', '/', $uploadRoot), '/') : '';
  $safePath = $safePath ? str_replace('\\', '/', $safePath) : false;
  $isInsideRoot = $safePath && strncasecmp($safePath, $basePath . '/', strlen($basePath) + 1) === 0;
  if (!$isInsideRoot || !is_file($safePath) || !in_array($decision, ['approved', 'rejected'], true)) {
    $adminError = 'That document could not be reviewed.';
  } else {
    $documentPath = mysqli_real_escape_string($db, $relative);
    $reviewedBy = (int) $_SESSION['user_id'];
    $saveReview = mysqli_query($db, "INSERT INTO admin_document_reviews (document_path, status, reviewed_by, reviewed_at) VALUES ('$documentPath', '$decision', $reviewedBy, NOW()) ON DUPLICATE KEY UPDATE status='$decision', reviewed_by=$reviewedBy, reviewed_at=NOW()");
    if ($saveReview) {
      $adminNotice = 'Document marked ' . $decision . ' and sent to the supervisor queue.';
    } else {
      $adminError = 'The admin review could not be saved: ' . mysqli_error($db);
    }
  }
}

$adminReviews = [];
$adminReviewQuery = mysqli_query($db, "SELECT document_path, status, reviewed_at FROM admin_document_reviews");
if ($adminReviewQuery) {
  while ($review = mysqli_fetch_assoc($adminReviewQuery)) {
    $adminReviews[$review['document_path']] = $review;
  }
}

// Pagination
$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = count($documents);
$totalPages = max(1, ceil($total / $perPage));
$start = ($page - 1) * $perPage;
$documentsPage = array_slice($documents, $start, $perPage);
?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>View Documents</h4>
        <p class="text-muted">Browse and view uploaded PDFs, images, and documents from farmers.</p>
        <?php if ($adminNotice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($adminNotice); ?></div><?php endif; ?>
        <?php if ($adminError !== ''): ?><div class="alert alert-danger"><?php echo htmlspecialchars($adminError); ?></div><?php endif; ?>

        <div class="mb-3">
          <div class="p-3 bg-dark text-light rounded">
                <?php echo sanitize_display($docs); ?>
          </div>
        </div>

            <?php if (count($documents) > 0): ?>
              <h5 class="mt-3">Uploaded Documents</h5>
              <div class="row g-3 mt-2">
                <?php foreach ($documentsPage as $document): $relative = $document['relative']; $bn = basename($relative); $ext = strtolower(pathinfo($bn, PATHINFO_EXTENSION)); $url = '../uploads/docs/' . implode('/', array_map('rawurlencode', explode('/', $relative))); ?>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100">
                      <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) { ?><a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener"><img src="<?php echo htmlspecialchars($url); ?>" class="img-fluid rounded-top" alt="<?php echo htmlspecialchars($bn); ?>"></a><?php } elseif ($ext === 'pdf') { ?><embed src="<?php echo htmlspecialchars($url); ?>" type="application/pdf" width="100%" height="180"><a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="text-center d-block py-2">Open PDF</a><?php } else { ?><div class="text-center p-5"><i class="bx bx-file fs-1"></i><div><?php echo strtoupper(htmlspecialchars($ext)); ?></div></div><?php } ?>
                      <div class="card-body p-2 text-center">
                        <?php $adminReview = $adminReviews[$relative] ?? null; ?>
                        <?php if (!$adminReview): ?><span class="badge bg-warning text-dark d-block mb-2">Awaiting admin review</span><?php elseif ($adminReview['status'] === 'approved'): ?><span class="badge bg-success d-block mb-2">Sent to supervisor</span><?php else: ?><span class="badge bg-danger d-block mb-2">Rejected by admin</span><?php endif; ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary me-1">View</a>
                        <form method="post" class="d-inline"><input type="hidden" name="document_path" value="<?php echo htmlspecialchars($relative, ENT_QUOTES); ?>"><input type="hidden" name="review_document" value="1"><button type="submit" name="decision" value="approved" class="btn btn-sm btn-success">Review and send</button><button type="submit" name="decision" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button></form>
                        <a href="<?php echo htmlspecialchars($url); ?>" download class="btn btn-sm btn-outline-secondary me-1">Download</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this document?');">
                          <input type="hidden" name="file" value="<?php echo htmlspecialchars($relative); ?>">
                          <button type="submit" name="delete_image" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- pagination -->
              <nav class="mt-3">
                <ul class="pagination">
                  <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="view_documents.php?page=<?php echo $page-1; ?>">Previous</a></li>
                  <?php endif; ?>
                  <?php for ($p=1;$p<=$totalPages;$p++): ?>
                    <li class="page-item <?php echo $p==$page? 'active':''; ?>"><a class="page-link" href="view_documents.php?page=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                  <?php endfor; ?>
                  <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="view_documents.php?page=<?php echo $page+1; ?>">Next</a></li>
                  <?php endif; ?>
                </ul>
              </nav>

            <?php else: ?>
              <div class="alert alert-secondary">No documents uploaded yet.</div>
            <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
