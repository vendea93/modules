<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_sections_model extends App_Model
{
    protected $table = 'flexacademy_sections';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $conditions
     * @return array|array[]
     * get all models
     */
    public function all($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if(!empty($conditions)){
            $this->db->where($conditions);
        }
        $this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * @param $conditions
     * @return array
     * get model by id
     */
    public function get($conditions, $relations)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if(!empty($conditions)){
            $this->db->where($conditions);
        }
        if(!empty($relations)){
            foreach($relations as $relation) {
                $this->db->join($relation, $relation . '.id');
            }
        }
        $this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * @param $data
     * @return bool
     * add model
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Flexacademy Section Added [ID:' . $insert_id . ', ' . $data['title'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * @param $data
     * @param $id
     * @return bool
     * update model
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     * delete model
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->table);
        if ($this->db->affected_rows() > 0) {
            log_activity('Flexacademy Section Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_max_sort_order($course_id)
    {
        $this->db->select_max('sort_order');
        $this->db->where('course_id', $course_id);
        $query = $this->db->get(db_prefix() . $this->table);
        return $query->row_array()['sort_order'] ? $query->row_array()['sort_order'] : 0;
    }

}