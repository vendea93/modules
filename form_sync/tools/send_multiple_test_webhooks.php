<?php
/**
 * Send Multiple Test Webhooks
 * 
 * This script sends multiple test webhook requests with different data
 * to test the FormSync module end-to-end.
 * 
 * Usage:
 *   php send_multiple_test_webhooks.php --form-id=CON1 --url=http://localhost:8080 --secret=YOUR_SECRET [--count=10]
 * 
 * Options:
 *   --form-id=ID          Form ID (required)
 *   --url=URL             Base URL (default: http://localhost:8080)
 *   --secret=SECRET        Webhook secret (required)
 *   --count=N              Number of test submissions (default: 10)
 *   --help                 Show help
 */

// Parse command line arguments
$options = getopt('', [
    'form-id:',
    'url:',
    'secret:',
    'count:',
    'help'
]);

// Show help
if (isset($options['help']) || !isset($options['form-id']) || !isset($options['secret'])) {
    echo "Send Multiple Test Webhooks\n";
    echo "==========================\n\n";
    echo "Usage: php send_multiple_test_webhooks.php --form-id=FORM_ID --secret=SECRET [options]\n\n";
    echo "Required:\n";
    echo "  --form-id=ID          Form ID from your form configuration\n";
    echo "  --secret=SECRET        Webhook secret for signature\n\n";
    echo "Options:\n";
    echo "  --url=URL             Base URL (default: http://localhost:8080)\n";
    echo "  --count=N              Number of test submissions (default: 10)\n";
    echo "  --help                 Show this help\n\n";
    echo "Examples:\n";
    echo "  php send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123\n";
    echo "  php send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123 --count=20\n";
    echo "  php send_multiple_test_webhooks.php --form-id=CON1 --secret=abc123 --url=http://localhost:3000\n\n";
    exit(0);
}

// Get parameters
$form_id = $options['form-id'];
$base_url = isset($options['url']) ? rtrim($options['url'], '/') : 'http://localhost:8080';
$webhook_secret = $options['secret'];
$count = isset($options['count']) ? (int)$options['count'] : 10;

// Test data templates
$test_data_templates = [
    // Business leads
    [
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'phone' => '+1-555-0101',
        'company' => 'Tech Solutions Inc.',
        'message' => 'Interested in your enterprise solutions'
    ],
    [
        'name' => 'Sarah Johnson',
        'email' => 'sarah.j@business.com',
        'phone' => '+1-555-0102',
        'company' => 'Digital Marketing Pro',
        'message' => 'Looking for CRM integration'
    ],
    [
        'name' => 'Michael Chen',
        'email' => 'mchen@startup.io',
        'phone' => '+1-555-0103',
        'company' => 'Startup Labs',
        'message' => 'Need consultation on lead management'
    ],
    [
        'name' => 'Emily Davis',
        'email' => 'emily.davis@corp.com',
        'phone' => '+1-555-0104',
        'company' => 'Corporate Solutions',
        'message' => 'Interested in bulk pricing'
    ],
    [
        'name' => 'David Wilson',
        'email' => 'dwilson@tech.com',
        'phone' => '+1-555-0105',
        'company' => 'Tech Innovations',
        'message' => 'Requesting demo'
    ],
    // Personal leads
    [
        'name' => 'Robert Brown',
        'email' => 'robert.brown@gmail.com',
        'phone' => '+1-555-0201',
        'message' => 'Personal inquiry about services'
    ],
    [
        'name' => 'Lisa Anderson',
        'email' => 'lisa.anderson@email.com',
        'phone' => '+1-555-0202',
        'message' => 'Looking for freelance opportunities'
    ],
    [
        'name' => 'James Taylor',
        'email' => 'james.t@outlook.com',
        'phone' => '+1-555-0203',
        'message' => 'Interested in learning more'
    ],
    [
        'name' => 'Jennifer Martinez',
        'email' => 'j.martinez@yahoo.com',
        'phone' => '+1-555-0204',
        'message' => 'Need help with implementation'
    ],
    [
        'name' => 'William Garcia',
        'email' => 'w.garcia@mail.com',
        'phone' => '+1-555-0205',
        'message' => 'Question about pricing'
    ],
    // International leads
    [
        'name' => 'Hans Mueller',
        'email' => 'hans.mueller@deutschland.de',
        'phone' => '+49-30-12345678',
        'company' => 'Deutsche Firma GmbH',
        'message' => 'Interesse an Ihren Dienstleistungen'
    ],
    [
        'name' => 'Marie Dubois',
        'email' => 'marie.dubois@france.fr',
        'phone' => '+33-1-23456789',
        'company' => 'Entreprise Française',
        'message' => 'Intéressé par vos services'
    ],
    [
        'name' => 'Yuki Tanaka',
        'email' => 'yuki.tanaka@japan.jp',
        'phone' => '+81-3-1234-5678',
        'company' => 'Japanese Corporation',
        'message' => 'サービスについてお問い合わせ'
    ],
    // Edge cases
    [
        'name' => 'Test User',
        'email' => 'test' . time() . '@example.com',
        'phone' => '+1-555-9999',
        'message' => 'Test submission with timestamp'
    ],
    [
        'name' => 'Special Characters Test',
        'email' => 'special@test.com',
        'phone' => '+1-555-8888',
        'company' => 'Company & Co. (Ltd.)',
        'message' => 'Testing special chars: <>&"\''
    ]
];

