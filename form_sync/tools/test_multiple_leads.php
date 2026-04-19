<?php
/**
 * Test Multiple Lead Creation via Framer Webhook
 * 
 * This script sends multiple test webhook requests with varied sample data
 * to verify automatic lead creation is working correctly.
 * 
 * Usage:
 *   php test_multiple_leads.php --form-id=CON1 [--url=http://localhost:8080] [--count=10]
 */

// Parse command line arguments
$options = getopt('', [
    'form-id:',
    'url:',
    'count:',
    'secret:',
    'help'
]);

// Show help
if (isset($options['help']) || !isset($options['form-id'])) {
    echo "Test Multiple Lead Creation via Framer Webhook\n";
    echo "==============================================\n\n";
    echo "Usage: php test_multiple_leads.php --form-id=FORM_ID [options]\n\n";
    echo "Required:\n";
    echo "  --form-id=ID          Form ID from your form configuration\n\n";
    echo "Options:\n";
    echo "  --url=URL             Base URL (default: http://localhost:8080)\n";
    echo "  --count=N             Number of test submissions (default: 10)\n";
    echo "  --secret=SECRET        Webhook secret (optional, if configured)\n";
    echo "  --help                 Show this help\n\n";
    echo "Example:\n";
    echo "  php test_multiple_leads.php --form-id=CON1 --count=10\n\n";
    exit(0);
}

// Get parameters
$form_id = $options['form-id'];
$base_url = isset($options['url']) ? rtrim($options['url'], '/') : 'http://localhost:8080';
$webhook_secret = isset($options['secret']) ? $options['secret'] : null;
$count = isset($options['count']) ? (int)$options['count'] : 10;

// Varied test data templates
$test_data_templates = [
    // Business leads with company
    [
        'name' => 'Alice Thompson',
        'email' => 'alice.thompson@enterprise.com',
        'phone' => '+1-555-1001',
        'company' => 'Enterprise Solutions Ltd',
        'message' => 'Interested in enterprise CRM solutions for our team of 500+ employees'
    ],
    [
        'name' => 'Bob Martinez',
        'email' => 'bob.martinez@techcorp.io',
        'phone' => '+1-555-1002',
        'company' => 'TechCorp Innovations',
        'message' => 'Looking for API integration capabilities'
    ],
    [
        'name' => 'Carol White',
        'email' => 'carol.white@designstudio.com',
        'phone' => '+1-555-1003',
        'company' => 'Creative Design Studio',
        'message' => 'Need a CRM for managing client relationships'
    ],
    [
        'name' => 'Daniel Lee',
        'email' => 'daniel.lee@consulting.com',
        'phone' => '+1-555-1004',
        'company' => 'Strategic Consulting Group',
        'message' => 'Interested in lead management features'
    ],
    [
        'name' => 'Emma Garcia',
        'email' => 'emma.garcia@retail.com',
        'phone' => '+1-555-1005',
        'company' => 'Retail Solutions Inc',
        'message' => 'Looking for customer tracking system'
    ],
    // Personal leads without company
    [
        'name' => 'Frank Miller',
        'email' => 'frank.miller@personal.com',
        'phone' => '+1-555-2001',
        'message' => 'Personal inquiry about your services'
    ],
    [
        'name' => 'Grace Taylor',
        'email' => 'grace.taylor@gmail.com',
        'phone' => '+1-555-2002',
        'message' => 'Interested in learning more about your offerings'
    ],
    [
        'name' => 'Henry Clark',
        'email' => 'henry.clark@outlook.com',
        'phone' => '+1-555-2003',
        'message' => 'Need help with implementation'
    ],
    // International leads
    [
        'name' => 'Isabella Rodriguez',
        'email' => 'isabella.rodriguez@empresa.es',
        'phone' => '+34-91-1234567',
        'company' => 'Empresa Española S.L.',
        'message' => 'Interesado en sus servicios de CRM'
    ],
    [
        'name' => 'James O\'Connor',
        'email' => 'james.oconnor@company.ie',
        'phone' => '+353-1-2345678',
        'company' => 'Irish Business Solutions',
        'message' => 'Looking for CRM solution for our Dublin office'
    ],
    // Edge cases
    [
        'name' => 'Karen Smith-Johnson',
        'email' => 'karen.smith.johnson@test.com',
        'phone' => '+1-555-9999',
        'company' => 'Smith & Johnson LLC',
        'message' => 'Testing special characters: <>&"\''
    ],
    [
        'name' => 'Larry Chen',
        'email' => 'larry.chen@startup.co',
        'phone' => '+1-555-8888',
        'company' => 'Startup Co.',
        'message' => 'Early stage startup looking for affordable CRM'
    ]
];

