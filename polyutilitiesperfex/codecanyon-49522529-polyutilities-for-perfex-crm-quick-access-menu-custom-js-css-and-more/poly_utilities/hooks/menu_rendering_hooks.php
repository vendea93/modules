<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Hook to modify sidebar rendering for unlimited levels
 * This injects custom rendering logic WITHOUT modifying core files
 * 
 * @author Poly Utilities
 * @version 1.0
 */

/**
 * Hook to override sidebar menu rendering with unlimited levels support
 */
function poly_render_unlimited_sidebar_menu()
{
    $CI = &get_instance();
    
    // Get sidebar menu (already merged with custom menus)
    $sidebar_menu = $CI->app_menu->get_sidebar_menu_items();
    
    // Start output buffering
    ob_start();
    ?>
    <ul class="nav metis-menu tw-mt-[15px]" id="side-menu">
        <?php hooks()->do_action('before_render_aside_menu'); ?>
        
        <?php foreach ($sidebar_menu as $key => $item) {
            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {
                continue;
            }
            
            // ✨ Use recursive partial view for unlimited levels
            echo $CI->load->view('poly_utilities/partials/menu_item_recursive', [
                'item' => $item,
                'current_level' => 1,
                'menu_type' => 'sidebar'
            ], true);
            
            hooks()->do_action('after_render_single_aside_menu', $item);
        } ?>
    </ul>
    <?php
    
    return ob_get_clean();
}

/**
 * Hook to override setup menu rendering with unlimited levels support
 */
function poly_render_unlimited_setup_menu()
{
    $CI = &get_instance();
    $setup_menu = $CI->app_menu->get_setup_menu_items();
    
    // Start output buffering
    ob_start();
    ?>
    <ul class="nav metis-menu" id="setup-menu">
        <?php hooks()->do_action('before_render_setup_menu'); ?>
        
        <?php foreach ($setup_menu as $key => $item) {
            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {
                continue;
            }
            
            // ✨ Use recursive partial view for unlimited levels
            echo $CI->load->view('poly_utilities/partials/menu_item_recursive', [
                'item' => $item,
                'current_level' => 1,
                'menu_type' => 'setup'
            ], true);
            
            hooks()->do_action('after_render_single_setup_menu', $item);
        } ?>
    </ul>
    <?php
    
    return ob_get_clean();
}

/**
 * Hook to override client menu rendering with unlimited levels support
 */
function poly_render_unlimited_client_menu()
{
    $CI = &get_instance();
    
    // Get client menu (already merged with custom menus)
    $client_menu = $CI->app_menu->get_client_menu_items();
    
    // Start output buffering
    ob_start();
    ?>
    <ul class="nav nav-pills nav-stacked customers-nav">
        <?php hooks()->do_action('before_render_client_menu'); ?>
        
        <?php foreach ($client_menu as $key => $item) {
            if ((isset($item['collapse']) && $item['collapse']) && count($item['children']) === 0) {
                continue;
            }
            
            // ✨ Use recursive partial view for unlimited levels
            echo $CI->load->view('poly_utilities/themes/partials/client_menu_recursive', [
                'item' => $item,
                'current_level' => 1
            ], true);
            
            hooks()->do_action('after_render_single_client_menu', $item);
        } ?>
    </ul>
    <?php
    
    return ob_get_clean();
}

/**
 * Register hooks for unlimited menu rendering
 */
function register_unlimited_menu_hooks()
{
    if (!function_exists('poly_utilities_is_custom_menu_enabled') || !poly_utilities_is_custom_menu_enabled()) {
        return;
    }

    // Hook into sidebar menu rendering
    hooks()->add_filter('sidebar_menu_render', 'poly_render_unlimited_sidebar_menu', 999);
    
    // Hook into setup menu rendering
    hooks()->add_filter('setup_menu_render', 'poly_render_unlimited_setup_menu', 999);
    
    // Hook into client menu rendering
    hooks()->add_filter('client_menu_render', 'poly_render_unlimited_client_menu', 999);
}

/**
 * Initialize unlimited menu hooks
 */
function init_unlimited_menu_hooks()
{
    if (!function_exists('poly_utilities_is_custom_menu_enabled') || !poly_utilities_is_custom_menu_enabled()) {
        return;
    }

    // Register hooks
    register_unlimited_menu_hooks();
    
    // Add CSS and JS assets via hook (delayed loading)
    hooks()->add_action('app_admin_head', 'add_unlimited_menu_assets');
}

/**
 * Add CSS and JS assets for unlimited menu support
 */
