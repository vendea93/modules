<?php
/**
 * Interactive Framer Webhook Simulator
 * 
 * This script provides an interactive interface to simulate Framer webhook requests.
 * It prompts for inputs and allows testing multiple scenarios easily.
 * 
 * Usage:
 *   php simulate_framer_webhook_interactive.php
 */

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║      Framer Webhook Simulator - Interactive Mode          ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Get form ID
echo "Enter Form ID: ";
$form_id = trim(fgets(STDIN));
if (empty($form_id)) {
    echo "Error: Form ID is required\n";
    exit(1);
}

// Get base URL
echo "Enter Base URL (default: http://localhost:8080): ";
$base_url_input = trim(fgets(STDIN));
$base_url = !empty($base_url_input) ? rtrim($base_url_input, '/') : 'http://localhost:8080';

// Get webhook secret (optional)
echo "Enter Webhook Secret (optional, press Enter to skip): ";
$webhook_secret = trim(fgets(STDIN));
$webhook_secret = !empty($webhook_secret) ? $webhook_secret : null;

// Get form data
echo "\nEnter form data as JSON (press Enter for default sample data):\n";
echo "Example: {\"name\":\"John Doe\",\"email\":\"john@example.com\"}\n";
$data_input = trim(fgets(STDIN));

if (empty($data_input)) {
    // Default sample data
    $form_data = [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'message' => 'This is a test submission from the webhook simulator',
        'company' => 'Test Company Inc.'
    ];
} else {
    $form_data = json_decode($data_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Error: Invalid JSON. Using default data.\n";
        $form_data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'message' => 'This is a test submission from the webhook simulator',
            'company' => 'Test Company Inc.'
        ];
    }
}

// Generate submission ID
$submission_id = 'test_' . time() . '_' . rand(1000, 9999);

// Convert form data to JSON
$payload = json_encode($form_data);
$webhook_url = $base_url . '/form_sync/webhook/framer/' . $form_id;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    Webhook Details                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
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

// Confirm before sending
echo "Press Enter to send webhook (or Ctrl+C to cancel)...";
fgets(STDIN);

// Send webhook request
echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              Sending Webhook Request...                 ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";

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
    echo "✗ Error: {$error}\n";
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

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
if ($http_code === 200) {
    echo "║              ✓ Webhook sent successfully!              ║\n";
} else {
    echo "║          ✗ Webhook failed with HTTP {$http_code}          ║\n";
}
echo "╚═══════════════════════════════════════════════════════════╝\n";

exit($http_code === 200 ? 0 : 1);











