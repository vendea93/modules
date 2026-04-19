<?php
defined('BASEPATH') or exit('No direct script access allowed');

function flexacademy_lesson_type_icon($lesson){
    $lesson_type = $lesson['lesson_type'];
    $file_source = $lesson['file_source'];

    if($lesson_type == 'file'){
        switch($file_source){
            case 'youtube': return 'fa-brands fa-square-youtube';
            case 'vimeo': return 'fa-brands fa-square-vimeo';
            case 'external-link': return 'fa-external-link-alt';
            case 'iframe': return 'fa-code';
            default: return 'fa-file';
        }
    }elseif($lesson_type == 'quiz'){
        return 'fa-chart-line';
    }elseif($lesson_type == 'text'){
        return 'fa-solid fa-file-text';
    }
    return 'fa-file';
}

function flexacademy_instructor_image($instructor){
    if(!$instructor) return "";
    $image = $instructor['image'];
    if($image == ""){
        return base_url('assets/images/user-placeholder.jpg');
    }
    return flexacademy_media_url($image);
}

function flexacademy_instructor_signature($instructor){
    if (empty($instructor['signature'])) {
        return '';
    }
    return flexacademy_media_url($instructor['signature']);
}

function flexacademyPerfectSerialize($string)
{
    return base64_encode(serialize($string));
}

function flexacademyPerfectUnserialize($string)
{
    if (base64_decode($string, true) == true) {
        return @unserialize(base64_decode($string));
    } else {
        return @unserialize($string);
    }
}

function flexacademy_get_by_key($array, $key = null) {
    if (is_array($array) == false || !isset($key)) {
        return $array;
    }
    $filteredArray = array_filter($array, function($item) use ($key){
            return $item["id"] == $key;
    });
    return array_shift($filteredArray)["name"] ?? "";
}

function flexacademy_quiz_question_types(){
    return [
        ['id' => 'single', 'name' => _flexacademy_lang('single-choice')],
        ['id' => 'multiple', 'name' => _flexacademy_lang('multiple-choice')],
        ['id' => 'fill-in-the-blank', 'name' => _flexacademy_lang('fill-in-the-blank')],
        ['id' => 'true-false', 'name' => _flexacademy_lang('true-false')],
    ];
}

function flexacademy_get_quiz_by_id($quiz_id){
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_quiz_model');
    $quiz = $CI->Flexacademy_quiz_model->get(['id' => $quiz_id]);
    return $quiz;
}

function flexacademy_file_types(){
    return [
        ['id' => 'image', 'name' => _flexacademy_lang('image')],
        ['id' => 'video', 'name' => _flexacademy_lang('video')],
        ['id' => 'audio', 'name' => _flexacademy_lang('audio')],
        ['id' => 'pdf', 'name' => _flexacademy_lang('pdf')],
        ['id' => 'docx', 'name' => _flexacademy_lang('word')],
        ['id' => 'xlsx', 'name' => _flexacademy_lang('excel')],
        ['id' => 'pptx', 'name' => _flexacademy_lang('powerpoint')],
        ['id' => 'txt', 'name' => _flexacademy_lang('text')],
        ['id' => 'csv', 'name' => _flexacademy_lang('csv')],
        ['id' => 'zip', 'name' => _flexacademy_lang('zip')],
        ['id' => 'other', 'name' => _flexacademy_lang('other')],
    ];
}
function flexacademy_get_file_sources($key = null){
    $arr =  [
        ['id' => 'upload-file', 'name' => _flexacademy_lang('upload-file')],
        ['id' => 'youtube', 'name' => _flexacademy_lang('youtube')],
        ['id' => 'vimeo', 'name' => _flexacademy_lang('vimeo')],
        ['id' => 'external-link', 'name' => _flexacademy_lang('external-link')],
        ['id' => 'iframe', 'name' => _flexacademy_lang('iframe-embed')],
    ];
    return flexacademy_get_by_key($arr, $key);
}

function flexacademy_get_lesson_types(){
    return [
        ['id' => 'file', 'name' => _flexacademy_lang('file')],
        ['id' => 'text', 'name' => _flexacademy_lang('text-lesson')],
    ];
}

