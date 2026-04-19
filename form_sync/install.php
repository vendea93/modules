<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Module - Installation Script
 * 
 * This file is executed automatically when the module is activated.
 * It handles database table creation, migrations, and default option setup.
 * 
 * @package    FormSync
 * @subpackage Installation
 * @category   Module
 * @version    1.0.0
 * @author     LiquidApps Studio
 */

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Ensure CodeIgniter instance is available
 * The $CI variable may be passed from the activation hook, or we need to get it
 */
if (!isset($CI) || !is_object($CI)) {
    $CI = &get_instance();
}

// ============================================================================
// DATABASE TABLE CREATION
// ============================================================================

/**
 * Create form_sync_config table
 * 
 * This table stores the main integration configuration settings.
 */
if (!$CI->db->table_exists(db_prefix() . 'form_sync_config')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "form_sync_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` text NOT NULL,
  `form_id` varchar(191) DEFAULT NULL,
  `sync_method` varchar(50) NOT NULL DEFAULT 'polling',
  `polling_interval` int(11) NOT NULL DEFAULT '15',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `datecreated` datetime NOT NULL,
  `dateupdated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

/**
 * Create form_sync_field_mappings table
 * 
 * Stores field mappings between form fields and Perfex CRM fields.
 * Supports multiple forms and different target types (leads/customers).
 * 
 * Structure:
 * - form_id: Links to a specific form
 * - target_type: Whether this mapping is for 'lead' or 'customer'
 * - form_field_id: The form field identifier
 * - perfex_field: The corresponding Perfex CRM field name
 */
if (!$CI->db->table_exists(db_prefix() . 'form_sync_field_mappings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "form_sync_field_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(191) NOT NULL,
  `target_type` enum('lead','customer','estimate_request','ticket') NOT NULL DEFAULT 'customer',
  `form_field_id` varchar(191) NOT NULL,
  `form_field_label` varchar(191) NOT NULL,
  `perfex_field` varchar(191) NOT NULL,
  `datecreated` datetime NOT NULL,
  `dateupdated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_field_target` (`form_id`, `form_field_id`, `target_type`),
  KEY `form_id` (`form_id`),
  KEY `target_type` (`target_type`),
  KEY `perfex_field` (`perfex_field`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    /**
     * Migration: Update existing form_sync_field_mappings table
     * 
     * This migration runs when updating from an older version of the module.
     * It updates:
     * - target_type ENUM to include 'estimate_request' and 'ticket'
     */
    try {
        $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_field_mappings` WHERE Field = "target_type"')->row();
        if ($column_info && strpos($column_info->Type, 'enum') !== false) {
            // Check if new values are already in the enum
            if (strpos($column_info->Type, 'estimate_request') === false) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_field_mappings` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                log_message('info', '[FormSync] Updated target_type enum to include estimate_request and ticket in field_mappings');
            }
        }
    } catch (Exception $e) {
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Could not update target_type enum in field_mappings: ' . $e->getMessage());
        }
    }
}

/**
 * Create form_sync_submission_logs table
 * 
 * Stores logs of all form submissions processed by the integration.
 * Tracks status, errors, duplicates, and created entities.
 * 
 * Status values:
 * - success: Submission successfully processed and entity created
 * - failed: Processing failed due to error
 * - hold: Submission held for review (duplicate detected)
 * 
 * Hold status values:
 * - none: No hold status
 * - hold: Currently held for review
 * - approved: Admin approved and entity created
 * - ignored: Admin ignored the submission
 */
if (!$CI->db->table_exists(db_prefix() . 'form_sync_submission_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "form_sync_submission_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` varchar(191) NOT NULL,
  `form_id` varchar(191) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'framer',
  `site_id` varchar(191) DEFAULT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `target_type` enum('lead','customer','estimate_request','ticket') NOT NULL DEFAULT 'customer',
  `status` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `estimate_request_id` int(11) DEFAULT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `hold_status` enum('none','hold','approved','ignored') NOT NULL DEFAULT 'none',
  `hold_reason_type` enum('duplicate','no_mappings','manual_review','none') NOT NULL DEFAULT 'none',
  `duplicate_reason` text DEFAULT NULL,
  `duplicate_entity_type` varchar(50) DEFAULT NULL,
  `duplicate_entity_id` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `submission_data` longtext DEFAULT NULL,
  `datecreated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_id` (`submission_id`),
  KEY `form_id` (`form_id`),
  KEY `status` (`status`),
  KEY `target_type` (`target_type`),
  KEY `hold_status` (`hold_status`),
  KEY `provider` (`provider`),
  KEY `site_id` (`site_id`),
  KEY `customer_id` (`customer_id`),
  KEY `lead_id` (`lead_id`),
  KEY `estimate_request_id` (`estimate_request_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    /**
     * Migration: Update existing form_sync_submission_logs table
     * 
     * This migration runs when updating from an older version of the module.
     * It adds:
     * - provider, site_id, site_name columns (if missing)
     * - hold_reason_type column (if missing)
     * - estimate_request_id and ticket_id columns (if missing)
     * - Updates target_type ENUM to include 'estimate_request' and 'ticket'
     */
    try {
        $columns = $CI->db->list_fields(db_prefix() . 'form_sync_submission_logs');
        
        // Add provider column (use varchar for scalability to 20+ providers)
        if (!in_array('provider', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `provider` varchar(50) NOT NULL DEFAULT "framer" AFTER `form_id`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD KEY `provider` (`provider`)');
        } else {
            // Migrate from enum to varchar if needed
            $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_submission_logs` WHERE Field = "provider"')->row();
            if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                // Convert enum to varchar
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` MODIFY `provider` varchar(50) NOT NULL DEFAULT "framer"');
                log_message('info', '[FormSync] Migrated provider column from enum to varchar in logs table');
            }
        }
        
        // Add site_id column
        if (!in_array('site_id', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `site_id` varchar(191) DEFAULT NULL AFTER `provider`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD KEY `site_id` (`site_id`)');
        }
        
        // Add site_name column
        if (!in_array('site_name', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `site_name` varchar(255) DEFAULT NULL AFTER `site_id`');
        }
        
        // Add hold_reason_type column
        if (!in_array('hold_reason_type', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `hold_reason_type` enum(\'duplicate\',\'no_mappings\',\'manual_review\',\'none\') NOT NULL DEFAULT \'none\' AFTER `hold_status`');
            
            // Update existing records based on their current state
            // Set hold_reason_type='duplicate' where duplicate_reason IS NOT NULL
            $CI->db->query('UPDATE `' . db_prefix() . 'form_sync_submission_logs` SET `hold_reason_type` = \'duplicate\' WHERE `duplicate_reason` IS NOT NULL AND `duplicate_reason` != \'\'');
            
            // Set hold_reason_type='no_mappings' where error_message contains 'No field mappings' and hold_status='hold'
            $CI->db->query('UPDATE `' . db_prefix() . 'form_sync_submission_logs` SET `hold_reason_type` = \'no_mappings\' WHERE `error_message` LIKE \'%No field mappings%\' AND `hold_status` = \'hold\'');
            
            log_message('info', '[FormSync] Added hold_reason_type column and migrated existing records');
        }
        
        // Add estimate_request_id column
        if (!in_array('estimate_request_id', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `estimate_request_id` int(11) DEFAULT NULL AFTER `lead_id`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD KEY `estimate_request_id` (`estimate_request_id`)');
            log_message('info', '[FormSync] Added estimate_request_id column to submission_logs');
        }
        
        // Add ticket_id column
        if (!in_array('ticket_id', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `ticket_id` int(11) DEFAULT NULL AFTER `estimate_request_id`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD KEY `ticket_id` (`ticket_id`)');
            log_message('info', '[FormSync] Added ticket_id column to submission_logs');
        }
        
        // Update target_type ENUM to include estimate_request and ticket
        try {
            $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_submission_logs` WHERE Field = "target_type"')->row();
            if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                // Check if new values are already in the enum
                if (strpos($column_info->Type, 'estimate_request') === false) {
                    $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                    log_message('info', '[FormSync] Updated target_type enum to include estimate_request and ticket in submission_logs');
                }
            }
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Could not update target_type enum in submission_logs: ' . $e->getMessage());
            }
        }
        
        // Check if hold_reason_type column exists and needs manual_review
        if (in_array('hold_reason_type', $columns)) {
            // Column exists, check if enum needs to be updated to include 'manual_review'
            try {
                $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_submission_logs` WHERE Field = "hold_reason_type"')->row();
                if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                    // Check if 'manual_review' is already in the enum
                    if (strpos($column_info->Type, 'manual_review') === false) {
                        // Update enum to include 'manual_review'
                        $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` MODIFY `hold_reason_type` enum(\'duplicate\',\'no_mappings\',\'manual_review\',\'none\') NOT NULL DEFAULT \'none\'');
                        log_message('info', '[FormSync] Updated hold_reason_type enum to include \'manual_review\'');
                    }
                }
            } catch (Exception $e) {
                // If enum value already exists or update fails, log and continue
                if (ENVIRONMENT === 'development') {
                    log_message('debug', '[FormSync] Could not update hold_reason_type enum (may already be updated): ' . $e->getMessage());
                }
            }
        }
    } catch (Exception $e) {
        log_message('error', 'FormSync: Error adding provider/site columns to logs - ' . $e->getMessage());
    }
}

