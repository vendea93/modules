<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: FlowQuest Menu
Description: Centralne uproszczenie nazw, ikon i wygladu menu bocznego.
Version: 1.1.0
Requires at least: 3.0.*
*/

define('FLOWQUEST_MENU_MODULE_NAME', 'flowquest_menu');

register_activation_hook(FLOWQUEST_MENU_MODULE_NAME, 'flowquest_menu_activation_hook');

function flowquest_menu_activation_hook()
{
}

/* Pozniej niz menu_setup (998/999) i poly (999), zeby skroty i ikony zostaly w finale */
hooks()->add_filter('sidebar_menu_items', 'flowquest_menu_customize_sidebar', 10000);
hooks()->add_action('app_admin_head', 'flowquest_menu_admin_head');
hooks()->add_action('module_activated', 'flowquest_sync_go_module_access');
hooks()->add_action('module_deactivated', 'flowquest_sync_go_module_access');

/**
 * Demo autologin (bez dodatkowych klików).
 * - działa tylko na tenantach demo (clientid=3) z ustawionym login_panel w metadata
 * - uruchamiane tylko gdy jest ?demo_account=owner|employee|client&autologin=1
 * - fallback: jeśli autologin się nie uda, zwykły formularz logowania zostaje
 */
hooks()->add_action('admin_auth_init', 'flowquest_demo_autologin_admin', PHP_INT_MIN);
hooks()->add_action('clients_authentication_constructor', 'flowquest_demo_autologin_client', PHP_INT_MIN);

/**
 * Linki publiczne instancji (SaaS) jako slug.domena — bez subdomeny dla głównej instancji „go”.
 */
hooks()->add_filter('fq_saas_tenant_can_use_subdomain', 'flowquest_fq_saas_force_subdomain_urls', 20, 4);

function flowquest_demo_autologin_is_enabled(): bool
{
    if (!isset($_GET['autologin']) || (string)$_GET['autologin'] !== '1') {
        return false;
    }
    if (empty($_GET['demo_account'])) {
        return false;
    }
    return true;
}

function flowquest_demo_autologin_get_account_key(): string
{
    $key = strtolower((string)($_GET['demo_account'] ?? ''));
    if (!in_array($key, ['owner', 'employee', 'client'], true)) {
        return '';
    }
    return $key;
}

function flowquest_demo_autologin_get_tenant()
{
    if (!function_exists('fq_saas_is_tenant') || !fq_saas_is_tenant()) {
        return null;
    }
    if (!function_exists('fq_saas_tenant')) {
        return null;
    }
    return fq_saas_tenant();
}

function flowquest_demo_autologin_get_accounts($tenant): array
{
    if (!$tenant || empty($tenant->metadata) || !is_object($tenant->metadata)) {
        return [];
    }
    if (empty($tenant->metadata->login_panel) || !is_object($tenant->metadata->login_panel)) {
        return [];
    }
    $accounts = $tenant->metadata->login_panel->accounts ?? null;
    if (!$accounts || !is_array((array)$accounts)) {
        return [];
    }
    return (array)$accounts;
}

function flowquest_demo_autologin_is_allowed($tenant, array $accounts): bool
{
    // Twarde ograniczenie do naszych publicznych demo instancji.
    if (!$tenant || (int)($tenant->clientid ?? 0) !== 3) {
        return false;
    }
    if (empty($accounts)) {
        return false;
    }
    return true;
}

function flowquest_demo_autologin_admin(): void
{
    if (!flowquest_demo_autologin_is_enabled()) {
        return;
    }
    if (function_exists('is_staff_logged_in') && is_staff_logged_in()) {
        return;
    }

    $accountKey = flowquest_demo_autologin_get_account_key();
    if ($accountKey === '') {
        return;
    }

    $tenant = flowquest_demo_autologin_get_tenant();
    $accounts = flowquest_demo_autologin_get_accounts($tenant);
    if (!flowquest_demo_autologin_is_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$accountKey] ?? null;
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
    if (!$CI || !isset($CI->Authentication_model)) {
        return;
    }

    // Zaloguj i przekieruj od razu do panelu (bez renderowania widoku logowania).
    $data = $CI->Authentication_model->login($email, $password, false, true);
    if ($data === false || (is_array($data) && (isset($data['memberinactive']) || isset($data['two_factor_auth'])))) {
        // Zostaw standardowy flow + alert, żeby nie było białej strony.
        if (function_exists('set_alert')) {
            set_alert('danger', _l('admin_auth_invalid_email_or_password'));
        }
        return;
    }

    if (function_exists('hooks')) {
        hooks()->do_action('after_staff_login');
    }

    redirect(admin_url());
}

