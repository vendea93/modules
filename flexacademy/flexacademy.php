<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: FlexAcademy
Description: Complete Learning Management System for Perfex CRM
Author: FlexiByte
Author URI: https://codecanyon.net/user/flexibyte88
Version: 1.0.0
Requires at least: 3.0.*
*/

define('FLEXACADEMY_MODULE_NAME', 'flexacademy');
define('FLEXACADEMY_FOLDER',  FCPATH . 'uploads/flexacademy' . '/');

/**
 * Register activation module hook
 */
register_activation_hook(FLEXACADEMY_MODULE_NAME, 'flexacademy_activation_hook');

hooks()->add_action('admin_init', 'flexacademy_admin_init');
hooks()->add_action('client_area_menu_loaded', 'flexacademy_client_menu');
hooks()->add_action('invoice_status_changed', 'flexacademy_invoice_status_changed');
hooks()->add_action('admin_init', 'flexacademy_permissions');
hooks()->add_action('clients_init', FLEXACADEMY_MODULE_NAME . '_clients_init');
register_merge_fields("flexacademy/merge_fields/flexacademy_merge_fields");

function flexacademy_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files
 */
register_language_files(FLEXACADEMY_MODULE_NAME, [FLEXACADEMY_MODULE_NAME]);

/**
 * Register merge fields for email templates
 */
register_merge_fields('flexacademy/merge_fields/flexacademy_merge_fields');

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */


function flexacademy_admin_init()
{
    $CI = &get_instance();


    if (has_permission('flexacademy', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('flexacademy', [
            'name'     => _flexacademy_lang('flexacademy'),
            'href'     => admin_url('flexacademy'),
            'icon'     => 'fa fa-graduation-cap',
            'position' => 20,
        ]);

        // Submenu items
        $CI->app_menu->add_sidebar_children_item('flexacademy', [
            'slug'     => 'flexacademy-dashboard',
            'name'     => _flexacademy_lang('_dashboard'),
            'href'     => admin_url('flexacademy'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('flexacademy', [
            'slug'     => 'flexacademy-courses',
            'name'     => _flexacademy_lang('courses'),
            'href'     => admin_url('flexacademy/courses'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('flexacademy', [
            'slug'     => 'flexacademy-categories',
            'name'     => _flexacademy_lang('categories'),
            'href'     => admin_url('flexacademy/categories'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('flexacademy', [
            'slug'     => 'flexacademy-enrollments',
            'name'     => _flexacademy_lang('enrollments'),
            'href'     => admin_url('flexacademy/enrollments'),
            'position' => 4,
        ]);
        $CI->app_menu->add_sidebar_children_item('flexacademy', [
            'slug'     => 'flexacademy-settings',
            'name'     => _flexacademy_lang('settings'),
            'href'     => admin_url('flexacademy/settings'),
            'position' => 50,
        ]);
        flexacademy_add_staff_menu_items($CI);
    }
    
}

function flexacademy_add_staff_menu_items($CI) {
   
        $CI->app_menu->add_sidebar_menu_item('flexacademy-staff-training', [
            'name'     => _flexacademy_lang('staff-training'),
            'href'     => admin_url('flexacademy/staff_courses'),
            'icon'     => 'fa fa-user-graduate',
            'position' => 30,
        ]);
        $CI->app_menu->add_sidebar_children_item('flexacademy-staff-training', [
            'slug'     => 'flexacademy-staff-all-courses',
            'name'     => _flexacademy_lang('all-courses'),
            'href'     => admin_url('flexacademy/staff_courses'),
            'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item('flexacademy-staff-training', [
            'slug'     => 'flexacademy-staff-enrollments',
            'name'     => _flexacademy_lang('my-enrollments'),
            'href'     => admin_url('flexacademy/staff_enrollments'),
            'position' => 2,
        ]);

}


function flexacademy_clients_init()
{
    if (is_client_logged_in()) {
        add_theme_menu_item('flexacademy', [
            'name' => _flexacademy_lang('my-courses'),
            'href' => site_url('flexacademy/my-courses'),
            'position' => 16,
            'icon'     => '',
        ]);
    }else{
        add_theme_menu_item('flexacademy', [
            'name' => _flexacademy_lang('courses'),
                'href' => site_url('flexacademy/courses'),
                'position' => 16,
                'icon'     => '',
            ]);
        }
}

// Add permissions
function flexacademy_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('flexacademy', $capabilities, _l('flexacademy'));
}


/**
 * Load helper function for language
 */
function _flexacademy_lang($line)
{
    $CI = &get_instance();
    $line = 'flexacademy_' . $line;
    if (!$CI->lang->line($line)) {
        return $line;
    }
    return $CI->lang->line($line);
}

/**
 * Add client menu items
 */

function flexacademy_client_menu()
{
    if (!is_client_logged_in()) {
        return;
    }

    add_theme_menu_item('flexacademy', [
        'name'     => _flexacademy_lang('courses'),
        'href'     => site_url('flexacademy/courses'),
        'position' => 20,
        'icon'     => 'fa fa-graduation-cap',
    ]);

    add_theme_menu_item('flexacademy-my-courses', [
        'name'     => _flexacademy_lang('my-courses'),
        'href'     => site_url('flexacademy/my-courses'),
        'position' => 21,
        'icon'     => 'fa fa-book',
    ]);
}


/**
 * Hook for invoice status changed
 * Update enrollment payment status when invoice status changes
 */

function flexacademy_invoice_status_changed($data)
{
    // If invoice is paid, process order and create enrollments
    if ($data['status'] == Invoices_model::STATUS_PAID) {
        // NEW FLOW: Find order by invoice and complete it
        $order = flexacademy_get_order_by_invoice($data['invoice_id']);
        
        if ($order && $order['status'] != 'completed') {
            // Complete order (creates enrollments)
            flexacademy_complete_order($order['id']);
        }
    }
}

/**
 * Send FlexAcademy email
 * @param array $data Email data
 * @param string $type Email type
 * @return bool
 */
function flexacademy_send_email($data, $type)
{
    $CI = &get_instance();
    $CI->load->library('flexacademy/flexacademy_module');
    
    try {
        return $CI->flexacademy_module->send_email($data, $type);
    } catch (Exception $e) {
        log_activity('FlexAcademy: Email sending failed - ' . $e->getMessage());
        return false;
    }
}

/**
 * Create FlexAcademy email templates
 * Call this manually or via Setup if needed
 */
function flexacademy_create_email_templates()
{
    $CI = &get_instance();
    $CI->load->library('flexacademy/flexacademy_module');
    $CI->flexacademy_module->create_email_templates();
}


function flexacademy_media_url($path) {
    if (empty($path)) {
        return '';
    }
    return base_url('uploads/flexacademy/' . $path);
}

function flexacademy_create_storage_directory() {
    $upload_path = FCPATH . 'uploads/flexacademy/';
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
}

/**
 * Handle payment success callback
 * @param int $invoice_id Invoice ID
 * @return bool
 */
function flexacademy_handle_payment_success($invoice_id)
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $CI->load->model('flexacademy/flexacademy_enrollments_model');
    $CI->load->model('flexacademy/flexacademy_courses_model');
    $CI->load->model('clients_model');

    $invoice = $CI->invoices_model->get($invoice_id);
    
    if (!$invoice) {
        return false;
    }

    // Check if invoice is paid
    if ($invoice->status != 2) { // 2 = Paid status
        return false;
    }

    // Get all enrollments for this invoice
    $enrollments = flexacademy_get_enrollments_by_invoice($invoice_id);

    if (empty($enrollments)) {
        return false;
    }

    // Calculate amount per enrollment
    $enrollment_count = count($enrollments);
    $amount_per_enrollment = $enrollment_count > 0 ? ($invoice->total / $enrollment_count) : 0;

    // Prepare data for payment received email
    $course_names = [];
    $customer_email = '';
    $customer_name = '';

    // Mark all enrollments as paid and activate course access
    foreach ($enrollments as $enrollment) {
        // Update payment status
        $update_data = [
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d H:i:s'),
            'amount_paid' => $amount_per_enrollment
        ];
        
        $CI->db->where('id', $enrollment['id']);
        $CI->db->update(db_prefix() . 'flexacademy_enrollments', $update_data);
        
        // Get course name for email
        $course = $CI->flexacademy_courses_model->get(['id' => $enrollment['course_id']]);
        if ($course) {
            $course_names[] = $course['title'];
        }
        
        // Log activity
        log_activity('FlexAcademy: Payment received and course access granted [Enrollment ID: ' . $enrollment['id'] . ', Invoice ID: ' . $invoice_id . ', Amount: ' . app_format_money($amount_per_enrollment, $invoice->currency_name) . ']');
    }

    // Get customer details for email
    if (!empty($enrollments)) {
        $first_enrollment = $enrollments[0];
        $contact = $CI->clients_model->get_contact($first_enrollment['student_id']);
        
        if ($contact) {
            $customer_email = $contact->email;
            $customer_name = $contact->firstname . ' ' . $contact->lastname;
            
            // Send payment received email
            flexacademy_send_payment_received_email($invoice, $enrollments, $course_names, $customer_email, $customer_name);
        }
    }

    return true;
}


/**
 * Send enrollment confirmation email
 * @param int $enrollment_id
 * @return bool
 */
function flexacademy_send_enrollment_email($enrollment_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_enrollments_model');
    $CI->load->model('flexacademy/flexacademy_courses_model');
    $CI->load->model('clients_model');
    
    $enrollment = $CI->flexacademy_enrollments_model->get($enrollment_id);
    
    if (!$enrollment) {
        return false;
    }
    
    // Get course details
    $course = $CI->flexacademy_courses_model->get(['id' => $enrollment->course_id]);
    if (!$course) {
        return false;
    }
    
    // Get sections and lessons for total count and duration
    $total_lessons = flexacademy_get_course_total_lessons($course['id']);
    $total_duration = flexacademy_get_course_total_duration($course['id']);
    
    // Get customer details
    $contact = $CI->clients_model->get_contact($enrollment->student_id);
    if (!$contact) {
        return false;
    }
    
    $enrollment_data = [
        'customer_name' => $contact->firstname . ' ' . $contact->lastname,
        'customer_email' => $contact->email,
        'course_name' => $course['title'],
        'enrollment_date' => date('M d, Y', strtotime($enrollment->enrollment_date)),
        'total_lessons' => $total_lessons,
        'course_duration' => flexacademy_convert_duration_from_minutes($total_duration),
        'course_url' => site_url('flexacademy/course/player/' . $course['slug']),
        'my_courses_url' => site_url('flexacademy/my-courses'),
        'enrollment_id' => $enrollment_id
    ];
    
    return flexacademy_send_email($enrollment_data, 'enrollment_confirmed');
}



/**
 * Create invoice for course enrollment
 * @param int $client_id Client ID
 * @param int $course_id Course ID
 * @param object $course Course object
 * @param int $enrollment_id Enrollment ID
 * @return int|false Invoice ID or false on failure
 */
function flexacademy_create_invoice($client_id, $course_id, $course, $enrollment_id = null)
{
    $CI = &get_instance();
    
    if (!class_exists('Invoices_model', false)) {
        $CI->load->model('invoices_model');
    }
    
    if (!class_exists('Payment_modes_model', false)) {
        $CI->load->model('payment_modes_model');
    }

    if (!class_exists('Currencies_model', false)) {
        $CI->load->model('currencies_model');
    }

    // Get payment modes
    $payment_modes = $CI->payment_modes_model->get();
    $allowed_payment_modes = array_pluck($payment_modes, 'id');

    // Get currency
    $currency = $CI->currencies_model->get_base_currency();

    // Calculate price
    $price = $course->discount_price > 0 ? $course->discount_price : $course->price;
    
    // Prepare invoice items
    $items = [[
        'description' => $course->title,
        'long_description' => strip_tags($course->short_description ?? $course->description ?? ''),
        'qty' => 1,
        'rate' => $price,
        'order' => 1,
        'taxname' => [], // Can be extended to support taxes
        'unit' => ''
    ]];

    // Calculate due date
    $due_after = get_option('invoice_due_after') > 0 ? get_option('invoice_due_after') : 30;

    // Get client billing information
    $CI->load->model('clients_model');
    $client = $CI->clients_model->get($client_id);
    
    $billing_street = isset($client->address) ? $client->address : '';
    $billing_city = isset($client->city) ? $client->city : '';
    $billing_state = isset($client->state) ? $client->state : '';
    $billing_zip = isset($client->zip) ? $client->zip : '';
    $billing_country = isset($client->country) ? $client->country : '';

    // Prepare invoice data
    $invoice_data = [
        'clientid' => $client_id,
        'number' => get_option('next_invoice_number'),
        'number_format' => get_option('invoice_number_format'),
        'date' => date('Y-m-d'),
        'duedate' => date('Y-m-d', strtotime('+' . $due_after . ' DAY')),
        'currency' => $currency->id,
        'newitems' => $items,
        'subtotal' => $price,
        'total' => $price,
        'adjustment' => 0,
        'discount_percent' => 0,
        'discount_total' => 0,
        'discount_type' => '',
        'sale_agent' => 0,
        'billing_street' => $billing_street,
        'billing_city' => $billing_city,
        'billing_state' => $billing_state,
        'billing_zip' => $billing_zip,
        'billing_country' => $billing_country,
        'shipping_street' => '',
        'shipping_city' => '',
        'shipping_state' => '',
        'shipping_zip' => '',
        'shipping_country' => '',
        'include_shipping' => 0,
        'show_shipping_on_invoice' => 0,
        'show_quantity_as' => 1,
        'clientnote' => _flexacademy_lang('invoice-note-for-course') . ' ' . $course->title,
        'adminnote' => 'FlexAcademy Course: ' . $course->title . ($enrollment_id ? ' (Enrollment ID: ' . $enrollment_id . ')' : ''),
        'terms' => get_option('predefined_terms_invoice'),
        'allowed_payment_modes' => $allowed_payment_modes,
        'status' => 1, // Unpaid status
    ];

    // Create invoice
    $invoice_id = $CI->invoices_model->add($invoice_data);

    if ($invoice_id) {
        log_activity('FlexAcademy Invoice Created [Invoice ID: ' . $invoice_id . ', Course: ' . $course->title . ', Client: ' . $client_id . ']');
    }

    return $invoice_id;
}

/**
 * Create invoice for multiple courses (bulk enrollment/cart)
 * @param int $client_id Client ID
 * @param array $courses Array of courses with course objects
 * @return int|false Invoice ID or false on failure
 */
function flexacademy_create_bulk_invoice($client_id, $courses)
{
    $CI = &get_instance();
    
    if (!class_exists('Invoices_model', false)) {
        $CI->load->model('invoices_model');
    }
    
    if (!class_exists('Payment_modes_model', false)) {
        $CI->load->model('payment_modes_model');
    }

    if (!class_exists('Currencies_model', false)) {
        $CI->load->model('currencies_model');
    }

    // Get payment modes
    $payment_modes = $CI->payment_modes_model->get();
    $allowed_payment_modes = array_pluck($payment_modes, 'id');

    // Get currency
    $currency = $CI->currencies_model->get_base_currency();

    // Prepare invoice items
    $items = [];
    $total_amount = 0;
    $order = 1;

    foreach ($courses as $course) {
        $price = $course->discount_price > 0 ? $course->discount_price : $course->price;
        
        // Include ALL courses (even free ones with $0)
        $items[] = [
            'description' => $course->title,
            'long_description' => strip_tags($course->short_description ?? $course->description ?? ''),
            'qty' => 1,
            'rate' => $price,
            'order' => $order,
            'taxname' => [],
            'unit' => ''
        ];
        
        $total_amount += $price;
        $order++;
    }

    // Return false if no items
    if (empty($items)) {
        return false;
    }

    // Calculate due date
    $due_after = get_option('invoice_due_after') > 0 ? get_option('invoice_due_after') : 30;

    // Get client billing information
    $CI->load->model('clients_model');
    $client = $CI->clients_model->get($client_id);
    
    $billing_street = isset($client->address) ? $client->address : '';
    $billing_city = isset($client->city) ? $client->city : '';
    $billing_state = isset($client->state) ? $client->state : '';
    $billing_zip = isset($client->zip) ? $client->zip : '';
    $billing_country = isset($client->country) ? $client->country : '';

    // Prepare invoice data
    $invoice_data = [
        'clientid' => $client_id,
        'number' => get_option('next_invoice_number'),
        'number_format' => get_option('invoice_number_format'),
        'date' => date('Y-m-d'),
        'duedate' => date('Y-m-d', strtotime('+' . $due_after . ' DAY')),
        'currency' => $currency->id,
        'newitems' => $items,
        'subtotal' => $total_amount,
        'total' => $total_amount,
        'adjustment' => 0,
        'discount_percent' => 0,
        'discount_total' => 0,
        'discount_type' => '',
        'sale_agent' => 0,
        'billing_street' => $billing_street,
        'billing_city' => $billing_city,
        'billing_state' => $billing_state,
        'billing_zip' => $billing_zip,
        'billing_country' => $billing_country,
        'shipping_street' => '',
        'shipping_city' => '',
        'shipping_state' => '',
        'shipping_zip' => '',
        'shipping_country' => '',
        'include_shipping' => 0,
        'show_shipping_on_invoice' => 0,
        'show_quantity_as' => 1,
        'clientnote' => _flexacademy_lang('invoice-note-for-courses'),
        'adminnote' => 'FlexAcademy Bulk Enrollment - ' . count($courses) . ' course(s)',
        'terms' => get_option('predefined_terms_invoice'),
        'allowed_payment_modes' => $allowed_payment_modes,
        'status' => 1, // Unpaid status
    ];

    // Create invoice
    $invoice_id = $CI->invoices_model->add($invoice_data);

    if ($invoice_id) {
        log_activity('FlexAcademy Bulk Invoice Created [Invoice ID: ' . $invoice_id . ', Courses: ' . count($courses) . ', Client: ' . $client_id . ']');
    }

    return $invoice_id;
}

/**
 * Check if course requires payment
 * @param object $course Course object
 * @return bool
 */
function flexacademy_course_requires_payment($course)
{
    if ($course->pricing_type === 'free') {
        return false;
    }

    $price = $course->discount_price > 0 ? $course->discount_price : $course->price;
    
    return $price > 0;
}

/**
 * Mark enrollment as paid
 * @param int $enrollment_id Enrollment ID
 * @param float $amount Amount paid
 * @return bool
 */
function flexacademy_mark_enrollment_paid($enrollment_id, $amount = null)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_enrollments_model');

    $enrollment = $CI->flexacademy_enrollments_model->get($enrollment_id);
    
    if (!$enrollment) {
        return false;
    }

    $update_data = [
        'payment_status' => 'paid',
        'payment_date' => date('Y-m-d H:i:s')
    ];

    if ($amount !== null) {
        $update_data['amount_paid'] = $amount;
    }

    return $CI->flexacademy_enrollments_model->update($enrollment_id, $update_data);
}

/**
 * Get enrollment by invoice ID
 * @param int $invoice_id Invoice ID
 * @return object|null
 */
function flexacademy_get_enrollment_by_invoice($invoice_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_enrollments_model');

    $CI->db->where('invoice_id', $invoice_id);
    return $CI->db->get(db_prefix() . 'flexacademy_enrollments')->row();
}

/**
 * Get all enrollments by invoice ID (for bulk enrollments)
 * @param int $invoice_id Invoice ID
 * @return array
 */
function flexacademy_get_enrollments_by_invoice($invoice_id)
{
    $CI = &get_instance();
    
    $CI->db->where('invoice_id', $invoice_id);
    return $CI->db->get(db_prefix() . 'flexacademy_enrollments')->result_array();
}


/**
 * Send payment received email
 * @param object $invoice
 * @param array $enrollments
 * @param array $course_names
 * @param string $customer_email
 * @param string $customer_name
 * @return bool
 */
function flexacademy_send_payment_received_email($invoice, $enrollments, $course_names, $customer_email, $customer_name)
{
    // Build courses list HTML
    $courses_list_html = '<ul>';
    foreach ($course_names as $course_name) {
        $courses_list_html .= '<li>' . htmlspecialchars($course_name) . '</li>';
    }
    $courses_list_html .= '</ul>';
    
    $payment_data = [
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'invoice_number' => format_invoice_number($invoice->id),
        'amount_paid' => app_format_money($invoice->total, $invoice->currency_name),
        'payment_date' => date('M d, Y', strtotime($invoice->date)),
        'enrolled_courses_list' => $courses_list_html,
        'my_courses_url' => site_url('flexacademy/my-courses'),
        'course_name' => !empty($course_names) ? $course_names[0] : 'Your Courses',
        'invoice_id' => $invoice->id
    ];
    
    return flexacademy_send_email($payment_data, 'payment_received');
}

// ========================================
// ORDER MANAGEMENT FUNCTIONS (HOOKS)
// ========================================

/**
 * Get order by ID
 * @param int $order_id
 * @return array|null
 */
function flexacademy_get_order($order_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    
    return $CI->flexacademy_orders_model->get(['id' => $order_id]);
}

/**
 * Get order by invoice ID
 * @param int $invoice_id
 * @return array|null
 */
function flexacademy_get_order_by_invoice($invoice_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    
    return $CI->flexacademy_orders_model->get_by_invoice($invoice_id);
}

function flexacademy_calculate_enrollment_expiry($course)
{

    $expiryType = $course['expiry_type'] ?? 'never';
    $expiryPeriod = (int) ($course['expiry_period'] ?? 0);

    if ($expiryType === 'never' || $expiryPeriod <= 0) {
        return null;
    }
    return date('Y-m-d H:i:s', strtotime('+' . $expiryPeriod . $expiryType));
}

/**
 * Process order completion after payment
 * @param int $order_id
 * @return bool
 */
function flexacademy_complete_order($order_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    $CI->load->model('flexacademy/flexacademy_enrollments_model');
    $CI->load->model('flexacademy/Flexacademy_courses_model');
    
    $order = $CI->flexacademy_orders_model->get(['id' => $order_id]);
    
    if (!$order) {
        return false;
    }
    
    // Decode order items
    $order_items = json_decode($order['order_items'], true);
    
    if (empty($order_items)) {
        return false;
    }
    
    // Create enrollments for all courses in the order
    foreach ($order_items as $item) {
        // Check if already enrolled
        $existing_enrollment = $CI->flexacademy_enrollments_model->get_by_course_student(
            $item['course_id'],
            $order['contact_id']
        );
        
        if (!$existing_enrollment) {
            $enrollment_data = [
                'course_id' => $item['course_id'],
                'student_id' => $order['contact_id'],
                'status' => 'enrolled',
                'invoice_id' => $order['invoice_id'],
                'payment_status' => 'paid',
                'amount_paid' => $item['price'],
                'payment_date' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $course = $CI->Flexacademy_courses_model->get(['id' => $item['course_id']]);
            $expires_at = flexacademy_calculate_enrollment_expiry($course);

            if ($expires_at) {
                $enrollment_data['expires_at'] = $expires_at;
            }
            
            // Insert enrollment directly
            $CI->db->insert(db_prefix() . 'flexacademy_enrollments', $enrollment_data);
            $enrollment_id = $CI->db->insert_id();
            
            if ($enrollment_id) {
                // Send enrollment confirmation email
                flexacademy_send_enrollment_email($enrollment_id);
            }
        }
    }
    
    // Update order status to completed
    $CI->flexacademy_orders_model->update_status($order_id, 'completed');
    
    log_activity('FlexAcademy Order Completed [Order ID: ' . $order_id . ', Order #' . $order['order_number'] . ']');
    
    return true;
}

/**
 * Convert duration from minutes to formatted string
 * @param int $duration Duration in minutes
 * @return string Formatted duration
 */
function flexacademy_convert_duration_from_minutes($duration)
{
    if (!is_numeric($duration)) {
        return $duration;
    }
    if ($duration < 0) {
        return 0;
    }
    $duration = (int)$duration;
    $hours = floor($duration / 60);
    $minutes = $duration % 60;
    return sprintf("%02d%s %02d%s", $hours, _flexacademy_lang('h'), $minutes, _flexacademy_lang('m'));
}

/**
 * Get course sections with their lessons organized
 * @param int $course_id
 * @return array
 */
function flexacademy_get_course_sections_with_lessons($course_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_sections_model');
    $CI->load->model('flexacademy/Flexacademy_lessons_model');
    
    $sections = $CI->Flexacademy_sections_model->all(['course_id' => $course_id]);
    if (empty($sections)) {
        return [];
    }

    $lessons = $CI->Flexacademy_lessons_model->get_course_lessons($course_id);
    $sectionsIndexed = [];

    foreach ($sections as $section) {
        $section['lessons'] = [];
        $sectionsIndexed[$section['id']] = $section;
    }

    $orphanLessons = [];

    foreach ($lessons as $lesson) {
        $sectionId = isset($lesson['section_id']) ? (int) $lesson['section_id'] : null;

        if ($sectionId && isset($sectionsIndexed[$sectionId])) {
            $sectionsIndexed[$sectionId]['lessons'][] = $lesson;
        } else {
            $orphanLessons[] = $lesson;
        }
    }

    if (!empty($orphanLessons)) {
        $sectionsIndexed[0] = [
            'id' => 0,
            'course_id' => $course_id,
            'title' => 'General',
            'description' => '',
            'sort_order' => 9999,
            'lessons' => $orphanLessons,
        ];
    }

    return array_values($sectionsIndexed);
}

/**
 * Get total lesson count for all sections
 * @param array $sections
 * @return int
 */
function flexacademy_get_course_total_lessons($course_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_lessons_model');

    return (int) $CI->Flexacademy_lessons_model->get_total_lessons($course_id);
}

function flexacademy_get_course_total_duration($course_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_lessons_model');

    return $CI->Flexacademy_lessons_model->get_total_duration($course_id);
}

function flexacademy_get_course_lessons($course_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_lessons_model');

    return $CI->Flexacademy_lessons_model->get_course_lessons($course_id);
}