/**
 * Create form_sync_form_configurations table
 * 
 * Stores configuration for each form integration.
 * Allows multiple forms to be configured with different target types.
 * 
 * Key features:
 * - One form can be configured for both leads and customers (different configs)
 * - Each configuration can specify customer group or lead source
 * - Enabled/disabled per configuration
 * - Multi-provider support (Framer, Webflow)
 * - Multi-site support with site_id and site_name
 */
if (!$CI->db->table_exists(db_prefix() . 'form_sync_form_configurations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "form_sync_form_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` varchar(191) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'framer',
  `site_id` varchar(191) DEFAULT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `target_type` enum('lead','customer','estimate_request','ticket') NOT NULL DEFAULT 'customer',
  `customer_group_id` int(11) DEFAULT NULL,
  `lead_source_id` int(11) DEFAULT NULL,
  `estimate_request_status_id` int(11) DEFAULT NULL,
  `estimate_request_assigned_id` int(11) DEFAULT NULL,
  `ticket_department_id` int(11) DEFAULT NULL,
  `ticket_priority` int(11) DEFAULT NULL,
  `perfex_form_id` int(11) DEFAULT NULL,
  `webhook_secret` text DEFAULT NULL,
  `webhook_url` text DEFAULT NULL,
  `custom_provider_settings` text DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `datecreated` datetime NOT NULL,
  `dateupdated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_id_target_type` (`form_id`, `target_type`),
  KEY `form_id` (`form_id`),
  KEY `target_type` (`target_type`),
  KEY `enabled` (`enabled`),
  KEY `provider` (`provider`),
  KEY `site_id` (`site_id`),
  KEY `provider_site` (`provider`, `site_id`),
  KEY `customer_group_id` (`customer_group_id`),
  KEY `lead_source_id` (`lead_source_id`),
  KEY `perfex_form_id` (`perfex_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
} else {
    /**
     * Migration: Update existing form_sync_form_configurations table
     * 
     * This migration runs when updating from an older version of the module.
     * It adds:
     * - provider, site_id, site_name columns (if missing)
     * - estimate_request_status_id, estimate_request_assigned_id columns (if missing)
     * - ticket_department_id, ticket_priority columns (if missing)
     * - webhook_secret, webhook_url columns (if missing)
     * - Updates target_type ENUM to include 'estimate_request' and 'ticket'
     */
    try {
        $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        
        // Add provider column (use varchar for scalability to 20+ providers)
        if (!in_array('provider', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `provider` varchar(50) NOT NULL DEFAULT "framer" AFTER `form_name`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD KEY `provider` (`provider`)');
        } else {
            // Migrate from enum to varchar if needed
            $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_form_configurations` WHERE Field = "provider"')->row();
            if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                // Convert enum to varchar
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` MODIFY `provider` varchar(50) NOT NULL DEFAULT "framer"');
                log_message('info', '[FormSync] Migrated provider column from enum to varchar');
            }
        }
        
        // Add site_id column
        if (!in_array('site_id', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `site_id` varchar(191) DEFAULT NULL AFTER `provider`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD KEY `site_id` (`site_id`)');
        }
        
        // Add site_name column
        if (!in_array('site_name', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `site_name` varchar(255) DEFAULT NULL AFTER `site_id`');
        }
        
        // Add estimate_request_status_id column
        if (!in_array('estimate_request_status_id', $columns)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `estimate_request_status_id` int(11) DEFAULT NULL AFTER `lead_source_id`');
            log_message('info', '[FormSync] Added estimate_request_status_id column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add estimate_request_assigned_id column
        if (!in_array('estimate_request_assigned_id', $columns)) {
            $after_col = in_array('estimate_request_status_id', $columns) ? 'estimate_request_status_id' : 'lead_source_id';
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `estimate_request_assigned_id` int(11) DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added estimate_request_assigned_id column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add ticket_department_id column
        if (!in_array('ticket_department_id', $columns)) {
            $after_col = in_array('estimate_request_assigned_id', $columns) ? 'estimate_request_assigned_id' : (in_array('estimate_request_status_id', $columns) ? 'estimate_request_status_id' : 'lead_source_id');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `ticket_department_id` int(11) DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added ticket_department_id column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add ticket_priority column
        if (!in_array('ticket_priority', $columns)) {
            $after_col = in_array('ticket_department_id', $columns) ? 'ticket_department_id' : (in_array('estimate_request_assigned_id', $columns) ? 'estimate_request_assigned_id' : (in_array('estimate_request_status_id', $columns) ? 'estimate_request_status_id' : 'lead_source_id'));
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `ticket_priority` int(11) DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added ticket_priority column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add webhook_secret column (if not already added in previous migrations)
        if (!in_array('webhook_secret', $columns)) {
            $after_col = in_array('ticket_priority', $columns) ? 'ticket_priority' : (in_array('ticket_department_id', $columns) ? 'ticket_department_id' : (in_array('estimate_request_assigned_id', $columns) ? 'estimate_request_assigned_id' : (in_array('estimate_request_status_id', $columns) ? 'estimate_request_status_id' : 'lead_source_id')));
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `webhook_secret` text DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added webhook_secret column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add webhook_url column (if not already added in previous migrations)
        if (!in_array('webhook_url', $columns)) {
            $after_col = in_array('webhook_secret', $columns) ? 'webhook_secret' : (in_array('ticket_priority', $columns) ? 'ticket_priority' : 'lead_source_id');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `webhook_url` text DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added webhook_url column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add perfex_form_id column
        if (!in_array('perfex_form_id', $columns)) {
            $after_col = in_array('ticket_priority', $columns) ? 'ticket_priority' : (in_array('ticket_department_id', $columns) ? 'ticket_department_id' : (in_array('estimate_request_assigned_id', $columns) ? 'estimate_request_assigned_id' : (in_array('estimate_request_status_id', $columns) ? 'estimate_request_status_id' : 'lead_source_id')));
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `perfex_form_id` int(11) DEFAULT NULL AFTER `' . $after_col . '`');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD KEY `perfex_form_id` (`perfex_form_id`)');
            log_message('info', '[FormSync] Added perfex_form_id column to form_configurations');
            // Re-fetch columns after adding
            $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
        }
        
        // Add custom_provider_settings column (for Universal provider and future custom providers)
        if (!in_array('custom_provider_settings', $columns)) {
            $after_col = in_array('webhook_url', $columns) ? 'webhook_url' : (in_array('webhook_secret', $columns) ? 'webhook_secret' : 'perfex_form_id');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `custom_provider_settings` text DEFAULT NULL AFTER `' . $after_col . '`');
            log_message('info', '[FormSync] Added custom_provider_settings column to form_configurations');
        }
        
        // Update target_type ENUM to include estimate_request and ticket
        try {
            $column_info = $CI->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_form_configurations` WHERE Field = "target_type"')->row();
            if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                // Check if new values are already in the enum
                if (strpos($column_info->Type, 'estimate_request') === false) {
                    $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                    log_message('info', '[FormSync] Updated target_type enum to include estimate_request and ticket in form_configurations');
                }
            }
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Could not update target_type enum in form_configurations: ' . $e->getMessage());
            }
        }
        
        // Add composite index for provider_site if it doesn't exist
        $indexes_result = $CI->db->query('SHOW INDEX FROM `' . db_prefix() . 'form_sync_form_configurations`');
        if ($indexes_result) {
            $indexes = $indexes_result->result_array();
            $index_names = array_column($indexes, 'Key_name');
            
            if (!in_array('provider_site', $index_names)) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD KEY `provider_site` (`provider`, `site_id`)');
            }
        }
    } catch (Exception $e) {
        log_message('error', 'FormSync: Error adding webhook columns - ' . $e->getMessage());
    }
}

