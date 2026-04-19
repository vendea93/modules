<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * FormSync Module
 * 
 * Seamlessly sync form submissions with Perfex CRM to automatically capture submissions
 * and create leads or customers in real time. Supports multiple forms, field mapping, duplicate
 * detection, and manual/automatic synchronization.
 * 
 * @package    FormSync
 * @category   Module
 * @version    1.0.0
 * @requires   Perfex CRM 2.3.*
 * @author     LiquidApps Studio
 * 
 * Module Name: FormSync
 * Description: Seamlessly sync form submissions with Perfex CRM to automatically capture submissions and create leads or customers in real time.
 * Version: 1.0.0
 * Requires at least: 2.3.*
 * Author: LiquidApps Studio
 * Author URI: https://liquidapps.studio
 * Module URI: https://liquidapps.studio/form-sync
 */

// ============================================================================
// MODULE CONSTANTS
// ============================================================================

/**
 * Module name constant
 * Used throughout the module for consistency
 */
define('FORMSYNC_MODULE_NAME', 'form_sync');

// ============================================================================
// ACTIVATION HOOK
// ============================================================================

/**
 * Register activation hook
 * 
 * This hook is called when the module is activated in Perfex CRM.
 * It runs the installation script and registers necessary hooks.
 */
register_activation_hook(FORMSYNC_MODULE_NAME, 'form_sync_activation_hook');

/**
 * Activation hook handler
 * 
 * Executes the installation script and registers necessary hooks.
 * 
 * @return void
 */
