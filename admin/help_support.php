<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
    <?php
      if (isset($_POST['reply_support'])) {
        $commentId = (int) $_POST['comment_id'];
        $response = mysqli_real_escape_string($db, trim($_POST['response'] ?? ''));
        $status = (int) $_POST['status'];
        if ($status < 1 || $status > 2) {
          $status = 1;
        }
        $supportOwnerQuery = mysqli_query($db, "SELECT user_id, subject FROM comments WHERE id='$commentId' LIMIT 1");
        $supportOwner = $supportOwnerQuery ? mysqli_fetch_assoc($supportOwnerQuery) : null;
        mysqli_query($db, "UPDATE comments SET response='$response', status='$status', responded_at=NOW() WHERE id='$commentId'");
        if ($supportOwner && $response) {
          farmers_market_send_email($db, $supportOwner['user_id'], 'Response to your support request: ' . $supportOwner['subject'], $response);
        }
      }
      $supportQuery = mysqli_query($db, "SELECT * FROM comments ORDER BY cmt_date DESC");
      $statusLabels = [0 => 'Pending', 1 => 'Responded', 2 => 'Resolved'];
      $statusClasses = [0 => 'warning', 1 => 'info', 2 => 'success'];
    ?>
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Help & Support</div>
      <div class="ps-3"><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li><li class="breadcrumb-item active">Support Requests</li></ol></nav></div>
    </div>
    <div class="card shadow-sm"><div class="card-body">
      <h4>Support Requests</h4>
      <p class="text-muted">View requests from customers and farmers, reply to them, and mark them as responded or resolved.</p>
      <div class="table-responsive"><table class="table table-striped table-hover align-middle">
        <thead class="table-dark"><tr><th>Requester</th><th>Subject</th><th>Request</th><th>Status</th><th>Reply</th></tr></thead>
        <tbody>
        <?php if ($supportQuery && mysqli_num_rows($supportQuery) > 0) { while ($support = mysqli_fetch_assoc($supportQuery)) { $supportStatus = ((int) $support['status'] === 2 && empty($support['response'])) ? 0 : (int) $support['status']; ?>
          <tr><td><?php echo htmlspecialchars($support['user_id']); ?><br><small><?php echo htmlspecialchars($support['user_number']); ?></small></td><td><?php echo htmlspecialchars($support['subject']); ?></td><td><?php echo nl2br(htmlspecialchars($support['comments'])); ?></td><td><span class="badge text-bg-<?php echo $statusClasses[$supportStatus] ?? 'secondary'; ?>"><?php echo $statusLabels[$supportStatus] ?? 'Pending'; ?></span></td><td><form method="post" class="d-flex flex-column gap-2" style="min-width:220px"><input type="hidden" name="comment_id" value="<?php echo (int) $support['id']; ?>"><textarea name="response" class="form-control form-control-sm" rows="3" placeholder="Write a reply"><?php echo htmlspecialchars($support['response'] ?? ''); ?></textarea><select name="status" class="form-select form-select-sm"><option value="1" <?php echo $supportStatus === 1 ? 'selected' : ''; ?>>Responded</option><option value="2" <?php echo $supportStatus === 2 ? 'selected' : ''; ?>>Resolved</option></select><button name="reply_support" class="btn btn-sm btn-success">Save Reply</button></form></td></tr>
        <?php } } else { ?><tr><td colspan="5" class="text-center text-muted">No support requests yet.</td></tr><?php } ?>
        </tbody>
      </table></div>
    </div></div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
