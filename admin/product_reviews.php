<?php include "inc/header.php"; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['moderation_type'])) {
    $moderationType = $_POST['moderation_type'] === 'farmer' ? 'farmer' : 'product';
    $decision = $_POST['decision'] === 'approved' ? 'approved' : 'rejected';
    if ($moderationType === 'farmer' && isset($_POST['rating_id'])) {
        $ratingId = (int) $_POST['rating_id'];
        mysqli_query($db, "UPDATE farmer_ratings SET status = '$decision' WHERE rating_id = $ratingId");
    } elseif (isset($_POST['review_id'])) {
        $reviewId = (int) $_POST['review_id'];
        mysqli_query($db, "UPDATE product_reviews SET status = '$decision' WHERE review_id = $reviewId");
    }
}
$productReviews = mysqli_query($db, "SELECT r.review_id, r.rating, r.review_text, r.status, r.created_at, p.product_name, u.user_name FROM product_reviews r INNER JOIN products p ON p.product_id = r.product_id INNER JOIN users u ON u.user_id = r.buyer_id ORDER BY FIELD(r.status, 'pending', 'approved', 'rejected'), r.created_at DESC");
$farmerRatings = mysqli_query($db, "SELECT fr.rating_id, fr.farmer_email, fr.rating, fr.review_text, fr.status, fr.created_at, u.user_name AS buyer_name FROM farmer_ratings fr INNER JOIN users u ON u.user_id = fr.buyer_id ORDER BY FIELD(fr.status, 'pending', 'approved', 'rejected'), fr.created_at DESC");
?>
<div class="page-wrapper"><div class="page-content">
    <div class="page-header d-flex justify-content-between align-items-center"><div><div class="text-uppercase small fw-semibold text-success">Customer feedback</div><h2 class="mb-0 mt-2">Feedback management</h2></div><span class="badge bg-success">Moderation queue</span></div>
    <div class="card p-4">
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#productReviewsTab" type="button">Product Reviews</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#farmerRatingsTab" type="button">Farmer Reputation</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="productReviewsTab">
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Product</th><th>Buyer</th><th>Rating</th><th>Review</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead><tbody>
                <?php if ($productReviews && mysqli_num_rows($productReviews) > 0): while ($review = mysqli_fetch_assoc($productReviews)): ?><tr><td class="fw-semibold"><?php echo htmlspecialchars($review['product_name']); ?></td><td><?php echo htmlspecialchars($review['user_name']); ?></td><td class="text-warning"><?php echo str_repeat('&#9733;', (int) $review['rating']); ?></td><td><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></td><td><span class="badge bg-<?php echo $review['status'] === 'approved' ? 'success' : ($review['status'] === 'rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo htmlspecialchars(ucfirst($review['status'])); ?></span></td><td><small><?php echo htmlspecialchars(date('M j, Y g:i a', strtotime($review['created_at']))); ?></small></td><td><?php if ($review['status'] === 'pending'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="moderation_type" value="product"><input type="hidden" name="review_id" value="<?php echo (int) $review['review_id']; ?>"><button class="btn btn-sm btn-success" name="decision" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="rejected">Reject</button></form><?php else: ?><span class="text-muted">Reviewed</span><?php endif; ?></td></tr><?php endwhile; else: ?><tr><td colspan="7" class="text-center text-muted py-4">No product reviews submitted yet.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>

            <div class="tab-pane fade" id="farmerRatingsTab">
                <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Farmer</th><th>Buyer</th><th>Rating</th><th>Feedback</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead><tbody>
                <?php if ($farmerRatings && mysqli_num_rows($farmerRatings) > 0): while ($farmerRating = mysqli_fetch_assoc($farmerRatings)): ?><tr><td class="fw-semibold"><?php echo htmlspecialchars($farmerRating['farmer_email']); ?></td><td><?php echo htmlspecialchars($farmerRating['buyer_name']); ?></td><td class="text-warning"><?php echo str_repeat('&#9733;', (int) $farmerRating['rating']); ?></td><td><?php echo nl2br(htmlspecialchars($farmerRating['review_text'])); ?></td><td><span class="badge bg-<?php echo $farmerRating['status'] === 'approved' ? 'success' : ($farmerRating['status'] === 'rejected' ? 'danger' : 'warning text-dark'); ?>"><?php echo htmlspecialchars(ucfirst($farmerRating['status'])); ?></span></td><td><small><?php echo htmlspecialchars(date('M j, Y g:i a', strtotime($farmerRating['created_at']))); ?></small></td><td><?php if ($farmerRating['status'] === 'pending'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="moderation_type" value="farmer"><input type="hidden" name="rating_id" value="<?php echo (int) $farmerRating['rating_id']; ?>"><button class="btn btn-sm btn-success" name="decision" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" name="decision" value="rejected">Reject</button></form><?php else: ?><span class="text-muted">Reviewed</span><?php endif; ?></td></tr><?php endwhile; else: ?><tr><td colspan="7" class="text-center text-muted py-4">No farmer ratings submitted yet.</td></tr><?php endif; ?>
                </tbody></table></div>
            </div>
        </div>
    </div>
</div></div>
<?php include "inc/footer.php"; ?>
