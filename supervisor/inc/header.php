<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
ob_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || (int) ($_SESSION['role'] ?? 0) !== 5) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../admin/inc/db.php';

$supervisorTitle = 'Farmers Market Supervisor';
$supervisorRole = 5;
$supervisorName = !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Supervisor';
$managerRole = $supervisorRole;
$managerName = $supervisorName;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($supervisorTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/dashboard-modern.css" rel="stylesheet">
    <script src="../admin/assets/plugins/chartjs/chart.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-tractor me-2"></i>Benny Farmers Market</a>
            <div class="ms-auto d-flex align-items-center gap-3 text-white">
                <span class="badge rounded-pill bg-light text-dark">Supervisor</span>
                <span class="fw-semibold"><?php echo htmlspecialchars($supervisorName); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-0">
                <div class="sidebar-header"><i class="fa-solid fa-bars-progress me-2"></i>Field Operations</div>
                <nav class="nav flex-column p-3 gap-1">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'farmers.php' ? 'active' : ''; ?>" href="farmers.php"><i class="fa-solid fa-tractor me-2"></i>Farm Visits</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'documents.php' ? 'active' : ''; ?>" href="documents.php"><i class="fa-solid fa-file-circle-check me-2"></i>Approve Documents</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'costs.php' ? 'active' : ''; ?>" href="costs.php"><i class="fa-solid fa-wallet me-2"></i>Extra Costs</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php"><i class="fa-solid fa-file-lines me-2"></i>Field Reports</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>" href="profile.php"><i class="fa-solid fa-user-pen me-2"></i>My Profile</a>
                    <a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 content-wrap">
