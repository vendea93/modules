<?php
defined('BASEPATH') or exit('No direct script access allowed');

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class Poly_utilities extends AdminController
{
    private $CI;
    private $current_user_id;
    private $feature_custom_menu_hooks = true;
    private $feature_multiple_companies = true;
    private $feature_multiple_addresses = true;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->current_user_id = get_staff_user_id();

        $this->load->model('project_name_patterns_model');
        $this->load->model('poly_utilities/custom_menu_model');

        $this->load->model('currencies_model');
        $this->load->model('projects_model');
        $this->load->model('contracts_model');
        $this->load->model('proposals_model');
        $this->load->model('tasks_model');
        $this->load->model('staff_model');
        $this->load->model('clients_model');

        $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));
        $poly_utilities_settings = $poly_utilities_settings ? json_decode($poly_utilities_settings, true) : [];
        if (!is_array($poly_utilities_settings)) {
            $poly_utilities_settings = [];
        }

        $this->feature_custom_menu_hooks = $this->get_feature_flag($poly_utilities_settings, 'enable_custom_menu_hooks', true);
        $this->feature_multiple_companies = $this->get_feature_flag($poly_utilities_settings, 'enable_multiple_companies', true);
        $this->feature_multiple_addresses = $this->get_feature_flag($poly_utilities_settings, 'enable_multiple_addresses', true);
    }

    /**
     * Check if current user has access to PolyUtilities module
     * Uses 'users_access' setting to restrict access
     */
    private function _check_module_access()
    {
        if (!staff_can_poly_utilities()) {
            access_denied();
        }
    }

    ////////////////////////////////////////////////////////////////////// FUNCTIONS //////////////////////////////////////////////////////////////////////
    /**
     * Scripts
     * @return view
     */
    public function scripts()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_scripts_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('scripts/manage', $data);
    }

    /**
     * Add Scripts
     * @return view
     */ //
    public function scripts_add()
    {
        $this->_check_module_access();
        $data['title'] = (isset($_GET['id'])) ? _l('poly_utilities_scripts_update_extend') : _l('poly_utilities_scripts_add_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('scripts/create', $data);
    }

    /**
     * Styles
     * @return view
     */
    public function styles()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_styles_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('styles/manage', $data);
    }

    /**
     * Add Styles
     * @return view
     */
    public function styles_add()
    {
        $this->_check_module_access();
        $data['title'] = (isset($_GET['id'])) ? _l('poly_utilities_styles_update_extend') : _l('poly_utilities_styles_add_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('styles/create', $data);
    }

    /**
     * Quick Access Menu
     * @return view
     */
    public function quick_access()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_shortcut_menu_extend');
        $this->load->view('quick_access/manage', $data);
    }

    /**
     * Retrieve the list of default settings
     */
    public function ajax_default_settings()
    {
        $defaultSettings = [
            'rels' => poly_utilities_common_helper::$rels,
            'target' => poly_utilities_common_helper::$targets,
            'type' => poly_utilities_common_helper::get_link_type()
        ];
        header('Content-Type: application/json');
        echo json_encode($defaultSettings);
        exit();
    }

    /**
     * Custom Menu
     * @return view
     */
    public function custom_menu()
    {
        if(!staff_can_poly_utilities_custom_menu()){
            access_denied();
        }

        if (!$this->feature_custom_menu_hooks) {
            $data['title'] = _l('poly_custom_menu_disabled_title');
            $data['settings_url'] = admin_url('poly_utilities/settings');
            $this->load->view('custom_menu/disabled', $data);
            return;
        }

        // For textarea html content
        $this->CI->app_scripts->add('tinymce-stickytoolbar', site_url('assets/plugins/tinymce-stickytoolbar/stickytoolbar.js'));
        $data['bodyclass'] = 'kb-article';

        // View display tab by: menu settup, menu clients, menu sidebar.
        $tab_menu = $this->input->get('menu');

        if ($tab_menu == 'setup') {
            $data['title'] = _l('poly_utilities_custom_setup_menu_extend');
            $data['active'] = 'setup';
            $this->load->view('custom_menu/menu_setup', $data);
        } elseif ($tab_menu == 'clients') {
            $data['title'] = _l('poly_utilities_custom_clients_menu_extend');
            $data['active'] = 'clients';
            $this->load->view('custom_menu/menu_clients', $data);
        } else {
            hooks()->remove_filter('sidebar_menu_items', 'app_poly_admin_sidebar_custom_options', 999);
            $data['title'] = _l('poly_utilities_custom_sidebar_menu_extend');
            $data['active'] = 'sidebar';
            $this->load->view('custom_menu/manage', $data);
        }
    }

    private function get_feature_flag(array $settings, $key, $default = true)
    {
        if (!array_key_exists($key, $settings)) {
            return (bool) $default;
        }

        $value = $settings[$key];

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower($value);
            if (in_array($normalized, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($normalized, ['false', '0', 'no', 'off'], true)) {
                return false;
            }
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return (bool) $value;
    }

    /**
     * Context Menu
     * @return view
     */
    public function context_menu()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_context_menu');
        $this->load->view('context_menu/manage', $data);
    }

    /**
     * Re init configs
     */
    public function ajax_reinit_configs()
    {
        $data = $this->input->post('data');
        // Refresh register the routes and hoooks
        poly_utilities_common_helper::require_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Translation cache for menu names
     * Prevents multiple calls to _l() for the same key
     * 
     * @var array
     */
    private static $translation_cache = [];
    
    /**
     * Translate menu names recursively using _l()
     * Handles both language keys and static text
     * 
     * OPTIMIZED WITH CACHING:
     * - Caches translation results to avoid repeated _l() calls
     * - Significantly improves performance for menus with many items
     * 
     * @param array $items Menu items
     * @return void
     */
    private function translate_menu_names(&$items)
    {
        if (!is_array($items)) {
            return;
        }
        
        foreach ($items as &$item) {
            // Translate name field using _l()
            if (isset($item['name']) && !empty($item['name'])) {
                $name_key = $item['name'];
                
                // Check cache first
                if (!isset(self::$translation_cache[$name_key])) {
                // _l() will:
                // - Return translated text if language key exists
                // - Return original text if key not found (for custom static text)
                    self::$translation_cache[$name_key] = _l($name_key, '', false);
                }
                
                $item['name'] = self::$translation_cache[$name_key];
            }
            
            // Recursively translate children
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->translate_menu_names($item['children']);
            }
        }
        unset($item);
    }
    
    /**
     * Clear translation cache
     * Useful when language changes or for testing
     * 
     * @return void
     */
    public static function clear_translation_cache()
    {
        self::$translation_cache = [];
    }
    
    /**
     * Retrieve the list of sidebar menus + custom sidebar menus
     * Merge with database data for option_settings and popup_description
     */
    public function ajax_sidebar_menu_items()
    {
        // Set global flag to skip permission filtering
        if (!defined('POLY_SKIP_PERMISSION_FILTER')) {
            define('POLY_SKIP_PERMISSION_FILTER', true);
        }
        
        $items = $this->app_menu->get_sidebar_menu_items();
        
        // Get custom menu items from database to merge option_settings and popup_description
        $db_menus = $this->custom_menu_model->get_menus('sidebar', true, true);
        
        // Create a map of db menus by slug for quick lookup
        $db_menus_map = [];
        if (is_array($db_menus) && !empty($db_menus)) {
            $flattenMenus = function ($menus) use (&$flattenMenus, &$db_menus_map) {
                foreach ($menus as $menu) {
                    if (isset($menu['slug'])) {
                        $db_menus_map[$menu['slug']] = $menu;
                    }
                    if (!empty($menu['children']) && is_array($menu['children'])) {
                        $flattenMenus($menu['children']);
                    }
                }
            };
            $flattenMenus($db_menus);
        }
        
        // Merge function to add option_settings and popup_description from database
        $mergeMenuData = function (&$items) use (&$mergeMenuData, $db_menus_map) {
            foreach ($items as $key => &$item) {
                $slug = $item['slug'] ?? null;
                
                if ($slug && isset($db_menus_map[$slug])) {
                    $db_menu = $db_menus_map[$slug];
                    
                    // Merge popup_description with safe JSON decode
                    if (!empty($db_menu['popup_description'])) {
                        if (is_string($db_menu['popup_description'])) {
                            // Use PHP native json_decode (not Guzzle's) with error handling
                            $decoded = @\json_decode($db_menu['popup_description'], true);
                            // Accept both array and string (popup_description is typically string content from TinyMCE)
                            $item['popup_description'] = (\json_last_error() === JSON_ERROR_NONE) 
                                ? $decoded 
                                : $db_menu['popup_description']; // Keep as string if not valid JSON
                        } else {
                            $item['popup_description'] = $db_menu['popup_description'];
                        }
                    }
                    
                    // Merge option_settings - parse and merge into item
                    if (!empty($db_menu['option_settings'])) {
                        if (is_string($db_menu['option_settings'])) {
                            // Use PHP native json_decode (not Guzzle's) with error handling
                            $settings = @\json_decode($db_menu['option_settings'], true);
                            if (\json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
                                foreach ($settings as $setting_key => $setting_value) {
                                    $item[$setting_key] = $setting_value;
                                }
                            }
                        } elseif (is_array($db_menu['option_settings'])) {
                            foreach ($db_menu['option_settings'] as $setting_key => $setting_value) {
                                $item[$setting_key] = $setting_value;
                            }
                        }
                    }
                    
                    // Merge roles, users, clients (already parsed by model as arrays)
                    $item['roles'] = isset($db_menu['roles']) && is_array($db_menu['roles']) ? $db_menu['roles'] : [];
                    $item['users'] = isset($db_menu['users']) && is_array($db_menu['users']) ? $db_menu['users'] : [];
                    $item['clients'] = isset($db_menu['clients']) && is_array($db_menu['clients']) ? $db_menu['clients'] : [];
                }
                
                // Recursively merge children
                if (!empty($item['children']) && is_array($item['children'])) {
                    $mergeMenuData($item['children']);
                }
            }
            unset($item);
        };
        
        $mergeMenuData($items);
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($items);
        
        array_unshift($items, ['name' => _l('Root', '', false), 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    /**
     * Retrieve the list of custom sidebar menus from database
     */
    public function ajax_custom_sidebar_menu_items()
    {
        // Load from database
        $menus = $this->custom_menu_model->get_menus('sidebar', true, true);
        
        //  Always ensure array (empty or with data)
        if (!is_array($menus)) {
            $menus = [];
        }
        
        // Convert to array format expected by frontend
        $data = $this->convert_db_menus_to_frontend_format($menus);
        
        //  Ensure data is array
        if (!is_array($data)) {
            $data = [];
        }
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($data);
        
        // Debug mode: return RAW database values + converted values
        if ($this->input->get('debug') == '1') {
            $debug_info = [];
            foreach ($menus as $menu) {
                $debug_info[] = [
                    'slug' => $menu['slug'],
                    'name' => $menu['name'],
                    'disabled_db' => $menu['disabled'],  // Raw from database
                    'disabled_frontend' => null // Will find in converted data
                ];
            }
            
            // Match with converted data
            $flattenData = function($items) use (&$flattenData) {
                $result = [];
                foreach ($items as $item) {
                    $result[] = $item;
                    if (!empty($item['children'])) {
                        $result = array_merge($result, $flattenData($item['children']));
                    }
                }
                return $result;
            };
            $flat_data = $flattenData($data);
            
            foreach ($debug_info as &$info) {
                foreach ($flat_data as $converted) {
                    if ($converted['slug'] === $info['slug']) {
                        $info['disabled_frontend'] = $converted['disabled'];
                        break;
                    }
                }
            }
            
            $response = [
                'source' => 'database',
                'count' => count($data),
                'debug_disabled' => $debug_info,
                'data' => $data
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // Normal debug mode
        if ($this->input->get('debug') == '2') {
            $response = [
                'source' => 'database',
                'count' => count($data),
                'has_parent_id' => !empty($data[0]['parent_id'] ?? null) || isset($data[0]['parent_id']),
                'sample' => array_slice($data, 0, 2),
                'data' => $data
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Retrieve the list of setup menus
     * Merge with database data for option_settings and popup_description
     */
    public function ajax_setup_menu_items()
    {
        $items = $this->app_menu->get_setup_menu_items();
        
        // Get custom menu items from database to merge option_settings and popup_description
        $db_menus = $this->custom_menu_model->get_menus('setup', true, true);
        
        // Create a map of db menus by slug for quick lookup
        $db_menus_map = [];
        if (is_array($db_menus) && !empty($db_menus)) {
            $flattenMenus = function ($menus) use (&$flattenMenus, &$db_menus_map) {
                foreach ($menus as $menu) {
                    if (isset($menu['slug'])) {
                        $db_menus_map[$menu['slug']] = $menu;
                    }
                    if (!empty($menu['children']) && is_array($menu['children'])) {
                        $flattenMenus($menu['children']);
                    }
                }
            };
            $flattenMenus($db_menus);
        }
        
        // Merge function to add option_settings and popup_description from database
        $mergeMenuData = function (&$items) use (&$mergeMenuData, $db_menus_map) {
            foreach ($items as $key => &$item) {
                $slug = $item['slug'] ?? null;
                
                if ($slug && isset($db_menus_map[$slug])) {
                    $db_menu = $db_menus_map[$slug];
                    
                    // Merge popup_description with safe JSON decode
                    if (!empty($db_menu['popup_description'])) {
                        if (is_string($db_menu['popup_description'])) {
                            // Use PHP native json_decode (not Guzzle's) with error handling
                            $decoded = @\json_decode($db_menu['popup_description'], true);
                            // Accept both array and string (popup_description is typically string content from TinyMCE)
                            $item['popup_description'] = (\json_last_error() === JSON_ERROR_NONE) 
                                ? $decoded 
                                : $db_menu['popup_description']; // Keep as string if not valid JSON
                        } else {
                            $item['popup_description'] = $db_menu['popup_description'];
                        }
                    }
                    
                    // Merge option_settings - parse and merge into item
                    if (!empty($db_menu['option_settings'])) {
                        if (is_string($db_menu['option_settings'])) {
                            // Use PHP native json_decode (not Guzzle's) with error handling
                            $settings = @\json_decode($db_menu['option_settings'], true);
                            if (\json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
                                foreach ($settings as $setting_key => $setting_value) {
                                    $item[$setting_key] = $setting_value;
                                }
                            }
                        } elseif (is_array($db_menu['option_settings'])) {
                            foreach ($db_menu['option_settings'] as $setting_key => $setting_value) {
                                $item[$setting_key] = $setting_value;
                            }
                        }
                    }
                    
                    // Merge roles, users, clients (already parsed by model as arrays)
                    $item['roles'] = isset($db_menu['roles']) && is_array($db_menu['roles']) ? $db_menu['roles'] : [];
                    $item['users'] = isset($db_menu['users']) && is_array($db_menu['users']) ? $db_menu['users'] : [];
                    $item['clients'] = isset($db_menu['clients']) && is_array($db_menu['clients']) ? $db_menu['clients'] : [];
                }
                
                // Recursively merge children
                if (!empty($item['children']) && is_array($item['children'])) {
                    $mergeMenuData($item['children']);
                }
            }
            unset($item);
        };
        
        $mergeMenuData($items);
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($items);
        
        array_unshift($items, ['name' => _l('Root', '', false), 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    /**
     * Retrieve the list of custom setup menus from database
     */
    public function ajax_custom_setup_menu_items()
    {
        // Load from database
        $menus = $this->custom_menu_model->get_menus('setup', true, true);
        
        //  Always ensure array (empty or with data)
        if (!is_array($menus)) {
            $menus = [];
        }
        
        // Convert to array format expected by frontend
        $data = $this->convert_db_menus_to_frontend_format($menus);
        
        //  Ensure data is array
        if (!is_array($data)) {
            $data = [];
        }
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($data);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Retrieve the list of clients menus from database
     */
    public function ajax_client_menu_items()
    {
        // Load clients menu from database
        $menu_items_custom = $this->custom_menu_model->get_menus('clients', true, true);
        
        //  Always ensure array
        if (!is_array($menu_items_custom)) {
            $menu_items_custom = [];
        }
        
        $menu_items_custom = poly_utilities_convert_db_to_system_format($menu_items_custom);
        
        //  Ensure array after conversion
        if (!is_array($menu_items_custom)) {
            $menu_items_custom = [];
        }

        $menu_items_custom = array_values($menu_items_custom);
        $flat_menu_items = poly_flatten_menu_items($menu_items_custom);

        //////////// Clients Logged ////////////
        poly_add_default_menu_items($flat_menu_items, $menu_items_custom);
        //////////// Clients Logged ////////////

        //  No longer update option - all data in database now
        // update_option(POLY_MENU_CLIENTS, json_encode($menu_items_custom));

        // SVG
        foreach ($menu_items_custom as $key => &$item) {
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
        }
        unset($item);
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($menu_items_custom);

        array_unshift($menu_items_custom, ['name' => _l('Root', '', false), 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($menu_items_custom);
        exit();
    }

    /**
     * Add or update a custom clients menu using database
     */
    public function update_custom_clients_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post();
            
            if (!isset($menu_item['parent_slug'])) {
                $menu_item['parent_slug'] = 'root';
            }
            
            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            
            // Convert frontend format to database format
            $converted = $this->convert_frontend_to_db_format($menu_item, 'clients');
            
            if ($isEdit !== 'true') {
                // Add new menu
                $menu_id = $this->custom_menu_model->add($converted['data'], $converted['permissions']);
                
                if ($menu_id) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    set_alert('success', _l('poly_utilities_response_add_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to add menu');
                }
            } else {
                // Update existing menu
                $existing = $this->custom_menu_model->get_by_slug($menu_item['slug'], 'clients');
                
                if ($existing) {
                    $success = $this->custom_menu_model->update($existing['id'], $converted['data'], $converted['permissions']);
                    
                    if ($success) {
                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                        set_alert('success', _l('poly_utilities_response_update_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_error('Failed to update menu');
                    }
                } else {
                    poly_utilities_ajax_response_helper::response_error('Menu not found');
                }
            }
            exit();
        }
    }

    /**
     * Retrieve the list of custom clients menus from database
     */
    public function ajax_custom_clients_menu_items()
    {
        // Load from database
        $menus = $this->custom_menu_model->get_menus('clients', true, true);
        
        //  Always ensure array (empty or with data)
        if (!is_array($menus)) {
            $menus = [];
        }
        
        // Convert to array format expected by frontend
        $data = $this->convert_db_menus_to_frontend_format($menus);
        
        //  Ensure data is array
        if (!is_array($data)) {
            $data = [];
        }
        
        // Translate all menu names using _l() before returning
        $this->translate_menu_names($data);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Retrieve the list of roles
     */
    public function ajax_roles()
    {
        $this->load->model("Roles_model");
        $data = $this->Roles_model->get();

        $data_slim_objects = array_map(function ($role) {
            return [
                'roleid' => $role['roleid'],
                'name' => $role['name']
            ];
        }, $data);

        $data_slim_objects = $data_slim_objects ? $data_slim_objects : [];
        header('Content-Type: application/json');
        echo json_encode($data_slim_objects);
        exit();
    }

    /**
     * Retrieve the list of clients based on search keywords.
     */
    public function ajax_clients_search()
    {
        $result = [];
        if (isset($_GET['search'])) {
            $search_keywords = $_GET['search'];
            $this->db->select('userid, company, address, phonenumber');
            $this->db->from(db_prefix() . 'clients');
            $this->db->group_start();
            $this->db->like('company', $search_keywords);
            $this->db->or_like('address', $search_keywords);
            $this->db->or_like('phonenumber', $search_keywords);
            $this->db->or_like('address', $search_keywords);
            $this->db->group_end();
            $this->db->order_by('company', 'ASC');
            $result = $this->db->get()->result_array();
            unset($value);
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve the list of users based on search keywords.
     */
    public function ajax_users_search()
    {
        $result = [];
        if (isset($_GET['search'])) {
            $search_keywords = $_GET['search'];
            if (has_permission('staff', '', 'view')) {
                $this->db->select('staffid, firstname, lastname');
                $this->db->from(db_prefix() . 'staff');
                $this->db->group_start();
                $this->db->like('firstname', $search_keywords);
                $this->db->or_like('lastname', $search_keywords);
                $this->db->or_like("CONCAT(firstname, ' ', lastname)", $search_keywords, false);
                $this->db->or_like("CONCAT(lastname, ' ', firstname)", $search_keywords, false);
                $this->db->or_like('phonenumber', $search_keywords);
                $this->db->or_like('email', $search_keywords);
                $this->db->group_end();
                $this->db->order_by('firstname', 'ASC');
                $result = $this->db->get()->result_array();

                // Get users_custom_menu list to exclude
                $access_data_json = poly_utilities_user_helper::get_users_access_modules();
                $access_data = json_decode($access_data_json, true);
                $users_custom_menu = [];
                if (isset($access_data['users_custom_menu']) && is_array($access_data['users_custom_menu'])) {
                    $users_custom_menu = $access_data['users_custom_menu'];
                }
                
                // Build array of IDs to exclude
                $exclude_ids = [1]; // Always exclude super admin
                foreach ($users_custom_menu as $user) {
                    if (isset($user['id'])) {
                        $exclude_ids[] = (int)$user['id'];
                    }
                }

                foreach ($result as $key => &$value) {
                    // Exclude Administrator id 1 and users in users_custom_menu
                    if (in_array((int)$value['staffid'], $exclude_ids)) {
                        unset($result[$key]);
                        continue;
                    }
                    $value['avatar'] = staff_profile_image_url($value['staffid']);
                }
                unset($value);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve the list of widgets area
     */
    public function ajax_widgets_area()
    {
        $items = poly_utilities_widget_helper::$widget_blocks;
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    public function update_users_access_modules()
    {
        if ($this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_unauthorize(_l('poly_utilities_response_unauthorized'));
        }

        $users_access = $this->input->post('users_access');
        $users_custom_menu = $this->input->post('users_custom_menu');
        $full_menu_items = [
            'users_access' => $users_access,
            'users_custom_menu' => $users_custom_menu
        ];
        update_option(POLY_UTILITIES_USERS_ACCESS_MODULES, json_encode($full_menu_items));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    public function get_users_access_modules()
    {
        $data = poly_utilities_user_helper::get_users_access_modules();
        header('Content-Type: application/json');
        echo $data;
        exit();
    }

    /**
     * Get users by IDs for select2 display (for edit mode)
     */
    public function get_users_by_ids()
    {
        $user_ids = $this->input->get('ids');
        
        if (empty($user_ids) || !is_array($user_ids)) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit();
        }
        
        $this->db->select('staffid, firstname, lastname');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where_in('staffid', $user_ids);
        $this->db->order_by('firstname', 'ASC');
        $result = $this->db->get()->result_array();
        
        $users = [];
        foreach ($result as $user) {
            $users[] = [
                'id' => $user['staffid'],
                'text' => $user['lastname'] . ' ' . $user['firstname'],
                'avatar' => staff_profile_image_url($user['staffid'])
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($users);
        exit();
    }

    /**
     * Get clients by IDs for select2 display (for edit mode in clients menu)
     */
    public function get_clients_by_ids()
    {
        $client_ids = $this->input->get('ids');
        
        if (empty($client_ids) || !is_array($client_ids)) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit();
        }
        
        $this->db->select('userid, company, CONCAT(company, " - ", address) as display_text', false);
        $this->db->from(db_prefix() . 'clients');
        $this->db->where_in('userid', $client_ids);
        $this->db->order_by('company', 'ASC');
        $result = $this->db->get()->result_array();
        
        $clients = [];
        foreach ($result as $client) {
            $clients[] = [
                'id' => $client['userid'],
                'text' => $client['display_text']
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($clients);
        exit();
    }

    /**
     * BACKUP: Update the order of sidebar menu items using database (ALL items)
     * @deprecated Use update_single_menu_item() for better performance
     */
    public function update_sidebar_menu_positions_backup()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            
            // Check if database is empty (first time drag & drop)
            $menu_count = $this->db->where('menu_type', 'sidebar')
                                   ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
            
            // If database is empty but we have menu items, auto-migrate first
            if ($menu_count == 0 && !empty($full_menu_items)) {
                $this->auto_migrate_menus_to_database('sidebar', $full_menu_items);
            }
            
            // Flatten menu to get all items with their new positions and parents
            $flat_menu_items = poly_flatten_menu_items($full_menu_items);
            
            //  Only update items that actually changed position or parent
            $positions = [];
            $parent_changes = [];
            
            foreach ($flat_menu_items as $item) {
                if (!isset($item['id']) || empty($item['id'])) {
                    continue;
                }
                
                $slug = $item['id']; // Frontend uses slug as id
                $menu = $this->custom_menu_model->get_by_slug($slug, 'sidebar');
                
                if ($menu) {
                    $new_position = $item['position'] ?? 0;
                    $current_position = $menu['position'];
                    
                    //  Only update if position actually changed
                    if ($new_position != $current_position) {
                        $positions[$menu['id']] = $new_position;
                    }
                    
                    //  Only update parent if it actually changed
                    if (isset($item['parent_slug'])) {
                        $new_parent_id = null;
                        
                        if ($item['parent_slug'] === 'root') {
                            $new_parent_id = null;
                        } else {
                            $parent_menu = $this->custom_menu_model->get_by_slug($item['parent_slug'], 'sidebar');
                            if ($parent_menu) {
                                $new_parent_id = $parent_menu['id'];
                            }
                        }
                        
                        $current_parent_id = $menu['parent_id'];
                        
                        //  Only update if parent actually changed
                        if ($new_parent_id != $current_parent_id) {
                            $parent_changes[$menu['id']] = $new_parent_id;
                        }
                    }
                }
            }
            
            //  Only update if there are actual changes
            if (!empty($positions) || !empty($parent_changes)) {
                $success = $this->custom_menu_model->update_positions($positions, $parent_changes);
                
                if ($success) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to update positions');
                }
            } else {
                // No changes needed
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        }
    }

    /**
     *  NEW: Update SINGLE menu item (optimized for drag-drop)
     * Only update the dragged item instead of all menus
     */
    public function update_single_menu_item()
    {
        if ($this->input->post()) {
            $item = $this->input->post('item');
            $menu_type = $this->input->post('menu_type') ?: 'sidebar';
            
            if (!$item || (!isset($item['id']) && !isset($item['slug']))) {
                poly_utilities_ajax_response_helper::response_error('Invalid item data - no ID or slug');
                return;
            }
            
            //  Find menu by ID first, fallback to slug
            $menu = null;
            
            // Try by database ID first (numeric)
            if (isset($item['id']) && is_numeric($item['id'])) {
                $menu = $this->db->where('id', $item['id'])
                                 ->where('menu_type', $menu_type)
                                 ->get(db_prefix() . 'poly_utilities_custom_menus')
                                 ->row_array();
            }
            
            // Fallback: Try by slug if ID search failed
            if (!$menu && isset($item['slug'])) {
                $menu = $this->db->where('slug', $item['slug'])
                                 ->where('menu_type', $menu_type)
                                 ->get(db_prefix() . 'poly_utilities_custom_menus')
                                 ->row_array();
            }
            
            if (!$menu) {
                poly_utilities_ajax_response_helper::response_error('Menu item not found in database');
                return;
            }
            
            $update_data = [];
            $changed = false;
            
            //  Update position if changed
            if (isset($item['position']) && $item['position'] != $menu['position']) {
                $update_data['position'] = (int)$item['position'];
                $changed = true;
            }
            
            //  Update parent if changed
            if (isset($item['parent_slug'])) {
                $new_parent_id = null;
                $new_parent_slug = $item['parent_slug'];
                
                if ($item['parent_slug'] === 'root' || empty($item['parent_slug'])) {
                    $new_parent_id = null;
                    $new_parent_slug = 'root';
                } else {
                    // Find parent by slug
                    $parent_menu = $this->custom_menu_model->get_by_slug($item['parent_slug'], $menu_type);
                    if ($parent_menu) {
                        $new_parent_id = $parent_menu['id'];
                    }
                }
                
                //  Check if parent_id changed
                if ($new_parent_id != $menu['parent_id']) {
                    $update_data['parent_id'] = $new_parent_id;
                    $changed = true;
                }
                
                //  Check if parent_slug changed
                if ($new_parent_slug != $menu['parent_slug']) {
                    $update_data['parent_slug'] = $new_parent_slug;
                    $changed = true;
                }
            }
            
            //  Only update if there are actual changes
            if ($changed && !empty($update_data)) {
                $this->db->where('id', $menu['id']);
                $success = $this->db->update(db_prefix() . 'poly_utilities_custom_menus', $update_data);
                
                if ($success) {
                    poly_utilities_ajax_response_helper::response_success('Menu updated successfully');
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to update menu');
                }
            } else {
                // No changes needed
                poly_utilities_ajax_response_helper::response_success('No changes needed');
            }
        }
    }

    /**
     * Update positions for multiple menu items (batch update)
     *  Used when drag & drop affects multiple items' positions
     */
    public function update_menu_positions_batch()
    {
        if ($this->input->post()) {
            $items = $this->input->post('items');
            $menu_type = $this->input->post('menu_type') ?: 'sidebar';
            
            if (!$items || !is_array($items) || empty($items)) {
                poly_utilities_ajax_response_helper::response_error('Invalid items data');
                return;
            }
            
            $updated_count = 0;
            $errors = [];
            
            //  Start transaction for atomic update
            $this->db->trans_start();
            
            foreach ($items as $item) {
                if (!isset($item['slug']) || empty($item['slug'])) {
                    continue; // Skip items without slug
                }
                
                //  Find menu by ID or slug
                $menu = null;
                
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $menu = $this->db->where('id', $item['id'])
                                     ->where('menu_type', $menu_type)
                                     ->get(db_prefix() . 'poly_utilities_custom_menus')
                                     ->row_array();
                }
                
                if (!$menu && isset($item['slug'])) {
                    $menu = $this->db->where('slug', $item['slug'])
                                     ->where('menu_type', $menu_type)
                                     ->get(db_prefix() . 'poly_utilities_custom_menus')
                                     ->row_array();
                }
                
                if (!$menu) {
                    $errors[] = "Menu not found: {$item['slug']}";
                    continue;
                }
                
                //  Prepare update data
                $update_data = [];
                
                // Update position if provided and changed
                if (isset($item['position']) && $item['position'] != $menu['position']) {
                    $update_data['position'] = (int)$item['position'];
                }
                
                // Update parent if provided and changed
                if (isset($item['parent_slug'])) {
                    $new_parent_id = null;
                    $new_parent_slug = $item['parent_slug'];
                    
                    if ($item['parent_slug'] === 'root' || empty($item['parent_slug'])) {
                        $new_parent_id = null;
                        $new_parent_slug = 'root';
                    } else {
                        $parent_menu = $this->custom_menu_model->get_by_slug($item['parent_slug'], $menu_type);
                        if ($parent_menu) {
                            $new_parent_id = $parent_menu['id'];
                        }
                    }
                    
                    if ($new_parent_id != $menu['parent_id']) {
                        $update_data['parent_id'] = $new_parent_id;
                    }
                    
                    if ($new_parent_slug != $menu['parent_slug']) {
                        $update_data['parent_slug'] = $new_parent_slug;
                    }
                }
                
                //  Only update if there are changes
                if (!empty($update_data)) {
                    $this->db->where('id', $menu['id']);
                    $success = $this->db->update(db_prefix() . 'poly_utilities_custom_menus', $update_data);
                    
                    if ($success) {
                        $updated_count++;
                    } else {
                        $errors[] = "Failed to update: {$item['slug']}";
                    }
                }
            }
            
            //  Complete transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                poly_utilities_ajax_response_helper::response_error('Failed to update menu positions - transaction failed');
            } else {
                //  Auto-fix any remaining duplicate positions for the SAME LEVEL ONLY
                $this->load->helper('poly_utilities/poly_utilities_menu_sync');
                
                // Determine the level of the dragged items
                $target_parent_id = null;
                if (!empty($items)) {
                    $first_item = $items[0];
                    if (isset($first_item['parent_slug']) && $first_item['parent_slug'] !== 'root') {
                        // Find parent ID for nested items
                        $parent_menu = $this->custom_menu_model->get_by_slug($first_item['parent_slug'], $menu_type);
                        if ($parent_menu) {
                            $target_parent_id = $parent_menu['id'];
                        }
                    }
                }
                
                $fixed_duplicates = poly_fix_duplicate_positions($menu_type, $target_parent_id);
                
                if (!empty($errors)) {
                    poly_utilities_ajax_response_helper::response_success(
                        "Updated {$updated_count} items. Auto-fixed {$fixed_duplicates} duplicates. Errors: " . implode(', ', $errors)
                    );
                } else {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                }
            }
        }
    }

    /**
     * Auto-fix duplicate positions in menu items
     *  Ensures unique sequential positions (1, 2, 3, 4...)
     */
    public function fix_duplicate_positions()
    {
        if (!$this->input->post()) {
            poly_utilities_ajax_response_helper::response_error('Invalid request method');
            return;
        }
        
        $menu_type = $this->input->post('menu_type') ?: 'sidebar';
        $parent_id = $this->input->post('parent_id') ?: null;
        
        if ($parent_id === 'null' || $parent_id === '') {
            $parent_id = null;
        }
        
        // Load helper
        $this->load->helper('poly_utilities/poly_utilities_menu_sync');
        
        // Fix positions
        $fixed_count = poly_fix_duplicate_positions($menu_type, $parent_id);
        
        if ($fixed_count > 0) {
            poly_utilities_ajax_response_helper::response_success(
                "Fixed {$fixed_count} duplicate positions"
            );
        } else {
            poly_utilities_ajax_response_helper::response_success(
                "No duplicate positions found"
            );
        }
    }

    /**
     * Fix all duplicate positions across all menu types
     */
    public function fix_all_duplicate_positions()
    {
        if (!$this->input->post()) {
            poly_utilities_ajax_response_helper::response_error('Invalid request method');
            return;
        }
        
        // Load helper
        $this->load->helper('poly_utilities/poly_utilities_menu_sync');
        
        // Fix all positions
        $summary = poly_fix_all_duplicate_positions();
        
        $total_fixed = array_sum($summary);
        
        if ($total_fixed > 0) {
            poly_utilities_ajax_response_helper::response_success(
                "Fixed {$total_fixed} duplicate positions across all menus. Details: " . json_encode($summary)
            );
        } else {
            poly_utilities_ajax_response_helper::response_success(
                "No duplicate positions found across all menus"
            );
        }
    }

    /**
     * Add or update a custom sidebar menu.
     */
    /**
     * Add or update a custom sidebar menu using database
     */
    public function update_custom_sidebar_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post(null, false);
            if ($menu_item) {
                $menu_item = poly_utilities_common_helper::clean_xss_except($menu_item, ['popup_description']);
            }

            if (!isset($menu_item['parent_slug'])) {
                $menu_item['parent_slug'] = 'root';
            }

            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            
            // Convert frontend format to database format
            $converted = $this->convert_frontend_to_db_format($menu_item, 'sidebar');
            
            if ($isEdit !== 'true') {
                // Add new menu
                $menu_id = $this->custom_menu_model->add($converted['data'], $converted['permissions']);
                
                if ($menu_id) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    set_alert('success', _l('poly_utilities_response_add_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to add menu');
                }
            } else {
                // Update existing menu - find by slug
                $existing = $this->custom_menu_model->get_by_slug($menu_item['slug'], 'sidebar');
                
                if ($existing) {
                    $success = $this->custom_menu_model->update($existing['id'], $converted['data'], $converted['permissions']);
                    
                    if ($success) {
                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                        set_alert('success', _l('poly_utilities_response_update_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_error('Failed to update menu');
                    }
                } else {
                    poly_utilities_ajax_response_helper::response_error('Menu not found');
                }
            }
            exit();
        }
    }

    /**
     * Recursively searches for the parent_slug in the menu structure and appends the menu_object to the parent's children.
     *
     * @param array $menu_items The array of menu items that may contain nested children.
     * @param array $menu_object The new menu item to be added to its parent's children.
     * @return bool Returns true if the parent is found and the child is added, otherwise false.
     */
    public function poly_add_menu_item_to_parent(&$menu_items, $menu_object)
    {
        foreach ($menu_items as &$item) {
            if ($item['id'] === $menu_object['parent_slug']) {
                if (!isset($item['children'])) {
                    $item['children'] = [];
                }
                array_push($item['children'], $menu_object);
                return true;
            }

            if (isset($item['children']) && !empty($item['children'])) {
                $found = $this->poly_add_menu_item_to_parent($item['children'], $menu_object);
                if ($found) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Delete a custom sidebar menu using database
     */
    public function delete_custom_sidebar_menu()
    {
        $slug = $this->input->post('id', FALSE); // Frontend sends 'id' which is actually slug
        
        if (!$slug) {
            poly_utilities_ajax_response_helper::response_error("Menu ID is required.");
            return;
        }
        
        // Find menu by slug
        $menu = $this->custom_menu_model->get_by_slug($slug, 'sidebar');
        
        if (!$menu) {
            poly_utilities_ajax_response_helper::response_error("Menu not found.");
            return;
        }
        
        // Delete using model (will cascade delete children and permissions)
        $success = $this->custom_menu_model->delete($menu['id']);
        
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Update the order of setup menu items using database
     */
    public function update_setup_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            
            // Check if database is empty (first time drag & drop)
            $menu_count = $this->db->where('menu_type', 'setup')
                                   ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
            
            // If database is empty but we have menu items, auto-migrate first
            if ($menu_count == 0 && !empty($full_menu_items)) {
                $this->auto_migrate_menus_to_database('setup', $full_menu_items);
            }
            
            $flat_menu_items = poly_flatten_menu_items($full_menu_items);
            
            //  Only update items that actually changed position or parent
            $positions = [];
            $parent_changes = [];
            
            foreach ($flat_menu_items as $item) {
                if (!isset($item['id'])) continue;
                
                $slug = $item['id'];
                $menu = $this->custom_menu_model->get_by_slug($slug, 'setup');
                
                if ($menu) {
                    $new_position = $item['position'] ?? 0;
                    $current_position = $menu['position'];
                    
                    //  Only update if position actually changed
                    if ($new_position != $current_position) {
                        $positions[$menu['id']] = $new_position;
                    }
                    
                    //  Only update parent if it actually changed
                    if (isset($item['parent_slug'])) {
                        $new_parent_id = null;
                        
                        if ($item['parent_slug'] === 'root') {
                            $new_parent_id = null;
                        } else {
                            $parent_menu = $this->custom_menu_model->get_by_slug($item['parent_slug'], 'setup');
                            if ($parent_menu) {
                                $new_parent_id = $parent_menu['id'];
                            }
                        }
                        
                        $current_parent_id = $menu['parent_id'];
                        
                        //  Only update if parent actually changed
                        if ($new_parent_id != $current_parent_id) {
                            $parent_changes[$menu['id']] = $new_parent_id;
                        }
                    }
                }
            }
            
            //  Only update if there are actual changes
            if (!empty($positions) || !empty($parent_changes)) {
                $success = $this->custom_menu_model->update_positions($positions, $parent_changes);
                
                if ($success) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to update positions');
                }
            } else {
                // No changes needed
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        }
    }

    /**
     * Add or update a custom setup menu using database
     */
    public function update_custom_setup_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post();
            
            if (!isset($menu_item['parent_slug'])) {
                $menu_item['parent_slug'] = 'root';
            }
            
            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            
            // Convert frontend format to database format
            $converted = $this->convert_frontend_to_db_format($menu_item, 'setup');
            
            if ($isEdit !== 'true') {
                // Add new menu
                $menu_id = $this->custom_menu_model->add($converted['data'], $converted['permissions']);
                
                if ($menu_id) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    set_alert('success', _l('poly_utilities_response_add_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to add menu');
                }
            } else {
                // Update existing menu
                $existing = $this->custom_menu_model->get_by_slug($menu_item['slug'], 'setup');
                
                if ($existing) {
                    $success = $this->custom_menu_model->update($existing['id'], $converted['data'], $converted['permissions']);
                    
                    if ($success) {
                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                        set_alert('success', _l('poly_utilities_response_update_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_error('Failed to update menu');
                    }
                } else {
                    poly_utilities_ajax_response_helper::response_error('Menu not found');
                }
            }
            exit();
        }
    }

    /**
     * Delete a custom setup menu using database
     */
    public function delete_custom_setup_menu()
    {
        $slug = $this->input->post('id', FALSE);
        
        if (!$slug) {
            poly_utilities_ajax_response_helper::response_error("Menu ID is required.");
            return;
        }
        
        $menu = $this->custom_menu_model->get_by_slug($slug, 'setup');
        
        if (!$menu) {
            poly_utilities_ajax_response_helper::response_error("Menu not found.");
            return;
        }
        
        $success = $this->custom_menu_model->delete($menu['id']);
        
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Update the order of clients menu items using database
     */
    public function update_clients_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            
            // Check if database is empty (first time drag & drop)
            $menu_count = $this->db->where('menu_type', 'clients')
                                   ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
            
            // If database is empty but we have menu items, auto-migrate first
            if ($menu_count == 0 && !empty($full_menu_items)) {
                $this->auto_migrate_menus_to_database('clients', $full_menu_items);
            }
            
            $flat_menu_items = poly_flatten_menu_items($full_menu_items);
            
            //  Only update items that actually changed position or parent
            $positions = [];
            $parent_changes = [];
            
            foreach ($flat_menu_items as $item) {
                if (!isset($item['id'])) continue;
                
                $slug = $item['id'];
                $menu = $this->custom_menu_model->get_by_slug($slug, 'clients');
                
                if ($menu) {
                    $new_position = $item['position'] ?? 0;
                    $current_position = $menu['position'];
                    
                    //  Only update if position actually changed
                    if ($new_position != $current_position) {
                        $positions[$menu['id']] = $new_position;
                    }
                    
                    //  Only update parent if it actually changed
                    if (isset($item['parent_slug'])) {
                        $new_parent_id = null;
                        
                        if ($item['parent_slug'] === 'root') {
                            $new_parent_id = null;
                        } else {
                            $parent_menu = $this->custom_menu_model->get_by_slug($item['parent_slug'], 'clients');
                            if ($parent_menu) {
                                $new_parent_id = $parent_menu['id'];
                            }
                        }
                        
                        $current_parent_id = $menu['parent_id'];
                        
                        //  Only update if parent actually changed
                        if ($new_parent_id != $current_parent_id) {
                            $parent_changes[$menu['id']] = $new_parent_id;
                        }
                    }
                }
            }
            
            //  Only update if there are actual changes
            if (!empty($positions) || !empty($parent_changes)) {
                $success = $this->custom_menu_model->update_positions($positions, $parent_changes);
                
                if ($success) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error('Failed to update positions');
                }
            } else {
                // No changes needed
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        }
    }

    /**
     * Update menu item disabled status (on/off switch)
     */
    public function update_menu_disabled_status()
    {
        if ($this->input->post()) {
            $menu_id = $this->input->post('menu_id');
            $disabled = $this->input->post('disabled');
            $menu_type = $this->input->post('menu_type', 'sidebar');
            
            if (!$menu_id) {
                poly_utilities_ajax_response_helper::response_error('Menu ID is required');
                exit();
            }
            
            //  Use slug directly (no need to convert to ID)
            // menu_id is already a slug (e.g., 'menu_68f8da673ba3a')
            
            // Validate menu_type
            if (!in_array($menu_type, ['sidebar', 'setup', 'clients'])) {
                poly_utilities_ajax_response_helper::response_error('Invalid menu type');
                exit();
            }
            
            // Convert disabled value to integer
            // Handle both string and boolean values
            if ($disabled === 'true' || $disabled === true || $disabled === '1' || $disabled === 1) {
                $disabled_value = 1;
            } elseif ($disabled === 'false' || $disabled === false || $disabled === '0' || $disabled === 0) {
                $disabled_value = 0;
            } else {
                // Default to 0 if value is unclear
                $disabled_value = 0;
            }
            
            //  Check if menu exists before update
            $existing_menu = $this->custom_menu_model->get_by_slug($menu_id, $menu_type);
            if (!$existing_menu) {
                poly_utilities_ajax_response_helper::response_error('Menu not found');
                exit();
            }
            
            // Update only the disabled status
            $success = $this->custom_menu_model->update_disabled_status($menu_id, $disabled_value, $menu_type);
            
            if ($success) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error('Failed to update menu status');
            }
        }
    }

    /**
     * Clone a custom sidebar menu using database
     */
    public function clone_sidebar_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            $menu = $this->custom_menu_model->get_by_slug($slug, 'sidebar');
            
            if (!$menu) {
                poly_utilities_ajax_response_helper::response_error('Menu not found');
                exit();
            }
            
            $new_id = $this->custom_menu_model->clone_menu($menu['id'], true);
            
            if ($new_id) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error('Failed to clone menu');
            }
            exit();
        }
    }

    /**
     * Clone a custom setup menu using database
     */
    public function clone_setup_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            $menu = $this->custom_menu_model->get_by_slug($slug, 'setup');
            
            if (!$menu) {
                poly_utilities_ajax_response_helper::response_error('Menu not found');
                exit();
            }
            
            $new_id = $this->custom_menu_model->clone_menu($menu['id'], true);
            
            if ($new_id) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error('Failed to clone menu');
            }
            exit();
        }
    }

    /**
     * Clone a custom clients menu using database
     */
    public function clone_clients_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            $menu = $this->custom_menu_model->get_by_slug($slug, 'clients');
            
            if (!$menu) {
                poly_utilities_ajax_response_helper::response_error('Menu not found');
                exit();
            }
            
            $new_id = $this->custom_menu_model->clone_menu($menu['id'], true);
            
            if ($new_id) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error('Failed to clone menu');
            }
            exit();
        }
    }

    /**
     * Delete a custom sidebar menu.
     */
    /**
     * Delete a custom clients menu using database
     */
    public function delete_custom_clients_menu()
    {
        $slug = $this->input->post('id', FALSE);
        
        if (!$slug) {
            poly_utilities_ajax_response_helper::response_error("Menu ID is required.");
            return;
        }
        
        $menu = $this->custom_menu_model->get_by_slug($slug, 'clients');
        
        if (!$menu) {
            poly_utilities_ajax_response_helper::response_error("Menu not found.");
            return;
        }
        
        $success = $this->custom_menu_model->delete($menu['id']);
        
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Reset a custom menu using database (delete only custom items)
     */
    public function reset_custom_menu()
    {
        hooks()->do_action('poly_demo_before_action', true);

        if ($this->input->post()) {
            $menu = $this->input->post('menu'); // 'sidebar', 'setup', 'clients', or 'all'
            
            $menu_types = [];
            if ($menu === 'all') {
                $menu_types = ['sidebar', 'setup', 'clients'];
            } else {
                $menu_types = [$menu];
            }
            
            $success = true;
            foreach ($menu_types as $menu_type) {
                $result = $this->custom_menu_model->reset_menus($menu_type, true); // Only delete custom menus
                $success = $success && $result;
            }
            
            if ($success) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error('Failed to reset menu');
            }
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
        }
    }

    /**
     * Delete a custom menu using database (TRUNCATE table for better performance)
     */
    public function delete_custom_menu()
    {
        hooks()->do_action('poly_demo_before_action', true);

        if ($this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
            return;
        }

        if ($this->input->post()) {
            $menu = $this->input->post('menu'); // 'sidebar', 'setup', 'clients', or 'all'
            
            //  Use transaction for safety
            $this->db->trans_start();
            
            try {
                if ($menu === 'all') {
                    // Truncate all menus
                    $this->db->truncate(db_prefix() . 'poly_utilities_custom_menus');
                    
                    $message = 'All menus deleted successfully';
                } else {
                    // Delete specific menu type
                    $this->db->where('menu_type', $menu);
                    $this->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                    
                    $message = ucfirst($menu) . ' menus deleted successfully';
                }
                
                //  Complete transaction
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Transaction failed');
                }
                
                //  After delete, re-sync system menus to ensure they're back
                if (function_exists('poly_force_sync_all_menus')) {
                    poly_force_sync_all_menus();
                }
                
                // Re-initialize clients menu if clients or all was deleted
                if (($menu === 'clients' || $menu === 'all') && function_exists('poly_init_default_clients_menu')) {
                    poly_init_default_clients_menu();
                }
                
                poly_utilities_ajax_response_helper::response_success($message);
            } catch (Exception $e) {
                //  Rollback on error
                $this->db->trans_rollback();
                
                log_message('error', 'Delete menu error: ' . $e->getMessage());
                poly_utilities_ajax_response_helper::response_error('Failed to delete menu: ' . $e->getMessage());
            }
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
        }
    }

    /**
     * Widgets
     * @return view
     */
    public function widgets()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_widgets_extend');
        $this->load->view('widgets/manage', $data);
    }

    /**
     * Support
     * @return view
     */
    public function support()
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];

        $data['title'] = _l('poly_utilities_support');
        $data['poly_utilities_aio_supports'] = $poly_utilities_aio_supports;

        $this->load->view('support/manage', $data);
    }

    /**
     * Projects
     * @return view
     */
    public function projects()
    {
        $data['title'] = _l('poly_utilities_projects');
        $tab = $this->input->get('group');
        $data['current_tab'] = $tab;

        $data['tabs'] = [
            "project_name_patterns" => $this->createTab("project_name_patterns", _l('poly_utilities_projects_tabs_name_template'), "poly_utilities/projects/project_name_patterns", 5, 'fa-solid fa-puzzle-piece fa-fw'),
            //"settings" => $this->createTab("settings", _l('poly_utilities_projects_tabs_settings'), "poly_utilities/projects/settings", 10, "fa fa-sliders-h")
            //Waiting for newest version: Tasks
        ];

        if (!$tab || (in_array($tab, $data['tabs']) && !is_admin())) {
            $tab = 'project_name_patterns';
        }

        //if ($tab === 'settings') {// Waiting for newest version: Tasks
        //$data['tab'] = $this->createTab("settings", _l('poly_utilities_projects_tabs_settings'), "poly_utilities/projects/settings", 10, "fa fa-th");
        //} else {
        if (!in_array($tab, $data['tabs'])) {
            $data['tab'] = $this->app_tabs->filter_tab($data['tabs'], $tab);
        }
        //}
        $this->load->view('projects/index', $data);
    }

    /**
     * Banners & Annoucements
     * @return view
     */
    public function banners()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_banners');
        $tab = $this->input->get('group');
        $data['current_tab'] = $tab;

        $data['tabs'] = [
            "manage" => $this->createTab("manage", _l('poly_utilities_banner_media_tabs_banners'), "poly_utilities/banners/manage", 5, "fa fa-th"),
            "announcements" => $this->createTab("announcements", _l('poly_utilities_banner_media_tabs_announcements'), "poly_utilities/banners/announcements", 10, "fa-solid fa-comments fa-fw"),
            "settings" => $this->createTab("settings", _l('poly_utilities_banner_media_tabs_settings'), "poly_utilities/banners/settings", 15, "fa fa-sliders-h")
        ];

        if (!$tab || (in_array($tab, $data['tabs']) && !is_admin())) {
            $tab = 'manage';
        }

        if ($tab === 'settings') {
            $data['tab'] = $this->createTab("settings", _l('poly_utilities_banner_media_tabs_settings'), "poly_utilities/banners/settings", 15, "fa fa-th");
        } else {
            if (!in_array($tab, $data['tabs'])) {
                $data['tab'] = $this->app_tabs->filter_tab($data['tabs'], $tab);
            }
        }
        $this->load->view('banners/index', $data);
    }

    public function createTab($slug, $name, $view, $position, $icon, $is_display = true)
    {
        return [
            "slug" => $slug,
            "name" => $name,
            "view" => $view,
            "position" => $position,
            "icon" => $icon,
            "is_display" => $is_display,
            "href" => "#",
            "badge" => [],
            "children" => []
        ];
    }

    /**
     * Appearance
     * @return view
     */
    public function appearance(){
        $data['title'] = _l('poly_utilities_appearance');

        $poly_utilities_appearance = clear_textarea_breaks(get_option(POLY_UTILITIES_APPEARANCE_SETTINGS));
        if (empty($poly_utilities_appearance)) {
            $obj_settings = new stdClass();
            $obj_settings->is_active_login_page = false;
            $obj_settings->login_background = '';
            $obj_settings->dashboard_background = '';

            update_option(POLY_UTILITIES_APPEARANCE_SETTINGS, json_encode($obj_settings));
            $poly_utilities_appearance = clear_textarea_breaks(get_option(POLY_UTILITIES_APPEARANCE_SETTINGS));
        }
        $poly_utilities_appearance = !empty($poly_utilities_appearance) ? json_decode($poly_utilities_appearance, true) : [];
        
        $data['poly_utilities_appearance'] = $poly_utilities_appearance;

        $this->load->view('appearance/manage', $data);
    }
    
    /**
     * Settings
     * @return view
     */
    public function settings()
    {
        $this->_check_module_access();
        $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));

        if (empty($poly_utilities_settings)) {
            $obj_settings = new stdClass();
            $obj_settings->is_sticky = false;
            $obj_settings->is_admin_breadcrumb = true;
            $obj_settings->is_toggle_sidebar_menu = false;
            $obj_settings->is_table_of_content = false;
            $obj_settings->is_active_scripts = true;
            $obj_settings->is_active_styles = true;
            $obj_settings->is_note_confirm_delete = true;
            $obj_settings->is_operation_functions = true;
            $obj_settings->is_scroll_to_top = false;
            $obj_settings->is_data_table_reorder_column = true;
            update_option(POLY_UTILITIES_SETTINGS, json_encode($obj_settings));
            $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));
        }
        $poly_utilities_settings = !empty($poly_utilities_settings) ? json_decode($poly_utilities_settings, true) : [];

        $data['title'] = _l('poly_utilities_settings');
        $data['poly_utilities_settings'] = $poly_utilities_settings;

        $this->load->view('settings', $data);
    }

    /**
     * Remove Quick Access Menu
     * @return view
     */
    public function delete_quick_access()
    {
        foreach (['link'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'link', $link)) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'link', $link);
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($x));
                poly_utilities_ajax_response_helper::response_success("Remove {$link}");
            }
        }
    }

    /**
     * Update Quick Access Menu
     * @return view
     */
    public function update_quick_access_menu()
    {
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_QUICK_ACCESS_MENU, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Add Quick Access Menu
     * @return view
     */
    public function save_quick_access()
    {
        foreach (['icon', 'title', 'link', 'shortcut_key', 'target', 'rel'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj = new stdClass();
        $obj->index = poly_utilities_common_helper::generateUniqueID();
        $obj->icon = $icon;
        $obj->title = $title;
        $obj->link = $link;
        $obj->target = !empty($target) ? $target : '_self';
        $obj->rel = !empty($rel) ? $rel : 'nofollow';
        $obj->shortcut_key = $shortcut_key;

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (!poly_utilities_common_helper::isExisted($obj_old_data, 'link', $obj->link)) {
                $obj_old_data[] = $obj;
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
            } else {
                poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
            }
        } else {
            $obj_old_data[] = $obj;
            update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
        }
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
    }

    /**
     * Uppdate All in One Supports
     * @return json
     */
    public function save_aio_supports()
    {
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_SUPPORTS, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Add resource: js | css
     * @return view
     */
    public function save_resource()
    {
        foreach (['title', 'file', 'mode', 'is_lock', 'content', 'state', 'resource', 'is_embed', 'is_embed_position'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = is_string($$input_object) ? trim($$input_object) : $$input_object;
        }

        if ($is_lock == 'true' && $this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
        }

        switch ($mode) {
            case 'truetrue':
                $mode = 'admin_customers';
                break;
            case 'truefalse':
                $mode = 'admin';
                break;
            case 'falsetrue':
                $mode = 'customers';
                break;
        }
        $resourceTable = POLY_SCRIPTS;
        $resourceExtension = '.js';
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    $resourceExtension = '.js';
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    $resourceExtension = '.css';
                    break;
                }
        }

        $obj = new stdClass();
        $obj->title = $title;
        $obj->file = ($file ? $file : poly_utilities_common_helper::create_slug($title));
        $obj->mode = $mode; //admin, customers, admin_customers;
        $obj->is_embed = $is_embed;
        $obj->is_embed_position = $is_embed_position;

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (isset($state) && $state == true) {
                foreach ($obj_old_data as &$item) {
                    if ($item['file'] === $obj->file) {
                        $item['title'] =  $obj->title;
                        $item['mode'] = $mode;
                        $item['is_embed'] = $is_embed;
                        if ($this->current_user_id == 1) {
                            $item['is_lock'] = $is_lock;
                        }
                        $item['is_embed_position'] = $is_embed_position;
                    }
                }
                unset($item);

                $isSave = poly_utilities_common_helper::save_to_file($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content, true);
                if ($isSave == 1) {
                    update_option($resourceTable, json_encode($obj_old_data));
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_update_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        if (poly_utilities_common_helper::isExisted($obj_old_data, 'file', $obj->file)) {
            poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
        } else {
            $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $file . $resourceExtension;
            if (file_exists($file_path)) {
                if (!@unlink($file_path)) {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        $obj_old_data[] = $obj;
        $isSave = poly_utilities_common_helper::save_to_file($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content);
        if ($isSave == 1) {
            update_option($resourceTable, json_encode($obj_old_data));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
        } else {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
        }
    }

    /**
     * Remove resource: js | css
     * @return view
     */
    public function delete_resource()
    {
        foreach (['id', 'resource'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        $resourceTable = POLY_SCRIPTS;
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    break;
                }
        }

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            $obj_resource = poly_utilities_common_helper::getResourceObject($obj_old_data, 'file', $id);

            if (isset($obj_resource['is_lock']) && $obj_resource['is_lock'] === 'true' && $this->current_user_id != 1) {
                poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
            }

            if ($obj_resource) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'file', $id);
                update_option($resourceTable, json_encode($x));
                $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $id . '.' . $resource;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        poly_utilities_ajax_response_helper::response_success("Remove & delete file: {$id}");
                    }
                }
                poly_utilities_ajax_response_helper::response_success("Remove {$id}");
            }
        }
    }

    public function update_resource_status()
    {
        foreach (['id', 'mode', 'is_lock', 'resource'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        if ($is_lock == 'true' && $this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
        }

        $resourceTable = POLY_SCRIPTS;
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    break;
                }
        }
        switch ($mode) {
            case 'truetrue':
                $mode = 'admin_customers';
                break;
            case 'truefalse':
                $mode = 'admin';
                break;
            case 'falsetrue':
                $mode = 'customers';
                break;
        }

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if ($obj_old_data) {
                $obj = poly_utilities_common_helper::getResourceObject($obj_old_data, 'file', $id);
                if ($this->current_user_id == 1) {
                    $obj['is_lock'] = $is_lock;
                }
                $obj['mode'] = $mode;
                $dataTableFiltersUpdate = poly_utilities_common_helper::updateDataByField($obj_old_data, 'file', $id, $obj);
                if (update_option($resourceTable, json_encode($dataTableFiltersUpdate)) === true) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }
    }

    /**
     * Update Settings
     * @return view
     */
    public function update_settings()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);
            update_option(POLY_UTILITIES_SETTINGS, json_encode($objs));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    public function update_widget()
    {
        if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit') || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            $objs = $this->input->post('data', FALSE);
            update_option(POLY_WIDGETS, json_encode($objs));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('access_denied'));
        }
    }
    ////////////////////////////////////////////////////////////////////// END FUNCTIONS //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// DATA TABLE FILTER //////////////////////////////////////////////////////////////////////

    /**
     * Update the display filter configuration for the data tables.
     * @return view
     */
    public function save_data_filters()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);

            $obj = new stdClass();
            $obj->key = $objs['key'];
            $obj->value = $objs['value'];

            $dataFilters = get_option(POLY_TABLE_FILTERS);

            $dataTaleFilters = [];
            if (!empty($dataFilters)) {
                $dataTaleFilters = json_decode($dataFilters, true);
                //Update
                if (poly_utilities_common_helper::isExisted($dataTaleFilters, 'key', $obj->key)) {
                    $dataTableFiltersUpdate = poly_utilities_common_helper::updateDataByField($dataTaleFilters, 'key', $obj->key, $obj);
                    if (update_option(POLY_TABLE_FILTERS, json_encode($dataTableFiltersUpdate)) === true) {
                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_data_not_saved('Error');
                    }
                }
            }

            $dataTaleFilters[] = $obj;
            if (update_option(POLY_TABLE_FILTERS, json_encode($dataTaleFilters)) === true) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            } else {
                poly_utilities_ajax_response_helper::response_data_not_saved('Error');
            }
        }
    }
    ////////////////////////////////////////////////////////////////////// END DATA TABLE FILTER //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// CONTEXT MENU //////////////////////////////////////////////////////////////////////

    /**
     * Retrieve the list of context menu
     */
    public function ajax_context_menu_items()
    {
        $data = get_option(POLY_CONTEXT_MENU);
        $data = $data ? json_decode($data, true) : [];
        if (!empty($data) && is_array($data)) {
            $data = array_values($data);
        }
        array_unshift($data, ['name' => 'Root', 'slug' => 'root', 'href' => '#', 'badge' => array('color' => '', 'value' => '')]);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Update the order of sidebar menu items.
     */
    public function update_context_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            update_option(POLY_CONTEXT_MENU, json_encode($full_menu_items));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    /**
     * Delete a custom sidebar menu.
     */
    public function delete_context_menu()
    {
        $id = $this->input->post('id', FALSE);
        
        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error("Menu ID is required.");
            return;
        }
        
        // Context menu uses options table (not database)
        $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_CONTEXT_MENU), TRUE);
        
        if (!is_array($menu_items)) {
            $menu_items = [];
        }
        
        // Remove item by slug
        $found = false;
        foreach ($menu_items as $key => $item) {
            if (isset($item['slug']) && $item['slug'] === $id) {
                unset($menu_items[$key]);
                $found = true;
                break;
            }
        }
        
        if ($found) {
            // Re-index array
            $menu_items = array_values($menu_items);
            
            // Save back to options
            update_option(POLY_CONTEXT_MENU, json_encode($menu_items));
            
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_delete_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error("Menu item not found.");
        }
    }

    /**
     * Clone a context menu item
     */
    public function clone_context_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            // Context menu uses options table (not database)
            $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_CONTEXT_MENU), TRUE);
            
            if (!is_array($menu_items)) {
                $menu_items = [];
            }
            
            // Find item to clone
            $item_to_clone = null;
            foreach ($menu_items as $item) {
                if (isset($item['slug']) && $item['slug'] === $slug) {
                    $item_to_clone = $item;
                    break;
                }
            }
            
            if (!$item_to_clone) {
                poly_utilities_ajax_response_helper::response_error('Menu item not found');
                exit();
            }
            
            // Clone item with new slug and name
            $cloned_item = $item_to_clone;
            $cloned_item['slug'] = poly_generate_menu_slug();
            $cloned_item['name'] = $item_to_clone['name'] . ' (Copy)';
            $cloned_item['is_custom'] = 'true';
            
            // Insert cloned item right after the original item
            if (!poly_insert_cloned_menu_item($menu_items, $slug, $cloned_item)) {
                // Fallback: if not found (shouldn't happen), append to end
                $menu_items[] = $cloned_item;
            }
            
            // Save back to options
            update_option(POLY_CONTEXT_MENU, json_encode($menu_items));
            
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_add_success'));
            exit();
        }
    }

    /**
     * Add or update a custom context menu.
     */
    public function update_context_menu()
    {
        if ($this->input->post()) {

            $menu_item = $this->input->post(null, false);
            if ($menu_item) {
                $menu_item = poly_utilities_common_helper::clean_xss_except($menu_item, ['popup_description']);
            }

            if (!isset($menu_item['parent_slug'])) { //Fix lost parent slug; 
                $menu_item['parent_slug'] = 'root';
            }

            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            if ($isEdit !== 'true') { // Add new
                $menu_items_custom = get_option(POLY_CONTEXT_MENU);
                $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);

                $menu_object = poly_create_menu_item($menu_item);
                $menu_items_custom[] = $menu_object;

                update_option(POLY_CONTEXT_MENU, json_encode($menu_items_custom));

                $custom_items_position = get_option(POLY_CONTEXT_MENU);

                if (!empty($custom_items_position)) {
                    $custom_items_position = poly_utilities_common_helper::json_decode($custom_items_position, TRUE);

                    if ($menu_object['parent_slug'] === 'root') { // Add first array when root
                        array_unshift($custom_items_position, $menu_object);
                        update_option(POLY_CONTEXT_MENU, json_encode($custom_items_position));
                    } else {
                        $found = $this->poly_add_menu_item_to_parent($custom_items_position, $menu_object);
                        if ($found) {
                            update_option(POLY_CONTEXT_MENU, json_encode($custom_items_position));
                        }
                    }
                }

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else { // Update
                $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_CONTEXT_MENU), TRUE);

                if (isset($menu_item['icon']) && strpos($menu_item['icon'], 'svg') !== false) {
                    $menu_item['svg'] = $menu_item['icon'];
                    $menu_item['icon'] = 'menu-icon';
                }else{
                    $menu_item['svg'] = '';// Remove SVG;
                }

                poly_utilities_menu_sidebar_update($menu_items, $menu_item, true);
                update_option(POLY_CONTEXT_MENU, json_encode($menu_items));

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                set_alert('success', _l('poly_utilities_response_update_success'));
            }
            exit();
        }
    }
    ////////////////////////////////////////////////////////////////////// END CONTEXT MENU //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// FIXED BOTTOM MENU //////////////////////////////////////////////////////////////////////

    /**
     * Fixed Bottom Menu
     * @return view
     */
    public function fixed_bottom_menu()
    {
        $this->_check_module_access();
        $data['title'] = _l('poly_utilities_fixed_bottom_menu');
        $this->load->view('fixed_bottom_menu/manage', $data);
    }

    /**
     * Retrieve the list of fixed bottom menu
     */
    public function ajax_fixed_bottom_menu_items()
    {
        $data = get_option(POLY_FIXED_BOTTOM_MENU);
        $data = $data ? json_decode($data, true) : [];
        if (!empty($data) && is_array($data)) {
            $data = array_values($data);
        }
        array_unshift($data, ['name' => 'Root', 'slug' => 'root', 'href' => '#', 'badge' => array('color' => '', 'value' => '')]);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Update the order of fixed bottom menu items.
     */
    public function update_fixed_bottom_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            update_option(POLY_FIXED_BOTTOM_MENU, json_encode($full_menu_items));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    /**
     * Delete a custom fixed bottom menu.
     */
    public function delete_fixed_bottom_menu()
    {
        if ($this->input->post()) {
            // JavaScript sends {id: slug} directly, same as other delete functions
            $slug = $this->input->post('id', FALSE);
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            // Fixed bottom menu uses options table (not database)
            $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_FIXED_BOTTOM_MENU), TRUE);
            
            if (!is_array($menu_items)) {
                $menu_items = [];
            }
            
            // Remove item recursively
            poly_utilities_menu_sidebar_delete($menu_items, $slug);
            
            // Re-index array after deletion to fix JSON encoding
            $menu_items = array_values($menu_items);
            
            // Save back to options
            update_option(POLY_FIXED_BOTTOM_MENU, json_encode($menu_items));
            
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_delete_success'));
            exit();
        }
    }

    /**
     * Clone a fixed bottom menu item.
     */
    public function clone_fixed_bottom_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            $slug = $menu_item['slug'] ?? $menu_item['id'] ?? null;
            
            if (!$slug) {
                poly_utilities_ajax_response_helper::response_error('Menu slug is required');
                exit();
            }
            
            // Fixed bottom menu uses options table (not database)
            $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_FIXED_BOTTOM_MENU), TRUE);
            
            if (!is_array($menu_items)) {
                $menu_items = [];
            }
            
            // Find item to clone
            $item_to_clone = null;
            foreach ($menu_items as $item) {
                if (isset($item['slug']) && $item['slug'] === $slug) {
                    $item_to_clone = $item;
                    break;
                }
            }
            
            if (!$item_to_clone) {
                poly_utilities_ajax_response_helper::response_error('Menu item not found');
                exit();
            }
            
            // Clone item with new slug and name
            $cloned_item = $item_to_clone;
            $cloned_item['slug'] = poly_generate_menu_slug();
            $cloned_item['name'] = $item_to_clone['name'] . ' (Copy)';
            $cloned_item['is_custom'] = 'true';
            
            // Insert cloned item right after the original item
            if (!poly_insert_cloned_menu_item($menu_items, $slug, $cloned_item)) {
                // Fallback: if not found (shouldn't happen), append to end
                $menu_items[] = $cloned_item;
            }
            
            // Save back to options
            update_option(POLY_FIXED_BOTTOM_MENU, json_encode($menu_items));
            
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_add_success'));
            exit();
        }
    }

    /**
     * Add or update a custom fixed bottom menu.
     */
    public function update_fixed_bottom_menu()
    {
        if ($this->input->post()) {

            $menu_item = $this->input->post(null, false);
            if ($menu_item) {
                $menu_item = poly_utilities_common_helper::clean_xss_except($menu_item, ['popup_description']);
            }

            if (!isset($menu_item['parent_slug'])) { //Fix lost parent slug; 
                $menu_item['parent_slug'] = 'root';
            }

            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            if ($isEdit !== 'true') { // Add new
                $menu_items_custom = get_option(POLY_FIXED_BOTTOM_MENU);
                $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);

                $menu_object = poly_create_menu_item($menu_item);
                $menu_items_custom[] = $menu_object;

                update_option(POLY_FIXED_BOTTOM_MENU, json_encode($menu_items_custom));

                $custom_items_position = get_option(POLY_FIXED_BOTTOM_MENU);

                if (!empty($custom_items_position)) {
                    $custom_items_position = poly_utilities_common_helper::json_decode($custom_items_position, TRUE);

                    if ($menu_object['parent_slug'] === 'root') { // Add first array when root
                        array_unshift($custom_items_position, $menu_object);
                        update_option(POLY_FIXED_BOTTOM_MENU, json_encode($custom_items_position));
                    } else {
                        $found = $this->poly_add_menu_item_to_parent($custom_items_position, $menu_object);
                        if ($found) {
                            update_option(POLY_FIXED_BOTTOM_MENU, json_encode($custom_items_position));
                        }
                    }
                }

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else { // Update
                $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_FIXED_BOTTOM_MENU), TRUE);

                if (isset($menu_item['icon']) && strpos($menu_item['icon'], 'svg') !== false) {
                    $menu_item['svg'] = $menu_item['icon'];
                    $menu_item['icon'] = 'menu-icon';
                }else{
                    $menu_item['svg'] = '';// Remove SVG;
                }

                poly_utilities_menu_sidebar_update($menu_items, $menu_item, true);
                update_option(POLY_FIXED_BOTTOM_MENU, json_encode($menu_items));

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                set_alert('success', _l('poly_utilities_response_update_success'));
            }
            exit();
        }
    }
    ////////////////////////////////////////////////////////////////////// END FIXED BOTTOM MENU //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// DISPLAY CUSTOM MENU //////////////////////////////////////////////////////////////////////
    public function details($slug)
    {
        if (!$slug) {
            show_404();
        }
        
        // Load menu from database directly to get full data including permissions
        $this->load->model('poly_utilities/custom_menu_model');
        $menu = $this->custom_menu_model->get_by_slug($slug, 'sidebar');
        
        if (empty($menu)) {
            $menu = $this->custom_menu_model->get_by_slug($slug, 'setup');
        }
        
        // If not found in database, try fixed bottom menu from options
        if (empty($menu)) {
            $fixed_bottom_menu = get_option(POLY_FIXED_BOTTOM_MENU);
            $fixed_bottom_menu_items = poly_utilities_common_helper::json_decode($fixed_bottom_menu, TRUE);
            
            if (!empty($fixed_bottom_menu_items) && is_array($fixed_bottom_menu_items)) {
                // Recursive function to find menu item by slug
                $findMenuItem = function($items, $targetSlug) use (&$findMenuItem) {
                    foreach ($items as $item) {
                        if (isset($item['slug']) && $item['slug'] === $targetSlug) {
                            return $item;
                        }
                        // Check children recursively
                        if (isset($item['children']) && is_array($item['children'])) {
                            $found = $findMenuItem($item['children'], $targetSlug);
                            if ($found) {
                                return $found;
                            }
                        }
                    }
                    return null;
                };
                
                $menu = $findMenuItem($fixed_bottom_menu_items, $slug);
                
                // If found in fixed bottom menu, set default permissions (empty arrays)
                if ($menu) {
                    $menu['users'] = isset($menu['users']) ? $menu['users'] : '[]';
                    $menu['roles'] = isset($menu['roles']) ? $menu['roles'] : '[]';
                }
            }
        }
        
        if (empty($menu)) {
            show_404();
        }
        
        // Load permissions from database (only if menu is from database)
        if (isset($menu['id']) && is_numeric($menu['id'])) {
            $permissions = $this->custom_menu_model->get_menu_permissions($menu['id']);
            
            // Convert permissions to the format expected by poly_utilities_is_access_menu_item
            $menu['users'] = isset($permissions['users']) && is_array($permissions['users']) 
                ? json_encode($permissions['users']) 
                : (isset($menu['users']) ? $menu['users'] : '[]');
            $menu['roles'] = isset($permissions['roles']) && is_array($permissions['roles']) 
                ? json_encode($permissions['roles']) 
                : (isset($menu['roles']) ? $menu['roles'] : '[]');
        }
        
        // Check roles/users access here
        $access = poly_utilities_is_access_menu_item($menu, $this->current_user_id);
        if (!$access && $this->current_user_id != 1) {
            access_denied();
            return; // Ensure no further processing
        }
        
        // Convert to view format
        $object = $menu;
        
        $data['custom_menu'] = $object;
        $data['title'] = isset($object['name']) ? $object['name'] : 'Untitled Menu';
        $this->load->view('custom_menu/details', $data);
    }
    ////////////////////////////////////////////////////////////////////// END DISPLAY CUSTOM MENU //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// BANNERS & ANNOUCEMENTS //////////////////////////////////////////////////////////////////////
    /**
     * Retrieve the list of banners settings
     */
    public function ajax_banners_settings()
    {
        $data = get_option(POLY_BANNERS_SETTINGS);
        $settings = $data ? json_decode($data, true) : [];
        if (!is_array($settings)) {
            $settings = [];
        }
        
        // Set default values if not set
        $settings['active'] = $settings['active'] ?? 1;
        $settings['transition_effect'] = $settings['transition_effect'] ?? 'fadeInOut';
        $settings['transition_effect_announcements'] = $settings['transition_effect_announcements'] ?? 'fadeInOut';
        
        header('Content-Type: application/json');
        echo json_encode($settings);
        exit();
    }

    /**
     * Update Banners Settings
     * @return view
     */
    public function update_banners_settings()
    {
        $post_data = $this->input->post();

        if (empty($post_data)) {
            poly_utilities_ajax_response_helper::response_error('Error');
            return;
        }
        
        // Get existing settings to preserve fields not sent in POST (like transition_effect)
        $existing_settings = get_option(POLY_BANNERS_SETTINGS);
        $existing_data = $existing_settings ? json_decode($existing_settings, true) : [];
        if (!is_array($existing_data)) {
            $existing_data = [];
        }
        
        // Merge existing data with POST data (POST data will override existing)
        $merged_data = array_merge($existing_data, $post_data);
        
        $boolean_fields = ['active', 'is_autoplay', 'is_controls', 'is_thumbnails', 'active_announcements', 'is_autoplay_announcements', 'is_controls_announcements'];
        foreach ($boolean_fields as $field) {
            $merged_data[$field] = isset($merged_data[$field]) && ($merged_data[$field] == 'on' || $merged_data[$field] == '1') ? 1 : 0;
        }
        
        // Set default transition effects if not set
        if (empty($merged_data['transition_effect'])) {
            $merged_data['transition_effect'] = 'fadeInOut';
        }
        if (empty($merged_data['transition_effect_announcements'])) {
            $merged_data['transition_effect_announcements'] = 'fadeInOut';
        }
        
        $result = update_option(POLY_BANNERS_SETTINGS, json_encode($merged_data));
        if ($result == true) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error('Error');
        }
    }

    /**
     * Retrieve the list of banners
     */
    public function ajax_adnnouncements()
    {
        $data = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $data = $data ? json_decode($data, true) : [];

        poly_utilities_common_helper::sortByFieldName($data, 'created');

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;

        $total_items = count($data);
        $total_pages = ceil($total_items / $items_per_page);

        $offset = ($page - 1) * $items_per_page;
        $paginated_data = array_slice($data, $offset, $items_per_page);

        $start_item = $offset + 1;
        $end_item = min($offset + $items_per_page, $total_items);

        $data_info = _l('poly_utilities_dt_info', [$start_item, $end_item, $total_items]) . ' ' . _l('poly_utilities_dt_entries');

        $response = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'banners' => $paginated_data,
            'data_info' => $data_info
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function banners_announcements_add()
    {
        $this->db->trans_begin();

        $widgets_area = $this->input->post('area');
        if (empty($widgets_area)) {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_banner_response_widgets_area_required'));
            return;
        }

        $id = $this->input->post('id');
        $active = $this->input->post('active');
        $active = ($active == 'on') ? 1 : 0;

        $announcement = array(
            'title' => $this->input->post('title'),
            'area' => $widgets_area,
            'date_from' => $this->input->post('date_from'),
            'date_to' => $this->input->post('date_to'),
            'url' => $this->input->post('url'),
            'target' => $this->input->post('target'),
            'rel' => $this->input->post('rel'),
            'active' => $active,
            'updated' => time()
        );

        $content = $this->input->post('content');

        if (!empty($content)) {
            $content = clear_textarea_breaks($content);
            $content = html_purify($content);
            $announcement['content'] = $content;
        }

        $announcements = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $announcements = poly_utilities_common_helper::json_decode($announcements, TRUE);

        if (!empty($id)) {
            foreach ($announcements as &$existingBanner) {
                if ($existingBanner['id'] == $id) {
                    $existingBanner = array_merge($existingBanner, $announcement);
                    $announcement = $existingBanner;
                    break;
                }
            }
        } else {
            $announcement['id'] = poly_utilities_common_helper::generateUniqueID();
            $announcement['created'] = time();

            $announcements[] = $announcement;
        }

        update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode($announcements));

        poly_utilities_banners_helper::media_by_areas($announcements, POLY_BANNERS_ANNOUNCEMENTS_AREA);

        $this->db->trans_commit();
        poly_utilities_ajax_response_helper::response_data(array('status' => 'success', 'code' => 200, 'message' => _l('poly_utilities_response_success'), 'data' => $announcement));
    }

    public function delete_announcement()
    {
        $id = $this->input->post('id', TRUE);

        if ($id === NULL) {
            poly_utilities_ajax_response_helper::response_error("ID is missing.");
            return;
        }

        $id = trim($id);
        $id = nl2br($id);

        $obj_storage = clear_textarea_breaks(get_option(POLY_BANNERS_ANNOUNCEMENTS));
        $obj_old_data = [];

        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'id', $id)) {
                $this->db->trans_begin();
                foreach ($obj_old_data as $key => $announcement) {
                    if ($announcement['id'] == $id) {
                        unset($obj_old_data[$key]);
                        break;
                    }
                }
                update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode(array_values($obj_old_data)));
                $this->db->trans_commit();
                poly_utilities_banners_helper::media_by_areas($obj_old_data, POLY_BANNERS_ANNOUNCEMENTS_AREA);
                poly_utilities_ajax_response_helper::response_success("Announcement with ID {$id} was successfully deleted.");
            } else {
                poly_utilities_ajax_response_helper::response_error("Announcement with ID {$id} does not exist.");
            }
        } else {
            poly_utilities_ajax_response_helper::response_error("No announcement data available to delete.");
        }
    }

    public function update_announcement_status()
    {
        $data = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $announcements = json_decode($data, true);

        $status = $this->input->post('active');
        $id = $this->input->post('id');

        if ($id !== NULL) {
            foreach ($announcements as &$announcement) {
                if ($announcement['id'] == $id) {
                    $announcement['active'] = $status;
                    break;
                }
            }

            $updated = update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode($announcements));

            if ($updated) {
                poly_utilities_banners_helper::media_by_areas($announcements, POLY_BANNERS_ANNOUNCEMENTS_AREA);
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                return;
            }
        }

        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_resource_access_error'));
    }

    /**
     * Retrieve the list of banners
     */
    public function ajax_banners()
    {
        $data = get_option(POLY_BANNERS);
        $data = $data ? json_decode($data, true) : [];

        poly_utilities_common_helper::sortByFieldName($data, 'created');

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;

        $total_items = count($data);
        $total_pages = ceil($total_items / $items_per_page);

        $offset = ($page - 1) * $items_per_page;
        $paginated_data = array_slice($data, $offset, $items_per_page);

        $start_item = $offset + 1;
        $end_item = min($offset + $items_per_page, $total_items);

        $data_info = _l('poly_utilities_dt_info', [$start_item, $end_item, $total_items]) . ' ' . _l('poly_utilities_dt_entries');

        $response = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'banners' => $paginated_data,
            'data_info' => $data_info
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function ajax_banners_announcements()
    {
        $data = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $data = $data ? json_decode($data, true) : [];

        poly_utilities_common_helper::sortByFieldName($data, 'created');

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;

        $total_items = count($data);
        $total_pages = ceil($total_items / $items_per_page);

        $offset = ($page - 1) * $items_per_page;
        $paginated_data = array_slice($data, $offset, $items_per_page);

        $start_item = $offset + 1;
        $end_item = min($offset + $items_per_page, $total_items);

        $data_info = _l('poly_utilities_dt_info', [$start_item, $end_item, $total_items]) . ' ' . _l('poly_utilities_dt_entries');

        $response = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'banners' => $paginated_data,
            'data_info' => $data_info
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function update_banner_status()
    {
        $data = get_option(POLY_BANNERS);
        $banners = json_decode($data, true);

        $status = $this->input->post('active');
        $id = $this->input->post('id');

        if ($id !== NULL) {
            foreach ($banners as &$banner) {
                if ($banner['id'] == $id) {
                    $banner['active'] = $status;
                    break;
                }
            }

            $updated = update_option(POLY_BANNERS, json_encode($banners));

            if ($updated) {
                poly_utilities_banners_helper::media_by_areas($banners);
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                return;
            }
        }

        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_resource_access_error'));
    }

    public function delete_banner()
    {
        $id = $this->input->post('id', TRUE);

        if ($id === NULL) {
            poly_utilities_ajax_response_helper::response_error("ID is missing.");
            return;
        }

        $id = trim($id);
        $id = nl2br($id);

        $obj_storage = clear_textarea_breaks(get_option(POLY_BANNERS));
        $obj_old_data = [];

        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'id', $id)) {
                $this->db->trans_begin();
                foreach ($obj_old_data as $key => $banner) {
                    if ($banner['id'] == $id) {
                        if (!empty($banner['media'])) {
                            if (!poly_utilities_common_helper::deleteOldFiles($banner['media'])) {
                                $this->db->trans_rollback();
                                poly_utilities_ajax_response_helper::response_error("Failed to delete media files for banner with ID {$id}.");
                                return;
                            }
                        }
                        unset($obj_old_data[$key]);
                        break;
                    }
                }
                update_option(POLY_BANNERS, json_encode(array_values($obj_old_data)));
                $this->db->trans_commit();
                poly_utilities_banners_helper::media_by_areas($obj_old_data);
                poly_utilities_ajax_response_helper::response_success("Banner with ID {$id} was successfully deleted.");
            } else {
                poly_utilities_ajax_response_helper::response_error("Banner with ID {$id} does not exist.");
            }
        } else {
            poly_utilities_ajax_response_helper::response_error("No banner data available to delete.");
        }
    }

    public function banners_add()
    {
        $this->db->trans_begin(); // Start transaction

        $widgets_area = $this->input->post('area');
        if (empty($widgets_area)) {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_banner_response_widgets_area_required'));
            return;
        }

        $id = $this->input->post('id');
        $filesIDS = [];
        $errors = [];
        $path = POLY_UTILITIES_MODULE_UPLOAD_MEDIA_FOLDER;

        // Handle file upload
        if (
            isset($_FILES['file']['name'])
            && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)
        ) {
            if (!is_array($_FILES['file']['name'])) {
                $_FILES['file']['name']     = [$_FILES['file']['name']];
                $_FILES['file']['type']     = [$_FILES['file']['type']];
                $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
                $_FILES['file']['error']    = [$_FILES['file']['error']];
                $_FILES['file']['size']     = [$_FILES['file']['size']];
            }

            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                    $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);
                    continue;
                }

                $tmpFilePath = $_FILES['file']['tmp_name'][$i];

                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);

                    $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                    $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);

                    if (!_upload_extension_allowed($filename)) {
                        $errors[$_FILES['file']['name'][$i]] = 'File extension not allowed';
                        continue;
                    }

                    $newFilePath = rtrim($path, '/') . '/' . $filename;

                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $filesIDS[] = rtrim(POLY_UTILITIES_MEDIA_PATH, '/') . '/' . $filename;
                    }
                }
            }
        }

        $active = $this->input->post('active');
        $active = ($active == 'on') ? 1 : 0;

        $banner = array(
            'title' => $this->input->post('title'),
            'area' => $widgets_area,
            'date_from' => $this->input->post('date_from'),
            'date_to' => $this->input->post('date_to'),
            'url' => $this->input->post('url'),
            'target' => $this->input->post('target'),
            'rel' => $this->input->post('rel'),
            'active' => $active,
            'updated' => time()
        );

        $embed = $this->input->post('embed');
        $media = $this->input->post('media');

        if (!empty($embed)) {
            $embed = clear_textarea_breaks($embed);
            $embed = html_purify($embed);
            $banner['embed'] = $embed;
        }

        // Process media input if it's not a file upload
        if (!empty($media) && empty($_FILES['file']['name'])) {
            // Assume media is a URL if it's not a file
            $banner['media'] = filter_var($media, FILTER_VALIDATE_URL) ? $media : null;
        }

        $banners = get_option(POLY_BANNERS);
        $banners = poly_utilities_common_helper::json_decode($banners, TRUE);

        if (!empty($id)) {
            // Update existing banner
            foreach ($banners as &$existingBanner) {
                if ($existingBanner['id'] == $id) {
                    if (!empty($filesIDS)) {
                        if (!empty($existingBanner['media']) && $existingBanner['media'] != $media) {
                            if (!poly_utilities_common_helper::deleteOldFiles($existingBanner['media'])) {
                                $this->db->trans_rollback();
                                poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
                                return;
                            }
                        }
                        $banner['media'] = $filesIDS;
                    } elseif (!empty($media)) {
                        if (!empty($existingBanner['media']) && $existingBanner['media'] != $media) {
                            if (!filter_var($media, FILTER_VALIDATE_URL)) {
                                poly_utilities_common_helper::deleteOldFiles($existingBanner['media']);
                            }
                        }
                        $banner['media'] = $media;
                    } else {
                        $banner['media'] = $existingBanner['media'];
                    }
                    $existingBanner = array_merge($existingBanner, $banner);
                    $banner = $existingBanner; // Update banner with the latest information
                    break;
                }
            }
        } else {
            // Create new banner
            $banner['id'] = poly_utilities_common_helper::generateUniqueID();
            $banner['created'] = time();
            if (!empty($filesIDS)) {
                $banner['media'] = $filesIDS[0]; // Assign the first uploaded file
            } elseif (!empty($media)) {
                $banner['media'] = filter_var($media, FILTER_VALIDATE_URL) ? $media : null;
            }

            $banners[] = $banner;
        }

        // Update banners option with the new or updated banner
        update_option(POLY_BANNERS, json_encode($banners));

        poly_utilities_banners_helper::media_by_areas($banners);

        $this->db->trans_commit();
        poly_utilities_ajax_response_helper::response_data(array('status' => 'success', 'code' => 200, 'message' => _l('poly_utilities_response_success'), 'data' => $banner));

        if (!empty($filesIDS)) {
            echo json_encode(['files' => $filesIDS]);
        } else {
            echo json_encode(['errors' => $errors]);
            $this->db->trans_rollback();
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
        }
    }
    ////////////////////////////////////////////////////////////////////// END BANNERS & ANNOUCEMENTS //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// APPEARANCE //////////////////////////////////////////////////////////////////////
    public function appearance_update() {

        if ($this->input->post()) {
            $post_data        = $this->input->post();

        $filesToUpload = [
            'login_background' => $_FILES['login_background'] ?? null,
            'dashboard_background' => $_FILES['dashboard_background'] ?? null,
        ];
    
        $filesIDS = [];
        $errors = [];
        $path = POLY_UTILITIES_MODULE_UPLOAD_APPEARANCE_FOLDER;
    
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    
        foreach ($filesToUpload as $key => $file) {
            if (!$file || !isset($file['name']) || $file['name'] == '') {
                continue;
            }
    
            if (!is_array($file['name'])) {
                $file['name']     = [$file['name']];
                $file['type']     = [$file['type']];
                $file['tmp_name'] = [$file['tmp_name']];
                $file['error']    = [$file['error']];
                $file['size']     = [$file['size']];
            }
    
            for ($i = 0; $i < count($file['name']); $i++) {
                if (_perfex_upload_error($file['error'][$i])) {
                    $errors[$file['name'][$i]] = _perfex_upload_error($file['error'][$i]);
                    continue;
                }
    
                $tmpFilePath = $file['tmp_name'][$i];
    
                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    if (!is_writable($path)) {
                        $errors[$file['name'][$i]] = 'Upload directory is not writable. Please check permissions.';
                        continue;
                    }
    
                    _maybe_create_upload_path($path);
    
                    $originalFilename = unique_filename($path, $file['name'][$i]);
                    $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);
    
                    if (!_upload_extension_allowed($filename)) {
                        $errors[$file['name'][$i]] = 'File extension not allowed';
                        continue;
                    }
    
                    $newFilePath = rtrim($path, '/') . '/' . $filename;
    
                    if (!move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $errors[$file['name'][$i]] = 'Failed to move uploaded file. Please check permissions.';
                    } else {
                        $filesIDS[$key] = rtrim(POLY_UTILITIES_MODULE_UPLOAD_APPEARANCE_PATH, '/') . '/' . $filename;
                    }
                }
            }
        }
    
        $existingAppearance = poly_utilities_common_helper::json_decode(get_option(POLY_UTILITIES_APPEARANCE_SETTINGS) ?? '', true);
        
        $appearance = [
            'login_background' => $filesIDS['login_background'] ?? ($existingAppearance['login_background'] ?? null),
            'dashboard_background' => $filesIDS['dashboard_background'] ?? ($existingAppearance['dashboard_background'] ?? null),
            'login_page_background_color' => $this->input->post('login_page_background_color') ?? ($existingAppearance['login_page_background_color'] ?? null),
            'login_page_text_color' => $this->input->post('login_page_text_color') ?? ($existingAppearance['login_page_text_color'] ?? null),
            'effect' => $this->input->post('effect') ?? ($existingAppearance['effect'] ?? null),
            'effect_content' => $this->input->post('effect_content') ?? ($existingAppearance['effect_content'] ?? null),
            'active' => isset($post_data['active']) && ($post_data['active'] == 'on' || $post_data['active'] == '1') ? 1 : 0,
            'active_dashboard_background' => isset($post_data['active_dashboard_background']) && ($post_data['active_dashboard_background'] == 'on' || $post_data['active_dashboard_background'] == '1') ? 1 : 0,
            'active_login_background' => isset($post_data['active_login_background']) && ($post_data['active_login_background'] == 'on' || $post_data['active_login_background'] == '1') ? 1 : 0
        ];
    
        $updatedAppearance = array_merge($existingAppearance ?? [], $appearance);
    
        update_option(POLY_UTILITIES_APPEARANCE_SETTINGS, json_encode($updatedAppearance));
    
        if (!empty($errors)) {
            poly_utilities_ajax_response_helper::response_error('Please check file permissions or contact the administrator if the issue persists.');
        } else {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }
    }
    ////////////////////////////////////////////////////////////////////// END APPEARANCE //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// MODULE ACTIONS //////////////////////////////////////////////////////////////////////
    public function ajax_modules_activate()
    {
        if ($this->input->post('data')) {
            $data = $this->input->post('data');
            $action = isset($data['action']) ? $data['action'] : '';
            $modules = isset($data['modules']) ? $data['modules'] : [];

            $modules = array_filter($modules, function ($module_name) {
                return $module_name !== 'poly_utilities';
            });

            $CI = &get_instance(); //poly_utilities

            if ($action === 'activate') {
                foreach ($modules as $module_name) {
                    $CI->app_modules->activate($module_name);
                }
            } elseif ($action === 'deactivate') {
                foreach ($modules as $module_name) {
                    $CI->app_modules->deactivate($module_name);
                }
            } else {
                poly_utilities_ajax_response_helper::response_error(_l('Invalid action'), 400);
                return;
            }
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('No data provided'), 400);
        }
    }
   ////////////////////////////////////////////////////////////////////// END MODULE ACTIONS //////////////////////////////////////////////////////////////////////

   ////////////////////////////////////////////////////////////////////// PROJECTS & TASKS //////////////////////////////////////////////////////////////////////
    public function delete_project_name_pattern($id)
    {
        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error('ID is required.');
        }

        $deleted = $this->project_name_patterns_model->delete_project_name_pattern($id);

        if ($deleted) {
            poly_utilities_ajax_response_helper::response_success('Project name pattern deleted successfully.');
        } else {
            poly_utilities_ajax_response_helper::response_error('Project name pattern not found.');
        }
    }

    public function get_project_name_patterns()
    {
        $isActive = $this->input->get('active') ?? null;
        if ($isActive !== null) {
            $isActive = $isActive === '1' ? true : false;
        }

        $data = $this->project_name_patterns_model->get_all($isActive) ?? [];
        $data = array_values($data);
        $total_items = count($data);

        $response = [
            'code' => 200,
            'total_items' => $total_items,
            'data' => $data,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function project_name_pattern_add()
    {
        if ($this->input->post()) {
            $post_data        = $this->input->post();

            $name = $post_data['name'];
            $note = $post_data['note'];

            $active = poly_utilities_cast_boolean_flag($post_data['active'] ?? null, false) ? 1 : 0;

            if (empty($name)) {
                poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_projects_field_name_validate'));
            }

            if (!$this->input->post('id')) { // Add new

                if ($this->project_name_patterns_model->is_existed($name)) {
                    poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
                }

                $created_by =  $this->current_user_id;
                $updated_by =  $created_by;

                $this->project_name_patterns_model->add($name, $note, $active, $created_by, $updated_by);
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_add_success'));
            } else { // Update
                $id = $post_data['id'];
                unset($post_data['id']);
                $updated_by = $this->current_user_id;
                $is_update = $this->project_name_patterns_model->update($id, $name, $note, $active, $updated_by);
                if ($is_update) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_unsuccess'));
                }
            }
        }
    }

    public function update_pattern_status()
    {
        if ($this->input->post() && $this->input->post('id')) {

            $post_data  = $this->input->post();

            $created_by =  $this->current_user_id;
            $updated_by =  $created_by;

            $id = $post_data['id'];
            unset($post_data['id']);
            $active = poly_utilities_cast_boolean_flag($post_data['active'] ?? null, false) ? 1 : 0;

            $updated_by = $this->current_user_id;

            $is_update = $this->project_name_patterns_model->update($id, null, null, $active, $updated_by);
            if ($is_update) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
            } else {
                poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_unsuccess'));
            }
        }
        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
    }

    public function update_project_name_patterns_order()
    {
        if (!$this->input->is_ajax_request()) {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
        }

        if (!staff_can_poly_utilities()) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $orderRaw = $this->input->post('order');

        if (empty($orderRaw)) {
            poly_utilities_ajax_response_helper::response_no_data_received();
        }

        $orderData = is_array($orderRaw) ? $orderRaw : json_decode($orderRaw, true);

        if (!is_array($orderData)) {
            poly_utilities_ajax_response_helper::response_invalid_json();
        }

        $updated = $this->project_name_patterns_model->update_orders($orderData);

        if ($updated) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
        }

        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_unsuccess'));
    }

    // Add estimate
    public function add_estimate($id = '')
    {
        if ($this->input->post()) {
            $estimate_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($estimate_data['save_and_send_later'])) {
                unset($estimate_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if ($id == '') {
                if (staff_cant('create', 'estimates')) {
                    access_denied('estimates');
                }
                $id = $this->estimates_model->add($estimate_data);

                if ($id) {
                    set_alert('success', _l('added_successfully', _l('estimate')));

                    $redUrl = admin_url('estimates/list_estimates/' . $id);

                    if ($save_and_send_later) {
                        $this->session->set_userdata('send_later', true);
                        // die(redirect($redUrl));
                    }

                    redirect(
                        !$this->set_estimate_pipeline_autoload($id) ? $redUrl : admin_url('estimates/list_estimates/')
                    );
                }
            }
        }
        if ($id == '') {
            $title = _l('create_new_estimate');
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }

        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();
        $data['title']             = $title;
        $this->load->view('admin/estimates/estimate', $data);
    }

    public function set_estimate_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if (
            $this->session->has_userdata('estimate_pipeline')
            && $this->session->userdata('estimate_pipeline') == 'true'
        ) {
            $this->session->set_flashdata('estimateid', $id);

            return true;
        }

        return false;
    }
    // END Add estimate

    // Add proposal
    public function add_proposal($id = '')
    {
        if ($this->input->post()) {
            $proposal_data = $this->input->post();
            if ($id == '') {
                if (staff_cant('create', 'proposals')) {
                    access_denied('proposals');
                }
                $id = $this->proposals_model->add($proposal_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('proposal')));
                    if ($this->set_proposal_pipeline_autoload($id)) {
                        redirect(admin_url('proposals'));
                    } else {
                        redirect(admin_url('proposals/list_proposals/' . $id));
                    }
                }
            }
        }
    }
    public function set_proposal_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('proposals_pipeline') && $this->session->userdata('proposals_pipeline') == 'true') {
            $this->session->set_flashdata('proposalid', $id);

            return true;
        }

        return false;
    }
    // END Add proposal

    /* Edit contract or add new contract */
    public function add_contract($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (staff_cant('create', 'contracts')) {
                    access_denied('contracts');
                }
                $id = $this->contracts_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('contract')));
                    redirect(admin_url('contracts/contract/' . $id));
                }
            } else {
                if (staff_cant('edit', 'contracts')) {
                    access_denied('contracts');
                }
                $contract = $this->contracts_model->get($id);
                $data     = $this->input->post();

                if ($contract->signed == 1) {
                    unset($data['contract_value'], $data['clientid'], $data['datestart'], $data['dateend']);
                }

                $success = $this->contracts_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('contract')));
                }
                redirect(admin_url('contracts/contract/' . $id));
            }
        }

        if ($id == '') {
            $title = _l('add_new', _l('contract_lowercase'));
        } else {
            $data['contract']                 = $this->contracts_model->get($id, [], true);
            $data['contract_renewal_history'] = $this->contracts_model->get_contract_renewal_history($id);
            $data['totalNotes']               = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'contract']);
            if (!$data['contract'] || (staff_cant('view', 'contracts') && $data['contract']->addedfrom != get_staff_user_id())) {
                blank_page(_l('contract_not_found'));
            }

            $data['contract_merge_fields'] = $this->app_merge_fields->get_flat('contract', ['other', 'client'], '{email_signature}');

            $title = $data['contract']->subject;

            $data = array_merge($data, prepare_mail_preview_data('contract_send_to_customer', $data['contract']->client));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['types']         = $this->contracts_model->get_contract_types();
        $data['title']         = $title;
        $this->load->view('admin/contracts/contract', $data);
    }
    ////////////////////////////////////////////////////////////////////// END PROJECTS & TASKS //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// REORDER COLUMNS //////////////////////////////////////////////////////////////////////
     /**
     * Update the custom reorder columns display.
     * @return view
     */
    public function save_reorder_columns()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);

            $obj = new stdClass();
            $keyReorder = $objs['key'];
            $indexReorder = $objs['value'];

            $obj->key = $keyReorder;
            $obj->value = $indexReorder;

            //poly_utilities_ajax_response_helper::response_success(json_encode($obj));//TEST

            $columnsReorder = get_option(POLY_TABLE_COLUMNS_REORDER);
            $dataTaleReorders = [];
            if (!empty($columnsReorder)) {
                $dataTaleReorders = json_decode($columnsReorder, true);
                //Update
                if (poly_utilities_common_helper::isExisted($dataTaleReorders, 'key', $obj->key)) {
                    $dataTaleReorders = poly_utilities_common_helper::updateDataByField($dataTaleReorders, 'key', $obj->key, $obj);
                    if (update_option(POLY_TABLE_COLUMNS_REORDER, json_encode($dataTaleReorders)) === true) {

                        // Reset all data table filters that contain the existence of $keyReorder
                        poly_utilities_common_helper::reset_columns_display($keyReorder, POLY_TABLE_FILTERS, true);

                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_response_error'));
                    }
                }
            }

            $dataTaleReorders[] = $obj;
            if (update_option(POLY_TABLE_COLUMNS_REORDER, json_encode($dataTaleReorders)) === true) {

                // Reset all data table filters that contain the existence of $keyReorder
                poly_utilities_common_helper::reset_columns_display($keyReorder, POLY_TABLE_FILTERS, true);

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            } else {
                poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_response_error'));
            }
        }
    }
    public function reset_columns_reorder()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);
            if (!empty($objs['key'])) {
                poly_utilities_common_helper::reset_columns_display($objs['key'], POLY_TABLE_COLUMNS_REORDER);

                poly_utilities_common_helper::reset_columns_display($objs['key'], POLY_TABLE_FILTERS, true);

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        }
        poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_response_error'));
    }

    public function reset_all_columns_reorder()
    {
        if (!$this->input->post()) {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_response_error'));
        }

        if (!is_admin()) {
            poly_utilities_ajax_response_helper::response_unauthorize();
        }

        update_option(POLY_TABLE_COLUMNS_REORDER, '');
        update_option(POLY_TABLE_FILTERS, '');
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }
    ////////////////////////////////////////////////////////////////////// END REORDER COLUMNS //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// SETTINGS //////////////////////////////////////////////////////////////////////
    public function export_settings()
    {
        if (!is_admin()) {
             poly_utilities_ajax_response_helper::response_unauthorize();
        }
        
        $settings = $this->getPolyUtilitiesSettings();
        
        // Add metadata to identify export type
        $export_data = [
            'export_type' => 'all_settings',
            'export_date' => date('Y-m-d H:i:s'),
        ] + $settings;
        
        $json_data = json_encode($export_data);

        $domain = $_SERVER['HTTP_HOST'];
        $version = defined('POLY_UTILITIES_VERSION') ? POLY_UTILITIES_VERSION : 'unknown';
        $date = date('Y-m-d');
        $filename = sprintf('%s_polyutilities_settings_%s-v%s.json', $domain, $date, $version);
    
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json_data));
    
        echo $json_data;
        exit;
    }

    public function getPolyUtilitiesSettings() {
        function fetchOption($option_name, $default = []) {
            $raw_data = get_option($option_name);
            if (!$raw_data) {
                return $default;
            }
    
            $decoded_data = json_decode($raw_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                //error_log("JSON Error in $option_name: " . json_last_error_msg());
                return $default;
            }
    
            return $decoded_data;
        }

        $poly_utilities_settings = fetchOption(POLY_UTILITIES_SETTINGS);
        $poly_context_menu = fetchOption(POLY_CONTEXT_MENU);
        
        // Get menus from database instead of options
        $this->load->model('poly_utilities/custom_menu_model');
        $menus_from_db = $this->getMenusFromDatabase();
    
        return [
            'poly_utilities_settings' => $poly_utilities_settings,
            'menus' => [
                'sidebar' => $menus_from_db['sidebar'] ?? [],
                'setup' => $menus_from_db['setup'] ?? [],
                'clients' => $menus_from_db['clients'] ?? [],
                'context' => [
                    'data' => $poly_context_menu,
                ],
            ],
        ];
    }
    
    /**
     * Get all menus from database table tblpoly_utilities_custom_menus
     * @return array Array of menus grouped by menu_type
     */
    private function getMenusFromDatabase() {
        $menus = [];
        $menu_types = ['sidebar', 'setup', 'clients'];
        
        // Check if table exists
        if (!$this->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return $menus;
        }
        
        foreach ($menu_types as $type) {
            // Get all menu items from database
            $query = $this->db->select('*')
                ->from(db_prefix() . 'poly_utilities_custom_menus')
                ->where('menu_type', $type)
                ->order_by('position', 'ASC')
                ->order_by('level', 'ASC')
                ->get();
            
            $menu_items = $query->result_array();
            
            // Process href to remove current domain
            foreach ($menu_items as &$item) {
                if (!empty($item['href'])) {
                    $item['href'] = $this->removeDomainFromHref($item['href']);
                }
                if (!empty($item['href_original'])) {
                    $item['href_original'] = $this->removeDomainFromHref($item['href_original']);
                }
            }
            
            $menus[$type] = $menu_items;
        }
        
        return $menus;
    }
    
    /**
     * Remove current domain from href to make it portable
     * @param string $href Original href with domain
     * @return string Href without domain
     */
    private function removeDomainFromHref($href) {
        if (empty($href)) {
            return $href;
        }
        
        // Get current domain
        $current_domain = rtrim(base_url(), '/');
        
        // Remove current domain from href
        if (strpos($href, $current_domain) === 0) {
            $href = substr($href, strlen($current_domain));
            // Ensure it starts with / for proper URL structure
            if (!empty($href) && $href[0] !== '/') {
                $href = '/' . $href;
            }
        }
        
        return $href;
    }
    
    /**
     * Add current domain to href to make it functional
     * @param string $href Href without domain
     * @return string Href with current domain
     */
    private function addDomainToHref($href) {
        if (empty($href)) {
            return $href;
        }
        
        // If href already has domain, return as is
        if (strpos($href, 'http://') === 0 || strpos($href, 'https://') === 0) {
            return $href;
        }
        
        // Get current domain
        $current_domain = rtrim(base_url(), '/');
        
        // Ensure href starts with /
        if (!empty($href) && $href[0] !== '/') {
            $href = '/' . $href;
        }
        
        return $current_domain . $href;
    }

    public function import_settings()
    {
        if (!is_admin()) {
            poly_utilities_ajax_response_helper::response_unauthorize();
        }
    
        if (empty($_FILES['file']['tmp_name'])) {
            poly_utilities_ajax_response_helper::response_no_data_received();
        }
    
        // Check if uploaded file exists and is readable
        if (!file_exists($_FILES['file']['tmp_name']) || !is_readable($_FILES['file']['tmp_name'])) {
            poly_utilities_ajax_response_helper::response_failed('Uploaded file is not accessible.');
            return;
        }
    
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        if ($fileContent === false) {
            poly_utilities_ajax_response_helper::response_failed('Cannot read uploaded file.');
            return;
        }
        
        $settings = json_decode($fileContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            poly_utilities_ajax_response_helper::response_invalid_json();
        }

        try {
            if (isset($settings['poly_utilities_settings'])) {
                $settings_data = json_encode($settings['poly_utilities_settings']);
                update_option(POLY_UTILITIES_SETTINGS, clear_textarea_breaks($settings_data));
            }

            if (isset($settings['menus'])) {
                // Import menus from database format
                foreach ($settings['menus'] as $menuType => $menuData) {
                    switch ($menuType) {
                        case 'sidebar':
                        case 'setup':
                        case 'clients':
                            // Import menus to database
                            if (!empty($menuData) && is_array($menuData)) {
                                $this->importMenusToDatabase($menuType, $menuData);
                            }
                            break;
                        case 'context':
                            if (isset($menuData['data'])) {
                                update_option(POLY_CONTEXT_MENU, json_encode($menuData['data']));
                            }
                            break;

                        default:
                            break;
                    }
                }
            }

            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } catch (Exception $e) {
            poly_utilities_ajax_response_helper::response_failed($e->getMessage());
        }
    }
    
    /**
     * Import menus to database (truncate and insert)
     * @param string $menu_type Menu type (sidebar, setup, clients)
     * @param array $menu_data Array of menu items
     */
    private function importMenusToDatabase($menu_type, $menu_data) {
        // Check if table exists
        if (!$this->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        $this->db->trans_start();
        
        // Truncate menus for this menu_type
        $this->db->where('menu_type', $menu_type);
        $this->db->delete(db_prefix() . 'poly_utilities_custom_menus');
        
        // Create mapping from old_id to new_id
        $id_mapping = [];
        
        // Sort menu items: parent first (parent_id = null or parent_id not in list), then children
        $sorted_items = $this->sortMenuItemsByHierarchy($menu_data);
        
        // Insert menu items with proper parent_id mapping
        foreach ($sorted_items as $menu_item) {
            $old_id = $menu_item['id'] ?? null;
            $old_parent_id = $menu_item['parent_id'] ?? null;
            
            // Remove id, created_at, updated_at for fresh insert
            unset($menu_item['id'], $menu_item['created_at'], $menu_item['updated_at']);
            
            // Map old parent_id to new parent_id
            if (!empty($old_parent_id) && isset($id_mapping[$old_parent_id])) {
                $menu_item['parent_id'] = $id_mapping[$old_parent_id];
                
                // Also update parent_slug from mapping
                $parent_menu = $this->db->select('slug')
                    ->from(db_prefix() . 'poly_utilities_custom_menus')
                    ->where('id', $id_mapping[$old_parent_id])
                    ->get()
                    ->row_array();
                    
                if ($parent_menu) {
                    $menu_item['parent_slug'] = $parent_menu['slug'];
                }
            } else {
                // No parent or parent not found, set to root
                $menu_item['parent_id'] = null;
                $menu_item['parent_slug'] = 'root';
            }
            
            // Process href to add current domain
            if (!empty($menu_item['href'])) {
                $menu_item['href'] = $this->addDomainToHref($menu_item['href']);
            }
            if (!empty($menu_item['href_original'])) {
                $menu_item['href_original'] = $this->addDomainToHref($menu_item['href_original']);
            }
            
            // Insert menu item
            $this->db->insert(db_prefix() . 'poly_utilities_custom_menus', $menu_item);
            $new_id = $this->db->insert_id();
            
            // Store mapping from old_id to new_id
            if ($old_id !== null) {
                $id_mapping[$old_id] = $new_id;
            }
        }
        
        $this->db->trans_complete();
        
        if (!$this->db->trans_status()) {
            throw new Exception("Failed to import menus for {$menu_type}");
        }
    }
    
    /**
     * Sort menu items by hierarchy (parent first, then children)
     * @param array $menu_items Array of menu items
     * @return array Sorted menu items
     */
    private function sortMenuItemsByHierarchy($menu_items) {
        $sorted = [];
        $remaining = $menu_items;
        $processed_ids = [];
        
        // Maximum iterations to prevent infinite loop
        $max_iterations = count($menu_items) * 2;
        $iteration = 0;
        
        while (!empty($remaining) && $iteration < $max_iterations) {
            $iteration++;
            $found_in_this_pass = false;
            
            foreach ($remaining as $key => $item) {
                $item_id = $item['id'] ?? null;
                $parent_id = $item['parent_id'] ?? null;
                
                // Add item if:
                // 1. It has no parent (root level)
                // 2. Its parent has already been processed
                if ($parent_id === null || in_array($parent_id, $processed_ids)) {
                    $sorted[] = $item;
                    if ($item_id !== null) {
                        $processed_ids[] = $item_id;
                    }
                    unset($remaining[$key]);
                    $found_in_this_pass = true;
                }
            }
            
            // If no items were processed in this pass, add remaining items anyway
            // (handles orphaned items or circular references)
            if (!$found_in_this_pass && !empty($remaining)) {
                foreach ($remaining as $item) {
                    $sorted[] = $item;
                }
                break;
            }
        }
        
        return $sorted;
    }

    /**
     * Sync Custom Menus from Options to Database
     * For legacy sites that have menus in options, sync custom menus to database
     */
    public function sync_custom_menus_from_options_to_database()
    {
        if (!is_admin()) {
            poly_utilities_ajax_response_helper::response_unauthorize();
            return;
        }
        
        try {
            // Check if table exists
            if (!$this->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
                poly_utilities_ajax_response_helper::response_failed('Database table does not exist.');
                return;
            }
            
            $this->db->trans_start();
            
            $total_synced = 0;
            $menu_types = ['sidebar', 'setup', 'clients'];
            
            foreach ($menu_types as $menu_type) {
                // Get menus from options
                $option_key = $this->getOptionKeyForMenuType($menu_type);
                $menus_json = get_option($option_key);
                
                if (empty($menus_json)) {
                    continue;
                }
                
                $menus_data = json_decode($menus_json, true);
                if (empty($menus_data) || !is_array($menus_data)) {
                    continue;
                }
                
                // Flatten nested structure and filter custom menus
                $flat_menus = $this->flattenMenusFromOptions($menus_data);
                
                // Sync all menus (both custom and system) to fix is_custom values
                foreach ($flat_menus as $menu_item) {
                    // Prepare data for database
                    $db_data = $this->mapOptionsMenuToDatabase($menu_item, $menu_type);
                    
                    // Reset query builder
                    $this->db->reset_query();
                    
                    // Check if menu already exists (by slug and menu_type)
                    $existing = $this->db->select('id, is_custom')
                        ->from(db_prefix() . 'poly_utilities_custom_menus')
                        ->where('slug', $db_data['slug'])
                        ->where('menu_type', $menu_type)
                        ->get()
                        ->row_array();
                    
                    if ($existing) {
                        // Update existing menu to fix is_custom value
                        $update_data = [
                            'is_custom' => $db_data['is_custom'],
                            'name' => $db_data['name'],
                            'href' => $db_data['href'],
                            'icon' => $db_data['icon'],
                            'type' => $db_data['type'],
                            'target' => $db_data['target'],
                            'rel' => $db_data['rel'],
                            'css' => $db_data['css'],
                            'disabled' => $db_data['disabled'],
                            'badge_value' => $db_data['badge_value'],
                            'badge_color' => $db_data['badge_color'],
                            'popup_description' => $db_data['popup_description']
                        ];
                        
                        $this->db->where('id', $existing['id']);
                        $this->db->update(db_prefix() . 'poly_utilities_custom_menus', $update_data);
                        $total_synced++;
                    } else {
                        // Insert new menu
                        $this->db->insert(db_prefix() . 'poly_utilities_custom_menus', $db_data);
                        $total_synced++;
                    }
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                poly_utilities_ajax_response_helper::response_failed('Failed to sync menus to database.');
                return;
            }
            
            $message = sprintf(
                'Successfully synced %d menu item(s) to database (inserted new items and updated existing items with correct is_custom values).',
                $total_synced
            );
            
            poly_utilities_ajax_response_helper::response_success($message);
            
        } catch (Exception $e) {
            poly_utilities_ajax_response_helper::response_failed('Sync failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get option key for menu type
     * @param string $menu_type
     * @return string
     */
    private function getOptionKeyForMenuType($menu_type)
    {
        $option_keys = [
            'sidebar' => POLY_MENU_SIDEBAR_CUSTOM_ACTIVE,
            'setup' => POLY_MENU_SETUP_CUSTOM_ACTIVE,
            'clients' => POLY_MENU_CLIENTS_CUSTOM_ACTIVE
        ];
        
        return $option_keys[$menu_type] ?? '';
    }
    
    /**
     * Flatten nested menu structure from options
     * @param array $menus
     * @return array
     */
    private function flattenMenusFromOptions($menus)
    {
        $flat = [];
        
        foreach ($menus as $menu) {
            // Add parent menu (remove children before adding)
            $parent = $menu;
            if (isset($parent['children'])) {
                $children = $parent['children'];
                unset($parent['children']);
            } else {
                $children = [];
            }
            $flat[] = $parent;
            
            // Add children recursively
            if (!empty($children)) {
                $flat = array_merge($flat, $this->flattenMenusFromOptions($children));
            }
        }
        
        return $flat;
    }
    
    /**
     * Map options menu format to database format
     * @param array $menu_item
     * @param string $menu_type
     * @return array
     */
    private function mapOptionsMenuToDatabase($menu_item, $menu_type)
    {
        // Extract badge value and color
        $badge_value = '';
        $badge_color = '';
        if (isset($menu_item['badge']) && is_array($menu_item['badge'])) {
            $badge_value = $menu_item['badge']['value'] ?? '';
            $badge_color = $menu_item['badge']['color'] ?? '';
        }
        
        // Convert is_custom from string "true"/"false" to int 1/0
        $is_custom = 0;
        if (isset($menu_item['is_custom'])) {
            $is_custom = ($menu_item['is_custom'] === 'true' || $menu_item['is_custom'] === true || $menu_item['is_custom'] === 1) ? 1 : 0;
        }
        
        return [
            'menu_type' => $menu_type,
            'slug' => $menu_item['slug'] ?? $menu_item['id'] ?? poly_generate_menu_slug(),
            'parent_id' => null, // Set null as requested - user will reconfigure
            'parent_slug' => 'root', // Reset to root
            'name' => $menu_item['name'] ?? 'Untitled',
            'href' => $menu_item['href'] ?? null,
            'icon' => $menu_item['icon'] ?? null,
            'svg' => null,
            'type' => $menu_item['type'] ?? 'default',
            'target' => $menu_item['target'] ?? null,
            'rel' => $menu_item['rel'] ?? null,
            'css' => $menu_item['css'] ?? null,
            'position' => isset($menu_item['position']) ? (int)$menu_item['position'] : 0,
            'level' => 1, // All set to level 1 since parent_id is null
            'disabled' => (isset($menu_item['disabled']) && $menu_item['disabled'] === 'true') ? 1 : 0,
            'is_custom' => $is_custom, // Convert from options format (string "true") to database format (int 1)
            'require_login' => 0,
            'badge_value' => $badge_value,
            'badge_color' => $badge_color,
            'popup_description' => $menu_item['popup_description'] ?? null,
            'href_original' => null,
            'option_settings' => null
        ];
    }

    /**
     * Download Module
     * AJAX handler to download a module as ZIP file
     */
    public function download_module()
    {
        // Check admin permission - only allow if user is admin AND user ID is 1
        if (!is_admin() || $this->current_user_id != 1) {
            // User doesn't have download permission
            poly_utilities_ajax_response_helper::response_access(_l('poly_utilities_module_download_permission_required'));
            return;
        }

        // Define temp directory for later use
        $temp_dir = FCPATH . 'uploads/poly_utilities/temp/';

        $module_name = $this->input->post('module_name');
        
        if (empty($module_name)) {
            poly_utilities_ajax_response_helper::response_failed(_l('poly_utilities_module_download_module_name_required'));
            return;
        }

        // Validate module name (prevent directory traversal)
        if (preg_match('/[^a-zA-Z0-9_-]/', $module_name)) {
            poly_utilities_ajax_response_helper::response_failed(_l('poly_utilities_module_download_invalid_module_name'));
            return;
        }

        $module_path = APP_MODULES_PATH . $module_name;
        
        // Check if module directory exists
        if (!is_dir($module_path)) {
            poly_utilities_ajax_response_helper::response_failed(_l('poly_utilities_module_download_module_not_found'));
            return;
        }

        try {
            // Get module version for filename
            $module_version = $this->get_module_version($module_name);
            $filename = $module_name . ($module_version ? '.' . $module_version : '') . '.zip';
            
            // Create temporary ZIP file in uploads directory
            $temp_dir = FCPATH . 'uploads/poly_utilities/temp/';
            
            // Create parent directories if they don't exist
            $parent_dir = dirname($temp_dir);
            if (!is_dir($parent_dir)) {
                if (!mkdir($parent_dir, 0755, true)) {
                    poly_utilities_ajax_response_helper::response_failed(sprintf(_l('poly_utilities_module_download_cannot_create_parent_directory'), $parent_dir));
                    return;
                }
            }
            
            // Create temp directory if it doesn't exist
            if (!is_dir($temp_dir)) {
                if (!mkdir($temp_dir, 0755, true)) {
                    poly_utilities_ajax_response_helper::response_failed(sprintf(_l('poly_utilities_module_download_cannot_create_temp_directory'), $temp_dir));
                    return;
                }
            }
            
            // Ensure temp directory is writable
            if (!is_writable($temp_dir)) {
                $instruction = '<div class="poly-permission-instruction">
                    <h4>' . _l('poly_utilities_module_download_permission_instruction_title') . '</h4>
                    <p>' . _l('poly_utilities_module_download_permission_instruction_message') . '</p>
                    <p><span class="poly-directory-path">' . _l('poly_utilities_module_download_permission_instruction_directory') . '</span> <code>' . $temp_dir . '</code></p>
                    <div class="poly-instruction-box">
                        <h5>' . _l('poly_utilities_module_download_permission_instruction_command') . '</h5>
                        <pre class="poly-command-code"><code>chmod 755 ' . $temp_dir . '</code></pre>
                        <p>' . _l('poly_utilities_module_download_permission_instruction_alternative') . '</p>
                    </div>
                    <p class="poly-note">' . _l('poly_utilities_module_download_permission_instruction_note') . '</p>
                </div>';
                poly_utilities_ajax_response_helper::response_access($instruction);
                return;
            }
            
            $temp_zip = $temp_dir . 'download_' . uniqid() . '.zip';
            
            $zip = new ZipArchive();
            
            // Try to open/create the ZIP file
            $result = $zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($result !== TRUE) {
                $error_messages = [
                    ZipArchive::ER_OK => 'No error',
                    ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
                    ZipArchive::ER_RENAME => 'Renaming temporary file failed',
                    ZipArchive::ER_CLOSE => 'Closing zip archive failed',
                    ZipArchive::ER_SEEK => 'Seek error',
                    ZipArchive::ER_READ => 'Read error',
                    ZipArchive::ER_WRITE => 'Write error',
                    ZipArchive::ER_CRC => 'CRC error',
                    ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
                    ZipArchive::ER_NOENT => 'No such file',
                    ZipArchive::ER_EXISTS => 'File already exists',
                    ZipArchive::ER_OPEN => 'Can\'t open file',
                    ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
                    ZipArchive::ER_ZLIB => 'Zlib error',
                    ZipArchive::ER_MEMORY => 'Memory allocation failure',
                    ZipArchive::ER_CHANGED => 'Entry has been changed',
                    ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
                    ZipArchive::ER_EOF => 'Premature EOF',
                    ZipArchive::ER_INVAL => 'Invalid argument',
                    ZipArchive::ER_NOZIP => 'Not a zip archive',
                    ZipArchive::ER_INTERNAL => 'Internal error',
                    ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                    ZipArchive::ER_REMOVE => 'Can\'t remove file',
                    ZipArchive::ER_DELETED => 'Entry has been deleted'
                ];
                
                $error_msg = isset($error_messages[$result]) ? $error_messages[$result] : 'Unknown error';
                poly_utilities_ajax_response_helper::response_failed(sprintf(_l('poly_utilities_module_download_cannot_create_zip'), $error_msg));
                return;
            }

            // Add module files to ZIP
            $this->addDirectoryToZip($zip, $module_path, $module_name);
            
            $zip->close();

            // Check if ZIP file was created successfully
            if (!file_exists($temp_zip) || filesize($temp_zip) === 0) {
                poly_utilities_ajax_response_helper::response_failed('Failed to create ZIP file.');
                return;
            }

            // Set headers for file download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($temp_zip));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Output file and clean up
            readfile($temp_zip);
            unlink($temp_zip);
            exit();

        } catch (Exception $e) {
            // Clean up temp file if it exists
            if (isset($temp_zip) && file_exists($temp_zip)) {
                unlink($temp_zip);
            }
            poly_utilities_ajax_response_helper::response_failed(sprintf(_l('poly_utilities_module_download_failed_with_error'), $e->getMessage()));
        }
    }

    /**
     * Recursively add directory to ZIP
     * @param ZipArchive $zip
     * @param string $dir
     * @param string $zipDir
     */
    private function addDirectoryToZip($zip, $dir, $zipDir = '')
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            $zipPath = $zipDir . '/' . $file;

            // Skip certain files/directories
            if (in_array($file, ['.git', 'node_modules', '.DS_Store', 'logs', 'cache', 'tmp'])) {
                continue;
            }

            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipPath);
                $this->addDirectoryToZip($zip, $filePath, $zipPath);
            } else {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }

    /**
     * Get module version from headers or database
     * @param string $module_name
     * @return string|null
     */
    private function get_module_version($module_name)
    {
        try {
            // Try to get module info from app_modules
            $module = $this->CI->app_modules->get($module_name);
            
            if ($module && isset($module['headers']['version'])) {
                $version = $module['headers']['version'];
                // Clean version string (remove any non-numeric characters except dots)
                $version = preg_replace('/[^0-9.]/', '', $version);
                return !empty($version) ? $version : null;
            }
            
            // Fallback: Try to read version from module file directly
            $module_file = APP_MODULES_PATH . $module_name . '/' . $module_name . '.php';
            if (file_exists($module_file)) {
                $content = file_get_contents($module_file);
                if (preg_match('/Version:\s*(.*)$/mi', $content, $matches)) {
                    $version = trim($matches[1]);
                    // Clean version string
                    $version = preg_replace('/[^0-9.]/', '', $version);
                    return !empty($version) ? $version : null;
                }
            }
            
        } catch (Exception $e) {
            // Log error but don't fail the download
            log_message('error', 'Failed to get module version for ' . $module_name . ': ' . $e->getMessage());
        }
        
        return null;
    }
    ////////////////////////////////////////////////////////////////////// END SETTINGS //////////////////////////////////////////////////////////////////////
    
    /**
     * Auto-migrate menu items from frontend format to database
     * Called when drag & drop is performed for the first time on empty database
     * 
     * @param string $menu_type sidebar|setup|clients
     * @param array $menu_items Menu items from frontend (in frontend format)
     * @return bool Success status
     */
    private function auto_migrate_menus_to_database($menu_type, $menu_items)
    {
        if (empty($menu_items)) {
            return false;
        }
        
        try {
            $this->db->trans_start();
            
            // Recursively migrate all menu items
            $this->migrate_menu_items_recursive($menu_type, $menu_items);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Recursively migrate menu items and their children from frontend to database
     * 
     * @param string $menu_type Menu type
     * @param array $menu_items Menu items array
     * @param int $parent_id Parent menu database ID
     * @param int $level Menu level (1, 2, 3)
     * @return void
     */
    private function migrate_menu_items_recursive($menu_type, $menu_items, $parent_id = null, $level = 1)
    {
        foreach ($menu_items as $index => $item) {
            if (!is_array($item) || empty($item['slug'])) {
                continue;
            }
            
            // Check if item already exists
            $existing = $this->custom_menu_model->get_by_slug($item['slug'], $menu_type);
            
            if ($existing) {
                // Already migrated, skip
                $menu_id = $existing['id'];
            } else {
                // Convert and insert
                $converted = $this->convert_frontend_to_db_format($item, $menu_type);
                
                // Override parent_id and level for hierarchical structure
                $converted['data']['parent_id'] = $parent_id;
                $converted['data']['level'] = $level;
                
                // Insert menu item
                $menu_id = $this->custom_menu_model->add($converted['data'], $converted['permissions']);
            }
            
            // Migrate children recursively
            if ($menu_id && !empty($item['children']) && is_array($item['children'])) {
                $this->migrate_menu_items_recursive($menu_type, $item['children'], $menu_id, $level + 1);
            }
        }
    }
    
    /**
     * Convert database menu format to frontend format
     * Database stores some fields differently than frontend expects
     */
    private function convert_db_menus_to_frontend_format($menus)
    {
        if (empty($menus)) {
            return [];
        }
        
        $converted = [];
        
        foreach ($menus as $menu) {
            // Handle SVG logic like original code
            $icon = $menu['icon'] ?? '';
            $svg = $menu['svg'] ?? '';
            
            // If icon contains SVG, move it to svg field and set icon to default
            if (isset($icon) && strpos($icon, 'svg') !== false) {
                $svg = $icon;
                $icon = 'menu-icon';
            }
            
            $item = [
                'id' => $menu['id'] ?? null, // Use real database ID
                'db_id' => $menu['id'] ?? null, // Real database ID for reference
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
                'disabled' => isset($menu['disabled']) && $menu['disabled'] ? 'false' : 'true', // Frontend uses opposite logic
                'is_custom' => isset($menu['is_custom']) && $menu['is_custom'] ? 'true' : 'false',
                'require_login' => isset($menu['require_login']) && $menu['require_login'] ? 'on' : '',
                'href_original' => $menu['href_original'] ?? '',
                'parent_id' => $menu['parent_id'] ?? null, // Use parent_id from database
                'parent_slug' => 'root', // Keep for backward compatibility (will be updated for children)
            ];
            
            // Badge - Always include badge object with default values
            $item['badge'] = [
                'value' => $menu['badge_value'] ?? '',
                'color' => $menu['badge_color'] ?? ''
            ];
            
            // Popup description - Accept both string and array
            $item['popup_description'] = '';
            if (!empty($menu['popup_description'])) {
                try {
                    if (is_string($menu['popup_description'])) {
                        //  Use native json_decode with error handling
                        $decoded = @json_decode($menu['popup_description'], true);
                        // Accept both array and string (popup_description is typically string content from TinyMCE)
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $item['popup_description'] = $decoded;
                        } else {
                            $item['popup_description'] = $menu['popup_description'];
                        }
                    } elseif (is_array($menu['popup_description'])) {
                        $item['popup_description'] = $menu['popup_description'];
                    }
                } catch (Exception $e) {
                    // If decode fails, keep empty string
                    $item['popup_description'] = '';
                }
            }
            
            //  Option settings - Parse JSON and merge into item (flexible object value)
            // Also keep the original option_settings field for frontend to re-parse if needed
            $option_settings_raw = $menu['option_settings'] ?? null;
            $item['option_settings'] = $option_settings_raw; // Keep original JSON string
            
            if (!empty($menu['option_settings'])) {
                try {
                    if (is_string($menu['option_settings'])) {
                        $settings = @json_decode($menu['option_settings'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($settings)) {
                            foreach ($settings as $key => $value) {
                                $item[$key] = $value; // Merge into item for easy access
                            }
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
            
            // Children - Always include empty array if not set
            $item['children'] = [];
            if (!empty($menu['children']) && is_array($menu['children'])) {
                $item['children'] = $this->convert_db_menus_to_frontend_format($menu['children']);
                // Update parent_id and parent_slug for children
                foreach ($item['children'] as &$child) {
                    $child['parent_id'] = $menu['id'] ?? null; // Use database ID
                    $child['parent_slug'] = $item['slug']; // Keep for backward compatibility
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
            }
            
            $converted[] = $item;
        }
        
        return $converted;
    }
    
    /**
     * Convert frontend menu format to database format
     */
    private function convert_frontend_to_db_format($menu_item, $menu_type)
    {
        //  Determine parent_id and parent_slug
        $parent_id = null;
        $parent_slug = $menu_item['parent_slug'] ?? 'root';
        
        // Priority 1: Check if frontend explicitly sent parent_id (from form edit)
        if (isset($menu_item['parent_id'])) {
            if ($menu_item['parent_id'] === '' || $menu_item['parent_id'] === null || $menu_item['parent_id'] === 'null' || $menu_item['parent_id'] === 0 || $menu_item['parent_id'] === '0') {
                // Empty parent_id means root level
                $parent_id = null;
                $parent_slug = 'root';
            } else {
                // Use parent_id from frontend (already validated)
                $parent_id = (int)$menu_item['parent_id'];
                // Also verify parent_slug matches, or derive from parent_id
                if (empty($parent_slug) || $parent_slug === 'root') {
                    // Derive parent_slug from parent_id
                    $parent = $this->custom_menu_model->get($parent_id, false);
                    $parent_slug = $parent ? $parent['slug'] : 'root';
                }
            }
        } 
        // Priority 2: Use parent_slug to find parent_id (from drag & drop or when parent_id not provided)
        else {
            if (empty($parent_slug) || $parent_slug === 'root') {
                $parent_slug = 'root';
                $parent_id = null;
            } else {
                // Find parent_id from parent_slug
                $parent = $this->custom_menu_model->get_by_slug($parent_slug, $menu_type);
                if ($parent) {
                    $parent_id = $parent['id'];
                } else {
                    // Parent not found, set to root
                    $parent_slug = 'root';
                    $parent_id = null;
                }
            }
        }
        
        // Handle SVG logic like original code
        $icon = $menu_item['icon'] ?? null;
        $svg = $menu_item['svg'] ?? null;
        
        // If icon contains SVG, move it to svg field and set icon to default
        if (isset($icon) && strpos($icon, 'svg') !== false) {
            $svg = $icon;
            $icon = 'menu-icon';
        }
        
        $data = [
            'menu_type' => $menu_type,
            'slug' => $menu_item['slug'] ?? $menu_item['id'] ?? poly_generate_menu_slug(),
            'parent_id' => $parent_id,
            'parent_slug' => $parent_slug,
            'name' => $menu_item['name'] ?? 'Untitled',
            'href' => $menu_item['href'] ?? null,
            'icon' => $icon,
            'svg' => $svg,
            'type' => $menu_item['type'] ?? 'default',
            'target' => $menu_item['target'] ?? null,
            'rel' => $menu_item['rel'] ?? null,
            'css' => $menu_item['css'] ?? null,
            'position' => (int)($menu_item['position'] ?? 0),
            // disabled: Frontend sends 'true' (enabled) = 0, 'false' (disabled) = 1
            'disabled' => (isset($menu_item['disabled']) && $menu_item['disabled'] === 'true') ? 0 : 1,
            'is_custom' => (isset($menu_item['is_custom']) && $menu_item['is_custom'] === 'true') ? 1 : 0,
            'require_login' => (isset($menu_item['require_login']) && $menu_item['require_login'] === 'on') ? 1 : 0,
            'badge_value' => $menu_item['badge']['value'] ?? null,
            'badge_color' => $menu_item['badge']['color'] ?? null,
            'popup_description' => isset($menu_item['popup_description']) ? json_encode($menu_item['popup_description']) : null,
            'href_original' => $menu_item['href_original'] ?? null,
            'option_settings' => isset($menu_item['option_settings']) && !empty($menu_item['option_settings']) 
                ? (is_string($menu_item['option_settings']) ? $menu_item['option_settings'] : json_encode($menu_item['option_settings'])) 
                : null, // ← Simply encode and save as JSON object, or null if empty. No predefined structure.
        ];
        
        // Add permissions to data array (they are now stored as JSON columns in the database)
        if (!empty($menu_item['roles'])) {
            $data['roles'] = is_string($menu_item['roles']) ? $menu_item['roles'] : json_encode($menu_item['roles']);
        } else {
            $data['roles'] = '[]';
        }
        if (!empty($menu_item['users'])) {
            $data['users'] = is_string($menu_item['users']) ? $menu_item['users'] : json_encode($menu_item['users']);
        } else {
            $data['users'] = '[]';
        }
        if (!empty($menu_item['clients'])) {
            $data['clients'] = is_string($menu_item['clients']) ? $menu_item['clients'] : json_encode($menu_item['clients']);
        } else {
            $data['clients'] = '[]';
        }
        
        // Keep permissions array for backward compatibility (though not used anymore)
        $permissions = [];
        if (!empty($data['roles'])) {
            $permissions['roles'] = json_decode($data['roles'], true) ?: [];
        }
        if (!empty($data['users'])) {
            $permissions['users'] = json_decode($data['users'], true) ?: [];
        }
        if (!empty($data['clients'])) {
            $permissions['clients'] = json_decode($data['clients'], true) ?: [];
        }
        
        // Handle children SVG logic like original code
        if (!empty($menu_item['children']) && is_array($menu_item['children'])) {
            foreach ($menu_item['children'] as &$child) {
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
        }
        
        return ['data' => $data, 'permissions' => $permissions];
    }
    
    /**
     * Provide options for the convert-to-task modal
     */
    public function ajax_text_to_task_form()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!is_admin()) {
            poly_utilities_ajax_response_helper::response_access(_l('poly_utilities_text_to_task_permission_denied'));
        }

        $projects = $this->db->select('id, name')
            ->from(db_prefix() . 'projects')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $customers = $this->db->select('userid as id, company as name')
            ->from(db_prefix() . 'clients')
            ->order_by('company', 'ASC')
            ->get()
            ->result_array();

        $staff = $this->db->select('staffid as id, CONCAT(firstname, " ", lastname) as name')
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->order_by('firstname', 'ASC')
            ->order_by('lastname', 'ASC')
            ->get()
            ->result_array();

        $payload = [
            'status'    => 'success',
            'code'      => 200,
            'projects'  => $projects,
            'customers' => $customers,
            'staff'     => $staff,
            'statuses'  => $this->tasks_model->get_statuses(),
        ];

        poly_utilities_ajax_response_helper::response_data($payload);
    }

    /**
     * Convert selected text into a task
     */
    public function convert_to_task()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!is_admin()) {
            poly_utilities_ajax_response_helper::response_access(_l('poly_utilities_text_to_task_permission_denied'));
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('title', _l('poly_utilities_text_to_task_task_title'), 'required|trim|max_length[255]');
        $this->form_validation->set_rules('description', _l('poly_utilities_text_to_task_task_description'), 'trim');
        $this->form_validation->set_rules('project_id', 'project', 'integer');
        $this->form_validation->set_rules('customer_id', 'customer', 'integer');
        $this->form_validation->set_rules('assignee_id', 'assignee', 'integer');
        $this->form_validation->set_rules('status', 'status', 'integer');
        $this->form_validation->set_rules('due_date', 'due date', 'trim');

        if ($this->form_validation->run() === false) {
            poly_utilities_ajax_response_helper::response_error(strip_tags(validation_errors()), 422);
        }

        $title       = trim($this->input->post('title', true));
        $description = $this->input->post('description', false);
        $projectId   = (int) $this->input->post('project_id');
        $customerId  = (int) $this->input->post('customer_id');
        $assigneeId  = (int) $this->input->post('assignee_id');
        $dueDate     = $this->input->post('due_date', true);
        $sourceUrl   = trim($this->input->post('source_url', true));

        $cleanDescription = trim((string) $description);
        if ($cleanDescription !== '') {
            $cleanDescription = nl2br(htmlspecialchars($cleanDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        if (!function_exists('prep_url')) {
            $this->load->helper('url');
        }

        if (!empty($sourceUrl)) {
            $safeUrl = prep_url($sourceUrl);
            $sourceNote = sprintf(
                '<p class="tw-text-sm tw-text-neutral-500"><strong>%s</strong>: <a href="%s" target="_blank" rel="noopener">%s</a></p>',
                _l('poly_utilities_text_to_task_source_label'),
                html_escape($safeUrl),
                html_escape($safeUrl)
            );
            $cleanDescription = $cleanDescription
                ? $cleanDescription . '<br><br>' . $sourceNote
                : $sourceNote;
        }

        $taskData = [
            'name'                => $title,
            'description'         => $cleanDescription,
            'startdate'           => date('Y-m-d'),
            'duedate'             => !empty($dueDate) ? $dueDate : '',
            'priority'            => 2,
            'visible_to_client'   => 0,
            'is_public'           => 0,
            'repeat_every'        => '',
            'recurring_type'      => '',
            'recurring'           => 0,
            'custom_recurring'    => 0,
            'withDefaultAssignee' => false,
            'assignees'           => $assigneeId > 0 ? [$assigneeId] : [],
            'followers'           => [],
            'tags'                => [],
        ];

        if ($projectId > 0) {
            $taskData['rel_type'] = 'project';
            $taskData['rel_id']   = $projectId;
        } elseif ($customerId > 0) {
            $taskData['rel_type'] = 'customer';
            $taskData['rel_id']   = $customerId;
        }

        $taskId = $this->tasks_model->add($taskData);

        if (!$taskId) {
            poly_utilities_ajax_response_helper::response_failed(_l('poly_utilities_text_to_task_failed'));
        }

        log_activity('Task created from selected text via Poly Utilities [TaskID: ' . $taskId . ']');

        $response = [
            'status'   => 'success',
            'code'     => 200,
            'message'  => _l('poly_utilities_text_to_task_created_successfully'),
            'task_id'  => $taskId,
            'task_url' => admin_url('tasks/view/' . $taskId),
        ];

        poly_utilities_ajax_response_helper::response_data($response);
    }

    /**
     * Create test menu via AJAX
     */
    public function create_test_menu()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $menu_data = $this->input->post('menu_data');
        if (empty($menu_data)) {
            echo json_encode(['success' => false, 'message' => 'No menu data provided']);
            return;
        }
        
        try {
            $this->load->model('poly_utilities/custom_menu_model');
            $menu_id = $this->create_test_menu_recursive($menu_data, 'sidebar');
            
            if ($menu_id) {
                echo json_encode(['success' => true, 'message' => 'Test menu created successfully', 'menu_id' => $menu_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create test menu']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Clear test menus
     */
    public function clear_test_menus()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        try {
            $this->load->model('poly_utilities/custom_menu_model');
            
            // Delete test menus (menus with 'test-' or 'deep-' prefix)
            $this->db->where("(slug LIKE 'test-%' OR slug LIKE 'deep-%')", NULL, FALSE)
                     ->delete(db_prefix() . 'poly_utilities_custom_menus');
            
            echo json_encode(['success' => true, 'message' => 'Test menus cleared successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Create test menu recursively
     */
    private function create_test_menu_recursive($menu_data, $menu_type, $parent_id = null, $level = 1)
    {
        $this->load->model('poly_utilities/custom_menu_model');
        
        // Prepare menu data
        $menu_item = [
            'name' => $menu_data['name'],
            'slug' => $menu_data['slug'],
            'href' => $menu_data['href'],
            'icon' => $menu_data['icon'] ?? '',
            'menu_type' => $menu_type,
            'parent_id' => $parent_id,
            'level' => $level,
            'position' => 0,
            'disabled' => 0,
            'is_custom' => 1
        ];
        
        // Add to database
        $menu_id = $this->custom_menu_model->add($menu_item, []);
        
        if ($menu_id && !empty($menu_data['children'])) {
            foreach ($menu_data['children'] as $child) {
                $this->create_test_menu_recursive($child, $menu_type, $menu_id, $level + 1);
            }
        }
        
        return $menu_id;
    }
}
