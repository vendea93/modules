<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: FQ SAAS
Description: FlowQuest SaaS engine for Perfex CRM — multi-tenant provisioning, billing (Stripe), and instance management.
Version: 0.4.2
Requires at least: 3.1.*
Author: FlowQuest
Author URI: https://flowquest.app
*/
defined('FQ_SAAS_VERSION_NUMBER') or define('FQ_SAAS_VERSION_NUMBER', '0.4.2');

require_once __DIR__ . '/helpers/fq_saas_php8_polyfill_helper.php';

// Global common module constants
require_once('config/constants.php');
require_once(__DIR__ . '/libraries/Fq_saas_kernel.php');
Fq_saas_kernel::boot();

$CI = &get_instance();

/**
 * Load models
 */
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_model');
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_stripe_model');
$CI->load->model(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_cron_model');

/**
 * Load the module helper
 */
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME);
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_core');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_setup');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_usage_limit');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_api');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_billing');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_coupon');
$CI->load->helper(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME . '_affiliate');
if (!fq_saas_is_tenant()) {
    fq_saas_billing_register_hooks();
    fq_saas_coupon_register_filters();
    fq_saas_affiliate_register_hooks();
}


/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FQ_SAAS_MODULE_NAME, [FQ_SAAS_MODULE_NAME]);

hooks()->do_action('fq_saas_loaded');
hooks()->do_action('perfex_saas_loaded'); // @deprecated compatibility alias; remove after downstream migrates

/**
 * DEMO autologin (dla landingów i list demo) — bez dodatkowych klików.
 * Działa wyłącznie na publicznych tenantach demo (clientid=3) i tylko gdy jest:
 * `?demo_account=owner|employee|client&autologin=1`.
 *
 * Dane kont pobierane są z tenant->metadata->login_panel->accounts (wypełniane w demo factory).
 */
hooks()->add_action('admin_auth_init', 'fq_saas_demo_autologin_admin', PHP_INT_MIN);
hooks()->add_action('clients_authentication_constructor', 'fq_saas_demo_autologin_client', PHP_INT_MIN);

function fq_saas_demo_autologin_enabled(): bool
{
    if (!isset($_GET['autologin']) || (string)$_GET['autologin'] !== '1') {
        return false;
    }
    if (empty($_GET['demo_account'])) {
        return false;
    }
    return true;
}

function fq_saas_demo_autologin_account_key(): string
{
    $key = strtolower((string)($_GET['demo_account'] ?? ''));
    if (!in_array($key, ['owner', 'employee', 'client'], true)) {
        return '';
    }
    return $key;
}

function fq_saas_demo_autologin_accounts($tenant): array
{
    if (!$tenant || empty($tenant->metadata) || !is_object($tenant->metadata)) {
        return [];
    }
    $panel = $tenant->metadata->login_panel ?? null;
    if (!$panel || !is_object($panel)) {
        return [];
    }
    $accounts = $panel->accounts ?? null;
    if (!$accounts || !is_array((array)$accounts)) {
        return [];
    }
    return (array)$accounts;
}

function fq_saas_demo_autologin_allowed($tenant, array $accounts): bool
{
    if (!$tenant || (int)($tenant->clientid ?? 0) !== 3) {
        return false;
    }
    return !empty($accounts);
}

function fq_saas_demo_autologin_admin(): void
{
    if (!fq_saas_is_tenant() || !fq_saas_demo_autologin_enabled()) {
        return;
    }
    if (function_exists('is_staff_logged_in') && is_staff_logged_in()) {
        return;
    }

    $key = fq_saas_demo_autologin_account_key();
    if ($key === '') {
        return;
    }

    $tenant = fq_saas_tenant();
    $accounts = fq_saas_demo_autologin_accounts($tenant);
    if (!fq_saas_demo_autologin_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$key] ?? null;
    if (!$account || !is_array((array)$account)) {
        return;
    }
    $account = (array)$account;
    if (($account['target'] ?? '') !== 'admin') {
        return;
    }

    $email = trim((string)($account['email'] ?? ''));
    $password = (string)($account['password'] ?? '');
    if ($email === '' || $password === '') {
        return;
    }

    $CI = &get_instance();
    if (!$CI) {
        return;
    }
    $CI->load->model('Authentication_model');

    $data = $CI->Authentication_model->login($email, $password, false, true);
    if ($data === false || (is_array($data) && (isset($data['memberinactive']) || isset($data['two_factor_auth'])))) {
        return;
    }

    hooks()->do_action('after_staff_login');
    redirect(admin_url());
}

