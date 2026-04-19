<?php
/**
 * Test Lead Creation via Framer Webhook
 * 
 * This script tests the automatic lead creation functionality
 * after the fix that creates leads automatically instead of holding for review.
 * 
 * Usage:
 *   php test_lead_creation.php --form-id=YOUR_FORM_ID [--url=http://localhost:8080] [--secret=YOUR_SECRET]
 * 
 * Example:
 *   php test_lead_creation.php --form-id=Contact --url=http://localhost:8080
 */

// Parse command line arguments
$options = getopt('', [
    'form-id:',
    'url:',
    'secret:',
    'help'
]);

// Show help
if (isset($options['help']) || !isset($options['form-id'])) {
    echo "Test Lead Creation via Framer Webhook\n";
    echo "=====================================\n\n";
    echo "Usage: php test_lead_creation.php --form-id=FORM_ID [options]\n\n";
    echo "Required:\n";
    echo "  --form-id=ID          Form ID from your form configuration\n\n";
    echo "Options:\n";
    echo "  --url=URL             Base URL (default: http://localhost:8080)\n";
    echo "  --secret=SECRET        Webhook secret (optional)\n";
    echo "  --help                 Show this help\n\n";
    echo "Example:\n";
    echo "  php test_lead_creation.php --form-id=Contact\n";
    echo "  php test_lead_creation.php --form-id=Contact --url=http://localhost:8080\n\n";
    echo "Note: Make sure you have:\n";
    echo "  1. Created a form configuration with the form_id\n";
    echo "  2. Set target_type to 'lead'\n";
    echo "  3. Configured field mappings (name, email, etc.)\n";
    echo "  4. Enabled the form configuration\n\n";
    exit(0);
}

// Get parameters
$form_id = $options['form-id'];
$base_url = isset($options['url']) ? rtrim($options['url'], '/') : 'http://localhost:8080';
$webhook_secret = isset($options['secret']) ? $options['secret'] : null;

// Generate unique submission ID
$submission_id = 'test_' . time() . '_' . rand(1000, 9999);

// Create test data with unique email to avoid duplicates
$timestamp = time();
$form_data = [
    'name' => 'Test Lead ' . $timestamp,
    'email' => 'testlead' . $timestamp . '@example.com',
    'phone' => '+1-555-' . rand(1000, 9999),
    'message' => 'This is a test submission to verify automatic lead creation via Framer webhook',
    'company' => 'Test Company ' . $timestamp
];

// Convert to JSON
$payload = json_encode($form_data);
$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     Test Lead Creation via Framer Webhook                ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";
echo "Form ID:        {$form_id}\n";
echo "Webhook URL:    {$webhook_url}\n";
echo "Submission ID:  {$submission_id}\n";
echo "Has Secret:     " . ($webhook_secret ? 'Yes' : 'No') . "\n";
echo "\nTest Data:\n";
echo json_encode($form_data, JSON_PRETTY_PRINT) . "\n\n";

// Generate signature if secret is provided
$signature = null;
if (!empty($webhook_secret)) {
    $hmac = hash_hmac('sha256', $payload . $submission_id, $webhook_secret, true);
    $signature = 'sha256=' . bin2hex($hmac);
    echo "Generated Signature: {$signature}\n\n";
}

// Prepare headers
$headers = [
    'Content-Type: application/json',
    'Framer-Webhook-Submission-Id: ' . $submission_id
];

if ($signature) {
    $headers[] = 'Framer-Signature: ' . $signature;
}

// Send webhook request
echo "Sending webhook request...\n";
echo "─────────────────────────────────────────────────────────────\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "✗ Error: {$error}\n";
    exit(1);
}

$response_headers = substr($response, 0, $header_size);
$response_body = substr($response, $header_size);

echo "HTTP Status: {$http_code}\n\n";

// Try to parse JSON response
$json_response = json_decode($response_body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "Response:\n";
    echo json_encode($json_response, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "Response Body:\n";
    echo $response_body . "\n\n";
}

echo "─────────────────────────────────────────────────────────────\n";

if ($http_code === 200) {
    if (isset($json_response['error'])) {
        echo "⚠ Webhook received but returned an error:\n";
        echo "  " . $json_response['error'] . "\n\n";
        
        if (strpos($json_response['error'], 'Form configuration not found') !== false) {
            echo "This means the form_id '{$form_id}' is not configured.\n";
            echo "Please:\n";
            echo "  1. Go to FormSync > Form Configurations\n";
            echo "  2. Create a new form configuration with form_id: {$form_id}\n";
            echo "  3. Set target_type to 'lead'\n";
            echo "  4. Configure field mappings\n";
            echo "  5. Enable the configuration\n";
            echo "  6. Run this test again\n\n";
        }
    } else {
        echo "✓ Webhook sent successfully!\n\n";
        echo "Next steps:\n";
        echo "  1. Check the FormSync > Logs page to see the submission status\n";
        echo "  2. Check the Leads table in Perfex CRM to verify the lead was created\n";
        echo "  3. Look for lead with email: {$form_data['email']}\n\n";
    }
} else {
    echo "✗ Webhook failed with HTTP {$http_code}\n";
    exit(1);
}

exit(0);