function flowquest_demo_autologin_client($controller): void
{
    if (!flowquest_demo_autologin_is_enabled()) {
        return;
    }
    if (function_exists('is_client_logged_in') && is_client_logged_in()) {
        return;
    }

    $accountKey = flowquest_demo_autologin_get_account_key();
    if ($accountKey === '') {
        return;
    }

    $tenant = flowquest_demo_autologin_get_tenant();
    $accounts = flowquest_demo_autologin_get_accounts($tenant);
    if (!flowquest_demo_autologin_is_allowed($tenant, $accounts)) {
        return;
    }

    $account = $accounts[$accountKey] ?? null;
    if (!$account || !is_array((array)$account)) {
        return;
    }
    $account = (array)$account;

    // Jeżeli ktoś kliknie klienta na admin auth, to i tak to obsłuży flowquest_demo_autologin_admin().
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
        if (function_exists('set_alert')) {
            set_alert('danger', _l('client_invalid_username_or_password'));
        }
        return;
    }

    if (function_exists('hooks')) {
        hooks()->do_action('after_contact_login');
    }

    redirect(site_url());
}

function flowquest_fq_saas_force_subdomain_urls($can_use_subdomain, $tenant, $package, $method)
{
    if ($method !== 'auto') {
        return (bool) $can_use_subdomain;
    }
    $slug = is_object($tenant) ? ($tenant->slug ?? '') : ($tenant['slug'] ?? '');
    if ($slug === '' || $slug === 'go') {
        return (bool) $can_use_subdomain;
    }

    return true;
}

