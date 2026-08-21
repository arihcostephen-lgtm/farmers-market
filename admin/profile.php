<?php
include "inc/header.php";

$adminId = (int) $_SESSION['user_id'];
$notice = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($db, trim($_POST['user_name'] ?? ''));
    $email = trim($_POST['user_email'] ?? '');
    $phone = mysqli_real_escape_string($db, trim($_POST['user_phone'] ?? ''));
    $address = mysqli_real_escape_string($db, trim($_POST['user_address'] ?? ''));
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($newPassword !== '' && (strlen($newPassword) < 5 || $newPassword !== $confirmPassword)) {
        $error = 'Passwords must match and contain at least 5 characters.';
    } else {
        $emailEscaped = mysqli_real_escape_string($db, $email);
        $duplicate = mysqli_query($db, "SELECT user_id FROM users WHERE user_email='$emailEscaped' AND user_id != '$adminId' LIMIT 1");
        if ($duplicate && mysqli_num_rows($duplicate) > 0) {
            $error = 'That email address is already in use.';
        } else {
            $setPassword = $newPassword !== '' ? ", user_password='" . sha1($newPassword) . "'" : '';
            $imageSql = '';
            if (!empty($_FILES['user_image']['name']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $error = 'Please upload a JPG, PNG, GIF, or WEBP image.';
                } else {
                    $imageName = time() . '_' . basename($_FILES['user_image']['name']);
                    $uploadPath = __DIR__ . '/assets/images/users/' . $imageName;
                    if (move_uploaded_file($_FILES['user_image']['tmp_name'], $uploadPath)) {
                        $imageSql = ", user_image='" . mysqli_real_escape_string($db, $imageName) . "'";
                    }
                }
            }
            if ($error === '') {
                $update = mysqli_query($db, "UPDATE users SET user_name='$name', user_email='$emailEscaped', user_phone='$phone', user_address='$address'$setPassword$imageSql WHERE user_id='$adminId' AND role=1");
                if ($update) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $notice = 'Profile updated successfully.';
                } else {
                    $error = 'Unable to update the profile.';
                }
            }
        }
    }
}
$adminQuery = mysqli_query($db, "SELECT * FROM users WHERE user_id='$adminId' AND role=1 LIMIT 1");
$admin = $adminQuery ? mysqli_fetch_assoc($adminQuery) : null;
?>
<div class="page-wrapper"><div class="page-content"><div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3"><div class="breadcrumb-title pe-3">My Profile</div><div class="ps-3"><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item active">Edit Profile</li></ol></nav></div></div><div class="card"><div class="card-body"><h5 class="mb-3">Administrator Profile</h5><?php if ($notice) { ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php } ?><?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?><?php if ($admin) { ?><form method="post" enctype="multipart/form-data"><div class="row g-3"><div class="col-md-4 text-center"><img src="assets/images/users/<?php echo htmlspecialchars($admin['user_image'] ?: 'default.png'); ?>" class="img-thumbnail mb-3" style="max-width:180px;height:180px;object-fit:cover"><label class="form-label d-block">Profile image</label><input type="file" name="user_image" class="form-control" accept="image/*"></div><div class="col-md-8"><div class="mb-3"><label class="form-label">Name</label><input type="text" name="user_name" class="form-control" value="<?php echo htmlspecialchars($admin['user_name']); ?>" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" name="user_email" class="form-control" value="<?php echo htmlspecialchars($admin['user_email']); ?>" required></div><div class="mb-3"><label class="form-label">Phone</label><input type="text" name="user_phone" class="form-control" value="<?php echo htmlspecialchars($admin['user_phone'] ?? ''); ?>"></div><div class="mb-3"><label class="form-label">Address</label><textarea name="user_address" class="form-control" rows="2"><?php echo htmlspecialchars($admin['user_address'] ?? ''); ?></textarea></div><hr><h6>Change Password</h6><div class="row"><div class="col-md-6"><input type="password" name="new_password" class="form-control" placeholder="New password" minlength="5"></div><div class="col-md-6"><input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" minlength="5"></div></div><button type="submit" name="update_profile" class="btn btn-success mt-3">Save Profile</button></div></div></form><?php } ?></div></div></div></div>
<?php include "inc/footer.php"; ?>
