<?php 
  session_start(); 
  ob_start();
  include "admin/inc/db.php";
  require_once __DIR__ . '/admin/inc/email.php';
  require_once __DIR__ . '/inc/language.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Farmer Dashboard | Local Farm Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/0c66e46c25.js" crossorigin="anonymous"></script>

    <!-- Modern Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/farmer-dashboard-modern.css">
    
    <!-- DATATABLE CSS LINK -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <section class="">
      <div class="container-fluid">
        <div class="row flex-nowrap">
            <div class="col-auto px-0" style="background: #11101D;">
                <div id="sidebar" class="collapse collapse-horizontal show border-end" >
                    <div id="sidebar-nav" class="list-group border-0 rounded-0 text-sm-start min-vh-100">
                        <a href="farmerDashboard.php?do=Home" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-gauge-simple-high"></i> <span>&nbsp;Dashboard</span> </a>
                        <hr style="color: #72717f;">
                        <a href="farmerDashboard.php?do=Manage" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-box"></i> <span>&nbsp;My Products</span></a>
                        <a href="farmerDashboard.php?do=Orders" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-cart-shopping"></i> <span>&nbsp;Orders</span></a>
                        <a href="farmerDashboard.php?do=Inquiries" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-regular fa-message"></i> <span>&nbsp;Buyer Inquiries</span></a>
                        <a href="farmerDashboard.php?do=AddDoc" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-file-circle-plus"></i> <span>&nbsp;Add Documents</span></a>
                        <a href="farmerDashboard.php?do=ViewDoc" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-file-lines"></i> <span>&nbsp;View Documents</span></a>
                        <a href="farmerDashboard.php?do=Profile" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-user"></i> <span>&nbsp;Profile</span></a>
                        <a href="farmerDashboard.php?do=Support" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-regular fa-message"></i> <span>&nbsp;Support</span></a>
                        <a href="farmerDashboard.php?do=Contact" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-address-book"></i> <span>&nbsp;Contact</span></a>
                        <div class="list-group-item border-end-0">
                          <small><?php echo t('Language'); ?></small><br>
                          <a href="<?php echo language_url('en'); ?>">English</a> |
                          <a href="<?php echo language_url('lg'); ?>">Luganda</a>
                        </div>
                        <a href="logout.php" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-right-from-bracket"></i> <span>&nbsp;Logout</span></a>
                    </div>
                </div>
            </div>
            <main class="col ps-md-2 pt-2 main_body">
                

                <div class="d-flex justify-content-between pb-3">
                  <a href="#" data-bs-target="#sidebar" data-bs-toggle="collapse" class="border rounded-3 p-1 text-decoration-none"><i class="fa-solid fa-backward"></i> Menu</a>
                  <!-- For users login or nor -->
                  <?php  
                    if (!empty($_SESSION['user_id'])) { 

                      $user_id = $_SESSION['user_id'];
                      $readUId_Sql = "SELECT * FROM users WHERE user_id='$user_id'";
                      $readUId_Query = mysqli_query($db, $readUId_Sql);

                      while( $row = mysqli_fetch_assoc($readUId_Query) ) {
                        $user_id        = $row['user_id'];
                        $fullname         = $row['user_name'];
                        $_SESSION['email']    = $row['user_email'];
                        $_SESSION['phone']    = $row['user_phone'];
                        $password         = $row['user_password'];
                        $role           = $row['role'];
                        $status         = $row['status'];
                        $user_image       = $row['user_image'];
                        ?>
                          <div class="d-flex align-self-center">
                            <div>
                              <?php  
                                    if (!empty($user_image)) {
                                  echo '<img src="admin/assets/images/farmer/' . $user_image . '" style="width: 50px;margin: 0px 10px;">';
                                }
                                else {
                                  echo '<img src="admin/assets/images/farmer/default.png" style="width: 50px;margin: 0px 10px;">';
                                }
                                  ?>
                            </div>
                            <div>
                              <h3><?php echo $fullname; ?></h3>
                            </div>
                          </div>
                        <?php
                      }

                      ?>
                      
                    <?php }

                  ?>
                  <!-- For users login or nor -->
                </div>

                <div class="p-3">

                 

                  <?php

                    $do = isset( $_GET['do'] ) ? $_GET['do'] : "Manage";

                    $farmerAccessEmail = $_SESSION['email'] ?? $_SESSION['user_email'] ?? '';
                    $farmerAccessEmailEscaped = mysqli_real_escape_string($db, $farmerAccessEmail);
                    $bulkUploadSuccess = 0;
                    $bulkUploadErrors = [];
                    if ($do === 'BulkUpload' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['products_csv'])) {
                      if ($_FILES['products_csv']['error'] !== UPLOAD_ERR_OK) {
                        $bulkUploadErrors[] = 'Please choose a valid CSV file.';
                      } elseif (strtolower(pathinfo($_FILES['products_csv']['name'], PATHINFO_EXTENSION)) !== 'csv') {
                        $bulkUploadErrors[] = 'Only CSV files are supported.';
                      } else {
                        $uploadHandle = fopen($_FILES['products_csv']['tmp_name'], 'r');
                        $headers = $uploadHandle ? fgetcsv($uploadHandle) : false;
                        $headerMap = [];
                        if ($headers) {
                          foreach ($headers as $index => $header) {
                            $headerMap[strtolower(trim($header))] = $index;
                          }
                        }
                        foreach (['product_name', 'price', 'stock_quantity'] as $requiredHeader) {
                          if (!array_key_exists($requiredHeader, $headerMap)) {
                            $bulkUploadErrors[] = "Missing required column: $requiredHeader";
                          }
                        }
                        if (!$bulkUploadErrors && $uploadHandle) {
                          $insertSql = "INSERT INTO products (product_name, description, category_id, price, product_unit, is_negotiable, view_count, harvest_date, seasonal_availability, stock_quantity, low_stock_threshold, seller_email, status, join_date) VALUES (?, ?, ?, ?, ?, ?, 0, NULLIF(?, ''), NULLIF(?, ''), ?, ?, ?, 2, NOW())";
                          $uploadStatement = mysqli_prepare($db, $insertSql);
                          if (!$uploadStatement) {
                            $bulkUploadErrors[] = 'Unable to prepare the bulk upload.';
                          } else {
                            while (($row = fgetcsv($uploadHandle)) !== false) {
                              $getUploadValue = function (string $name) use ($headerMap, $row): string {
                                return isset($headerMap[$name]) ? trim($row[$headerMap[$name]] ?? '') : '';
                              };
                              $productName = $getUploadValue('product_name');
                              $price = (float) $getUploadValue('price');
                              $stockQuantity = max(0, (int) $getUploadValue('stock_quantity'));
                              $productUnit = in_array(strtolower($getUploadValue('product_unit')), ['kilogram', 'litre', 'gram', 'piece', 'each'], true) ? strtolower($getUploadValue('product_unit')) : 'kilogram';
                              if ($productName === '' || $price < 0) {
                                $bulkUploadErrors[] = 'Skipped a row with an empty product name or invalid price.';
                                continue;
                              }
                              $description = $getUploadValue('description');
                              $categoryId = $getUploadValue('category_id') !== '' ? (int) $getUploadValue('category_id') : null;
                              $isNegotiable = in_array(strtolower($getUploadValue('is_negotiable')), ['1', 'yes', 'true'], true) ? 1 : 0;
                              $harvestDate = $getUploadValue('harvest_date');
                              $seasonalAvailability = $getUploadValue('seasonal_availability');
                              $lowStockThreshold = max(0, (int) ($getUploadValue('low_stock_threshold') ?: 5));
                              mysqli_stmt_bind_param($uploadStatement, 'ssidsisssis', $productName, $description, $categoryId, $price, $productUnit, $isNegotiable, $harvestDate, $seasonalAvailability, $stockQuantity, $lowStockThreshold, $farmerAccessEmail);
                              if (mysqli_stmt_execute($uploadStatement)) {
                                $bulkUploadSuccess++;
                              } else {
                                $bulkUploadErrors[] = "Could not import product: $productName";
                              }
                            }
                            mysqli_stmt_close($uploadStatement);
                          }
                        }
                        if ($uploadHandle) {
                          fclose($uploadHandle);
                        }
                      }
                    }
                    if ($do === 'Orders' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
                      $orderId = (int) ($_POST['order_id'] ?? 0);
                      $orderStatus = max(0, min(3, (int) ($_POST['status'] ?? 0)));
                      $deliveryUpdate = mysqli_real_escape_string($db, trim($_POST['delivery_update'] ?? ''));
                      mysqli_query($db, "UPDATE order_list o INNER JOIN products p ON p.product_id=o.or_category SET o.status='$orderStatus', o.delivery_update='$deliveryUpdate', o.updated_at=NOW() WHERE o.or_id='$orderId' AND p.seller_email='$farmerAccessEmailEscaped'");
                    }
                    $activeSubscriptionQuery = mysqli_query($db, "SELECT fs.*, sp.description, sp.duration_days FROM farmer_subscriptions fs LEFT JOIN subscription_plans sp ON sp.plan_id=fs.plan_id WHERE fs.farmer_id=" . (int) ($_SESSION['user_id'] ?? 0) . " AND fs.status=1 LIMIT 1");
                    $activeSubscription = $activeSubscriptionQuery ? mysqli_fetch_assoc($activeSubscriptionQuery) : null;
                    if ((int) ($_SESSION['role'] ?? 0) === 2 && !$activeSubscription && $do !== 'Home') {
                      header('Location: farmerDashboard.php?do=Home&subscription_required=1');
                      exit;
                    }

                    if ( $do == "Manage" ) { ?>
                      <div class="container pb-5">
                        <div class="row">
                          <div class="col-lg-12">
                              <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                  <h4 class="fw-bold mb-1">My Products</h4>
                                  <p class="text-muted mb-0">Manage your product listings</p>
                                </div>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                  <a href="farmerDashboard.php?do=Add" class="btn btn-success btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
                                  <a href="farmerDashboard.php?do=BulkUpload" class="btn btn-info btn-sm"><i class="fa-solid fa-upload me-1"></i>Bulk Upload</a>
                                  <a href="farmerDashboard.php?do=ManageTrash" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash me-1"></i>Trash</a>
                                </div>
                              </div>

                            <!-- START: TABLE -->
                            <div class="card">
                              <div class="card-body p-0">
                                <div class="table-responsive">
                                  <table id="example" class="table table-hover align-middle mb-0">
                                    <thead>
                                      <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Unit</th>
                                        <th>Stock</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                      </tr>
                                    </thead>

                                    <tbody>
                                  <?php  
                                    if (!empty($_SESSION['email'])) {
                                      $sellerId = $_SESSION['email'];

                                      $sellerReadSql = "SELECT p.*, c.cat_name FROM products p LEFT JOIN category c ON c.cat_id = p.category_id WHERE p.status != 0 AND p.seller_email='$sellerId' ORDER BY p.product_name ASC";
                                      $sellerReadQuery = mysqli_query( $db, $sellerReadSql );
                                      $sellerCount = mysqli_num_rows($sellerReadQuery);

                                      if ( $sellerCount == 0 ) { ?>
                                        <div class="alert alert-danger text-center" role="alert">
                                        Sorry! No Product Found!.
                                      </div>
                                      <?php }

                                      else {
                                        $i = 0;

                                        while ($row = mysqli_fetch_assoc($sellerReadQuery)) {
                                          $cat_id     = $row['product_id'];
                                          $cat_name     = $row['product_name'];
                                          $cat_desc     = $row['description'];
                                          $is_parent    = $row['category_id'];
                                          $status     = $row['status'];
                                          $join_date    = $row['join_date'];
                                          $cat_image    = $row['image'];
                                          $price      = $row['price'];        
                                          $product_unit = $row['product_unit'] ?? 'kilogram';
                                          $stock_quantity = (int) ($row['stock_quantity'] ?? 0);
                                          $seller_email   = $row['seller_email'];       
                                          $i++;
                                          ?>

                                          <tr>
                                            <td><?php echo $i; ?></td>
                                            <td>
                                              <div class="d-flex align-items-center gap-2">
                                                <?php  
                                                  if (!empty($cat_image)) {
                                                    echo '<img src="admin/assets/images/products/' . htmlspecialchars($cat_image) . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">';
                                                  } else {
                                                    echo '<div style="width: 40px; height: 40px; background: rgba(16, 184, 130, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-box-open text-success"></i></div>';
                                                  }
                                                ?>
                                                <strong><?php echo $cat_name; ?></strong>
                                              </div>
                                            </td>
                                            <td>UGX <?php echo number_format($price); ?></td>
                                            <td><small><?php echo htmlspecialchars($product_unit); ?></small></td>
                                            <td><?php echo $stock_quantity > 0 ? '<span class="badge bg-success">In Stock (' . $stock_quantity . ')</span>' : '<span class="badge bg-danger">Out of Stock</span>'; ?></td>
                                            <td>
                                              <?php  
                                                if (!empty($row['cat_name'])) {
                                                  echo '<span class="badge bg-secondary">' . htmlspecialchars($row['cat_name']) . '</span>';
                                                } else {
                                                  echo '<span class="badge bg-secondary">Uncategorized</span>';
                                                }
                                              ?>
                                            </td>
                                            <td>
                                              <?php  
                                                if ($status == 1) { ?>
                                                  <span class="badge bg-success">Active</span>
                                                <?php } else if ($status == 0) { ?>
                                                  <span class="badge bg-danger">Inactive</span>
                                                <?php } else if ($status == 2) { ?>
                                                  <span class="badge bg-warning">Pending</span>
                                                <?php }
                                              ?>
                                            </td>
                                            <td><small class="text-muted"><?php echo date('M j', strtotime($join_date)); ?></small></td>
                                            <td>
                                              <div class="d-flex gap-2">
                                                <a href="farmerDashboard.php?do=Edit&uId=<?php echo $cat_id; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#uId<?php echo $cat_id; ?>" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash-can"></i></a>
                                              </div>

                                              <!-- Modal Start -->
                                              <div class="modal fade" id="uId<?php echo $cat_id; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-sm">
                                                  <div class="modal-content">
                                                    <div class="modal-header">
                                                      <h5 class="modal-title">Delete Product?</h5>
                                                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                      <p>Move <strong><?php echo htmlspecialchars($cat_name); ?></strong> to trash?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                      <a href="farmerDashboard.php?do=Trash&tId=<?php echo $cat_id; ?>" class="btn btn-danger btn-sm">Move to Trash</a>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                              <!-- Modal End -->
                                            </td>
                                          </tr>

                                          <?php
                                        }
                                      }






                                      
                                    }
                                  ?>
                                  
                                </tbody>
                              </table>
                            </div>
                            <!-- END: TABLE -->

                          </div>
                        </div>
                      </div>
                    <?php }

                    else if ( $do == "BulkUpload" ) { ?>
                      <div class="container-fluid pb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <div><h4 class="fw-bold mb-1">Bulk Product Upload</h4><p class="text-muted mb-0">Add multiple product listings from one CSV file.</p></div>
                          <a href="farmerDashboard.php?do=Manage" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back to Products</a>
                        </div>
                        <?php if ($bulkUploadSuccess > 0): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?php echo $bulkUploadSuccess; ?> product(s) uploaded and sent for admin approval.</div><?php endif; ?>
                        <?php foreach ($bulkUploadErrors as $bulkUploadError): ?><div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($bulkUploadError); ?></div><?php endforeach; ?>
                        <div class="card dashboard-card">
                          <div class="card-body p-4">
                            <div class="upload-dropzone mb-4"><i class="fa-solid fa-file-csv"></i><h5 class="mt-3 mb-2">Choose your product CSV</h5><p class="text-muted mb-0">Required columns: product_name, price, stock_quantity.</p></div>
                            <form method="post" enctype="multipart/form-data">
                              <label for="productsCsv" class="form-label">Products CSV file</label>
                              <input id="productsCsv" type="file" name="products_csv" class="form-control mb-3" accept=".csv,text/csv" required>
                              <button type="submit" class="btn btn-success"><i class="fa-solid fa-upload me-2"></i>Upload Products</button>
                            </form>
                            <p class="small text-muted mt-3 mb-0">Optional columns: product_unit, description, category_id, is_negotiable, harvest_date, seasonal_availability, low_stock_threshold.</p>
                          </div>
                        </div>
                      </div>
                    <?php }

                    else if ( $do == "Orders" ) {
                      $orderQuery = mysqli_query($db, "SELECT o.*, p.product_name FROM order_list o INNER JOIN products p ON p.product_id=o.or_category WHERE p.seller_email='$farmerAccessEmailEscaped' ORDER BY o.or_id DESC");
                      $orderStatuses = ['Pending', 'Confirmed', 'Fulfilled', 'Cancelled'];
                      $orderClasses = ['warning', 'info', 'success', 'danger'];
                    ?>
                      <div class="container-fluid px-0 pb-5">
                        <div class="page-header text-start">
                          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                              <span class="text-uppercase small">Sales workspace</span>
                              <h2><i class="fa-solid fa-boxes-stacked me-2"></i>Farmer Orders</h2>
                              <p class="mb-0">Review customer orders and keep delivery information current.</p>
                            </div>
                            <a href="farmerDashboard.php?do=Home" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard</a>
                          </div>
                        </div>

                        <section class="card" aria-labelledby="orders-heading">
                          <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 id="orders-heading" class="mb-0"><i class="fa-solid fa-list-check text-success me-2"></i>Order Queue</h5>
                            <span class="badge bg-success"><?php echo $orderQuery ? mysqli_num_rows($orderQuery) : 0; ?> orders</span>
                          </div>
                          <div class="card-body p-0">
                            <div class="table-responsive">
                              <table class="table table-hover align-middle mb-0 orders-table">
                                <thead>
                                  <tr><th>Product</th><th>Buyer</th><th>Quantity</th><th>Total</th><th>Delivery</th><th>Status</th><th>Update</th></tr>
                                </thead>
                                <tbody>
                                  <?php if ($orderQuery && mysqli_num_rows($orderQuery) > 0) { while ($order = mysqli_fetch_assoc($orderQuery)) { $orderStatus = (int) $order['status']; ?>
                                    <tr>
                                      <td><strong><?php echo htmlspecialchars($order['product_name'] ?: $order['or_name']); ?></strong></td>
                                      <td><span><?php echo htmlspecialchars($order['user_id']); ?></span><small class="d-block text-muted"><i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($order['user_phone']); ?></small></td>
                                      <td><?php echo number_format((int) ($order['quantity'] ?? 1)); ?> <small class="text-muted"><?php echo htmlspecialchars($order['order_unit'] ?? 'kilogram'); ?></small></td>
                                      <td><strong class="text-success">UGX <?php echo number_format((float) ($order['total_amount'] ?? $order['price'] ?? 0), 2); ?></strong></td>
                                      <td><span><i class="fa-solid fa-location-dot text-success me-1"></i><?php echo nl2br(htmlspecialchars($order['delivery_location'] ?? 'Not provided')); ?></span><?php if (!empty($order['delivery_notes'])) { ?><small class="d-block text-muted mt-1"><?php echo nl2br(htmlspecialchars($order['delivery_notes'])); ?></small><?php } ?></td>
                                      <td><span class="badge bg-<?php echo $orderClasses[$orderStatus] ?? 'secondary'; ?>"><?php echo $orderStatuses[$orderStatus] ?? 'Pending'; ?></span><?php if (!empty($order['delivery_update'])) { ?><small class="d-block text-muted mt-1"><?php echo nl2br(htmlspecialchars($order['delivery_update'])); ?></small><?php } ?></td>
                                      <td>
                                        <form method="post" class="d-flex flex-column gap-2" style="min-width: 190px">
                                          <input type="hidden" name="update_order" value="1"> 
                                          <input type="hidden" name="order_id" value="<?php echo (int) $order['or_id']; ?>">
                                          <select name="status" class="form-select form-select-sm" aria-label="Order status"><?php foreach ($orderStatuses as $statusIndex => $statusLabel) { ?><option value="<?php echo $statusIndex; ?>" <?php echo $statusIndex === $orderStatus ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option><?php } ?></select>
                                          <textarea name="delivery_update" class="form-control form-control-sm" rows="2" placeholder="Add delivery update"><?php echo htmlspecialchars($order['delivery_update'] ?? ''); ?></textarea>
                                          <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i>Save Update</button>
                                        </form>
                                      </td>
                                    </tr>
                                  <?php } } else { ?><tr><td colspan="7" class="text-center text-muted py-5"><i class="fa-solid fa-inbox d-block fs-3 mb-2"></i>No orders yet.</td></tr><?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </section>
                      </div>
                    <?php }

                    else if ( $do == "Home" ) {

                        $farmerEmail = $_SESSION['email'] ?? '';
                        $topProducts = [];
                        $customerRequests = [];
                        $newRequestCount = 0;
                        $totalOrdersCount = 0;
                        $lowStockCount = 0;
                        $pendingInquiryCount = 0;
                        $availablePlans = null;
                        $subscriptionRequest = null;

                        if (!empty($farmerEmail)) {
                          if (isset($_POST['request_subscription']) && !empty($_POST['plan_id'])) {
                            $planId = (int) $_POST['plan_id'];
                            $planQuery = mysqli_query($db, "SELECT plan_id, plan_name, amount FROM subscription_plans WHERE plan_id=$planId AND status=1 LIMIT 1");
                            $selectedPlan = $planQuery ? mysqli_fetch_assoc($planQuery) : null;
                            if ($selectedPlan) {
                              $farmerId = (int) $_SESSION['user_id'];
                              mysqli_query($db, "INSERT INTO farmer_subscriptions (farmer_id, plan_id, subscription_name, amount, status, created_at) VALUES ($farmerId, " . (int) $selectedPlan['plan_id'] . ", '" . mysqli_real_escape_string($db, $selectedPlan['plan_name']) . "', " . (float) $selectedPlan['amount'] . ", 0, NOW()) ON DUPLICATE KEY UPDATE plan_id=VALUES(plan_id), subscription_name=VALUES(subscription_name), amount=VALUES(amount), status=0, approved_by=NULL, approved_at=NULL, created_at=NOW()");
                              header('Location: farmerDashboard.php?do=Home&subscription_submitted=1');
                              exit;
                            }
                          }
                          $availablePlans = mysqli_query($db, "SELECT * FROM subscription_plans WHERE status=1 ORDER BY amount ASC, plan_name ASC");
                          $subscriptionRequestQuery = mysqli_query($db, "SELECT fs.*, sp.description, sp.duration_days FROM farmer_subscriptions fs LEFT JOIN subscription_plans sp ON sp.plan_id=fs.plan_id WHERE fs.farmer_id=" . (int) $_SESSION['user_id'] . " LIMIT 1");
                          $subscriptionRequest = $subscriptionRequestQuery ? mysqli_fetch_assoc($subscriptionRequestQuery) : null;
                          $topProductSql = "SELECT p.product_id AS cat_id, p.product_name AS cat_name, p.image AS cat_image, " .
                                           "COALESCE(SUM(COALESCE(o.quantity, 1)), 0) AS demand_count " .
                                           "FROM products p " .
                                           "LEFT JOIN order_list o ON p.product_id=o.or_category " .
                                           "WHERE p.seller_email='$farmerEmail' AND p.status=1 " .
                                           "GROUP BY p.product_id, p.product_name, p.image " .
                                           "ORDER BY demand_count DESC " .
                                           "LIMIT 5";
                          $topProductQuery = mysqli_query($db, $topProductSql);
                          while ($product = mysqli_fetch_assoc($topProductQuery)) {
                            $topProducts[] = $product;
                          }

                          $customerRequestSql = "SELECT o.or_id, o.user_id, o.user_phone, o.or_name, o.price, o.quantity, o.join_date, o.status, o.delivery_location, o.delivery_update, COALESCE(p.product_name, c.cat_name) AS product_name, u.user_email AS customer_email " .
                                                "FROM order_list o " .
                                                "LEFT JOIN products p ON p.product_id=o.or_category " .
                                                "LEFT JOIN category c ON c.cat_id=o.or_category " .
                                                "LEFT JOIN users u ON u.user_id=o.user_id " .
                                                "WHERE (p.seller_email='$farmerEmail' OR c.seller_email='$farmerEmail') " .
                                                "ORDER BY o.join_date DESC " .
                                                "LIMIT 6";
                          $customerRequestQuery = mysqli_query($db, $customerRequestSql);
                          while ($request = mysqli_fetch_assoc($customerRequestQuery)) {
                            $customerRequests[] = $request;
                          }

                          $newRequestCount = (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM order_list o LEFT JOIN products p ON p.product_id=o.or_category LEFT JOIN category c ON c.cat_id=o.or_category WHERE (p.seller_email='$farmerEmail' OR c.seller_email='$farmerEmail') AND o.status=0"))['total'];
                          $totalOrdersCount = (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM order_list o LEFT JOIN products p ON p.product_id=o.or_category LEFT JOIN category c ON c.cat_id=o.or_category WHERE (p.seller_email='$farmerEmail' OR c.seller_email='$farmerEmail')"))['total'];
                          $lowStockCount = (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM products WHERE seller_email='$farmerEmail' AND status=1 AND stock_quantity <= low_stock_threshold"))['total'];
                          $pendingInquiryCount = (int) mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS total FROM product_inquiries i INNER JOIN products p ON p.product_id=i.product_id WHERE p.seller_email='$farmerEmail' AND i.status=0"))['total'];
                        }

                        $farmerNotifications = [];
                        if ($do === 'Home' && (int) ($_SESSION['role'] ?? 0) === 2) {
                          $notificationQuery = mysqli_query($db, "SELECT n.notification_id, n.title, n.message, n.created_at, n.is_read, f.farm_name FROM farmer_notifications n LEFT JOIN farmer f ON f.farm_id = n.farm_id WHERE n.farmer_id = '" . (int) $_SESSION['user_id'] . "' ORDER BY n.created_at DESC, n.notification_id DESC LIMIT 10");
                          if ($notificationQuery) {
                            while ($notification = mysqli_fetch_assoc($notificationQuery)) {
                              $farmerNotifications[] = $notification;
                            }
                          }
                        }

                        ?>
                        <div class="page-header pt-3 mb-4">
                          <h2>Farmer Dashboard</h2>
                          <p>Your farm's most in-demand products and customer activity at a glance.</p>
                        </div>

                        <?php if (!$activeSubscription) { ?>
                          <div class="alert alert-warning shadow-sm mb-4">
                            <h5 class="alert-heading"><i class="fa-solid fa-lock me-2"></i>Subscription Required</h5>
                            <p class="mb-0">Choose a plan below to unlock all farmer dashboard features. Your request will be verified by our manager.</p>
                          </div>
                        <?php } else { ?>
                          <div class="alert alert-success shadow-sm mb-4"><strong><i class="fa-solid fa-circle-check me-2"></i>Subscription Active:</strong> <?php echo htmlspecialchars($activeSubscription['subscription_name']); ?></div>
                        <?php } ?>
                        <?php if (isset($_GET['subscription_submitted'])): ?><div class="alert alert-success mb-4"><i class="fa-solid fa-check me-2"></i>Subscription request submitted! Awaiting manager approval.</div><?php endif; ?>
                        <?php if ($subscriptionRequest && (int) $subscriptionRequest['status'] === 0): ?>
                          <div class="alert alert-info shadow-sm mb-4"><i class="fa-solid fa-hourglass-half me-2"></i><strong>Pending Approval:</strong> <?php echo htmlspecialchars($subscriptionRequest['subscription_name']); ?> · <span class="badge bg-secondary">Awaiting review</span></div>
                        <?php endif; ?>
                        <?php if ($subscriptionRequest && (int) $subscriptionRequest['status'] === 1): ?>
                          <div class="card border-success shadow-sm mb-4">
                            <div class="card-body">
                              <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                <h5 class="mb-0">Subscription Approved - Payment Required</h5>
                              </div>
                              <p class="mb-3"><strong>Plan:</strong> <?php echo htmlspecialchars($subscriptionRequest['subscription_name'] ?? ''); ?> | <strong>Amount:</strong> UGX <?php echo number_format((float) $subscriptionRequest['amount'] ?? 0, 0); ?></p>
                              <div class="bg-light p-3 rounded mb-3">
                                <h6 class="mb-2"><i class="fa-solid fa-mobile me-2"></i>USSD Payment Method</h6>
                                <p class="mb-1"><strong>Short Code:</strong> <code><?php echo htmlspecialchars($subscriptionRequest['ussd_code'] ?? '*165#'); ?></code></p>
                                <p class="small text-muted mb-2">Dial the above code on your phone and follow the prompts to complete payment.</p>
                              </div>
                              <div class="bg-light p-3 rounded">
                                <h6 class="mb-2"><i class="fa-solid fa-mobile-screen me-2"></i>Mobile Money Instructions</h6>
                                <pre class="mb-0 small" style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($subscriptionRequest['mobile_money_instructions'] ?? ''); ?></pre>
                              </div>
                              <div class="mt-3 p-2 bg-warning bg-opacity-10 rounded">
                                <small class="text-muted"><strong>Payment Reference:</strong> <code><?php echo htmlspecialchars($subscriptionRequest['payment_reference'] ?? ''); ?></code></small>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>
                        <?php if ($farmerNotifications): ?>
                          <div class="card border-info shadow-sm mb-4"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0"><i class="fa-solid fa-bell text-info me-2"></i>Notifications</h5><span class="badge bg-info text-dark"><?php echo count($farmerNotifications); ?></span></div>
                            <?php foreach ($farmerNotifications as $notification): ?><div class="border-start border-3 border-info ps-3 mb-3"><div class="d-flex justify-content-between gap-3"><strong><?php echo htmlspecialchars($notification['title']); ?></strong><small class="text-muted text-nowrap"><?php echo date('M j, Y g:i a', strtotime($notification['created_at'])); ?></small></div><div class="small mt-1"><?php echo htmlspecialchars($notification['message']); ?></div></div><?php endforeach; ?>
                          </div></div>
                        <?php endif; ?>

                        <div class="mb-4">
                          <h5 class="mb-3"><i class="fa-solid fa-layer-group me-2"></i>Subscription Plans</h5>
                          <div class="row g-3">
                            <?php if ($availablePlans && mysqli_num_rows($availablePlans) > 0): while ($plan = mysqli_fetch_assoc($availablePlans)): ?>
                              <?php $isCurrentPlan = $activeSubscription && (int) $activeSubscription['plan_id'] === (int) $plan['plan_id']; ?>
                              <div class="col-md-6 col-lg-4">
                                <div class="card h-100 <?php echo $isCurrentPlan ? 'border-2' : ''; ?>">
                                  <div class="card-body">
                                    <?php if ($isCurrentPlan): ?><span class="badge bg-success mb-2">Active Plan</span><?php endif; ?>
                                    <h5 class="fw-bold"><?php echo htmlspecialchars($plan['plan_name']); ?></h5>
                                    <h4 class="text-success mb-2">UGX <?php echo number_format((float) $plan['amount'], 0); ?></h4>
                                    <small class="text-muted"><?php echo (int) $plan['duration_days']; ?> days access</small>
                                    <p class="mt-2 mb-3 small"><?php echo htmlspecialchars($plan['description'] ?: 'Access all farmer features'); ?></p>
                                    <?php if (!$isCurrentPlan): ?>
                                      <form method="post" class="d-inline-block w-100"><input type="hidden" name="plan_id" value="<?php echo (int) $plan['plan_id']; ?>"><button class="btn btn-success w-100 btn-sm" type="submit" name="request_subscription"><i class="fa-solid fa-check me-1"></i>Select Plan</button></form>
                                    <?php else: ?>
                                      <button type="button" class="btn btn-outline-success w-100 btn-sm" disabled>Current</button>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            <?php endwhile; else: ?>
                              <div class="col-12"><div class="alert alert-secondary mb-0">No plans available.</div></div>
                            <?php endif; ?>
                          </div>
                        </div>

                        <div class="row g-4 mb-4">
                          <div class="col-xl-3 col-md-6">
                            <div class="card stat-card h-100">
                              <i class="fa-solid fa-inbox text-success fs-5 mb-2"></i>
                              <small>New Orders</small>
                              <h3><?php echo number_format($newRequestCount); ?></h3>
                              <p>Pending review</p>
                            </div>
                          </div>
                          <div class="col-xl-3 col-md-6">
                            <div class="card stat-card h-100">
                              <i class="fa-solid fa-shopping-bag text-success fs-5 mb-2"></i>
                              <small>Total Orders</small>
                              <h3><?php echo number_format($totalOrdersCount); ?></h3>
                              <p>All time</p>
                            </div>
                          </div>
                          <div class="col-xl-3 col-md-6">
                            <div class="card stat-card h-100">
                              <i class="fa-solid fa-triangle-exclamation text-warning fs-5 mb-2"></i>
                              <small>Low Stock Items</small>
                              <h3><?php echo number_format($lowStockCount); ?></h3>
                              <p>Needs action</p>
                            </div>
                          </div>
                          <div class="col-xl-3 col-md-6">
                            <div class="card stat-card h-100">
                              <i class="fa-solid fa-message text-info fs-5 mb-2"></i>
                              <small>Buyer Inquiries</small>
                              <h3><?php echo number_format($pendingInquiryCount); ?></h3>
                              <p>Unanswered</p>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4 mb-4">
                          <div class="col-lg-8">
                            <div class="card h-100">
                              <div class="card-header">
                                <h5 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Top Products</h5>
                                <small class="text-muted">Your most requested products</small>
                              </div>
                              <div class="card-body">
                                <div class="table-responsive">
                                  <table class="table table-hover align-middle mb-0">
                                    <thead>
                                      <tr>
                                        <th>Product</th>
                                        <th>Orders</th>
                                        <th>Trend</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php if (count($topProducts) > 0) {
                                        foreach ($topProducts as $product) {
                                          $demand = (int) $product['demand_count'];
                                          $trend = $demand > 5 ? '<span class="badge bg-danger">Hot</span>' : ($demand > 0 ? '<span class="badge bg-success">Rising</span>' : '<span class="badge bg-secondary">New</span>');
                                          ?>
                                          <tr>
                                            <td>
                                              <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($product['cat_image'])) { ?>
                                                  <img src="admin/assets/images/products/<?php echo htmlspecialchars($product['cat_image']); ?>" alt="<?php echo htmlspecialchars($product['cat_name']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                                <?php } else { ?>
                                                  <div style="width: 40px; height: 40px; background: rgba(16, 184, 130, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-box-open text-success"></i></div>
                                                <?php } ?>
                                                <div>
                                                  <strong><?php echo htmlspecialchars($product['cat_name']); ?></strong>
                                                  <br><small class="text-muted">#<?php echo htmlspecialchars($product['cat_id']); ?></small>
                                                </div>
                                              </div>
                                            </td>
                                            <td><strong><?php echo number_format($demand); ?></strong></td>
                                            <td><?php echo $trend; ?></td>
                                          </tr>
                                        <?php }
                                      } else { ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No order data yet.</td></tr>
                                      <?php } ?>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-4">
                            <div class="card h-100">
                              <div class="card-header">
                                <h5 class="mb-0"><i class="fa-solid fa-bell me-2"></i>Quick Actions</h5>
                              </div>
                              <div class="card-body">
                                <div class="d-grid gap-2">
                                  <a href="farmerDashboard.php?do=Orders" class="btn btn-success btn-sm"><i class="fa-solid fa-shopping-bag me-1"></i>Manage Orders</a>
                                  <a href="farmerDashboard.php?do=Inquiries" class="btn btn-info btn-sm"><i class="fa-solid fa-message me-1"></i>Buyer Inquiries <?php if ($pendingInquiryCount > 0) echo "($pendingInquiryCount)"; ?></a>
                                  <a href="farmerDashboard.php?do=Manage" class="btn btn-warning btn-sm"><i class="fa-solid fa-box me-1"></i>My Products</a>
                                  <a href="farmerDashboard.php?do=Profile" class="btn btn-secondary btn-sm"><i class="fa-solid fa-user me-1"></i>Profile</a>
                                </div>
                                <?php if ($lowStockCount > 0) { ?>
                                  <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    <strong><?php echo $lowStockCount; ?></strong> products low on stock
                                  </div>
                                <?php } ?>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4">
                          <div class="col-lg-6">
                            <div class="card h-100">
                              <div class="card-header">
                                <h5 class="mb-0"><i class="fa-solid fa-chart-pie me-2"></i>Order Status Distribution</h5>
                              </div>
                              <div class="card-body">
                                <canvas id="orderStatusChart" height="300"></canvas>
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <div class="card h-100">
                              <div class="card-header">
                                <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Recent Orders</h5>
                              </div>
                              <div class="card-body">
                                <?php if (count($customerRequests) > 0) { ?>
                                  <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($customerRequests, 0, 5) as $request) { ?>
                                      <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                          <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($request['or_name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($request['product_name'] ?: 'Unknown'); ?></small>
                                            <br>
                                            <small class="text-success">UGX <?php echo number_format((float) $request['price'], 0); ?></small>
                                          </div>
                                          <small class="text-muted"><?php echo date('M j', strtotime($request['join_date'])); ?></small>
                                        </div>
                                      </div>
                                    <?php } ?>
                                  </div>
                                <?php } else { ?>
                                  <p class="text-center text-muted py-4">No recent orders</p>
                                <?php } ?>
                              </div>
                            </div>
                          </div>
                        </div>

                        <script>
                          document.addEventListener('DOMContentLoaded', function() {
                            const chartCtx = document.getElementById('orderStatusChart');
                            if (chartCtx) {
                              new Chart(chartCtx, {
                                type: 'doughnut',
                                data: {
                                  labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
                                  datasets: [{
                                    data: [<?php echo $newRequestCount; ?>, 0, <?php echo ($totalOrdersCount - $newRequestCount); ?>, 0],
                                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                                    borderWidth: 2,
                                    borderColor: '#0d1725'
                                  }]
                                },
                                options: {
                                  responsive: true,
                                  maintainAspectRatio: false,
                                  cutout: '60%',
                                  plugins: {
                                    legend: {
                                      position: 'bottom',
                                      labels: { color: '#d7ffe8', padding: 15, font: { size: 12, weight: 600 } }
                                    },
                                    tooltip: {
                                      backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                      borderColor: '#10b981',
                                      borderWidth: 1,
                                      titleColor: '#fff',
                                      bodyColor: '#fff'
                                    }
                                  }
                                }
                              });
                            }
                          });
                        </script>
                    <?php }

                    else if ( $do == "Inquiries" ) {
                        $farmerEmail = mysqli_real_escape_string($db, $_SESSION['user_email'] ?? $_SESSION['email'] ?? '');
                        $inquiryMessage = '';
                        $inquiryError = '';

                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inquiry'])) {
                          $inquiryId = (int) ($_POST['inquiry_id'] ?? 0);
                          $inquiryStatus = max(0, min(2, (int) ($_POST['status'] ?? 0)));
                          $responseText = trim($_POST['response'] ?? '');
                          $response = mysqli_real_escape_string($db, $responseText);
                          $buyerQuery = mysqli_query($db, "SELECT i.buyer_email, i.response AS previous_response, u.user_id AS buyer_id, u.user_email, p.product_name, i.subject FROM product_inquiries i INNER JOIN products p ON p.product_id=i.product_id LEFT JOIN users u ON u.user_id=i.buyer_id WHERE i.inquiry_id='$inquiryId' AND p.seller_email='$farmerEmail' LIMIT 1");
                          $buyer = $buyerQuery ? mysqli_fetch_assoc($buyerQuery) : null;
                          $updateInquirySql = "UPDATE product_inquiries i INNER JOIN products p ON p.product_id=i.product_id SET i.status='$inquiryStatus', i.response='$response', i.updated_at=NOW() WHERE i.inquiry_id='$inquiryId' AND p.seller_email='$farmerEmail'";
                          if (mysqli_query($db, $updateInquirySql)) {
                            $buyerEmail = $buyer['user_email'] ?? $buyer['buyer_email'] ?? '';
                            if (filter_var($buyerEmail, FILTER_VALIDATE_EMAIL) && $responseText !== '') {
                              farmers_market_send_email($db, $buyerEmail, 'Response to your inquiry: ' . ($buyer['subject'] ?? 'Product inquiry'), "Your inquiry about " . ($buyer['product_name'] ?? 'a product') . " has received a response.\n\n" . $responseText);
                            }
                            if ($responseText !== '' && $responseText !== ($buyer['previous_response'] ?? '') && !empty($buyer['buyer_id'])) {
                              $notificationTitle = mysqli_real_escape_string($db, 'Your inquiry received a response');
                              $notificationMessage = mysqli_real_escape_string($db, 'The farmer responded to your inquiry about ' . ($buyer['product_name'] ?? 'a product') . ': ' . $responseText);
                              mysqli_query($db, "INSERT INTO farmer_notifications (farmer_id, notification_type, title, message) VALUES ('" . (int) $buyer['buyer_id'] . "', 'inquiry_response', '$notificationTitle', '$notificationMessage')");
                            }
                            $inquiryMessage = 'Inquiry response saved.';
                          } else {
                            $inquiryError = 'Unable to save the inquiry response.';
                          }
                        }

                        $inquirySql = "SELECT i.*, p.product_name, u.user_name, u.user_email FROM product_inquiries i INNER JOIN products p ON p.product_id=i.product_id LEFT JOIN users u ON u.user_id=i.buyer_id WHERE p.seller_email='$farmerEmail' ORDER BY i.created_at DESC";
                        $inquiryQuery = mysqli_query($db, $inquirySql);
                        $inquiryLabels = ['Pending', 'Responded', 'Resolved'];
                        $inquiryClasses = ['warning', 'info', 'success'];
                    ?>
                      <div class="container-fluid pb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <div>
                            <h4 class="fw-bold mb-1">Buyer Inquiries</h4>
                            <p class="text-muted mb-0">Read buyer questions and respond from your dashboard.</p>
                          </div>
                          <a href="farmerDashboard.php?do=Home" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>black</a>
                        </div>
                        <?php if ($inquiryMessage) { ?><div class="alert alert-success"><?php echo htmlspecialchars($inquiryMessage); ?></div><?php } ?>
                        <?php if ($inquiryError) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($inquiryError); ?></div><?php } ?>
                        <div class="card">
                          <div class="card-body">
                            <div class="table-responsive">
                              <table class="table table-hover align-middle mb-0">
                                <thead>
                                  <tr><th>Product</th><th>Buyer</th><th>Subject</th><th>Message</th><th>Status</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                  <?php if ($inquiryQuery && mysqli_num_rows($inquiryQuery) > 0) { while ($inquiry = mysqli_fetch_assoc($inquiryQuery)) { $inquiryStatus = (int) $inquiry['status']; ?>
                                    <tr>
                                      <td><?php echo htmlspecialchars($inquiry['product_name']); ?></td>
                                      <td><?php echo htmlspecialchars($inquiry['user_name'] ?: ($inquiry['user_email'] ?: $inquiry['buyer_email'])); ?></td>
                                      <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                      <td><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></td>
                                      <td><span class="badge bg-<?php echo $inquiryClasses[$inquiryStatus] ?? 'secondary'; ?>"><?php echo $inquiryLabels[$inquiryStatus] ?? 'Pending'; ?></span></td>
                                      <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inquiryModal<?php echo (int) $inquiry['inquiry_id']; ?>"><i class="fa-solid fa-reply me-1"></i>Respond</button>
                                        
                                        <!-- Response Modal -->
                                        <div class="modal fade" id="inquiryModal<?php echo (int) $inquiry['inquiry_id']; ?>" tabindex="-1" aria-hidden="true">
                                          <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h5 class="modal-title">Respond to Inquiry</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                              </div>
                                              <form method="post">
                                                <div class="modal-body">
                                                  <p><strong>Product:</strong> <?php echo htmlspecialchars($inquiry['product_name']); ?></p>
                                                  <p><strong>Original Message:</strong></p>
                                                  <p class="bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                                                  
                                                  <input type="hidden" name="inquiry_id" value="<?php echo (int) $inquiry['inquiry_id']; ?>">
                                                  <div class="mb-3">
                                                    <label for="status<?php echo (int) $inquiry['inquiry_id']; ?>" class="form-label">Status</label>
                                                    <select name="status" class="form-select" id="status<?php echo (int) $inquiry['inquiry_id']; ?>">
                                                      <option value="0" <?php echo $inquiryStatus === 0 ? 'selected' : ''; ?>>Pending</option>
                                                      <option value="1" <?php echo $inquiryStatus === 1 ? 'selected' : ''; ?>>Responded</option>
                                                      <option value="2" <?php echo $inquiryStatus === 2 ? 'selected' : ''; ?>>Resolved</option>
                                                    </select>
                                                  </div>
                                                  <div class="mb-3">
                                                    <label for="response<?php echo (int) $inquiry['inquiry_id']; ?>" class="form-label">Your Response</label>
                                                    <textarea name="response" class="form-control" id="response<?php echo (int) $inquiry['inquiry_id']; ?>" rows="4" placeholder="Write your response here..."><?php echo htmlspecialchars($inquiry['response'] ?? ''); ?></textarea>
                                                  </div>
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                  <button type="submit" name="update_inquiry" class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>Save Response</button>
                                                </div>
                                              </form>
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                    </tr>
                                  <?php } } else { ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No buyer inquiries yet.</td></tr>
                                  <?php } ?>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php }

                    else if ( $do == "Contact" ) { 

                        $contact_email = "";
                        $contact_phone = "";
                        $contact_address = "";

                        if (!empty($_SESSION['user_id'])) {
                          $sessionId = $_SESSION['user_id'];
                          $readUId_Sql = "SELECT user_email, user_phone, user_address FROM users WHERE status=1 AND user_id='$sessionId'";
                          $readUId_Query = mysqli_query($db, $readUId_Sql);

                          if ($row = mysqli_fetch_assoc($readUId_Query)) {
                            $contact_email = $row['user_email'];
                            $contact_phone = $row['user_phone'];
                            $contact_address = $row['user_address'];
                          }
                        }

                        ?>
                        <div class="container pb-5">
                          <div class="row">
                            <div class="col-lg-8 offset-lg-2">
                              <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                  <h4 class="fw-bold mb-1">Contact Information</h4>
                                  <p class="text-muted mb-0">Your account contact details</p>
                                </div>
                                <a href="farmerDashboard.php?do=Home" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                              </div>
                              
                              <div class="card">
                                <div class="card-body">
                                  <div class="row">
                                    <div class="col-lg-6">
                                      <div class="d-flex gap-3 mb-4">
                                        <div style="width: 48px; height: 48px; background: rgba(16, 184, 130, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                          <i class="fa-solid fa-envelope text-success fs-5"></i>
                                        </div>
                                        <div>
                                          <p class="text-muted mb-1">Email</p>
                                          <p class="fw-semibold"><?php echo htmlspecialchars($contact_email ?: 'Not available'); ?></p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-lg-6">
                                      <div class="d-flex gap-3 mb-4">
                                        <div style="width: 48px; height: 48px; background: rgba(16, 184, 130, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                          <i class="fa-solid fa-phone text-success fs-5"></i>
                                        </div>
                                        <div>
                                          <p class="text-muted mb-1">Phone</p>
                                          <p class="fw-semibold"><?php echo htmlspecialchars($contact_phone ?: 'Not available'); ?></p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="d-flex gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(16, 184, 130, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                      <i class="fa-solid fa-map-pin text-success fs-5"></i>
                                    </div>
                                    <div>
                                      <p class="text-muted mb-1">Address</p>
                                      <p class="fw-semibold"><?php echo htmlspecialchars($contact_address ?: 'Not available'); ?></p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                    <?php }

                    else if ( $do == "Profile" ) { ?>
                        <div class="container pb-5">
                          <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                              <h4 class="fw-bold mb-1">Profile Settings</h4>
                              <p class="text-muted mb-0">Update your account information</p>
                            </div>
                            <a href="farmerDashboard.php?do=Home" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                          </div>
                          <div class="card">
                            <div class="card-body">

                          <?php  

                            $sessionId = (int) ($_SESSION['user_id'] ?? 0);
                            $readUId_Sql = "SELECT u.*, f.farm_id, f.farm_name, f.farm_address AS farm_location, f.farm_latitude, f.farm_longitude, f.market_name, f.market_address, f.market_latitude, f.market_longitude, f.market_operating_days, f.market_hours, f.pickup_instructions, f.delivery_instructions, f.farm_about, f.farm_document FROM users u LEFT JOIN farmer f ON f.farm_email COLLATE utf8mb4_unicode_ci = u.user_email COLLATE utf8mb4_unicode_ci WHERE u.status=1 AND u.user_id='$sessionId'";
                            $readUId_Query = mysqli_query($db, $readUId_Sql);

                            while( $row = mysqli_fetch_assoc($readUId_Query) ) {
                              $user_id    = $row['user_id'];
                              $user_name    = $row['user_name'];
                              $user_email   = $row['user_email'];
                              $user_phone   = $row['user_phone'];
                              $user_address   = $row['user_address'];
                              $role       = $row['role'];
                              $status     = $row['status'];
                              $user_image   = $row['user_image'];
                              $join_date    = $row['join_date'];
                              $farm_id      = (int) ($row['farm_id'] ?? 0);
                              $farm_name    = $row['farm_name'] ?? '';
                              $farm_location = $row['farm_location'] ?? '';
                              $farm_latitude = $row['farm_latitude'] ?? '';
                              $farm_longitude = $row['farm_longitude'] ?? '';
                              $market_name = $row['market_name'] ?? '';
                              $market_address = $row['market_address'] ?? '';
                              $market_latitude = $row['market_latitude'] ?? '';
                              $market_longitude = $row['market_longitude'] ?? '';
                              $market_operating_days = $row['market_operating_days'] ?? '';
                              $market_hours = $row['market_hours'] ?? '';
                              $pickup_instructions = $row['pickup_instructions'] ?? '';
                              $delivery_instructions = $row['delivery_instructions'] ?? '';
                              $farm_about   = $row['farm_about'] ?? '';
                              $farm_document = $row['farm_document'] ?? '';

                              ?>

                              <form action="" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                  <div class="col-lg-4">
                                    <div class="mb-3">
                                      <label for="" class="form-label">Full Name</label>
                                      <input type="text" name="fname" class="form-control" required autocomplete="off" autofocus value="<?php echo $user_name; ?>">
                                    </div>

                                    <div class="mb-3">
                                      <label for="farmerEmailProfile" class="form-label">Email Address</label>
                                      <input type="email" id="farmerEmailProfile" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                      <label for="" class="form-label">Password</label>
                                      <input type="password" name="password" class="form-control" autocomplete="off" autofocus placeholder="password..">
                                    </div>

                                    <div class="mb-3">
                                      <label for="" class="form-label">Re-type Password</label>
                                      <input type="password" name="re_password" class="form-control" autocomplete="off" autofocus placeholder="re-type password..">
                                    </div>
                                  </div>

                                  <div class="col-lg-4">
                                    <div class="mb-3">
                                      <label for="" class="form-label">Phone No.</label>
                                      <input type="tel" name="phone" class="form-control" required autocomplete="off" autofocus  value="<?php echo $user_phone; ?>">
                                    </div>

                                    <div class="mb-3">
                                      <label for="" class="form-label">Address</label>
                                      <textarea name="address" class="form-control" autocomplete="off" autofocus cols="30" rows="7"><?php echo $user_address; ?></textarea>
                                    </div>

                                    <?php if ((int) $role === 2) { ?>
                                      <div class="mb-3">
                                        <label for="farmNameProfile" class="form-label">Farm name</label>
                                        <input type="text" name="farm_name" id="farmNameProfile" class="form-control" value="<?php echo htmlspecialchars($farm_name); ?>" required>
                                      </div>
                                      <div class="mb-3">
                                        <label for="farmLocationProfile" class="form-label">Farm location</label>
                                        <textarea name="farm_location" id="farmLocationProfile" class="form-control" rows="4" required><?php echo htmlspecialchars($farm_location); ?></textarea>
                                      </div>
                                      <div class="row g-2">
                                        <div class="col-md-6 mb-3">
                                          <label for="farmLatitudeProfile" class="form-label">Farm latitude</label>
                                          <input type="number" step="any" name="farm_latitude" id="farmLatitudeProfile" class="form-control" value="<?php echo htmlspecialchars((string) $farm_latitude); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label for="farmLongitudeProfile" class="form-label">Farm longitude</label>
                                          <input type="number" step="any" name="farm_longitude" id="farmLongitudeProfile" class="form-control" value="<?php echo htmlspecialchars((string) $farm_longitude); ?>">
                                        </div>
                                      </div>
                                      <div class="mb-3">
                                        <label for="marketNameProfile" class="form-label">Nearby market / collection point</label>
                                        <input type="text" name="market_name" id="marketNameProfile" class="form-control" value="<?php echo htmlspecialchars($market_name); ?>" placeholder="e.g. Mukono market or local buyers hub">
                                      </div>
                                      <div class="mb-3">
                                        <label for="marketAddressProfile" class="form-label">Market address</label>
                                        <textarea name="market_address" id="marketAddressProfile" class="form-control" rows="2" placeholder="Physical market or pickup site"><?php echo htmlspecialchars($market_address); ?></textarea>
                                      </div>
                                      <div class="row g-2">
                                        <div class="col-md-6 mb-3">
                                          <label for="marketLatitudeProfile" class="form-label">Market latitude</label>
                                          <input type="number" step="any" name="market_latitude" id="marketLatitudeProfile" class="form-control" value="<?php echo htmlspecialchars((string) $market_latitude); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label for="marketLongitudeProfile" class="form-label">Market longitude</label>
                                          <input type="number" step="any" name="market_longitude" id="marketLongitudeProfile" class="form-control" value="<?php echo htmlspecialchars((string) $market_longitude); ?>">
                                        </div>
                                      </div>
                                      <div class="row g-2">
                                        <div class="col-md-6 mb-3">
                                          <label for="marketOperatingDaysProfile" class="form-label">Market operating days</label>
                                          <input type="text" name="market_operating_days" id="marketOperatingDaysProfile" class="form-control" value="<?php echo htmlspecialchars($market_operating_days); ?>" placeholder="Mon-Sat">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label for="marketHoursProfile" class="form-label">Market hours</label>
                                          <input type="text" name="market_hours" id="marketHoursProfile" class="form-control" value="<?php echo htmlspecialchars($market_hours); ?>" placeholder="8:00 AM - 5:00 PM">
                                        </div>
                                      </div>
                                      <div class="mb-3">
                                        <label for="pickupInstructionsProfile" class="form-label">Pickup instructions</label>
                                        <textarea name="pickup_instructions" id="pickupInstructionsProfile" class="form-control" rows="3" placeholder="When and where buyers can collect their produce"><?php echo htmlspecialchars($pickup_instructions); ?></textarea>
                                      </div>
                                      <div class="mb-3">
                                        <label for="deliveryInstructionsProfile" class="form-label">Delivery information</label>
                                        <textarea name="delivery_instructions" id="deliveryInstructionsProfile" class="form-control" rows="3" placeholder="Available delivery areas, fees, and timing"><?php echo htmlspecialchars($delivery_instructions); ?></textarea>
                                      </div>
                                      <div class="mb-3">
                                        <label for="farmAboutProfile" class="form-label">Farm/Business Description</label>
                                        <textarea name="farm_about" id="farmAboutProfile" class="form-control" rows="5" placeholder="Describe your farm, products, practices, certifications, or business story. This helps customers understand what makes your farm special."><?php echo htmlspecialchars($farm_about); ?></textarea>
                                        <small class="text-muted">Maximum 1000 characters. Admins can view this information.</small>
                                      </div>
                                    <?php } ?>

                                    
                                  </div>

                                  <div class="col-lg-4">

                                    <div class="mb-3">
                                      <!-- User Role -->
                                      <input type="hidden" value="<?php echo $role; ?>" name="role">
                                    </div>

                                    <div class="mb-3">
                                      <!-- Status -->
                                      <input type="hidden" value="1" name="status">
                                    </div>

                                    <?php if ((int) $role === 2) { ?>
                                      <div class="mb-3">
                                        <label for="farmDocumentProfile" class="form-label">Farm document (optional)</label>
                                        <?php if ($farm_document) { ?><a href="<?php echo htmlspecialchars($farm_document); ?>" target="_blank" rel="noopener" class="d-block text-success mb-2"><i class="fa-solid fa-file-lines me-1"></i>View current document</a><?php } ?>
                                        <input type="file" name="farm_document" id="farmDocumentProfile" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.txt">
                                        <small class="text-muted">Optional. Maximum size: 10 MB.</small>
                                      </div>
                                    <?php } ?>

                                    <div class="d-grid gap-2">
                                      <input type="hidden" name="updateUserId" value="<?php echo $user_id; ?>">
                                      <input type="submit" name="updateUser" class="btn btn-success btn-lg" value="Save Profile">
                                    </div>
                                  </div>
                                </div>
                              </form>

                              <?php  
                                if (isset($_POST['updateUser']) && (int) $role === 2) {
                                  $profileFarmLocation = mysqli_real_escape_string($db, trim($_POST['farm_location'] ?? ''));
                                  $profileFarmName = mysqli_real_escape_string($db, trim($_POST['farm_name'] ?? ''));
                                  $profileFarmLatitude = isset($_POST['farm_latitude']) && $_POST['farm_latitude'] !== '' ? (float) $_POST['farm_latitude'] : 'NULL';
                                  $profileFarmLongitude = isset($_POST['farm_longitude']) && $_POST['farm_longitude'] !== '' ? (float) $_POST['farm_longitude'] : 'NULL';
                                  $profileMarketName = mysqli_real_escape_string($db, trim($_POST['market_name'] ?? ''));
                                  $profileMarketAddress = mysqli_real_escape_string($db, trim($_POST['market_address'] ?? ''));
                                  $profileMarketLatitude = isset($_POST['market_latitude']) && $_POST['market_latitude'] !== '' ? (float) $_POST['market_latitude'] : 'NULL';
                                  $profileMarketLongitude = isset($_POST['market_longitude']) && $_POST['market_longitude'] !== '' ? (float) $_POST['market_longitude'] : 'NULL';
                                  $profileMarketOperatingDays = mysqli_real_escape_string($db, trim($_POST['market_operating_days'] ?? ''));
                                  $profileMarketHours = mysqli_real_escape_string($db, trim($_POST['market_hours'] ?? ''));
                                  $profilePickupInstructions = mysqli_real_escape_string($db, trim($_POST['pickup_instructions'] ?? ''));
                                  $profileDeliveryInstructions = mysqli_real_escape_string($db, trim($_POST['delivery_instructions'] ?? ''));
                                  $profileFarmAbout = mysqli_real_escape_string($db, substr(trim($_POST['farm_about'] ?? ''), 0, 1000));
                                  $profileEmail = mysqli_real_escape_string($db, $_SESSION['email'] ?? $_SESSION['user_email'] ?? '');
                                  $farmDocumentSql = '';
                                  if (!empty($_FILES['farm_document']['name']) && $_FILES['farm_document']['error'] === UPLOAD_ERR_OK) {
                                    $documentExtension = strtolower(pathinfo($_FILES['farm_document']['name'], PATHINFO_EXTENSION));
                                    if (in_array($documentExtension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'txt'], true) && $_FILES['farm_document']['size'] <= 10 * 1024 * 1024) {
                                      $documentName = bin2hex(random_bytes(8)) . '.' . $documentExtension;
                                      $documentDirectory = __DIR__ . '/uploads/docs/' . md5($_SESSION['email'] ?? $_SESSION['user_email']) . '/';
                                      if (!is_dir($documentDirectory)) {
                                        mkdir($documentDirectory, 0755, true);
                                      }
                                      if (move_uploaded_file($_FILES['farm_document']['tmp_name'], $documentDirectory . $documentName)) {
                                        $farmDocumentSql = ", farm_document='uploads/docs/" . md5($_SESSION['email'] ?? $_SESSION['user_email']) . "/$documentName'";
                                      }
                                    }
                                  }
                                  $farmExistsQuery = mysqli_query($db, "SELECT farm_id FROM farmer WHERE farm_email COLLATE utf8mb4_unicode_ci='$profileEmail' COLLATE utf8mb4_unicode_ci LIMIT 1");
                                  if ($farmExistsQuery && mysqli_num_rows($farmExistsQuery) > 0) {
                                    mysqli_query($db, "UPDATE farmer SET farm_name='$profileFarmName', farm_phone='" . mysqli_real_escape_string($db, $_POST['phone'] ?? $user_phone) . "', farm_address='$profileFarmLocation', farm_latitude=" . ($profileFarmLatitude === 'NULL' ? 'NULL' : $profileFarmLatitude) . ", farm_longitude=" . ($profileFarmLongitude === 'NULL' ? 'NULL' : $profileFarmLongitude) . ", market_name='$profileMarketName', market_address='$profileMarketAddress', market_latitude=" . ($profileMarketLatitude === 'NULL' ? 'NULL' : $profileMarketLatitude) . ", market_longitude=" . ($profileMarketLongitude === 'NULL' ? 'NULL' : $profileMarketLongitude) . ", market_operating_days='$profileMarketOperatingDays', market_hours='$profileMarketHours', pickup_instructions='$profilePickupInstructions', delivery_instructions='$profileDeliveryInstructions', farm_about='$profileFarmAbout'$farmDocumentSql WHERE farm_email COLLATE utf8mb4_unicode_ci='$profileEmail' COLLATE utf8mb4_unicode_ci");
                                  } else {
                                    mysqli_query($db, "INSERT INTO farmer (farm_name, farm_phone, farm_email, farm_address, farm_latitude, farm_longitude, market_name, market_address, market_latitude, market_longitude, market_operating_days, market_hours, pickup_instructions, delivery_instructions, farm_about, farm_document, status, join_date) VALUES ('$profileFarmName', '" . mysqli_real_escape_string($db, $_POST['phone'] ?? $user_phone) . "', '$profileEmail', '$profileFarmLocation', " . ($profileFarmLatitude === 'NULL' ? 'NULL' : $profileFarmLatitude) . ", " . ($profileFarmLongitude === 'NULL' ? 'NULL' : $profileFarmLongitude) . ", '$profileMarketName', '$profileMarketAddress', " . ($profileMarketLatitude === 'NULL' ? 'NULL' : $profileMarketLatitude) . ", " . ($profileMarketLongitude === 'NULL' ? 'NULL' : $profileMarketLongitude) . ", '$profileMarketOperatingDays', '$profileMarketHours', '$profilePickupInstructions', '$profileDeliveryInstructions', '$profileFarmAbout', '', 1, NOW())");
                                  }
                                }

                                if (isset($_POST['updateUser'])) {
                                $updateUserId   = mysqli_real_escape_string($db, $_POST['updateUserId']);
                                $fname      = mysqli_real_escape_string($db, $_POST['fname']);
                                $password     = mysqli_real_escape_string($db, $_POST['password']);
                                $re_password  = mysqli_real_escape_string($db, $_POST['re_password']);
                                $phone      = mysqli_real_escape_string($db, $_POST['phone']);
                                $address    = mysqli_real_escape_string($db, $_POST['address']);
                                $role       = mysqli_real_escape_string($db, $_POST['role']);
                                $status     = mysqli_real_escape_string($db, $_POST['status']);
                                
                                $image      = mysqli_real_escape_string($db, $_FILES['image']['name'] ?? '');
                                $temp_img     = $_FILES['image']['tmp_name'] ?? '';

                                // Only Password & Only Image Change
                                if (!empty($password) && !empty($image)) {
                                  if ($password == $re_password) {
                                    $hassedPass = sha1($password);

                                    // Delete Old Image From  Folder
                                    $oldImgSql = "SELECT * FROM users WHERE user_id='$updateUserId'";
                                    $oldImageQuery = mysqli_query($db, $oldImgSql);

                                    while ( $row = mysqli_fetch_assoc($oldImageQuery) ) {
                                      $oldImage   = $row['user_image'];
                                      unlink("admin/assets/images/farmer/" . $oldImage);
                                    }

                                    $img = rand(0, 999999) . "_" . $image;
                                    move_uploaded_file($temp_img, 'admin/assets/images/farmer/' . $img);

                                    $updateUserSql = "UPDATE users SET user_name='$fname', user_password='$hassedPass', user_phone='$phone', user_address='$address', role='$role', status='$status', user_image='$img' WHERE user_id='$updateUserId'";
                                    $upateUserQuery = mysqli_query($db, $updateUserSql);

                                    if ($upateUserQuery) {
                                      header("Location: farmerDashboard.php?do=Profile");
                                    }
                                    else {
                                      die ("Mysql Error." .mysqli_error($db) );
                                    }
                                  }
                                  else { ?>
                                    <div class="alert alert-warning text-center" role="alert">
                                      Sorry! please password and repassword use same input.
                                    </div>
                                  <?php }
                                }

                                // Not Password & Only Image Chnage
                                else if (empty($password) && !empty($image)) {

                                  // Delete Old Image From  Folder
                                    $oldImgSql = "SELECT * FROM users WHERE user_id='$updateUserId'";
                                    $oldImageQuery = mysqli_query($db, $oldImgSql);

                                    while ( $row = mysqli_fetch_assoc($oldImageQuery) ) {
                                      $oldImage   = $row['user_image'];
                                      unlink("admin/assets/images/farmer/" . $oldImage);
                                    }

                                  $img = rand(0, 999999) . "_" . $image;
                                  move_uploaded_file($temp_img, 'admin/assets/images/farmer/' . $img);

                                  $updateUserSql = "UPDATE users SET user_name='$fname', user_phone='$phone', user_address='$address', role='$role', status='$status', user_image='$img' WHERE user_id='$updateUserId'";
                                  $upateUserQuery = mysqli_query($db, $updateUserSql);

                                  if ($upateUserQuery) {
                                    header("Location: farmerDashboard.php?do=Profile");
                                  }
                                  else {
                                    die ("Mysql Error." .mysqli_error($db) );
                                  }

                                }

                                // Only Password & Not Image Chnage
                                else if (!empty($password) && empty($image)) {
                                  if ($password == $re_password) {
                                    $hassedPass = sha1($password);

                                    $updateUserSql = "UPDATE users SET user_name='$fname', user_password='$hassedPass', user_phone='$phone', user_address='$address', role='$role', status='$status' WHERE user_id='$updateUserId'";
                                    $upateUserQuery = mysqli_query($db, $updateUserSql);

                                    if ($upateUserQuery) {
                                      header("Location: farmerDashboard.php?do=Profile");
                                    }
                                    else {
                                      die ("Mysql Error." .mysqli_error($db) );
                                    }
                                  }
                                  else { ?>
                                    <div class="alert alert-warning text-center" role="alert">
                                      Sorry! please password and repassword use same input.
                                    </div>
                                  <?php }
                                }

                                // Not Password & Not Image Chnage
                                else if (empty($password) && empty($image)) {

                                  $updateUserSql = "UPDATE users SET user_name='$fname', user_phone='$phone', user_address='$address', role='$role', status='$status' WHERE user_id='$updateUserId'";
                                  $upateUserQuery = mysqli_query($db, $updateUserSql);

                                  if ($upateUserQuery) {
                                    header("Location: farmerDashboard.php?do=Profile");
                                  }
                                  else {
                                    die ("Mysql Error." .mysqli_error($db) );
                                  }

                                }

                      }
                      ?>

                              <?php


                            }

                            ?>

                            </div>
                          </div>
                        </div>
                    <?php }

                    else if ( $do == "Support" ) { ?>
                        <div class="container pb-5">
                          <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                              <h4 class="fw-bold mb-1">Support</h4>
                              <p class="text-muted mb-0">Send a message to the market support team</p>
                            </div>
                            <a href="farmerDashboard.php?do=Home" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                          </div>

                          <div class="row justify-content-center">
                            <!-- for form -->
                            <div class="col-lg-8">
                              <div class="card">
                                <div class="card-body">

                                <?php  
                                  if(isset($_SESSION['msg'])) {
                                      $message = $_SESSION['msg'];
                                      unset($_SESSION['msg']);
                                      ?>
                                      <div class="alert alert-info text-center" role="alert">
                                      <?php echo $message; ?>
                                    </div>
                                      <?php
                                      
                                  }
                                ?>

                                <form action="" method="POST" enctype="multipart/form-data">
                                  <div class="mb-3">
                                    <label for="subject">Subject of the Message</label>
                                    <input type="text" name="title" class="form-control" id="subject" aria-describedby="subject" placeholder="subject.." required autocomplete="off">
                                  </div>
                                  <div class="mb-3">
                                    <label for="message">Message</label>
                                    <textarea name="message" class="form-control" id="message.." rows="5" placeholder="message" required autocomplete="off"></textarea>
                                  </div>

                                  <?php  
                                    if (!empty($_SESSION['user_id'])) { ?>

                                      <input type="hidden" name="status" value="0">
                                  <input type="hidden" name="useremail" value="<?php echo $_SESSION['email']; ?>">
                                  <input type="hidden" name="userphone" value="<?php echo $_SESSION['phone']; ?>">
                                  <input type="submit" name="addUser" class="btn btn-primary btn-lg btn-block">

                                    <?php }
                                  ?>

                                  
                                </form>

                                <?php  
                                  if (isset($_POST['addUser'])) {
                                    $title    = mysqli_real_escape_string($db, $_POST['title']);
                                    $message  = mysqli_real_escape_string($db, $_POST['message']);
                                    $status   = mysqli_real_escape_string($db, $_POST['status']);
                                    $useremail  = mysqli_real_escape_string($db, $_POST['useremail']);
                                    $userphone  = mysqli_real_escape_string($db, $_POST['userphone']);

                                    $sql = "INSERT INTO comments ( user_id, user_number, subject, comments, status, cmt_date ) VALUES('$useremail', '$userphone', '$title', '$message', '$status', now())";
                                    $query = mysqli_query( $db, $sql );

                                    if ($query) {
                                      farmers_market_notify_admin_support($db, $_POST['useremail'], $_POST['userphone'], $_POST['title'], $_POST['message']);
                                      $_SESSION['msg'] = "We Received your message. After of some times letter we will call & email you. Thank you for with us.";
                                      header("Location: farmerDashboard.php?do=Support");
                                    }
                                    else {
                                      die("Mysql Error." . mysqli_error($db));
                                    }
                                  }
                                ?>

                                </div>
                              </div>
                            </div>
                            <!-- for form -->
                          </div>
                        </div>
                    <?php }

                    else if ( $do == "Add" ) { ?>
                      <div class="container pb-5">
                        <div class="row">
                          <div class="col-lg-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                              <div>
                                <h4 class="fw-bold mb-1">Add New Product</h4>
                                <p class="text-muted mb-0">Create a new product listing</p>
                              </div>
                              <a href="farmerDashboard.php?do=Manage" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                            </div>

                            <!-- ########## START: FORM ########## -->
                            <form action="farmerDashboard.php?do=Store" method="POST" enctype="multipart/form-data" class="card">
                              <div class="card-body">
                              <div class="row">
                                <div class="col-lg-6">
                                  <div class="mb-3">
                                    <label for=""  class="form-label">Product Name</label>
                                    <input type="text" name="catName" class="form-control" placeholder="enter product name" required autocomplete="off">
                                  </div>

                                  <div class="mb-3">
                                    <label for=""  class="form-label">Price per unit (UGX)</label>
                                    <input type="text" name="price" class="form-control" placeholder="enter price amount" required autocomplete="off">
                                  </div>

                                  <div class="mb-3">
                                    <label for="productUnit" class="form-label">Unit</label>
                                    <select name="product_unit" class="form-select" id="productUnit" required>
                                      <option value="kilogram">Kilogram (kg)</option>
                                      <option value="litre">Litre (L)</option>
                                      <option value="gram">Gram (g)</option>
                                      <option value="piece">Piece</option>
                                      <option value="each">Each</option>
                                    </select>
                                  </div>

                                  <div class="mb-3">
                                    <label for="" class="form-label">Available Quantity</label>
                                    <input type="number" name="stock_quantity" class="form-control" min="0" value="0" required>
                                  </div>

                                  <div class="mb-3">
                                    <label for="harvestDate" class="form-label">Expected Harvest Date (optional)</label>
                                    <input type="date" name="harvest_date" class="form-control" id="harvestDate">
                                  </div>

                                  <div class="mb-3">
                                    <label for="seasonalAvailability" class="form-label">Seasonal Availability (optional)</label>
                                    <input type="text" name="seasonal_availability" class="form-control" id="seasonalAvailability" placeholder="e.g. June to August or Year-round">
                                  </div>

                                  <div class="mb-3">
                                    <label for="lowStockThreshold" class="form-label">Low-stock alert at</label>
                                    <input type="number" name="low_stock_threshold" class="form-control" id="lowStockThreshold" min="0" value="5" required>
                                  </div>

                                  <div class="mb-3">
                                    <label for=""  class="form-label">Select the Parent Category [ If Any ]</label>
                                    <select class="form-select" name="is_parent">
                                      <option value="1">Please select the parent category</option>
                                      <?php  
                                        $sql = "SELECT * FROM category WHERE is_parent=1 AND status=1 ORDER BY cat_name ASC ";
                                        $query = mysqli_query($db, $sql);

                                        while( $row = mysqli_fetch_assoc($query) ){
                                          $cat_id     = $row['cat_id'];
                                        $cat_name     = $row['cat_name'];
                                          ?>

                                          <option value="<?php echo $cat_id; ?>"><?php echo $cat_name; ?></option>

                                          <?php
                                        }
                                      ?>
                                    </select>
                                  </div>
                                  

                                  <div class="mb-3">
                                    <label for=""  class="form-label">Category Image</label>
                                    <input class="form-control" type="file" name="image" type="file">
                                  </div>
                                </div>
                                <div class="col-lg-6">
                                  <div class="mb-3">
                                    <label for="" class="form-label">Category Description</label>
                                    <textarea name="desc" class="form-control" id="" cols="30" rows="8"></textarea>
                                  </div>

                                  <div class="mb-3">
                                    <input type="hidden" value="2" name="status">
                                    <input type="hidden" name="seller_email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-check">
                                      <input type="checkbox" class="form-check-input" name="is_negotiable" value="1" id="farmerIsNegotiable">
                                      <label class="form-check-label" for="farmerIsNegotiable">Price is negotiable</label>
                                    </div>
                                  </div>

                                  <div class="mb-3">
                                    <div class="d-grid gap-2">
                                      <input type="submit" name="addCategory" class="btn btn-success btn-lg" value="Add Product">
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>                          
                            </form>
                            <!-- ########## END: FORM ########## -->

                          </div>
                        </div>
                      </div>
                    <?php }

                    else if ( $do == "Store" ) {
                      if (isset($_POST['addCategory'])) {
                        $productName = mysqli_real_escape_string($db, trim($_POST['catName']));
                        $price = mysqli_real_escape_string($db, trim($_POST['price']));
                        $allowedUnits = ['kilogram', 'litre', 'gram', 'piece', 'each'];
                        $product_unit = in_array($_POST['product_unit'] ?? '', $allowedUnits, true) ? $_POST['product_unit'] : 'kilogram';
                        $product_unit = mysqli_real_escape_string($db, $product_unit);
                        $categoryId = !empty($_POST['is_parent']) ? (int) $_POST['is_parent'] : null;
                        $status = 2;
                        $seller_email = mysqli_real_escape_string($db, $_SESSION['email'] ?? '');
                        $desc = mysqli_real_escape_string($db, trim($_POST['desc']));
                        $stock_quantity = max(0, (int) ($_POST['stock_quantity'] ?? 0));
                        $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
                        $harvest_date = trim($_POST['harvest_date'] ?? '');
                        $harvestDateValue = $harvest_date !== '' ? "'" . mysqli_real_escape_string($db, $harvest_date) . "'" : 'NULL';
                        $seasonal_availability = mysqli_real_escape_string($db, trim($_POST['seasonal_availability'] ?? ''));
                        $low_stock_threshold = max(0, (int) ($_POST['low_stock_threshold'] ?? 5));
                        $image = '';

                        if (!empty($_FILES['image']['name'])) {
                          $uploadDir = 'admin/assets/images/products/';
                          if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                          }
                          $imageName = time() . '_' . basename($_FILES['image']['name']);
                          if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                            $image = mysqli_real_escape_string($db, $imageName);
                          }
                        }

                        $categoryValue = $categoryId ? "'$categoryId'" : 'NULL';
                        $addSql = "INSERT INTO products (product_name, description, category_id, price, product_unit, is_negotiable, view_count, harvest_date, seasonal_availability, stock_quantity, low_stock_threshold, seller_email, image, status, join_date) VALUES ('$productName', '$desc', $categoryValue, '$price', '$product_unit', '$is_negotiable', 0, $harvestDateValue, '$seasonal_availability', '$stock_quantity', '$low_stock_threshold', '$seller_email', '$image', '$status', NOW())";
                        $addQuery = mysqli_query($db, $addSql);

                        if ($addQuery) {
                          header("Location: farmerDashboard.php?do=Manage");
                        }
                        else {
                          die ("Mysql Error." .mysqli_error($db) );
                        }

                      }

                    }

                    else if ( $do == "Edit" ) { 
                      if (isset($_GET['uId'])) {
                        $upId = (int) $_GET['uId'];
                        $sellerId = mysqli_real_escape_string($db, $_SESSION['email'] ?? '');
                        $upReadSql = "SELECT p.*, c.cat_name FROM products p LEFT JOIN category c ON c.cat_id = p.category_id WHERE p.product_id='$upId' AND p.seller_email='$sellerId' LIMIT 1";
                        $upReadQuery = mysqli_query($db, $upReadSql);

                        while ( $row = mysqli_fetch_assoc($upReadQuery) ) {
                          $cat_id     = $row['product_id'];
                            $cat_name     = $row['product_name'];
                            $cat_desc     = $row['description'];
                            $is_parent    = $row['category_id'];
                            $status     = $row['status'];
                            $join_date    = $row['join_date'];
                            $cat_image    = $row['image'];
                            $price      = $row['price'];
                            $product_unit = $row['product_unit'] ?? 'kilogram';
                            $stock_quantity = (int) $row['stock_quantity'];
                            $is_negotiable = (int) ($row['is_negotiable'] ?? 0);
                            $harvest_date = $row['harvest_date'] ?? '';
                            $seasonal_availability = $row['seasonal_availability'] ?? '';
                            $low_stock_threshold = (int) ($row['low_stock_threshold'] ?? 5);
                            $seller_email   = $row['seller_email'];
                            ?>
                              <div class="container pb-5">
                              <div class="row">
                                <div class="col-lg-12">
                                  <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                      <h4 class="fw-bold mb-1">Update Product</h4>
                                      <p class="text-muted mb-0">Edit product details and pricing</p>
                                    </div>
                                    <a href="farmerDashboard.php?do=Manage" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                                  </div>

                                  <!-- ########## START: FORM ########## -->
                                  <form action="farmerDashboard.php?do=Update" method="POST" enctype="multipart/form-data" class="card">
                                    <div class="card-body">
                                    <div class="row">
                                      <div class="col-lg-6">
                                        <div class="mb-3">
                                          <label for="" class="form-label">Product Name</label>
                                          <input type="text" name="catName" class="form-control" placeholder="enter product name" required autocomplete="off" value="<?php echo htmlspecialchars($cat_name); ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="" class="form-label">Price per unit (UGX)</label>
                                          <input type="number" step="0.01" name="price" class="form-control" placeholder="enter price amount" required autocomplete="off" value="<?php echo htmlspecialchars($price); ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="editProductUnit" class="form-label">Unit</label>
                                          <select name="product_unit" class="form-select" id="editProductUnit" required>
                                            <option value="kilogram" <?php echo $product_unit === 'kilogram' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                            <option value="litre" <?php echo $product_unit === 'litre' ? 'selected' : ''; ?>>Litre (L)</option>
                                            <option value="gram" <?php echo $product_unit === 'gram' ? 'selected' : ''; ?>>Gram (g)</option>
                                            <option value="piece" <?php echo $product_unit === 'piece' ? 'selected' : ''; ?>>Piece</option>
                                            <option value="each" <?php echo $product_unit === 'each' ? 'selected' : ''; ?>>Each</option>
                                          </select>
                                        </div>

                                        <div class="mb-3">
                                          <label for="" class="form-label">Available Quantity</label>
                                          <input type="number" name="stock_quantity" class="form-control" min="0" required value="<?php echo $stock_quantity; ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="editHarvestDate" class="form-label">Expected Harvest Date (optional)</label>
                                          <input type="date" name="harvest_date" class="form-control" id="editHarvestDate" value="<?php echo htmlspecialchars($harvest_date); ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="editSeasonalAvailability" class="form-label">Seasonal Availability (optional)</label>
                                          <input type="text" name="seasonal_availability" class="form-control" id="editSeasonalAvailability" value="<?php echo htmlspecialchars($seasonal_availability); ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="editLowStockThreshold" class="form-label">Low-stock alert at</label>
                                          <input type="number" name="low_stock_threshold" class="form-control" id="editLowStockThreshold" min="0" value="<?php echo $low_stock_threshold; ?>" required>
                                        </div>

                                        <div class="mb-3">
                                          <label for="" class="form-label">Select the Parent Category [ If Any ]</label>
                                          <select class="form-control" name="is_parent">
                                            <option value="1">Please select the parent category</option>
                                            <?php  
                                              $p_sql = "SELECT * FROM category WHERE is_parent=1  ORDER BY cat_name ASC ";
                                              $p_query = mysqli_query($db, $p_sql);

                                              while( $row = mysqli_fetch_assoc($p_query) ){
                                                $p_cat_id     = $row['cat_id'];
                                              $p_cat_name   = $row['cat_name'];
                                              ?>

                                              <option value="<?php echo $p_cat_id; ?>" <?php if( $p_cat_id == $is_parent ){ echo "selected"; } ?> ><?php echo htmlspecialchars($p_cat_name); ?></option>

                                              <?php
                                              }
                                            ?>
                                          </select>
                                        </div>
                                        

                                        <div class="mb-3">
                                          <label for="" class="form-label">Product Image</label>
                                          <br><br>

                                          <?php  
                                                if (!empty($cat_image)) {
                                              echo '<img src="admin/assets/images/products/' . htmlspecialchars($cat_image) . '" style="width: 100%; height: 200px; object-fit: cover;">';
                                            }
                                            else {
                                              echo 'No Image Found';
                                            }
                                              ?>
                                              <br><br>
                                          <input class="form-control" type="file" name="image" accept="image/*">
                                        </div>
                                      </div>
                                      <div class="col-lg-6">
                                        <div class="mb-3">
                                          <label for="" class="form-label">Product Description</label>
                                          <textarea name="desc" class="form-control" id="" cols="30" rows="8"><?php echo htmlspecialchars($cat_desc); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                          <label for="" class="form-label">Status Update</label>
                                          <select class="form-select" aria-label="Default select example" name="status">
                                            <option value="2" <?php if ($status == 2) echo "selected"; ?>>Pending</option>
                                            <option value="1" <?php if ($status == 1) echo "selected"; ?>>Active</option>
                                            <option value="0" <?php if ($status == 0) echo "selected"; ?>>Inactive</option>
                                          </select>
                                        </div>

                                        <div class="mb-3">
                                          <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_negotiable" value="1" id="editFarmerIsNegotiable" <?php echo $is_negotiable ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="editFarmerIsNegotiable">Price is negotiable</label>
                                          </div>
                                        </div>

                                        <div class="mb-3">
                                          <div class="d-grid gap-2">
                                            <input type="hidden" name="updateCategoryId" value="<?php echo $cat_id; ?>">
                                            <input type="submit" name="updateCategory" class="btn btn-success btn-lg" value="Update Product">
                                            
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                    </div>
                                  </form>
                                  </form>
                                  <!-- ########## END: FORM ########## -->

                                </div>
                              </div>
                            </div>
                            <?php
                        }
                      }
                      
                    }

                    else if ( $do == "Update" ) {
                      if (isset($_POST['updateCategory'])) {
                        $updateProductId = (int) $_POST['updateCategoryId'];
                        $productName = mysqli_real_escape_string($db, trim($_POST['catName']));
                        $categoryId = !empty($_POST['is_parent']) ? (int) $_POST['is_parent'] : null;
                        $status = (int) $_POST['status'];
                        $desc = mysqli_real_escape_string($db, trim($_POST['desc']));
                        $price = mysqli_real_escape_string($db, trim($_POST['price']));
                        $allowedUnits = ['kilogram', 'litre', 'gram', 'piece', 'each'];
                        $product_unit = in_array($_POST['product_unit'] ?? '', $allowedUnits, true) ? $_POST['product_unit'] : 'kilogram';
                        $product_unit = mysqli_real_escape_string($db, $product_unit);
                        $stock_quantity = max(0, (int) ($_POST['stock_quantity'] ?? 0));
                        $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
                        $harvest_date = trim($_POST['harvest_date'] ?? '');
                        $harvestDateValue = $harvest_date !== '' ? "harvest_date='" . mysqli_real_escape_string($db, $harvest_date) . "'," : 'harvest_date=NULL,';
                        $seasonal_availability = mysqli_real_escape_string($db, trim($_POST['seasonal_availability'] ?? ''));
                        $low_stock_threshold = max(0, (int) ($_POST['low_stock_threshold'] ?? 5));
                        $sellerId = mysqli_real_escape_string($db, $_SESSION['email'] ?? '');
                        $categoryValue = $categoryId ? "category_id='$categoryId'," : 'category_id=NULL,';
                        $setImageSql = '';

                        if (!empty($_FILES['image']['name'])) {
                          $oldImageQuery = mysqli_query($db, "SELECT image FROM products WHERE product_id='$updateProductId' AND seller_email='$sellerId' LIMIT 1");
                          $oldImageRow = mysqli_fetch_assoc($oldImageQuery);
                          if ($oldImageRow && !empty($oldImageRow['image'])) {
                            $oldImagePath = 'admin/assets/images/products/' . $oldImageRow['image'];
                            if (file_exists($oldImagePath)) {
                              unlink($oldImagePath);
                            }
                          }

                          $uploadDir = 'admin/assets/images/products/';
                          if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                          }
                          $imageName = time() . '_' . basename($_FILES['image']['name']);
                          if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
                            $imageName = mysqli_real_escape_string($db, $imageName);
                            $setImageSql = "image='$imageName',";
                          }
                        }

                        $upSql = "UPDATE products SET product_name='$productName', description='$desc', $categoryValue $setImageSql price='$price', product_unit='$product_unit', stock_quantity='$stock_quantity', is_negotiable='$is_negotiable', $harvestDateValue seasonal_availability='$seasonal_availability', low_stock_threshold='$low_stock_threshold', status='$status' WHERE product_id='$updateProductId' AND seller_email='$sellerId'";
                        $updateQuery = mysqli_query($db, $upSql);

                        if ($updateQuery) {
                          header("Location: farmerDashboard.php?do=Manage");
                          exit;
                        }
                        die("Mysql Error." . mysqli_error($db));

                        
                      }
                    }

                    else if ( $do == "Trash" ) {
                      if (isset($_GET['tId'])) {
                        $trushId = (int) $_GET['tId'];
                        $sellerId = mysqli_real_escape_string($db, $_SESSION['email'] ?? '');
                        $trushSql = "UPDATE products SET status=0 WHERE product_id='$trushId' AND seller_email='$sellerId'";
                        $trushQuery = mysqli_query( $db, $trushSql );

                        if ($trushQuery) {
                          header("Location: farmerDashboard.php?do=Manage");
                        }
                        else {
                          die("mysql error" . mysqli_error($db));
                        }

                      }
                    }

                    else if ( $do == "ManageTrash" ) { ?>
                      <div class="container pb-5">
                        <div class="row">
                          <div class="col-lg-12">
                            <h4 class="text-uppercase">Trash Manage All Products</h4>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                              <a href="farmerDashboard.php?do=Add" class="btn btn-dark">Add New Product</a>
                              <a href="farmerDashboard.php?do=Manage" class="btn btn-dark">All Products</a>
                            </div>
                            <hr>

                            <!-- START: TABLE -->
                            <div class="table-responsive" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;">
                              <table id="example" class="table table-striped table-hover table-bordered">
                                <thead class="thead-dark">
                                  <tr>
                                    <th scope="col">#Sl.</th>
                                    <th scope="col">Product Image</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Price (Taka)</th>
                                    <th scope="col">Category Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Join Date</th>
                                    <th scope="col">Action</th>
                                  </tr>
                                </thead>

                                <tbody>
                                  <?php  
                                    if (!empty($_SESSION['email'])) {
                                      $sellerId = $_SESSION['email'];

                                      $sellerReadSql = "SELECT p.*, c.cat_name FROM products p LEFT JOIN category c ON c.cat_id = p.category_id WHERE p.status=0 AND p.seller_email='$sellerId' ORDER BY p.product_name ASC";
                                      $sellerReadQuery = mysqli_query( $db, $sellerReadSql );
                                      $sellerCount = mysqli_num_rows($sellerReadQuery);

                                      if ( $sellerCount == 0 ) { ?>
                                        <div class="alert alert-info text-center" role="alert">
                                        Sorry! No Product Found!.
                                      </div>
                                      <?php }

                                      else {
                                        $i = 0;

                                        while ($row = mysqli_fetch_assoc($sellerReadQuery)) {
                                          $cat_id     = $row['product_id'];
                                          $cat_name     = $row['product_name'];
                                          $cat_desc     = $row['description'];
                                          $is_parent    = $row['category_id'];
                                          $status     = $row['status'];
                                          $join_date    = $row['join_date'];
                                          $cat_image    = $row['image'];
                                          $price      = $row['price'];        
                                          $seller_email   = $row['seller_email'];       
                                          $i++;
                                          ?>

                                          <tr>
                                            <th scope="row" class="text-center"><?php echo $i; ?></th>
                                            <td class="text-center">
                                              <?php  
                                                if (!empty($cat_image)) {
                                              echo '<img src="admin/assets/images/products/' . htmlspecialchars($cat_image) . '" style="width: 60px">';
                                            }
                                            else {
                                              echo '<img src="admin/assets/images/category/default.jpg" style="width: 60px">';
                                            }
                                              ?>
                                            </td>
                                            <td class="text-center"><?php echo $cat_name; ?></td>
                                            <td class="text-center"><?php echo $price; ?></td>
                                            <td class="text-center">
                                          <?php  
                                                if (!empty($row['cat_name'])) {
                                                  echo '<span class="badge text-bg-secondary">' . htmlspecialchars($row['cat_name']) . '</span>';
                                                } else {
                                                  echo '<span class="badge text-bg-secondary">Uncategorized</span>';
                                                }

                                              ?>
                                        </td>
                                            <td class="text-center">
                                              <?php  
                                                if ($status == 1) { ?>
                                                  <span class="badge text-bg-success">ACTIVE</span>
                                                <?php }
                                                else if ($status == 0) { ?>
                                                  <span class="badge text-bg-danger">INACTIVE</span>
                                                <?php }
                                                else if ($status == 2) { ?>
                                                  <span class="badge text-bg-warning">PENDING</span>
                                                <?php }
                                              ?>
                                            </td>
                                            <td class="text-center"><?php echo $join_date; ?></td>
                                            <td>
                                            <div class="action-btn">
                                              <ul>
                                                  <li>
                                                    <a href="farmerDashboard.php?do=Edit&uId=<?php echo $cat_id; ?>"><i class="fa-regular fa-pen-to-square edit"></i></a>
                                                  </li>
                                                  <li>
                                                    <a href=""  data-bs-toggle="modal" data-bs-target="#uId<?php echo $cat_id; ?>"><i class="fa-regular fa-trash-can trush"></i></a>
                                                  </li>
                                              </ul>
                                            </div>

                                            <!-- Modal Start -->
                                            <div class="modal fade" id="uId<?php echo $cat_id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                              <div class="modal-dialog">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h3 class="modal-title" id="exampleModalLabel">Are You Sure?? To Delete <i class="fa-regular fa-face-frown"></i><br> <span style="color: green;"><?php echo $cat_name; ?></span></h3>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                      <span aria-hidden="true">&times;</span>
                                                    </button>
                                                  </div>
                                                  <div class="modal-body">
                                                    <div class="modal-btn">
                                                      <a href="farmerDashboard.php?do=Delete&DId=<?php echo $cat_id; ?>"class="btn btn-danger me-3">Delete</a>
                                                      <a href="" class="btn btn-success" data-bs-dismiss="modal">Close</a>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                            <!-- Modal End -->
                                           </td>
                                          </tr>

                                          <?php
                                        }
                                      }






                                      
                                    }
                                  ?>
                                  
                                </tbody>
                              </table>
                            </div>
                            <!-- END: TABLE -->

                          </div>
                        </div>
                      </div>
                    <?php }

                    else if ( $do == "Delete" ) {
                      if (isset($_GET['DId'])) {
                        $deleteId = (int) $_GET['DId'];
                        $sellerId = mysqli_real_escape_string($db, $_SESSION['email'] ?? '');
                        $imageQuery = mysqli_query($db, "SELECT image FROM products WHERE product_id='$deleteId' AND seller_email='$sellerId' LIMIT 1");
                        $imageRow = mysqli_fetch_assoc($imageQuery);
                        if ($imageRow && !empty($imageRow['image'])) {
                          $imagePath = 'admin/assets/images/products/' . $imageRow['image'];
                          if (file_exists($imagePath)) {
                            unlink($imagePath);
                          }
                        }
                        $deleteSql = "DELETE FROM products WHERE product_id='$deleteId' AND seller_email='$sellerId'";
                        $deleteQuery = mysqli_query($db, $deleteSql);

                        if ($deleteQuery) {
                          header("Location: farmerDashboard.php?do=Manage");
                        }
                        else {
                          die("Mysql Error." . mysqli_error($db));
                        }
                      }
                    }

                    else if ( $do == "AddDoc" ) { 
                      // Document upload handler
                      if (!empty($_SESSION['email'])) {
                        $farmerEmail = $_SESSION['email'];
                        $uploadDir = __DIR__ . '/uploads/docs/' . md5($farmerEmail) . '/';
                        
                        if (!is_dir($uploadDir)) {
                          @mkdir($uploadDir, 0755, true);
                        }
                        
                        $msg = '';
                        $msgType = 'info';
                        
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_doc'])) {
                          if (!empty($_FILES['doc_file']['name'])) {
                            $fileName = basename($_FILES['doc_file']['name']);
                            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx', 'txt'];
                            $allowedMime = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                            $detectedMime = function_exists('finfo_open') ? (function () { $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $_FILES['doc_file']['tmp_name']); finfo_close($finfo); return $mime; })() : $_FILES['doc_file']['type'];
                            
                            if ($_FILES['doc_file']['size'] <= 10 * 1024 * 1024 && in_array($fileExt, $allowedExt, true) && in_array($detectedMime, $allowedMime, true)) {
                              $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                              $target = $uploadDir . $newFileName;
                              
                              if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $target)) {
                                $msg = 'Document uploaded successfully!';
                                $msgType = 'success';
                              } else {
                                $msg = 'Failed to upload document.';
                                $msgType = 'danger';
                              }
                            } else {
                              $msg = 'Invalid file. Allowed formats: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX, TXT. Maximum size: 10 MB.';
                              $msgType = 'danger';
                            }
                          } else {
                            $msg = 'Please select a file to upload.';
                            $msgType = 'warning';
                          }
                        }
                        ?>
                        <div class="container pb-5">
                          <div class="row">
                            <div class="col-lg-8 mx-auto">
                              <div style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;">
                                <h4 class="text-uppercase mb-4">Add Documents</h4>
                                
                                <?php if ($msg): ?>
                                  <div class="alert alert-<?php echo $msgType; ?>" role="alert">
                                    <?php echo htmlspecialchars($msg); ?>
                                  </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data">
                                  <div class="mb-3">
                                    <label for="doc_file" class="form-label">Select Document (PDF, Images, or Documents)</label>
                                    <input type="file" class="form-control" id="doc_file" name="doc_file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.txt" required>
                                    <small class="text-muted">Supported formats: PDF, JPG, PNG, GIF, WEBP, DOC, DOCX, TXT. Maximum size: 10 MB.</small>
                                  </div>
                                  <button type="submit" name="upload_doc" class="btn btn-primary">Upload Document</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                        <?php
                      } else {
                        echo '<div class="alert alert-warning">Please log in to add documents.</div>';
                      }
                    }

                    else if ( $do == "ViewDoc" ) {
                      // View documents handler
                      if (!empty($_SESSION['email'])) {
                        $farmerEmail = $_SESSION['email'];
                        $uploadDir = __DIR__ . '/uploads/docs/' . md5($farmerEmail) . '/';
                        $baseDir = realpath(__DIR__ . '/uploads/docs/');
                        
                        if (!is_dir($uploadDir)) {
                          @mkdir($uploadDir, 0755, true);
                        }
                        
                        // Handle file deletion
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doc'])) {
                          $fileName = basename($_POST['file'] ?? '');
                          $filePath = realpath($uploadDir . $fileName);
                          
                          if ($filePath && strpos($filePath, realpath($uploadDir)) === 0 && file_exists($filePath)) {
                            unlink($filePath);
                            header('Location: farmerDashboard.php?do=ViewDoc');
                            exit;
                          }
                        }
                        
                        // Get all documents
                        $documents = [];
                        if (is_dir($uploadDir)) {
                          $allFiles = glob($uploadDir . '*');
                          if ($allFiles) {
                            usort($allFiles, function($a, $b) {
                              return filemtime($b) - filemtime($a);
                            });
                            $documents = $allFiles;
                          }
                        }
                        ?>
                        <div class="container pb-5">
                          <div class="row">
                            <div class="col-lg-12">
                              <div style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                  <h4 class="text-uppercase mb-0">My Documents</h4>
                                  <a href="farmerDashboard.php?do=AddDoc" class="btn btn-primary btn-sm">+ Add Document</a>
                                </div>
                                
                                <?php if (count($documents) > 0): ?>
                                  <div class="table-responsive">
                                    <table class="table table-hover">
                                      <thead class="table-dark">
                                        <tr>
                                          <th>File Name</th>
                                          <th>Type</th>
                                          <th>Size</th>
                                          <th>Uploaded</th>
                                          <th>Action</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php foreach ($documents as $doc): 
                                          $fileName = basename($doc);
                                          $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                          $fileSize = filesize($doc);
                                          $fileSizeKB = round($fileSize / 1024, 2);
                                          $uploadTime = filemtime($doc);
                                          $uploadDate = date('M d, Y H:i', $uploadTime);
                                          
                                          // Determine file type icon
                                          $icon = 'fa-file';
                                          if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                            $icon = 'fa-image';
                                          } elseif ($fileExt === 'pdf') {
                                            $icon = 'fa-file-pdf';
                                          } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                            $icon = 'fa-file-word';
                                          }
                                        ?>
                                        <tr>
                                          <td>
                                            <i class="fa-solid <?php echo $icon; ?>"></i> 
                                            <?php echo htmlspecialchars($fileName); ?>
                                          </td>
                                          <td><span class="badge bg-info"><?php echo strtoupper($fileExt); ?></span></td>
                                          <td><?php echo $fileSizeKB; ?> KB</td>
                                          <td><?php echo $uploadDate; ?></td>
                                          <td>
                                            <div class="document-actions">
                                              <a href="uploads/docs/<?php echo md5($farmerEmail); ?>/<?php echo rawurlencode($fileName); ?>"
                                                 class="btn btn-xs btn-outline-primary" target="_blank" rel="noopener" title="View document">
                                                <i class="fa-solid fa-eye"></i><span>View</span>
                                              </a>
                                              <a href="uploads/docs/<?php echo md5($farmerEmail); ?>/<?php echo rawurlencode($fileName); ?>" class="btn btn-xs btn-outline-secondary" download title="Download document"><i class="fa-solid fa-download"></i><span>Download</span></a>
                                              <form method="post" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                                <input type="hidden" name="file" value="<?php echo $fileName; ?>">
                                                <button type="submit" name="delete_doc" class="btn btn-xs btn-outline-danger" title="Delete document">
                                                  <i class="fa-solid fa-trash"></i><span>Delete</span>
                                                </button>
                                              </form>
                                            </div>
                                          </td>
                                        </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                <?php else: ?>
                                  <div class="alert alert-info text-center">
                                    <p>No documents uploaded yet.</p>
                                    <a href="farmerDashboard.php?do=AddDoc" class="btn btn-primary">Upload Your First Document</a>
                                  </div>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        </div>
                        <?php
                      } else {
                        echo '<div class="alert alert-warning">Please log in to view documents.</div>';
                      }
                    }

                  ?>

                 
                  
                </div>
                
            </main>
        </div>
    </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <script>
      new DataTable('#example', {
          layout: {
              topStart: {
              buttons: [
                'copy', 'csv', 'excel',
                {
                  extend: 'pdf',
                  exportOptions: {
                    columns: function(columnIndex, columnData, headerNode) {
                      return $(headerNode).text().trim().toLowerCase() !== 'action';
                    }
                  }
                },
                'print'
              ]
              }
          }
      });
    </script>
    <script src="assets/js/farmer-dashboard.js"></script>



    <?php  
      ob_end_flush();
    ?>
  </body>
</html>
