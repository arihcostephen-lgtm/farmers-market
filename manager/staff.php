<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if ((int) ($_SESSION['role'] ?? 0) !== 4) {
    header('Location: dashboard.php');
    exit;
}
?>
<?php include __DIR__ . '/inc/header.php'; ?>
<?php

$notice = '';
$currentManagerId = (int) ($_SESSION['user_id'] ?? 0);
$addAllowed = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_staff']) || isset($_POST['update_staff']))) {
    $salary = max(0, (float) ($_POST['salary'] ?? 0));
    $status = isset($_POST['status']) && (int) $_POST['status'] === 1 ? 1 : 0;
    $staffId = (int) ($_POST['staff_id'] ?? 0);

    if (isset($_POST['add_staff'])) {
        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId === $currentManagerId) {
            $notice = 'You cannot add yourself to the payroll list.';
            $addAllowed = false;
        } else {
            $existingQuery = mysqli_query($db, "SELECT staff_id FROM staff_payroll WHERE user_id='$userId' LIMIT 1");
            $existingStaff = $existingQuery ? mysqli_fetch_assoc($existingQuery) : null;
            if ($existingStaff) {
                $notice = 'That account is already on the payroll list.';
                $addAllowed = false;
            }
            $userQuery = mysqli_query($db, "SELECT user_name, user_email, user_phone, role FROM users WHERE user_id='$userId' AND role IN (1, 4, 5) AND status=1 LIMIT 1");
            $selectedUser = $userQuery ? mysqli_fetch_assoc($userQuery) : null;
            if (!$selectedUser) {
                $notice = 'Select an active Admin or Supervisor account.';
                $addAllowed = false;
            }
            $roleLabels = [1 => 'Admin', 4 => 'Manager', 5 => 'Supervisor'];
            $name = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_name']) : '';
            $role = $selectedUser ? mysqli_real_escape_string($db, $roleLabels[(int) $selectedUser['role']] ?? 'Staff') : '';
            $email = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_email']) : '';
            $phone = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_phone'] ?? '') : '';
        }
    } else {
        $name = mysqli_real_escape_string($db, trim($_POST['staff_name'] ?? ''));
        $role = mysqli_real_escape_string($db, trim($_POST['staff_role'] ?? ''));
        $email = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
        $phone = mysqli_real_escape_string($db, trim($_POST['phone'] ?? ''));
    }

    if ($name !== '' && $role !== '' && $addAllowed) {
        if (isset($_POST['update_staff']) && $staffId > 0) {
            $staffRecord = mysqli_fetch_assoc(mysqli_query($db, "SELECT user_id FROM staff_payroll WHERE staff_id='$staffId' LIMIT 1"));
            $roleIds = ['Admin' => 1, 'Manager' => 4, 'Supervisor' => 5];
            $updatedRoleId = $roleIds[$role] ?? 0;
            $userUpdated = true;
            mysqli_begin_transaction($db);
            if ($staffRecord && (int) $staffRecord['user_id'] > 0 && $updatedRoleId > 0) {
                $linkedUserId = (int) $staffRecord['user_id'];
                $duplicateEmail = mysqli_query($db, "SELECT user_id FROM users WHERE user_email='$email' AND user_id != '$linkedUserId' LIMIT 1");
                if ($duplicateEmail && mysqli_num_rows($duplicateEmail) > 0) {
                    $userUpdated = false;
                    $notice = 'That email address is already in use.';
                } else {
                    $userUpdated = mysqli_query($db, "UPDATE users SET user_name='$name', user_email='$email', user_phone='$phone', role='$updatedRoleId' WHERE user_id='$linkedUserId' AND user_id != '$currentManagerId'");
                }
            } elseif ($staffRecord && (int) $staffRecord['user_id'] > 0) {
                $userUpdated = false;
                $notice = 'Select a valid staff role.';
            }
            $payrollUpdated = $userUpdated && mysqli_query($db, "UPDATE staff_payroll SET staff_name='$name', staff_role='$role', email='$email', phone='$phone', salary='$salary', status='$status', paid_at=" . ($status === 1 ? 'NOW()' : 'NULL') . " WHERE staff_id='$staffId'");
            if ($userUpdated && $payrollUpdated) {
                mysqli_commit($db);
                $notice = 'Staff record updated successfully.';
            } else {
                mysqli_rollback($db);
                if ($notice === '') {
                    $notice = 'Unable to update staff record: ' . mysqli_error($db);
                }
            }
        } else {
        $insert = mysqli_query($db, "INSERT INTO staff_payroll (user_id, staff_name, staff_role, email, phone, salary, status, created_at) VALUES ('$userId', '$name', '$role', '$email', '$phone', '$salary', 0, NOW())");
        $notice = $insert ? 'Staff record created successfully.' : 'Unable to create staff record: ' . mysqli_error($db);
        }
    } elseif (!$addAllowed) {
        $notice = $notice !== '' ? $notice : 'Complete the staff account selection before saving.';
    } elseif (isset($_POST['update_staff'])) {
        $notice = 'Staff name and role are required.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_staff'])) {
    $staffId = (int) ($_POST['staff_id'] ?? 0);
    if ($staffId > 0) {
        $delete = mysqli_query($db, "DELETE FROM staff_payroll WHERE staff_id='$staffId'");
        $notice = $delete ? 'Staff record deleted successfully.' : 'Unable to delete staff record.';
    }
}

