<?php
/**
 * Fix existing leads that don't have a status set
 */

// Bootstrap minimal CodeIgniter
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
define('BASEPATH', true);
define('APPPATH', dirname(dirname(dirname(__DIR__))) . '/application/');

if (!function_exists('db_prefix')) {
    function db_prefix() {
        return defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
    }
}

require_once APPPATH . 'config/app-config.php';
require_once APPPATH . 'config/database.php';

// Connect to database
$db_config = $db['default'];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$prefix = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$leads_table = $prefix . 'leads';
$statuses_table = $prefix . 'leads_status';
$options_table = $prefix . 'options';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Fix Leads Without Status                              ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Get default status from options
$default_status = null;
$result = $mysqli->query("SELECT value FROM `{$options_table}` WHERE name = 'leads_default_status'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $default_status = $row['value'];
}

// Get first available status if no default
$first_status_id = null;
$result = $mysqli->query("SELECT id FROM `{$statuses_table}` ORDER BY id ASC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $first_status_id = $row['id'];
}

$status_to_use = $default_status ?: $first_status_id;

if (!$status_to_use) {
    echo "⚠ Error: No lead statuses found in the database!\n";
    echo "   Please create at least one lead status first.\n";
    exit(1);
}

echo "Using status ID: " . $status_to_use . ($default_status ? " (default)" : " (first available)") . "\n\n";

// Find leads without status
$result = $mysqli->query("SELECT id, name, email, status FROM `{$leads_table}` WHERE (status IS NULL OR status = '' OR status = '0')");
if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " lead(s) without status:\n\n";
    
    $fixed = 0;
    while ($row = $result->fetch_assoc()) {
        echo "  Fixing Lead ID: " . $row['id'] . " - " . $row['name'] . " (" . ($row['email'] ?: 'no email') . ")\n";
        
        $update_query = "UPDATE `{$leads_table}` SET status = " . intval($status_to_use) . " WHERE id = " . intval($row['id']);
        if ($mysqli->query($update_query)) {
            $fixed++;
        } else {
            echo "    ✗ Error: " . $mysqli->error . "\n";
        }
    }
    
    echo "\n✓ Fixed " . $fixed . " lead(s)\n";
} else {
    echo "✓ All leads already have a status set.\n";
}

$mysqli->close();
echo "\nDone!\n";











