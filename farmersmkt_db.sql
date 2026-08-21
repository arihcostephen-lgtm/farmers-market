-- Local Farm Market Database Schema
-- Import this file into MySQL/MariaDB using:
--   mysql -u root -p < farmersmkt_db.sql
--
-- Requirements:
--   - MySQL 5.7+ or MariaDB 10.4+
--   - A database named farmersmkt_db
--   - UTF-8 support (utf8mb4)

CREATE DATABASE IF NOT EXISTS farmersmkt_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE farmersmkt_db;

CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(100) NOT NULL,
  user_email VARCHAR(150) NOT NULL UNIQUE,
  user_password VARCHAR(255) NOT NULL,
  user_phone VARCHAR(30) DEFAULT NULL,
  user_address TEXT DEFAULT NULL,
  role TINYINT(1) NOT NULL DEFAULT 3,
  status TINYINT(1) NOT NULL DEFAULT 1,
  user_image VARCHAR(255) DEFAULT NULL,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_email (user_email),
  INDEX idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS about (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  descrive TEXT DEFAULT NULL,
  year VARCHAR(50) DEFAULT NULL,
  total_age VARCHAR(50) DEFAULT NULL,
  a_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_about_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS owner_info (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_owner_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farmer (
  farm_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  farm_name VARCHAR(150) NOT NULL,
  farm_phone VARCHAR(30) DEFAULT NULL,
  farm_email VARCHAR(150) DEFAULT NULL,
  farm_address TEXT DEFAULT NULL,
  farm_about TEXT DEFAULT NULL,
  farm_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_farmer_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing (
  m_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  descrive TEXT DEFAULT NULL,
  m_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_marketing_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_overview (
  ov_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  descrive TEXT DEFAULT NULL,
  ov_category INT UNSIGNED NOT NULL DEFAULT 1,
  ov_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_farm_overview_category_status (ov_category, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS category (
  cat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cat_name VARCHAR(150) NOT NULL,
  cat_desc TEXT DEFAULT NULL,
  is_parent INT UNSIGNED NOT NULL DEFAULT 1,
  status TINYINT(1) NOT NULL DEFAULT 1,
  cat_image VARCHAR(255) DEFAULT NULL,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  seller_email VARCHAR(150) DEFAULT NULL,
  INDEX idx_category_parent_status (is_parent, status),
  INDEX idx_category_name (cat_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_negotiable TINYINT(1) NOT NULL DEFAULT 0,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  harvest_date DATE DEFAULT NULL,
  seasonal_availability VARCHAR(100) DEFAULT NULL,
  stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
  low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
  seller_email VARCHAR(150) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_category_status (category_id, status),
  INDEX idx_products_name (product_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post (
  post_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  post_desc TEXT DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  author_id INT UNSIGNED DEFAULT NULL,
  tags VARCHAR(255) DEFAULT NULL,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  post_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_post_status (status),
  INDEX idx_post_category (category_id),
  INDEX idx_post_author (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(150) DEFAULT NULL,
  user_number VARCHAR(30) DEFAULT NULL,
  subject VARCHAR(255) DEFAULT NULL,
  comments TEXT DEFAULT NULL,
  response TEXT DEFAULT NULL,
  responded_at DATETIME DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  cmt_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_list (
  or_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(150) DEFAULT NULL,
  user_phone VARCHAR(30) DEFAULT NULL,
  delivery_location TEXT DEFAULT NULL,
  delivery_notes TEXT DEFAULT NULL,
  or_name VARCHAR(255) NOT NULL,
  or_category INT UNSIGNED DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  or_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  delivery_update TEXT DEFAULT NULL,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_order_status (status),
  INDEX idx_order_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_inquiries (
  inquiry_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  buyer_id INT UNSIGNED NOT NULL,
  buyer_email VARCHAR(150) DEFAULT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  response TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_inquiries_product_status (product_id, status),
  INDEX idx_inquiries_buyer (buyer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional seed entry for an admin account.
-- This uses the same SHA1-based password handling used by the application.
INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, role, status)
VALUES ('Admin', 'stephenarichco@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', '+256761393437', 'Admin Address', 1, 1);