$staffList = mysqli_query($db, "SELECT sp.staff_id, sp.user_id, sp.salary, sp.status AS payroll_status, sp.created_at, sp.staff_name, sp.staff_role, sp.email, sp.phone, COALESCE(sp.staff_name, u.user_name) AS account_name, COALESCE(sp.email, u.user_email) AS account_email, COALESCE(sp.phone, u.user_phone) AS account_phone, COALESCE(sp.staff_role, CASE u.role WHEN 1 THEN 'Admin' WHEN 4 THEN 'Manager' WHEN 5 THEN 'Supervisor' END) AS account_role, u.status AS account_status FROM staff_payroll sp LEFT JOIN users u ON u.user_id = sp.user_id ORDER BY sp.created_at DESC, sp.staff_id DESC");
$staffMembers = [];
if (!$staffList) {
    $notice = 'Staff records could not be loaded: ' . mysqli_error($db);
} else {
    while ($member = mysqli_fetch_assoc($staffList)) {
        $staffMembers[] = $member;
    }
}
$staffCount = count($staffMembers);
$pendingStaffCount = count(array_filter($staffMembers, static function ($member) {
    return (int) ($member['payroll_status'] ?? 0) !== 1;
}));
$payrollTotal = array_sum(array_map(static function ($member) {
    return (float) ($member['salary'] ?? 0);
}, $staffMembers));
$availableStaff = mysqli_query($db, "SELECT u.user_id, u.user_name, u.user_email, u.user_phone, u.role, sp.staff_id AS payroll_id FROM users u LEFT JOIN staff_payroll sp ON sp.user_id = u.user_id WHERE u.role IN (1, 4, 5) AND u.status=1 AND u.user_id != '$currentManagerId' ORDER BY u.user_name ASC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Payroll</div>
    <h2 class="mb-0 mt-2">Manage staff and pay salaries</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-1">Add staff to payroll</h5>
    <p class="text-muted mb-3">Choose an active staff account. Account details fill automatically.</p>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="staffUser">Staff account</label>
                <select class="form-select" name="user_id" id="staffUser" required>
                    <option value="">Select staff account</option>
                    <?php if ($availableStaff): while ($user = mysqli_fetch_assoc($availableStaff)): ?>
                        <option value="<?php echo (int) $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES); ?>" data-role="<?php echo htmlspecialchars([1 => 'Admin', 4 => 'Manager', 5 => 'Supervisor'][$user['role']] ?? 'Staff', ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($user['user_email'], ENT_QUOTES); ?>" data-phone="<?php echo htmlspecialchars($user['user_phone'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($user['user_name']); ?><?php echo $user['payroll_id'] ? ' (Already on payroll)' : ''; ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label" for="staffName">Name</label><input class="form-control" name="staff_name" id="staffName" readonly placeholder="Auto-filled"></div>
            <div class="col-md-2"><label class="form-label" for="staffRole">Role</label><input class="form-control" name="staff_role" id="staffRole" readonly placeholder="Auto-filled"></div>
            <div class="col-md-2"><label class="form-label" for="staffEmail">Email</label><input class="form-control" name="email" id="staffEmail" type="email" readonly placeholder="Auto-filled"></div>
            <div class="col-md-1"><label class="form-label" for="staffPhone">Phone</label><input class="form-control" name="phone" id="staffPhone" readonly placeholder="Auto-filled"></div>
            <div class="col-md-1"><label class="form-label" for="staffSalary">Salary</label><input class="form-control" name="salary" id="staffSalary" type="number" step="0.01" min="0" placeholder="0"></div>
            <div class="col-md-1 d-flex align-items-end"><button type="submit" name="add_staff" class="btn btn-success w-100">Add</button></div>
        </div>
    </form>
</div>

<div class="card p-4">
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="border rounded p-3 h-100"><div class="small text-muted text-uppercase">Saved staff</div><strong class="fs-4"><?php echo number_format($staffCount); ?></strong></div></div>
        <div class="col-md-4"><div class="border rounded p-3 h-100"><div class="small text-muted text-uppercase">Pending payments</div><strong class="fs-4 text-warning"><?php echo number_format($pendingStaffCount); ?></strong></div></div>
        <div class="col-md-4"><div class="border rounded p-3 h-100"><div class="small text-muted text-uppercase">Payroll total</div><strong class="fs-4 text-success">UGX <?php echo number_format($payrollTotal, 2); ?></strong></div></div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Saved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($staffCount > 0): foreach ($staffMembers as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['account_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['account_role']); ?></td>
                        <td><?php echo htmlspecialchars($member['account_email'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($member['account_phone'] ?: '—'); ?></td>
                        <td>UGX <?php echo number_format((float) $member['salary'], 2); ?></td>
                        <td><?php echo (int) $member['payroll_status'] === 1 ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning text-dark">Pending</span>'; ?></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($member['created_at']))); ?></small></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-staff-button" data-edit-target="editStaff<?php echo (int) $member['staff_id']; ?>" title="Edit staff"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="post" onsubmit="return confirm('Delete this staff record?');">
                                    <input type="hidden" name="staff_id" value="<?php echo (int) $member['staff_id']; ?>">
                                    <button type="submit" name="delete_staff" class="btn btn-sm btn-outline-danger" title="Delete staff"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                            <div class="edit-staff-panel border rounded p-3 mt-3 d-none" id="editStaff<?php echo (int) $member['staff_id']; ?>">
                                <h6 class="mb-3">Edit Staff Details</h6>
                                <form method="post">
                                    <input type="hidden" name="staff_id" value="<?php echo (int) $member['staff_id']; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="staff_name" value="<?php echo htmlspecialchars($member['account_name']); ?>" required></div>
                                        <div class="col-md-6"><label class="form-label">Role</label><select class="form-select" name="staff_role" required><option value="Admin" <?php echo $member['account_role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option><option value="Manager" <?php echo $member['account_role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option><option value="Supervisor" <?php echo $member['account_role'] === 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option></select></div>
                                        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="<?php echo htmlspecialchars($member['account_email']); ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo htmlspecialchars($member['account_phone']); ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Salary</label><input class="form-control" name="salary" type="number" step="0.01" min="0" value="<?php echo htmlspecialchars($member['salary']); ?>" required></div>
                                        <div class="col-md-6"><label class="form-label">Payment status</label><select class="form-select" name="status"><option value="0" <?php echo (int) $member['payroll_status'] === 0 ? 'selected' : ''; ?>>Pending</option><option value="1" <?php echo (int) $member['payroll_status'] === 1 ? 'selected' : ''; ?>>Paid</option></select></div>
                                        <div class="col-12"><button type="submit" name="update_staff" class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>Save Changes</button> <button type="button" class="btn btn-secondary btn-sm cancel-edit">Cancel</button></div>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No saved staff records are available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    const staffUserSelect = document.getElementById('staffUser');
    if (staffUserSelect) {
        staffUserSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            document.getElementById('staffName').value = selected.dataset.name || '';
            document.getElementById('staffRole').value = selected.dataset.role || '';
            document.getElementById('staffEmail').value = selected.dataset.email || '';
            document.getElementById('staffPhone').value = selected.dataset.phone || '';
        });
    }
    document.querySelectorAll('.edit-staff-button').forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = document.getElementById(button.dataset.editTarget);
            panel.classList.toggle('d-none');
        });
    });
    document.querySelectorAll('.cancel-edit').forEach(function (button) {
        button.addEventListener('click', function () {
            button.closest('.edit-staff-panel').classList.add('d-none');
        });
    });
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
