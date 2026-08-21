<?php include __DIR__ . '/inc/header.php'; ?>
<?php
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $name = mysqli_real_escape_string($db, trim($_POST['staff_name']));
    $role = mysqli_real_escape_string($db, trim($_POST['staff_role']));
    $email = mysqli_real_escape_string($db, trim($_POST['email']));
    $phone = mysqli_real_escape_string($db, trim($_POST['phone']));
    $salary = (float) $_POST['salary'];
    if ($name !== '') {
        $insert = mysqli_query($db, "INSERT INTO staff_payroll (staff_name, staff_role, email, phone, salary, status, created_at) VALUES ('$name', '$role', '$email', '$phone', '$salary', 0, NOW())");
        $notice = $insert ? 'Staff record created successfully.' : 'Unable to create staff record.';
    }
}

$staffList = mysqli_query($db, "SELECT * FROM staff_payroll ORDER BY created_at DESC");
?>
<div class="page-header">
    <div class="text-uppercase small fw-semibold opacity-75">Payroll</div>
    <h2 class="mb-0 mt-2">Manage staff and pay salaries</h2>
</div>

<?php if ($notice !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="card p-4 mb-4">
    <h5 class="mb-3">Add staff</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3"><input class="form-control" name="staff_name" placeholder="Staff name" required></div>
            <div class="col-md-3"><input class="form-control" name="staff_role" placeholder="Role" required></div>
            <div class="col-md-2"><input class="form-control" name="email" type="email" placeholder="Email"></div>
            <div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone"></div>
            <div class="col-md-1"><input class="form-control" name="salary" type="number" step="0.01" min="0" placeholder="Salary"></div>
            <div class="col-md-1"><button type="submit" name="add_staff" class="btn btn-success w-100">Add</button></div>
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
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
