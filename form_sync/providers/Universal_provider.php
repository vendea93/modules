<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Universal Provider
 * 
 * Handles form submissions from any form provider via webhooks with configurable payload structure.
 * Supports flat, nested, array-based, and custom JSON path structures.
 * 
 * @package    FormSync
 * @subpackage Providers
 * @category   Module
 * @author     LiquidApps Studio
 */
class Universal_provider extends App_form_provider
{
    /**
     * Default metadata fields to filter from form data
     * 
     * @var array
     */
    private $default_metadata_fields = ['form_id', 'submission_id', 'timestamp', 'site_id', 'id'];

    /**
     * Current form ID (set during extraction, used for settings lookup)
     * 
     * @var string|null
     */
    private $current_form_id = null;

    /**
     * Current controller instance (for auto-detection)
     * 
     * @var object|null
     */
    private $current_controller = null;

    /**
     * Get provider identifier
     * 
     * @return string
     */
    public function getId()
    {
        return 'universal';
    }

    /**
     * Get provider name
     * 
     * @return string
     */
    public function getName()
    {
        return 'Universal';
    }

    /**
     * Get provider description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Connect any form provider via webhook with configurable payload structure.';
    }

    /**
     * Generate webhook URL for a form
     * 
     * @param string $form_id Form ID
     * @param string|null $site_id Site ID (not used for Universal)
     * @return string
     */
    public function getWebhookUrl($form_id, $site_id = null)
    {
        return site_url('form_sync/webhook/universal/' . $form_id);
    }

    /**
     * Get custom provider settings from form configuration
     * 
     * @param string $form_id Form ID
     * @return array Settings array or default settings
     */
    private function getCustomSettings($form_id)
    {
        try {
            if (!isset($this->ci) || !$this->ci) {
                $this->ci = &get_instance();
            }
            
            $this->ci->load->model('form_sync/form_sync_model');
            $settings = $this->ci->form_sync_model->getCustomProviderSettings($form_id, $this->getId());
            
            if ($settings && is_array($settings)) {
                return $settings;
            }
        } catch (Exception $e) {
            log_message('error', '[FormSync] Universal provider - Error loading custom settings: ' . $e->getMessage());
        }
        
        // Return default settings
        return $this->getDefaultSettings();
    }

    /**
     * Get default settings structure
     * 
     * @return array
     */
    private function getDefaultSettings()
    {
        return [
            'payload_structure' => 'flat',
            'data_path' => '',
            'form_id_source' => 'url',
            'form_id_path' => 'form_id',
            'submission_id_source' => 'auto',
            'submission_id_path' => 'submission_id',
            'site_id_source' => 'none',
            'site_id_path' => 'site_id',
            'metadata_fields' => $this->default_metadata_fields,
            'signature_verification' => [
                'enabled' => false,
                'method' => 'header',
                'header_name' => 'X-Signature',
                'algorithm' => 'sha256'
            ]
        ];
    }

