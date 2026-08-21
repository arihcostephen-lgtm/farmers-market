<?php
session_start();
ob_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || empty($_SESSION['role']) || !in_array((int) $_SESSION['role'], [4, 5], true)) {
    header('Location: ../manager/login.php');
    exit;
}

require_once __DIR__ . '/../../admin/inc/db.php';

$managerTitle = 'Farmers Market Manager';
$managerRole = (int) $_SESSION['role'];
$managerName = !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Manager';
$managerStatusLabel = $managerRole === 4 ? 'Manager' : 'Supervisor';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($managerTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="../admin/assets/plugins/chartjs/chart.min.js"></script>
    <style>
        body { background: #f3f7f4; color: #153126; }
        .navbar { background: linear-gradient(135deg, #0c4d39, #123d29); }
        .sidebar { background: #0d241d; min-height: calc(100vh - 72px); }
        .sidebar .nav-link { color: rgba(255,255,255,.8); }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .content-wrap { padding: 24px; }
        .card { border: 0; border-radius: 16px; box-shadow: 0 12px 26px rgba(10,30,22,.08); }
        .metric { background: linear-gradient(135deg, #ebfff5, #f4f9f7); }
        .badge-status { font-size: 12px; }
        .table thead { background: #edf7f1; }
        .data-table th, .data-table td { vertical-align: middle; }
        .page-header { background: linear-gradient(135deg, #0d4c3b, #1d6d52); color: #fff; margin-bottom: 20px; border-radius: 16px; padding: 26px 28px; }
        .chart-wrap { position: relative; height: 300px; }
        .chart-wrap-doughnut { height: 300px; }
        .alert-success { background: #dffbe8; color: #0d4d39; border-color: #a9e6bf; }
        .alert-warning { background: #fff3cd; color: #7a5c00; border-color: #ffe39b; }
        .alert-danger { background: #ffe5e5; color: #7f1d1d; border-color: #f5b7b1; }
        @media (max-width: 767.98px) {
            .content-wrap { padding: 16px; }
            .chart-wrap, .chart-wrap-doughnut { height: 240px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark px-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-tractor me-2"></i>Farmers Market Manager</a>
            <div class="ms-auto d-flex align-items-center gap-3 text-white">
                <span class="badge rounded-pill bg-light text-dark"><?php echo htmlspecialchars($managerStatusLabel); ?></span>
                <span class="fw-semibold"><?php echo htmlspecialchars($managerName); ?></span>
                <a href="../manager/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3 border-bottom border-secondary-subtle text-white fw-semibold">
                    <i class="fa-solid fa-bars-progress me-2"></i>Operations
                </div>
                <nav class="nav flex-column p-3 gap-1">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admins.php' ? 'active' : ''; ?>" href="admins.php"><i class="fa-solid fa-user-shield me-2"></i>Admins & Supervisors</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'farmers.php' ? 'active' : ''; ?>" href="farmers.php"><i class="fa-solid fa-tractor me-2"></i>Farmer Subscriptions</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'transactions.php' ? 'active' : ''; ?>" href="transactions.php"><i class="fa-solid fa-money-check me-2"></i>Transactions</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'taxes.php' ? 'active' : ''; ?>" href="taxes.php"><i class="fa-solid fa-percent me-2"></i>Tax Rules</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'staff.php' ? 'active' : ''; ?>" href="staff.php"><i class="fa-solid fa-user-tie me-2"></i>Staff Payroll</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'costs.php' ? 'active' : ''; ?>" href="costs.php"><i class="fa-solid fa-wallet me-2"></i>Extra Costs</a>
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php"><i class="fa-solid fa-file-lines me-2"></i>Reports</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 content-wrap">
