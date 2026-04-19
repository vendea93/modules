<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: AI Project Analyzer
Description: Generate AI analysis & insights for projects.
Version: 1.0.0
Requires at least: 2.3.*
Author: ManissDev
Author URI: https://codecanyon.net/user/manissdev
*/

require(__DIR__ . '/vendor/autoload.php');

/**
 * Module constants
 */
define('AI_PROJECT_ANALYZER_MODULE_NAME', 'ai_project_analyzer');
define('AI_PROJECT_ANALYZER_TABLE', db_prefix() . '_ai_analysis');
define('AI_PROJECT_ANALYZER_QUEUE_TABLE', db_prefix() . '_ai_analysis_queue');
define('AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE', db_prefix() . '_ai_prompt_templates');

/**
 * Initialize module
 */
function ai_project_analyzer_init()
{
    $CI = &get_instance();
    $CI->load->helper(AI_PROJECT_ANALYZER_MODULE_NAME . '/ai_project_analyzer');

    // Register hooks
    ai_project_analyzer_register_hooks();

    // Register language files
    register_language_files(AI_PROJECT_ANALYZER_MODULE_NAME, [AI_PROJECT_ANALYZER_MODULE_NAME]);

    // Register activation hook
    register_activation_hook(AI_PROJECT_ANALYZER_MODULE_NAME, 'ai_project_analyzer_module_activation_hook');

    // Register deactivation hook
    register_deactivation_hook(AI_PROJECT_ANALYZER_MODULE_NAME, 'ai_project_analyzer_module_deactivation_hook');
}

/**
 * Register all module hooks
 */
function ai_project_analyzer_register_hooks()
{
    $hooks = [
        'actions' => [
            'after_cron_run' => 'ai_project_analyzer_send',
            'admin_init' => ['ai_project_analyzer_init_menu_items', 'ai_project_analyzer_permissions'],
            'app_admin_head' => 'ai_project_analyzer_analysis_load_style',
            'app_admin_footer' => 'ai_project_analyzer_analysis_load_javascript',
            'settings_group_end' => 'ai_project_analyzer_settings_group_end',
            'before_update_system_options' => 'ai_project_analyzer_update_system_options'
        ],
        'filters' => [
            'module_ai_project_analyzer_action_links' => 'ai_project_analyzer_action_links',
            'numbers_of_features_using_cron_job' => 'ai_project_analyzer_numbers_of_features_using_cron_job',
            'used_cron_features' => 'ai_project_analyzer_used_cron_features'
        ]
    ];

    foreach ($hooks['actions'] as $hook => $callbacks) {
        if (is_array($callbacks)) {
            foreach ($callbacks as $callback) {
                hooks()->add_action($hook, $callback);
            }
        } else {
            hooks()->add_action($hook, $callbacks);
        }
    }

    foreach ($hooks['filters'] as $hook => $callback) {
        hooks()->add_filter($hook, $callback);
    }
}

/**
 * Module activation hook
 */
function ai_project_analyzer_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Module deactivation hook
 */
function ai_project_analyzer_module_deactivation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

/**
 * Add module action links
 */
function ai_project_analyzer_action_links($actions)
{
    $actions[] = sprintf(
        '<a href="%s">%s</a>',
        admin_url('settings?group=ai_project_analyzer'),
        _l('settings')
    );

    return $actions;
}

/**
 * Initialize menu items with caching
 */
