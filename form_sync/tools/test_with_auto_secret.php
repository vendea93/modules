<?php
/**
 * Test Multiple Leads with Auto-Detected Secret
 * 
 * This script automatically reads the webhook secret from the form configuration
 * and uses it to send test webhooks.
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

// Parse command line arguments
$options = getopt('', [
    'form-id:',
    'url:',
    'count:',
    'help'
]);

// Show help
if (isset($options['help']) || !isset($options['form-id'])) {
    echo "Test Multiple Leads with Auto-Detected Secret\n";
    echo "=============================================\n\n";
    echo "Usage: php test_with_auto_secret.php --form-id=FORM_ID [options]\n\n";
    echo "Required:\n";
    echo "  --form-id=ID          Form ID from your form configuration\n\n";
    echo "Options:\n";
    echo "  --url=URL             Base URL (default: http://localhost:8080)\n";
    echo "  --count=N             Number of test submissions (default: 10)\n";
    echo "  --help                 Show this help\n\n";
    exit(0);
}

// Get parameters
$form_id = $options['form-id'];
$base_url = isset($options['url']) ? rtrim($options['url'], '/') : 'http://localhost:8080';
$count = isset($options['count']) ? (int)$options['count'] : 10;

// Connect to database to get webhook secret
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
$forms_table = $prefix . 'form_sync_form_configurations';

// Get form configuration
$result = $mysqli->query("SELECT webhook_secret, enabled FROM `{$forms_table}` WHERE form_id = '" . $mysqli->real_escape_string($form_id) . "' AND provider = 'framer' LIMIT 1");

if (!$result || $result->num_rows === 0) {
    die("Error: Form configuration not found for form_id: {$form_id}\n");
}

$form_config = $result->fetch_assoc();
$webhook_secret = $form_config['webhook_secret'];
$enabled = $form_config['enabled'];

$mysqli->close();

if (!$enabled) {
    die("Error: Form configuration is disabled.\n");
}

// Test data templates
$test_data_templates = [
    ['name' => 'Alice Thompson', 'email' => 'alice.thompson@enterprise.com', 'phone' => '+1-555-1001', 'company' => 'Enterprise Solutions Ltd', 'message' => 'Interested in enterprise CRM solutions'],
    ['name' => 'Bob Martinez', 'email' => 'bob.martinez@techcorp.io', 'phone' => '+1-555-1002', 'company' => 'TechCorp Innovations', 'message' => 'Looking for API integration'],
    ['name' => 'Carol White', 'email' => 'carol.white@designstudio.com', 'phone' => '+1-555-1003', 'company' => 'Creative Design Studio', 'message' => 'Need CRM for client management'],
    ['name' => 'Daniel Lee', 'email' => 'daniel.lee@consulting.com', 'phone' => '+1-555-1004', 'company' => 'Strategic Consulting', 'message' => 'Interested in lead management'],
    ['name' => 'Emma Garcia', 'email' => 'emma.garcia@retail.com', 'phone' => '+1-555-1005', 'company' => 'Retail Solutions Inc', 'message' => 'Looking for customer tracking'],
    ['name' => 'Frank Miller', 'email' => 'frank.miller@personal.com', 'phone' => '+1-555-2001', 'message' => 'Personal inquiry about services'],
    ['name' => 'Grace Taylor', 'email' => 'grace.taylor@gmail.com', 'phone' => '+1-555-2002', 'message' => 'Interested in learning more'],
    ['name' => 'Henry Clark', 'email' => 'henry.clark@outlook.com', 'phone' => '+1-555-2003', 'message' => 'Need help with implementation'],
    ['name' => 'Isabella Rodriguez', 'email' => 'isabella.rodriguez@empresa.es', 'phone' => '+34-91-1234567', 'company' => 'Empresa Española', 'message' => 'Interesado en servicios CRM'],
    ['name' => 'James O\'Connor', 'email' => 'james.oconnor@company.ie', 'phone' => '+353-1-2345678', 'company' => 'Irish Business Solutions', 'message' => 'Looking for CRM solution'],
];

$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Test Multiple Leads (Auto-Detected Secret)            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";
echo "Form ID:        {$form_id}\n";
echo "Webhook URL:    {$webhook_url}\n";
echo "Count:          {$count}\n";
echo "Has Secret:     " . (!empty($webhook_secret) ? 'Yes (auto-detected)' : 'No') . "\n\n";

if (empty($webhook_secret)) {
    echo "⚠ Warning: No webhook secret configured. Webhook may fail if signature verification is required.\n\n";
}

$success_count = 0;
$fail_count = 0;

// Send webhooks
for ($i = 1; $i <= $count; $i++) {
    $template_index = ($i - 1) % count($test_data_templates);
    $form_data = $test_data_templates[$template_index];
    
    // Make email unique
    $email_parts = explode('@', $form_data['email']);
    $timestamp = time();
    $form_data['email'] = $email_parts[0] . '+' . $timestamp . '_' . $i . '@' . $email_parts[1];
    
    $submission_id = 'test_' . time() . '_' . $i . '_' . rand(1000, 9999);
    $payload = json_encode($form_data);
    
    // Generate signature if secret exists
    $signature = null;
    if (!empty($webhook_secret)) {
        $hmac = hash_hmac('sha256', $payload . $submission_id, $webhook_secret, true);
        $signature = 'sha256=' . bin2hex($hmac);
    }
    
    $headers = [
        'Content-Type: application/json',
        'Framer-Webhook-Submission-Id: ' . $submission_id
    ];
    
    if ($signature) {
        $headers[] = 'Framer-Signature: ' . $signature;
    }
    
    echo "[{$i}/{$count}] {$form_data['name']} ({$form_data['email']})... ";
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ Error: {$error}\n";
        $fail_count++;
    } else {
        $response_body = substr($response, $header_size);
        $json_response = json_decode($response_body, true);
        
        if ($http_code === 200) {
            if (isset($json_response['error'])) {
                echo "⚠ {$json_response['error']}\n";
                $fail_count++;
            } else {
                echo "✓ Success\n";
                $success_count++;
            }
        } else {
            echo "✗ Failed (HTTP {$http_code})\n";
            $fail_count++;
        }
    }
    
    if ($i < $count) {
        usleep(300000);
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "Summary: {$success_count} successful, {$fail_count} failed out of {$count} total\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "Refresh your Leads page to see the new leads!\n\n";

exit($fail_count > 0 ? 1 : 0);











