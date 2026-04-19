<?php

defined('BASEPATH') or exit('No direct script access allowed');

class POLYCUSTOMMENU
{
    const SIDEBAR = 'sidebar';
    const SETUP = 'setup';
    const CLIENTS = 'clients';
}

/**
 * Helper function to convert the menu structure from a hierarchical tree into a flat array.
 *
 * @param array $menu_items The original list of menu items in a hierarchical structure.
 * @return array The list of menu items flattened into a single level array.
 */
function poly_flatten_menu_items($menu_items)
{
    $flat_menu = [];

    foreach ($menu_items as $item) {
        $flat_menu[] = $item;
        if (isset($item['children']) && !empty($item['children'])) {
            $children_flat = poly_flatten_menu_items($item['children']);
            $flat_menu = array_merge($flat_menu, $children_flat);
            unset($item['children']);
        }
    }

    return $flat_menu;
}

/**
 * Generate a unique menu slug/ID
 * Uses 'i' prefix with uniqid() for consistency across the system
 * 
 * @return string Unique menu slug (e.g., 'i67890abcdef')
 */
function poly_generate_menu_slug()
{
    return 'i' . uniqid();
}

function poly_create_menu_item($custom_link)
{
    $id = poly_generate_menu_slug(); //slug

    $menu_item = $custom_link;

    /*if (isset($menu_item['icon']) && strpos($menu_item['icon'], 'svg') !== false) {
        $menu_item['svg'] = $menu_item['icon'];
        $menu_item['icon'] = 'menu-icon';
    }*/

    $menu_item['id'] = $id;
    $menu_item['slug'] = $id;
    $menu_item['disabled'] = 'true';/* Display is default */
    $menu_item['position'] = 0;

    return $menu_item;
}

function poly_utilities_is_user_access_module($user_id)
{
    $data = json_decode(poly_utilities_user_helper::get_users_access_modules(), true);
    if (!isset($data['users_access']) || !is_array($data['users_access'])) { // Default is accept all
        return true;
    }

    foreach ($data['users_access'] as $user) {
        if (isset($user['id']) && (int)$user['id'] === (int)$user_id) {
            return true;
        }
    }

    return false;
}

function poly_utilities_is_user_access_custom_menu($user_id)
{
    $data = json_decode(poly_utilities_user_helper::get_users_access_modules(), true);
    
    // If no users_custom_menu setting exists, return null to indicate "not configured"
    if (!isset($data['users_custom_menu']) || !is_array($data['users_custom_menu'])) {
        return null;
    }
    
    // If users_custom_menu is empty array, deny all (restricted access)
    if (empty($data['users_custom_menu'])) {
        return false;
    }

    // Check if user is in the allowed list
    foreach ($data['users_custom_menu'] as $user) {
        if (isset($user['id']) && (int)$user['id'] === (int)$user_id) {
            return true;
        }
    }

    return false;
}

function poly_custom_create_menu_item_array($item)
{
    $href = poly_utilities_normalize_url($item['href']);
    if (isset($item['type']) && $item['type']) {

        if (!isset($item['href_attributes'])) {
            $item['href_attributes'] = [];
        }

        if (!isset($item['href_attributes']['class'])) {
            $item['href_attributes']['class'] = '';
        }

        if (!isset($item['href_attributes']['popup'])) {
            $item['href_attributes']['popup'] = '';
        }

        switch ($item['type']) {
            case 'default': { // Accept empty
                    $href = $item['href'];
                    break;
                }
            case 'none':
                $href = '#';
                break;
            case 'iframe':
                $href = site_url('article/' . $item['slug']);
                $item['href_original'] = $item['href'];
                break;
            case 'popup':
                $href = '#';
                $class = $item['href_attributes']['class'];
                $arr_class = explode(' ', $class);
                array_push($arr_class, 'poly-menu-popup');
                $item['href_attributes']['class'] = implode(' ', $arr_class);
                $item['href_attributes']['popup'] = $item['slug'];
                break;
            default:
                $href = $item['href'];
                break;
        }
    }
    $item['href'] = $href;
    $menu_item = $item;
    $menu_item['href_attributes'] = [
        "target" => isset($item['target']) ? $item['target'] : '',
        "rel" => isset($item['rel']) ? $item['rel'] : ''
    ];

    if (isset($item['href_attributes']['data-children'])) {
        $menu_item['href_attributes']['data-children'] = $item['href_attributes']['data-children'] ?? '[]';
    }

    return $menu_item;
}

/**
 * Convert database menu format to system format
 * 
 * OPTIMIZED WITH CACHING:
 * - Caches conversion results per request to avoid repeated processing
 * - Translation remains dynamic (language keys are translated on render via _l())
 * 
 * @param array $db_menus Menus from database
 * @return array Menus in system format
 */
function poly_utilities_convert_db_to_system_format($db_menus)
{
    static $convert_cache = [];
    
    if (empty($db_menus)) {
        return [];
    }
    
    // Create cache key from menu data (excluding dynamic translation)
    // Note: We cache structure, not translated text - translation happens dynamically
    $cache_key = md5(serialize($db_menus));
    
    // Check cache first
    if (isset($convert_cache[$cache_key])) {
        return $convert_cache[$cache_key];
    }
    
    $converted = [];
    
    foreach ($db_menus as $menu) {
        // Handle SVG logic like original code
        $icon = $menu['icon'] ?? '';
        $svg = $menu['svg'] ?? '';
        
        // If icon contains SVG, move it to svg field and set icon to default
        if (isset($icon) && strpos($icon, 'svg') !== false) {
            $svg = $icon;
            $icon = 'menu-icon';
        }
        
        $item = [
            'id' => $menu['slug'] ?? poly_generate_menu_slug(),
            'slug' => $menu['slug'] ?? poly_generate_menu_slug(),
            'name' => $menu['name'] ?? 'Untitled',
            'href' => $menu['href'] ?? '',
            'icon' => $icon,
            'svg' => $svg,
            'type' => $menu['type'] ?? 'default',
            'target' => $menu['target'] ?? '',
            'rel' => $menu['rel'] ?? '',
            'css' => $menu['css'] ?? '',
            'position' => isset($menu['position']) ? (int)$menu['position'] : 0,
            'level' => isset($menu['level']) ? (int)$menu['level'] : 1,
            'disabled' => isset($menu['disabled']) && $menu['disabled'] ? 'false' : 'true',
            'is_custom' => isset($menu['is_custom']) && $menu['is_custom'] ? 'true' : 'false',
            'require_login' => isset($menu['require_login']) && $menu['require_login'] ? 'on' : '',
            'href_original' => $menu['href_original'] ?? '',
            'parent_slug' => 'root',
            'href_attributes' => []
        ];
        
        // Badge
        $item['badge'] = [
            'value' => $menu['badge_value'] ?? '',
            'color' => $menu['badge_color'] ?? ''
        ];
        
        // Popup description
        $item['popup_description'] = [];
        if (!empty($menu['popup_description'])) {
            $decoded = is_string($menu['popup_description']) 
                ? json_decode($menu['popup_description'], true) 
                : $menu['popup_description'];
            $item['popup_description'] = $decoded ?? [];
        }
        
        // Option settings - Parse JSON and merge into item (flexible object value)
        // Also keep the original option_settings field for frontend to re-parse if needed
        $item['option_settings'] = $menu['option_settings'] ?? null; // Keep original
        
        if (!empty($menu['option_settings'])) {
            try {
                if (is_string($menu['option_settings'])) {
                    $settings = @json_decode($menu['option_settings'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
                        foreach ($settings as $key => $value) {
                            $item[$key] = $value; // Merge into item for easy access
                        }
                    }
                } elseif (is_array($menu['option_settings'])) {
                    foreach ($menu['option_settings'] as $key => $value) {
                        $item[$key] = $value;
                    }
                }
            } catch (Exception $e) {
                // If decode fails, ignore
            }
        }
        
        // Permissions - Get from direct columns (roles, users, clients) not from permissions table
        // Model already parsed JSON to array, so we pass arrays directly (json_encode will handle them)
        $item['roles'] = !empty($menu['roles']) && is_array($menu['roles']) ? $menu['roles'] : [];
        $item['users'] = !empty($menu['users']) && is_array($menu['users']) ? $menu['users'] : [];
        $item['clients'] = !empty($menu['clients']) && is_array($menu['clients']) ? $menu['clients'] : [];
        
        // Children
        if (!empty($menu['children']) && is_array($menu['children'])) {
            $item['children'] = poly_utilities_convert_db_to_system_format($menu['children']);
            foreach ($item['children'] as &$child) {
                $child['parent_slug'] = $item['slug'];
            }
            unset($child);
            
            // Apply SVG logic to children like original code
            foreach ($item['children'] as &$child) {
                if (isset($child['icon']) && strpos($child['icon'], 'svg') !== false) {
                    $child['svg'] = $child['icon'];
                    $child['icon'] = 'menu-icon';
                }
                
                // Handle sub-children (level 3)
                if (!empty($child['children']) && is_array($child['children'])) {
                    foreach ($child['children'] as &$sub_child) {
                        if (isset($sub_child['icon']) && strpos($sub_child['icon'], 'svg') !== false) {
                            $sub_child['svg'] = $sub_child['icon'];
                            $sub_child['icon'] = 'menu-icon';
                        }
                    }
                    unset($sub_child);
                }
            }
            unset($child);
        } else {
            $item['children'] = [];
        }
        
        $converted[] = $item;
    }
    
    // Cache the result (structure only, translation happens dynamically)
    $convert_cache[$cache_key] = $converted;
    
    return $converted;
}

/**
 * Get setup menu items from database with fallback to options
 */
function app_admin_poly_custom_setup_menu_items($items, $exclude_disabled = false)
{
    // Check if user has access to PolyUtilities module
    // If users_access is configured and user is not in the list, return system menus only
    if (!staff_can_poly_utilities()) {
        return $items; // Return only system menus, exclude all custom menus
    }
    
    $CI = &get_instance();
    
    // Check if table exists (prevent errors during deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return $items; // Return system menus only if table doesn't exist
    }
    
    $CI->load->model('poly_utilities/custom_menu_model');
    
    // Get custom menus from database
    $custom_menu_items = $CI->custom_menu_model->get_menus('setup', true, true);
    
    // Fallback to options if database is empty
    if (empty($custom_menu_items)) {
        $custom_menu_items = poly_utilities_custom_menu_items_from_options('setup');
    }
    
    // Convert to format expected by system
    $custom_menu_items = poly_utilities_convert_db_to_system_format($custom_menu_items);
    
    if (empty($custom_menu_items)) {
        return $items;
    }
    
    // Merge with system menu items
    $menu_items_arranged = poly_utilities_custom_sidebar_menu_items_pre_render($items, $custom_menu_items);

    // Process and filter
    $rest_menu_items = poly_utilities_custom_sidebar_menu_defined($menu_items_arranged, $custom_menu_items, $exclude_disabled);
    
    return $rest_menu_items;
}

