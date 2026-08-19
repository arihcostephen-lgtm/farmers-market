<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
    <?php
      $do = isset($_GET['do']) ? $_GET['do'] : 'Manage';

      if ($do == 'Manage') {
    ?>
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Products Management</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">Manage Products</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 text-uppercase">Manage All Products</h6>
        <div>
          <a href="products.php?do=Add" class="btn btn-success btn-sm me-2">Add Product</a>
          <a href="products.php?do=ManageTrash" class="btn btn-secondary btn-sm">Trash</a>
        </div>
      </div>
      <hr>

      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="example3" class="table table-striped table-hover table-bordered">
              <thead class="table-dark">
                <tr>
                  <th>#</th>
                  <th>Image</th>
                  <th>Product Name</th>
                  <th>Price</th>
                  <th>Seller</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Added</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $productSql = "SELECT p.*, c.cat_name, c.cat_image FROM products p LEFT JOIN category c ON p.category_id = c.cat_id WHERE p.status != 0 ORDER BY p.join_date DESC";
                  $productQuery = mysqli_query($db, $productSql);
                  $productCount = mysqli_num_rows($productQuery);

                  if ($productCount == 0) {
                ?>
                  <tr>
                    <td colspan="9" class="text-center text-warning">No products found in the database.</td>
                  </tr>
                <?php
                  } else {
                    $i = 0;
                    while ($row = mysqli_fetch_assoc($productQuery)) {
                      $product_id = $row['product_id'];
                      $product_name = $row['product_name'];
                      $price = $row['price'];
                      $seller_email = $row['seller_email'];
                      $status = (int) $row['status'];
                      $join_date = $row['join_date'];
                      $product_image = $row['image'] ?: $row['cat_image'];
                      $assigned_category_name = $row['cat_name'];
                      $i++;
                ?>
                  <tr>
                    <th scope="row"><?php echo $i; ?></th>
                    <td>
                      <?php
                        if (!empty($product_image)) {
                          echo '<img src="assets/images/products/' . htmlspecialchars($product_image) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                        } else {
                          echo '<img src="assets/images/category/default.png" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                        }
                      ?>
                    </td>
                    <td><?php echo htmlspecialchars($product_name); ?></td>
                    <td><?php echo number_format((float) $price, 2); ?> Tk</td>
                    <td><?php echo !empty($seller_email) ? htmlspecialchars($seller_email) : 'N/A'; ?></td>
                    <td>
                      <?php
                        if (!empty($assigned_category_name)) {
                          echo '<span class="badge text-bg-info text-dark">' . htmlspecialchars($assigned_category_name) . '</span>';
                        } else {
                          echo '<span class="badge text-bg-secondary">Unassigned</span>';
                        }
                      ?>
                    </td>
                    <td>
                      <?php
                        if ($status == 1) {
                          echo '<span class="badge text-bg-success">ACTIVE</span>';
                        } else if ($status == 2) {
                          echo '<span class="badge text-bg-warning text-dark">PENDING</span>';
                        } else {
                          echo '<span class="badge text-bg-danger">INACTIVE</span>';
                        }
                      ?>
                    </td>
                    <td><?php echo htmlspecialchars($join_date); ?></td>
                    <td>
                      <div class="d-flex gap-2">
                        <a href="products.php?do=Edit&uId=<?php echo $product_id; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                        <a href="" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product_id; ?>" title="Trash"><i class="fa-regular fa-trash-can"></i></a>
                      </div>

                      <div class="modal fade" id="deleteModal<?php echo $product_id; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title text-dark">Move to Trash?</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark">
                              Are you sure you want to move <strong><?php echo htmlspecialchars($product_name); ?></strong> to the trash?
                            </div>
                            <div class="modal-footer">
                              <a href="products.php?do=Trash&tId=<?php echo $product_id; ?>" class="btn btn-danger">Trash</a>
                              <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                <?php
                    }
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php
      }
      elseif ($do == 'Add') {
    ?>
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Products Management</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item"><a href="products.php?do=Manage">Products</a></li>
              <li class="breadcrumb-item active" aria-current="page">Add Product</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h6 class="mb-3 text-uppercase">Add New Product</h6>
          <form action="products.php?do=Store" method="POST" enctype="multipart/form-data">
            <div class="row">
              <div class="col-lg-6">
                <div class="mb-3">
                  <label class="form-label">Product Name</label>
                  <input type="text" name="productName" class="form-control" placeholder="Enter product name" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Price (Tk)</label>
                  <input type="number" step="0.01" name="price" class="form-control" placeholder="Enter product price" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Assigned Category</label>
                  <select class="form-select" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php
                      $cats = mysqli_query($db, "SELECT * FROM category WHERE status != 0 ORDER BY cat_name ASC");
                      while ($categoryRow = mysqli_fetch_assoc($cats)) {
                        echo '<option value="' . (int) $categoryRow['cat_id'] . '">' . htmlspecialchars($categoryRow['cat_name']) . '</option>';
                      }
                    ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Seller Email</label>
                  <input type="email" name="seller_email" class="form-control" placeholder="Enter seller email">
                </div>
              </div>

              <div class="col-lg-6">
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="desc" rows="6" class="form-control" placeholder="Write product description..."></textarea>
                </div>

                <div class="mb-3">
                  <label class="form-label">Product Image</label>
                  <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select class="form-select" name="status">
                    <option value="1">Active</option>
                    <option value="2">Pending</option>
                  </select>
                </div>

                <div class="d-grid gap-2">
                  <button type="submit" name="addProduct" class="btn btn-success btn-lg">Save Product</button>
                  <a href="products.php?do=Manage" class="btn btn-outline-secondary">Cancel</a>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?php
      }
      elseif ($do == 'Store') {
          if (isset($_POST['addProduct'])) {
          $productName = mysqli_real_escape_string($db, trim($_POST['productName']));
          $price = mysqli_real_escape_string($db, trim($_POST['price']));
          $desc = mysqli_real_escape_string($db, trim($_POST['desc']));
          $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
          $status = (int) $_POST['status'];
          $seller_email = mysqli_real_escape_string($db, trim($_POST['seller_email']));

          $image = '';
          if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../admin/assets/images/products/';
            if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
            }
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $target = $uploadDir . $imageName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
              $image = $imageName;
            }
          }

          $image = mysqli_real_escape_string($db, $image);
          $categoryValue = $category_id ? "'$category_id'" : 'NULL';
          // Ensure `products` table exists (in case DB migration wasn't applied)
          $createProductsSql = "CREATE TABLE IF NOT EXISTS products (
            product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            category_id INT UNSIGNED DEFAULT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            seller_email VARCHAR(150) DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
          @mysqli_query($db, $createProductsSql);
          $insertSql = "INSERT INTO products (product_name, description, category_id, price, seller_email, image, status, join_date) VALUES ('$productName', '$desc', $categoryValue, '$price', '$seller_email', '$image', '$status', NOW())";
          $insertQuery = mysqli_query($db, $insertSql);

          if ($insertQuery) {
            if ($status === 1) {
              farmers_market_notify_customers_new_product($db, $_POST['productName'], $_POST['desc'], $_POST['price']);
            }
            header('Location: products.php?do=Manage');
            exit;
          } else {
            die('MySQL Error: ' . mysqli_error($db));
          }
        }
      }
      elseif ($do == 'Edit') {
        if (isset($_GET['uId'])) {
          $upId = (int) $_GET['uId'];
          $readSql = "SELECT * FROM products WHERE product_id = '$upId' LIMIT 1";
          $readQuery = mysqli_query($db, $readSql);
          if (mysqli_num_rows($readQuery) > 0) {
            $row = mysqli_fetch_assoc($readQuery);
            $product_id = $row['product_id'];
            $product_name = $row['product_name'];
            $product_desc = $row['description'];
            $category_selected = (int) $row['category_id'];
            $status = (int) $row['status'];
            $product_image = $row['image'];
            $price = $row['price'];
            $seller_email = $row['seller_email'];
    ?>
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
              <div class="breadcrumb-title pe-3">Products Management</div>
              <div class="ps-3">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="products.php?do=Manage">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
                  </ol>
                </nav>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <h6 class="mb-3 text-uppercase">Update Product</h6>
                <form action="products.php?do=Update" method="POST" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="productName" class="form-control" value="<?php echo htmlspecialchars($product_name); ?>" required>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Price (Tk)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" required>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Assigned Category</label>
                        <select class="form-select" name="category_id">
                          <option value="">Select a category</option>
                          <?php
                            $assignedCats = mysqli_query($db, "SELECT * FROM category WHERE status != 0 ORDER BY cat_name ASC");
                            while ($assignedRow = mysqli_fetch_assoc($assignedCats)) {
                              $selected = ($assignedRow['cat_id'] == $category_selected) ? 'selected' : '';
                              echo '<option value="' . (int) $assignedRow['cat_id'] . '" ' . $selected . '>' . htmlspecialchars($assignedRow['cat_name']) . '</option>';
                            }
                          ?>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Seller Email</label>
                        <input type="email" name="seller_email" class="form-control" value="<?php echo htmlspecialchars($seller_email); ?>">
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="desc" rows="6" class="form-control"><?php echo htmlspecialchars($product_desc); ?></textarea>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Current Image</label><br>
                          <?php
                          if (!empty($product_image)) {
                            echo '<img src="assets/images/products/' . htmlspecialchars($product_image) . '" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px;">';
                          } else {
                            echo '<img src="assets/images/category/default.png" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px;">';
                          }
                        ?>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Replace Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                          <option value="1" <?php if ($status == 1) echo 'selected'; ?>>Active</option>
                          <option value="2" <?php if ($status == 2) echo 'selected'; ?>>Pending</option>
                          <option value="0" <?php if ($status == 0) echo 'selected'; ?>>Inactive</option>
                        </select>
                      </div>

                      <input type="hidden" name="updateProductId" value="<?php echo $product_id; ?>">
                      <div class="d-grid gap-2">
                        <button type="submit" name="updateProduct" class="btn btn-primary btn-lg">Update Product</button>
                        <a href="products.php?do=Manage" class="btn btn-outline-secondary">Cancel</a>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
    <?php
          }
        }
      }
      elseif ($do == 'Update') {
        if (isset($_POST['updateProduct'])) {
          $updateProductId = (int) $_POST['updateProductId'];
          $productName = mysqli_real_escape_string($db, trim($_POST['productName']));
          $price = mysqli_real_escape_string($db, trim($_POST['price']));
          $desc = mysqli_real_escape_string($db, trim($_POST['desc']));
          $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
          $status = (int) $_POST['status'];
          $seller_email = mysqli_real_escape_string($db, trim($_POST['seller_email']));
          $newImage = '';

          if (!empty($_FILES['image']['name'])) {
            $oldImgSql = "SELECT image FROM products WHERE product_id = '$updateProductId' LIMIT 1";
            $oldImageResult = mysqli_query($db, $oldImgSql);
            if ($oldImageRow = mysqli_fetch_assoc($oldImageResult)) {
              $oldImage = $oldImageRow['image'];
              if (!empty($oldImage) && file_exists('../admin/assets/images/products/' . $oldImage)) {
                unlink('../admin/assets/images/products/' . $oldImage);
              }
            }

            $uploadDir = '../admin/assets/images/products/';
            if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
            }
            $newImage = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage);
            $setImageSql = "image = '$newImage',";
          } else {
            $setImageSql = '';
          }

          $categoryValue = $category_id ? "category_id = '$category_id'," : '';
          $updateSql = "UPDATE products SET product_name = '$productName', description = '$desc', $categoryValue $setImageSql price = '$price', seller_email = '$seller_email', status = '$status' WHERE product_id = '$updateProductId'";
          $updateQuery = mysqli_query($db, $updateSql);

          if ($updateQuery) {
            header('Location: products.php?do=Manage');
            exit;
          } else {
            die('MySQL Error: ' . mysqli_error($db));
          }
        }
      }
      elseif ($do == 'Trash') {
        if (isset($_GET['tId'])) {
          $trashId = (int) $_GET['tId'];
          $trashSql = "UPDATE products SET status = 0 WHERE product_id = '$trashId'";
          $trashQuery = mysqli_query($db, $trashSql);
          if ($trashQuery) {
            header('Location: products.php?do=Manage');
            exit;
          } else {
            die('MySQL Error: ' . mysqli_error($db));
          }
        }
      }
      elseif ($do == 'ManageTrash') {
    ?>
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Products Management</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item"><a href="products.php?do=Manage">Products</a></li>
              <li class="breadcrumb-item active" aria-current="page">Trash</li>
            </ol>
          </nav>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
                        <select class="form-select" name="category_id">
                          <option value="">Select a category</option>
                          <?php
                            $assignedCats = mysqli_query($db, "SELECT * FROM category WHERE status != 0 ORDER BY cat_name ASC");
                            while ($assignedRow = mysqli_fetch_assoc($assignedCats)) {
                              $selected = ($assignedRow['cat_id'] == $category_selected) ? 'selected' : '';
                              echo '<option value="' . (int) $assignedRow['cat_id'] . '" ' . $selected . '>' . htmlspecialchars($assignedRow['cat_name']) . '</option>';
                            }
                          ?>
                        </select>
                  <th>Name</th>
                  <th>Price</th>
                  <th>Seller</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $trashSql = "SELECT * FROM products WHERE status = 0 ORDER BY join_date DESC";
                  $trashQuery = mysqli_query($db, $trashSql);
                  if (mysqli_num_rows($trashQuery) == 0) {
                ?>
                  <tr>
                    <td colspan="5" class="text-center text-warning">Trash is empty.</td>
                  </tr>
                <?php
                  } else {
                    $i = 0;
                    while ($row = mysqli_fetch_assoc($trashQuery)) {
                      $i++;
                ?>
                  <tr>
                    <th scope="row"><?php echo $i; ?></th>
                    <td><?php echo htmlspecialchars($row['cat_name']); ?></td>
                    <td><?php echo number_format((float) $row['price'], 2); ?> Tk</td>
                    <td><?php echo !empty($row['seller_email']) ? htmlspecialchars($row['seller_email']) : 'N/A'; ?></td>
                    <td>
                      <div class="d-flex gap-2">
                        <a href="products.php?do=Restore&rId=<?php echo (int) $row['product_id']; ?>" class="btn btn-sm btn-success">Restore</a>
                        <a href="products.php?do=Delete&dId=<?php echo (int) $row['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product permanently?')">Delete</a>
                      </div>
                    </td>
                  </tr>
                <?php
                    }
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php
      }
      elseif ($do == 'Restore') {
        if (isset($_GET['rId'])) {
          $restoreId = (int) $_GET['rId'];
          $restoreSql = "UPDATE products SET status = 1 WHERE product_id = '$restoreId'";
          $restoreQuery = mysqli_query($db, $restoreSql);
          if ($restoreQuery) {
            header('Location: products.php?do=Manage');
            exit;
          } else {
            die('MySQL Error: ' . mysqli_error($db));
          }
        }
      }
      elseif ($do == 'Delete') {
        if (isset($_GET['dId'])) {
          $deleteId = (int) $_GET['dId'];

          $imageSql = "SELECT image FROM products WHERE product_id = '$deleteId' LIMIT 1";
          $imageQuery = mysqli_query($db, $imageSql);
          if ($imageRow = mysqli_fetch_assoc($imageQuery)) {
            $imageName = $imageRow['image'];
            if (!empty($imageName) && file_exists('../admin/assets/images/products/' . $imageName)) {
              unlink('../admin/assets/images/products/' . $imageName);
            }
          }

          $deleteSql = "DELETE FROM products WHERE product_id = '$deleteId'";
          $deleteQuery = mysqli_query($db, $deleteSql);
          if ($deleteQuery) {
            header('Location: products.php?do=ManageTrash');
            exit;
          } else {
            die('MySQL Error: ' . mysqli_error($db));
          }
        }
      }
    ?>
  </div>
</div>

<?php include "inc/footer.php"; ?>