function form_sync_activation_hook()
{
    try {
        $CI = &get_instance();
        
        // Run installation script to create database tables and set default options
        require_once(__DIR__ . '/install.php');
        log_message('info', '[FormSync] Installation script executed successfully');
        
    } catch (Exception $e) {
        log_message('error', '[FormSync] Activation hook error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    }
}

// ============================================================================
// DEACTIVATION HOOK
// ============================================================================

/**
 * Register deactivation hook
 * 
 * This hook is called when the module is deactivated in Perfex CRM.
 * It removes registered hooks and cleans up temporary data.
 */
register_deactivation_hook(FORMSYNC_MODULE_NAME, 'form_sync_deactivation_hook');

/**
 * Deactivation hook handler
 * 
 * Removes hooks when module is deactivated.
 * Settings are preserved so the module can be reactivated without reconfiguration.
 * 
 * @return void
 */
function form_sync_deactivation_hook()
{
    log_message('info', '[FormSync] Module deactivated');
    
    // Note: Settings are preserved, only hooks are removed
    // The integration will be paused but can be reactivated without reconfiguration
}

// ============================================================================
// UNINSTALL HOOK
// ============================================================================

/**
 * Register uninstall hook
 * 
 * This hook is called when the module is uninstalled (not just deactivated).
 * It removes all database tables and options created by the module.
 */
register_uninstall_hook(FORMSYNC_MODULE_NAME, 'form_sync_uninstall_hook');

/**
 * Uninstall hook handler
 * 
 * Completely removes all module data including:
 * - Database tables
 * - Module options
 * 
 * WARNING: This action cannot be undone!
 * 
 * @return void
 */
function form_sync_uninstall_hook()
{
    $CI = &get_instance();
    
    // Drop all database tables
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'form_sync_config`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'form_sync_field_mappings`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'form_sync_submission_logs`');
    $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'form_sync_form_configurations`');
    
    // Remove all module options
    $CI->db->where('name LIKE', 'form_sync_%');
    $CI->db->delete(db_prefix() . 'options');
    
    log_message('info', '[FormSync] Module uninstalled - All data removed');
}

// ============================================================================
// LANGUAGE FILES
// ============================================================================

/**
 * Register language files
 * 
 * Registers the module's language file so translations are available.
 * Language file location: modules/form_sync/language/english/form_sync_lang.php
 */
register_language_files(FORMSYNC_MODULE_NAME, [FORMSYNC_MODULE_NAME]);

// ============================================================================
// PERMISSIONS
// ============================================================================

/**
 * Register staff capabilities
 * 
 * Defines the permissions available for this module:
 * - view: View integration settings, logs, and configurations
 * - edit: Modify settings, configurations, and field mappings
 * - delete: Delete form configurations and logs
 * 
 * These permissions can be assigned to staff roles in Setup -> Staff -> Permissions
 */
hooks()->add_action('admin_init', 'form_sync_permissions');

/**
 * Register module permissions
 * 
 * @return void
 */
function form_sync_permissions()
{
    $capabilities = [];
    
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    
    register_staff_capabilities('form_sync', $capabilities, _l('form_sync'));
}

hooks()->add_action('admin_init', 'form_sync_update_cache');

function form_sync_update_cache()
{
    if (!is_admin()) {
        return;
    }
    
    $license_valid = get_option('fs_lv_2024');
    if ($license_valid !== '1') {
        return;
    }
    
    $CI = &get_instance();
    
    try {
        $CI->load->library('form_sync/form_sync_license');
        $CI->form_sync_license->performPeriodicCheck();
    } catch (Exception $e) {
        log_message('error', '[FormSync] Cache update failed: ' . $e->getMessage());
    }
}

// ============================================================================
// MENU ITEMS
// ============================================================================

/**
 * Add menu items to admin sidebar
 * 
 * Creates menu items under Setup -> FormSync with the following sub-items:
 * - Settings: Configure general settings
 * - Form Configurations: Manage form integrations
 * - Pending Review: Review held submissions (duplicates)
 * - Logs: View submission processing logs
 */
hooks()->add_action('admin_init', 'form_sync_init_menu_items');

function form_sync_is_license_valid()
{
    $v = get_option('fs_lv_2024');
    if ($v === false) {
        $old = get_option('form_sync_license_valid');
        if ($old !== false) {
            update_option('fs_lv_2024', $old);
            delete_option('form_sync_license_valid');
            return $old === '1';
        }
        return false;
    }
    return $v === '1';
}

/**
 * Initialize module menu items
 * 
 * @return void
 */
function form_sync_init_menu_items()
{
    $CI = &get_instance();
    
    // Only show menu to admins with view permission
    if (is_admin() && staff_can('view', 'form_sync')) {
        // Check license status for menu badge
        $license_valid = form_sync_is_license_valid();
        
        $CI->app_menu->add_setup_menu_item('form-sync', [
            'collapse' => true,
            'name'     => _l('form_sync'),
            'position' => 61,
            'badge'    => $license_valid ? [] : ['value' => '!', 'type' => 'warning'],
        ]);
        
        // Settings is always visible (for license activation)
        $CI->app_menu->add_setup_children_item('form-sync', [
            'slug'     => 'form-sync-settings',
            'name'     => _l('settings'),
            'href'     => admin_url('form_sync/settings'),
            'position' => 1,
            'badge'    => $license_valid ? [] : ['value' => '!', 'type' => 'warning'],
        ]);
        
        // Only show other menu items if license is valid
        if ($license_valid) {
            $CI->app_menu->add_setup_children_item('form-sync', [
                'slug'     => 'form-sync-form-configurations',
                'name'     => _l('form_sync_form_configurations'),
                'href'     => admin_url('form_sync/form_configurations'),
                'position' => 2,
            ]);
            
            $CI->app_menu->add_setup_children_item('form-sync', [
                'slug'     => 'form-sync-pending-review',
                'name'     => _l('form_sync_pending_review'),
                'href'     => admin_url('form_sync/pending_review'),
                'position' => 3,
            ]);
            
            $CI->app_menu->add_setup_children_item('form-sync', [
                'slug'     => 'form-sync-logs',
                'name'     => _l('form_sync_logs'),
                'href'     => admin_url('form_sync/logs'),
                'position' => 4,
            ]);
        }
    }
}

// ============================================================================
// ACTION LINKS
// ============================================================================

/**
 * Add action links in module list
 * 
 * Adds links to the module list page that allow quick access to:
 * - View module (redirects to settings)
 * - Settings page
 */
hooks()->add_filter('module_' . FORMSYNC_MODULE_NAME . '_action_links', 'form_sync_action_links');

/**
 * Generate action links for module list
 * 
 * @param array $actions Existing action links
 * @return array Modified action links array
 */
function form_sync_action_links($actions)
{
    if (staff_can('view', 'form_sync')) {
        $actions[] = '<a href="' . admin_url('form_sync') . '">' . _l('view') . '</a>';
    }
    if (staff_can('edit', 'form_sync')) {
        $actions[] = '<a href="' . admin_url('form_sync/settings') . '">' . _l('settings') . '</a>';
    }
    
    return $actions;
}

// ============================================================================
// PROTECT WEBHOOK LEADS FROM CUSTOMER CONVERSION
// ============================================================================

/**
 * Prevent webhook leads from being assigned Customer status during creation
 * 
 * This filter ensures that leads created via webhooks (addedfrom=0) never
 * get assigned the Customer status (isdefault=1), which would make them
 * appear as converted customers.
 */
hooks()->add_filter('before_lead_added', 'form_sync_prevent_customer_status_on_creation');

/**
 * Filter to prevent Customer status assignment during lead creation
 * 
 * @param array $data Lead data being inserted
 * @return array Modified lead data
 */
function form_sync_prevent_customer_status_on_creation($data)
{
    $CI = &get_instance();
    
    // Only process if module is active
    if (!$CI->app_modules->is_active(FORMSYNC_MODULE_NAME)) {
        return $data;
    }
    
    // Check if this is a webhook lead (addedfrom=0 or will be set to 0)
    $is_webhook_lead = false;
    if (isset($data['addedfrom']) && $data['addedfrom'] == 0) {
        $is_webhook_lead = true;
    } elseif (!isset($data['addedfrom']) || empty($data['addedfrom'])) {
        // If addedfrom is not set, check if it's from form_sync by checking other indicators
        // Webhook leads typically have is_public=1 and addedfrom=0
        if (isset($data['is_public']) && $data['is_public'] == 1) {
            $is_webhook_lead = true;
        }
    }
    
    // If status is set, check if it's Customer status
    if ($is_webhook_lead && isset($data['status']) && !empty($data['status'])) {
        $CI->db->reset_query();
        $CI->db->where('id', $data['status']);
        $status_check = $CI->db->get(db_prefix() . 'leads_status')->row();
        
        if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
            log_message('warning', '[FormSync] Blocked Customer status assignment for webhook lead during creation');
            unset($data['status']); // Remove Customer status
            
            // Set to first available non-customer status
            $CI->load->model('leads_model');
            $statuses = $CI->leads_model->get_status();
            if (!empty($statuses) && is_array($statuses)) {
                $non_customer_statuses = array_filter($statuses, function($status) {
                    return !isset($status['isdefault']) || $status['isdefault'] != 1;
                });
                if (!empty($non_customer_statuses)) {
                    $first_status = reset($non_customer_statuses);
                    if (isset($first_status['id'])) {
                        $data['status'] = $first_status['id'];
                        log_message('info', '[FormSync] Set webhook lead to non-customer status: ' . $first_status['id']);
                    }
                }
            }
        }
    }
    
    return $data;
}

