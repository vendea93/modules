<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_courses_model extends App_Model
{
    protected $table = 'flexacademy_courses';

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
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
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
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * @param $slug
     * @return array
     * get model by slug
     */
    public function get_by_slug_or_id($slug)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('slug', $slug);
        $query = $this->db->get();
        $result = $query->row_array();
        if($result){
            return $result;
        }else{
            $this->db->where('id', $slug);
            $this->db->from(db_prefix() . $this->table);
            $query = $this->db->get();
            $result = $query->row_array();
        }
        return $result;
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
            log_activity('New Course Added [ID:' . $insert_id  . ']');
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
            log_activity('Course Deleted [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    public function get_client_course_listing($filters = [])
    {
        $per_page = isset($filters['per_page']) ? (int) $filters['per_page'] : null;
        $offset   = isset($filters['offset']) ? (int) $filters['offset'] : 0;

        $this->db->from(db_prefix() . $this->table);
        $this->db->where_in('access', ['clients', 'everyone']);

        if (!empty($filters['category_id'])) {
            $this->db->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['pricing']) && in_array($filters['pricing'], ['free', 'paid'], true)) {
            $this->db->where('pricing_type', $filters['pricing']);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('title', $filters['search']);
            $this->db->or_like('description', $filters['search']);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results('', false);

        if ($per_page !== null) {
            $this->db->limit($per_page, $offset);
        }

        $this->db->order_by('created_at', 'DESC');

        $courses = $this->db->get()->result_array();

        return [
            'total'   => $total,
            'courses' => $courses,
        ];
    }

    public function count_all_courses()
    {
        return $this->db->count_all(db_prefix() . $this->table);
    }

    public function count_active_courses()
    {
        $this->db->where('status', 'active');
        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    public function get_recent_courses($limit = 5)
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_many(array $course_ids)
    {
        if (empty($course_ids)) {
            return [];
        }

        $this->db->from(db_prefix() . $this->table);
        $this->db->where_in('id', $course_ids);

        $result = $this->db->get()->result_array();
        $map = [];

        foreach ($result as $course) {
            $map[$course['id']] = $course;
        }

        return $map;
    }

    public function get_staff_accessible_courses()
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('status', 'active');
        $this->db->group_start();
        $this->db->where_in('access', ['staffs', 'everyone']);
        $this->db->or_where('access IS NULL', null, false);
        $this->db->group_end();

        return $this->db->get()->result_array();
    }

    public function is_creator($course_id, $user_id)
    {
        $this->db->where('id', $course_id);
        $this->db->where('creator_id', $user_id);
        return $this->db->count_all_results(db_prefix() . $this->table) > 0;
    }
}