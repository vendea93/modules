<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_certificates_model extends App_Model
{
    protected $table = 'flexacademy_certificates';

    public function __construct()
    {
        parent::__construct();
    }

    public function count_all()
    {
        return $this->db->count_all(db_prefix() . $this->table);
    }

    public function get($id)
    {
        return $this->db
            ->where('id', $id)
            ->get(db_prefix() . $this->table)
            ->row();
    }

    public function find_by_number($certificate_number)
    {
        return $this->db
            ->where('certificate_number', $certificate_number)
            ->get(db_prefix() . $this->table)
            ->row();
    }

    public function get_by_enrollment($enrollment_id)
    {
        return $this->db
            ->where('enrollment_id', $enrollment_id)
            ->get(db_prefix() . $this->table)
            ->row();
    }

    public function issue_certificate($enrollment)
    {
        if (!$enrollment) {
            return false;
        }

        $existing = $this->get_by_enrollment($enrollment->id);
        if ($existing) {
            return $existing->id;
        }

        $certificate_number = $this->generate_certificate_number();
        $now = date('Y-m-d H:i:s');

        $data = [
            'enrollment_id'      => $enrollment->id,
            'certificate_number' => $certificate_number,
            'issue_date'         => $now,
            'status'             => 'active',
            'template_id'        => 1,
            'created_at'         => $now,
        ];

        $this->db->insert(db_prefix() . $this->table, $data);
        $certificate_id = $this->db->insert_id();

        if ($certificate_id) {
            $this->db->where('id', $enrollment->id)
                ->update(db_prefix() . 'flexacademy_enrollments', [
                    'certificate_id' => $certificate_id,
                    'updated_at'     => $now,
                ]);

            log_activity('FlexAcademy: Certificate issued [Enrollment ID: ' . $enrollment->id . ', Certificate #' . $certificate_number . ']');
        }

        return $certificate_id;
    }

    private function generate_certificate_number()
    {
        $this->load->helper('string');

        $custom_prefix = '';
        if (!function_exists('get_option')) {
            $this->load->model('options_model');
            $custom_prefix = $this->options_model->get('flexacademy_certificate_prefix');
        } else {
            $custom_prefix = get_option('flexacademy_certificate_prefix');
        }

        $custom_prefix = strtoupper(trim((string) $custom_prefix));
        if ($custom_prefix === '') {
            $custom_prefix = 'FLEX';
        }

        $prefix = $custom_prefix . '-' . date('Ymd');
        do {
            $candidate = $prefix . '-' . strtoupper(random_string('alnum', 6));
            $exists = $this->db
                ->where('certificate_number', $candidate)
                ->count_all_results(db_prefix() . $this->table) > 0;
        } while ($exists);

        return $candidate;
    }
}