    /**
     * Verify webhook signature
     * 
     * Supports header-based and HMAC signature verification.
     * Uses custom_provider_settings to determine verification method.
     * 
     * @param object $request Request object (controller instance)
     * @param string $secret Webhook secret from form configuration
     * @return bool True if signature is valid or no secret configured, false otherwise
     */
    public function verifySignature($request, $secret)
    {
        // If no secret configured, skip verification (optional)
        if (empty($secret)) {
            return true;
        }

        // Load webhook library for header access
        $request->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $request->form_sync_webhook;

        // Try to get settings for signature verification method
        $form_id = $this->current_form_id;
        $settings = null;
        if ($form_id) {
            $settings = $this->getCustomSettings($form_id);
        }
        
        // Get signature verification settings
        $sig_enabled = false;
        $sig_method = 'header';
        $sig_header_name = 'X-Signature';
        
        if ($settings && isset($settings['signature_verification'])) {
            $sig_config = $settings['signature_verification'];
            $sig_enabled = isset($sig_config['enabled']) ? $sig_config['enabled'] : false;
            $sig_method = isset($sig_config['method']) ? $sig_config['method'] : 'header';
            $sig_header_name = isset($sig_config['header_name']) ? $sig_config['header_name'] : 'X-Signature';
        }
        
        // If signature verification is not enabled in settings, skip verification entirely
        // This allows webhooks to work even if a secret is configured but verification is disabled
        if (!$sig_enabled) {
            log_message('info', '[FormSync] Universal webhook - Signature verification disabled in settings, skipping verification');
            return true;
        }

        // Get signature from header
        $signature_header = $webhook_lib->getHeader($sig_header_name);
        
        if (empty($signature_header)) {
            log_message('warning', '[FormSync] Universal webhook - Signature header missing (' . $sig_header_name . '). Available headers: ' . json_encode($webhook_lib->getAllHeaders()));
            return false;
        }

        // Verify based on method
        $is_valid = false;
        
        if ($sig_method === 'hmac') {
            // HMAC verification
            $raw_payload = @file_get_contents('php://input');
            if ($raw_payload === false) {
                log_message('error', '[FormSync] Universal webhook - Failed to read raw payload for HMAC verification');
                return false;
            }
            
            // Generate expected HMAC signature
            $algorithm = isset($settings['signature_verification']['algorithm']) ? $settings['signature_verification']['algorithm'] : 'sha256';
            $expected_signature = hash_hmac($algorithm, $raw_payload, $secret);
            
            // Compare signatures (timing-safe)
            $is_valid = hash_equals($expected_signature, $signature_header);
        } else {
            // Header-based comparison (simple secret comparison)
            $is_valid = hash_equals($secret, $signature_header);
        }
        
        if (!$is_valid) {
            log_message('error', '[FormSync] Universal webhook - Signature verification failed (method: ' . $sig_method . ')');
        } else {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Universal webhook - Signature verification successful (method: ' . $sig_method . ')');
            }
        }
        
