<?php
include "inc/header.php";
require_once __DIR__ . '/locations_helper.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$customerId = (int) $_SESSION['user_id'];
$customerName = htmlspecialchars($_SESSION['user_name'] ?? 'Customer');
$customerEmail = $_SESSION['user_email'] ?? '';
$customerPhone = $_SESSION['user_phone'] ?? '';
$customerEmailSql = $db->real_escape_string($customerEmail);
$checkoutProductId = (int) ($_GET['order_product'] ?? $_POST['product_id'] ?? 0);
$checkoutError = '';
$cartSaved = false;
$profileUpdateError = '';
$profileUpdateSuccess = '';

$customerDeliveryColumn = $db->query("SHOW COLUMNS FROM users LIKE 'delivery_location'");
if (!$customerDeliveryColumn || $customerDeliveryColumn->num_rows === 0) {
    $db->query("ALTER TABLE users ADD COLUMN delivery_location TEXT DEFAULT NULL AFTER user_address");
}

$currentCustomer = $db->query("SELECT user_name, user_email, user_phone, user_address, delivery_location FROM users WHERE user_id = '$customerId' LIMIT 1")->fetch_assoc();
if ($currentCustomer) {
    $customerName = htmlspecialchars($currentCustomer['user_name'] ?? $customerName);
    $customerPhone = $currentCustomer['user_phone'] ?? $customerPhone;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_customer_profile'])) {
    $profileName = trim($_POST['profile_name'] ?? '');
    $profilePhone = trim($_POST['profile_phone'] ?? '');
    $profileAddress = trim($_POST['profile_address'] ?? '');
    $profileLocation = trim($_POST['profile_delivery_location'] ?? '');
    $profilePassword = $_POST['profile_password'] ?? '';
    $profileConfirmPassword = $_POST['profile_confirm_password'] ?? '';

    if ($profileName === '' || $profilePhone === '' || $profileLocation === '') {
        $profileUpdateError = 'Name, phone number, and delivery location are required.';
    } elseif ($profilePassword !== '' && $profilePassword !== $profileConfirmPassword) {
        $profileUpdateError = 'Passwords do not match. Please re-enter them.';
    } else {
        $profileNameEsc = $db->real_escape_string($profileName);
        $profilePhoneEsc = $db->real_escape_string($profilePhone);
        $profileAddressEsc = $db->real_escape_string($profileAddress);
        $profileLocationEsc = $db->real_escape_string($profileLocation);
        $profileSql = "UPDATE users SET user_name = '$profileNameEsc', user_phone = '$profilePhoneEsc', user_address = '$profileAddressEsc', delivery_location = '$profileLocationEsc'";

        if ($profilePassword !== '') {
            $profileSql .= ", user_password = '" . sha1($profilePassword) . "'";
        }

        $profileSql .= " WHERE user_id = '$customerId'";

        if ($db->query($profileSql)) {
            $_SESSION['user_name'] = $profileName;
            $_SESSION['user_phone'] = $profilePhone;
            $customerName = htmlspecialchars($profileName);
            $customerPhone = $profilePhone;
            $profileUpdateSuccess = 'Your profile was updated successfully.';
        } else {
            $profileUpdateError = 'Unable to update your profile. Please try again.';
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_to_cart'])) {
    $checkoutProductId = (int) ($_POST['product_id'] ?? 0);
    $checkoutQuantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $deliveryLocationInput = trim($_POST['delivery_location'] ?? '');
    $deliveryLocation = $db->real_escape_string($deliveryLocationInput);
    $deliveryNotes = $db->real_escape_string(trim($_POST['delivery_notes'] ?? ''));

    if ($deliveryLocationInput === '') {
        $checkoutError = 'Please select a delivery location before adding this order.';
    }

    if ($checkoutError === '') {
        $db->begin_transaction();
        $checkoutProductQuery = $db->query("SELECT product_name, price, product_unit, stock_quantity, category_id FROM products WHERE product_id = $checkoutProductId AND status != 0 FOR UPDATE");
        $checkoutProduct = $checkoutProductQuery ? $checkoutProductQuery->fetch_assoc() : null;
        if ($checkoutProduct && $checkoutQuantity <= (int) $checkoutProduct['stock_quantity']) {
            $checkoutUnit = mysqli_real_escape_string($db, $checkoutProduct['product_unit'] ?? 'kilogram');
            $checkoutCategory = (int) ($checkoutProduct['category_id'] ?? 0);
            $checkoutTaxQuery = $db->query("SELECT rate_percent FROM tax_rules WHERE status = 1 AND min_quantity <= $checkoutQuantity AND (max_quantity IS NULL OR max_quantity >= $checkoutQuantity) AND (applies_to = 'all' OR applies_to = '$checkoutCategory') AND (applies_unit = 'all' OR applies_unit = '$checkoutUnit') ORDER BY (applies_to = '$checkoutCategory') DESC, (applies_unit = '$checkoutUnit') DESC, rate_percent DESC LIMIT 1");
            $checkoutTaxRate = $checkoutTaxQuery ? (float) ($checkoutTaxQuery->fetch_assoc()['rate_percent'] ?? 0) : 0;
            $checkoutSubtotal = (float) $checkoutProduct['price'] * $checkoutQuantity;
            $checkoutTax = round($checkoutSubtotal * ($checkoutTaxRate / 100), 2);
            $checkoutTotal = round($checkoutSubtotal + $checkoutTax, 2);
            $checkoutName = mysqli_real_escape_string($db, $checkoutProduct['product_name']);
            $insertOrder = $db->query("INSERT INTO order_list (user_id, user_phone, delivery_location, delivery_notes, or_name, or_category, price, tax_amount, total_amount, quantity, order_unit, status, join_date) VALUES ('$customerId', '" . mysqli_real_escape_string($db, $customerPhone) . "', '$deliveryLocation', '$deliveryNotes', '$checkoutName', '$checkoutProductId', '$checkoutSubtotal', '$checkoutTax', '$checkoutTotal', '$checkoutQuantity', '$checkoutUnit', 0, NOW())");
            $stockUpdated = $db->query("UPDATE products SET stock_quantity = stock_quantity - $checkoutQuantity WHERE product_id = $checkoutProductId AND stock_quantity >= $checkoutQuantity");
            if ($insertOrder && $stockUpdated) {
                $newOrderId = $db->insert_id;
                $db->commit();
                $checkoutProductId = 0;
                $cartMessage = 'Product added to your cart. Choose Checkout from the order list when you are ready to pay.';
                $cartSaved = true;
            }
        }
        if (!$cartSaved) {
            $db->rollback();
        }
        if (!$cartSaved && $checkoutError === '') {
            $checkoutError = 'The requested quantity is not available.';
        }
    }
}

$productsSql = "SELECT p.*, c.cat_name, u.user_name AS farmer_name, f.farm_address, f.farm_location_id, f.farm_latitude, f.farm_longitude, f.market_name, f.market_location_id, f.market_latitude, f.market_longitude, f.market_operating_days, f.market_hours, f.pickup_instructions, f.delivery_instructions, 
  COALESCE(f.farm_latitude, l1.latitude) AS farm_map_latitude, COALESCE(f.farm_longitude, l1.longitude) AS farm_map_longitude,
  COALESCE(f.market_latitude, l2.latitude) AS market_map_latitude, COALESCE(f.market_longitude, l2.longitude) AS market_map_longitude,
  COALESCE(l1.location_name, '') AS farm_location, COALESCE(l2.location_name, '') AS market_location 
  FROM products p 
  LEFT JOIN category c ON c.cat_id = p.category_id 
  LEFT JOIN users u ON u.user_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci 
  LEFT JOIN farmer f ON f.farm_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci 
  LEFT JOIN locations l1 ON l1.location_id = f.farm_location_id 
  LEFT JOIN locations l2 ON l2.location_id = f.market_location_id 
  WHERE p.status != 0 ORDER BY p.product_name ASC";
$productsResult = $db->query($productsSql);
$productsCount = $productsResult->num_rows;
$featuredProducts = $db->query("SELECT product_id, product_name, price, product_unit, stock_quantity, image FROM products WHERE status != 0 AND stock_quantity > 0 ORDER BY view_count DESC, join_date DESC LIMIT 3");

$ordersSql = "SELECT * FROM order_list WHERE user_id = '$customerId' OR user_id = '$customerEmailSql' ORDER BY or_id DESC";
$ordersResult = $db->query($ordersSql);
$ordersCount = $ordersResult->num_rows;
$cartSql = "SELECT * FROM order_list WHERE (user_id = '$customerId' OR user_id = '$customerEmailSql') AND payment_status <> 'paid' AND status <> 3 ORDER BY or_id DESC";
$cartResult = $db->query($cartSql);
$cartCount = $cartResult ? $cartResult->num_rows : 0;
$cartTotalQuery = $db->query("SELECT COALESCE(SUM(CASE WHEN total_amount > 0 THEN total_amount ELSE price + COALESCE(tax_amount, 0) END), 0) AS total FROM order_list WHERE (user_id = '$customerId' OR user_id = '$customerEmailSql') AND payment_status <> 'paid' AND status <> 3");
$cartTotal = $cartTotalQuery ? (float) ($cartTotalQuery->fetch_assoc()['total'] ?? 0) : 0;
$orderListResult = $db->query("SELECT o.or_id, o.or_name, o.quantity, o.order_unit, o.price, o.tax_amount, o.total_amount, o.delivery_location, o.delivery_update, o.payment_status, o.status, o.join_date, c.cat_name FROM order_list o LEFT JOIN products p ON p.product_id = o.or_category LEFT JOIN category c ON c.cat_id = p.category_id WHERE o.user_id = '$customerId' OR o.user_id = '$customerEmailSql' ORDER BY o.join_date DESC, o.or_id DESC");
$checkoutProductQuery = $checkoutProductId > 0 ? $db->query("SELECT product_id, product_name, price, product_unit, stock_quantity, category_id FROM products WHERE product_id = $checkoutProductId AND status != 0 LIMIT 1") : false;
$checkoutProduct = $checkoutProductQuery ? $checkoutProductQuery->fetch_assoc() : null;
$checkoutTaxRate = 0;
$checkoutTaxRules = [];
if ($checkoutProduct) {
    $checkoutUnit = $db->real_escape_string($checkoutProduct['product_unit'] ?? 'kilogram');
    $checkoutCategory = (int) ($checkoutProduct['category_id'] ?? 0);
    $checkoutTaxQuery = $db->query("SELECT min_quantity, max_quantity, rate_percent FROM tax_rules WHERE status = 1 AND (applies_to = 'all' OR applies_to = '$checkoutCategory') AND (applies_unit = 'all' OR applies_unit = '$checkoutUnit') ORDER BY (applies_to = '$checkoutCategory') DESC, (applies_unit = '$checkoutUnit') DESC, rate_percent DESC");
    if ($checkoutTaxQuery) {
        while ($checkoutTaxRow = $checkoutTaxQuery->fetch_assoc()) {
            $checkoutTaxRules[] = ['min' => (int) $checkoutTaxRow['min_quantity'], 'max' => $checkoutTaxRow['max_quantity'] === null ? null : (int) $checkoutTaxRow['max_quantity'], 'rate' => (float) $checkoutTaxRow['rate_percent']];
        }
    }
    foreach ($checkoutTaxRules as $checkoutTaxRule) {
        if ($checkoutTaxRule['min'] <= 1 && ($checkoutTaxRule['max'] === null || $checkoutTaxRule['max'] >= 1)) {
            $checkoutTaxRate = $checkoutTaxRule['rate'];
            break;
        }
    }
}

$commentsCount = 0;
if ($customerEmail) {
    $commentsCount = (int) $db->query("SELECT COUNT(*) AS total FROM comments WHERE user_id = '" . $db->real_escape_string($customerEmail) . "'")->fetch_assoc()['total'];
}

$categories = $db->query("SELECT * FROM category WHERE is_parent = 1 AND status = 1 ORDER BY cat_name ASC");
$message = '';
$cartMessage = $cartMessage ?? '';
$orderActionMessage = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
if ($requestMethod === 'POST' && isset($_POST['cancel_order'])) {
    $cancelOrderId = (int) ($_POST['cancel_order_id'] ?? 0);
    $cancelOrder = $db->query("UPDATE order_list SET status = 3, updated_at = NOW() WHERE or_id = $cancelOrderId AND (user_id = '$customerId' OR user_id = '$customerEmailSql') AND status IN (0, 1)");
    $orderActionMessage = $cancelOrder && $db->affected_rows > 0 ? 'Order #' . $cancelOrderId . ' was cancelled.' : 'This order cannot be cancelled.';
    $ordersResult = $db->query($ordersSql);
    $orderListResult = $db->query("SELECT o.or_id, o.or_name, o.quantity, o.order_unit, o.price, o.tax_amount, o.total_amount, o.delivery_location, o.delivery_update, o.payment_status, o.status, o.join_date, c.cat_name FROM order_list o LEFT JOIN products p ON p.product_id = o.or_category LEFT JOIN category c ON c.cat_id = p.category_id WHERE o.user_id = '$customerId' OR o.user_id = '$customerEmailSql' ORDER BY o.join_date DESC, o.or_id DESC");
    $cartResult = $db->query($cartSql);
    $cartCount = $cartResult ? $cartResult->num_rows : 0;
}
if ($requestMethod === 'POST' && isset($_POST['update_cart_quantity'])) {
    $cartOrderId = (int) ($_POST['cart_order_id'] ?? 0);
    $newQuantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $db->begin_transaction();
    $cartOrderQuery = $db->query("SELECT o.quantity, o.or_category, o.payment_status, o.status, p.price, p.product_unit, p.stock_quantity, p.category_id FROM order_list o INNER JOIN products p ON p.product_id = o.or_category WHERE o.or_id = $cartOrderId AND (o.user_id = '$customerId' OR o.user_id = '$customerEmailSql') AND o.payment_status <> 'paid' AND o.status <> 3 FOR UPDATE");
    $cartOrder = $cartOrderQuery ? $cartOrderQuery->fetch_assoc() : null;
    $quantityDifference = $cartOrder ? $newQuantity - (int) $cartOrder['quantity'] : 0;
    $canUpdate = $cartOrder && ($quantityDifference <= 0 || $quantityDifference <= (int) $cartOrder['stock_quantity']);
    if ($canUpdate) {
        $unit = $db->real_escape_string($cartOrder['product_unit']);
        $categoryId = (int) $cartOrder['category_id'];
        $taxQuery = $db->query("SELECT rate_percent FROM tax_rules WHERE status = 1 AND min_quantity <= $newQuantity AND (max_quantity IS NULL OR max_quantity >= $newQuantity) AND (applies_to = 'all' OR applies_to = '$categoryId') AND (applies_unit = 'all' OR applies_unit = '$unit') ORDER BY (applies_to = '$categoryId') DESC, (applies_unit = '$unit') DESC, rate_percent DESC LIMIT 1");
        $taxRate = $taxQuery ? (float) ($taxQuery->fetch_assoc()['rate_percent'] ?? 0) : 0;
        $subtotal = (float) $cartOrder['price'] * $newQuantity;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = round($subtotal + $taxAmount, 2);
        $stockChanged = $quantityDifference === 0 || $db->query("UPDATE products SET stock_quantity = stock_quantity - $quantityDifference WHERE product_id = " . (int) $cartOrder['or_category'] . " AND stock_quantity >= $quantityDifference");
        $orderChanged = $stockChanged && $db->query("UPDATE order_list SET quantity = $newQuantity, price = '$subtotal', tax_amount = '$taxAmount', total_amount = '$totalAmount', updated_at = NOW() WHERE or_id = $cartOrderId");
        if ($orderChanged) {
            $db->commit();
            $orderActionMessage = 'Cart quantity updated.';
        } else {
            $db->rollback();
            $orderActionMessage = 'The requested quantity is not available.';
        }
    } else {
        $db->rollback();
        $orderActionMessage = 'The requested quantity is not available.';
    }
    $cartResult = $db->query($cartSql);
    $cartCount = $cartResult ? $cartResult->num_rows : 0;
}
$cartTotalQuery = $db->query("SELECT COALESCE(SUM(CASE WHEN total_amount > 0 THEN total_amount ELSE price + COALESCE(tax_amount, 0) END), 0) AS total FROM order_list WHERE (user_id = '$customerId' OR user_id = '$customerEmailSql') AND payment_status <> 'paid' AND status <> 3");
$cartTotal = $cartTotalQuery ? (float) ($cartTotalQuery->fetch_assoc()['total'] ?? 0) : 0;
$reviewError = '';
$farmerRatingError = '';
$inquiryMessage = '';
$inquiryError = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_review'])) {
    $reviewProductId = (int) ($_POST['review_product_id'] ?? 0);
    $reviewOrderId = (int) ($_POST['review_order_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $reviewTextInput = trim($_POST['review_text'] ?? '');
    $eligibleReview = $db->query("SELECT o.or_id FROM order_list o INNER JOIN products p ON p.product_id = o.or_category WHERE o.or_id = $reviewOrderId AND (o.user_id = '$customerId' OR o.user_id = '$customerEmailSql') AND o.or_category = $reviewProductId AND o.status = 2 LIMIT 1");
    $alreadyReviewed = $db->query("SELECT review_id FROM product_reviews WHERE order_id = $reviewOrderId LIMIT 1");
    if (!$eligibleReview || $eligibleReview->num_rows === 0 || $rating < 1 || $rating > 5 || $reviewTextInput === '' || ($alreadyReviewed && $alreadyReviewed->num_rows > 0)) {
        $reviewError = 'Select a fulfilled order, choose a rating from 1 to 5, and enter your review.';
    } else {
        $reviewText = $db->real_escape_string($reviewTextInput);
        if ($db->query("INSERT INTO product_reviews (product_id, buyer_id, order_id, rating, review_text, status) VALUES ($reviewProductId, $customerId, $reviewOrderId, $rating, '$reviewText', 'pending')")) {
            $message = 'Thank you. Your product review was submitted for moderation.';
        } else {
            $reviewError = 'Unable to save your review. Please try again.';
        }
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_farmer_rating'])) {
    $farmerOrderId = (int) ($_POST['farmer_order_id'] ?? 0);
    $farmerRating = (int) ($_POST['farmer_rating'] ?? 0);
    $farmerReviewTextInput = trim($_POST['farmer_review_text'] ?? '');
    $farmerOrderQuery = $db->query("SELECT o.or_id, p.seller_email, p.product_name FROM order_list o INNER JOIN products p ON p.product_id = o.or_category WHERE o.or_id = $farmerOrderId AND (o.user_id = '$customerId' OR o.user_id = '$customerEmailSql') AND o.status = 2 LIMIT 1");
    $farmerOrder = $farmerOrderQuery ? $farmerOrderQuery->fetch_assoc() : null;
    $farmerAlreadyReviewed = $db->query("SELECT rating_id FROM farmer_ratings WHERE order_id = $farmerOrderId LIMIT 1");
    if (!$farmerOrder || !$farmerOrder['seller_email'] || $farmerRating < 1 || $farmerRating > 5 || $farmerReviewTextInput === '' || ($farmerAlreadyReviewed && $farmerAlreadyReviewed->num_rows > 0)) {
        $farmerRatingError = 'Select a fulfilled order, choose a rating from 1 to 5, and enter your farmer feedback.';
    } else {
        $farmerEmail = $db->real_escape_string($farmerOrder['seller_email']);
        $farmerReviewText = $db->real_escape_string($farmerReviewTextInput);
        if ($db->query("INSERT INTO farmer_ratings (farmer_email, buyer_id, order_id, rating, review_text, status) VALUES ('$farmerEmail', $customerId, $farmerOrderId, $farmerRating, '$farmerReviewText', 'pending')")) {
            $message = 'Thank you. Your farmer rating was submitted for moderation.';
        } else {
            $farmerRatingError = 'Unable to save your farmer rating. Please try again.';
        }
    }
}
$ratingSummary = $db->query("SELECT product_id, ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS review_count FROM product_reviews WHERE status = 'approved' GROUP BY product_id");
$productRatings = [];
if ($ratingSummary) {
    while ($ratingRow = $ratingSummary->fetch_assoc()) {
        $productRatings[(int) $ratingRow['product_id']] = $ratingRow;
    }
}
$farmerRatingSummary = $db->query("SELECT farmer_email, ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS review_count FROM farmer_ratings WHERE status = 'approved' GROUP BY farmer_email");
$farmerRatings = [];
if ($farmerRatingSummary) {
    while ($farmerRatingRow = $farmerRatingSummary->fetch_assoc()) {
        $farmerRatings[strtolower(trim((string) $farmerRatingRow['farmer_email']))] = $farmerRatingRow;
    }
}
$reviewableOrders = $db->query("SELECT o.or_id, o.or_name, o.or_category FROM order_list o LEFT JOIN product_reviews r ON r.order_id = o.or_id WHERE (o.user_id = '$customerId' OR o.user_id = '$customerEmailSql') AND o.status = 2 AND r.review_id IS NULL ORDER BY o.or_id DESC");
$farmerReviewableOrders = $db->query("SELECT o.or_id, o.or_name, p.product_name, p.seller_email, COALESCE(u.user_name, p.seller_email) AS farmer_name FROM order_list o INNER JOIN products p ON p.product_id = o.or_category LEFT JOIN users u ON u.user_email COLLATE utf8mb4_unicode_ci = p.seller_email COLLATE utf8mb4_unicode_ci LEFT JOIN farmer_ratings fr ON fr.order_id = o.or_id WHERE (o.user_id = '$customerId' OR o.user_id = '$customerEmailSql') AND o.status = 2 AND fr.rating_id IS NULL ORDER BY o.or_id DESC");
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_inquiry'])) {
    $inquiryProductId = (int) ($_POST['inquiry_product_id'] ?? 0);
    $inquirySubjectInput = trim($_POST['inquiry_subject'] ?? '');
    $inquiryTextInput = trim($_POST['inquiry_message'] ?? '');
    $inquiryProductQuery = $db->query("SELECT product_id, product_name, seller_email FROM products WHERE product_id = $inquiryProductId AND status = 1 LIMIT 1");
    $inquiryProduct = $inquiryProductQuery ? $inquiryProductQuery->fetch_assoc() : null;
    if (!$inquiryProduct || $inquirySubjectInput === '' || $inquiryTextInput === '') {
        $inquiryError = 'Select a product and enter both a subject and message.';
    } else {
        $inquirySubject = $db->real_escape_string($inquirySubjectInput);
        $inquiryText = $db->real_escape_string($inquiryTextInput);
        $buyerEmail = $db->real_escape_string($customerEmail);
        $insertInquiry = "INSERT INTO product_inquiries (product_id, buyer_id, buyer_email, subject, message, status, created_at) VALUES ($inquiryProductId, $customerId, '$buyerEmail', '$inquirySubject', '$inquiryText', 0, NOW())";
        if ($db->query($insertInquiry)) {
            farmers_market_send_email($db, $inquiryProduct['seller_email'], 'New inquiry about ' . $inquiryProduct['product_name'], "A customer submitted a new product inquiry.\n\nProduct: " . $inquiryProduct['product_name'] . "\nSubject: " . $inquirySubjectInput . "\n\n" . $inquiryTextInput, $customerEmail);
            $inquiryMessage = 'Your inquiry was sent and is pending a response.';
        } else {
            $inquiryError = 'Unable to save your inquiry: ' . $db->error;
        }
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_comment'])) {
    $subject = $db->real_escape_string(trim($_POST['comment_subject']));
    $commentText = $db->real_escape_string(trim($_POST['comment_text']));
    $commentStatus = 0;
    $commentUserId = $db->real_escape_string($customerEmail ?: $customerId);
    $commentNumber = $db->real_escape_string($customerPhone ?: '');
    if ($subject && $commentText) {
        $insertComment = "INSERT INTO comments (user_id, user_number, subject, comments, status, cmt_date) VALUES ('{$commentUserId}','{$commentNumber}','{$subject}','{$commentText}','{$commentStatus}', now())";
        if ($db->query($insertComment)) {
            $message = 'Comment submitted successfully. Thank you!';
            $commentsCount++;
        } else {
            $message = 'Unable to save your comment. Please try again.';
        }
    }
}

// Handle quick product rating
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_product_rating'])) {
    $ratingProductId = (int) ($_POST['rating_product_id'] ?? 0);
    $ratingValue = (int) ($_POST['rating_value'] ?? 0);
    
    if ($ratingProductId > 0 && $ratingValue >= 1 && $ratingValue <= 5) {
        // Check if product exists
        $productExists = $db->query("SELECT product_id FROM products WHERE product_id = $ratingProductId LIMIT 1");
        if ($productExists && $productExists->num_rows > 0) {
            // Insert rating (allow multiple ratings from same user for same product)
            if ($db->query("INSERT INTO product_reviews (product_id, buyer_id, rating, review_text, status, created_at) VALUES ($ratingProductId, $customerId, $ratingValue, 'Quick rating', 'approved', NOW())")) {
                $message = 'Thank you! Your rating has been saved.';
            }
        }
    }
}

$recentComments = $db->query("SELECT * FROM comments WHERE user_id = '" . $db->real_escape_string($customerEmail ?: $customerId) . "' ORDER BY cmt_date DESC LIMIT 5");
$inquiryHistory = $db->query("SELECT i.*, p.product_name FROM product_inquiries i LEFT JOIN products p ON p.product_id = i.product_id WHERE i.buyer_id = '$customerId' ORDER BY i.created_at DESC");
$farmerNotifications = $db->query("SELECT notification_id, title, message, created_at, is_read FROM farmer_notifications WHERE farmer_id = $customerId ORDER BY created_at DESC, notification_id DESC LIMIT 10");
$inquiryLabels = ['Pending', 'Responded', 'Resolved'];
$inquiryClasses = ['warning', 'info', 'success'];

// Customer messaging with farmers
$selectedCustomerConversation = null;
$selectedCustomerMessages = [];
$unreadMessagesCount = 0;
$allCustomerConversations = null;
$messagingError = '';
$messagingSuccess = '';

// Handle starting a new conversation
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['start_new_conversation'])) {
    $farmerId = (int) ($_POST['farmer_id'] ?? 0);
    $firstMessage = trim($_POST['first_message'] ?? '');
    
    if ($farmerId <= 0) {
        $messagingError = 'Please select a farmer.';
    } elseif (empty($firstMessage)) {
        $messagingError = 'Please enter a message.';
    } elseif (strlen($firstMessage) > 5000) {
        $messagingError = 'Message is too long (max 5000 characters).';
    } else {
        // Check if conversation already exists
        $existingConv = $db->query("SELECT conversation_id FROM conversations WHERE customer_id = $customerId AND farmer_id = $farmerId LIMIT 1");
        if ($existingConv && $existingConv->num_rows > 0) {
            $conv = $existingConv->fetch_assoc();
            $conversationId = $conv['conversation_id'];
        } else {
            // Create new conversation
            if ($db->query("INSERT INTO conversations (customer_id, farmer_id, created_at, updated_at) VALUES ($customerId, $farmerId, NOW(), NOW())")) {
                $conversationId = $db->insert_id;
            } else {
                $messagingError = 'Failed to start conversation. Please try again.';
                $conversationId = 0;
            }
        }
        
        // Send the first message
        if ($conversationId > 0) {
            if ($db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($conversationId, $customerId, 'customer', '" . $db->real_escape_string($firstMessage) . "', NOW())")) {
                $db->query("UPDATE conversations SET updated_at = NOW() WHERE conversation_id = $conversationId");
                $messagingSuccess = 'Message sent! Farmer will receive your message shortly.';
                $_SESSION['last_conversation_id'] = $conversationId;
            } else {
                $messagingError = 'Failed to send message. Please try again.';
            }
        }
    }
}

// Handle sending a message from customer
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['send_customer_message'])) {
    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    
    if (empty($messageText)) {
        $messagingError = 'Message cannot be empty.';
    } elseif (strlen($messageText) > 5000) {
        $messagingError = 'Message is too long (max 5000 characters).';
    } elseif ($conversationId > 0) {
        $conversationAccess = $db->query("SELECT conversation_id FROM conversations WHERE conversation_id = $conversationId AND customer_id = $customerId LIMIT 1");
        if (!$conversationAccess || $conversationAccess->num_rows === 0) {
            $messagingError = 'That conversation is not available.';
        } elseif ($db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($conversationId, $customerId, 'customer', '" . $db->real_escape_string($messageText) . "', NOW())")) {
            $db->query("UPDATE conversations SET updated_at = NOW() WHERE conversation_id = $conversationId");
            $messagingSuccess = 'Message sent!';
            $_SESSION['last_conversation_id'] = $conversationId;
        } else {
            $messagingError = 'Failed to send message. Please try again.';
        }
    }
}

