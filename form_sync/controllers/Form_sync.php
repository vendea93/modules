<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Controller
 * 
 * Main controller for the FormSync module.
 * Handles all module-related requests and views.
 * 
 * @package    FormSync
 * @subpackage Controllers
 * @category   Module
 * @author     LiquidApps Studio
 */

class Form_sync extends AdminController
{
    /**
     * Constructor
     * 
     * Initializes the controller and determines if the request is a webhook
     * (which requires public access) or an admin method (which requires authentication).
     * 
     * Uses a multi-layered detection approach to reliably identify webhook requests:
     * 1. Primary: Check URI segment 2 (most reliable for module controllers)
     * 2. Secondary: Check router method (if available)
     * 3. Tertiary: Check REQUEST_URI directly (fallback)
     * 
     * @return void
     */
    public function __construct()
    {
        // #region agent log
        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:32','message'=>'Constructor entry','data'=>['uri'=>$_SERVER['REQUEST_URI']??'unknown'],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        // Detect if this is a webhook request (public access, no authentication)
        $is_webhook = $this->detectWebhookRequest();
        
        // #region agent log
        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:38','message'=>'Webhook detection result','data'=>['is_webhook'=>$is_webhook],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        if ($is_webhook) {
            // For webhook methods, extend App_Controller behavior (public access)
            // Don't call parent::__construct() which requires authentication
            App_Controller::__construct();
            
            // Load required models and libraries for webhooks
            $this->load->model('form_sync/form_sync_model');
            $this->load->library('form_sync/form_sync_webhook');
            $this->load->library('form_sync/form_sync_provider_manager');
        } else {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:48','message'=>'Before parent::__construct() call','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            
            // For admin methods, use normal AdminController behavior
            try {
                // Set error handler to catch database connection errors
                set_error_handler(function($errno, $errstr, $errfile, $errline) {
                    if (strpos($errstr, 'Operation not permitted') !== false || strpos($errstr, 'mysqli') !== false) {
                        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:61','message'=>'Database error caught in error handler','data'=>['error'=>$errstr,'file'=>$errfile,'line'=>$errline],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                    }
                    return false; // Let PHP handle it normally
                }, E_WARNING | E_ERROR);
                
                parent::__construct();
                
                // Restore error handler
                restore_error_handler();
                
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:72','message'=>'parent::__construct() succeeded','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
            } catch (Throwable $e) {
                // Restore error handler if still set
                restore_error_handler();
                
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:78','message'=>'parent::__construct() failed','data'=>['error'=>$e->getMessage(),'trace'=>substr($e->getTraceAsString(),0,500)],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                log_message('error', '[FormSync] Constructor error: ' . $e->getMessage());
                // Don't rethrow - let it fail gracefully
            }
            
            // Load required models and libraries
            try {
                $this->load->model('form_sync/form_sync_model');
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:64','message'=>'Model loaded successfully','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
            } catch (Throwable $e) {
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:67','message'=>'Model load failed','data'=>['error'=>$e->getMessage()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
            }
            
            try {
                $this->load->library('form_sync/form_sync_provider_manager');
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:72','message'=>'Provider manager loaded successfully','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
            } catch (Exception $e) {
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:75','message'=>'Provider manager load failed','data'=>['error'=>$e->getMessage()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
                // #endregion
                log_message('error', '[FormSync] Failed to load provider manager: ' . $e->getMessage());
            }
        }
        
        // #region agent log
        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync.php:80','message'=>'Constructor exit','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
    }
    
