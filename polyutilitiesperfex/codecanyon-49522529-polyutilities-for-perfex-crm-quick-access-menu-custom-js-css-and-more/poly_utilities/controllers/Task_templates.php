<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('poly_utilities/task_templates_model');
        $this->load->model('staff_model');
    }

    /**
     * Main management page
     */
    public function index()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'view')) {
            access_denied('Task Templates');
        }

        $data['title'] = _l('poly_utilities_task_templates');
        $data['categories'] = $this->task_templates_model->get_categories();
        
        // Load templates with items count
        $data['templates'] = $this->task_templates_model->get_templates([], false);
        
        $this->load->view('task_templates/manage', $data);
    }

    /**
     * Get templates (AJAX)
     */
    public function get_templates()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'view')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $category_id = $this->input->get('category_id');
        $active = $this->input->get('active');
        
        $where = [];
        if ($category_id !== null && $category_id !== '') {
            $where['t.category_id'] = $category_id;
        }
        if ($active !== null && $active !== '') {
            $where['t.active'] = $active;
        }

        $templates = $this->task_templates_model->get_templates($where, true);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'code' => 200,
                'data' => $templates,
                'total' => count($templates),
            ]));
    }

    /**
     * Add/Edit template
     */
    public function template($id = '')
    {
        if (!has_permission('poly_utilities_task_templates', '', empty($id) ? 'create' : 'edit')) {
            access_denied('Task Templates');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Process active field - if checkbox is not checked, it won't be in POST
            $data['active'] = isset($data['active']) && $data['active'] == '1' ? 1 : 0;
            
            // Process category_id - convert empty string or 0 to NULL for foreign key constraint
            if (isset($data['category_id'])) {
                $data['category_id'] = !empty($data['category_id']) && is_numeric($data['category_id']) && $data['category_id'] > 0 
                    ? (int)$data['category_id'] 
                    : null;
            } else {
                $data['category_id'] = null;
            }
            
            // Process items - ensure it's an array
            // CodeIgniter automatically parses items[0][name] format into array
            if (isset($data['items']) && is_array($data['items'])) {
                // Filter out empty items (items with empty name)
                $data['items'] = array_filter($data['items'], function($item) {
                    return isset($item['name']) && !empty(trim($item['name']));
                });
                // Re-index array after filtering
                $data['items'] = array_values($data['items']);
            } else {
                $data['items'] = [];
            }

            if (empty($id)) {
                // Add new
                $template_id = $this->task_templates_model->add_template($data);
                if ($template_id) {
                    set_alert('success', _l('added_successfully', _l('poly_utilities_task_template')));
                    redirect(admin_url('poly_utilities/task_templates/template/' . $template_id));
                } else {
                    set_alert('danger', _l('problem_adding', _l('poly_utilities_task_template')));
                }
            } else {
                // Update
                $success = $this->task_templates_model->update_template($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('poly_utilities_task_template')));
                    redirect(admin_url('poly_utilities/task_templates/template/' . $id));
                } else {
                    set_alert('danger', _l('problem_updating', _l('poly_utilities_task_template')));
                }
            }
        }

        $data['title'] = empty($id) ? _l('add_new', _l('poly_utilities_task_template')) : _l('edit', _l('poly_utilities_task_template'));
        $data['categories'] = $this->task_templates_model->get_categories(); // Load all categories, not filtered by active
        
        if (!empty($id)) {
            $data['template'] = $this->task_templates_model->get_template($id, true);
            if (!$data['template']) {
                set_alert('danger', _l('not_found', _l('poly_utilities_task_template')));
                redirect(admin_url('poly_utilities/task_templates'));
            }
        }

        $this->load->view('task_templates/template', $data);
    }

    /**
     * Delete template
     */
    public function delete_template($id)
    {
        if (!has_permission('poly_utilities_task_templates', '', 'delete')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error('ID is required');
        }

        $success = $this->task_templates_model->delete_template($id);
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('deleted', _l('poly_utilities_task_template')));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('problem_deleting', _l('poly_utilities_task_template')));
        }
    }

    /**
     * Update template status
     */
    public function update_template_status()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'edit')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $id = $this->input->post('id');
        $active = $this->input->post('active');

        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error('ID is required');
        }

        $success = $this->task_templates_model->update_template($id, [
            'active' => $active ? 1 : 0
        ]);

        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('updated_successfully', _l('poly_utilities_task_template')));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('problem_updating', _l('poly_utilities_task_template')));
        }
    }

    /**
     * Update template order
     */
    public function update_template_order()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'edit')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $orders = $this->input->post('order');
        if (empty($orders) || !is_array($orders)) {
            poly_utilities_ajax_response_helper::response_error('Invalid order data');
        }

        $success = $this->task_templates_model->update_template_order($orders);
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('updated_successfully'));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('problem_updating'));
        }
    }

    // ==================== CATEGORIES ====================

    /**
     * Get categories (AJAX)
     */
    public function get_categories()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'view')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $active = $this->input->get('active');
        $where = [];
        if ($active !== null && $active !== '') {
            $where['active'] = $active;
        }

        $categories = $this->task_templates_model->get_categories($where);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'code' => 200,
                'data' => $categories,
                'total' => count($categories),
            ]));
    }

    /**
     * Add/Edit category
     */
    public function category($id = '')
    {
        if (!has_permission('poly_utilities_task_templates', '', empty($id) ? 'create' : 'edit')) {
            access_denied('Task Templates');
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Process active field - if checkbox is not checked, it won't be in POST
            $data['active'] = isset($data['active']) && $data['active'] == '1' ? 1 : 0;
            
            // Process category_id - convert empty string or 0 to NULL for foreign key constraint
            if (isset($data['category_id'])) {
                $data['category_id'] = !empty($data['category_id']) && is_numeric($data['category_id']) && $data['category_id'] > 0 
                    ? (int)$data['category_id'] 
                    : null;
            } else {
                $data['category_id'] = null;
            }

            if (empty($id)) {
                // Add new
                $category_id = $this->task_templates_model->add_category($data);
                if ($category_id) {
                    set_alert('success', _l('added_successfully', _l('poly_utilities_category')));
                    redirect(admin_url('poly_utilities/task_templates'));
                } else {
                    set_alert('danger', _l('problem_adding', _l('poly_utilities_category')));
                }
            } else {
                // Update
                $success = $this->task_templates_model->update_category($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('poly_utilities_category')));
                    redirect(admin_url('poly_utilities/task_templates'));
                } else {
                    set_alert('danger', _l('problem_updating', _l('poly_utilities_category')));
                }
            }
        }

        $data['title'] = empty($id) ? _l('add_new', _l('poly_utilities_category')) : _l('edit', _l('poly_utilities_category'));
        
        if (!empty($id)) {
            $data['category'] = $this->task_templates_model->get_category($id);
            if (!$data['category']) {
                set_alert('danger', _l('not_found', _l('poly_utilities_category')));
                redirect(admin_url('poly_utilities/task_templates'));
            }
        }

        $this->load->view('task_templates/category', $data);
    }

    /**
     * Delete category
     */
    public function delete_category($id)
    {
        if (!has_permission('poly_utilities_task_templates', '', 'delete')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error('ID is required');
        }

        $result = $this->task_templates_model->delete_category($id);
        if ($result['success']) {
            poly_utilities_ajax_response_helper::response_success(_l('deleted', _l('poly_utilities_category')));
        } else {
            poly_utilities_ajax_response_helper::response_error($result['message'] ?? _l('problem_deleting', _l('poly_utilities_category')));
        }
    }

    /**
     * Update category status
     */
    public function update_category_status()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'edit')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $id = $this->input->post('id');
        $active = $this->input->post('active');

        if (empty($id)) {
            poly_utilities_ajax_response_helper::response_error('ID is required');
        }

        $success = $this->task_templates_model->update_category($id, [
            'active' => $active ? 1 : 0
        ]);

        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('updated_successfully', _l('poly_utilities_category')));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('problem_updating', _l('poly_utilities_category')));
        }
    }

    /**
     * Update category order
     */
    public function update_category_order()
    {
        if (!has_permission('poly_utilities_task_templates', '', 'edit')) {
            poly_utilities_ajax_response_helper::response_access(_l('access_denied'));
        }

        $orders = $this->input->post('order');
        if (empty($orders) || !is_array($orders)) {
            poly_utilities_ajax_response_helper::response_error('Invalid order data');
        }

        $success = $this->task_templates_model->update_category_order($orders);
        if ($success) {
            poly_utilities_ajax_response_helper::response_success(_l('updated_successfully'));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('problem_updating'));
        }
    }

    /**
     * Get templates for dropdown (AJAX)
     */
    public function get_templates_dropdown()
    {
        $templates = $this->task_templates_model->get_active_templates_for_dropdown();
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'code' => 200,
                'data' => $templates,
            ]));
    }
}

