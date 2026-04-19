<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Framer Provider
 * 
 * Handles form submissions from Framer forms via webhooks.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Framer_provider extends App_form_provider
{
    /**
     * Get provider identifier
     * 
     * @return string
     */
    public function getId()
    {
        return 'framer';
    }

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Framer';
    }

    /**
     * Get provider description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Sync form submissions from Framer forms via webhooks.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (not used for Framer)
     * @return string
     */
    public function getWebhookUrl($form_id, $site_id = null)
    {
        return site_url('form_sync/webhook/framer/' . $form_id);
    }

    /**
     * Verify webhook signature
     * 
     * Verifies the Framer webhook signature using SHA-256 HMAC.
     * Signature verification is optional for Framer - if no secret is configured,
     * verification is skipped and returns true.
     * 
     * Algorithm: SHA-256 HMAC of (payload + submission_id)
     * Format: sha256=<hex_hash>
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret from form configuration
     * @return bool True if signature is valid or no secret configured, false otherwise
     */
    public function verifySignature($request, $secret)
    {
        // If no secret configured, skip verification (optional for Framer)
        if (empty($secret)) {
            return true;
        }

        // Load webhook library for header access and signature verification
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;

        // Get required headers for signature verification
        $framer_signature = $webhook_lib->getHeader('Framer-Signature');
        $submission_id = $webhook_lib->getHeader('Framer-Webhook-Submission-Id');

        // Log header retrieval for debugging (only in development)
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Framer webhook - Signature header: ' . ($framer_signature ? 'present' : 'missing'));
            log_message('debug', '[FormSync] Framer webhook - Submission ID header: ' . ($submission_id ? 'present' : 'missing'));
        }

        // Signature is required if secret is configured
        if (empty($framer_signature)) {
            log_message('error', '[FormSync] Framer webhook - Signature header missing. Available headers: ' . json_encode($webhook_lib->getAllHeaders()));
            return false;
        }

        // Get raw payload for signature verification
        $raw_payload = @file_get_contents('php://input');
        
        if ($raw_payload === false) {
            log_message('error', '[FormSync] Framer webhook - Failed to read raw payload');
            return false;
        }

        // Verify signature using webhook library method
        $is_valid = $webhook_lib->verifyFramerSignature(
            $secret,
            $submission_id ?: '',
            $raw_payload,
            $framer_signature
        );
        
        // Log verification result for debugging
        if (!$is_valid) {
            log_message('error', '[FormSync] Framer webhook - Signature verification failed. Signature: ' . substr($framer_signature, 0, 20) . '..., Submission ID: ' . ($submission_id ?: 'empty'));
        }
        
        return $is_valid;
    }

    /**
     * Extract form data from payload
     * 
     * Framer payload is already flat (field names as keys)
     * 
     * @param array $payload Raw payload
     * @return array
     */
    public function extractFormData($payload)
    {
        // Framer payload is already flat, return as-is
        return $payload;
    }

    /**
     * Extract form ID from payload or request
     * 
     * Framer doesn't include form_id in the payload, so it must be extracted
     * from the URL path. The form_id is part of the webhook URL structure:
     * /form_sync/webhook/framer/{form_id}
     * 
     * @param array $payload Raw payload (not used for Framer)
     * @param object $request Request object (controller instance)
     * @return string|null Form ID or null if not found
     */
    public function extractFormId($payload, $request)
    {
        // Get form_id from URL segment 4
        // URL structure: /form_sync/webhook/framer/{form_id}
        // Segments: [0]=form_sync, [1]=webhook, [2]=framer, [3]=form_id
        $form_id = $request->uri->segment(4);
        
        // Fallback: check query parameter if not in URL path
        if (empty($form_id)) {
            $form_id = $request->input->get('form_id');
        }
        
        return $form_id ?: null;
    }

    /**
     * Extract submission ID from payload or request
     * 
     * Framer sends the submission ID in the 'Framer-Webhook-Submission-Id' header.
     * This is used for signature verification and logging purposes.
     * 
     * @param array $payload Raw payload (not used for Framer)
     * @param object $request Request object (controller instance)
     * @return string|null Submission ID or null if not found
     */
    public function extractSubmissionId($payload, $request)
    {
        // Load webhook library to access headers
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;
        
        // Submission ID comes from Framer-Webhook-Submission-Id header
        return $webhook_lib->getHeader('Framer-Webhook-Submission-Id');
    }

    /**
     * Get setup instructions
     * 
     * @return array
     */
    public function getSetupInstructions()
    {
        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration for your Framer form. Give it a name that helps you remember which form it\'s for (like "Contact Form" or "Newsletter Signup").',
            'After creating the form configuration, you\'ll see a <strong>webhook URL</strong>. Click the copy button to copy this URL - you\'ll need it in the next step.',
            'In your Framer project, select the form you want to connect. Click "Add..." next to "Send To", choose "<strong>Webhook</strong>" from the options, and paste the URL you copied in Step 2.',
            'Go back to your form configuration and set up field mappings. This tells the system which fields from your Framer form should go into which fields in <strong>Perfex CRM</strong> (like name, email, phone number, etc.).',
            'Test your setup by submitting a test form in Framer. Then check the <strong>Logs page</strong> to make sure the submission was received successfully. If you see it in the logs, you\'re all set!',
        ];
    }
}

