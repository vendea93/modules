<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PolyUtilities Module Installation
 * 
 * This script handles:
 * 1. Creating necessary database tables
 * 2. Setting up default options
 * 3. Creating required directories
 */

// ============================================
// DATABASE TABLES
// ============================================

$CI = &get_instance();

// Create custom menus table
if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "poly_utilities_custom_menus` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `menu_type` enum('sidebar','setup','clients') NOT NULL COMMENT 'Type of menu',
          `slug` varchar(191) NOT NULL COMMENT 'Unique identifier',
          `parent_id` int(11) DEFAULT NULL COMMENT 'Parent menu ID for hierarchical structure',
          `parent_slug` varchar(255) DEFAULT 'root' COMMENT 'Parent menu slug for frontend convenience',
          `name` varchar(255) NOT NULL COMMENT 'Display name',
          `href` text DEFAULT NULL COMMENT 'Link URL',
          `icon` varchar(255) DEFAULT NULL COMMENT 'Icon class (e.g., fa-home)',
          `svg` text DEFAULT NULL COMMENT 'SVG icon content',
          `type` varchar(50) DEFAULT 'default' COMMENT 'Link type: default, none, iframe, popup, divider',
          `target` varchar(50) DEFAULT NULL COMMENT '_blank, _self, _parent, _top',
          `rel` varchar(50) DEFAULT NULL COMMENT 'nofollow, noopener, etc.',
          `css` text DEFAULT NULL COMMENT 'Custom CSS classes',
          `position` int(11) DEFAULT 0 COMMENT 'Display order',
          `level` tinyint(1) DEFAULT 1 COMMENT 'Nesting level (1, 2, 3)',
          `disabled` tinyint(1) DEFAULT 0 COMMENT 'Hide/Show menu item',
          `is_custom` tinyint(1) DEFAULT 1 COMMENT 'Is custom menu (not system menu)',
          `module_name` varchar(100) DEFAULT NULL COMMENT 'Module system name that owns this menu item',
          `require_login` tinyint(1) DEFAULT 0 COMMENT 'For clients: require login to view',
          `badge_value` varchar(50) DEFAULT NULL COMMENT 'Badge text',
          `badge_color` varchar(50) DEFAULT NULL COMMENT 'Badge color',
          `popup_description` text DEFAULT NULL COMMENT 'Popup content (JSON)',
          `href_original` text DEFAULT NULL COMMENT 'Original href for iframe type',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_menu_type` (`menu_type`),
          KEY `idx_parent_id` (`parent_id`),
          KEY `idx_parent_slug` (`parent_slug`),
          KEY `idx_slug` (`slug`),
          KEY `idx_position` (`position`),
          KEY `idx_menu_type_parent` (`menu_type`, `parent_id`),
          KEY `idx_disabled` (`disabled`),
          KEY `idx_module_name` (`module_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
}

