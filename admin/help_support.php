<?php include "inc/header.php"; ?>

<div class="page-wrapper">
  <div class="page-content">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Help & Support</h4>
        <p class="text-muted">Submit a support request. Messages are saved to a file for admin review.</p>

        <?php
        $store = __DIR__ . '/support_messages.txt';
        if (isset($_POST['send_support'])) {
          $name = trim($_POST['name']);
          $email = trim($_POST['email']);
          $message = trim($_POST['message']);
          $entry = date('c') . " | " . $name . " | " . $email . "\n" . $message . "\n---\n";
          file_put_contents($store, $entry, FILE_APPEND);
          echo '<div class="alert alert-success">Support request saved.</div>';
        }
        ?>

        <form method="post" class="mt-3">
          <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input class="form-control" name="email" required></div>
          <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="5" required></textarea></div>
          <button class="btn btn-success" name="send_support">Send</button>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include "inc/footer.php"; ?>
