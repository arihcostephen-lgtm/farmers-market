<?php
/**
 * Notifications Helper
 * Provides functions for creating and managing in-app notifications
 */

if (!function_exists('notify_manager')) {
    /**
     * Send a notification to a manager
     * @param int $manager_id - The manager's user ID
     * @param string $title - Notification title
     * @param string $message - Notification message
     * @param string $type - Notification type (general, user, order, payment, system)
     * @param string $entity_type - Related entity type (optional)
     * @param int $entity_id - Related entity ID (optional)
     * @param string $action_url - Action URL to open (optional)
     */
    function notify_manager($manager_id, $title, $message, $type = 'general', $entity_type = null, $entity_id = null, $action_url = null) {
        global $db;
        
        $manager_id = (int) $manager_id;
        $title = $db->real_escape_string($title);
        $message = $db->real_escape_string($message);
        $type = $db->real_escape_string($type);
        $entity_type = $entity_type ? $db->real_escape_string($entity_type) : null;
        $entity_id = $entity_id ? (int) $entity_id : null;
        $action_url = $action_url ? $db->real_escape_string($action_url) : null;
        
        $query = "INSERT INTO manager_notifications (manager_id, notification_type, title, message, related_entity_type, related_entity_id, action_url, is_read, created_at) 
                  VALUES ($manager_id, '$type', '$title', '$message', " . ($entity_type ? "'$entity_type'" : "NULL") . ", $entity_id, " . ($action_url ? "'$action_url'" : "NULL") . ", 0, NOW())";
        
        return $db->query($query);
    }
}

if (!function_exists('notify_all_managers')) {
    /**
     * Send a system notification to all managers
     * @param string $title - Notification title
     * @param string $message - Notification message
     * @param string $type - Notification type
     * @param string $action_url - Action URL (optional)
     */
    function notify_all_managers($title, $message, $type = 'system', $action_url = null) {
        global $db;
        
        // Get all active managers
        $managersResult = $db->query("SELECT user_id FROM users WHERE role = 4 AND status = 1");
        
        if ($managersResult && $managersResult->num_rows > 0) {
            while ($manager = $managersResult->fetch_assoc()) {
                notify_manager($manager['user_id'], $title, $message, $type, null, null, $action_url);
            }
            return true;
        }
        return false;
    }
}

if (!function_exists('mark_notification_read')) {
    /**
     * Mark a notification as read
     * @param int $notification_id - The notification ID
     * @param int $manager_id - The manager's user ID (for security)
     */
    function mark_notification_read($notification_id, $manager_id) {
        global $db;
        
        $notification_id = (int) $notification_id;
        $manager_id = (int) $manager_id;
        
        return $db->query("UPDATE manager_notifications SET is_read = 1, read_at = NOW() WHERE notification_id = $notification_id AND manager_id = $manager_id");
    }
}

if (!function_exists('get_manager_unread_count')) {
    /**
     * Get the unread notification count for a manager
     * @param int $manager_id - The manager's user ID
     */
    function get_manager_unread_count($manager_id) {
        global $db;
        
        $manager_id = (int) $manager_id;
        $result = $db->query("SELECT COUNT(*) as count FROM manager_notifications WHERE manager_id = $manager_id AND is_read = 0");
        
        if ($result) {
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        }
        return 0;
    }
}

if (!function_exists('create_message')) {
    /**
     * Create a message between customer and farmer
     * @param int $customer_id - Customer user ID
     * @param int $farmer_id - Farmer user ID
     * @param string $message_text - Message content
     * @param string $subject - Conversation subject (optional)
     * @param int $product_id - Related product ID (optional)
     */
    function create_message($customer_id, $farmer_id, $message_text, $subject = null, $product_id = null) {
        global $db;
        
        $customer_id = (int) $customer_id;
        $farmer_id = (int) $farmer_id;
        $message_text = $db->real_escape_string($message_text);
        $subject = $subject ? $db->real_escape_string($subject) : null;
        $product_id = $product_id ? (int) $product_id : null;
        
        // Check if conversation exists
        $convResult = $db->query("SELECT conversation_id FROM conversations WHERE customer_id = $customer_id AND farmer_id = $farmer_id LIMIT 1");
        
        if ($convResult && $convResult->num_rows > 0) {
            $conv = $convResult->fetch_assoc();
            $conversation_id = $conv['conversation_id'];
        } else {
            // Create new conversation
            $escapedSubject = $subject ? "'$subject'" : "NULL";
            $escapedProduct = $product_id ? $product_id : "NULL";
            $db->query("INSERT INTO conversations (customer_id, farmer_id, product_id, subject, created_at, updated_at) 
                       VALUES ($customer_id, $farmer_id, $escapedProduct, $escapedSubject, NOW(), NOW())");
            $conversation_id = $db->insert_id;
        }
        
        // Insert message
        $query = "INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) 
                  VALUES ($conversation_id, $customer_id, 'customer', '$message_text', NOW())";
        
        if ($db->query($query)) {
            // Update conversation updated_at
            $db->query("UPDATE conversations SET updated_at = NOW() WHERE conversation_id = $conversation_id");
            
            // Notify farmer
            notify_manager($farmer_id, 'New Customer Message', 
                          "You have a new message from a customer", 
                          'message', 
                          'conversation', 
                          $conversation_id,
                          "farmerMessages.php?conversation_id=$conversation_id");
            
            return true;
        }
        
        return false;
    }
}

if (!function_exists('get_unread_conversations')) {
    /**
     * Get count of conversations with unread messages for a user
     * @param int $user_id - User ID
     * @param string $user_type - User type (customer or farmer)
     */
    function get_unread_conversations($user_id, $user_type = 'customer') {
        global $db;
        
        $user_id = (int) $user_id;
        $user_type = $db->real_escape_string($user_type);
        
        if ($user_type === 'customer') {
            $query = "SELECT COUNT(DISTINCT c.conversation_id) as count FROM conversations c 
                     LEFT JOIN messages m ON m.conversation_id = c.conversation_id 
                     WHERE c.customer_id = $user_id AND m.is_read = 0 AND m.sender_id != $user_id";
        } else {
            $query = "SELECT COUNT(DISTINCT c.conversation_id) as count FROM conversations c 
                     LEFT JOIN messages m ON m.conversation_id = c.conversation_id 
                     WHERE c.farmer_id = $user_id AND m.is_read = 0 AND m.sender_id != $user_id";
        }
        
        $result = $db->query($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return (int) $row['count'];
        }
        return 0;
    }
}
?>
