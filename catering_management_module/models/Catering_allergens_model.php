<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_allergens_model extends App_Model
{
    private $table = 'catering_allergens';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get allergen by id
     * @param  mixed $id allergen id
     * @return object
     */
    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . $this->table)->row();
    }

    /**
     * Get all allergens or filtered
     * @param  array $where conditions
     * @return array
     */
    public function get_all($where = [])
    {
        if (isset($where['active'])) {
            $this->db->where('active', $where['active']);
        }

        $this->db->order_by('display_order', 'ASC');
        $this->db->order_by('label', 'ASC');
        
        return $this->db->get(db_prefix() . $this->table)->result_array();
    }

    /**
     * Add new allergen
     * @param array $data allergen data
     * @return mixed
     */
    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');

        if (!isset($data['display_order'])) {
            $data['display_order'] = $this->get_max_display_order() + 1;
        }

        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Allergen Created [ID: ' . $insert_id . ', Code: ' . $data['code'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update allergen
     * @param  mixed $id   allergen id
     * @param  array $data update data
     * @return boolean
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Allergen Updated [ID: ' . $id . ']');
            return true;
        }

        return false;
    }

    /**
     * Delete allergen
     * @param  mixed $id allergen id
     * @return boolean
     */
    public function delete($id)
    {
        // Check if allergen is in use
        if ($this->is_allergen_in_use($id)) {
            return [
                'status' => false,
                'message' => _l('allergen_cannot_be_deleted_in_use')
            ];
        }

        $allergen = $this->get($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->table);

        if ($this->db->affected_rows() > 0) {
            log_activity('Allergen Deleted [ID: ' . $id . ', Code: ' . $allergen->code . ']');
            return ['status' => true];
        }

        return ['status' => false, 'message' => _l('something_went_wrong')];
    }

    /**
     * Check if allergen is in use by menu items
     * @param  mixed $id allergen id
     * @return boolean
     */
    private function is_allergen_in_use($id)
    {
        $this->db->where('allergen_id', $id);
        $count = $this->db->count_all_results(db_prefix() . 'catering_menu_item_allergens');
        
        return $count > 0;
    }

    /**
     * Get max display order
     * @return int
     */
    private function get_max_display_order()
    {
        $this->db->select_max('display_order');
        $result = $this->db->get(db_prefix() . $this->table)->row();
        
        return $result->display_order ?? 0;
    }

    /**
     * Update display orders
     * @param  array $orders array of id => order
     * @return boolean
     */
    public function update_display_orders($orders)
    {
        foreach ($orders as $id => $order) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . $this->table, ['display_order' => $order]);
        }
        return true;
    }

    /**
     * Get allergens by severity
     * @param  string $severity
     * @return array
     */
    public function get_by_severity($severity)
    {
        $this->db->where('severity', $severity);
        $this->db->where('active', 1);
        $this->db->order_by('display_order', 'ASC');
        
        return $this->db->get(db_prefix() . $this->table)->result_array();
    }
}
