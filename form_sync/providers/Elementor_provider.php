<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Elementor Provider
 * 
 * Handles form submissions from Elementor forms via webhooks.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Elementor_provider extends App_form_provider
{
    /**
     * Get provider identifier
     * 
     * @return string
     */
    public function getId()
    {
        return 'elementor';
    }

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Elementor';
    }

    /**
     * Get provider description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Sync form submissions from Elementor forms via webhooks.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (not used for Elementor)
     * @return string
     */
    public function getWebhookUrl($form_id, $site_id = null)
    {
        return site_url('form_sync/webhook/elementor/' . $form_id);
    }

    /**
     * Verify webhook signature
     * 
     * Elementor does not implement webhook signature verification.
     * This method always returns true.
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret (not used for Elementor)
     * @return bool Always returns true
     */
    public function verifySignature($request, $secret)
    {
        // Elementor does not support webhook signature verification
        // Always return true
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Elementor webhook - No signature verification (Elementor does not support it)');
        }
        return true;
    }

    /**
     * Extract form data from payload
     * 
     * Elementor sends webhooks in two modes:
     * - Standard mode: Flat structure with field labels as keys, plus form_id and form_name
     * - Advanced mode: Nested structure with form, fields array, and meta objects
     * 
     * @param array $payload Raw payload
     * @return array Extracted form fields (flat array with field names as keys)
     */
    public function extractFormData($payload)
    {
        // Check if this is advanced mode (has 'form' and 'fields' keys)
        if (isset($payload['form']) && isset($payload['fields']) && is_array($payload['fields'])) {
            // Advanced mode: Extract from fields array
            $form_data = [];
            
            foreach ($payload['fields'] as $field) {
                // Use field title as key, or field id if title is empty
                $key = !empty($field['title']) ? $field['title'] : (isset($field['id']) ? $field['id'] : '');
                
                if (!empty($key) && isset($field['value'])) {
                    $form_data[$key] = $field['value'];
                }
            }
            
            // Also include meta data if present (as additional fields)
            if (isset($payload['meta']) && is_array($payload['meta'])) {
                foreach ($payload['meta'] as $meta_key => $meta_value) {
                    if (is_array($meta_value) && isset($meta_value['title']) && isset($meta_value['value'])) {
                        $form_data[$meta_value['title']] = $meta_value['value'];
                    }
                }
            }
            
            return $form_data;
        } else {
            // Standard mode: Flat structure
            // Remove form_id and form_name as they're not form fields
            $form_data = $payload;
            unset($form_data['form_id']);
            unset($form_data['form_name']);
            
            return $form_data;
        }
    }

    /**
     * Extract form ID from payload or request
     * 
     * IMPORTANT: For Elementor, we prioritize the URL form_id over the payload form_id.
     * This is because Elementor sends its internal form ID (like "40ee6eb") in the payload,
     * but the user configures a custom form_id (like "elementor") in Perfex CRM that matches
     * the URL. We need to use the URL form_id to match the form configuration.
     * 
     * Elementor includes form_id in payload:
     * - Standard mode: form_id at root level
     * - Advanced mode: form.id nested
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Form ID or null if not found
     */
    public function extractFormId($payload, $request)
    {
        // PRIORITY 1: Get from URL segment 4 (this matches the form configuration)
        // URL structure: /form_sync/webhook/elementor/{form_id}
        // Segments: [0]=form_sync, [1]=webhook, [2]=elementor, [3]=form_id
        $form_id = $request->uri->segment(4);
        
        // PRIORITY 2: Check query parameter
        if (empty($form_id)) {
            $form_id = $request->input->get('form_id');
        }
        
        // PRIORITY 3: Fallback to payload (Elementor's internal form ID)
        // Only use payload if URL doesn't have form_id
        // This allows flexibility but prioritizes the configured form_id
        if (empty($form_id)) {
            // Try standard mode (form_id at root level)
            $form_id = isset($payload['form_id']) ? $payload['form_id'] : null;
            
            // If not found, try advanced mode (form.id)
            if (empty($form_id) && isset($payload['form']['id'])) {
                $form_id = $payload['form']['id'];
            }
        }
        
        // Log which source was used for debugging
        if (ENVIRONMENT === 'development') {
            $source = 'unknown';
            if ($request->uri->segment(4)) {
                $source = 'URL segment';
            } elseif ($request->input->get('form_id')) {
                $source = 'query parameter';
            } elseif (isset($payload['form_id'])) {
                $source = 'payload (standard)';
            } elseif (isset($payload['form']['id'])) {
                $source = 'payload (advanced)';
            }
            log_message('debug', '[FormSync] Elementor webhook - Form ID extracted from: ' . $source . ', Value: ' . $form_id);
        }
        
        return $form_id ?: null;
    }

    /**
     * Extract submission ID from payload or request
     * 
     * Elementor does not include a submission ID in the standard payload.
     * This method generates a unique ID based on timestamp and form data.
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Submission ID or null if not found
     */
    public function extractSubmissionId($payload, $request)
    {
        // Elementor doesn't include submission ID in payload
        // Generate a unique ID based on timestamp and form data hash
        // This ensures we have a submission ID for logging purposes
        
        $timestamp = time();
        $payload_hash = md5(json_encode($payload));
        $submission_id = 'elementor_' . $timestamp . '_' . substr($payload_hash, 0, 8);
        
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Elementor webhook - Generated submission ID: ' . $submission_id);
        }
        
        return $submission_id;
    }

    /**
     * Get setup instructions
     * 
     * @return array
     */
    public function getSetupInstructions()
    {
        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration for your Elementor form. Give it a name that helps you remember which form it\'s for (like "Contact Form" or "Newsletter Signup").',
            'After creating the form configuration, you\'ll see a <strong>webhook URL</strong>. Click the copy button to copy this URL - you\'ll need it in the next step.',
            'In your WordPress site, edit the page with your Elementor form. Select the form widget, go to the <strong>Actions</strong> tab, and enable the <strong>Webhook</strong> action.',
            'Paste the webhook URL you copied in Step 2 into the "Webhook URL" field. Optionally, enable "Advanced Data" for more detailed payload structure.',
            'Go back to your form configuration and set up field mappings. This tells the system which fields from your Elementor form should go into which fields in <strong>Perfex CRM</strong> (like name, email, phone number, etc.).',
            'Test your setup by submitting a test form on your WordPress site. Then check the <strong>Logs page</strong> to make sure the submission was received successfully. If you see it in the logs, you\'re all set!',
        ];
    }
}