/**
 * Get sidebar menu items from database with fallback to options
 */
function app_admin_poly_custom_sidebar_menu_items($items, $exclude_disabled = false)
{
    // Check if user has access to PolyUtilities module
    // If users_access is configured and user is not in the list, return system menus only
    if (!staff_can_poly_utilities()) {
        return $items; // Return only system menus, exclude all custom menus
    }
    
    $CI = &get_instance();
    
    // Check if table exists (prevent errors during deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        return $items; // Return system menus only if table doesn't exist
    }
    
    $CI->load->model('poly_utilities/custom_menu_model');
    
    // Get custom menus from database
    $custom_menu_items = $CI->custom_menu_model->get_menus('sidebar', true, true);
    
    // Fallback to options if database is empty
    if (empty($custom_menu_items)) {
        $custom_menu_items = poly_utilities_custom_menu_items_from_options('sidebar');
    }
    
    // Convert to format expected by system
    $custom_menu_items = poly_utilities_convert_db_to_system_format($custom_menu_items);
    
    if (empty($custom_menu_items)) {
        return $items;
    }
    
    // Merge with system menu items
    $menu_items_arranged = poly_utilities_custom_sidebar_menu_items_pre_render($items, $custom_menu_items);

    // Process and filter
    $rest_menu_items = poly_utilities_custom_sidebar_menu_defined($menu_items_arranged, $custom_menu_items, $exclude_disabled);
    
    return $rest_menu_items;
}
/**
 * Get clients menu items from database with fallback to options
 */
function app_admin_poly_custom_clients_menu_items()
{
    $CI = &get_instance();
    
    // Check if table exists (prevent errors during deactivation)
    if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
        // Use default theme menu items
        if (function_exists('add_default_theme_menu_items')) {
            add_default_theme_menu_items();
        }
        return;
    }
    
    $CI->load->model('poly_utilities/custom_menu_model');
    
    // Get custom menus from database
    $custom_clients_menu_items = $CI->custom_menu_model->get_menus('clients', true, true);
    
    // Fallback to options if database is empty
    if (empty($custom_clients_menu_items)) {
        $custom_clients_menu_items = poly_utilities_custom_menu_items_from_options('clients');
    }
    
    // Convert to format expected by system
    $custom_clients_menu_items = poly_utilities_convert_db_to_system_format($custom_clients_menu_items);
    
    if (empty($custom_clients_menu_items)) {
        return;
    }
    $current_client_id = get_client_user_id();

    // First: remove menu items that the current client does not have permission to access.
    if (is_client_logged_in()) {
        $flat_menu_items = poly_flatten_menu_items($custom_clients_menu_items);
        poly_process_menu_items($flat_menu_items, $custom_clients_menu_items);
    } else {
        // Remove all clients item logged
        poly_remove_menu_items_logged($custom_clients_menu_items);

        // Remove register
        if (get_option('allow_registration') != 1) {
            poly_remove_menu_item_by_slug($custom_clients_menu_items, 'register');
        }
    }

    foreach ($custom_clients_menu_items as $key => &$item) {
        // SVG
        if (isset($item['icon']) && strpos($item['icon'], 'svg') !== false) {
            $item['svg'] = $item['icon'];
            $item['icon'] = 'menu-icon';
        }

        // $item['children'] level 2
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child) {
                if (isset($child['icon']) && strpos($child['icon'], 'svg') !== false) {
                    $child['svg'] = $child['icon'];
                    $child['icon'] = 'menu-icon';
                }

                // $child['children'] level 3
                if (isset($child['children']) && is_array($child['children'])) {
                    foreach ($child['children'] as &$sub_child) {
                        if (isset($sub_child['icon']) && strpos($sub_child['icon'], 'svg') !== false) {
                            $sub_child['svg'] = $sub_child['icon'];
                            $sub_child['icon'] = 'menu-icon';
                        }
                    }
                    unset($sub_child);
                }
            }
            unset($child);
        }

        // Submenu level 3
        if (!empty($item['children'])) {
            $item['href_attributes']['data-children'] = htmlspecialchars(json_encode($item['children']), ENT_QUOTES, 'UTF-8');
        }

        if (is_client_logged_in() && in_array($item['slug'], ['register', 'login'])) {
            continue;
        }

        if (is_admin() || !isset($item['require_login']) || $item['require_login'] !== 'on') {
            $menu_item = poly_custom_create_menu_item_array($item);
            add_theme_menu_item($menu_item['slug'], $menu_item);
            continue;
        }

        if ($item['require_login'] === 'on' && !is_client_logged_in()) {
            continue;
        }

        if (!empty($item['clients']) && $item['clients'] != '[]') {
            $clients = poly_utilities_common_helper::json_decode($item['clients'], true);
            $client_can_access = poly_utilities_common_helper::get_item_by($clients, 'id', $current_client_id);

            if (!$client_can_access) {
                continue;
            }
        }

        // Remove: 'knowledge-base', 'register', 'login'
        if (in_array($item['slug'], ['knowledge-base', 'register', 'login'])) {
            continue;
        }

        $menu_item = poly_custom_create_menu_item_array($item);
        add_theme_menu_item($menu_item['slug'], $menu_item);
    }

    unset($item);
}

/**
 * Render the main sidebar list with custom attributes, categorizing menus: iframe, popup, blank,...
 * @param array $items System menu items
 * @param array $custom_menu_items Custom menu items (array or constant name for backward compatibility)
 */
