-- Messaging and Notifications Tables
-- Add these tables to support in-app messaging and notifications

USE farmersmkt_db;

-- Conversations table (groups messages between customer and farmer)
CREATE TABLE IF NOT EXISTS conversations (
  conversation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  farmer_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED DEFAULT NULL,
  subject VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_conversation (customer_id, farmer_id),
  INDEX idx_conversation_customer (customer_id),
  INDEX idx_conversation_farmer (farmer_id),
  INDEX idx_conversation_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table (individual messages in conversations)
CREATE TABLE IF NOT EXISTS messages (
  message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  sender_type ENUM('customer', 'farmer') NOT NULL,
  message_text TEXT NOT NULL,
  attachment_path VARCHAR(255) DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME DEFAULT NULL,
  INDEX idx_messages_conversation (conversation_id),
  INDEX idx_messages_sender (sender_id),
  INDEX idx_messages_created (created_at),
  FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Manager notifications table
CREATE TABLE IF NOT EXISTS manager_notifications (
  notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  manager_id INT UNSIGNED NOT NULL,
  notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  related_entity_type VARCHAR(100) DEFAULT NULL,
  related_entity_id INT UNSIGNED DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  action_url VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME DEFAULT NULL,
  INDEX idx_manager_notifications_manager (manager_id, is_read),
  INDEX idx_manager_notifications_type (notification_type),
  INDEX idx_manager_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System notifications (for all admin/manager users)
CREATE TABLE IF NOT EXISTS system_notifications (
  notification_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  notification_type VARCHAR(50) NOT NULL DEFAULT 'general',
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  target_role TINYINT(1) NOT NULL DEFAULT 4,
  icon_class VARCHAR(100) DEFAULT 'fas fa-bell',
  action_url VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME DEFAULT NULL,
  INDEX idx_system_notifications_type (notification_type),
  INDEX idx_system_notifications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
