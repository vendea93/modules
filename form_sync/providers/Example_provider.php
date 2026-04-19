<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Example Provider Template
 * 
 * This is a template for creating new form providers.
 * Copy this file and rename it to YourProvider_provider.php,
 * then implement all required methods.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Example_provider extends App_form_provider
{
    /**
     * Get provider identifier
     * 
     * @return string Provider ID (e.g., 'example', 'typeform', 'google_forms')
     */
    public function getId()
    {
        return 'example';
    }

    /**
     * Get provider name
     * 
     * @return string Human-readable provider name
     */
    public function getName()
    {
        return 'Example Provider';
    }

    /**
     * Get provider description
     * 
     * @return string Provider description
     */
    public function getDescription()
    {
        return 'Example provider for demonstration purposes.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @return string Webhook URL
     */
    public function getWebhookUrl($form_id)
    {
        // Example: Unified endpoint pattern
        return site_url('form_sync/webhook/' . $this->getId() . '/' . $form_id);
        
        // Or provider-specific endpoint:
        // return site_url('form_sync/webhook_' . $this->getId() . '/' . $form_id);
    }

    /**
     * Verify webhook signature
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret from form configuration
     * @return bool True if signature is valid
     */
    public function verifySignature($request, $secret)
    {
        // If no secret configured, skip verification
        if (empty($secret)) {
            return true;
        }

        // Load webhook library
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;

        // Get signature header (adjust header name as needed)
        $signature = $webhook_lib->getHeader('X-Example-Signature');
        
        if (empty($signature)) {
            return false;
        }

        // Get raw payload
        $raw_payload = @file_get_contents('php://input');

        // Implement your provider's signature verification algorithm
        // Example: SHA-256 HMAC
        $expected_signature = hash_hmac('sha256', $raw_payload, $secret);
        
        // Compare signatures (use hash_equals for timing-safe comparison)
        return hash_equals($expected_signature, $signature);
    }

    /**
     * Extract form data from provider payload
     * 
     * Convert provider-specific payload structure to flat array
     * with field names as keys.
     * 
     * @param array $payload Raw payload from provider
     * @return array Extracted form fields (flat array with field names as keys)
     */
    public function extractFormData($payload)
    {
        // Example: If payload is already flat
        // return $payload;
        
        // Example: If payload is nested
        // return isset($payload['data']['fields']) ? $payload['data']['fields'] : [];
        
        // Example: If payload has different structure
        // $form_data = [];
        // if (isset($payload['submission']['fields'])) {
        //     foreach ($payload['submission']['fields'] as $field) {
        //         $form_data[$field['name']] = $field['value'];
        //     }
        // }
        // return $form_data;
        
        // For this example, return empty array
        return [];
    }

    /**
     * Extract form ID from payload or request
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Form ID
     */
    public function extractFormId($payload, $request)
    {
        // Option 1: Get from URL segment
        // return $request->uri->segment(4); // form_sync/webhook/example/{form_id}
        
        // Option 2: Get from payload
        // return isset($payload['form_id']) ? $payload['form_id'] : null;
        
        // Option 3: Get from query parameter
        // return $request->input->get('form_id');
        
        // For this example, return null
        return null;
    }

    /**
     * Extract submission ID from payload or request
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Submission ID
     */
    public function extractSubmissionId($payload, $request)
    {
        // Option 1: Get from header
        // $request->load->library('form_sync/form_sync_webhook');
        // $webhook_lib = $request->form_sync_webhook;
        // return $webhook_lib->getHeader('X-Example-Submission-Id');
        
        // Option 2: Get from payload
        // return isset($payload['submission_id']) ? $payload['submission_id'] : null;
        
        // For this example, return null
        return null;
    }

    /**
     * Extract site ID from payload (if applicable)
     * 
     * Override this method if your provider supports multi-site.
     * 
     * @param array $payload Raw payload
     * @return string|null Site ID
     */
    public function extractSiteId($payload)
    {
        // Example: Get from payload
        // return isset($payload['site_id']) ? $payload['site_id'] : null;
        
        // Default: return null (single-site provider)
        return null;
    }

    /**
     * Get setup instructions
     * 
     * @return array Array of instruction steps (HTML strings)
     */
    public function getSetupInstructions()
    {
        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration.',
            'Copy the generated webhook URL from the form configuration.',
            'Configure the webhook in your provider\'s dashboard.',
            'Configure field mappings to map form fields to Perfex CRM fields.',
            'Submit a test form and check the Logs page to verify it\'s working.',
        ];
    }

    /**
     * Validate payload structure
     * 
     * Override this method to add custom payload validation.
     * 
     * @param array $payload Raw payload
     * @return bool True if payload is valid
     */
    public function validatePayload($payload)
    {
        // Example: Check for required fields
        // if (!isset($payload['required_field'])) {
        //     return false;
        // }
        
        return true;
    }
}











