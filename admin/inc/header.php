<?php 
	session_start(); 
	ob_start();
	require_once __DIR__ . '/db.php';
	require_once __DIR__ . '/email.php';

	if ( empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || empty($_SESSION['role']) || (int) $_SESSION['role'] !== 1 ) {
		header("Location: index.php");
		exit;
	}
	$adminSiteSettings = [];
	$adminSiteSettingsSql = @mysqli_query($db, "SELECT * FROM site_settings ORDER BY id DESC LIMIT 1");
	if ($adminSiteSettingsSql) {
		$adminSiteSettings = mysqli_fetch_assoc($adminSiteSettingsSql) ?: [];
	}
	$adminSiteTitle = htmlspecialchars($adminSiteSettings['site_title'] ?? 'Farmers Market');?>

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png" />
	<!--plugins-->
	<link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />	
	<link href="assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
	<!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet" />
	<script src="assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">

	<!-- FONT AWESOME CDN LINK -->
	<script src="https://kit.fontawesome.com/0c66e46c25.js" crossorigin="anonymous"></script>
	
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="assets/css/dark-theme.css" />
	<link rel="stylesheet" href="assets/css/semi-dark.css" />
	<link rel="stylesheet" href="assets/css/header-colors.css" />
	<link rel="stylesheet" href="assets/css/custom.css" />
	<style>
		/* overall page and content colors - subtle green tint */
		body, .wrapper, .page-wrapper {
			background: linear-gradient(180deg,#062a1f 0%, #08130f 100%) !important;
			color: #e9fff4 !important;
		}
		/* content panels have a slightly lighter overlay to show cards */
		.page-content {
			background: rgba(6,42,31,0.04) !important;
			color: #e9fff4 !important;
		}
		/* unify readable text color across UI elements */
		body, .page-wrapper, .page-content, .page-footer, .card, .card-header, .card-body, .modal-content, .table, .dataTables_wrapper, .form-control, .form-select, .form-check-label, label, p, span, th, td, li, a {
			color: #e9fff4 !important;
		}
		/* link and accent colors tuned to green theme */
		a, .page-content a, .breadcrumb-item a, .page-footer a {
			color: #9ef7b8 !important;
		}
		a:hover, .page-content a:hover, .breadcrumb-item a:hover, .page-footer a:hover {
			color: #c9ffd9 !important;
		}
		.page-footer {
			background: #052a1c !important;
			color: #cfeede !important;
			border-top: 1px solid rgba(255,255,255,0.06);
		}
		.card, .card-body, .card-header, .table, .dataTables_wrapper, .modal-content {
			background: rgba(255,255,255,0.03) !important;
			border-color: rgba(255,255,255,0.06) !important;
		}
		.card .card-header, .card .card-title, .page-title, .breadcrumb, .breadcrumb-item a {
			color: #e9fff4 !important;
		}
		.table thead th, .table tbody td {
			border-color: rgba(255,255,255,0.06) !important;
		}
		.btn, .btn-outline-success, .btn-outline-success:hover, .btn-outline-success:focus, .btn-primary, .btn-secondary {
			color: #e9fff4 !important;
		}
		.btn-outline-success {
			border-color: #22c55e !important;
			color: #c7ffde !important;
		}
		.form-control, .form-select {
			background: rgba(255,255,255,0.04) !important;
			border-color: rgba(255,255,255,0.1) !important;
			color: #eafef4 !important;
		}
		.form-control::placeholder, .form-select {
			color: rgba(230, 255, 240, 0.65) !important;
		}
		.table thead th {
			color: #e9fff4 !important;
		}
		.table tbody tr:hover {
			background: rgba(34,197,94,0.06) !important;
		}
	</style>
	<title><?php echo $adminSiteTitle; ?> | Admin Dashboard</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">

		<!-- ########## START: LEFT MENU ########## -->
		<?php include __DIR__ . '/leftmenu.php'; ?>
		<!-- ########## END: LEFT MENU ########## -->

		<!-- ########## START: SIDE BAR ########## -->
		<?php include __DIR__ . '/topbar.php'; ?>
		<!-- ########## END: SIDE BAR ########## -->