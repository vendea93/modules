<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Provider Manager
 * 
 * Manages provider registration, discovery, and instantiation.
 * Singleton pattern for managing all form providers.
 * 
 * @package    FormSync
 * @subpackage Libraries
 * @category   Module
 * @author     LiquidApps Studio
 */
class Form_sync_provider_manager
{
    /**
     * CodeIgniter instance
     * 
     * @var object
     */
    private $ci;

    /**
     * Registered providers cache
     * 
     * @var array
     */
    private $providers = [];

    /**
     * Provider instances cache
     * 
     * @var array
     */
    private $instances = [];

    /**
     * Singleton instance
     * 
     * @var Form_sync_provider_manager
     */
    private static $instance = null;

    /**
     * Constructor
     * 
     * Initializes the provider manager and auto-discovers available providers.
     * Handles cases where CodeIgniter may not be fully initialized yet.
     * 
     * Must be public for CodeIgniter MX library loader to instantiate.
     * Singleton pattern is maintained through getInstance() method.
     * 
     * @return void
     */
    public function __construct()
    {
        try {
            // Get CI instance - may not be available during early initialization
            $this->initializeCodeIgniterInstance();
            
            // Always run auto-discovery in constructor (doesn't require CI)
            $this->autoDiscoverProviders();
        } catch (Throwable $e) {
            // Catch all errors (Exception, Error, etc.) to prevent fatal errors
            // Allow instance to be created even if auto-discovery fails
            // This prevents 500 errors if a provider file has issues
            log_message('error', '[FormSync] Provider manager constructor error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize CodeIgniter instance
     * 
     * Attempts to get the CI instance, handling cases where it may not
     * be available during early initialization.
     * 
     * @return void
     */
    private function initializeCodeIgniterInstance()
    {
        if (function_exists('get_instance')) {
            try {
                $this->ci = &get_instance();
            } catch (Exception $e) {
                // CI not ready yet, will be set later if needed
                $this->ci = null;
            }
        }
    }

    /**
     * Get singleton instance
     * 
     * If CodeIgniter has already loaded an instance, use that.
     * Otherwise, create a new instance.
     * 
     * @return Form_sync_provider_manager
     */
    public static function getInstance()
    {
        // If CodeIgniter has loaded the library, try to get it from CI instance
        $ci = &get_instance();
        if (isset($ci->form_sync_provider_manager)) {
            return $ci->form_sync_provider_manager;
        }
        
        // Otherwise, create new instance (singleton pattern)
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Auto-discover providers in providers/ directory
     * 
     * Scans the providers directory for provider classes and registers them.
     * Skips base class and example providers.
     * 
     * @return void
     */
    private function autoDiscoverProviders()
    {
        // Skip if already discovered
        if (!empty($this->providers)) {
            return;
        }
        
        // Get providers directory path
        $providers_path = $this->getProvidersPath();
        if (!$providers_path) {
            return;
        }

        // Load base provider class first (required for all providers)
        $this->loadBaseProviderClass($providers_path);

        // Discover and register all provider classes
        $this->discoverAndRegisterProviders($providers_path);
    }
    
    /**
     * Get providers directory path
     * 
     * Attempts to find the providers directory using multiple methods:
     * 1. module_dir_path() function (if available)
     * 2. Relative path from this file
     * 
     * @return string|null Providers directory path or null if not found
     */
    private function getProvidersPath()
    {
        $providers_path = null;
        
        // Try module_dir_path first (preferred method)
        if (function_exists('module_dir_path')) {
            $providers_path = module_dir_path('form_sync', 'providers');
        }
        
        // Fallback: construct path directly if module_dir_path not available or returns invalid path
        if (empty($providers_path) || !is_dir($providers_path)) {
            // Try to find the path relative to this file
            $this_file = __FILE__;
            $libraries_dir = dirname($this_file);
            $module_dir = dirname($libraries_dir);
            $providers_path = $module_dir . '/providers/';
        }
        
        // Ensure trailing slash
        $providers_path = rtrim($providers_path, '/') . '/';
        
        if (!is_dir($providers_path)) {
            log_message('error', '[FormSync] Providers directory not found: ' . $providers_path);
            return null;
        }
        
        return $providers_path;
    }
    
    /**
     * Load base provider class
     * 
     * Loads the App_form_provider base class that all providers must extend.
     * 
     * @param string $providers_path Path to providers directory
     * @return void
     */
    private function loadBaseProviderClass($providers_path)
    {
        $base_provider_path = $providers_path . 'App_form_provider.php';
        
        if (file_exists($base_provider_path)) {
            require_once $base_provider_path;
        }
    }
    
    /**
     * Discover and register provider classes
     * 
     * Scans for provider files and registers valid provider classes.
     * Skips base class and example providers.
     * 
     * @param string $providers_path Path to providers directory
     * @return void
     */
    private function discoverAndRegisterProviders($providers_path)
    {
        // Get all PHP files matching provider naming pattern
        $files = glob($providers_path . '*_provider.php');
        
        foreach ($files as $file) {
            $class_name = basename($file, '.php');
            
            // Skip base class and example providers
            if ($class_name === 'App_form_provider' || $class_name === 'Example_provider') {
                continue;
            }
            
            // Include the provider file directly (providers are not CodeIgniter libraries)
            try {
                require_once $file;
            } catch (Exception $e) {
                log_message('error', '[FormSync] Failed to include provider file: ' . $file . ' - ' . $e->getMessage());
                continue;
            }
            
            // Register provider if valid
            $this->registerProvider($class_name);
        }
    }

    /**
     * Register a provider class
     * 
     * Validates and registers a provider class. The provider must extend App_form_provider
     * and implement all required methods. Providers are stored keyed by provider_id,
     * not class_name, to allow for consistent access.
     * 
     * @param string $class_name Provider class name
     * @return bool True if registration succeeded, false otherwise
     */
    public function registerProvider($class_name)
    {
        // Validate class exists
        if (!class_exists($class_name)) {
            log_message('error', '[FormSync] Provider class not found: ' . $class_name);
            return false;
        }

        // Validate class extends base provider
        if (!is_subclass_of($class_name, 'App_form_provider')) {
            log_message('error', '[FormSync] Provider class must extend App_form_provider: ' . $class_name);
            return false;
        }

        // Create instance to get provider ID
        $instance = $this->createProviderInstance($class_name);
        if (!$instance) {
            return false;
        }
        
        $provider_id = $instance->getId();

        // Store provider (keyed by provider_id, not class_name)
        $this->providers[$provider_id] = $class_name;
        $this->instances[$provider_id] = $instance;

        log_message('info', '[FormSync] Registered provider: ' . $provider_id . ' (' . $class_name . ')');
        
        return true;
    }
    
    /**
     * Create provider instance
     * 
     * Attempts to instantiate a provider class and returns the instance.
     * 
     * @param string $class_name Provider class name
     * @return App_form_provider|null Provider instance or null on failure
     */
    private function createProviderInstance($class_name)
    {
        try {
            $instance = new $class_name();
            return $instance;
        } catch (Exception $e) {
            log_message('error', '[FormSync] Failed to create provider instance: ' . $class_name . ' - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get provider instance by ID
     * 
     * @param string $provider_id Provider ID
     * @return App_form_provider|null Provider instance or null
     */
    public function getProvider($provider_id)
    {
        if (isset($this->instances[$provider_id])) {
            return $this->instances[$provider_id];
        }

        if (isset($this->providers[$provider_id])) {
            $class_name = $this->providers[$provider_id];
            $instance = new $class_name();
            $this->instances[$provider_id] = $instance;
            return $instance;
        }

        return null;
    }

    /**
     * Get all registered providers
     * 
     * @return array Array of provider instances
     */
    public function getAllProviders()
    {
        // If no providers discovered yet, try to discover them now
        if (empty($this->providers)) {
            $this->autoDiscoverProviders();
        }
        
        $result = [];
        
        foreach ($this->providers as $provider_id => $class_name) {
            $result[$provider_id] = $this->getProvider($provider_id);
        }
        
        return $result;
    }

    /**
     * Get only enabled providers
     * 
     * @return array Array of enabled provider instances
     */
    public function getEnabledProviders()
    {
        $result = [];
        
        foreach ($this->getAllProviders() as $provider_id => $provider) {
            if ($provider->isEnabled()) {
                $result[$provider_id] = $provider;
            }
        }
        
        return $result;
    }

    /**
     * Detect provider from webhook request
     * 
     * Attempts to identify the provider based on request headers, URL, or payload.
     * 
     * @param object $input CodeIgniter input object
     * @return string|null Provider ID or null if not detected
     */
    public function detectProvider($input)
    {
        // Try to detect from URL segment
        $uri = $this->ci->uri;
        $segments = $uri->segment_array();
        
        // Check if provider is in URL: form_sync/webhook/{provider}
        if (isset($segments[2]) && $segments[2] === 'webhook' && isset($segments[3])) {
            $provider_id = $segments[3];
            if ($this->getProvider($provider_id)) {
                return $provider_id;
            }
        }

        // Try to detect from headers
        $this->ci->load->library('form_sync/form_sync_webhook');
        $webhook_lib = $this->ci->form_sync_webhook;
        
        // Check for Framer headers
        if ($webhook_lib->getHeader('Framer-Signature') || $webhook_lib->getHeader('Framer-Webhook-Submission-Id')) {
            return 'framer';
        }
        
        // Check for Webflow headers
        if ($webhook_lib->getHeader('x-webflow-signature') || $webhook_lib->getHeader('x-webflow-timestamp')) {
            return 'webflow';
        }

        // Try to detect from payload structure
        $raw_payload = @file_get_contents('php://input');
        if (!empty($raw_payload)) {
            $payload = json_decode($raw_payload, true);
            
            if (is_array($payload)) {
                // Webflow has nested payload structure
                if (isset($payload['payload']['formId']) || isset($payload['payload']['siteId'])) {
                    return 'webflow';
                }
                
                // Elementor can be detected by:
                // - Standard mode: form_id and form_name at root level (not nested)
                // - Advanced mode: form object with id/name, plus fields array
                if ((isset($payload['form_id']) && isset($payload['form_name'])) || 
                    (isset($payload['form']['id']) && isset($payload['form']['name']) && isset($payload['fields']))) {
                    return 'elementor';
                }
                
                // Framer has flat structure with field names as keys
                // This is less reliable, but can be a fallback
                if (isset($payload['triggerType']) === false && !isset($payload['payload'])) {
                    // Might be Framer, but not definitive
                    // Check if we have Framer-specific patterns
                }
            }
        }

        return null;
    }

    /**
     * Check if provider exists
     * 
     * @param string $provider_id Provider ID
     * @return bool True if provider exists
     */
    public function providerExists($provider_id)
    {
        return isset($this->providers[$provider_id]);
    }

    /**
     * Get provider IDs list
     * 
     * @return array Array of provider IDs
     */
    public function getProviderIds()
    {
        return array_keys($this->providers);
    }
}