function ai_project_analyzer_init_menu_items()
{
    $CI = &get_instance();

    if (!staff_can('view', 'ai_project_analyzer')) {
        return;
    }

    $can_view_analytics = staff_can('view_analytics', 'ai_project_analyzer');
    $can_view_templates = staff_can('view_templates', 'ai_project_analyzer');
    $can_view_settings = staff_can('view', 'settings');

    // Main menu item
    $CI->app_menu->add_sidebar_menu_item('ai_project_analyzer', [
        'name' => _l('ai_project_analyzer'),
        'collapse' => true,
        'icon' => 'fa-solid fa-wand-magic-sparkles',
        'view' => 'ai_project_analyzer/index',
        'position' => 31,
    ]);

    // Sub-menu items
    $menu_items = [
        [
            'condition' => $can_view_templates,
            'slug' => 'ai-project-analyzer-templates',
            'name' => _l('ai_project_analyzer_templates'),
            'href' => admin_url('ai_project_analyzer/templates'),
            'position' => 1
        ],
        [
            'condition' => $can_view_analytics,
            'slug' => 'ai-project-analyzer-analytics',
            'name' => _l('ai_project_analyzer_analytics'),
            'href' => admin_url('ai_project_analyzer/analytics'),
            'position' => 2
        ],
        [
            'condition' => $can_view_settings,
            'slug' => 'ai-project-analyzer-settings',
            'name' => _l('module_settings'),
            'href' => admin_url('settings?group=ai_project_analyzer'),
            'position' => 3
        ]
    ];

    foreach ($menu_items as $item) {
        if ($item['condition']) {
            $CI->app_menu->add_sidebar_children_item('ai_project_analyzer', [
                'slug' => $item['slug'],
                'name' => $item['name'],
                'href' => $item['href'],
                'position' => $item['position']
            ]);
        }
    }

    // Project tab
    $CI->app_tabs->add_project_tab('project_analysis', [
        'name' => _l('ai_analyzer'),
        'icon' => 'fa-solid fa-wand-magic-sparkles',
        'view' => 'ai_project_analyzer/project_analysis',
        'position' => 31,
        'visible' => staff_can('view', 'ai_project_analyzer'),
    ]);

    // Settings section
    $CI->app->add_settings_section_child('ai', 'ai_project_analyzer', [
        'name' => _l('ai_project_analyzer'),
        'icon' => 'fa-solid fa-wand-magic-sparkles',
        'view' => 'ai_project_analyzer/settings',
        'position' => 20,
    ]);
}

/**
 * Register module permissions
 */
function ai_project_analyzer_permissions()
{
    $capabilities = [
        'view' => _l('ai_project_analyzer_view_analysis'),
        'create' => _l('ai_project_analyzer_create_analysis'),
        'edit' => _l('ai_project_analyzer_edit_analysis'),
        'delete' => _l('ai_project_analyzer_delete_analysis'),
        'download' => _l('ai_project_analyzer_download_analysis'),
        'send_to_email' => _l('ai_project_analyzer_send_to_email_analysis'),
        'view_analytics' => _l('ai_project_analyzer_view_analytics'),
        'view_templates' => _l('ai_project_analyzer_view_templates'),
        'create_templates' => _l('ai_project_analyzer_create_templates'),
        'edit_templates' => _l('ai_project_analyzer_edit_templates'),
        'delete_templates' => _l('ai_project_analyzer_delete_templates'),
    ];

    register_staff_capabilities(
        'ai_project_analyzer',
        ['capabilities' => $capabilities],
        _l('ai_project_analyzer')
    );
}



/**
 * Get analyses with optimized pagination
 */
function get_analyses_with_pagination($project_id)
{
    static $pagination_config = null;

    $CI = &get_instance();

    if (!isset($_GET['page']) || $_GET['page'] === '' || $_GET['page'] === null || !ctype_digit((string) $_GET['page'])) {
        $_GET['page'] = '1';
    }

    // Initialize pagination config once
    if ($pagination_config === null) {
        $CI->load->library('pagination');

        $pagination_config = [
            'full_tag_open' => '<ul class="ai-flex ai-items-center ai-gap-2 ai-mt-6">',
            'full_tag_close' => '</ul>',
            'cur_tag_open' => '<li><span class="btn btn-primary ai-px-3 ai-py-1.5">',
            'cur_tag_close' => '</span></li>',
            'num_tag_open' => '<li>',
            'num_tag_close' => '</li>',
            'num_links' => 3,
            'next_tag_open' => '<li>',
            'next_tag_close' => '</li>',
            'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>',
            'first_tag_open' => '<li>',
            'first_tag_close' => '</li>',
            'last_tag_open' => '<li>',
            'last_tag_close' => '</li>',
            'attributes' => ['class' => 'btn btn-default ai-px-3 ai-py-1.5'],
            'use_page_numbers' => true,
            'page_query_string' => true,
            'query_string_segment' => 'page',
            'reuse_query_string' => true
        ];
    }

    $config = array_merge($pagination_config, [
        'base_url' => admin_url('projects/view/' . $project_id . '?group=project_analysis'),
        'total_rows' => $CI->db->where('project_id', $project_id)->count_all_results(AI_PROJECT_ANALYZER_TABLE),
        'per_page' => (int) get_option('ai_project_analyzer_pagination_max', 10)
    ]);

    $CI->pagination->initialize($config);

    $CI->load->library('form_validation');
    $page = $CI->form_validation->is_natural($CI->input->get('page'))
        ? max(1, (int) $CI->input->get('page'))
        : 1;
    $offset = ($page - 1) * $config['per_page'];

    // Build query
    $CI->db->select('*')
        ->from(AI_PROJECT_ANALYZER_TABLE)
        ->where('project_id', $project_id)
        ->order_by('created_at', 'DESC')
        ->limit($config['per_page'], $offset);

    $ai_analyses = $CI->db->get()->result();

    return [
        'ai_analyses' => $ai_analyses,
        'pagination_links' => $CI->pagination->create_links(),
    ];
}

