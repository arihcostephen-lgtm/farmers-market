<?php include "inc/header.php"; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['decision'])) {
    $reviewId = (int) $_POST['review_id'];
    $decision = $_POST['decision'] === 'approved' ? 'approved' : 'rejected';
    mysqli_query($db, "UPDATE product_reviews SET status = '$decision' WHERE review_id = $reviewId");
}
$reviews = mysqli_query($db, "SELECT r.review_id, r.rating, r.review_text, r.status, r.created_at, p.product_name, u.user_name FROM product_reviews r INNER JOIN products p ON p.product_id = r.product_id INNER JOIN users u ON u.user_id = r.buyer_id ORDER BY FIELD(r.status, 'pending', 'approved', 'rejected'), r.created_at DESC");
?>
<div class="page-wrapper"><div class="page-content">
    <div class="page-header d-flex justify-content-between align-items-center"><div><div class="text-uppercase small fw-semibold text-success">Product showcase</div><h2 class="mb-0 mt-2">Product reviews</h2></div><span class="badge bg-success">Moderation queue</span></div>
    <div class="card p-4"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Product</th><th>Buyer</th><th>Rating</th><th>Review</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead><tbody>
    <?php if ($reviews && mysqli_num_rows($reviews) > 0): while ($review = mysqli_fetch_assoc($reviews)): ?><tr><td class="fw-semibold"><?php echo htmlspecialchars($review['product_name']); ?></td><td><?php echo htmlspecialchars($review['user_name']); ?></td><td class="text-warning"><?php echo str_repeat('&#9733;', (int) $review['rating']); ?></td><td><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></td><td><span class="badge bg-<?php echo $review['status'] === 'approved' ? 'success' : ($review['status'] === 'rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo htmlspecialchars(ucfirst($review['status'])); ?></span></td><td><small><?php echo htmlspecialchars(date('M j, Y g:i a', strtotime($review['created_at']))); ?></small></td><td><?php if ($review['status'] === 'pending'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="review_id" value="<?php echo (int) $review['review_id']; ?>"><button class="btn btn-sm btn-success" name="decision" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="rejected">Reject</button></form><?php else: ?><span class="text-muted">Reviewed</span><?php endif; ?></td></tr><?php endwhile; else: ?><tr><td colspan="7" class="text-center text-muted py-4">No product reviews submitted yet.</td></tr><?php endif; ?>
    </tbody></table></div></div>
</div></div>
<?php include "inc/footer.php"; ?>