function fq_saas_demo_autologin_client($controller): void
{
    if (!fq_saas_is_tenant() || !fq_saas_demo_autologin_enabled()) {
        return;
    }
    if (function_exists('is_client_logged_in') && is_client_logged_in()) {
        return;
    }

    $key = fq_saas_demo_autologin_account_key();
    if ($key === '') {
        return;
    }

    $tenant = fq_saas_tenant();
    $accounts = fq_saas_demo_autologin_accounts($tenant);
    if (!fq_saas_demo_autologin_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$key] ?? null;
    if (!$account || !is_array((array)$account)) {
        return;
    }
    $account = (array)$account;
    if (($account['target'] ?? '') !== 'client') {
        return;
    }

    $email = trim((string)($account['email'] ?? ''));
    $password = (string)($account['password'] ?? '');
    if ($email === '' || $password === '') {
        return;
    }

    if (!$controller || !isset($controller->load)) {
        return;
    }

    $controller->load->model('Authentication_model');
    $success = $controller->Authentication_model->login($email, $password, false, false);
    if ($success === false || (is_array($success) && isset($success['memberinactive']))) {
        return;
    }

    hooks()->do_action('after_contact_login');
    redirect(site_url());
}

/**
 * Cron management
 */
if (fq_saas_is_tenant()) {
    hooks()->add_action('before_cron_run', 'fq_saas_cron', PHP_INT_MIN); // Want to run this first for tenant
} else {
    hooks()->add_action('after_cron_run', 'fq_saas_cron');

    hooks()->add_action('before_cron_run', 'fq_saas_cron_before', PHP_INT_MIN);

    hooks()->add_action('after_cron_run', 'fq_saas_reset_demo_instances');
}

hooks()->add_filter('cron_functions_execute_seconds', function ($seconds) {
    // Disable cron lock for tenant. This is neccessary as there is already parent lock by the top saas cron.
    if (fq_saas_is_tenant() && !defined('APP_DISABLE_CRON_LOCK')) define('APP_DISABLE_CRON_LOCK', true);
    return $seconds;
});
hooks()->add_filter('used_cron_features', function ($f) {
    $f[] = _l('fq_saas_cron_feature_migration');
    return $f;
});

/**
 * Listen to any module activation and run the setup again.
 * This ensure new tables are prepared for saas.
 */
hooks()->add_action('module_activated', 'fq_saas_trigger_module_install');
hooks()->add_action('module_deactivated', 'fq_saas_trigger_module_install');

/**
 * Register activation module hook
 */
register_activation_hook(FQ_SAAS_MODULE_NAME, 'fq_saas_module_activation_hook');

function fq_saas_module_activation_hook()
{
    fq_saas_install();
}

/**
 * Self-heal: re-inject routes/hooks into application/config if a file upgrade wiped the markers.
 *
 * This is the #1 source of post-upgrade 404s — when the user uploads a new ZIP without going through
 * the Perfex Setup → Modules Deactivate/Activate cycle, the `require_once` line that pulls in our
 * module's config/my_routes.php is missing from application/config/my_routes.php. Without that line,
 * admin_url('fq_saas/...') hits the default CI router and returns 404. We detect the missing marker
 * and restore it transparently; nothing is re-run if markers already exist.
 */
hooks()->add_action('admin_init', 'fq_saas_routes_self_heal', 1);
function fq_saas_routes_self_heal()
{
    if (fq_saas_is_tenant()) {
        return;
    }

    $required = [
        APPPATH . 'config/my_routes.php'  => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/my_routes.php'",
        APPPATH . 'config/app-config.php' => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/app-config.php'",
        APPPATH . 'config/my_hooks.php'   => "FCPATH.'modules/" . FQ_SAAS_MODULE_NAME . "/config/my_hooks.php'",
    ];

    $healed = false;
    foreach ($required as $dest => $requirePath) {
        if (!is_file($dest) || !is_writable($dest)) {
            continue;
        }
        $haystack = @file_get_contents($dest);
        if ($haystack === false) {
            continue;
        }
        if (strpos($haystack, "modules/" . FQ_SAAS_MODULE_NAME . "/config/" . basename($dest)) === false) {
            fq_saas_require_in_file($dest, $requirePath, false, false, true);
            $healed = true;
        }
    }

    if ($healed) {
        log_message('info', 'fq_saas: self-healed missing require_once in application/config; reloading request.');
        $url = current_url();
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        header('Location: ' . $url . $sep . 'fq_saas_selfheal=1');
        exit;
    }
}

