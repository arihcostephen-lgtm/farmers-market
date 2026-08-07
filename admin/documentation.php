<?php include "inc/header.php"; ?>

<?php
$docFile = __DIR__ . '/docs_content.html';
$uploadDir = __DIR__ . '/../uploads/docs/';
if (!is_dir($uploadDir)) @mkdir($uploadDir,0755,true);
$msg = '';

function sanitize_html($html) {
    // Basic sanitizer: remove script/style/iframe/object/embed and on* attributes
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $xpath = new DOMXPath($doc);
    // remove dangerous elements
    foreach (['//script', '//style', '//iframe', '//object', '//embed', '//link'] as $query) {
        $nodes = $xpath->query($query);
        foreach ($nodes as $n) { $n->parentNode->removeChild($n); }
    }
    // remove on* attributes
    $nodes = $doc->getElementsByTagName('*');
    foreach ($nodes as $node) {
        $attrs = [];
        foreach ($node->attributes ?? [] as $attr) { $attrs[] = $attr->name; }
        foreach ($attrs as $a) {
            if (stripos($a, 'on') === 0) $node->removeAttribute($a);
            if (in_array(strtolower($a), ['javascript', 'srcdoc'])) $node->removeAttribute($a);
        }
    }
    $body = '';
    foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $child) {
        $body .= $doc->saveHTML($child);
    }
    return $body;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_docs'])) {
  $content = $_POST['doc_content'] ?? '';
  $safe = sanitize_html($content);
  file_put_contents($docFile, $safe);
  if (!empty($_FILES['doc_image']['name'])) {
    $target = $uploadDir . basename($_FILES['doc_image']['name']);
    if (move_uploaded_file($_FILES['doc_image']['tmp_name'], $target)) {
      $msg = 'Documentation and image saved.';
    } else {
      $msg = 'Documentation saved. Image upload failed.';
    }
  } else {
    $msg = 'Documentation saved.';
  }
}
$existing = file_exists($docFile) ? file_get_contents($docFile) : '';
$latestImage = '';
$files = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
if ($files) {
  usort($files, function($a,$b){return filemtime($b)-filemtime($a);});
  $latestImage = $files[0];
}
?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Documentation Editor</h4>
        <p class="text-muted">Edit site documentation. You can upload an image to use within docs.</p>

        <?php if ($msg) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>'; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Image (optional)</label>
            <input type="file" name="doc_image" accept="image/*" class="form-control">
            <?php if ($latestImage) echo '<div class="mt-2"><img src="../uploads/docs/'.basename($latestImage).'" style="max-height:120px;" alt=""></div>'; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Content (HTML allowed)</label>
            <textarea name="doc_content" rows="12" class="form-control"><?php echo htmlspecialchars($existing); ?></textarea>
          </div>
          <button class="btn btn-success" name="save_docs">Save Documentation</button>
        </form>

        <hr>
        <h5 class="mt-3">Preview</h5>
        <div class="p-3 bg-dark text-light">
          <?php echo $existing; ?>
          <?php if ($latestImage) echo '<div class="mt-3"><img src="../uploads/docs/'.basename($latestImage).'" style="max-width:100%;" alt=""></div>'; ?>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
