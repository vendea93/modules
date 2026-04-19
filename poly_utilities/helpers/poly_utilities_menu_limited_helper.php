<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Poly Utilities Menu Helper - Limited Levels Version
 * This helper provides functions to fetch and build menu structures with level limitations
 * 
 * Level Limits:
 * - Sidebar: 3 levels
 * - Setup: 2 levels
 * - Clients: 2 levels
 */

/**
 * Get custom menu items from database with level limitation
 * 
 * @param string $menu_type Menu type: 'sidebar', 'setup', 'clients'
 * @param int $max_level Maximum level to fetch (1-based)
 * @return array Menu items with children
 */
function poly_get_custom_menu_limited($menu_type, $max_level = 3)
{
    $CI =& get_instance();
    $CI->load->database();
    
    // Fetch all menu items for this type, ordered by position
    $CI->db->select('*');
    $CI->db->from(db_prefix() . 'poly_utilities_custom_menus');
    $CI->db->where('menu_type', $menu_type);
    $CI->db->where('disabled', 0); // Only enabled menus
    $CI->db->where('level <=', $max_level); // Level limitation
    $CI->db->order_by('position', 'ASC');
    $CI->db->order_by('id', 'ASC');
    $query = $CI->db->get();
    
    if (!$query || $query->num_rows() === 0) {
        return [];
    }
    
    $items = $query->result_array();
    
    // Build hierarchical structure - pass menu_type to tree builder
    return poly_build_menu_tree($items, null, $max_level, 1, $menu_type);
}

/**
 * Build hierarchical menu tree from flat array
 * 
 * @param array $items Flat array of menu items
 * @param int|null $parent_id Parent ID to filter by
 * @param int $max_level Maximum allowed level
 * @param int $current_level Current recursion level
 * @param string $menu_type Menu type for context (sidebar, setup, clients)
 * @return array Hierarchical menu structure
 */