/**
 * Dactivation module hook
 */
register_deactivation_hook(FQ_SAAS_MODULE_NAME, 'fq_saas_module_deactivation_hook');
function fq_saas_module_deactivation_hook()
{
    fq_saas_uninstall();
}

/**
 * Register admin footer hook - Common to both admin and instance
 * @todo Separate instance js customization from super admin
 */
hooks()->add_action('app_admin_footer', 'fq_saas_admin_footer_hook');
function fq_saas_admin_footer_hook()
{
    //load common admin asset
    $CI = &get_instance();
    $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/scripts');


    //load add user to package modal
    if (!fq_saas_is_tenant() && $CI->router->fetch_class() == 'invoices')
        $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/add_user_to_package_modal');
}


/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
hooks()->add_action('admin_init', 'fq_saas_module_init_menu_items', 50);
function fq_saas_module_init_menu_items()
{
    $CI = &get_instance();

    // Ensure module strings are available before building menu (matches register_staff_capabilities feature ids).
    $locale = $GLOBALS['language'] ?? get_option('active_language') ?: 'english';
    $CI->lang->load(FQ_SAAS_MODULE_NAME . '/' . FQ_SAAS_MODULE_NAME, $locale);

    if (
        !fq_saas_is_tenant() && (
            staff_can('view', 'fq_saas_companies') ||
            staff_can('view', 'fq_saas_packages') ||
            staff_can('view', 'fq_saas_settings') ||
            staff_can('view', 'fq_saas_landing') ||
            staff_can('view', 'fq_saas_cms') ||
            staff_can('view', 'fq_saas_coupons') ||
            staff_can('view', 'fq_saas_affiliates') ||
            staff_can('view', 'fq_saas_domains') ||
            staff_can('view', 'fq_saas_api_user') ||
            staff_can('view', 'fq_saas_dashboard')
        )
    ) {

        $badge = [];

        $CI->app_menu->add_sidebar_menu_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
            'name' => _l('fq_saas_menu_title'),
            'icon' => 'fa fa-users tw-font-bold',
            'position' => 2,
            'badge' => $badge
        ]);

        if (staff_can('view', 'fq_saas_api_user')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_api',
                'name' => _l('fq_saas_api'),
                'icon' => 'fa fa-link',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/api'),
                'position' => 1,
            ]);
        }

        if (staff_can('view', 'fq_saas_packages')) {
            $is_single_package_mode = fq_saas_is_single_package_mode();
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_packages',
                'name' => $is_single_package_mode ? _l('fq_saas_pricing') : _l('fq_saas_packages'),
                'icon' => 'fa fa-list',
                'href' => $is_single_package_mode ? admin_url(FQ_SAAS_ROUTE_NAME . '/pricing') : admin_url(FQ_SAAS_ROUTE_NAME . '/packages'),
                'position' => 5,
            ]);
        }

        if (staff_can('view', 'fq_saas_landing')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_landing',
                'name' => _l('fq_saas_landing_builder'),
                'icon' => 'fa fa-paint-brush',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/landing_builder'),
                'position' => 6,
            ]);
        }

        if (staff_can('view', 'fq_saas_cms')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_cms',
                'name' => _l('fq_saas_cms'),
                'icon' => 'fa fa-file-lines',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/cms'),
                'position' => 7,
            ]);
        }

        if (staff_can('view', 'fq_saas_coupons')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_coupons',
                'name' => _l('fq_saas_coupons'),
                'icon' => 'fa fa-ticket',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/coupons'),
                'position' => 8,
            ]);
        }

        if (staff_can('view', 'fq_saas_affiliates')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_affiliates',
                'name' => _l('fq_saas_affiliates'),
                'icon' => 'fa fa-handshake',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/affiliates'),
                'position' => 9,
            ]);
        }

        if (staff_can('view', 'fq_saas_domains')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_domains',
                'name' => _l('fq_saas_domains'),
                'icon' => 'fa fa-globe',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/domains'),
                'position' => 10,
            ]);
        }

        if (staff_can('view', 'fq_saas_companies')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_companies',
                'name' => _l('fq_saas_tenants'),
                'icon' => 'fa fa-university',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/companies'),
                'position' => 11,
            ]);
        }

        if (staff_can('view', 'fq_saas_companies') && staff_can('view', 'invoices')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_invoices',
                'name' => _l('fq_saas_invoices'),
                'icon' => 'fa-solid fa-receipt',
                'href' => admin_url('invoices') . '?' . FQ_SAAS_FILTER_TAG,
                'position' => 15,
            ]);
        }

        if (staff_can('view', 'fq_saas_settings')) {
            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_settings',
                'name' => _l('fq_saas_settings'),
                'icon' => 'fa fa-cog',
                'href' => admin_url('settings?group=' . FQ_SAAS_MODULE_WHITELABEL_NAME),
                'position' => 20,
            ]);

            $CI->app_menu->add_sidebar_children_item(FQ_SAAS_MODULE_WHITELABEL_NAME, [
                'slug' => FQ_SAAS_MODULE_WHITELABEL_NAME . '_update_ext',
                'name' => _l('fq_saas_update_ext_menu'),
                'icon' => 'fa fa-plug',
                'href' => admin_url(FQ_SAAS_ROUTE_NAME . '/system'),
                'position' => 30,
                'badge' => $badge
            ]);

            // SaaS tab on settings page
            $settings_tab = [
                'name'     => _l('settings_group_' . FQ_SAAS_MODULE_NAME),
                'view'     => 'fq_saas/settings/index',
                'position' => -5,
                'icon'     => 'fa fa-users',
            ];

            if (method_exists($CI->app, 'add_settings_section_child'))
                $CI->app->add_settings_section_child('general', FQ_SAAS_MODULE_WHITELABEL_NAME, $settings_tab);
            else
                $CI->app_tabs->add_settings_tab(FQ_SAAS_MODULE_WHITELABEL_NAME, $settings_tab);
        }
    }

    if (fq_saas_is_tenant()) {
        // Reserved routes
        $restricted_menus = ['modules'];
        foreach ($restricted_menus as $menu) {
            $CI->app_menu->add_setup_menu_item($menu, ['name' => '', 'href' => '', 'disabled' => true, 'collapse' => true, 'children' => []]);
        }
    }
}

