<?php
/**
 * Framer Webhook Simulator
 * 
 * This script simulates Framer webhook requests for local development testing.
 * It sends POST requests to your local webhook endpoint with proper headers and signatures.
 * 
 * Usage:
 *   php simulate_framer_webhook.php --form-id=FORM_ID --url=http://localhost:8080
 * 
 * Options:
 *   --form-id=ID          Form ID (required)
 *   --url=URL             Base URL of your local server (default: http://localhost:8080)
 *   --secret=SECRET       Webhook secret for signature generation (optional)
 *   --data=JSON           JSON string with form data (default: sample data)
 *   --submission-id=ID    Submission ID (default: auto-generated)
 *   --help                Show this help message
 * 
 * Examples:
 *   # Basic test without signature
 *   php simulate_framer_webhook.php --form-id=demo1
 * 
 *   # Test with custom data
 *   php simulate_framer_webhook.php --form-id=demo1 --data='{"name":"John Doe","email":"john@example.com"}'
 * 
 *   # Test with signature verification
 *   php simulate_framer_webhook.php --form-id=demo1 --secret=my_secret_key
 * 
 *   # Test with custom URL
 *   php simulate_framer_webhook.php --form-id=demo1 --url=http://localhost:3000
 */

// Parse command line arguments
$options = getopt('', [
    'form-id:',
    'url:',
    'secret:',
    'data:',
    'submission-id:',
    'help'
]);

// Show help
if (isset($options['help']) || !isset($options['form-id'])) {
    echo "Framer Webhook Simulator\n";
    echo "========================\n\n";
    echo "Usage: php simulate_framer_webhook.php --form-id=FORM_ID [options]\n\n";
    echo "Required:\n";
    echo "  --form-id=ID          Form ID from your form configuration\n\n";
    echo "Options:\n";
    echo "  --url=URL             Base URL (default: http://localhost:8080)\n";
    echo "  --secret=SECRET        Webhook secret for signature (optional)\n";
    echo "  --data=JSON           JSON string with form data\n";
    echo "  --submission-id=ID    Submission ID (default: auto-generated)\n";
    echo "  --help                Show this help\n\n";
    echo "Examples:\n";
    echo "  php simulate_framer_webhook.php --form-id=demo1\n";
    echo "  php simulate_framer_webhook.php --form-id=demo1 --data='{\"name\":\"John\",\"email\":\"john@example.com\"}'\n";
    echo "  php simulate_framer_webhook.php --form-id=demo1 --secret=my_secret\n\n";
    exit(0);
}

// Get parameters
$form_id = $options['form-id'];
$base_url = isset($options['url']) ? rtrim($options['url'], '/') : 'http://localhost:8080';
$webhook_secret = isset($options['secret']) ? $options['secret'] : null;
$submission_id = isset($options['submission-id']) ? $options['submission-id'] : 'test_' . time() . '_' . rand(1000, 9999);

// Prepare form data
if (isset($options['data'])) {
    $form_data = json_decode($options['data'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error: Invalid JSON in --data parameter\n";
        exit(1);
    }
} else {
    // Default sample data
    $form_data = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'message' => 'This is a test submission from the webhook simulator',
        'company' => 'Test Company Inc.'
    ];
}

// Convert form data to JSON
$payload = json_encode($form_data);
$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "Framer Webhook Simulator\n";
echo "========================\n\n";
echo "Form ID:        {$form_id}\n";
echo "Webhook URL:    {$webhook_url}\n";
echo "Submission ID:  {$submission_id}\n";
echo "Has Secret:     " . ($webhook_secret ? 'Yes' : 'No') . "\n";
echo "\nPayload:\n";
echo json_encode($form_data, JSON_PRETTY_PRINT) . "\n\n";

// Generate signature if secret is provided
$signature = null;
if (!empty($webhook_secret)) {
    // Framer signature: SHA-256 HMAC of (payload + submission_id)
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
echo "----------------------------------------\n";

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "Error: {$error}\n";
    exit(1);
}

$response_headers = substr($response, 0, $header_size);
$response_body = substr($response, $header_size);

echo "HTTP Status: {$http_code}\n\n";
echo "Response Headers:\n";
echo $response_headers . "\n";
echo "Response Body:\n";

// Try to pretty print JSON response
$json_response = json_decode($response_body, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($json_response, JSON_PRETTY_PRINT) . "\n";
} else {
    echo $response_body . "\n";
}

echo "\n----------------------------------------\n";

if ($http_code === 200) {
    echo "✓ Webhook sent successfully!\n";
    exit(0);
} else {
    echo "✗ Webhook failed with HTTP {$http_code}\n";
    exit(1);
}