function flexacademy_course_details_menu($course_id)
{
    $base_url = admin_url('flexacademy/course_details/' . $course_id);
    return [
        [
            'name' => _flexacademy_lang('curriculum'),
            'href' => $base_url . '?key=curriculum',
            'icon' => 'fa-solid fa-pen-to-square',
            'key' => 'curriculum',
        ],
        [
            'name' => _flexacademy_lang('basic'),
            'href' => $base_url . '?key=basic',
            'icon' => 'fa-solid fa-gear',
            'key' => 'basic',
        ],
        [
            'name' => _flexacademy_lang('instructors'),
            'href' => $base_url . '?key=instructors',
            'icon' => 'fa-solid fa-users',
            'key' => 'instructors',
        ],
        [
            'name' => _flexacademy_lang('info'),
            'href' => $base_url . '?key=info',
            'icon' =>   'fa-solid fa-book',
            'key' => 'info',
        ],
    ];
}

function flexacademy_client_course_details_menu()
{

    return [
        [
            'name' => _flexacademy_lang('curriculum'),
            'icon' => 'fa-solid fa-pen-to-square',
            'key' => 'curriculum',
        ],
      
        [
            'name' => _flexacademy_lang('instructors'),
            'icon' => 'fa-solid fa-users',
            'key' => 'instructors',
        ],
        [
            'name' => _flexacademy_lang('faqs'),
            'icon' => 'fa-solid fa-question-circle',
            'key' => 'faqs',
        ],
        [
            'name' => _flexacademy_lang('info'),
            'icon' =>  'fa-solid fa-book',
            'key' => 'info',
        ],
    ];
}

function flexacademy_expiry_types($key = null){
    $types =  [
        ['id' => 'never', 'name' => _flexacademy_lang('never')],
        ['id' => 'days', 'name' => _flexacademy_lang('days')],
        ['id' => 'weeks', 'name' => _flexacademy_lang('weeks')],
        ['id' => 'months', 'name' => _flexacademy_lang('months')],
    ];
  return flexacademy_get_by_key($types, $key);
}


function flexacademy_is_enrollment_expired($expires_at)
{
    if (empty($expires_at)) {
        return false;
    }

    $expires_at = strtotime($expires_at);
    return $expires_at < time();
}


