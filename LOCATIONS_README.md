# Location System - Ntungamo Areas Implementation

## Overview

This update replaces latitude/longitude coordinate entry with human-readable Ntungamo area names for easier search, filtering, and delivery management.

## What Changed

### 1. **Database Schema** (`locations_migration.sql`)

A new `locations` table has been created containing 10 Ntungamo areas:
- Ntungamo Town
- Rubaare
- Bushenyi
- Kabwohe
- Mirama Hills
- Kanungu
- Rukungiri
- Katuna
- Rukoki
- Kisoro

**New Columns Added:**
- `farmer.farm_location_id` - References the location where farm is located
- `farmer.market_location_id` - References the location of market/collection point
- `order_list.delivery_location_id` - References the delivery location (via text field)

**Backward Compatibility:**
- Old `farm_latitude`, `farm_longitude`, `market_latitude`, `market_longitude` fields are preserved for data migration purposes

### 2. **Files Created**

#### `locations_helper.php`
Utility functions for location management:

```php
// Get all active locations
get_all_locations()

// Get location name by ID
get_location_name($location_id)

// Create HTML dropdown for location selection
create_location_dropdown($name, $id, $selected_id, $label, $required)

// Get locations as key-value array for filtering
get_locations_for_filter()

// Search farms by location
search_farms_by_location($location_id)

// Get all delivery locations
get_delivery_locations()

// Validate if location exists
validate_location_id($location_id)
```

#### `locations_migration.sql`
SQL script to:
- Create `locations` table
- Insert 10 Ntungamo areas
- Add foreign key references in `farmer` and `order_list` tables
- Create proper indexes

### 3. **Pages Updated**

#### `farmerDashboard.php`
**Changes:**
- Import `locations_helper.php`
- Replace latitude/longitude input fields with location dropdowns
- "Farm location" field now selects from predefined Ntungamo areas
- "Market location" field also selects from Ntungamo areas
- Added "Farm address/directions" textarea for specific landmark details
- SQL queries updated to use `farm_location_id` and `market_location_id`
- Form processing saves location IDs instead of coordinates

**Before:**
```html
<input type="number" step="any" name="farm_latitude" ... />
<input type="number" step="any" name="farm_longitude" ... />
```

**After:**
```html
<select name="farm_location_id" class="form-select" required>
  <option>Ntungamo Town</option>
  <option>Rubaare</option>
  ...
</select>
<textarea name="farm_location_desc" ... />
```

#### `customerDashboard.php`
**Changes:**
- Import `locations_helper.php`
- Updated product SQL query to JOIN with `locations` table
- Show location names instead of coordinates in product cards
- Location filter updated to search by location names
- Added location icon and display to product cards

**SQL Change:**
```sql
-- Now includes location lookups
LEFT JOIN locations l1 ON l1.location_id = f.farm_location_id 
LEFT JOIN locations l2 ON l2.location_id = f.market_location_id
```

**Display Change:**
Shows location name with icon:
```
🗺️ Ntungamo Town
```

#### `placeOrder.php`
**Changes:**
- Import `locations_helper.php`
- Delivery location changed from textarea to dropdown
- Customers select from predefined Ntungamo areas
- Removed requirement for custom text entry

**Before:**
```html
<textarea name="delivery_location" placeholder="Enter address..." />
```

**After:**
```html
<select name="delivery_location" class="form-select" required>
  <option>Ntungamo Town</option>
  <option>Rubaare</option>
  ...
</select>
```

## Installation Steps

### Step 1: Import Database Schema

Run the migration SQL script to create the locations table and add new columns:

```bash
mysql -u root -p farmersmkt_db < locations_migration.sql
```

Or via phpMyAdmin:
1. Go to Databases → `farmersmkt_db`
2. Click "SQL" tab
3. Paste contents of `locations_migration.sql`
4. Click "Go"

### Step 2: Verify Database Changes

After running the migration, verify the changes:

```sql
-- Check locations table was created
SELECT * FROM locations;

-- Check farmer table has new columns
DESCRIBE farmer;

-- Check order_list table has new columns
DESCRIBE order_list;
```

### Step 3: Test the System

