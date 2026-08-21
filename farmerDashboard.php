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

    <script src="https://kit.fontawesome.com/0c66e46c25.js" crossorigin="anonymous"></script>

    <!-- DATATABLE CSS LINK -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">


    <style>
      body, .main_body {
        background-color: #E4E9F7;
      }
      #sidebar-nav {
          width: 200px;
      }

      a.list-group-item.border-end-0.d-inline-block.text-truncate {
          background: #11101D;
          color: #fff;
          border: 0;
          line-height: 4em;
      }

      a.list-group-item.border-end-0.d-inline-block.text-truncate:hover{
        border-bottom: 1px solid #fff;        
        color: #fff;
        border-radius: 5px; 
        transition: 0.2s ease-in-out;
        background: #1d1b31;
      }

      a.border.rounded-3.p-1.text-decoration-none {
          color: #11101D;
      }
    </style>
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
                        <a href="farmer_orders.php" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-solid fa-cart-shopping"></i> <span>&nbsp;Orders</span></a>
                        <a href="farmer_inquiries.php" class="list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="fa-regular fa-message"></i> <span>&nbsp;Buyer Inquiries</span></a>
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
                                  echo '<img src="admin/assets/images/seller/' . $user_image . '" style="width: 50px;margin: 0px 10px;">';
                                }
                                else {
                                  echo '<img src="admin/assets/images/seller/default.png" style="width: 50px;margin: 0px 10px;">';
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

                    else { ?>
                      <li class="dropdown">
                        <a class="dropdown-item dropdown-toggle" href="login.php">
                          <i class="fa-solid fa-arrow-right-to-bracket px-1"></i> Login
                        </a>
                      </li>

                      <li class="dropdown">
                        <a class="dropdown-item dropdown-toggle" href="register.php">
                          <i class="fa-regular fa-address-card px-1"></i> Regsiter
                        </a>
                      </li>

                    <?php }
                  ?>
                  <!-- For users login or nor -->
                </div>

                <div class="p-3">

                 

                  <?php

                    $do = isset( $_GET['do'] ) ? $_GET['do'] : "Manage";

                    if ( $do == "Manage" ) { ?>
                      <div class="container pb-5">
                        <div class="row">
                          <div class="col-lg-12">
                              <h4 class="text-uppercase">Manage All Products</h4>

                              <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="farmerDashboard.php?do=Add" class="btn btn-dark">Add New Product</a>
                                <a href="bulk_upload.php" class="btn btn-success">Bulk Upload</a>
                                <a href="farmerDashboard.php?do=ManageTrash" class="btn btn-danger">Trash</a>
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
                                    <th scope="col">Price (Ugx)</th>
                                    <th scope="col">Availability</th>
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
                                          $stock_quantity = (int) ($row['stock_quantity'] ?? 0);
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
                                            <td class="text-center"><?php echo $stock_quantity > 0 ? '<span class="badge text-bg-success">IN STOCK</span>' : '<span class="badge text-bg-danger">OUT OF STOCK</span>'; ?></td>
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
                                              <div class="modal-dialog" >
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h3 class="modal-title" id="exampleModalLabel">Are You Sure?? To Move <i class="fa-regular fa-face-frown"></i><br> <span style="color: green;"><?php echo $cat_name; ?></span> Trash folder!!</h3>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                  </div>
                                                  <div class="modal-body">
                                                    <div class="modal-btn">
                                                      <a href="farmerDashboard.php?do=Trash&tId=<?php echo $cat_id; ?>"class="btn btn-danger me-3">Trash</a>
                                                      <a href="" class="btn btn-success" data-dismiss="modal">Close</a>
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

                    else if ( $do == "Home" ) {

                        $farmerEmail = $_SESSION['email'] ?? '';
                        $topProducts = [];
                        $customerRequests = [];
                        $newRequestCount = 0;
                        $totalOrdersCount = 0;
                        $lowStockCount = 0;

                        if (!empty($farmerEmail)) {
                          $topProductSql = "SELECT c.cat_id, c.cat_name, c.cat_image, COALESCE(SUM(COALESCE(o.quantity, 1)), 0) AS demand_count " .
                                           "FROM category c " .
                                           "LEFT JOIN order_list o ON c.cat_id=o.or_category " .
                                           "WHERE c.seller_email='$farmerEmail' " .
                                           "GROUP BY c.cat_id " .
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
                        }

                        ?>
                        <div class="page-header pt-3 mb-4">
                          <h2 class="text-center">Farmer Dashboard</h2>
                          <p class="text-center text-muted">Your farm's most in-demand products and customer reachouts at a glance.</p>
                        </div>

                        <div class="row g-4 mb-4">
                          <div class="col-xl-4 col-md-6">
                            <div class="card shadow-sm border-start border-success border-4 h-100">
                              <div class="card-body">
                                <h6 class="text-uppercase text-muted">New customer requests</h6>
                                <h3 class="fw-bold mb-2"><?php echo number_format($newRequestCount); ?></h3>
                                <p class="mb-0 text-muted">Customers who have selected your products</p>
                              </div>
                            </div>
                          </div>
                          <div class="col-xl-4 col-md-6">
                            <div class="card shadow-sm border-start border-primary border-4 h-100">
                              <div class="card-body">
                                <h6 class="text-uppercase text-muted">Total orders</h6>
                                <h3 class="fw-bold mb-2"><?php echo number_format($totalOrdersCount); ?></h3>
                                <p class="mb-0 text-muted">All orders placed for your products</p>
                              </div>
                            </div>
                          </div>
                          <div class="col-xl-4 col-md-12">
                            <div class="card shadow-sm border-start border-warning border-4 h-100">
                              <div class="card-body">
                                <h6 class="text-uppercase text-muted">Demand trend</h6>
                                <p class="mb-3 text-muted">How in demand your catalog is this week.</p>
                                <div class="progress" style="height: 12px;">
                                  <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo min(100, $newRequestCount * 15); ?>%;" aria-valuenow="<?php echo $newRequestCount; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="row g-4">
                          <div class="col-xl-7">
                            <div class="card shadow-sm h-100">
                              <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <div>
                                  <h5 class="mb-0"><i class="fa-solid fa-chart-simple me-2"></i>Top Products in Demand</h5>
                                  <small class="text-light">Updated from your order history.</small>
                                </div>
                              </div>
                              <div class="card-body p-0">
                                <div class="table-responsive">
                                  <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                      <tr>
                                        <th>Product</th>
                                        <th>Orders</th>
                                        <th>Status</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <?php if (count($topProducts) > 0) {
                                        foreach ($topProducts as $product) {
                                          $demand = (int) $product['demand_count'];
                                          $statusLabel = $demand > 5 ? 'Hot' : ($demand > 0 ? 'Rising' : 'New');
                                          $statusClass = $demand > 5 ? 'badge bg-danger' : ($demand > 0 ? 'badge bg-success' : 'badge bg-secondary');
                                          ?>
                                          <tr>
                                            <td>
                                              <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 overflow-hidden" style="width: 50px; height: 50px; background: #f4f5f8; display:flex; align-items:center; justify-content:center;">
                                                  <?php if (!empty($product['cat_image'])) { ?>
                                                    <img src="admin/assets/images/category/<?php echo htmlspecialchars($product['cat_image']); ?>" alt="<?php echo htmlspecialchars($product['cat_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                  <?php } else { ?>
                                                    <i class="fa-solid fa-box-open fa-lg text-muted"></i>
                                                  <?php } ?>
                                                </div>
                                                <div>
                                                  <h6 class="mb-1"><?php echo htmlspecialchars($product['cat_name']); ?></h6>
                                                  <small class="text-muted">ID <?php echo htmlspecialchars($product['cat_id']); ?></small>
                                                </div>
                                              </div>
                                            </td>
                                            <td><?php echo number_format($demand); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                                          </tr>
                                        <?php }
                                      } else { ?>
                                        <tr>
                                          <td colspan="3" class="text-center py-4 text-muted">No demand data available yet.</td>
                                        </tr>
                                      <?php } ?>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-xl-5">
                            <div class="card shadow-sm h-100">
                              <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa-solid fa-bell me-2"></i>Notifications <?php if ($lowStockCount > 0) { ?><span class="badge bg-warning text-dark ms-2"><?php echo $lowStockCount; ?> low stock</span><?php } ?></h5>
                                <a href="farmer_orders.php" class="btn btn-sm btn-light mt-2">Manage Orders</a>
                              </div>
                              <div class="card-body">
                                <?php if (count($customerRequests) > 0) { ?>
                                  <div class="list-group">
                                    <?php foreach ($customerRequests as $request) { ?>
                                      <div class="list-group-item list-group-item-action mb-3 rounded-3 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-start">
                                          <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($request['or_name']); ?></h6>
                                            <p class="mb-1 text-muted small">Product: <?php echo htmlspecialchars($request['product_name'] ?: 'Unknown'); ?></p>
                                            <p class="mb-1 text-muted small">Quantity: <?php echo number_format((int) ($request['quantity'] ?? 1)); ?> • Total: UGX <?php echo number_format((float) $request['price'], 2); ?></p>
                                            <p class="mb-0 text-muted small">Customer: <?php echo htmlspecialchars($request['user_id'] ?: 'Guest'); ?> • <?php echo htmlspecialchars($request['user_phone'] ?: 'No phone'); ?></p>
                                          </div>
                                          <span class="badge bg-light text-dark"><?php echo date('M j', strtotime($request['join_date'])); ?></span>
                                        </div>
                                        <?php if (filter_var($request['customer_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                          <a href="mailto:<?php echo htmlspecialchars($request['customer_email']); ?>?subject=<?php echo rawurlencode('Reply about ' . ($request['product_name'] ?: $request['or_name'])); ?>&body=<?php echo rawurlencode('Hello, regarding your interest in ' . ($request['product_name'] ?: $request['or_name']) . ':'); ?>" class="btn btn-sm btn-outline-primary mt-3">Reply Customer</a>
                                        <?php } ?>
                                      </div>
                                    <?php } ?>
                                  </div>
                                <?php } else { ?>
                                  <div class="text-center py-5">
                                    <i class="fa-solid fa-comments fa-2x text-muted mb-3"></i>
                                    <p class="mb-0 text-muted">No new inquiries or orders. New customer activity will appear here.</p>
                                  </div>
                                <?php } ?>
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
                        <div class="page-header pt-3" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;  margin: 0px auto;">
                          <h2 class="text-center pb-5">Contact Info</h2>

                          <div>
                            <p><i class="fa-solid fa-envelope"></i> &nbsp; <?php echo htmlspecialchars($contact_email ?: 'Not available'); ?></p>
                            <p><i class="fa-solid fa-phone"></i> &nbsp; <?php echo htmlspecialchars($contact_phone ?: 'Not available'); ?></p>
                            <p><i class="fa-solid fa-map-pin"></i> &nbsp; <?php echo htmlspecialchars($contact_address ?: 'Not available'); ?></p>
                          </div>
                        </div>
                    <?php }

                    else if ( $do == "Profile" ) { ?>
                        <div class="page-header pt-3" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;  margin: 0px auto;">
                          <h2 class="text-center pb-5">Profile Update</h2>

                          <?php  

                            $sessionId =  $_SESSION['user_id'];
                            $readUId_Sql = "SELECT * FROM users WHERE status=1 AND user_id='$sessionId'";
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

                              ?>

                              <form action="" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                  <div class="col-lg-4">
                                    <div class="mb-3">
                                      <label for="" class="form-label">Full Name</label>
                                      <input type="text" name="fname" class="form-control" required autocomplete="off" autofocus value="<?php echo $user_name; ?>">
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

                                    <div class="mb-3">
                                      <label for="">Image</label>
                                      <br>
                                      <?php  
                                            if (!empty($user_image)) {
                                          echo '<img src="admin/assets/images/seller/' . $user_image . '" style="width: 100%; height: 200px;">';
                                        }
                                        else {
                                          echo "Sorry! No Image Uploaded.";
                                        }
                                          ?>  
                                          <br><br>
                                      <input type="file" name="image" class="form-control">
                                    </div>

                                    <div class="d-grid gap-2">
                                      <input type="hidden" name="updateUserId" value="<?php echo $user_id; ?>">
                                      <input type="submit" name="updateUser" class="btn btn-dark btn-lg btn-block">
                                    </div>
                                  </div>
                                </div>
                              </form>

                              <?php  
                                if (isset($_POST['updateUser'])) {
                                $updateUserId   = mysqli_real_escape_string($db, $_POST['updateUserId']);
                                $fname      = mysqli_real_escape_string($db, $_POST['fname']);
                                $password     = mysqli_real_escape_string($db, $_POST['password']);
                                $re_password  = mysqli_real_escape_string($db, $_POST['re_password']);
                                $phone      = mysqli_real_escape_string($db, $_POST['phone']);
                                $address    = mysqli_real_escape_string($db, $_POST['address']);
                                $role       = mysqli_real_escape_string($db, $_POST['role']);
                                $status     = mysqli_real_escape_string($db, $_POST['status']);
                                
                                $image      = mysqli_real_escape_string($db,$_FILES['image']['name']);
                                $temp_img     = $_FILES['image']['tmp_name'];

                                // Only Password & Only Image Change
                                if (!empty($password) && !empty($image)) {
                                  if ($password == $re_password) {
                                    $hassedPass = sha1($password);

                                    // Delete Old Image From  Folder
                                    $oldImgSql = "SELECT * FROM users WHERE user_id='$updateUserId'";
                                    $oldImageQuery = mysqli_query($db, $oldImgSql);

                                    while ( $row = mysqli_fetch_assoc($oldImageQuery) ) {
                                      $oldImage   = $row['user_image'];
                                      unlink("admin/assets/images/seller/$img" . $oldImage);
                                    }

                                    $img = rand(0, 999999) . "_" . $image;
                                    move_uploaded_file($temp_img, 'admin/assets/images/seller/' . $img);

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
                                      unlink("admin/assets/images/seller/$img" . $oldImage);
                                    }

                                  $img = rand(0, 999999) . "_" . $image;
                                  move_uploaded_file($temp_img, 'admin/assets/images/seller/' . $img);

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
                    <?php }

                    else if ( $do == "Support" ) { ?>
                        <div class="page-header pt-3" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;  margin: 0px auto;">
                          <h2 class="text-center pb-5">Support</h2>

                          <div>
                            <!-- for form -->
                            <div class="col-lg-6" style="margin: 0px auto;">
                              <div class="contact_form" style="box-shadow: 1px 10px 15px #ccc; border-top: 4px solid #08c; border-radius: 5px; color: #000; background: #F7F7F7; font-size: 16px; padding: 34px;">

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
                                    if (empty($_SESSION['user_id'])) {
                                      ?>
                                      <a href="login.php">Login to reserve your service</a>
                                      <?php
                                    }
                                    else { ?>

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
                            <!-- for form -->
                          </div>
                        </div>
                    <?php }

                    else if ( $do == "Add" ) { ?>
                      <div class="container pb-5">
                        <div class="row">
                          <div class="col-lg-12">
                            <h4 class="text-uppercase">ADD NEW PRODUCT</h4>
                            <hr>

                            <!-- ########## START: FORM ########## -->
                            <form action="farmerDashboard.php?do=Store" method="POST" enctype="multipart/form-data" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;">
                              <div class="row">
                                <div class="col-lg-6">
                                  <div class="mb-3">
                                    <label for=""  class="form-label">Product Name</label>
                                    <input type="text" name="catName" class="form-control" placeholder="enter product name" required autocomplete="off">
                                  </div>

                                  <div class="mb-3">
                                    <label for=""  class="form-label">Price</label>
                                    <input type="text" name="price" class="form-control" placeholder="enter price amount" required autocomplete="off">
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
                                    <input type="hidden" name="seller_email" value="<?php echo $_SESSION['email']; ?>">
                                    <div class="form-check">
                                      <input type="checkbox" class="form-check-input" name="is_negotiable" value="1" id="farmerIsNegotiable">
                                      <label class="form-check-label" for="farmerIsNegotiable">Price is negotiable</label>
                                    </div>
                                  </div>

                                  <div class="mb-3">
                                    <div class="d-grid gap-2">
                                      <input type="submit" name="addCategory" class="btn btn-dark btn-lg btn-block" value="Add New Product">
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
                        $addSql = "INSERT INTO products (product_name, description, category_id, price, is_negotiable, view_count, harvest_date, seasonal_availability, stock_quantity, low_stock_threshold, seller_email, image, status, join_date) VALUES ('$productName', '$desc', $categoryValue, '$price', '$is_negotiable', 0, $harvestDateValue, '$seasonal_availability', '$stock_quantity', '$low_stock_threshold', '$seller_email', '$image', '$status', NOW())";
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
                                  <h4 class="text-uppercase">UPDATE PRODUCT</h4>
                                  <hr>

                                  <!-- ########## START: FORM ########## -->
                                  <form action="farmerDashboard.php?do=Update" method="POST" enctype="multipart/form-data" style="padding: 30px; box-shadow: 0px 1px 8px #ccc; border-radius: 10px;">
                                    <div class="row">
                                      <div class="col-lg-6">
                                        <div class="mb-3">
                                          <label for="" class="form-label">Product Name</label>
                                          <input type="text" name="catName" class="form-control" placeholder="enter product name" required autocomplete="off" value="<?php echo htmlspecialchars($cat_name); ?>">
                                        </div>

                                        <div class="mb-3">
                                          <label for="" class="form-label">Price (Ugx)</label>
                                          <input type="number" step="0.01" name="price" class="form-control" placeholder="enter price amount" required autocomplete="off" value="<?php echo htmlspecialchars($price); ?>">
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
                                            <input type="submit" name="updateCategory" class="btn btn-dark btn-lg btn-block" value="Update Product">
                                            
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

                        $upSql = "UPDATE products SET product_name='$productName', description='$desc', $categoryValue $setImageSql price='$price', stock_quantity='$stock_quantity', is_negotiable='$is_negotiable', $harvestDateValue seasonal_availability='$seasonal_availability', low_stock_threshold='$low_stock_threshold', status='$status' WHERE product_id='$updateProductId' AND seller_email='$sellerId'";
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
                                            <a href="uploads/docs/<?php echo md5($farmerEmail); ?>/<?php echo rawurlencode($fileName); ?>"
                                               class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                              <i class="fa-solid fa-eye"></i> View
                                            </a>
                                            <a href="uploads/docs/<?php echo md5($farmerEmail); ?>/<?php echo rawurlencode($fileName); ?>" class="btn btn-sm btn-outline-secondary" download><i class="fa-solid fa-download"></i> Download</a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                              <input type="hidden" name="file" value="<?php echo $fileName; ?>">
                                              <button type="submit" name="delete_doc" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                              </button>
                                            </form>
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



    <?php  
      ob_end_flush();
    ?>
  </body>
</html>