/**
 * Optimized asset loading functions
 */
function ai_project_analyzer_settings_group_end()
{
    $CI = &get_instance();

    if ($CI->input->get('group') === 'ai_project_analyzer') {
        $CI->load->view('ai_project_analyzer/load_javascript');
    }
}

function ai_project_analyzer_analysis_load_style()
{
    $CI = &get_instance();

    if ($CI->input->get('group') === 'project_analysis') {
        $css_path = module_dir_url(AI_PROJECT_ANALYZER_MODULE_NAME, 'assets/css/app.min.css');
        echo sprintf('<link href="%s" rel="stylesheet">', $css_path);
    }
}

function ai_project_analyzer_analysis_load_javascript()
{
    $CI = &get_instance();

    if ($CI->input->get('group') === 'project_analysis') {
        $CI->load->view('ai_project_analyzer/analysis/load_javascript');
    }
}

/**
 * Cron feature tracking functions
 */
function ai_project_analyzer_numbers_of_features_using_cron_job($number)
{
    if (get_option('ai_project_analyzer_use_cron')) {
        return $number + total_rows(AI_PROJECT_ANALYZER_QUEUE_TABLE, ['iscronfinished' => 0]);
    }
}

function ai_project_analyzer_used_cron_features($features)
{
    if (get_option('ai_project_analyzer_use_cron')) {
        if (total_rows(AI_PROJECT_ANALYZER_QUEUE_TABLE, ['iscronfinished' => 0]) > 0) {
            $features[] = 'AI Project Analyzer';
        }
    }
    return $features;
}

/**
 * Execute cron job
 */
function ai_project_analyzer_send()
{
    $CI = &get_instance();
    $CI->load->library(AI_PROJECT_ANALYZER_MODULE_NAME . '/' . 'ai_project_analyzer_module');
    $CI->ai_project_analyzer_module->queue();
}

/**
 * Optimized settings update with validation
 */
function ai_project_analyzer_update_system_options($data)
{
    $CI = &get_instance();

    if ($CI->input->get('group') !== 'ai_project_analyzer') {
        return $data;
    }

    $settings = [
        'ai_project_analyzer_api_provider',
        'ai_project_analyzer_api_provider_model',
        'ai_project_analyzer_api_key',
        'ai_project_analyzer_use_cron',
        'ai_project_analyzer_in_progress_analyses',
        'ai_project_analyzer_pagination_max',
        'ai_project_analyzer_data_limit',
        'ai_project_analyzer_tone_list',
        'ai_project_analyzer_custom_instructions'
    ];

    foreach ($settings as $setting) {
        $value = $CI->input->post("settings[{$setting}]");

        // Handle NULL values - convert to empty string
        if ($value === null) {
            $value = '';
        }

        // Validate specific settings
        if ($setting === 'ai_project_analyzer_pagination_max') {
            $value = max(1, min(100, (int) $value));
        }

        if ($setting === 'ai_project_analyzer_data_limit') {
            $value = max(1, (int) $value);
        }

        update_option($setting, $value);
    }

    set_alert('success', _l('settings_updated'));
    redirect(admin_url('settings?group=ai_project_analyzer'));

    return $data;
}

// Initialize the module
ai_project_analyzer_init();