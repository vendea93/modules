<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$template_data = [
    'course_language' => isset($course_language) ? $course_language : '',
    'qr_code_url' => isset($qr_code_url) ? $qr_code_url : '',
    'certificate_number' => isset($certificate_number) ? $certificate_number : '',
    'issue_date_formatted' => isset($issue_date_formatted) ? $issue_date_formatted : '',
    'student_name' => isset($student_name) ? $student_name : '',
    'course' => (isset($course) && is_array($course)) ? $course : ['title' => ''],
    'total_lessons' => isset($total_lessons) ? (int) $total_lessons : 0,
    'total_duration_formatted' => isset($total_duration_formatted) ? $total_duration_formatted : '',
    'course_level' => isset($course_level) ? $course_level : '',
    'primary_instructor' => isset($primary_instructor) ? $primary_instructor : '',
    'company_name' => isset($company_name) ? $company_name : '',
    'primary_instructor_signature_url' => isset($primary_instructor_signature_url) ? $primary_instructor_signature_url : '',
    'issuer_signature_url' => isset($issuer_signature_url) ? $issuer_signature_url : '',
    'certificate_prefix' => isset($certificate_prefix) ? $certificate_prefix : 'FLEX',
];

$this->load->view('flexacademy/partials/certificate/template-content', $template_data);