// ============================================================================
// DEFAULT OPTIONS SETUP
// ============================================================================

/**
 * Create default module options
 * 
 * These options are stored in the Perfex CRM options table and can be
 * accessed/modified through the Settings page.
 */

// API Key - API key for authentication
if (!get_option('form_sync_api_key')) {
    add_option('form_sync_api_key', '', 0);
}

// Form ID - Legacy option, kept for backward compatibility
if (!get_option('form_sync_form_id')) {
    add_option('form_sync_form_id', '', 0);
}

// Sync Method - Always use polling method
if (!get_option('form_sync_sync_method')) {
    add_option('form_sync_sync_method', 'polling', 1);
} else {
    // Update existing installations to use polling
    update_option('form_sync_sync_method', 'polling');
}

// Polling Interval - How often to check for new submissions (in minutes)
if (!get_option('form_sync_polling_interval')) {
    add_option('form_sync_polling_interval', '5', 1); // Default: 5 minutes
}

// Enabled - Master switch for the integration
if (!get_option('form_sync_enabled')) {
    add_option('form_sync_enabled', '0', 1);
}

// Cron Enabled - Whether automatic polling via cron is enabled
if (!get_option('form_sync_cron_enabled')) {
    add_option('form_sync_cron_enabled', '0', 1);
}

// Framer Enabled - Master switch for Framer webhook integration
if (!get_option('form_sync_framer_enabled')) {
    add_option('form_sync_framer_enabled', '0', 1);
}

// Webflow Enabled - Master switch for Webflow webhook integration
if (!get_option('form_sync_webflow_enabled')) {
    add_option('form_sync_webflow_enabled', '0', 1);
}

// Rate Limiting Options
if (!get_option('form_sync_rate_limit_max_requests')) {
    add_option('form_sync_rate_limit_max_requests', '100', 1); // 100 requests per hour
}

if (!get_option('form_sync_rate_limit_time_window')) {
    add_option('form_sync_rate_limit_time_window', '3600', 1); // 1 hour in seconds
}

// Estimate/Ticket Migration Flag - Mark as done for fresh installs (schema already includes these)
// This prevents unnecessary auto-migration checks on fresh installations
if (!get_option('form_sync_estimate_ticket_migrated')) {
    add_option('form_sync_estimate_ticket_migrated', '1', 0);
}

// ============================================================================
// INSTALLATION COMPLETE
// ============================================================================

log_message('info', '[FormSync] Installation completed successfully');

