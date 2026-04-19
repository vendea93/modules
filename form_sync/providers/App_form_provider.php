<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * App Form Provider - Base Class
 * 
 * Abstract base class that all form providers must extend.
 * Defines the contract for all providers in the FormSync module.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
abstract class App_form_provider
{
    /**
     * CodeIgniter instance
     * 
     * @var object
     */
    protected $ci;

    /**
     * Provider settings cache
     * 
     * @var array
     */
    protected $settings_cache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Get CI instance only if available (may not be during auto-discovery)
        if (function_exists('get_instance')) {
            try {
                $this->ci = &get_instance();
            } catch (Exception $e) {
                // CI not ready yet, will be set later if needed
                $this->ci = null;
            }
        }
    }

    // ============================================================================
    // REQUIRED METHODS - Must be implemented by all providers
    // ============================================================================

    /**
     * Get provider identifier
     * 
     * @return string Provider ID (e.g., 'framer', 'webflow')
     */
    abstract public function getId();

    /**
     * Get provider name
     * 
     * @return string Human-readable provider name
     */
    abstract public function getName();

    /**
     * Get provider description
     * 
     * @return string Provider description
     */
    abstract public function getDescription();

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (optional, used by Webflow)
     * @return string Webhook URL
     */
    abstract public function getWebhookUrl($form_id, $site_id = null);

    /**
     * Verify webhook signature
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret from form configuration
     * @return bool True if signature is valid
     */
    abstract public function verifySignature($request, $secret);

    /**
     * Extract form data from provider payload
     * 
     * @param array $payload Raw payload from provider
     * @return array Extracted form fields (flat array with field names as keys)
     */
    abstract public function extractFormData($payload);

    /**
     * Extract form ID from payload or request
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Form ID
     */
    abstract public function extractFormId($payload, $request);

    /**
     * Extract submission ID from payload or request
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Submission ID
     */
    abstract public function extractSubmissionId($payload, $request);

    // ============================================================================
    // OPTIONAL METHODS - Can be overridden by providers
    // ============================================================================

    /**
     * Extract site ID from payload or request (if applicable)
     * 
     * @param array $payload Raw payload
     * @param object|null $request Request object (optional, for URL extraction)
     * @return string|null Site ID
     */
    public function extractSiteId($payload, $request = null)
    {
        return null;
    }

    /**
     * Get provider-specific settings fields
     * 
     * @return array Array of setting field definitions
     */
    public function getSettingsFields()
    {
        return [];
    }

    /**
     * Get setup instructions for this provider
     * 
     * @return array Array of instruction steps
     */
    public function getSetupInstructions()
    {
        return [];
    }

    /**
     * Validate payload structure
     * 
     * @param array $payload Raw payload
     * @return bool True if payload is valid
     */
    public function validatePayload($payload)
    {
        return true;
    }

    /**
     * Get field mapping hints for this provider
     * 
     * @return array Array of field mapping suggestions
     */
    public function getFieldMappingHints()
    {
        return [];
    }

    /**
     * Get API client instance (if provider has API access)
     * 
     * @return object|null API client instance or null
     */
    public function getApiClient()
    {
        return null;
    }

    /**
     * Check if provider is enabled
     * 
     * @return bool True if provider is enabled
     */
    public function isEnabled()
    {
        $option_name = 'form_sync_' . $this->getId() . '_enabled';
        return get_option($option_name) == '1';
    }

    /**
     * Get provider setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    protected function getSetting($key, $default = '')
    {
        $option_name = 'form_sync_' . $this->getId() . '_' . $key;
        return get_option($option_name, $default);
    }

    /**
     * Set provider setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success
     */
    protected function setSetting($key, $value)
    {
        $option_name = 'form_sync_' . $this->getId() . '_' . $key;
        return update_option($option_name, $value);
    }

    /**
     * Handle webhook request
     * 
     * This method processes the webhook request and delegates to the model
     * for submission processing. Can be overridden by providers for custom handling.
     * 
     * Processing flow:
     * 1. Read and parse raw POST data
     * 2. Validate payload structure
     * 3. Extract form ID, submission ID, and site ID
     * 4. Verify provider is enabled
     * 5. Load form configuration from database
     * 6. Verify webhook signature (if secret configured)
     * 7. Extract form data from payload
     * 8. Process submission via model
     * 
     * @param object $controller Controller instance
     * @return void
     */
    public function handleWebhook($controller)
    {
        // Wrap entire method in try-catch to prevent 500 errors
        try {
            // Step 1: Read raw POST data
            $raw_payload = @file_get_contents('php://input');
            $content_type = $controller->input->server('CONTENT_TYPE') ?: '';
            
            if (empty($raw_payload)) {
                log_message('warning', '[FormSync] ' . $this->getName() . ' webhook - Empty payload received. Request method: ' . ($controller->input->server('REQUEST_METHOD') ?: 'unknown') . ', Content-Type: ' . $content_type);
                $this->sendErrorResponse(400, 'Empty payload - webhook requires POST request with data');
                return;
            }

            // Step 2: Parse payload based on content type
            $payload = null;
            
            // Check if it's JSON
            if (stripos($content_type, 'application/json') !== false || empty($content_type)) {
                // Try to parse as JSON (default for most webhooks)
                $payload = json_decode($raw_payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Invalid JSON: ' . json_last_error_msg());
                    log_message('debug', '[FormSync] ' . $this->getName() . ' webhook - Raw payload (first 500 chars): ' . substr($raw_payload, 0, 500));
                    $this->sendErrorResponse(400, 'Invalid JSON: ' . json_last_error_msg());
                    return;
                }
            } elseif (stripos($content_type, 'application/x-www-form-urlencoded') !== false) {
                // Parse as form-urlencoded
                parse_str($raw_payload, $payload);
                if (empty($payload)) {
                    log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Failed to parse form-urlencoded data');
                    $this->sendErrorResponse(400, 'Invalid form data');
                    return;
                }
            } else {
                // Try JSON first, then form-urlencoded as fallback
                $payload = json_decode($raw_payload, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Fallback to form-urlencoded
                    parse_str($raw_payload, $payload);
                    if (empty($payload)) {
                        log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Failed to parse payload. Content-Type: ' . $content_type);
                        log_message('debug', '[FormSync] ' . $this->getName() . ' webhook - Raw payload (first 500 chars): ' . substr($raw_payload, 0, 500));
                        $this->sendErrorResponse(400, 'Invalid payload format');
                        return;
                    }
                }
            }
            
            // Ensure payload is an array
            if (!is_array($payload)) {
                log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Payload is not an array');
                $this->sendErrorResponse(400, 'Invalid payload structure');
                return;
            }
            
            // Log payload structure for debugging (only in development)
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] ' . $this->getName() . ' webhook - Payload keys: ' . json_encode(array_keys($payload)));
            }

            // Step 3: Validate payload structure
            if (!$this->validatePayload($payload)) {
                log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Invalid payload structure');
                $this->sendErrorResponse(400, 'Invalid payload');
                return;
            }

            // Step 4: Extract identifiers from payload/request
            $form_id = $this->extractFormId($payload, $controller);
            $submission_id = $this->extractSubmissionId($payload, $controller);
            $site_id = $this->extractSiteId($payload, $controller);

        // Log incoming webhook with detailed information
        log_message('info', '[FormSync] ' . $this->getName() . ' webhook received - Form ID: ' . ($form_id ?: 'unknown') . ', Submission ID: ' . ($submission_id ?: 'unknown') . ', Site ID: ' . ($site_id ?: 'unknown'));
        log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Payload keys: ' . json_encode(array_keys($payload)));
        log_message('info', '[FormSync] ' . $this->getName() . ' webhook - URL segments: ' . json_encode($controller->uri->segment_array()));
        
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] ' . $this->getName() . ' webhook - Full payload: ' . json_encode($payload));
        }

        // Validate form_id is present
        if (empty($form_id)) {
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Form ID missing');
            
            // Log the submission even though we can't process it
            $controller->load->model('form_sync/form_sync_model');
            $form_data = $this->extractFormData($payload);
            $controller->form_sync_model->logSubmission(
                $submission_id ?: 'unknown',
                'unknown',
                'failed',
                'lead',
                null,
                null,
                'Form ID required',
                $form_data,
                $this->getId(),
                $site_id,
                null,
                'none',
                null,
                null,
                null,
                'none'
            );
            
            // Return 200 to prevent webhook retries
            $this->sendErrorResponse(200, 'Form ID required');
            return;
        }

        // Step 5: Check if provider is enabled
        if (!$this->isEnabled()) {
            log_message('warning', '[FormSync] ' . $this->getName() . ' webhook - Provider is disabled');
            
            // Log the submission even though provider is disabled
            $controller->load->model('form_sync/form_sync_model');
            $form_data = $this->extractFormData($payload);
            $controller->form_sync_model->logSubmission(
                $submission_id ?: 'unknown',
                $form_id,
                'failed',
                'lead',
                null,
                null,
                'Provider is disabled',
                $form_data,
                $this->getId(),
                $site_id,
                null,
                'none',
                null,
                null,
                null,
                'none'
            );
            
            $this->sendErrorResponse(200, 'Provider is disabled');
            return;
        }

        // Step 6: Get form configuration from database
        log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Looking up form configuration for Form ID: ' . $form_id . ', Provider: ' . $this->getId() . ', Site ID: ' . ($site_id ?: 'none'));
        $form_config = $this->getFormConfiguration($controller, $form_id, $site_id);
        
        if ($form_config) {
            log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Form configuration found: ID ' . (isset($form_config['id']) ? $form_config['id'] : 'unknown'));
        } else {
            log_message('warning', '[FormSync] ' . $this->getName() . ' webhook - Form configuration NOT found for Form ID: ' . $form_id . ', Provider: ' . $this->getId());
        }
        
        // For Webflow: If config not found, try to find by site_id or find unassigned configs and auto-update
        if (!$form_config && $this->getId() === 'webflow' && !empty($site_id)) {
            log_message('info', '[FormSync] Webflow webhook - Form ID mismatch, trying to find config by site_id: ' . $site_id);
            $controller->load->model('form_sync/form_sync_model');
            
            // First, try to find configs with matching site_id
            $site_configs = $controller->form_sync_model->getFormConfigurationsBySite($site_id, 'webflow');
            
            // If no configs with site_id, try to find Webflow configs without site_id (unassigned)
            if (empty($site_configs)) {
                $all_webflow_configs = $controller->form_sync_model->get_form_configurations([
                    'provider' => 'webflow',
                    'site_id' => null
                ]);
                
                // If exactly one unassigned Webflow config, use it
                if (count($all_webflow_configs) === 1) {
                    $site_configs = $all_webflow_configs;
                    log_message('info', '[FormSync] Webflow webhook - Found unassigned config, will assign site_id and update form_id');
                }
            }
            
            // If exactly one config found (by site_id or unassigned), use it
            // CRITICAL: NEVER update form_id - it must remain constant once set by the user
            // We match by site_id, not by form_id, so webhooks work even if form_id differs
            if (count($site_configs) === 1) {
                $form_config = $site_configs[0];
                $config_id = is_object($form_config) ? $form_config->id : $form_config['id'];
                $old_form_id = is_object($form_config) ? $form_config->form_id : $form_config['form_id'];
                $old_site_id = is_object($form_config) ? (isset($form_config->site_id) ? $form_config->site_id : null) : (isset($form_config['site_id']) ? $form_config['site_id'] : null);
                
                // ALWAYS use the config's form_id (user-entered value), NEVER the webhook's form_id
                // This ensures form_id remains constant - RULE #1
                if (!empty($old_form_id)) {
                    log_message('info', '[FormSync] Webflow webhook - Using existing form_id from config: "' . $old_form_id . '" (webhook form_id: "' . $form_id . '" ignored)');
                    $form_id = $old_form_id; // Use config's form_id for processing
                }
                // Note: We do NOT update form_id even if empty - let user set it manually
                
                $update_data = [];
                
                // Only update site_id if not already set
                if (empty($old_site_id)) {
                    $update_data['site_id'] = $site_id;
                    
                    // Regenerate webhook URL with form_id (using config's form_id, not webhook's)
                    $provider_instance = $controller->form_sync_provider_manager->getProvider('webflow');
                    if ($provider_instance && !empty($form_id)) {
                        $update_data['webhook_url'] = $provider_instance->getWebhookUrl($form_id, $site_id);
                        log_message('info', '[FormSync] Webflow webhook - Regenerated webhook URL: ' . $update_data['webhook_url']);
                    }
                }
                
                // CRITICAL: form_id is NEVER added to update_data - it must never change
                // Only update if there's something to update (site_id or webhook_url)
                if (!empty($update_data)) {
                    $controller->form_sync_model->update_form_configuration($config_id, $update_data);
                    // Reload the config with updated values
                    $form_config = $controller->form_sync_model->get_form_configuration($config_id);
                    log_message('info', '[FormSync] Webflow webhook - Config auto-updated (site_id/webhook_url only, form_id unchanged)');
                } else {
                    log_message('info', '[FormSync] Webflow webhook - Config found by site_id, no updates needed');
                }
            } elseif (count($site_configs) > 1) {
                log_message('warning', '[FormSync] Webflow webhook - Multiple configs found for site_id: ' . $site_id . ' - Cannot auto-update. Please ensure form_id matches.');
            }
        }
        
        if (!$form_config) {
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Form configuration not found: ' . $form_id . (empty($site_id) ? '' : ' (Site ID: ' . $site_id . ')'));
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Available form configurations for provider ' . $this->getId() . ': Checking database...');
            
            // Log the submission even though configuration is not found
            try {
                $controller->load->model('form_sync/form_sync_model');
                $form_data = $this->extractFormData($payload);
                
                log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Attempting to log submission with Form ID: ' . $form_id);
                
                $log_id = $controller->form_sync_model->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'failed',
                    'lead',
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'Form configuration not found',
                    $form_data,
                    $this->getId(),
                    $site_id,
                    null, // site_name
                    'none', // hold_status
                    null, // duplicate_reason
                    null, // duplicate_entity_type
                    null, // duplicate_entity_id
                    'none' // hold_reason_type
                );
                
                if ($log_id) {
                    log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Submission logged successfully with ID: ' . $log_id);
                } else {
                    log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Failed to log submission (logSubmission returned false/null)');
                }
            } catch (Exception $e) {
                log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Exception while logging submission: ' . $e->getMessage());
                log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Stack trace: ' . $e->getTraceAsString());
            }
            
            // Return 200 to prevent webhook retries
            $this->sendErrorResponse(200, 'Form configuration not found');
            return;
        }

        // Step 7: Verify webhook signature if secret is configured
        if (!$this->verifyWebhookSignature($controller, $form_config, $form_id)) {
            return; // Error response already sent
        }

        // Step 8: Auto-populate site_id in form configuration if not set
        $this->autoPopulateSiteId($controller, $form_config, $site_id);

        // Step 9: Extract form data and process submission
        $form_data = $this->extractFormData($payload);
        $this->processSubmission($controller, $form_data, $form_id, $submission_id, $site_id, $form_config);
        
        } catch (Throwable $e) {
            // Catch any uncaught exceptions to prevent 500 errors
            $error_message = $e->getMessage();
            $error_file = $e->getFile();
            $error_line = $e->getLine();
            
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Uncaught exception: ' . $error_message);
            log_message('error', '[FormSync] Error in ' . $error_file . ':' . $error_line);
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            
            // Try to log the submission if we have enough info
            try {
                $controller->load->model('form_sync/form_sync_model');
                $form_id_extracted = isset($form_id) ? $form_id : 'unknown';
                $site_id_extracted = isset($site_id) ? $site_id : null;
                
                $controller->form_sync_model->logSubmission(
                    isset($submission_id) ? $submission_id : 'unknown',
                    $form_id_extracted,
                    'failed',
                    'lead',
                    null,
                    null,
                    'Uncaught exception: ' . $error_message,
                    isset($payload) ? $payload : null,
                    $this->getId(),
                    $site_id_extracted,
                    null,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
            } catch (Exception $log_error) {
                log_message('error', '[FormSync] Failed to log submission after exception: ' . $log_error->getMessage());
            }
            
            // Clear any previous output
            if (ob_get_level() > 0) {
                ob_clean();
            }
            
            // Set proper headers
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            
            $response = ['error' => 'Processing failed'];
            
            // Include detailed error message in development mode
            if (ENVIRONMENT === 'development') {
                $response['message'] = $error_message . ' in ' . basename($error_file) . ':' . $error_line;
            } else {
                $response['message'] = 'Internal server error';
            }
            
            echo json_encode($response);
            exit;
        }
    }
    
    /**
     * Get form configuration from database
     * 
     * Attempts to find form configuration by site_id+form_id first,
     * then falls back to form_id+provider.
     * 
     * @param object $controller Controller instance
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (optional)
     * @return array|null Form configuration array or null if not found
     */
    private function getFormConfiguration($controller, $form_id, $site_id)
    {
        try {
            $controller->load->model('form_sync/form_sync_model');
            $form_config = null;
            
            // Try to find by site_id + form_id first (more specific)
            if (!empty($site_id)) {
                log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Searching for config with Site ID: ' . $site_id . ', Form ID: ' . $form_id . ', Provider: ' . $this->getId());
                $form_config = $controller->form_sync_model->getFormConfigurationBySiteAndForm($site_id, $form_id, $this->getId());
                if ($form_config) {
                    log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Found config by site_id+form_id');
                }
            }
            
            // Fallback to form_id + provider if not found
            if (!$form_config) {
                log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Searching for config with Form ID: ' . $form_id . ', Provider: ' . $this->getId());
                $form_config = $controller->form_sync_model->getFormConfigurationByProvider($form_id, $this->getId());
                if ($form_config) {
                    log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Found config by form_id+provider');
                }
            }
            
            // Convert to array if object
            if ($form_config && is_object($form_config)) {
                $form_config = (array)$form_config;
            }
            
            return $form_config;
        } catch (Exception $e) {
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Error looking up form configuration: ' . $e->getMessage());
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Verify webhook signature
     * 
     * Verifies the webhook signature if a secret is configured.
     * 
     * @param object $controller Controller instance
     * @param array $form_config Form configuration array
     * @param string $form_id Form ID (for error messages)
     * @return bool True if signature is valid or not required, false otherwise
     */
    private function verifyWebhookSignature($controller, $form_config, $form_id)
    {
        $webhook_secret = isset($form_config['webhook_secret']) ? $form_config['webhook_secret'] : null;
        
        // Skip verification if no secret configured
        if (empty($webhook_secret)) {
            return true;
        }
        
        // Verify signature
        $is_valid = $this->verifySignature($controller, $webhook_secret);
        
        if (!$is_valid) {
            log_message('error', '[FormSync] ' . $this->getName() . ' webhook - Invalid signature for form: ' . $form_id);
            $this->sendErrorResponse(401, 'Your webhook requires authentication. Please verify the webhook secret is correctly configured in both the provider and the form configuration.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Auto-populate site_id in form configuration
     * 
     * If site_id was extracted from payload but not stored in configuration,
     * automatically update the configuration to include it.
     * Also regenerates webhook URL if needed (for Webflow).
     * 
     * @param object $controller Controller instance
     * @param array $form_config Form configuration array
     * @param string|null $site_id Site ID from payload
     * @return void
     */
    private function autoPopulateSiteId($controller, $form_config, $site_id)
    {
        $form_config_id = isset($form_config['id']) ? $form_config['id'] : null;
        $form_config_site_id = isset($form_config['site_id']) ? $form_config['site_id'] : null;
        $form_config_form_id = isset($form_config['form_id']) ? $form_config['form_id'] : null;
        
        // Update if site_id is available but not stored in config
        if (!empty($site_id) && empty($form_config_site_id) && $form_config_id) {
            $update_data = ['site_id' => $site_id];
            
            // For Webflow, also regenerate webhook URL with site_id
            if ($this->getId() === 'webflow' && !empty($form_config_form_id)) {
                $provider_instance = $controller->form_sync_provider_manager->getProvider('webflow');
                if ($provider_instance) {
                    $update_data['webhook_url'] = $provider_instance->getWebhookUrl($form_config_form_id, $site_id);
                    log_message('info', '[FormSync] Webflow webhook - Regenerated webhook URL with site_id: ' . $update_data['webhook_url']);
                }
            }
            
            $controller->form_sync_model->update_form_configuration($form_config_id, $update_data);
        }
    }
    
    /**
     * Process webhook submission
     * 
     * Extracts form data and processes the submission via the model.
     * Handles all errors gracefully to prevent webhook retries.
     * 
     * @param object $controller Controller instance
     * @param array $form_data Extracted form data
     * @param string $form_id Form ID
     * @param string|null $submission_id Submission ID
     * @param string|null $site_id Site ID
     * @param array $form_config Form configuration
     * @return void
     */
    private function processSubmission($controller, $form_data, $form_id, $submission_id, $site_id, $form_config)
    {
        try {
            log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Starting processing for form: ' . $form_id . ', submission: ' . ($submission_id ?: 'unknown'));
            
            $controller->form_sync_model->processWebhookSubmission(
                $form_data,
                $form_id,
                $this->getId(),
                $submission_id,
                $site_id,
                isset($form_config['site_name']) ? $form_config['site_name'] : null
            );
            
            log_message('info', '[FormSync] ' . $this->getName() . ' webhook - Processing completed successfully for form: ' . $form_id);
            
            // Clear any previous output
            if (ob_get_level() > 0) {
                ob_clean();
            }
            
            // Return success response
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode(['success' => true]);
            
        } catch (Throwable $e) {
            // Catch all errors including fatal errors
            $this->handleProcessingError($e, $form_id, $submission_id);
        }
    }
    
    /**
     * Handle processing errors
     * 
     * Logs detailed error information and returns appropriate response.
     * Returns 200 to prevent webhook retries (submission is already logged).
     * 
     * @param Throwable $e Exception/Error object
     * @param string $form_id Form ID
     * @param string|null $submission_id Submission ID
     * @return void
     */
    private function handleProcessingError($e, $form_id, $submission_id)
    {
        $error_message = $e->getMessage();
        $error_file = $e->getFile();
        $error_line = $e->getLine();
        $error_trace = $e->getTraceAsString();
        
        log_message('error', '[FormSync] ' . $this->getName() . ' webhook processing error: ' . $error_message);
        log_message('error', '[FormSync] Error in ' . $error_file . ':' . $error_line);
        log_message('error', '[FormSync] Stack trace: ' . $error_trace);
        log_message('error', '[FormSync] Form ID: ' . $form_id . ', Submission ID: ' . ($submission_id ?: 'unknown'));
        
        // Clear any previous output
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Return 200 to prevent webhook retries, but log the error
        // The processWebhookSubmission method should have already logged the submission
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        $response = ['error' => 'Processing failed'];
        
        // Include detailed error message in development mode
        if (ENVIRONMENT === 'development') {
            $response['message'] = $error_message;
        } else {
            $response['message'] = 'Internal server error';
        }
        
        echo json_encode($response);
    }
    
    /**
     * Send error response
     * 
     * Helper method to send consistent error responses.
     * 
     * @param int $status_code HTTP status code
     * @param string $message Error message
     * @return void
     */
    private function sendErrorResponse($status_code, $message)
    {
        // Clear any previous output
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Set proper headers
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        
        // Send JSON response
        echo json_encode(['error' => $message]);
        exit;
    }
}