function poly_utilities_custom_sidebar_menu_items_pre_render($items, $custom_menu_items)
{
    $menu_items_merged = $items;
    
    // Handle backward compatibility: if constant name passed, get from options
    if (is_string($custom_menu_items) && defined($custom_menu_items)) {
        $menu_items_custom = get_option($custom_menu_items);
        if (!empty($menu_items_custom)) {
            $tmp = poly_utilities_init_custom_sidebar_menu_items($menu_items_custom);
            $menu_items_merged = array_merge($items, $tmp);
        }
    } else {
        // New way: receive array directly
        if (!empty($custom_menu_items) && is_array($custom_menu_items)) {
            $tmp = poly_utilities_init_custom_sidebar_menu_items($custom_menu_items);
            $menu_items_merged = array_merge($items, $tmp);
        }
    }
    
    $menu_items_merged = poly_utilities_init_custom_sidebar_menu_items($menu_items_merged);

    foreach ($menu_items_merged as $key => &$value) {
        if (!isset($value['position'])) {
            $value['position'] = 0;
        }
    }
    unset($value);

    usort($menu_items_merged, function ($a, $b) {
        return $a['position'] <=> $b['position'];
    });

    // Reset root position
    $maxPositionParent = 0;
    foreach ($menu_items_merged as $key => &$value) {
        $value['position'] = $maxPositionParent++;
    }
    unset($value);

    foreach ($menu_items_merged as $key => &$value) {
        if (!empty($value['children']) && is_array($value['children'])) {
            $positions = array_column($value['children'], 'position');
            $maxPosition = (!empty($positions)) ? max($positions) : 0;

            foreach ($value['children'] as &$children_item) {
                if (!isset($children_item['position'])) {
                    $maxPosition++;
                    $children_item['position'] = $maxPosition;

                    // Level 3
                    if (isset($children_item['children']) && is_array($children_item['children'])) {
                        $positions_level3 = array_column($children_item['children'], 'position');
                        $maxPositionLevel3 = (!empty($positions_level3)) ? max($positions_level3) : 0;
                        foreach ($children_item['children'] as &$children_item3) {
                            if (!isset($children_item3['position'])) {
                                $maxPositionLevel3++;
                                $children_item3['position'] = $maxPositionLevel3;
                            }
                        }
                        unset($children_item3);

                        usort($children_item['children'], function ($a, $b) {
                            return $a['position'] <=> $b['position'];
                        });
                    }
                    // Level 3
                }
            }
            unset($children_item);

            usort($value['children'], function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });
        }
    }
    return $menu_items_merged;
}

/**
 * Function to rearrange the order of menu items based on parent_slug and children.
 * @param array $custom_menu_items List of menus to be sorted.
 * @return array Array of sorted menu items.
 */
function poly_utilities_init_custom_sidebar_menu_items($custom_menu_items)
{
    $menu_items = $custom_menu_items;
    if (is_string($custom_menu_items)) {
        $menu_items = json_decode($custom_menu_items, true);
    }
    $result = [];
    $temp = [];
    foreach ($menu_items as &$item) {
        $temp[$item['slug']] = $item + ['children' => []];
    }
    unset($item);

    foreach ($temp as $key => &$itm) {
        if (!empty($itm['parent_slug']) && isset($temp[$itm['parent_slug']])) {
            $temp[$itm['parent_slug']]['children'][] = &$itm;
        } else {
            $result[$itm['slug']] = &$itm;
        }
    }
    unset($itm);
    return $result;
}

/**
 * Searches for a menu item by its slug within a list of menu items.
 * 
 * @param array $custom_menu_items An array of menu item objects, where each item is expected
 * to be an associative array with at least a 'slug' key.
 * @param string $menu_item_slug The slug string to search for within the 'slug' attribute of each menu item array.
 * 
 * @return mixed Returns the found menu item array if a match is found, or null if no match is found.
 */
function poly_utilities_find_menu_item_by_slug($custom_menu_items, $menu_item_slug, $is_object = false)
{
    foreach ($custom_menu_items as $item) {
        if ($item['slug'] === $menu_item_slug) {
            return $is_object ? $item : true;
        }
        if (isset($item['children']) && is_array($item['children'])) {
            if (poly_utilities_find_menu_item_by_slug($item['children'], $menu_item_slug)) {
                return $is_object ? $item : true;
            }
        }
    }
    return $is_object ? null : false;
}

/**
 * Reorders the full menu list to maintain the custom sort order of the custom menu.
 * @param array &$custom_menu_items The custom sorted menu list. This array may be modified to include items from $menu_items that are not present.
 * @param array $menu_items The full list of menu items, including those in $custom_menu_items but not sorted.
 */
function poly_utilities_menu_sidebar_merged(&$custom_menu_items, $menu_items)
{
    //TODO: $item exist in $menu_items but not in $custom_menu_items => add it
    foreach ($menu_items as &$item) {
        $current_object = poly_utilities_find_menu_item_by_slug($custom_menu_items, $item['slug']);
        if (!$current_object) {
            $custom_menu_items[] = $item;
        }
    }
    unset($item);

    //TODO: $item exist in $custom_menu_items but not in $menu_item => remove it
    $menu_items_mapped = [];
    poly_utilities_map_slug_arr_sidebar_menu($menu_items, $menu_items_mapped);
    poly_utilities_menu_sidebar_merged_mapped($custom_menu_items, $menu_items_mapped);

    // Add update menu items: synchronize the menu items of the core modules with changes in the item list.
    poly_utilities_synchronize_menu_items($menu_items_mapped, $custom_menu_items);
}

/**
 * Synchronizes menu items by ensuring the custom menu items array includes all relevant child items from the mapped menu items while avoiding duplicates.
 *
 * @param array $menuItemsMapped An associative array where keys are slugs of menu items, and values are menu item data, including potential child items.
 * @param array &$customMenuItems An array of custom menu items to synchronize. This array will be updated to include missing child items from the mapped menu items, ensuring consistency.
 */
function poly_utilities_synchronize_menu_items(&$menuItemsMapped, &$customMenuItems)
{
    foreach ($customMenuItems as &$customItem) {
        // Check if the current custom item exists in the mapped menu items
        if (isset($menuItemsMapped[$customItem['slug']])) {
            $mappedItem = $menuItemsMapped[$customItem['slug']];

            // Check if the mapped item has children
            if (!empty($mappedItem['children']) && is_array($mappedItem['children'])) {
                // Ensure the custom item has a 'children' array initialized
                if (!isset($customItem['children']) || !is_array($customItem['children'])) {
                    $customItem['children'] = [];
                }

                $childrenUpdated = false;
                // Iterate through each child of the mapped item
                foreach ($mappedItem['children'] as $mappedChild) {
                    // Check if the child already exists in customMenuItems
                    if (!does_item_exist_in_custom_menu($customMenuItems, $mappedChild['slug'])) {
                        $customItem['children'][] = $mappedChild;
                        $childrenUpdated = true;
                    }
                }

                // If children were updated, sort them based on their 'position' property
                if ($childrenUpdated) {
                    usort($customItem['children'], function ($a, $b) {
                        return intval($a['position']) <=> intval($b['position']);
                    });
                }

                // Recursively synchronize child menu items
                poly_utilities_synchronize_menu_items($menuItemsMapped, $customItem['children']);
            }
        }
    }
}

/**
 * Check if an item with a given slug exists in the custom menu items or any of their children.
 *
 * @param array $customMenuItems The array of menu items to search through. Each item is an associative array
 *                               that may contain a 'slug' key and optionally a 'children' key (an array of child items).
 * @param string $slug The slug of the item to search for in the menu items.
 *
 * @return bool Returns true if the item with the given slug exists in the menu items or any of their children, false otherwise.
 */
function does_item_exist_in_custom_menu($customMenuItems, $slug)
{
    foreach ($customMenuItems as $item) {
        // Check if the current item matches the given slug
        if ($item['slug'] === $slug) {
            return true;
        }
        // Recursively check children if they exist
        if (!empty($item['children']) && is_array($item['children'])) {
            if (does_item_exist_in_custom_menu($item['children'], $slug)) {
                return true;
            }
        }
    }
    // Return false if no match is found
    return false;
}

/**
 * Function to remove all custom elements in $custom_menu_items if they do not exist in the main menu $menu_items (mapped by slug).
 */