// Get all conversations for this customer
$conversationsSql = "SELECT c.conversation_id, c.farmer_id, c.subject, c.updated_at, u.user_name AS farmer_name, u.user_email AS farmer_email, 
                    COUNT(CASE WHEN m.is_read = 0 AND m.sender_id != $customerId THEN 1 END) AS unread_count,
                    (SELECT message_text FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) AS last_message,
                    (SELECT MAX(created_at) FROM messages WHERE conversation_id = c.conversation_id) AS last_message_date
                    FROM conversations c
                    LEFT JOIN users u ON u.user_id = c.farmer_id
                    LEFT JOIN messages m ON m.conversation_id = c.conversation_id
                    WHERE c.customer_id = $customerId
                    GROUP BY c.conversation_id, c.farmer_id, c.subject, c.updated_at, u.user_name, u.user_email
                    ORDER BY c.updated_at DESC";
$allCustomerConversations = $db->query($conversationsSql);
// Determine which conversation to display - check session first, then POST, then GET
$selectedConversationId = 0;
if (isset($_SESSION['last_conversation_id'])) {
    $selectedConversationId = (int) $_SESSION['last_conversation_id'];
    unset($_SESSION['last_conversation_id']);
} elseif (isset($_POST['conversation_id'])) {
    $selectedConversationId = (int) $_POST['conversation_id'];
} elseif (isset($_GET['conversation_id'])) {
    $selectedConversationId = (int) $_GET['conversation_id'];
}


