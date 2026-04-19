<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_lessons_model extends App_Model
{
    protected $table = 'flexacademy_lessons';

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
    public function get($conditions)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if(!empty($conditions)){
            $this->db->where($conditions);
        }
        $this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_by_ids(array $lesson_ids)
    {
        if (empty($lesson_ids)) {
            return [];
        }

        $this->db->from(db_prefix() . $this->table);
        $this->db->where_in('id', $lesson_ids);
        $this->db->order_by('sort_order', 'ASC');

        $result = $this->db->get()->result_array();
        $map = [];

        foreach ($result as $lesson) {
            $map[$lesson['id']] = $lesson;
        }

        return $map;
    }

    public function get_course_lessons($course_id)
    {
        $this->db->select('l.*, s.title as section_title, s.sort_order as section_sort_order');
        $this->db->from(db_prefix() . $this->table . ' l');
        $this->db->join(db_prefix() . 'flexacademy_sections s', 's.id = l.section_id', 'left');
        $this->db->where('l.course_id', $course_id);
        $this->db->order_by('s.sort_order', 'ASC');
        $this->db->order_by('l.sort_order', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_total_duration($course_id)
    {
        $this->db->select_sum('duration');
        $this->db->where('course_id', $course_id);
        $row = $this->db->get(db_prefix() . $this->table)->row();

        return $row && isset($row->duration) ? (int) $row->duration : 0;
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
            log_activity('New Flexacademy Lesson Added [ID:' . $insert_id . ', ' . $data['title'] . ']');
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
            log_activity('Flexacademy Lesson Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_total_lessons($course_id)
    {
        $this->db->where('course_id', $course_id);
        return $this->db->count_all_results(db_prefix() . $this->table);
    }
}