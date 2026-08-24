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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_staff']) || isset($_POST['update_staff']))) {
    $salary = max(0, (float) ($_POST['salary'] ?? 0));
    $status = isset($_POST['status']) && (int) $_POST['status'] === 1 ? 1 : 0;
    $staffId = (int) ($_POST['staff_id'] ?? 0);

    if (isset($_POST['add_staff'])) {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $userQuery = mysqli_query($db, "SELECT user_name, user_email, user_phone, role FROM users WHERE user_id='$userId' AND role IN (1, 5) AND status=1 LIMIT 1");
        $selectedUser = $userQuery ? mysqli_fetch_assoc($userQuery) : null;
        $roleLabels = [1 => 'Admin', 5 => 'Supervisor'];
        $name = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_name']) : '';
        $role = $selectedUser ? mysqli_real_escape_string($db, $roleLabels[(int) $selectedUser['role']] ?? 'Staff') : '';
        $email = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_email']) : '';
        $phone = $selectedUser ? mysqli_real_escape_string($db, $selectedUser['user_phone'] ?? '') : '';
    } else {
        $name = mysqli_real_escape_string($db, trim($_POST['staff_name'] ?? ''));
        $role = mysqli_real_escape_string($db, trim($_POST['staff_role'] ?? ''));
        $email = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
        $phone = mysqli_real_escape_string($db, trim($_POST['phone'] ?? ''));
    }

    if ($name !== '' && $role !== '') {
        if (isset($_POST['update_staff']) && $staffId > 0) {
            $update = mysqli_query($db, "UPDATE staff_payroll SET staff_name='$name', staff_role='$role', email='$email', phone='$phone', salary='$salary', status='$status', paid_at=" . ($status === 1 ? 'NOW()' : 'NULL') . " WHERE staff_id='$staffId'");
            $notice = $update ? 'Staff record updated successfully.' : 'Unable to update staff record.';
        } else {
        $insert = mysqli_query($db, "INSERT INTO staff_payroll (staff_name, staff_role, email, phone, salary, status, created_at) VALUES ('$name', '$role', '$email', '$phone', '$salary', 0, NOW())");
        $notice = $insert ? 'Staff record created successfully.' : 'Unable to create staff record.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_staff'])) {
    $staffId = (int) ($_POST['staff_id'] ?? 0);
    if ($staffId > 0) {
        $delete = mysqli_query($db, "DELETE FROM staff_payroll WHERE staff_id='$staffId'");
        $notice = $delete ? 'Staff record deleted successfully.' : 'Unable to delete staff record.';
    }
}

$staffList = mysqli_query($db, "SELECT * FROM staff_payroll ORDER BY created_at DESC");
$availableStaff = mysqli_query($db, "SELECT user_id, user_name, user_email, user_phone, role FROM users WHERE role IN (1, 5) AND status=1 ORDER BY user_name ASC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Payroll</div>
    <h2 class="mb-0 mt-2">Manage staff and pay salaries</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-1">Add staff to payroll</h5>
    <p class="text-muted mb-3">Choose an active Admin or Supervisor account. Account details fill automatically.</p>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="staffUser">Staff account</label>
                <select class="form-select" name="user_id" id="staffUser" required>
                    <option value="">Select Admin or Supervisor</option>
                    <?php if ($availableStaff): while ($user = mysqli_fetch_assoc($availableStaff)): ?>
                        <option value="<?php echo (int) $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES); ?>" data-role="<?php echo (int) $user['role'] === 1 ? 'Admin' : 'Supervisor'; ?>" data-email="<?php echo htmlspecialchars($user['user_email'], ENT_QUOTES); ?>" data-phone="<?php echo htmlspecialchars($user['user_phone'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($user['user_name']); ?></option>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($member = mysqli_fetch_assoc($staffList)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['staff_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['staff_role']); ?></td>
                        <td><?php echo htmlspecialchars($member['email'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($member['phone'] ?: '—'); ?></td>
                        <td>UGX <?php echo number_format((float) $member['salary'], 2); ?></td>
                        <td><?php echo (int) $member['status'] === 1 ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning text-dark">Pending</span>'; ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStaff<?php echo (int) $member['staff_id']; ?>" title="Edit staff"><i class="fa-solid fa-pen-to-square"></i></button>
                                <form method="post" onsubmit="return confirm('Delete this staff record?');">
                                    <input type="hidden" name="staff_id" value="<?php echo (int) $member['staff_id']; ?>">
                                    <button type="submit" name="delete_staff" class="btn btn-sm btn-outline-danger" title="Delete staff"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                            <div class="modal fade" id="editStaff<?php echo (int) $member['staff_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Edit Staff Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="staff_id" value="<?php echo (int) $member['staff_id']; ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="staff_name" value="<?php echo htmlspecialchars($member['staff_name']); ?>" required></div>
                                                    <div class="col-md-6"><label class="form-label">Role</label><input class="form-control" name="staff_role" value="<?php echo htmlspecialchars($member['staff_role']); ?>" required></div>
                                                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="<?php echo htmlspecialchars($member['email']); ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Salary</label><input class="form-control" name="salary" type="number" step="0.01" min="0" value="<?php echo htmlspecialchars($member['salary']); ?>"></div>
                                                    <div class="col-md-6"><label class="form-label">Payment status</label><select class="form-select" name="status"><option value="0" <?php echo (int) $member['status'] === 0 ? 'selected' : ''; ?>>Pending</option><option value="1" <?php echo (int) $member['status'] === 1 ? 'selected' : ''; ?>>Paid</option></select></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" name="update_staff" class="btn btn-success btn-sm"><i class="fa-solid fa-check me-1"></i>Save Changes</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
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
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