function add_unlimited_menu_assets()
{
    if (!function_exists('poly_utilities_is_custom_menu_enabled') || !poly_utilities_is_custom_menu_enabled()) {
        return;
    }

    // Check if we're in admin area
    if (!is_admin()) {
        return;
    }
    
    // Inject CSS
    $css_path = module_dir_url('poly_utilities', 'assets/css/admin/unlimited_menu_levels.css');
    echo '<link href="' . $css_path . '" rel="stylesheet" type="text/css" />';
    
    // Inject JS (add to footer via hook)
    hooks()->add_action('app_admin_footer', function() {
        $js_handler_path = poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/unlimited_menu_handler.js');
        $js_metis_path = poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/metis_menu_unlimited.js');
        
        echo '<script src="' . $js_handler_path . '"></script>';
        echo '<script src="' . $js_metis_path . '"></script>';
    });
}

/**
 * Check if unlimited menu rendering is enabled
 * @return bool
 */
function is_unlimited_menu_enabled()
{
    return get_option('poly_unlimited_menu_enabled', '1') === '1';
}

/**
 * Enable/disable unlimited menu rendering
 * @param bool $enabled
 */
function set_unlimited_menu_enabled($enabled = true)
{
    update_option('poly_unlimited_menu_enabled', $enabled ? '1' : '0');
}

/**
 * Get maximum menu level setting
 * @return int
 */
function get_max_menu_level()
{
    return (int) get_option('poly_max_menu_level', '9');
}

/**
 * Set maximum menu level
 * @param int $level
 */
function set_max_menu_level($level = 9)
{
    update_option('poly_max_menu_level', max(1, min(20, $level)));
}

/**
 * Validate menu level
 * @param int $level
 * @return bool
 */
function is_valid_menu_level($level)
{
    $max_level = get_max_menu_level();
    return $level >= 1 && $level <= $max_level;
}

/**
 * Get menu level CSS class
 * @param int $level
 * @return string
 */
function get_menu_level_class($level)
{
    if (!is_valid_menu_level($level)) {
        return 'menu-level-invalid';
    }
    
    return 'menu-level-' . $level;
}

/**
 * Get menu level indentation
 * @param int $level
 * @return int
 */
function get_menu_level_indentation($level)
{
    if (!is_valid_menu_level($level)) {
        return 15;
    }
    
    return 15 + (($level - 1) * 15);
}

/**
 * Check if menu item has deep children
 * @param array $item
 * @return bool
 */
function has_deep_children($item)
{
    if (empty($item['children'])) {
        return false;
    }
    
    foreach ($item['children'] as $child) {
        if (!empty($child['children'])) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get menu depth level
 * @param array $item
 * @return int
 */
function get_menu_depth($item)
{
    if (empty($item['children'])) {
        return 1;
    }
    
    $max_depth = 1;
    foreach ($item['children'] as $child) {
        $child_depth = get_menu_depth($child);
        $max_depth = max($max_depth, $child_depth + 1);
    }
    
    return $max_depth;
}

/**
 * Flatten menu structure for analysis
 * @param array $menu_items
 * @return array
 */
function flatten_menu_structure($menu_items)
{
    $flattened = [];
    
    foreach ($menu_items as $item) {
        $flattened[] = [
            'slug' => $item['slug'] ?? '',
            'name' => $item['name'] ?? '',
            'level' => $item['level'] ?? 1,
            'depth' => get_menu_depth($item),
            'has_children' => !empty($item['children']),
            'children_count' => count($item['children'] ?? [])
        ];
        
        if (!empty($item['children'])) {
            $flattened = array_merge($flattened, flatten_menu_structure($item['children']));
        }
    }
    
    return $flattened;
}

/**
 * Get menu statistics
 * @param array $menu_items
 * @return array
 */
function get_menu_statistics($menu_items)
{
    $flattened = flatten_menu_structure($menu_items);
    
    $stats = [
        'total_items' => count($flattened),
        'max_level' => 0,
        'max_depth' => 0,
        'items_by_level' => [],
        'deep_items' => 0
    ];
    
    foreach ($flattened as $item) {
        $level = $item['level'];
        $depth = $item['depth'];
        
        $stats['max_level'] = max($stats['max_level'], $level);
        $stats['max_depth'] = max($stats['max_depth'], $depth);
        
        if (!isset($stats['items_by_level'][$level])) {
            $stats['items_by_level'][$level] = 0;
        }
        $stats['items_by_level'][$level]++;
        
        if ($level >= 4) {
            $stats['deep_items']++;
        }
    }
    
    return $stats;
}
