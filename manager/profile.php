<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
ob_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['user_email']) || !in_array((int) ($_SESSION['role'] ?? 0), [4, 5], true)) {
    header('Location: ../admin/index.php');
    exit;
}
require_once __DIR__ . '/../admin/inc/db.php';

$notice = '';
$error = '';
$staffId = (int) $_SESSION['user_id'];

if (isset($_GET['updated'])) {
    $notice = 'Profile updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['user_email'] ?? '');
    $phone = trim($_POST['user_phone'] ?? '');
    $address = trim($_POST['user_address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid name and email address.';
    } elseif ($newPassword !== '' && (strlen($newPassword) < 5 || $newPassword !== $confirmPassword)) {
        $error = 'Passwords must match and contain at least 5 characters.';
    } else {
        $duplicateStatement = mysqli_prepare($db, 'SELECT user_id FROM users WHERE user_email = ? AND user_id != ? LIMIT 1');
        $duplicate = false;
        if ($duplicateStatement) {
            mysqli_stmt_bind_param($duplicateStatement, 'si', $email, $staffId);
            mysqli_stmt_execute($duplicateStatement);
            $duplicateResult = mysqli_stmt_get_result($duplicateStatement);
            $duplicate = $duplicateResult && mysqli_num_rows($duplicateResult) > 0;
            mysqli_stmt_close($duplicateStatement);
        }
        if ($duplicate) {
            $error = 'That email address is already in use.';
        } else {
            if ($newPassword !== '') {
                $updateStatement = mysqli_prepare($db, 'UPDATE users SET user_name = ?, user_email = ?, user_phone = ?, user_address = ?, user_password = ? WHERE user_id = ? AND role IN (4, 5) LIMIT 1');
                $passwordHash = sha1($newPassword);
                if ($updateStatement) {
                    mysqli_stmt_bind_param($updateStatement, 'sssssi', $name, $email, $phone, $address, $passwordHash, $staffId);
                }
            } else {
                $updateStatement = mysqli_prepare($db, 'UPDATE users SET user_name = ?, user_email = ?, user_phone = ?, user_address = ? WHERE user_id = ? AND role IN (4, 5) LIMIT 1');
                if ($updateStatement) {
                    mysqli_stmt_bind_param($updateStatement, 'ssssi', $name, $email, $phone, $address, $staffId);
                }
            }
            $update = $updateStatement && mysqli_stmt_execute($updateStatement);
            $updateError = $updateStatement ? mysqli_stmt_error($updateStatement) : mysqli_error($db);
            if ($updateStatement) {
                mysqli_stmt_close($updateStatement);
            }
            if ($update) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['email'] = $email;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['phone'] = $phone;
                header('Location: profile.php?updated=1');
                exit;
            } else {
                $error = 'Unable to update your profile: ' . $updateError;
            }
        }
    }
}

include __DIR__ . '/inc/header.php';

$profileQuery = mysqli_query($db, "SELECT user_name, user_email, user_phone, user_address FROM users WHERE user_id='$staffId' AND role IN (4, 5) LIMIT 1");
$profile = $profileQuery ? mysqli_fetch_assoc($profileQuery) : null;
if (!$profileQuery) {
    $error = 'Your profile could not be loaded: ' . mysqli_error($db);
}
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Account</div>
    <h2 class="mb-0 mt-2">My profile</h2>
</div>
<?php if ($notice): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<div class="card p-4">
    <h5 class="mb-1"><i class="fa-solid fa-user-pen text-success me-2"></i>Edit my details</h5>
    <p class="text-muted mb-4">Update only your own manager or supervisor account.</p>
    <?php if ($profile): ?>
        <form method="post" action="profile.php">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="userName">Full name</label><input class="form-control" id="userName" name="user_name" value="<?php echo htmlspecialchars($profile['user_name']); ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="userEmail">Email</label><input class="form-control" id="userEmail" name="user_email" type="email" value="<?php echo htmlspecialchars($profile['user_email']); ?>" required></div>
                <div class="col-md-6"><label class="form-label" for="userPhone">Phone</label><input class="form-control" id="userPhone" name="user_phone" value="<?php echo htmlspecialchars($profile['user_phone'] ?? ''); ?>"></div>
                <div class="col-md-6"><label class="form-label" for="userAddress">Address</label><input class="form-control" id="userAddress" name="user_address" value="<?php echo htmlspecialchars($profile['user_address'] ?? ''); ?>"></div>
                <div class="col-md-6"><label class="form-label" for="newPassword">New password</label><input class="form-control" id="newPassword" name="new_password" type="password" minlength="5" placeholder="Leave blank to keep current password"></div>
                <div class="col-md-6"><label class="form-label" for="confirmPassword">Confirm password</label><input class="form-control" id="confirmPassword" name="confirm_password" type="password" minlength="5"></div>
                <div class="col-12"><button type="submit" name="update_profile" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-2"></i>Save Profile</button></div>
            </div>
        </form>
    <?php else: ?><p class="text-muted mb-0">Your profile could not be loaded.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
