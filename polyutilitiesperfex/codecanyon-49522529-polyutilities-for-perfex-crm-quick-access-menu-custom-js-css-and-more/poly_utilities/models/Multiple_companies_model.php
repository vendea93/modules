<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Multiple Companies Model
 * Handles operations for managing multiple companies with the same contact email
 * Optimized with caching and batch operations
 */
class Multiple_companies_model extends App_Model
{
    /**
     * Cache storage for companies data
     * @var array
     */
    private $companies_cache = [];

    /**
     * Cache storage for email validation
     * @var array
     */
    private $validation_cache = [];

    /**
     * Cache TTL in seconds (5 minutes)
     * @var int
     */
    private $cache_ttl = 300;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all companies associated with a contact email (with caching)
     * @param string $email Contact email address
     * @param int $customer_id Current customer ID to exclude
     * @return array List of companies with contact info
     */
    public function get_contact_companies($email = '', $customer_id = 0)
    {
        if (empty($email)) {
            return [];
        }

        // Generate cache key
        $cache_key = md5($email . '_' . $customer_id);

        // Check cache
        if (isset($this->companies_cache[$cache_key])) {
            return $this->companies_cache[$cache_key];
        }

        if (!empty($customer_id)) {
            $this->db->where('contact.userid !=', $customer_id);
        }

        $result = $this->db->select('client.company, contact.userid, contact.id, contact.firstname, contact.lastname, contact.is_primary, contact.email')
                        ->from(db_prefix() . 'contacts contact')
                        ->join(db_prefix() . 'clients client', 'contact.userid = client.userid')
                        ->where('contact.email', $email)
                        ->group_by('contact.userid')
                        ->order_by('client.company')
                        ->get()
                        ->result();

        // Store in cache
        $this->companies_cache[$cache_key] = $result;

        return $result;
    }

    /**
     * Clear companies cache
     * @param string $email Optional email to clear specific cache
     */
    public function clear_companies_cache($email = null)
    {
        if ($email === null) {
            $this->companies_cache = [];
        } else {
            // Clear all cache entries for this email
            foreach (array_keys($this->companies_cache) as $key) {
                if (strpos($key, md5($email)) !== false) {
                    unset($this->companies_cache[$key]);
                }
            }
        }
    }

    /**
     * Copy contact from one company to another
     * @param int $customer_id Target customer ID
     * @param int $contact_id Source contact ID to copy
     * @return bool Success status
     */
    public function contact_copy($customer_id, $contact_id)
    {
        $contact = $this->db->select('*')
                            ->from(db_prefix() . 'contacts')
                            ->where('id', $contact_id)
                            ->get()
                            ->row();

        if (!empty($contact)) {
            unset($contact->id);

            $contact->userid = $customer_id;
            $contact->datecreated = date('Y-m-d H:i:s');
            $contact->is_primary = 0;

            $this->db->insert(db_prefix() . 'contacts', $contact);

            $new_contact_id = $this->db->insert_id();

            if ($new_contact_id) {
                log_activity('Contact Added FROM Multiple Companies Feature [ID: ' . $new_contact_id . ']');

                hooks()->do_action('contact_created', $new_contact_id);

                return true;
            }
        }

        return false;
    }

    /**
     * Check if email exists in the same customer (with caching)
     * @param string $email Email to check
     * @param int $customer_id Customer ID
     * @param int $contact_id Contact ID to exclude
     * @return bool
     */
    public function email_exists_in_customer($email, $customer_id, $contact_id = 0)
    {
        if (empty($email)) {
            return false;
        }

        // Generate cache key
        $cache_key = md5('email_check_' . $email . '_' . $customer_id . '_' . $contact_id);

        // Check cache
        if (isset($this->validation_cache[$cache_key])) {
            return $this->validation_cache[$cache_key];
        }

        $this->db->where('userid', $customer_id);
        $this->db->where('email', $email);
        
        if ($contact_id > 0) {
            $this->db->where('id !=', $contact_id);
        }

        $result = $this->db->count_all_results(db_prefix() . 'contacts') > 0;

        // Store in cache
        $this->validation_cache[$cache_key] = $result;

        return $result;
    }

    /**
     * Batch copy contacts to a customer (with transaction)
     * @param int $customer_id Target customer ID
     * @param array $contact_ids Array of source contact IDs
     * @return array Result with success and failed counts
     */
    public function contact_copy_batch($customer_id, $contact_ids = [])
    {
        if (empty($contact_ids) || !is_array($contact_ids)) {
            return ['success' => 0, 'failed' => 0, 'contact_ids' => []];
        }

        $success_count = 0;
        $failed_count = 0;
        $new_contact_ids = [];

        // Begin transaction
        $this->db->trans_start();

        foreach ($contact_ids as $contact_id) {
            $new_id = $this->contact_copy_single($customer_id, $contact_id);
            if ($new_id) {
                $success_count++;
                $new_contact_ids[] = $new_id;
            } else {
                $failed_count++;
            }
        }

        // Complete transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => 0, 'failed' => count($contact_ids), 'contact_ids' => []];
        }

        return [
            'success' => $success_count,
            'failed' => $failed_count,
            'contact_ids' => $new_contact_ids
        ];
    }

    /**
     * Copy single contact (internal method for batch operation)
     * @param int $customer_id Target customer ID
     * @param int $contact_id Source contact ID
     * @return int|bool New contact ID or false
     */
    private function contact_copy_single($customer_id, $contact_id)
    {
        $contact = $this->db->select('*')
                            ->from(db_prefix() . 'contacts')
                            ->where('id', $contact_id)
                            ->get()
                            ->row();

        if (!empty($contact)) {
            unset($contact->id);

            $contact->userid = $customer_id;
            $contact->datecreated = date('Y-m-d H:i:s');
            $contact->is_primary = 0;

            $this->db->insert(db_prefix() . 'contacts', $contact);

            $new_contact_id = $this->db->insert_id();

            if ($new_contact_id) {
                log_activity('Contact Added FROM Multiple Companies Feature [ID: ' . $new_contact_id . ']');
                hooks()->do_action('contact_created', $new_contact_id);
                
                // Clear cache for this email
                if (!empty($contact->email)) {
                    $this->clear_companies_cache($contact->email);
                }

                return $new_contact_id;
            }
        }

        return false;
    }

    /**
     * Get contact statistics by email
     * @param string $email Contact email
     * @return array Statistics data
     */
    public function get_contact_stats($email)
    {
        if (empty($email)) {
            return [
                'total_companies' => 0,
                'primary_count' => 0,
                'secondary_count' => 0
            ];
        }

        $contacts = $this->db->select('is_primary')
                            ->from(db_prefix() . 'contacts')
                            ->where('email', $email)
                            ->get()
                            ->result();

        $primary = 0;
        $secondary = 0;

        foreach ($contacts as $contact) {
            if ($contact->is_primary == 1) {
                $primary++;
            } else {
                $secondary++;
            }
        }

        return [
            'total_companies' => count($contacts),
            'primary_count' => $primary,
            'secondary_count' => $secondary
        ];
    }
}