function poly_utilities_menu_sidebar_merged_mapped(&$custom_menu_items, $menu_items_mapped)
{
    foreach ($custom_menu_items as $key => &$custom_item) {
        $exists = false;
        foreach ($menu_items_mapped as $item) {
            if ($item['slug'] === $custom_item['slug']) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            unset($custom_menu_items[$key]);
        } else {
            if (isset($custom_item['children']) && is_array($custom_item['children'])) {
                poly_utilities_menu_sidebar_merged_mapped($custom_item['children'], $menu_items_mapped);
            }
        }
    }
}

/**
 * Check permission to view the custom menu feature
 */
function staff_can_poly_utilities_custom_menu()
{
    $staff_id = get_staff_user_id();

    if ($staff_id == 1) {
        return true;
    }

    // Check if user is in the allowed list for custom menu
    $has_custom_menu_access = poly_utilities_is_user_access_custom_menu($staff_id);
    
    // If users_custom_menu is configured (not null)
    if ($has_custom_menu_access !== null) {
        // If user is not in the users_custom_menu list, deny access
        if ($has_custom_menu_access === false) {
            return false;
        }
        
        // User is in users_custom_menu list
        // Now check if user also has users_access (module access)
        $has_module_access = poly_utilities_is_user_access_module($staff_id);
        
        // User must be in BOTH lists to access custom menu
        if ($has_module_access === false) {
            return false; // User has custom menu access but not module access - DENIED
        }
        
        // User is in both lists, grant access
        return true;
    }
    
    // If users_custom_menu is not configured, fallback to permission check
    if (!staff_can('view', 'poly_utilities_custom_menu_extend')) {
        return false;
    }

    return true;
}


/**
 * Check permission to view the polyutilities feature
 * Uses 'users_access' setting to restrict access to module features (except custom menu)
 */
function staff_can_poly_utilities()
{
    $staff_id = get_staff_user_id();
    
    // Super admin always has access
    if ($staff_id == 1) {
        return true;
    }

    // Check if user is in the allowed list for module access
    $has_access = poly_utilities_is_user_access_module($staff_id);
    
    // users_access setting logic:
    // - If not configured: return true (allow all, fallback to normal permissions)
    // - If configured with users: only users in list can access
    // - Note: This is different from users_custom_menu which handles custom menu separately
    
    return $has_access;
}

/**
 * Prevents access to a specific custom menu by removing it from the $menu_items list.
 * The function iterates over $menu_items, checking for a specific 'slug'. If the 'slug' matches
 * the predefined value and the staff does not have the 'view' permission for this menu, it is removed.
 * It also recursively checks and applies the same logic to any children menus.
 *
 * @param array &$menu_items An array representing the list of menu items. Each item is an associative array that may include 'slug' and 'children' keys.
 */
function poly_utilities_denie_access_custom_menu(&$menu_items)
{
    foreach ($menu_items as $key => &$item) {
        if (isset($item['slug']) && $item['slug'] === 'poly_utilities_custom_menu_extend' && !staff_can('view', $item['slug'])) {
            unset($menu_items[$key]);
            continue;
        }

        if (array_key_exists('children', $item) && is_array($item['children'])) {
            poly_utilities_denie_access_custom_menu($item['children']);
        }
    }
    unset($item);
    $menu_items = array_values($menu_items);
}

/**
 * Sorts the main menu items $menu_items according to the order specified in the list of custom menu items.
 * This function adjusts the order of $menu_items based on their positions in the $custom_menu_items list,
 * ensuring that the final order of menu items reflects the custom order defined.
 *
 * @param array $menu_items An array of the main menu items. Each item in this array is expected to be
 * an associative array that could represent a menu item.
 * @param array $custom_menu_items An array of custom menu items specifying the desired order. Each item
 * in this array should correspond to or be identifiable with items in $menu_items, dictating the order
 * the items in $menu_items should be arranged in.
 */
function poly_utilities_custom_sidebar_menu_defined($menu_items, $custom_menu_items, $exclude_disabled = false)
{
    if ($custom_menu_items != null) {
        foreach ($custom_menu_items as $key => &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!array_key_exists('children', $item)) {
                $item['children'] = [];
            }
            if (!empty($item['children'])) {
                poly_utilities_denie_access_custom_menu($item['children']);
            }
        }
        unset($item);

        $menu_sidebar_slug_map_items = [];

        poly_utilities_map_slug_arr_sidebar_menu($menu_items, $menu_sidebar_slug_map_items);
        poly_utilities_menu_sidebar_language($custom_menu_items, $menu_sidebar_slug_map_items);

        poly_utilities_menu_sidebar_merged($custom_menu_items, $menu_items);

        $menu_items = $custom_menu_items;
    }

    poly_utilities_menu_sidebar_define_by_type($menu_items);

    //ROLES & Permissions
    // Skip permission filtering if flag is set (for admin management page)
    if (!defined('POLY_SKIP_PERMISSION_FILTER') || !POLY_SKIP_PERMISSION_FILTER) {
        $staff_id = get_staff_user_id();
        poly_utilities_menu_sidebar_users_access($menu_items, $staff_id, $exclude_disabled);
    }
    //ROLES & Permissions

    return $menu_items;
}

/**
 * Same as poly_utilities_custom_sidebar_menu_defined but WITHOUT permission filtering
 * Used for admin management page to show all menu items
 */
function poly_utilities_custom_sidebar_menu_items_defined_no_permission($menu_items, $custom_menu_items, $exclude_disabled = false)
{
    if ($custom_menu_items != null) {
        foreach ($custom_menu_items as $key => &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!array_key_exists('children', $item)) {
                $item['children'] = [];
            }
            if (!empty($item['children'])) {
                poly_utilities_denie_access_custom_menu($item['children']);
            }
        }
        unset($item);

        $menu_sidebar_slug_map_items = [];

        poly_utilities_map_slug_arr_sidebar_menu($menu_items, $menu_sidebar_slug_map_items);
        poly_utilities_menu_sidebar_language($custom_menu_items, $menu_sidebar_slug_map_items);

        poly_utilities_menu_sidebar_merged($custom_menu_items, $menu_items);

        $menu_items = $custom_menu_items;
    }

    poly_utilities_menu_sidebar_define_by_type($menu_items);

    // NO PERMISSION FILTERING - Just apply disabled state
    foreach ($menu_items as $key => &$item) {
        if (!isset($item['disabled'])) {
            $item['disabled'] = 'true'; //Display all;
        }

        if (isset($item['icon']) && strpos($item['icon'], 'svg') !== false) {
            $item['svg'] = $item['icon'];
            $item['icon'] = 'menu-icon';
        }

        if ($item['disabled'] && $item['disabled'] === 'false') {
            // Menu is disabled (hidden)
            $arr_class = [];
            if (isset($item['href_attributes']['class'])) {
                $class = $item['href_attributes']['class'];
                $arr_class = explode(' ', $class);
            }
            array_push($arr_class, 'poly-remove-menu-items');
            array_push($arr_class, 'hide');
            $item['href_attributes']['class'] = implode(' ', $arr_class);

            $item['name'] = '<span class="poly-hidehide hide">' . $item['name'] . '</span>';
        } else {
            // Menu is enabled (visible) - remove hide classes if they exist
            if (isset($item['href_attributes']['class'])) {
                $class = $item['href_attributes']['class'];
                $arr_class = explode(' ', $class);
                // Remove hide classes
                $arr_class = array_filter($arr_class, function($c) {
                    return !in_array($c, ['poly-remove-menu-items', 'hide']);
                });
                $item['href_attributes']['class'] = implode(' ', $arr_class);
            }
            
            // Remove hide wrapper from name if exists
            if (isset($item['name']) && strpos($item['name'], 'poly-hidehide') !== false) {
                $item['name'] = strip_tags($item['name']);
            }
        }
        
        // Process children recursively
        if (!empty($item['children'])) {
            poly_utilities_custom_sidebar_menu_items_defined_no_permission($item['children'], $custom_menu_items, $exclude_disabled);
        }
    }
    unset($item);

    return $menu_items;
}