/**
 * Common hook to filter dangerous file extension when updating settings
 */
hooks()->add_filter('before_settings_updated', 'fq_saas_before_settings_updated_common_hook');
function fq_saas_before_settings_updated_common_hook($data)
{
    $filter_permitted_extensions = function ($extensions_string) {
        $_exts = explode(',', $extensions_string);
        if (count($_exts) > 100) throw new \Exception("Ext size too large: Error Processing Request", 1);

        $allowed_files = [];
        foreach ($_exts as $ext) {
            $ext = trim($ext);
            if (str_starts_with($ext, '.') && !in_array($ext, FQ_SAAS_DANGEROUS_EXTENSIONS)) {
                $allowed_files[] = $ext;
            }
        }
        return implode(',', $allowed_files);
    };

    if (isset($data['settings']['allowed_files'])) {
        $data['settings']['allowed_files'] = $filter_permitted_extensions($data['settings']['allowed_files']);
    }

    if (isset($data['settings']['ticket_attachments_file_extensions'])) {
        $data['settings']['ticket_attachments_file_extensions'] = $filter_permitted_extensions($data['settings']['ticket_attachments_file_extensions']);
    }

    return $data;
}



/********SAAS CLIENTS AND SUPER ADMIN HOOKS ******/
$is_tenant = fq_saas_is_tenant();
$is_admin = is_admin();
$is_client = is_client_logged_in();

// Perfex allows admin and client login on the same browser. When a dual-session user lands on
// an admin-area controller, treat them as admin (some controllers extend AdminController via
// custom base classes / traits, so rely on instanceof rather than class-name subclass lookup).
if ($is_admin && $is_client && (
    $CI instanceof AdminController
    || is_subclass_of($CI, 'AdminController')
)) {
    $is_client = false;
}