        return $is_valid;
    }

    /**
     * Extract form data from payload
     * 
     * Supports multiple payload structures:
     * - Flat: Direct field mapping
     * - Nested: Extract from configured path (e.g., payload.data)
     * - Array: Convert array of objects to flat key-value pairs
     * - Custom: Use dot notation to navigate JSON structure
     * 
     * @param array $payload Raw payload
     * @return array Extracted form fields (flat array with field names as keys)
     */
    public function extractFormData($payload)
    {
        // Use stored form_id from extractFormId call
        $form_id = $this->current_form_id;
        
        // Try to get settings (may not be available on first submission)
        $settings = null;
        $has_settings = false;
        if ($form_id) {
            $settings = $this->getCustomSettings($form_id);
            $has_settings = ($settings !== null);
        }
        
        // If no settings found, use defaults
        if (!$has_settings) {
            $settings = $this->getDefaultSettings();
        }
        
        $structure = isset($settings['payload_structure']) ? $settings['payload_structure'] : 'flat';
        $data_path = isset($settings['data_path']) ? $settings['data_path'] : '';
        $metadata_fields = isset($settings['metadata_fields']) ? $settings['metadata_fields'] : $this->default_metadata_fields;
        
        // Handle auto-detect: if structure is 'auto-detect' or no settings exist yet, detect and save
        if (($structure === 'auto-detect' || !$has_settings) && $form_id && $this->current_controller) {
            $detected = $this->detectPayloadStructure($payload);
            $structure = $detected['type'];
            $data_path = $detected['path'];
            
            // Save detected settings
            $detected_settings = $this->getDefaultSettings();
            $detected_settings['payload_structure'] = $structure;
            $detected_settings['data_path'] = $data_path;
            
            // Save to database
            try {
                if (!isset($this->ci) || !$this->ci) {
                    $this->ci = &get_instance();
                }
                $this->ci->load->model('form_sync/form_sync_model');
                $this->ci->form_sync_model->saveCustomProviderSettings($form_id, $this->getId(), $detected_settings);
                log_message('info', '[FormSync] Universal provider - Auto-detected and saved payload structure: ' . $structure . ' (path: ' . $data_path . ')');
            } catch (Exception $e) {
                log_message('error', '[FormSync] Universal provider - Error saving auto-detected settings: ' . $e->getMessage());
            }
        }
        
        $form_data = [];
        
        switch ($structure) {
            case 'nested':
                // Extract from nested path (e.g., payload.data)
                if (!empty($data_path)) {
                    $form_data = $this->extractFromPath($payload, $data_path);
                } else {
                    // Try common nested patterns
                    if (isset($payload['payload']['data'])) {
                        $form_data = $payload['payload']['data'];
                    } elseif (isset($payload['data'])) {
                        $form_data = $payload['data'];
                    } else {
                        log_message('warning', '[FormSync] Universal webhook - Nested structure specified but data path not found');
                        $form_data = [];
                    }
                }
                break;
                
            case 'array':
                // Extract from array of objects
                if (!empty($data_path)) {
                    $array_data = $this->extractFromPath($payload, $data_path);
                } else {
                    // Try common array patterns
                    if (isset($payload['fields']) && is_array($payload['fields'])) {
                        $array_data = $payload['fields'];
                    } elseif (isset($payload['items']) && is_array($payload['items'])) {
                        $array_data = $payload['items'];
                    } else {
                        log_message('warning', '[FormSync] Universal webhook - Array structure specified but array not found');
                        $array_data = [];
                    }
                }
                
                // Convert array of objects to flat key-value pairs
                $form_data = $this->convertArrayToFlat($array_data);
                break;
                
            case 'custom':
                // Extract from custom JSON path
                if (!empty($data_path)) {
                    $form_data = $this->extractFromPath($payload, $data_path);
                } else {
                    log_message('warning', '[FormSync] Universal webhook - Custom structure specified but data path not provided');
                    $form_data = [];
                }
                break;
                
            case 'flat':
            default:
                // Flat structure - return payload as-is, filter metadata
                $form_data = $payload;
                break;
        }
        
        // Filter metadata fields
        if (is_array($form_data)) {
            foreach ($metadata_fields as $metadata_field) {
                unset($form_data[$metadata_field]);
            }
        }
        
        // Ensure we return an array
        if (!is_array($form_data)) {
            log_message('warning', '[FormSync] Universal webhook - Form data is not an array after extraction');
            return [];
        }
        
        return $form_data;
    }

    /**
     * Extract value from JSON path using dot notation
     * 
     * @param array $data Data array
     * @param string $path Dot notation path (e.g., "payload.data.fields")
     * @return mixed Extracted value or null
     */
    private function extractFromPath($data, $path)
    {
        if (empty($path)) {
            return $data;
        }
        
        $parts = explode('.', $path);
        $current = $data;
        
        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } else {
                log_message('warning', '[FormSync] Universal webhook - Path segment not found: ' . $part . ' in path: ' . $path);
                return [];
            }
        }
        
        return is_array($current) ? $current : [];
    }

    /**
     * Convert array of objects to flat key-value pairs
     * 
     * Supports multiple formats:
     * - [{"id": "name", "value": "John"}]
     * - [{"field": "name", "value": "John"}]
     * - [{"name": "name", "value": "John"}]
     * - [{"key": "name", "value": "John"}]
     * 
     * @param array $array_data Array of field objects
     * @return array Flat key-value array
     */
    private function convertArrayToFlat($array_data)
    {
        $form_data = [];
        
        if (!is_array($array_data)) {
            return $form_data;
        }
        
        foreach ($array_data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            // Try different field name keys
            $field_name = null;
            if (isset($item['id'])) {
                $field_name = $item['id'];
            } elseif (isset($item['field'])) {
                $field_name = $item['field'];
            } elseif (isset($item['name'])) {
                $field_name = $item['name'];
            } elseif (isset($item['key'])) {
                $field_name = $item['key'];
            } elseif (isset($item['title'])) {
                $field_name = $item['title'];
            }
            
            // Try different value keys
            $field_value = null;
            if (isset($item['value'])) {
                $field_value = $item['value'];
            } elseif (isset($item['answer'])) {
                $field_value = $item['answer'];
            } elseif (isset($item['response'])) {
                $field_value = $item['response'];
            }
            
            if ($field_name !== null && $field_value !== null) {
                $form_data[$field_name] = $field_value;
            }
        }
        
        return $form_data;
    }

    /**
     * Extract form ID from payload or request
     * 
     * Supports multiple sources:
     * - URL path (default)
     * - Payload field (configurable path)
     * - Header (configurable header name)
     * - Query parameter (fallback)
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string|null Form ID or null if not found
     */
    public function extractFormId($payload, $request)
    {
        // Store controller for auto-detection
        $this->current_controller = $request;
        
        // Try to get settings (may not be available on first submission)
        $form_id_from_url = $request->uri->segment(4);
        $settings = null;
        
        if ($form_id_from_url) {
            $settings = $this->getCustomSettings($form_id_from_url);
        } else {
            $settings = $this->getDefaultSettings();
        }
        
        $source = isset($settings['form_id_source']) ? $settings['form_id_source'] : 'url';
        $path = isset($settings['form_id_path']) ? $settings['form_id_path'] : 'form_id';
        
        $form_id = null;
        
        switch ($source) {
            case 'payload':
                // Extract from payload using path
                if (!empty($path)) {
                    $form_id = $this->extractFromPath($payload, $path);
                    if (!is_string($form_id) && !is_numeric($form_id)) {
                        $form_id = null;
                    } else {
                        $form_id = (string)$form_id;
                    }
                } else {
                    // Try common payload locations
                    if (isset($payload['form_id'])) {
                        $form_id = $payload['form_id'];
                    } elseif (isset($payload['formId'])) {
                        $form_id = $payload['formId'];
                    } elseif (isset($payload['payload']['formId'])) {
                        $form_id = $payload['payload']['formId'];
                    }
                }
                break;
                
            case 'header':
                // Extract from header
                $request->load->library('form_sync/form_sync_webhook');
                $webhook_lib = $request->form_sync_webhook;
                $form_id = $webhook_lib->getHeader($path);
                break;
                
            case 'url':
            default:
                // Extract from URL path (default)
                $form_id = $request->uri->segment(4);
                break;
        }
        
        // Fallback: check query parameter
        if (empty($form_id)) {
            $form_id = $request->input->get('form_id');
        }
        
        // Validate form_id is not empty
        if (empty($form_id)) {
            log_message('warning', '[FormSync] Universal webhook - Form ID not found. Source: ' . $source . ', Path: ' . $path);
        } else {
            log_message('info', '[FormSync] Universal webhook - Form ID extracted: ' . $form_id . ' (source: ' . $source . ')');
            // Store form_id for use in extractFormData
            $this->current_form_id = $form_id;
        }
        
        return $form_id ?: null;
    }

    /**
     * Extract submission ID from payload or request
     * 
     * Supports multiple sources:
     * - Payload field (configurable path)
     * - Header (configurable header name)
     * - Auto-generate (UUID if not found)
     * 
     * @param array $payload Raw payload
     * @param object $request Request object (controller instance)
     * @return string Submission ID (always returns a value, never null)
     */
    public function extractSubmissionId($payload, $request)
    {
        // Try to get settings
        $form_id_from_url = $request->uri->segment(4);
        $settings = null;
        
        if ($form_id_from_url) {
            $settings = $this->getCustomSettings($form_id_from_url);
        } else {
            $settings = $this->getDefaultSettings();
        }
        
        $source = isset($settings['submission_id_source']) ? $settings['submission_id_source'] : 'auto';
        $path = isset($settings['submission_id_path']) ? $settings['submission_id_path'] : 'submission_id';
        
        $submission_id = null;
        
        switch ($source) {
            case 'payload':
                // Extract from payload using path
                if (!empty($path)) {
                    $submission_id = $this->extractFromPath($payload, $path);
                    if (!is_string($submission_id) && !is_numeric($submission_id)) {
                        $submission_id = null;
                    } else {
                        $submission_id = (string)$submission_id;
                    }
                } else {
                    // Try common payload locations
                    if (isset($payload['submission_id'])) {
                        $submission_id = $payload['submission_id'];
                    } elseif (isset($payload['id'])) {
                        $submission_id = $payload['id'];
                    } elseif (isset($payload['payload']['id'])) {
                        $submission_id = $payload['payload']['id'];
                    }
                }
                break;
                
            case 'header':
                // Extract from header
                $request->load->library('form_sync/form_sync_webhook');
                $webhook_lib = $request->form_sync_webhook;
                $submission_id = $webhook_lib->getHeader($path);
                break;
                
            case 'auto':
            default:
                // Will auto-generate below if not found
                break;
        }
        
        // Auto-generate UUID if not found
        if (empty($submission_id)) {
            // Generate UUID v4
            $submission_id = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            log_message('info', '[FormSync] Universal webhook - Submission ID auto-generated: ' . $submission_id);
        } else {
            log_message('info', '[FormSync] Universal webhook - Submission ID extracted: ' . $submission_id . ' (source: ' . $source . ')');
        }
        
        return $submission_id;
    }

    /**
     * Extract site ID from payload or request
     * 
     * Optional field for multi-site scenarios.
     * 
     * @param array $payload Raw payload
     * @param object|null $request Request object (optional)
     * @return string|null Site ID or null if not configured/found
     */
    public function extractSiteId($payload, $request = null)
    {
        if (!$request) {
            return null;
        }
        
        // Try to get settings
        $form_id_from_url = $request->uri->segment(4);
        $settings = null;
        
        if ($form_id_from_url) {
            $settings = $this->getCustomSettings($form_id_from_url);
        } else {
            $settings = $this->getDefaultSettings();
        }
        
        $source = isset($settings['site_id_source']) ? $settings['site_id_source'] : 'none';
        $path = isset($settings['site_id_path']) ? $settings['site_id_path'] : 'site_id';
        
        if ($source === 'none') {
            return null;
        }
        
        $site_id = null;
        
        switch ($source) {
            case 'payload':
                // Extract from payload using path
                if (!empty($path)) {
                    $site_id = $this->extractFromPath($payload, $path);
                    if (!is_string($site_id) && !is_numeric($site_id)) {
                        $site_id = null;
                    } else {
                        $site_id = (string)$site_id;
                    }
                } else {
                    // Try common payload locations
                    if (isset($payload['site_id'])) {
                        $site_id = $payload['site_id'];
                    } elseif (isset($payload['siteId'])) {
                        $site_id = $payload['siteId'];
                    } elseif (isset($payload['payload']['siteId'])) {
                        $site_id = $payload['payload']['siteId'];
                    }
                }
                break;
                
            case 'header':
                // Extract from header
                $request->load->library('form_sync/form_sync_webhook');
                $webhook_lib = $request->form_sync_webhook;
                $site_id = $webhook_lib->getHeader($path);
                break;
        }
        
        if ($site_id) {
            log_message('info', '[FormSync] Universal webhook - Site ID extracted: ' . $site_id . ' (source: ' . $source . ')');
        }
        
        return $site_id ?: null;
    }

    /**
     * Validate payload structure
     * 
     * Validates that the payload matches the configured structure.
     * Checks data path existence for nested/custom structures.
     * 
     * @param array $payload Raw payload
     * @return bool True if payload is valid
     */
    public function validatePayload($payload)
    {
        // Basic validation: ensure payload is an array
        if (!is_array($payload)) {
            log_message('error', '[FormSync] Universal webhook - Payload is not an array');
            return false;
        }
        
        // Check if payload is empty
        if (empty($payload)) {
            log_message('warning', '[FormSync] Universal webhook - Payload is empty');
            // Empty payloads are technically valid (might be a test webhook)
            return true;
        }
        
        // Try to get settings for validation (optional, won't fail if not available)
        $form_id = $this->current_form_id;
        $settings = null;
        if ($form_id) {
            $settings = $this->getCustomSettings($form_id);
        }
        
        // If settings exist, validate data path
        if ($settings) {
            $structure = isset($settings['payload_structure']) ? $settings['payload_structure'] : 'flat';
            $data_path = isset($settings['data_path']) ? $settings['data_path'] : '';
            
            // For nested/custom structures, validate data path exists
            if (in_array($structure, ['nested', 'custom']) && !empty($data_path)) {
                $extracted_data = $this->extractFromPath($payload, $data_path);
                if (empty($extracted_data) || !is_array($extracted_data)) {
                    log_message('error', '[FormSync] Universal webhook - Data path not found or invalid: ' . $data_path);
                    return false;
                }
            }
            
            // For array structures, validate array exists
            if ($structure === 'array') {
                if (!empty($data_path)) {
                    $array_data = $this->extractFromPath($payload, $data_path);
                    if (!is_array($array_data) || empty($array_data)) {
                        log_message('error', '[FormSync] Universal webhook - Array data not found at path: ' . $data_path);
                        return false;
                    }
                } else {
                    // Try common array patterns
                    $has_array = false;
                    if (isset($payload['fields']) && is_array($payload['fields']) && !empty($payload['fields'])) {
                        $has_array = true;
                    } elseif (isset($payload['items']) && is_array($payload['items']) && !empty($payload['items'])) {
                        $has_array = true;
                    }
                    
                    if (!$has_array) {
                        log_message('error', '[FormSync] Universal webhook - Array structure specified but no array found in payload');
                        return false;
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * Get provider-specific settings fields
     * 
     * @return array Array of setting field definitions
     */
    public function getSettingsFields()
    {
        return [
            [
                'name' => 'payload_structure',
                'label' => 'Payload Structure',
                'type' => 'select',
                'options' => [
                    'flat' => 'Flat (field names as keys)',
                    'nested' => 'Nested (e.g., payload.data)',
                    'array' => 'Array-based (array of field objects)',
                    'custom' => 'Custom JSON Path',
                    'auto-detect' => 'Auto-detect on first submission'
                ],
                'default' => 'flat',
                'help' => 'Select the structure of your webhook payload. Auto-detect will analyze the first submission and save the detected structure.'
            ],
            [
                'name' => 'data_path',
                'label' => 'Data Path',
                'type' => 'text',
                'default' => '',
                'help' => 'JSON path to form data using dot notation (e.g., "payload.data" or "form.values"). Leave empty for flat structure.',
                'show_when' => ['payload_structure' => ['nested', 'custom']]
            ],
            [
                'name' => 'form_id_source',
                'label' => 'Form ID Source',
                'type' => 'select',
                'options' => [
                    'url' => 'URL Path (default)',
                    'payload' => 'Payload Field',
                    'header' => 'HTTP Header'
                ],
                'default' => 'url',
                'help' => 'Where to extract the form ID from. URL path is recommended for most cases.'
            ],
            [
                'name' => 'form_id_path',
                'label' => 'Form ID Path/Name',
                'type' => 'text',
                'default' => 'form_id',
                'help' => 'JSON path (for payload) or header name (for header). Examples: "payload.formId" or "X-Form-ID"',
                'show_when' => ['form_id_source' => ['payload', 'header']]
            ],
            [
                'name' => 'submission_id_source',
                'label' => 'Submission ID Source',
                'type' => 'select',
                'options' => [
                    'payload' => 'Payload Field',
                    'header' => 'HTTP Header',
                    'auto' => 'Auto-generate (UUID)'
                ],
                'default' => 'auto',
                'help' => 'Where to extract the submission ID from. Auto-generate creates a UUID if not found.'
            ],
            [
                'name' => 'submission_id_path',
                'label' => 'Submission ID Path/Name',
                'type' => 'text',
                'default' => 'submission_id',
                'help' => 'JSON path (for payload) or header name (for header). Examples: "payload.id" or "X-Submission-ID"',
                'show_when' => ['submission_id_source' => ['payload', 'header']]
            ],
            [
                'name' => 'site_id_source',
                'label' => 'Site ID Source',
                'type' => 'select',
                'options' => [
                    'none' => 'None (not used)',
                    'payload' => 'Payload Field',
                    'header' => 'HTTP Header'
                ],
                'default' => 'none',
                'help' => 'Optional: Extract site ID for multi-site scenarios.'
            ],
            [
                'name' => 'site_id_path',
                'label' => 'Site ID Path/Name',
                'type' => 'text',
                'default' => 'site_id',
                'help' => 'JSON path (for payload) or header name (for header).',
                'show_when' => ['site_id_source' => ['payload', 'header']]
            ],
            [
                'name' => 'metadata_fields',
                'label' => 'Metadata Fields',
                'type' => 'text',
                'default' => implode(', ', $this->default_metadata_fields),
                'help' => 'Comma-separated list of field names to exclude from form data (e.g., form_id, submission_id, timestamp).'
            ],
            [
                'name' => 'signature_verification_enabled',
                'label' => 'Enable Signature Verification',
                'type' => 'checkbox',
                'default' => false,
                'help' => 'Enable webhook signature verification for security.'
            ],
            [
                'name' => 'signature_method',
                'label' => 'Signature Method',
                'type' => 'select',
                'options' => [
                    'header' => 'Header-based (compare secret with header value)',
                    'hmac' => 'HMAC (SHA-256)'
                ],
                'default' => 'header',
                'help' => 'Signature verification method.',
                'show_when' => ['signature_verification_enabled' => true]
            ],
            [
                'name' => 'signature_header_name',
                'label' => 'Signature Header Name',
                'type' => 'text',
                'default' => 'X-Signature',
                'help' => 'HTTP header name containing the signature.',
                'show_when' => ['signature_verification_enabled' => true]
            ]
        ];
    }

    /**
     * Get setup instructions
     * 
     * @return array Array of instruction steps
     */
    public function getSetupInstructions()
    {
        return [
            'Go to <strong>Form Configurations</strong> and create a new form configuration. Select <strong>Universal</strong> as the provider.',
            'After creating the form configuration, you\'ll see a <strong>webhook URL</strong>. Copy this URL - you\'ll need it in the next step.',
            'In your form provider (Gravity Forms, Typeform, etc.), configure the webhook to send POST requests to the URL from Step 2. Make sure the request format is <strong>JSON</strong>.',
            'Configure the <strong>Payload Structure</strong> settings:',
            '<ul style="margin-left: 20px; margin-top: 10px;">' .
                '<li><strong>Flat:</strong> Field names are direct keys in the JSON (e.g., {"name": "John", "email": "john@example.com"})</li>' .
                '<li><strong>Nested:</strong> Form data is nested (e.g., {"payload": {"data": {"name": "John"}}})</li>' .
                '<li><strong>Array:</strong> Fields are in an array (e.g., {"fields": [{"id": "name", "value": "John"}]})</li>' .
                '<li><strong>Custom:</strong> Specify a custom JSON path using dot notation</li>' .
                '<li><strong>Auto-detect:</strong> Submit a test form first, and the system will detect the structure automatically</li>' .
            '</ul>',
            'If using <strong>Auto-detect</strong>, submit a test form and check the logs. The detected structure will be saved automatically.',
            'Configure <strong>Form ID Source</strong>:',
            '<ul style="margin-left: 20px; margin-top: 10px;">' .
                '<li><strong>URL Path:</strong> Form ID is in the webhook URL (recommended)</li>' .
                '<li><strong>Payload Field:</strong> Form ID is in the JSON payload (specify the path)</li>' .
                '<li><strong>HTTP Header:</strong> Form ID is in a header (specify the header name)</li>' .
            '</ul>',
            'Configure <strong>Submission ID Source</strong> (optional, defaults to auto-generate):',
            '<ul style="margin-left: 20px; margin-top: 10px;">' .
                '<li><strong>Payload Field:</strong> Extract from JSON payload</li>' .
                '<li><strong>HTTP Header:</strong> Extract from HTTP header</li>' .
                '<li><strong>Auto-generate:</strong> System creates a UUID if not found</li>' .
            '</ul>',
            'Set up <strong>field mappings</strong> to map your form fields to Perfex CRM fields (name, email, phone, etc.).',
            'Test your setup by submitting a test form. Check the <strong>Logs page</strong> to verify the submission was received and processed correctly.',
            'If you encounter issues, check the logs for detailed error messages. Common issues include incorrect payload structure settings or missing form ID.'
        ];
    }

    /**
     * Detect payload structure automatically
     * 
     * Analyzes the payload and detects the structure type.
     * 
     * @param array $payload Raw payload
     * @return array Detected structure info ['type' => 'flat|nested|array', 'path' => 'data path if nested/array']
     */
    public function detectPayloadStructure($payload)
    {
        if (!is_array($payload)) {
            return ['type' => 'flat', 'path' => ''];
        }
        
        // Check for nested structure (payload.data pattern)
        if (isset($payload['payload']['data']) && is_array($payload['payload']['data'])) {
            log_message('info', '[FormSync] Universal provider - Detected nested structure: payload.data');
            return ['type' => 'nested', 'path' => 'payload.data'];
        }
        
        // Check for nested structure (data pattern)
        if (isset($payload['data']) && is_array($payload['data']) && !$this->isFlatStructure($payload['data'])) {
            log_message('info', '[FormSync] Universal provider - Detected nested structure: data');
            return ['type' => 'nested', 'path' => 'data'];
        }
        
        // Check for array structure (fields array)
        if (isset($payload['fields']) && is_array($payload['fields']) && $this->isArrayOfObjects($payload['fields'])) {
            log_message('info', '[FormSync] Universal provider - Detected array structure: fields');
            return ['type' => 'array', 'path' => 'fields'];
        }
        
        // Check for array structure (items array)
        if (isset($payload['items']) && is_array($payload['items']) && $this->isArrayOfObjects($payload['items'])) {
            log_message('info', '[FormSync] Universal provider - Detected array structure: items');
            return ['type' => 'array', 'path' => 'items'];
        }
        
        // Default to flat structure
        log_message('info', '[FormSync] Universal provider - Detected flat structure');
        return ['type' => 'flat', 'path' => ''];
    }

    /**
     * Check if data is a flat structure (no nested objects)
     * 
     * @param array $data Data to check
     * @return bool True if flat structure
     */
    private function isFlatStructure($data)
    {
        if (!is_array($data)) {
            return false;
        }
        
        foreach ($data as $value) {
            if (is_array($value) && !empty($value)) {
                // Check if it's an array of objects (not flat)
                if ($this->isArrayOfObjects($value)) {
                    return false;
                }
                // Check if it's a nested object
                if (!$this->isSimpleArray($value)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Check if array is an array of objects (for field extraction)
     * 
     * @param array $array Array to check
     * @return bool True if array of objects
     */
    private function isArrayOfObjects($array)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }
        
        // Check if first element is an associative array (object-like)
        $first = reset($array);
        if (!is_array($first)) {
            return false;
        }
        
        // Check if it has field-like structure (id/field/name + value/answer)
        $has_field_key = isset($first['id']) || isset($first['field']) || isset($first['name']) || isset($first['key']);
        $has_value_key = isset($first['value']) || isset($first['answer']) || isset($first['response']);
        
        return $has_field_key && $has_value_key;
    }

    /**
     * Check if array is a simple array (not object-like)
     * 
     * @param array $array Array to check
     * @return bool True if simple array
     */
    private function isSimpleArray($array)
    {
        if (empty($array)) {
            return true;
        }
        
        // Simple arrays have numeric keys
        return array_keys($array) === range(0, count($array) - 1);
    }
}
