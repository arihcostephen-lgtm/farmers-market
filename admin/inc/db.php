<?php  
	$db = mysqli_connect("localhost", "root", "", "farmersmkt_db");

	if ($db) {
		mysqli_set_charset($db, "utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS farmer_subscriptions (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			farmer_id INT UNSIGNED NOT NULL,
			plan_id INT UNSIGNED DEFAULT NULL,
			subscription_name VARCHAR(150) NOT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status TINYINT(1) NOT NULL DEFAULT 0,
			approved_by INT UNSIGNED DEFAULT NULL,
			approved_at DATETIME DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_farmer_subscription (farmer_id),
			INDEX idx_farmer_subscriptions_farmer (farmer_id),
			INDEX idx_farmer_subscriptions_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS subscription_plans (
			plan_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			plan_name VARCHAR(150) NOT NULL,
			description TEXT DEFAULT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			duration_days INT UNSIGNED NOT NULL DEFAULT 30,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_by INT UNSIGNED DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_subscription_plans_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$planIdColumn = mysqli_query($db, "SHOW COLUMNS FROM farmer_subscriptions LIKE 'plan_id'");
		if ($planIdColumn && mysqli_num_rows($planIdColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE farmer_subscriptions ADD COLUMN plan_id INT UNSIGNED DEFAULT NULL AFTER farmer_id");
		}
		$planCountQuery = mysqli_query($db, "SELECT COUNT(*) AS total FROM subscription_plans");
		if ($planCountQuery && (int) mysqli_fetch_assoc($planCountQuery)['total'] === 0) {
			@mysqli_query($db, "INSERT INTO subscription_plans (plan_name, description, amount, duration_days, status, created_at) VALUES ('Standard Plan', 'Access to farmer product listings, orders, inquiries, and reports.', 50000, 30, 1, NOW())");
		}
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS tax_rules (
			rule_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			rule_name VARCHAR(150) NOT NULL,
			rate_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
			min_quantity INT UNSIGNED NOT NULL DEFAULT 0,
			max_quantity INT UNSIGNED DEFAULT NULL,
			applies_to VARCHAR(50) NOT NULL DEFAULT 'all',
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_tax_rules_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS staff_payroll (
			staff_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			staff_name VARCHAR(150) NOT NULL,
			staff_role VARCHAR(100) NOT NULL,
			email VARCHAR(150) DEFAULT NULL,
			phone VARCHAR(30) DEFAULT NULL,
			salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status TINYINT(1) NOT NULL DEFAULT 0,
			paid_at DATETIME DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_staff_payroll_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS extra_costs (
			cost_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			cost_name VARCHAR(150) NOT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			notes TEXT DEFAULT NULL,
			created_by INT UNSIGNED DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_extra_costs_created_by (created_by)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS manager_activity_log (
			log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			actor_id INT UNSIGNED NOT NULL,
			actor_name VARCHAR(150) DEFAULT NULL,
			action_type VARCHAR(100) NOT NULL,
			target_type VARCHAR(100) DEFAULT NULL,
			target_id INT UNSIGNED DEFAULT NULL,
			notes TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_manager_activity_actor (actor_id),
			INDEX idx_manager_activity_type (action_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_document_reviews (
			review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			document_path VARCHAR(500) NOT NULL,
			status ENUM('approved', 'rejected') NOT NULL,
			reviewed_by INT UNSIGNED NOT NULL,
			reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_supervisor_document_path (document_path),
			INDEX idx_supervisor_document_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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
			'tax_amount' => "ALTER TABLE order_list ADD COLUMN tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price",
			'total_amount' => "ALTER TABLE order_list ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tax_amount",
			'updated_at' => "ALTER TABLE order_list ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER join_date"
		];
		$managerRoleCheck = mysqli_query($db, "SHOW COLUMNS FROM users LIKE 'role'");
		if ($managerRoleCheck && mysqli_num_rows($managerRoleCheck) > 0) {
			$managerRoleValues = mysqli_query($db, "SELECT DISTINCT role FROM users WHERE role IN (4,5)");
			if ($managerRoleValues && mysqli_num_rows($managerRoleValues) === 0) {
				// Role values 4 and 5 are reserved for manager and supervisor access.
			}
		}
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