// CRITICAL: Ensure module_name column exists immediately after table creation
// This must run before any hooks can query this column
$custom_menus_table = db_prefix() . 'poly_utilities_custom_menus';
if ($CI->db->table_exists($custom_menus_table)) {
    if (!$CI->db->field_exists('module_name', $custom_menus_table)) {
        try {
            $CI->db->query("
                ALTER TABLE `{$custom_menus_table}`
                ADD COLUMN `module_name` VARCHAR(100) DEFAULT NULL COMMENT 'Module system name that owns this menu item' AFTER `is_custom`,
                ADD INDEX `idx_module_name` (`module_name`)
            ");
        } catch (Exception $e) {
            log_message('error', 'poly_utilities install failed to add module_name column: ' . $e->getMessage());
        }
    }
}

// Create customer addresses table (introduced in version 3.4.0)
$customer_addresses_table = db_prefix() . 'poly_utilities_customer_addresses';

if (!$CI->db->table_exists($customer_addresses_table)) {
    $CI->db->query("
        CREATE TABLE `{$customer_addresses_table}` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `clientid` INT(11) UNSIGNED NOT NULL,
            `title` VARCHAR(191) NOT NULL,
            `address_line1` TEXT DEFAULT NULL,
            `address_line2` TEXT DEFAULT NULL,
            `city` VARCHAR(120) DEFAULT NULL,
            `state` VARCHAR(120) DEFAULT NULL,
            `zip` VARCHAR(40) DEFAULT NULL,
            `country_id` INT(11) DEFAULT NULL,
            `contact_person` VARCHAR(191) DEFAULT NULL,
            `phone` VARCHAR(60) DEFAULT NULL,
            `email` VARCHAR(191) DEFAULT NULL,
            `map_url` TEXT DEFAULT NULL,
            `map_embed` TEXT DEFAULT NULL,
            `latitude` VARCHAR(50) DEFAULT NULL,
            `longitude` VARCHAR(50) DEFAULT NULL,
            `additional_info` TEXT DEFAULT NULL,
            `social_links` LONGTEXT DEFAULT NULL,
            `is_default_billing` TINYINT(1) NOT NULL DEFAULT 0,
            `is_default_shipping` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_customer` (`clientid`),
            KEY `idx_country` (`country_id`),
            KEY `idx_default_billing` (`clientid`, `is_default_billing`),
            KEY `idx_default_shipping` (`clientid`, `is_default_shipping`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
}

// Ensure additional columns exist (handles upgrades without running older migrations)
$customer_columns = [
    'map_url'      => "ALTER TABLE `{$customer_addresses_table}` ADD COLUMN `map_url` TEXT DEFAULT NULL AFTER `email`",
    'map_embed'    => "ALTER TABLE `{$customer_addresses_table}` ADD COLUMN `map_embed` TEXT DEFAULT NULL AFTER `map_url`",
    'latitude'     => "ALTER TABLE `{$customer_addresses_table}` ADD COLUMN `latitude` VARCHAR(50) DEFAULT NULL AFTER `map_embed`",
    'longitude'    => "ALTER TABLE `{$customer_addresses_table}` ADD COLUMN `longitude` VARCHAR(50) DEFAULT NULL AFTER `latitude`",
    'social_links' => "ALTER TABLE `{$customer_addresses_table}` ADD COLUMN `social_links` LONGTEXT DEFAULT NULL AFTER `additional_info`",
];

foreach ($customer_columns as $column => $statement) {
    if ($CI->db->table_exists($customer_addresses_table) && !$CI->db->field_exists($column, $customer_addresses_table)) {
        try {
            $CI->db->query($statement);
        } catch (Throwable $th) {
            log_message('error', 'poly_utilities install failed to add column ' . $column . ': ' . $th->getMessage());
        }
    }
}

// Add foreign key constraint for parent_id (if not exists)
try {
    $has_fk = $CI->db->query("
        SELECT COUNT(*) as count 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() 
        AND TABLE_NAME = '" . db_prefix() . "poly_utilities_custom_menus' 
        AND CONSTRAINT_NAME = '" . db_prefix() . "fk_parent_menu'
    ")->row()->count;
    
    if ($has_fk == 0) {
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
            ADD CONSTRAINT `" . db_prefix() . "fk_parent_menu` FOREIGN KEY (`parent_id`) 
            REFERENCES `" . db_prefix() . "poly_utilities_custom_menus` (`id`) 
            ON DELETE CASCADE
        ");
    }
} catch (Exception $e) {
    // Foreign key may already exist or other constraint issue
}

// Add parent_slug column if not exists (for existing installations)
if ($CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
    if (!$CI->db->field_exists('parent_slug', db_prefix() . 'poly_utilities_custom_menus')) {
        try {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
                ADD COLUMN `parent_slug` VARCHAR(255) DEFAULT 'root' COMMENT 'Parent menu slug for frontend convenience' AFTER `parent_id`,
                ADD INDEX `idx_parent_slug` (`parent_slug`)
            ");
            
            // Populate parent_slug from existing parent_id
            $menus = $CI->db->select('id, slug, parent_id')
                           ->from(db_prefix() . 'poly_utilities_custom_menus')
                           ->get()
                           ->result_array();
            
            if (!empty($menus)) {
                // Create map of id => slug
                $id_to_slug = [];
                foreach ($menus as $menu) {
                    $id_to_slug[$menu['id']] = $menu['slug'];
                }
                
                // Update parent_slug based on parent_id
                foreach ($menus as $menu) {
                    $parent_slug = 'root';
                    if ($menu['parent_id'] !== null && isset($id_to_slug[$menu['parent_id']])) {
                        $parent_slug = $id_to_slug[$menu['parent_id']];
                    }
                    
                    $CI->db->where('id', $menu['id'])
                           ->update(db_prefix() . 'poly_utilities_custom_menus', ['parent_slug' => $parent_slug]);
                }
                
            }
        } catch (Exception $e) {
        }
    }
}

//  Add option_settings column if not exists (for custom menu settings like popup size, etc.)
if ($CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
    if (!$CI->db->field_exists('option_settings', db_prefix() . 'poly_utilities_custom_menus')) {
        try {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
                ADD COLUMN `option_settings` TEXT DEFAULT NULL COMMENT 'JSON object for custom settings (e.g., popup_size, etc.)' AFTER `href_original`
            ");
        } catch (Exception $e) {
        }
    }
}

// Add roles, users, clients columns if not exist (for permissions stored as JSON)
if ($CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
    // Add roles column
    if (!$CI->db->field_exists('roles', db_prefix() . 'poly_utilities_custom_menus')) {
        try {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
                ADD COLUMN `roles` TEXT DEFAULT NULL COMMENT 'JSON array of role IDs for permission control' AFTER `option_settings`
            ");
        } catch (Exception $e) {
        }
    }
    
    // Add users column
    if (!$CI->db->field_exists('users', db_prefix() . 'poly_utilities_custom_menus')) {
        try {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
                ADD COLUMN `users` TEXT DEFAULT NULL COMMENT 'JSON array of user IDs for permission control' AFTER `roles`
            ");
        } catch (Exception $e) {
        }
    }
    
    // Add clients column
    if (!$CI->db->field_exists('clients', db_prefix() . 'poly_utilities_custom_menus')) {
        try {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "poly_utilities_custom_menus`
                ADD COLUMN `clients` TEXT DEFAULT NULL COMMENT 'JSON array of client IDs for permission control' AFTER `users`
            ");
        } catch (Exception $e) {
        }
    }
}

