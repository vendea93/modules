<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Templates extends AdminController
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
     * Templates page
     * 
     * @return object|string
     */
    public function index()
    {
        if (staff_cant('view_templates', 'ai_project_analyzer')) {
            access_denied('ai_project_analyzer');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('ai_project_analyzer', 'templates/table'));
        }

        $data['title'] = _l('ai_project_analyzer') . ' - ' . _l('ai_project_analyzer_templates');
        return $this->load->view('ai_project_analyzer/templates/index', $data);
    }

    /**
     * Create/Edit template page
     * 
     * @return object|string
     */
    public function template($id = '')
    {
        $data = [];

        if ($id == '') {
            if (staff_cant('create_templates', 'ai_project_analyzer')) {
                access_denied('ai_project_analyzer');
            }

            $data['title'] = _l('ai_project_analyzer') . ' - ' . _l('ai_project_analyzer_templates_add');

            if ($this->input->post()) {
                if (staff_cant('create', 'ai_project_analyzer')) {
                    access_denied('ai_project_analyzer');
                }

                $this->db->insert(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE, [
                    'name' => $this->input->post('name'),
                    'staff_id' => get_staff_user_id(),
                    'prompt' => $this->input->post('prompt'),
                    'datecreated' => date('Y-m-d H:i:s')
                ]);

                set_alert('success', _l('added_successfully', _l('ai_project_analyzer_template')));
                redirect(admin_url('ai_project_analyzer/templates'));
            }
        } else {
            if (staff_cant('edit_templates', 'ai_project_analyzer')) {
                access_denied('ai_project_analyzer');
            }

            $template = $this->db->where('id', $id)
                ->get(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE)
                ->row();

            $data['title'] = $template->name;
            $data['template'] = $template;

            if ($this->input->post()) {

                $this->db->where('id', $id)
                    ->update(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE, [
                        'name' => $this->input->post('name'),
                        'staff_id' => get_staff_user_id(),
                        'prompt' => $this->input->post('prompt'),
                    ]);

                set_alert('success', _l('updated_successfully', _l('ai_project_analyzer_template')));
                redirect(admin_url('ai_project_analyzer/templates/template/' . $id));
            }
        }

        return $this->load->view('ai_project_analyzer/templates/template', $data);
    }

    /**
     * Delete template
     * 
     * @return void
     */
    public function delete($id)
    {
        if (staff_cant('delete_templates', 'ai_project_analyzer')) {
            access_denied('ai_project_analyzer');
        }

        if ($id) {
            $this->db->where('id', $id)
                ->delete(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE);

            set_alert('success', _l('deleted', _l('ai_project_analyzer_template')));
            redirect(admin_url('ai_project_analyzer/templates'));
        } else {
            access_denied('ai_project_analyzer');
        }
    }
}