1. **Farmer Registration/Profile:**
   - Log in as a farmer
   - Go to Profile
   - Location fields should now be dropdowns
   - Select a Ntungamo area for farm and market

2. **Customer Browsing:**
   - View products in dashboard
   - Should see location names displayed (e.g., "🗺️ Ntungamo Town")
   - Use location filter to search products

3. **Placing Orders:**
   - Select a product to order
   - Delivery location should be a dropdown
   - Select a Ntungamo area for delivery
   - Complete checkout

## Features

### For Farmers
- ✅ Easy location selection from predefined Ntungamo areas
- ✅ Ability to add specific directions/landmarks in description field
- ✅ Consistent location representation across platform

### For Customers
- ✅ Filter products by Ntungamo location
- ✅ Clear location information on all products
- ✅ Select delivery location from predefined areas
- ✅ No need to remember or type addresses

### For Platform
- ✅ Standardized location data
- ✅ Easier analytics and reporting
- ✅ Better search and filtering performance
- ✅ Consistent data quality

## Data Migration (If Existing Data)

If the system has existing farm/market data with coordinates:

1. **Old coordinates are preserved** in the database
2. **Manual mapping needed** to convert old data to new location IDs
3. **Query to help migration:**

```sql
-- Update farmers with approximate location based on coordinates
-- This is a starting point - manual review recommended
UPDATE farmer f
SET f.farm_location_id = (
  SELECT location_id FROM locations 
  WHERE location_name = 'Ntungamo Town' 
  LIMIT 1
)
WHERE f.farm_location_id IS NULL 
  AND f.status = 1;
```

## API Reference

All functions in `locations_helper.php` use the global `$db` mysqli connection.

### get_all_locations()
Returns array of all active locations with `location_id` and `location_name`.

```php
$locations = get_all_locations();
// Returns: [
//   ['location_id' => 1, 'location_name' => 'Ntungamo Town', 'description' => '...'],
//   ['location_id' => 2, 'location_name' => 'Rubaare', 'description' => '...'],
//   ...
// ]
```

### create_location_dropdown($name, $id, $selected_id, $label, $required)
Generates HTML dropdown for form fields.

```php
echo create_location_dropdown(
  'farm_location_id',      // name attribute
  'farmLocation',          // id attribute
  5,                       // selected location ID
  'Select Farm Location',  // label text
  true                     // required field
);
```

### search_farms_by_location($location_id)
Find all farms in a specific location.

```php
$farms = search_farms_by_location(1);
// Returns farms where farm_location_id OR market_location_id = 1
```

## Backward Compatibility Notes

- Old latitude/longitude fields remain in database but are not used
- New queries use location IDs via foreign keys
- Existing orders with text delivery locations continue to work
- Data migration is optional - system works with mix of old/new data

## Future Enhancements

Possible improvements for the location system:

1. **Map Integration**
   - Show farms on interactive map
   - Add Google Maps API integration
   - Geofencing for delivery zones

2. **More Locations**
   - Expand beyond Ntungamo to other districts
   - Add subcategories within each area

3. **Distance Calculation**
   - Calculate delivery fees based on location
   - Suggest nearest farms/markets to customer

4. **Location Analytics**
   - Reports on top locations by orders
   - Heatmaps of customer demand

5. **Multi-location Support**
   - Farmers with multiple farms
   - Markets in multiple areas

## Troubleshooting

### Locations dropdown is empty
- Verify `locations_migration.sql` was executed
- Check: `SELECT COUNT(*) FROM locations;` should return 10

### Can't update farmer profile
- Ensure `farm_location_id` column exists
- Run: `ALTER TABLE farmer ADD COLUMN farm_location_id INT UNSIGNED DEFAULT NULL;`

### Delivery location not showing on orders
- Check that order was placed with dropdown selection
- Verify `delivery_location` table field contains location name

### Old farms showing no location
- Existing data didn't have location IDs
- Farmers need to update profile and select location
- Can be auto-populated during migration

## Support

For issues or questions about the location system:
1. Check the functions in `locations_helper.php`
2. Review the SQL schema in `locations_migration.sql`
3. Verify all three new files are in place

## Version

Location System v1.0
- Created: 2026-08-31
- Ntungamo-specific implementation
- 10 predefined areas
- Backward compatible with existing data
