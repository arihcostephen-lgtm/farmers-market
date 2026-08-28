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

CREATE TABLE IF NOT EXISTS manager_profiles (
  manager_id INT UNSIGNED PRIMARY KEY,
  department VARCHAR(150) DEFAULT NULL,
  hire_date DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_manager_profiles_department (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supervisor_profiles (
  supervisor_id INT UNSIGNED PRIMARY KEY,
  region VARCHAR(150) DEFAULT NULL,
  specialization VARCHAR(150) DEFAULT NULL,
  hire_date DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_supervisor_profiles_region (region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS manager_activity_log (
  log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id INT UNSIGNED NOT NULL,
  actor_name VARCHAR(150) DEFAULT NULL,
  action_type VARCHAR(100) NOT NULL,
  target_type VARCHAR(100) DEFAULT NULL,
  target_id INT UNSIGNED DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_manager_activity_actor (actor_id),
  INDEX idx_manager_activity_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supervisor_activity_log (
  log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id INT UNSIGNED NOT NULL,
  actor_name VARCHAR(150) DEFAULT NULL,
  action_type VARCHAR(100) NOT NULL,
  target_type VARCHAR(100) DEFAULT NULL,
  target_id INT UNSIGNED DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_supervisor_activity_actor (actor_id),
  INDEX idx_supervisor_activity_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supervisor_reports (
  report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supervisor_id INT UNSIGNED NOT NULL,
  supervisor_name VARCHAR(150) NOT NULL,
  title VARCHAR(200) NOT NULL,
  report_body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_supervisor_reports_created (created_at),
  INDEX idx_supervisor_reports_supervisor (supervisor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_visits (
  visit_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  farm_id INT UNSIGNED NOT NULL,
  supervisor_id INT UNSIGNED NOT NULL,
  visit_date DATE NOT NULL,
  status ENUM('Scheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_farm_visits_farm (farm_id),
  INDEX idx_farm_visits_supervisor (supervisor_id),
  INDEX idx_farm_visits_date (visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS supervisor_document_reviews (
  review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_path VARCHAR(500) NOT NULL,
  status ENUM('approved', 'rejected') NOT NULL,
  reviewed_by INT UNSIGNED NOT NULL,
  reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_supervisor_document_path (document_path),
  INDEX idx_supervisor_document_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_document_reviews (
  review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_path VARCHAR(500) NOT NULL,
  status ENUM('approved', 'rejected') NOT NULL,
  reviewed_by INT UNSIGNED NOT NULL,
  reviewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admin_document_path (document_path),
  INDEX idx_admin_document_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farmer_subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  farmer_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED DEFAULT NULL,
  subscription_name VARCHAR(150) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status TINYINT(1) NOT NULL DEFAULT 0,
  approved_by INT UNSIGNED DEFAULT NULL,
  approved_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_farmer_subscription (farmer_id),
  INDEX idx_farmer_subscriptions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_plans (
  plan_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_name VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  duration_days INT UNSIGNED NOT NULL DEFAULT 30,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_subscription_plans_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tax_rules (
  rule_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rule_name VARCHAR(150) NOT NULL,
  rate_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  min_quantity INT UNSIGNED NOT NULL DEFAULT 0,
  max_quantity INT UNSIGNED DEFAULT NULL,
  applies_to VARCHAR(50) NOT NULL DEFAULT 'all',
  applies_unit VARCHAR(20) NOT NULL DEFAULT 'all',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tax_rules_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_payroll (
  staff_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  staff_name VARCHAR(150) NOT NULL,
  staff_role VARCHAR(100) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status TINYINT(1) NOT NULL DEFAULT 0,
  paid_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_staff_payroll_status (status),
  INDEX idx_staff_payroll_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS extra_costs (
  cost_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cost_name VARCHAR(150) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes TEXT DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_extra_costs_created_by (created_by),
  INDEX idx_extra_costs_created_date (created_at, cost_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS extra_cost_requests (
  request_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  requested_by INT UNSIGNED NOT NULL,
  requested_by_name VARCHAR(150) NOT NULL,
  cost_name VARCHAR(150) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  reason TEXT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  approved_by INT UNSIGNED DEFAULT NULL,
  approved_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_extra_cost_requests_status (status),
  INDEX idx_extra_cost_requests_requested_by (requested_by),
  INDEX idx_extra_cost_requests_approved_by (approved_by),
  INDEX idx_extra_cost_requests_owner_date (requested_by, created_at)
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
  farm_document VARCHAR(255) DEFAULT NULL,
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
  product_unit VARCHAR(20) NOT NULL DEFAULT 'kilogram',
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
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  order_unit VARCHAR(20) NOT NULL DEFAULT 'kilogram',
  or_image VARCHAR(255) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  payment_status ENUM('unpaid', 'pending', 'paid', 'failed') NOT NULL DEFAULT 'unpaid',
  delivery_update TEXT DEFAULT NULL,
  join_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_order_status (status),
  INDEX idx_order_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  provider ENUM('mtn_uganda', 'airtel_uganda', 'ussd') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  reference VARCHAR(100) NOT NULL,
  provider_reference VARCHAR(150) DEFAULT NULL,
  status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
  provider_response TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  UNIQUE KEY uq_payment_reference (reference),
  INDEX idx_payment_order (order_id),
  INDEX idx_payment_provider_reference (provider_reference),
  INDEX idx_payment_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_batches (
  batch_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  provider ENUM('mtn_uganda', 'airtel_uganda', 'ussd') NOT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  status ENUM('pending', 'successful', 'failed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_payment_batches_user (user_id),
  INDEX idx_payment_batches_status (status)
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
