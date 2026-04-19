<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Multiple Companies Helper Functions
 * Reusable functions for multiple companies feature
 */

if (!function_exists('poly_mc_get_all_client_companies')) {
    /**
     * Get all companies for a contact email
     * @param string $email Contact email address
     * @param int $exclude_customer_id Customer ID to exclude from results
     * @return array List of companies with contact info
     */
    function poly_mc_get_all_client_companies($email, $exclude_customer_id = 0)
    {
        if (empty($email)) {
            return [];
        }

        $CI = &get_instance();
        $CI->load->model('poly_utilities/multiple_companies_model');
        
        return $CI->multiple_companies_model->get_contact_companies($email, $exclude_customer_id);
    }
}

if (!function_exists('poly_mc_sync_password_for_email')) {
    /**
     * Sync password for all contacts with same email
     * @param string $email Contact email
     * @param string $password_hash Hashed password
     * @return bool Success status
     */
    function poly_mc_sync_password_for_email($email, $password_hash)
    {
        if (empty($email) || empty($password_hash)) {
            return false;
        }

        $CI = &get_instance();
        $CI->db->set('password', $password_hash)
                ->set('last_password_change', date('Y-m-d H:i:s'))
                ->where('email', $email)
                ->update(db_prefix() . 'contacts');
        
        return $CI->db->affected_rows() > 0;
    }
}

if (!function_exists('poly_mc_check_email_in_customer')) {
    /**
     * Check if email exists in customer (excluding specific contact)
     * @param string $email Email to check
     * @param int $customer_id Customer ID
     * @param int $exclude_contact_id Contact ID to exclude from check
     * @return bool True if email exists
     */
    function poly_mc_check_email_in_customer($email, $customer_id, $exclude_contact_id = 0)
    {
        if (empty($email)) {
            return false;
        }

        $CI = &get_instance();
        $CI->load->model('poly_utilities/multiple_companies_model');
        
        return $CI->multiple_companies_model->email_exists_in_customer(
            $email, 
            $customer_id, 
            $exclude_contact_id
        );
    }
}

if (!function_exists('poly_mc_get_contact_avatar')) {
    /**
     * Get contact avatar URL with fallback
     * @param int $contact_id Contact ID
     * @param string $contact_email Contact email for Gravatar
     * @param int $size Avatar size
     * @return string Avatar URL
     */
    function poly_mc_get_contact_avatar($contact_id, $contact_email = '', $size = 32)
    {
        static $cache = [];

        $cacheKey = $contact_id . ':' . (int) $size;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // Check if contact has uploaded avatar
        $avatar_path = 'uploads/client_profile_images/' . $contact_id . '/profile_image.jpg';
        if (file_exists(FCPATH . $avatar_path)) {
            $cache[$cacheKey] = base_url($avatar_path) . '?v=' . time();
            return $cache[$cacheKey];
        }
        
        // Default avatar (local asset only)
        $cache[$cacheKey] = base_url('assets/images/user-placeholder.jpg');
        return $cache[$cacheKey];
    }
}

if (!function_exists('poly_mc_get_company_logo')) {
    /**
     * Get company logo URL with fallback
     * @param int $customer_id Customer/Company ID
     * @param int $size Logo size
     * @return string Logo URL or empty string
     */
    function poly_mc_get_company_logo($customer_id, $size = 32)
    {
        static $cache = [];

        if (isset($cache[$customer_id])) {
            return $cache[$customer_id];
        }

        // Check if company has logo
        $logo_path = get_upload_path_by_type('company') . $customer_id . '/';
        
        if (file_exists($logo_path)) {
            $files = scandir($logo_path);
            foreach ($files as $file) {
                if (strpos($file, 'logo') !== false && $file != '.' && $file != '..') {
                    $cache[$customer_id] = base_url('uploads/company/' . $customer_id . '/' . $file);
                    return $cache[$customer_id];
                }
            }
        }
        
        $cache[$customer_id] = '';
        return $cache[$customer_id];
    }
}

if (!function_exists('poly_mc_has_multiple_companies')) {
    /**
     * Check if current contact has multiple companies
     * @return bool True if contact has multiple companies
     */
    function poly_mc_has_multiple_companies()
    {
        return !empty($_SESSION['all_clients']) && count($_SESSION['all_clients']) > 1;
    }
}

if (!function_exists('poly_mc_get_current_company')) {
    /**
     * Get current active company info
     * @return object|null Company object or null
     */
    function poly_mc_get_current_company()
    {
        if (!poly_mc_has_multiple_companies()) {
            return null;
        }

        $client_user_id = get_client_user_id();
        
        foreach ($_SESSION['all_clients'] as $client) {
            if ($client->userid == $client_user_id) {
                return $client;
            }
        }
        
        return null;
    }
}

if (!function_exists('poly_mc_copy_contacts_batch')) {
    /**
     * Copy multiple contacts to a customer
     * @param int $customer_id Target customer ID
     * @param array $contact_ids Array of contact IDs to copy
     * @return array Result with success and failed counts
     */
    function poly_mc_copy_contacts_batch($customer_id, $contact_ids = [])
    {
        if (empty($contact_ids) || !is_array($contact_ids)) {
            return ['success' => 0, 'failed' => 0];
        }

        $CI = &get_instance();
        $CI->load->model('poly_utilities/multiple_companies_model');
        
        return $CI->multiple_companies_model->contact_copy_batch($customer_id, $contact_ids);
    }
}

if (!function_exists('poly_mc_format_dropdown_item')) {
    /**
     * Format dropdown item with avatar and company name
     * @param object $client Client object with company info
     * @param bool $is_active Whether this is the active company
     * @return string HTML for dropdown item
     */
    function poly_mc_format_dropdown_item($client, $is_active = false)
    {
        $class = $is_active ? 'customers-nav-item-edit-profile active' : 'customers-nav-item-edit-profile';
        $link = $is_active ? '#' : site_url('poly_utilities/multiple_companies_clients/change_auth/' . $client->userid);
        
        // Get avatar
        $avatar = poly_mc_get_contact_avatar($client->id, $client->email ?? '', 24);
        $company_logo = poly_mc_get_company_logo($client->userid, 24);
        
        // Use company logo if available, otherwise contact avatar
        $display_avatar = !empty($company_logo) ? $company_logo : $avatar;
        
        $html = '<li class="' . $class . '">';
        $html .= '<a href="' . $link . '" class="tw-flex tw-items-center tw-py-2">';
        $html .= '<img src="' . $display_avatar . '" class="tw-w-6 tw-h-6 tw-rounded-full tw-mr-2" alt="' . htmlspecialchars($client->company) . '">';
        $html .= '<span>' . htmlspecialchars($client->company) . '</span>';
        if ($is_active) {
            $html .= '<i class="fa fa-check tw-ml-auto tw-text-success"></i>';
        }
        $html .= '</a>';
        $html .= '</li>';
        
        return $html;
    }
}




