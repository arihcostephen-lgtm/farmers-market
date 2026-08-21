<?php
session_start();
ob_start();
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/inc/language.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

$successCount = 0;
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['products_csv'])) {
    if ($_FILES['products_csv']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages[] = 'Please choose a valid CSV file.';
    } elseif (strtolower(pathinfo($_FILES['products_csv']['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $errorMessages[] = 'Only CSV files are supported.';
    } else {
        $handle = fopen($_FILES['products_csv']['tmp_name'], 'r');
        $headers = $handle ? fgetcsv($handle) : false;
        $headerMap = [];
        if ($headers) {
            foreach ($headers as $index => $header) {
                $headerMap[strtolower(trim($header))] = $index;
            }
        }
        $requiredHeaders = ['product_name', 'price', 'stock_quantity'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headerMap)) {
                $errorMessages[] = "Missing required column: $requiredHeader";
            }
        }

        if (!$errorMessages && $handle) {
            $sellerEmail = mysqli_real_escape_string($db, $_SESSION['email']);
            $insertSql = "INSERT INTO products (product_name, description, category_id, price, product_unit, is_negotiable, view_count, harvest_date, seasonal_availability, stock_quantity, low_stock_threshold, seller_email, status, join_date) VALUES (?, ?, ?, ?, ?, ?, 0, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, 2, NOW())";
            $statement = mysqli_prepare($db, $insertSql);
            if (!$statement) {
                $errorMessages[] = 'Unable to prepare the bulk upload.';
            } else {
                while (($row = fgetcsv($handle)) !== false) {
                    $get = function (string $name) use ($headerMap, $row): string {
                        return isset($headerMap[$name]) ? trim($row[$headerMap[$name]] ?? '') : '';
                    };
                    $productName = $get('product_name');
                    $price = (float) $get('price');
                    $stockQuantity = max(0, (int) $get('stock_quantity'));
                    $productUnit = in_array(strtolower($get('product_unit')), ['kilogram', 'litre', 'gram', 'piece', 'each'], true) ? strtolower($get('product_unit')) : 'kilogram';
                    if ($productName === '' || $price < 0) {
                        $errorMessages[] = 'Skipped a row with an empty product name or invalid price.';
                        continue;
                    }
                    $description = $get('description');
                    $categoryId = $get('category_id') !== '' ? (int) $get('category_id') : null;
                    $isNegotiable = in_array(strtolower($get('is_negotiable')), ['1', 'yes', 'true'], true) ? 1 : 0;
                    $harvestDate = $get('harvest_date');
                    $seasonalAvailability = $get('seasonal_availability');
                    $lowStockThreshold = max(0, (int) ($get('low_stock_threshold') ?: 5));
                    mysqli_stmt_bind_param($statement, 'ssidsisssis', $productName, $description, $categoryId, $price, $productUnit, $isNegotiable, $harvestDate, $seasonalAvailability, $stockQuantity, $lowStockThreshold, $sellerEmail);
                    if (mysqli_stmt_execute($statement)) {
                        $successCount++;
                    } else {
                        $errorMessages[] = "Could not import product: $productName";
                    }
                }
                mysqli_stmt_close($statement);
            }
        }
        if ($handle) {
            fclose($handle);
        }
    }
}
?>
<!doctype html>
<html lang="<?php echo $currentLanguage === 'lg' ? 'lg' : 'en'; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bulk Product Upload | Farmers Market</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="mb-0">Bulk Product Upload</h3>
          <a href="farmerDashboard.php?do=Manage" class="btn btn-outline-secondary">Back to Products</a>
        </div>
        <p class="text-muted">Upload a CSV with columns: product_name, price, product_unit (kilogram, litre, gram, piece, or each), stock_quantity, description, category_id, is_negotiable, harvest_date, seasonal_availability, low_stock_threshold.</p>
        <?php if ($successCount > 0) { ?><div class="alert alert-success"><?php echo $successCount; ?> product(s) uploaded and sent for admin approval.</div><?php } ?>
        <?php foreach ($errorMessages as $errorMessage) { ?><div class="alert alert-warning"><?php echo htmlspecialchars($errorMessage); ?></div><?php } ?>
        <form method="post" enctype="multipart/form-data">
          <label for="productsCsv" class="form-label">Products CSV file</label>
          <input id="productsCsv" type="file" name="products_csv" class="form-control mb-3" accept=".csv,text/csv" required>
          <button type="submit" class="btn btn-success">Upload Products</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
<?php ob_end_flush(); ?>