    /**
     * Detect if the current request is a webhook request
     * 
     * Uses multi-layered detection approach for reliability:
     * - Primary: Check URI segment 2 (most reliable for module controllers)
     * - Secondary: Check router method (if available)
     * - Tertiary: Check REQUEST_URI directly (fallback)
     * 
     * @return bool True if this is a webhook request, false otherwise
     */
    private function detectWebhookRequest()
    {
        // Primary: Check URI segment 2 (most reliable for module controllers)
        // For URL /form_sync/webhook/framer/demo1, segment(2) = 'webhook'
        if (isset($this->uri) && $this->uri->segment(2) !== false) {
            $method_name = $this->uri->segment(2);
            if (in_array($method_name, ['webhook', 'webhook_framer', 'webhook_webflow'])) {
                return true;
            }
        }
        
        // Secondary: Check router method (if available)
        if (isset($this->router) && !empty($this->router->method)) {
            if (in_array($this->router->method, ['webhook', 'webhook_framer', 'webhook_webflow'])) {
                return true;
            }
        }
        
        // Tertiary: Check REQUEST_URI directly (fallback)
        if (isset($_SERVER['REQUEST_URI'])) {
            if (preg_match('#/form_sync/(webhook|webhook_framer|webhook_webflow)#', $_SERVER['REQUEST_URI'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Index page - redirects to settings
     * 
     * Default entry point for the FormSync module.
     * Redirects users to the settings page.
     * 
     * @return void
     */
    public function index()
    {
        redirect(admin_url('form_sync/settings'));
    }
    
    /**
     * Settings page
     * 
     * Displays and handles form provider settings.
     * Allows administrators to enable/disable form providers.
     * 
     * @return void
     */
    public function settings()
    {
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'Form_sync.php:120','message'=>'settings() method entry','data'=>['uri'=>$_SERVER['REQUEST_URI']??'unknown'],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        if (!staff_can('edit', 'form_sync')) {
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'G','location'=>'Form_sync.php:123','message'=>'Permission check failed','data'=>['has_permission'=>false],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            access_denied('form_sync');
        }
        
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'Form_sync.php:128','message'=>'Permission check passed, loading license library','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        // Load license library with error handling
        try {
            $this->load->library('form_sync/form_sync_license');
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'Form_sync.php:131','message'=>'License library loaded successfully','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
        } catch (Exception $e) {
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'Form_sync.php:133','message'=>'License library load failed','data'=>['error'=>$e->getMessage()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Failed to load license library: ' . $e->getMessage());
            show_error('Failed to load FormSync license library. Please check error logs.', 500);
            return;
        }
        
        // Ensure library is loaded (should already be from constructor)
        if (!isset($this->form_sync_provider_manager)) {
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync.php:139','message'=>'Provider manager not set, loading','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $this->load->library('form_sync/form_sync_provider_manager');
        }
        
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync.php:143','message'=>'Getting provider manager instance','data'=>['has_manager'=>isset($this->form_sync_provider_manager)],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        $provider_manager = $this->form_sync_provider_manager;
        
        // Only process provider settings if license is valid
        if ($this->input->post() && $this->form_sync_license->isLicenseValid()) {
            $post_data = $this->input->post();
            
            // Update provider enabled status dynamically
            foreach ($provider_manager->getAllProviders() as $provider_id => $provider) {
                $option_name = 'form_sync_' . $provider_id . '_enabled';
                $enabled = isset($post_data[$provider_id . '_enabled']) ? '1' : '0';
                update_option($option_name, $enabled);
            }
            
            set_alert('success', _l('form_sync_settings_saved'));
            redirect(admin_url('form_sync/settings'));
        }
        
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync.php:158','message'=>'Before getAllProviders() call','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        // Get all providers and their enabled status
        try {
            $data['providers'] = $provider_manager->getAllProviders();
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync.php:162','message'=>'getAllProviders() succeeded','data'=>['provider_count'=>count($data['providers']),'provider_ids'=>array_keys($data['providers'])],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
        } catch (Exception $e) {
            // #region agent log
            file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync.php:165','message'=>'getAllProviders() failed','data'=>['error'=>$e->getMessage()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $data['providers'] = [];
        }
        
        $data['title'] = _l('form_sync') . ' - ' . _l('settings');
        
        // Pass license status and details to view
        try {
            $data['license_valid'] = $this->form_sync_license->isLicenseValid();
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error checking license validity: ' . $e->getMessage());
            $data['license_valid'] = false;
        }
        
        try {
            $data['license_details'] = $this->form_sync_license->getLicenseDetails();
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error getting license details: ' . $e->getMessage());
            $data['license_details'] = null;
        }
        
        try {
            $data['support_active'] = $this->form_sync_license->isSupportActive();
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error checking support status: ' . $e->getMessage());
            $data['support_active'] = null;
        }
        
        try {
            $stored_code = $this->form_sync_license->getStoredPurchaseCode();
            $data['stored_purchase_code'] = !empty($stored_code) ? $stored_code : '';
            $data['purchase_code_masked'] = !empty($stored_code) ? $this->form_sync_license->maskPurchaseCode($stored_code) : '';
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error getting stored purchase code: ' . $e->getMessage());
            $data['stored_purchase_code'] = '';
            $data['purchase_code_masked'] = '';
        }
        
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'Form_sync.php:193','message'=>'Before load->view() call','data'=>['data_keys'=>array_keys($data),'has_providers'=>isset($data['providers'])],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        
        $this->load->view('form_sync/settings', $data);
        
        // #region agent log
        file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/application/logs/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'Form_sync.php:196','message'=>'After load->view() call','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
    }
    
    /**
     * Validate purchase code via AJAX
     * 
     * Validates the provided purchase code against the Envato API.
     * 
     * @return void Outputs JSON response
     */
    public function validate_purchase_code()
    {
        header('Content-Type: application/json');
        
        try {
            if (!staff_can('edit', 'form_sync')) {
                echo json_encode(['success' => false, 'message' => _l('access_denied')]);
                exit;
            }
            
            $purchase_code = $this->input->post('purchase_code');
            
            if (empty($purchase_code)) {
                echo json_encode(['success' => false, 'message' => _l('form_sync_license_code_required')]);
                exit;
            }
            
            // Load and use license library
            $this->load->library('form_sync/form_sync_license');
            $result = $this->form_sync_license->validatePurchaseCode($purchase_code);
            
            echo json_encode($result);
            exit;
            
        } catch (Exception $e) {
            log_message('error', '[FormSync] License validation error: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Validation error: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Deactivate license via AJAX
     * 
     * Clears the stored license data, requiring re-validation.
     * 
     * @return void Outputs JSON response
     */
    public function deactivate_license()
    {
        header('Content-Type: application/json');
        
        if (!staff_can('edit', 'form_sync')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            exit;
        }
        
        $this->load->library('form_sync/form_sync_license');
        $this->form_sync_license->clearLicenseData();
        
        echo json_encode([
            'success' => true, 
            'message' => _l('form_sync_license_deactivated')
        ]);
        exit;
    }
    
    /**
     * Re-validate stored purchase code via AJAX
     * 
     * Re-validates the stored purchase code without requiring user input.
     * 
     * @return void Outputs JSON response
     */
    public function revalidate_license()
    {
        header('Content-Type: application/json');
        
        if (!staff_can('edit', 'form_sync')) {
            echo json_encode(['success' => false, 'message' => _l('access_denied')]);
            exit;
        }
        
        try {
            $this->load->library('form_sync/form_sync_license');
            $result = $this->form_sync_license->revalidateStoredCode();
            
            echo json_encode($result);
            exit;
            
        } catch (Exception $e) {
            log_message('error', '[FormSync] License re-validation error: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Re-validation error: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    /**
     * Check if license is valid and redirect if not
     * 
     * Helper method to ensure license is valid before accessing module features.
     * 
     * @return bool True if valid, redirects otherwise
     */
    private function requireValidLicense()
    {
        $this->load->library('form_sync/form_sync_license');
        
        if (!$this->form_sync_license->isLicenseValid()) {
            set_alert('warning', _l('form_sync_license_required'));
            redirect(admin_url('form_sync/settings'));
            return false;
        }
        
        return true;
    }
    
    /**
     * Form configurations page
     * 
     * Displays and manages form configurations. Handles creating, updating,
     * and deleting form configurations. Also displays submission counts
     * to determine if field mapping is available.
     * 
     * @return void
     */
    public function form_configurations()
    {
        if (!staff_can('view', 'form_sync')) {
            access_denied('form_sync');
        }
        
        // Require valid license
        $this->requireValidLicense();
        
        // Handle form submissions (create/update/delete)
        if ($this->input->post() && staff_can('edit', 'form_sync')) {
            $this->handleFormConfigurationPost();
        }
        
        // Prepare view data
        $data = $this->prepareFormConfigurationsData();
        
        $data['title'] = _l('form_sync') . ' - ' . _l('form_sync_form_configurations');
        $this->load->view('form_sync/form_configurations', $data);
    }
    
    /**
     * Handle form configuration POST requests (save/delete)
     * 
     * Processes form submissions for creating, updating, or deleting
     * form configurations. Validates input and generates webhook URLs.
     * 
     * @return void
     */
    private function handleFormConfigurationPost()
    {
        $action = $this->input->post('action');
        
        if ($action === 'save') {
            $this->saveFormConfiguration();
        } elseif ($action === 'delete') {
            $this->deleteFormConfiguration();
        }
    }
    
    /**
     * Save form configuration (create or update)
     * 
     * Validates input, generates webhook URL, and saves configuration.
     * Auto-generates webhook secret for new configurations.
     * 
     * @return void
     */
    private function saveFormConfiguration()
    {
        // Extract and sanitize POST data
        $form_id = $this->input->post('form_id');
        $form_name = $this->input->post('form_name');
        $provider = $this->input->post('provider');
        $site_name = $this->input->post('site_name');
        $target_type = $this->input->post('target_type');
        $customer_group_id = $this->input->post('customer_group_id');
        $lead_source_id = $this->input->post('lead_source_id');
        $estimate_request_status_id = $this->input->post('estimate_request_status_id');
        $estimate_request_assigned_id = $this->input->post('estimate_request_assigned_id');
        $ticket_department_id = $this->input->post('ticket_department_id');
        $ticket_priority = $this->input->post('ticket_priority');
        $perfex_form_id = $this->input->post('perfex_form_id');
        $enabled = $this->input->post('enabled') ? 1 : 0;
        $config_id = $this->input->post('config_id');
        
        // Sanitize inputs
        $form_id = $this->sanitizeFormId($form_id);
        $form_name = $this->sanitizeString($form_name, 255);
        $provider = $this->sanitizeProvider($provider);
        $site_name = $this->sanitizeString($site_name, 255);
        $target_type = $this->sanitizeTargetType($target_type);
        $customer_group_id = !empty($customer_group_id) ? (int)$customer_group_id : null;
        $lead_source_id = !empty($lead_source_id) ? (int)$lead_source_id : null;
        $estimate_request_status_id = !empty($estimate_request_status_id) ? (int)$estimate_request_status_id : null;
        $estimate_request_assigned_id = !empty($estimate_request_assigned_id) ? (int)$estimate_request_assigned_id : null;
        $ticket_department_id = !empty($ticket_department_id) ? (int)$ticket_department_id : null;
        $ticket_priority = !empty($ticket_priority) ? (int)$ticket_priority : null;
        $perfex_form_id = !empty($perfex_form_id) ? (int)$perfex_form_id : null;
        $config_id = !empty($config_id) ? (int)$config_id : null;
        
        // Validate required fields
        if (empty($form_id)) {
            set_alert('danger', 'Form ID is required.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        if (empty($form_name)) {
            set_alert('danger', 'Form name is required.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Validate target_type - must be 'lead', 'customer', 'estimate_request', or 'ticket'
        if (!in_array($target_type, ['lead', 'customer', 'estimate_request', 'ticket'])) {
            set_alert('danger', 'Invalid target type. Must be Lead, Customer, Estimate Request, or Support Ticket.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Validate lead source is required when target_type is 'lead'
        if ($target_type === 'lead' && empty($lead_source_id)) {
            set_alert('danger', 'Lead source is required when target type is Lead.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Validate estimate request status is required when target_type is 'estimate_request'
        if ($target_type === 'estimate_request' && empty($estimate_request_status_id)) {
            set_alert('danger', _l('form_sync_estimate_request_status') . ' is required when target type is Estimate Request.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Validate perfex_form_id is required when target_type is 'estimate_request'
        if ($target_type === 'estimate_request') {
            if (empty($perfex_form_id)) {
                set_alert('danger', 'Perfex CRM Form is required when target type is Estimate Request.');
                redirect(admin_url('form_sync/form_configurations'));
                return;
            }
            
            // Validate that the form exists
            try {
                $this->load->model('estimate_request_model');
                $perfex_form = $this->estimate_request_model->get_form(['id' => $perfex_form_id]);
                if (!$perfex_form) {
                    set_alert('danger', 'Selected Perfex form does not exist.');
                    redirect(admin_url('form_sync/form_configurations'));
                    return;
                }
            } catch (Throwable $e) {
                log_message('error', '[FormSync] Failed to validate Perfex form: ' . $e->getMessage());
                set_alert('danger', 'Failed to validate Perfex form. Please try again.');
                redirect(admin_url('form_sync/form_configurations'));
                return;
            }
        } else {
            $perfex_form_id = null;
        }
        
        // Validate ticket department and priority are required when target_type is 'ticket'
        if ($target_type === 'ticket' && (empty($ticket_department_id) || empty($ticket_priority))) {
            set_alert('danger', _l('form_sync_ticket_department') . ' and ' . _l('form_sync_ticket_priority') . ' are required when target type is Support Ticket.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Validate provider using provider manager
        if (empty($provider)) {
            set_alert('danger', 'Provider is required.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        $provider_manager = $this->getProviderManager();
        $provider_instance = $provider_manager->getProvider($provider);
        
        if (!$provider_instance) {
            set_alert('danger', 'Invalid provider selected.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Handle webhook secret: generate for new configs (except Webflow), preserve for updates
        $webhook_secret = $this->getWebhookSecret($config_id, $provider);
        
        // For updates: preserve existing form_id - it should never change once set
        $form_id_to_use = $form_id;
        if ($config_id) {
            $existing_config = $this->form_sync_model->get_form_configuration($config_id);
            if ($existing_config) {
                $existing_form_id = is_object($existing_config) ? $existing_config->form_id : (isset($existing_config['form_id']) ? $existing_config['form_id'] : null);
                // If form_id already exists, preserve it (don't allow changes)
                if (!empty($existing_form_id)) {
                    $form_id_to_use = $existing_form_id;
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', '[FormSync] Preserving existing form_id: "' . $existing_form_id . '" (ignoring submitted form_id: "' . $form_id . '")');
                    }
                }
            }
        }
        
        // Generate webhook URL using provider instance
        // For Webflow, use form_id (same as Framer) - always available
        $webhook_url = $provider_instance->getWebhookUrl($form_id_to_use);
        
        // Prepare configuration data
        $config_data = [
            'form_id' => $form_id_to_use, // Use preserved form_id for updates
            'form_name' => $form_name,
            'provider' => $provider,
            'site_name' => !empty($site_name) ? $site_name : null,
            'target_type' => $target_type,
            'customer_group_id' => !empty($customer_group_id) ? (int)$customer_group_id : null,
            'lead_source_id' => !empty($lead_source_id) ? (int)$lead_source_id : null,
            'webhook_secret' => $webhook_secret,
            'webhook_url' => $webhook_url,
            'enabled' => $enabled,
        ];
        
        // Check if new columns exist before adding them to avoid DB errors
        try {
            $columns = $this->db->list_fields(db_prefix() . 'form_sync_form_configurations');
            if (in_array('estimate_request_status_id', $columns)) {
                $config_data['estimate_request_status_id'] = $estimate_request_status_id;
            }
            if (in_array('estimate_request_assigned_id', $columns)) {
                $config_data['estimate_request_assigned_id'] = $estimate_request_assigned_id;
            }
            if (in_array('ticket_department_id', $columns)) {
                $config_data['ticket_department_id'] = $ticket_department_id;
            }
            if (in_array('ticket_priority', $columns)) {
                $config_data['ticket_priority'] = $ticket_priority;
            }
            if (in_array('perfex_form_id', $columns)) {
                $config_data['perfex_form_id'] = $perfex_form_id;
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Could not check for new columns: ' . $e->getMessage());
            // Still try to include the fields - they might exist
            $config_data['estimate_request_status_id'] = $estimate_request_status_id;
            $config_data['estimate_request_assigned_id'] = $estimate_request_assigned_id;
            $config_data['ticket_department_id'] = $ticket_department_id;
            $config_data['ticket_priority'] = $ticket_priority;
            $config_data['perfex_form_id'] = $perfex_form_id;
        }
        
        // Save configuration (create or update)
        try {
            if ($config_id) {
                $this->form_sync_model->update_form_configuration($config_id, $config_data);
                $saved_config_id = $config_id;
            } else {
                $saved_config_id = $this->form_sync_model->add_form_configuration($config_data);
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to save form configuration: ' . $e->getMessage());
            set_alert('danger', 'Failed to save form configuration. Database columns may need to be updated. Please deactivate and reactivate the FormSync module.');
            redirect(admin_url('form_sync/form_configurations'));
            return;
        }
        
        // Handle Universal provider custom settings
        if ($provider === 'universal' && $saved_config_id) {
            try {
                $universal_settings = $this->extractUniversalProviderSettings();
                if (!empty($universal_settings)) {
                    $this->form_sync_model->saveCustomProviderSettings($form_id_to_use, $provider, $universal_settings);
                }
            } catch (Exception $e) {
                log_message('error', '[FormSync] Failed to save Universal provider settings: ' . $e->getMessage());
                // Don't fail the whole save, just log the error
            }
        }
        
        set_alert('success', _l('form_sync_form_config_saved'));
        redirect(admin_url('form_sync/form_configurations'));
    }
    
    /**
     * Delete form configuration
     * 
     * Removes a form configuration from the database.
     * 
     * @return void
     */
    private function deleteFormConfiguration()
    {
        $config_id = $this->input->post('config_id');
        
        if ($config_id) {
            if ($this->form_sync_model->delete_form_configuration($config_id)) {
                set_alert('success', _l('form_sync_form_config_deleted'));
            } else {
                set_alert('danger', 'Failed to delete form configuration.');
            }
        }
        
        redirect(admin_url('form_sync/form_configurations'));
    }
    
    /**
     * Get webhook secret for configuration
     * 
     * Generates a new secret for new configurations (except Webflow) or preserves
     * the existing secret for updates.
     * 
     * @param int|null $config_id Configuration ID (null for new configs)
     * @param string $provider Provider ID (e.g., 'framer', 'webflow')
     * @return string|null Webhook secret
     */
    private function getWebhookSecret($config_id, $provider)
    {
        // For Webflow, don't auto-generate - user must provide their own secret
        if ($provider === 'webflow') {
            // For new Webflow configs, return null (no secret)
            if (empty($config_id)) {
                return null;
            }
            
            // For updates, preserve existing secret if it exists
            $existing_config = $this->form_sync_model->get_form_configuration($config_id);
            if ($existing_config) {
                // Convert to array if object
                if (is_object($existing_config)) {
                    $existing_config = (array)$existing_config;
                }
                return isset($existing_config['webhook_secret']) ? $existing_config['webhook_secret'] : null;
            }
            
            return null;
        }
        
        // For other providers (e.g., Framer), auto-generate secret for new configs
        if (empty($config_id)) {
            // Generate a secure 32-character random secret
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $webhook_secret = '';
            for ($i = 0; $i < 32; $i++) {
                $webhook_secret .= $chars[random_int(0, strlen($chars) - 1)];
            }
            return $webhook_secret;
        }
        
        // For updates, keep existing secret (don't regenerate)
        $existing_config = $this->form_sync_model->get_form_configuration($config_id);
        if ($existing_config) {
            // Convert to array if object
            if (is_object($existing_config)) {
                $existing_config = (array)$existing_config;
            }
            return isset($existing_config['webhook_secret']) ? $existing_config['webhook_secret'] : null;
        }
        
        return null;
    }
    
    /**
     * Get provider manager instance
     * 
     * Returns the provider manager, using CodeIgniter-loaded instance
     * if available, otherwise creating a singleton instance.
     * 
     * @return Form_sync_provider_manager Provider manager instance
     */
    private function getProviderManager()
    {
        if (isset($this->form_sync_provider_manager)) {
            return $this->form_sync_provider_manager;
        }
        return Form_sync_provider_manager::getInstance();
    }
    
    /**
     * Prepare data for form configurations view
     * 
     * Gathers all necessary data for displaying the form configurations page,
     * including configurations, providers, customer groups, and lead sources.
     * 
     * @return array View data array
     */
    private function prepareFormConfigurationsData()
    {
        // Initialize data array with defaults
        $data = [
            'form_configs' => [],
            'providers' => [],
            'customer_groups' => [],
            'lead_sources' => [],
            'estimate_request_statuses' => [],
            'staff_members' => [],
            'ticket_departments' => [],
            'ticket_priorities' => [],
        ];
        
        // Get all form configurations with error handling
        try {
            $data['form_configs'] = $this->form_sync_model->get_form_configurations();
            if (!is_array($data['form_configs'])) {
                $data['form_configs'] = [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load form configurations: ' . $e->getMessage());
            $data['form_configs'] = [];
        }
        
        // Get provider manager once (for Webflow URL regeneration)
        try {
            $provider_manager = $this->getProviderManager();
            $webflow_provider = $provider_manager ? $provider_manager->getProvider('webflow') : null;
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to get provider manager: ' . $e->getMessage());
            $provider_manager = null;
            $webflow_provider = null;
        }
        
        // Check submission counts for each form to enable/disable map fields button
        // Field mapping is only available if at least one submission has been received
        try {
        foreach ($data['form_configs'] as &$config) {
            $submission_count = 0;
            
            // For Webflow, use more flexible matching
            if ($config['provider'] === 'webflow') {
                if (!empty($config['site_id'])) {
                    // Config has site_id: Try exact match first, then fallback to site_id only
                    // First try exact match (form_id + site_id + provider)
                    $this->db->where('form_id', $config['form_id']);
                    $this->db->where('site_id', $config['site_id']);
                    $this->db->where('provider', $config['provider']);
                    $this->db->where('submission_data IS NOT NULL');
                    $this->db->where('submission_data !=', '');
                    $submission_count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
                    
                    // If no exact match, try by site_id only (handles form_id mismatches)
                    if ($submission_count == 0) {
                        $this->db->reset_query();
                        $this->db->where('site_id', $config['site_id']);
                        $this->db->where('provider', $config['provider']);
                        $this->db->where('submission_data IS NOT NULL');
                        $this->db->where('submission_data !=', '');
                        $submission_count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
                    }
                } else {
                    // Config has no site_id yet: Check if there's only one Webflow config
                    // If so, check for any Webflow submissions (will be matched after first webhook)
                    $all_webflow_configs = $this->form_sync_model->get_form_configurations(['provider' => 'webflow']);
                    if (count($all_webflow_configs) === 1) {
                        // Only one Webflow config, check for any Webflow submissions
                        $this->db->where('provider', $config['provider']);
                        $this->db->where('submission_data IS NOT NULL');
                        $this->db->where('submission_data !=', '');
                        $submission_count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
                    } else {
                        // Multiple Webflow configs, use form_id only (less reliable but best we can do)
                        $this->db->where('form_id', $config['form_id']);
                        $this->db->where('provider', $config['provider']);
                        $this->db->where('submission_data IS NOT NULL');
                        $this->db->where('submission_data !=', '');
                        $submission_count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
                    }
                }
            } else {
                // For Framer and other providers, use form_id + provider
                $this->db->where('form_id', $config['form_id']);
                $this->db->where('provider', $config['provider']);
                $this->db->where('submission_data IS NOT NULL');
                $this->db->where('submission_data !=', '');
                $submission_count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
            }
            
            $config['has_submissions'] = $submission_count > 0;
            
            // For Webflow: Regenerate webhook URL to ensure it includes form_id (same as Framer)
            if ($config['provider'] === 'webflow' && $webflow_provider && !empty($config['form_id'])) {
                $expected_url = $webflow_provider->getWebhookUrl($config['form_id']);
                $current_url = isset($config['webhook_url']) ? $config['webhook_url'] : '';
                
                // Check if URL needs updating (should always include form_id like Framer)
                if (empty($current_url) || $current_url !== $expected_url || strpos($current_url, '/webflow/' . $config['form_id']) === false) {
                    // Update the webhook URL in database
                    if ($this->form_sync_model->update_form_configuration($config['id'], [
                        'webhook_url' => $expected_url
                    ])) {
                        // Update the config array for display
                        $config['webhook_url'] = $expected_url;
                        if (ENVIRONMENT === 'development') {
                            log_message('debug', '[FormSync] Webflow - Updated URL to include form_id: ' . $expected_url);
                        }
                    }
                }
            }
        }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Error processing form configs: ' . $e->getMessage());
        }
        unset($config); // Break reference to prevent accidental modifications
        
        // Get only enabled providers for dropdown
        try {
            $data['providers'] = $provider_manager ? $provider_manager->getEnabledProviders() : [];
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to get providers: ' . $e->getMessage());
            $data['providers'] = [];
        }
        
        // Get customer groups and lead sources for dropdowns
        try {
            $this->load->model('clients_model');
            $data['customer_groups'] = $this->clients_model->get_groups();
            if (!is_array($data['customer_groups'])) {
                $data['customer_groups'] = [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load customer groups: ' . $e->getMessage());
        }
        
        try {
            $this->load->model('leads_model');
            $data['lead_sources'] = $this->leads_model->get_source();
            if (!is_array($data['lead_sources'])) {
                $data['lead_sources'] = [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load lead sources: ' . $e->getMessage());
        }
        
        // Get estimate request statuses for dropdown
        try {
            if (class_exists('Estimate_request_model') || file_exists(APPPATH . 'models/Estimate_request_model.php')) {
                $this->load->model('estimate_request_model');
                $statuses = $this->estimate_request_model->get_status();
                $data['estimate_request_statuses'] = is_array($statuses) ? $statuses : [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load estimate request statuses: ' . $e->getMessage());
        }
        
        // Get staff members for estimate request assignment dropdown
        try {
            if (!isset($this->staff_model)) {
                $this->load->model('staff_model');
            }
            $staff = $this->staff_model->get('', ['active' => 1]);
            $data['staff_members'] = is_array($staff) ? $staff : [];
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load staff members: ' . $e->getMessage());
        }
        
        // Get ticket departments and priorities for dropdowns
        try {
            // Departments are in a separate model
            $this->load->model('departments_model');
            $departments = $this->departments_model->get();
            $data['ticket_departments'] = is_array($departments) ? $departments : [];
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load ticket departments: ' . $e->getMessage());
        }
        
        try {
            $this->load->model('tickets_model');
            $priorities = $this->tickets_model->get_priority();
            $data['ticket_priorities'] = is_array($priorities) ? $priorities : [];
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load ticket priorities: ' . $e->getMessage());
        }
        
        // Get existing Perfex estimate request forms for linking
        try {
            if (class_exists('Estimate_request_model') || file_exists(APPPATH . 'models/Estimate_request_model.php')) {
                $this->load->model('estimate_request_model');
                $forms = $this->estimate_request_model->get_forms();
                $data['perfex_estimate_forms'] = is_array($forms) ? $forms : [];
            } else {
                $data['perfex_estimate_forms'] = [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Failed to load Perfex estimate forms: ' . $e->getMessage());
            $data['perfex_estimate_forms'] = [];
        }
        
        return $data;
    }
    
    /**
     * Pending review page
     * 
     * Displays submissions that were held for review due to duplicate detection.
     * Administrators can approve or ignore these submissions.
     */
    public function pending_review()
    {
        if (!staff_can('view', 'form_sync')) {
            access_denied('form_sync');
        }
        
        // Require valid license
        $this->requireValidLicense();
        
        // Get filters
        $filters = [
            'form_id' => $this->input->get('form_id'),
            'target_type' => $this->input->get('target_type'),
        ];
        
        try {
            $data['logs'] = $this->form_sync_model->getHeldSubmissions($filters);
            
            // Check mapping availability for each log entry (especially for no_mappings type)
            if (is_array($data['logs'])) {
                foreach ($data['logs'] as &$log) {
                    try {
                        if (isset($log['form_id']) && isset($log['target_type'])) {
                            $log['has_mappings'] = $this->form_sync_model->hasFieldMappings($log['form_id'], $log['target_type']);
                        } else {
                            $log['has_mappings'] = false;
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error checking mappings for log: ' . $e->getMessage());
                        $log['has_mappings'] = false;
                    }
                }
                unset($log); // Break reference
            } else {
                $data['logs'] = [];
            }
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Error in pending_review: ' . $e->getMessage());
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            $data['logs'] = [];
        }
        
        // Get form configurations for filter dropdown
        try {
            $data['form_configs'] = $this->form_sync_model->get_form_configurations();
        } catch (Exception $e) {
            $data['form_configs'] = [];
        }
        
        $data['filters'] = $filters;
        $data['title'] = _l('form_sync') . ' - ' . _l('form_sync_pending_review');
        $this->load->view('form_sync/pending_review', $data);
    }

    /**
     * Approve held submission
     * 
     * Approves a submission that was held for review (e.g., due to duplicate detection).
     * Creates the lead or customer entity from the submission data.
     * 
     * @param int $log_id Submission log ID
     * @return void
     */
    public function approve_held($log_id)
    {
        if (!staff_can('edit', 'form_sync')) {
            access_denied('form_sync');
        }
        
        $result = $this->form_sync_model->approveHeldSubmission($log_id);
        
        if (is_array($result) && isset($result['success'])) {
            if ($result['success']) {
                set_alert('success', $result['message']);
            } else {
                $message = $result['message'];
                // If mappings not configured, add helpful link
                if (strpos($message, 'Field mappings not configured') !== false) {
                    $message .= ' <a href="' . admin_url('form_sync/form_configurations') . '">Configure mappings here</a>.';
                }
                set_alert('danger', $message);
            }
        } else {
            // Fallback for old return format (boolean)
            if ($result) {
                set_alert('success', 'Submission approved and entity created successfully.');
            } else {
                set_alert('danger', 'Failed to approve submission.');
            }
        }
        
        redirect(admin_url('form_sync/pending_review'));
    }

    /**
     * Ignore held submission
     * 
     * Marks a held submission as ignored, preventing it from being processed.
     * 
     * @param int $log_id Submission log ID
     * @return void
     */
    public function ignore_held($log_id)
    {
        if (!staff_can('edit', 'form_sync')) {
            access_denied('form_sync');
        }
        
        if ($this->form_sync_model->ignoreHeldSubmission($log_id)) {
            set_alert('success', 'Submission ignored.');
        } else {
            set_alert('danger', 'Failed to ignore submission.');
        }
        
        redirect(admin_url('form_sync/pending_review'));
    }

    /**
     * Bulk approve held submissions
     * 
     * Approves multiple held submissions at once. Processes each submission
     * independently, so failures don't prevent other submissions from being approved.
     * 
     * Supports both AJAX and form POST requests.
     * 
     * @return void
     */
    public function bulk_approve_held()
    {
        if (!staff_can('edit', 'form_sync')) {
            access_denied('form_sync');
        }
        
        // Validate CSRF token
        if (!$this->input->post('csrf_token_name')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
                exit;
            }
            set_alert('danger', 'Invalid security token. Please refresh the page and try again.');
            redirect(admin_url('form_sync/pending_review'));
            return;
        }
        
        // Get log IDs from POST
        $log_ids = $this->input->post('log_ids');
        
        // Validate input
        if (empty($log_ids) || !is_array($log_ids)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'No submissions selected.']);
                exit;
            }
            set_alert('warning', 'No submissions selected.');
            redirect(admin_url('form_sync/pending_review'));
            return;
        }
        
        // Process bulk approval
        $result = $this->form_sync_model->bulkApproveSubmissions($log_ids);
        
        // Prepare response message
        $message = '';
        if ($result['success_count'] > 0) {
            $message .= $result['success_count'] . ' submission' . ($result['success_count'] > 1 ? 's' : '') . ' approved successfully.';
        }
        if ($result['failed_count'] > 0) {
            if (!empty($message)) {
                $message .= ' ';
            }
            $message .= $result['failed_count'] . ' submission' . ($result['failed_count'] > 1 ? 's' : '') . ' failed.';
            
            // Add error details if available
            if (!empty($result['errors']) && count($result['errors']) <= 5) {
                $error_messages = array_column($result['errors'], 'message');
                $message .= ' Errors: ' . implode('; ', array_unique($error_messages));
            } elseif (!empty($result['errors'])) {
                $message .= ' (' . count($result['errors']) . ' errors - see logs for details)';
            }
        }
        
        // Handle AJAX vs form POST
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $result['failed_count'] == 0,
                'message' => $message,
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'errors' => $result['errors']
            ]);
            exit;
        }
        
        // Set alerts and redirect
        if ($result['success_count'] > 0 && $result['failed_count'] == 0) {
            set_alert('success', $message);
        } elseif ($result['success_count'] > 0 && $result['failed_count'] > 0) {
            set_alert('warning', $message);
        } else {
            set_alert('danger', $message);
        }
        
        redirect(admin_url('form_sync/pending_review'));
    }

    /**
     * Bulk ignore held submissions
     * 
     * Marks multiple held submissions as ignored at once.
     * Supports both AJAX and form POST requests.
     * 
     * @return void
     */
    public function bulk_ignore_held()
    {
        if (!staff_can('edit', 'form_sync')) {
            access_denied('form_sync');
        }
        
        // Validate CSRF token
        if (!$this->input->post('csrf_token_name')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
                exit;
            }
            set_alert('danger', 'Invalid security token. Please refresh the page and try again.');
            redirect(admin_url('form_sync/pending_review'));
            return;
        }
        
        // Get log IDs from POST
        $log_ids = $this->input->post('log_ids');
        
        // Validate input
        if (empty($log_ids) || !is_array($log_ids)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'No submissions selected.']);
                exit;
            }
            set_alert('warning', 'No submissions selected.');
            redirect(admin_url('form_sync/pending_review'));
            return;
        }
        
        // Process bulk ignore
        $result = $this->form_sync_model->bulkIgnoreSubmissions($log_ids);
        
        // Prepare response message
        $message = '';
        if ($result['success_count'] > 0) {
            $message .= $result['success_count'] . ' submission' . ($result['success_count'] > 1 ? 's' : '') . ' ignored successfully.';
        }
        if ($result['failed_count'] > 0) {
            if (!empty($message)) {
                $message .= ' ';
            }
            $message .= $result['failed_count'] . ' submission' . ($result['failed_count'] > 1 ? 's' : '') . ' failed.';
        }
        
        // Handle AJAX vs form POST
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $result['failed_count'] == 0,
                'message' => $message,
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'errors' => $result['errors']
            ]);
            exit;
        }
        
        // Set alerts and redirect
        if ($result['success_count'] > 0 && $result['failed_count'] == 0) {
            set_alert('success', $message);
        } elseif ($result['success_count'] > 0 && $result['failed_count'] > 0) {
            set_alert('warning', $message);
        } else {
            set_alert('danger', $message);
        }
        
        redirect(admin_url('form_sync/pending_review'));
    }
    
    /**
     * Logs page
     * 
     * Displays submission processing logs with filtering options.
     * Shows all submissions processed by the FormSync module.
     * 
     * @return void
     */
    public function logs()
    {
        if (!staff_can('view', 'form_sync')) {
            access_denied('form_sync');
        }
        
        // Require valid license
        $this->requireValidLicense();
        
        // Get filters
        $filters = [
            'form_id' => $this->input->get('form_id'),
            'target_type' => $this->input->get('target_type'),
            'hold_status' => $this->input->get('hold_status'),
            'status' => $this->input->get('status'),
            'provider' => $this->input->get('provider'),
        ];
        
        $data['logs'] = $this->form_sync_model->getSubmissionLogs(100, 0, $filters);
        
        // Get form configurations for filter dropdown
        $data['form_configs'] = $this->form_sync_model->get_form_configurations();
        
        $data['filters'] = $filters;
        $data['title'] = _l('form_sync') . ' - ' . _l('form_sync_logs');
        $this->load->view('form_sync/logs', $data);
    }

    /**
     * Get field mapping data via AJAX
     * 
     * Extracts form fields from recent submissions and returns them along with
     * existing mappings and available Perfex CRM fields. Used by the field
     * mapping interface to populate the mapping form.
     * 
     * Note: Works with just 1 submission - no minimum required. A single
     * submission contains all the form fields needed for mapping.
     * 
     * @return void Outputs JSON response
     * @throws Throwable Catches and logs all errors, returns JSON error response
     */
    public function get_field_mapping_data()
    {
        // Clear any output buffers to prevent issues
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Set JSON header immediately to prevent any output issues
        header('Content-Type: application/json; charset=utf-8');
        
        // Wrap entire method in error handler
        try {
            // Check permissions
            if (!staff_can('edit', 'form_sync')) {
                $this->sendJsonResponse(false, _l('access_denied'));
                return;
            }
            
            // Extract and validate input parameters
            $form_id = $this->input->get('form_id') ?: $this->input->post('form_id');
            $target_type = $this->input->get('target_type') ?: $this->input->post('target_type') ?: 'customer';
            $provider = $this->input->get('provider') ?: $this->input->post('provider');
            
            // Debug logging (only in development)
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] get_field_mapping_data called - form_id: ' . ($form_id ?: 'empty') . ', target_type: ' . ($target_type ?: 'empty') . ', provider: ' . ($provider ?: 'empty'));
            }
            
            if (empty($form_id)) {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', '[FormSync] get_field_mapping_data - Form ID is empty!');
                }
                $this->sendJsonResponse(false, 'Form ID is required');
                return;
            }
            
            // Get form configuration for form name
            try {
                $form_name = $this->getFormName($form_id, $provider);
                if (ENVIRONMENT === 'development') {
                    log_message('debug', '[FormSync] get_field_mapping_data - Form name: ' . ($form_name ?: 'empty'));
                }
            } catch (Exception $e) {
                log_message('error', '[FormSync] Error getting form name: ' . $e->getMessage());
                $form_name = $form_id; // Fallback to form_id
            }
            
            // Extract form fields from recent submissions
            try {
                $form_fields_array = $this->extractFormFieldsFromSubmissions($form_id, $provider);
                if (ENVIRONMENT === 'development') {
                    log_message('debug', '[FormSync] get_field_mapping_data - Form fields count: ' . count($form_fields_array));
                }
            } catch (Throwable $e) {
                log_message('error', '[FormSync] Error extracting form fields: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                $form_fields_array = [];
            }
            
            // If no fields found, return helpful error message
            if (empty($form_fields_array)) {
                $this->sendJsonResponse(false, 'No form fields found. Please submit a test form first to detect the form fields. Even one submission is enough to map fields.', [
                    'form_id' => $form_id,
                    'form_name' => $form_name,
                    'target_type' => $target_type,
                    'form_fields' => [],
                    'mappings' => [],
                    'perfex_fields' => [],
                ]);
                return;
            }
            
            // Get existing mappings and available Perfex fields
            try {
                $mappings = $this->form_sync_model->getFieldMappings($form_id, $target_type);
            } catch (Exception $e) {
                log_message('error', '[FormSync] Error getting field mappings: ' . $e->getMessage());
                $mappings = [];
            }
            
            try {
                $perfex_fields = $this->getPerfexFieldsForTargetType($target_type);
            } catch (Exception $e) {
                log_message('error', '[FormSync] Error getting Perfex fields: ' . $e->getMessage());
                $perfex_fields = [];
            }
            
            // Return success response with all mapping data
            $this->sendJsonResponse(true, null, [
                'form_id' => $form_id,
                'form_name' => $form_name,
                'target_type' => $target_type,
                'form_fields' => $form_fields_array,
                'mappings' => $mappings,
                'perfex_fields' => $perfex_fields,
            ]);
            
        } catch (Throwable $e) {
            // Catch all errors including fatal errors
            log_message('error', '[FormSync] Fatal error in get_field_mapping_data: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            
            // Clear any output buffers
            if (ob_get_level() > 0) {
                ob_clean();
            }
            
            $error_data = [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ];
            
            // Include detailed error info in development mode
            if (ENVIRONMENT === 'development') {
                $error_data['error'] = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }
            
            $json_output = json_encode($error_data);
            if ($json_output === false) {
                $json_output = json_encode(['success' => false, 'message' => 'JSON encoding error: ' . json_last_error_msg()]);
            }
            
            echo $json_output;
            exit;
        }
    }
    
    /**
     * Get form name from configuration
     * 
     * Retrieves the form name from the form configuration, handling
     * both object and array return types from the model.
     * 
     * @param string $form_id Form ID
     * @param string|null $provider Provider name
     * @return string Form name or form_id as fallback
     */
    private function getFormName($form_id, $provider)
    {
        $form_config = $this->form_sync_model->getFormConfigurationByProvider($form_id, $provider);
        
        // Handle both object and array return types
        if (is_object($form_config)) {
            return $form_config ? $form_config->form_name : $form_id;
        } else {
            return $form_config ? $form_config['form_name'] : $form_id;
        }
    }
    
    /**
     * Extract form fields from recent submissions
     * 
     * Analyzes recent submission logs to discover all form fields.
     * Works with just 1 submission - a single submission contains all form fields.
     * 
     * @param string $form_id Form ID
     * @param string|null $provider Provider name (optional filter)
     * @return array Array of form field definitions for frontend
     */
    private function extractFormFieldsFromSubmissions($form_id, $provider)
    {
        try {
            // Query recent submissions (up to 10, but works with just 1)
            // Use try-catch around database query to handle connection issues
            try {
                $this->db->where('form_id', $form_id);
                if ($provider) {
                    $this->db->where('provider', $provider);
                }
                $this->db->order_by('datecreated', 'DESC');
                $this->db->limit(10);
                $recent_logs = $this->db->get(db_prefix() . 'form_sync_submission_logs')->result_array();
            } catch (Exception $db_error) {
                log_message('error', '[FormSync] Database error in extractFormFieldsFromSubmissions: ' . $db_error->getMessage());
                return []; // Return empty array on database error
            }
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] extractFormFieldsFromSubmissions - Found ' . count($recent_logs) . ' submission logs for form_id: ' . $form_id . ', provider: ' . ($provider ?: 'any'));
            }
            
            $form_fields = [];
            $field_labels = [];
            
            // Get Universal provider settings if provider is universal
            $universal_settings = null;
            if ($provider === 'universal') {
                try {
                    $universal_settings = $this->form_sync_model->getCustomProviderSettings($form_id, $provider);
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', '[FormSync] Universal settings loaded: ' . ($universal_settings ? 'yes' : 'no'));
                    }
                } catch (Exception $e) {
                    log_message('error', '[FormSync] Error loading Universal settings for field discovery: ' . $e->getMessage());
                }
            }
            
            // Process submissions to extract unique form fields
            foreach ($recent_logs as $log) {
            if (empty($log['submission_data'])) {
                continue;
            }
            
            $submission_data = json_decode($log['submission_data'], true);
            if (!is_array($submission_data)) {
                continue;
            }
            
            // For Universal provider, submission_data should already be flattened by extractFormData
            // Just filter metadata fields - no need to re-extract since data is already processed
            if ($provider === 'universal' && $universal_settings) {
                // Get metadata fields to filter
                $metadata_fields = isset($universal_settings['metadata_fields']) ? $universal_settings['metadata_fields'] : ['form_id', 'submission_id', 'timestamp', 'site_id', 'id'];
                
                // Filter metadata fields for Universal provider
                foreach ($metadata_fields as $metadata_field) {
                    unset($submission_data[$metadata_field]);
                }
            } elseif ($provider === 'universal') {
                // If no settings found, use default metadata fields
                $default_metadata = ['form_id', 'submission_id', 'timestamp', 'site_id', 'id'];
                foreach ($default_metadata as $metadata_field) {
                    unset($submission_data[$metadata_field]);
                }
            }
            
            // Extract all fields from this submission
            // A single submission contains all the form fields
            foreach ($submission_data as $field_id => $field_value) {
                // Skip if it's an array (nested structure that wasn't flattened)
                if (is_array($field_value) && !$this->isSimpleValueArray($field_value)) {
                    continue;
                }
                
                if (!in_array($field_id, $form_fields)) {
                    $form_fields[] = $field_id;
                    $field_labels[$field_id] = $field_id; // Default to field ID as label
                }
            }
            }
            
            // Convert to format expected by frontend
            $form_fields_array = [];
            foreach ($form_fields as $field_id) {
                $form_fields_array[] = [
                    'key' => $field_id,
                    'id' => $field_id,
                    'label' => $field_labels[$field_id] ?? $field_id,
                    'title' => $field_labels[$field_id] ?? $field_id,
                ];
            }
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] extractFormFieldsFromSubmissions - Extracted ' . count($form_fields_array) . ' unique fields');
            }
            
            return $form_fields_array;
            
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Error in extractFormFieldsFromSubmissions: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return [];
        }
    }
    
    /**
     * Check if data structure is nested (not flat)
     * 
     * @param array $data Data to check
     * @return bool True if nested structure
     */
    private function isNestedStructure($data)
    {
        if (!is_array($data)) {
            return false;
        }
        
        foreach ($data as $value) {
            if (is_array($value) && !empty($value)) {
                // Check if it's an object-like array (associative)
                if (array_keys($value) !== range(0, count($value) - 1)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if array is a simple value array (not nested objects)
     * 
     * @param array $array Array to check
     * @return bool True if simple array
     */
    private function isSimpleValueArray($array)
    {
        if (empty($array)) {
            return true;
        }
        
        // Simple arrays have numeric keys
        return array_keys($array) === range(0, count($array) - 1);
    }
    
    /**
     * Get available Perfex CRM fields for target type
     * 
     * Returns the list of available CRM fields that can be mapped to,
     * based on whether the target is a Lead, Customer, Estimate Request, or Ticket.
     * 
     * @param string $target_type Target type ('lead', 'customer', 'estimate_request', or 'ticket')
     * @return array Associative array of field_id => field_label
     */
    private function getPerfexFieldsForTargetType($target_type)
    {
        if ($target_type === 'lead') {
            return [
                'name'        => _l('lead_name'),
                'company'     => _l('lead_company'),
                'email'       => _l('lead_email'),
                'phonenumber' => _l('lead_phonenumber'),
                'address'     => _l('lead_address'),
                'city'        => _l('lead_city'),
                'state'       => _l('lead_state'),
                'zip'         => _l('lead_zip'),
                'country'     => _l('lead_country'),
                'website'     => _l('lead_website'),
                'description' => _l('lead_description'),
                'title'       => _l('lead_title'),
            ];
        } elseif ($target_type === 'estimate_request') {
            return [
                'email'       => _l('estimate_request_email'),
                'name'        => _l('estimate_request_name'),
                'company'     => _l('estimate_request_company'),
                'phonenumber' => _l('estimate_request_phonenumber'),
                'address'     => _l('estimate_request_address'),
                'city'        => _l('estimate_request_city'),
                'state'       => _l('estimate_request_state'),
                'zip'         => _l('estimate_request_zip'),
                'country'     => _l('estimate_request_country'),
                'website'     => _l('estimate_request_website'),
                'description' => _l('estimate_request_description'),
            ];
        } elseif ($target_type === 'ticket') {
            return [
                'subject'     => _l('customer_ticket_subject'),
                'message'     => _l('ticket_message'),
                'email'       => _l('ticket_form_email'),
                'name'        => _l('ticket_form_name'),
                'department'  => _l('clients_ticket_open_departments'),
                'priority'    => _l('priority'),
                'phonenumber' => _l('ticket_phonenumber'),
            ];
        } else {
            // Customer (default)
            return [
                'company'      => _l('client_company'),
                'firstname'   => _l('client_firstname'),
                'lastname'    => _l('client_lastname'),
                'email'       => _l('client_email'),
                'phonenumber' => _l('client_phonenumber'),
                'address'     => _l('client_address'),
                'city'        => _l('client_city'),
                'state'       => _l('client_state'),
                'zip'         => _l('client_zip'),
                'country'     => _l('client_country'),
                'website'     => _l('client_website'),
                'vat'         => _l('client_vat_number'),
            ];
        }
    }
    
    /**
     * Send JSON response and exit
     * 
     * Helper method to send consistent JSON responses.
     * 
     * @param bool $success Whether the operation was successful
     * @param string|null $message Optional message
     * @param array $data Optional additional data
     * @return void
     */
    private function sendJsonResponse($success, $message = null, $data = [])
    {
        // Clear any output buffers
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        $response = ['success' => $success];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        
        // Ensure JSON encoding doesn't fail
        $json_output = json_encode($response);
        if ($json_output === false) {
            // Fallback if JSON encoding fails
            $response = ['success' => false, 'message' => 'JSON encoding error: ' . json_last_error_msg()];
            $json_output = json_encode($response);
        }
        
        echo $json_output;
        exit;
    }

    /**
     * Save field mappings via AJAX
     * 
     * Saves field mappings for a form configuration. Maps form field IDs
     * to Perfex CRM field names. Handles both JSON string and array formats.
     * 
     * @return void Outputs JSON response
     */
    public function save_field_mappings()
    {
        // Set JSON header immediately to prevent any output issues
        header('Content-Type: application/json');
        
        if (!staff_can('edit', 'form_sync')) {
            $this->sendJsonResponse(false, _l('access_denied'));
            return;
        }
        
        // Extract and validate input parameters
        $form_id = $this->input->post('form_id');
        $target_type = $this->input->post('target_type');
        $provider = $this->input->post('provider');
        
        // Get mappings from JSON string (preferred method - preserves field IDs with special chars)
        $mappings = [];
        $mappings_json = $this->input->post('mappings_json');
        if (!empty($mappings_json)) {
            $decoded = json_decode($mappings_json, true);
            if (is_array($decoded)) {
                $mappings = $decoded;
            }
        }
        
        // Fallback: Try CodeIgniter's input->post with array notation
        if (empty($mappings)) {
            $ci_mappings = $this->input->post('mappings');
            if (is_array($ci_mappings) && !empty($ci_mappings)) {
                $mappings = $ci_mappings;
            }
        }
        
        // Fallback: Try regex extraction from $_POST keys
        if (empty($mappings)) {
            foreach ($_POST as $key => $value) {
                if (preg_match('/^mappings\[(.+)\]$/', $key, $matches)) {
                    $field_id = $matches[1];
                    $mappings[$field_id] = $value;
                }
            }
        }
        
        // Validate required fields
        if (empty($form_id) || empty($target_type)) {
            $error_msg = 'Form ID and target type are required.';
            log_message('error', '[FormSync] ' . $error_msg);
            $this->sendJsonResponse(false, $error_msg);
            return;
        }
        
        // Parse and normalize mappings data
        $filtered_mappings = $this->parseAndFilterMappings($mappings);
        
        // Save mappings to database
        try {
            $result = $this->form_sync_model->saveFieldMappings($form_id, $target_type, $filtered_mappings, $provider);
            
            if ($result) {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', '[FormSync] Field mappings saved successfully for form_id=' . $form_id);
                }
                $this->sendJsonResponse(true, _l('form_sync_field_mappings_saved'));
            } else {
                $this->handleMappingSaveError();
            }
        } catch (Exception $e) {
            log_message('error', '[FormSync] Exception saving field mappings - ' . $e->getMessage());
            $this->sendJsonResponse(false, 'Error saving field mappings: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse and filter field mappings
     * 
     * Handles mappings in both JSON string and array formats, then filters
     * out empty or 'none' values.
     * 
     * @param mixed $mappings Mappings data (string or array)
     * @return array Filtered mappings array
     */
    private function parseAndFilterMappings($mappings)
    {
        // Handle mappings if it's a string (JSON) or array
        if (is_string($mappings)) {
            $mappings = json_decode($mappings, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', '[FormSync] Failed to decode mappings JSON: ' . json_last_error_msg());
                return [];
            }
        }
        
        // Ensure mappings is an array
        if (!is_array($mappings)) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Mappings is not an array, converting to empty array. Type: ' . gettype($mappings));
            }
            return [];
        }
        
        // Filter out empty or 'none' values
        $filtered_mappings = [];
        foreach ($mappings as $form_field_id => $perfex_field) {
            if (!empty($form_field_id) && !empty($perfex_field) && $perfex_field !== 'none') {
                $filtered_mappings[$form_field_id] = $perfex_field;
            }
        }
        
        if (ENVIRONMENT === 'development') {
            log_message('debug', '[FormSync] Filtered mappings count: ' . count($filtered_mappings));
        }
        
        return $filtered_mappings;
    }
    
    /**
     * Handle mapping save error
     * 
     * Logs database errors and sends appropriate error response.
     * 
     * @return void
     */
    private function handleMappingSaveError()
    {
        $db_error = $this->db->error();
        $error_msg = 'Failed to save field mappings';
        
        if (!empty($db_error['message'])) {
            $error_msg .= ': ' . $db_error['message'];
            log_message('error', '[FormSync] Database error - ' . json_encode($db_error));
        }
        
        log_message('error', '[FormSync] saveFieldMappings returned false');
        $this->sendJsonResponse(false, $error_msg);
    }

    /**
     * Extract Universal provider settings from POST data
     * 
     * Validates and extracts Universal provider custom settings from form submission.
     * 
     * @return array Settings array or empty array if not valid
     */
    private function extractUniversalProviderSettings()
    {
        $settings = [];
        
        // Payload structure
        $payload_structure = $this->input->post('universal_payload_structure');
        if (!empty($payload_structure) && in_array($payload_structure, ['flat', 'nested', 'array', 'custom', 'auto-detect'])) {
            $settings['payload_structure'] = $payload_structure;
        } else {
            $settings['payload_structure'] = 'flat';
        }
        
        // Data path (for nested, array, custom)
        if (in_array($settings['payload_structure'], ['nested', 'array', 'custom'])) {
            $data_path = $this->input->post('universal_data_path');
            $settings['data_path'] = !empty($data_path) ? trim($data_path) : '';
        } else {
            $settings['data_path'] = '';
        }
        
        // Form ID source
        $form_id_source = $this->input->post('universal_form_id_source');
        if (!empty($form_id_source) && in_array($form_id_source, ['url', 'payload', 'header'])) {
            $settings['form_id_source'] = $form_id_source;
        } else {
            $settings['form_id_source'] = 'url';
        }
        
        // Form ID path/header
        if (in_array($settings['form_id_source'], ['payload', 'header'])) {
            $form_id_path = $this->input->post('universal_form_id_path');
            $settings['form_id_path'] = !empty($form_id_path) ? trim($form_id_path) : '';
        } else {
            $settings['form_id_path'] = '';
        }
        
        // Submission ID source
        $submission_id_source = $this->input->post('universal_submission_id_source');
        if (!empty($submission_id_source) && in_array($submission_id_source, ['auto', 'payload', 'header'])) {
            $settings['submission_id_source'] = $submission_id_source;
        } else {
            $settings['submission_id_source'] = 'auto';
        }
        
        // Submission ID path/header
        if (in_array($settings['submission_id_source'], ['payload', 'header'])) {
            $submission_id_path = $this->input->post('universal_submission_id_path');
            $settings['submission_id_path'] = !empty($submission_id_path) ? trim($submission_id_path) : '';
        } else {
            $settings['submission_id_path'] = '';
        }
        
        // Site ID source
        $site_id_source = $this->input->post('universal_site_id_source');
        if (!empty($site_id_source) && in_array($site_id_source, ['none', 'payload', 'header'])) {
            $settings['site_id_source'] = $site_id_source;
        } else {
            $settings['site_id_source'] = 'none';
        }
        
        // Site ID path/header
        if (in_array($settings['site_id_source'], ['payload', 'header'])) {
            $site_id_path = $this->input->post('universal_site_id_path');
            $settings['site_id_path'] = !empty($site_id_path) ? trim($site_id_path) : '';
        } else {
            $settings['site_id_path'] = '';
        }
        
        // Metadata fields
        $metadata_fields = $this->input->post('universal_metadata_fields');
        if (!empty($metadata_fields)) {
            $fields_array = array_map('trim', explode(',', $metadata_fields));
            $settings['metadata_fields'] = array_filter($fields_array); // Remove empty values
        } else {
            $settings['metadata_fields'] = ['form_id', 'submission_id', 'timestamp', 'site_id'];
        }
        
        // Signature verification
        $sig_enabled = $this->input->post('universal_signature_verification_enabled') ? true : false;
        $settings['signature_verification'] = [
            'enabled' => $sig_enabled,
            'method' => $sig_enabled ? ($this->input->post('universal_signature_method') ?: 'header') : 'header',
            'header_name' => $sig_enabled ? ($this->input->post('universal_signature_header_name') ?: 'X-Signature') : 'X-Signature',
            'algorithm' => 'sha256'
        ];
        
        return $settings;
    }

    /**
     * Save Universal provider settings (AJAX endpoint)
     * 
     * Saves Universal provider custom settings independently.
     * 
     * @return void Outputs JSON response
     */
    public function save_universal_settings()
    {
        header('Content-Type: application/json');
        
        if (!staff_can('edit', 'form_sync')) {
            $this->sendJsonResponse(false, _l('access_denied'));
            return;
        }
        
        $form_id = $this->input->post('form_id');
        $provider = $this->input->post('provider');
        
        if (empty($form_id) || $provider !== 'universal') {
            $this->sendJsonResponse(false, 'Invalid form ID or provider');
            return;
        }
        
        try {
            $settings = $this->extractUniversalProviderSettings();
            $result = $this->form_sync_model->saveCustomProviderSettings($form_id, $provider, $settings);
            
            if ($result) {
                $this->sendJsonResponse(true, 'Universal provider settings saved successfully');
            } else {
                $this->sendJsonResponse(false, 'Failed to save Universal provider settings');
            }
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error saving Universal settings: ' . $e->getMessage());
            $this->sendJsonResponse(false, 'Error saving settings: ' . $e->getMessage());
        }
    }

    /**
     * Check if field mappings exist (AJAX endpoint)
     * 
     * Checks whether field mappings have been configured for a given
     * form and target type combination.
     * 
     * @return void Outputs JSON response with has_mappings boolean
     */
    public function check_mappings()
    {
        header('Content-Type: application/json');
        
        if (!staff_can('view', 'form_sync')) {
            echo json_encode(['success' => false, 'has_mappings' => false, 'message' => _l('access_denied')]);
            exit;
        }
        
        $form_id = $this->input->get('form_id');
        $target_type = $this->input->get('target_type');
        
        if (empty($form_id) || empty($target_type)) {
            echo json_encode(['success' => false, 'has_mappings' => false, 'message' => 'Form ID and target type are required']);
            exit;
        }
        
        $has_mappings = $this->form_sync_model->hasFieldMappings($form_id, $target_type);
        
        echo json_encode([
            'success' => true,
            'has_mappings' => $has_mappings,
            'form_id' => $form_id,
            'target_type' => $target_type
        ]);
        exit;
    }

    /**
     * Save Webflow secret key via AJAX
     * 
     * Saves the Webflow secret key for a form configuration.
     * This secret is provided by Webflow and used for webhook signature verification.
     * 
     * @return void Outputs JSON response
     */
    public function save_webflow_secret()
    {
        // Set JSON header immediately
        header('Content-Type: application/json');
        
        if (!staff_can('edit', 'form_sync')) {
            $this->sendJsonResponse(false, _l('access_denied'));
            return;
        }
        
        $config_id = $this->input->post('config_id');
        $webflow_secret = $this->input->post('webflow_secret');
        
        // Validate required fields
        if (empty($config_id)) {
            $this->sendJsonResponse(false, 'Configuration ID is required.');
            return;
        }
        
        if (empty($webflow_secret)) {
            $this->sendJsonResponse(false, _l('form_sync_webflow_secret_required'));
            return;
        }
        
        // Verify configuration exists and is Webflow
        $config = $this->form_sync_model->get_form_configuration($config_id);
        if (!$config) {
            $this->sendJsonResponse(false, 'Form configuration not found.');
            return;
        }
        
        // Convert to array if object
        if (is_object($config)) {
            $config = (array)$config;
        }
        
        // Verify it's a Webflow configuration
        if (isset($config['provider']) && $config['provider'] !== 'webflow') {
            $this->sendJsonResponse(false, 'This endpoint is only for Webflow configurations.');
            return;
        }
        
        // Update the webhook secret
        $update_data = [
            'webhook_secret' => trim($webflow_secret)
        ];
        
        if ($this->form_sync_model->update_form_configuration($config_id, $update_data)) {
            $this->sendJsonResponse(true, _l('form_sync_webflow_secret_saved'));
        } else {
            $this->sendJsonResponse(false, 'Failed to save Webflow secret key.');
        }
    }

    /**
     * View submission data
     * 
     * Returns the raw submission data for a log entry as JSON.
     * Used for debugging and reviewing submission details.
     * 
     * @param int $log_id Submission log ID
     * @return void Outputs JSON response or 404 if not found
     */
    public function view_submission_data($log_id)
    {
        if (!staff_can('view', 'form_sync')) {
            access_denied('form_sync');
        }
        
        $log = $this->form_sync_model->getSubmissionLogById($log_id);
        
        if (!$log) {
            show_404();
        }
        
        $submission_data = json_decode($log['submission_data'], true);
        
        header('Content-Type: application/json');
        echo json_encode($submission_data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Unified webhook endpoint (public)
     * 
     * Receives form submissions from all providers.
     * URL: {site_url}/form_sync/webhook/{provider_id}/{form_id?}
     * 
     * @param string|null $provider_id Provider ID from URL (e.g., 'framer', 'webflow')
     * @param string|null $form_id Form ID from URL (optional, some providers include it in payload)
     * @return void
     */
    public function webhook($provider_id = null, $form_id = null)
    {
        // Wrap in try-catch to prevent 500 errors
        try {
            // Check request method - webhooks must be POST
            $request_method = $this->input->server('REQUEST_METHOD');
            if ($request_method !== 'POST') {
                log_message('warning', '[FormSync] Webhook - Invalid request method: ' . $request_method . ' (expected POST)');
                // Clear any previous output
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(405); // Method Not Allowed
                echo json_encode([
                    'error' => 'Method not allowed',
                    'message' => 'Webhook endpoints only accept POST requests'
                ]);
                exit;
            }
            
            // Rate limiting: Check IP-based rate limit
            $this->load->library('form_sync/form_sync_rate_limiter');
            $client_ip = $this->getClientIp();
            $rate_limit = $this->form_sync_rate_limiter->checkLimit($client_ip);
            
            if (!$rate_limit['allowed']) {
                log_message('warning', '[FormSync] Webhook - Rate limit exceeded for IP: ' . $client_ip);
                // Clear any previous output
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(429);
                header('Retry-After: ' . ($rate_limit['reset_time'] - time()));
                echo json_encode([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => $rate_limit['reset_time'] - time()
                ]);
                exit;
            }

            // Auto-detect provider if not in URL
            if (empty($provider_id)) {
                $provider_id = $this->form_sync_provider_manager->detectProvider($this->input);
            }

            if (empty($provider_id)) {
                log_message('error', '[FormSync] Webhook - Provider ID not found');
                // Clear any previous output
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => 'Provider ID required']);
                exit;
            }

            // Get provider instance
            $provider = $this->form_sync_provider_manager->getProvider($provider_id);
            
            if (!$provider) {
                log_message('error', '[FormSync] Webhook - Provider not found: ' . $provider_id);
                // Clear any previous output
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(404);
                echo json_encode(['error' => 'Provider not found']);
                exit;
            }

            // Check if provider is enabled
            if (!$provider->isEnabled()) {
                log_message('warning', '[FormSync] Webhook - Provider is disabled: ' . $provider_id);
                // Clear any previous output
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode(['error' => 'Provider is disabled']);
                exit;
            }

            // Delegate to provider
            $provider->handleWebhook($this);
            
        } catch (Throwable $e) {
            // Catch any uncaught exceptions at controller level
            $error_message = $e->getMessage();
            $error_file = $e->getFile();
            $error_line = $e->getLine();
            
            log_message('error', '[FormSync] Webhook controller - Uncaught exception: ' . $error_message);
            log_message('error', '[FormSync] Error in ' . $error_file . ':' . $error_line);
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            
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
     * Framer webhook endpoint (public) - Backward compatibility
     * 
     * @deprecated Use webhook('framer', $form_id) instead
     * @param string|null $form_id Form ID from URL path
     * @return void
     */
    public function webhook_framer($form_id = null)
    {
        // Route to unified webhook endpoint
        $this->webhook('framer', $form_id);
    }

    /**
     * Sanitize form ID
     * 
     * @param string $form_id Raw form ID
     * @return string Sanitized form ID
     */
    private function sanitizeFormId($form_id)
    {
        if (empty($form_id)) {
            return '';
        }
        // Allow alphanumeric, hyphens, underscores, dots
        return preg_replace('/[^a-zA-Z0-9._-]/', '', trim($form_id));
    }
    
    /**
     * Sanitize string input
     * 
     * @param string $value Raw string
     * @param int $max_length Maximum length
     * @return string Sanitized string
     */
    private function sanitizeString($value, $max_length = 255)
    {
        if (empty($value)) {
            return '';
        }
        // Strip HTML tags and trim
        $value = strip_tags(trim($value));
        // Limit length
        return substr($value, 0, $max_length);
    }
    
    /**
     * Sanitize provider identifier
     * 
     * @param string $provider Raw provider
     * @return string Sanitized provider
     */
    private function sanitizeProvider($provider)
    {
        if (empty($provider)) {
            return '';
        }
        // Only allow lowercase alphanumeric and underscores
        return preg_replace('/[^a-z0-9_]/', '', strtolower(trim($provider)));
    }
    
    /**
     * Sanitize target type
     * 
     * @param string $target_type Raw target type
     * @return string Sanitized target type
     */
    private function sanitizeTargetType($target_type)
    {
        if (empty($target_type)) {
            return 'customer'; // Default
        }
        // Only allow 'lead', 'customer', 'estimate_request', or 'ticket'
        $target_type = strtolower(trim($target_type));
        return in_array($target_type, ['lead', 'customer', 'estimate_request', 'ticket']) ? $target_type : 'customer';
    }
    
    /**
     * Get client IP address
     * 
     * Handles various proxy headers and Cloudflare.
     * 
     * @return string IP address
     */
    private function getClientIp()
    {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',         // Nginx proxy
            'HTTP_X_FORWARDED_FOR',   // Proxy
            'REMOTE_ADDR'             // Standard
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
    
    /**
     * Fix all leads visibility
     * 
     * This method fixes visibility for all FormSync-created leads
     * that might have incorrect visibility settings.
     * 
     * @return void
     */
    public function fix_all_leads_visibility()
    {
        if (!staff_can('edit', 'form_sync')) {
            access_denied('form_sync');
        }
        
        $result = $this->form_sync_model->fixAllLeadsVisibility();
        
        if ($result['fixed'] > 0) {
            set_alert('success', 'Fixed visibility for ' . $result['fixed'] . ' lead(s). ' . $result['correct'] . ' lead(s) were already correct.');
        } else {
            set_alert('info', 'All leads already have correct visibility settings. (' . $result['correct'] . ' lead(s) total)');
        }
        
        redirect(admin_url('form_sync/pending_review'));
    }
    
    /**
     * Webflow webhook endpoint (public) - Backward compatibility
     * 
     * @deprecated Use webhook('webflow', $form_id) instead
     * @param string|null $form_id Form ID from URL path (same as Framer)
     * @return void
     */
    public function webhook_webflow($form_id = null)
    {
        // Route to unified webhook endpoint
        // For Webflow, form_id is passed as the second parameter (same as Framer)
        $this->webhook('webflow', $form_id);
    }
}