function flexacademy_build_enrollment_data($course, $student, $options = []) {
    $now = date('Y-m-d H:i:s');

        $status = isset($options['status']) ? $options['status'] : ($student['type'] === 'client' ? 'enrolled' : 'active');
        $payment_status = isset($options['payment_status']) ? $options['payment_status'] : 'paid';
        $amount_paid = isset($options['amount_paid']) ? (float) $options['amount_paid'] : 0.00;
        $invoice_id = isset($options['invoice_id']) ? $options['invoice_id'] : null;

        $data = [
            'course_id'      => $course['id'],
            'student_id'     => $student['id'],
            'student_type'   => $student['type'],
            'enrolled_at'    => $now,
            'enrollment_date'=> $now,
            'status'         => $status,
            'progress'       => 0.00,
            'payment_status' => $payment_status,
            'amount_paid'    => $amount_paid,
            'invoice_id'     => $invoice_id,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        if (isset($options['additional'])) {
            $data = array_merge($data, $options['additional']);
        }

        if (!isset($data['expires_at'])) {
      
            $expires_at = flexacademy_calculate_enrollment_expiry($course);

            if ($expires_at) {
                $data['expires_at'] = $expires_at;
            }
        }

        return $data;
}


function flexacademy_get_course_pricing_types(){
    return [
        ['id' => 'free', 'name' => _flexacademy_lang('free')],
        ['id' => 'paid', 'name' => _flexacademy_lang('paid')],
    ];
}

function flexacademy_get_course_privacy(){
    return [
        ['id' => 'public', 'name' => _flexacademy_lang('public')],
        ['id' => 'private', 'name' => _flexacademy_lang('private')],
    ];
}

function flexacademy_get_course_access_types(){
    return [
        ['id' => 'everyone', 'name' => _flexacademy_lang('everyone')],
        ['id' => 'clients', 'name' => _flexacademy_lang('clients-only')],
        ['id' => 'staffs', 'name' => _flexacademy_lang('staffs-only')],
    ];
}

function flexacademy_get_course_statuses(){
    return [
        ['id' => 'active', 'name' => _flexacademy_lang('active')],
        ['id' => 'inactive', 'name' => _flexacademy_lang('inactive')],
        ['id' => 'draft', 'name' => _flexacademy_lang('draft')],
        ['id' => 'archived', 'name' => _flexacademy_lang('archived')],
        ['id' => 'pending', 'name' => _flexacademy_lang('pending')],
    ];
}

function flexacademy_get_course_languages($key = null){
  
    $languages = [
        ['id' => '', 'name' => _flexacademy_lang('select-language')],
        ['id' => 'en', 'name' => 'English'],
        ['id' => 'es', 'name' => 'Spanish'],
        ['id' => 'fr', 'name' => 'French'],
        ['id' => 'de', 'name' => 'German'],
        ['id' => 'it', 'name' => 'Italian'],
        ['id' => 'pt', 'name' => 'Portuguese'],
        ['id' => 'ru', 'name' => 'Russian'],
        ['id' => 'zh', 'name' => 'Chinese'],
        ['id' => 'ja', 'name' => 'Japanese'],
        ['id' => 'ko', 'name' => 'Korean'],
        ['id' => 'ar', 'name' => 'Arabic'],
        ['id' => 'hi', 'name' => 'Hindi'],
        ['id' => 'bn', 'name' => 'Bengali'],
        ['id' => 'pa', 'name' => 'Punjabi'],
        ['id' => 'ta', 'name' => 'Tamil'],
        ['id' => 'te', 'name' => 'Telugu'],
        ['id' => 'th', 'name' => 'Thai'],
        ['id' => 'tr', 'name' => 'Turkish'],
        ['id' => 'uk', 'name' => 'Ukrainian'],
        ['id' => 'vi', 'name' => 'Vietnamese'],
        ['id' => 'nl', 'name' => 'Dutch'],
        ['id' => 'pl', 'name' => 'Polish'],
        ['id' => 'ro', 'name' => 'Romanian'],
        ['id' => 'sk', 'name' => 'Slovak'],
        ['id' => 'sl', 'name' => 'Slovenian'],
    ];
    return flexacademy_get_by_key($languages, $key);
}

function flexacademy_get_course_levels($key = null){
    $levels = [
        ['id' => '', 'name' => _flexacademy_lang('select-level')],
        ['id' => 'beginner', 'name' => _flexacademy_lang('beginner')],
        ['id' => 'intermediate', 'name' => _flexacademy_lang('intermediate')],
        ['id' => 'advanced', 'name' => _flexacademy_lang('advanced')],
    ];
    return flexacademy_get_by_key($levels, $key);
}

function flexacademy_get_category_name($category_id) {
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_categories_model');
    $category = $CI->Flexacademy_categories_model->get(['id' => $category_id]);
    return $category['title'] ?? _flexacademy_lang('no-parent');
}

function flexacademy_get_parent_categories(){
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_categories_model');
    $categories = $CI->Flexacademy_categories_model->all(['parent_id' => 0]);
    return $categories;
}

function flexacademy_get_sub_categories($parent_id){
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_categories_model');
    $categories = $CI->Flexacademy_categories_model->all(['parent_id' => $parent_id]);
    return $categories;
}

function flexacademy_get_lessons_duration($lessons) {
    return array_reduce($lessons, function($duration, $lesson) {
    return $duration + $lesson['duration'];
}, 0);
}

function get_media_icon_php($type) {
    switch($type) {
        case 'image': return 'image';
        case 'video': return 'video';
        case 'document': return 'file-alt';
        case 'audio': return 'music';
        default: return 'file';
    }
}

function format_file_size_php($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Get course requirements from course data
 * @param array $course
 * @return array
 */
function flexacademy_get_course_requirements($course) {
    $requirements = [];
    
    if (isset($course['requirements']) && !empty($course['requirements'])) {
        if (is_string($course['requirements'])) {
            // First try to unserialize the data
            $unserialized = flexacademyPerfectUnserialize($course['requirements']);
            if (is_array($unserialized)) {
                $requirements = $unserialized;
            } else {
                // Try to decode as JSON
                $decoded = json_decode($course['requirements'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $requirements = $decoded;
                } else {
                    // If not JSON, split by newlines or commas
                    $requirements = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $course['requirements'])));
                }
            }
        } elseif (is_array($course['requirements'])) {
            $requirements = $course['requirements'];
        }
    }
    
    return $requirements;
}

/**
 * Get course outcomes from course data
 * @param array $course
 * @return array
 */
function flexacademy_get_course_outcomes($course) {
    $outcomes = [];
    
    if (isset($course['outcomes']) && !empty($course['outcomes'])) {
        if (is_string($course['outcomes'])) {
            // First try to unserialize the data
            $unserialized = flexacademyPerfectUnserialize($course['outcomes']);
            if (is_array($unserialized)) {
                $outcomes = $unserialized;
            } else {
                // Try to decode as JSON
                $decoded = json_decode($course['outcomes'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $outcomes = $decoded;
                } else {
                    // If not JSON, split by newlines or commas
                    $outcomes = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $course['outcomes'])));
                }
            }
        } elseif (is_array($course['outcomes'])) {
            $outcomes = $course['outcomes'];
        }
    }
    
    return $outcomes;
}

