<?php
    include "inc/header.php";
?>

<div class="page-wrapper">
    <div class="page-content">
        <?php
            $do = isset($_GET['do']) ? $_GET['do'] : "Manage";

            if ($do == "Manage") {
        ?>
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Category Management</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Category Manage</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 text-uppercase">Manage All Category</h6>
                <div>
                    <a href="category.php?do=Add" class="btn btn-success btn-sm me-2">Add Category</a>
                    <a href="category.php?do=ManageTrash" class="btn btn-secondary btn-sm">Trash</a>
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
                                    <th>Category Name</th>
                                    <th>Price</th>
                                    <th>Farmer Email</th>
                                    <th>Category Type</th>
                                    <th>Status</th>
                                    <th>Join Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $catSql = "SELECT * FROM category WHERE status != 0 ORDER BY cat_name ASC";
                                    $catQuery = mysqli_query($db, $catSql);
                                    $catCount = mysqli_num_rows($catQuery);

                                    if ($catCount == 0) {
                                ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-warning">No categories found in the database.</td>
                                    </tr>
                                <?php
                                    } else {
                                        $i = 0;
                                        while ($row = mysqli_fetch_assoc($catQuery)) {
                                            $cat_id = $row['cat_id'];
                                            $cat_name = $row['cat_name'];
                                            $cat_desc = $row['cat_desc'];
                                            $status = (int) $row['status'];
                                            $join_date = $row['join_date'];
                                            $cat_image = $row['cat_image'];
                                            $price = $row['price'];
                                            $seller_email = $row['seller_email'];
                                            $i++;
                                ?>
                                    <tr>
                                        <th scope="row"><?php echo $i; ?></th>
                                        <td>
                                            <?php
                                                if (!empty($cat_image)) {
                                                    echo '<img src="assets/images/category/' . htmlspecialchars($cat_image) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                                                } else {
                                                    echo '<img src="assets/images/category/default.png" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cat_name); ?></td>
                                        <td><?php echo number_format((float) $price, 2); ?></td>
                                        <td><?php echo !empty($seller_email) ? htmlspecialchars($seller_email) : 'N/A'; ?></td>
                                        <td><span class="badge text-bg-light text-dark">STANDARD</span></td>
                                        <td>
                                            <?php
                                                if ($status == 1) {
                                                    echo '<span class="badge text-bg-success">ACTIVE</span>';
                                                } elseif ($status == 2) {
                                                    echo '<span class="badge text-bg-warning text-dark">PENDING</span>';
                                                } else {
                                                    echo '<span class="badge text-bg-danger">INACTIVE</span>';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($join_date); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="category.php?do=Edit&uId=<?php echo $cat_id; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                                                <a href="" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $cat_id; ?>" title="Trash"><i class="fa-regular fa-trash-can"></i></a>
                                            </div>

                                            <div class="modal fade" id="deleteModal<?php echo $cat_id; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-dark">Move to Trash?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-dark">
                                                            Are you sure you want to move <strong><?php echo htmlspecialchars($cat_name); ?></strong> to the trash?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="category.php?do=Trash&tId=<?php echo $cat_id; ?>" class="btn btn-danger">Trash</a>
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
            } elseif ($do == "Add") {
        ?>
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Category Management</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="category.php?do=Manage">Categories</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Category</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3 text-uppercase">Add New Category</h6>
                    <form action="category.php?do=Store" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" name="catName" class="form-control" placeholder="Enter category name" required autocomplete="off">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Price (Ugx)</label>
                                    <input type="number" step="0.01" name="price" class="form-control" placeholder="Enter category price">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"> Farmer Email</label>
                                    <input type="email" name="seller_email" class="form-control" placeholder="Enter farmer email">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Category Type</label>
                                    <input type="text" class="form-control" value="Standard category" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="1">Active</option>
                                        <option value="2">Pending</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Description</label>
                                    <textarea name="desc" class="form-control" rows="7"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Category Image</label>
                                    <input class="form-control" name="image" type="file" accept="image/*">
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="addCategory" class="btn btn-primary btn-lg">Add New Category</button>
                                    <a href="category.php?do=Manage" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php
            } elseif ($do == "Store") {
                if (isset($_POST['addCategory'])) {
                    $catName = mysqli_real_escape_string($db, trim($_POST['catName']));
                    $price = mysqli_real_escape_string($db, trim($_POST['price']));
                    $seller_email = mysqli_real_escape_string($db, trim($_POST['seller_email']));
                    $is_parent = 1;
                    $status = (int) $_POST['status'];
                    $desc = mysqli_real_escape_string($db, trim($_POST['desc']));

                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $uploadDir = 'assets/images/category/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $imageName = time() . '_' . basename($_FILES['image']['name']);
                        $target = $uploadDir . $imageName;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                            $image = $imageName;
                        }
                    }

                    $addSql = "INSERT INTO category (cat_name, cat_desc, is_parent, status, cat_image, join_date, price, seller_email) VALUES ('$catName', '$desc', '$is_parent', '$status', '$image', NOW(), '$price', '$seller_email')";
                    $addQuery = mysqli_query($db, $addSql);

                    if ($addQuery) {
                        header("Location: category.php?do=Manage");
                        exit;
                    } else {
                        die("Mysql Error." . mysqli_error($db));
                    }
                }
            } elseif ($do == "Edit") {
                if (isset($_GET['uId'])) {
                    $upId = (int) $_GET['uId'];
                    $upReadSql = "SELECT * FROM category WHERE cat_id = '$upId' LIMIT 1";
                    $upReadQuery = mysqli_query($db, $upReadSql);

                    if (mysqli_num_rows($upReadQuery) > 0) {
                        $row = mysqli_fetch_assoc($upReadQuery);
                        $cat_id = $row['cat_id'];
                        $cat_name = $row['cat_name'];
                        $cat_desc = $row['cat_desc'];
                        $status = (int) $row['status'];
                        $cat_image = $row['cat_image'];
                        $price = $row['price'];
                        $seller_email = $row['seller_email'];
                ?>
                        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                            <div class="breadcrumb-title pe-3">Category Management</div>
                            <div class="ps-3">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0 p-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                                        <li class="breadcrumb-item"><a href="category.php?do=Manage">Categories</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Edit Category</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3 text-uppercase">Update <span style="color: firebrick;"><?php echo htmlspecialchars($cat_name); ?></span> Info</h6>
                                <form action="category.php?do=Update" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Category Name</label>
                                                <input type="text" name="catName" class="form-control" value="<?php echo htmlspecialchars($cat_name); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Price (Ugx)</label>
                                                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Seller Email</label>
                                                <input type="email" name="seller_email" class="form-control" value="<?php echo htmlspecialchars($seller_email); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Category Type</label>
                                                <input type="text" class="form-control" value="Standard category" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="1" <?php if ($status == 1) echo 'selected'; ?>>Active</option>
                                                    <option value="2" <?php if ($status == 2) echo 'selected'; ?>>Pending</option>
                                                    <option value="0" <?php if ($status == 0) echo 'selected'; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label">Category Description</label>
                                                <textarea name="desc" class="form-control" rows="7"><?php echo htmlspecialchars($cat_desc); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Current Image</label><br>
                                                <?php
                                                    if (!empty($cat_image)) {
                                                        echo '<img src="assets/images/category/' . htmlspecialchars($cat_image) . '" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px;">';
                                                    } else {
                                                        echo '<img src="assets/images/category/default.png" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px;">';
                                                    }
                                                ?>
                                                <br><br>
                                                <input class="form-control" name="image" type="file" accept="image/*">
                                            </div>

                                            <div class="d-grid gap-2">
                                                <input type="hidden" name="updateCategoryId" value="<?php echo $cat_id; ?>">
                                                <button type="submit" name="updateCategory" class="btn btn-primary btn-lg">Update Category</button>
                                                <a href="category.php?do=Manage" class="btn btn-outline-secondary">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                <?php
                    }
                }
            } elseif ($do == "Update") {
                if (isset($_POST['updateCategory'])) {
                    $updateCategoryId = (int) $_POST['updateCategoryId'];
                    $catName = mysqli_real_escape_string($db, trim($_POST['catName']));
                    $price = mysqli_real_escape_string($db, trim($_POST['price']));
                    $seller_email = mysqli_real_escape_string($db, trim($_POST['seller_email']));
                    $is_parent = 1;
                    $status = (int) $_POST['status'];
                    $desc = mysqli_real_escape_string($db, trim($_POST['desc']));

                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $oldImageSql = "SELECT cat_image FROM category WHERE cat_id = '$updateCategoryId' LIMIT 1";
                        $oldImageResult = mysqli_query($db, $oldImageSql);
                        if ($oldImageRow = mysqli_fetch_assoc($oldImageResult)) {
                            $oldImage = $oldImageRow['cat_image'];
                            if (!empty($oldImage) && file_exists('assets/images/category/' . $oldImage)) {
                                unlink('assets/images/category/' . $oldImage);
                            }
                        }

                        $uploadDir = 'assets/images/category/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $image = time() . '_' . basename($_FILES['image']['name']);
                        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
                        $setImageSql = "cat_image = '$image',";
                    } else {
                        $setImageSql = '';
                    }

                    $upSql = "UPDATE category SET cat_name = '$catName', cat_desc = '$desc', is_parent = '$is_parent', status = '$status', price = '$price', seller_email = '$seller_email', $setImageSql join_date = join_date WHERE cat_id = '$updateCategoryId'";
                    $upSql = str_replace("', join_date = join_date", "', join_date = join_date", $upSql);
                    $updateQuery = mysqli_query($db, $upSql);

                    if ($updateQuery) {
                        header("Location: category.php?do=Manage");
                        exit;
                    } else {
                        die("Mysql Error." . mysqli_error($db));
                    }
                }
            } elseif ($do == "Trash") {
                if (isset($_GET['tId'])) {
                    $trashId = (int) $_GET['tId'];
                    $trashSql = "UPDATE category SET status = 0 WHERE cat_id = '$trashId'";
                    $trashQuery = mysqli_query($db, $trashSql);
                    if ($trashQuery) {
                        header("Location: category.php?do=Manage");
                        exit;
                    } else {
                        die("Mysql Error." . mysqli_error($db));
                    }
                }
            } elseif ($do == "ManageTrash") {
        ?>
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Category Trash Management</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Trash Manage</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0 text-uppercase">Manage All Trash</h6>
                        <a href="category.php?do=Manage" class="btn btn-sm btn-outline-secondary">Back to Categories</a>
                    </div>

                    <div class="table-responsive">
                        <table id="example3" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Category Name</th>
                                    <th>Price</th>
                                    <th>Seller Email</th>
                                    <th>Category Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $trashSql = "SELECT * FROM category WHERE status = 0 ORDER BY join_date DESC";
                                    $trashQuery = mysqli_query($db, $trashSql);

                                    if (mysqli_num_rows($trashQuery) == 0) {
                                ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-warning">Trash is empty.</td>
                                    </tr>
                                <?php
                                    } else {
                                        $i = 0;
                                        while ($row = mysqli_fetch_assoc($trashQuery)) {
                                            $i++;
                                            $cat_id = $row['cat_id'];
                                            $cat_name = $row['cat_name'];
                                            $price = $row['price'];
                                            $seller_email = $row['seller_email'];
                                            $cat_image = $row['cat_image'];
                                ?>
                                    <tr>
                                        <th scope="row"><?php echo $i; ?></th>
                                        <td>
                                            <?php
                                                if (!empty($cat_image)) {
                                                    echo '<img src="assets/images/category/' . htmlspecialchars($cat_image) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                                                } else {
                                                    echo '<img src="assets/images/category/default.png" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cat_name); ?></td>
                                        <td><?php echo number_format((float) $price, 2); ?></td>
                                        <td><?php echo !empty($seller_email) ? htmlspecialchars($seller_email) : 'N/A'; ?></td>
                                        <td><span class="badge text-bg-light text-dark">STANDARD</span></td>
                                        <td><span class="badge text-bg-danger">TRASHED</span></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="category.php?do=Restore&rId=<?php echo $cat_id; ?>" class="btn btn-sm btn-success">Restore</a>
                                                <a href="category.php?do=Delete&DId=<?php echo $cat_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category permanently?')">Delete</a>
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
            } elseif ($do == "Restore") {
                if (isset($_GET['rId'])) {
                    $restoreId = (int) $_GET['rId'];
                    $restoreSql = "UPDATE category SET status = 1 WHERE cat_id = '$restoreId'";
                    $restoreQuery = mysqli_query($db, $restoreSql);
                    if ($restoreQuery) {
                        header("Location: category.php?do=ManageTrash");
                        exit;
                    } else {
                        die("Mysql Error." . mysqli_error($db));
                    }
                }
            } elseif ($do == "Delete") {
                if (isset($_GET['DId'])) {
                    $deleteId = (int) $_GET['DId'];
                    $imageSql = "SELECT cat_image FROM category WHERE cat_id = '$deleteId' LIMIT 1";
                    $imageQuery = mysqli_query($db, $imageSql);
                    if ($imageRow = mysqli_fetch_assoc($imageQuery)) {
                        $imageName = $imageRow['cat_image'];
                        if (!empty($imageName) && file_exists('assets/images/category/' . $imageName)) {
                            unlink('assets/images/category/' . $imageName);
                        }
                    }

                    $deleteSql = "DELETE FROM category WHERE cat_id = '$deleteId'";
                    $deleteQuery = mysqli_query($db, $deleteSql);
                    if ($deleteQuery) {
                        header("Location: category.php?do=ManageTrash");
                        exit;
                    } else {
                        die("Mysql Error." . mysqli_error($db));
                    }
                }
            }
        ?>
    </div>
</div>

<?php
    include "inc/footer.php";
?>