function poly_build_menu_tree($items, $parent_id = null, $max_level = 3, $current_level = 1, $menu_type = 'sidebar')
{
    $tree = [];
    
    foreach ($items as $item) {
        // Match parent
        $item_parent = $item['parent_id'] ? (int)$item['parent_id'] : null;
        
        if ($item_parent === $parent_id) {
            // Process item
            $node = [
                'id' => $item['id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'href' => $item['href'] ?? '#',
                'icon' => $item['icon'] ?? '',
                'svg' => $item['svg'] ?? '',
                'type' => $item['type'] ?? 'default',
                'target' => $item['target'] ?? '',
                'rel' => $item['rel'] ?? '',
                'css' => $item['css'] ?? '',
                'level' => $current_level,
                'position' => $item['position'] ?? 0,
                'is_custom' => $item['is_custom'] ?? 0,
                'require_login' => $item['require_login'] ?? 0,
                'menu_type' => $menu_type, // Add menu_type to node
                'badge' => null,
                'href_attributes' => [],
                'li_attributes' => [],
                'children' => []
            ];
            
            // Load permissions (users and roles) if available
            if (isset($item['permissions'])) {
                // Convert permissions from model format to flat arrays
                $node['users'] = isset($item['permissions']['users']) && is_array($item['permissions']['users']) 
                    ? json_encode($item['permissions']['users']) 
                    : '[]';
                $node['roles'] = isset($item['permissions']['roles']) && is_array($item['permissions']['roles']) 
                    ? json_encode($item['permissions']['roles']) 
                    : '[]';
            } else {
                $node['users'] = '[]';
                $node['roles'] = '[]';
            }
            
            // Add custom CSS class
            if (!empty($item['css'])) {
                $node['li_attributes']['class'] = $item['css'];
            }
            
            // Add target attribute
            if (!empty($item['target'])) {
                $node['href_attributes']['target'] = $item['target'];
            }
            
            // Add rel attribute
            if (!empty($item['rel'])) {
                $node['href_attributes']['rel'] = $item['rel'];
            }
            
            // Build badge
            if (!empty($item['badge_value'])) {
                $node['badge'] = [
                    'value' => $item['badge_value'],
                    'color' => $item['badge_color'] ?? '',
                    'type' => '' // Perfex uses 'info', 'warning', 'danger', etc.
                ];
            }
            
            // Handle disabled items (old approach)
            if (!empty($item['disabled']) && $item['disabled'] == 1) {
                $existing_class = $node['href_attributes']['class'] ?? '';
                $classes = array_filter(explode(' ', $existing_class));
                $classes[] = 'poly-remove-menu-items';
                $classes[] = 'hide';
                $node['href_attributes']['class'] = implode(' ', $classes);
                $node['name'] = '<span class="poly-hidehide hide">' . $node['name'] . '</span>';
            }
            
            // Handle special link types
            switch ($item['type']) {
                case 'iframe':
                    // For clients menu: use article route
                    // For admin/setup menu: use details route
                    if (isset($item['menu_type']) && $item['menu_type'] === 'clients') {
                        $node['href'] = site_url('article/' . $item['slug']);
                    } else {
                        $node['href'] = admin_url('poly_utilities/details/' . $item['slug']);
                    }
                    $node['href_original'] = $item['href_original'] ?? $item['href'];
                    $node['href_attributes']['data-type'] = 'iframe';
                    break;
                    
                case 'popup':
                    // Use old approach: poly-menu-popup class + data-popup attribute
                    $node['href'] = 'javascript:void(0)'; // ← Use javascript:void(0) to avoid # in URL
                    $existing_class = $node['href_attributes']['class'] ?? '';
                    $classes = array_filter(explode(' ', $existing_class));
                    $classes[] = 'poly-menu-popup';
                    $classes[] = 'poly-prevent-default'; // ← Add class to prevent default click behavior
                    $node['href_attributes']['class'] = implode(' ', $classes);
                    $node['href_attributes']['popup'] = $item['slug'];
                    
                    // Encode popup content as JSON with proper escaping
                    if (!empty($item['popup_description'])) {
                        $popup_data = $item['popup_description'];
                        // If already JSON string, decode first
                        if (is_string($popup_data) && json_decode($popup_data) !== null) {
                            $popup_data = json_decode($popup_data);
                        }
                        $node['href_attributes']['data-popup'] = htmlspecialchars(
                            json_encode($popup_data, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP), 
                            ENT_QUOTES, 
                            'UTF-8'
                        );
                    } else {
                        $node['href_attributes']['data-popup'] = '';
                    }
                    $node['href_attributes']['data-type'] = 'popup';
                    
                    // Parse option_settings and get popup_size
                    $popup_size = 'medium'; // Default
                    if (!empty($item['option_settings'])) {
                        $option_settings = $item['option_settings'];
                        // Parse JSON if it's a string
                        if (is_string($option_settings)) {
                            $option_settings = json_decode($option_settings, true);
                        }
                        // Get popup_size from option_settings
                        if (is_array($option_settings) && isset($option_settings['popup_size'])) {
                            $popup_size = $option_settings['popup_size'];
                        }
                    }
                    $node['href_attributes']['data-popup-size'] = $popup_size;
                    
                    // Remove target and rel for popup type (không cần open tab mới)
                    unset($node['href_attributes']['target']);
                    unset($node['href_attributes']['rel']);
                    break;
                    
                case 'divider':
                    $node['href'] = '#';
                    $existing_class = $node['href_attributes']['class'] ?? '';
                    $classes = array_filter(explode(' ', $existing_class));
                    $classes[] = 'poly-menu-divider';
                    $node['href_attributes']['class'] = implode(' ', $classes);
                    $node['li_attributes']['class'] = trim(($node['li_attributes']['class'] ?? '') . ' menu-divider');
                    $node['href_attributes']['data-type'] = 'divider';
                    
                    // Encode divider style into name (old approach)
                    $color = $item['badge_color'] ?? 'transparent';
                    $height = $item['badge_value'] ?? '1px';
                    $node['name'] = '<span class="poly-dividivi hide" title="background-color:' . $color . ';height:' . $height . '">' . $node['name'] . '</span>';
                    $node['icon'] = ''; // Remove icon for divider
                    break;
                    
                case 'none':
                    $node['href'] = '#';
                    $node['href_attributes']['data-type'] = 'none';
                    break;
                    
                default:
                    // Add data-type for default links too
                    $node['href_attributes']['data-type'] = 'default';
                    break;
            }
            
            // Recursively get children if not at max level
            if ($current_level < $max_level) {
                $node['children'] = poly_build_menu_tree($items, (int)$item['id'], $max_level, $current_level + 1, $menu_type);
            }
            
            $tree[] = $node;
        }
    }
    
    return $tree;
}

/**
 * Merge custom menus with system menus
 * 
 * @param array $system_menus System menu items
 * @param array $custom_menus Custom menu items
 * @return array Merged menu structure
 */
function poly_merge_system_and_custom_menus($system_menus, $custom_menus)
{
    // For now, just append custom menus at the end
    // Can be enhanced to insert at specific positions
    return array_merge($system_menus, $custom_menus);
}

/**
 * Check if staff has permission to view menu item
 * 
 * @param int $menu_id Menu item ID
 * @param int $staff_id Staff user ID
 * @return bool True if has permission
 */
function poly_staff_can_view_menu($menu_id, $staff_id = null)
{
    $CI =& get_instance();
    
    if ($staff_id === null) {
        $staff_id = get_staff_user_id();
    }
    
    // Admin can see everything
    if (is_admin()) {
        return true;
    }
    return true; // Simplified for now
}

/**
 * Get max level for menu type
 * 
 * @param string $menu_type Menu type
 * @return int Max level
 */
function poly_get_max_menu_level($menu_type)
{
    $levels = [
        'sidebar' => 3,
        'setup' => 2,
        'clients' => 2
    ];
    
    return $levels[$menu_type] ?? 2;
}