function poly_utilities_menu_sidebar_users_access(&$menu_items, $staff_id, $exclude_disabled = false)
{
    foreach ($menu_items as $key => &$item) {

        if (!isset($item['disabled'])) {
            $item['disabled'] = 'true'; //Display all;
        }

        if (isset($item['icon']) && strpos($item['icon'], 'svg') !== false) {
            $item['svg'] = $item['icon'];
            $item['icon'] = 'menu-icon';
        }

        if ($item['disabled'] && $item['disabled'] === 'false') {
            // Menu is disabled (hidden)
            $arr_class = [];
            if (isset($item['href_attributes']['class'])) {
                $class = $item['href_attributes']['class'];
                $arr_class = explode(' ', $class);
            }
            array_push($arr_class, 'poly-remove-menu-items');
            array_push($arr_class, 'hide');
            $item['href_attributes']['class'] = implode(' ', $arr_class);

            $item['name'] = '<span class="poly-hidehide hide">' . $item['name'] . '</span>';
        } else {
            // Menu is enabled (visible) - remove hide classes if they exist
            if (isset($item['href_attributes']['class'])) {
                $class = $item['href_attributes']['class'];
                $arr_class = explode(' ', $class);
                // Remove hide classes
                $arr_class = array_filter($arr_class, function($c) {
                    return !in_array($c, ['poly-remove-menu-items', 'hide']);
                });
                $item['href_attributes']['class'] = implode(' ', $arr_class);
            }
            
            // Remove hide wrapper from name if exists
            if (isset($item['name']) && strpos($item['name'], 'poly-hidehide') !== false) {
                $item['name'] = strip_tags($item['name']);
            }
        }
        //Disabled

        //Badge
        if (isset($item['is_custom']) && $item['is_custom']) {
            if ($item['is_custom'] === 'true') {
                $color = '#fff';
                $height = '1px';
                if (isset($item['badge']) && empty($item['badge']['value']) && ((isset($item['type']) && $item['type'] !== 'divider'))) {
                    $item['badge']['value'] = '';
                    $item['badge']['color'] = 'transparent';
                } else {
                    if (isset($item['badge']['value'])) {
                        $item['badge']['value'] = $item['badge']['value'];
                        $height = !empty($item['badge']['value']) ? $item['badge']['value'] : $height;
                        $color = empty($item['badge']['color']) ? 'transparent' : $item['badge']['color'];
                    }
                }

                if (isset($item['type']) && $item['type'] === 'divider') {
                    if (isset($item['icon'])) {
                        $item['icon'] = '';
                    }
                    $item['name'] = '<span class=\'poly-dividivi hide\' title=\'background-color:' . $color . ';height:' . $height . '\'>' . $item['name'] . '</span>';
                }
            }
        }
        //Badge

        $access = poly_utilities_is_access_menu_item($item, $staff_id);

        if (!$access) {
            unset($menu_items[$key]);
        } elseif (!empty($item['children'])) {
            // Apply SVG logic to children like original code
            foreach ($item['children'] as &$child) {
                if (isset($child['icon']) && strpos($child['icon'], 'svg') !== false) {
                    $child['svg'] = $child['icon'];
                    $child['icon'] = 'menu-icon';
                }
                
                // Handle sub-children (level 3)
                if (!empty($child['children']) && is_array($child['children'])) {
                    foreach ($child['children'] as &$sub_child) {
                        if (isset($sub_child['icon']) && strpos($sub_child['icon'], 'svg') !== false) {
                            $sub_child['svg'] = $sub_child['icon'];
                            $sub_child['icon'] = 'menu-icon';
                        }
                    }
                    unset($sub_child);
                }
            }
            unset($child);
            
            poly_utilities_menu_sidebar_users_access($item['children'], $staff_id, $exclude_disabled);
        }
    }
    unset($item);
}

/**
 * Determines if a user has access to a menu item based on assigned users and roles.
 *
 * @param array $item The menu item containing 'users' and 'roles' as JSON strings.
 * @param int $staff_id The ID of the staff member whose access is being checked.
 * @return bool Returns true if the staff member has access, otherwise false.
 */
function poly_utilities_is_access_menu_item($item, $staff_id)
{
    // Super admin (ID = 1) always has access
    if ($staff_id == 1) {
        return true;
    }
    
    // Check if user is in users_custom_menu list
    // If yes, bypass all menu item restrictions (Display to specific roles/users)
    if (poly_utilities_is_user_access_custom_menu($staff_id)) {
        return true;
    }
    
    $users = isset($item['users']) ? poly_utilities_common_helper::json_decode($item['users'], true) : [];
    $roles_access = isset($item['roles']) ? poly_utilities_common_helper::json_decode($item['roles'], true) : [];

    // If both users and roles are empty, allow all users
    if ((is_array($users) && empty($users)) && (is_array($roles_access) && empty($roles_access))) {
        return true;
    }

    // If users list is specified, check if user is in the list
    if (!empty($users) && is_array($users)) {
        if (poly_utilities_common_helper::get_item_by($users, 'id', $staff_id)) {
            return true; // User is in the list
        }
        // User is not in the list, deny access
        return false;
    }

    // If roles list is specified, check if user's role is in the list
    if (!empty($roles_access) && is_array($roles_access)) {
        $role_by_staffid = poly_utilities_user_helper::get_user_role($staff_id);
        if ($role_by_staffid !== null) {
            $roleid_by_user = $role_by_staffid->role;
            if (poly_utilities_common_helper::get_item_by($roles_access, 'id', $roleid_by_user)) {
                return true; // User's role is in the list
            }
        }
        // User's role is not in the list, deny access
        return false;
    }

    return false;
}

/**
 * Handle custom item by type: none, iframe, popup,...
 */
