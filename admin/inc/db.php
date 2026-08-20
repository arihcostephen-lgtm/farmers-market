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
		// echo "Database Connected Successfully";
	}
	else {
		die("Mysqli Error." . mysqli_error($db));
	}
?>
