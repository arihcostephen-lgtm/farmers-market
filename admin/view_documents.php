<?php include "inc/header.php"; ?>

<?php
$docFile = __DIR__ . '/docs_content.html';
$docs = file_exists($docFile) ? file_get_contents($docFile) : '<p>No documentation available.</p>';
$uploadDir = __DIR__ . '/../uploads/docs/';
$images = [];
if (is_dir($uploadDir)) {
  $images = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
  usort($images, function($a,$b){return filemtime($b)-filemtime($a);} );
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
    foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $child) {
        $body .= $doc->saveHTML($child);
    }
    return $body;
}

// Handle image delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
  $file = basename($_POST['file'] ?? '');
  $path = realpath(__DIR__ . '/../uploads/docs/' . $file);
  $baseDir = realpath(__DIR__ . '/../uploads/docs/');
  if ($path && strpos($path, $baseDir) === 0 && file_exists($path)) {
    unlink($path);
  }
  // refresh list
  header('Location: view_documents.php');
  exit;
}

// Pagination
$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = count($images);
$totalPages = max(1, ceil($total / $perPage));
$start = ($page - 1) * $perPage;
$imagesPage = array_slice($images, $start, $perPage);
?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>View Documents</h4>
        <p class="text-muted">Browse the current documentation content and uploaded images.</p>

        <div class="mb-3">
          <div class="p-3 bg-dark text-light rounded">
                <?php echo sanitize_display($docs); ?>
          </div>
        </div>

            <?php if (count($images) > 0): ?>
              <h5 class="mt-3">Uploaded Images</h5>
              <div class="row g-3 mt-2">
                <?php foreach ($imagesPage as $img): $bn = basename($img); ?>
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100">
                      <a href="<?php echo '../uploads/docs/' . $bn; ?>" target="_blank" class="d-block">
                        <img src="<?php echo '../uploads/docs/' . $bn; ?>" class="img-fluid rounded-top" alt="">
                      </a>
                      <div class="card-body p-2 text-center">
                        <a href="<?php echo '../uploads/docs/' . $bn; ?>" download class="btn btn-sm btn-outline-primary me-1">Download</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this image?');">
                          <input type="hidden" name="file" value="<?php echo $bn; ?>">
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
              <div class="alert alert-secondary">No images uploaded for documentation yet.</div>
            <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
