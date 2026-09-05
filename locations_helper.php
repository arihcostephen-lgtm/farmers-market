<?php
/**
 * Locations Helper
 * Provides functions for managing Ntungamo area locations
 */

if (!function_exists('get_all_locations')) {
    /**
     * Get all active locations
     * @return array Array of location records with location_id and location_name
     */
    function get_all_locations() {
        global $db;
        $result = $db->query("SELECT location_id, location_name, description, latitude, longitude FROM locations WHERE is_active = 1 ORDER BY location_name ASC");
        $locations = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $locations[] = $row;
            }
        }
        
        return $locations;
    }
}

if (!function_exists('get_location_map_link')) {
    /**
     * Build a map URL for a location name or coordinates.
     * @param string $location_name
     * @param float|null $latitude
     * @param float|null $longitude
     * @return string
     */
    function get_location_map_link($location_name, $latitude = null, $longitude = null) {
        $location = trim((string) $location_name);
        if ($location === '' && $latitude !== null && $longitude !== null) {
            return 'https://www.google.com/maps?q=' . rawurlencode($latitude . ',' . $longitude);
        }
        if ($location !== '') {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($location . ' Ntungamo Uganda');
        }
        return '#';
    }
}

if (!function_exists('get_location_name')) {
    /**
     * Get a location name by ID
     * @param int $location_id - The location ID
     * @return string The location name or empty string if not found
     */
    function get_location_name($location_id) {
        global $db;
        
        if (!$location_id) {
            return '';
        }
        
        $location_id = (int) $location_id;
        $result = $db->query("SELECT location_name FROM locations WHERE location_id = $location_id AND is_active = 1");
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['location_name'];
        }
        
        return '';
    }
}

if (!function_exists('create_location_dropdown')) {
    /**
     * Create an HTML dropdown for location selection
     * @param string $name - The input name attribute
     * @param string $id - The input ID attribute
     * @param int $selected_id - The currently selected location ID
     * @param string $label - The label text (optional)
     * @param bool $required - Whether the field is required
     * @return string HTML markup for the dropdown
     */
    function create_location_dropdown($name, $id, $selected_id = null, $label = null, $required = false) {
        global $db;
        
        $html = '';
        
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($id) . '" class="form-label">' . htmlspecialchars($label) . '</label>';
        }
        
        $html .= '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '" class="form-select" ' . ($required ? 'required' : '') . '>';
        $html .= '<option value="">-- Select Location --</option>';
        
        $result = $db->query("SELECT location_id, location_name FROM locations WHERE is_active = 1 ORDER BY location_name ASC");
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $isSelected = ($selected_id && $selected_id == $row['location_id']) ? 'selected' : '';
                $html .= '<option value="' . $row['location_id'] . '" ' . $isSelected . '>' . htmlspecialchars($row['location_name']) . '</option>';
            }
        }
        
        $html .= '</select>';
        
        return $html;
    }
}

if (!function_exists('get_locations_for_filter')) {
    /**
     * Get locations formatted for filter/search operations
     * @return array Array with location_id as key and location_name as value
     */
    function get_locations_for_filter() {
        global $db;
        $result = $db->query("SELECT location_id, location_name FROM locations WHERE is_active = 1 ORDER BY location_name ASC");
        $locations = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $locations[$row['location_id']] = $row['location_name'];
            }
        }
        
        return $locations;
    }
}

if (!function_exists('search_farms_by_location')) {
    /**
     * Search farms by location
     * @param int $location_id - The location ID to search for
     * @return array Array of farm records
     */
    function search_farms_by_location($location_id) {
        global $db;
        
        $location_id = (int) $location_id;
        $result = $db->query("SELECT f.farm_id, f.farm_name, f.farm_email, f.farm_phone, f.farm_address, 
                            l.location_name FROM farmer f 
                            LEFT JOIN locations l ON l.location_id = f.farm_location_id 
                            WHERE (f.farm_location_id = $location_id OR f.market_location_id = $location_id) 
                            AND f.status = 1 
                            ORDER BY f.farm_name ASC");
        
        $farms = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $farms[] = $row;
            }
        }
        
        return $farms;
    }
}

if (!function_exists('get_delivery_locations')) {
    /**
     * Get all available delivery locations
     * @return array Array of location records
     */
    function get_delivery_locations() {
        return get_all_locations();
    }
}

if (!function_exists('validate_location_id')) {
    /**
     * Validate if a location ID exists and is active
     * @param int $location_id - The location ID to validate
     * @return bool True if location exists and is active
     */
    function validate_location_id($location_id) {
        global $db;
        
        $location_id = (int) $location_id;
        $result = $db->query("SELECT 1 FROM locations WHERE location_id = $location_id AND is_active = 1");
        
        return $result && $result->num_rows > 0;
    }
}
?>
