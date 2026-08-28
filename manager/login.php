<?php
session_start();
ob_start();

include __DIR__ . '/../admin/inc/db.php';

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_email']) && (int) ($_SESSION['role'] ?? 0) === 4) {
    header('Location: dashboard.php');
    exit;
}

$login_error = '';

if (isset($_POST['managerSubmit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = mysqli_real_escape_string($db, $email);
    $shaPass = sha1($password);

    $query = mysqli_query($db, "SELECT * FROM users WHERE user_email = '$email' AND role = 4 AND status = 1 LIMIT 1");

    if (!$query || mysqli_num_rows($query) === 0) {
        $login_error = 'No active manager or supervisor account was found for that email.';
    } else {
        $row = mysqli_fetch_assoc($query);
        if ($row['user_password'] === $shaPass) {
            $_SESSION['user_id'] = (int) $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['user_email'] = $row['user_email'];
            $_SESSION['role'] = (int) $row['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $login_error = 'Invalid manager credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manager Login | Farmers Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eafaf2, #d5f5e2 45%, #b9f2d0);
            font-family: Arial, sans-serif;
        }
        .login-card {
            width: min(100%, 900px);
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(11, 74, 47, 0.18);
            display: flex;
            flex-direction: row;
        }
        .login-panel { width: 50%; min-width: 0; }
        .panel-left {
            background: linear-gradient(135deg, #0e5a3a, #1aa661);
            color: #fff;
            padding: 52px 42px;
        }
        .panel-right {
            padding: 52px 42px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(22,163,74,0.2);
            border-color: #1aa661;
        }
        .btn-success {
            background: linear-gradient(135deg, #0d8b47, #1aa661);
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-panel panel-left d-flex flex-column justify-content-center">
            <span class="badge bg-light text-success rounded-pill mb-3 align-self-start px-3 py-2 fw-semibold">Manager Access</span>
            <h1 class="fw-bold mb-3">Farmers Market</h1>
            <p class="mb-4">Monitor sales, manage farmer subscriptions, handle payroll, and review system-wide operations from one secure manager dashboard.</p>
            <ul class="list-unstyled mb-0">
                <li class="mb-2">• Track transactions and reports</li>
                <li class="mb-2">• Approve farmer subscriptions</li>
                <li class="mb-2">• Configure quantity-based tax rules</li>
                <li>• Manage staff and extra costs</li>
            </ul>
        </div>
        <div class="login-panel panel-right">
            <h3 class="mb-2 fw-bold">Manager / Supervisor Sign in</h3>
            <p class="text-muted mb-4">Use your manager or supervisor account to access the operations dashboard.</p>

            <?php if (!empty($login_error)): ?>
                <div class="alert alert-warning" role="alert"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Enter email" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Enter password" required>
                </div>
                <button type="submit" name="managerSubmit" class="btn btn-success btn-lg w-100">Sign in</button>
                <div class="mt-3 text-center text-muted small">
                    Manager login: ben@gmail.com / ben1234
                </div>
            </form>
        </div>
    </div>
    <style>
        @media (max-width: 767.98px) {
            .login-card { flex-direction: column; }
            .login-panel { width: 100%; }
            .panel-left, .panel-right { padding: 32px 28px; }
        }
    </style>
</body>
</html>
<?php ob_end_flush(); ?>
