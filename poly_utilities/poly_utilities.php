<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: PolyUtilities
Description: Integrated utility features have been added to Perfex CRM to enhance operations and optimize workflow. These include projects, banners, widgets, a collapsible menu for search and rearrangement, a quick access menu, a custom menu (admin, setup, clients, grouping, popup, multi-level), a data table filter for displaying or reordering columns, an All-in-One support button, custom/embedded JavaScript/CSS, and additional operational functions.
Version: 3.4.3
Requires at least: 3.2.1
Author: PolyXGO
Author URI: https://codecanyon.net/user/polyxgo
*/

define('POLYUTILS_ISMINIFIED', true);
define('POLY_UTILITIES_VERSION', '3.4.3');
define('POLY_UTILITIES_MODULE_NAME', 'poly_utilities');
define('POLY_UTILITIES_MODULE_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME));
define('POLY_UTILITIES_MODULE_UPLOAD_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME, 'uploads'));
define('POLY_UTILITIES_MODULE_UPLOAD_MEDIA_FOLDER', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/media');
define('POLY_UTILITIES_MEDIA_PATH', base_url('/modules/' . POLY_UTILITIES_MODULE_NAME . '/uploads/media'));
define('POLY_UTILITIES_MODULE_UPLOAD_APPEARANCE_FOLDER', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/appearance');
define('POLY_UTILITIES_MODULE_UPLOAD_APPEARANCE_PATH', base_url('/modules/' . POLY_UTILITIES_MODULE_NAME . '/uploads/appearance'));
define('POLY_UTILITIES_SETTINGS', 'poly_utilities_settings');
define('POLY_WIDGETS', 'poly_utilities_widgets');
define('POLY_CUSTOM_MENU', 'poly_utilities_custom_menu');
define('POLY_QUICK_ACCESS_MENU', 'poly_utilities_global_quick_access_menu');
define('POLY_CONTEXT_MENU', 'poly_utilities_global_context_menu');
define('POLY_FIXED_BOTTOM_MENU', 'poly_utilities_global_fixed_bottom_menu');
define('POLY_SUPPORTS', 'poly_utilities_global_supports');
define('POLY_SCRIPTS', 'poly_utilities_global_scripts');
define('POLY_TABLE_FILTERS', 'poly_utilities_table_filters');
define('POLY_TABLE_COLUMNS_REORDER', 'poly_utilities_column_reorder');

define('POLY_STYLES', 'poly_utilities_global_styles');

define('POLY_UTILITIES_USERS_ACCESS_MODULES', 'poly_utilities_global_users_access_modules');

define('POLY_MENU_SIDEBAR', 'poly_utilities_global_menu_sidebar_custom');
define('POLY_MENU_SIDEBAR_CUSTOM_ACTIVE', 'poly_utilities_global_menu_sidebar_custom_active');

define('POLY_MENU_SETUP', 'poly_utilities_global_menu_setup_custom');
define('POLY_MENU_SETUP_CUSTOM_ACTIVE', 'poly_utilities_global_menu_setup_custom_active');

define('POLY_MENU_CLIENTS', 'poly_utilities_global_menu_clients_custom');
define('POLY_MENU_CLIENTS_CUSTOM_ACTIVE', 'poly_utilities_global_menu_clients_custom_active');

define('POLY_BANNERS', 'poly_utilities_banners');
define('POLY_BANNERS_ANNOUNCEMENTS', 'poly_utilities_banners_announcements');
define('POLY_BANNERS_AREA', 'poly_utilities_banners_area');
define('POLY_BANNERS_ANNOUNCEMENTS_AREA', 'poly_utilities_banners_announcements_area');
define('POLY_BANNERS_SETTINGS', 'poly_utilities_banners_settings');

define('POLY_UTILITIES_APPEARANCE_SETTINGS', 'poly_utilities_appearance_settings');

define('POLYUTILITIES_PROJECT_NAME_PATTERNS', 'poly_utilities_projects_name_patterns');

define('POLY_UTILITIES_CUSTOM_MENU_CLIENTS_SLUG', 'article');

class POLYUTILITIES
{
    private $CI;
    private $poly_utilities_settings;
    private $quick_access_menu;
    private $current_user_id;
    private $feature_custom_menu_hooks = true;
    private $feature_multiple_companies = true;
    private $feature_multiple_addresses = true;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('projects_model');
        $this->CI->load->model('contracts_model');

        $this->current_user_id = get_staff_user_id();

        $this->quick_access_menu = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        register_activation_hook(POLY_UTILITIES_MODULE_NAME, array($this, 'register_module_activation_hook'));

        /**
         * Dactivation module hook
         */
        register_deactivation_hook(POLY_UTILITIES_MODULE_NAME, array($this, 'register_module_deactivation_hook'));

        hooks()->add_action('admin_init', [$this, 'admin_init_common']);/* language, widgets,... define */

        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_menu');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_menu_limited'); // Limited levels menu helper
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_menu_sync'); // Sync system menus to DB
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_banners');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_user');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_widget');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_common');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_ajax_response');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_multiple_companies'); // Multiple companies helper

        hooks()->add_action('pre_admin_init', [$this, 'hook_custom_module_permisson']);

        hooks()->add_action('admin_init', [$this, 'hook_custom_module_init_menu_items']);
        
        // Sync menus when on modules page (after module activation, menu items are registered)
        // This ensures menu items are synced when user returns to modules list after activation
        hooks()->add_action('admin_init', [$this, 'hook_sync_menus_on_modules_page'], 999);

        hooks()->add_action('app_admin_head', [$this, 'assets_head'], 1); // Logged head
        hooks()->add_action('app_admin_footer', [$this, 'assets_footer'], 1); // Logged footer
        hooks()->add_action('app_init', [$this, 'ensure_core_email_templates'], 1);
        hooks()->add_filter('register_merge_fields', [$this, 'override_contract_merge_fields'], 20);

        hooks()->add_action('app_admin_head', [$this, 'hook_admins_admin_head'], 1); // Admin head
        hooks()->add_action('app_admin_head', [$this, 'hook_customize_scripts_styles_admin_header'], 1); // Customize admin head

        /**
         * Admin | Customers | Both => scripts, styles.
         */
        hooks()->add_action('app_customers_head', [$this, 'hook_customers_admin_head']); // Login page head
        hooks()->add_action('app_customers_head', [$this, 'hook_customize_scripts_styles_customer_header']); // Login page head

        hooks()->add_action('app_admin_footer', [$this, 'hook_customize_scripts_styles_admin_footer']); // Login page footer

        hooks()->add_action('app_customers_footer', [$this, 'hook_customize_scripts_styles_customer_footer']); // Customer's footer logged
        hooks()->add_action('app_customers_footer', [$this, 'hook_scripts_styles_clients_footer']); // Front page common

        /**
         * Admin login form head
         */
        hooks()->add_action('app_admin_authentication_head', [$this, 'hook_widgets_clients']);

        /**
         * Register language files, must be registered if the module is using languages
         */
        register_language_files(POLY_UTILITIES_MODULE_NAME, [POLY_UTILITIES_MODULE_NAME]);

        $this->poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));
        $this->poly_utilities_settings = $this->poly_utilities_settings
            ? json_decode($this->poly_utilities_settings)
            : new stdClass();

        $dataOptions = [
            'data_filters' => POLY_TABLE_FILTERS,
            'data_reorder' => POLY_TABLE_COLUMNS_REORDER,
        ];

        foreach ($dataOptions as $property => $optionKey) {
            $optionValue = get_option($optionKey);
            $this->poly_utilities_settings->$property = !empty($optionValue) ? json_decode($optionValue, true) : [];
        }

        $this->poly_utilities_settings->table_hooks = !empty(poly_utilities_common_helper::$table_hooks)
            ? poly_utilities_common_helper::$table_hooks
            : [];

        $this->feature_custom_menu_hooks = $this->get_feature_flag('enable_custom_menu_hooks', true);
        $this->feature_multiple_companies = $this->get_feature_flag('enable_multiple_companies', true);
        $this->feature_multiple_addresses = $this->get_feature_flag('enable_multiple_addresses', true);


        /**
         * The hook method is processed before showing the sidebar menu
         */
        if ($this->feature_custom_menu_hooks) {
            hooks()->add_filter('sidebar_menu_items', 'app_admin_poly_custom_sidebar_menu_items', 999);
            hooks()->add_filter('setup_menu_items', 'app_admin_poly_custom_setup_menu_items', 999);
        }

        /**
         * Handle permission clients menu items.
         */
        if ($this->feature_custom_menu_hooks) {
            hooks()->add_action('clients_init', 'app_admin_poly_custom_clients_menu_items', 999);
        }
        
        /**
         * Initialize unlimited menu levels support
         */
        if (file_exists(__DIR__ . '/hooks/menu_rendering_hooks.php')) {
            require_once(__DIR__ . '/hooks/menu_rendering_hooks.php');
            if ($this->feature_custom_menu_hooks) {
                init_unlimited_menu_hooks();
            }
        }
        
        /**
         * Note: View override for unlimited menu support is now handled globally
         * in modules/poly_utilities/config/my_hooks.php and loaded via application/config/my_hooks.php
         * This ensures it works across all routes, not just poly_utilities routes
         */

        /**
         * Handle defined clients menu items.
         */
        if ($this->feature_custom_menu_hooks) {
            hooks()->add_action('clients_init', [$this, 'hook_theme_custom_menu_items'], 9); // 9 before priority 10
        }

        /**
         * Reset the custom menu settings when the modules are activated or deactivated
         * TODO: Need to handle the case of maintaining the order of the menus when there are changes in activating/deactivating various modules, including poly_utilities.
         */
        hooks()->add_action("pre_activate_module", [$this, 'when_activate_modules']);
        hooks()->add_action("module_activated", [$this, 'when_module_activated']); // Sync AFTER module is activated
        hooks()->add_action("pre_deactivate_module", [$this, 'when_deactivate_modules']);
        hooks()->add_action("module_deactivated", [$this, 'when_module_deactivated']); // Final cleanup AFTER module is deactivated

        hooks()->add_action("pre_admin_init", [$this, 'clearn_migrations']);

        ////////////////////////////////////////////////////////////////////// REORDER COLUMNS //////////////////////////////////////////////////////////////////////
        $this->hooks_reorder_columns();
        ////////////////////////////////////////////////////////////////////// REORDER COLUMNS //////////////////////////////////////////////////////////////////////
        hooks()->add_filter('app_view_data', [$this, 'poly_utilities_custom_search_handle']);

        hooks()->add_filter('module_poly_utilities_action_links', [$this, 'add_poly_utilities_settings_link']);

        ////////////////////////////////////////////////////////////////////// MULTIPLE COMPANIES //////////////////////////////////////////////////////////////////////
        if ($this->feature_multiple_companies) {
            $this->init_multiple_companies_hooks();
        }
        ////////////////////////////////////////////////////////////////////// MULTIPLE COMPANIES //////////////////////////////////////////////////////////////////////

        if ($this->feature_multiple_addresses) {
            hooks()->add_filter('customer_profile_tabs', [$this, 'register_customer_addresses_tab'], 60);
        }

        ////////////////////////////////////////////////////////////////////// TASK TEMPLATES //////////////////////////////////////////////////////////////////////
        hooks()->add_filter('before_add_project', [$this, 'remove_task_template_id_from_project_data']);
        hooks()->add_action('after_add_project', [$this, 'create_tasks_from_template']);
        ////////////////////////////////////////////////////////////////////// TASK TEMPLATES //////////////////////////////////////////////////////////////////////
    }

    /**
     * Remove hook action add_default_theme_menu_items. Funtion init clients menu.
     */
    public function hook_theme_custom_menu_items()
    {
        if (!$this->feature_custom_menu_hooks) {
            return;
        }

        if (function_exists('add_default_theme_menu_items')) {
            hooks()->remove_action('clients_init', 'add_default_theme_menu_items');
        }

        // Load clients menu from database
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
        
        // Initialize default clients menu if empty (auto-init on first access)
        if (function_exists('poly_init_default_clients_menu')) {
            poly_init_default_clients_menu();
        }
        
        $custom_clients_menu_items = $CI->custom_menu_model->get_menus('clients', true, true);
        $custom_clients_menu_items = poly_utilities_convert_db_to_system_format($custom_clients_menu_items);
        $flat_menu_items = poly_flatten_menu_items($custom_clients_menu_items);

        // Define
        if (is_knowledge_base_viewable(true)) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'knowledge-base');
            if (!$current_object) {
                add_theme_menu_item('knowledge-base', [
                    'name'     => _l('clients_nav_kb'),
                    'href'     => site_url('knowledge-base'),
                    'position' => 5,
                ]);
            }
        }

        if (!is_client_logged_in() && get_option('allow_registration') == 1) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'register');
            if (!$current_object) {
                add_theme_menu_item('register', [
                    'name'     => _l('clients_nav_register'),
                    'href'     => site_url('authentication/register'),
                    'position' => 99,
                ]);
            }
        }

        if (!is_client_logged_in()) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'login');
            if (!$current_object) {
                add_theme_menu_item('login', [
                    'name'     => _l('clients_nav_login'),
                    'href'     => site_url('authentication/login'),
                    'position' => 100,
                    'icon'     => 'fa-regular fa-user',
                ]);
            }
        }
        if (is_client_logged_in()) {
            // Remove menu items that the current client does not have permission to access.
            poly_process_menu_items($flat_menu_items, $custom_clients_menu_items);
        }
    }

    /**
     * Cleanup Old Migration Files
     *
     * This function is responsible for managing migration files in the specified module directory.
     * It identifies the 3 latest migration files based on the numeric prefix in the filename and
     * removes all older migration files, keeping only the 3 most recent ones.
     *
     * Features:
     * - Scans the migration directory for PHP files.
     * - Extracts the numeric prefix from each migration file's name.
     * - Sorts migration numbers in descending order (newest first).
     * - Keeps only the 3 latest migration files and deletes all older ones.
     *
     * Use Case:
     * - Maintains a clean migration directory while preserving recent migration history.
     * - Prevents conflicts or redundancy caused by old migrations.
     * - Balances between keeping migration history and directory cleanliness.
     *
     * @return void
     */
    public function clearn_migrations()
    {
        $migrationPath = APP_MODULES_PATH . 'poly_utilities/migrations';
        $files = get_dir_contents($migrationPath);
        $migrationNumbers = [];

        if ($files) {
            // Collect all migration numbers
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                    $fileParts = explode('_', $fileNameWithoutExt);
                    $fileNumber = intval($fileParts[0]);
                    $migrationNumbers[] = $fileNumber;
                }
            }

            // Sort migration numbers in descending order
            rsort($migrationNumbers);

            // Keep only the 3 latest versions
            if (count($migrationNumbers) > 3) {
                $keepNumbers = array_slice($migrationNumbers, 0, 3);
                
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                        $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                        $fileParts = explode('_', $fileNameWithoutExt);
                        $fileNumber = intval($fileParts[0]);
                        
                        if (!in_array($fileNumber, $keepNumbers)) {
                            unlink($file);
                        }
                    }
                }
            }
        }
    }

    /**
     * Initialize data, configuration, language, and shared components within the module.
     * @return void
     */
    public function admin_init_common()
    {
        poly_utilities_widget_helper::init();
    }

    /**
     * Make sure required core email templates exist.
     */
    public function ensure_core_email_templates()
    {
        $CI = &get_instance();

        if (!isset($CI->db)) {
            return;
        }

        $table = db_prefix() . 'emailtemplates';
        if (!$CI->db->table_exists($table)) {
            return;
        }

        if (!class_exists('Emails_model', false)) {
            $CI->load->model('emails_model');
        }

        $availableColumns = array_flip($CI->db->list_fields($table));

        $requiredTemplates = [
            [
                'slug'     => 'send-contract',
                'type'     => 'contract',
                'language' => 'english',
                'name'     => 'Send Contract to Customer',
                'subject'  => 'Contract - {contract_subject}',
                'message'  => '<p><span style="font-size: 12pt;">Hi&nbsp;{contact_firstname}&nbsp;{contact_lastname}</span><br /><br /><span style="font-size: 12pt;">Please find the <a href="{contract_link}">{contract_subject}</a> attached.<br /><br />Description: {contract_description}<br /><br /></span><span style="font-size: 12pt;">Looking forward to hear from you.</span><br /><br /><span style="font-size: 12pt;">Kind Regards,</span><br /><span style="font-size: 12pt;">{email_signature}</span></p>',
            ],
        ];

        foreach ($requiredTemplates as $template) {
            $existing = $CI->emails_model->get(
                ['slug' => $template['slug'], 'language' => $template['language']],
                'row'
            );

            if ($existing) {
                continue;
            }

            $baseData = [
                'type'      => $template['type'],
                'slug'      => $template['slug'],
                'language'  => $template['language'],
                'name'      => $template['name'],
                'subject'   => $template['subject'],
                'message'   => $template['message'],
                'fromname'  => '{companyname} | CRM',
                'fromemail' => '',
                'plaintext' => 0,
                'active'    => 1,
                'order'     => 0,
            ];

            $dataToInsert = array_intersect_key($baseData, $availableColumns);

            if (!empty($dataToInsert)) {
                $CI->emails_model->add_template($dataToInsert);
            }
        }
    }

    /**
     * Replace core contract merge fields loader with module-safe version.
     *
     * @param array $fields
     * @return array
     */
    public function override_contract_merge_fields($fields)
    {
        $corePath = 'merge_fields/contract_merge_fields';

        $filtered = array_filter($fields, function ($path) use ($corePath) {
            return $path !== $corePath;
        });

        $filtered[] = 'poly_utilities/merge_fields/contract_merge_fields';

        return $filtered;
    }

    /**
     * Handle data, configuration when activating the module
     */
    public function when_activate_modules($module = null)
    {
        // Check if table exists before syncing (prevent errors during activation)
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        // If poly_utilities is being activated, ensure its menu items are restored
        $module_name = null;
        if (is_array($module)) {
            if (isset($module['system_name'])) {
                $module_name = $module['system_name'];
            } elseif (isset($module['name'])) {
                $module_name = $module['name'];
            } elseif (isset($module['headers']['module_name'])) {
                $module_name = $module['headers']['module_name'];
            }
        } elseif (is_string($module)) {
            $module_name = $module;
        } elseif (is_object($module)) {
            if (isset($module->system_name)) {
                $module_name = $module->system_name;
            } elseif (isset($module->name)) {
                $module_name = $module->name;
            } elseif (isset($module->headers->module_name)) {
                $module_name = $module->headers->module_name;
            }
        }
        
        // Sync system menus when any module is activated/deactivated
        if (function_exists('poly_force_sync_all_menus')) {
            poly_force_sync_all_menus(true); // Force update to catch route changes
        }
        
        // Initialize clients menu if still empty
        if (function_exists('poly_init_default_clients_menu')) {
            poly_init_default_clients_menu();
        }
    }

    /**
     * Sync menus when on modules page
     * This ensures menu items are synced when user returns to modules list after activation
     * At this point, admin_init has run and menu items are registered
     */
    public function hook_sync_menus_on_modules_page()
    {
        $CI = &get_instance();
        
        // Only sync when on modules list page (not during activate/deactivate)
        $controller = $CI->router->fetch_class();
        $method = $CI->router->fetch_method();
        
        // Check if we're on modules list page
        if ($controller !== 'mods' || $method !== 'index') {
            return;
        }
        
        // Check if table exists
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        // Skip if already synced in this request (prevent multiple syncs)
        static $synced = false;
        if ($synced) {
            return;
        }
        $synced = true;
        
        // Sync menus - menu items are already registered at this point (admin_init has run)
        if (function_exists('poly_force_sync_all_menus')) {
            try {
                $CI->db->reset_query();
                poly_force_sync_all_menus(true);
                $CI->db->reset_query();
            } catch (Exception $e) {
                log_message('error', 'Poly Utilities: Error syncing menus on modules page: ' . $e->getMessage());
                $CI->db->reset_query();
            }
        }
    }

    /**
     * Handle menu sync AFTER module is activated
     * This ensures module menu items are available in system menu before syncing
     */
    public function when_module_activated($module = null)
    {
        // Check if table exists before syncing (prevent errors during activation)
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        // Get module name
        $module_name = null;
        if (is_array($module)) {
            if (isset($module['system_name'])) {
                $module_name = $module['system_name'];
            } elseif (isset($module['name'])) {
                $module_name = $module['name'];
            }
        } elseif (is_string($module)) {
            $module_name = $module;
        } elseif (is_object($module)) {
            if (isset($module->system_name)) {
                $module_name = $module->system_name;
            } elseif (isset($module->name)) {
                $module_name = $module->name;
            }
        }
        
        if (!$module_name) {
            return;
        }
        
        // Skip if poly_utilities itself (will be handled separately)
        if ($module_name === POLY_UTILITIES_MODULE_NAME) {
            return;
        }
        
        // CRITICAL: Menu items of modules are usually registered in admin_init hook or module constructor
        // But when module_activated hook runs, admin_init might not have run yet
        // Solution: Trigger multiple hooks to ensure menu items are registered before sync
        
        // Step 1: Trigger pre_admin_init hook (some modules register menus here)
        try {
            if (function_exists('hooks')) {
                hooks()->do_action('pre_admin_init');
            }
        } catch (Exception $e) {
            //log_message('debug', 'Poly Utilities: Note - pre_admin_init hook triggered: ' . $e->getMessage());
        }
        
        // Step 2: Trigger admin_init hooks to ensure menu items are registered
        // This is important because modules typically register menu items in admin_init
        try {
            if (function_exists('hooks')) {
                hooks()->do_action('admin_init');
            }
        } catch (Exception $e) {
            //log_message('debug', 'Poly Utilities: Note - admin_init hook triggered: ' . $e->getMessage());
        }
        
        // Step 3: Force reload app_menu by getting menu items (this triggers filters and ensures items are registered)
        // This is a workaround to ensure menu items are available in app_menu before sync
        try {
            $CI->app_menu->get_sidebar_menu_items();
            $CI->app_menu->get_setup_menu_items();
            $CI->app_menu->get_theme_items();
        } catch (Exception $e) {
            //log_message('debug', 'Poly Utilities: Note - app_menu reloaded: ' . $e->getMessage());
        }
        
        // NOW sync menus - module is already activated and menu items should be registered
        // This will INSERT new menu items from the activated module into database
        // IMPORTANT: Force sync to ensure menu items are inserted even if they were deleted before
        if (function_exists('poly_force_sync_all_menus')) {
            try {
                // Force sync all menus - this will INSERT new items from the activated module
                $results = poly_force_sync_all_menus(true);
            } catch (Exception $e) {
                //log_message('error', 'Poly Utilities: Error syncing menus after activation: ' . $e->getMessage());
            }
        }
        
        // Additional check: Verify menu items were inserted
        // If not, try syncing again (menu items might need additional time to register)
        if (function_exists('poly_sync_system_menus_to_db')) {
            try {
                // Check if any menu items exist for this module
                $CI->db->reset_query();
                $menu_count = $CI->db->where('module_name', $module_name)
                                    ->where('is_custom', 0)
                                    ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
                
                // If no menu items found, try syncing again
                // This handles cases where menu items are registered in hooks that run later
                if ($menu_count == 0) {
                    // Try triggering admin_init again (some modules might register menus in nested hooks)
                    if (function_exists('hooks')) {
                        hooks()->do_action('admin_init');
                    }
                    
                    // Sync each menu type individually to ensure items are inserted
                    foreach (['sidebar', 'setup', 'clients'] as $menu_type) {
                        poly_sync_system_menus_to_db($menu_type);
                    }
                    
                    // Check again after sync
                    $CI->db->reset_query();
                    $menu_count_after = $CI->db->where('module_name', $module_name)
                                               ->where('is_custom', 0)
                                               ->count_all_results(db_prefix() . 'poly_utilities_custom_menus');
                    
                    if ($menu_count_after > 0) {
                        //log_message('debug', "Poly Utilities: Inserted {$menu_count_after} menu items for module {$module_name} after retry");
                    } else {
                        // Still no items - log for debugging
                        //log_message('debug', "Poly Utilities: No menu items found for module {$module_name} after sync - module might not have menu items");
                    }
                } else {
                    //log_message('debug', "Poly Utilities: Found {$menu_count} menu items for module {$module_name}");
                }
            } catch (Exception $e) {
                    //log_message('error', 'Poly Utilities: Error in menu verification: ' . $e->getMessage());
            }
        }
        
        // Reset query builder
        $CI->db->reset_query();
    }

    /**
     * Handle data, configuration when deactivating the module
     */
    public function when_deactivate_modules($module = null)
    {
        // Check if table exists before syncing (prevent errors during deactivation)
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        // If poly_utilities itself is being deactivated, skip cleanup to preserve menu items
        $module_name = null;
        if (is_array($module)) {
            // Check various possible keys in module array
            if (isset($module['system_name'])) {
                $module_name = $module['system_name'];
            } elseif (isset($module['name'])) {
                $module_name = $module['name'];
            } elseif (isset($module['headers']['module_name'])) {
                $module_name = $module['headers']['module_name'];
            }
        } elseif (is_string($module)) {
            $module_name = $module;
        } elseif (is_object($module)) {
            if (isset($module->system_name)) {
                $module_name = $module->system_name;
            } elseif (isset($module->name)) {
                $module_name = $module->name;
            } elseif (isset($module->headers->module_name)) {
                $module_name = $module->headers->module_name;
            }
        }
        
        // Skip sync if poly_utilities is being deactivated (preserve its menu items)
        if ($module_name === POLY_UTILITIES_MODULE_NAME) {
            return;
        }
        
        // IMPORTANT: Do NOT sync here because module is still active in system menu
        // Instead, only cleanup menu items of the deactivated module
        // The cleanup will be done by poly_cleanup_orphaned_system_menus() which is called
        // after module is actually deactivated (via Strategy 2 and Strategy 3)
        
        // For immediate cleanup, we can call cleanup directly for this specific module
        // This ensures menu items are removed even before Strategy 2/3 run
        if (function_exists('poly_cleanup_orphaned_system_menus')) {
            try {
                // Cleanup for all menu types
                foreach (['sidebar', 'setup', 'clients'] as $menu_type) {
                    $deleted = poly_cleanup_orphaned_system_menus($menu_type, $module_name);
                    // Log for debugging
                    if ($deleted > 0) {
                        //log_message('debug', "Poly Utilities: Cleaned up {$deleted} menu items for module {$module_name} in {$menu_type}");
                    }
                }
            } catch (Exception $e) {
                // Log error but don't prevent deactivation
                //log_message('error', 'Poly Utilities: Error cleaning up menus during deactivation: ' . $e->getMessage());
            }
        }
        
        // Additional comprehensive cleanup: Delete ALL items related to this module
        // This is a comprehensive safety net that deletes by multiple criteria
        try {
            $module_lower = strtolower($module_name);
            
            foreach (['sidebar', 'setup', 'clients'] as $menu_type) {
                $deleted_count = 0;
                
                // Method 1: Delete by exact slug match (root items)
                $CI->db->reset_query();
                $CI->db->where('menu_type', $menu_type);
                $CI->db->where('is_custom', 0);
                $CI->db->where('slug', $module_name);
                $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                $deleted_count += $deleted;
                
                // Method 2: Delete by slug pattern (all items starting with module name)
                $CI->db->reset_query();
                $CI->db->where('menu_type', $menu_type);
                $CI->db->where('is_custom', 0);
                $CI->db->group_start()
                       ->like('slug', $module_lower . '_', 'after')
                       ->or_like('slug', $module_lower . '-', 'after')
                ->group_end();
                $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                $deleted_count += $deleted;
                
                // Method 3: Delete by module_name
                $CI->db->reset_query();
                $CI->db->where('menu_type', $menu_type);
                $CI->db->where('is_custom', 0);
                $CI->db->where('module_name', $module_name);
                $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                $deleted_count += $deleted;
                
                // Method 4: Delete by href pattern
                $CI->db->reset_query();
                $CI->db->where('menu_type', $menu_type);
                $CI->db->where('is_custom', 0);
                $CI->db->group_start()
                       ->like('href', '/admin/' . $module_lower . '/', 'after')
                       ->or_like('href', '/admin/' . $module_lower, 'both')
                       ->or_like('href', '/clients/' . $module_lower . '/', 'after')
                       ->or_like('href', '/clients/' . $module_lower, 'both')
                       ->or_like('href', '/' . $module_lower . '/', 'after')
                       ->or_like('href', '/' . $module_lower, 'both')
                ->group_end();
                $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                $deleted_count += $deleted;
                
                // Method 5: Get all remaining items and delete children recursively
                $CI->db->reset_query();
                $remaining = $CI->db->select('id, slug')
                                   ->where('menu_type', $menu_type)
                                   ->where('is_custom', 0)
                                   ->get(db_prefix() . 'poly_utilities_custom_menus')
                                   ->result_array();
                
                $ids_to_delete = [];
                foreach ($remaining as $item) {
                    $slug = strtolower($item['slug'] ?? '');
                    if ($slug === $module_lower ||
                        strpos($slug, $module_lower . '_') === 0 ||
                        strpos($slug, $module_lower . '-') === 0) {
                        $ids_to_delete[] = $item['id'];
                    }
                }
                
                if (!empty($ids_to_delete)) {
                    // Delete children first
                    if (function_exists('delete_menu_children_recursive')) {
                        delete_menu_children_recursive($CI, $ids_to_delete, $menu_type);
                    }
                    
                    // Then delete the items
                    $CI->db->reset_query();
                    $CI->db->where_in('id', $ids_to_delete);
                    $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                    $deleted_count += $deleted;
                }
                
                if ($deleted_count > 0) {
                    //log_message('debug', "Poly Utilities: Force deleted {$deleted_count} menu items for module {$module_name} in {$menu_type}");
                }
            }
        } catch (Exception $e) {
            //log_message('error', 'Poly Utilities: Error force deleting menus: ' . $e->getMessage());
        }
        
        // CRITICAL: Reset query builder to prevent leftover WHERE clauses from affecting
        // the subsequent App_modules->deactivate() database update
        // This prevents "Unknown column 'menu_type' in 'where clause'" errors
        $CI->db->reset_query();
    }

    /**
     * Final cleanup AFTER module is deactivated
     * This runs after module is actually deactivated, so system menu no longer has its items
     */
    public function when_module_deactivated($module = null)
    {
        // Check if table exists
        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        // Get module name
        $module_name = null;
        if (is_array($module)) {
            if (isset($module['system_name'])) {
                $module_name = $module['system_name'];
            } elseif (isset($module['name'])) {
                $module_name = $module['name'];
            }
        } elseif (is_string($module)) {
            $module_name = $module;
        } elseif (is_object($module)) {
            if (isset($module->system_name)) {
                $module_name = $module->system_name;
            } elseif (isset($module->name)) {
                $module_name = $module->name;
            }
        }
        
        // Skip if poly_utilities itself
        if ($module_name === POLY_UTILITIES_MODULE_NAME) {
            return;
        }
        
        // NOW sync and cleanup - module is deactivated, so its items are no longer in system menu
        // Strategy 2 and Strategy 3 in poly_cleanup_orphaned_system_menus will catch any remaining items
        if (function_exists('poly_force_sync_all_menus')) {
            try {
                // This will sync (which won't add items from deactivated module) and cleanup orphaned items
                poly_force_sync_all_menus(true, $module_name);
            } catch (Exception $e) {
                log_message('error', 'Poly Utilities: Error syncing menus after deactivation: ' . $e->getMessage());
            }
        }
        
        // Additional final cleanup: Delete any remaining items by slug pattern
        // This ensures nothing is left behind
        if ($module_name) {
            try {
                $module_lower = strtolower($module_name);
                
                foreach (['sidebar', 'setup', 'clients'] as $menu_type) {
                    // Delete all items where slug matches module name pattern
                    $CI->db->reset_query();
                    $CI->db->where('menu_type', $menu_type);
                    $CI->db->where('is_custom', 0);
                    $CI->db->group_start()
                           ->where('slug', $module_name)
                           ->or_like('slug', $module_lower . '_', 'after')
                           ->or_like('slug', $module_lower . '-', 'after')
                    ->group_end();
                    $deleted = $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                    
                    if ($deleted > 0) {
                        log_message('debug', "Poly Utilities: Final cleanup deleted {$deleted} items for {$module_name} in {$menu_type}");
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Poly Utilities: Error in final cleanup: ' . $e->getMessage());
            }
        }
        
        // Reset query builder
        $CI->db->reset_query();
    }

    public function hook_widgets_clients()
    {
        $this->poly_utilities_styles_customers();
        echo '<script src="' . site_url() . 'assets/plugins/jquery/jquery.min.js"></script>';
        echo '<script src="' . site_url() . 'assets/plugins/bootstrap/js/bootstrap.min.js"></script>';

        $this->poly_utilities_settings_scripts(true);

        $this->poly_utilities_scripts_customers_public_head();
        $this->poly_utilities_scripts_customers_public_scripts();

        // Widgets area
        echo '<div class="poly-area-login-top-page-element-helper"></div>';

        $this->poly_utilities_cusomize_login_page();
    }

    public function poly_utilities_cusomize_login_page()
    {
        $existingAppearance = poly_utilities_common_helper::json_decode(get_option(POLY_UTILITIES_APPEARANCE_SETTINGS) ?? '', true);
        if (isset($existingAppearance['active']) && !$existingAppearance['active']) return;

        $active_login_background = isset($existingAppearance['active_login_background'])  && $existingAppearance['active_login_background'];

        $login_background = $existingAppearance['login_background'] ?? '';
        $dashboard_background = $existingAppearance['dashboard_background'] ?? '';
        $login_page_background_color = $existingAppearance['login_page_background_color'] ?? 'inherith';
        $login_page_text_color = $existingAppearance['login_page_text_color'] ?? 'inherith';
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/effects.js') . '"></script>';
?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.createElement('canvas');
                canvas.style.top = 0;
                canvas.style.left = 0;
                canvas.style.zIndex = -1;
                canvas.style.position = 'absolute';
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                document.body.appendChild(canvas);

                let clEffect = '<?php echo $existingAppearance['effect'] ?? 'None'; ?>';
                if (clEffect !== 'None' && typeof window[clEffect] === 'function') {
                    const EffectClass = window[clEffect];
                    if (clEffect === 'TextCloud') {
                        let clEffectContent = '<?php echo $existingAppearance['effect_content'] ?? ""; ?>';
                        let clEffectElements = clEffectContent.split(',').map(item => item.trim());
                        new TextCloud({
                            matrixWatermark: clEffectElements,
                            textColor: '<?php echo $login_page_text_color ?>',
                            backdropOpacity: 0.8,
                            textScale: 1,
                            fadeInDuration: 1000,
                            fadeOutDuration: 2000,
                            minInterval: 500,
                            maxInterval: 1000,
                            targetSelectors: [".poly-utilities.customers_login", ".poly-utilities.login_admin"],
                        });

                    } else {
                        new EffectClass(canvas);

                        //
                        var navbar = document.querySelector('body.login_admin .navbar.navbar-default, body.customers_login .navbar.navbar-default');
                        var footer = document.querySelector('body.login_admin footer.footer, body.customers_login footer.footer');
                        if (navbar) {
                            navbar.style.opacity = '0.4';
                        }
                        if (footer) {
                            footer.style.opacity = '0.4';
                        }
                    }
                }
            });
        </script>
        <?php
        $style = "<style>\n";
        $style .= "            body.login_admin,\n";
        $style .= "            body.customers_login {\n";
        $style .= "                position: relative;\n";
        $style .= "                margin: 0;\n";
        $style .= "                min-height: 100vh;\n";
        $style .= "            }\n\n";
        $style .= "            body.login_admin form,\n";
        $style .= "            body.customers_login form {\n";
        $style .= "                color: #000;\n";
        $style .= "            }\n\n";
        $style .= "            body.login_admin,\n";
        $style .= "            body.customers_login,\n";
        $style .= "            body.login_admin [class*=\"tw-text-neutral-\"],\n";
        $style .= "            body.customers_login [class*=\"tw-text-neutral-\"] {\n";
        $style .= "                color: {$login_page_text_color};\n";
        $style .= "            }\n\n";
        $style .= "            body.login_admin::before,\n";
        $style .= "            body.customers_login::before {\n";
        $style .= "                content: \"\";\n";
        $style .= "                position: fixed;\n";
        $style .= "                top: 0;\n";
        $style .= "                left: 0;\n";
        $style .= "                width: 100%;\n";
        $style .= "                height: 100%;\n";
        $style .= "                background-color: {$login_page_background_color};\n";
        if ($active_login_background) {
            $style .= "                background: url('{$login_background}') no-repeat center center fixed;\n";
        }
        $style .= "                background-size: cover;\n";
        $style .= "                z-index: -1;\n";
        $style .= "                pointer-events: none;\n";
        $style .= "            }\n";
        $style .= "        </style>\n";

        echo $style;
        ?>
        <?php
    }

    public function poly_utilities_cusomize_dashboard_page()
    {
        $existingAppearance = poly_utilities_common_helper::json_decode(get_option(POLY_UTILITIES_APPEARANCE_SETTINGS) ?? '', true);
        if (isset($existingAppearance['active']) && !$existingAppearance['active']) return;

        $active_dashboard_background = isset($existingAppearance['active_dashboard_background'])  && $existingAppearance['active_dashboard_background'];
        if ($active_dashboard_background) {
            $dashboard_background = $existingAppearance['dashboard_background'] ?? '';
        ?>
            <style>
                /* stylelint-disable */
                body {
                    background-image: url('<?php echo $dashboard_background ?>') !important;
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-attachment: fixed;
                    background-size: cover;
                }

                #wrapper {
                    background-color: unset;
                }
            </style>
        <?php
        }
    }

    public function poly_utilities_settings_scripts($is_widget = false)
    {
        $public_settings = $this->poly_utilities_settings;
        if (!empty($this->poly_utilities_settings)) {
            $public_settings = $this->poly_utilities_settings;
            unset($public_settings->data_filters);
            $public_settings = json_encode($public_settings);
        } else {
            $public_settings = [];
        }
        $poly_utilities_banners = poly_utilities_banners_helper::banners();
        ?>
        <script>
            var poly_utilities_settings = <?php echo $public_settings ?>;
            <?php
            if ($is_widget == true) {
            ?>
                poly_utilities_settings.banners_settings = <?php echo json_encode($poly_utilities_banners), false ?>; //Banners
                poly_utilities_settings.widgets = <?php echo poly_utilities_widget_helper::widgets_generate_content(true) ?>;
                poly_utilities_settings.widgets_hook = <?php echo poly_utilities_widget_helper::widgets_generate_content() ?>;
            <?php
            }
            ?>
            poly_utilities_settings.logged_client = <?php echo json_encode(poly_client_logged_in_can_access(), true); ?>;
            <?php

            // AIO Supports
            $poly_utilities_aio_supports = $this->get_aio_supports();
            if (!empty($poly_utilities_aio_supports)) { ?>
                poly_utilities_settings.aio_supports = <?php echo json_encode($poly_utilities_aio_supports, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            <?php } else { ?>
                poly_utilities_settings.aio_supports = null;
            <?php }
            ?>
        </script>
        <?php
    }

    public function hook_admins_admin_head()
    {
        $this->poly_utilities_settings('admin');
    }

    public function hook_customers_admin_head()
    {
        $this->poly_utilities_cusomize_login_page();

        $this->poly_utilities_settings_scripts();
        $this->poly_utilities_settings('customers');
        $this->poly_utilities_scripts_customers_public_head();
    }

    /**
     * Public shared CSS assets
     */
    public function poly_utilities_public_assets_css()
    {
        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/admin/flags/flag-icon.css') . '"/>';
        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/public/style.css') . '"/>';
    }

    public function poly_utilities_scripts_customers_public_head()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/head.js') . '"></script>';
    }

    public function poly_utilities_scripts_customers_public_scripts()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
    }

    /**
     * Initialize Multiple Companies Feature Hooks
     */
    public function init_multiple_companies_hooks()
    {
        // Disable contact email unique constraint
        hooks()->add_filter('contact_email_unique', function ($status) {
            return false;
        });

        // Client area: Load all companies for logged-in contact
        hooks()->add_action('after_clients_area_init', [$this, 'poly_mc_load_contact_companies']);

        // Client area: Add company navigation dropdown
        hooks()->add_action('customers_navigation_start', [$this, 'poly_mc_customers_navigation']);

        // Admin: Contact modal - show other companies with same email
        hooks()->add_action('after_contact_modal_content_loaded', [$this, 'poly_mc_contact_modal_script']);

        // Admin: Company profile form script
        hooks()->add_action('after_custom_profile_tab_content', [$this, 'poly_mc_company_modal_script']);

        // Admin footer: Load JS assets
        hooks()->add_action('app_admin_footer', [$this, 'poly_mc_admin_footer']);

        // Sync passwords across contacts with same email
        hooks()->add_action('contact_updated', [$this, 'poly_mc_sync_contact_password']);
        hooks()->add_action('contact_created', [$this, 'poly_mc_sync_contact_password']);

        // Handle profile and contact updates for multiple companies
        hooks()->add_action('after_clients_area_init', [$this, 'poly_mc_handle_profile_updates']);
        hooks()->add_action('after_clients_area_init', [$this, 'poly_mc_handle_contact_updates']);

        // AJAX search for multiple contacts
        hooks()->add_filter('get_relation_data', [$this, 'poly_mc_get_relation_data'], 11, 2);
        hooks()->add_filter('init_relation_options', [$this, 'poly_mc_init_relation_options'], 11, 2);
    }

    /**
     * Load all companies associated with the logged-in contact's email
     */
    public function poly_mc_load_contact_companies()
    {
        if (!$this->feature_multiple_companies) {
            return;
        }

        $contact_user_id = get_contact_user_id();

        if (empty($contact_user_id)) {
            unset($_SESSION['all_clients'], $_SESSION['client_user_id']);
            return;
        }

        static $loadedContacts = [];
        if (isset($loadedContacts[$contact_user_id])) {
            return;
        }

        $table = db_prefix() . 'contacts contact';

        $info = $this->CI->db->select('email')
            ->where('id', $contact_user_id)
            ->get($table)
            ->row();

        if (!empty($info->email)) {
            $tableClient = db_prefix() . 'clients client';

            static $companiesCache = [];
            if (isset($companiesCache[$info->email])) {
                $clients = $companiesCache[$info->email];
            } else {
                $clients = $this->CI->db->select('client.company, contact.userid, contact.id')
                    ->from($table)
                    ->join($tableClient, 'contact.userid = client.userid')
                    ->where('contact.email', $info->email)
                    ->order_by('client.company')
                    ->get()
                    ->result();
                $companiesCache[$info->email] = $clients;
            }

            foreach ($clients as $client) {
                if ($client->id == $contact_user_id) {
                    $_SESSION['client_user_id'] = $client->userid;
                }
            }

            if (count($clients) > 1) {
                $_SESSION['all_clients'] = $clients;
            } else {
                unset($_SESSION['all_clients']);
            }
        }

        $loadedContacts[$contact_user_id] = true;
    }

    /**
     * Display company switcher dropdown in customer navigation (with avatars)
     */
    public function poly_mc_customers_navigation()
    {
        if (!$this->feature_multiple_companies) {
            return;
        }

        if (!is_client_logged_in() || empty($_SESSION['all_clients']) || count($_SESSION['all_clients']) <= 1) {
            return;
        }

        $get_client_user_id = get_client_user_id();
        $current_company = poly_mc_get_current_company();
        $current_company_name = $current_company ? $current_company->company : get_company_name($get_client_user_id);
        
        // Get current company avatar/logo
        $current_avatar = '';
        if ($current_company) {
            $company_logo = poly_mc_get_company_logo($current_company->userid, 24);
            $current_avatar = !empty($company_logo) 
                ? $company_logo 
                : poly_mc_get_contact_avatar($current_company->id, $current_company->email ?? '', 24);
        }
        ?>
        <ul class="nav navbar-nav navbar-left">
            <li class="dropdown poly-mc-dropdown">
                <a href="#" class="dropdown-toggle tw-flex tw-items-center" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <?php if ($current_avatar): ?>
                        <img src="<?php echo $current_avatar; ?>" class="tw-w-6 tw-h-6 tw-rounded-full tw-mr-2" alt="<?php echo htmlspecialchars($current_company_name); ?>" style="display: inline-block; vertical-align: middle;">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($current_company_name); ?></span>
                    <i class="fa fa-caret-down tw-ml-1" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu animated fadeIn poly-mc-dropdown-menu">
                    <li class="dropdown-header">
                        <small class="tw-text-muted"><?php echo _l('poly_mc_switch_company'); ?></small>
                    </li>
                    <?php 
                    foreach ($_SESSION['all_clients'] as $all_client) {
                        $is_active = $get_client_user_id == $all_client->userid;
                        echo poly_mc_format_dropdown_item($all_client, $is_active);
                    } 
                    ?>
                </ul>
            </li>
        </ul>
        
        <style>
        .poly-mc-dropdown .dropdown-toggle {
            padding: 10px 15px !important;
        }
        .poly-mc-dropdown-menu {
            min-width: 250px;
            max-height: 400px;
            overflow-y: auto;
        }
        .poly-mc-dropdown-menu .dropdown-header {
            padding: 8px 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .poly-mc-dropdown-menu li a {
            padding: 10px 15px !important;
            transition: all 0.2s ease;
        }
        .poly-mc-dropdown-menu li a:hover {
            background-color: #f5f7fa;
        }
        .poly-mc-dropdown-menu li.active a {
            background-color: #e3f2fd;
            font-weight: 600;
        }
        .poly-mc-dropdown-menu img {
            border: 2px solid #e0e0e0;
        }
        .poly-mc-dropdown-menu li.active img {
            border-color: #2196F3;
        }
        </style>
        <?php
    }

    /**
     * Add contact modal script for checking email across companies
     */
    public function poly_mc_contact_modal_script()
    {
        if (!$this->feature_multiple_companies) {
            return;
        }

        $this->CI->load->view('poly_utilities/multiple_companies/contact_modal_script');
    }

    /**
     * Add company modal script
     */
    public function poly_mc_company_modal_script()
    {
        if (!$this->feature_multiple_companies) {
            return;
        }

        $this->CI->load->view('poly_utilities/multiple_companies/company_modal_script');
    }

    /**
     * Add JS assets and container div to admin footer
     */
    public function poly_mc_admin_footer()
    {
        if (!$this->feature_multiple_companies) {
            return;
        }

        echo '<div id="poly_multiple_companies_div_content"></div>';
        echo '<script>';
        echo 'var poly_mc_existing_user_lang = "' . _l('poly_mc_existing_user') . '";';
        echo '</script>';
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/multiple_companies.js') . '"></script>';
    }

    /**
     * Sync password across all contacts with the same email
     */
    public function poly_mc_sync_contact_password($contact_id)
    {
        if (!empty($contact_id)) {
            $contact = $this->CI->db->select('password,email')
                ->from(db_prefix() . 'contacts')
                ->where('id', $contact_id)
                ->get()
                ->row();

            if (!empty($contact->password)) {
                $this->CI->db->set('password', $contact->password)
                    ->set('last_password_change', date('Y-m-d H:i:s'))
                    ->where('email', $contact->email)
                    ->update(db_prefix() . 'contacts');
            }
        }
    }

    /**
     * Handle profile updates for contacts with multiple companies
     */
    public function poly_mc_handle_profile_updates($page_obj)
    {
        if (!poly_mc_has_multiple_companies()) {
            return;
        }

        $called_function = $page_obj->router->fetch_method();

        if ($called_function != 'profile') {
            return;
        }

        // Handle password change
        if ($page_obj->input->post('change_password')) {
            $this->poly_mc_process_password_change($page_obj);
        }

        // Handle profile update
        if ($page_obj->input->post('profile')) {
            $this->poly_mc_process_profile_update($page_obj);
        }
    }

    /**
     * Helper: Check if should handle multiple companies
     * @return bool
     */
    private function should_handle_multiple_companies()
    {
        return poly_mc_has_multiple_companies();
    }

    /**
     * Process password change for all contacts with same email
     */
    private function poly_mc_process_password_change($page_obj)
    {
        $page_obj->form_validation->set_rules('oldpassword', _l('clients_edit_profile_old_password'), 'required');
        $page_obj->form_validation->set_rules('newpassword', _l('clients_edit_profile_new_password'), 'required');
        $page_obj->form_validation->set_rules('newpasswordr', _l('clients_edit_profile_new_password_repeat'), 'required|matches[newpassword]');

        if ($page_obj->form_validation->run() !== false) {
            $oldPassword = $page_obj->input->post('oldpassword', false);
            $newPassword = $page_obj->input->post('newpasswordr', false);

            $password_control = false;

            foreach ($_SESSION['all_clients'] as $all_client) {
                $page_obj->db->where('id', $all_client->id);
                $client = $page_obj->db->get(db_prefix() . 'contacts')->row();

                if (app_hasher()->CheckPassword($oldPassword, $client->password)) {
                    $password_control = true;
                }
            }

            if ($password_control) {
                $password_control = false;

                foreach ($_SESSION['all_clients'] as $all_client) {
                    $page_obj->db->where('id', $all_client->id);
                    $page_obj->db->update(db_prefix() . 'contacts', [
                        'last_password_change' => date('Y-m-d H:i:s'),
                        'password' => app_hash_password($newPassword),
                    ]);

                    if ($page_obj->db->affected_rows() > 0) {
                        log_activity('Contact Password Changed [ContactID: ' . $all_client->id . ']');
                        $password_control = true;
                    }
                }

                $success = $password_control;
            } else {
                $success = ['old_password_not_match' => true];
            }

            if (is_array($success) && isset($success['old_password_not_match'])) {
                set_alert('danger', _l('client_old_password_incorrect'));
            } elseif ($success == true) {
                set_alert('success', _l('client_password_changed'));
            }

            redirect(site_url('clients/profile'));
        }
    }

    /**
     * Process profile update (refactored for clarity)
     */
    private function poly_mc_process_profile_update($page_obj)
    {
        $this->poly_mc_set_profile_validation_rules($page_obj);

        if ($page_obj->form_validation->run() !== false) {
            $check_email = $page_obj->input->post('email');

            if ($this->poly_mc_check_company_email_is_correct($check_email, get_contact_user_id())) {
                handle_contact_profile_image_upload();

                $data = $page_obj->input->post();
                $contact = $page_obj->clients_model->get_contact(get_contact_user_id());

                // Get email notification permissions
                $permissions = $this->poly_mc_get_email_notification_permissions($contact, $data);

                // Prepare update data
                $update_data = array_merge([
                    'firstname' => $page_obj->input->post('firstname'),
                    'lastname' => $page_obj->input->post('lastname'),
                    'title' => $page_obj->input->post('title'),
                    'email' => $page_obj->input->post('email'),
                    'phonenumber' => $page_obj->input->post('phonenumber'),
                    'direction' => $page_obj->input->post('direction'),
                    'custom_fields' => isset($data['custom_fields']) && is_array($data['custom_fields']) ? $data['custom_fields'] : [],
                ], $permissions);

                $success = $page_obj->clients_model->update_contact($update_data, get_contact_user_id(), true);

                if ($success == true) {
                    set_alert('success', _l('clients_profile_updated'));
                }

                redirect(site_url('clients/profile'));
            }
        }
    }

    /**
     * Set profile validation rules
     */
    private function poly_mc_set_profile_validation_rules($page_obj)
    {
        $page_obj->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
        $page_obj->form_validation->set_rules('lastname', _l('client_lastname'), 'required');
        $page_obj->form_validation->set_rules('email', _l('clients_email'), 'required|valid_email');

        $custom_fields = get_custom_fields('contacts', [
            'show_on_client_portal' => 1,
            'required' => 1,
            'disalow_client_to_edit' => 0,
        ]);

        foreach ($custom_fields as $field) {
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if ($field['type'] == 'checkbox' || $field['type'] == 'multiselect') {
                $field_name .= '[]';
            }
            $page_obj->form_validation->set_rules($field_name, $field['name'], 'required');
        }
    }

    /**
     * Get email notification permissions based on contact permissions
     */
    private function poly_mc_get_email_notification_permissions($contact, $data)
    {
        $permissions = [];

        if (has_contact_permission('invoices')) {
            $permissions['invoice_emails'] = isset($data['invoice_emails']) ? 1 : 0;
            $permissions['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 : 0;
        } else {
            $permissions['invoice_emails'] = $contact->invoice_emails;
            $permissions['credit_note_emails'] = $contact->credit_note_emails;
        }

        if (has_contact_permission('estimates')) {
            $permissions['estimate_emails'] = isset($data['estimate_emails']) ? 1 : 0;
        } else {
            $permissions['estimate_emails'] = $contact->estimate_emails;
        }

        if (has_contact_permission('support')) {
            $permissions['ticket_emails'] = isset($data['ticket_emails']) ? 1 : 0;
        } else {
            $permissions['ticket_emails'] = $contact->ticket_emails;
        }

        if (has_contact_permission('contracts')) {
            $permissions['contract_emails'] = isset($data['contract_emails']) ? 1 : 0;
        } else {
            $permissions['contract_emails'] = $contact->contract_emails;
        }

        if (has_contact_permission('projects')) {
            $permissions['project_emails'] = isset($data['project_emails']) ? 1 : 0;
            $permissions['task_emails'] = isset($data['task_emails']) ? 1 : 0;
        } else {
            $permissions['project_emails'] = $contact->project_emails;
            $permissions['task_emails'] = $contact->task_emails;
        }

        return $permissions;
    }

    /**
     * Handle contact add/edit in client area
     */
    public function poly_mc_handle_contact_updates($page_obj)
    {
        if (!poly_mc_has_multiple_companies()) {
            return;
        }

        $called_function = $page_obj->router->fetch_method();

        if ($called_function == 'contact' && $page_obj->input->post()) {
            $contact_id = str_replace('contacts/contact', '', $page_obj->uri->uri_string);
            $contact_id = str_replace('/', '', $contact_id);

            // Note: Extended validation and processing can be added here if needed
            // This hook allows for future enhancements without breaking existing functionality
        }
    }

    /**
     * Check if company email is correct (not used in other customers)
     */
    private function poly_mc_check_company_email_is_correct($email = '', $contact_id = 0)
    {
        if (empty($email)) {
            return true;
        }

        if (!empty($contact_id)) {
            $info = $this->CI->db->select('email')
                ->from(db_prefix() . 'contacts')
                ->where('id', $contact_id)
                ->get()
                ->row();

            if (!empty($info->email) && $info->email == $email) {
                return true;
            }
        }

        return total_rows(db_prefix() . 'contacts', 'userid != ' . get_client_user_id() . ' AND email="' . $this->CI->db->escape_str($email) . '"') > 0 ? false : true;
    }

    /**
     * AJAX search data for multiple contacts
     */
    public function poly_mc_get_relation_data($data, $filter)
    {
        if (!empty($filter['type']) && $filter['type'] == 'multiple_contact') {
            $q = '';
            if (!empty($this->CI->input->post('q'))) {
                $q = trim($this->CI->input->post('q'));
            }

            $company_id = $this->CI->input->post('mt_customer_id');
            $where_contacts = db_prefix() . 'contacts.active=1';

            $company_emails = '';
            $company_contacts = $this->CI->db->select('email')
                ->from(db_prefix() . 'contacts')
                ->where('userid', $company_id)
                ->get()
                ->result();

            if (!empty($company_contacts)) {
                foreach ($company_contacts as $company_contact) {
                    if (!empty($company_contact->email)) {
                        if ($company_emails != '') {
                            $company_emails .= ' , ';
                        }
                        $company_emails .= "'" . $company_contact->email . "'";
                    }
                }

                if ($company_emails != '') {
                    $where_contacts .= ' AND ' . db_prefix() . 'contacts.email NOT IN ( ' . $company_emails . ' ) ';
                }
            }

            $this->CI->db->select(implode(',', prefixed_table_fields_array(db_prefix() . 'contacts')) . ',company');
            $this->CI->db->from(db_prefix() . 'contacts');
            $this->CI->db->join(db_prefix() . 'clients', '' . db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
            $this->CI->db->where('(firstname LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR lastname LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR email LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(firstname, \' \', lastname) LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR CONCAT(lastname, \' \', firstname) LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . 'contacts.phonenumber LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR ' . db_prefix() . 'contacts.title LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\'
                OR company LIKE "%' . $this->CI->db->escape_like_str($q) . '%" ESCAPE \'!\')');

            $this->CI->db->where($where_contacts);
            $this->CI->db->group_by('email');
            $this->CI->db->order_by('firstname', 'ASC');

            $data = $this->CI->db->get()->result_array();
        }

        return $data;
    }

    /**
     * Initialize relation options for contact selection
     */
    public function poly_mc_init_relation_options($_data, $filter)
    {
        if (!empty($filter['data']) && $filter['type'] == 'multiple_contact') {
            $data = $filter['data'];

            foreach ($data as $relation) {
                $userid = $relation['userid'];
                $id = $relation['id'];
                $name = $relation['firstname'] . ' ' . $relation['lastname'];
                $subtext = $relation['email'];
                $link = admin_url('clients/client/' . $userid . '?contactid=' . $id);

                $relation_values = [
                    'id' => $id,
                    'name' => $name,
                    'link' => $link,
                    'subtext' => $subtext,
                ];

                $_data[] = $relation_values;
            }
        }

        return $_data;
    }

    public function register_module_activation_hook()
    {
        require_once(__DIR__ . '/install.php');

        // Register the routes and hoooks
        poly_utilities_common_helper::require_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
        
        // Register global hooks for view overrides (unlimited menu levels support)
        poly_utilities_common_helper::require_in_file(APPPATH . 'config/my_hooks.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_hooks.php'");
        
        // Manually add poly_utilities menu items to system menu before syncing
        // This ensures menu items are available for sync even if admin_init hasn't run yet
        $this->restore_poly_utilities_menu_items();
        
        // Sync system menus to database (ensure all menus have IDs)
        // This will sync poly_utilities menu items that were just added above
        // IMPORTANT: This sync will:
        // - INSERT new menu items from code that don't exist in database
        // - UPDATE existing menu items (href, name, icon, position) to reflect code changes
        // - DELETE orphaned menu items that were removed from code
        // This ensures database always reflects current code state, even when routes change
        if (function_exists('poly_force_sync_all_menus')) {
            poly_force_sync_all_menus(true); // Force update to catch route changes
        }
        
        // Initialize default clients menu if empty
        if (function_exists('poly_init_default_clients_menu')) {
            poly_init_default_clients_menu();
        }
    }
    
    /**
     * Manually restore poly_utilities menu items to system menu
     * This is called during activation to ensure menu items are available for sync
     * 
     * @return void
     */
    private function restore_poly_utilities_menu_items()
    {
        $CI = &get_instance();
        
        // Check if app_menu is available
        if (!isset($CI->app_menu)) {
            return;
        }
        
        // Add setup menu item (without permission check during activation)
        $CI->app_menu->add_setup_menu_item('poly_utilities_settings', [
            'name'     => _l('poly_utilities_name'),
            'href'     => admin_url('poly_utilities/custom_menu'),
            'position' => 9999,
        ]);
        
        // Add sidebar menu item (without permission check during activation)
        $CI->app_menu->add_sidebar_menu_item('poly_utilities', [
            'name'     => _l('poly_utilities_name'),
            'collapse' => true,
            'icon'     => 'fas fa-user-clock',
            'position' => 3,
        ]);
        
        // Add child menu items (add all items during activation, permissions checked later)
        $menuItems = [
                [
                    'slug'       => 'poly_utilities_shortcut_menu_extend',
                    'name'       => _l('poly_utilities_shortcut_menu_extend'),
                    'icon'       => 'fa-solid fa-list-check',
                    'href'       => admin_url('poly_utilities/quick_access'),
                    'position'   => 1,
                    'permission' => 'poly_utilities_shortcut_menu_extend',
                ],
                [
                    'slug'       => 'poly_utilities_custom_menu_extend',
                    'name'       => _l('poly_utilities_custom_menu_extend'),
                    'icon'       => 'fa-solid fa-list-ul fa-fw',
                    'href'       => admin_url('poly_utilities/custom_menu'),
                    'position'   => 2,
                    'permission' => 'poly_utilities_custom_menu_extend',
                ],
                [
                    'slug'       => 'poly_utilities_context_menu',
                    'name'       => _l('poly_utilities_context_menu'),
                    'icon'       => 'fa-regular fa-rectangle-list fa-fw',
                    'href'       => admin_url('poly_utilities/context_menu'),
                    'position'   => 3,
                    'permission' => 'poly_utilities_context_menu',
                ],
                [
                    'slug'       => 'poly_utilities_fixed_bottom_menu',
                    'name'       => _l('poly_utilities_fixed_bottom_menu'),
                    'icon'       => 'fa-solid fa-bars-staggered fa-fw',
                    'href'       => admin_url('poly_utilities/fixed_bottom_menu'),
                    'position'   => 4,
                    'permission' => 'poly_utilities_fixed_bottom_menu',
                ],
                [
                    'slug'       => 'poly_utilities_widgets_extend',
                    'name'       => _l('poly_utilities_widgets_extend'),
                    'icon'       => 'fa-solid fa-palette fa-fw',
                    'href'       => admin_url('poly_utilities/widgets'),
                    'position'   => 5,
                    'permission' => 'poly_utilities_widgets_extend',
                ],
                [
                    'slug'       => 'poly_utilities_scripts_extend',
                    'name'       => _l('poly_utilities_scripts_extend'),
                    'icon'       => 'fas fa-file-code',
                    'href'       => admin_url('poly_utilities/scripts'),
                    'position'   => 6,
                    'permission' => 'poly_utilities_scripts_extend',
                ],
                [
                    'slug'       => 'poly_utilities_styles_extend',
                    'name'       => _l('poly_utilities_styles_extend'),
                    'icon'       => 'fas fa-file-alt',
                    'href'       => admin_url('poly_utilities/styles'),
                    'position'   => 7,
                    'permission' => 'poly_utilities_styles_extend',
                ],
                [
                    'slug'       => 'poly_utilities_supports',
                    'name'       => _l('poly_utilities_support'),
                    'icon'       => 'fa-solid fa-headset',
                    'href'       => admin_url('poly_utilities/support'),
                    'position'   => 8,
                    'permission' => 'poly_utilities_supports',
                ],
                [
                    'slug'       => 'poly_utilities_banners',
                    'name'       => _l('poly_utilities_banners'),
                    'icon'       => 'fa-solid fa-photo-film fa-fw',
                    'href'       => admin_url('poly_utilities/banners'),
                    'position'   => 9,
                    'permission' => 'poly_utilities_banners',
                ],
                [
                    'slug'       => 'poly_utilities_projects',
                    'name'       => _l('poly_utilities_projects'),
                    'icon'       => 'fa-solid fa-diagram-project fa-fw',
                    'href'       => admin_url('poly_utilities/projects'),
                    'position'   => 10,
                    'permission' => 'poly_utilities_projects',
                ],
                [
                    'slug'       => 'poly_utilities_task_templates',
                    'name'       => _l('poly_utilities_task_templates'),
                    'icon'       => 'fa-solid fa-tasks fa-fw',
                    'href'       => admin_url('poly_utilities/task_templates'),
                    'position'   => 10.5,
                    'permission' => 'poly_utilities_task_templates',
                ],
                [
                    'slug'       => 'poly_utilities_appearance',
                    'name'       => _l('poly_utilities_appearance'),
                    'icon'       => 'fa-solid fa-building-lock fa-fw',
                    'href'       => admin_url('poly_utilities/appearance'),
                    'position'   => 11,
                    'permission' => 'poly_utilities_appearance',
                ],
                [
                    'slug'       => 'poly_utilities_settings',
                    'name'       => _l('poly_utilities_settings'),
                    'icon'       => 'fa fa-cog',
                    'href'       => admin_url('poly_utilities/settings'),
                    'position'   => 11,
                    'permission' => 'poly_utilities_settings',
                ],
            ];
            
        foreach ($menuItems as $item) {
            // During activation, add all items without permission checks
            // This ensures items are available in database for sync
            // Permissions will be checked when menu is rendered
            $CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => $item['slug'],
                'name'     => $item['name'],
                'icon'     => $item['icon'],
                'href'     => $item['href'],
                'position' => $item['position'],
            ]);
        }
    }

    public function register_module_deactivation_hook()
    {
        $CI = &get_instance();
        
        // Delete menu items with module_name = 'poly_utilities' when deactivating module
        if ($CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            try {
                // Check if module_name column exists
                if ($CI->db->field_exists('module_name', db_prefix() . 'poly_utilities_custom_menus')) {
                    // Delete menu items with module_name = 'poly_utilities'
                    // Foreign key CASCADE will automatically delete children if they have parent_id pointing to these items
                    $CI->db->where('module_name', POLY_UTILITIES_MODULE_NAME);
                    $CI->db->delete(db_prefix() . 'poly_utilities_custom_menus');
                }
            } catch (Exception $e) {
                //log_message('error', 'Poly Utilities: Error deleting menu items during deactivation: ' . $e->getMessage());
            }
        }
        
        // Remove the routes and hooks
        poly_utilities_common_helper::unrequire_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
        
        // Remove global hooks
        poly_utilities_common_helper::unrequire_in_file(APPPATH . 'config/my_hooks.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_hooks.php'");
    }

    /**
     * Enqueues scripts and styles common Admin & Clients.
     * @return void
     */
    public function poly_utilities_settings($mode)
    {
        // Bind Banners
        $poly_utilities_banners = poly_utilities_banners_helper::banners();
        if (!empty($poly_utilities_banners)) {
        ?>
            <script>
                poly_utilities_settings.banners_settings = <?php echo json_encode($poly_utilities_banners, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            </script>
        <?php
        }

        // Bind Widgets
        $widgets = poly_utilities_widget_helper::widgets_generate_content(true);
        $widgets_hook = poly_utilities_widget_helper::widgets_generate_content();
        ?>
        <script>
            <?php if (!empty($widgets)) { ?>
                poly_utilities_settings.widgets = <?php echo $widgets; ?>;
            <?php } ?>
            <?php if (!empty($widgets_hook)) { ?>
                poly_utilities_settings.widgets_hook = <?php echo $widgets_hook; ?>;
            <?php } ?>
        </script>
        <?php

        // Bind Menu - Load from database (only when feature enabled)
        $sidebar_menu_slim = [];
        $setup_menu_slim = [];
        $clients_menu_slim = [];

        if ($this->feature_custom_menu_hooks) {
            $sidebar_menu_slim = [];
            $setup_menu_slim = [];
            $clients_menu_slim = [];

            if ($this->feature_custom_menu_hooks) {
                $sidebar_menu_slim = poly_utilities_custom_menu_slim('sidebar');
                $setup_menu_slim = poly_utilities_custom_menu_slim('setup');
                $clients_menu_slim = poly_utilities_custom_menu_slim('clients');
            }

            $context_menu = get_option(POLY_CONTEXT_MENU);
            $context_menu_display = poly_utilities_common_helper::json_decode($context_menu, TRUE);

            if (!empty($context_menu_display)) {
                $active_context_menu = array_values(array_filter($context_menu_display, function ($item) {
                    return isset($item['disabled']) && $item['disabled'] !== "false";
                }));
        ?>
                <script>
                    let menuItemsDisplay = {};
                    <?php
                    $menu_data = [
                        'context_menu' => $active_context_menu ?? null
                    ];

                    foreach ($menu_data as $key => $value) {
                        if (!empty($value)) { ?>
                            menuItemsDisplay['<?php echo $key; ?>'] = <?php echo json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    <?php   }
                    }
                    ?>
                    poly_utilities_settings.menu_display = menuItemsDisplay;
                </script>
        <?php
            }
        }

        // Fixed Bottom Menu - Load independently (not affected by enable_custom_menu_hooks)
        $fixed_bottom_menu = get_option(POLY_FIXED_BOTTOM_MENU);
        $fixed_bottom_menu_display = poly_utilities_common_helper::json_decode($fixed_bottom_menu, TRUE);

        if (!empty($fixed_bottom_menu_display)) {
            // Process fixed bottom menu items through helper to set href for iframe type
            poly_utilities_menu_sidebar_define_by_type($fixed_bottom_menu_display);
            
            $active_fixed_bottom_menu = array_values(array_filter($fixed_bottom_menu_display, function ($item) {
                return isset($item['disabled']) && $item['disabled'] !== "false";
            }));
    ?>
            <script>
                if (!poly_utilities_settings.menu_display) {
                    poly_utilities_settings.menu_display = {};
                }
                <?php
                if (!empty($active_fixed_bottom_menu)) { ?>
                    poly_utilities_settings.menu_display['fixed_bottom_menu'] = <?php echo json_encode($active_fixed_bottom_menu, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                <?php }
                ?>
            </script>
    <?php
        }

        // Custom Menu Hooks - Only load sidebar/setup/clients menu when feature enabled
        if ($this->feature_custom_menu_hooks) {
            if (!empty($sidebar_menu_slim) || !empty($setup_menu_slim) || !empty($clients_menu_slim)) {
        ?>
                <script>
                    let menuItems = {};
                    <?php
                    $menu_data = [
                        'sidebar' => $sidebar_menu_slim ?? null,
                        'setup' => $setup_menu_slim ?? null,
                        'clients' => $clients_menu_slim ?? null,
                    ];

                    foreach ($menu_data as $key => $value) {
                        if (!empty($value)) { ?>
                            menuItems['<?php echo $key; ?>'] = <?php echo json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    <?php   }
                    }
                    ?>
                    poly_utilities_settings.menu = menuItems;
                </script>
        <?php
            }
        }
    }


    public function hook_customize_scripts_styles_admin_header()
    {
        $this->poly_utilities_resource_css_files('admin', 'header');
        $this->poly_utilities_resource_js_files('admin', 'header');
    }

    /**
     * Enqueues scripts and styles for Admin (Footer).
     * @return void
     */
    public function hook_customize_scripts_styles_admin_footer()
    {
        $this->poly_utilities_resource_css_files('admin', 'footer');
        $this->poly_utilities_resource_js_files('admin', 'footer');
    }

    public function hook_customize_scripts_styles_customer_header()
    {
        $this->poly_utilities_resource_css_files('customers', 'header');
        $this->poly_utilities_resource_js_files('customers', 'header');
    }

    public function hook_customize_scripts_styles_customer_footer()
    {
        if (is_client_logged_in()) {
            $this->poly_utilities_resource_css_files('customers', 'footer');
            $this->poly_utilities_resource_js_files('customers', 'footer');
        }
    }

    /**
     * Enqueues scripts and styles for Clients (Footer)).
     * @return void
     */
    public function hook_scripts_styles_clients_footer()
    {
        $this->poly_utilities_styles_customers();

        $this->poly_utilities_js_library();

        $this->poly_utilities_scripts_customers();
    }

    public function poly_utilities_js_library()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/sweetalert2/11.7.31/sweetalert2.min.js') . '"></script>';
        echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/clipboardjs/2.0.11/clipboard.min.js') . '"></script>';
    }

    public function poly_utilities_styles_customers()
    {
        $this->poly_utilities_public_assets_css();
    }

    public function poly_utilities_scripts_customers()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
    }

    /**
     * Enqueues JavaScript files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_js_files($mode_area, $position = 'footer')
    {
        if (property_exists($this->poly_utilities_settings, 'is_active_scripts') && $this->poly_utilities_settings->is_active_scripts !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_SCRIPTS));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::read_file($resource->file . '.js', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/js');
                        } else {
                            echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/uploads/js/' . $resource->file . '.js', true, true) . '"></script>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Enqueues Cascading Style Sheet files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_css_files($mode_area, $position = 'header')
    {
        if (property_exists($this->poly_utilities_settings, 'is_active_styles') && $this->poly_utilities_settings->is_active_styles !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_STYLES));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::read_file($resource->file . '.css', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/css');
                        } else {
                            echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/uploads/css/' . $resource->file . '.css', true, true) . '"/>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Load CSS/JS assets in the head
     * @return void
     */
    public function assets_head()
    {
        /*if (!defined('POLY_UTILITIES_FORCE_JQUERY')) {
            echo '<script src="' . base_url('assets/plugins/jquery/jquery.min.js') . '"></script>';
            define('POLY_UTILITIES_FORCE_JQUERY', true);
        }*/

        // Handle for data filter
        if ($this->CI->session->staff_user_id) {
            $this->poly_utilities_settings->is_admin = is_admin() ? 'true' : 'false';
            $this->poly_utilities_settings->uid = $this->CI->session->staff_user_id;
            $this->poly_utilities_settings->segments = $this->CI->uri->segments;
            $this->poly_utilities_settings->version = $this->CI->app_css->core_version();
            if (isset($this->CI->load->_ci_cached_vars['tab']) && $this->CI->load->_ci_cached_vars['tab']) {
                $this->poly_utilities_settings->tab = $this->CI->load->_ci_cached_vars['tab'];
            } else {
                $this->poly_utilities_settings->tab = '';
            }
        }

        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/admin/style.css') . '"/>';

        $this->poly_utilities_public_assets_css();

        $this->poly_utilities_cusomize_dashboard_page();

        ?>
        <script>
            <?php
            $confirmPopup = _l('poly_utilities_delete_object');
            if ($confirmPopup === 'poly_utilities_delete_object') {
                $decodedPopup = '{}';
            } else {
                $decodedPopup = html_entity_decode($confirmPopup);
            }

            $modalObjects = _l('poly_utilities_modals');
            if ($modalObjects === 'poly_utilities_modals') {
                $decodedModals = '[]';
            } else {
                $decodedModals = html_entity_decode($modalObjects);
            }

            $tableOfContents = _l('poly_utilities_table_of_contents_header');
            $tableOfContents = ($tableOfContents === 'poly_utilities_table_of_contents_header') ? '' : html_entity_decode($tableOfContents);

            $favicon = get_option('favicon');
            $favicon_path = (!empty($favicon)) ? base_url('uploads/company/' . $favicon) : '';

            $sidebar_menu_slim = poly_utilities_custom_menu_slim('sidebar');
            $setup_menu_slim = poly_utilities_custom_menu_slim('setup');
            $clients_menu_slim = poly_utilities_custom_menu_slim('clients');
            ?>
            var poly_utilities_settings = <?php echo (!empty($this->poly_utilities_settings) ? json_encode($this->poly_utilities_settings) : []) ?>;
            var poly_quick_access_menu = <?php echo json_encode(!empty($this->quick_access_menu) ? $this->quick_access_menu : []); ?>;

            poly_utilities_settings.favicon_path = '<?php echo $favicon_path ?>';
            poly_utilities_settings.popup_delete = <?php echo $decodedPopup ?>;
            poly_utilities_settings.modals = <?php echo $decodedModals ?>;
            poly_utilities_settings.table_of_content_header = '<?php echo $tableOfContents ?>';

            poly_utilities_settings.lang = <?php echo poly_utilities_common_helper::render_language() ?: '{}'; ?>;

            poly_utilities_settings.alphabet = <?php echo json_encode(poly_utilities_common_helper::$alphabet, true) ?>;
            poly_utilities_settings.numbers = <?php echo json_encode(poly_utilities_common_helper::$numbers, true) ?>;
            poly_utilities_settings.targets = <?php echo json_encode(poly_utilities_common_helper::$targets, true) ?>;
            poly_utilities_settings.rels = <?php echo json_encode(poly_utilities_common_helper::$rels, true) ?>;

            // Integration | Perfex CRM - Flat Theme for Admin (Backend) Interface
            <?php
            $is_flatadmintheme = $this->CI->app_modules->is_active('flatadmintheme') ? 'true' : 'false';
            ?>
            poly_utilities_settings.integration = {};
            poly_utilities_settings.integration.is_flatadmintheme = <?php echo $is_flatadmintheme ?>;
            <?php
            // Integration | Perfex CRM - Flat Theme for Admin (Backend) Interface
            ?>

            //Widgets
            poly_utilities_settings.widgets = <?php echo poly_utilities_widget_helper::widgets_generate_content(true) ?>;
            poly_utilities_settings.widgets_hook = <?php echo poly_utilities_widget_helper::widgets_generate_content() ?>;

            // Menu
            poly_utilities_settings.menu = {
                sidebar: <?php echo json_encode($sidebar_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                setup: <?php echo json_encode($setup_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                clients: <?php echo json_encode($clients_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
            };
            <?php

            $poly_utilities_aio_supports = $this->get_aio_supports();
            if (!empty($poly_utilities_aio_supports)) { ?>
                poly_utilities_settings.aio_supports = <?php echo json_encode($poly_utilities_aio_supports, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            <?php } else { ?>
                poly_utilities_settings.aio_supports = null;
            <?php }
            ?>
        </script>

    <?php
    }

    public function get_aio_supports()
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];

        if (
            !empty($poly_utilities_aio_supports) &&
            (($poly_utilities_aio_supports['is_admin'] === 'true') ||
                ($poly_utilities_aio_supports['is_clients'] === 'true'))
        ) {
            return $poly_utilities_aio_supports;
        }

        return null;
    }


    /**
     * Load CSS/JS assets in the footer
     * @return void
     */
    public function assets_footer()
    {
        // Projects & Tasks Modals: feature menu supporting the creation of contracts, estimates, and proposals on the project detail page.
        if (staff_can('view', 'projects')) {
            if (($this->CI->uri->segment(2) == 'projects' && $this->CI->uri->segment(3) == 'view')) {
                // Contract
                $title = _l('add_new', _l('contract_lowercase'));
                $this->CI->load->model('currencies_model');
                $this->CI->load->model('staff_model');

                $data['staff']         = $this->CI->staff_model->get('', ['active' => 1]);
                $data['currencies']    = $this->CI->currencies_model->get();

                $data['base_currency'] = $this->CI->currencies_model->get_base_currency();
                $data['types']         = $this->CI->contracts_model->get_contract_types();
                $data['title']         = $title;

                $this->CI->load->view(POLY_UTILITIES_MODULE_NAME . '/projects/add_contract', $data);

                // Proposals
                $this->CI->load->model('taxes_model');
                $data['taxes'] = $this->CI->taxes_model->get();
                $this->CI->load->model('invoice_items_model');
                $data['ajaxItems'] = false;
                if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
                    $data['items'] = $this->CI->invoice_items_model->get_grouped();
                } else {
                    $data['items']     = [];
                    $data['ajaxItems'] = true;
                }
                $data['items_groups'] = $this->CI->invoice_items_model->get_groups();

                $data['statuses']      = $this->CI->proposals_model->get_statuses();
                $data['staff']         = $this->CI->staff_model->get('', ['active' => 1]);
                $this->CI->load->view(POLY_UTILITIES_MODULE_NAME . '/projects/add_proposal', $data);

                // Estimate
                $data['estimate_statuses'] = $this->CI->estimates_model->get_statuses();
                $this->CI->load->view(POLY_UTILITIES_MODULE_NAME . '/projects/add_estimate', $data);

                // Add item
                $this->CI->load->view('admin/invoice_items/item');
            }
        }
        // Projects & Tasks Modals: feature menu supporting the creation of contracts, estimates, and proposals on the project detail page.

        // Assets
        if (!defined('POLY_UTILITIES_JQUERY_FALLBACK')) {
            $jqueryPath = base_url('assets/plugins/jquery/jquery.min.js');
            echo '<script>window.jQuery || document.write(\'<script src="' . $jqueryPath . '"><\\/script>\');</script>';
            define('POLY_UTILITIES_JQUERY_FALLBACK', true);
        }
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/head.js') . '"></script>';
        $this->poly_utilities_js_library();

        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/script.js') . '"></script>';
        
        // Projects items management - handle multiple modals
        if ($this->CI->uri->segment(2) == 'projects' && $this->CI->uri->segment(3) == 'view') {
            echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/projects_items.js') . '"></script>';
        }

        if ($this->feature_multiple_addresses && ($this->is_customer_addresses_context() || $this->is_sales_document_address_context())) {
            echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/customer_addresses.js') . '"></script>';
        }

        if (is_staff_logged_in()) {
            echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/text_to_task.js') . '"></script>';
        }
    }

    /**
     * Render the quick access menu into the main menu bar.
     * @return void
     */
    public function before_render_aside_menu_poly_utilities()
    {
        //if(!isset($this->poly_utilities_settings)) return;
        if (isset($this->poly_utilities_settings->is_quick_access_menu) && $this->poly_utilities_settings->is_quick_access_menu !== 'true') return;
        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));
        $obj_old_data = [];
    ?>
        <div id="poly_utilities_quick_access_menu" class="poly-absolute poly-hide">
            <div class="poly_utilities_quick_access_menu">
                <span class="menu-items" data-toggle="dropdown"><i class="fas fa-bars"></i></span>
                <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base">
                    <li class="dropdown-header tw-mb-1"><?php echo _l('poly_utilities_quick_access_menu') ?></li>
                    <?php
                    if (!empty($obj_storage)) {
                        $obj_old_data = json_decode($obj_storage);
                        foreach ($obj_old_data as $key => $item) {
                            $icon = $item->icon ? $item->icon : 'fas fa-link';
                    ?>
                            <li>
                                <a href="<?php echo $item->link ?>" target="<?php echo (!empty($item->target) ? $item->target : '_self') ?>" rel="<?php echo (!empty($item->rel) ? $item->rel : 'nofollow') ?>" class="tw-group tw-inline-flex tw-space-x-0.5 tw-text-neutral-700">
                                    <i class="<?php echo $icon ?>"></i>&nbsp;<span><?php echo $item->title . ($item->shortcut_key ? "&nbsp;<span class='poly-quick-access-shortcut-key pull-right' data-toggle='tooltip' data-title='Shortcut key'>{$item->shortcut_key}</span>" : '') ?></span>
                                </a>
                            </li>
                        <?php
                        }
                    }
                    if (
                        has_permission('poly_utilities_shortcut_menu_extend', '', 'create') &&
                        (!isset($this->poly_utilities_settings->is_display_add_quick_access_menu) || $this->poly_utilities_settings->is_display_add_quick_access_menu === 'true')
                    ) {
                        ?>
                        <li>
                            <hr class="hr" />
                            <a href="<?php echo admin_url('poly_utilities/quick_access') ?>"><i class="fas fa-plus"></i>&nbsp;<?php echo _l('poly_utilities_quick_access_menu_mini_add') ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
<?php
    }

    public function flattenMenuItems($items, &$flatArray = [])
    {
        foreach ($items as $item) {
            if (isset($item['href'])) {
                $flatArray[] = $item;
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $this->flattenMenuItems($item['children'], $flatArray);
            }
        }
        return $flatArray;
    }

    public function hrefExistsInMenu($href, $menu)
    {
        $flatMenu = $this->flattenMenuItems($menu);
        foreach ($flatMenu as $menuItem) {
            if ($menuItem['href'] === $href && $menuItem['is_custom'] === 'true') {
                return $menuItem;
            }
        }
        return false;
    }

    public function hook_custom_module_permisson()
    {
        // Load custom menu slugs from database for permission checking
        $CI = &get_instance();
        
        // Check if table exists before loading model (prevent errors during deactivation)
        if (!$CI->db->table_exists(db_prefix() . 'poly_utilities_custom_menus')) {
            return;
        }
        
        $CI->load->model('poly_utilities/custom_menu_model');
        
        $custom_menu_items = $CI->custom_menu_model->get_menus('setup', true, false);
        $custom_menu_sidebar_items = $CI->custom_menu_model->get_menus('sidebar', true, false);
        
        // Extract slugs for permission checking
        $setup_slugs = array_column($custom_menu_items, 'slug');
        $sidebar_slugs = array_column($custom_menu_sidebar_items, 'slug');
        
        $merged_menu_items = array_merge($setup_slugs ?? [], $sidebar_slugs ?? []);

        if ($merged_menu_items) {

            $currentUrl = poly_utilities_common_helper::get_current_url();

            $item = $this->hrefExistsInMenu($currentUrl, $merged_menu_items);

            if ($item !== false) {
                //Roles
                $user_can_access = false;
                $role_can_access = false;

                if (!empty($item['roles'])) {
                    $role_by_staffid = poly_utilities_user_helper::get_user_role($this->current_user_id);
                    if ($role_by_staffid !== null) {
                        $roleid_by_user = $role_by_staffid->role;
                        $roles_access = poly_utilities_common_helper::json_decode($item['roles'], true);
                        $role_can_access = poly_utilities_common_helper::get_item_by($roles_access, 'id', $roleid_by_user);
                    }
                } else {
                    $role_can_access = true;
                }

                //Users
                if (!empty($item['users'])) {
                    $users = poly_utilities_common_helper::json_decode($item['users'], true);
                    $user_can_access = poly_utilities_common_helper::get_item_by($users, 'id', $this->current_user_id);
                } else {
                    $user_can_access = true;
                }

                //Remove menu items from the list if the account or group does not have access permission.
                if (!$role_can_access && !$user_can_access && ($this->current_user_id != 1 && $this->current_user_id != 2)) { // 2 for demo. Need to add Settings. && !is_admin(). Need to denie !admin 1 when access menu slug
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('access_denied'));
                }
            }
        }
    }

    ////////////////////////////////////////////////////////////////////// REORDER COLUMNS //////////////////////////////////////////////////////////////////////
    private function hooks_reorder_columns()
    {
        // List accept hooks -> rest to clients check validation drag drop tables.
        $dataTables = poly_utilities_common_helper::$table_hooks;
        foreach ($dataTables as $dataTable) {
            $keyTable = $dataTable['key_table'];
            $keyReorder = $dataTable['key_reorder'];
            $this->add_table_hooks($keyTable, $keyReorder);
        }
    }

    private function add_table_hooks($keyTable, $keyReorder)
    {
        hooks()->add_filter("{$keyTable}_table_columns", function ($tableData) use ($keyReorder) {
            $indexReorder = $this->get_reorder_columns($keyReorder);
            if (!empty($indexReorder)) {
                return $this->reorder_columns($tableData, $indexReorder);
            }
            return $tableData;
        }, 8);

        hooks()->add_filter("{$keyTable}_table_sql_columns", function ($aColumns) use ($keyReorder) {
            $indexReorder = $this->get_reorder_columns($keyReorder);
            if (!empty($indexReorder)) {
                return $this->reorder_columns($aColumns, $indexReorder);
            }
            return $aColumns;
        }, 8);

        hooks()->add_filter("{$keyTable}_table_row_data", function ($row, $aRow) use ($keyReorder) {
            $indexReorder = $this->get_reorder_columns($keyReorder);
            if (!empty($indexReorder)) {
                return $this->reorder_columns($row, $indexReorder);
            }
            return $row;
        }, 8, 2);
    }

    private function get_reorder_columns($key)
    {
        foreach ($this->poly_utilities_settings->data_reorder as $item) {
            if ($item['key'] === $key && $item['value']) {
                return array_map('intval', $item['value']);
            }
        }
        return null;
    }

    /**
     * @note array nre order setting
     */
    private function reorder_columns($data_array, $index_array)
    {
        $reordered_array = [];
        foreach ($index_array as $index) {

            if (key_exists($index,  $data_array)) {
                $reordered_array[] = $data_array[$index];
            }
        }
        foreach ($data_array as $index => $value) {

            if (!in_array($index,  $index_array)) {
                $reordered_array[] = $value;
            }
        }
        return $reordered_array;
    }
    ////////////////////////////////////////////////////////////////////// REORDER COLUMNS //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// CUSTOMIZE //////////////////////////////////////////////////////////////////////
    public function poly_utilities_custom_search_handle($hook_data)
    {
        if ($hook_data['path'] == 'admin/search') {
            $hook_data['path'] = '../../modules/poly_utilities/views/admin/search';
        }
        return $hook_data;
    }
    ////////////////////////////////////////////////////////////////////// END CUSTOMIZE //////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////// ADMIN MENU & PERMISSION //////////////////////////////////////////////////////////////////////
    public function add_poly_utilities_settings_link($action_links)
    {
        $actions[] = '<a href="' . admin_url(POLY_UTILITIES_MODULE_NAME.'/settings') . '">' . _l('settings') . '</a>';
        return $actions;
    }
    /**
     * Init goals module menu items in setup in app_init hook
     * @return void
     */
    public function hook_custom_module_init_menu_items()
    {
        // Role: Setting/ Users can access PolyUtilities
        if (!staff_can_poly_utilities()) {
            //access_denied();
            return;
        }

        // =========== Quick Access Menu =========== //
        hooks()->add_action('admin_navbar_start', [$this, 'before_render_aside_menu_poly_utilities'], 10);

        if (is_admin() && $this->current_user_id != 1 && !poly_utilities_is_user_access_module($this->current_user_id)) {
            return '';
        }

        // =========== Menu setup ===========//
        if (staff_can_poly_utilities_custom_menu()) {
            $this->CI->app_menu->add_setup_menu_item('poly_utilities_settings', [
                'name'     => _l('poly_utilities_name'),
                'href'     => admin_url('poly_utilities/custom_menu'),
                'position' => 9999,
            ]);
        }

        // =========== Sidebar Menu ===========//
        if (has_permission('poly_utilities', '', 'view')) {
            $this->CI->app_menu->add_sidebar_menu_item('poly_utilities', [
                'name'     => _l('poly_utilities_name'),
                'collapse' => true,
                'icon'     => 'fas fa-user-clock',
                'position' => 3,
            ]);
        }

        // Define child menu items with permissions
        $menuItems = [
            [
                'slug'       => 'poly_utilities_shortcut_menu_extend',
                'name'       => _l('poly_utilities_shortcut_menu_extend'),
                'icon'       => 'fa-solid fa-list-check',
                'href'       => admin_url('poly_utilities/quick_access'),
                'position'   => 1,
                'permission' => 'poly_utilities_shortcut_menu_extend',
            ],
            [
                'slug'       => 'poly_utilities_custom_menu_extend',
                'name'       => _l('poly_utilities_custom_menu_extend'),
                'icon'       => 'fa-solid fa-list-ul fa-fw',
                'href'       => admin_url('poly_utilities/custom_menu'),
                'position'   => 2,
                'permission' => 'poly_utilities_custom_menu_extend',
            ],
            [
                'slug'       => 'poly_utilities_context_menu',
                'name'       => _l('poly_utilities_context_menu'),
                'icon'       => 'fa-regular fa-rectangle-list fa-fw',
                'href'       => admin_url('poly_utilities/context_menu'),
                'position'   => 3,
                'permission' => 'poly_utilities_context_menu',
            ],
            [
                'slug'       => 'poly_utilities_fixed_bottom_menu',
                'name'       => _l('poly_utilities_fixed_bottom_menu'),
                'icon'       => 'fa-solid fa-bars-staggered fa-fw',
                'href'       => admin_url('poly_utilities/fixed_bottom_menu'),
                'position'   => 4,
                'permission' => 'poly_utilities_fixed_bottom_menu',
            ],
            [
                'slug'       => 'poly_utilities_widgets_extend',
                'name'       => _l('poly_utilities_widgets_extend'),
                'icon'       => 'fa-solid fa-palette fa-fw',
                'href'       => admin_url('poly_utilities/widgets'),
                'position'   => 5,
                'permission' => 'poly_utilities_widgets_extend',
            ],
            [
                'slug'       => 'poly_utilities_scripts_extend',
                'name'       => _l('poly_utilities_scripts_extend'),
                'icon'       => 'fas fa-file-code',
                'href'       => admin_url('poly_utilities/scripts'),
                    'position'   => 6,
                    'permission' => 'poly_utilities_scripts_extend',
                ],
                [
                    'slug'       => 'poly_utilities_styles_extend',
                    'name'       => _l('poly_utilities_styles_extend'),
                    'icon'       => 'fas fa-file-alt',
                    'href'       => admin_url('poly_utilities/styles'),
                    'position'   => 7,
                'permission' => 'poly_utilities_styles_extend',
            ],
            [
                'slug'       => 'poly_utilities_supports',
                'name'       => _l('poly_utilities_support'),
                'icon'       => 'fa-solid fa-headset',
                'href'       => admin_url('poly_utilities/support'),
                    'position'   => 8,
                    'permission' => 'poly_utilities_supports',
                ],
                [
                    'slug'       => 'poly_utilities_banners',
                    'name'       => _l('poly_utilities_banners'),
                    'icon'       => 'fa-solid fa-photo-film fa-fw',
                    'href'       => admin_url('poly_utilities/banners'),
                    'position'   => 9,
                'permission' => 'poly_utilities_banners',
            ],
            [
                'slug'       => 'poly_utilities_projects',
                'name'       => _l('poly_utilities_projects'),
                'icon'       => 'fa-solid fa-diagram-project fa-fw',
                'href'       => admin_url('poly_utilities/projects'),
                    'position'   => 10,
                    'permission' => 'poly_utilities_projects',
                ],
                [
                    'slug'       => 'poly_utilities_task_templates',
                    'name'       => _l('poly_utilities_task_templates'),
                    'icon'       => 'fa-solid fa-tasks fa-fw',
                    'href'       => admin_url('poly_utilities/task_templates'),
                    'position'   => 10.5,
                    'permission' => 'poly_utilities_task_templates',
                ],
                [
                    'slug'       => 'poly_utilities_appearance',
                    'name'       => _l('poly_utilities_appearance'),
                    'icon'       => 'fa-solid fa-building-lock fa-fw',
                    'href'       => admin_url('poly_utilities/appearance'),
                    'position'   => 11,
                'permission' => 'poly_utilities_appearance',
            ],
            [
                'slug'       => 'poly_utilities_settings',
                'name'       => _l('poly_utilities_settings'),
                'icon'       => 'fa fa-cog',
                'href'       => admin_url('poly_utilities/settings'),
                'position'   => 11,
                'permission' => 'poly_utilities_settings',
            ],
        ];

        foreach ($menuItems as $item) {
            // Skip admin-only items for non-admin users
            if (isset($item['admin_only']) && $item['admin_only'] === true && !is_admin()) {
                continue;
            }
            
            if (has_permission($item['permission'], '', 'view') || has_permission($item['permission'], '', 'edit') || has_permission($item['permission'], '', 'create') || has_permission($item['permission'], '', 'delete')) {
                $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                    'slug'     => $item['slug'],
                    'name'     => $item['name'],
                    'icon'     => $item['icon'],
                    'href'     => $item['href'],
                    'position' => $item['position'],
                ]);
            }
        }

        $this->permissions();
    }

    /**
     * Initialize module permissions during setup in the admin_init hook.
     * @return void
     */
    public function permissions()
    {
        // Common capabilities used across multiple features
        $commonCapabilities = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];

        // Define permissions for various features
        $permissions = [
            // =========== PolyUtilities ===========
            'poly_utilities' => [
                'capabilities' => ['view' => _l('permission_view')],
                'label'        => _l('poly_utilities') // Main utilities feature
            ],
            // =========== JavaScripts ===========
            'poly_utilities_scripts_extend' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_scripts_extend') . ' (' . _l('poly_utilities') . ')' // JavaScript extensions
            ],
            // =========== Custom Menu ===========
            'poly_utilities_custom_menu_extend' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_custom_menu_extend') . ' (' . _l('poly_utilities') . ')' // Custom menu feature
            ],

            // =========== Context Menu ===========
            'poly_utilities_context_menu' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_context_menu') . ' (' . _l('poly_utilities') . ')' // Custom context menu feature
            ],

            // =========== Widgets ===========
            'poly_utilities_widgets_extend' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_widgets_extend') . ' (' . _l('poly_utilities') . ')' // Widgets support
            ],
            // =========== Styles ===========
            'poly_utilities_styles_extend' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_styles_extend') . ' (' . _l('poly_utilities') . ')' // CSS Styles extension
            ],
            // =========== Quick Access Menu ===========
            'poly_utilities_shortcut_menu_extend' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_shortcut_menu_extend') . ' (' . _l('poly_utilities') . ')' // Quick access menu
            ],
            // =========== AIO Supports ===========
            'poly_utilities_supports' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_support') . ' (' . _l('poly_utilities') . ')' // All-in-one support
            ],
            // =========== Banners ===========
            'poly_utilities_banners' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_banners') . ' (' . _l('poly_utilities') . ')' // Banners management
            ],
            // =========== Projects ===========
            'poly_utilities_projects' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_projects') . ' (' . _l('poly_utilities') . ')' // Projects management
            ],
            // =========== Task Templates ===========
            'poly_utilities_task_templates' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_task_templates') . ' (' . _l('poly_utilities') . ')' // Task templates management
            ],
            // =========== Appearance ===========
            'poly_utilities_appearance' => [
                'capabilities' => $commonCapabilities,
                'label'        => _l('poly_utilities_appearance') . ' (' . _l('poly_utilities') . ')' // Appearance page
            ],
            // =========== Settings ===========
            'poly_utilities_settings' => [
                'capabilities' => [
                    'view' => _l('permission_view'),
                    'edit' => _l('permission_edit'),
                ],
                'label' => _l('poly_utilities_settings') . ' (' . _l('poly_utilities') . ')' // Settings configuration
            ],
        ];

        // Register permissions dynamically
        foreach ($permissions as $key => $data) {
            register_staff_capabilities($key, ['capabilities' => $data['capabilities']], $data['label']);
        }
    }
    ////////////////////////////////////////////////////////////////////// ADMIN MENU & PERMISSION //////////////////////////////////////////////////////////////////////

    public function register_customer_addresses_tab($tabs)
    {
        if (!$this->feature_multiple_addresses) {
            return $tabs;
        }

        if (!staff_can('view', 'customers') && !staff_can('view_own', 'customers')) {
            return $tabs;
        }

        $slug = 'poly_customer_addresses';

        if (!isset($tabs[$slug])) {
            $tabs[$slug] = [
                'slug'     => $slug,
                'name'     => _l('poly_utilities_address_tab_heading'),
                'icon'     => 'fa-solid fa-location-dot',
                'view'     => POLY_UTILITIES_MODULE_NAME . '/customers/address',
                'position' => 10,
                'visible'  => true,
                'badge'    => [],
            ];
        }

        return $tabs;
    }

    private function get_feature_flag($property, $default = true)
    {
        if (!isset($this->poly_utilities_settings) || !is_object($this->poly_utilities_settings)) {
            return $default;
        }

        if (!property_exists($this->poly_utilities_settings, $property)) {
            return $default;
        }

        $value = $this->poly_utilities_settings->{$property};

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

    private function is_customer_addresses_context()
    {
        return $this->CI->uri->segment(2) === 'clients'
            && $this->CI->uri->segment(3) === 'client';
    }

    /**
     * Remove task_template_id from project data before insert
     * This prevents the field from being inserted into projects table
     */
    public function remove_task_template_id_from_project_data($data)
    {
        // Remove task_template_id from data to prevent database error
        // The value will be retrieved from POST in create_tasks_from_template
        if (isset($data['task_template_id'])) {
            unset($data['task_template_id']);
        }
        return $data;
    }

    /**
     * Create tasks from template when project is created
     */
    public function create_tasks_from_template($project_id)
    {
        // Get task template ID from POST data (if available)
        $template_id = $this->CI->input->post('task_template_id');
        
        if (empty($template_id)) {
            return;
        }

        // Load task templates model
        $this->CI->load->model('poly_utilities/task_templates_model');
        
        // Create tasks from template
        $result = $this->CI->task_templates_model->create_tasks_from_template($template_id, $project_id);
        
        if ($result['success'] && $result['created_count'] > 0) {
            log_activity('Tasks created from template [Template ID: ' . $template_id . ', Project ID: ' . $project_id . ', Count: ' . $result['created_count'] . ']');
        }
    }

    private function is_sales_document_address_context()
    {
        $segment2 = $this->CI->uri->segment(2);
        $segment3 = $this->CI->uri->segment(3);

        $map = [
            'invoices'  => ['invoice'],
            'estimates' => ['estimate'],
            'proposals' => ['proposal'],
        ];

        return isset($map[$segment2]) && in_array($segment3, $map[$segment2], true);
    }
}
new POLYUTILITIES();