function poly_utilities_menu_sidebar_define_by_type(&$finally_sidebar_menu_items)
{
    foreach ($finally_sidebar_menu_items as $key => &$item) {
        $href = poly_utilities_normalize_url($item['href']);
        if (isset($item['type']) && $item['type']) {

            if (!isset($item['href_attributes'])) {
                $item['href_attributes'] = [];
            }

            if (!isset($item['href_attributes']['class'])) {
                $item['href_attributes']['class'] = '';
            }

            if (!isset($item['href_attributes']['popup'])) {
                $item['href_attributes']['popup'] = '';
            }

            switch ($item['type']) {
                case 'none':
                    $href = '#';
                    break;
                case 'iframe':
                    $href = admin_url('poly_utilities/details/' . $item['slug']);
                    $item['href_original'] = $item['href'];

                    break;
                case 'popup':
                    $href = '#';
                    $class = $item['href_attributes']['class'];
                    $arr_class = explode(' ', $class);
                    array_push($arr_class, 'poly-menu-popup');
                    $item['href_attributes']['class'] = implode(' ', $arr_class);
                    $item['href_attributes']['popup'] = $item['slug'];

                    // data-popup: JSON string (HTML-encoded) of popup_description after shortcodes
                    if (isset($item['popup_description']) && $item['popup_description'] !== '') {
                        $popupDesc = $item['popup_description'];

                        // 1) Decode HTML entities first (because content is stored encoded)
                        if (is_string($popupDesc)) {
                            $popupDesc = htmlspecialchars_decode($popupDesc, ENT_QUOTES);
                        }

                        // 2) Apply Demo Builder shortcodes if available
                        if (class_exists('poly_utilities_common_helper')) {
                            $popupDesc = poly_utilities_common_helper::apply_demobuilder_shortcodes($popupDesc);
                        }

                        // 3) Encode back for safe JSON-in-attribute usage
                        $item['href_attributes']['data-popup'] = htmlspecialchars(
                            json_encode($popupDesc, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    } else {
                        $item['href_attributes']['data-popup'] = '';
                    }

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
                    $item['href_attributes']['data-popup-size'] = $popup_size;

                    break;
                case 'divider':
                    $href = '#';
                    $class = $item['href_attributes']['class'];
                    $arr_class = explode(' ', $class);
                    array_push($arr_class, 'poly-menu-divider');
                    $item['href_attributes']['class'] = implode(' ', $arr_class);
                    break;
                default:
                    $href = $item['href'];
                    break;
            }

            $item['href'] = $href;

            if (isset($item['target'])) {
                $item['href_attributes']['target'] = $item['target'];
            }
            if (isset($item['rel'])) {
                $item['href_attributes']['rel'] = $item['rel'];
            }
            if (isset($item['data-type'])) {
                $item['href_attributes']['data-type'] = $item['type'];
            }
        }

        // Check if children exists and is an array before processing
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child_item) {
            // Apply SVG logic to children like original code
            if (isset($child_item['icon']) && strpos($child_item['icon'], 'svg') !== false) {
                $child_item['svg'] = $child_item['icon'];
                $child_item['icon'] = 'menu-icon';
            }
            
            $child_href = poly_utilities_normalize_url($child_item['href']);
            if (isset($child_item['type']) && $child_item['type']) {

                if (!isset($child_item['href_attributes'])) {
                    $child_item['href_attributes'] = [];
                }

                if (!isset($child_item['href_attributes']['class'])) {
                    $child_item['href_attributes']['class'] = '';
                }

                if (!isset($child_item['href_attributes']['popup'])) {
                    $child_item['href_attributes']['popup'] = '';
                }

                switch ($child_item['type']) {
                    case 'none':
                        $child_href = '#';
                        break;
                    case 'iframe':
                        $child_href = admin_url('poly_utilities/details/' . $child_item['slug']);
                        $child_item['href_original'] = $child_item['href'];
                        break;
                    case 'popup': // Display popup
                        $child_href = '#';
                        $class = $child_item['href_attributes']['class'];
                        $arr_class = explode(' ', $class);
                        array_push($arr_class, 'poly-menu-popup');
                        $child_item['href_attributes']['class'] = implode(' ', $arr_class);
                        $child_item['href_attributes']['popup'] = $child_item['slug'];

                        if (isset($child_item['popup_description']) && $child_item['popup_description'] !== '') {
                            $popupDesc = $child_item['popup_description'];

                            if (is_string($popupDesc)) {
                                $popupDesc = htmlspecialchars_decode($popupDesc, ENT_QUOTES);
                            }

                            if (class_exists('poly_utilities_common_helper')) {
                                $popupDesc = poly_utilities_common_helper::apply_demobuilder_shortcodes($popupDesc);
                            }

                            $child_item['href_attributes']['data-popup'] = htmlspecialchars(
                                json_encode($popupDesc, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                        } else {
                            $child_item['href_attributes']['data-popup'] = '';
                        }

                        // Parse option_settings and get popup_size
                        $popup_size = 'medium'; // Default
                        if (!empty($child_item['option_settings'])) {
                            $option_settings = $child_item['option_settings'];
                            // Parse JSON if it's a string
                            if (is_string($option_settings)) {
                                $option_settings = json_decode($option_settings, true);
                            }
                            // Get popup_size from option_settings
                            if (is_array($option_settings) && isset($option_settings['popup_size'])) {
                                $popup_size = $option_settings['popup_size'];
                            }
                        }
                        $child_item['href_attributes']['data-popup-size'] = $popup_size;

                        break;
                    case 'divider':
                        $child_href = '#';
                        $class = $child_item['href_attributes']['class'];
                        $arr_class = explode(' ', $class);
                        array_push($arr_class, 'poly-menu-divider');
                        $child_item['href_attributes']['class'] = implode(' ', $arr_class);
                        break;
                    default:
                        $child_href = $child_item['href'];
                        break;
                }
            }
            if (($child_item['href'] == '') && (isset($child_item['type']) && $child_item['type'] != 'popup') && ((isset($item['type']) && $item['type'] !== 'divider'))) {
                $child_item['href_attributes']['class'] = 'hide';
            }

            $child_item['href'] = $child_href;

            // Submenu level 3
            if (!empty($child_item['children'])) {

                foreach ($child_item['children'] as &$child_item_sub) {
                    // Apply SVG logic to sub-children like original code
                    if (isset($child_item_sub['icon']) && strpos($child_item_sub['icon'], 'svg') !== false) {
                        $child_item_sub['svg'] = $child_item_sub['icon'];
                        $child_item_sub['icon'] = 'menu-icon';
                    }

                    if (!isset($child_item_sub['href_attributes'])) {
                        $child_item_sub['href_attributes'] = [];
                    }

                    if (!isset($child_item_sub['href_attributes']['class'])) {
                        $child_item_sub['href_attributes']['class'] = '';
                    }

                    if (!isset($child_item_sub['href_attributes']['popup'])) {
                        $child_item_sub['href_attributes']['popup'] = '';
                    }

                    if (isset($child_item_sub['type']) && $child_item_sub['type']) {
                        switch ($child_item_sub['type']) {
                            case 'popup': // Display popup
                                $child_sub_href = '#';
                                $class = (isset($child_item_sub['href_attributes']) && $child_item_sub['href_attributes']['class']) ? $child_item_sub['href_attributes']['class'] : '';
                                $arr_class = explode(' ', $class);
                                array_push($arr_class, 'poly-menu-popup');
                                $child_item_sub['href_attributes']['class'] = implode(' ', $arr_class);
                                $child_item_sub['href_attributes']['popup'] = $child_item_sub['slug'];

                                if (isset($child_item_sub['popup_description']) && $child_item_sub['popup_description'] !== '') {
                                    $popupDesc = $child_item_sub['popup_description'];

                                    if (is_string($popupDesc)) {
                                        $popupDesc = htmlspecialchars_decode($popupDesc, ENT_QUOTES);
                                    }

                                    if (class_exists('poly_utilities_common_helper')) {
                                        $popupDesc = poly_utilities_common_helper::apply_demobuilder_shortcodes($popupDesc);
                                    }

                                    $child_item_sub['href_attributes']['data-popup'] = htmlspecialchars(
                                        json_encode($popupDesc, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                } else {
                                    $child_item_sub['href_attributes']['data-popup'] = '';
                                }

                                // Parse option_settings and get popup_size
                                $popup_size = 'medium'; // Default
                                if (!empty($child_item_sub['option_settings'])) {
                                    $option_settings = $child_item_sub['option_settings'];
                                    // Parse JSON if it's a string
                                    if (is_string($option_settings)) {
                                        $option_settings = json_decode($option_settings, true);
                                    }
                                    // Get popup_size from option_settings
                                    if (is_array($option_settings) && isset($option_settings['popup_size'])) {
                                        $popup_size = $option_settings['popup_size'];
                                    }
                                }
                                $child_item_sub['href_attributes']['data-popup-size'] = $popup_size;

                                $child_item_sub['href'] = $child_sub_href;
                                break;
                        }
                    }
                }
                unset($child_item_sub);

                $child_item['href_attributes']['data-children'] = htmlspecialchars(json_encode($child_item['children']), ENT_QUOTES, 'UTF-8');
            }
            // Submenu level 3

            if (isset($child_item['target'])) {
                $child_item['href_attributes']['target'] = $child_item['target'];
            }
            if (isset($child_item['rel'])) {
                $child_item['href_attributes']['rel'] = $child_item['rel'];
            }
            if (isset($child_item['type'])) {
                $child_item['href_attributes']['data-type'] = $child_item['type'];
            }
        }
        unset($child_item);
        } // End if (isset($item['children']) && is_array($item['children']))
    }
    unset($item);
}

/**
 * Updates the sidebar menu items by merging updates for a specific item. 
 * If child items are being processed, the item is removed and re-appended under its parent.
 * If the item or its parent is not found, the item is added to the top-level menu.
 * 
 * @param array &$menu_items      Reference to the array of menu items to be updated.
 * @param array $menu_item_update The updated menu item data, including the 'id' and 'parent_slug' to identify the item.
 * @param bool  $isChildrenProcess Flag to indicate whether to process child items. Default is false.
 */
function poly_utilities_menu_sidebar_update(&$menu_items, $menu_item_update, $isChildrenProcess = false)
{
    if (!$isChildrenProcess) {
        $found = false;
        foreach ($menu_items as &$item) {
            if (isset($item['id']) && $item['id'] === $menu_item_update['id']) {
                $item = array_merge($item, $menu_item_update);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $menu_items[] = $menu_item_update;
        }
    } else {
        $found_item_result = poly_remove_item_recursive($menu_items, $menu_item_update);
        if ($found_item_result !== null) {
            if ($found_item_result['status'] === 'updated') {
                return;
            } elseif ($found_item_result['status'] === 'removed') {
                $found_item = array_merge($found_item_result['data'], $menu_item_update);
                $parent_found = poly_find_and_append($menu_items, $found_item);

                if (!$parent_found) {
                    $menu_items[] = $found_item;
                }
            }
        } else {
            if (isset($menu_item_update['parent_slug'])) {
                $parent_found = poly_find_and_append($menu_items, $menu_item_update);
                if (!$parent_found) {
                    $menu_items[] = $menu_item_update;
                }
            } else {
                $menu_items[] = $menu_item_update;
            }
        }
    }
}

/**
 * Recursively removes a menu item by its ID from the menu items array.
 * If the item's parent_slug matches, it updates the item instead of removing it.
 * 
 * @param array  &$menu_items        Reference to the array of menu items.
 * @param object $menu_item_update   The menu item object containing the 'id' and 'parent_slug' to identify the item.
 * 
 * @return array|null                The removed or updated menu item if found, or null if not found.
 */
function poly_remove_item_recursive(&$menu_items, $menu_item_update)
{
    foreach ($menu_items as $index => &$item) {
        if (isset($item['id']) && $item['id'] === $menu_item_update['id']) {
            if (isset($item['parent_slug']) && $item['parent_slug'] === $menu_item_update['parent_slug']) {
                $item = array_merge($item, $menu_item_update);
                return [
                    'status' => 'updated',
                    'data' => $item
                ];
            } else {
                $removed_item = $item;
                unset($menu_items[$index]);
                return [
                    'status' => 'removed',
                    'data' => $removed_item
                ];
            }
        }

        if (isset($item['children']) && !empty($item['children'])) {
            $removed_item = poly_remove_item_recursive($item['children'], $menu_item_update);
            if ($removed_item !== null) {
                return $removed_item;
            }
        }
    }
    return null;
}

/**
 * Recursively finds the parent item by its slug and appends the child item to it.
 * 
 * @param array &$menu_items      Reference to the array of menu items.
 * @param array $child_item       The child menu item to append, which includes a 'parent_slug' to identify the parent.
 * 
 * @return bool                   True if the parent item was found and the child item was appended, false otherwise.
 */
function poly_find_and_append(&$menu_items, $child_item)
{
    foreach ($menu_items as &$item) {
        if (isset($item['id']) && $item['id'] === $child_item['parent_slug']) {
            if (!isset($item['children'])) {
                $item['children'] = [];
            }
            $item['children'][] = $child_item;
            return true;
        }
        if (isset($item['children']) && !empty($item['children'])) {
            $found_in_children = poly_find_and_append($item['children'], $child_item);
            if ($found_in_children) {
                return true;
            }
        }
    }
    return false;
}

function poly_utilities_normalize_url($url)
{
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl['path']) && $parsedUrl['path']) {
        $path = $parsedUrl['path'];
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        $parts = explode('/', $path);

        $adminKey = array_search('admin', $parts);
        if ($adminKey === false) {
            return $url;
        }
        $afterAdminParts = array_slice($parts, $adminKey + 1);
        $afterAdmin = implode('/', $afterAdminParts) . $query;

        return admin_url($afterAdmin);
    } else {
        return '';
    }
}

/**
 * Handles multilingual display
 */
function poly_utilities_menu_sidebar_language(&$menu_items, $menu_items_map)
{
    foreach ($menu_items as $key => &$item) {

        if (isset($item['slug']) && isset($menu_items_map[$item['slug']])) {
            $item['name'] = $menu_items_map[$item['slug']]['name'];
        } else {
            //Error menu item => remove;
            unset($menu_items[$key]);
            continue;
        }

        if (!empty($item['children'])) {
            poly_utilities_menu_sidebar_language($item['children'], $menu_items_map);
        }
    }
    unset($item);
}

function poly_utilities_map_slug_arr_sidebar_menu($menu_items, &$slugMap)
{
    foreach ($menu_items as $item) {
        $slugMap[$item['slug']] = $item;
        if (!empty($item['children'])) {
            poly_utilities_map_slug_arr_sidebar_menu($item['children'], $slugMap);
        }
    }
}

/**
 * Delete a custom menu item by slug using database
 * @param string $slug Menu slug
 * @param string $menu_type Menu type
 * @return bool Success status
 * @deprecated Use custom_menu_model->delete() instead
 */
function poly_utilities_delete_custom_sidebar_menu_by_id($slug, $menu_type = 'sidebar')
{
    $CI = &get_instance();
    $CI->load->model('poly_utilities/custom_menu_model');
    
    $menu = $CI->custom_menu_model->get_by_slug($slug, $menu_type);
    
    if ($menu) {
        return $CI->custom_menu_model->delete($menu['id']);
    }
    
    return false;
}

/**
 * Get custom menu items from database (flattened)
 * Fallback to options if database is empty
 * @param string $menu_type Menu type: sidebar, setup, clients
 * @return array Flattened menu items keyed by slug
 */
function poly_utilities_custom_menu_items($menu_type = 'sidebar')
{
    static $cache = [];

    if (isset($cache[$menu_type])) {
        return $cache[$menu_type];
    }

    $CI = &get_instance();
    $CI->load->model('poly_utilities/custom_menu_model');
    
    // Load from database first
    $menus = $CI->custom_menu_model->get_menus($menu_type, true, true);
    
    // If database is empty, fallback to options
    if (empty($menus)) {
        $menus = poly_utilities_custom_menu_items_from_options($menu_type);
    }
    
    // Flatten menu structure
    $result = [];
    $flattenMenu = function ($menuItems) use (&$result, &$flattenMenu) {
        foreach ($menuItems as $item) {
            if (isset($item['slug'])) {
                $result[$item['slug']] = $item;
                if (isset($item['children']) && is_array($item['children'])) {
                    $flattenMenu($item['children']);
                }
            }
        }
    };
    $flattenMenu($menus);

    $cache[$menu_type] = $result;

    return $cache[$menu_type];
}

/**
 * Get custom menu items from options (old storage method)
 * @param string $menu_type Menu type: sidebar, setup, clients
 * @return array Menu items from options
 */
function poly_utilities_custom_menu_items_from_options($menu_type = 'sidebar')
{
    $option_keys = [
        'sidebar' => POLY_MENU_SIDEBAR_CUSTOM_ACTIVE,
        'setup' => POLY_MENU_SETUP_CUSTOM_ACTIVE,
        'clients' => POLY_MENU_CLIENTS_CUSTOM_ACTIVE
    ];
    
    $option_key = $option_keys[$menu_type] ?? null;
    if (!$option_key) {
        return [];
    }
    
    $menu_data_json = get_option($option_key);
    if (empty($menu_data_json) || $menu_data_json === '[]') {
        return [];
    }
    
    $menu_items = json_decode($menu_data_json, true);
    return is_array($menu_items) ? $menu_items : [];
}

function poly_utilities_custom_menu_slim($menu_type)
{
    static $slimCache = [];

    if (isset($slimCache[$menu_type])) {
        return $slimCache[$menu_type];
    }

    $menu = poly_utilities_custom_menu_items($menu_type);
    $slim_menu = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'css' => $item['css'] ?? '',
            'icon' => isset($item['svg']) ? htmlspecialchars($item['svg']) : ($item['icon'] ?? '')
        ];
    }, $menu);

    $slimCache[$menu_type] = !empty($slim_menu) ? $slim_menu : [];

    return $slimCache[$menu_type];
}

