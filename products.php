<?php 
include "inc/header.php";

// Initialize cart session
if (!isset($_SESSION['temp_cart'])) {
    $_SESSION['temp_cart'] = [];
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $productId = (int) $_POST['product_id'];
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    
    // Check if product exists and is available
    $checkProduct = mysqli_query($db, "SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = $productId AND status != 0 LIMIT 1");
    if ($checkProduct && mysqli_num_rows($checkProduct) > 0) {
        $product = mysqli_fetch_assoc($checkProduct);
        
        if ($quantity <= $product['stock_quantity']) {
            // Add or update cart
            $_SESSION['temp_cart'][$productId] = $quantity;
            $_SESSION['cart_message'] = 'Product added to cart successfully!';
        } else {
            $_SESSION['cart_error'] = 'Requested quantity exceeds available stock.';
        }
    } else {
        $_SESSION['cart_error'] = 'Product not found or is unavailable.';
    }
}

// Get all active products
$productsQuery = mysqli_query($db, "SELECT p.product_id, p.product_name, p.description, p.price, p.product_unit, p.stock_quantity, p.image, p.join_date, c.cat_name, u.user_name AS farmer_name, f.farm_address, f.farm_latitude, f.farm_longitude, f.market_name, f.market_address, f.market_latitude, f.market_longitude, f.market_operating_days, f.market_hours, f.pickup_instructions, f.delivery_instructions FROM products p LEFT JOIN category c ON c.cat_id = p.category_id LEFT JOIN users u ON u.user_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci LEFT JOIN farmer f ON f.farm_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci WHERE p.status != 0 ORDER BY p.join_date DESC");

$allProducts = [];
while ($row = mysqli_fetch_assoc($productsQuery)) {
    $allProducts[] = $row;
}

// Get categories for filter
$categoriesQuery = mysqli_query($db, "SELECT DISTINCT cat_id, cat_name FROM category WHERE status = 1 ORDER BY cat_name ASC");
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesQuery)) {
    $categories[] = $row;
}

