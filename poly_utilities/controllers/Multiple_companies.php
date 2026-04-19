<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Multiple Companies Controller (Admin Side)
 * Handles admin operations for multiple companies feature
 */
class Multiple_companies extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('multiple_companies_model');
    }

    /**
     * Get and display contact companies
     * @param int $current_customer_id Current customer ID
     * @param int $contact_id Contact ID
     */
    public function get_contact_companies($current_customer_id = 0, $contact_id = 0)
    {
        $email_address = $this->input->get('email_adress');

        $data['customer_id']   = $current_customer_id;
        $data['contact_id']    = $contact_id;
        $data['companies']     = [];
        $data['email_address'] = $email_address;

        if (!empty($email_address)) {
            $data['companies'] = $this->multiple_companies_model->get_contact_companies($email_address, $current_customer_id);
        }

        // Check if email exists in the same customer
        $data['email_exist'] = 0;
        if (!empty($email_address)) {
            $data['email_exist'] = $this->multiple_companies_model->email_exists_in_customer(
                $email_address,
                $current_customer_id,
                $contact_id
            );
        }

        $this->load->view('multiple_companies/v_contact_companies', $data);
    }

    public function check_contact_email()
    {
        $this->validate_ajax_request();

        $email_address = $this->input->post('email');
        $contact_id    = $this->input->post('contact_id') ?: 0;
        $customer_id   = $this->input->post('customer_id');

        $success = empty($email_address) 
            ? false 
            : !$this->multiple_companies_model->email_exists_in_customer(
                $email_address,
                $customer_id,
                $contact_id
            );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($success));
    }

    /**
     * Display modal to add existing contact
     * @param int $customer_id Customer ID
     */
    public function exist_contact($customer_id = 0)
    {
        $data['customer_id'] = $customer_id;
        $this->load->view('multiple_companies/v_exist_contact', $data);
    }

    /**
     * AJAX: Save existing contact(s) to new company (optimized with batch)
     */
    public function save_contact()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $customer_id = $this->input->post('customer_id');
        $contacts    = $this->input->post('contacts');

        if (empty($contacts) || !is_array($contacts)) {
            echo json_encode([
                'success' => false,
                'message' => _l('poly_mc_no_contacts_selected')
            ]);
            return;
        }

        // Use batch operation
        $result = $this->multiple_companies_model->contact_copy_batch($customer_id, $contacts);

        $success = $result['success'] > 0;
        
        if ($success) {
            $message = $result['success'] == 1 
                ? _l('poly_mc_exist_contact_success')
                : _l('poly_mc_contacts_added_count', $result['success']);
        } else {
            $message = _l('poly_mc_exist_contact_failed');
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'stats' => [
                'added' => $result['success'],
                'failed' => $result['failed'],
                'total' => count($contacts)
            ]
        ]);
    }

    /**
     * AJAX: Check if contact email is valid (optimized)
     */
    private function validate_ajax_request()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
    }
}