// Get messages for selected conversation
if ($selectedConversationId > 0) {
    $convCheck = $db->query("SELECT c.*, u.user_name AS farmer_name, u.user_email AS farmer_email FROM conversations c LEFT JOIN users u ON u.user_id = c.farmer_id WHERE c.conversation_id = $selectedConversationId AND c.customer_id = $customerId LIMIT 1");
    if ($convCheck && $convCheck->num_rows > 0) {
        $selectedCustomerConversation = $convCheck->fetch_assoc();
        
        // Mark messages as read
        $db->query("UPDATE messages SET is_read = 1, read_at = NOW() WHERE conversation_id = $selectedConversationId AND sender_id != $customerId AND is_read = 0");
        
        // Get all messages
        $messagesSql = "SELECT m.*, u.user_name AS sender_name FROM messages m LEFT JOIN users u ON u.user_id = m.sender_id WHERE m.conversation_id = $selectedConversationId ORDER BY m.created_at ASC";
        $messagesResult = $db->query($messagesSql);
        while ($msg = $messagesResult->fetch_assoc()) {
            $selectedCustomerMessages[] = $msg;
        }
    }
}

// Get list of farmers the customer can message (from their orders + all active farmers)
$availableFarmersQuery = $db->query("SELECT DISTINCT u.user_id, u.user_name, u.user_email FROM users u WHERE u.role = 2 AND u.status = 1 ORDER BY u.user_name ASC");

