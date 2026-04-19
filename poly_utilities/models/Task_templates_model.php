<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Task_templates_model extends App_Model
{
    private $table_categories;
    private $table_templates;
    private $table_items;
    private $table_checklist_items;

    public function __construct()
    {
        parent::__construct();
        $this->table_categories = db_prefix() . 'poly_utilities_task_template_categories';
        $this->table_templates = db_prefix() . 'poly_utilities_task_templates';
        $this->table_items = db_prefix() . 'poly_utilities_task_template_items';
        $this->table_checklist_items = db_prefix() . 'poly_utilities_task_template_item_checklist_items';
    }

    // ==================== CATEGORIES ====================

    /**
     * Get all categories
     */
    public function get_categories($where = [])
    {
        $this->db->where($where);
        $this->db->order_by('order', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get($this->table_categories)->result_array();
    }

    /**
     * Get single category
     */
    public function get_category($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_categories)->row();
    }

    /**
     * Add category
     */
    public function add_category($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        
        if (!isset($data['order'])) {
            $max_order = $this->db->select_max('order')->get($this->table_categories)->row()->order;
            $data['order'] = ($max_order ? $max_order + 1 : 1);
        }

        // Remove fields that don't exist in table
        $allowed_fields = ['name', 'description', 'color', 'order', 'active', 'datecreated', 'created_by'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed_fields)) {
                unset($data[$key]);
            }
        }

        $this->db->insert($this->table_categories, $data);
        return $this->db->insert_id();
    }

    /**
     * Update category
     */
    public function update_category($id, $data)
    {
        $data['dateupdated'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();
        
        // Remove fields that don't exist in table
        $allowed_fields = ['name', 'description', 'color', 'order', 'active', 'dateupdated', 'updated_by'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed_fields)) {
                unset($data[$key]);
            }
        }
        
        $this->db->where('id', $id);
        $this->db->update($this->table_categories, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete category
     */
    public function delete_category($id)
    {
        // Check if category has templates
        $templates_count = $this->db->where('category_id', $id)->count_all_results($this->table_templates);
        if ($templates_count > 0) {
            return ['success' => false, 'message' => 'Cannot delete category with existing templates'];
        }

        $this->db->where('id', $id);
        $this->db->delete($this->table_categories);
        return ['success' => $this->db->affected_rows() > 0];
    }

    /**
     * Update category order
     */
    public function update_category_order($orders)
    {
        foreach ($orders as $order => $id) {
            $this->db->where('id', $id);
            $this->db->update($this->table_categories, ['order' => $order + 1]);
        }
        return true;
    }

    // ==================== TEMPLATES ====================

    /**
     * Get all templates
     */
    public function get_templates($where = [], $include_items = false)
    {
        $this->db->select('t.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table_templates . ' t');
        $this->db->join($this->table_categories . ' c', 'c.id = t.category_id', 'left');
        $this->db->where($where);
        $this->db->order_by('t.order', 'ASC');
        $this->db->order_by('t.id', 'ASC');
        $templates = $this->db->get()->result_array();

        if ($include_items) {
            foreach ($templates as &$template) {
                $template['items'] = $this->get_template_items($template['id']);
                $template['items_count'] = count($template['items']);
            }
        } else {
            foreach ($templates as &$template) {
                $template['items_count'] = $this->db->where('template_id', $template['id'])->count_all_results($this->table_items);
            }
        }

        return $templates;
    }

    /**
     * Get single template
     */
    public function get_template($id, $include_items = false)
    {
        $this->db->select('t.*, c.name as category_name, c.color as category_color');
        $this->db->from($this->table_templates . ' t');
        $this->db->join($this->table_categories . ' c', 'c.id = t.category_id', 'left');
        $this->db->where('t.id', $id);
        $template = $this->db->get()->row_array();

        if ($template && $include_items) {
            $template['items'] = $this->get_template_items($id);
        }

        return $template;
    }

    /**
     * Add template
     */
    public function add_template($data)
    {
        // Extract items before inserting template
        $items = null;
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        
        if (!isset($data['order'])) {
            $max_order = $this->db->select_max('order')->get($this->table_templates)->row()->order;
            $data['order'] = ($max_order ? $max_order + 1 : 1);
        }

        // Only insert template fields (not items)
        // Remove fields that don't exist in table
        $allowed_fields = ['name', 'description', 'category_id', 'order', 'active', 'datecreated', 'created_by'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed_fields)) {
                unset($data[$key]);
            }
        }
        
        // Ensure category_id is NULL if empty (for foreign key constraint)
        if (isset($data['category_id']) && (empty($data['category_id']) || $data['category_id'] == 0)) {
            $data['category_id'] = null;
        }
        
        $this->db->insert($this->table_templates, $data);
        $template_id = $this->db->insert_id();

        if (!$template_id) {
            return false;
        }

        // Add template items if provided
        if ($items !== null && is_array($items) && !empty($items)) {
            $this->add_template_items($template_id, $items);
        }

        return $template_id;
    }

    /**
     * Update template
     */
    public function update_template($id, $data)
    {
        $data['dateupdated'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();
        
        $items = null;
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        // Remove fields that don't exist in table
        $allowed_fields = ['name', 'description', 'category_id', 'order', 'active', 'dateupdated', 'updated_by'];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed_fields)) {
                unset($data[$key]);
            }
        }
        
        // Ensure category_id is NULL if empty (for foreign key constraint)
        if (isset($data['category_id']) && (empty($data['category_id']) || $data['category_id'] == 0)) {
            $data['category_id'] = null;
        }
        
        $this->db->where('id', $id);
        $this->db->update($this->table_templates, $data);
        $updated = $this->db->affected_rows() > 0;

        // Update items if provided
        if ($items !== null) {
            // Delete existing items
            $this->db->where('template_id', $id);
            $this->db->delete($this->table_items);
            
            // Add new items
            if (is_array($items) && !empty($items)) {
                $this->add_template_items($id, $items);
            }
        }

        return $updated;
    }

    /**
     * Delete template
     */
    public function delete_template($id)
    {
        // Items will be deleted automatically due to CASCADE
        $this->db->where('id', $id);
        $this->db->delete($this->table_templates);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Update template order
     */
    public function update_template_order($orders)
    {
        foreach ($orders as $order => $id) {
            $this->db->where('id', $id);
            $this->db->update($this->table_templates, ['order' => $order + 1]);
        }
        return true;
    }

    // ==================== TEMPLATE ITEMS ====================

    /**
     * Get template items
     */
    public function get_template_items($template_id)
    {
        $this->db->where('template_id', $template_id);
        $this->db->order_by('order', 'ASC');
        $this->db->order_by('id', 'ASC');
        $items = $this->db->get($this->table_items)->result_array();
        
        // Load checklist items for each item
        foreach ($items as &$item) {
            $item['checklist_items'] = $this->get_template_item_checklist_items($item['id']);
        }
        
        return $items;
    }

    /**
     * Get single template item
     */
    public function get_template_item($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table_items)->row();
    }

    /**
     * Add template items
     */
    public function add_template_items($template_id, $items)
    {
        if (empty($items) || !is_array($items)) {
            return false;
        }

        $order = 1;
        foreach ($items as $item) {
            // Skip items without name
            if (empty($item['name']) || trim($item['name']) === '') {
                continue;
            }

            $item_data = [
                'template_id' => $template_id,
                'name' => trim($item['name']),
                'description' => isset($item['description']) && !empty($item['description']) ? trim($item['description']) : null,
                'priority' => isset($item['priority']) && is_numeric($item['priority']) ? (int)$item['priority'] : 2,
                'estimated_hours' => isset($item['estimated_hours']) && !empty($item['estimated_hours']) ? (float)$item['estimated_hours'] : null,
                'milestone_id' => isset($item['milestone_id']) && !empty($item['milestone_id']) ? (int)$item['milestone_id'] : null,
                'order' => isset($item['order']) && is_numeric($item['order']) ? (int)$item['order'] : $order++,
                'datecreated' => date('Y-m-d H:i:s'),
            ];

            if (!$this->db->insert($this->table_items, $item_data)) {
                log_message('error', 'Failed to insert template item: ' . $this->db->error()['message']);
                continue;
            }
            
            $item_id = $this->db->insert_id();
            
            // Add checklist items if provided
            if (isset($item['checklist_items']) && is_array($item['checklist_items']) && !empty($item['checklist_items'])) {
                $this->add_template_item_checklist_items($item_id, $item['checklist_items']);
            }
        }
        return true;
    }

    /**
     * Update template item
     */
    public function update_template_item($id, $data)
    {
        $data['dateupdated'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update($this->table_items, $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete template item
     */
    public function delete_template_item($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table_items);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Update template items order
     */
    public function update_template_items_order($template_id, $orders)
    {
        foreach ($orders as $order => $id) {
            $this->db->where('id', $id);
            $this->db->where('template_id', $template_id);
            $this->db->update($this->table_items, ['order' => $order + 1]);
        }
        return true;
    }

    // ==================== CHECKLIST ITEMS ====================

    /**
     * Get checklist items for a template item
     */
    public function get_template_item_checklist_items($template_item_id)
    {
        $this->db->where('template_item_id', $template_item_id);
        $this->db->order_by('order', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get($this->table_checklist_items)->result_array();
    }

    /**
     * Add checklist items for a template item
     */
    public function add_template_item_checklist_items($template_item_id, $checklist_items)
    {
        if (empty($checklist_items) || !is_array($checklist_items)) {
            return false;
        }

        $order = 1;
        foreach ($checklist_items as $checklist_item) {
            // Skip items without description
            if (empty($checklist_item['description']) || trim($checklist_item['description']) === '') {
                continue;
            }

            $checklist_data = [
                'template_item_id' => $template_item_id,
                'description' => trim($checklist_item['description']),
                'order' => isset($checklist_item['order']) && is_numeric($checklist_item['order']) ? (int)$checklist_item['order'] : $order++,
                'datecreated' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert($this->table_checklist_items, $checklist_data);
        }
        return true;
    }

    /**
     * Delete checklist items for a template item
     */
    public function delete_template_item_checklist_items($template_item_id)
    {
        $this->db->where('template_item_id', $template_item_id);
        $this->db->delete($this->table_checklist_items);
        return $this->db->affected_rows() > 0;
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Create tasks from template for a project
     */
    public function create_tasks_from_template($template_id, $project_id)
    {
        $template = $this->get_template($template_id, true);
        if (!$template || !$template['active']) {
            return ['success' => false, 'message' => 'Template not found or inactive'];
        }

        if (empty($template['items'])) {
            return ['success' => false, 'message' => 'Template has no items'];
        }

        $this->load->model('tasks_model');
        $created_tasks = [];
        $errors = [];

        foreach ($template['items'] as $item) {
            $task_data = [
                'name' => $item['name'],
                'description' => $item['description'],
                'priority' => $item['priority'],
                'rel_type' => 'project',
                'rel_id' => $project_id,
                'status' => 1, // Not Started
                'startdate' => date('Y-m-d'),
                'duedate' => null,
            ];

            if (!empty($item['estimated_hours'])) {
                $task_data['hourly_rate'] = 0;
            }

            $task_id = $this->tasks_model->add($task_data);
            if ($task_id) {
                $created_tasks[] = $task_id;
                
                // Create checklist items for the task if they exist
                if (isset($item['checklist_items']) && is_array($item['checklist_items']) && !empty($item['checklist_items'])) {
                    $checklist_order = 1;
                    foreach ($item['checklist_items'] as $checklist_item) {
                        if (!empty($checklist_item['description'])) {
                            $this->tasks_model->add_checklist_item([
                                'taskid' => $task_id,
                                'description' => trim($checklist_item['description']),
                                'list_order' => $checklist_order++,
                            ]);
                        }
                    }
                }
            } else {
                $errors[] = $item['name'];
            }
        }

        return [
            'success' => count($created_tasks) > 0,
            'created_count' => count($created_tasks),
            'created_tasks' => $created_tasks,
            'errors' => $errors,
        ];
    }

    /**
     * Get active templates for dropdown
     */
    public function get_active_templates_for_dropdown()
    {
        $templates = $this->get_templates(['t.active' => 1]);
        $options = ['' => _l('dropdown_non_selected_tex')];
        
        foreach ($templates as $template) {
            $category_name = $template['category_name'] ? ' (' . $template['category_name'] . ')' : '';
            $options[$template['id']] = $template['name'] . $category_name;
        }
        
        return $options;
    }
}

