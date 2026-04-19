<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Webhook Library
 * 
 * Handles webhook signature verification for Framer and Webflow providers.
 * Implements provider-specific signature algorithms and validation.
 * 
 * @package    FormSync
 * @subpackage Libraries
 * @category   Module
 * @author     LiquidApps Studio
 */
class Form_sync_webhook
{
    /**
     * CodeIgniter instance
     * 
     * @var object
     */
    private $ci;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * Verify Framer webhook signature
     * 
     * Algorithm: SHA-256 HMAC of (payload + submission_id)
     * Format: sha256=<hex_hash>
     * 
     * @param string $secret Webhook secret from form configuration
     * @param string $submission_id Submission ID from Framer-Webhook-Submission-Id header
     * @param string $payload Raw request body
     * @param string $signature Signature from Framer-Signature header
     * @return bool True if signature is valid, false otherwise
     */
    public function verifyFramerSignature($secret, $submission_id, $payload, $signature)
    {
        // If no secret configured, skip verification (optional for Framer)
        if (empty($secret)) {
            return true;
        }

        // If signature is not provided, verification fails
        if (empty($signature)) {
            return false;
        }

        // Signature format should be: sha256=<hex_hash>
        // Check length (64 hex chars + 7 for "sha256=" = 71)
        if (strlen($signature) < 71 || substr($signature, 0, 7) !== 'sha256=') {
            log_message('warning', '[FormSync] Framer signature format invalid. Length: ' . strlen($signature) . ', Expected: 71, Starts with sha256=: ' . (substr($signature, 0, 7) === 'sha256=' ? 'yes' : 'no'));
            return false;
        }

        // Generate expected signature
        // HMAC of (payload + submission_id)
        $hmac = hash_hmac('sha256', $payload . $submission_id, $secret, true);
        $expected_signature = 'sha256=' . bin2hex($hmac);

        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($expected_signature, $signature);
    }

    /**
     * Verify Webflow webhook signature
     * 
     * Algorithm: SHA-256 HMAC of (timestamp + ":" + JSON.stringify(request_body))
     * Headers: x-webflow-signature, x-webflow-timestamp
     * 
     * @param string $secret OAuth client secret or secret key from Webflow
     * @param string $timestamp Timestamp from x-webflow-timestamp header
     * @param string $payload Raw request body (JSON string)
     * @param string $signature Signature from x-webflow-signature header
     * @return bool True if signature is valid, false otherwise
     */
    public function verifyWebflowSignature($secret, $timestamp, $payload, $signature)
    {
        // If no secret configured, verification fails (required for Webflow)
        if (empty($secret)) {
            log_message('warning', '[FormSync] Webflow signature verification failed: No secret configured');
            return false;
        }

        // If signature or timestamp is not provided, verification fails
        if (empty($signature) || empty($timestamp)) {
            log_message('warning', '[FormSync] Webflow signature verification failed: Missing signature or timestamp');
            return false;
        }

        // Validate timestamp is within 5 minutes (300,000 milliseconds)
        $request_timestamp = (int)$timestamp;
        $current_time = time() * 1000; // Convert to milliseconds
        $time_diff = $current_time - $request_timestamp;

        if ($time_diff > 300000) {
            log_message('warning', '[FormSync] Webflow signature verification failed: Request is older than 5 minutes');
            return false;
        }

        // Generate expected signature
        // HMAC of (timestamp + ":" + JSON.stringify(request_body))
        $data = $timestamp . ':' . $payload;
        $hmac = hash_hmac('sha256', $data, $secret, true);
        $expected_signature = bin2hex($hmac);

        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($expected_signature, $signature);
    }

    /**
     * Get header value (case-insensitive)
     * 
     * PHP $_SERVER headers are uppercase with underscores, but providers send lowercase with hyphens.
     * This function normalizes header access and handles proxy/load balancer scenarios.
     * 
     * Tries multiple methods in order:
     * 1. getallheaders() with various case variations
     * 2. $_SERVER with HTTP_ prefix
     * 3. $_SERVER with REDIRECT_HTTP_ prefix (for some server configs)
     * 4. $_SERVER with HTTP_X_ prefix (for proxy headers)
     * 
     * @param string $header_name Header name to retrieve (e.g., 'Framer-Signature')
     * @return string|null Header value or null if not found
     */
    public function getHeader($header_name)
    {
        // Try getallheaders() first (if available) - most reliable method
        if (function_exists('getallheaders')) {
            $header_value = $this->getHeaderFromGetallheaders($header_name);
            if ($header_value !== null) {
                return $header_value;
            }
        }

        // Fallback to $_SERVER with various key formats
        return $this->getHeaderFromServer($header_name);
    }
    
    /**
     * Get header from getallheaders() function
     * 
     * Tries multiple case variations to find the header.
     * 
     * @param string $header_name Header name to retrieve
     * @return string|null Header value or null if not found
     */
    private function getHeaderFromGetallheaders($header_name)
    {
        $headers = getallheaders();
        if (!$headers) {
            return null;
        }
        
        // Headers may be in various formats, try multiple variations
        $variations = [
            $header_name, // Original case
            strtolower($header_name), // Lowercase
            str_replace('-', '_', strtolower($header_name)), // Lowercase with underscores
            strtoupper(str_replace('-', '_', $header_name)), // Uppercase with underscores
            ucwords(str_replace('-', ' ', strtolower($header_name)), ' '), // Title case
        ];

        // Try exact matches first
        foreach ($variations as $variation) {
            if (isset($headers[$variation])) {
                return $headers[$variation];
            }
        }
        
        // Fallback: case-insensitive search by normalizing both keys
        foreach ($headers as $key => $value) {
            $normalized_key = strtolower(str_replace(['-', '_'], '', $key));
            $normalized_name = strtolower(str_replace(['-', '_'], '', $header_name));
            if ($normalized_key === $normalized_name) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Get header from $_SERVER superglobal
     * 
     * Tries various $_SERVER key formats to find the header.
     * 
     * @param string $header_name Header name to retrieve
     * @return string|null Header value or null if not found
     */
    private function getHeaderFromServer($header_name)
    {
        // Normalize header name for $_SERVER keys (uppercase with underscores)
        $normalized = strtoupper(str_replace('-', '_', $header_name));
        
        // Try standard HTTP_ prefix first
        $server_key = 'HTTP_' . $normalized;
        if (isset($_SERVER[$server_key])) {
            return $_SERVER[$server_key];
        }
        
        // Try REDIRECT_HTTP_ prefix (for some server configurations)
        $redirect_key = 'REDIRECT_HTTP_' . $normalized;
        if (isset($_SERVER[$redirect_key])) {
            return $_SERVER[$redirect_key];
        }
        
        // Try HTTP_X_ prefix (for proxy headers in load balancer setups)
        $proxy_key = 'HTTP_X_' . $normalized;
        if (isset($_SERVER[$proxy_key])) {
            return $_SERVER[$proxy_key];
        }
        
        return null;
    }

    /**
     * Get all headers for debugging
     * 
     * @return array All available headers
     */
    public function getAllHeaders()
    {
        $headers = [];
        
        // Try getallheaders() first
        if (function_exists('getallheaders')) {
            $all_headers = getallheaders();
            if ($all_headers) {
                foreach ($all_headers as $key => $value) {
                    $headers[$key] = $value;
                }
            }
        }
        
        // Also check $_SERVER for HTTP_* keys
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace('_', '-', substr($key, 5));
                $headers[$header_name] = $value;
            }
        }
        
        return $headers;
    }
}