function flowquest_sync_go_module_access()
{
    if (!function_exists('perfex_saas_is_tenant') || perfex_saas_is_tenant()) {
        return;
    }

    if (!function_exists('perfex_saas_search_tenant_by_field')) {
        return;
    }

    $CI = &get_instance();
    if (!$CI || !isset($CI->app_modules)) {
        return;
    }

    $tenant = perfex_saas_search_tenant_by_field('slug', 'go');
    if (!$tenant) {
        return;
    }

    $approved_modules = [];
    foreach ($CI->app_modules->get() as $module) {
        $name = $module['system_name'] ?? '';
        if (empty($name) || in_array($name, ['perfex_saas', FLOWQUEST_MENU_MODULE_NAME], true)) {
            continue;
        }

        if ((int)($module['activated'] ?? 0) === 1) {
            $approved_modules[] = $name;
        }
    }

    sort($approved_modules);

    $metadata = [];
    if (!empty($tenant->metadata)) {
        $decoded = json_decode($tenant->metadata, true);
        if (is_array($decoded)) {
            $metadata = $decoded;
        }
    }

    $metadata['admin_approved_modules'] = array_values($approved_modules);
    $metadata['admin_disabled_modules'] = array_values((array)($metadata['admin_disabled_modules'] ?? []));
    $metadata['admin_disabled_default_modules'] = array_values((array)($metadata['admin_disabled_default_modules'] ?? []));

    $CI->db->where('slug', 'go');
    $CI->db->update(perfex_saas_table('companies'), [
        'metadata' => json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
}

function flowquest_menu_admin_head()
{
    echo '<style>
    .sidebar-menu li a i[class*="fa-"],
    .sidebar-menu li a .menu-icon {
        width: 18px;
        min-width: 18px;
        text-align: center;
        margin-right: 10px;
        color: #b7c0cf !important;
        opacity: 1 !important;
    }
    .sidebar-menu > li > a {
        display: flex;
        align-items: center;
        min-width: 0;
    }
    .sidebar-menu > li > a .menu-text {
        flex: 1 1 auto;
        min-width: 0;
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-menu .nav-second-level,
    .sidebar-menu ul.children {
        padding-left: 0 !important;
        margin-left: 0 !important;
    }
    .sidebar-menu .nav-second-level > li > a,
    .sidebar-menu ul.children > li > a {
        padding-left: 46px !important;
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 12.5px;
    }
    .sidebar-menu li.active > a i[class*="fa-"],
    .sidebar-menu li.active > a .menu-icon,
    .sidebar-menu li a:hover i[class*="fa-"],
    .sidebar-menu li a:hover .menu-icon {
        color: #ffffff !important;
    }
    </style>';
}

function flowquest_menu_customize_sidebar($items)
{
    $parent_map = [
        /* Rdzen Perfex CRM – krotkie PL + Font Awesome 6 */
        'dashboard'        => ['name' => 'Panel',     'icon' => 'fa-solid fa-gauge'],
        'customers'        => ['name' => 'Klienci',   'icon' => 'fa-regular fa-building'],
        'subscriptions'    => ['name' => 'Subsk.',    'icon' => 'fa-solid fa-arrows-rotate'],
        'expenses'         => ['name' => 'Koszty',    'icon' => 'fa-solid fa-coins'],
        'contracts'        => ['name' => 'Umowy',     'icon' => 'fa-solid fa-file-contract'],
        'projects'         => ['name' => 'Proj.',     'icon' => 'fa-solid fa-diagram-project'],
        'tasks'            => ['name' => 'Zadania',   'icon' => 'fa-regular fa-square-check'],
        'support'          => ['name' => 'Pomoc',     'icon' => 'fa-regular fa-life-ring'],
        'leads'            => ['name' => 'Leady',     'icon' => 'fa-solid fa-crosshairs'],
        'estimate_request' => ['name' => 'Zapyt.',    'icon' => 'fa-regular fa-file-lines'],
        'knowledge-base'   => ['name' => 'Baza',      'icon' => 'fa-regular fa-book'],
        'utilities'        => ['name' => 'Narzedzia', 'icon' => 'fa-solid fa-screwdriver-wrench'],
        'reports'          => ['name' => 'Raporty',   'icon' => 'fa-solid fa-chart-pie'],

        'landingpages-menu'              => ['name' => 'Landing Page', 'icon' => 'fa-solid fa-window-maximize'],
        'website_maintenance_management' => ['name' => 'Opieka WWW',   'icon' => 'fa-solid fa-globe'],
        'hotel-management'               => ['name' => 'Hotel',        'icon' => 'fa-solid fa-hotel'],
        'catering_management'            => ['name' => 'Katering',     'icon' => 'fa-solid fa-utensils'],
        'hosting_manager'                => ['name' => 'Hosting',      'icon' => 'fa-solid fa-server'],
        'appointly'                      => ['name' => 'Rezerwacje',   'icon' => 'fa-solid fa-calendar-check'],
        'assets'                         => ['name' => 'Zasoby',       'icon' => 'fa-solid fa-box-archive'],
        'flexform'                       => ['name' => 'Formularze',   'icon' => 'fa-solid fa-rectangle-list'],
        'flexform_staff'                 => ['name' => 'Formularze',   'icon' => 'fa-regular fa-file-lines'],
        'saas'                           => ['name' => 'SaaS',         'icon' => 'fa-solid fa-layer-group'],
        'workshop'                       => ['name' => 'Warsztaty',    'icon' => 'fa-solid fa-car-side'],
        'lg-logistic'                    => ['name' => 'Logistyka',    'icon' => 'fa-solid fa-truck-fast'],
        'reputation'                     => ['name' => 'Opinie',       'icon' => 'fa-solid fa-star-half-stroke'],
        'realestate'                     => ['name' => 'Nieruchom.',  'icon' => 'fa-solid fa-house'],
        'timesheets'                     => ['name' => 'Czas',        'icon' => 'fa-solid fa-clock'],
        'fixed_equipment'                => ['name' => 'Majatek',      'icon' => 'fa-solid fa-toolbox'],
        'hr_profile'                     => ['name' => 'Kadry',        'icon' => 'fa-solid fa-users'],
        'ai_project_analyzer'            => ['name' => 'AI Projekty',  'icon' => 'fa-solid fa-wand-magic-sparkles'],
        'mention'                        => ['name' => 'Wzmianki',     'icon' => 'fa-solid fa-at'],
        'prchat'                         => ['name' => 'Czat',         'icon' => 'fa-solid fa-comments'],
        'account_planning'               => ['name' => 'Planowanie',   'icon' => 'fa-solid fa-calendar-days'],
        'purchase'                       => ['name' => 'Zakupy',       'icon' => 'fa-solid fa-cart-shopping'],
        'accounting'                     => ['name' => 'Ksiegowosc',   'icon' => 'fa-solid fa-wallet'],
        'wa-workflow-automation'         => ['name' => 'Workflow',     'icon' => 'fa-solid fa-shuffle'],
        'sales'                          => ['name' => 'Sprzedaz',     'icon' => 'fa-solid fa-sack-dollar'],
    ];

    $child_map = [
        'landingpages-menu' => [
            'landingpages'         => ['name' => 'Strony',      'icon' => 'fa-regular fa-window-restore'],
            'landingpages-leads'   => ['name' => 'Leady',       'icon' => 'fa-solid fa-users'],
            'landingpages-blocks'  => ['name' => 'Bloki',       'icon' => 'fa-solid fa-cubes'],
            'landingpages-setting' => ['name' => 'Ustawienia',  'icon' => 'fa-solid fa-gear'],
        ],
        'website_maintenance_management' => [
            'wmm-dashboard'     => ['name' => 'Panel',      'icon' => 'fa-solid fa-chart-line'],
            'wmm-tasks'         => ['name' => 'Zadania',    'icon' => 'fa-solid fa-list-check'],
            'wmm-categories'    => ['name' => 'Kategorie',  'icon' => 'fa-solid fa-tags'],
            'wmm-websites'      => ['name' => 'Strony',     'icon' => 'fa-solid fa-globe'],
            'wmm-logs'          => ['name' => 'Dziennik',   'icon' => 'fa-solid fa-file-lines'],
            'wmm-calendar'      => ['name' => 'Kalendarz',  'icon' => 'fa-solid fa-calendar'],
            'wmm-packages'      => ['name' => 'Pakiety',    'icon' => 'fa-solid fa-box-open'],
            'wmm-package-usage' => ['name' => 'Uzycie',     'icon' => 'fa-solid fa-clock-rotate-left'],
            'wmm-reports'       => ['name' => 'Raporty',    'icon' => 'fa-solid fa-chart-pie'],
        ],
        'hotel-management' => [
            'hms-landlords'  => ['name' => 'Wlasciciele', 'icon' => 'fa-solid fa-user-tie'],
            'hms-properties' => ['name' => 'Obiekty',     'icon' => 'fa-solid fa-building'],
            'hms-rooms'      => ['name' => 'Pokoje',      'icon' => 'fa-solid fa-bed'],
            'hms-services'   => ['name' => 'Uslugi',      'icon' => 'fa-solid fa-bell-concierge'],
            'hms-bookings'   => ['name' => 'Rezerwacje',  'icon' => 'fa-solid fa-calendar-check'],
        ],
        'catering_management' => [
            'events'          => ['name' => 'Wydarzenia', 'icon' => 'fa-solid fa-calendar-days'],
            'event_types'     => ['name' => 'Typy',       'icon' => 'fa-solid fa-shapes'],
            'menus'           => ['name' => 'Menu',       'icon' => 'fa-solid fa-book-open'],
            'packages'        => ['name' => 'Pakiety',    'icon' => 'fa-solid fa-box-open'],
            'menu_items'      => ['name' => 'Pozycje',    'icon' => 'fa-solid fa-utensils'],
            'item_categories' => ['name' => 'Kategorie',  'icon' => 'fa-solid fa-tags'],
            'menu_sections'   => ['name' => 'Sekcje',     'icon' => 'fa-solid fa-table-cells-large'],
            'allergens'       => ['name' => 'Alergeny',   'icon' => 'fa-solid fa-triangle-exclamation'],
            'dietary_types'   => ['name' => 'Diety',      'icon' => 'fa-solid fa-leaf'],
        ],
        'appointly' => [
            'appointly-user-dashboard' => ['name' => 'Wizyty',       'icon' => 'fa-solid fa-calendar-check'],
            'appointly-user-history'   => ['name' => 'Historia',     'icon' => 'fa-solid fa-clock-rotate-left'],
            'appointly-callbacks'      => ['name' => 'Oddzwonienia', 'icon' => 'fa-solid fa-phone'],
            'appointly-user-settings'  => ['name' => 'Ustawienia',   'icon' => 'fa-solid fa-gear'],
            'appointly-link-menu-form' => ['name' => 'Formularz',    'icon' => 'fa-solid fa-wpforms'],
        ],
        'workshop' => [
            'wshop_dashboard'      => ['name' => 'Panel',      'icon' => 'fa-solid fa-gauge-high'],
            'wshop_repair_job'     => ['name' => 'Zlecenia',   'icon' => 'fa-solid fa-clipboard-list'],
            'wshop_device'         => ['name' => 'Urzadzenia', 'icon' => 'fa-solid fa-engine-warning'],
            'wshop_mechanic'       => ['name' => 'Mechanicy',  'icon' => 'fa-solid fa-user-gear'],
            'wshop_labour_product' => ['name' => 'Robocizna',  'icon' => 'fa-solid fa-gears'],
            'wshop_branch'         => ['name' => 'Oddzialy',   'icon' => 'fa-solid fa-building'],
            'wshop_inspection'     => ['name' => 'Inspekcje',  'icon' => 'fa-solid fa-clipboard-check'],
            'wshop_workshop'       => ['name' => 'Warsztaty',  'icon' => 'fa-solid fa-warehouse'],
            'wshop_report'         => ['name' => 'Raporty',    'icon' => 'fa-solid fa-chart-column'],
            'wshop_setting'        => ['name' => 'Ustawienia', 'icon' => 'fa-solid fa-gear'],
        ],
        'lg-logistic' => [
            'logistic-dashboard'      => ['name' => 'Kokpit',       'icon' => 'fa-solid fa-gauge-high'],
            'logistic-users'          => ['name' => 'Uzytkownicy',  'icon' => 'fa-solid fa-users'],
            'logistic-recipients'     => ['name' => 'Odbiorcy',     'icon' => 'fa-solid fa-address-book'],
            'logistic-pre_alert_list' => ['name' => 'Alerty',       'icon' => 'fa-solid fa-bell'],
            'logistic-packages'       => ['name' => 'Paczki',       'icon' => 'fa-solid fa-box'],
            'logistic-shipping'       => ['name' => 'Wysylki',      'icon' => 'fa-solid fa-truck'],
            'logistic-pickup'         => ['name' => 'Odbiory',      'icon' => 'fa-solid fa-hand-holding'],
            'logistic-consolidated'   => ['name' => 'Konsolidacja', 'icon' => 'fa-solid fa-boxes-packing'],
            'logistic-lg-reports'     => ['name' => 'Raporty',      'icon' => 'fa-solid fa-chart-column'],
            'logistic-settings'       => ['name' => 'Ustawienia',   'icon' => 'fa-solid fa-gear'],
        ],
        'reputation' => [
            'reputation_topic'           => ['name' => 'Tematy',       'icon' => 'fa-solid fa-list'],
            'reputation_project'         => ['name' => 'Projekty',     'icon' => 'fa-solid fa-diagram-project'],
            'reputation_mentions'        => ['name' => 'Wzmianki',     'icon' => 'fa-solid fa-at'],
            'reputation_summary'         => ['name' => 'Podsumowanie', 'icon' => 'fa-solid fa-chart-pie'],
            'reputation_vendor'          => ['name' => 'Dostawcy',     'icon' => 'fa-solid fa-truck-field'],
            'reputation_social_accounts' => ['name' => 'Konta',        'icon' => 'fa-solid fa-share-nodes'],
            'reputation_case'            => ['name' => 'Sprawy',       'icon' => 'fa-solid fa-briefcase'],
            'reputation_pdf_report'      => ['name' => 'PDF',          'icon' => 'fa-solid fa-file-pdf'],
            'reputation_setting'         => ['name' => 'Ustawienia',   'icon' => 'fa-solid fa-gear'],
        ],
        'realestate' => [
            'realestate_dashboard'          => ['name' => 'Panel',       'icon' => 'fa-solid fa-gauge-high'],
            'realestate_property_owners'    => ['name' => 'Wlasciciele', 'icon' => 'fa-solid fa-user-tie'],
            'realestate_my_staffs'          => ['name' => 'Zespol',      'icon' => 'fa-solid fa-users'],
            'realestate_real_estate_agents' => ['name' => 'Agenci',      'icon' => 'fa-solid fa-user-group'],
            'realestate_business_brokers'   => ['name' => 'Brokerzy',    'icon' => 'fa-solid fa-handshake-angle'],
            'realestate_properties'         => ['name' => 'Oferty',      'icon' => 'fa-solid fa-house'],
            'realestate_property_approvals' => ['name' => 'Akceptacje',  'icon' => 'fa-solid fa-check-to-slot'],
            'realestate_buy_requests'       => ['name' => 'Kupno',       'icon' => 'fa-solid fa-cart-shopping'],
            'realestate_rent_requests'      => ['name' => 'Najem',       'icon' => 'fa-solid fa-key'],
            'realestate_tenants'            => ['name' => 'Najemcy',     'icon' => 'fa-solid fa-address-book'],
            'realestate_reports'            => ['name' => 'Raporty',     'icon' => 'fa-solid fa-chart-column'],
            'realestate_setting'            => ['name' => 'Ustawienia',  'icon' => 'fa-solid fa-gear'],
            'realestate_permissions'        => ['name' => 'Uprawnienia', 'icon' => 'fa-solid fa-user-shield'],
        ],
        'timesheets' => [
            'timesheets_timekeeping'      => ['name' => 'Obecnosc',    'icon' => 'fa-solid fa-user-check'],
            'timesheets_timekeeping_mnrh' => ['name' => 'Urlopy',      'icon' => 'fa-solid fa-plane-departure'],
            'timesheets_route_management' => ['name' => 'Trasy',       'icon' => 'fa-solid fa-route'],
            'timesheets_table_shiftwork'  => ['name' => 'Grafik',      'icon' => 'fa-solid fa-table-cells'],
            'timesheets_shift_management' => ['name' => 'Zmiany',      'icon' => 'fa-solid fa-calendar-days'],
            'timesheets_shift_type'       => ['name' => 'Typy zmian',  'icon' => 'fa-solid fa-layer-group'],
            'timesheets_workplace_mgt'    => ['name' => 'Lokalizacje', 'icon' => 'fa-solid fa-location-dot'],
            'timesheets-report'           => ['name' => 'Raporty',     'icon' => 'fa-solid fa-chart-line'],
            'timesheets_setting'          => ['name' => 'Ustawienia',  'icon' => 'fa-solid fa-gear'],
        ],
        'fixed_equipment' => [
            'fixed_equipment_dashboard'           => ['name' => 'Panel',       'icon' => 'fa-solid fa-gauge-high'],
            'fixed_equipment_assets'              => ['name' => 'Sprzet',      'icon' => 'fa-solid fa-laptop'],
            'fixed_equipment_licenses'            => ['name' => 'Licencje',    'icon' => 'fa-solid fa-key'],
            'fixed_equipment_accessories'         => ['name' => 'Akcesoria',   'icon' => 'fa-solid fa-headphones'],
            'fixed_equipment_consumables'         => ['name' => 'Materialy',   'icon' => 'fa-solid fa-box-open'],
            'fixed_equipment_components'          => ['name' => 'Czesci',      'icon' => 'fa-solid fa-microchip'],
            'fixed_equipment_predefined_kits'     => ['name' => 'Zestawy',     'icon' => 'fa-solid fa-toolbox'],
            'fixed_equipment_checkout_mgt'        => ['name' => 'Wydania',     'icon' => 'fa-solid fa-right-left'],
            'fixed_equipment_requested'           => ['name' => 'Wnioski',     'icon' => 'fa-solid fa-paper-plane'],
            'fixed_equipment_assets_maintenances' => ['name' => 'Serwis',      'icon' => 'fa-solid fa-screwdriver-wrench'],
            'fixed_equipment_bulk_audit'          => ['name' => 'Audyt',       'icon' => 'fa-solid fa-clipboard-check'],
            'fixed_equipment_depreciations'       => ['name' => 'Amortyzacja', 'icon' => 'fa-solid fa-percent'],
            'fixed_equipment_locations'           => ['name' => 'Lokalizacje', 'icon' => 'fa-solid fa-location-dot'],
            'fixed_equipment_inventory'           => ['name' => 'Magazyn',     'icon' => 'fa-solid fa-warehouse'],
        ],
        'hr_profile' => [
            'hr_profile_dashboard'            => ['name' => 'Panel',      'icon' => 'fa-solid fa-gauge-high'],
            'hr_profile_job_position_manage'  => ['name' => 'Stanowiska', 'icon' => 'fa-solid fa-briefcase'],
            'hr_profile_organizational_chart' => ['name' => 'Struktura',  'icon' => 'fa-solid fa-sitemap'],
            'hr_profile_reception_of_staff'   => ['name' => 'Onboarding', 'icon' => 'fa-solid fa-user-plus'],
            'hr_profile_hr_records'           => ['name' => 'Pracownicy', 'icon' => 'fa-solid fa-id-card'],
            'hr_profile_training'             => ['name' => 'Szkolenia',  'icon' => 'fa-solid fa-graduation-cap'],
            'hr_profile_contract'             => ['name' => 'Umowy',      'icon' => 'fa-solid fa-file-signature'],
            'hr_profile_dependent_person'     => ['name' => 'Rodzina',    'icon' => 'fa-solid fa-people-roof'],
            'hr_profile_quitting_works'       => ['name' => 'Odejscia',   'icon' => 'fa-solid fa-person-walking-arrow-right'],
            'hr_profile_reports'              => ['name' => 'Raporty',    'icon' => 'fa-solid fa-chart-column'],
            'hr_profile_setting'              => ['name' => 'Ustawienia', 'icon' => 'fa-solid fa-gear'],
        ],
        'ai_project_analyzer' => [
            'ai-project-analyzer-templates' => ['name' => 'Szablony',  'icon' => 'fa-regular fa-copy'],
            'ai-project-analyzer-analytics' => ['name' => 'Analityka', 'icon' => 'fa-solid fa-chart-column'],
            'ai-project-analyzer-settings'  => ['name' => 'Ustawienia','icon' => 'fa-solid fa-gear'],
        ],
        'utilities' => [
            'media'                       => ['name' => 'Media',       'icon' => 'fa-solid fa-photo-film'],
            'bulk-pdf-exporter'           => ['name' => 'PDF',         'icon' => 'fa-solid fa-file-pdf'],
            'calendar'                    => ['name' => 'Kalendarz',   'icon' => 'fa-solid fa-calendar-days'],
            'announcements'               => ['name' => 'Ogloszenia',  'icon' => 'fa-solid fa-bullhorn'],
            'activity-log'                => ['name' => 'Aktywnosc',   'icon' => 'fa-solid fa-clock-rotate-left'],
            'ticket-pipe-log'             => ['name' => 'Pipe',        'icon' => 'fa-solid fa-inbox'],
            'utility_backup'              => ['name' => 'Kopie',       'icon' => 'fa-solid fa-floppy-disk'],
            'csv-export'                  => ['name' => 'CSV',         'icon' => 'fa-solid fa-file-csv'],
            'goals-tracking'              => ['name' => 'Cele',        'icon' => 'fa-solid fa-bullseye'],
            'einvoice_module_bulk_export' => ['name' => 'E-faktury',   'icon' => 'fa-solid fa-file-invoice'],
            'xml-export'                  => ['name' => 'XML',         'icon' => 'fa-solid fa-code'],
            'surveys'                     => ['name' => 'Ankiety',     'icon' => 'fa-regular fa-rectangle-list'],
        ],
        'reports' => [
            'timesheets-reports'           => ['name' => 'Czas',    'icon' => 'fa-solid fa-clock'],
            'sales-reports'                => ['name' => 'Sprzedaz', 'icon' => 'fa-solid fa-chart-line'],
            'expenses-reports'             => ['name' => 'Koszty',  'icon' => 'fa-solid fa-coins'],
            'expenses-vs-income-reports'   => ['name' => 'Bilans',  'icon' => 'fa-solid fa-scale-balanced'],
            'leads-reports'                => ['name' => 'Leady',   'icon' => 'fa-solid fa-crosshairs'],
            'knowledge-base-reports'      => ['name' => 'Baza',    'icon' => 'fa-regular fa-book'],
        ],
        'accounting' => [
            'accounting_dashboard'         => ['name' => 'Panel',        'icon' => 'fa-solid fa-chart-pie'],
            'accounting_transaction'       => ['name' => 'Transakcje',   'icon' => 'fa-solid fa-arrow-right-arrow-left'],
            'accounting_journal_entry'     => ['name' => 'Dziennik',     'icon' => 'fa-solid fa-book-open'],
            'accounting_transfer'          => ['name' => 'Transfery',    'icon' => 'fa-solid fa-right-left'],
            'accounting_chart_of_accounts' => ['name' => 'Konta',        'icon' => 'fa-solid fa-list-ol'],
            'accounting_reconcile'         => ['name' => 'Uzgadnianie',  'icon' => 'fa-solid fa-scale-balanced'],
            'accounting_report'            => ['name' => 'Raporty',      'icon' => 'fa-solid fa-chart-column'],
            'accounting_setting'           => ['name' => 'Ustawienia',   'icon' => 'fa-solid fa-gear'],
        ],
        'purchase' => [
            'purchase-items'     => ['name' => 'Przedmioty',  'icon' => 'fa-solid fa-boxes-stacked'],
            'vendors'            => ['name' => 'Dostawcy',    'icon' => 'fa-solid fa-truck-field'],
            'vendors-items'      => ['name' => 'Dostawy',     'icon' => 'fa-solid fa-truck-ramp-box'],
            'purchase-request'   => ['name' => 'Zadania',     'icon' => 'fa-solid fa-file-circle-plus'],
            'purchase-quotation' => ['name' => 'Oferty',      'icon' => 'fa-solid fa-file-signature'],
            'purchase-order'     => ['name' => 'Zamow.',        'icon' => 'fa-solid fa-cart-flatbed'],
            'purchase-contract'  => ['name' => 'Umowy',       'icon' => 'fa-solid fa-file-contract'],
            'purchase-invoices'  => ['name' => 'Faktury',     'icon' => 'fa-solid fa-file-invoice'],
            'purchase_reports'   => ['name' => 'Raporty',     'icon' => 'fa-solid fa-chart-column'],
            'purchase-settings'  => ['name' => 'Ustawienia',  'icon' => 'fa-solid fa-gear'],
        ],
        'wa-workflow-automation' => [
            'wa-workflow'         => ['name' => 'Przeplyw',    'icon' => 'fa-solid fa-shuffle'],
            'wa-workflow-history' => ['name' => 'Historia',    'icon' => 'fa-solid fa-clock-rotate-left'],
            'wa-settings'         => ['name' => 'Ustawienia',  'icon' => 'fa-solid fa-gear'],
        ],
        'sales' => [
            'proposals'    => ['name' => 'Oferty',         'icon' => 'fa-solid fa-file-signature'],
            'estimates'    => ['name' => 'Wyceny',         'icon' => 'fa-solid fa-calculator'],
            'invoices'     => ['name' => 'Faktury',        'icon' => 'fa-solid fa-file-invoice'],
            'payments'     => ['name' => 'Platnosci',      'icon' => 'fa-solid fa-credit-card'],
            'credit_notes' => ['name' => 'Noty kred.', 'icon' => 'fa-solid fa-receipt'],
            'items'        => ['name' => 'Pozycje',        'icon' => 'fa-solid fa-boxes-stacked'],
        ],
    ];

    foreach ($items as $parent_slug => &$item) {
        if (isset($parent_map[$parent_slug])) {
            $item['name'] = $parent_map[$parent_slug]['name'];
            $item['icon'] = $parent_map[$parent_slug]['icon'];
        } elseif (empty($item['icon'])) {
            $item['icon'] = 'fa-regular fa-folder';
        }

        if (empty($item['children']) || !is_array($item['children'])) {
            continue;
        }

        foreach ($item['children'] as &$child) {
            $child_slug = isset($child['slug']) ? $child['slug'] : '';
            $href = isset($child['href']) ? $child['href'] : '';

            if ($parent_slug === 'landingpages-menu' && $child_slug === 'landingpages-templates') {
                if (strpos($href, 'landingpages/templates') !== false) {
                    $child['name'] = 'Wzory';
                    $child['icon'] = 'fa-regular fa-copy';
                } elseif (strpos($href, 'templates/index') !== false) {
                    $child['name'] = 'Szablony';
                    $child['icon'] = 'fa-regular fa-file-lines';
                }
            }

            if (isset($child_map[$parent_slug][$child_slug])) {
                $child['name'] = $child_map[$parent_slug][$child_slug]['name'];
                $child['icon'] = $child_map[$parent_slug][$child_slug]['icon'];
            } elseif ($parent_slug === 'support' && strncmp($child_slug, 'support-', 8) === 0) {
                /* Statusy ticketow z Perfexa – zawsze ikona biletu */
                $child['icon'] = 'fa-solid fa-ticket';
            } elseif (empty($child['icon'])) {
                $child['icon'] = 'fa-solid fa-angle-right';
            }
        }
        unset($child);
    }
    unset($item);

    return $items;
}
