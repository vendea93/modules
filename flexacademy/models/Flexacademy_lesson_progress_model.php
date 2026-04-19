<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_lesson_progress_model extends App_Model
{
    protected $table = 'flexacademy_lesson_progress';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_entry($enrollment_id, $lesson_id)
    {
        $this->db->where('enrollment_id', $enrollment_id);
        $this->db->where('lesson_id', $lesson_id);

        return $this->db->get(db_prefix() . $this->table)->row();
    }

    public function update_entry($enrollment_id, $lesson_id, $status, $time_spent = 0, $score = null)
    {
        // Only persist data when the lesson is completed; otherwise delete any existing record
        if ($status !== 'completed') {
            $this->db->where('enrollment_id', $enrollment_id);
            $this->db->where('lesson_id', $lesson_id);
            $this->db->delete(db_prefix() . $this->table);

            return true;
        }

        $existing = $this->get_entry($enrollment_id, $lesson_id);
        $data = [
            'time_spent'     => $time_spent,
            'score'          => $score,
            'completion_date'=> date('Y-m-d H:i:s'),
            'last_accessed'  => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('enrollment_id', $enrollment_id);
            $this->db->where('lesson_id', $lesson_id);
            return $this->db->update(db_prefix() . $this->table, $data);
        }

        $data['enrollment_id'] = $enrollment_id;
        $data['lesson_id'] = $lesson_id;
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->db->insert(db_prefix() . $this->table, $data);
    }

    public function count_completed($enrollment_id)
    {
        $this->db->where('enrollment_id', $enrollment_id);

        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    public function sum_time_spent($enrollment_id)
    {
        $this->db->select_sum('time_spent');
        $this->db->where('enrollment_id', $enrollment_id);

        $result = $this->db->get(db_prefix() . $this->table)->row();

        return $result ? (int) $result->time_spent : 0;
    }

    public function get_last_accessed_entry($enrollment_id)
    {
        $this->db->where('enrollment_id', $enrollment_id);
        $this->db->where('last_accessed IS NOT NULL', null, false);
        $this->db->order_by('last_accessed', 'DESC');
        $this->db->limit(1);

        return $this->db->get(db_prefix() . $this->table)->row_array();
    }

    public function get_entries_for_enrollment($enrollment_id)
    {
        $this->db->where('enrollment_id', $enrollment_id);
        $entries = $this->db->get(db_prefix() . $this->table)->result_array();

        $map = [];
        foreach ($entries as $entry) {
            $map[$entry['lesson_id']] = $entry;
        }

        return $map;
    }
}
