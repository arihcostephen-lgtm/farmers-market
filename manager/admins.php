<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$action = $_GET['action'] ?? '';
$notice = '';

if ($action === 'add-user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = mysqli_real_escape_string($db, trim($_POST['full_name'] ?? ''));
    $email = mysqli_real_escape_string($db, strtolower(trim($_POST['email'] ?? '')));
    $phone = mysqli_real_escape_string($db, trim($_POST['phone'] ?? ''));
    $address = mysqli_real_escape_string($db, trim($_POST['address'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = (int) ($_POST['role'] ?? 5);
    $status = 1;

    if ($fullName !== '' && $email !== '' && $password !== '' && in_array($role, [1, 4, 5], true)) {
        $exists = mysqli_query($db, "SELECT user_id FROM users WHERE user_email = '$email' LIMIT 1");
        if (mysqli_num_rows($exists) === 0) {
            $hash = sha1($password);
            $insert = mysqli_query($db, "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status, join_date) VALUES ('$fullName', '$email', '$hash', '$phone', '$address', '$role', '$status', NOW())");
            $notice = $insert ? 'Staff account created successfully.' : 'Failed to create staff account.';
        } else {
            $notice = 'An account with this email already exists.';
        }
    } else {
        $notice = 'Please complete all required fields.';
    }
}

if (isset($_GET['toggle_status']) && !empty($_GET['id'])) {
    $targetId = (int) $_GET['id'];
    $current = mysqli_fetch_assoc(mysqli_query($db, "SELECT status FROM users WHERE user_id = $targetId AND role IN (1,4,5) LIMIT 1"));
    if ($current) {
        $newStatus = (int) $current['status'] === 1 ? 0 : 1;
        mysqli_query($db, "UPDATE users SET status = $newStatus WHERE user_id = $targetId");
        header('Location: admins.php');
        exit;
    }
}

$staff = mysqli_query($db, "SELECT user_id, user_name, user_email, user_phone, user_address, role, status FROM users WHERE role IN (1,4,5) ORDER BY user_name ASC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Administration</div>
    <h2 class="mb-0 mt-2">Add admin and supervisor roles</h2>
</div>

<?php if ($notice !== ''): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($notice); ?></div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-3">Create staff account</h5>
    <form method="post" action="admins.php?action=add-user">
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="full_name" placeholder="Full name" required></div>
            <div class="col-md-4"><input class="form-control" name="email" type="email" placeholder="Email address" required></div>
            <div class="col-md-4"><input class="form-control" name="phone" placeholder="Phone number"></div>
            <div class="col-md-4"><input class="form-control" name="password" type="password" placeholder="Password" required></div>
            <div class="col-md-4">
                <select class="form-select" name="role" required>
                    <option value="1">Admin</option>
                    <option value="4" selected>Manager</option>
                    <option value="5">Supervisor</option>
                </select>
            </div>
            <div class="col-md-4"><input class="form-control" name="address" placeholder="Address"></div>
            <div class="col-12"><button class="btn btn-success" type="submit">Create account</button></div>
        </div>
    </form>
</div>

<div class="card p-4">
    <h5 class="mb-3">Staff directory</h5>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($member = mysqli_fetch_assoc($staff)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($member['user_phone'] ?: '—'); ?></td>
                        <td><?php echo [1 => 'Admin', 4 => 'Manager', 5 => 'Supervisor'][$member['role']] ?? 'Staff'; ?></td>
                        <td>
                            <span class="badge <?php echo (int) $member['status'] === 1 ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo (int) $member['status'] === 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="admins.php?toggle_status=1&id=<?php echo (int) $member['user_id']; ?>" class="btn btn-sm btn-outline-<?php echo (int) $member['status'] === 1 ? 'warning' : 'success'; ?>"><?php echo (int) $member['status'] === 1 ? 'Disable' : 'Enable'; ?></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
