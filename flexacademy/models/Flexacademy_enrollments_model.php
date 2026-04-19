<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_enrollments_model extends App_Model
{
    protected $table = 'flexacademy_enrollments';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get enrollment by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . $this->table)->row();
    }

    /**
     * Get enrollment by course and student
     * @param int $course_id
     * @param int $student_id
     * @param string $student_type 'client' or 'staff'
     * @return object|null
     */
    public function get_by_course_student($course_id, $student_id, $student_type = 'client')
    {
        $this->db->where('course_id', $course_id);
        $this->db->where('student_id', $student_id);
        $this->db->where('student_type', $student_type);
        return $this->db->get(db_prefix() . $this->table)->row();
    }

    /**
     * Get all enrollments for a student
     * @param int $student_id
     * @param array $where
     * @return array
     */
    public function get_student_enrollments($student_id, $where = [])
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('student_id', $student_id);
        $this->db->where('student_type', 'client');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('enrolled_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get all enrollments for a staff member
     * @param int $staff_id
     * @param array $where
     * @return array
     */
    public function get_staff_enrollments($staff_id, $where = [])
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('student_id', $staff_id);
        $this->db->where('student_type', 'staff');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('enrolled_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get all enrollments for a course
     * @param int $course_id
     * @param array $where
     * @return array
     */
    public function get_course_enrollments($course_id, $where = [])
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('course_id', $course_id);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('enrolled_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get enrollment count for a course
     * @param int $course_id
     * @return int
     */
    public function get_course_enrollment_count($course_id)
    {
        $this->db->where('course_id', $course_id);
        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    /**
     * Check if student is enrolled in course
     * @param int $course_id
     * @param int $student_id
     * @param string $student_type 'client' or 'staff'
     * @return bool
     */
    public function is_enrolled($course_id, $student_id, $student_type = 'client')
    {
        $this->db->where('course_id', $course_id);
        $this->db->where('student_id', $student_id);
        $this->db->where('student_type', $student_type);
        return $this->db->count_all_results(db_prefix() . $this->table) > 0;
    }

    /**
     * Enroll student in course
     * @param array $course
     * @param int $student_id
     * @param bool $create_invoice Whether to create invoice for paid courses
     * @return int|false
     */
    public function enroll_student($data)
    {
        if ($this->is_enrolled($data['course_id'], $data['student_id'], $data['student_type']) && flexacademy_is_enrollment_expired($data['expires_at']) == false) {
            return false;
        }

        $this->db->insert(db_prefix() . $this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update enrollment status
     * @param int $enrollment_id
     * @param string $status
     * @return bool
     */
    public function update_status($enrollment_id, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'completed') {
            $data['completion_date'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $enrollment_id);
        $result = $this->db->update(db_prefix() . $this->table, $data);

        return $result;
    }

    /**
     * Update enrollment progress
     * @param int $enrollment_id
     * @param float $progress
     * @return bool
     */
    public function update_progress($enrollment_id, $progress)
    {
        $normalized_progress = round((float) $progress, 2);

        if ($normalized_progress > 100) {
            $normalized_progress = 100;
        }

        if ($normalized_progress < 0) {
            $normalized_progress = 0;
        }

        $data = [
            'progress' => $normalized_progress,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Auto-update status based on progress
        if ($normalized_progress >= 100) {
            $data['status'] = 'completed';
            $data['completion_date'] = date('Y-m-d H:i:s');
        } elseif ($normalized_progress > 0) {
            $data['status'] = 'in_progress';
        }

        $this->db->where('id', $enrollment_id);
        $updated = $this->db->update(db_prefix() . $this->table, $data);

        
        return $updated;
    }

    /**
     * Drop student from course
     * @param int $enrollment_id
     * @return bool
     */
    public function drop_student($enrollment_id)
    {
        $this->db->where('id', $enrollment_id);
        return $this->db->delete(db_prefix() . $this->table);
    }

    /**
     * Initialize lesson progress for all lessons in course
     * @param int $enrollment_id
     * @param int $course_id
     * @return bool
     */
    

    public function update_enrollment($enrollment_id, array $data)
    {
        if (empty($data)) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $enrollment_id);

        return $this->db->update(db_prefix() . $this->table, $data);
    }

    public function get_client_enrollments()
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('student_type', 'client');
        $this->db->order_by('enrolled_at', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_all_staff_enrollments()
    {
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('student_type', 'staff');
        $this->db->order_by('enrolled_at', 'DESC');

        return $this->db->get()->result_array();
    }
 
    public function count_total($student_type = null)
    {
        if ($student_type !== null) {
            $this->db->where('student_type', $student_type);
        }

        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    public function count_expired($student_type = null)
    {
        $this->db->where('expires_at IS NOT NULL', null, false);
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));

        if ($student_type !== null) {
            $this->db->where('student_type', $student_type);
        }

        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    public function count_by_status($status, $student_type = null)
    {
        $this->db->where('status', $status);

        if ($student_type !== null) {
            $this->db->where('student_type', $student_type);
        }

        return $this->db->count_all_results(db_prefix() . $this->table);
    }

    public function get_recent_enrollments($limit = 5, $student_type = null)
    {
        $this->db->from(db_prefix() . $this->table . ' e');

        if ($student_type !== null) {
            $this->db->where('e.student_type', $student_type);
        }

        $this->db->order_by('e.enrolled_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_top_courses($limit = 5, $student_type = null)
    {
        $this->db->select('course_id, COUNT(id) as enrollment_count, AVG(progress) as avg_progress');
        $this->db->from(db_prefix() . $this->table);

        if ($student_type !== null) {
            $this->db->where('student_type', $student_type);
        }

        $this->db->group_by('course_id');
        $this->db->order_by('enrollment_count', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    /**
     * Get enrollment statistics
     * @param int $course_id
     * @return array
     */
    public function get_enrollment_stats($course_id)
    {
        $stats = [];

        // Total enrollments
        $this->db->where('course_id', $course_id);
        $stats['total_enrollments'] = $this->db->count_all_results(db_prefix() . 'flexacademy_enrollments');

        // Active enrollments
        $this->db->where('course_id', $course_id);
        $this->db->where_in('status', ['enrolled', 'in_progress']);
        $stats['active_enrollments'] = $this->db->count_all_results(db_prefix() . 'flexacademy_enrollments');

        // Completed enrollments
        $this->db->where('course_id', $course_id);
        $this->db->where('status', 'completed');
        $stats['completed_enrollments'] = $this->db->count_all_results(db_prefix() . 'flexacademy_enrollments');

        // Average progress
        $this->db->select_avg('progress');
        $this->db->where('course_id', $course_id);
        $this->db->where('status !=', 'dropped');
        $avg_progress = $this->db->get(db_prefix() . 'flexacademy_enrollments')->row();
        $stats['average_progress'] = $avg_progress ? round($avg_progress->progress, 2) : 0;

        return $stats;
    }

    /**
     * Delete enrollment and related data
     * @param int $enrollment_id
     * @return bool
     */
    public function delete($enrollment_id)
    {
        // Delete lesson progress
        $this->db->where('enrollment_id', $enrollment_id);
        $this->db->delete(db_prefix() . 'flexacademy_lesson_progress');

        // Delete quiz attempts
        $this->db->where('enrollment_id', $enrollment_id);
        $this->db->delete(db_prefix() . 'flexacademy_quiz_attempts');

        // Delete certificates
        $this->db->where('enrollment_id', $enrollment_id);
        $this->db->delete(db_prefix() . 'flexacademy_certificates');

        // Delete enrollment
        $this->db->where('id', $enrollment_id);
        $result = $this->db->delete(db_prefix() . 'flexacademy_enrollments');

        if ($result) {
            log_activity('Enrollment deleted [Enrollment ID: ' . $enrollment_id . ']');
        }

        return $result;
    }
}


