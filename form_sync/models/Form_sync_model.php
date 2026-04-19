<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Model
 * 
 * Handles all database operations for the FormSync module.
 * 
 * @package    FormSync
 * @subpackage Models
 * @category   Module
 * @author     LiquidApps Studio
 */

class Form_sync_model extends App_Model
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Only run migration if database is connected
        // Use a try-catch to prevent fatal errors during construction
        try {
            if (isset($this->db) && is_object($this->db) && method_exists($this->db, 'table_exists')) {
                $this->ensureHoldReasonTypeColumn();
                $this->ensureEstimateTicketColumns();
            }
        } catch (Throwable $e) {
            // Silently fail during construction to prevent fatal errors
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Could not run auto-migration in constructor: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Ensure estimate request and ticket columns exist (auto-migration)
     * This runs on model instantiation to ensure backward compatibility
     */
    private function ensureEstimateTicketColumns()
    {
        // Check if migration already done using option flag
        if (get_option('form_sync_estimate_ticket_migrated') == '1') {
            return;
        }
        
        try {
            // Check if database connection is available
            if (!isset($this->db) || !is_object($this->db)) {
                return;
            }
            
            $migration_done = false;
            
            // Update target_type ENUM in form_sync_form_configurations
            if ($this->db->table_exists(db_prefix() . 'form_sync_form_configurations')) {
                $column_info = $this->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_form_configurations` WHERE Field = "target_type"')->row();
                if ($column_info) {
                    log_message('info', '[FormSync] Current target_type ENUM: ' . $column_info->Type);
                    if (strpos($column_info->Type, 'estimate_request') === false) {
                        $result = $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                        log_message('info', '[FormSync] Auto-migrated: Updated target_type ENUM in form_configurations - Result: ' . ($result ? 'success' : 'failed'));
                        $migration_done = true;
                    }
                }
                
                // Add new columns if they don't exist
                $columns = $this->db->list_fields(db_prefix() . 'form_sync_form_configurations');
                
                if (!in_array('estimate_request_status_id', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `estimate_request_status_id` int(11) DEFAULT NULL AFTER `lead_source_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added estimate_request_status_id column');
                }
                if (!in_array('estimate_request_assigned_id', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `estimate_request_assigned_id` int(11) DEFAULT NULL AFTER `estimate_request_status_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added estimate_request_assigned_id column');
                }
                if (!in_array('ticket_department_id', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `ticket_department_id` int(11) DEFAULT NULL AFTER `estimate_request_assigned_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added ticket_department_id column');
                }
                if (!in_array('ticket_priority', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_form_configurations` ADD `ticket_priority` int(11) DEFAULT NULL AFTER `ticket_department_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added ticket_priority column');
                }
            }
            
            // Update target_type ENUM in form_sync_submission_logs
            if ($this->db->table_exists(db_prefix() . 'form_sync_submission_logs')) {
                $column_info = $this->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_submission_logs` WHERE Field = "target_type"')->row();
                if ($column_info && strpos($column_info->Type, 'estimate_request') === false) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                    log_message('info', '[FormSync] Auto-migrated: Updated target_type ENUM in submission_logs');
                }
                
                // Add new columns if they don't exist
                $columns = $this->db->list_fields(db_prefix() . 'form_sync_submission_logs');
                
                if (!in_array('estimate_request_id', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `estimate_request_id` int(11) DEFAULT NULL AFTER `lead_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added estimate_request_id column');
                }
                if (!in_array('ticket_id', $columns)) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `ticket_id` int(11) DEFAULT NULL AFTER `estimate_request_id`');
                    log_message('info', '[FormSync] Auto-migrated: Added ticket_id column');
                }
            }
            
            // Update target_type ENUM in form_sync_field_mappings
            if ($this->db->table_exists(db_prefix() . 'form_sync_field_mappings')) {
                $column_info = $this->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_field_mappings` WHERE Field = "target_type"')->row();
                if ($column_info && strpos($column_info->Type, 'estimate_request') === false) {
                    $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_field_mappings` MODIFY `target_type` enum(\'lead\',\'customer\',\'estimate_request\',\'ticket\') NOT NULL DEFAULT \'customer\'');
                    log_message('info', '[FormSync] Auto-migrated: Updated target_type ENUM in field_mappings');
                    $migration_done = true;
                }
            }
            
            // Mark migration as done
            if ($migration_done) {
                update_option('form_sync_estimate_ticket_migrated', '1');
                log_message('info', '[FormSync] Estimate/ticket migration completed and flagged');
            } else {
                // If no migrations were needed, still mark as checked
                update_option('form_sync_estimate_ticket_migrated', '1');
            }
            
        } catch (Throwable $e) {
            // Silently fail - don't break the application if migration fails
            log_message('error', '[FormSync] Could not auto-migrate estimate/ticket columns: ' . $e->getMessage());
        }
    }
    
    /**
     * Ensure hold_reason_type column exists (auto-migration)
     * This runs on model instantiation to ensure backward compatibility
     */
    private function ensureHoldReasonTypeColumn()
    {
        static $checked = false;
        if ($checked) {
            return; // Only check once per request
        }
        $checked = true;
        
        try {
            // Check if database connection is available
            if (!isset($this->db) || !is_object($this->db)) {
                return;
            }
            
            if (!$this->db->table_exists(db_prefix() . 'form_sync_submission_logs')) {
                return; // Table doesn't exist, install.php will handle it
            }
            
            $columns = $this->db->list_fields(db_prefix() . 'form_sync_submission_logs');
            if (!in_array('hold_reason_type', $columns)) {
                // Column doesn't exist, add it
                $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` ADD `hold_reason_type` enum(\'duplicate\',\'no_mappings\',\'manual_review\',\'none\') NOT NULL DEFAULT \'none\' AFTER `hold_status`');
                
                // Update existing records (only if update succeeds)
                try {
                    $this->db->query('UPDATE `' . db_prefix() . 'form_sync_submission_logs` SET `hold_reason_type` = \'duplicate\' WHERE `duplicate_reason` IS NOT NULL AND `duplicate_reason` != \'\'');
                    $this->db->query('UPDATE `' . db_prefix() . 'form_sync_submission_logs` SET `hold_reason_type` = \'no_mappings\' WHERE `error_message` LIKE \'%No field mappings%\' AND `hold_status` = \'hold\'');
                } catch (Exception $update_e) {
                    // Ignore update errors, column was added successfully
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', '[FormSync] Could not update existing records: ' . $update_e->getMessage());
                    }
                }
                
                log_message('info', '[FormSync] Auto-migrated: Added hold_reason_type column to submission_logs table');
            } else {
                // Column exists, check if enum needs to be updated to include 'manual_review'
                try {
                    $column_info = $this->db->query('SHOW COLUMNS FROM `' . db_prefix() . 'form_sync_submission_logs` WHERE Field = "hold_reason_type"')->row();
                    if ($column_info && strpos($column_info->Type, 'enum') !== false) {
                        // Check if 'manual_review' is already in the enum
                        if (strpos($column_info->Type, 'manual_review') === false) {
                            // Update enum to include 'manual_review'
                            $this->db->query('ALTER TABLE `' . db_prefix() . 'form_sync_submission_logs` MODIFY `hold_reason_type` enum(\'duplicate\',\'no_mappings\',\'manual_review\',\'none\') NOT NULL DEFAULT \'none\'');
                            log_message('info', '[FormSync] Auto-migrated: Updated hold_reason_type enum to include \'manual_review\'');
                        }
                    }
                } catch (Exception $e) {
                    // If enum value already exists or update fails, log and continue
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', '[FormSync] Could not update hold_reason_type enum (may already be updated): ' . $e->getMessage());
                    }
                }
            }
        } catch (Throwable $e) {
            // Silently fail - don't break the application if migration fails
            log_message('error', '[FormSync] Could not auto-migrate hold_reason_type column: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
    
    /**
     * Get all form configurations
     * 
     * Retrieves all form configurations from the database, optionally filtered
     * by where conditions.
     * 
     * @param array $where Optional where conditions for filtering
     * @return array Array of form configuration records
     */
    public function get_form_configurations($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->get(db_prefix() . 'form_sync_form_configurations')->result_array();
    }
    
    /**
     * Get form configuration by ID
     * 
     * Retrieves a single form configuration by its database ID.
     * 
     * @param int $id Configuration ID
     * @return object|null Form configuration object or null if not found
     */
    public function get_form_configuration($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'form_sync_form_configurations')->row();
    }
    
    /**
     * Add form configuration
     * 
     * Creates a new form configuration record in the database.
     * Automatically sets the datecreated timestamp.
     * 
     * @param array $data Configuration data (form_id, form_name, provider, etc.)
     * @return int Database insert ID of the new configuration
     */
    public function add_form_configuration($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'form_sync_form_configurations', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update form configuration
     * 
     * Updates an existing form configuration record.
     * Automatically sets the dateupdated timestamp.
     * 
     * @param int $id Configuration ID
     * @param array $data Configuration data to update
     * @return bool True on success, false on failure
     */
    public function update_form_configuration($id, $data)
    {
        $data['dateupdated'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'form_sync_form_configurations', $data);
    }
    
    /**
     * Delete form configuration
     * 
     * Removes a form configuration from the database.
     * Note: This does not delete associated field mappings or logs.
     * 
     * @param int $id Configuration ID
     * @return bool True on success, false on failure
     */
    public function delete_form_configuration($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(db_prefix() . 'form_sync_form_configurations');
    }
    
    /**
     * Get submission logs
     * 
     * @param array $where Optional where conditions
     * @param int $limit Optional limit
     * @param int $offset Optional offset
     * @return array
     */
    public function get_submission_logs($where = [], $limit = null, $offset = null)
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $this->db->order_by('datecreated', 'DESC');
        return $this->db->get(db_prefix() . 'form_sync_submission_logs')->result_array();
    }
    
    /**
     * Get submission log by ID
     * 
     * @param int $id Log ID
     * @return object|null
     */
    public function get_submission_log($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'form_sync_submission_logs')->row();
    }
    
    /**
     * Add submission log
     * 
     * @param array $data Log data
     * @return int Insert ID
     */
    public function add_submission_log($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'form_sync_submission_logs', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Update submission log
     * 
     * @param int $id Log ID
     * @param array $data Log data
     * @return bool
     */
    public function update_submission_log($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'form_sync_submission_logs', $data);
    }

    /**
     * Get form configuration by provider and form ID
     * 
     * @param string $form_id Form ID
     * @param string $provider Provider name ('framer' or 'webflow')
     * @param string|null $target_type Optional target type
     * @return object|null
     */
    public function getFormConfigurationByProvider($form_id, $provider, $target_type = null)
    {
        $this->db->where('form_id', $form_id);
        $this->db->where('provider', $provider);
        
        if ($target_type) {
            $this->db->where('target_type', $target_type);
        }
        
        return $this->db->get(db_prefix() . 'form_sync_form_configurations')->row();
    }

    /**
     * Get form configuration by site ID, form ID, and provider
     * 
     * @param string $site_id Site ID
     * @param string $form_id Form ID
     * @param string $provider Provider name ('framer' or 'webflow')
     * @param string|null $target_type Optional target type
     * @return object|null
     */
    public function getFormConfigurationBySiteAndForm($site_id, $form_id, $provider, $target_type = null)
    {
        $this->db->where('site_id', $site_id);
        $this->db->where('form_id', $form_id);
        $this->db->where('provider', $provider);
        
        if ($target_type) {
            $this->db->where('target_type', $target_type);
        }
        
        return $this->db->get(db_prefix() . 'form_sync_form_configurations')->row();
    }

    /**
     * Get all form configurations for a specific site
     * 
     * @param string $site_id Site ID
     * @param string|null $provider Optional provider filter
     * @return array
     */
    public function getFormConfigurationsBySite($site_id, $provider = null)
    {
        $this->db->where('site_id', $site_id);
        
        if ($provider) {
            $this->db->where('provider', $provider);
        }
        
        return $this->db->get(db_prefix() . 'form_sync_form_configurations')->result_array();
    }

    /**
     * Get custom provider settings for a form
     * 
     * Retrieves and parses the custom_provider_settings JSON for a form configuration.
     * Returns default structure if settings don't exist or are invalid.
     * 
     * @param string $form_id Form ID
     * @param string $provider Provider ID
     * @return array Settings array or empty array if not found/invalid
     */
    public function getCustomProviderSettings($form_id, $provider)
    {
        try {
            $this->db->where('form_id', $form_id);
            $this->db->where('provider', $provider);
            $config = $this->db->get(db_prefix() . 'form_sync_form_configurations')->row();
            
            if (!$config || empty($config->custom_provider_settings)) {
                return [];
            }
            
            $settings = json_decode($config->custom_provider_settings, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', '[FormSync] Invalid JSON in custom_provider_settings for form_id: ' . $form_id . ', provider: ' . $provider . ' - Error: ' . json_last_error_msg());
                return [];
            }
            
            return is_array($settings) ? $settings : [];
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error getting custom provider settings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save custom provider settings for a form
     * 
     * Validates and saves custom_provider_settings JSON for a form configuration.
     * 
     * @param string $form_id Form ID
     * @param string $provider Provider ID
     * @param array $settings Settings array to save
     * @return bool True on success, false on failure
     */
    public function saveCustomProviderSettings($form_id, $provider, $settings)
    {
        try {
            // Validate settings is an array
            if (!is_array($settings)) {
                log_message('error', '[FormSync] Settings must be an array for form_id: ' . $form_id . ', provider: ' . $provider);
                return false;
            }
            
            // Convert to JSON
            $json_settings = json_encode($settings);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', '[FormSync] Error encoding settings to JSON: ' . json_last_error_msg());
                return false;
            }
            
            // Update form configuration
            $this->db->where('form_id', $form_id);
            $this->db->where('provider', $provider);
            $data = [
                'custom_provider_settings' => $json_settings,
                'dateupdated' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->update(db_prefix() . 'form_sync_form_configurations', $data);
            
            if ($result) {
                log_message('info', '[FormSync] Saved custom provider settings for form_id: ' . $form_id . ', provider: ' . $provider);
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error saving custom provider settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update form configuration custom settings only
     * 
     * Updates only the custom_provider_settings column without affecting other fields.
     * 
     * @param int $config_id Configuration ID
     * @param array $settings Settings array to save
     * @return bool True on success, false on failure
     */
    public function updateFormConfigurationCustomSettings($config_id, $settings)
    {
        try {
            // Validate settings is an array
            if (!is_array($settings)) {
                log_message('error', '[FormSync] Settings must be an array for config_id: ' . $config_id);
                return false;
            }
            
            // Convert to JSON
            $json_settings = json_encode($settings);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', '[FormSync] Error encoding settings to JSON: ' . json_last_error_msg());
                return false;
            }
            
            // Update only custom_provider_settings column
            $this->db->where('id', $config_id);
            $data = [
                'custom_provider_settings' => $json_settings,
                'dateupdated' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->update(db_prefix() . 'form_sync_form_configurations', $data);
        } catch (Exception $e) {
            log_message('error', '[FormSync] Error updating custom provider settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Detect payload structure (helper method)
     * 
     * Analyzes payload and returns detected structure type and path.
     * Used by Universal provider for auto-detection.
     * 
     * @param array $payload Payload array to analyze
     * @return array Detected structure info ['structure' => 'flat|nested|array', 'data_path' => 'path']
     */
    public function detectPayloadStructure($payload)
    {
        if (!is_array($payload)) {
            return ['structure' => 'flat', 'data_path' => ''];
        }

        // Check for nested structure (payload.data pattern)
        if (isset($payload['payload']['data']) && is_array($payload['payload']['data'])) {
            return ['structure' => 'nested', 'data_path' => 'payload.data'];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return ['structure' => 'nested', 'data_path' => 'data'];
        }

        // Check for array structure (fields[] or items[] pattern)
        if (isset($payload['fields']) && is_array($payload['fields']) && !empty($payload['fields'])) {
            $first_item = reset($payload['fields']);
            if (is_array($first_item) && (isset($first_item['id']) || isset($first_item['name']) || isset($first_item['field']))) {
                return ['structure' => 'array', 'data_path' => 'fields'];
            }
        }

        if (isset($payload['items']) && is_array($payload['items']) && !empty($payload['items'])) {
            $first_item = reset($payload['items']);
            if (is_array($first_item) && (isset($first_item['id']) || isset($first_item['name']) || isset($first_item['field']))) {
                return ['structure' => 'array', 'data_path' => 'items'];
            }
        }

        // Default to flat structure
        return ['structure' => 'flat', 'data_path' => ''];
    }

    /**
     * Get field mappings for a form
     * 
     * @param string $form_id Form ID
     * @param string $target_type Target type ('lead' or 'customer')
     * @return array
     */
    public function getFieldMappings($form_id, $target_type)
    {
        $this->db->where('form_id', $form_id);
        $this->db->where('target_type', $target_type);
        $mappings = $this->db->get(db_prefix() . 'form_sync_field_mappings')->result_array();
        
        // Convert to associative array keyed by form_field_id for easier lookup
        $result = [];
        foreach ($mappings as $mapping) {
            $result[$mapping['form_field_id']] = $mapping['perfex_field'];
        }
        
        return $result;
    }

    /**
     * Check if field mappings exist for a form and target type
     * 
     * @param string $form_id Form ID
     * @param string $target_type Target type ('lead' or 'customer')
     * @return boolean
     */
    public function hasFieldMappings($form_id, $target_type)
    {
        $this->db->where('form_id', $form_id);
        $this->db->where('target_type', $target_type);
        $count = $this->db->count_all_results(db_prefix() . 'form_sync_field_mappings');
        
        return $count > 0;
    }

    /**
     * Save field mappings for a form
     * 
     * @param string $form_id Form ID
     * @param string $target_type Target type ('lead' or 'customer')
     * @param array $mappings Array of ['form_field_id' => 'perfex_field'] mappings
     * @param string|null $provider Optional provider name for context
     * @return boolean
     */
    public function saveFieldMappings($form_id, $target_type, $mappings, $provider = null)
    {
        // Start transaction for data integrity
        $this->db->trans_start();
        
        try {
            // Delete existing mappings for this form and target type
            $this->db->where('form_id', $form_id);
            $this->db->where('target_type', $target_type);
            $this->db->delete(db_prefix() . 'form_sync_field_mappings');
            
            // Get form field label from recent submissions once (outside loop for efficiency)
            $form_field_labels = [];
            if (!empty($mappings)) {
                $this->db->reset_query();
                $this->db->where('form_id', $form_id);
                if ($provider) {
                    $this->db->where('provider', $provider);
                }
                $recent_log = $this->db->order_by('datecreated', 'DESC')
                    ->limit(1)
                    ->get(db_prefix() . 'form_sync_submission_logs')
                    ->row();
                
                if ($recent_log && $recent_log->submission_data) {
                    $submission_data = json_decode($recent_log->submission_data, true);
                    if (is_array($submission_data)) {
                        $form_field_labels = $submission_data;
                    }
                }
            }
            
            // Insert new mappings
            $inserted_count = 0;
            foreach ($mappings as $form_field_id => $perfex_field) {
                if ($perfex_field === 'none' || empty($perfex_field)) {
                    continue; // Skip unmapped fields
                }
                
                // Use field ID as label, or get from submission data if available
                $form_field_label = $form_field_id;
                if (isset($form_field_labels[$form_field_id])) {
                    $form_field_label = $form_field_id;
                }
                
                $this->db->reset_query();
                $insert_data = [
                    'form_id' => $form_id,
                    'target_type' => $target_type,
                    'form_field_id' => $form_field_id,
                    'form_field_label' => $form_field_label,
                    'perfex_field' => $perfex_field,
                    'datecreated' => date('Y-m-d H:i:s'),
                    'dateupdated' => date('Y-m-d H:i:s'),
                ];
                
                $insert_result = $this->db->insert(db_prefix() . 'form_sync_field_mappings', $insert_data);
                if (!$insert_result) {
                    $error = $this->db->error();
                    log_message('error', 'Form Sync: Failed to insert field mapping - ' . json_encode($error));
                    $this->db->trans_rollback();
                    return false;
                }
                $inserted_count++;
            }
            
            // Complete transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Form Sync: Transaction failed while saving field mappings');
                return false;
            }
            
            // Even if no mappings were inserted (all filtered out), this is still a success
            // The delete happened, and we're just not inserting anything new
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Form Sync: Successfully saved ' . $inserted_count . ' field mappings for form_id=' . $form_id);
            }
            return true;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Form Sync: Exception in saveFieldMappings - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Map form fields to Perfex CRM fields
     * 
     * @param array $submission Form submission data
     * @param array $mappings Field mappings (associative array: ['form_field_id' => 'perfex_field'])
     * @return array Mapped data for Perfex CRM
     */
    public function mapFields($submission, $mappings)
    {
        $perfexData = [];
        
        // Handle both old format (array of arrays) and new format (associative array)
        foreach ($mappings as $key => $value) {
            if (is_array($value) && isset($value['form_field_id'])) {
                // Old format: array of arrays
                $form_field_id = $value['form_field_id'];
                $perfex_field = $value['perfex_field'];
            } else {
                // New format: associative array
                $form_field_id = $key;
                $perfex_field = $value;
            }
            
            // Get value from submission
            $submission_value = isset($submission[$form_field_id]) ? $submission[$form_field_id] : null;
            
            if ($submission_value !== null && $submission_value !== '') {
                // Handle arrays - if the value is an array, convert to string (take first element or implode)
                if (is_array($submission_value)) {
                    // For arrays, use the first element or implode with comma
                    if (count($submission_value) === 1) {
                        $submission_value = (string)reset($submission_value);
                    } else {
                        $submission_value = implode(', ', array_filter(array_map('trim', $submission_value)));
                    }
                } else {
                    // Ensure it's a string
                    $submission_value = (string)$submission_value;
                }
                
                $perfexData[$perfex_field] = $submission_value;
            }
        }
        
        return $perfexData;
    }

    /**
     * Check for duplicate leads or customers
     * 
     * @param array $data Mapped data
     * @param string $target_type lead or customer
     * @return array ['is_duplicate' => bool, 'entity_type' => 'lead'|'customer', 'entity_id' => int, 'reason' => string]
     */
    public function checkDuplicate($data, $target_type)
    {
        // Safely extract email - handle arrays and ensure it's a string
        $email = '';
        if (isset($data['email'])) {
            if (is_array($data['email'])) {
                // If it's an array, take the first element
                $email = trim((string)(reset($data['email']) ?: ''));
            } else {
                $email = trim((string)$data['email']);
            }
        }
        
        // Safely extract phone - handle arrays and ensure it's a string
        $phone = '';
        if (isset($data['phonenumber'])) {
            if (is_array($data['phonenumber'])) {
                // If it's an array, take the first element
                $phone = trim((string)(reset($data['phonenumber']) ?: ''));
            } else {
                $phone = trim((string)$data['phonenumber']);
            }
        }
        
        // Check email in leads
        if (!empty($email)) {
            $this->db->where('email', $email);
            $lead = $this->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                return [
                    'is_duplicate' => true,
                    'entity_type' => 'lead',
                    'entity_id' => $lead->id,
                    'reason' => 'Email already exists in leads: ' . $email,
                ];
            }
        }
        
        // Check phone in leads
        if (!empty($phone)) {
            $this->db->where('phonenumber', $phone);
            $lead = $this->db->get(db_prefix() . 'leads')->row();
            if ($lead) {
                return [
                    'is_duplicate' => true,
                    'entity_type' => 'lead',
                    'entity_id' => $lead->id,
                    'reason' => 'Phone number already exists in leads: ' . $phone,
                ];
            }
        }
        
        // Check email in customers
        if (!empty($email)) {
            $this->load->model('clients_model');
            $this->db->where('email', $email);
            $customer = $this->db->get(db_prefix() . 'contacts')->row();
            if ($customer) {
                return [
                    'is_duplicate' => true,
                    'entity_type' => 'customer',
                    'entity_id' => $customer->userid,
                    'reason' => 'Email already exists in customers: ' . $email,
                ];
            }
        }
        
        // Check phone in customers
        if (!empty($phone)) {
            $this->load->model('clients_model');
            $this->db->where('phonenumber', $phone);
            $customer = $this->db->get(db_prefix() . 'contacts')->row();
            if ($customer) {
                return [
                    'is_duplicate' => true,
                    'entity_type' => 'customer',
                    'entity_id' => $customer->userid,
                    'reason' => 'Phone number already exists in customers: ' . $phone,
                ];
            }
        }
        
        return [
            'is_duplicate' => false,
            'entity_type' => null,
            'entity_id' => null,
            'reason' => null,
        ];
    }

    /**
     * Create lead from mapped data
     * 
     * Creates a new lead in Perfex CRM from mapped form submission data.
     * Ensures proper visibility settings (is_public=1, addedfrom=0) so that
     * webhook-created leads are visible to all staff members.
     * 
     * @param array $perfexData Mapped lead data (name, email, company, etc.)
     * @param int|null $lead_source_id Optional lead source ID from form configuration
     * @return int|false Lead ID on success, false on failure
     */
    public function createLead($perfexData, $lead_source_id = null)
    {
        $this->load->model('leads_model');
        
        // Ensure required fields - leads need at least name or email
        if (empty($perfexData['name']) && empty($perfexData['email'])) {
            log_message('error', '[FormSync] Lead creation failed: Missing required fields (name or email)');
            return false;
        }
        
        // Clean and prepare data - ensure arrays are converted to strings
        foreach ($perfexData as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to strings
                if (count($value) === 1) {
                    $perfexData[$key] = (string)reset($value);
                } else {
                    $perfexData[$key] = implode(', ', array_filter(array_map('trim', $value)));
                }
            } else {
                $perfexData[$key] = (string)$value;
            }
        }
        
        // Set default values
        // IMPORTANT: Set addedfrom BEFORE calling leads_model->add() because that method overwrites it
        // Also set is_public=1 so leads are visible to all staff (webhook imports should be visible)
        $leadData = [
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => 0, // System import - set BEFORE add() call
            'is_public' => 1, // Make webhook leads visible to all staff
        ];
        
        // Add lead source if provided
        if ($lead_source_id) {
            $leadData['source'] = $lead_source_id;
        }
        
        // If name is not provided, use email or a default
        if (empty($perfexData['name'])) {
            $leadData['name'] = !empty($perfexData['email']) ? $perfexData['email'] : 'Unknown';
        }
        
        // Ensure email is trimmed and valid
        if (isset($perfexData['email'])) {
            $perfexData['email'] = trim($perfexData['email']);
            if (empty($perfexData['email'])) {
                unset($perfexData['email']);
            }
        }
        
        // Merge with provided data
        $leadData = array_merge($leadData, $perfexData);
        
        // CRITICAL: Ensure status is set and is NOT the "Customer" status (isdefault=1)
        // Leads should never be assigned the Customer status - that's only for converted leads
        if (isset($leadData['status']) && !empty($leadData['status'])) {
            // Check if the status is the Customer status
            $this->db->reset_query(); // Reset query builder to avoid state issues
            $this->db->where('id', $leadData['status']);
            $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
            
            if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                log_message('warning', '[FormSync] Status in mapped data is Customer status (ID: ' . $leadData['status'] . '), replacing with non-customer status');
                unset($leadData['status']); // Remove customer status
            }
        }
        
        // If status is not set or was removed, set it to a non-customer status
        if (!isset($leadData['status']) || empty($leadData['status'])) {
            $default_status = get_option('leads_default_status');
            
            // Check if default status is the "Customer" status and exclude it
            if ($default_status && $default_status != '') {
                $this->db->reset_query(); // Reset query builder
                $this->db->where('id', $default_status);
                $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
                
                if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                    log_message('warning', '[FormSync] Default status is Customer status, excluding it for new leads');
                    $default_status = null;
                }
            }
            
            if ($default_status && $default_status != '') {
                $leadData['status'] = $default_status;
            } else {
                // Get first available NON-CUSTOMER status
                $this->load->model('leads_model');
                $statuses = $this->leads_model->get_status();
                if (!empty($statuses) && is_array($statuses)) {
                    // Filter out the Customer status (isdefault=1)
                    $non_customer_statuses = array_filter($statuses, function($status) {
                        return !isset($status['isdefault']) || $status['isdefault'] != 1;
                    });
                    
                    if (!empty($non_customer_statuses)) {
                        $first_status = reset($non_customer_statuses);
                        if (isset($first_status['id'])) {
                            $leadData['status'] = $first_status['id'];
                            log_message('info', '[FormSync] Using first non-customer status: ' . $first_status['id'] . ' (' . ($first_status['name'] ?? 'N/A') . ')');
                        }
                    } else {
                        // Fallback: if all statuses are customer (shouldn't happen), use the first one
                        $first_status = reset($statuses);
                        if (isset($first_status['id'])) {
                            $leadData['status'] = $first_status['id'];
                            log_message('warning', '[FormSync] All statuses are customer status, using first available: ' . $first_status['id']);
                        }
                    }
                }
            }
        }
        
        // Ensure name is set
        if (empty($leadData['name'])) {
            $leadData['name'] = !empty($leadData['email']) ? $leadData['email'] : 'Unknown';
        }
        
        // CRITICAL: Ensure is_public is set to 1 for webhook leads
        // The Leads table query filters: assigned=user OR addedfrom=user OR is_public=1
        // Since webhook leads have addedfrom=0 (system), they must have is_public=1
        // to be visible to staff members. Without this, webhook-created leads won't appear.
        $leadData['is_public'] = 1;
        
        // Create lead
        $leadId = $this->leads_model->add($leadData);
        
        // Verify lead was actually created
        if ($leadId && $leadId > 0) {
            // CRITICAL: Update both addedfrom=0 AND is_public=1 after creation
            // The leads_model->add() method may overwrite addedfrom with get_staff_user_id()
            // (which is 0 for webhooks). The leads table query filters:
            // assigned=user OR addedfrom=user OR is_public=1
            // Since addedfrom=0 (system), we MUST have is_public=1 for visibility
            
            // First, check current state
            $this->db->where('id', $leadId);
            $current_lead = $this->db->get(db_prefix() . 'leads')->row();
            
            if (!$current_lead) {
                log_message('error', '[FormSync] Cannot find lead ID ' . $leadId . ' before update');
                return false;
            }
            
            // Prepare update data
            $update_data = [
                'addedfrom' => 0,
                'is_public' => 1  // Ensure visibility - leads with addedfrom=0 need is_public=1
            ];
            
            // CRITICAL: Check if current status IS Customer status and fix it
            $status_needs_fix = false;
            if (isset($current_lead->status) && !empty($current_lead->status)) {
                $this->db->reset_query(); // Reset query builder
                $this->db->where('id', $current_lead->status);
                $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
                
                if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                    log_message('warning', '[FormSync] Lead ID ' . $leadId . ' has Customer status (ID: ' . $current_lead->status . '), fixing to non-customer status');
                    $status_needs_fix = true;
                }
            }
            
            // Ensure status is set if missing OR if it's Customer status
            if ($status_needs_fix || !isset($current_lead->status) || $current_lead->status === null || $current_lead->status === '') {
                $default_status = get_option('leads_default_status');
                
                // Check if default status is the "Customer" status and exclude it
                if ($default_status && $default_status != '') {
                    $this->db->reset_query(); // Reset query builder
                    $this->db->where('id', $default_status);
                    $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
                    if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                        $default_status = null; // Reset to find a non-customer status
                    }
                }
                
                if ($default_status && $default_status != '') {
                    $update_data['status'] = $default_status;
                } else {
                    // Get first non-customer status
                    $this->load->model('leads_model');
                    $statuses = $this->leads_model->get_status();
                    if (!empty($statuses) && is_array($statuses)) {
                        $non_customer_statuses = array_filter($statuses, function($status) {
                            return !isset($status['isdefault']) || $status['isdefault'] != 1;
                        });
                        if (!empty($non_customer_statuses)) {
                            $first_status = reset($non_customer_statuses);
                            if (isset($first_status['id'])) {
                                $update_data['status'] = $first_status['id'];
                                log_message('info', '[FormSync] Setting lead ID ' . $leadId . ' to non-customer status: ' . $first_status['id'] . ' (' . ($first_status['name'] ?? 'N/A') . ')');
                            }
                        }
                    }
                }
            }
            
            // Perform update
            $this->db->where('id', $leadId);
            $update_result = $this->db->update(db_prefix() . 'leads', $update_data);
            $affected_rows = $this->db->affected_rows();
            
            log_message('info', '[FormSync] Updated lead ID ' . $leadId . ' - update result: ' . ($update_result ? 'success' : 'failed') . ', affected rows: ' . $affected_rows . ', update data: ' . json_encode($update_data));
            
            // Verify update worked
            if ($affected_rows == 0) {
                log_message('warning', '[FormSync] Update query returned success but affected 0 rows for lead ID ' . $leadId);
            }
            
            // Verify the lead exists in database and check visibility settings
            $verifyLead = $this->leads_model->get($leadId);
            
            if (!$verifyLead) {
                log_message('error', '[FormSync] Lead creation returned ID ' . $leadId . ' but lead not found in database');
                return false;
            }
            
            // Double-check visibility settings after update - query directly from database
            $this->db->where('id', $leadId);
            $db_lead = $this->db->get(db_prefix() . 'leads')->row();
            
            if (!$db_lead) {
                log_message('error', '[FormSync] Lead ID ' . $leadId . ' not found in database after creation');
                return false;
            }
            
            log_message('info', '[FormSync] Lead created successfully: ID ' . $leadId . ', Name: ' . ($verifyLead->name ?? 'N/A') . ', addedfrom: ' . ($db_lead->addedfrom ?? 'NULL') . ', is_public: ' . ($db_lead->is_public ?? 'NULL') . ', status: ' . ($db_lead->status ?? 'NULL'));
            
            // CRITICAL: Ensure visibility settings are correct - fix if needed
            $needs_fix = false;
            $fix_data = [];
            
            if (!isset($db_lead->addedfrom) || $db_lead->addedfrom != 0) {
                $needs_fix = true;
                $fix_data['addedfrom'] = 0;
                log_message('warning', '[FormSync] Lead ID ' . $leadId . ' has incorrect addedfrom=' . ($db_lead->addedfrom ?? 'NULL') . ', fixing to 0');
            }
            
            if (!isset($db_lead->is_public) || $db_lead->is_public != 1) {
                $needs_fix = true;
                $fix_data['is_public'] = 1;
                log_message('warning', '[FormSync] Lead ID ' . $leadId . ' has incorrect is_public=' . ($db_lead->is_public ?? 'NULL') . ', fixing to 1');
            }
            
            // CRITICAL: Check if current status IS Customer status and fix it
            if (isset($db_lead->status) && !empty($db_lead->status)) {
                $this->db->reset_query(); // Reset query builder
                $this->db->where('id', $db_lead->status);
                $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
                
                if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                    log_message('warning', '[FormSync] Lead ID ' . $leadId . ' has Customer status (ID: ' . $db_lead->status . '), fixing to non-customer status');
                    $needs_fix = true;
                    // Mark status as needing fix - will be set below
                    $db_lead->status = null; // Force status update
                }
            }
            
            // Ensure status is set (use default if not provided, but NOT the Customer status)
            // OR if status is Customer, replace it
            if (!isset($db_lead->status) || $db_lead->status === null || $db_lead->status === '') {
                $default_status = get_option('leads_default_status');
                
                // Check if default status is the "Customer" status and exclude it
                if ($default_status && $default_status != '') {
                    $this->db->reset_query(); // Reset query builder
                    $this->db->where('id', $default_status);
                    $status_check = $this->db->get(db_prefix() . 'leads_status')->row();
                    if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
                        $default_status = null; // Reset to find a non-customer status
                    }
                }
                
                if ($default_status && $default_status != '') {
                    $needs_fix = true;
                    $fix_data['status'] = $default_status;
                    log_message('info', '[FormSync] Lead ID ' . $leadId . ' has no status, setting to default: ' . $default_status);
                } else {
                    // If no default status, get the first available NON-CUSTOMER status
                    $this->load->model('leads_model');
                    $statuses = $this->leads_model->get_status();
                    if (!empty($statuses) && is_array($statuses)) {
                        // Filter out the Customer status (isdefault=1)
                        $non_customer_statuses = array_filter($statuses, function($status) {
                            return !isset($status['isdefault']) || $status['isdefault'] != 1;
                        });
                        
                        if (!empty($non_customer_statuses)) {
                            $first_status = reset($non_customer_statuses);
                            if (isset($first_status['id'])) {
                                $needs_fix = true;
                                $fix_data['status'] = $first_status['id'];
                                log_message('info', '[FormSync] Lead ID ' . $leadId . ' has no status, setting to first non-customer status: ' . $first_status['id'] . ' (' . ($first_status['name'] ?? 'N/A') . ')');
                            }
                        } else {
                            // Fallback: if all statuses are customer, use the first one anyway
                            $first_status = reset($statuses);
                            if (isset($first_status['id'])) {
                                $needs_fix = true;
                                $fix_data['status'] = $first_status['id'];
                                log_message('warning', '[FormSync] Lead ID ' . $leadId . ' has no status, all statuses are customer, using first available: ' . $first_status['id']);
                            }
                        }
                    }
                }
            }
            
            if ($needs_fix) {
                $this->db->where('id', $leadId);
                $fix_result = $this->db->update(db_prefix() . 'leads', $fix_data);
                log_message('info', '[FormSync] Fixed visibility settings for lead ID ' . $leadId . ', update result: ' . ($fix_result ? 'success' : 'failed'));
                
                // Verify fix
                $this->db->where('id', $leadId);
                $fixed_lead = $this->db->get(db_prefix() . 'leads')->row();
                log_message('info', '[FormSync] After fix - addedfrom: ' . ($fixed_lead->addedfrom ?? 'NULL') . ', is_public: ' . ($fixed_lead->is_public ?? 'NULL') . ', status: ' . ($fixed_lead->status ?? 'NULL'));
            }
            
            return $leadId;
        } else {
            log_message('error', '[FormSync] Lead creation failed: Invalid ID returned');
            return false;
        }
    }

    /**
     * Create customer from mapped data
     * 
     * Creates a new customer in Perfex CRM from mapped form submission data.
     * Requires either email or company name to be present.
     * 
     * @param array $perfexData Mapped customer data (company, email, firstname, etc.)
     * @param int|null $customer_group_id Optional customer group ID from form configuration
     * @return int|false Customer ID on success, false on failure
     */
    public function createCustomer($perfexData, $customer_group_id = null)
    {
        $this->load->model('clients_model');
        
        // Ensure required fields - customers need at least email or company
        if (empty($perfexData['email']) && empty($perfexData['company'])) {
            log_message('error', '[FormSync] Customer creation failed: Missing required fields (email or company)');
            return false;
        }
        
        // Clean and prepare data - ensure arrays are converted to strings
        foreach ($perfexData as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to strings
                if (count($value) === 1) {
                    $perfexData[$key] = (string)reset($value);
                } else {
                    $perfexData[$key] = implode(', ', array_filter(array_map('trim', $value)));
                }
            } else {
                $perfexData[$key] = (string)$value;
            }
        }
        
        // Set default values
        $customerData = [
            'datecreated' => date('Y-m-d H:i:s'),
        ];
        
        // Add customer group if provided
        if ($customer_group_id) {
            $customerData['groups_in'] = [$customer_group_id];
        }
        
        // Ensure email is trimmed and valid
        if (isset($perfexData['email'])) {
            $perfexData['email'] = trim($perfexData['email']);
            if (empty($perfexData['email'])) {
                unset($perfexData['email']);
            }
        }
        
        // Ensure company is set if email is provided but company is not
        if (empty($perfexData['company']) && !empty($perfexData['email'])) {
            $perfexData['company'] = $perfexData['email']; // Use email as company name fallback
        }
        
        // Merge with provided data
        $customerData = array_merge($customerData, $perfexData);
        
        // Create customer
        $customerId = $this->clients_model->add($customerData);
        
        // Verify customer was actually created
        if ($customerId && $customerId > 0) {
            // Verify the customer exists in database
            $verifyCustomer = $this->clients_model->get($customerId);
            if (!$verifyCustomer) {
                log_message('error', '[FormSync] Customer creation returned ID ' . $customerId . ' but customer not found in database');
                return false;
            }
            
            log_message('info', '[FormSync] Customer created and verified: ID ' . $customerId . ', Company: ' . ($verifyCustomer->company ?? 'N/A'));
            return $customerId;
        } else {
            log_message('error', '[FormSync] Customer creation failed: Invalid ID returned (' . var_export($customerId, true) . ')');
            log_message('error', '[FormSync] Customer data attempted: ' . json_encode($customerData));
            return false;
        }
    }

    /**
     * Create estimate request from form submission
     * 
     * @param array $perfexData Mapped Perfex CRM data
     * @param int|null $default_status_id Default status ID (optional)
     * @param int|null $assigned_staff_id Assigned staff member ID (optional)
     * @return int|false Estimate request ID on success, false on failure
     */
    public function createEstimateRequest($perfexData, $default_status_id = null, $assigned_staff_id = null)
    {
        $this->load->model('estimate_request_model');
        
        // Ensure required fields - estimate requests need at least email
        if (empty($perfexData['email'])) {
            log_message('error', '[FormSync] Estimate request creation failed: Missing required field (email)');
            return false;
        }
        
        // Clean and prepare data - ensure arrays are converted to strings
        foreach ($perfexData as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to strings
                if (count($value) === 1) {
                    $perfexData[$key] = (string)reset($value);
                } else {
                    $perfexData[$key] = implode(', ', array_filter(array_map('trim', $value)));
                }
            } else {
                $perfexData[$key] = (string)$value;
            }
        }
        
        // Ensure email is trimmed and valid
        $email = trim($perfexData['email']);
        if (empty($email)) {
            log_message('error', '[FormSync] Estimate request creation failed: Empty email after trimming');
            return false;
        }
        
        // Build submission data in Perfex format: array of {label, name, value} objects
        // Only valid table columns: id, email, submission, last_status_change, date_estimated, 
        // from_form_id, assigned, status, default_language, date_added
        // All other fields go into the submission column as JSON
        $submission = [];
        $valid_columns = ['id', 'email', 'submission', 'last_status_change', 'date_estimated', 
                         'from_form_id', 'assigned', 'status', 'default_language', 'date_added'];
        
        // Field label mapping for better display in Perfex
        $field_labels = [
            'email' => 'Email',
            'name' => 'Name',
            'company' => 'Company',
            'phonenumber' => 'Phone Number',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip Code',
            'country' => 'Country',
            'website' => 'Website',
            'description' => 'Description',
        ];
        
        // Build submission array from all mapped fields (except email which goes in its own column)
        foreach ($perfexData as $field_name => $field_value) {
            // Skip email as it's stored in its own column
            if ($field_name === 'email') {
                continue;
            }
            
            // Skip if empty
            if (empty($field_value) && $field_value !== '0') {
                continue;
            }
            
            // Get label for this field
            $label = isset($field_labels[$field_name]) ? $field_labels[$field_name] : ucfirst(str_replace('_', ' ', $field_name));
            
            // Add to submission array
            $submission[] = [
                'label' => $label,
                'name' => $field_name,
                'value' => $field_value,
            ];
        }
        
        // Set default values - only use valid table columns
        $estimateRequestData = [
            'email' => $email,
            'date_added' => date('Y-m-d H:i:s'),
            'from_form_id' => 0, // Not from a Perfex form
            'submission' => json_encode($submission), // Store all form data as JSON
        ];
        
        // Set status - use provided status or get default
        if ($default_status_id) {
            $estimateRequestData['status'] = $default_status_id;
        } else {
            // Get default status (usually the first status)
            $statuses = $this->estimate_request_model->get_status();
            if (!empty($statuses) && is_array($statuses)) {
                $first_status = reset($statuses);
                if (isset($first_status['id'])) {
                    $estimateRequestData['status'] = $first_status['id'];
                }
            }
        }
        
        // Set assigned staff if provided
        if ($assigned_staff_id) {
            $estimateRequestData['assigned'] = $assigned_staff_id;
        } else {
            $estimateRequestData['assigned'] = 0; // Unassigned
        }
        
        // Insert estimate request directly into database
        $this->db->insert(db_prefix() . 'estimate_requests', $estimateRequestData);
        $estimateRequestId = $this->db->insert_id();
        
        if ($estimateRequestId && $estimateRequestId > 0) {
            // Trigger hooks
            hooks()->do_action('estimate_requests_created', [
                'estimate_request_id' => $estimateRequestId,
                'estimate_request_form' => false, // Not from Perfex form
            ]);
            
            // Send notification if assigned
            if ($assigned_staff_id && $assigned_staff_id > 0) {
                $this->estimate_request_model->assigned_member_notification($estimateRequestId, $assigned_staff_id);
            }
            
            log_message('info', '[FormSync] Estimate request created successfully: ID ' . $estimateRequestId . ', Email: ' . $email);
            return $estimateRequestId;
        } else {
            log_message('error', '[FormSync] Estimate request creation failed: Invalid ID returned (' . var_export($estimateRequestId, true) . ')');
            log_message('error', '[FormSync] Estimate request data attempted: ' . json_encode($estimateRequestData));
            return false;
        }
    }

    /**
     * Create support ticket from form submission
     * 
     * @param array $perfexData Mapped Perfex CRM data
     * @param int|null $default_department_id Default department ID (optional)
     * @param int|null $default_priority Default priority (optional, 0-3)
     * @return int|false Ticket ID on success, false on failure
     */
    public function createTicket($perfexData, $default_department_id = null, $default_priority = null)
    {
        // #region agent log
        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'A','location'=>'Form_sync_model.php:1247','message'=>'createTicket entry','data'=>['perfexData'=>$perfexData,'default_department_id'=>$default_department_id,'default_priority'=>$default_priority],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
        // #endregion
        
        $this->load->model('tickets_model');
        $this->load->model('clients_model');
        
        // Ensure required fields - tickets need subject, message, and email (or userid/contactid)
        if (empty($perfexData['subject'])) {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'Form_sync_model.php:1253','message'=>'Missing subject field','data'=>['perfexData_keys'=>array_keys($perfexData),'has_subject'=>isset($perfexData['subject']),'subject_value'=>$perfexData['subject']??'NOT_SET'],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Ticket creation failed: Missing required field (subject)');
            return false;
        }
        
        if (empty($perfexData['message'])) {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'Form_sync_model.php:1258','message'=>'Missing message field','data'=>['perfexData_keys'=>array_keys($perfexData),'has_message'=>isset($perfexData['message']),'message_value'=>$perfexData['message']??'NOT_SET'],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Ticket creation failed: Missing required field (message)');
            return false;
        }
        
        // Clean and prepare data - ensure arrays are converted to strings
        foreach ($perfexData as $key => $value) {
            if (is_array($value)) {
                // Convert arrays to strings
                if (count($value) === 1) {
                    $perfexData[$key] = (string)reset($value);
                } else {
                    $perfexData[$key] = implode(', ', array_filter(array_map('trim', $value)));
                }
            } else {
                $perfexData[$key] = (string)$value;
            }
        }
        
        // Set default values
        $ticketData = [
            'date' => date('Y-m-d H:i:s'),
            'ticketkey' => app_generate_hash(),
            'status' => 1, // Open status
        ];
        
        // Handle email - try to find existing contact/customer
        if (isset($perfexData['email']) && !empty($perfexData['email'])) {
            $email = trim($perfexData['email']);
            
            // Try to find contact by email
            $contact = $this->clients_model->get_contact_by_email($email);
            if ($contact) {
                $ticketData['userid'] = $contact->userid;
                $ticketData['contactid'] = $contact->id;
                unset($perfexData['email']); // Remove email since we have contact
            } else {
                // Keep email for external ticket
                $ticketData['email'] = $email;
                $ticketData['userid'] = 0;
                $ticketData['contactid'] = 0;
            }
        } else {
            // No email provided
            $ticketData['userid'] = 0;
            $ticketData['contactid'] = 0;
        }
        
        // Set department
        if ($default_department_id) {
            $ticketData['department'] = $default_department_id;
        } elseif (isset($perfexData['department'])) {
            $ticketData['department'] = $perfexData['department'];
            unset($perfexData['department']);
        } else {
            // Get first available department
            $this->load->model('departments_model');
            $departments = $this->departments_model->get();
            if (!empty($departments) && is_array($departments)) {
                $first_dept = reset($departments);
                if (isset($first_dept['departmentid'])) {
                    $ticketData['department'] = $first_dept['departmentid'];
                }
            }
        }
        
        // Set priority
        if ($default_priority !== null) {
            $ticketData['priority'] = (int)$default_priority;
        } elseif (isset($perfexData['priority'])) {
            $ticketData['priority'] = (int)$perfexData['priority'];
            unset($perfexData['priority']);
        } else {
            $ticketData['priority'] = 2; // Default priority (medium)
        }
        
        // Clean subject and message
        $ticketData['subject'] = trim($perfexData['subject']);
        $ticketData['message'] = trim($perfexData['message']);
        
        // Strip HTML tags for security (external form submissions)
        $ticketData['subject'] = _strip_tags($ticketData['subject']);
        $ticketData['message'] = _strip_tags($ticketData['message']);
        $ticketData['message'] = nl2br_save_html($ticketData['message']);
        
        // Remove subject and message from perfexData to avoid duplication
        unset($perfexData['subject'], $perfexData['message']);
        
        // Add name if provided (for external tickets)
        if (isset($perfexData['name'])) {
            $ticketData['name'] = trim($perfexData['name']);
            unset($perfexData['name']);
        }
        
        // Ensure department is set (required for tickets)
        if (empty($ticketData['department'])) {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'D','location'=>'Form_sync_model.php:1353','message'=>'No department available','data'=>['ticketData_keys'=>array_keys($ticketData),'has_department'=>isset($ticketData['department']),'default_department_id'=>$default_department_id],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Ticket creation failed: No department available');
            return false;
        }
        
        // Only include valid ticket table columns
        // Valid columns based on Perfex CRM tickets table structure:
        // ticketid, ticketkey, date, subject, message, status, priority, department, 
        // userid, contactid, email, name, admin, assigned, project_id, service, 
        // lastreply, adminread, clientread, ip, merged_ticket_id, etc.
        // Do NOT include: phonenumber, city, address, company, etc. (these don't exist)
        // NOTE: cc, tags, and custom_fields are handled separately by tickets_model->add()
        $valid_ticket_columns = [
            'ticketid', 'ticketkey', 'date', 'subject', 'message', 'status', 'priority', 
            'department', 'userid', 'contactid', 'email', 'name', 'admin', 'assigned', 
            'project_id', 'service', 'lastreply', 'adminread', 'clientread', 'ip', 
            'merged_ticket_id'
        ];
        
        // Only merge valid columns from perfexData (exclude cc, tags, custom_fields)
        foreach ($perfexData as $key => $value) {
            // Skip if not a valid column (most mapped fields like city, address, etc. are not valid)
            if (!in_array($key, $valid_ticket_columns)) {
                continue;
            }
            $ticketData[$key] = $value;
        }
        
        // #region agent log
        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'E','location'=>'Form_sync_model.php:1381','message'=>'Before tickets_model->add()','data'=>['ticketData_keys'=>array_keys($ticketData),'ticketData'=>array_intersect_key($ticketData,array_flip(['subject','message','email','name','department','priority','status','date','ticketkey','userid','contactid']))],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
        // #endregion
        
        // Use tickets_model->add() method which handles all the logic
        try {
            $ticketId = $this->tickets_model->add($ticketData, null); // null = not from admin area
            
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'F','location'=>'Form_sync_model.php:1386','message'=>'After tickets_model->add()','data'=>['ticketId'=>$ticketId,'ticketId_type'=>gettype($ticketId),'ticketId_valid'=>$ticketId&&$ticketId>0],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            
            // Check for database errors
            $db_error = $this->db->error();
            if (!empty($db_error['message'])) {
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'G','location'=>'Form_sync_model.php:1387','message'=>'Database error detected','data'=>['db_error'=>$db_error],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                // #endregion
                log_message('error', '[FormSync] Database error during ticket creation: ' . $db_error['message']);
                log_message('error', '[FormSync] Database error code: ' . $db_error['code']);
                log_message('error', '[FormSync] Ticket data attempted: ' . json_encode($ticketData));
                return false;
            }
            
            if ($ticketId && $ticketId > 0) {
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'H','location'=>'Form_sync_model.php:1394','message'=>'Ticket created successfully','data'=>['ticketId'=>$ticketId],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                // #endregion
                log_message('info', '[FormSync] Ticket created successfully: ID ' . $ticketId . ', Subject: ' . ($ticketData['subject'] ?? 'N/A'));
                return $ticketId;
            } else {
                // #region agent log
                @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'I','location'=>'Form_sync_model.php:1398','message'=>'Invalid ticket ID returned','data'=>['ticketId'=>$ticketId,'ticketId_type'=>gettype($ticketId)],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                // #endregion
                log_message('error', '[FormSync] Ticket creation failed: Invalid ID returned (' . var_export($ticketId, true) . ')');
                log_message('error', '[FormSync] Ticket data attempted: ' . json_encode($ticketData));
                
                // Check for database errors again (in case insert failed silently)
                $db_error = $this->db->error();
                if (!empty($db_error['message'])) {
                    // #region agent log
                    @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'J','location'=>'Form_sync_model.php:1402','message'=>'Database error after insert','data'=>['db_error'=>$db_error],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                    // #endregion
                    log_message('error', '[FormSync] Database error (after insert): ' . $db_error['message']);
                }
                
                return false;
            }
        } catch (Exception $e) {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'K','location'=>'Form_sync_model.php:1409','message'=>'Exception during ticket creation','data'=>['exception_message'=>$e->getMessage(),'exception_file'=>$e->getFile(),'exception_line'=>$e->getLine()],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Exception during ticket creation: ' . $e->getMessage());
            log_message('error', '[FormSync] Exception file: ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            log_message('error', '[FormSync] Ticket data attempted: ' . json_encode($ticketData));
            return false;
        } catch (Throwable $e) {
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'L','location'=>'Form_sync_model.php:1415','message'=>'Throwable during ticket creation','data'=>['throwable_message'=>$e->getMessage(),'throwable_file'=>$e->getFile(),'throwable_line'=>$e->getLine()],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            log_message('error', '[FormSync] Throwable during ticket creation: ' . $e->getMessage());
            log_message('error', '[FormSync] Throwable file: ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            log_message('error', '[FormSync] Ticket data attempted: ' . json_encode($ticketData));
            return false;
        }
    }

    /**
     * Log submission
     * 
     * @param string $submission_id Submission ID
     * @param string $form_id Form ID
     * @param string $status Status (success, failed, hold)
     * @param string $target_type Target type (lead or customer)
     * @param int|null $customer_id Customer ID if created
     * @param int|null $lead_id Lead ID if created
     * @param string|null $errorMessage Error message
     * @param array|null $submissionData Submission data
     * @param string $provider Provider name ('framer' or 'webflow')
     * @param string|null $site_id Site ID
     * @param string|null $site_name Site name
     * @param string $hold_status Hold status
     * @param string|null $duplicate_reason Duplicate reason
     * @param string|null $duplicate_entity_type Duplicate entity type
     * @param int|null $duplicate_entity_id Duplicate entity ID
     * @param string $hold_reason_type Hold reason type ('duplicate', 'no_mappings', 'none')
     * @return int Log ID
     */
    public function logSubmission($submission_id, $form_id, $status, $target_type, $customer_id, $lead_id, $estimate_request_id = null, $ticket_id = null, $errorMessage = null, $submissionData = null, $provider = 'framer', $site_id = null, $site_name = null, $hold_status = 'none', $duplicate_reason = null, $duplicate_entity_type = null, $duplicate_entity_id = null, $hold_reason_type = 'none')
    {
        $data = [
            'submission_id' => $submission_id,
            'form_id' => $form_id,
            'provider' => $provider,
            'site_id' => $site_id,
            'site_name' => $site_name,
            'target_type' => $target_type,
            'status' => $status,
            'customer_id' => $customer_id,
            'lead_id' => $lead_id,
            'estimate_request_id' => $estimate_request_id,
            'ticket_id' => $ticket_id,
            'hold_status' => $hold_status,
            'duplicate_reason' => $duplicate_reason,
            'duplicate_entity_type' => $duplicate_entity_type,
            'duplicate_entity_id' => $duplicate_entity_id,
            'error_message' => $errorMessage,
            'submission_data' => $submissionData ? json_encode($submissionData) : null,
            'datecreated' => date('Y-m-d H:i:s'),
        ];
        
        // Check if hold_reason_type column exists before adding it
        // This ensures backward compatibility if migration hasn't run yet
        try {
            $columns = $this->db->list_fields(db_prefix() . 'form_sync_submission_logs');
            if (is_array($columns) && in_array('hold_reason_type', $columns)) {
                $data['hold_reason_type'] = $hold_reason_type;
            }
        } catch (Throwable $e) {
            // If we can't check columns, just skip hold_reason_type
            // This prevents errors if table doesn't exist or other issues
            if (ENVIRONMENT === 'development') {
                log_message('debug', '[FormSync] Could not check for hold_reason_type column: ' . $e->getMessage());
            }
        }
        
        try {
            $this->db->insert(db_prefix() . 'form_sync_submission_logs', $data);
            return $this->db->insert_id();
        } catch (Exception $e) {
            // If error is about unknown column, try without hold_reason_type
            $error_message = $e->getMessage();
            if (stripos($error_message, 'hold_reason_type') !== false || stripos($error_message, 'Unknown column') !== false) {
                log_message('info', '[FormSync] Retrying insert without hold_reason_type column');
                unset($data['hold_reason_type']);
                try {
                    $this->db->insert(db_prefix() . 'form_sync_submission_logs', $data);
                    return $this->db->insert_id();
                } catch (Exception $e2) {
                    // Log the error with full details
                    log_message('error', '[FormSync] Failed to insert submission log (retry): ' . $e2->getMessage());
                    log_message('error', '[FormSync] Data: ' . json_encode($data));
                    throw $e2; // Re-throw to let caller handle it
                }
            } else {
                // Log the error with full details
                log_message('error', '[FormSync] Failed to insert submission log: ' . $error_message);
                log_message('error', '[FormSync] Data: ' . json_encode($data));
                throw $e; // Re-throw to let caller handle it
            }
        }
    }

    /**
     * Check if submission already processed
     * 
     * @param string $submission_id Submission ID
     * @return boolean
     */
    public function isSubmissionProcessed($submission_id)
    {
        $this->db->where('submission_id', $submission_id);
        $count = $this->db->count_all_results(db_prefix() . 'form_sync_submission_logs');
        
        return $count > 0;
    }

    /**
     * Process webhook submission (provider-agnostic)
     * 
     * Main processing method for webhook submissions. Handles the complete workflow:
     * 1. Validates form configuration
     * 2. Maps form fields to Perfex CRM fields
     * 3. Checks for duplicates
     * 4. Creates lead, customer, estimate request, or ticket entity
     * 5. Logs the result
     * 
     * @param array $form_data Extracted form data (flat array with field names as keys)
     * @param string $form_id Form ID from provider
     * @param string $provider Provider ID (e.g., 'framer', 'webflow')
     * @param string|null $submission_id Optional submission ID from provider
     * @param string|null $site_id Optional site ID for multi-site providers
     * @param string|null $site_name Optional site name for display purposes
     * @return void
     * @throws Throwable Catches and logs all exceptions, ensures submission is always logged
     */
    public function processWebhookSubmission($form_data, $form_id, $provider, $submission_id = null, $site_id = null, $site_name = null)
    {
        // Initialize variables for error handling
        // IMPORTANT: Default to 'lead' - leads should remain as leads unless explicitly configured as 'customer'
        $target_type = 'lead'; // Default value - leads stay as leads
        
        // Wrap entire method in try-catch to ensure we always log something
        try {
            log_message('info', '[FormSync] processWebhookSubmission called - Form ID: ' . $form_id . ', Provider: ' . $provider . ', Submission ID: ' . ($submission_id ?: 'unknown'));
            
            // Get form configuration
            $form_config = null;
            
            // Try with site_id first for precision (if provided)
            if (!empty($site_id)) {
                $form_config = $this->getFormConfigurationBySiteAndForm($site_id, $form_id, $provider);
            }
            
            // Fallback to form_id only
            if (!$form_config) {
                $form_config = $this->getFormConfigurationByProvider($form_id, $provider);
            }
            
            if (!$form_config) {
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'failed',
                    'lead', // Default to lead when config not found
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'Form configuration not found',
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
                return;
            }
            
            // Convert to array if object
            if (is_object($form_config)) {
                $form_config = (array)$form_config;
            }
            
            if (!isset($form_config['enabled']) || !$form_config['enabled']) {
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'failed',
                    'lead', // Default to lead when disabled
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'Form configuration not found or disabled',
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
                return;
            }
            
            // CRITICAL: Use the target_type from form configuration, default to 'lead' if not set
            // Leads should remain as leads unless explicitly configured as 'customer'
            $target_type = isset($form_config['target_type']) && !empty($form_config['target_type']) 
                ? $form_config['target_type'] 
                : 'lead';
            
            // Log the target_type being used for debugging
            log_message('info', '[FormSync] Using target_type: ' . $target_type . ' for Form ID: ' . $form_id . ' (from config: ' . (isset($form_config['target_type']) ? $form_config['target_type'] : 'not set') . ')');
            
            // Get field mappings
            $mappings = $this->getFieldMappings($form_id, $target_type);
            
            if (empty($mappings)) {
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'hold',
                    $target_type,
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'No field mappings configured for this form and target type.',
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'hold',
                    null,
                    null,
                    null,
                    'no_mappings'
                );
                return;
            }
            
            // Map fields
            $perfexData = $this->mapFields($form_data, $mappings);
            
            // #region agent log
            @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'M','location'=>'Form_sync_model.php:1656','message'=>'After mapFields','data'=>['form_data_keys'=>array_keys($form_data),'mappings_count'=>count($mappings),'perfexData_keys'=>array_keys($perfexData),'perfexData'=>$perfexData],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
            // #endregion
            
            if (empty($perfexData)) {
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'failed',
                    $target_type,
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'No data mapped from submission. Please configure field mappings.',
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
                return;
            }
            
            // Check for duplicates
            $duplicate_check = $this->checkDuplicate($perfexData, $target_type);
            
            if ($duplicate_check['is_duplicate']) {
                // Log as held for review
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'hold',
                    $target_type,
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    'Duplicate found: ' . $duplicate_check['reason'],
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'hold',
                    $duplicate_check['reason'],
                    $duplicate_check['entity_type'],
                    $duplicate_check['entity_id'],
                    'duplicate'
                );
                return;
            }
            
            // Create entity automatically (lead or customer)
            log_message('info', '[FormSync] Creating entity automatically - Form ID: ' . $form_id . ', Target Type: ' . $target_type . ', Submission ID: ' . ($submission_id ?: 'unknown'));
            
            // Get group/source from form configuration
            $customer_group_id = isset($form_config['customer_group_id']) ? $form_config['customer_group_id'] : null;
            $lead_source_id = isset($form_config['lead_source_id']) ? $form_config['lead_source_id'] : null;
            $estimate_request_status_id = isset($form_config['estimate_request_status_id']) ? $form_config['estimate_request_status_id'] : null;
            $estimate_request_assigned_id = isset($form_config['estimate_request_assigned_id']) ? $form_config['estimate_request_assigned_id'] : null;
            $ticket_department_id = isset($form_config['ticket_department_id']) ? $form_config['ticket_department_id'] : null;
            $ticket_priority = isset($form_config['ticket_priority']) ? $form_config['ticket_priority'] : null;
            
            $entityId = false;
            $leadId = null;
            $customerId = null;
            $estimateRequestId = null;
            $ticketId = null;
            $errorMessage = null;
            
            try {
                if ($target_type === 'lead') {
                    $entityId = $this->createLead($perfexData, $lead_source_id);
                    $leadId = $entityId;
                    
                    if (!$entityId || $entityId <= 0) {
                        $errorMessage = 'Failed to create lead';
                        log_message('error', '[FormSync] Lead creation failed for submission: ' . ($submission_id ?: 'unknown'));
                    } else {
                        log_message('info', '[FormSync] Lead created successfully: ID ' . $entityId . ', Form ID: ' . $form_id);
                    }
                } elseif ($target_type === 'customer') {
                    $entityId = $this->createCustomer($perfexData, $customer_group_id);
                    $customerId = $entityId;
                    
                    if (!$entityId || $entityId <= 0) {
                        $errorMessage = 'Failed to create customer';
                        log_message('error', '[FormSync] Customer creation failed for submission: ' . ($submission_id ?: 'unknown'));
                    } else {
                        log_message('info', '[FormSync] Customer created successfully: ID ' . $entityId . ', Form ID: ' . $form_id);
                    }
                } elseif ($target_type === 'estimate_request') {
                    $entityId = $this->createEstimateRequest($perfexData, $estimate_request_status_id, $estimate_request_assigned_id);
                    $estimateRequestId = $entityId;
                    
                    if (!$entityId || $entityId <= 0) {
                        $errorMessage = 'Failed to create estimate request';
                        log_message('error', '[FormSync] Estimate request creation failed for submission: ' . ($submission_id ?: 'unknown'));
                    } else {
                        log_message('info', '[FormSync] Estimate request created successfully: ID ' . $entityId . ', Form ID: ' . $form_id);
                    }
                } elseif ($target_type === 'ticket') {
                    try {
                        // #region agent log
                        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'N','location'=>'Form_sync_model.php:1804','message'=>'Calling createTicket','data'=>['perfexData_keys'=>array_keys($perfexData),'perfexData'=>$perfexData,'ticket_department_id'=>$ticket_department_id,'ticket_priority'=>$ticket_priority],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                        // #endregion
                        $entityId = $this->createTicket($perfexData, $ticket_department_id, $ticket_priority);
                        $ticketId = $entityId;
                        
                        // #region agent log
                        @file_put_contents('/Users/nalinduabeysekara/Documents/Cursor Projects/Perfex crm 2/.cursor/debug.log', json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'O','location'=>'Form_sync_model.php:1807','message'=>'After createTicket call','data'=>['entityId'=>$entityId,'ticketId'=>$ticketId,'entityId_valid'=>$entityId&&$entityId>0],'timestamp'=>round(microtime(true)*1000)]) . "\n", FILE_APPEND);
                        // #endregion
                        
                        if (!$entityId || $entityId <= 0) {
                            // Get more detailed error from database
                            $db_error = $this->db->error();
                            if (!empty($db_error['message'])) {
                                $errorMessage = 'Failed to create ticket: ' . $db_error['message'];
                            } else {
                                $errorMessage = 'Failed to create ticket';
                            }
                            log_message('error', '[FormSync] Ticket creation failed for submission: ' . ($submission_id ?: 'unknown'));
                            if (!empty($db_error['message'])) {
                                log_message('error', '[FormSync] Database error: ' . $db_error['message']);
                            }
                        } else {
                            log_message('info', '[FormSync] Ticket created successfully: ID ' . $entityId . ', Form ID: ' . $form_id);
                        }
                    } catch (Throwable $ticket_e) {
                        $errorMessage = 'Exception during ticket creation: ' . $ticket_e->getMessage();
                        log_message('error', '[FormSync] Exception during ticket creation: ' . $ticket_e->getMessage());
                        log_message('error', '[FormSync] Stack trace: ' . $ticket_e->getTraceAsString());
                        $entityId = false;
                        $ticketId = null;
                    }
                } else {
                    $errorMessage = 'Unknown target type: ' . $target_type;
                    log_message('error', '[FormSync] Unknown target type: ' . $target_type);
                }
            } catch (Exception $create_e) {
                $errorMessage = 'Exception during entity creation: ' . $create_e->getMessage();
                log_message('error', '[FormSync] Exception during entity creation: ' . $create_e->getMessage());
                log_message('error', '[FormSync] Stack trace: ' . $create_e->getTraceAsString());
            }
            
            // Log submission result
            if ($entityId && $entityId > 0) {
                // Success - log with entity ID
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'success',
                    $target_type,
                    $customerId,
                    $leadId,
                    $estimateRequestId,
                    $ticketId,
                    null, // No error message for success
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
                log_message('info', '[FormSync] Submission processed successfully - Entity ID: ' . $entityId . ', Type: ' . $target_type);
            } else {
                // Failed - log with error
                $this->logSubmission(
                    $submission_id ?: 'unknown',
                    $form_id,
                    'failed',
                    $target_type,
                    null,
                    null,
                    null, // estimate_request_id
                    null, // ticket_id
                    $errorMessage ?: 'Failed to create entity',
                    $form_data,
                    $provider,
                    $site_id,
                    $site_name,
                    'none',
                    null,
                    null,
                    null,
                    'none'
                );
                log_message('error', '[FormSync] Submission processing failed - Error: ' . ($errorMessage ?: 'Unknown error'));
            }
        } catch (Exception $e) {
            // Log exception with full details
            $error_message = 'Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
            log_message('error', '[FormSync] processWebhookSubmission exception: ' . $error_message);
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            
            // Ensure target_type is set (use default if not set)
            if (!isset($target_type)) {
                $target_type = 'customer';
            }
            
            $this->logSubmission(
                $submission_id ?: 'unknown',
                $form_id ?: 'unknown',
                'failed',
                $target_type,
                null,
                null,
                null, // estimate_request_id
                null, // ticket_id
                $error_message,
                $form_data,
                $provider ?: 'unknown',
                $site_id,
                $site_name,
                'none',
                null,
                null,
                null,
                'none'
            );
            // Re-throw to let caller know there was an error
            throw $e;
        } catch (Throwable $e) {
                // Catch any fatal errors or exceptions that occur before we can log
                $error_message = 'Fatal error in processWebhookSubmission: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine();
                log_message('error', '[FormSync] ' . $error_message);
                log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
                
                // Try to log the submission even if everything else failed
                try {
                    $this->logSubmission(
                        $submission_id ?: 'unknown',
                        $form_id ?: 'unknown',
                        'failed',
                        $target_type ?: 'customer',
                        null,
                        null,
                        null, // estimate_request_id
                        null, // ticket_id
                        $error_message,
                        $form_data,
                        $provider ?: 'unknown',
                        $site_id,
                        $site_name,
                        'none',
                        null,
                        null,
                        null,
                        'none'
                    );
                } catch (Exception $log_e) {
                    // If even logging fails, log to error log
                    log_message('error', '[FormSync] Failed to log submission after fatal error: ' . $log_e->getMessage());
                }
                
                // Re-throw to let caller know there was an error
                throw $e;
            }
    }


    /**
     * Get submission logs with filters
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Optional filters
     * @return array
     */
    public function getSubmissionLogs($limit = 100, $offset = 0, $filters = [])
    {
        if (isset($filters['form_id']) && !empty($filters['form_id'])) {
            $this->db->where('form_id', $filters['form_id']);
        }
        
        if (isset($filters['target_type']) && !empty($filters['target_type'])) {
            $this->db->where('target_type', $filters['target_type']);
        }
        
        if (isset($filters['hold_status']) && !empty($filters['hold_status'])) {
            $this->db->where('hold_status', $filters['hold_status']);
        }
        
        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        if (isset($filters['provider']) && !empty($filters['provider'])) {
            $this->db->where('provider', $filters['provider']);
        }
        
        $this->db->order_by('datecreated', 'DESC');
        $this->db->limit($limit, $offset);
        
        $logs = $this->db->get(db_prefix() . 'form_sync_submission_logs')->result_array();
        
        // Enrich with customer/lead info
        foreach ($logs as &$log) {
            try {
                if (!empty($log['customer_id'])) {
                    try {
                        $this->load->model('clients_model');
                        $customer = $this->clients_model->get($log['customer_id']);
                        if ($customer) {
                            $log['customer_name'] = $customer->company ? $customer->company : trim($customer->firstname . ' ' . $customer->lastname);
                            $log['customer_email'] = $customer->email;
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error loading customer info for log ID ' . (isset($log['id']) ? $log['id'] : 'unknown') . ': ' . $e->getMessage());
                    }
                }
                if (!empty($log['lead_id'])) {
                    try {
                        $this->load->model('leads_model');
                        $lead = $this->leads_model->get($log['lead_id']);
                        if ($lead) {
                            $log['lead_name'] = $lead->name;
                            $log['lead_email'] = $lead->email;
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error loading lead info for log ID ' . (isset($log['id']) ? $log['id'] : 'unknown') . ': ' . $e->getMessage());
                    }
                }
                
                // Get form name from configuration
                if (!empty($log['form_id'])) {
                    try {
                        $provider = isset($log['provider']) && !empty($log['provider']) ? $log['provider'] : 'framer';
                        $form_config = $this->getFormConfigurationByProvider($log['form_id'], $provider);
                        if ($form_config) {
                            if (is_object($form_config)) {
                                $form_config = (array)$form_config;
                            }
                            $log['form_name'] = isset($form_config['form_name']) ? $form_config['form_name'] : $log['form_id'];
                        } else {
                            $log['form_name'] = $log['form_id'];
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error loading form config for form_id ' . $log['form_id'] . ', provider ' . (isset($log['provider']) ? $log['provider'] : 'unknown') . ': ' . $e->getMessage());
                        $log['form_name'] = $log['form_id'];
                    }
                } else {
                    $log['form_name'] = isset($log['form_id']) ? $log['form_id'] : 'Unknown';
                }
            } catch (Throwable $e) {
                log_message('error', '[FormSync] Error enriching log entry: ' . $e->getMessage());
                // Continue processing other logs
            }
        }
        
        return $logs;
    }

    /**
     * Get held submissions
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getHeldSubmissions($filters = [])
    {
        $filters['hold_status'] = 'hold';
        
        return $this->getSubmissionLogs(1000, 0, $filters);
    }

    /**
     * Get submission log by ID
     * 
     * @param int $log_id
     * @return array|false
     */
    public function getSubmissionLogById($log_id)
    {
        try {
            $this->db->where('id', $log_id);
            $log = $this->db->get(db_prefix() . 'form_sync_submission_logs')->row_array();
            
            if ($log) {
                // Enrich with customer/lead info
                if (!empty($log['customer_id'])) {
                    try {
                        $this->load->model('clients_model');
                        $customer = $this->clients_model->get($log['customer_id']);
                        if ($customer) {
                            $log['customer_name'] = $customer->company ? $customer->company : trim($customer->firstname . ' ' . $customer->lastname);
                            $log['customer_email'] = $customer->email;
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error loading customer info in getSubmissionLogById: ' . $e->getMessage());
                    }
                }
                if (!empty($log['lead_id'])) {
                    try {
                        $this->load->model('leads_model');
                        $lead = $this->leads_model->get($log['lead_id']);
                        if ($lead) {
                            $log['lead_name'] = $lead->name;
                            $log['lead_email'] = $lead->email;
                        }
                    } catch (Exception $e) {
                        log_message('error', '[FormSync] Error loading lead info in getSubmissionLogById: ' . $e->getMessage());
                    }
                }
            }
            
            return $log;
        } catch (Throwable $e) {
            log_message('error', '[FormSync] Error in getSubmissionLogById: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve held submission
     * 
     * Processes a submission that was held for review (e.g., due to duplicate detection).
     * Creates the lead or customer entity from the submission data, bypassing duplicate checks
     * since the administrator has explicitly approved it.
     * 
     * Uses database transactions to ensure atomicity. After successful creation,
     * verifies and fixes visibility settings for leads to ensure they're visible to all staff.
     * 
     * @param int $log_id Submission log ID
     * @return array Result array with 'success' (bool) and 'message' (string) keys
     * @throws Exception|Throwable Catches and logs all exceptions, returns error message
     */
    public function approveHeldSubmission($log_id)
    {
        // Validate log_id
        if (empty($log_id) || !is_numeric($log_id) || $log_id <= 0) {
            return ['success' => false, 'message' => 'Invalid submission ID.'];
        }
        
        // Start transaction for atomicity
        $this->db->trans_start();
        
        try {
            // Get submission log with row lock to prevent concurrent approval
            $this->db->where('id', $log_id);
            $this->db->where('hold_status', 'hold');
            $log = $this->db->get(db_prefix() . 'form_sync_submission_logs')->row_array();
            
            if (!$log) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Submission not found or not in hold status. It may have already been approved or ignored.'];
            }
            
            // Validate submission_data is valid JSON
            $submission_data = null;
            if (!empty($log['submission_data'])) {
                $submission_data = json_decode($log['submission_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->db->trans_rollback();
                    log_message('error', '[FormSync] Invalid JSON in submission_data for log_id: ' . $log_id . ' - Error: ' . json_last_error_msg());
                    return ['success' => false, 'message' => 'Invalid submission data. The data appears to be corrupted. Please contact support.'];
                }
            }
            
            if (!$submission_data || !is_array($submission_data)) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Invalid submission data. No data found in submission.'];
            }
            
            // Get form configuration
            $provider = isset($log['provider']) ? $log['provider'] : 'framer';
            $form_config = $this->getFormConfigurationByProvider($log['form_id'], $provider);
            
            if (!$form_config) {
                $this->db->trans_rollback();
                log_message('warning', '[FormSync] Form configuration not found for approval - Form ID: ' . $log['form_id'] . ', Provider: ' . $provider);
                return ['success' => false, 'message' => 'Form configuration not found. The form may have been deleted. Please ensure the form is still configured.'];
            }
            
            // Convert to array if object
            if (is_object($form_config)) {
                $form_config = (array)$form_config;
            }
            
            // Check if form configuration is enabled
            if (!isset($form_config['enabled']) || !$form_config['enabled']) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Form configuration is disabled. Please enable it before approving submissions.'];
            }
            
            // Get field mappings
            $mappings = $this->getFieldMappings($log['form_id'], $log['target_type']);
            if (empty($mappings)) {
                $this->db->trans_rollback();
                log_message('warning', '[FormSync] Field mappings not configured for approval - Form ID: ' . $log['form_id'] . ', Target Type: ' . $log['target_type']);
                return ['success' => false, 'message' => 'Field mappings not configured. Please configure field mappings first before approving submissions.'];
            }
            
            // Map fields
            $perfexData = $this->mapFields($submission_data, $mappings);
            
            if (empty($perfexData)) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'No data could be mapped from submission. Please check field mappings configuration.'];
            }
            
            // Get group/source from form configuration
            $customer_group_id = isset($form_config['customer_group_id']) ? $form_config['customer_group_id'] : null;
            $lead_source_id = isset($form_config['lead_source_id']) ? $form_config['lead_source_id'] : null;
            
            // Create entity (ignore duplicate check since admin approved)
            log_message('info', '[FormSync] Approving submission - Log ID: ' . $log_id . ', Target Type: ' . $log['target_type'] . ', Mapped Data: ' . json_encode($perfexData));
            
            $entityId = false;
            $leadId = null;
            $customerId = null;
            $estimateRequestId = null;
            $ticketId = null;
            
            if ($log['target_type'] === 'lead') {
                $entityId = $this->createLead($perfexData, $lead_source_id);
                $leadId = $entityId;
                
                if ($entityId && $entityId > 0) {
                    // Final verification: ensure lead exists and has data
                    $this->load->model('leads_model');
                    $verifyLead = $this->leads_model->get($entityId);
                    
                    if ($verifyLead) {
                        log_message('info', '[FormSync] Lead creation verified during approval: ID ' . $entityId . ', Name: ' . ($verifyLead->name ?? 'N/A'));
                    } else {
                        log_message('error', '[FormSync] Lead creation returned ID ' . $entityId . ' but verification failed during approval - lead not found');
                        $entityId = false; // Mark as failed
                    }
                }
                
                log_message('info', '[FormSync] Lead creation result during approval: ' . ($entityId ? 'Success (ID: ' . $entityId . ')' : 'Failed'));
            } elseif ($log['target_type'] === 'customer') {
                $entityId = $this->createCustomer($perfexData, $customer_group_id);
                $customerId = $entityId;
                
                if ($entityId && $entityId > 0) {
                    // Final verification: ensure customer exists and has data
                    $this->load->model('clients_model');
                    $verifyCustomer = $this->clients_model->get($entityId);
                    if ($verifyCustomer) {
                        log_message('info', '[FormSync] Customer creation verified during approval: ID ' . $entityId . ', Company: ' . ($verifyCustomer->company ?? 'N/A'));
                    } else {
                        log_message('error', '[FormSync] Customer creation returned ID ' . $entityId . ' but verification failed during approval - customer not found');
                        $entityId = false; // Mark as failed
                    }
                }
                
                log_message('info', '[FormSync] Customer creation result during approval: ' . ($entityId ? 'Success (ID: ' . $entityId . ')' : 'Failed'));
            } elseif ($log['target_type'] === 'estimate_request') {
                $estimate_request_status_id = isset($form_config['estimate_request_status_id']) ? $form_config['estimate_request_status_id'] : null;
                $estimate_request_assigned_id = isset($form_config['estimate_request_assigned_id']) ? $form_config['estimate_request_assigned_id'] : null;
                $entityId = $this->createEstimateRequest($perfexData, $estimate_request_status_id, $estimate_request_assigned_id);
                $estimateRequestId = $entityId;
                
                if ($entityId && $entityId > 0) {
                    log_message('info', '[FormSync] Estimate request created during approval: ID ' . $entityId);
                }
                
                log_message('info', '[FormSync] Estimate request creation result during approval: ' . ($entityId ? 'Success (ID: ' . $entityId . ')' : 'Failed'));
            } elseif ($log['target_type'] === 'ticket') {
                $ticket_department_id = isset($form_config['ticket_department_id']) ? $form_config['ticket_department_id'] : null;
                $ticket_priority = isset($form_config['ticket_priority']) ? $form_config['ticket_priority'] : null;
                $entityId = $this->createTicket($perfexData, $ticket_department_id, $ticket_priority);
                $ticketId = $entityId;
                
                if ($entityId && $entityId > 0) {
                    log_message('info', '[FormSync] Ticket created during approval: ID ' . $entityId);
                }
                
                log_message('info', '[FormSync] Ticket creation result during approval: ' . ($entityId ? 'Success (ID: ' . $entityId . ')' : 'Failed'));
            } else {
                log_message('error', '[FormSync] Unknown target type during approval: ' . $log['target_type']);
            }
            
            // Update log only if entity creation succeeded and is verified
            if ($entityId && $entityId > 0) {
                $this->db->where('id', $log_id);
                $this->db->update(db_prefix() . 'form_sync_submission_logs', [
                    'hold_status' => 'approved',
                    'status' => 'success',
                    'customer_id' => $customerId,
                    'lead_id' => $leadId,
                    'estimate_request_id' => $estimateRequestId,
                    'ticket_id' => $ticketId,
                ]);
                
                // Commit transaction
                $this->db->trans_commit();
                
                // CRITICAL: After transaction commits, verify and fix visibility settings
                // This ensures the lead is visible even if the update inside createLead didn't work
                if ($log['target_type'] === 'lead' && $leadId) {
                    $this->db->where('id', $leadId);
                    $final_check = $this->db->get(db_prefix() . 'leads')->row();
                    
                    if ($final_check) {
                        $needs_final_fix = false;
                        $final_fix_data = [];
                        
                        if (!isset($final_check->addedfrom) || $final_check->addedfrom != 0) {
                            $needs_final_fix = true;
                            $final_fix_data['addedfrom'] = 0;
                        }
                        
                        if (!isset($final_check->is_public) || $final_check->is_public != 1) {
                            $needs_final_fix = true;
                            $final_fix_data['is_public'] = 1;
                        }
                        
                        if ($needs_final_fix) {
                            log_message('warning', '[FormSync] Final fix needed for lead ID ' . $leadId . ' after approval - fixing visibility');
                            
                            // Try CodeIgniter query builder
                            $this->db->where('id', $leadId);
                            $update_result = $this->db->update(db_prefix() . 'leads', $final_fix_data);
                            $affected = $this->db->affected_rows();
                            
                            // If that didn't work, try direct SQL
                            if (!$update_result || $affected == 0) {
                                log_message('warning', '[FormSync] Final fix - CodeIgniter update failed, trying direct SQL');
                                $sql = 'UPDATE `' . db_prefix() . 'leads` SET ';
                                $set_parts = [];
                                foreach ($final_fix_data as $key => $value) {
                                    $set_parts[] = '`' . $key . '` = ' . $this->db->escape($value);
                                }
                                $sql .= implode(', ', $set_parts) . ' WHERE `id` = ' . (int)$leadId;
                                $direct_result = $this->db->query($sql);
                                $affected = $this->db->affected_rows();
                                $update_result = $direct_result;
                                log_message('info', '[FormSync] Final fix - Direct SQL result: ' . ($update_result ? 'success' : 'failed') . ', affected: ' . $affected);
                            }
                            
                            log_message('info', '[FormSync] Final fix applied - addedfrom: ' . ($final_fix_data['addedfrom'] ?? 'N/A') . ', is_public: ' . ($final_fix_data['is_public'] ?? 'N/A'));
                        }
                    }
                }
                
                $entity_type = $log['target_type'];
                $entity_label = str_replace('_', ' ', ucfirst($entity_type));
                log_message('info', '[FormSync] Submission approved successfully - Log ID: ' . $log_id . ', Entity Type: ' . $entity_type . ', Entity ID: ' . $entityId);
                return ['success' => true, 'message' => $entity_label . ' created successfully (ID: ' . $entityId . ').'];
            } else {
                // Entity creation failed or verification failed - rollback transaction
                $this->db->trans_rollback();
                
                // Determine failure reason
                $entity_label = str_replace('_', ' ', ucfirst($log['target_type']));
                $errorMsg = 'Failed to create ' . $entity_label . '. ';
                if ($log['target_type'] === 'lead') {
                    if (empty($perfexData['name']) && empty($perfexData['email'])) {
                        $errorMsg .= 'Missing required fields: name or email.';
                    } else {
                        $errorMsg .= 'Lead creation returned invalid ID or verification failed. Please check the submission data and logs.';
                    }
                } elseif ($log['target_type'] === 'customer') {
                    if (empty($perfexData['email']) && empty($perfexData['company'])) {
                        $errorMsg .= 'Missing required fields: email or company name.';
                    } else {
                        $errorMsg .= 'Customer creation returned invalid ID or verification failed. Please check the submission data and logs.';
                    }
                } elseif ($log['target_type'] === 'estimate_request') {
                    if (empty($perfexData['email'])) {
                        $errorMsg .= 'Missing required field: email.';
                    } else {
                        $errorMsg .= 'Estimate request creation failed. Please check the submission data and logs.';
                    }
                } elseif ($log['target_type'] === 'ticket') {
                    if (empty($perfexData['subject']) || empty($perfexData['message'])) {
                        $errorMsg .= 'Missing required fields: subject or message.';
                    } else {
                        $errorMsg .= 'Ticket creation failed. Please check the submission data and logs.';
                    }
                } else {
                    $errorMsg .= 'Unknown target type. Please check the form configuration.';
                }
                
                log_message('error', '[FormSync] Submission approval failed - Log ID: ' . $log_id . ' - ' . $errorMsg);
                log_message('error', '[FormSync] Approval failed - Perfex Data: ' . json_encode($perfexData));
                return ['success' => false, 'message' => $errorMsg];
            }
        } catch (Exception $e) {
            // Rollback on any exception
            $this->db->trans_rollback();
            
            $error_message = 'Exception during approval: ' . $e->getMessage();
            log_message('error', '[FormSync] Exception in approveHeldSubmission - Log ID: ' . $log_id . ' - ' . $error_message . ' in ' . $e->getFile() . ':' . $e->getLine());
            log_message('error', '[FormSync] Stack trace: ' . $e->getTraceAsString());
            
            return ['success' => false, 'message' => 'An error occurred while approving the submission. Please try again or contact support if the problem persists.'];
        } catch (Throwable $e) {
            // Rollback on any fatal error
            $this->db->trans_rollback();
            
            $error_message = 'Fatal error during approval: ' . $e->getMessage();
            log_message('error', '[FormSync] Fatal error in approveHeldSubmission - Log ID: ' . $log_id . ' - ' . $error_message . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            return ['success' => false, 'message' => 'A system error occurred while approving the submission. Please contact support.'];
        }
    }

    /**
     * Ignore held submission
     * 
     * Marks a held submission as ignored, preventing it from being processed.
     * The submission remains in the logs but will not be processed further.
     * 
     * @param int $log_id Submission log ID
     * @return bool True on success, false if submission not found or not in hold status
     */
    public function ignoreHeldSubmission($log_id)
    {
        $log = $this->getSubmissionLogById($log_id);
        
        if (!$log || $log['hold_status'] !== 'hold') {
            return false;
        }
        
        // Update log
        $this->db->where('id', $log_id);
        $this->db->update(db_prefix() . 'form_sync_submission_logs', [
            'hold_status' => 'ignored',
        ]);
        
        return true;
    }

    /**
     * Bulk approve held submissions
     * 
     * Approves multiple held submissions at once. Each submission is processed
     * independently, so failures don't prevent other submissions from being approved.
     * Limited to 100 submissions per batch to prevent timeouts.
     * 
     * @param array $log_ids Array of log IDs to approve
     * @return array Result array with:
     *   - 'success_count' (int): Number of successfully approved submissions
     *   - 'failed_count' (int): Number of failed approvals
     *   - 'errors' (array): Array of error details for failed submissions
     */
    public function bulkApproveSubmissions($log_ids)
    {
        // Validate input
        if (empty($log_ids) || !is_array($log_ids)) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['Invalid input: log_ids must be a non-empty array.']
            ];
        }
        
        // Limit bulk operations to prevent timeouts (max 100 at a time)
        if (count($log_ids) > 100) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['Too many submissions selected. Maximum 100 submissions can be approved at once.']
            ];
        }
        
        // Filter and validate each ID
        $valid_ids = array_filter(array_map('intval', $log_ids), function($id) {
            return $id > 0;
        });
        
        if (empty($valid_ids)) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['No valid submission IDs provided.']
            ];
        }
        
        // Remove duplicates
        $valid_ids = array_unique($valid_ids);
        
        $success_count = 0;
        $failed_count = 0;
        $errors = [];
        
        // Process each submission independently (don't fail all if one fails)
        foreach ($valid_ids as $log_id) {
            try {
                $result = $this->approveHeldSubmission($log_id);
                
                if (is_array($result) && isset($result['success']) && $result['success']) {
                    $success_count++;
                } else {
                    $failed_count++;
                    $error_message = isset($result['message']) ? $result['message'] : 'Unknown error';
                    $errors[] = [
                        'log_id' => $log_id,
                        'message' => $error_message
                    ];
                }
            } catch (Exception $e) {
                $failed_count++;
                $error_message = 'Exception: ' . $e->getMessage();
                $errors[] = [
                    'log_id' => $log_id,
                    'message' => $error_message
                ];
                log_message('error', '[FormSync] Exception in bulkApproveSubmissions for log_id ' . $log_id . ': ' . $e->getMessage());
            } catch (Throwable $e) {
                $failed_count++;
                $error_message = 'Fatal error: ' . $e->getMessage();
                $errors[] = [
                    'log_id' => $log_id,
                    'message' => $error_message
                ];
                log_message('error', '[FormSync] Fatal error in bulkApproveSubmissions for log_id ' . $log_id . ': ' . $e->getMessage());
            }
        }
        
        log_message('info', '[FormSync] Bulk approval completed - Success: ' . $success_count . ', Failed: ' . $failed_count . ', Total: ' . count($valid_ids));
        
        return [
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'errors' => $errors
        ];
    }

    /**
     * Bulk ignore held submissions
     * 
     * Marks multiple held submissions as ignored at once.
     * Limited to 100 submissions per batch to prevent timeouts.
     * 
     * @param array $log_ids Array of log IDs to ignore
     * @return array Result array with:
     *   - 'success_count' (int): Number of successfully ignored submissions
     *   - 'failed_count' (int): Number of failed operations
     *   - 'errors' (array): Array of error details for failed operations
     */
    public function bulkIgnoreSubmissions($log_ids)
    {
        // Validate input
        if (empty($log_ids) || !is_array($log_ids)) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['Invalid input: log_ids must be a non-empty array.']
            ];
        }
        
        // Limit bulk operations to prevent timeouts (max 100 at a time)
        if (count($log_ids) > 100) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['Too many submissions selected. Maximum 100 submissions can be ignored at once.']
            ];
        }
        
        // Filter and validate each ID
        $valid_ids = array_filter(array_map('intval', $log_ids), function($id) {
            return $id > 0;
        });
        
        if (empty($valid_ids)) {
            return [
                'success_count' => 0,
                'failed_count' => 0,
                'errors' => ['No valid submission IDs provided.']
            ];
        }
        
        // Remove duplicates
        $valid_ids = array_unique($valid_ids);
        
        $success_count = 0;
        $failed_count = 0;
        $errors = [];
        
        // Process each submission independently
        foreach ($valid_ids as $log_id) {
            try {
                if ($this->ignoreHeldSubmission($log_id)) {
                    $success_count++;
                } else {
                    $failed_count++;
                    $errors[] = [
                        'log_id' => $log_id,
                        'message' => 'Submission not found or not in hold status.'
                    ];
                }
            } catch (Exception $e) {
                $failed_count++;
                $error_message = 'Exception: ' . $e->getMessage();
                $errors[] = [
                    'log_id' => $log_id,
                    'message' => $error_message
                ];
                log_message('error', '[FormSync] Exception in bulkIgnoreSubmissions for log_id ' . $log_id . ': ' . $e->getMessage());
            } catch (Throwable $e) {
                $failed_count++;
                $error_message = 'Fatal error: ' . $e->getMessage();
                $errors[] = [
                    'log_id' => $log_id,
                    'message' => $error_message
                ];
                log_message('error', '[FormSync] Fatal error in bulkIgnoreSubmissions for log_id ' . $log_id . ': ' . $e->getMessage());
            }
        }
        
        log_message('info', '[FormSync] Bulk ignore completed - Success: ' . $success_count . ', Failed: ' . $failed_count . ', Total: ' . count($valid_ids));
        
        return [
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'errors' => $errors
        ];
    }
    
    /**
     * Fix visibility for all FormSync-created leads
     * 
     * This method fixes visibility settings for all leads with addedfrom=0
     * that don't have is_public=1. This ensures all webhook-created leads
     * are visible in the Leads table.
     * 
     * The Leads table query filters: assigned=user OR addedfrom=user OR is_public=1
     * Since webhook leads have addedfrom=0 (system), they must have is_public=1
     * to be visible to staff members.
     * 
     * @return array Result array with:
     *   - 'fixed' (int): Number of leads that were fixed
     *   - 'correct' (int): Number of leads that were already correct
     *   - 'total' (int): Total number of FormSync-created leads
     */
    public function fixAllLeadsVisibility()
    {
        // Find all leads with addedfrom=0 that need fixing
        $this->db->where('addedfrom', 0);
        $this->db->where('(is_public IS NULL OR is_public != 1)');
        $leads_to_fix = $this->db->get(db_prefix() . 'leads')->result();
        
        $fixed = 0;
        $correct = 0;
        
        foreach ($leads_to_fix as $lead) {
            // Use direct SQL to ensure update works
            $sql = 'UPDATE `' . db_prefix() . 'leads` SET `addedfrom` = 0, `is_public` = 1 WHERE `id` = ' . (int)$lead->id;
            $result = $this->db->query($sql);
            $affected = $this->db->affected_rows();
            
            if ($result && $affected > 0) {
                $fixed++;
                log_message('info', '[FormSync] Fixed visibility for lead ID ' . $lead->id);
            }
        }
        
        // Count leads that are already correct
        $this->db->where('addedfrom', 0);
        $this->db->where('is_public', 1);
        $correct = $this->db->count_all_results(db_prefix() . 'leads');
        
        $total = $fixed + $correct;
        
        return [
            'fixed' => $fixed,
            'correct' => $correct,
            'total' => $total
        ];
    }


}

