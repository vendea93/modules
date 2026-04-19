<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Google Forms Provider
 * 
 * Handles form submissions from Google Forms via webhooks sent through Google Apps Script.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Google_forms_provider extends App_form_provider
{
    /**
     * Get provider identifier
     * 
     * @return string
     */
    public function getId()
    {
        return 'google_forms';
    }

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Google Forms';
    }

    /**
     * Get provider description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Sync form submissions from Google Forms via webhooks using Google Apps Script.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (not used for Google Forms)
     * @return string
     */
    public function getWebhookUrl($form_id, $site_id = null)
    {
        return site_url('form_sync/webhook/google_forms/' . $form_id);
    }

    /**
     * Verify webhook signature
     * 
     * Google Apps Script doesn't have built-in webhook signatures, but users can
     * optionally add a secret header for basic authentication.
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret from form configuration
     * @return bool True if signature is valid or no secret configured, false otherwise
     */
    public function verifySignature($request, $secret)
    {
        // If no secret configured, skip verification (optional for Google Forms)
        if (empty($secret)) {
            return true;
        }

        // Load webhook library for header access
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;

        // Check for optional secret header (X-FormSync-Secret)
        $secret_header = $webhook_lib->getHeader('X-FormSync-Secret');

        // Log header retrieval for debugging (only in development)
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Google Forms webhook - Secret header: ' . ($secret_header ? 'present' : 'missing'));
        }

        // If secret is configured but header is missing, reject
        if (empty($secret_header)) {
            log_message('warning', '[FormSync] Google Forms webhook - Secret is configured but header is missing');
            return false;
        }

        // Compare secrets using timing-safe comparison
        $is_valid = hash_equals($secret, $secret_header);

        if (!$is_valid) {
            log_message('error', '[FormSync] Google Forms webhook - Secret verification failed');
        }

        return $is_valid;
    }

    /**
     * Extract form data from payload
     * 
     * Google Apps Script allows flexible payload structures, so we handle multiple formats:
     * - Flat structure: field names as keys directly in payload
     * - Nested structure: payload.fields or payload.data
     * - Responses array: payload.responses
     * 
     * @param array $payload Raw payload
     * @return array Extracted form fields (flat array with field names as keys)
     */
    public function extractFormData($payload)
    {
        // Remove metadata fields that aren't form data
        $metadata_fields = ['form_id', 'submission_id', 'timestamp', 'fields', 'data', 'responses'];
        
        // Try nested structure first: payload.fields
        if (isset($payload['fields']) && is_array($payload['fields'])) {
            return $payload['fields'];
        }
        
        // Try nested structure: payload.data
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }
        
        // Try responses array format: payload.responses
        if (isset($payload['responses']) && is_array($payload['responses'])) {
            $form_data = [];
            foreach ($payload['responses'] as $response) {
                if (isset($response['question']) && isset($response['answer'])) {
                    $form_data[$response['question']] = $response['answer'];
                } elseif (isset($response['field']) && isset($response['value'])) {
                    $form_data[$response['field']] = $response['value'];
                }
            }
            return $form_data;
        }
        
        // Default: assume flat structure, but filter out metadata fields
        $form_data = [];
        foreach ($payload as $key => $value) {
            if (!in_array($key, $metadata_fields)) {
                $form_data[$key] = $value;
            }
        }
        
        return $form_data;
    }

    /**
     * Extract form ID from payload or request
     * 
     * Google Forms webhook can include form_id in payload or URL.
     * URL structure: /form_sync/webhook/google_forms/{form_id}
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Form ID or null if not found
     */
    public function extractFormId($payload, $request)
    {
        // First try to get from payload
        $form_id = isset($payload['form_id']) ? $payload['form_id'] : null;
        
        // If not in payload, try to get from URL segment 4
        // URL structure: /form_sync/webhook/google_forms/{form_id}
        // Segments: [0]=form_sync, [1]=webhook, [2]=google_forms, [3]=form_id
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
     * Google Apps Script typically generates a UUID for submission_id.
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Submission ID or null if not found
     */
    public function extractSubmissionId($payload, $request)
    {
        // Get from payload (Apps Script template includes submission_id)
        return isset($payload['submission_id']) ? $payload['submission_id'] : null;
    }

    /**
     * Get setup instructions
     * 
     * Returns step-by-step instructions including the complete Google Apps Script code
     * that users can copy and paste into their Google Form's Apps Script editor.
     * 
     * @return array Array of instruction steps (HTML strings)
     */
    public function getSetupInstructions()
    {
        // Google Apps Script code template
        $apps_script_code = <<<'CODE'
function onFormSubmit(e) {
  // Replace WEBHOOK_URL_PLACEHOLDER with your actual webhook URL from step 2
  var webhookUrl = 'WEBHOOK_URL_PLACEHOLDER';
  var formId = 'YOUR_FORM_ID';
  
  // Optional: Add webhook secret for authentication (if configured in form settings)
  var webhookSecret = ''; // Leave empty if not using secret authentication
  
  // Get form responses
  var formResponses = e.response.getItemResponses();
  var payload = {
    form_id: formId,
    submission_id: Utilities.getUuid(),
    timestamp: new Date().toISOString()
  };
  
  // Extract form fields
  formResponses.forEach(function(response) {
    var question = response.getItem().getTitle();
    var answer = response.getResponse();
    payload[question] = answer;
  });
  
  // Prepare request options
  var options = {
    'method': 'post',
    'contentType': 'application/json',
    'payload': JSON.stringify(payload)
  };
  
  // Add webhook secret header if provided
  if (webhookSecret) {
    options.headers = {
      'X-FormSync-Secret': webhookSecret
    };
  }
  
  // Send webhook
  try {
    var response = UrlFetchApp.fetch(webhookUrl, options);
    Logger.log('Webhook sent successfully: ' + response.getResponseCode());
  } catch (error) {
    Logger.log('Error sending webhook: ' + error.toString());
  }
}
CODE;

        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration for your Google Form. Give it a name that helps you remember which form it\'s for (like "Contact Form" or "Newsletter Signup").',
            'After creating the form configuration, you\'ll see a <strong>webhook URL</strong>. Click the copy button to copy this URL - you\'ll need it in the next step.',
            'Open your Google Form, click the <strong>three dots menu (⋮)</strong> in the top right → <strong>Script editor</strong>.',
            'Copy and paste the following Google Apps Script code into the editor:',
            '<div style="margin: 15px 0;">
                <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; max-height: 400px; overflow-y: auto;"><code style="font-family: \'Courier New\', monospace; font-size: 13px; line-height: 1.5;">' . htmlspecialchars($apps_script_code) . '</code></pre>
                <button onclick="copyGoogleFormsCode(this)" class="btn btn-sm btn-default" style="margin-top: 10px;">
                    <i class="fa fa-copy"></i> Copy Code
                </button>
            </div>',
            'Replace <code>WEBHOOK_URL_PLACEHOLDER</code> with your actual webhook URL from step 2.',
            'Replace <code>YOUR_FORM_ID</code> with the Form ID you entered in your form configuration.',
            'If you configured a webhook secret in your form configuration, paste it into the <code>webhookSecret</code> variable. Otherwise, leave it empty.',
            'Save the script (Ctrl+S or Cmd+S) and give it a name like "FormSync Webhook".',
            'Set up the trigger: Click <strong>Triggers</strong> (clock icon) in the left sidebar → <strong>Add Trigger</strong> → Select <code>onFormSubmit</code> function → Event source: <strong>From form</strong> → Event type: <strong>On form submit</strong> → Click <strong>Save</strong>.',
            'Go back to your form configuration and set up <strong>field mappings</strong>. This tells the system which fields from your Google Form should go into which fields in <strong>Perfex CRM</strong> (like name, email, phone number, etc.).',
            'Test your setup by submitting a test form. Then check the <strong>Logs page</strong> to make sure the submission was received successfully. If you see it in the logs, you\'re all set!'
        ];
    }
}