function poly_get_clients_menu_items()
{
    $CI = &get_instance();
    $menu_items = $CI->app_menu->get_theme_items();
    return $menu_items;
}

/**
 * Clone menu items using database
 * @param array $menu_item Menu item to clone
 * @param string $menu_type Menu type
 * @return int|false New menu ID or false
 * @deprecated Use custom_menu_model->clone_menu() instead
 */
function poly_clone_menu_items($menu_item, $menu_type = 'sidebar')
{
    $CI = &get_instance();
    $CI->load->model('poly_utilities/custom_menu_model');
    
    $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
    
    if (!$slug) {
        return false;
    }
    
    $menu = $CI->custom_menu_model->get_by_slug($slug, $menu_type);
    
    if ($menu) {
        return $CI->custom_menu_model->clone_menu($menu['id'], true);
    }
    
    return false;
}

function poly_remove_menu_item_by_slug(&$menu_items, $slug)
{
    foreach ($menu_items as $key => &$item) {
        if (isset($item['slug']) && $item['slug'] === $slug) {
            unset($menu_items[$key]);
            return true; // Found and deleted
        }
        if (isset($item['children']) && is_array($item['children'])) {
            if (poly_remove_menu_item_by_slug($item['children'], $slug)) {
                // Re-index children array after deletion
                $item['children'] = array_values($item['children']);
                return true; // Found and deleted in children
            }
        }
    }
    unset($item); // Unset reference
    return false; // Not found
}