$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "Send Multiple Test Webhooks\n";
echo "==========================\n\n";
echo "Form ID:        {$form_id}\n";
echo "Webhook URL:    {$webhook_url}\n";
echo "Count:          {$count}\n";
echo "Has Secret:     Yes\n\n";

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
        $form_data['email'] = $email_parts[0] . '+' . time() . '_' . $i . '@' . $email_parts[1];
    }
    
    // Generate unique submission ID
    $submission_id = 'test_' . time() . '_' . $i . '_' . rand(1000, 9999);
    
    // Convert to JSON
    $payload = json_encode($form_data);
    
    // Generate signature
    $hmac = hash_hmac('sha256', $payload . $submission_id, $webhook_secret, true);
    $signature = 'sha256=' . bin2hex($hmac);
    
    // Prepare headers
    $headers = [
        'Content-Type: application/json',
        'Framer-Webhook-Submission-Id: ' . $submission_id,
        'Framer-Signature: ' . $signature
    ];
    
    // Send request
    echo "[{$i}/{$count}] Sending webhook for: " . ($form_data['name'] ?? 'Unknown') . " (" . ($form_data['email'] ?? 'no email') . ")... ";
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "✗ Error: {$error}\n";
        $fail_count++;
        $results[] = [
            'index' => $i,
            'name' => $form_data['name'] ?? 'Unknown',
            'email' => $form_data['email'] ?? 'N/A',
            'status' => 'error',
            'message' => $error
        ];
    } elseif ($http_code === 200) {
        echo "✓ Success (HTTP {$http_code})\n";
        $success_count++;
        $results[] = [
            'index' => $i,
            'name' => $form_data['name'] ?? 'Unknown',
            'email' => $form_data['email'] ?? 'N/A',
            'status' => 'success',
            'http_code' => $http_code
        ];
    } else {
        echo "✗ Failed (HTTP {$http_code})\n";
        $fail_count++;
        $results[] = [
            'index' => $i,
            'name' => $form_data['name'] ?? 'Unknown',
            'email' => $form_data['email'] ?? 'N/A',
            'status' => 'failed',
            'http_code' => $http_code,
            'response' => substr($response, 0, 200)
        ];
    }
    
    // Small delay between requests to avoid overwhelming the server
    if ($i < $count) {
        usleep(500000); // 0.5 second delay
    }
}

// Summary
echo "\n";
echo "========================================\n";
echo "Summary\n";
echo "========================================\n";
echo "Total sent:     {$count}\n";
echo "Successful:     {$success_count}\n";
echo "Failed:         {$fail_count}\n";
echo "Success rate:   " . ($count > 0 ? round(($success_count / $count) * 100, 2) : 0) . "%\n\n";

if ($fail_count > 0) {
    echo "Failed submissions:\n";
    foreach ($results as $result) {
        if ($result['status'] !== 'success') {
            echo "  - [{$result['index']}] {$result['name']} ({$result['email']}): {$result['status']}\n";
            if (isset($result['message'])) {
                echo "    Error: {$result['message']}\n";
            }
            if (isset($result['http_code'])) {
                echo "    HTTP: {$result['http_code']}\n";
            }
        }
    }
    echo "\n";
}

echo "Next steps:\n";
echo "1. Go to FormSync > Pending Review to see submissions\n";
echo "2. Approve submissions to create leads/customers\n";
echo "3. Check FormSync > Logs for detailed status\n";
echo "4. Verify leads appear in Perfex CRM Leads table\n\n";

exit($fail_count > 0 ? 1 : 0);











