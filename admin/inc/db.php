<?php  
	$db = mysqli_connect("localhost", "root", "", "farmersmkt_db");

	if ($db) {
		mysqli_set_charset($db, "utf8mb4");
		// Keep existing installations compatible with product quantity support.
		$quantityColumns = [
			'products' => "ALTER TABLE products ADD COLUMN stock_quantity INT UNSIGNED NOT NULL DEFAULT 0 AFTER price",
			'order_list' => "ALTER TABLE order_list ADD COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1 AFTER price"
		];
		foreach ($quantityColumns as $table => $alterSql) {
			$columnCheck = mysqli_query($db, "SHOW COLUMNS FROM `$table` LIKE '" . ($table === 'products' ? 'stock_quantity' : 'quantity') . "'");
			if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
				@mysqli_query($db, $alterSql);
			}
		}
		$negotiableColumn = mysqli_query($db, "SHOW COLUMNS FROM products LIKE 'is_negotiable'");
		if ($negotiableColumn && mysqli_num_rows($negotiableColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE products ADD COLUMN is_negotiable TINYINT(1) NOT NULL DEFAULT 0 AFTER price");
		}
		$productUnitColumn = mysqli_query($db, "SHOW COLUMNS FROM products LIKE 'product_unit'");
		if ($productUnitColumn && mysqli_num_rows($productUnitColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE products ADD COLUMN product_unit VARCHAR(20) NOT NULL DEFAULT 'kilogram' AFTER price");
		}
		$viewCountColumn = mysqli_query($db, "SHOW COLUMNS FROM products LIKE 'view_count'");
		if ($viewCountColumn && mysqli_num_rows($viewCountColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE products ADD COLUMN view_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER is_negotiable");
		}
		$harvestDateColumn = mysqli_query($db, "SHOW COLUMNS FROM products LIKE 'harvest_date'");
		if ($harvestDateColumn && mysqli_num_rows($harvestDateColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE products ADD COLUMN harvest_date DATE DEFAULT NULL AFTER view_count");
		}
		$inventoryColumns = [
			'low_stock_threshold' => "ALTER TABLE products ADD COLUMN low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5 AFTER stock_quantity",
			'seasonal_availability' => "ALTER TABLE products ADD COLUMN seasonal_availability VARCHAR(100) DEFAULT NULL AFTER harvest_date"
		];
		foreach ($inventoryColumns as $column => $alterSql) {
			$columnCheck = mysqli_query($db, "SHOW COLUMNS FROM products LIKE '$column'");
			if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
				@mysqli_query($db, $alterSql);
			}
		}
		@mysqli_query($db, "CREATE TABLE IF NOT EXISTS product_inquiries (
			inquiry_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			product_id INT UNSIGNED NOT NULL,
			buyer_id INT UNSIGNED NOT NULL,
			buyer_email VARCHAR(150) DEFAULT NULL,
			subject VARCHAR(255) NOT NULL,
			message TEXT NOT NULL,
			status TINYINT(1) NOT NULL DEFAULT 0,
			response TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_inquiries_product_status (product_id, status),
			INDEX idx_inquiries_buyer (buyer_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$orderColumns = [
			'order_unit' => "ALTER TABLE order_list ADD COLUMN order_unit VARCHAR(20) NOT NULL DEFAULT 'kilogram' AFTER quantity",
			'delivery_location' => "ALTER TABLE order_list ADD COLUMN delivery_location TEXT DEFAULT NULL AFTER user_phone",
			'delivery_notes' => "ALTER TABLE order_list ADD COLUMN delivery_notes TEXT DEFAULT NULL AFTER delivery_location",
			'delivery_update' => "ALTER TABLE order_list ADD COLUMN delivery_update TEXT DEFAULT NULL AFTER status",
			'updated_at' => "ALTER TABLE order_list ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER join_date"
		];
		foreach ($orderColumns as $column => $alterSql) {
			$columnCheck = mysqli_query($db, "SHOW COLUMNS FROM order_list LIKE '$column'");
			if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
				@mysqli_query($db, $alterSql);
			}
		}
		$commentColumns = [
			'response' => "ALTER TABLE comments ADD COLUMN response TEXT DEFAULT NULL AFTER comments",
			'responded_at' => "ALTER TABLE comments ADD COLUMN responded_at DATETIME DEFAULT NULL AFTER response"
		];
		foreach ($commentColumns as $column => $alterSql) {
			$columnCheck = mysqli_query($db, "SHOW COLUMNS FROM comments LIKE '$column'");
			if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
				@mysqli_query($db, $alterSql);
			}
		}
		// echo "Database Connected Successfully";
	}
	else {
		die("Mysqli Error." . mysqli_error($db));
	}
?>