/**
 * Get course FAQs from course data
 * @param array $course
 * @return array
 */
function flexacademy_get_course_faqs($course) {
    $faqs = [];
    
    if (isset($course['faq']) && !empty($course['faq'])) {
        if (is_string($course['faq'])) {
            // Unserialize the FAQ data
            $unserialized = flexacademyPerfectUnserialize($course['faq']);
            if (is_array($unserialized)) {
                $questions = $unserialized['question'] ?? [];
                $answers = $unserialized['answer'] ?? [];
                
                // Combine questions and answers into a structured array
                for ($i = 0; $i < count($questions); $i++) {
                    if (!empty($questions[$i])) {
                        $faqs[] = [
                            'question' => $questions[$i],
                            'answer' => $answers[$i] ?? ''
                        ];
                    }
                }
            }
        } elseif (is_array($course['faq'])) {
            $faqs = $course['faq'];
        }
    }
    
    return $faqs;
}

function flexacademy_add_to_cart($course_id, $course_data)
{
    if (!isset($_SESSION['flexacademy_cart'])) {
        $_SESSION['flexacademy_cart'] = [];
    }

    if (!isset($_SESSION['flexacademy_cart'][$course_id])) {
        $_SESSION['flexacademy_cart'][$course_id] = [
            'course_id' => $course_id,
            'title' => $course_data['title'],
            'description' => $course_data['description'] ?? '',
            'price' => $course_data['price'],
            'discount_price' => $course_data['discount_price'],
            'image' => $course_data['image'],
            'slug' => $course_data['slug'],
            'added_at' => date('Y-m-d H:i:s')
        ];
        return true;
    }
    return false;
}

function flexacademy_get_cart_items()
{
    if (!isset($_SESSION['flexacademy_cart'])) {
        return [];
    }
    return $_SESSION['flexacademy_cart'];
}

function flexacademy_remove_from_cart($course_id)
{
    if (isset($_SESSION['flexacademy_cart'][$course_id])) {
        unset($_SESSION['flexacademy_cart'][$course_id]);
        return true;
    }
    return false;
}

function flexacademy_clear_cart()
{
    $_SESSION['flexacademy_cart'] = [];
    return true;
}

function flexacademy_get_cart_count()
{
    if (!isset($_SESSION['flexacademy_cart'])) {
        return 0;
    }
    return count($_SESSION['flexacademy_cart']);
}