/**
 * Delete a menu item by slug from menu items array (recursively)
 * Alias for poly_remove_menu_item_by_slug() for consistency
 * 
 * @param array &$menu_items Reference to menu items array
 * @param string $slug Menu slug to delete
 * @return void
 */
function poly_utilities_menu_sidebar_delete(&$menu_items, $slug)
{
    poly_remove_menu_item_by_slug($menu_items, $slug);
}

/**
 * Find menu item index by slug in nested structure
 * 
 * @param array $menu_items Menu items array
 * @param string $slug Menu slug to find
 * @param array $path Path to the item (for nested items)
 * @return array|null Returns ['index' => int, 'parent' => &array, 'path' => array] or null
 */
function poly_find_menu_item_index(&$menu_items, $slug, $path = [])
{
    foreach ($menu_items as $index => &$item) {
        if (isset($item['slug']) && $item['slug'] === $slug) {
            return [
                'index' => $index,
                'parent' => &$menu_items,
                'path' => $path
            ];
        }
        
        // Check children recursively
        if (isset($item['children']) && is_array($item['children'])) {
            $new_path = array_merge($path, [$index]);
            $found = poly_find_menu_item_index($item['children'], $slug, $new_path);
            if ($found !== null) {
                return $found;
            }
        }
    }
    unset($item);
    return null;
}

/**
 * Insert cloned menu item right after the original item
 * 
 * @param array &$menu_items Reference to menu items array
 * @param string $original_slug Slug of original item
 * @param array $cloned_item Cloned item to insert
 * @return bool True if inserted, false if original not found
 */
function poly_insert_cloned_menu_item(&$menu_items, $original_slug, $cloned_item)
{
    $found = poly_find_menu_item_index($menu_items, $original_slug);
    
    if ($found === null) {
        return false; // Original item not found
    }
    
    $parent = &$found['parent'];
    $index = $found['index'];
    
    // Insert cloned item right after original (index + 1)
    // Use array_splice to insert at specific position
    array_splice($parent, $index + 1, 0, [$cloned_item]);
    
    return true;
}

/**
 * This function removes menu items that the current client ID does not have permission to access.
 * 
 * The function processes an array of slugs representing the menu items available to clients and checks if 
 * the logged-in contact has the required permissions to view each menu item. If the client does not have 
 * permission for a specific item, that item is removed from the custom client's menu.
 * 
 * Special condition: For the "subscriptions" menu item, it checks a different permission function 
 * (can_logged_in_contact_view_subscriptions()) to determine access.
 *
 * @param array $flat_menu_items - List of available menu items.
 * @param array $custom_clients_menu_items - Custom client menu items to be modified based on permission checks.
 */
function poly_process_menu_items($flat_menu_items, &$custom_clients_menu_items)
{
    $arr_slug = poly_utilities_common_helper::$clients_menu_items ?? [];
    if (!empty($arr_slug)) {
        foreach ($arr_slug as $slug) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, $slug);
            if ($current_object) {
                // Check permission for 'subscriptions' or general permission for other items
                if (($slug === 'subscriptions' && !can_logged_in_contact_view_subscriptions()) ||
                    (!has_contact_permission($slug))
                ) {
                    poly_remove_menu_item_by_slug($custom_clients_menu_items, $slug);
                }
            }
        }
    }
}

function poly_client_logged_in_can_access()
{
    $arr_slug = poly_utilities_common_helper::$clients_menu_items ?? [];

    if (get_option('allow_registration') !== 1) {
        array_push($arr_slug, 'register');
    }

    $access = [];
    if (!empty($arr_slug)) {
        $access = $arr_slug;
        if (is_client_logged_in()) {
            foreach ($arr_slug as $key => $slug) {
                if (($slug === 'subscriptions' && !can_logged_in_contact_view_subscriptions()) ||
                    (!has_contact_permission($slug))
                ) {
                    unset($arr_slug[$key]);
                }
            }
        }
    }
    return array('can_access' => array_values($arr_slug), 'access' => array_values($access));
}

function poly_remove_menu_items_logged(&$custom_clients_menu_items)
{
    $arr_slug = poly_utilities_common_helper::$clients_menu_items ?? [];
    if (!empty($arr_slug)) {
        foreach ($arr_slug as $slug) {
            poly_remove_menu_item_by_slug($custom_clients_menu_items, $slug);
        }
    }
}

function poly_add_default_menu_items($flat_menu_items, &$menu_items_custom)
{
    $arr_slug = [
        ['slug' => 'projects', 'name' => _l('clients_nav_projects'), 'href' => site_url('clients/projects'), 'position' => 10],
        ['slug' => 'invoices', 'name' => _l('clients_nav_invoices'), 'href' => site_url('clients/invoices'), 'position' => 15],
        ['slug' => 'contracts', 'name' => _l('clients_nav_contracts'), 'href' => site_url('clients/contracts'), 'position' => 20],
        ['slug' => 'estimates', 'name' => _l('clients_nav_estimates'), 'href' => site_url('clients/estimates'), 'position' => 25],
        ['slug' => 'proposals', 'name' => _l('clients_nav_proposals'), 'href' => site_url('clients/proposals'), 'position' => 30],
        ['slug' => 'subscriptions', 'name' => _l('subscriptions'), 'href' => site_url('clients/subscriptions'), 'position' => 40],
        ['slug' => 'support', 'name' => _l('clients_nav_support'), 'href' => site_url('clients/tickets'), 'position' => 45]
    ];

    foreach ($arr_slug as $item) {
        $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, $item['slug']);
        if (!$current_object) {
            array_push($menu_items_custom, [
                'name' => $item['name'],
                'slug' => $item['slug'],
                'href' => $item['href'],
                'position' => $item['position']
            ]);
        }
    }
}

/**
 * Reset the custom menu using database (delete only custom items)
 * @param string $menu Menu type: sidebar, setup, clients, all
 * @param bool $remove_custom Only delete custom menus (true) or all menus (false)
 * @return bool Success status
 */
function poly_reset_custom_menu($menu, $remove_custom = true)
{
    if (empty($menu)) return false;

    $CI = &get_instance();
    $CI->load->model('poly_utilities/custom_menu_model');

    $menu_types = [];
    if ($menu === 'all') {
        $menu_types = ['sidebar', 'setup', 'clients'];
    } elseif (in_array($menu, ['sidebar', 'setup', 'clients'])) {
        $menu_types = [$menu];
    } elseif ($menu === POLYCUSTOMMENU::SIDEBAR) {
        $menu_types = ['sidebar'];
    } elseif ($menu === POLYCUSTOMMENU::SETUP) {
        $menu_types = ['setup'];
    } elseif ($menu === POLYCUSTOMMENU::CLIENTS) {
        $menu_types = ['clients'];
    }

    if (empty($menu_types)) {
        return false;
    }

    $result = true;
    foreach ($menu_types as $menu_type) {
        $success = $CI->custom_menu_model->reset_menus($menu_type, $remove_custom);
        $result = $result && $success;
    }

    return $result;
}

/**
 * Delete all custom menus using database
 * @param string $menu Menu type: sidebar, setup, clients, all
 * @return bool Success status
 */
function poly_delete_custom_menu($menu)
{
    if (empty($menu)) return false;

    $CI = &get_instance();
    $CI->load->model('poly_utilities/custom_menu_model');

    $menu_types = [];
    if ($menu === 'all') {
        $menu_types = ['sidebar', 'setup', 'clients'];
    } elseif (in_array($menu, ['sidebar', 'setup', 'clients'])) {
        $menu_types = [$menu];
    } elseif ($menu === POLYCUSTOMMENU::SIDEBAR) {
        $menu_types = ['sidebar'];
    } elseif ($menu === POLYCUSTOMMENU::SETUP) {
        $menu_types = ['setup'];
    } elseif ($menu === POLYCUSTOMMENU::CLIENTS) {
        $menu_types = ['clients'];
    }

    if (empty($menu_types)) {
        return false;
    }

    $result = true;
    foreach ($menu_types as $menu_type) {
        $success = $CI->custom_menu_model->reset_menus($menu_type, false); // Delete all
        $result = $result && $success;
    }

    return $result;
}
