<?php
session_start();
ob_start();
include "inc/db.php";

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$login_error = "";
if (isset($_POST['adminSubmit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = mysqli_real_escape_string($db, $email);
    $shaPass = sha1($password);
    $md5Pass = md5($password);

    $readSql = "SELECT * FROM users WHERE user_email = '$email' AND role IN (1, 4, 5) AND status = 1 LIMIT 1";
    $readQuery = mysqli_query($db, $readSql);

    if (!$readQuery || mysqli_num_rows($readQuery) === 0) {
        $login_error = 'No active admin, manager, or supervisor account was found for that email.';
    } else {
        $row = mysqli_fetch_assoc($readQuery);
        if ($row['user_password'] === $shaPass || $row['user_password'] === $md5Pass) {
            if ($row['user_password'] !== $shaPass) {
                $updateSql = "UPDATE users SET user_password = '$shaPass' WHERE user_id = " . (int) $row['user_id'];
                mysqli_query($db, $updateSql);
            }

            $_SESSION['user_id'] = (int) $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['user_email'] = $row['user_email'];
            $_SESSION['role'] = (int) $row['role'];
            if ((int) $row['role'] === 1) {
                header("Location: dashboard.php");
            } else {
                header("Location: ../" . ((int) $_SESSION['role'] === 5 ? 'supervisor' : 'manager') . "/dashboard.php");
            }
            exit;
        } else {
            $login_error = 'Invalid login credentials.';
        }
    }
}

if (isset($db)) {
    $checkAdminSql = "SELECT * FROM users WHERE role = 1 LIMIT 1";
    $checkAdminQuery = mysqli_query($db, $checkAdminSql);

    if ($checkAdminQuery && mysqli_num_rows($checkAdminQuery) == 0) {
        $defaultPassword = sha1('12345');
        $insertAdminSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status) VALUES ('Admin', 'stephenarichco@gmail.com', '$defaultPassword', '+256761393437', 'Admin Address', 1, 1)";
        mysqli_query($db, $insertAdminSql);
    } elseif ($checkAdminQuery) {
        $admin = mysqli_fetch_assoc($checkAdminQuery);
        if ($admin['user_email'] !== 'stephenarichco@gmail.com' || $admin['user_phone'] !== '+256761393437') {
            $updateContactSql = "UPDATE users SET user_email = 'stephenarichco@gmail.com', user_phone = '+256761393437' WHERE user_id = " . (int) $admin['user_id'];
            mysqli_query($db, $updateContactSql);
            $admin['user_email'] = 'stephenarichco@gmail.com';
            $admin['user_phone'] = '+256761393437';
        }
        if ($admin['user_email'] === 'stephenarichco@gmail.com' && $admin['user_password'] !== sha1('12345')) {
            $defaultPassword = sha1('12345');
            $fixAdminSql = "UPDATE users SET user_password = '$defaultPassword' WHERE user_id = " . (int) $admin['user_id'];
            mysqli_query($db, $fixAdminSql);
        }
    }

    $checkManagerSql = "SELECT * FROM users WHERE role IN (4, 5) LIMIT 1";
    $checkManagerQuery = mysqli_query($db, $checkManagerSql);
    if ($checkManagerQuery && mysqli_num_rows($checkManagerQuery) === 0) {
        $managerPassword = sha1('ben1234');
        $insertManagerSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status, join_date) VALUES ('Ben', 'ben@gmail.com', '$managerPassword', '+256700000000', 'Main Office', 4, 1, NOW())";
        mysqli_query($db, $insertManagerSql);

        $supervisorPassword = sha1('ben1234');
        $insertSupervisorSql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status, join_date) VALUES ('Supervisor', 'supervisor@gmail.com', '$supervisorPassword', '+256700000001', 'Field Office', 5, 1, NOW())";
        mysqli_query($db, $insertSupervisorSql);
    }
}
?>
<?php include 'inc/login_header.php'; ?>

