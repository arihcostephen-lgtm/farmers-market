<?php
session_start();
ob_start();
include "inc/db.php";

if (!empty($_SESSION['user_id']) && !empty($_SESSION['user_email']) && !empty($_SESSION['role']) && (int) $_SESSION['role'] === 1) {
    header("Location: dashboard.php");
    exit;
}

$login_error = "";
if (isset($_POST['adminSubmit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = mysqli_real_escape_string($db, $email);
    $shaPass = sha1($password);
    $md5Pass = md5($password);

    $readSql = "SELECT * FROM users WHERE user_email = '$email' AND role = 1 AND status = 1 LIMIT 1";
    $readQuery = mysqli_query($db, $readSql);

    if (!$readQuery || mysqli_num_rows($readQuery) === 0) {
        $login_error = 'Sorry! No active admin account was found for that email.';
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
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = 'Invalid admin credentials.';
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
}
?>
<?php include 'inc/login_header.php'; ?>

<div class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden w-100" style="max-width: 940px;">
        <div class="row g-0">
            <div class="col-lg-6 d-none d-lg-flex bg-auth-left flex-column justify-content-center p-5 text-white">
                <div class="px-4">
                    <h1 class="display-5 fw-bold mb-4">Farmers Market</h1>
                    <p class="lead mb-4">Welcome back, admin. Manage sellers, products, orders and dashboard insights from one secure portal.</p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3"><i class="bx bx-check-double align-middle me-2"></i>Green branded admin access</li>
                        <li class="mb-3"><i class="bx bx-check-double align-middle me-2"></i>Easy product & order management</li>
                        <li class="mb-3"><i class="bx bx-check-double align-middle me-2"></i>Secure role-based dashboard</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 bg-white p-5">
                <div class="text-center mb-4">
                    <h3 class="mb-2">Admin Sign in</h3>
                    <p class="text-muted mb-0">Login to your Farmers Market dashboard</p>
                </div>

                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-warning text-center" role="alert"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="row g-3">
                    <div class="col-12">
                        <label for="inputEmailAddress" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" id="inputEmailAddress" placeholder="Email Address" required autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label for="inputChoosePassword" class="form-label">Password</label>
                        <div class="input-group" id="show_hide_password">
                            <input type="password" name="password" class="form-control form-control-lg border-end-0" id="inputChoosePassword" placeholder="Enter Password" required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button"><i class='bx bx-hide'></i></button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-grid">
                            <button type="submit" name="adminSubmit" class="btn btn-success btn-lg">Sign in</button>
                        </div>
                    </div>
                    <div class="col-12 text-center">
                        <p class="text-muted mb-0">Use your administrator email and password to continue.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #eefaf1 0%, #c9f0d5 100%);
    }
    .bg-auth-left {
        background: linear-gradient(180deg, #0a7021 0%, #22b544 100%);
        min-height: 100%;
    }
    .bg-auth-left h1,
    .bg-auth-left p,
    .bg-auth-left li {
        color: #fff;
    }
    .bg-auth-left .bx {
        font-size: 1.1rem;
    }
    .auth-card {
        border-radius: 1.5rem;
    }
    #show_hide_password .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
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