$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Test Multiple Lead Creation via Framer Webhook       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";
echo "Form ID:        {$form_id}\n";
echo "Webhook URL:    {$webhook_url}\n";
echo "Count:          {$count}\n";
echo "Has Secret:     " . ($webhook_secret ? 'Yes' : 'No') . "\n\n";

$success_count = 0;
$fail_count = 0;
$results = [];

// Send webhooks
for ($i = 1; $i <= $count; $i++) {
    // Select template (cycle through available templates)
    $template_index = ($i - 1) % count($test_data_templates);
    $form_data = $test_data_templates[$template_index];
    
    // Add unique identifier to email to avoid duplicates
    if (isset($form_data['email'])) {
        $email_parts = explode('@', $form_data['email']);
        $timestamp = time();
        $form_data['email'] = $email_parts[0] . '+' . $timestamp . '_' . $i . '@' . $email_parts[1];
    }
    
    // Generate unique submission ID
    $submission_id = 'test_' . time() . '_' . $i . '_' . rand(1000, 9999);
    
    // Convert to JSON
    $payload = json_encode($form_data);
    
    // Generate signature if secret is provided
    $signature = null;
    if (!empty($webhook_secret)) {
        $hmac = hash_hmac('sha256', $payload . $submission_id, $webhook_secret, true);
        $signature = 'sha256=' . bin2hex($hmac);
    }
    
    // Prepare headers
    $headers = [
        'Content-Type: application/json',
        'Framer-Webhook-Submission-Id: ' . $submission_id
    ];
    
    if ($signature) {
        $headers[] = 'Framer-Signature: ' . $signature;
    }
    
    // Send request
    echo "[{$i}/{$count}] Sending: " . $form_data['name'] . " (" . $form_data['email'] . ")... ";
    
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
        $results[] = ['index' => $i, 'name' => $form_data['name'], 'status' => 'error', 'message' => $error];
    } else {
        $response_body = substr($response, $header_size);
        $json_response = json_decode($response_body, true);
        
        if ($http_code === 200) {
            if (isset($json_response['error'])) {
                echo "⚠ " . $json_response['error'] . "\n";
                $fail_count++;
                $results[] = ['index' => $i, 'name' => $form_data['name'], 'status' => 'error', 'message' => $json_response['error']];
            } else {
                echo "✓ Success\n";
                $success_count++;
                $results[] = ['index' => $i, 'name' => $form_data['name'], 'status' => 'success'];
            }
        } else {
            echo "✗ Failed (HTTP {$http_code})\n";
            $fail_count++;
            $results[] = ['index' => $i, 'name' => $form_data['name'], 'status' => 'failed', 'http_code' => $http_code];
        }
    }
    
    // Small delay between requests
    if ($i < $count) {
        usleep(300000); // 0.3 second delay
    }
}

// Summary
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Summary\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Total sent:     {$count}\n";
echo "Successful:     {$success_count}\n";
echo "Failed:         {$fail_count}\n";
echo "Success rate:   " . ($count > 0 ? round(($success_count / $count) * 100, 2) : 0) . "%\n\n";

if ($fail_count > 0) {
    echo "Failed submissions:\n";
    foreach ($results as $result) {
        if ($result['status'] !== 'success') {
            echo "  - [{$result['index']}] {$result['name']}: {$result['status']}";
            if (isset($result['message'])) {
                echo " - {$result['message']}";
            }
            echo "\n";
        }
    }
    echo "\n";
}

echo "Next steps:\n";
echo "1. Refresh your Leads page in the admin panel\n";
echo "2. You should see {$success_count} new leads in the table\n";
echo "3. Check FormSync > Logs to see detailed submission status\n";
echo "4. Verify all leads have proper status and are visible\n\n";

exit($fail_count > 0 ? 1 : 0);











