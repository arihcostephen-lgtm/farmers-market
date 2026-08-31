-- ================================================================
-- NTUNGAMO LOCATIONS TABLE & MIGRATION SCRIPT
-- This script adds location-based area selection for the Ntungamo
-- farmers market system, replacing latitude/longitude coordinates
-- ================================================================

-- Create locations table for Ntungamo areas
CREATE TABLE IF NOT EXISTS locations (
  location_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_name VARCHAR(100) NOT NULL UNIQUE,
  district VARCHAR(50) NOT NULL DEFAULT 'Ntungamo',
  description TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_location_name (location_name),
  INDEX idx_location_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Ntungamo area locations
INSERT INTO locations (location_name, district, description) VALUES
('Ntungamo Town', 'Ntungamo', 'Central Ntungamo town area'),
('Rubaare', 'Ntungamo', 'Rubaare division'),
('Bushenyi', 'Ntungamo', 'Bushenyi division'),
('Kabwohe', 'Ntungamo', 'Kabwohe area'),
('Mirama Hills', 'Ntungamo', 'Mirama Hills region'),
('Kanungu', 'Ntungamo', 'Kanungu area'),
('Rukungiri', 'Ntungamo', 'Rukungiri division'),
('Katuna', 'Ntungamo', 'Katuna border area'),
('Rukoki', 'Ntungamo', 'Rukoki area'),
('Kisoro', 'Ntungamo', 'Kisoro area')
ON DUPLICATE KEY UPDATE location_name = VALUES(location_name);

-- Modify farmer table to add location references
ALTER TABLE farmer 
ADD COLUMN IF NOT EXISTS farm_location_id INT UNSIGNED DEFAULT NULL AFTER farm_address,
ADD COLUMN IF NOT EXISTS market_location_id INT UNSIGNED DEFAULT NULL AFTER market_address,
ADD KEY idx_farm_location (farm_location_id),
ADD KEY idx_market_location (market_location_id),
ADD CONSTRAINT fk_farmer_farm_location FOREIGN KEY (farm_location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
ADD CONSTRAINT fk_farmer_market_location FOREIGN KEY (market_location_id) REFERENCES locations(location_id) ON DELETE SET NULL;

-- Modify order_list table to add delivery location reference
ALTER TABLE order_list 
ADD COLUMN IF NOT EXISTS delivery_location_id INT UNSIGNED DEFAULT NULL AFTER delivery_location,
ADD KEY idx_order_delivery_location (delivery_location_id),
ADD CONSTRAINT fk_order_delivery_location FOREIGN KEY (delivery_location_id) REFERENCES locations(location_id) ON DELETE SET NULL;

-- Keep latitude/longitude fields for backward compatibility
-- These can be deprecated later but won't break existing data

-- ================================================================
-- Data Migration Notes:
-- - Existing latitude/longitude data is preserved
-- - New entries should use location_id instead
-- - Old coordinates can be manually matched to nearest location
-- - Future: Run cleanup queries to remove lat/lng dependencies
-- ================================================================