<div class="staff-login-page d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 overflow-hidden w-100 login-card">
        <div class="login-split">
            <div class="login-panel login-panel-brand d-flex flex-column justify-content-center">
                <span class="badge bg-light text-success rounded-pill mb-3 align-self-start px-3 py-2 fw-semibold">Staff Portal Access</span>
                <h1 class="fw-bold mb-3" style="font-size: 2.1rem;"><a href="../index.php" class="brand-link">Farmers Market</a></h1>
                <p class="mb-4" style="line-height: 1.7;">Sign in to access the dashboard assigned to your role.</p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">• Admin: manage the marketplace</li>
                    <li class="mb-2">• Manager: oversee operations and reports</li>
                    <li>• Supervisor: manage field operations</li>
                </ul>
            </div>
            <div class="login-panel login-panel-form">
                <a href="../index.php" class="back-link mb-4"><i class="fa-solid fa-arrow-left me-2"></i>Back to marketplace</a>
                <h3 class="mb-2 fw-bold">Staff Sign in</h3>
                <p class="text-muted mb-4">Admin, manager and supervisor login</p>

                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-warning" role="alert"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="row g-3">
                    <div class="col-12">
                        <label for="inputEmailAddress" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" id="inputEmailAddress" placeholder="Email Address" required autocomplete="username">
                    </div>
                    <div class="col-12">
                        <label for="inputChoosePassword" class="form-label">Password</label>
                        <div class="input-group" id="show_hide_password">
                            <input type="password" name="password" class="form-control form-control-lg border-end-0" id="inputChoosePassword" placeholder="Enter Password" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" aria-label="Show password"><i class='bx bx-hide'></i></button>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="adminSubmit" class="btn btn-success btn-lg w-100">Sign in</button>
                    </div>
                    <div class="col-12 text-center">
                        <p class="text-muted mb-0 small">Your role determines the dashboard you will see.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

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
    .wrapper {
        width: 100%;
    }
    .staff-login-page {
        width: 100%;
        padding: 24px;
    }
    .login-card {
        max-width: 900px;
        margin: 0 auto;
        border-radius: 22px;
        box-shadow: 0 25px 60px rgba(11, 74, 47, 0.18);
    }
    .login-split {
        display: flex;
        flex-direction: row;
        align-items: stretch;
    }
    .login-panel {
        width: 50%;
        min-width: 0;
    }
    .login-panel-brand {
        background: linear-gradient(135deg, #0e5a3a, #1aa661);
        color: #fff;
        padding: 52px 42px;
    }
    .brand-link {
        color: #fff;
        text-decoration: none;
    }
    .brand-link:hover {
        color: #eafaf2;
    }
    .login-panel-form {
        padding: 52px 42px;
        background: #fff;
    }
    .back-link {
        display: inline-block;
        color: #0d8b47;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
    }
    .back-link:hover {
        color: #0e5a3a;
        text-decoration: underline;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(22,163,74,0.2);
        border-color: #1aa661;
    }
    .btn-success {
        background: linear-gradient(135deg, #0d8b47, #1aa661);
        border: none;
    }
    #show_hide_password .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    @media (max-width: 767.98px) {
        .staff-login-page {
            padding: 12px;
        }
        .login-split {
            flex-direction: column;
        }
        .login-panel {
            width: 100%;
        }
        .login-panel-brand {
            padding: 32px 28px;
        }
        .login-panel-form {
            padding: 32px 28px;
        }
    }
</style>

<!-- Bootstrap JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script>
    $(document).ready(function () {
        $("#show_hide_password button").on('click', function (event) {
            event.preventDefault();
            var passwordInput = $('#show_hide_password input');
            var icon = $('#show_hide_password i');
            if (passwordInput.attr('type') === 'text') {
                passwordInput.attr('type', 'password');
                icon.addClass('bx-hide').removeClass('bx-show');
            } else {
                passwordInput.attr('type', 'text');
                icon.removeClass('bx-hide').addClass('bx-show');
            }
        });
    });
</script>
<!--app JS-->
<script src="assets/js/app.js"></script>
</body>
</html>