// ============================================
// OPTIONS (Keep for backward compatibility)
// ============================================

// Settings
add_option(POLY_UTILITIES_SETTINGS, '');
add_option(POLY_CUSTOM_MENU, '');
add_option(POLY_WIDGETS, '');
add_option(POLY_STYLES, '');
add_option(POLY_SCRIPTS, '');
add_option(POLY_QUICK_ACCESS_MENU, '');
add_option(POLY_SUPPORTS, '');

// Data table
add_option(POLY_TABLE_FILTERS, '');
add_option(POLY_TABLE_COLUMNS_REORDER, '[]');

// Context menu
add_option(POLY_CONTEXT_MENU, '');

// Menu options (kept for backward compatibility during migration period)
add_option(POLY_MENU_SIDEBAR, '[]');
add_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE, '[]');
add_option(POLY_MENU_SETUP, '[]');
add_option(POLY_MENU_SETUP_CUSTOM_ACTIVE, '[]');
add_option(POLY_MENU_CLIENTS, '[]');
add_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE, '[]');

// Banners & announcements
add_option(POLY_BANNERS, '[]');
add_option(POLY_BANNERS_AREA, '');
add_option(POLY_BANNERS_ANNOUNCEMENTS, '[]');
add_option(POLY_BANNERS_ANNOUNCEMENTS_AREA, '');
add_option(POLY_BANNERS_SETTINGS, '');

// Appearance
add_option(POLY_UTILITIES_APPEARANCE_SETTINGS, '');

// Projects
add_option(POLYUTILITIES_PROJECT_NAME_PATTERNS, '[]');

// ============================================
// TASK TEMPLATES TABLES (Version 3.4.2)
// ============================================

// Create task template categories table
if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_task_template_categories')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "poly_utilities_task_template_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `color` varchar(7) DEFAULT NULL,
            `order` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `datecreated` datetime NOT NULL,
            `dateupdated` datetime DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `active` (`active`),
            KEY `order` (`order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
}

// Create task templates table
if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_task_templates')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "poly_utilities_task_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `order` int(11) DEFAULT 0,
            `active` tinyint(1) DEFAULT 1,
            `datecreated` datetime NOT NULL,
            `dateupdated` datetime DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `category_id` (`category_id`),
            KEY `active` (`active`),
            KEY `order` (`order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
    
    // Add foreign key constraint
    try {
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "poly_utilities_task_templates`
            ADD CONSTRAINT `" . db_prefix() . "fk_task_template_category` 
            FOREIGN KEY (`category_id`) 
            REFERENCES `" . db_prefix() . "poly_utilities_task_template_categories` (`id`) 
            ON DELETE SET NULL
        ");
    } catch (Exception $e) {
        // Foreign key may already exist
    }
}

// Create task template items table
if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_task_template_items')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "poly_utilities_task_template_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `template_id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `priority` int(11) DEFAULT 2,
            `estimated_hours` decimal(10,2) DEFAULT NULL,
            `milestone_id` int(11) DEFAULT NULL,
            `order` int(11) DEFAULT 0,
            `datecreated` datetime NOT NULL,
            `dateupdated` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `template_id` (`template_id`),
            KEY `order` (`order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
    
    // Add foreign key constraint
    try {
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "poly_utilities_task_template_items`
            ADD CONSTRAINT `" . db_prefix() . "fk_task_template_item` 
            FOREIGN KEY (`template_id`) 
            REFERENCES `" . db_prefix() . "poly_utilities_task_templates` (`id`) 
            ON DELETE CASCADE
        ");
    } catch (Exception $e) {
        // Foreign key may already exist
    }
}

