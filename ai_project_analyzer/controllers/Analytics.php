<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Analytics extends AdminController
{
    public $app_modules;

    public function __construct()
    {
        parent::__construct();

        // Initialize the app modules class
        $this->app_modules = new App_modules;

        if ($this->app_modules->is_inactive('ai_project_analyzer')) {
            access_denied();
        }
    }

    /**
     * Analytics page
     * 
     * @return object|string
     */
    public function index()
    {
        if (staff_cant('view_analytics', 'ai_project_analyzer')) {
            access_denied('ai_project_analyzer');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('ai_project_analyzer', 'analytics/table'));
        }

        $data['title'] = _l('ai_project_analyzer') . ' - ' . _l('ai_project_analyzer_analytics');
        return $this->load->view('ai_project_analyzer/analytics/index', $data);
    }
}