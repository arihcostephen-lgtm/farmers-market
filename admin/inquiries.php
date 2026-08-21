<?php include "inc/header.php"; ?>
<div class="page-wrapper">
  <div class="page-content">
    <?php
    if (isset($_POST['updateInquiry'])) {
        $inquiryId = (int) $_POST['inquiry_id'];
        $status = max(0, min(2, (int) $_POST['status']));
        $response = mysqli_real_escape_string($db, trim($_POST['response'] ?? ''));
        mysqli_query($db, "UPDATE product_inquiries SET status='$status', response='$response', updated_at=NOW() WHERE inquiry_id='$inquiryId'");
    }
    $inquiries = mysqli_query($db, "SELECT i.*, p.product_name, u.user_name, u.user_email FROM product_inquiries i LEFT JOIN products p ON p.product_id=i.product_id LEFT JOIN users u ON u.user_id=i.buyer_id ORDER BY i.created_at DESC");
    ?>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Product Inquiries</div>
      <div class="ps-3"><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item active">Manage Inquiries</li></ol></nav></div>
    </div>
    <div class="card"><div class="card-body">
      <h6 class="mb-3 text-uppercase">Buyer Inquiries</h6>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark"><tr><th>Product</th><th>Buyer</th><th>Subject</th><th>Message</th><th>Status</th><th>Response</th><th>Action</th></tr></thead>
          <tbody>
          <?php if ($inquiries && mysqli_num_rows($inquiries) > 0) { while ($inquiry = mysqli_fetch_assoc($inquiries)) { ?>
            <tr>
              <td><?php echo htmlspecialchars($inquiry['product_name'] ?: 'Unavailable'); ?></td>
              <td><?php echo htmlspecialchars($inquiry['user_name'] ?: ($inquiry['buyer_email'] ?: 'Buyer')); ?><br><small><?php echo htmlspecialchars($inquiry['user_email'] ?: $inquiry['buyer_email']); ?></small></td>
              <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
              <td><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></td>
              <td><?php $labels = ['Pending', 'Responded', 'Resolved']; $classes = ['warning', 'info', 'success']; ?><span class="badge text-bg-<?php echo $classes[(int) $inquiry['status']] ?? 'secondary'; ?>"><?php echo $labels[(int) $inquiry['status']] ?? 'Pending'; ?></span></td>
              <td><?php echo nl2br(htmlspecialchars($inquiry['response'] ?? '')); ?></td>
              <td>
                <form method="post" class="d-flex flex-column gap-2" style="min-width: 190px;">
                  <input type="hidden" name="inquiry_id" value="<?php echo (int) $inquiry['inquiry_id']; ?>">
                  <select name="status" class="form-select form-select-sm"><option value="0" <?php echo (int) $inquiry['status'] === 0 ? 'selected' : ''; ?>>Pending</option><option value="1" <?php echo (int) $inquiry['status'] === 1 ? 'selected' : ''; ?>>Responded</option><option value="2" <?php echo (int) $inquiry['status'] === 2 ? 'selected' : ''; ?>>Resolved</option></select>
                  <textarea name="response" class="form-control form-control-sm" rows="2" placeholder="Response"><?php echo htmlspecialchars($inquiry['response'] ?? ''); ?></textarea>
                  <button type="submit" name="updateInquiry" class="btn btn-sm btn-success">Save Status</button>
                </form>
              </td>
            </tr>
          <?php } } else { ?><tr><td colspan="7" class="text-center text-muted">No product inquiries yet.</td></tr><?php } ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
<?php include "inc/footer.php"; ?>
