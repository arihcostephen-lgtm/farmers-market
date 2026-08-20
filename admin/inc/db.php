<?php  
	$db = mysqli_connect("localhost", "root", "", "farmersmkt_db");

	if ($db) {
		mysqli_set_charset($db, "utf8mb4");
		// echo "Database Connected Successfully";
	}
	else {
		die("Mysqli Error." . mysqli_error($db));
	}
?>
