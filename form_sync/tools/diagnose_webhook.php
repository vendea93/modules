<?php
/**
 * Diagnostic script to check webhook setup and lead creation
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

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     FormSync Webhook Diagnostic Tool                    ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// 1. Check form configurations
echo "1. FORM CONFIGURATIONS\n";
echo "─────────────────────────────────────────────────────────────\n";
$forms_table = $prefix . 'form_sync_form_configurations';
$result = $mysqli->query("SELECT * FROM `{$forms_table}` ORDER BY form_id");
if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " form configuration(s):\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "  Form ID: " . $row['form_id'] . "\n";
        echo "  Name: " . $row['form_name'] . "\n";
        echo "  Provider: " . $row['provider'] . "\n";
        echo "  Target Type: " . $row['target_type'] . "\n";
        echo "  Enabled: " . ($row['enabled'] ? 'Yes ✓' : 'No ✗') . "\n";
        echo "  Lead Source ID: " . ($row['lead_source_id'] ?: 'Not set') . "\n";
        echo "  Webhook URL: " . ($row['webhook_url'] ?: 'Not set') . "\n";
        echo "\n";
    }
} else {
    echo "⚠ No form configurations found!\n";
    echo "   You need to create a form configuration first.\n";
    echo "   Go to: Admin > FormSync > Form Configurations\n\n";
}

// 2. Check field mappings
echo "\n2. FIELD MAPPINGS\n";
echo "─────────────────────────────────────────────────────────────\n";
$mappings_table = $prefix . 'form_sync_field_mappings';
if ($mysqli->query("SHOW TABLES LIKE '{$mappings_table}'")->num_rows > 0) {
    $result = $mysqli->query("SELECT form_id, target_type, COUNT(*) as count FROM `{$mappings_table}` GROUP BY form_id, target_type");
    if ($result && $result->num_rows > 0) {
        echo "Field mappings found:\n\n";
        while ($row = $result->fetch_assoc()) {
            echo "  Form ID: " . $row['form_id'] . " (Target: " . $row['target_type'] . ") - " . $row['count'] . " mappings\n";
        }
    } else {
        echo "⚠ No field mappings found!\n";
        echo "   You need to configure field mappings for your forms.\n";
    }
} else {
    echo "⚠ Field mappings table does not exist.\n";
}
echo "\n";

// 3. Check recent submission logs
echo "\n3. RECENT SUBMISSION LOGS (Last 10)\n";
echo "─────────────────────────────────────────────────────────────\n";
$logs_table = $prefix . 'form_sync_submission_logs';
if ($mysqli->query("SHOW TABLES LIKE '{$logs_table}'")->num_rows > 0) {
    $result = $mysqli->query("SELECT * FROM `{$logs_table}` ORDER BY datecreated DESC LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "Found " . $result->num_rows . " recent submission(s):\n\n";
        while ($row = $result->fetch_assoc()) {
            echo "  Submission ID: " . $row['submission_id'] . "\n";
            echo "  Form ID: " . $row['form_id'] . "\n";
            echo "  Provider: " . ($row['provider'] ?: 'framer') . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Target Type: " . $row['target_type'] . "\n";
            echo "  Lead ID: " . ($row['lead_id'] ?: 'Not created') . "\n";
            echo "  Customer ID: " . ($row['customer_id'] ?: 'Not created') . "\n";
            echo "  Error: " . ($row['error_message'] ?: 'None') . "\n";
            echo "  Date: " . $row['datecreated'] . "\n";
            echo "\n";
        }
    } else {
        echo "⚠ No submission logs found.\n";
        echo "   This means no webhooks have been received yet.\n";
    }
} else {
    echo "⚠ Submission logs table does not exist.\n";
}

// 4. Check recent leads
echo "\n4. RECENT LEADS (Last 10)\n";
echo "─────────────────────────────────────────────────────────────\n";
$leads_table = $prefix . 'leads';
if ($mysqli->query("SHOW TABLES LIKE '{$leads_table}'")->num_rows > 0) {
    $result = $mysqli->query("SELECT id, name, email, dateadded, addedfrom, is_public, status FROM `{$leads_table}` ORDER BY dateadded DESC LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "Found " . $result->num_rows . " recent lead(s):\n\n";
        while ($row = $result->fetch_assoc()) {
            echo "  ID: " . $row['id'] . "\n";
            echo "  Name: " . $row['name'] . "\n";
            echo "  Email: " . ($row['email'] ?: 'N/A') . "\n";
            echo "  Added From: " . $row['addedfrom'] . "\n";
            echo "  Is Public: " . $row['is_public'] . "\n";
            echo "  Status: " . ($row['status'] ?: 'Not set') . "\n";
            echo "  Date Added: " . $row['dateadded'] . "\n";
            echo "\n";
        }
    } else {
        echo "⚠ No leads found in the database.\n";
    }
} else {
    echo "⚠ Leads table does not exist.\n";
}

// 5. Check for leads with addedfrom=0 (webhook imports)
echo "\n5. WEBHOOK-IMPORTED LEADS (addedfrom=0)\n";
echo "─────────────────────────────────────────────────────────────\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM `{$leads_table}` WHERE addedfrom = 0");
if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    echo "Total webhook-imported leads: " . $count . "\n";
    
    if ($count > 0) {
        $result2 = $mysqli->query("SELECT id, name, email, is_public, status FROM `{$leads_table}` WHERE addedfrom = 0 ORDER BY dateadded DESC LIMIT 5");
        echo "\nRecent webhook-imported leads:\n";
        while ($row2 = $result2->fetch_assoc()) {
            echo "  ID: " . $row2['id'] . " - " . $row2['name'] . " (" . ($row2['email'] ?: 'no email') . ")";
            echo " - is_public: " . $row2['is_public'] . ", status: " . ($row2['status'] ?: 'not set') . "\n";
        }
    } else {
        echo "⚠ No webhook-imported leads found.\n";
        echo "   This could mean:\n";
        echo "   - No webhooks have been received\n";
        echo "   - Form configurations are missing\n";
        echo "   - Field mappings are not configured\n";
        echo "   - Leads are being created but not visible (check is_public and status)\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "DIAGNOSIS COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n";

$mysqli->close();











