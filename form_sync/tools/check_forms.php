<?php
/**
 * Quick script to check configured form IDs
 */

// Bootstrap CodeIgniter
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
define('BASEPATH', true);
define('APPPATH', dirname(dirname(dirname(__DIR__))) . '/application/');
define('SYSTEMPATH', dirname(dirname(dirname(__DIR__))) . '/system/');
define('VIEWPATH', APPPATH . 'views/');

// Define db_prefix function before loading database config
if (!function_exists('db_prefix')) {
    function db_prefix() {
        return defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
    }
}

// Load config
require_once APPPATH . 'config/app-config.php';
require_once APPPATH . 'config/database.php';

// Connect to database
$db = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$prefix = defined('APP_DB_PREFIX') ? APP_DB_PREFIX : 'tbl';
$table = $prefix . 'form_sync_form_configurations';

$result = $db->query("SELECT form_id, form_name, provider, target_type, enabled FROM {$table} ORDER BY form_id");

echo "Configured Form IDs:\n";
echo "===================\n\n";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Form ID: " . $row['form_id'] . "\n";
        echo "  Name: " . $row['form_name'] . "\n";
        echo "  Provider: " . $row['provider'] . "\n";
        echo "  Target: " . $row['target_type'] . "\n";
        echo "  Enabled: " . ($row['enabled'] ? 'Yes' : 'No') . "\n";
        echo "\n";
    }
} else {
    echo "No form configurations found.\n";
    echo "You need to create a form configuration first.\n";
}

$db->close();