// Create task template item checklist items table
if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_task_template_item_checklist_items')) {
    $CI->db->query("
        CREATE TABLE `" . db_prefix() . "poly_utilities_task_template_item_checklist_items` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `template_item_id` int(11) NOT NULL,
            `description` text NOT NULL,
            `order` int(11) DEFAULT 0,
            `datecreated` datetime NOT NULL,
            `dateupdated` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `template_item_id` (`template_item_id`),
            KEY `order` (`order`),
            KEY `idx_template_item_order` (`template_item_id`, `order`),
            FOREIGN KEY (`template_item_id`) 
            REFERENCES `" . db_prefix() . "poly_utilities_task_template_items` (`id`) 
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . " COLLATE=" . $CI->db->dbcollat . "
    ");
}

// ============================================
// ADDITIONAL INDEXES FOR PERFORMANCE OPTIMIZATION
// ============================================

// Indexes for poly_utilities_task_template_categories
$categories_table = db_prefix() . 'poly_utilities_task_template_categories';
if ($CI->db->table_exists($categories_table)) {
    // Composite index for filtering active categories and sorting by order
    try {
        $CI->db->query("CREATE INDEX `idx_active_order` ON `{$categories_table}` (`active`, `order`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Index for filtering by creator
    try {
        $CI->db->query("CREATE INDEX `idx_created_by` ON `{$categories_table}` (`created_by`)");
    } catch (Exception $e) {
        // Index may already exist
    }
}

// Indexes for poly_utilities_task_templates
$templates_table = db_prefix() . 'poly_utilities_task_templates';
if ($CI->db->table_exists($templates_table)) {
    // Composite index for filtering active templates by category
    try {
        $CI->db->query("CREATE INDEX `idx_active_category` ON `{$templates_table}` (`active`, `category_id`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Composite index for filtering active templates and sorting by order
    try {
        $CI->db->query("CREATE INDEX `idx_active_order` ON `{$templates_table}` (`active`, `order`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Index for filtering by creator
    try {
        $CI->db->query("CREATE INDEX `idx_created_by` ON `{$templates_table}` (`created_by`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Index for sorting by date created
    try {
        $CI->db->query("CREATE INDEX `idx_datecreated` ON `{$templates_table}` (`datecreated`)");
    } catch (Exception $e) {
        // Index may already exist
    }
}

// Indexes for poly_utilities_task_template_items
$items_table = db_prefix() . 'poly_utilities_task_template_items';
if ($CI->db->table_exists($items_table)) {
    // Composite index for filtering by template and sorting by order
    try {
        $CI->db->query("CREATE INDEX `idx_template_order` ON `{$items_table}` (`template_id`, `order`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Index for filtering by priority
    try {
        $CI->db->query("CREATE INDEX `idx_priority` ON `{$items_table}` (`priority`)");
    } catch (Exception $e) {
        // Index may already exist
    }
    
    // Index for filtering by milestone
    try {
        $CI->db->query("CREATE INDEX `idx_milestone_id` ON `{$items_table}` (`milestone_id`)");
    } catch (Exception $e) {
        // Index may already exist
    }
}

// ============================================
// DIRECTORIES
// ============================================

// Create uploads directories
$uploads_dir = module_dir_path(POLY_UTILITIES_MODULE_NAME, 'uploads');
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Create subdirectories for different file types
$sub_dirs = [
    'media',           // For media files (images, videos, etc.)
    'appearance',      // For appearance/customization files
    'js',             // For custom JavaScript files
    'css',            // For custom CSS files
];

foreach ($sub_dirs as $sub_dir) {
    $dir_path = $uploads_dir . '/' . $sub_dir;
    if (!is_dir($dir_path)) {
        mkdir($dir_path, 0755, true);
    }
}

// Create .htaccess file for security (prevent direct access to sensitive files)
$htaccess_content = "# Deny access to PHP files in uploads directory
<Files \"*.php\">
    Order Deny,Allow
    Deny from all
</Files>

# Deny access to sensitive file types
<FilesMatch \"\\.(inc|conf|config|sql|log|txt)$\">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Allow access to media files
<FilesMatch \"\\.(jpg|jpeg|png|gif|svg|ico|css|js|pdf|doc|docx|xls|xlsx|zip|rar)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>";

$htaccess_file = $uploads_dir . '/.htaccess';
if (!file_exists($htaccess_file)) {
    file_put_contents($htaccess_file, $htaccess_content);
}

// ============================================
// MIGRATION CHECK
// ============================================

// Check if we need to migrate old options data to database
$migration_flag = get_option('poly_utilities_db_migrated');
if (!$migration_flag) {
    // Flag will be set by migration 322
    add_option('poly_utilities_db_migrated', '0');
}