if (!$is_tenant) {

    // Add contact Permissions os super admin can create contact of a company with some saas feature control
    hooks()->add_filter('get_contact_permissions', function ($permissions) {
        return array_merge($permissions, fq_saas_contact_permissions());
    });

    // We do not want to laod module for the exluded super clients and does without any saas permission
    if ($is_client && !fq_saas_client_can_use_saas())
        return;
}

if (!$is_tenant) {

    // Log a selected plan id whenever we have it. I.e the copied package url
    $plan_identifier = fq_saas_route_id_prefix('plan');
    if (!empty($package_slug = $CI->input->post_get($plan_identifier, true))) {
        $CI->session->set_userdata([$plan_identifier => $package_slug]);
    }

    $slug_identifier = fq_saas_route_id_prefix('slug');
    $custom_domain_identifier = fq_saas_route_id_prefix('custom_domain');
    // Log package, subdomain and custom domain from registration form
    if (!$is_client) {
        $company_slug = $CI->input->post('slug', true);
        if (!empty($company_slug)) {
            $CI->session->set_userdata([$slug_identifier => $company_slug]);
        }

        $custom_domain = $CI->input->post('custom_domain', true);
        if (!empty($custom_domain)) {
            $CI->session->set_userdata([$custom_domain_identifier => $custom_domain]);
        }
    }

    // Add custom domain and subdomain from session if any
    hooks()->add_filter('fq_saas_create_instance_data', function ($data) use ($CI, $custom_domain_identifier, $slug_identifier) {
        $company_slug = $CI->session->{$slug_identifier};
        if (!empty($company_slug) && !isset($data['slug'])) {
            $data['slug'] = $company_slug;
        }

        $custom_domain = $CI->session->{$custom_domain_identifier};
        if (!empty($custom_domain) && !isset($data['custom_domain']) && fq_saas_is_valid_custom_domain($custom_domain)) {
            $data['custom_domain'] = $custom_domain;
        }

        return $data;
    });

    // Clear the session if present after success creating an instance
    hooks()->add_action('fq_saas_after_client_create_instance', function ($id) use ($CI, $plan_identifier, $custom_domain_identifier, $slug_identifier) {
        if ($id) {
            foreach ([$custom_domain_identifier, $slug_identifier, $plan_identifier] as $key) {
                if ($CI->session->has_userdata($key))
                    $CI->session->unset_userdata($key);
            }
        }
    });

    /******* SUPER CLIENT SPECIFIC HOOKS *********/
    if ($is_client) {
        // Auto subscribe to package when logged in as client
        if (fq_saas_contact_can_manage_subscription())
            fq_saas_autosubscribe();
    }

    // Use naked hooks out of $is_client to ensure availability in simulations of the client hooks from within admin panel i.e client menus
    hooks()->add_action('clients_init', 'fq_saas_clients_area_menu_items');
    function fq_saas_clients_area_menu_items()
    {
        if (is_client_logged_in()) {
            if (fq_saas_contact_can_manage_instances())
                add_theme_menu_item('companies', [
                    'name' => _l('fq_saas_client_menu_companies'),
                    'href' => site_url('clients/?companies'),
                    'position' => -2,
                    'href_attributes' => [
                        'class' => 'ps-spa',
                        'data-tab' => "#companies"
                    ]
                ]);

            if (fq_saas_contact_can_manage_subscription()) {
                add_theme_menu_item('subscription', [
                    'name' => _l('fq_saas_client_menu_subscription'),
                    'href' => fq_saas_is_single_package_mode() ? site_url('clients/my_account') : site_url('clients/?subscription'),
                    'position' => -1,
                    'href_attributes' => [
                        'class' => 'ps-spa',
                        'data-tab' => "#subscription"
                    ]
                ]);

                add_theme_menu_item('marketplace', [
                    'name' => _l('fq_saas_marketplace_client_menu'),
                    'href' => site_url('clients/my_account?view-modal=module'),
                    'position' => -1,
                ]);
            }

            // Link to the client profile (Perfex contact profile page). Added in 0.3.7 changelog.
            if (function_exists('add_theme_menu_item')) {
                add_theme_menu_item('saas_profile', [
                    'name' => _l('fq_saas_client_menu_profile'),
                    'href' => site_url('clients/profile'),
                    'position' => 0,
                ]);
            }
        }
    }
    // Add home view for client
    hooks()->add_action('client_area_after_project_overview', 'fq_saas_show_client_home');
    function fq_saas_show_client_home()
    {
        include_once(__DIR__ . '/views/client/home.php');
    }

    // Client panel scripts and widgets
    hooks()->add_action('app_customers_head', function () {
        include_once(__DIR__ . '/views/client/scripts.php');
    });




    /**
     * Register permissions for every admin-context request (priority 5, before menu builder at 50).
     *
     * Must be registered regardless of whether the current staff is a super admin — otherwise
     * non-admin staff visiting the role permission screen will not see fq_saas capabilities,
     * and staff_can() checks return false for never-registered capabilities.
     */
    hooks()->add_action('admin_init', 'fq_saas_permissions', 5);
    if (!function_exists('fq_saas_permissions')) {
        function fq_saas_permissions()
        {
            $view_only = ['capabilities' => ['view' => _l('fq_saas_permission_view')]];
            register_staff_capabilities('fq_saas_dashboard', $view_only, _l('fq_saas') . ' ' . _l('fq_saas_dashboard'));

            $crud = [
                'capabilities' => [
                    'view'   => _l('fq_saas_permission_view'),
                    'create' => _l('fq_saas_permission_create'),
                    'edit'   => _l('fq_saas_permission_edit'),
                    'delete' => _l('fq_saas_permission_delete'),
                ],
            ];
            register_staff_capabilities('fq_saas_companies',  $crud, _l('fq_saas') . ' ' . _l('fq_saas_companies'));
            register_staff_capabilities('fq_saas_packages',   $crud, _l('fq_saas') . ' ' . _l('fq_saas_packages'));
            register_staff_capabilities('fq_saas_api_user',   $crud, _l('fq_saas') . ' ' . _l('fq_saas_api_user'));
            register_staff_capabilities('fq_saas_landing',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_landing_builder'));
            register_staff_capabilities('fq_saas_cms',        $crud, _l('fq_saas') . ' ' . _l('fq_saas_cms'));
            register_staff_capabilities('fq_saas_coupons',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_coupons'));
            register_staff_capabilities('fq_saas_affiliates', $crud, _l('fq_saas') . ' ' . _l('fq_saas_affiliates'));
            register_staff_capabilities('fq_saas_domains',    $crud, _l('fq_saas') . ' ' . _l('fq_saas_domains'));

            $view_edit = [
                'capabilities' => [
                    'view' => _l('fq_saas_permission_view'),
                    'edit' => _l('fq_saas_permission_edit'),
                ],
            ];
            register_staff_capabilities('fq_saas_settings', $view_edit, _l('fq_saas') . ' ' . _l('fq_saas_settings'));
        }
    }

    /******* SUPER ADMIN PANEL SPECIFIC HOOKS *********/
    if ($is_admin || is_staff_member()) {

        //dashboard
        if (staff_can('view', 'fq_saas_dashboard')) {
            hooks()->add_filter('get_dashboard_widgets', function ($widgets) {

                return array_merge([
                    ['path' => FQ_SAAS_MODULE_NAME . '/dashboard/overview_widget', 'container' => 'top-12'],
                    ['path' => FQ_SAAS_MODULE_NAME . '/dashboard/mrr_widget', 'container' => 'top-12'],
                ], $widgets);
            });

            hooks()->add_action('before_start_render_dashboard_content', 'fq_saas_dashboard_hook');
            function fq_saas_dashboard_hook()
            {
                get_instance()->load->view(FQ_SAAS_MODULE_NAME . '/dashboard/index', []);
            }
        }

        /** Invoice view hooks and filters */
        if (staff_can('view', 'fq_saas_packages')) {
            // Add packageid column to the datatable column and hide
            hooks()->add_filter('invoices_table_columns', 'fq_saas_invoices_table_columns');
            function fq_saas_invoices_table_columns($cols)
            {
                $cols[fq_saas_column('packageid')] = ['name' => fq_saas_column('packageid'), 'th_attrs' => ['class' => 'not_visible']];
                return $cols;
            }

            // Add packageid to selected invoice fields
            hooks()->add_filter('invoices_table_sql_columns', 'fq_saas_invoices_table_sql_columns');
            function fq_saas_invoices_table_sql_columns($fields)
            {
                $fields[] = fq_saas_column('packageid');
                return $fields;
            }

            // Add package name to recurring bill on invoices list
            hooks()->add_filter('invoices_table_row_data', 'fq_saas_invoices_table_row_data', 10, 2);
            function fq_saas_invoices_table_row_data($row, $data)
            {
                $label = _l('fq_saas_invoice_recurring_indicator');
                $col = fq_saas_column('packageid');
                if (!empty($data[$col])) {
                    $packageid = $data[$col];
                    $package_name = get_instance()->fq_saas_model->packages($packageid)->name;
                    $row[0] = str_ireplace($label, $label . ' | ' . $package_name, $row[0]);
                }
                $row[] = '';
                return $row;
            }


            // Add package selection to invoice edit/create
            hooks()->add_action('before_render_invoice_template', 'fq_saas_after_render_invoice_template_hook');
            function fq_saas_after_render_invoice_template_hook($invoice)
            {
                $col_name = fq_saas_column('packageid');
                if (empty($invoice->{$col_name})) return;
                $CI = &get_instance();
                $data = [
                    'packages' => $CI->fq_saas_model->packages(),
                    'invoice' => $invoice,
                    'col_name' => $col_name,
                    'invoice_packageid' => $invoice->{$col_name}
                ];

                $CI->load->view(FQ_SAAS_MODULE_NAME . '/includes/select_package_invoice_template', $data);
            }
        }

        /************Settings */
        // Ensure perfex saas setting is use as default when no settings group is defined
        hooks()->add_action('before_settings_group_view', 'fq_saas_before_settings_group_view_hook');
        function fq_saas_before_settings_group_view_hook($tab)
        {

            if (empty(get_instance()->input->get('group'))) { //root settings

                redirect(admin_url('settings?group=' . FQ_SAAS_MODULE_WHITELABEL_NAME));
            }
        }

        // Get modules whitelabeling settings
        hooks()->add_filter('before_settings_updated', 'fq_saas_before_settings_updated_hook');
        function fq_saas_before_settings_updated_hook($data)
        {
            $fq_saas_settings_array_fields = [
                'fq_saas_custom_modules_name',
                'fq_saas_tenants_seed_tables',
                'fq_saas_sensitive_options',
                'fq_saas_modules_marketplace',
                'fq_saas_restricted_clients_id',
                'fq_saas_custom_services',
                'fq_saas_require_invoice_payment_status',
                'fq_saas_demo_instance'
            ];
            foreach ($fq_saas_settings_array_fields as $key) {
                if (isset($data['settings'][$key])) {
                    $data['settings'][$key] = json_encode($data['settings'][$key]);
                }
            }

            $encrypted_fields = ['fq_saas_cpanel_password', 'fq_saas_plesk_password', 'fq_saas_mysql_root_password'];
            $CI = &get_instance();
            foreach ($encrypted_fields as $key => $field) {
                if (isset($data['settings'][$field]))
                    $data['settings'][$field] = $CI->encryption->encrypt($data['settings'][$field]);
            }

            return $data;
        }
    }
}


/********OTHER SPECIFIC HOOKS ******/
/**
 * Load every feature hook file exactly once. require_once guards against duplicate registration
 * when middleware_hooks.php already pulled the same file (e.g. tenant mode + admin mode in one
 * request), and the is_file() check keeps us safe if a filter injected a non-existent path.
 */
$folder_path = __DIR__ . '/hooks/';
$feature_hook_files = glob($folder_path . '*.php') ?: [];
$feature_hook_files = hooks()->apply_filters('fq_saas_extra_hook_files', $feature_hook_files);
foreach ($feature_hook_files as $file) {
    if (is_string($file) && is_file($file)) {
        require_once $file;
    }
}


// Manual run test or cron for development purpose only
if (!empty($CI->input->get(FQ_SAAS_MODULE_NAME . '_dev'))) {

    // Only permit this in development mode and user should be logged in as admin.
    $is_developer = ENVIRONMENT === 'development' && !fq_saas_is_tenant() && $is_admin;
    if (!$is_developer) {
        exit("This action can only be run in development mode");
    }

    $action = $CI->input->get('action');

    if ($action === 'test') {
        include_once(__DIR__ . '/test.php');
    }

    if ($action === 'cron') {
        fq_saas_cron();
    }
    exit();
}