// Get price range
$priceQuery = mysqli_query($db, "SELECT MIN(price) AS min_price, MAX(price) AS max_price FROM products WHERE status != 0");
$priceRange = mysqli_fetch_assoc($priceQuery);
$minPrice = (int) $priceRange['min_price'];
$maxPrice = (int) $priceRange['max_price'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products Catalogue | Local Farm Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .catalogue-page { background: #f8faf6; min-height: 100vh; }
        .product-card { 
            background: white; 
            border: 1px solid rgba(16,184,129,0.15); 
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }
        .product-card:hover { 
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(16,184,129,0.15);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .product-body { padding: 1.25rem; }
        .product-name { 
            font-weight: 600;
            color: #092214;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b881;
            margin: 0.75rem 0;
        }
        .product-unit {
            font-size: 0.85rem;
            color: #666;
        }
        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.75rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-clamp: 2;
            overflow: hidden;
        }
        .stock-status {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            margin: 0.5rem 0;
            display: inline-block;
        }
        .stock-status.in-stock { background: #d4edda; color: #155724; }
        .stock-status.low-stock { background: #fff3cd; color: #856404; }
        .stock-status.out-stock { background: #f8d7da; color: #721c24; }
        .filter-panel {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(16,184,129,0.15);
            position: sticky;
            top: 80px;
        }
        .filter-title {
            font-weight: 600;
            color: #092214;
            margin-bottom: 1rem;
            border-bottom: 2px solid #10b881;
            padding-bottom: 0.75rem;
        }
        .filter-group { margin-bottom: 1.5rem; }
        .form-check-input:checked { background-color: #10b881; border-color: #10b881; }
        .price-input {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        .search-box {
            margin-bottom: 1.5rem;
        }
        .search-box input {
            border: 2px solid #10b881;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .no-results {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        .no-results i { font-size: 3rem; color: #ddd; margin-bottom: 1rem; }
        .header-section {
            background: linear-gradient(135deg, #f7fdf4 0%, #eef8eb 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .header-section h1 {
            color: #092214;
            font-weight: 700;
        }
        .header-section p {
            color: #666;
            font-size: 1.1rem;
        }
        .btn-add-cart {
            background: #10b881;
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-add-cart:hover {
            background: #0d9068;
            color: white;
        }
        .btn-checkout {
            background: #10b881;
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-checkout:hover {
            background: #0d9068;
            color: white;
        }
        .quantity-control {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .quantity-control button {
            width: 32px;
            height: 32px;
            padding: 0;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.35rem;
        }
        .farmer-badge {
            font-size: 0.8rem;
            background: #e8f5e9;
            color: #10b881;
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            display: inline-block;
            margin-top: 0.5rem;
        }
        .cart-sidebar {
            position: fixed;
            right: -350px;
            top: 0;
            width: 350px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            padding: 1.5rem;
            overflow-y: auto;
        }
        .cart-sidebar.open {
            right: 0;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body class="catalogue-page">
    <div class="header-section">
        <div class="container">
            <h1 class="mb-2"><i class="fa-solid fa-leaf me-2"></i>All Farm Products and animals all in one place</h1>
            <p class="mb-0">Browse recently added products from trusted farmers. Add items to cart and checkout anytime!</p>
        </div>
    </div>

    <div class="container py-4">
        <?php if (isset($_SESSION['cart_message'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['cart_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['cart_message']); ?>
        <?php } ?>
        <?php if (isset($_SESSION['cart_error'])) { ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['cart_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php } ?>

        <div class="row g-4">
            <!-- Sidebar Filters -->
            <div class="col-lg-3">
                <div class="filter-panel">
                    <div class="filter-title">
                        <i class="fa-solid fa-filter me-2"></i>Filters
                    </div>

                    <!-- Search -->
                    <div class="search-box">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <label class="fw-600 mb-2">Category</label>
                        <div id="categoryFilter">
                            <div class="form-check mb-2">
                                <input class="form-check-input category-filter" type="checkbox" id="all-categories" value="" checked>
                                <label class="form-check-label" for="all-categories">All Categories</label>
                            </div>
                            <?php foreach ($categories as $cat) { ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input category-filter" type="checkbox" value="<?php echo htmlspecialchars($cat['cat_name']); ?>" id="cat-<?php echo $cat['cat_id']; ?>">
                                    <label class="form-check-label" for="cat-<?php echo $cat['cat_id']; ?>">
                                        <?php echo htmlspecialchars($cat['cat_name']); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <label class="fw-600 mb-2">Price Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" id="minPrice" class="price-input" placeholder="Min" value="<?php echo $minPrice; ?>" min="<?php echo $minPrice; ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" id="maxPrice" class="price-input" placeholder="Max" value="<?php echo $maxPrice; ?>" max="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Availability Filter -->
                    <div class="filter-group">
                        <label class="fw-600 mb-2">Availability</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input availability-filter" type="radio" name="availability" id="all-stock" value="all" checked>
                            <label class="form-check-label" for="all-stock">All Products</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input availability-filter" type="radio" name="availability" id="in-stock" value="in-stock">
                            <label class="form-check-label" for="in-stock">In Stock</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input availability-filter" type="radio" name="availability" id="low-stock" value="low-stock">
                            <label class="form-check-label" for="low-stock">Low Stock</label>
                        </div>
                    </div>

                    <button class="btn btn-outline-success w-100" id="resetFilters">
                        <i class="fa-solid fa-rotate-left me-2"></i>Reset Filters
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="row g-4" id="productsGrid">
                    <?php if (empty($allProducts)) { ?>
                        <div class="col-12">
                            <div class="no-results">
                                <i class="fa-solid fa-box-open"></i>
                                <h5>No Products Available</h5>
                                <p>Check back soon for fresh products from our farmers!</p>
                            </div>
                        </div>
                    <?php } else {
                        foreach ($allProducts as $product) {
                            $stock = (int) $product['stock_quantity'];
                            $stockStatus = 'out-stock';
                            $stockText = 'Out of Stock';
                            if ($stock > 5) {
                                $stockStatus = 'in-stock';
                                $stockText = 'In Stock';
                            } elseif ($stock > 0) {
                                $stockStatus = 'low-stock';
                                $stockText = "Only $stock left";
                            }
                    ?>
                        <div class="col-md-6 col-xl-4 product-item" 
                             data-name="<?php echo strtolower(htmlspecialchars($product['product_name'])); ?>"
                             data-category="<?php echo strtolower(htmlspecialchars($product['cat_name'] ?? '')); ?>"
                             data-price="<?php echo (float) $product['price']; ?>"
                             data-stock="<?php echo $stock; ?>">
                            <div class="product-card">
                                <?php if (!empty($product['image']) && is_file(__DIR__ . '/admin/assets/images/products/' . basename($product['image']))) { ?>
                                    <img src="admin/assets/images/products/<?php echo rawurlencode(basename($product['image'])); ?>" class="product-image" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <?php } else { ?>
                                    <div class="product-image d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);">
                                        <i class="fa-solid fa-image" style="font-size: 2rem; color: #999;"></i>
                                    </div>
                                <?php } ?>

                                <div class="product-body">
                                    <h6 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                    
                                    <?php if (!empty($product['description'])) { ?>
                                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <?php } ?>

                                    <div class="product-price">
                                        UGX <?php echo number_format((float) $product['price'], 0); ?>
                                        <span class="product-unit">/ <?php echo htmlspecialchars($product['product_unit'] ?? 'unit'); ?></span>
                                    </div>

                                    <div class="stock-status <?php echo $stockStatus; ?>">
                                        <i class="fa-solid fa-<?php echo $stock > 0 ? 'check-circle' : 'ban'; ?> me-1"></i>
                                        <?php echo $stockText; ?>
                                    </div>

                                    <?php if (!empty($product['farmer_name'])) { ?>
                                        <div class="farmer-badge">
                                            <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($product['farmer_name']); ?>
                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($product['farm_address']) || !empty($product['market_name']) || !empty($product['market_operating_days']) || !empty($product['market_hours'])): ?>
                                        <div class="mt-3 small text-muted">
                                            <?php if (!empty($product['farm_address'])): ?>
                                                <div class="mb-1">
                                                    <i class="fa-solid fa-location-dot me-1 text-success"></i>
                                                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode($product['farm_address']); ?>" target="_blank" rel="noopener" class="text-decoration-none text-muted">
                                                        <?php echo htmlspecialchars($product['farm_address']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($product['market_name']) || !empty($product['market_address'])): ?>
                                                <div class="mb-1">
                                                    <i class="fa-solid fa-store me-1 text-primary"></i>
                                                    <?php echo htmlspecialchars($product['market_name'] ?: $product['market_address']); ?>
                                                    <?php if (!empty($product['market_address'])): ?>
                                                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode($product['market_address']); ?>" target="_blank" rel="noopener" class="text-decoration-none text-primary ms-1">Map</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($product['market_operating_days']) || !empty($product['market_hours'])): ?>
                                                <div>
                                                    <i class="fa-solid fa-clock me-1 text-warning"></i>
                                                    <?php echo htmlspecialchars(trim(($product['market_operating_days'] ?: '') . ' ' . ($product['market_hours'] ?: ''))); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="post" class="add-to-cart-form mt-3">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        
                                        <div class="quantity-control">
                                            <button type="button" class="qty-btn" data-action="decrease">−</button>
                                            <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $stock; ?>" <?php echo $stock < 1 ? 'disabled' : ''; ?>>
                                            <button type="button" class="qty-btn" data-action="increase">+</button>
                                        </div>

                                        <button type="submit" name="add_to_cart" class="btn-add-cart" <?php echo $stock < 1 ? 'disabled' : ''; ?>>
                                            <i class="fa-solid fa-shopping-cart me-1"></i><?php echo $stock < 1 ? 'Out of Stock' : 'Add to Cart'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } } ?>
                </div>

                <div id="noResults" class="no-results" style="display: none;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h5>No Products Found</h5>
                    <p>Try adjusting your filters or search terms</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <?php if (!empty($_SESSION['temp_cart'])) { ?>
        <div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 999;">
            <button class="btn btn-success btn-lg rounded-circle" id="cartBtn" style="width: 60px; height: 60px; font-size: 1.5rem; position: relative;">
                <i class="fa-solid fa-shopping-cart"></i>
                <?php if (!empty($_SESSION['temp_cart'])) { ?>
                    <span class="cart-badge"><?php echo count($_SESSION['temp_cart']); ?></span>
                <?php } ?>
            </button>
        </div>
    <?php } ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quantity controls
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const input = this.parentElement.querySelector('.qty-input');
                const currentVal = parseInt(input.value) || 1;
                const action = this.dataset.action;
                const maxVal = parseInt(input.max) || 999;
                
                if (action === 'increase' && currentVal < maxVal) {
                    input.value = currentVal + 1;
                } else if (action === 'decrease' && currentVal > 1) {
                    input.value = currentVal - 1;
                }
            });
        });

        // Filter functionality
        const searchInput = document.getElementById('searchInput');
        const categoryFilters = document.querySelectorAll('.category-filter');
        const availabilityFilters = document.querySelectorAll('.availability-filter');
        const minPriceInput = document.getElementById('minPrice');
        const maxPriceInput = document.getElementById('maxPrice');
        const resetBtn = document.getElementById('resetFilters');
        const productsGrid = document.getElementById('productsGrid');
        const productItems = document.querySelectorAll('.product-item');
        const noResults = document.getElementById('noResults');

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategories = Array.from(categoryFilters)
                .filter(c => c.checked)
                .map(c => c.value)
                .filter(v => v !== '');
            const minPrice = parseFloat(minPriceInput.value) || 0;
            const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
            const availability = document.querySelector('input[name="availability"]:checked').value;

            let visibleCount = 0;

            productItems.forEach(item => {
                const name = item.dataset.name;
                const category = item.dataset.category;
                const price = parseFloat(item.dataset.price);
                const stock = parseInt(item.dataset.stock);

                let matches = true;

                // Search filter
                if (searchTerm && !name.includes(searchTerm)) {
                    matches = false;
                }

                // Category filter
                if (selectedCategories.length > 0 && !selectedCategories.some(cat => category.includes(cat.toLowerCase()))) {
                    matches = false;
                }

                // Price filter
                if (price < minPrice || price > maxPrice) {
                    matches = false;
                }

                // Availability filter
                if (availability === 'in-stock' && stock <= 0) {
                    matches = false;
                } else if (availability === 'low-stock' && stock > 5) {
                    matches = false;
                }

                item.style.display = matches ? 'block' : 'none';
                if (matches) visibleCount++;
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Event listeners
        searchInput.addEventListener('keyup', filterProducts);
        categoryFilters.forEach(filter => filter.addEventListener('change', filterProducts));
        availabilityFilters.forEach(filter => filter.addEventListener('change', filterProducts));
        minPriceInput.addEventListener('input', filterProducts);
        maxPriceInput.addEventListener('input', filterProducts);

        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            categoryFilters.forEach(c => c.checked = c.id === 'all-categories');
            availabilityFilters.forEach(f => f.checked = f.id === 'all-stock');
            minPriceInput.value = minPriceInput.min;
            maxPriceInput.value = maxPriceInput.max;
            filterProducts();
        });

        // Cart button handler
        document.getElementById('cartBtn')?.addEventListener('click', function() {
            const cartItems = document.querySelectorAll('input[name="product_id"]');
            if (cartItems.length > 0) {
                window.location.href = '<?php echo empty($_SESSION['user_id']) ? 'login.php?redirect=checkout' : 'customerDashboard.php'; ?>';
            }
        });
    </script>
</body>
</html>
