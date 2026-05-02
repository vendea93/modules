<?php
/**
 * Validate FormSync Installation
 * 
 * This script validates that the FormSync module is properly installed
 * and all database tables/columns exist.
 * 
 * Usage: php validate_installation.php
 */

// Bootstrap CodeIgniter
define('BASEPATH', true);
require_once(__DIR__ . '/../../index.php');

$CI = &get_instance();
$CI->load->database();

echo "FormSync Installation Validation\n";
echo "================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check if module is active
$CI->db->where('module_name', 'form_sync');
$module = $CI->db->get(db_prefix() . 'modules')->row();

if (!$module) {
    $errors[] = "Module not found in database. Please activate the module first.";
} elseif (!$module->active) {
    $warnings[] = "Module is installed but not active. Please activate it in Modules settings.";
} else {
    $success[] = "Module is installed and active (version: {$module->installed_version})";
}

// Check required tables
$required_tables = [
    'form_sync_config',
    'form_sync_field_mappings',
    'form_sync_submission_logs',
    'form_sync_form_configurations'
];

foreach ($required_tables as $table) {
    $full_table_name = db_prefix() . $table;
    if ($CI->db->table_exists($full_table_name)) {
        $success[] = "Table '{$table}' exists";
    } else {
        $errors[] = "Table '{$table}' is missing";
    }
}

// Check critical columns in submission_logs
if ($CI->db->table_exists(db_prefix() . 'form_sync_submission_logs')) {
    $columns = $CI->db->list_fields(db_prefix() . 'form_sync_submission_logs');
    $required_columns = [
        'id',
        'submission_id',
        'form_id',
        'provider',
        'site_id',
        'site_name',
        'target_type',
        'status',
        'customer_id',
        'lead_id',
        'hold_status',
        'hold_reason_type',
        'duplicate_reason',
        'error_message',
        'submission_data',
        'datecreated'
    ];
    
    foreach ($required_columns as $column) {
        if (in_array($column, $columns)) {
            $success[] = "Column '{$column}' exists in submission_logs";
        } else {
            $errors[] = "Column '{$column}' is missing in submission_logs";
        }
    }
    
    // Check hold_reason_type enum values
    $column_info = $CI->db->query("SHOW COLUMNS FROM `" . db_prefix() . "form_sync_submission_logs` WHERE Field = 'hold_reason_type'")->row();
    if ($column_info) {
        if (strpos($column_info->Type, 'manual_review') !== false) {
            $success[] = "Column 'hold_reason_type' includes 'manual_review' value";
        } else {
            $warnings[] = "Column 'hold_reason_type' may need update to include 'manual_review'";
        }
    }
}

// Check critical columns in form_configurations
if ($CI->db->table_exists(db_prefix() . 'form_sync_form_configurations')) {
    $columns = $CI->db->list_fields(db_prefix() . 'form_sync_form_configurations');
    $required_columns = [
        'id',
        'form_id',
        'form_name',
        'provider',
        'site_id',
        'site_name',
        'target_type',
        'customer_group_id',
        'lead_source_id',
        'webhook_secret',
        'webhook_url',
        'enabled',
        'datecreated',
        'dateupdated'
    ];
    
    foreach ($required_columns as $column) {
        if (in_array($column, $columns)) {
            $success[] = "Column '{$column}' exists in form_configurations";
        } else {
            $errors[] = "Column '{$column}' is missing in form_configurations";
        }
    }
}

// Check module options
$required_options = [
    'form_sync_api_key',
    'form_sync_form_id',
    'form_sync_sync_method',
    'form_sync_polling_interval',
    'form_sync_enabled',
    'form_sync_cron_enabled',
    'form_sync_framer_enabled',
    'form_sync_webflow_enabled'
];

foreach ($required_options as $option) {
    $value = get_option($option);
    if ($value !== false) {
        $success[] = "Option '{$option}' exists";
    } else {
        $warnings[] = "Option '{$option}' is missing (will be created on next activation)";
    }
}

// Summary
echo "Results:\n";
echo "--------\n\n";

if (count($success) > 0) {
    echo "✓ Success (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  - {$msg}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠ Warnings (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  - {$msg}\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "✗ Errors (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  - {$msg}\n";
    }
    echo "\n";
    echo "Action required: Please deactivate and reactivate the module to run migrations.\n";
    exit(1);
} else {
    echo "✓ Installation validation passed!\n";
    echo "\n";
    echo "The module is ready to use. You can now:\n";
    echo "1. Configure forms in FormSync > Form Configurations\n";
    echo "2. Set up field mappings\n";
    echo "3. Test webhooks using the test scripts\n";
    exit(0);
}











