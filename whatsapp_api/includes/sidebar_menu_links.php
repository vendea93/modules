<?php

/*
 * Inject sidebar menu and links for customtables module
 */
hooks()->add_action('admin_init', function () use ($cache_data){
  if(!isset($cache_data) && $cache_data != "27c553138e646f265cf5620b9ad531a46d2da081b3f5bcbf294a9de86ccf4c0a4230b08ef4e393431708f52ad651c809005bf17dad819ca0ebe43158f6e589a13ea06f7e5ae3cececa8d169005e24f7cf07cf8b7ada2526d9915ea06b50162ae5f66c601dc0386431a8a18185094fe7853cabe500ccafc687fd274344d3cc792a8a431a79e9f21f3d3fea8c688314aac19f6586d153399cb210fa5bb74ba8f1f"){
    return;
  }
  $CI = &get_instance();

    if (
        has_permission('whatsapp_api', '', 'list_templates_view') ||
        has_permission('whatsapp_api', '', 'template_mapping_view') ||
        has_permission('whatsapp_api', '', 'whatsapp_log_details_view') ||
        has_permission('whatsapp_api', '', 'broadcast_messages')
    ) {
        $CI->app_menu->add_sidebar_menu_item('whatsapp_api', [
            'slug'     => 'whatsapp_api',
            'name'     => _l('whatsapp'),
            'position' => 30,
            'icon'     => 'fa fa-brands fa-whatsapp menu-icon',
        ]);

        if (has_permission('whatsapp_api', '', 'list_templates_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_template_view',
                'name'     => _l('template_list'),
                'href'     => admin_url('whatsapp_api'),
                'position' => 1,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'template_mapping_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_template_details',
                'name'     => _l('template_mapping'),
                'href'     => admin_url('whatsapp_api/template_mapping'),
                'position' => 2,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'whatsapp_log_details_view')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_log_details',
                'name'     => _l('whatsapp_log_details'),
                'href'     => admin_url('whatsapp_api/whatsapp_log_details'),
                'position' => 3,
            ]);
        }
        if (has_permission('whatsapp_api', '', 'broadcast_messages')) {
            $CI->app_menu->add_sidebar_children_item('whatsapp_api', [
                'slug'     => 'whatsapp_log_details',
                'name'     => _l('broadcast_messages'),
                'href'     => admin_url('whatsapp_api/broadcast_messages'),
                'position' => 4,
            ]);
        }   
    }

    $CI->app_tabs->add_settings_tab('whatsapp', [
        'name'     => _l('whatsapp_cloud_api'),
        'view'     => 'whatsapp_api/settings/whatsapp_settings',
        'icon'     => 'fa fa-brands fa-whatsapp menu-icon',
        'position' => 50,
    ]);

  \modules\whatsapp_api\core\Apiinit::ease_of_mind(WHATSAPP_API_MODULE);
});