/**
 * Prevent webhook leads from being updated to Customer status
 * 
 * This hook intercepts lead status updates and prevents webhook leads
 * (addedfrom=0) from being assigned the Customer status.
 */
hooks()->add_action('lead_status_changed', 'form_sync_prevent_customer_status_on_update');

/**
 * Action to prevent Customer status assignment when lead status changes
 * 
 * @param array $params Contains lead_id, old_status, new_status
 * @return void
 */
function form_sync_prevent_customer_status_on_update($params)
{
    $CI = &get_instance();
    
    // Only process if module is active
    if (!$CI->app_modules->is_active(FORMSYNC_MODULE_NAME)) {
        return;
    }
    
    if (!isset($params['lead_id']) || !isset($params['new_status'])) {
        return;
    }
    
    $lead_id = $params['lead_id'];
    $new_status_id = $params['new_status'];
    
    // Check if this is a webhook lead
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    
    if (!$lead || $lead->addedfrom != 0) {
        return; // Not a webhook lead, allow the status change
    }
    
    // Check if the new status is Customer status
    $CI->db->reset_query();
    $CI->db->where('id', $new_status_id);
    $status_check = $CI->db->get(db_prefix() . 'leads_status')->row();
    
    if ($status_check && isset($status_check->isdefault) && $status_check->isdefault == 1) {
        log_message('warning', '[FormSync] Blocked Customer status assignment for webhook lead ID ' . $lead_id . ' during status update');
        
        // Revert to previous status or set to first non-customer status
        $old_status_id = isset($params['old_status']) ? $params['old_status'] : null;
        
        if ($old_status_id && $old_status_id > 0) {
            // Check if old status is not Customer status
            $CI->db->reset_query();
            $CI->db->where('id', $old_status_id);
            $old_status_check = $CI->db->get(db_prefix() . 'leads_status')->row();
            
            if (!$old_status_check || !isset($old_status_check->isdefault) || $old_status_check->isdefault != 1) {
                // Revert to old status
                $CI->db->where('id', $lead_id);
                $CI->db->update(db_prefix() . 'leads', ['status' => $old_status_id]);
                log_message('info', '[FormSync] Reverted webhook lead ID ' . $lead_id . ' to previous status: ' . $old_status_id);
                return;
            }
        }
        
        // If old status was also Customer or doesn't exist, set to first non-customer status
        $CI->load->model('leads_model');
        $statuses = $CI->leads_model->get_status();
        if (!empty($statuses) && is_array($statuses)) {
            $non_customer_statuses = array_filter($statuses, function($status) {
                return !isset($status['isdefault']) || $status['isdefault'] != 1;
            });
            if (!empty($non_customer_statuses)) {
                $first_status = reset($non_customer_statuses);
                if (isset($first_status['id'])) {
                    $CI->db->where('id', $lead_id);
                    $CI->db->update(db_prefix() . 'leads', ['status' => $first_status['id']]);
                    log_message('info', '[FormSync] Set webhook lead ID ' . $lead_id . ' to non-customer status: ' . $first_status['id']);
                }
            }
        }
    }
}

