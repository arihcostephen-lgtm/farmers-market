<?php
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

function farmers_market_send_email($db, $recipient, $subject, $body, $replyTo = '') {
    $recipient = trim($recipient);
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $senderEmail = farmers_market_sender_email($db);
    if ($senderEmail === '' || !function_exists('mail')) {
        return false;
    }

    $subject = str_replace(["\r", "\n"], '', $subject);
    $headers = [
        'From: Farmers Market <' . $senderEmail . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    return @mail($recipient, $subject, $body, implode("\r\n", $headers));
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
