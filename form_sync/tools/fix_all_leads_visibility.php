<?php
/**
 * Fix All Leads Visibility
 * 
 * This script fixes visibility for all FormSync-created leads that might have
 * incorrect visibility settings. Run this after approving submissions.
 * 
 * Usage: Access via browser: /admin/form_sync/fix_all_leads_visibility
 * Or via CLI: php -r "require 'index.php'; \$CI =& get_instance(); \$CI->load->model('form_sync_model'); \$CI->form_sync_model->fixAllLeadsVisibility();"
 */

// This should be accessed via the controller, but we can also make it standalone
if (php_sapi_name() === 'cli') {
    define('BASEPATH', true);
    require_once(__DIR__ . '/../../index.php');
    
    $CI = &get_instance();
    $CI->load->database();
    $CI->load->model('form_sync_model');
    
    echo "Fixing all FormSync leads visibility...\n";
    $result = $CI->form_sync_model->fixAllLeadsVisibility();
    echo "Fixed " . $result['fixed'] . " leads\n";
    echo "Already correct: " . $result['correct'] . " leads\n";
} else {
    // Should be accessed via controller
    echo "This script should be accessed via the FormSync controller.";
}