// Get unread message count
$unreadResult = $db->query("SELECT COUNT(*) as count FROM messages WHERE conversation_id IN (SELECT conversation_id FROM conversations WHERE customer_id = $customerId) AND is_read = 0 AND sender_id != $customerId");
$unreadMessagesCount = $unreadResult ? (int) $unreadResult->fetch_assoc()['count'] : 0;
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap');
    body { background: #06130d; color: #e9fff4; font-family: 'Manrope', 'Segoe UI', sans-serif; }
    .customer-dashboard { min-height: 100vh; padding: 30px 0; }
    .dashboard-shell { background: rgba(6,19,13,0.95); border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,0.25); }
    .dashboard-sidebar { background: #06130d; border-right: 1px solid rgba(255,255,255,0.06); }
    .dashboard-sidebar h4 { color: #e9fff4; font-weight: 500; letter-spacing: .2px; }
    .dashboard-sidebar .sidebar-caption { color: #9ef7b8; font-size: .78rem; letter-spacing: .08em; text-transform: uppercase; }
    .dashboard-sidebar .nav { gap: 6px; }
    .dashboard-sidebar .nav-link {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 46px;
        padding: 10px 12px;
        color: #cfeee0;
        border: 1px solid transparent;
        border-radius: 6px;
        transition: color .15s ease, background .15s ease, border-color .15s ease;
    }
    .dashboard-sidebar .nav-link .sidebar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        width: 28px;
        color: #9fe9c1;
        font-size: 1rem;
    }
    .dashboard-sidebar .nav-link .sidebar-label { flex: 1; }
    .dashboard-sidebar .nav-link:hover,
    .dashboard-sidebar .nav-link.active {
        background: linear-gradient(90deg, #0b8a4a 0%, #0b5b33 100%);
        border-color: rgba(158, 247, 184, 0.18);
        color: #ffffff;
        box-shadow: inset 0 2px 0 rgba(0,0,0,0.12);
    }
    .dashboard-sidebar .nav-link:hover .sidebar-icon,
    .dashboard-sidebar .nav-link.active .sidebar-icon { color: #ffffff; }
    .dashboard-card { background: #0d261a; border: 1px solid rgba(16,184,129,0.14); }
    .dashboard-card .card-header { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .dashboard-card .btn-primary { background: #0f8a45; border-color: #0f8a45; }
    .dashboard-card .btn-primary:hover { background: #0b6e37; border-color: #0b6e37; }
    .dashboard-card .form-control, .dashboard-card .form-select { background: rgba(255,255,255,0.06); color: #e9fff4; border: 1px solid rgba(255,255,255,0.1); }
    .dashboard-card .form-control:focus, .dashboard-card .form-select:focus { background: rgba(255,255,255,0.08); color: #ffffff; border-color: #0f8a45; box-shadow: none; }
    .product-card { background: #092214; border: 1px solid rgba(16,184,129,0.14); }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 18px 30px rgba(0,0,0,0.25); }
    .product-card .badge { background: rgba(16,184,129,0.17); color: #b7ffcf; }
    .section-title { color: #d8ffe6; }
    .badge-status { background: #0f8a45; color: #fff; }
    .comment-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }
    .product-tag { color: #9ffdd3; }
    .text-muted { color: rgba(233,255,244,0.72) !important; }
    .customer-dashboard,
    .customer-dashboard .dashboard-shell,
    .customer-dashboard .dashboard-card,
    .customer-dashboard .product-card {
        color: #e9fff4;
    }
    .customer-dashboard h1,
    .customer-dashboard h2,
    .customer-dashboard h3,
    .customer-dashboard h4,
    .customer-dashboard h5,
    .customer-dashboard h6,
    .customer-dashboard label,
    .customer-dashboard .form-label,
    .customer-dashboard .card-header,
    .customer-dashboard .card-body,
    .customer-dashboard .form-check-label {
        color: #e9fff4 !important;
    }
    .customer-dashboard .text-muted,
    .customer-dashboard small.text-muted,
    .customer-dashboard .product-card p.text-muted {
        color: #b8d8c5 !important;
    }
    .customer-dashboard .product-tag,
    .customer-dashboard .section-title {
        color: #9ef7b8 !important;
    }
    .customer-dashboard a:not(.nav-link) {
        color: #9ef7b8;
    }
    .customer-dashboard a:not(.nav-link):hover {
        color: #c9ffd9;
    }
    #farmer-messages .card-body {
        color: #1f2937 !important;
    }
    #farmer-messages .card-body h5,
    #farmer-messages .card-body h6,
    #farmer-messages .card-body .form-label {
        color: #17251d !important;
    }
    #farmer-messages .card-body .text-muted,
    #farmer-messages .card-body small.text-muted {
        color: #52655a !important;
    }
    #farmer-messages .nav-tabs .nav-link {
        color: #52655a !important;
    }
    #farmer-messages .nav-tabs .nav-link.active {
        color: #087f5b !important;
        font-weight: 700;
    }
    #farmer-messages .form-control,
    #farmer-messages .form-select {
        background: #ffffff !important;
        color: #17251d !important;
    }
    #farmer-messages .form-control::placeholder {
        color: #68786e !important;
        opacity: 1;
    }
    #farmer-messages .customer-conversation-link {
        color: #17251d;
    }
    #farmer-messages .customer-conversation-link:hover {
        background: #eef8f2 !important;
        border-color: #78c99a !important;
    }
    .customer-dashboard .form-control,
    .customer-dashboard .form-select {
        color: #f2fff7 !important;
    }
    .customer-dashboard .form-control::placeholder {
        color: #a9c7b5 !important;
        opacity: 1;
    }
    .customer-dashboard .form-select option {
        background: #0d261a;
        color: #e9fff4;
    }
    .customer-dashboard .alert-secondary {
        background: rgba(158, 247, 184, 0.12);
        border-color: rgba(158, 247, 184, 0.22);
        color: #dfffe9 !important;
    }
    .customer-dashboard h1, .customer-dashboard h2, .customer-dashboard h3, .customer-dashboard h4, .customer-dashboard h5, .customer-dashboard h6 { font-family: 'Space Grotesk', 'Manrope', sans-serif; }
    .customer-dashboard .dashboard-shell { overflow: hidden; }
    .customer-dashboard .table { --bs-table-bg: transparent; --bs-table-color: #e9fff4; color: #e9fff4; border-color: rgba(158,247,184,.16); }
    .customer-dashboard .table thead th { color: #f3fff7; background: #123523; border-bottom-color: #2e9c61; white-space: nowrap; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; }
    .customer-dashboard .table tbody td { color: #e1f8eb; background: rgba(6,19,13,.28); vertical-align: middle; }
    .customer-dashboard .table tbody tr:hover td { color: #ffffff; background: rgba(15,138,69,.18); }
    .customer-dashboard .order-history-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .customer-dashboard #orderHistoryFilter { max-width: 280px; }
    .customer-dashboard #orderStatusFilter { min-width: 150px; }
    .customer-dashboard .order-history-table { min-width: 980px; }
    .customer-dashboard .order-history-table tbody tr { transition: opacity .2s ease, transform .2s ease, background .2s ease; }
    .customer-dashboard .order-history-table tbody tr.order-hidden { display: none; }
    .customer-dashboard .order-history-table tbody tr.order-reveal { animation: orderReveal .45s ease both; }
    @keyframes orderReveal { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .customer-dashboard .history-empty { color: #b8d8c5 !important; }
    /* Star Rating Styles */
    .star-rating { display: inline-flex; gap: 4px; align-items: center; }
    .star-rating .star { cursor: pointer; font-size: 1.2rem; color: #666; transition: color 0.2s, transform 0.2s; }
    .star-rating .star:hover, .star-rating .star.hover { color: #ffc107; transform: scale(1.15); }
    .star-rating .star.active { color: #ffc107; }
    .product-card .star-rating { margin: 10px 0; }
    .product-card .star-rating .star { font-size: 1rem; }
    @media (max-width: 991px) {
        .dashboard-sidebar { border-right: none; }
    }
</style>

<div class="container-fluid customer-dashboard dashboard-single-page">
    <div class="dashboard-shell row gx-4">
        <aside class="col-lg-3 p-4 dashboard-sidebar">
            <div class="mb-4 text-center">
                <h4 class="mb-1"><?php echo t('Hello'); ?>, <?php echo $customerName; ?></h4>
                <p class="sidebar-caption mb-0"><?php echo t('Customer dashboard'); ?></p>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link active" href="#overview"><span class="sidebar-icon"><i class="fas fa-chart-pie"></i></span><span class="sidebar-label"><?php echo t('Dashboard Overview'); ?></span></a>
                <a class="nav-link" href="#view-cart"><span class="sidebar-icon"><i class="fas fa-shopping-cart"></i></span><span class="sidebar-label"><?php echo t('View Cart'); ?></span></a>
                <a class="nav-link" href="#check-products"><span class="sidebar-icon"><i class="fas fa-box-open"></i></span><span class="sidebar-label"><?php echo t('Check Products'); ?></span></a>
                <a class="nav-link" href="#browse"><span class="sidebar-icon"><i class="fas fa-search"></i></span><span class="sidebar-label"><?php echo t('Browse Marketplace'); ?></span></a>
                <a class="nav-link" href="#place-order"><span class="sidebar-icon"><i class="fas fa-basket-shopping"></i></span><span class="sidebar-label"><?php echo t('Place Order'); ?></span></a>
                <a class="nav-link" href="#profile-update"><span class="sidebar-icon"><i class="fas fa-user-edit"></i></span><span class="sidebar-label"><?php echo t('Update Profile'); ?></span></a>
                <a class="nav-link" href="#farmer-messages"><span class="sidebar-icon"><i class="fas fa-comments"></i></span><span class="sidebar-label"><?php echo t('Messages'); ?></span></a>
                <a class="nav-link" href="#add-comments"><span class="sidebar-icon"><i class="fas fa-comments"></i></span><span class="sidebar-label"><?php echo t('Add Comments'); ?></span></a>
                 <a class="nav-link" href="#order-list"><span class="sidebar-icon"><i class="fas fa-history"></i></span><span class="sidebar-label"><?php echo t('Order History'); ?></span></a>
                 <a class="nav-link" href="#inquiry-history"><span class="sidebar-icon"><i class="fas fa-message"></i></span><span class="sidebar-label"><?php echo t('Inquiry History'); ?></span></a>
                 <a class="nav-link" href="logout.php"><span class="sidebar-icon"><i class="fas fa-right-from-bracket"></i></span><span class="sidebar-label"><?php echo t('Log Out'); ?></span></a>
            </nav>
        </aside>

        <main class="col-lg-9 p-4">
            <?php if ($message): ?>
                <div class="alert alert-success bg-success bg-opacity-10 border border-success text-white"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($cartMessage): ?><div class="alert alert-info bg-info bg-opacity-10 border border-info text-white"><i class="fas fa-cart-plus me-2"></i><?php echo htmlspecialchars($cartMessage); ?></div><?php endif; ?>

            <section id="overview" class="mb-4">
                <div class="dashboard-welcome dashboard-card mb-4">
                    <div><div class="welcome-kicker">Your market desk</div><h1>Fresh choices, close to home.</h1><p class="mb-0">Browse produce from local farmers, keep an eye on your orders, and get in touch whenever you need more information.</p></div>
                    <div class="welcome-mark"><i class="fas fa-seedling"></i><span>Local<br>produce</span></div>
                </div>
                <div class="quick-actions mb-4">
                    <a href="#browse" class="quick-action"><span class="quick-action-icon"><i class="fas fa-store"></i></span><span><strong>Browse marketplace</strong><small>Explore available products</small></span><i class="fas fa-arrow-right"></i></a>
                    <a href="#view-cart" class="quick-action"><span class="quick-action-icon"><i class="fas fa-shopping-basket"></i></span><span><strong>Open your cart</strong><small><?php echo number_format($cartCount); ?> item<?php echo $cartCount === 1 ? '' : 's'; ?> waiting</small></span><i class="fas fa-arrow-right"></i></a>
                    <a href="#inquiry-history" class="quick-action"><span class="quick-action-icon"><i class="fas fa-message"></i></span><span><strong>Track inquiries</strong><small>Review farmer responses</small></span><i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Available Products'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($productsCount); ?></h3>
                                <span class="product-tag"><?php echo t('Browse fresh farm items.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Your Orders'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($ordersCount); ?></h3>
                                <span class="product-tag"><?php echo t('Quick access to your order list.'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 p-3">
                            <div class="card-body">
                                <h6 class="section-title"><?php echo t('Comments Posted'); ?></h6>
                                <h3 class="mt-3"><?php echo number_format($commentsCount); ?></h3>
                                <span class="product-tag"><?php echo t('Share feedback with farmers.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card mt-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3"><div><div class="welcome-kicker">Market picks</div><h5 class="mb-0">Available today</h5></div><a href="#browse" class="btn btn-sm btn-outline-success">View all</a></div>
                    <div class="row g-3">
                        <?php if ($featuredProducts && $featuredProducts->num_rows > 0): while ($featured = $featuredProducts->fetch_assoc()): $featuredProductId = (int) $featured['product_id']; ?>
                            <div class="col-md-4">
                                <div class="featured-product">
                                    <div class="featured-product-icon"><i class="fas fa-leaf"></i></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($featured['product_name']); ?></strong>
                                        <small class="d-block text-muted">UGX <?php echo number_format((float) $featured['price'], 2); ?> / <?php echo htmlspecialchars($featured['product_unit']); ?></small>
                                        <small class="d-block text-success"><?php echo number_format((int) $featured['stock_quantity']); ?> available</small>
                                        <!-- Product Rating Display -->
                                        <?php if (isset($productRatings[$featuredProductId])): ?>
                                            <small class="d-block text-warning mt-2">
                                                <?php echo str_repeat('&#9733;', (int) round($productRatings[$featuredProductId]['average_rating'])); ?>
                                                <span class="text-muted"><?php echo number_format((float) $productRatings[$featuredProductId]['average_rating'], 1); ?>/5 (<?php echo (int) $productRatings[$featuredProductId]['review_count']; ?>)</span>
                                            </small>
                                        <?php else: ?>
                                            <small class="d-block text-muted mt-2">No ratings yet</small>
                                        <?php endif; ?>
                                        <!-- Star Rating Input -->
                                        <form method="POST" class="mt-2" style="display: inline;">
                                            <input type="hidden" name="rating_product_id" value="<?php echo $featuredProductId; ?>">
                                            <div class="star-rating" data-product-id="<?php echo $featuredProductId; ?>">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star" data-value="<?php echo $i; ?>" style="cursor: pointer; font-size: 0.9rem;">★</span>
                                                <?php endfor; ?>
                                            </div>
                                            <input type="hidden" name="rating_value" class="rating-value" value="0">
                                            <button type="submit" name="submit_product_rating" style="display: none;"></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; else: ?><div class="col-12"><p class="text-muted mb-0">New market products will appear here soon.</p></div><?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="view-cart" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Your Cart'); ?> <span class="badge bg-success ms-2"><?php echo number_format($cartCount); ?></span></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($cartCount === 0): ?>
                            <div class="alert alert-secondary text-white"><?php echo t('Your cart is empty. Start browsing products to add items.'); ?></div>
                        <?php else: ?>
                            <?php while ($order = $cartResult->fetch_assoc()): ?>
                                <div class="d-flex align-items-center justify-content-between mb-3 comment-box p-3 rounded">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($order['or_name']); ?></h6>
                                        <div class="text-muted"><?php echo t('Quantity'); ?>: <?php echo number_format((int) ($order['quantity'] ?? 1)); ?> <?php echo htmlspecialchars($order['order_unit'] ?? 'kilogram'); ?> • <?php echo t('Category'); ?>: <?php echo htmlspecialchars($order['or_category']); ?> • <?php echo t('Ordered on'); ?> <?php echo htmlspecialchars(date('M j, Y', strtotime($order['join_date']))); ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="h6 mb-1 text-success">UGX <?php echo number_format($order['price'], 2); ?> <?php echo t('total'); ?></div>
                                        <span class="badge badge-status"><?php echo t($order['status'] == 1 ? 'Active' : ($order['status'] == 2 ? 'Pending' : 'Inactive')); ?></span>
                                        <form method="post" class="cart-quantity-form mt-2"><input type="hidden" name="cart_order_id" value="<?php echo (int) $order['or_id']; ?>"><input type="hidden" name="cancel_order_id" value="<?php echo (int) $order['or_id']; ?>"><div class="input-group input-group-sm"><input type="number" name="quantity" value="<?php echo (int) ($order['quantity'] ?? 1); ?>" min="1" class="form-control cart-quantity-input" aria-label="Quantity for <?php echo htmlspecialchars($order['or_name']); ?>"><button type="submit" name="update_cart_quantity" class="btn btn-outline-info">Update</button></div><div class="d-flex gap-1 mt-1"><a href="payment.php?order_id=<?php echo (int) $order['or_id']; ?>" class="btn btn-sm btn-success"><i class="fas fa-credit-card me-1"></i>Checkout</a><button type="submit" name="cancel_order" value="1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this order?');">Cancel</button></div></form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <?php if ($cartTotal > 0): ?><div class="cart-checkout-bar"><div><span class="text-muted d-block">Cart total</span><strong>UGX <?php echo number_format($cartTotal, 2); ?></strong></div><a href="payment.php?cart=1" class="btn btn-success"><i class="fas fa-credit-card me-2"></i>Checkout cart</a></div><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="order-list" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header order-history-toolbar"><h5 class="mb-0"><i class="fas fa-history me-2"></i><?php echo t('Order History'); ?></h5><div class="d-flex align-items-center gap-2"><input type="search" id="orderHistoryFilter" class="form-control form-control-sm" placeholder="Search your orders" aria-label="Search your orders"><select id="orderStatusFilter" class="form-select form-select-sm" aria-label="Filter orders by status"><option value="all">All statuses</option><option value="pending">Pending</option><option value="active">Active</option><option value="fulfilled">Fulfilled</option><option value="cancelled">Cancelled</option></select><span class="badge bg-primary"><?php echo number_format($ordersCount); ?></span></div></div>
                    <div class="card-body"><?php if ($orderActionMessage): ?><div class="alert alert-info"><?php echo htmlspecialchars($orderActionMessage); ?></div><?php endif; ?><div class="table-responsive"><table class="table table-hover align-middle mb-0 order-history-table"><thead><tr><th><?php echo t('Order'); ?></th><th><?php echo t('Product'); ?></th><th><?php echo t('Quantity'); ?></th><th><?php echo t('Total'); ?></th><th><?php echo t('Delivery Location'); ?></th><th><?php echo t('Payment'); ?></th><th><?php echo t('Status'); ?></th><th><?php echo t('Date'); ?></th></tr></thead><tbody>
                    <?php if ($orderListResult && $orderListResult->num_rows > 0): while ($order = $orderListResult->fetch_assoc()): $orderTotal = (float) ($order['total_amount'] ?? 0); if ($orderTotal <= 0) { $orderTotal = (float) $order['price'] + (float) ($order['tax_amount'] ?? 0); } $orderStatus = ['Pending', 'Active', 'Fulfilled', 'Cancelled'][(int) $order['status']] ?? 'Pending'; $statusClass = ['warning', 'info', 'success', 'danger'][(int) $order['status']] ?? 'secondary'; ?>
                        <tr data-order-row data-order-status="<?php echo strtolower($orderStatus); ?>"><td>#<?php echo (int) $order['or_id']; ?></td><td><strong><?php echo htmlspecialchars($order['or_name']); ?></strong><small class="d-block text-muted"><?php echo htmlspecialchars($order['cat_name'] ?: t('Uncategorized')); ?></small></td><td><?php echo number_format((int) $order['quantity']); ?> <?php echo htmlspecialchars($order['order_unit'] ?: 'unit'); ?></td><td>UGX <?php echo number_format($orderTotal, 2); ?><small class="d-block text-muted">Tax: UGX <?php echo number_format((float) ($order['tax_amount'] ?? 0), 2); ?></small></td><td><?php echo nl2br(htmlspecialchars($order['delivery_location'] ?: t('Not provided'))); ?><?php if (!empty($order['delivery_update'])): ?><small class="d-block text-info"><?php echo nl2br(htmlspecialchars($order['delivery_update'])); ?></small><?php endif; ?></td><td><span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning text-dark'; ?>"><?php echo htmlspecialchars(ucfirst($order['payment_status'] ?: 'unpaid')); ?></span></td><td><span class="badge bg-<?php echo $statusClass; ?><?php echo $statusClass === 'warning' ? ' text-dark' : ''; ?>"><?php echo $orderStatus; ?></span></td><td class="text-nowrap"><small><?php echo htmlspecialchars(date('M j, Y', strtotime($order['join_date']))); ?></small></td></tr>
                    <?php endwhile; else: ?><tr><td colspan="9" class="text-center text-muted py-4"><?php echo t('No orders yet.'); ?></td></tr><?php endif; ?>
                    </tbody></table></div></div>
                </div>
            </section>

            <section id="check-products" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Check Available Products'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Search Products'); ?></label>
                                <input id="searchCheckProducts" type="text" class="form-control" placeholder="<?php echo t('Search product name...'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Filter by Category'); ?></label>
                                <select id="categoryCheckFilter" class="form-select">
                                    <option value="all"><?php echo t('All Categories'); ?></option>
                                    <?php $categories->data_seek(0); while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['cat_name']); ?>"><?php echo htmlspecialchars($category['cat_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row gy-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Min Price (UGX)'); ?></label>
                                <input id="minPriceCheck" type="number" class="form-control" placeholder="Min price" value="0" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Max Price (UGX)'); ?></label>
                                <input id="maxPriceCheck" type="number" class="form-control" placeholder="Max price" value="1000000" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Availability'); ?></label>
                                <select id="availabilityCheckFilter" class="form-select">
                                    <option value="all"><?php echo t('All Products'); ?></option>
                                    <option value="in-stock"><?php echo t('In Stock'); ?></option>
                                    <option value="low-stock"><?php echo t('Low Stock'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row gy-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label"><?php echo t('Filter by Location'); ?></label>
                                <input id="locationCheckFilter" type="text" class="form-control" placeholder="<?php echo t('Search by location, address, or market name...'); ?>">
                            </div>
                        </div>
                        <div class="row gy-3 mb-3">
                            <div class="col-md-12">
                                <button type="button" id="resetCheckFilters" class="btn btn-outline-success w-100">
                                    <i class="fas fa-rotate-left me-2"></i><?php echo t('Reset Filters'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3" id="product-list">
                            <?php if ($productsCount === 0): ?>
                                <div class="alert alert-secondary text-white"><?php echo t('No products are available at the moment.'); ?></div>
                            <?php else: ?>
                                <?php
                                $productsResult->data_seek(0);
                                while ($product = $productsResult->fetch_assoc()): 
                                  $displayLocation = !empty($product['farm_location']) ? htmlspecialchars($product['farm_location']) : (!empty($product['farm_address']) ? htmlspecialchars($product['farm_address']) : '');
                                  $displayMarket = !empty($product['market_location']) ? htmlspecialchars($product['market_location']) : (!empty($product['market_name']) ? htmlspecialchars($product['market_name']) : '');
                                  $locationLowerCheck = strtolower(trim($displayLocation . ' ' . $displayMarket));
                                  $sellerEmailKey = strtolower(trim((string) ($product['seller_email'] ?? '')));
                                  $farmerFeedback = isset($farmerRatings[$sellerEmailKey]) ? $farmerRatings[$sellerEmailKey] : null;
                                  $mapCenter = '';
                                  if (!empty($product['farm_map_latitude']) && !empty($product['farm_map_longitude'])) {
                                      $mapCenter = (float) $product['farm_map_latitude'] . ',' . (float) $product['farm_map_longitude'];
                                  } elseif (!empty($product['market_map_latitude']) && !empty($product['market_map_longitude'])) {
                                      $mapCenter = (float) $product['market_map_latitude'] . ',' . (float) $product['market_map_longitude'];
                                  } elseif (!empty($displayLocation)) {
                                      $mapCenter = $displayLocation . ' Ntungamo Uganda';
                                  } elseif (!empty($displayMarket)) {
                                      $mapCenter = $displayMarket . ' Ntungamo Uganda';
                                  }
                                ?>
                                    <div class="col-md-6 check-product-card" data-name="<?php echo strtolower(htmlspecialchars($product['product_name'])); ?>" data-category="<?php echo strtolower(htmlspecialchars($product['cat_name'] ?? '')); ?>" data-price="<?php echo (float) $product['price']; ?>" data-stock="<?php echo (int) $product['stock_quantity']; ?>" data-location="<?php echo $locationLowerCheck; ?>" data-market="<?php echo strtolower($displayMarket); ?>">
                                        <div class="card product-card h-100 p-3">
                                            <?php if (!empty($mapCenter)): ?>
                                                <div class="mb-3">
                                                    <div class="small text-uppercase text-success mb-1">Location map</div>
                                                    <iframe class="w-100 rounded border-0" height="110" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?php echo rawurlencode($mapCenter); ?>&output=embed"></iframe>
                                                </div>
                                            <?php endif; ?>
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="text-white"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($product['description'] ?: t('Fresh farm produce')); ?></p>
                                                    <small class="text-muted d-block mb-2"><?php echo t('Available'); ?>: <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></small>
                                                    <?php if (!empty($displayLocation)): ?><small class="text-info d-block mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo $displayLocation; ?></small><?php endif; ?>
                                                    <?php echo (int) $product['stock_quantity'] > 0 ? '<span class="badge bg-success mb-2">' . t('In stock') . '</span>' : '<span class="badge bg-danger mb-2">' . t('Out of stock') . '</span>'; ?>
                                                    <?php if (!empty($product['is_negotiable'])): ?><span class="badge bg-warning text-dark mb-2"><?php echo t('Price is negotiable'); ?></span><?php endif; ?>
                                                    <?php if (!empty($product['harvest_date'])): ?><small class="text-muted d-block mb-2"><?php echo t('Harvest date'); ?>: <?php echo htmlspecialchars(date('d M Y', strtotime($product['harvest_date']))); ?></small><?php endif; ?>
                                                    <?php if (!empty($product['seasonal_availability'])): ?><small class="text-muted d-block mb-2"><?php echo t('Season'); ?>: <?php echo htmlspecialchars($product['seasonal_availability']); ?></small><?php endif; ?>
                                                    <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($product['cat_name'] ?: t('Uncategorized')); ?></small>
                                                    <!-- Product Rating Display -->
                                                    <?php if (isset($productRatings[(int) $product['product_id']])): ?><small class="d-block text-warning mb-2"><?php echo str_repeat('&#9733;', (int) round($productRatings[(int) $product['product_id']]['average_rating'])); ?> <span class="text-muted"><?php echo number_format((float) $productRatings[(int) $product['product_id']]['average_rating'], 1); ?>/5 (<?php echo (int) $productRatings[(int) $product['product_id']]['review_count']; ?>)</span></small><?php else: ?><small class="d-block text-muted mb-2">No ratings yet</small><?php endif; ?>
                                                    <!-- Star Rating Input -->
                                                    <form method="POST" class="mb-2" style="display: inline;">
                                                        <input type="hidden" name="rating_product_id" value="<?php echo (int) $product['product_id']; ?>">
                                                        <div class="star-rating" data-product-id="<?php echo (int) $product['product_id']; ?>">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <span class="star" data-value="<?php echo $i; ?>" style="cursor: pointer;">★</span>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <input type="hidden" name="rating_value" class="rating-value" value="0">
                                                        <button type="submit" name="submit_product_rating" class="btn btn-xs btn-link" style="padding: 0; font-size: 0.75rem; color: #9ef7b8; display: none;">Rate</button>
                                                    </form>
                                                    <?php if ($farmerFeedback): ?><small class="d-block text-warning mb-2"><?php echo str_repeat('&#9733;', (int) round($farmerFeedback['average_rating'])); ?> <span class="text-muted"><?php echo number_format((float) $farmerFeedback['average_rating'], 1); ?>/5 (<?php echo (int) $farmerFeedback['review_count']; ?>)</span></small><?php else: ?><small class="d-block text-muted mb-2">Farmer rating not yet available</small><?php endif; ?>
                                                    <div class="badge mb-2"><?php echo htmlspecialchars($product['farmer_name'] ?: t('Local Farm Market')); ?></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <span class="text-success h6">UGX <?php echo number_format($product['price'], 2); ?> per <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></span>
                                                    <div class="d-flex gap-2">
                                                        <?php if (filter_var($product['seller_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                                            <a href="mailto:<?php echo htmlspecialchars($product['seller_email']); ?>?subject=<?php echo rawurlencode('Question about ' . $product['product_name']); ?>&body=<?php echo rawurlencode('Hello, I have a question about your product: ' . $product['product_name']); ?>" class="btn btn-sm btn-outline-success"><?php echo t('Contact Farmer'); ?></a>
                                                        <?php } ?>
                                                        <a href="#product-inquiry" data-inquiry-product="<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-outline-info"><?php echo t('Ask'); ?></a>
                                                        <a href="customerDashboard.php?order_product=<?php echo (int) $product['product_id']; ?>#place-order" class="btn btn-sm btn-primary" role="button" <?php echo (int) $product['stock_quantity'] < 1 ? 'aria-disabled="true" style="pointer-events:none;opacity:.5"' : ''; ?>><i class="fas fa-cart-plus me-1"></i><?php echo t('Add to Cart'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="browse" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Browse Marketplace'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Search Products'); ?></label>
                                <input id="searchProducts" type="text" class="form-control" placeholder="<?php echo t('Search product name...'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo t('Filter by Category'); ?></label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="all"><?php echo t('All Categories'); ?></option>
                                    <?php $categories->data_seek(0); while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['cat_name']); ?>"><?php echo htmlspecialchars($category['cat_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row gy-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Min Price (UGX)'); ?></label>
                                <input id="minPriceBrowse" type="number" class="form-control" placeholder="Min price" value="0" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Max Price (UGX)'); ?></label>
                                <input id="maxPriceBrowse" type="number" class="form-control" placeholder="Max price" value="1000000" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?php echo t('Availability'); ?></label>
                                <select id="availabilityFilter" class="form-select">
                                    <option value="all"><?php echo t('All Products'); ?></option>
                                    <option value="in-stock"><?php echo t('In Stock'); ?></option>
                                    <option value="low-stock"><?php echo t('Low Stock'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row gy-3 mt-1">
                            <div class="col-md-12">
                                <label class="form-label"><?php echo t('Filter by Location'); ?></label>
                                <input id="locationFilter" type="text" class="form-control" placeholder="<?php echo t('Search by location, address, or market name...'); ?>">
                            </div>
                        </div>
                        <div class="row gy-3 mt-1">
                            <div class="col-md-12">
                                <button type="button" id="resetBrowseFilters" class="btn btn-outline-success w-100">
                                    <i class="fas fa-rotate-left me-2"></i><?php echo t('Reset Filters'); ?>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 mt-3" id="browse-results">
                            <?php $productsResult->data_seek(0); while ($product = $productsResult->fetch_assoc()): $location = !empty($product['farm_address']) ? htmlspecialchars($product['farm_address']) : (!empty($product['market_address']) ? htmlspecialchars($product['market_address']) : ''); $locationLower = strtolower(trim($location)); $sellerEmailKey = strtolower(trim((string) ($product['seller_email'] ?? ''))); $farmerFeedback = isset($farmerRatings[$sellerEmailKey]) ? $farmerRatings[$sellerEmailKey] : null; $browseMapCenter = ''; if (!empty($product['farm_map_latitude']) && !empty($product['farm_map_longitude'])) { $browseMapCenter = (float) $product['farm_map_latitude'] . ',' . (float) $product['farm_map_longitude']; } elseif (!empty($product['market_map_latitude']) && !empty($product['market_map_longitude'])) { $browseMapCenter = (float) $product['market_map_latitude'] . ',' . (float) $product['market_map_longitude']; } elseif (!empty($product['farm_location'])) { $browseMapCenter = $product['farm_location'] . ' Ntungamo Uganda'; } elseif (!empty($product['market_location'])) { $browseMapCenter = $product['market_location'] . ' Ntungamo Uganda'; } ?>
                                <div class="col-md-6 browse-card" data-name="<?php echo strtolower(htmlspecialchars($product['product_name'])); ?>" data-category="<?php echo strtolower(htmlspecialchars($product['cat_name'] ?? '')); ?>" data-price="<?php echo (float) $product['price']; ?>" data-stock="<?php echo (int) $product['stock_quantity']; ?>" data-location="<?php echo $locationLower; ?>" data-market="<?php echo strtolower(htmlspecialchars($product['market_name'] ?? '')); ?>">
                                    <div class="card product-card p-3">
                                        <?php if (!empty($product['image']) && is_file(__DIR__ . '/admin/assets/images/products/' . basename($product['image']))): ?><img src="admin/assets/images/products/<?php echo rawurlencode(basename($product['image'])); ?>" class="img-fluid rounded mb-3" style="height: 170px; width: 100%; object-fit: cover;" alt="<?php echo htmlspecialchars($product['product_name']); ?>"><?php endif; ?>
                                        <?php if (!empty($browseMapCenter)): ?>
                                            <div class="mb-3">
                                                <div class="small text-uppercase text-success mb-1">Location map</div>
                                                <iframe class="w-100 rounded border-0" height="110" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?php echo rawurlencode($browseMapCenter); ?>&output=embed"></iframe>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="text-white"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                            <p class="text-muted mb-2"><?php echo htmlspecialchars($product['description'] ?: t('Fresh farm produce')); ?></p>
                                            <small class="text-muted d-block mb-2"><?php echo t('Available'); ?>: <?php echo number_format((int) $product['stock_quantity']); ?> <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></small>
                                            <?php $displayLocation = !empty($product['farm_location']) ? htmlspecialchars($product['farm_location']) : (!empty($product['farm_address']) ? htmlspecialchars($product['farm_address']) : ''); ?>
                                            <?php if (!empty($displayLocation)): ?><small class="text-info d-block mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo $displayLocation; ?> <a href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode($displayLocation . ' Ntungamo Uganda'); ?>" target="_blank" rel="noopener" class="ms-1 text-success">Map</a></small><?php endif; ?>
                                            <?php echo (int) $product['stock_quantity'] > 0 ? '<span class="badge bg-success mb-2">' . t('In stock') . '</span>' : '<span class="badge bg-danger mb-2">' . t('Out of stock') . '</span>'; ?>
                                            <?php if (!empty($product['is_negotiable'])): ?><span class="badge bg-warning text-dark mb-2"><?php echo t('Price is negotiable'); ?></span><?php endif; ?>
                                            <?php if (!empty($product['harvest_date'])): ?><small class="text-muted d-block mb-2"><?php echo t('Harvest date'); ?>: <?php echo htmlspecialchars(date('d M Y', strtotime($product['harvest_date']))); ?></small><?php endif; ?>
                                            <?php if (!empty($product['seasonal_availability'])): ?><small class="text-muted d-block mb-2"><?php echo t('Season'); ?>: <?php echo htmlspecialchars($product['seasonal_availability']); ?></small><?php endif; ?>
                                            <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($product['cat_name'] ?: t('Uncategorized')); ?></small>
                                            <?php $displayMarket = !empty($product['market_location']) ? htmlspecialchars($product['market_location']) : (!empty($product['market_name']) ? htmlspecialchars($product['market_name']) : ''); ?>
                                            <?php if (!empty($displayMarket)): ?><div class="small text-light mb-2"><i class="fas fa-store me-1 text-primary"></i><?php echo $displayMarket; ?> <a href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode($displayMarket . ' Ntungamo Uganda'); ?>" target="_blank" rel="noopener" class="ms-1 text-success">Map</a></div><?php endif; ?>
                                            <?php if (!empty($product['market_operating_days']) || !empty($product['market_hours'])): ?><div class="small text-light mb-2"><i class="fas fa-clock me-1 text-warning"></i><?php echo htmlspecialchars(trim(($product['market_operating_days'] ?: '') . ' ' . ($product['market_hours'] ?: ''))); ?></div><?php endif; ?>
                                            <?php if (!empty($product['pickup_instructions']) || !empty($product['delivery_instructions'])): ?><div class="small text-muted mb-2"><i class="fas fa-truck me-1 text-success"></i><?php echo htmlspecialchars($product['pickup_instructions'] ?: $product['delivery_instructions']); ?></div><?php endif; ?>
                                            <?php if (isset($productRatings[(int) $product['product_id']])): ?><small class="d-block text-warning mb-2"><?php echo str_repeat('&#9733;', (int) round($productRatings[(int) $product['product_id']]['average_rating'])); ?> <span class="text-muted"><?php echo number_format((float) $productRatings[(int) $product['product_id']]['average_rating'], 1); ?>/5 (<?php echo (int) $productRatings[(int) $product['product_id']]['review_count']; ?>)</span></small><?php else: ?><small class="d-block text-muted mb-2">No reviews yet</small><?php endif; ?>
                                            <?php if ($farmerFeedback): ?><small class="d-block text-info mb-2"><?php echo str_repeat('&#9733;', (int) round($farmerFeedback['average_rating'])); ?> <span class="text-muted"><?php echo number_format((float) $farmerFeedback['average_rating'], 1); ?>/5 farmer reputation (<?php echo (int) $farmerFeedback['review_count']; ?>)</span></small><?php endif; ?>
                                            <!-- Star Rating Input -->
                                            <form method="POST" class="mb-2" style="display: inline;">
                                                <input type="hidden" name="rating_product_id" value="<?php echo (int) $product['product_id']; ?>">
                                                <div class="star-rating" data-product-id="<?php echo (int) $product['product_id']; ?>">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <span class="star" data-value="<?php echo $i; ?>" style="cursor: pointer;">★</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <input type="hidden" name="rating_value" class="rating-value" value="0">
                                                <button type="submit" name="submit_product_rating" class="btn btn-xs btn-link" style="padding: 0; font-size: 0.75rem; color: #9ef7b8; display: none;">Rate</button>
                                            </form>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-success">UGX <?php echo number_format($product['price'], 2); ?> per <?php echo htmlspecialchars($product['product_unit'] ?? 'kilogram'); ?></span>
                                                <div class="d-flex gap-2">
                                                    <?php if (filter_var($product['seller_email'], FILTER_VALIDATE_EMAIL)) { ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($product['seller_email']); ?>?subject=<?php echo rawurlencode('Question about ' . $product['product_name']); ?>&body=<?php echo rawurlencode('Hello, I have a question about your product: ' . $product['product_name']); ?>" class="btn btn-sm btn-outline-success"><?php echo t('Contact Farmer'); ?></a>
                                                    <?php } ?>
                                                    <a href="#product-inquiry" data-inquiry-product="<?php echo (int) $product['product_id']; ?>" class="btn btn-sm btn-outline-info"><?php echo t('Ask'); ?></a>
                                                    <a href="customerDashboard.php?order_product=<?php echo (int) $product['product_id']; ?>#place-order" class="btn btn-sm btn-primary" role="button" <?php echo (int) $product['stock_quantity'] < 1 ? 'aria-disabled="true" style="pointer-events:none;opacity:.5"' : ''; ?>><i class="fas fa-cart-plus me-1"></i><?php echo t('Add to Cart'); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section id="place-order" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-basket-shopping me-2"></i>Place an order</h5></div>
                    <div class="card-body">
                        <?php if ($checkoutProduct): ?>
                            <?php if ($checkoutError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($checkoutError); ?></div><?php endif; ?>
                            <form method="post" id="dashboardOrderForm" data-price="<?php echo (float) $checkoutProduct['price']; ?>" data-tax-rules="<?php echo htmlspecialchars(json_encode($checkoutTaxRules), ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="product_id" value="<?php echo (int) $checkoutProduct['product_id']; ?>">
                                <div class="row g-3 align-items-end"><div class="col-lg-4"><label class="form-label" for="dashboardOrderProduct">Product</label><input class="form-control" id="dashboardOrderProduct" value="<?php echo htmlspecialchars($checkoutProduct['product_name']); ?>" readonly></div><div class="col-lg-3"><label class="form-label" for="dashboardOrderQuantity">Quantity (<?php echo htmlspecialchars($checkoutProduct['product_unit']); ?>)</label><input class="form-control" id="dashboardOrderQuantity" name="quantity" type="number" min="1" max="<?php echo (int) $checkoutProduct['stock_quantity']; ?>" value="<?php echo max(1, (int) ($_POST['quantity'] ?? 1)); ?>" required></div><div class="col-lg-5"><div class="order-total-strip"><span>Cart total</span><strong>UGX <span id="dashboardOrderTotal"><?php echo number_format((float) $checkoutProduct['price'], 2); ?></span></strong></div></div><div class="col-md-6"><label class="form-label" for="dashboardDeliveryLocation">Delivery location <span class="text-danger fw-normal">required</span></label><select class="form-select" id="dashboardDeliveryLocation" name="delivery_location" required><option value="">-- Select your delivery area --</option><?php $deliveryLocations = get_all_locations(); foreach ($deliveryLocations as $deliveryLocation): $selected = (!empty($_POST['delivery_location']) && $_POST['delivery_location'] == $deliveryLocation['location_name']) ? 'selected' : ''; ?><option value="<?php echo htmlspecialchars($deliveryLocation['location_name']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($deliveryLocation['location_name']); ?></option><?php endforeach; ?></select><small class="text-muted d-block mt-2">Please confirm the delivery area before you continue.</small></div><div class="col-md-6"><label class="form-label" for="dashboardDeliveryNotes">Delivery notes <span class="text-muted fw-normal">(optional)</span></label><textarea class="form-control" id="dashboardDeliveryNotes" name="delivery_notes" rows="3" placeholder="Landmark or handling instructions"><?php echo htmlspecialchars($_POST['delivery_notes'] ?? ''); ?></textarea></div><div class="col-12"><div class="small text-muted mb-3">Subtotal: UGX <span id="dashboardOrderSubtotal"><?php echo number_format((float) $checkoutProduct['price'], 2); ?></span> · Tax: UGX <span id="dashboardOrderTax">0.00</span></div><button type="submit" name="add_to_cart" class="btn btn-success"><i class="fas fa-cart-plus me-2"></i>Add to cart</button><a href="customerDashboard.php#order-list" class="btn btn-outline-light ms-2">View cart</a></div></div>
                            </form>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const orderForm = document.getElementById('dashboardOrderForm');
                                    const deliveryField = document.getElementById('dashboardDeliveryLocation');
                                    if (!orderForm || !deliveryField) return;
                                    orderForm.addEventListener('submit', function (event) {
                                        if (!deliveryField.value.trim()) {
                                            event.preventDefault();
                                            deliveryField.focus();
                                            deliveryField.setCustomValidity('Please select a delivery location before adding this order.');
                                            deliveryField.reportValidity();
                                        } else {
                                            deliveryField.setCustomValidity('');
                                        }
                                    });
                                });
                            </script>
                        <?php else: ?><div class="order-prompt"><i class="fas fa-cart-plus"></i><div><strong>Select a product from the marketplace</strong><p class="text-muted mb-0">Choose Order or Add to Cart on any available product to open checkout here.</p></div><a href="#browse" class="btn btn-outline-success">Browse products</a></div><?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="profile-update" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Update profile</h5></div>
                    <div class="card-body">
                        <?php if ($profileUpdateError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($profileUpdateError); ?></div><?php endif; ?>
                        <?php if ($profileUpdateSuccess): ?><div class="alert alert-success"><?php echo htmlspecialchars($profileUpdateSuccess); ?></div><?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="profileName">Full name</label>
                                    <input class="form-control" id="profileName" name="profile_name" type="text" value="<?php echo htmlspecialchars($customerName); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="profilePhone">Phone number</label>
                                    <input class="form-control" id="profilePhone" name="profile_phone" type="tel" value="<?php echo htmlspecialchars($customerPhone); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="profileAddress">Address</label>
                                    <textarea class="form-control" id="profileAddress" name="profile_address" rows="3"><?php echo htmlspecialchars($currentCustomer['user_address'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="profileDeliveryLocation">Delivery location</label>
                                    <select class="form-select" id="profileDeliveryLocation" name="profile_delivery_location" required>
                                        <option value="">-- Select your delivery area --</option>
                                        <?php $profileLocations = get_all_locations(); foreach ($profileLocations as $profileLocation): $selected = (!empty($currentCustomer['delivery_location']) && $currentCustomer['delivery_location'] == $profileLocation['location_name']) ? 'selected' : ''; ?>
                                            <option value="<?php echo htmlspecialchars($profileLocation['location_name']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($profileLocation['location_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="profilePassword">New password</label>
                                    <input class="form-control" id="profilePassword" name="profile_password" type="password" placeholder="Leave blank to keep current password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="profileConfirmPassword">Confirm new password</label>
                                    <input class="form-control" id="profileConfirmPassword" name="profile_confirm_password" type="password" placeholder="Confirm new password">
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="update_customer_profile" class="btn btn-success"><i class="fas fa-save me-2"></i>Save profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="product-reviews" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><i class="fas fa-star text-warning me-2"></i>Review a purchased product</h5></div>
                    <div class="card-body">
                        <?php if ($reviewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($reviewError); ?></div><?php endif; ?>
                        <?php if ($reviewableOrders && $reviewableOrders->num_rows > 0): ?>
                            <form method="post"><div class="row g-3"><div class="col-md-5"><label class="form-label" for="reviewOrder">Fulfilled order</label><select class="form-select" id="reviewOrder" name="review_order_id" required><?php while ($reviewOrder = $reviewableOrders->fetch_assoc()): ?><option value="<?php echo (int) $reviewOrder['or_id']; ?>" data-product="<?php echo (int) $reviewOrder['or_category']; ?>"><?php echo htmlspecialchars($reviewOrder['or_name']); ?> (#<?php echo (int) $reviewOrder['or_id']; ?>)</option><?php endwhile; ?></select><input type="hidden" name="review_product_id" id="reviewProductId"></div><div class="col-md-3"><label class="form-label" for="reviewRating">Rating</label><select class="form-select" name="rating" id="reviewRating" required><option value="">Choose stars</option><option value="5">5 - Excellent</option><option value="4">4 - Good</option><option value="3">3 - Average</option><option value="2">2 - Poor</option><option value="1">1 - Very poor</option></select></div><div class="col-12"><label class="form-label" for="reviewText">Review</label><textarea class="form-control" name="review_text" id="reviewText" rows="3" maxlength="2000" required></textarea></div><div class="col-12"><button type="submit" name="submit_review" class="btn btn-warning"><i class="fas fa-star me-2"></i>Submit Review</button></div></div></form>
                            <script>document.getElementById('reviewOrder').addEventListener('change', function () { document.getElementById('reviewProductId').value = this.options[this.selectedIndex].dataset.product; }); document.getElementById('reviewOrder').dispatchEvent(new Event('change'));</script>
                        <?php else: ?><p class="text-muted mb-0">Fulfilled orders will appear here when they are ready for review.</p><?php endif; ?>
                    </div>
                </div>
            </section>

            <?php if ($farmerNotifications && $farmerNotifications->num_rows > 0): ?>
            <section id="notifications" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="fas fa-bell me-2"></i>Farmer messages</h5><span class="badge bg-info"><?php echo $farmerNotifications->num_rows; ?></span></div>
                    <div class="card-body">
                        <?php while ($notification = $farmerNotifications->fetch_assoc()): ?><div class="border-start border-3 border-info ps-3 mb-3"><div class="d-flex justify-content-between gap-3"><strong><?php echo htmlspecialchars($notification['title']); ?></strong><small class="text-muted text-nowrap"><?php echo htmlspecialchars(date('M j, Y g:i a', strtotime($notification['created_at']))); ?></small></div><p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p></div><?php endwhile; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <section id="product-inquiry" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><?php echo t('Product Inquiry'); ?></h5></div>
                    <div class="card-body">
                        <?php if ($inquiryMessage): ?><div class="alert alert-success bg-success bg-opacity-10 border border-success text-white"><?php echo htmlspecialchars($inquiryMessage); ?></div><?php endif; ?>
                        <?php if ($inquiryError): ?><div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-white"><?php echo htmlspecialchars($inquiryError); ?></div><?php endif; ?>
                        <form method="post" id="inquiryForm">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label" for="inquiryProduct"><?php echo t('Product'); ?></label><select class="form-select" name="inquiry_product_id" id="inquiryProduct" required><option value=""><?php echo t('Select a product'); ?></option><?php $productsResult->data_seek(0); while ($inquiryProductOption = $productsResult->fetch_assoc()): ?><option value="<?php echo (int) $inquiryProductOption['product_id']; ?>" <?php echo ((int) ($_POST['inquiry_product_id'] ?? 0) === (int) $inquiryProductOption['product_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($inquiryProductOption['product_name']); ?></option><?php endwhile; ?></select></div>
                                <div class="col-md-8"><label class="form-label" for="inquirySubject"><?php echo t('Subject'); ?></label><input class="form-control" type="text" name="inquiry_subject" id="inquirySubject" value="<?php echo htmlspecialchars($_POST['inquiry_subject'] ?? ''); ?>" required></div>
                                <div class="col-12"><label class="form-label" for="inquiryMessage"><?php echo t('Your inquiry'); ?></label><textarea class="form-control" name="inquiry_message" id="inquiryMessage" rows="4" required><?php echo htmlspecialchars($_POST['inquiry_message'] ?? ''); ?></textarea></div>
                                <div class="col-12"><button type="submit" name="submit_inquiry" class="btn btn-success"><i class="fas fa-paper-plane me-2"></i><?php echo t('Send Inquiry'); ?></button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section id="inquiry-history" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header"><h5 class="mb-0"><?php echo t('Inquiry History'); ?></h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th><?php echo t('Product'); ?></th><th><?php echo t('Subject'); ?></th><th><?php echo t('Message'); ?></th><th><?php echo t('Status'); ?></th><th><?php echo t('Response'); ?></th><th><?php echo t('Date'); ?></th></tr></thead>
                                <tbody>
                                    <?php if ($inquiryHistory && $inquiryHistory->num_rows > 0): while ($inquiry = $inquiryHistory->fetch_assoc()): $inquiryStatus = (int) $inquiry['status']; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inquiry['product_name'] ?: t('Unavailable')); ?></td>
                                            <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></td>
                                            <td><span class="badge text-bg-<?php echo $inquiryClasses[$inquiryStatus] ?? 'secondary'; ?>"><?php echo htmlspecialchars($inquiryLabels[$inquiryStatus] ?? t('Pending')); ?></span></td>
                                            <td><?php echo nl2br(htmlspecialchars($inquiry['response'] ?: t('Awaiting response'))); ?></td>
                                            <td><small><?php echo htmlspecialchars(date('M j, Y', strtotime($inquiry['created_at']))); ?></small></td>
                                        </tr>
                                    <?php endwhile; else: ?><tr><td colspan="6" class="text-center text-muted py-4"><?php echo t('No inquiries yet.'); ?></td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="add-comments" class="mb-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo t('Add Comments'); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('Subject'); ?></label>
                                <input type="text" name="comment_subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('Comment'); ?></label>
                                <textarea name="comment_text" rows="5" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="btn btn-primary"><?php echo t('Submit Comment'); ?></button>
                        </form>
                        <?php if ($recentComments && $recentComments->num_rows > 0): ?>
                            <div class="mt-4">
                                <h6 class="section-title"><?php echo t('Recent Comments'); ?></h6>
                                <?php while ($comment = $recentComments->fetch_assoc()): ?>
                                    <div class="comment-box p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong><?php echo htmlspecialchars($comment['subject']); ?></strong>
                                            <span class="badge badge-status"><?php echo t($comment['status'] == 2 ? 'Pending' : 'Published'); ?></span>
                                        </div>
                                        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($comment['comments'])); ?></p>
                                        <small class="text-muted"><?php echo htmlspecialchars(date('M j, Y', strtotime($comment['cmt_date']))); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="farmer-messages" class="mb-4">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fa-solid fa-comments me-2"></i><?php echo t('Messages with Farmers'); ?></h5>
                            <span class="badge bg-info"><?php echo $unreadMessagesCount; ?> unread</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($messagingError): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($messagingError); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                        <?php if ($messagingSuccess): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($messagingSuccess); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                        <!-- Nav Tabs -->
                        <ul class="nav nav-tabs mb-4" role="tablist" style="border-bottom: 2px solid #e0e0e0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="conversations-tab" data-bs-toggle="tab" data-bs-target="#conversations-pane" type="button" role="tab" aria-controls="conversations-pane" aria-selected="true">
                                    <i class="fa-solid fa-inbox me-2"></i>Conversations <?php if ($allCustomerConversations && $allCustomerConversations->num_rows > 0): ?><span class="badge bg-info ms-1"><?php echo $allCustomerConversations->num_rows; ?></span><?php endif; ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="new-message-tab" data-bs-toggle="tab" data-bs-target="#new-message-pane" type="button" role="tab" aria-controls="new-message-pane" aria-selected="false">
                                    <i class="fa-solid fa-pen-to-square me-2"></i>Start New Conversation
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Existing Conversations Tab -->
                            <div class="tab-pane fade show active" id="conversations-pane" role="tabpanel" aria-labelledby="conversations-tab">
                                <div class="row" style="min-height: 500px;">
                                    <!-- Conversations List -->
                                    <div class="col-lg-4">
                                        <div style="background: #f9f9f9; border-radius: 8px; padding: 15px; max-height: 500px; overflow-y: auto;">
                                            <h6 class="mb-3"><i class="fa-solid fa-list me-2"></i>Your Conversations</h6>
                                            <?php if ($allCustomerConversations && $allCustomerConversations->num_rows > 0): 
                                                mysqli_data_seek($allCustomerConversations, 0); // Reset pointer
                                                while ($conv = $allCustomerConversations->fetch_assoc()): ?>
                                                    <button type="button" class="customer-conversation-link" data-conversation-id="<?php echo (int) $conv['conversation_id']; ?>" style="border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 10px; padding: 12px; text-decoration: none; display: block; background: <?php echo $selectedConversationId === (int) $conv['conversation_id'] ? '#e3f2fd' : '#fff'; ?>; transition: all 0.2s; width: 100%; text-align: left; cursor: pointer;">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <strong style="color: #087f5b;"><?php echo htmlspecialchars($conv['farmer_name'] ?: 'Unknown Farmer'); ?></strong>
                                                            <?php if ($conv['unread_count'] > 0): ?><span class="badge bg-danger"><?php echo (int) $conv['unread_count']; ?></span><?php endif; ?>
                                                        </div>
                                                        <small class="text-muted d-block text-truncate" style="color: #666;"><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages', 0, 60)); ?></small>
                                                        <small class="text-muted d-block mt-1" style="color: #999;"><?php echo $conv['last_message_date'] ? htmlspecialchars(date('M j, g:i A', strtotime($conv['last_message_date']))) : ''; ?></small>
                                                    </button>
                                                <?php endwhile; 
                                            else: ?>
                                                <div class="alert alert-info mb-0"><i class="fa-solid fa-info-circle me-2"></i>No conversations yet. Use the "Start New Conversation" tab to message a farmer!</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Messages Display -->
                                    <div class="col-lg-8">
                                        <?php if ($selectedCustomerConversation): ?>
                                            <div class="border rounded" style="background: #fff; display: flex; flex-direction: column; height: 500px; border: 1px solid #e0e0e0;">
                                                <!-- Chat Header -->
                                                <div style="padding: 15px; background: linear-gradient(135deg, #087f5b 0%, #05604a 100%); color: white; border-radius: 8px 8px 0 0;">
                                                    <h6 class="mb-0"><i class="fa-solid fa-user-circle me-2"></i><?php echo htmlspecialchars($selectedCustomerConversation['farmer_name'] ?: 'Farmer'); ?></h6>
                                                    <small style="opacity: 0.9;"><?php echo htmlspecialchars($selectedCustomerConversation['farmer_email'] ?: ''); ?></small>
                                                </div>

                                                <!-- Messages Area -->
                                                <div style="flex: 1; overflow-y: auto; padding: 20px; background: #fafafa; display: flex; flex-direction: column; gap: 12px;">
                                                    <?php if (count($selectedCustomerMessages) > 0): ?>
                                                        <?php foreach ($selectedCustomerMessages as $msg): $isSent = (int) $msg['sender_id'] === $customerId; ?>
                                                            <div class="d-flex <?php echo $isSent ? 'justify-content-end' : 'justify-content-start'; ?>">
                                                                <div style="max-width: 70%;">
                                                                    <div style="padding: 12px 16px; border-radius: 12px; background: <?php echo $isSent ? '#087f5b' : '#e9f5f0'; ?>; color: <?php echo $isSent ? '#fff' : '#000'; ?>; word-wrap: break-word; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
                                                                        <small style="opacity: 0.8; display: block;"><?php echo htmlspecialchars(date('M j, g:i A', strtotime($msg['created_at']))); ?></small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="text-center text-muted py-5"><i class="fa-solid fa-comments fs-1 d-block mb-2" style="opacity: 0.3;"></i><small>No messages yet. Start the conversation below!</small></div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Input Area -->
                                                <form method="POST" action="customerDashboard.php#farmer-messages" style="padding: 15px; background: #fff; border-top: 1px solid #e0e0e0; border-radius: 0 0 8px 8px;">
                                                    <input type="hidden" name="conversation_id" value="<?php echo (int) $selectedCustomerConversation['conversation_id']; ?>">
                                                    <div class="input-group">
                                                        <textarea name="message_text" placeholder="Type your message..." rows="2" maxlength="5000" class="form-control" required style="resize: none; border-radius: 6px 0 0 6px;"></textarea>
                                                        <button type="submit" name="send_customer_message" class="btn btn-success" style="border-radius: 0 6px 6px 0;"><i class="fa-solid fa-paper-plane"></i> Send</button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <div style="background: #f9f9f9; border-radius: 8px; padding: 60px 30px; text-align: center; border: 1px solid #e0e0e0; min-height: 500px; display: flex; align-items: center; justify-content: center;">
                                                <div>
                                                    <i class="fa-solid fa-message fs-1 d-block mb-3" style="color: #ccc;"></i>
                                                    <p class="text-muted mb-0"><strong>Select a conversation to view messages</strong></p>
                                                    <small class="text-muted d-block mt-2">or use the "Start New Conversation" tab to message a farmer</small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- New Conversation Tab -->
                            <div class="tab-pane fade" id="new-message-pane" role="tabpanel" aria-labelledby="new-message-tab">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8">
                                        <div style="background: linear-gradient(135deg, #f0faf8 0%, #e8f5f0 100%); border-radius: 12px; padding: 30px; border: 1px solid #d0e8e0;">
                                            <h5 class="mb-4"><i class="fa-solid fa-envelope-open-text me-2"></i>Start a New Conversation</h5>
                                            <p class="text-muted mb-4">Send a message to any farmer in the marketplace. They'll receive your message and can respond directly.</p>

                                            <form method="POST" action="customerDashboard.php#farmer-messages">
                                                <div class="mb-4">
                                                    <label class="form-label" for="farmer_id"><strong>Select a Farmer</strong></label>
                                                    <select class="form-select form-select-lg" id="farmer_id" name="farmer_id" required style="border: 2px solid #e0e0e0;">
                                                        <option value="">-- Choose a farmer --</option>
                                                        <?php if ($availableFarmersQuery && $availableFarmersQuery->num_rows > 0): 
                                                            while ($farmer = $availableFarmersQuery->fetch_assoc()): ?>
                                                                <option value="<?php echo (int) $farmer['user_id']; ?>">
                                                                    <?php echo htmlspecialchars($farmer['user_name']); ?> (<?php echo htmlspecialchars($farmer['user_email']); ?>)
                                                                </option>
                                                            <?php endwhile; 
                                                        endif; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-2"><i class="fa-solid fa-info-circle me-1"></i>Can't find a farmer? Browse products first to see farmers in the marketplace.</small>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label" for="first_message"><strong>Your Message</strong></label>
                                                    <textarea name="first_message" id="first_message" class="form-control" rows="4" placeholder="Hello! I'm interested in your products..." maxlength="5000" required style="border: 2px solid #e0e0e0; resize: vertical;"></textarea>
                                                    <small class="text-muted d-block mt-2">You can include questions about products, bulk orders, delivery, or anything else.</small>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button type="submit" name="start_new_conversation" value="1" class="btn btn-success btn-lg" style="border-radius: 8px; font-weight: 600;">
                                                        <i class="fa-solid fa-paper-plane me-2"></i>Send Message
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Info Cards -->
                                        <div class="row g-3 mt-4">
                                            <div class="col-md-4">
                                                <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; text-align: center;">
                                                    <i class="fa-solid fa-leaf" style="font-size: 2.5rem; color: #087f5b; margin-bottom: 10px; display: block;"></i>
                                                    <h6 class="mb-2">Direct Connection</h6>
                                                    <small class="text-muted">Chat directly with farmers</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; text-align: center;">
                                                    <i class="fa-solid fa-handshake" style="font-size: 2.5rem; color: #087f5b; margin-bottom: 10px; display: block;"></i>
                                                    <h6 class="mb-2">Negotiate Prices</h6>
                                                    <small class="text-muted">Discuss bulk orders & deals</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; text-align: center;">
                                                    <i class="fa-solid fa-truck" style="font-size: 2.5rem; color: #087f5b; margin-bottom: 10px; display: block;"></i>
                                                    <h6 class="mb-2">Arrange Delivery</h6>
                                                    <small class="text-muted">Coordinate pickup & delivery</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
    const orderHistoryFilter = document.getElementById('orderHistoryFilter');
    const orderStatusFilter = document.getElementById('orderStatusFilter');
    function filterOrderHistory() {
        const searchText = (orderHistoryFilter?.value || '').toLowerCase().trim();
        const status = orderStatusFilter?.value || 'all';
        document.querySelectorAll('#order-list [data-order-row]').forEach(function (row, index) {
            const matchesText = row.textContent.toLowerCase().includes(searchText);
            const matchesStatus = status === 'all' || row.dataset.orderStatus === status;
            row.classList.toggle('order-hidden', !(matchesText && matchesStatus));
            if (matchesText && matchesStatus) {
                row.classList.remove('order-reveal');
                window.setTimeout(() => row.classList.add('order-reveal'), index * 25);
            }
        });
    }
    orderHistoryFilter?.addEventListener('input', filterOrderHistory);
    orderStatusFilter?.addEventListener('change', filterOrderHistory);
    filterOrderHistory();
    function filterBrowse() {
        const text = document.getElementById('searchProducts').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value.toLowerCase();
        const minPrice = parseFloat(document.getElementById('minPriceBrowse').value) || 0;
        const maxPrice = parseFloat(document.getElementById('maxPriceBrowse').value) || Infinity;
        const availability = document.getElementById('availabilityFilter').value;
        const location = document.getElementById('locationFilter').value.toLowerCase();
        document.querySelectorAll('#browse-results .browse-card').forEach(card => {
            const name = card.dataset.name || '';
            const cat = card.dataset.category || '';
            const price = parseFloat(card.dataset.price) || 0;
            const stock = parseInt(card.dataset.stock) || 0;
            const cardLocation = card.dataset.location || '';
            const cardMarket = card.dataset.market || '';
            const matchesText = name.includes(text);
            const matchesCat = category === 'all' || cat === category;
            const matchesPrice = price >= minPrice && price <= maxPrice;
            const matchesAvailability = availability === 'all' || (availability === 'in-stock' && stock > 0) || (availability === 'low-stock' && stock > 0 && stock <= 5);
            const matchesLocation = location === '' || cardLocation.includes(location) || cardMarket.includes(location);
            const display = matchesText && matchesCat && matchesPrice && matchesAvailability && matchesLocation;
            card.style.display = display ? 'block' : 'none';
        });
    }
    document.getElementById('searchProducts').addEventListener('input', filterBrowse);
    document.getElementById('categoryFilter').addEventListener('change', filterBrowse);
    document.getElementById('minPriceBrowse').addEventListener('input', filterBrowse);
    document.getElementById('maxPriceBrowse').addEventListener('input', filterBrowse);
    document.getElementById('availabilityFilter').addEventListener('change', filterBrowse);
    document.getElementById('locationFilter').addEventListener('input', filterBrowse);
    document.getElementById('resetBrowseFilters').addEventListener('click', function() {
        document.getElementById('searchProducts').value = '';
        document.getElementById('categoryFilter').value = 'all';
        document.getElementById('minPriceBrowse').value = '0';
        document.getElementById('maxPriceBrowse').value = '1000000';
        document.getElementById('availabilityFilter').value = 'all';
        document.getElementById('locationFilter').value = '';
        filterBrowse();
    });
    function filterCheckProducts() {
        const text = document.getElementById('searchCheckProducts').value.toLowerCase();
        const category = document.getElementById('categoryCheckFilter').value.toLowerCase();
        const minPrice = parseFloat(document.getElementById('minPriceCheck').value) || 0;
        const maxPrice = parseFloat(document.getElementById('maxPriceCheck').value) || Infinity;
        const availability = document.getElementById('availabilityCheckFilter').value;
        const location = document.getElementById('locationCheckFilter').value.toLowerCase();
        document.querySelectorAll('#product-list .check-product-card').forEach(card => {
            const name = card.dataset.name || '';
            const cat = card.dataset.category || '';
            const price = parseFloat(card.dataset.price) || 0;
            const stock = parseInt(card.dataset.stock) || 0;
            const cardLocation = card.dataset.location || '';
            const cardMarket = card.dataset.market || '';
            const matchesText = name.includes(text);
            const matchesCat = category === 'all' || cat === category;
            const matchesPrice = price >= minPrice && price <= maxPrice;
            const matchesAvailability = availability === 'all' || (availability === 'in-stock' && stock > 0) || (availability === 'low-stock' && stock > 0 && stock <= 5);
            const matchesLocation = location === '' || cardLocation.includes(location) || cardMarket.includes(location);
            const display = matchesText && matchesCat && matchesPrice && matchesAvailability && matchesLocation;
            card.style.display = display ? 'block' : 'none';
        });
    }
    document.getElementById('searchCheckProducts').addEventListener('input', filterCheckProducts);
    document.getElementById('categoryCheckFilter').addEventListener('change', filterCheckProducts);
    document.getElementById('minPriceCheck').addEventListener('input', filterCheckProducts);
    document.getElementById('maxPriceCheck').addEventListener('input', filterCheckProducts);
    document.getElementById('availabilityCheckFilter').addEventListener('change', filterCheckProducts);
    document.getElementById('locationCheckFilter').addEventListener('input', filterCheckProducts);
    document.getElementById('resetCheckFilters').addEventListener('click', function() {
        document.getElementById('searchCheckProducts').value = '';
        document.getElementById('categoryCheckFilter').value = 'all';
        document.getElementById('minPriceCheck').value = '0';
        document.getElementById('maxPriceCheck').value = '1000000';
        document.getElementById('availabilityCheckFilter').value = 'all';
        document.getElementById('locationCheckFilter').value = '';
        filterCheckProducts();
    });
    document.querySelectorAll('[data-inquiry-product]').forEach(function (button) {
        button.addEventListener('click', function () {
            const productSelect = document.getElementById('inquiryProduct');
            const subject = document.getElementById('inquirySubject');
            if (productSelect && subject) {
                productSelect.value = button.dataset.inquiryProduct;
                subject.value = 'Question about ' + productSelect.options[productSelect.selectedIndex].text;
                subject.focus();
            }
        });
    });

    document.querySelectorAll('.customer-conversation-link').forEach(function (button) {
        button.addEventListener('click', function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'customerDashboard.php#farmer-messages';
            form.hidden = true;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'conversation_id';
            input.value = button.dataset.conversationId;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    });

    // Star Rating Functionality
    document.querySelectorAll('.star-rating').forEach(function (ratingContainer) {
        const stars = ratingContainer.querySelectorAll('.star');
        const ratingInput = ratingContainer.parentElement.querySelector('.rating-value');
        const submitBtn = ratingContainer.parentElement.querySelector('button[name="submit_product_rating"]');

        stars.forEach(star => {
            // Hover effect
            star.addEventListener('mouseover', function () {
                const value = this.dataset.value;
                stars.forEach((s, idx) => {
                    if (idx < value) {
                        s.classList.add('hover');
                    } else {
                        s.classList.remove('hover');
                    }
                });
            });

            // Click to rate
            star.addEventListener('click', function () {
                const value = this.dataset.value;
                ratingInput.value = value;
                stars.forEach((s, idx) => {
                    if (idx < value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
                // Auto-submit the form
                ratingContainer.parentElement.submit();
            });
        });

        // Remove hover effect when leaving the rating
        ratingContainer.addEventListener('mouseleave', function () {
            stars.forEach(s => s.classList.remove('hover'));
        });
    });
</script>

<?php include "inc/footer.php"; ?>
