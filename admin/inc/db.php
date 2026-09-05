<?php  
	$db = mysqli_connect("localhost", "root", "", "farmersmkt_db");

	if ($db) {
		mysqli_set_charset($db, "utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS manager_profiles (
			manager_id INT UNSIGNED PRIMARY KEY,
			department VARCHAR(150) DEFAULT NULL,
			hire_date DATE DEFAULT NULL,
			notes TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_manager_profiles_department (department)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_profiles (
			supervisor_id INT UNSIGNED PRIMARY KEY,
			region VARCHAR(150) DEFAULT NULL,
			specialization VARCHAR(150) DEFAULT NULL,
			hire_date DATE DEFAULT NULL,
			notes TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_supervisor_profiles_region (region)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_activity_log (
			log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			actor_id INT UNSIGNED NOT NULL,
			actor_name VARCHAR(150) DEFAULT NULL,
			action_type VARCHAR(100) NOT NULL,
			target_type VARCHAR(100) DEFAULT NULL,
			target_id INT UNSIGNED DEFAULT NULL,
			notes TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_supervisor_activity_actor (actor_id),
			INDEX idx_supervisor_activity_type (action_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "INSERT IGNORE INTO manager_profiles (manager_id)
			SELECT user_id FROM users WHERE role = 4");
		mysqli_query($db, "INSERT IGNORE INTO supervisor_profiles (supervisor_id)
			SELECT user_id FROM users WHERE role = 5");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS farmer_notifications (
			notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			farmer_id INT UNSIGNED NOT NULL,
			farm_id INT UNSIGNED DEFAULT NULL,
			visit_id INT UNSIGNED DEFAULT NULL,
			notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
			title VARCHAR(200) NOT NULL,
			message TEXT NOT NULL,
			is_read TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			read_at DATETIME DEFAULT NULL,
			INDEX idx_farmer_notifications_farmer (farmer_id, is_read),
			INDEX idx_farmer_notifications_visit (visit_id),
			INDEX idx_farmer_notifications_created (created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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
		$ussdCodeColumn = mysqli_query($db, "SHOW COLUMNS FROM farmer_subscriptions LIKE 'ussd_code'");
		if ($ussdCodeColumn && mysqli_num_rows($ussdCodeColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE farmer_subscriptions ADD COLUMN ussd_code VARCHAR(50) DEFAULT NULL AFTER approved_at");
			@mysqli_query($db, "ALTER TABLE farmer_subscriptions ADD COLUMN mobile_money_instructions TEXT DEFAULT NULL AFTER ussd_code");
			@mysqli_query($db, "ALTER TABLE farmer_subscriptions ADD COLUMN payment_reference VARCHAR(100) DEFAULT NULL AFTER mobile_money_instructions");
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
			applies_unit VARCHAR(20) NOT NULL DEFAULT 'all',
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_tax_rules_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$taxUnitColumn = mysqli_query($db, "SHOW COLUMNS FROM tax_rules LIKE 'applies_unit'");
		if ($taxUnitColumn && mysqli_num_rows($taxUnitColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE tax_rules ADD COLUMN applies_unit VARCHAR(20) NOT NULL DEFAULT 'all' AFTER applies_to");
		}
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS staff_payroll (
			staff_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id INT UNSIGNED DEFAULT NULL,
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
		$staffUserColumn = mysqli_query($db, "SHOW COLUMNS FROM staff_payroll LIKE 'user_id'");
		if ($staffUserColumn && mysqli_num_rows($staffUserColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE staff_payroll ADD COLUMN user_id INT UNSIGNED DEFAULT NULL AFTER staff_id");
		}
		$staffUserIndex = mysqli_query($db, "SHOW INDEX FROM staff_payroll WHERE Key_name = 'idx_staff_payroll_user'");
		if ($staffUserIndex && mysqli_num_rows($staffUserIndex) === 0) {
			@mysqli_query($db, "ALTER TABLE staff_payroll ADD INDEX idx_staff_payroll_user (user_id)");
		}
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS extra_costs (
			cost_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			cost_name VARCHAR(150) NOT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			notes TEXT DEFAULT NULL,
			created_by INT UNSIGNED DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_extra_costs_created_by (created_by)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS extra_cost_requests (
			request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			requested_by INT UNSIGNED NOT NULL,
			requested_by_name VARCHAR(150) NOT NULL,
			cost_name VARCHAR(150) NOT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			reason TEXT NOT NULL,
			status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
			approved_by INT UNSIGNED DEFAULT NULL,
			approved_at DATETIME DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_extra_cost_requests_status (status),
			INDEX idx_extra_cost_requests_requested_by (requested_by)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$requestApproverIndex = mysqli_query($db, "SHOW INDEX FROM extra_cost_requests WHERE Key_name = 'idx_extra_cost_requests_approved_by'");
		if ($requestApproverIndex && mysqli_num_rows($requestApproverIndex) === 0) {
			@mysqli_query($db, "ALTER TABLE extra_cost_requests ADD INDEX idx_extra_cost_requests_approved_by (approved_by)");
		}
		$requestOwnerDateIndex = mysqli_query($db, "SHOW INDEX FROM extra_cost_requests WHERE Key_name = 'idx_extra_cost_requests_owner_date'");
		if ($requestOwnerDateIndex && mysqli_num_rows($requestOwnerDateIndex) === 0) {
			@mysqli_query($db, "ALTER TABLE extra_cost_requests ADD INDEX idx_extra_cost_requests_owner_date (requested_by, created_at)");
		}
		$costDateIndex = mysqli_query($db, "SHOW INDEX FROM extra_costs WHERE Key_name = 'idx_extra_costs_created_date'");
		if ($costDateIndex && mysqli_num_rows($costDateIndex) === 0) {
			@mysqli_query($db, "ALTER TABLE extra_costs ADD INDEX idx_extra_costs_created_date (created_at, cost_id)");
		}
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
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_reports (
			report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			supervisor_id INT UNSIGNED NOT NULL,
			supervisor_name VARCHAR(150) NOT NULL,
			title VARCHAR(200) NOT NULL,
			report_body TEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_supervisor_reports_created (created_at),
			INDEX idx_supervisor_reports_supervisor (supervisor_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS product_reviews (
			review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			product_id INT UNSIGNED NOT NULL,
			buyer_id INT UNSIGNED NOT NULL,
			order_id INT UNSIGNED NOT NULL,
			rating TINYINT UNSIGNED NOT NULL,
			review_text TEXT NOT NULL,
			status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_product_review_order (order_id),
			INDEX idx_product_reviews_product_status (product_id, status),
			INDEX idx_product_reviews_buyer (buyer_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS farmer_ratings (
			rating_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			farmer_email VARCHAR(150) NOT NULL,
			buyer_id INT UNSIGNED NOT NULL,
			order_id INT UNSIGNED NOT NULL,
			rating TINYINT UNSIGNED NOT NULL,
			review_text TEXT DEFAULT NULL,
			status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_farmer_rating_order (order_id),
			INDEX idx_farmer_ratings_farmer_status (farmer_email, status),
			INDEX idx_farmer_ratings_buyer (buyer_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_report_attachments (
			attachment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			report_id INT UNSIGNED NOT NULL,
			attachment_name VARCHAR(255) NOT NULL,
			attachment_path VARCHAR(500) NOT NULL,
			attachment_type VARCHAR(100) NOT NULL,
			attachment_size INT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_report_attachments_report (report_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS farm_visits (
			visit_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			farm_id INT UNSIGNED NOT NULL,
			supervisor_id INT UNSIGNED NOT NULL,
			visit_date DATE NOT NULL,
			status ENUM('Scheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
			notes TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_farm_visits_farm (farm_id),
			INDEX idx_farm_visits_supervisor (supervisor_id),
			INDEX idx_farm_visits_date (visit_date)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$farmDocumentColumn = mysqli_query($db, "SHOW COLUMNS FROM farmer LIKE 'farm_document'");
		if ($farmDocumentColumn && mysqli_num_rows($farmDocumentColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE farmer ADD COLUMN farm_document VARCHAR(255) DEFAULT NULL AFTER farm_address");
		}
		$marketColumns = [
			'farm_latitude' => "ALTER TABLE farmer ADD COLUMN farm_latitude DECIMAL(10,7) DEFAULT NULL AFTER farm_address",
			'farm_longitude' => "ALTER TABLE farmer ADD COLUMN farm_longitude DECIMAL(10,7) DEFAULT NULL AFTER farm_latitude",
			'market_name' => "ALTER TABLE farmer ADD COLUMN market_name VARCHAR(255) DEFAULT NULL AFTER farm_longitude",
			'market_address' => "ALTER TABLE farmer ADD COLUMN market_address VARCHAR(255) DEFAULT NULL AFTER market_name",
			'market_latitude' => "ALTER TABLE farmer ADD COLUMN market_latitude DECIMAL(10,7) DEFAULT NULL AFTER market_address",
			'market_longitude' => "ALTER TABLE farmer ADD COLUMN market_longitude DECIMAL(10,7) DEFAULT NULL AFTER market_latitude",
			'market_operating_days' => "ALTER TABLE farmer ADD COLUMN market_operating_days VARCHAR(255) DEFAULT NULL AFTER market_longitude",
			'market_hours' => "ALTER TABLE farmer ADD COLUMN market_hours VARCHAR(120) DEFAULT NULL AFTER market_operating_days",
			'pickup_instructions' => "ALTER TABLE farmer ADD COLUMN pickup_instructions TEXT DEFAULT NULL AFTER market_hours",
			'delivery_instructions' => "ALTER TABLE farmer ADD COLUMN delivery_instructions TEXT DEFAULT NULL AFTER pickup_instructions"
		];
		foreach ($marketColumns as $column => $alterSql) {
			$columnCheck = mysqli_query($db, "SHOW COLUMNS FROM farmer LIKE '$column'");
			if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
				@mysqli_query($db, $alterSql);
			}
		}
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS supervisor_document_reviews (
			review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			document_path VARCHAR(500) NOT NULL,
			status ENUM('approved', 'rejected') NOT NULL,
			reviewed_by INT UNSIGNED NOT NULL,
			reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_supervisor_document_path (document_path),
			INDEX idx_supervisor_document_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS admin_document_reviews (
			review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			document_path VARCHAR(500) NOT NULL,
			status ENUM('approved', 'rejected') NOT NULL,
			reviewed_by INT UNSIGNED NOT NULL,
			reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uq_admin_document_path (document_path),
			INDEX idx_admin_document_status (status)
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
			'updated_at' => "ALTER TABLE order_list ADD COLUMN updated_at DATETIME DEFAULT NULL AFTER join_date",
			'payment_status' => "ALTER TABLE order_list ADD COLUMN payment_status ENUM('unpaid', 'pending', 'paid', 'failed') NOT NULL DEFAULT 'unpaid' AFTER status"
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
		@mysqli_query($db, "CREATE TABLE IF NOT EXISTS payment_transactions (
			payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			order_id INT UNSIGNED NOT NULL,
			user_id INT UNSIGNED NOT NULL,
			provider ENUM('mtn_uganda', 'airtel_uganda', 'ussd') NOT NULL,
			amount DECIMAL(12,2) NOT NULL,
			phone VARCHAR(30) NOT NULL,
			reference VARCHAR(100) NOT NULL,
			provider_reference VARCHAR(150) DEFAULT NULL,
			status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
			provider_response TEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			UNIQUE KEY uq_payment_reference (reference),
			INDEX idx_payment_order (order_id),
			INDEX idx_payment_provider_reference (provider_reference),
			INDEX idx_payment_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		@mysqli_query($db, "CREATE TABLE IF NOT EXISTS payment_batches (
			batch_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_id INT UNSIGNED NOT NULL,
			amount DECIMAL(12,2) NOT NULL,
			phone VARCHAR(30) NOT NULL,
			provider ENUM('mtn_uganda', 'airtel_uganda', 'ussd') NOT NULL,
			reference VARCHAR(100) NOT NULL UNIQUE,
			status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT NULL,
			INDEX idx_payment_batches_user (user_id),
			INDEX idx_payment_batches_status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		@mysqli_query($db, "CREATE TABLE IF NOT EXISTS payment_batch_orders (
			batch_id BIGINT UNSIGNED NOT NULL,
			order_id INT UNSIGNED NOT NULL,
			PRIMARY KEY (batch_id, order_id),
			INDEX idx_payment_batch_orders_order (order_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$paymentBatchColumn = mysqli_query($db, "SHOW COLUMNS FROM payment_transactions LIKE 'batch_id'");
		if ($paymentBatchColumn && mysqli_num_rows($paymentBatchColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE payment_transactions ADD COLUMN batch_id BIGINT UNSIGNED DEFAULT NULL AFTER payment_id");
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
		// Locations table for Ntungamo areas
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS locations (
			location_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			location_name VARCHAR(100) NOT NULL UNIQUE,
			district VARCHAR(50) NOT NULL DEFAULT 'Ntungamo',
			description TEXT DEFAULT NULL,
			latitude DECIMAL(10,7) DEFAULT NULL,
			longitude DECIMAL(10,7) DEFAULT NULL,
			is_active TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_location_name (location_name),
			INDEX idx_location_active (is_active)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		// Add coordinate columns for older installations that already created the table
		$locationCoordCheck = mysqli_query($db, "SHOW COLUMNS FROM locations LIKE 'latitude'");
		if ($locationCoordCheck && mysqli_num_rows($locationCoordCheck) === 0) {
			@mysqli_query($db, "ALTER TABLE locations ADD COLUMN latitude DECIMAL(10,7) DEFAULT NULL AFTER description");
			@mysqli_query($db, "ALTER TABLE locations ADD COLUMN longitude DECIMAL(10,7) DEFAULT NULL AFTER latitude");
		}
		// Insert default Ntungamo locations if empty
		$locationCount = mysqli_query($db, "SELECT COUNT(*) as cnt FROM locations");
		if ($locationCount && (int) mysqli_fetch_assoc($locationCount)['cnt'] === 0) {
			@mysqli_query($db, "INSERT INTO locations (location_name, district, description, latitude, longitude) VALUES
			('Ntungamo Town', 'Ntungamo', 'Central Ntungamo town area', -0.7991, 29.7606),
			('Rubaare', 'Ntungamo', 'Rubaare division', -0.8245, 29.9002),
			('Bushenyi', 'Ntungamo', 'Bushenyi division', -0.5614, 30.1897),
			('Kabwohe', 'Ntungamo', 'Kabwohe area', -0.6447, 30.0709),
			('Mirama Hills', 'Ntungamo', 'Mirama Hills region', -0.7655, 30.5660),
			('Kanungu', 'Ntungamo', 'Kanungu area', -0.9571, 29.7339),
			('Rukungiri', 'Ntungamo', 'Rukungiri division', -0.7625, 29.9272),
			('Katuna', 'Ntungamo', 'Katuna border area', -1.2456, 29.8019),
			('Rukoki', 'Ntungamo', 'Rukoki area', -0.7791, 30.0798),
			('Kisoro', 'Ntungamo', 'Kisoro area', -1.2850, 29.6801)");
		}
		// Ensure existing rows have latitude/longitude values where missing
		@mysqli_query($db, "UPDATE locations SET latitude = -0.7991, longitude = 29.7606 WHERE latitude IS NULL AND location_name = 'Ntungamo Town'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.8245, longitude = 29.9002 WHERE latitude IS NULL AND location_name = 'Rubaare'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.5614, longitude = 30.1897 WHERE latitude IS NULL AND location_name = 'Bushenyi'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.6447, longitude = 30.0709 WHERE latitude IS NULL AND location_name = 'Kabwohe'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.7655, longitude = 30.5660 WHERE latitude IS NULL AND location_name = 'Mirama Hills'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.9571, longitude = 29.7339 WHERE latitude IS NULL AND location_name = 'Kanungu'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.7625, longitude = 29.9272 WHERE latitude IS NULL AND location_name = 'Rukungiri'");
		@mysqli_query($db, "UPDATE locations SET latitude = -1.2456, longitude = 29.8019 WHERE latitude IS NULL AND location_name = 'Katuna'");
		@mysqli_query($db, "UPDATE locations SET latitude = -0.7791, longitude = 30.0798 WHERE latitude IS NULL AND location_name = 'Rukoki'");
		@mysqli_query($db, "UPDATE locations SET latitude = -1.2850, longitude = 29.6801 WHERE latitude IS NULL AND location_name = 'Kisoro'");
		// Add location columns to farmer table
		$farmLocationColumn = mysqli_query($db, "SHOW COLUMNS FROM farmer LIKE 'farm_location_id'");
		if ($farmLocationColumn && mysqli_num_rows($farmLocationColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE farmer ADD COLUMN farm_location_id INT UNSIGNED DEFAULT NULL AFTER farm_address");
			@mysqli_query($db, "ALTER TABLE farmer ADD INDEX idx_farm_location (farm_location_id)");
		}
		$marketLocationColumn = mysqli_query($db, "SHOW COLUMNS FROM farmer LIKE 'market_location_id'");
		if ($marketLocationColumn && mysqli_num_rows($marketLocationColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE farmer ADD COLUMN market_location_id INT UNSIGNED DEFAULT NULL AFTER market_address");
			@mysqli_query($db, "ALTER TABLE farmer ADD INDEX idx_market_location (market_location_id)");
		}
		// Add delivery location to order_list table
		$deliveryLocationColumn = mysqli_query($db, "SHOW COLUMNS FROM order_list LIKE 'delivery_location_id'");
		if ($deliveryLocationColumn && mysqli_num_rows($deliveryLocationColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE order_list ADD COLUMN delivery_location_id INT UNSIGNED DEFAULT NULL AFTER delivery_location");
			@mysqli_query($db, "ALTER TABLE order_list ADD INDEX idx_order_delivery_location (delivery_location_id)");
		}
		// Messaging and notifications tables
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS conversations (
			conversation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			customer_id INT UNSIGNED NOT NULL,
			farmer_id INT UNSIGNED NOT NULL,
			product_id INT UNSIGNED DEFAULT NULL,
			subject VARCHAR(255) DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			UNIQUE KEY unique_conversation (customer_id, farmer_id),
			INDEX idx_conversation_customer (customer_id),
			INDEX idx_conversation_farmer (farmer_id),
			INDEX idx_conversation_created (created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS messages (
			message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			conversation_id INT UNSIGNED NOT NULL,
			sender_id INT UNSIGNED NOT NULL,
			sender_type ENUM('customer', 'farmer') NOT NULL,
			message_text TEXT NOT NULL,
			attachment_path VARCHAR(255) DEFAULT NULL,
			is_read TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			read_at DATETIME DEFAULT NULL,
			INDEX idx_messages_conversation (conversation_id),
			INDEX idx_messages_sender (sender_id),
			INDEX idx_messages_created (created_at),
			FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		$messagesReadColumn = mysqli_query($db, "SHOW COLUMNS FROM messages LIKE 'is_read'");
		if ($messagesReadColumn && mysqli_num_rows($messagesReadColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER attachment_path");
		}
		$messagesReadAtColumn = mysqli_query($db, "SHOW COLUMNS FROM messages LIKE 'read_at'");
		if ($messagesReadAtColumn && mysqli_num_rows($messagesReadAtColumn) === 0) {
			@mysqli_query($db, "ALTER TABLE messages ADD COLUMN read_at DATETIME DEFAULT NULL AFTER created_at");
		}
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS manager_notifications (
			notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			manager_id INT UNSIGNED NOT NULL,
			notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
			title VARCHAR(255) NOT NULL,
			message TEXT NOT NULL,
			related_entity_type VARCHAR(100) DEFAULT NULL,
			related_entity_id INT UNSIGNED DEFAULT NULL,
			is_read TINYINT(1) NOT NULL DEFAULT 0,
			action_url VARCHAR(255) DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			read_at DATETIME DEFAULT NULL,
			INDEX idx_manager_notifications_manager (manager_id, is_read),
			INDEX idx_manager_notifications_type (notification_type),
			INDEX idx_manager_notifications_created (created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		mysqli_query($db, "CREATE TABLE IF NOT EXISTS system_notifications (
			notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
			title VARCHAR(255) NOT NULL,
			message TEXT NOT NULL,
			target_role TINYINT(1) NOT NULL DEFAULT 4,
			icon_class VARCHAR(100) DEFAULT 'fas fa-bell',
			action_url VARCHAR(255) DEFAULT NULL,
			is_active TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			expires_at DATETIME DEFAULT NULL,
			INDEX idx_system_notifications_type (notification_type),
			INDEX idx_system_notifications_created (created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		// echo "Database Connected Successfully";
	}
	else {
		die("Mysqli Error." . mysqli_error($db));
	}
?>
