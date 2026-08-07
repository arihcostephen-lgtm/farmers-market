<?php include "inc/header.php"; ?>

<?php
$createSettingsTable = "CREATE TABLE IF NOT EXISTS site_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  site_title VARCHAR(255) NOT NULL DEFAULT 'Local Farm Market',
  contact_email VARCHAR(150) DEFAULT NULL,
  contact_phone VARCHAR(50) DEFAULT NULL,
  contact_address TEXT DEFAULT NULL,
  site_status TINYINT(1) NOT NULL DEFAULT 1,
  maintenance_message TEXT DEFAULT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
mysqli_query($db, $createSettingsTable);

if (isset($_POST['saveSettings'])) {
    $site_title = mysqli_real_escape_string($db, $_POST['site_title']);
    $contact_email = mysqli_real_escape_string($db, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($db, $_POST['contact_phone']);
    $contact_address = mysqli_real_escape_string($db, $_POST['contact_address']);
    $site_status = mysqli_real_escape_string($db, $_POST['site_status']);
    $maintenance_message = mysqli_real_escape_string($db, $_POST['maintenance_message']);

    $settingsQuery = mysqli_query($db, "SELECT id FROM site_settings ORDER BY id DESC LIMIT 1");

    if ($settingsQuery && mysqli_num_rows($settingsQuery) > 0) {
        $settingsRow = mysqli_fetch_assoc($settingsQuery);
        $updateSql = "UPDATE site_settings SET site_title='$site_title', contact_email='$contact_email', contact_phone='$contact_phone', contact_address='$contact_address', site_status='$site_status', maintenance_message='$maintenance_message' WHERE id='{$settingsRow['id']}'";
        mysqli_query($db, $updateSql);
    } else {
        $insertSql = "INSERT INTO site_settings (site_title, contact_email, contact_phone, contact_address, site_status, maintenance_message) VALUES ('$site_title', '$contact_email', '$contact_phone', '$contact_address', '$site_status', '$maintenance_message')";
        mysqli_query($db, $insertSql);
    }

    header("Location: system_config.php?updated=1");
    exit;
}

$settingsSql = mysqli_query($db, "SELECT * FROM site_settings ORDER BY id DESC LIMIT 1");
$settings = mysqli_fetch_assoc($settingsSql);
$site_title = $settings['site_title'] ?? '';
$contact_email = $settings['contact_email'] ?? '';
$contact_phone = $settings['contact_phone'] ?? '';
$contact_address = $settings['contact_address'] ?? '';
$site_status = $settings['site_status'] ?? 1;
$maintenance_message = $settings['maintenance_message'] ?? '';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">System Configuration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">System Configuration</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card radius-10 border-0 shadow-sm">
            <div class="card-body">
                <h4 class="mb-3">Update Site Settings</h4>

                <?php if (isset($_GET['updated']) && $_GET['updated'] == 1) { ?>
                    <div class="alert alert-success" role="alert">
                        System configuration saved successfully.
                    </div>
                <?php } ?>

                <form action="system_config.php" method="POST">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="site_title">Site Title</label>
                                <input type="text" id="site_title" name="site_title" class="form-control" value="<?php echo htmlspecialchars($site_title); ?>" placeholder="Local Farm Market" required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="contact_email">Contact Email</label>
                                <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($contact_email); ?>" placeholder="admin@example.com" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="contact_phone">Contact Phone</label>
                                <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($contact_phone); ?>" placeholder="+880 1234 567890" autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label for="site_status">Site Status</label>
                                <select id="site_status" name="site_status" class="form-select">
                                    <option value="1" <?php echo ($site_status == 1 ? 'selected' : ''); ?>>Live</option>
                                    <option value="0" <?php echo ($site_status == 0 ? 'selected' : ''); ?>>Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="contact_address">Contact Address</label>
                                <textarea id="contact_address" name="contact_address" class="form-control" rows="5" placeholder="Enter office address..."><?php echo htmlspecialchars($contact_address); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="maintenance_message">Maintenance Message</label>
                                <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="5" placeholder="Message shown during maintenance mode..."><?php echo htmlspecialchars($maintenance_message); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" name="saveSettings" class="btn btn-success">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>