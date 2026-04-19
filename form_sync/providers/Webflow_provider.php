<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Webflow Provider
 * 
 * Handles form submissions from Webflow forms via webhooks.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Webflow_provider extends App_form_provider
{
    /**
     * Get provider identifier
     * 
     * @return string
     */
    public function getId()
    {
        return 'webflow';
    }

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Webflow';
    }

    /**
     * Get provider description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Sync form submissions from Webflow forms via webhooks.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * Webflow uses form_id in URL (same pattern as Framer)
     * URL format: /form_sync/webhook/webflow/{form_id}
     * 
     * @param string $form_id Form ID (required, always included in URL like Framer)
     * @param string|null $site_id Site ID (not used in URL, kept for interface consistency)
     * @return string
     */
    public function getWebhookUrl($form_id, $site_id = null)
    {
        // Webflow uses form_id in URL, same as Framer
        // Always include form_id - this matches Framer's behavior
        return site_url('form_sync/webhook/webflow/' . $form_id);
    }

    /**
     * Verify webhook signature
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret
     * @return bool
     */
    public function verifySignature($request, $secret)
    {
        // If no secret configured, allow (dashboard webhooks may not have signatures)
        if (empty($secret)) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Webflow webhook - No secret configured, allowing request');
            }
            return true;
        }

        // Load webhook library
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;

        // Get headers
        $webflow_signature = $webhook_lib->getHeader('x-webflow-signature');
        $timestamp = $webhook_lib->getHeader('x-webflow-timestamp');

        // Log header retrieval for debugging (only in development)
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Webflow webhook - Signature header: ' . ($webflow_signature ? 'present' : 'missing'));
            log_message('debug', '[FormSync] Webflow webhook - Timestamp header: ' . ($timestamp ? 'present' : 'missing'));
        }

        // If headers are missing, allow (dashboard webhooks don't have signatures)
        // This is important: even if a secret is configured, dashboard-created webhooks
        // may not include signature headers, so we allow them to pass
        if (empty($webflow_signature) || empty($timestamp)) {
            log_message('warning', '[FormSync] Webflow webhook - Signature headers missing (webhook may have been created via dashboard). Secret is configured but headers are missing - allowing request. Available headers: ' . json_encode($webhook_lib->getAllHeaders()));
            return true; // Allow dashboard webhooks even if secret is configured
        }

        // Get raw payload
        $raw_payload = @file_get_contents('php://input');
        
        if ($raw_payload === false) {
            log_message('error', '[FormSync] Webflow webhook - Failed to read raw payload');
            return false;
        }

        // Use webhook library method
        $is_valid = $webhook_lib->verifyWebflowSignature(
            $secret,
            $timestamp,
            $raw_payload,
            $webflow_signature
        );
        
        // Log verification result for debugging (matching Framer's logging)
        if (!$is_valid) {
            log_message('error', '[FormSync] Webflow webhook - Signature verification failed. Signature: ' . substr($webflow_signature, 0, 20) . '..., Timestamp: ' . ($timestamp ?: 'empty') . ', Secret configured: ' . (empty($secret) ? 'no' : 'yes'));
            log_message('error', '[FormSync] Webflow webhook - This usually means the secret in Webflow does not match the secret in the form configuration. Please verify both secrets are identical.');
        } else {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Webflow webhook - Signature verification successful');
            }
        }
        
        return $is_valid;
    }

    /**
     * Extract form data from payload
     * 
     * Webflow payload has nested structure: payload.data contains form fields
     * 
     * @param array $payload Raw payload
     * @return array
     */
    public function extractFormData($payload)
    {
        // Extract from nested structure
        return isset($payload['payload']['data']) ? $payload['payload']['data'] : [];
    }

    /**
     * Extract form ID from payload or request
     * 
     * Webflow includes form_id in payload, and can also be extracted from URL
     * URL structure: /form_sync/webhook/webflow/{form_id} (same as Framer)
     * 
     * @param array $payload Raw payload
     * @param object $request Request object
     * @return string|null
     */
    public function extractFormId($payload, $request)
    {
        // First try to get from payload
        $form_id = isset($payload['payload']['formId']) ? $payload['payload']['formId'] : null;
        
        // If not in payload, try to get from URL segment 4 (form_id position, same as Framer)
        // URL structure: /form_sync/webhook/webflow/{form_id}
        // Segments: [0]=form_sync, [1]=webhook, [2]=webflow, [3]=form_id
        if (empty($form_id)) {
            $form_id = $request->uri->segment(4);
        }
        
        // Fallback: check query parameter
        if (empty($form_id)) {
            $form_id = $request->input->get('form_id');
        }
        
        return $form_id ?: null;
    }

    /**
     * Extract submission ID from payload or request
     * 
     * @param array $payload Raw payload
     * @param object $request Request object
     * @return string|null
     */
    public function extractSubmissionId($payload, $request)
    {
        return isset($payload['payload']['id']) ? $payload['payload']['id'] : null;
    }

    /**
     * Extract site ID from payload or request
     * 
     * Webflow includes site_id in payload, but can also be extracted from URL
     * URL structure: /form_sync/webhook/webflow/{site_id}
     * 
     * @param array $payload Raw payload
     * @param object|null $request Request object (optional, for URL extraction)
     * @return string|null
     */
    public function extractSiteId($payload, $request = null)
    {
        // First try to get from payload
        $site_id = isset($payload['payload']['siteId']) ? $payload['payload']['siteId'] : null;
        
        // If not in payload and request is available, try to get from URL segment 4
        // URL structure: /form_sync/webhook/webflow/{site_id}
        // Segments: [0]=form_sync, [1]=webhook, [2]=webflow, [3]=site_id
        if (empty($site_id) && $request) {
            $url_site_id = $request->uri->segment(4);
            if (!empty($url_site_id)) {
                $site_id = $url_site_id;
            }
        }
        
        // Fallback: check query parameter
        if (empty($site_id) && $request) {
            $site_id = $request->input->get('site_id');
        }
        
        return $site_id ?: null;
    }

    /**
     * Get setup instructions
     * 
     * @return array
     */
    public function getSetupInstructions()
    {
        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration for your Webflow form.',
            'Copy the generated webhook URL from the form configuration.',
            'In Webflow, go to <strong>Site Settings → Webhooks</strong>, click "Add Webhook", paste the URL, and select "Form Submission" as the trigger.',
            'Configure field mappings to map Webflow form fields to Perfex CRM fields.',
            'Submit a test form on your Webflow site and check the Logs page to verify it\'s working.',
        ];
    }
}



