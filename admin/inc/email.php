<?php
require_once __DIR__ . '/../../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function farmers_market_sender_email($db) {
    $senderEmail = '';
    $settingsQuery = @mysqli_query($db, "SELECT contact_email FROM site_settings ORDER BY id DESC LIMIT 1");
    if ($settingsQuery && ($settings = mysqli_fetch_assoc($settingsQuery))) {
        $senderEmail = trim($settings['contact_email'] ?? '');
    }

    if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        $adminQuery = @mysqli_query($db, "SELECT user_email FROM users WHERE role = 1 AND status = 1 ORDER BY user_id ASC LIMIT 1");
        if ($adminQuery && ($admin = mysqli_fetch_assoc($adminQuery))) {
            $senderEmail = trim($admin['user_email'] ?? '');
        }
    }

    return filter_var($senderEmail, FILTER_VALIDATE_EMAIL) ? $senderEmail : '';
}

function farmers_market_mail_setting($name, $default = '') {
    $value = getenv($name);
    return $value === false ? $default : trim($value);
}

function farmers_market_send_email($db, $recipient, $subject, $body, $replyTo = '') {
    $recipient = trim($recipient);
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $senderEmail = farmers_market_sender_email($db);
    if ($senderEmail === '') {
        return false;
    }

    $subject = str_replace(["\r", "\n"], '', $subject);
    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = farmers_market_mail_setting('MAIL_HOST', '127.0.0.1');
        $mailer->Port = (int) farmers_market_mail_setting('MAIL_PORT', '25');
        $mailer->SMTPAuth = farmers_market_mail_setting('MAIL_USERNAME') !== '';
        $mailer->Username = farmers_market_mail_setting('MAIL_USERNAME');
        $mailer->Password = farmers_market_mail_setting('MAIL_PASSWORD');
        $encryption = strtolower(farmers_market_mail_setting('MAIL_ENCRYPTION', 'tls'));
        if ($encryption === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mailer->SMTPSecure = '';
        }
        $mailer->SMTPAutoTLS = $encryption === 'tls';
        $mailer->CharSet = 'UTF-8';
        $mailer->isHTML(false);
        $mailer->setFrom(farmers_market_mail_setting('MAIL_FROM_ADDRESS', $senderEmail), farmers_market_mail_setting('MAIL_FROM_NAME', 'Farmers Market'));
        $mailer->addAddress($recipient);
        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mailer->addReplyTo($replyTo);
        }
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->send();
        return true;
    } catch (Exception $exception) {
        error_log('Farmers Market email failed: ' . $exception->getMessage());
        return false;
    }
}

function farmers_market_notify_admin_support($db, $customerEmail, $customerPhone, $subject, $message) {
    $adminQuery = @mysqli_query($db, "SELECT user_email FROM users WHERE role = 1 AND status = 1");
    if (!$adminQuery) {
        return;
    }

    $body = "A new support request was received.\n\n"
        . "From: " . $customerEmail . "\n"
        . "Phone: " . ($customerPhone ?: 'Not provided') . "\n"
        . "Subject: " . $subject . "\n\n"
        . $message;

    while ($admin = mysqli_fetch_assoc($adminQuery)) {
        farmers_market_send_email($db, $admin['user_email'], 'New support request: ' . $subject, $body, $customerEmail);
    }
}

function farmers_market_notify_customers_new_product($db, $productName, $description, $price) {
    $customersQuery = @mysqli_query($db, "SELECT user_email FROM users WHERE role = 3 AND status = 1");
    if (!$customersQuery) {
        return;
    }

    $body = "A new product is now available in the Farmers Market.\n\n"
        . "Product: " . $productName . "\n"
        . "Price: " . number_format((float) $price, 2) . " Tk\n\n"
        . ($description ?: 'Visit the marketplace to view this product and place an order.');

    while ($customer = mysqli_fetch_assoc($customersQuery)) {
        farmers_market_send_email($db, $customer['user_email'], 'New product available: ' . $productName, $body);
    }
}
?>