function flexacademy_get_cart_total()
{
    if (!isset($_SESSION['flexacademy_cart'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['flexacademy_cart'] as $item) {
        $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
        $total += $price;
    }
    return $total;
}

function flexacademy_is_course_in_cart($course_id)
{
    if (!isset($_SESSION['flexacademy_cart'])) {
        return false;
    }
    return isset($_SESSION['flexacademy_cart'][$course_id]);
}


/**
 * Truncate text to a specified length
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append (default: '...')
 * @param bool $strip_html Whether to strip HTML tags (default: true)
 * @return string Truncated text
 */
function flexacademy_truncate($text, $length = 100, $suffix = '...', $strip_html = true) {
    if (empty($text)) {
        return '';
    }
    
    // Strip HTML tags if requested
    if ($strip_html) {
        $text = strip_tags($text);
    }
    
    // If text is shorter than limit, return as is
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    // Truncate to length
    $truncated = mb_substr($text, 0, $length);
    
    // Try to break at last space to avoid cutting words
    $last_space = mb_strrpos($truncated, ' ');
    if ($last_space !== false && $last_space > ($length * 0.7)) {
        $truncated = mb_substr($truncated, 0, $last_space);
    }
    
    return trim($truncated) . $suffix;
}

/**
 * Get the effective price of a course
 * Returns discount price if available, otherwise regular price
 * @param array $course
 * @return float
 */
function flexacademy_get_course_price($course)
{
    if (!is_array($course)) {
        $course = (array) $course;
    }
    
    $discount_price = isset($course['discount_price']) ? (float)$course['discount_price'] : 0;
    $regular_price = isset($course['price']) ? (float)$course['price'] : 0;
    
    // Return discount price if it's greater than 0, otherwise return regular price
    return $discount_price > 0 ? $discount_price : $regular_price;
}

// ========================================
// ORDER MANAGEMENT FUNCTIONS
// ========================================

/**
 * Create order from cart
 * @param int $client_id
 * @param int $contact_id
 * @param array $cart_items
 * @return int|bool Order ID or false
 */
function flexacademy_create_order($client_id, $contact_id, $cart_items)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    $CI->load->model('flexacademy/flexacademy_courses_model');
    
    if (empty($cart_items)) {
        return false;
    }
    
    $total_amount = 0;
    $order_items = [];
    
    // Build order items
    foreach ($cart_items as $course_id => $item) {
        $course = $CI->flexacademy_courses_model->get(['id' => $course_id]);
        
        if (!$course) {
            continue;
        }
        
        $course_price = flexacademy_get_course_price($course);
        $total_amount += $course_price;
        
        $order_items[] = [
            'course_id' => $course['id'],
            'course_title' => $course['title'],
            'course_slug' => $course['slug'],
            'price' => $course_price,
            'pricing_type' => $course['pricing_type']
        ];
    }
    
    // Get currency
    $currency = get_base_currency()->name;
    
    $order_data = [
        'client_id' => $client_id,
        'contact_id' => $contact_id,
        'total_amount' => $total_amount,
        'currency' => $currency,
        'status' => 'pending',
        'order_items' => json_encode($order_items)
    ];
    
    $order_id = $CI->flexacademy_orders_model->add($order_data);
    
    if ($order_id) {
        log_activity('FlexAcademy Order Created for Client [Client ID: ' . $client_id . ', Order ID: ' . $order_id . ']');
    }
    
    return $order_id;
}

/**
 * Create invoice from order
 * @param int $order_id
 * @return int|bool Invoice ID or false
 */
function flexacademy_create_invoice_from_order($order_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    $CI->load->model('invoices_model');
    
    $order = $CI->flexacademy_orders_model->get(['id' => $order_id]);
    
    if (!$order) {
        return false;
    }
    
    // Decode order items
    $order_items = json_decode($order['order_items'], true);
    
    if (empty($order_items)) {
        return false;
    }
    
    // Get currency ID
    $CI->load->model('currencies_model');
    $currency_id = $CI->currencies_model->get_base_currency()->id;
    
    // Get available payment modes
    $CI->load->model('payment_modes_model');
    $payment_modes = $CI->payment_modes_model->get('', [
        'active' => 1
    ]);
    $allowed_payment_modes = [];
    foreach ($payment_modes as $mode) {
        $allowed_payment_modes[] = $mode['id'];
    }
    
    // Prepare invoice data with all required fields
    $invoice_data = [
        'clientid' => $order['client_id'],
        'number' => get_option('next_invoice_number'),
        'number_format' => get_option('invoice_number_format'),
        'date' => date('Y-m-d'),
        'duedate' => date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' days')),
        'currency' => $currency_id,
        'subtotal' => $order['total_amount'],
        'total' => $order['total_amount'],
        'adminnote' => 'Generated from FlexAcademy Order #' . $order['order_number'],
        'allowed_payment_modes' => $allowed_payment_modes,
        'billing_street' => '',
        'billing_city' => '',
        'billing_state' => '',
        'billing_zip' => '',
        'billing_country' => '',
        'clientnote' => ''
    ];
    
    // Add line items with order field
    $newitems = [];
    $item_order = 1;
    foreach ($order_items as $item) {
        $newitems[] = [
            'description' => 'Course: ' . $item['course_title'],
            'long_description' => 'Course enrollment - ' . $item['course_title'],
            'qty' => 1,
            'rate' => $item['price'],
            'order' => $item_order++,
            'taxname' => [],
            'unit' => ''
        ];
    }
    
    $invoice_data['newitems'] = $newitems;
    
    $invoice_id = $CI->invoices_model->add($invoice_data);
    
    if ($invoice_id) {
        // Link invoice to order
        $CI->flexacademy_orders_model->link_invoice($order_id, $invoice_id);
        
        // Update order status to processing
        $CI->flexacademy_orders_model->update_status($order_id, 'processing');
        
        log_activity('FlexAcademy Invoice Created from Order [Order #' . $order['order_number'] . ', Invoice ID: ' . $invoice_id . ']');
    }
    
    return $invoice_id;
}

/**
 * Cancel order
 * @param int $order_id
 * @return bool
 */
function flexacademy_cancel_order($order_id)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/flexacademy_orders_model');
    
    return $CI->flexacademy_orders_model->update_status($order_id, 'cancelled');
}

//get certificate url by enrollment id
function flexacademy_get_certificate_url($certificate_id, $admin = true)
{
    $CI = &get_instance();
    $CI->load->model('flexacademy/Flexacademy_certificates_model');
    $certificate = $CI->Flexacademy_certificates_model->get($certificate_id);
    if (!$certificate) {
        return '';
    }
    if ($admin) {
        return admin_url('flexacademy/certificate/' . $certificate->certificate_number);
    }
    return site_url('flexacademy/certificate/' . $certificate->certificate_number);
}