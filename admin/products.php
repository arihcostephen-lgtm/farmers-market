<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Products</h4>
        <p class="text-muted">Upload product images or add quick product entries. Full product CRUD can be implemented as needed.</p>

        <form method="post" enctype="multipart/form-data" class="mt-3">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="text" name="price" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="product_image" accept="image/*" class="form-control">
          </div>
          <button class="btn btn-success" type="submit" name="save_product">Save</button>
        </form>

        <?php
        if (isset($_POST['save_product'])) {
          $name = trim($_POST['name']);
          $price = trim($_POST['price']);
          $uploadDir = __DIR__ . '/../uploads/products/';
          if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
          $imagePath = '';
          if (!empty($_FILES['product_image']['name'])) {
            $target = $uploadDir . basename($_FILES['product_image']['name']);
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
              $imagePath = 'uploads/products/' . basename($_FILES['product_image']['name']);
            }
          }
          // Store a simple products JSON file (append)
          $storeFile = __DIR__ . '/products.json';
          $products = [];
          if (file_exists($storeFile)) $products = json_decode(file_get_contents($storeFile), true) ?? [];
          $products[] = ['name'=>$name,'price'=>$price,'image'=>$imagePath,'created'=>date('c')];
          file_put_contents($storeFile, json_encode($products, JSON_PRETTY_PRINT));
          echo '<div class="alert alert-success mt-3">Product saved.</div>';
        }

        // show recent products
        $storeFile = __DIR__ . '/products.json';
        if (file_exists($storeFile)) {
          $products = json_decode(file_get_contents($storeFile), true) ?? [];
          if (count($products) > 0) {
            echo '<div class="row row-cols-1 row-cols-md-3 g-3 mt-3">';
            foreach (array_reverse($products) as $p) {
              echo '<div class="col"><div class="card h-100"><div class="card-body">';
              if (!empty($p['image'])) echo '<img src="../' . htmlspecialchars($p['image']) . '" class="img-fluid mb-2" style="max-height:120px;" alt="">';
              echo '<h6>' . htmlspecialchars($p['name']) . '</h6>';
              if (!empty($p['price'])) echo '<div class="text-success">' . htmlspecialchars($p['price']) . '</div>';
              echo '</div></div></div>';
            }
            echo '</div>';
          }
        }
        ?>

      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
