<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clientflexacademy extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flexacademy/Flexacademy_courses_model');
        $this->load->model('flexacademy/Flexacademy_categories_model');
        $this->load->model('flexacademy/Flexacademy_enrollments_model');
        $this->load->model('flexacademy/Flexacademy_instructors_model');
        $this->load->model('flexacademy/Flexacademy_lessons_model');
        $this->load->model('flexacademy/Flexacademy_lesson_progress_model');
        $this->load->model('flexacademy/Flexacademy_certificates_model');
        $this->load->model('flexacademy/Flexacademy_quiz_model');
        $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');
        $this->load->model('flexacademy/Flexacademy_quiz_questions_model');
        $this->load->model('staff_model');
        $this->load->helper('flexacademy/flexacademy');
    }
    public function index()
    {


        // Get filter parameters
        $category_id = $this->input->get('category');
        $pricing_type = $this->input->get('pricing');
        $search = $this->input->get('search');
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
        $per_page = 6;
        $offset = ($page - 1) * $per_page;

        $filters = [
            'category_id' => $category_id,
            'pricing'     => $pricing_type,
            'search'      => $search,
            'per_page'    => $per_page,
            'offset'      => $offset,
        ];

        $listing = $this->Flexacademy_courses_model->get_client_course_listing($filters);
        $total_courses = $listing['total'];
        $total_pages = $total_courses > 0 ? ceil($total_courses / $per_page) : 1;
        $courses = $listing['courses'];

        // Enrich courses with additional data
        foreach ($courses as &$course) {
            $course['total_duration'] = flexacademy_get_course_total_duration($course['id']);
            $course['total_lessons'] = flexacademy_get_course_total_lessons($course['id']);
            $course['total_students'] = $this->Flexacademy_enrollments_model->get_course_enrollment_count($course['id']);
            $course['average_rating'] = 0; // TODO: Implement reviews/ratings system
            $course['total_reviews'] = 0; // TODO: Implement reviews/ratings system
            
            // Check if current user is enrolled and get progress
            $course['is_enrolled'] = false;
            $course['enrollment_progress'] = 0;
            if (is_client_logged_in()) {
                $student_id = get_contact_user_id();
                $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course['id'], $student_id);
                if ($enrollment) {
                    $course['is_enrolled'] = true;
                    $course['enrollment_progress'] = isset($enrollment->progress) ? $enrollment->progress : 0;
                }
            }
        }

        // Get all categories
        $categories = $this->Flexacademy_categories_model->all(['status' => 'active']);

        $this->data([
            'title' => _flexacademy_lang('all-courses'),
            'courses' => $courses,
            'categories' => $categories,
            'current_category' => $category_id,
            'current_pricing' => $pricing_type,
            'current_search' => $search,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_courses' => $total_courses,
            'currency' => $this->currencies_model->get_base_currency()
        ]);

        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-html2pdf', module_dir_url('flexacademy','assets/js/html2pdf.bundle.min.js'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));

        $this->view('client/courses/index');
        $this->layout();
    }

    public function details($slug)
    {
    
        $course = $this->Flexacademy_courses_model->get(['slug' => $slug]);
        if (!$course) {
            show_404();
        }
    
        $instructors = $this->Flexacademy_instructors_model->all(['course_id' => $course['id']]);
        
        $sections = flexacademy_get_course_sections_with_lessons($course['id']);
        $lessons = flexacademy_get_course_lessons($course['id']);
        $totalDuration = flexacademy_get_course_total_duration($course['id']);
        $totalLessons = flexacademy_get_course_total_lessons($course['id']);
        
        $requirements = flexacademy_get_course_requirements($course);
        $outcomes = flexacademy_get_course_outcomes($course);
        $faqs = flexacademy_get_course_faqs($course);
        
        $is_in_cart = flexacademy_is_course_in_cart($course['id']);
        
        // Get enrollment count for this course
        $enrollment_count = $this->Flexacademy_enrollments_model->get_course_enrollment_count($course['id']);
        
        // Check if user is enrolled and get enrollment data
        $enrollment = null;
        if (is_client_logged_in()) {
            $student_id = get_contact_user_id();
            $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course['id'], $student_id);
        }
        
        $this->data([
            'title' => $course['title'],
            'totalDuration' => $totalDuration,
            'totalLessons' => $totalLessons,
            'enrollment_count' => $enrollment_count,
            'requirements' => $requirements,
            'outcomes' => $outcomes,
            'faqs' => $faqs,
            'is_in_cart' => $is_in_cart,
            'enrollment' => $enrollment,
            'currency' => $this->currencies_model->get_base_currency(),
            'instructors' => $instructors,
            'course' => $course,
            'lessons' => $lessons,
            'sections' => $sections
        ]);
        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));
        $this->view('client/details/index');
        $this->layout();
    }

    public function ajax()
    {
        $action = $this->input->post('action');


        switch ($action) {
            case 'add_to_cart':
        $course_id = $this->input->post('course_id');
        if (!$course_id) {
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-id-required')]);
                    die();
        }

    
        $course = $this->Flexacademy_courses_model->get(['id' => $course_id]);
        if (!$course) {
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-not-found')]);
                    die();
        }

        if (flexacademy_is_course_in_cart($course_id)) {
            $cart_count = flexacademy_get_cart_count();
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-already-in-cart'), 'already_in_cart' => true, 'cart_count' => $cart_count]);
                    die();
        }

        $course_data = [
            'title' => $course['title'],
            'description' => $course['description'],
            'price' => $course['price'],
            'discount_price' => $course['discount_price'],
            'image' => $course['image'],
            'slug' => $course['slug']
        ];

        if (flexacademy_add_to_cart($course_id, $course_data)) {
            $cart_count = flexacademy_get_cart_count();
            echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('course-added-to-cart-success'), 'cart_count' => $cart_count]);
        } else {
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('add-to-cart-error')]);
        }
                die();
                break;

            case 'remove_from_cart':
        $course_id = $this->input->post('course_id');
        if (!$course_id) {
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-id-required')]);
                    die();
        }

        if (flexacademy_remove_from_cart($course_id)) {
            $cart_count = flexacademy_get_cart_count();
            echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('course-removed-from-cart-success'), 'cart_count' => $cart_count]);
        } else {
            echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('remove-from-cart-error')]);
        }
                die();
                break;

            case 'clear_cart':
                if (flexacademy_clear_cart()) {
                    echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('cart-cleared-success')]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('clear-cart-error')]);
                }
                die();
                break;

            case 'enroll_course':
                $course_id = $this->input->post('course_id');
                if (!$course_id) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-id-required')]);
                    die();
                }

                if (!is_client_logged_in()) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('login-required'), 'redirect' => site_url('authentication/login')]);
                    die();
                }

        
                $course = $this->Flexacademy_courses_model->get(['id' => $course_id]);
                if (!$course) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course-not-found')]);
                    die();
                }

                $student_id = get_contact_user_id();
                
                // Check if already enrolled
                if ($this->Flexacademy_enrollments_model->is_enrolled($course_id, $student_id)) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('already-enrolled')]);
                    die();
                }

                $price = isset($course['discount_price']) && (float) $course['discount_price'] > 0
                    ? (float) $course['discount_price']
                    : (float) ($course['price'] ?? 0);

                $requires_payment = ($course['pricing_type'] ?? 'free') === 'paid' && $price > 0;
                $enrollment_options = [
                    'status' => 'enrolled',
                    'payment_status' => $requires_payment ? 'pending' : 'paid',
                    'amount_paid' => 0,
                ];

                $data = flexacademy_build_enrollment_data($course, ['id' => $student_id, 'type' => 'client'], $enrollment_options);
                $enrollment_id = $this->Flexacademy_enrollments_model->enroll_student($data);
                
                if ($enrollment_id) {
                    $invoice_id = null;

                    if ($requires_payment) {
                        $this->load->model('clients_model');
                        $contact = $this->clients_model->get_contact($student_id);

                        if ($contact && $contact->userid) {
                            $invoice_id = flexacademy_create_invoice($contact->userid, $course_id, (object) $course, $enrollment_id);
                        }

                        if ($invoice_id) {
                            $this->Flexacademy_enrollments_model->update_enrollment($enrollment_id, [
                                'invoice_id' => $invoice_id,
                                'payment_status' => 'pending',
                            ]);
                        }
                    }

                    log_activity('Student enrolled in course [Course ID: ' . $course_id . ', Student ID: ' . $student_id . ', Type: client]');
                    echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('enrollment-success'), 'redirect' => site_url('flexacademy/my-courses')]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('enrollment-error')]);
                }
                die();
                break;

            case 'unenroll_course':
                // Feature disabled
                echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('action-not-allowed')]);
                die();
                break;

            case 'update_lesson_progress':
                $lesson_id = $this->input->post('lesson_id');
                $status = $this->input->post('status');
                $time_spent = $this->input->post('time_spent') ? (int)$this->input->post('time_spent') : 0;
                $score = $this->input->post('score') ? (float)$this->input->post('score') : null;

                if (!$lesson_id || !$status) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('invalid-parameters')]);
                    die();
                }

                // Check if user is staff or client
                $is_staff = is_staff_logged_in();
                $is_client = is_client_logged_in();
                
                if (!$is_staff && !$is_client) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('login-required')]);
                    die();
                }

              
                $this->load->model('flexacademy/Flexacademy_lessons_model');
                $this->load->model('flexacademy/Flexacademy_lesson_progress_model');
                $this->load->model('flexacademy/Flexacademy_certificates_model');
                
                // Get student ID and type based on user
                if ($is_client) {
                    $student_id = get_contact_user_id();
                    $student_type = 'client';
                    
                } else {
                    $student_id = get_staff_user_id();
                    $student_type = 'staff';
                }
                
                $lesson = $this->Flexacademy_lessons_model->get(['id' => $lesson_id]);
                
                if (!$lesson) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('lesson-not-found')]);
                    die();
                }

                $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($lesson['course_id'], $student_id, $student_type);
                if (!$enrollment) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('not-enrolled')]);
                    die();
                }

                if ($lesson['lesson_type'] === 'quiz') {
                    if ($status !== 'completed') {
                        echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz_cannot_uncheck')]);
                        die();
                    }

                    $this->load->model('flexacademy/Flexacademy_quiz_model');
                    $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');

                    $quiz_id = isset($lesson['quiz_id']) ? (int) $lesson['quiz_id'] : 0;
                    if (!$quiz_id) {
                        echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz-not-found')]);
                        die();
                    }

                    $quiz = $this->Flexacademy_quiz_model->get(['id' => $quiz_id]);
                    if (!$quiz) {
                        echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz-not-found')]);
                        die();
                    }

                    $attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz($enrollment->id, $quiz['id']);
                    $best_score = 0;

                    if (!empty($attempts)) {
                        foreach ($attempts as $attempt) {
                            if ($attempt['status'] === 'completed' && isset($attempt['score'])) {
                                $best_score = max($best_score, (float) $attempt['score']);
                            }
                        }
                    }

                    $required_score = isset($quiz['total_marks']) ? (float) $quiz['total_marks'] : 0.0;
                    if ($required_score > 0 && $best_score < $required_score) {
                        echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz_must_be_perfect')]);
                        die();
                    }
                }

                if ($this->Flexacademy_lesson_progress_model->update_entry($enrollment->id, $lesson_id, $status, $time_spent, $score)) {
                    $progress = $this->recalculate_enrollment_progress($enrollment->id, $lesson['course_id']);
                    if ($progress >= 100) {
                        $updated_enrollment = $this->Flexacademy_enrollments_model->get($enrollment->id);
                        $this->maybe_issue_certificate($updated_enrollment);
                    }

                    echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('progress-update-success')]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('progress-update-error')]);
                }
                die();
                break;

            case 'checkout':
            case 'enroll_from_cart':
                if (!is_client_logged_in()) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('login-required')]);
                    die();
                }


                $cart_items = flexacademy_get_cart_items();
                $student_id = get_contact_user_id();
                $client_id = $student_id;
                
                if (empty($cart_items)) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('cart-empty')]);
                    die();
                }

                $courses_to_process = [];
                $already_enrolled = [];
                $course_not_found = [];

                // Get full course details and check enrollment status
                foreach ($cart_items as $course_id => $course_data) {
                    $course = $this->Flexacademy_courses_model->get(['id' => $course_id]);
                    
                    if (!$course) {
                        $course_not_found[] = $course_data['title'];
                        continue;
                    }

                        // Check if already enrolled
                        if ($this->Flexacademy_enrollments_model->is_enrolled($course_id, $student_id)) {
                        $already_enrolled[] = $course_data['title'];
                            continue;
                        }

                    // Convert array to object for consistency
                    if (is_array($course)) {
                        $course = (object) $course;
                    }

                    $courses_to_process[] = $course;
                }

                // If no courses to process, return error with details
                if (empty($courses_to_process)) {
                    $message = !empty($already_enrolled) ? _flexacademy_lang('all-courses-already-enrolled') : _flexacademy_lang('no-valid-courses');
                    
                    // Add debug info
                    $debug_info = [
                        'cart_count' => count($cart_items),
                        'already_enrolled_count' => count($already_enrolled),
                        'not_found_count' => count($course_not_found)
                    ];
                    
                    echo json_encode([
                        'status' => 'error',
                        'message' => $message,
                        'already_enrolled' => $already_enrolled,
                        'course_not_found' => $course_not_found,
                        'debug' => $debug_info
                    ]);
                    die();
                }

                // NEW FLOW: Create ORDER first
                $order_id = flexacademy_create_order($client_id, $student_id, $cart_items);
                
                if (!$order_id) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => _flexacademy_lang('failed-to-create-order')
                    ]);
                    die();
                }

                // Create INVOICE from order
                $invoice_id = flexacademy_create_invoice_from_order($order_id);
                
                if (!$invoice_id) {
                    // Cancel the order if invoice creation fails
                    flexacademy_cancel_order($order_id);
                    echo json_encode([
                        'status' => 'error', 
                        'message' => _flexacademy_lang('invoice-creation-failed')
                    ]);
                    die();
                }

                // Clear cart after successful order + invoice creation
                if ($order_id && $invoice_id) {
                    flexacademy_clear_cart();
                }

                // Get invoice for redirect
                $this->load->model('invoices_model');
                $invoice = $this->invoices_model->get($invoice_id);
                $invoice_hash = $invoice ? $invoice->hash : '';

                // Get order details for message
                $order = flexacademy_get_order($order_id);
                $order_items = json_decode($order['order_items'], true);
                
                // Redirect to invoice payment page
                $response = [
                    'status' => 'success',
                    'message' => _flexacademy_lang('order-created-successfully'),
                    'invoice_id' => $invoice_id,
                    'order_id' => $order_id,
                    'order_number' => $order['order_number'],
                    'courses_count' => count($order_items),
                    'already_enrolled' => $already_enrolled,
                    'redirect' => site_url('invoice/' . $invoice_id . '/' . $invoice_hash)
                ];

                echo json_encode($response);
                die();
                break;

            case 'start_quiz_attempt':
                $quiz_id = $this->input->post('quiz_id');
                $enrollment_id = $this->input->post('enrollment_id');
                
                if (!$quiz_id || !$enrollment_id) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('missing-parameters')]);
                    die();
                }
                
                $this->load->model('flexacademy/Flexacademy_quiz_model');
                $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');
            
                // Get quiz details
                $quiz = $this->Flexacademy_quiz_model->get(['id' => $quiz_id]);
                if (!$quiz) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz-not-found')]);
                    die();
                }
                
                // Verify enrollment
                $enrollment = $this->Flexacademy_enrollments_model->get($enrollment_id);
                if (!$enrollment) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('enrollment-not-found')]);
                    die();
                }
                
                // Check if student is logged in
                $student_id = is_staff_logged_in() ? get_staff_user_id() : get_contact_user_id();
                
                // Abandon any existing in_progress attempts for this quiz and enrollment
                $existing_attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz(
                    $enrollment_id,
                    $quiz_id,
                    'in_progress'
                );
                
                $now = new DateTime('now', new DateTimeZone('UTC'));
                foreach ($existing_attempts as $attempt) {
                    $this->Flexacademy_quiz_attempts_model->update($attempt['id'], [
                        'status' => 'abandoned',
                        'end_time' => $now->format('Y-m-d H:i:s')
                    ]);
                }
                
                // Create new attempt
                $attempt_data = [
                    'quiz_id' => $quiz_id,
                    'enrollment_id' => $enrollment_id,
                    'student_id' => $student_id,
                    'status' => 'in_progress',
                    'start_time' => $now->format('Y-m-d H:i:s'),
                    'created_at' => $now->format('Y-m-d H:i:s')
                ];
                
                $attempt_id = $this->Flexacademy_quiz_attempts_model->add($attempt_data);
                
                if ($attempt_id) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => _flexacademy_lang('quiz-started-successfully'),
                        'attempt_id' => $attempt_id,
                        'start_time' => $now->format('Y-m-d H:i:s'),
                        'timezone' => 'UTC'
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('failed-to-start-quiz')]);
                }
                die();
                break;

            case 'submit_quiz':
                $attempt_id = $this->input->post('attempt_id');
                $quiz_id = $this->input->post('quiz_id');
                $answers_json = $this->input->post('answers');
                
                if (!$attempt_id || !$quiz_id || !$answers_json) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('missing-parameters')]);
                    die();
                }
                
                $this->load->model('flexacademy/Flexacademy_quiz_model');
                $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');
                $this->load->model('flexacademy/Flexacademy_quiz_questions_model');
                $this->load->model('flexacademy/Flexacademy_lessons_model');
                $this->load->model('flexacademy/Flexacademy_lesson_progress_model');
                $this->load->model('flexacademy/Flexacademy_certificates_model');
                
                // Get attempt
                $attempt = $this->Flexacademy_quiz_attempts_model->get(['id' => $attempt_id]);
                if (!$attempt || $attempt['status'] !== 'in_progress') {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('invalid-attempt')]);
                    die();
                }
                
                // Get quiz
                $quiz = $this->Flexacademy_quiz_model->get(['id' => $quiz_id]);
                if (!$quiz) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('quiz-not-found')]);
                    die();
                }
                
                // Get questions
                $questions = $this->Flexacademy_quiz_questions_model->all(['quiz_id' => $quiz_id]);
                if (empty($questions)) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('no-questions-found')]);
                    die();
                }
                
                // Parse answers
                $answers = json_decode($answers_json, true);
                
                // Calculate score
                $correct_count = 0;
                foreach ($questions as $question) {
                    $user_answer = isset($answers[$question['id']]) ? trim($answers[$question['id']]) : '';
                    $correct_answer = trim($question['correct_answer']);
                    
                    if (strcasecmp($user_answer, $correct_answer) === 0) {
                        $correct_count++;
                    }
                }
                
                $total_questions = count($questions);
                $score = ($correct_count / $total_questions) * 100;
                
                // Update attempt
                $now = new DateTime('now', new DateTimeZone('UTC'));
                $this->Flexacademy_quiz_attempts_model->update($attempt_id, [
                    'status' => 'completed',
                    'score' => $score,
                    'answers' => $answers_json,
                    'end_time' => $now->format('Y-m-d H:i:s')
                ]);
                
                // Update lesson progress based on pass/fail
                $lesson = $this->Flexacademy_lessons_model->get(['quiz_id' => $quiz_id]);
                if ($lesson) {
                    if ($score >= $quiz['pass_marks']) {
                        // Passed: mark lesson as completed
                        $this->Flexacademy_lesson_progress_model->update_entry(
                            $attempt['enrollment_id'],
                            $lesson['id'],
                            'completed',
                            0,
                            $score
                        );

                        $enrollment = $this->Flexacademy_enrollments_model->get($attempt['enrollment_id']);

                        if ($enrollment) {
                            $progress = $this->recalculate_enrollment_progress($enrollment->id, $enrollment->course_id);

                            if ($progress >= 100) {
                                $updated_enrollment = $this->Flexacademy_enrollments_model->get($enrollment->id);
                                $this->maybe_issue_certificate($updated_enrollment);
                            }
                        }
                    } else {
                        // Failed: mark as in_progress (not completed)
                        $this->Flexacademy_lesson_progress_model->update_entry(
                            $attempt['enrollment_id'],
                            $lesson['id'],
                            'in_progress',
                            0,
                            $score
                        );
                    }
                }
                
                $message = $score >= $quiz['pass_marks'] 
                    ? _flexacademy_lang('quiz-completed')
                    : _flexacademy_lang('quiz-completed-failed');
                
                echo json_encode([
                    'status' => 'success',
                    'message' => $message,
                    'score' => round($score, 2),
                    'passed' => $score >= $quiz['pass_marks'],
                    'correct' => $correct_count,
                    'total' => $total_questions
                ]);
                die();
                break;
        }
    }

    public function cart()
    {

        $cart_items = flexacademy_get_cart_items();
        $cart_total = flexacademy_get_cart_total();
        $cart_count = flexacademy_get_cart_count();

        $this->data([
            'cart_items' => $cart_items,
            'cart_total' => $cart_total,
            'cart_count' => $cart_count,
            'currency' => $this->currencies_model->get_base_currency()
        ]);

        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));

        $this->view('client/cart/index');
        $this->layout();
    }

    public function my_courses()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

       
        $student_id = get_contact_user_id();
        $enrollments = $this->Flexacademy_enrollments_model->get_student_enrollments($student_id);

        $course_ids = array_unique(array_column($enrollments, 'course_id'));
        $courses = $this->Flexacademy_courses_model->get_many($course_ids);

        // Enrich each enrollment with instructor data to avoid model loads in view
        foreach ($enrollments as &$enrollment) {
            $course = isset($courses[$enrollment['course_id']]) ? $courses[$enrollment['course_id']] : null;

            $enrollment['course_title'] = $course['title'] ?? '';
            $enrollment['course_slug'] = $course['slug'] ?? '';
            $enrollment['course_image'] = $course['image'] ?? '';
            $enrollment['course_price'] = $course['price'] ?? 0;
            $enrollment['course_discount_price'] = $course['discount_price'] ?? 0;
            $enrollment['difficulty_level'] = $course['difficulty_level'] ?? '';
            $enrollment['progress'] = isset($enrollment['progress']) ? (float) $enrollment['progress'] : 0;

            // Get first instructor for this course using existing helper
            $instructors = $this->Flexacademy_instructors_model->all(['course_id' => $enrollment['course_id']]);

            if (!empty($instructors)) {
                $primary_instructor = $instructors[0];
                $remaining = max(count($instructors) - 1, 0);
                $enrollment['instructor_name'] = $primary_instructor['name'] . ($remaining > 0 ? ' (+' . $remaining . ')' : '');
                $enrollment['instructor_image'] = flexacademy_instructor_image($primary_instructor);
            } else {
                $enrollment['instructor_name'] = '';
                $enrollment['instructor_image'] = '';
            }
        }
        $this->data([
            'title' => _flexacademy_lang('my-courses'),
            'enrollments' => $enrollments,
            'currency' => $this->currencies_model->get_base_currency()
        ]);

        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));

        $this->view('client/my-courses/index');
        $this->layout();
    }

    public function certificate($certificate_number)
    {
        if (!is_client_logged_in() && !is_staff_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $this->load->model('flexacademy/Flexacademy_certificates_model');
        $this->load->model('flexacademy/Flexacademy_enrollments_model');
        
        $certificate = $this->Flexacademy_certificates_model->find_by_number($certificate_number);
        if (!$certificate) {
            show_404();
        }

        $enrollment = $this->Flexacademy_enrollments_model->get($certificate->enrollment_id);
        if (!$enrollment) {
            show_404();
        }

        if (is_client_logged_in()) {
            if ($enrollment->student_type !== 'client' || $enrollment->student_id != get_contact_user_id()) {
                show_404();
            }
        } elseif (is_staff_logged_in()) {
            if ($enrollment->student_type !== 'staff' || $enrollment->student_id != get_staff_user_id()) {
                show_404();
            }
        }

        $course = $this->Flexacademy_courses_model->get(['id' => $enrollment->course_id]);
        if (!$course) {
            show_404();
        }

        $total_lessons = flexacademy_get_course_total_lessons($course['id']);
        $total_duration_minutes = flexacademy_get_course_total_duration($course['id']);
        $total_duration_formatted = flexacademy_convert_duration_from_minutes($total_duration_minutes);

        $primary_instructor = '';
        $instructors = $this->Flexacademy_instructors_model->all(['course_id' => $course['id']]);
        if (!empty($instructors)) {
            $primary_instructor = $instructors[0]['name'];
        }
        $primary_instructor_signature_url = (!empty($instructors) && !empty($instructors[0]['signature']))
            ? flexacademy_media_url($instructors[0]['signature'])
            : '';

        if ($enrollment->student_type === 'client') {
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact($enrollment->student_id);
            $student_name = $contact ? trim($contact->firstname . ' ' . $contact->lastname) : '';
        } else {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get($enrollment->student_id);
            $student_name = $staff ? trim($staff->firstname . ' ' . $staff->lastname) : '';
        }

        $certificate_url = site_url('flexacademy/certificate/' . $certificate->certificate_number);
        $qr_code_url = "";
        $issuer_signature_path = get_option('flexacademy_certificate_issuer_signature');
        $issuer_signature_url = $issuer_signature_path ? flexacademy_media_url($issuer_signature_path) : '';
        $certificate_prefix = get_option('flexacademy_certificate_prefix') ?: 'FLEX';

        $this->data([
            'title' => _flexacademy_lang('course_certificate'),
            'certificate' => $certificate,
            'certificate_number' => $certificate->certificate_number,
            'certificate_url' => $certificate_url,
            'issue_date_formatted' => _d($certificate->issue_date),
            'course' => $course,
            'course_level' => !empty($course['difficulty_level']) ? ucwords(str_replace('_', ' ', $course['difficulty_level'])) : '',
            'course_language' => !empty($course['language']) ? flexacademy_get_course_languages($course['language']) : '',
            'enrollment' => $enrollment,
            'student_name' => $student_name,
            'primary_instructor' => $primary_instructor,
            'company_name' => get_option('companyname'),
            'primary_instructor_signature_url' => $primary_instructor_signature_url,
            'issuer_signature_url' => $issuer_signature_url,
            'certificate_prefix' => $certificate_prefix,
            'total_lessons' => $total_lessons,
            'total_duration_formatted' => $total_duration_formatted,
            'total_duration_minutes' => $total_duration_minutes,
            'qr_code_url' => $qr_code_url,
        ]);

        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));
        $this->app_scripts->theme('flexacademy-html2pdf', module_dir_url('flexacademy','assets/js/html2pdf.bundle.min.js'));
        $this->view('client/certificate/show');
        $this->layout();
    }

    public function course_player($slug, $lesson_id = null)
    {
        if (!is_client_logged_in() && !is_staff_logged_in()) {
            redirect(site_url('authentication/login'));
        }

    
        $course = $this->Flexacademy_courses_model->get(['slug' => $slug]);
        if (!$course) {
            show_404();
        }

        $student_id = null;
        $student_type = 'client';
        $is_creator = false;
        
        if (is_client_logged_in()) {
            $student_id = get_contact_user_id();
            $student_type = 'client';
            $is_creator = $this->Flexacademy_courses_model->is_creator($course['id'], $student_id);
        } elseif (is_staff_logged_in()) {
            $staff_id = get_staff_user_id();
            $is_creator = $this->Flexacademy_courses_model->is_creator($course['id'], $staff_id);
            
            if (!$is_creator) {
                redirect(admin_url('flexacademy/staff_course_player/' . $slug));
            }
            
            
            $student_id = $staff_id;
            $student_type = 'staff';
        }

        $redirect_url = site_url('flexacademy/course/details/' . $slug);
        
        // Get enrollment (may be null for creators)
        $enrollment = null;
        if ($student_id) {
            $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course['id'], $student_id, $student_type);
        }
        
        // If not creator and no enrollment, redirect
        if (!$is_creator && !$enrollment) {
            redirect($redirect_url);
        }

        if ($enrollment && flexacademy_is_enrollment_expired($enrollment->expires_at)) {
            set_alert('warning', _flexacademy_lang('course-access-expired'));
            redirect($redirect_url);
        }

        // Check payment status - block access if payment is pending (only for clients)
        if ($enrollment) {
            $payment_status = isset($enrollment->payment_status) ? $enrollment->payment_status : 'paid';
            if ($payment_status === 'pending') {
                // Redirect to invoice payment if available
                if (isset($enrollment->invoice_id) && $enrollment->invoice_id) {
                    $this->load->model('invoices_model');
                    $invoice = $this->invoices_model->get($enrollment->invoice_id);
                    if ($invoice) {
                        set_alert('warning', _flexacademy_lang('payment-required-to-access'));
                        redirect(site_url('invoice/' . $enrollment->invoice_id . '/' . $invoice->hash));
                    }
                }
                // If no invoice, redirect to course details
                set_alert('warning', _flexacademy_lang('course-access-restricted'));
                redirect($redirect_url);
            }
        }

      
        $sections = flexacademy_get_course_sections_with_lessons($course['id']);
        
   
        $lesson = null;
        $lesson_progress = null;
        
        $progress_entries = [];
        if ($enrollment) {
            $progress_entries = $this->Flexacademy_lesson_progress_model->get_entries_for_enrollment($enrollment->id);
        }

        if ($lesson_id) {
            $lesson = $this->Flexacademy_lessons_model->get(['id' => $lesson_id]);
            if (!$lesson || $lesson['course_id'] != $course['id']) {
                show_404();
                
            }
            $lesson_progress = isset($progress_entries[$lesson_id]) ? (object) $progress_entries[$lesson_id] : null;
        } else {
            if (!empty($sections) && !empty($sections[0]['lessons'])) {
                $lesson = $sections[0]['lessons'][0];
                $lesson_id = $lesson['id'];
                $lesson_progress = isset($progress_entries[$lesson_id]) ? (object) $progress_entries[$lesson_id] : null;
            }
        }
        
        // If lesson is a quiz, load quiz data
        if ($lesson && $lesson['lesson_type'] === 'quiz' && !empty($lesson['quiz_id'])) {
            $this->load->model('flexacademy/Flexacademy_quiz_model');
            $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');
            $this->load->model('flexacademy/Flexacademy_quiz_questions_model');
            
            $quiz = $this->Flexacademy_quiz_model->get(['id' => $lesson['quiz_id']]);
            $questions = [];
            $attempts = [];
            $active_attempt = null;
            $attempt_count = 0;
            $best_score = null;
            $can_retake = false;
            $lesson_completed = false;
            
            if ($quiz) {
                // Get all questions for this quiz
                $questions = $this->Flexacademy_quiz_questions_model->all(['quiz_id' => $quiz['id']]);
                
                // Get all attempts for this enrollment and quiz (only if enrollment exists)
                $attempts = [];
                if ($enrollment) {
                    $attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz(
                        $enrollment->id,
                        $quiz['id']
                    );
                    
                    // Check and abandon expired in_progress attempts
                    $now = new DateTime('now', new DateTimeZone('UTC'));
                    foreach ($attempts as $attempt) {
                        if ($attempt['status'] === 'in_progress' && !empty($quiz['time_limit'])) {
                            $start_time = new DateTime($attempt['start_time'], new DateTimeZone('UTC'));
                            $time_limit_seconds = $quiz['time_limit'] * 60;
                            $elapsed = $now->getTimestamp() - $start_time->getTimestamp();
                            
                            if ($elapsed > $time_limit_seconds) {
                                $this->Flexacademy_quiz_attempts_model->update($attempt['id'], [
                                    'status' => 'abandoned',
                                    'end_time' => $now->format('Y-m-d H:i:s')
                                ]);
                            }
                        }
                    }
                    
                    // Reload attempts after abandoning expired ones
                    $attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz(
                        $enrollment->id,
                        $quiz['id']
                    );
                    
                    // Find the active (in_progress) attempt - get the newest one
                    $active_attempt = $this->Flexacademy_quiz_attempts_model->get_latest_attempt_by_status(
                        $enrollment->id,
                        $quiz['id'],
                        'in_progress'
                    );
                } else {
                    // Creator without enrollment - no attempts
                    $active_attempt = null;
                }
                
                // Calculate attempt statistics
                $completed_attempts = array_filter($attempts, function($a) {
                    return $a['status'] === 'completed';
                });
                $attempt_count = count($completed_attempts);
                
                // Get best score from completed attempts
                if (!empty($completed_attempts)) {
                    $scores = array_map(function($a) {
                        return $a['score'];
                    }, $completed_attempts);
                    $best_score = max($scores);
                    
                    // Check if lesson is completed (passed quiz)
                    // Only mark as completed if passed AND no active attempt in progress
                    if ($best_score >= $quiz['pass_marks'] && !$active_attempt) {
                        $lesson_completed = true;
                    }
                }
                
                // Check if user can retake
                $retake_limit = isset($quiz['retake_limit']) ? (int)$quiz['retake_limit'] : 0;
                if ($retake_limit === 0) {
                    // Unlimited retakes
                    $can_retake = true;
                } else {
                    $can_retake = ($attempt_count < $retake_limit);
                }
            }
            
            // Add quiz data to lesson
            $lesson['quiz_data'] = [
                'quiz' => $quiz,
                'questions' => $questions,
                'attempts' => $attempts,
                'active_attempt' => $active_attempt,
                'attempt_count' => $attempt_count,
                'best_score' => $best_score,
                'can_retake' => $can_retake,
                'lesson_completed' => $lesson_completed
            ];
        }
        
       
        $certificate = null;
        $certificate_url = null;
        
        if ($enrollment) {
            if ((float) $enrollment->progress >= 100 && $enrollment->status === 'completed' && empty($enrollment->certificate_id)) {
                $this->maybe_issue_certificate($enrollment);
                $enrollment = $this->Flexacademy_enrollments_model->get($enrollment->id);
            }

            $certificateId = !empty($enrollment->certificate_id) ? (int) $enrollment->certificate_id : null;

            if ($certificateId) {
                $certificate = $this->Flexacademy_certificates_model->get($certificateId);
            }

            if (!$certificate) {
                $certificate = $this->Flexacademy_certificates_model->get_by_enrollment($enrollment->id);

                if ($certificate && empty($enrollment->certificate_id)) {
                    $this->Flexacademy_enrollments_model->update_enrollment($enrollment->id, ['certificate_id' => $certificate->id]);
                    $enrollment->certificate_id = $certificate->id;
                }
            }
            if ($certificate) {
                $certificate_url = site_url('flexacademy/client/certificate/' . $certificate->certificate_number);
            }
        }

        // Mark current lesson in sections
        foreach ($sections as &$section) {
            foreach ($section['lessons'] as &$lesson_item) {
                $progress = isset($progress_entries[$lesson_item['id']]) ? (object) $progress_entries[$lesson_item['id']] : null;
                $lesson_item['progress'] = $progress;
                $lesson_item['is_current'] = ($lesson_item['id'] == $lesson_id);
            }
        }

        $this->data([
            'title' => $lesson ? ($lesson['title'] . ' - ' . $course['title']) : ($course['title'] . ' - ' . _flexacademy_lang('course-player')),
            'lesson' => $lesson,
            'course' => $course,
            'enrollment' => $enrollment,
            'lesson_progress' => $lesson_progress,
            'sections' => $sections,
            'currency' => $this->currencies_model->get_base_currency(),
            'certificate' => $certificate,
            'certificate_url' => $certificate_url,
            'certificate_prefix' => get_option('flexacademy_certificate_prefix') ?: 'FLEX',
            'back_url' => $is_creator ? admin_url('flexacademy/course_details/' . $course['id']) : null,
        ]);

        $this->app_css->theme('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'));
        $this->app_scripts->theme('flexacademy-html2pdf', module_dir_url('flexacademy','assets/js/html2pdf.bundle.min.js'));
        $this->app_scripts->theme('flexacademy-js', module_dir_url('flexacademy','assets/js/flexacademy.js'));

        $this->view('client/lesson/index');
        $this->layout();
    }

    private function recalculate_enrollment_progress($enrollment_id, $course_id)
    {
        if (!isset($this->Flexacademy_lessons_model)) {
            $this->load->model('flexacademy/Flexacademy_lessons_model');
        }

        if (!isset($this->Flexacademy_lesson_progress_model)) {
            $this->load->model('flexacademy/Flexacademy_lesson_progress_model');
        }

        $lessons = $this->Flexacademy_lessons_model->all(['course_id' => $course_id]);
        $total_lessons = count($lessons);

        if ($total_lessons === 0) {
            $this->Flexacademy_enrollments_model->update_progress($enrollment_id, 0);
            return 0;
        }

        $completed = $this->Flexacademy_lesson_progress_model->count_completed($enrollment_id);
        $progress = $completed >= $total_lessons ? 100 : ($completed / $total_lessons) * 100;

        $this->Flexacademy_enrollments_model->update_progress($enrollment_id, $progress);

        return $progress;
    }

    private function maybe_issue_certificate($enrollment)
    {
        if (!$enrollment) {
         
            return;
        }

        if ($enrollment->status !== 'completed') {
            return;
        }

        if (!isset($this->Flexacademy_certificates_model)) {
            $this->load->model('flexacademy/Flexacademy_certificates_model');
        }

        $this->Flexacademy_certificates_model->issue_certificate($enrollment);
    }

}