<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Poly Utilities Global Hooks
 * This file is loaded early in the bootstrap process to ensure
 * view overrides work across all routes
 */

if (!function_exists('poly_utilities_is_custom_menu_enabled')) {
    $menu_helper = FCPATH . 'modules/poly_utilities/helpers/poly_utilities_menu_sync_helper.php';
    if (file_exists($menu_helper)) {
        require_once $menu_helper;
    }
}

/**
 * Override core view files with module views for unlimited menu support
 * Use post_controller_constructor to ensure CI instance is available
 */
$hook['post_controller_constructor'] = array(
    'class'    => '',
    'function' => 'poly_utilities_override_views',
    'filename' => '',
    'filepath' => ''
);

/**
 * Global function to override specific view files
 * Uses Perfex CRM's built-in 'app_view_data' filter hook
 * This allows selective view override without breaking other views
 */
function poly_utilities_override_views()
{
    if (!function_exists('poly_utilities_is_custom_menu_enabled') || !poly_utilities_is_custom_menu_enabled()) {
        return;
    }

    // Add filter to intercept view loading
    hooks()->add_filter('app_view_data', 'poly_utilities_intercept_view_path', 1);
}

/**
 * Intercept and override specific view paths
 * @param array $data ['data' => variables, 'path' => view path]
 * @return array Modified data with new path if override exists
 */
function poly_utilities_intercept_view_path($data)
{
    if (!function_exists('poly_utilities_is_custom_menu_enabled') || !poly_utilities_is_custom_menu_enabled()) {
        return $data;
    }

    $view_path = $data['path'];
    $module_base = FCPATH . 'modules/poly_utilities/views/';
    
    // List of view files to override for unlimited menu support
    $override_views = [
        'admin/includes/aside',
        'admin/includes/setup_menu',
        'themes/perfex/template_parts/navigation',
        'admin/projects/project', // Override project form to add task template field
    ];
    
    // Check if this view should be overridden
    foreach ($override_views as $override_view) {
        // Match both with and without .php extension
        $clean_view = str_replace('.php', '', $view_path);
        $clean_override = str_replace('.php', '', $override_view);
        
        if ($clean_view === $clean_override || $view_path === $override_view) {
            // Check if override file exists
            $override_file = $module_base . $override_view . '.php';
            
            if (file_exists($override_file)) {
                // Use module view instead
                $data['path'] = $override_view;
                
                // Need to temporarily add package path for this view to load
                $CI =& get_instance();
                if ($CI && isset($CI->load)) {
                    $CI->load->add_package_path(FCPATH . 'modules/poly_utilities/', TRUE);
                }
                
                break;
            }
        }
    }
    
    return $data;
}

/**
 * Handle _prev_url exceptions for specified routes
 * Override post_controller hook to add custom skip patterns
 * This prevents certain URLs from being stored in session as "previous URL"
 */
$hook['post_controller'] = function () {
    $ci = get_instance();

    if (!$ci->input->is_ajax_request()) {
        $currentUrl = current_full_url();

        // STRATEGY 1: Clear _prev_url for authentication/login pages
        // This prevents redirect to wrong page after language change from login screen
        $clear_prev_url = [
            'authentication/login',
            'authentication/admin',
            'admin/authentication',
        ];
        
        foreach ($clear_prev_url as $pattern) {
            if (strpos($currentUrl, $pattern) !== false) {
                // Clear previous URL when on login pages
                $ci->session->unset_userdata('_prev_url');
                return; // Don't store this URL
            }
        }

        // STRATEGY 2: Clear _prev_url if it contains widgets routes
        // This prevents redirect to widgets/pixel after form submit
        // Needed because widgets routes should never be used for redirect
        $clear_prev_url_widgets = [
            'widgets/token',
            'widgets/pixel',
            'widgets',
        ];
        
        $prevUrl = $ci->session->userdata('_prev_url');
        if ($prevUrl) {
            foreach ($clear_prev_url_widgets as $pattern) {
                if (strpos($prevUrl, $pattern) !== false) {
                    // Clear previous URL if it contains widgets routes
                    $ci->session->unset_userdata('_prev_url');
                    break;
                }
            }
        }

        // STRATEGY 3: Skip patterns (don't remember these URLs)
        $skip = [
            'pusher_auth', // Prchat issue
            'download/preview_image',
            'download/preview_video',
            'download/file',
            'change_language', // All language change URLs
            'authentication/change_language',
            'clients/change_language',
            'staff/change_language',
            'admin/poly_utilities/get_project_name_patterns',
            // Widgets routes - should never be stored
            'widgets/token',
            'widgets/pixel',
            'widgets',
        ];

        $remember = true;

        foreach ($skip as $haystack) {
            if (strpos($currentUrl, $haystack) !== false) {
                $remember = false;
                break;
            }
        }

        if ($remember) {
            get_instance()->session->set_userdata('_prev_url', $currentUrl);
        }
    }
};

/**
 * Fix change_language redirect issue for STAFF area
 * Hook into before_staff_change_language to fix _prev_url before redirect
 */
if (!function_exists('poly_utilities_fix_staff_change_language_redirect')) {
    function poly_utilities_fix_staff_change_language_redirect($lang) {
        $CI = &get_instance();
        
        // Clear _prev_url completely
        // Let controller use $_SERVER['HTTP_REFERER'] instead
        $CI->session->unset_userdata('_prev_url');
    }
}

/**
 * Fix clients/authentication change_language redirect issue
 * Hook into app_init to check and fix _prev_url before controller redirect
 */
if (!function_exists('poly_utilities_fix_clients_change_language_redirect')) {
    function poly_utilities_fix_clients_change_language_redirect() {
        $CI = &get_instance();
        
        // Only run for change_language requests
        $uri = $CI->uri->uri_string();
        if (strpos($uri, 'authentication/change_language') === false && 
            strpos($uri, 'clients/change_language') === false) {
            return;
        }
        
        // Clear _prev_url completely
        // Let controller use $_SERVER['HTTP_REFERER'] instead
        $CI->session->unset_userdata('_prev_url');
    }
}

// Add hooks
hooks()->add_action('before_staff_change_language', 'poly_utilities_fix_staff_change_language_redirect');
hooks()->add_action('app_init', 'poly_utilities_fix_clients_change_language_redirect');
