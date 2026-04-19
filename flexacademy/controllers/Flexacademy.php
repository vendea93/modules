<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flexacademy/Flexacademy_courses_model');
        $this->load->model('flexacademy/Flexacademy_categories_model');
        $this->load->model('flexacademy/Flexacademy_sections_model');
        $this->load->model('flexacademy/Flexacademy_lessons_model');
        $this->load->model('flexacademy/Flexacademy_quiz_model');
        $this->load->model('flexacademy/Flexacademy_quiz_questions_model');
        $this->load->model('flexacademy/Flexacademy_instructors_model');
        $this->load->model('flexacademy/Flexacademy_certificates_model');
        $this->load->model('flexacademy/Flexacademy_enrollments_model');
        $this->load->model('flexacademy/Flexacademy_lesson_progress_model');
        $this->load->library('form_validation');
        $this->load->helper('flexacademy/flexacademy');
    }

    public function index()
    {
        if (!has_permission('flexacademy', '', 'view')) {
            access_denied('flexacademy');
        }

        $this->load->model('clients_model');

        // Get total statistics via models
        $total_courses = $this->Flexacademy_courses_model->count_all_courses();
        $total_enrollments = $this->Flexacademy_enrollments_model->count_total(null);
        $total_active_courses = $this->Flexacademy_courses_model->count_active_courses();
        $total_certificates = $this->Flexacademy_certificates_model->count_all();
        $expired_enrollments = $this->Flexacademy_enrollments_model->count_expired(null);

        // Get recent courses (last 5)
        $recent_courses = $this->Flexacademy_courses_model->get_recent_courses(5);

        // Enrich with instructor names
        foreach ($recent_courses as &$course) {
            $instructor_name = $this->Flexacademy_instructors_model->get_primary_instructor_name($course['id']);
            $course['instructor_name'] = $instructor_name ? $instructor_name : 'N/A';
            $course['date_created'] = $course['created_at'];
        }
        unset($course);

        // Get recent enrollments (last 5)
        $recent_enrollments = $this->Flexacademy_enrollments_model->get_recent_enrollments(5, null);

        $recent_course_ids = array_unique(array_column($recent_enrollments, 'course_id'));
        $recent_courses_map = $this->Flexacademy_courses_model->get_many($recent_course_ids);

        foreach ($recent_enrollments as &$enrollment) {
            $course = $recent_courses_map[$enrollment['course_id']] ?? null;
            $enrollment['course_title'] = $course['title'] ?? '';

            $contact = $this->clients_model->get_contact($enrollment['student_id']);
            $enrollment['student_name'] = $contact ? trim($contact->firstname . ' ' . $contact->lastname) : '';
            $enrollment['enrollment_date'] = $enrollment['enrolled_at'];
            $enrollment['progress'] = isset($enrollment['progress']) ? (float) $enrollment['progress'] : 0;
        }
        unset($enrollment);

        // Get top performing courses by enrollment count
        $top_courses = $this->Flexacademy_enrollments_model->get_top_courses(5, null);
        $top_course_ids = array_column($top_courses, 'course_id');
        $top_course_map = $this->Flexacademy_courses_model->get_many($top_course_ids);

        foreach ($top_courses as &$top_course) {
             $course = $top_course_map[$top_course['course_id']] ?? null;
            $top_course['id'] = $top_course['course_id'];
            $top_course['title'] = $course['title'] ?? '';
            $top_course['avg_progress'] = isset($top_course['avg_progress']) ? round((float) $top_course['avg_progress'], 2) : 0;
        }
        unset($top_course);

        // Get completion statistics
        $completion_stats = [
            'completed'   => $this->Flexacademy_enrollments_model->count_by_status('completed', null),
            'in_progress' => $this->Flexacademy_enrollments_model->count_by_status('in_progress', null),
            'enrolled'    => $this->Flexacademy_enrollments_model->count_by_status('enrolled', null),
        ];

        $data = [
            'title' => _flexacademy_lang('_dashboard'),
            'total_courses' => $total_courses,
            'total_active_courses' => $total_active_courses,
            'total_enrollments' => $total_enrollments,
            'total_certificates' => $total_certificates,
            'expired_enrollments' => $expired_enrollments,
            'recent_courses' => $recent_courses,
            'recent_enrollments' => $recent_enrollments,
            'top_courses' => $top_courses,
            'completion_stats' => $completion_stats,
        ];

        $this->load->view('dashboard', $data);
    }

    public function delete_instructor($id){
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('flexacademy');
        }
        $instructor = $this->Flexacademy_instructors_model->get(['id' => $id]);
        if (!$instructor) {
            set_alert('error', _flexacademy_lang('instructor_not_found'));
            redirect(admin_url('flexacademy/course_details/' . $instructor['course_id'] . '?key=instructors'));
        }
    
        if ($instructor['image']) {
            unlink(FLEXACADEMY_FOLDER . $instructor['image']);
        }
        if (!empty($instructor['signature'])) {
            @unlink(FLEXACADEMY_FOLDER . $instructor['signature']);
        }
        $this->Flexacademy_instructors_model->delete($id);
        set_alert('success', _flexacademy_lang('instructor_deleted_successfully'));
        redirect(admin_url('flexacademy/course_details/' . $instructor['course_id'] . '?key=instructors'));
    }

    public function add_edit_instructor(){
        $post_data = $this->input->post();
        if ($post_data) {
            $now = date('Y-m-d H:i:s');
            $data = [
                'course_id' => $post_data['course_id'],
                'name' => $post_data['name'],
                'email' => $post_data['email'],
                'job_title' => $post_data['job_title'],
                'bio' => $post_data['bio'],
                'updated_at' => $now,
            ];
            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                $data['image'] = $this->upload_file($_FILES['image'], true, 'instructors/'.$post_data['course_id'].'/');
            }
            $signature_path = '';
            if (isset($_FILES['signature']) && $_FILES['signature']['size'] > 0) {
                $signature_path = $this->upload_file($_FILES['signature'], true, 'instructors/'.$post_data['course_id'].'/');
            }
            if ($post_data['instructor_id'] == 0) {
                $data['created_at'] = $now;
                $data['signature'] = $signature_path;
                $this->Flexacademy_instructors_model->add($data);
                set_alert('success', _flexacademy_lang('instructor_added_successfully'));
            } else {
                if ($signature_path) {
                    $data['signature'] = $signature_path;
                }
                $this->Flexacademy_instructors_model->update($data, $post_data['instructor_id']);
                set_alert('success', _flexacademy_lang('instructor_updated_successfully'));
            }
        }
        redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=instructors'));
    }

    public function course_info(){
        $post_data = $this->input->post();
        if ($post_data) {
            $faq = [
                'question' => $post_data['question'],
                'answer' => $post_data['answer'],
            ];
            $data = [
                'faq' => flexacademyPerfectSerialize($faq),
                'requirements' => flexacademyPerfectSerialize($post_data['requirements']),
                'outcomes' => flexacademyPerfectSerialize($post_data['outcomes']),
            ];
            $this->Flexacademy_courses_model->update($data, $post_data['course_id']);
            set_alert('success', _flexacademy_lang('course_info_updated_successfully'));
            redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=info'));
        }
    }

    public function add_edit_quiz_question(){
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('flexacademy');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            //print_r($post_data);die();
            $options = "";
            $correct_answer = isset($post_data['correct_answer']) ? $post_data['correct_answer'] : "";
            if($post_data['question_type'] == "true-false"){
                $correct_answer = $post_data['correct_answer_true_false'];
            }else if($post_data['question_type'] == "single" || $post_data['question_type'] == "multiple"){
                $options = implode('::', $post_data['options']);
                $correct_answer = implode('::', $post_data['correct_answer_choice']);
            }
            $data = [
                'course_id' => $post_data['course_id'],
                'quiz_id' => $post_data['quiz_id'],
                'question_type' => $post_data['question_type'],
                'question' => $post_data['question'],
                'correct_answer' => $correct_answer,
                'options' => $options,
            ];
            if($post_data['question_id'] == 0){
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_quiz_questions_model->add($data);
                set_alert('success', _flexacademy_lang('question_created_successfully'));
            }else{
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_quiz_questions_model->update($data, $post_data['question_id']);
                set_alert('success', _flexacademy_lang('question_updated_successfully'));
            }
            redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
        }
        return redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
    }

    public function add_edit_quiz(){
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('flexacademy');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            $data = [
                'title' => $post_data['title'],
                'course_id' => $post_data['course_id'],
                'section_id' => $post_data['section_id'],
                'total_marks' => $post_data['total_marks'],
                'pass_marks' => $post_data['pass_marks'],
                'retake_limit' => $post_data['retake_limit'],
                'time_limit' => $post_data['time_limit'],
                'description' => $post_data['description'],
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($post_data['quiz_id'] == 0) {
                //create a type of Lesson with quiz type
                $data['created_at'] = date('Y-m-d H:i:s');
                $quiz_id = $this->Flexacademy_quiz_model->add($data);
                $lesson_data = [
                    'title' => $post_data['title'],
                    'course_id' => $post_data['course_id'],
                    'section_id' => $post_data['section_id'],
                    'duration' => $post_data['time_limit'],
                    'lesson_type' => 'quiz',
                    'sort_order' => 0,
                    'quiz_id' => $quiz_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->Flexacademy_lessons_model->add($lesson_data);
                set_alert('success', _flexacademy_lang('quiz-created-successfully'));
            } else {
                //update lesson if exists
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_quiz_model->update($data, $post_data['quiz_id']);
                //get the lesson id
                $lesson_id = $this->Flexacademy_lessons_model->get(['quiz_id' => $post_data['quiz_id']])['id'];
                //lesson data
                $lesson_data = [
                    'title' => $post_data['title'],
                    'course_id' => $post_data['course_id'],
                    'section_id' => $post_data['section_id'],
                    'duration' => $post_data['time_limit'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->Flexacademy_lessons_model->update($lesson_data, $lesson_id);
                set_alert('success', _flexacademy_lang('quiz-updated-successfully'));
            }
            redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
        }
        return redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
    }

    //delete quiz
    public function delete_quiz($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $lesson = $this->Flexacademy_lessons_model->get(['quiz_id' => $id]);
        $quiz = $this->Flexacademy_quiz_model->get(['id' => $id]);
        if (!$quiz) {
            set_alert('error', _flexacademy_lang('quiz_not_found'));
            redirect(admin_url('flexacademy/course_details/' . $lesson['course_id'] . '?key=curriculum'));
        }
        //delete lesson if exists
        if ($lesson) {
            $this->Flexacademy_lessons_model->delete($lesson['id']);
        }
        $this->Flexacademy_quiz_model->delete($id);
        //Todo :Delete all questions and options related to the quiz
        set_alert('success', _flexacademy_lang('quiz_deleted_successfully'));
        redirect(admin_url('flexacademy/course_details/' . $lesson['course_id'] . '?key=curriculum'));
    }

    public function add_edit_lesson()
    {
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('flexacademy');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            $data = [
                'title' => $post_data['title'],
                'course_id' => $post_data['course_id'],
                'section_id' => $post_data['section_id'],
                'duration' => $post_data['duration'],
                'lesson_type' => isset($post_data['lesson_type']) ? $post_data['lesson_type'] : '',
                'file_source' => isset($post_data['file_source']) ? $post_data['file_source'] : '',
                'file_url' => isset($post_data['file_url']) ? $post_data['file_url'] : '',
                'summary' => isset($post_data['summary']) ? $post_data['summary'] : '',
                'text_lesson' => isset($post_data['text_lesson']) ? $post_data['text_lesson'] : '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
                $data['file_path'] = $this->upload_file($_FILES['file'], false, 'lessons/'.$post_data['course_id'].'/');
            }
            if ($post_data['lesson_id'] == 0) {
                $this->Flexacademy_lessons_model->add($data);
                set_alert('success', _flexacademy_lang('lesson_created_successfully'));
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_lessons_model->update($data, $post_data['lesson_id']);
                set_alert('success', _flexacademy_lang('lesson_updated_successfully'));
            }
            redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
        }
        return redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
    }

    public function add_edit_section()
    {
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('flexacademy');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            $data = [
                'title' => $post_data['title'],
                'course_id' => $post_data['course_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($post_data['section_id'] == 0) {
                $data['sort_order'] = $this->Flexacademy_sections_model->get_max_sort_order($post_data['course_id']) + 1;
                $this->Flexacademy_sections_model->add($data);
                set_alert('success', _flexacademy_lang('section_created_successfully'));
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_sections_model->update($data, $post_data['section_id']);
                set_alert('success', _flexacademy_lang('section_updated_successfully'));
            }
            redirect(admin_url('flexacademy/course_details/' . $post_data['course_id'] . '?key=curriculum'));
        }
        $vm['title'] = _flexacademy_lang('add-section');
        $vm['course_id'] = $this->input->post('course_id');
        $this->load->view('course/details/add_edit_section', $vm);
    }

    public function delete_section($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $section = $this->Flexacademy_sections_model->get(['id' => $id]);
        if (!$section) {
            set_alert('error', _flexacademy_lang('section_not_found'));
            redirect(admin_url('flexacademy/course_details/' . $section['course_id'] . '?key=curriculum'));
        }
        $this->Flexacademy_sections_model->delete($id);
        set_alert('success', _flexacademy_lang('section_deleted_successfully'));
        redirect(admin_url('flexacademy/course_details/' . $section['course_id'] . '?key=curriculum'));
    }

    //delete enrollment
    public function delete_enrollment($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $enrollment = $this->Flexacademy_enrollments_model->get($id);
        if (!$enrollment) {
            set_alert('error', _flexacademy_lang('enrollment_not_found'));
            redirect(admin_url('flexacademy/enrollments'));
        }
        $this->Flexacademy_enrollments_model->drop_student($id);
        //delete certificate if exists
        if ($enrollment->certificate_id) {
            $this->Flexacademy_certificates_model->delete($enrollment->certificate_id);
        }
        set_alert('success', _flexacademy_lang('enrollment_deleted_successfully'));
        redirect(admin_url('flexacademy/enrollments'));
    }

    public function delete_lesson($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $lesson = $this->Flexacademy_lessons_model->get(['id' => $id]);
        if (!$lesson) {
            set_alert('error', _flexacademy_lang('lesson_not_found'));
            redirect(admin_url('flexacademy/course_details/' . $lesson['course_id'] . '?key=curriculum'));
        }
        //delete file if exists
        if ($lesson['file_path']) {
            unlink(FLEXACADEMY_FOLDER . $lesson['file_path']);
        }
        $this->Flexacademy_lessons_model->delete($id);
        set_alert('success', _flexacademy_lang('lesson_deleted_successfully'));
        redirect(admin_url('flexacademy/course_details/' . $lesson['course_id'] . '?key=curriculum'));
    }

    public function settings()
    {
        if (!has_permission('flexacademy', '', 'view')) {
            access_denied('Flexacademy');
        }

        $data['title'] = _flexacademy_lang('settings');

        if ($this->input->post()) {
            $this->handle_settings_file_upload(
                'flexacademy_certificate_issuer_signature',
                'flexacademy_certificate_issuer_signature',
                'remove_certificate_issuer_signature'
            );

            $prefix = trim((string) $this->input->post('flexacademy_certificate_prefix'));
            if ($prefix === '') {
                $prefix = 'FLEX';
            }
            update_option('flexacademy_certificate_prefix', strtoupper($prefix));

            set_alert('success', _flexacademy_lang('settings_updated_successfully'));
            redirect(admin_url('flexacademy/settings'));
        }

        $data['certificate_issuer_signature_path'] = get_option('flexacademy_certificate_issuer_signature');
        $data['certificate_prefix'] = get_option('flexacademy_certificate_prefix') ?: 'FLEX';
        $data['certificate_issuer_signature_url'] = $this->build_setting_file_url('flexacademy_certificate_issuer_signature');

        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-html2pdf', module_dir_url('flexacademy', 'assets/js/html2pdf.bundle.min.js'), 'admin', ['app-js']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);

        $this->load->view('settings', $data);
    }

    public function courses()
    {
        if (!has_permission('flexacademy', '', 'view')) {
            access_denied('Flexacademy');
        }
        $vm['title'] = _flexacademy_lang('courses');
        $vm['courses'] = $this->Flexacademy_courses_model->all();
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-html2pdf', module_dir_url('flexacademy', 'assets/js/html2pdf.bundle.min.js'), 'admin', ['app-js']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('course/index', $vm);
    }

    public function enrollments()
    {
        if (!has_permission('flexacademy', '', 'view')) {
            access_denied('Flexacademy');
        }

        $this->load->model('clients_model');
        $this->load->model('staff_model');
        

        $client_enrollments = $this->Flexacademy_enrollments_model->get_client_enrollments();
        $staff_enrollments = $this->Flexacademy_enrollments_model->get_all_staff_enrollments();
        $enrollments = array_merge($client_enrollments, $staff_enrollments);

        usort($enrollments, static function ($a, $b) {
            $aTime = isset($a['enrolled_at']) ? strtotime($a['enrolled_at']) : 0;
            $bTime = isset($b['enrolled_at']) ? strtotime($b['enrolled_at']) : 0;

            return $bTime <=> $aTime;
        });

        $course_ids = array_unique(array_column($enrollments, 'course_id'));
        $courses = $this->Flexacademy_courses_model->get_many($course_ids);
        $total_lessons_cache = [];
        $total_duration_cache = [];
        $contact_cache = [];
        $client_cache = [];
        $staff_cache = [];
        
        // Enrich enrollments with additional data
        foreach ($enrollments as &$enrollment) {
            $course = $courses[$enrollment['course_id']] ?? null;
            $enrollment['course_title'] = $course['title'] ?? '';
            $enrollment['course_slug'] = $course['slug'] ?? '';
            $enrollment['course_image'] = $course['image'] ?? '';

            $student_type = $enrollment['student_type'] ?? 'client';
            $enrollment['student_type_label'] = $student_type === 'staff'
                ? _flexacademy_lang('student-type-staff')
                : _flexacademy_lang('student-type-client');
            $enrollment['student_profile_url'] = '';
            $enrollment['student_meta'] = '';

            if ($student_type === 'staff') {
                if (!isset($staff_cache[$enrollment['student_id']])) {
                    $staff_cache[$enrollment['student_id']] = $this->staff_model->get($enrollment['student_id']);
                }

                $staff_member = $staff_cache[$enrollment['student_id']];
                if ($staff_member) {
                    $enrollment['student_name'] = trim(($staff_member->firstname ?? '') . ' ' . ($staff_member->lastname ?? ''));
                    $enrollment['student_email'] = $staff_member->email ?? '';
                    $enrollment['student_profile_url'] = admin_url('staff/member/' . $enrollment['student_id']);
                    $enrollment['student_meta'] = $staff_member->jobtitle ?? '';
                } else {
                    $enrollment['student_name'] = '';
                    $enrollment['student_email'] = '';
                }

                $enrollment['client_company'] = '';
            } else {
                if (!isset($contact_cache[$enrollment['student_id']])) {
                    $contact_cache[$enrollment['student_id']] = $this->clients_model->get_contact($enrollment['student_id']);
                }

                $contact = $contact_cache[$enrollment['student_id']];
                if ($contact) {
                    $enrollment['student_name'] = trim(($contact->firstname ?? '') . ' ' . ($contact->lastname ?? ''));
                    $enrollment['student_email'] = $contact->email ?? '';
                    $enrollment['student_profile_url'] = !empty($contact->userid)
                        ? admin_url('clients/client/' . $contact->userid . '?contactid=' . $contact->id)
                        : '';

                    if (!empty($contact->userid)) {
                        if (!isset($client_cache[$contact->userid])) {
                            $client_cache[$contact->userid] = $this->clients_model->get($contact->userid);
                        }

                        $client = $client_cache[$contact->userid];
                        $enrollment['client_company'] = $client ? ($client->company ?? '') : '';
                    } else {
                        $enrollment['client_company'] = '';
                    }

                    $enrollment['student_meta'] = $enrollment['client_company'];
                } else {
                    $enrollment['student_name'] = '';
                    $enrollment['student_email'] = '';
                    $enrollment['client_company'] = '';
                }
            }
            
            // Ensure progress is numeric (default to 0 if null)
            $enrollment['progress'] = isset($enrollment['progress']) && $enrollment['progress'] !== null ? (float)$enrollment['progress'] : 0;
            
            if (!isset($total_lessons_cache[$enrollment['course_id']])) {
                $total_lessons_cache[$enrollment['course_id']] = flexacademy_get_course_total_lessons($enrollment['course_id']);
            }

            if (!isset($total_duration_cache[$enrollment['course_id']])) {
                $total_duration_cache[$enrollment['course_id']] = flexacademy_get_course_total_duration($enrollment['course_id']);
            }

            $enrollment['total_lessons'] = $total_lessons_cache[$enrollment['course_id']];
            $enrollment['total_duration'] = $total_duration_cache[$enrollment['course_id']];
            
            $enrollment['completed_lessons'] = $this->Flexacademy_lesson_progress_model->count_completed($enrollment['id']);
            $enrollment['total_time_spent'] = $this->Flexacademy_lesson_progress_model->sum_time_spent($enrollment['id']);
            $last_entry = $this->Flexacademy_lesson_progress_model->get_last_accessed_entry($enrollment['id']);
            $enrollment['last_lesson'] = $last_entry ? ['last_accessed' => $last_entry['last_accessed'], 'lesson_id' => $last_entry['lesson_id']] : null;
        }
        unset($enrollment);
        
        // Get statistics
        $unique_student_keys = array_unique(array_map(static function ($enrollment) {
            return ($enrollment['student_type'] ?? 'client') . ':' . ($enrollment['student_id'] ?? '0');
        }, $enrollments));

        $stats = [
            'total_enrollments' => count($enrollments),
            'active_enrollments' => count(array_filter($enrollments, function($e) { return $e['status'] === 'active'; })),
            'completed_enrollments' => count(array_filter($enrollments, function($e) { return $e['progress'] >= 100; })),
            'in_progress' => count(array_filter($enrollments, function($e) { return $e['progress'] > 0 && $e['progress'] < 100; })),
            'total_students' => count($unique_student_keys),
        ];
        
        $available_courses = $this->Flexacademy_courses_model->all(['status' => 'active']);
        $enrollment_courses = [];

        foreach ($available_courses as $course_option) {
            $access = $course_option['access'] ?? 'everyone';

            if (!in_array($access, ['clients', 'staffs', 'everyone'], true)) {
                continue;
            }

            $enrollment_courses[] = [
                'id'   => $course_option['id'],
                'name' => $course_option['title'],
            ];
        }

        usort($enrollment_courses, static function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $contacts = $this->clients_model->get_contacts('', ['active' => 1]);
        $staff_members = $this->staff_model->get('', ['active' => 1]);
        $enrollment_students = [];
        $client_prefix = _flexacademy_lang('student-type-client');
        $staff_prefix = _flexacademy_lang('student-type-staff');

        foreach ($contacts as $contact) {
            $full_name = trim(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? ''));
            $email = $contact['email'] ?? '';

            if ($full_name === '' && $email !== '') {
                $full_name = $email;
            }

            if ($full_name === '') {
                $full_name = _flexacademy_lang('student');
            }

            if ($email !== '') {
                $full_name .= ' (' . $email . ')';
            }

            $enrollment_students[] = [
                'id'   => 'client:' . $contact['id'],
                'name' => $client_prefix . ' — ' . $full_name,
            ];
        }

        foreach ($staff_members as $staff_member) {
            $full_name = trim(($staff_member['firstname'] ?? '') . ' ' . ($staff_member['lastname'] ?? ''));
            $email = $staff_member['email'] ?? '';

            if ($full_name === '' && $email !== '') {
                $full_name = $email;
            }

            if ($full_name === '') {
                $full_name = _flexacademy_lang('student');
            }

            if ($email !== '') {
                $full_name .= ' (' . $email . ')';
            }

            $enrollment_students[] = [
                'id'   => 'staff:' . $staff_member['staffid'],
                'name' => $staff_prefix . ' — ' . $full_name,
            ];
        }

        usort($enrollment_students, static function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        $vm['title'] = _flexacademy_lang('enrollments');
        $vm['enrollments'] = $enrollments;
        $vm['stats'] = $stats;
        $vm['enrollment_courses'] = $enrollment_courses;
        $vm['enrollment_students'] = $enrollment_students;
        
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('admin/enrollments', $vm);
    }

    public function enroll_student()
    {
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('Flexacademy');
        }

        if (!$this->input->post()) {
            redirect(admin_url('flexacademy/enrollments'));
        }

        $this->load->model('clients_model');
        $this->load->model('staff_model');

        $course_id = (int) $this->input->post('course_id');
        $student_reference = $this->input->post('student_reference');

        if (!$course_id || empty($student_reference)) {
            set_alert('danger', _flexacademy_lang('enrollment-fields-required'));
            redirect(admin_url('flexacademy/enrollments'));
        }

        if (strpos($student_reference, ':') === false) {
            set_alert('danger', _flexacademy_lang('invalid-student-selection'));
            redirect(admin_url('flexacademy/enrollments'));
        }

        [$student_type, $student_id_value] = explode(':', $student_reference, 2);
        $student_type = trim($student_type);
        $student_id = (int) $student_id_value;

        if ($student_id <= 0 || !in_array($student_type, ['client', 'staff'], true)) {
            set_alert('danger', _flexacademy_lang('invalid-student-selection'));
            redirect(admin_url('flexacademy/enrollments'));
        }

        $course = $this->Flexacademy_courses_model->get(['id' => $course_id]);

        if (!$course) {
            set_alert('danger', _flexacademy_lang('course-not-found'));
            redirect(admin_url('flexacademy/enrollments'));
        }

        $course_access = $course['access'] ?? 'everyone';
        $enrollment_id = false;
        $log_student_reference = '';

        if ($student_type === 'client') {
            if (!in_array($course_access, ['clients', 'everyone'], true)) {
                set_alert('danger', _flexacademy_lang('course-not-available-for-clients'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $contact = $this->clients_model->get_contact($student_id);

            if (!$contact) {
                set_alert('danger', _flexacademy_lang('contact-not-found'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $existing_enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course_id, $student_id, 'client');

            if ($existing_enrollment) {
                set_alert('warning', _flexacademy_lang('already-enrolled'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $enrollment_id = $this->Flexacademy_enrollments_model->enroll_student($course, $student_id, 'client', []);
            $log_student_reference = 'Client Contact ID: ' . $student_id;

            if ($enrollment_id && function_exists('flexacademy_send_enrollment_email')) {
                flexacademy_send_enrollment_email($enrollment_id);
            }
        } elseif ($student_type === 'staff') {
            if (!in_array($course_access, ['staffs', 'everyone'], true)) {
                set_alert('danger', _flexacademy_lang('course-not-available-for-staff'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $staff_member = $this->staff_model->get($student_id);

            if (!$staff_member) {
                set_alert('danger', _flexacademy_lang('staff-not-found'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $existing_enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course_id, $student_id, 'staff');

            if ($existing_enrollment) {
                set_alert('warning', _flexacademy_lang('already-enrolled'));
                redirect(admin_url('flexacademy/enrollments'));
            }

            $enrollment_id = $this->Flexacademy_enrollments_model->enroll_student($course, $student_id, 'staff', []);
            $log_student_reference = 'Staff ID: ' . $student_id;
        }

        if (!$enrollment_id) {
            set_alert('danger', _flexacademy_lang('enroll-student-error'));
            redirect(admin_url('flexacademy/enrollments'));
        }

        log_activity('FlexAcademy: Admin enrolled ' . $student_type . ' [' . $log_student_reference . ', Course ID: ' . $course_id . ', Enrollment ID: ' . $enrollment_id . ']');

        set_alert('success', _flexacademy_lang('enroll-student-success'));
        redirect(admin_url('flexacademy/enrollments'));
    }

    public function staff_courses()
    {
        // Load models and helpers needed
        $this->load->helper('text');
        
        // Get current staff member ID
        $staff_id = get_staff_user_id();
        
        // Get courses accessible to staff (staffs or everyone) (move to model)
        $all_courses = $this->Flexacademy_courses_model->get_staff_accessible_courses();
        
        // Enrich courses with enrollment and progress data
        foreach ($all_courses as &$course) {
            $course['total_duration'] = flexacademy_get_course_total_duration($course['id']);
            $course['total_lessons'] = flexacademy_get_course_total_lessons($course['id']);
            $course['total_students'] = $this->Flexacademy_enrollments_model->get_course_enrollment_count($course['id']);
            
            // Check if staff member is enrolled
            $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course['id'], $staff_id, 'staff');
            $course['is_enrolled'] = !empty($enrollment);
            $course['enrollment_progress'] = 0;
            $course['enrollment_status'] = 'not_enrolled';
            
            if ($enrollment) {
                $course['enrollment_progress'] = isset($enrollment->progress) ? $enrollment->progress : 0;
                $course['enrollment_status'] = $enrollment->status;
                $course['enrollment_date'] = $enrollment->enrolled_at;
            }
        }
        
        $vm['title'] = _flexacademy_lang('staff-training');
        $vm['courses'] = $all_courses;
        $vm['staff_id'] = $staff_id;
        
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('staff/courses', $vm);
    }

    public function staff_enrollments()
    {
        // Load models needed
        $this->load->model('staff_model');
        
        // Get current staff member ID
        $staff_id = get_staff_user_id();
        
        // Get staff enrollments
        $enrollments = $this->Flexacademy_enrollments_model->get_staff_enrollments($staff_id);
        
        $course_ids = array_unique(array_column($enrollments, 'course_id'));
        $courses = $this->Flexacademy_courses_model->get_many($course_ids);
        $total_lessons_cache = [];
        $total_duration_cache = [];

        foreach ($enrollments as &$enrollment) {
            $course = $courses[$enrollment['course_id']] ?? null;
            $enrollment['course_title'] = $course['title'] ?? '';
            $enrollment['course_slug'] = $course['slug'] ?? '';
            $enrollment['course_image'] = $course['image'] ?? '';

            // Ensure progress is numeric (default to 0 if null)
            $enrollment['progress'] = isset($enrollment['progress']) && $enrollment['progress'] !== null ? (float)$enrollment['progress'] : 0;
            
            if (!isset($total_lessons_cache[$enrollment['course_id']])) {
                $total_lessons_cache[$enrollment['course_id']] = flexacademy_get_course_total_lessons($enrollment['course_id']);
            }

            if (!isset($total_duration_cache[$enrollment['course_id']])) {
                $total_duration_cache[$enrollment['course_id']] = flexacademy_get_course_total_duration($enrollment['course_id']);
            }

            $enrollment['total_lessons'] = $total_lessons_cache[$enrollment['course_id']];
            $enrollment['total_duration'] = $total_duration_cache[$enrollment['course_id']];
            
            // Get completed lessons count
            $enrollment['completed_lessons'] = $this->Flexacademy_lesson_progress_model->count_completed($enrollment['id']);
            
            // Calculate time spent (sum of all lesson progress)
            $enrollment['total_time_spent'] = $this->Flexacademy_lesson_progress_model->sum_time_spent($enrollment['id']);
            
            // Get last accessed lesson
            $last_entry = $this->Flexacademy_lesson_progress_model->get_last_accessed_entry($enrollment['id']);
            $enrollment['last_lesson'] = $last_entry ? ['last_accessed' => $last_entry['last_accessed'], 'lesson_id' => $last_entry['lesson_id']] : null;
        }
        unset($enrollment);
        
        // Get statistics
        $stats = [
            'total_enrollments' => count($enrollments),
            'active_enrollments' => count(array_filter($enrollments, function($e) { return $e['status'] === 'active'; })),
            'completed_enrollments' => count(array_filter($enrollments, function($e) { return $e['progress'] >= 100; })),
            'in_progress' => count(array_filter($enrollments, function($e) { return $e['progress'] > 0 && $e['progress'] < 100; })),
        ];
        
        $vm['title'] = _flexacademy_lang('my-enrollments');
        $vm['enrollments'] = $enrollments;
        $vm['stats'] = $stats;
        $vm['staff_id'] = $staff_id;
        
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('staff/enrollments', $vm);
    }

    public function staff_course_player($slug = null, $lesson_id = null)
    {
        if (!is_staff_logged_in()) {
            access_denied('Flexacademy');
        }
        

        $staff_id = get_staff_user_id();

        if (empty($slug)) {
            redirect(admin_url('flexacademy/staff_courses'));
        }

        $course = $this->Flexacademy_courses_model->get(['slug' => $slug]);
        if (!$course) {
            show_404();
        }

        $enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course['id'], $staff_id, 'staff');
        $is_creator = $this->Flexacademy_courses_model->is_creator($course['id'], $staff_id);
        
        if($is_creator && !$enrollment) {
            redirect(site_url('flexacademy/course_player/' . $slug));
        }

        if (!$enrollment) {
            show_404();
        }

        if ($enrollment && flexacademy_is_enrollment_expired($enrollment)) {
            set_alert('warning', _flexacademy_lang('course-access-expired'));
            redirect(admin_url('flexacademy/staff_courses'));
        }

        $sections = flexacademy_get_course_sections_with_lessons($course['id']);

        $lesson = null;
        $lesson_progress = null;

        $progress_entries = $this->Flexacademy_lesson_progress_model->get_entries_for_enrollment($enrollment->id);

        if ($lesson_id) {
            $lesson = $this->Flexacademy_lessons_model->get(['id' => $lesson_id]);
            if (!$lesson || $lesson['course_id'] != $course['id']) {
                redirect(admin_url('flexacademy/staff_course_player/' . $slug));
            }
            $lesson_progress = isset($progress_entries[$lesson_id]) ? (object) $progress_entries[$lesson_id] : null;
        } else {
            if (!empty($sections) && !empty($sections[0]['lessons'])) {
                $lesson = $sections[0]['lessons'][0];
                $lesson_id = $lesson['id'];
                $lesson_progress = isset($progress_entries[$lesson_id]) ? (object) $progress_entries[$lesson_id] : null;
            }
        }

        if ($lesson && $lesson['lesson_type'] === 'quiz' && !empty($lesson['quiz_id'])) {
            $this->load->model('flexacademy/Flexacademy_quiz_attempts_model');

            $quiz = $this->Flexacademy_quiz_model->get(['id' => $lesson['quiz_id']]);
            $questions = [];
            $attempts = [];
            $active_attempt = null;
            $attempt_count = 0;
            $best_score = null;
            $can_retake = false;
            $lesson_completed = false;

            if ($quiz) {
                $questions = $this->Flexacademy_quiz_questions_model->all(['quiz_id' => $quiz['id']]);
                $attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz(
                    $enrollment->id,
                    $quiz['id']
                );

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

                // Reload attempts after potentially abandoning any
                $attempts = $this->Flexacademy_quiz_attempts_model->get_by_enrollment_and_quiz(
                    $enrollment->id,
                    $quiz['id']
                );

                $active_attempt = $this->Flexacademy_quiz_attempts_model->get_latest_attempt_by_status(
                    $enrollment->id,
                    $quiz['id'],
                    'in_progress'
                );

                $completed_attempts = array_filter($attempts, function ($a) {
                    return $a['status'] === 'completed';
                });
                $attempt_count = count($completed_attempts);

                if (!empty($completed_attempts)) {
                    $scores = array_map(function ($a) {
                        return $a['score'];
                    }, $completed_attempts);
                    $best_score = max($scores);

                    if ($best_score >= $quiz['pass_marks'] && !$active_attempt) {
                        $lesson_completed = true;
                    }
                }

                $retake_limit = isset($quiz['retake_limit']) ? (int) $quiz['retake_limit'] : 0;
                $can_retake = $retake_limit === 0 ? true : ($attempt_count < $retake_limit);
            }

            $lesson['quiz_data'] = [
                'quiz' => $quiz,
                'questions' => $questions,
                'attempts' => $attempts,
                'active_attempt' => $active_attempt,
                'attempt_count' => $attempt_count,
                'best_score' => $best_score,
                'can_retake' => $can_retake,
                'lesson_completed' => $lesson_completed,
            ];
        }

        foreach ($sections as &$section) {
            foreach ($section['lessons'] as &$lesson_item) {
                $progress = isset($progress_entries[$lesson_item['id']]) ? (object) $progress_entries[$lesson_item['id']] : null;
                $lesson_item['progress'] = $progress;
                $lesson_item['is_current'] = ($lesson_item['id'] == $lesson_id);
            }
        }
        unset($section, $lesson_item);

        if ((float) $enrollment->progress >= 100 && $enrollment->status === 'completed' && empty($enrollment->certificate_id)) {
            $this->maybe_issue_certificate($enrollment);
            $enrollment = $this->Flexacademy_enrollments_model->get($enrollment->id);
        }

        $certificate = null;
        $certificate_url = null;
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
            $certificate_url = site_url('flexacademy/certificate/' . $certificate->certificate_number);
        }

        $data = [
            'title' => $lesson ? ($lesson['title'] . ' - ' . $course['title']) : ($course['title'] . ' - ' . _flexacademy_lang('course-player')),
            'show_selector' => false,
            'course' => $course,
            'lesson' => $lesson,
            'sections' => $sections,
            'lesson_progress' => $lesson_progress,
            'enrollment' => $enrollment,
            'player_base_url' => admin_url('flexacademy/staff_course_player/'),
            'back_url' => admin_url('flexacademy/staff_course_player'),
            'certificate' => $certificate,
            'certificate_url' => $certificate_url,
            'certificate_prefix' => get_option('flexacademy_certificate_prefix') ?: 'FLEX',
        ];

        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('staff/course_player', $data);
    }
 
    public function certificate($certificate_number)
    {

        $certificate = $this->Flexacademy_certificates_model->find_by_number($certificate_number);
        if (!$certificate) {
            show_404();
        }

        $enrollment = $this->Flexacademy_enrollments_model->get($certificate->enrollment_id);
        if (!$enrollment) {
            show_404();
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
        $primary_instructor_signature_url = '';
        if (!empty($instructors) && !empty($instructors[0]['signature'])) {
            $primary_instructor_signature_url = flexacademy_media_url($instructors[0]['signature']);
        }

        if ($enrollment->student_type === 'client') {
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact($enrollment->student_id);
            $student_name = $contact ? trim($contact->firstname . ' ' . $contact->lastname) : '';
        } else {
            $this->load->model('staff_model');
            $staff_member = $this->staff_model->get($enrollment->student_id);
            $student_name = $staff_member ? trim($staff_member->firstname . ' ' . $staff_member->lastname) : '';
        }

        $certificate_url = site_url('flexacademy/certificate/' . $certificate->certificate_number);
        $qr_code_url = "";

        $data = [
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
            'primary_instructor_signature_url' => $primary_instructor_signature_url,
            'company_name' => get_option('companyname'),
            'issuer_signature_url' => $this->build_setting_file_url('flexacademy_certificate_issuer_signature'),
            'certificate_prefix' => get_option('flexacademy_certificate_prefix') ?: 'FLEX',
            'total_lessons' => $total_lessons,
            'total_duration_formatted' => $total_duration_formatted,
            'total_duration_minutes' => $total_duration_minutes,
            'qr_code_url' => $qr_code_url,
        ];

        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-html2pdf', module_dir_url('flexacademy', 'assets/js/html2pdf.bundle.min.js'), 'admin', ['app-js']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('staff/certificate', $data);
        
    }
 
    private function maybe_issue_certificate($enrollment)
    {
        if (!$enrollment || (int) get_option('flexacademy_enable_certificates') !== 1) {
            return;
        }

        if ($enrollment->status !== 'completed') {
            return;
        }

        $this->Flexacademy_certificates_model->issue_certificate($enrollment);
    }

 
    public function course_details($id)
    {
        $vm['title'] = _flexacademy_lang('course-details');
        $course = $this->Flexacademy_courses_model->get(['id' => $id]);
        if (!$course) {
            set_alert('error', _flexacademy_lang('course_not_found'));
            redirect(admin_url('flexacademy/courses'));
        }
        $key = $this->input->get('key') ? $this->input->get('key') : 'curriculum';
        $course['sections'] = $this->Flexacademy_sections_model->all(['course_id' => $id]);
        // Load lessons for each section
        foreach ($course['sections'] as &$section) {
            $section['lessons'] = $this->Flexacademy_lessons_model->all(['section_id' => $section['id']]);
        }
        
        $vm['course'] = $course;
        $vm['key'] = $key;
        switch ($key) {
            case 'curriculum':
                $vm['content'] = $this->load->view('course/details/curriculum', $vm, true);
                break;
            case 'info':
                $vm['content'] = $this->load->view('course/details/info', $vm, true);
                break;
            case 'basic':
                $vm['content'] = $this->load->view('course/details/basic', $vm, true);
                break;
            case 'instructors':
                $vm['instructors'] = $this->Flexacademy_instructors_model->all(['course_id' => $id]);
                $vm['content'] = $this->load->view('course/details/instructors', $vm, true);
                break;
        }
        
        
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('course/details/index', $vm);
    }

    public function delete_course($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $course = $this->Flexacademy_courses_model->get(['id' => $id]);
        if (!$course) {
            set_alert('error', _flexacademy_lang('course_not_found'));
            redirect(admin_url('flexacademy/courses'));
        }
        $this->Flexacademy_courses_model->delete($id);
        set_alert('success', _flexacademy_lang('course_deleted_successfully'));
        redirect(admin_url('flexacademy/courses'));
    }

    /**
     * Create or Edit Course
     */
    public function course($id = 0)
    {
        if (!has_permission('flexacademy', '', 'create')) {
            access_denied('flexacademy');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            $data = [
                'title' => $post_data['title'],
                'slug' => slug_it($post_data['title']),
                'description' => $post_data['description'],
                'short_description' => $post_data['short_description'],
                'category_id' => $post_data['category_id'],
                'creator_id' => get_staff_user_id(),
                'price' => $post_data['price'],
                'discount_price' => $post_data['discount_price'],
                'pricing_type' => $post_data['pricing_type'],
                'difficulty_level' => $post_data['difficulty_level'],
                'status' => $post_data['status'],
                'language' => $post_data['language'],
                'expiry_type' => $post_data['expiry_type'],
                'expiry_period' => $post_data['expiry_value'],
                'privacy' => $post_data['privacy'],
                'access' => $post_data['access'],
            ];
            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                $data['image'] = $this->upload_file($_FILES['image']);
            }
            if ($id == 0) {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_courses_model->add($data);
                set_alert('success', _flexacademy_lang('course_created_successfully'));
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->Flexacademy_courses_model->update($data, $id);
                set_alert('success', _flexacademy_lang('course_updated_successfully'));
                //redirect to the course details page
                redirect(admin_url('flexacademy/course_details/' . $id . '?key=basic'));
            }
            redirect(admin_url('flexacademy/courses'));
        }
        $vm['title'] = $id == 0 ? _flexacademy_lang('create-course') : _flexacademy_lang('edit-course');
        $vm['course'] = $id == 0 ? [] : $this->Flexacademy_courses_model->get(['id' => $id]);
        $vm['action'] = $id == 0 ? admin_url('flexacademy/course') : admin_url('flexacademy/course/' . $id);
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('course/create-edit-course', $vm);
    }

    public function ajax()
    {
        $action = $this->input->post('action');
        switch ($action) {
            case 'delete_quiz_question':
                if(!has_permission('flexacademy', '', 'delete')){
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('permission_denied')]);
                    die();
                }
                $question_id = $this->input->post('question_id');
                $question = $this->Flexacademy_quiz_questions_model->get(['id' => $question_id]);
                if($question){
                    $this->Flexacademy_quiz_questions_model->delete($question_id);
                    echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('question_deleted_successfully')]);
                }else{
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('question_not_found')]);
                }
                die();
                break;
            case 'get_quiz_questions':
                $quiz_id = $this->input->post('quiz_id');
                $questions = $this->Flexacademy_quiz_questions_model->all(['quiz_id' => $quiz_id]);
                $view = $this->load->view('partials/questions/question-list', ['questions' => $questions, 'quiz_id' => $quiz_id], true);
                echo json_encode(['status' => 'success', 'html' => $view]);
                die();
                break;
            case 'get_quiz_results':
                $quiz_id = $this->input->post('quiz_id');
                $results = [];
                $view = $this->load->view('partials/quiz/quiz-results', ['results' => $results], true);
                echo json_encode(['status' => 'success', 'html' => $view]);
                die();
                break;
            case 'get_lessons_by_section':
                $section_id = $this->input->post('section_id');
                $lessons = $this->Flexacademy_lessons_model->all(['section_id' => $section_id]);
                $view = $this->load->view('partials/lesson/lesson-order-li', ['lessons' => $lessons], true);
                echo json_encode(['status' => 'success', 'html' => $view]);
                die();
                break;
            case 'update_actions_order':
                $lists = $this->input->post('lists');
                $type = $this->input->post('type');
                $i = 1;
                foreach ($lists as $list_id) {
                    if ($type == 'section') {
                        $this->Flexacademy_sections_model->update(['sort_order' => $i], $list_id);
                    } else if ($type == 'lesson') {
                        $this->Flexacademy_lessons_model->update(['sort_order' => $i], $list_id);
                    } else if ($type == 'category') {
                        $this->Flexacademy_categories_model->update(['sort_order' => $i], $list_id);
                    }
                    $i++;
                }
                echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('order_updated_successfully')]);
                die();
                break;
            case 'enroll_staff_free':
            case 'enroll_staff':
                
                $course_id = $this->input->post('course_id');
                $staff_id = get_staff_user_id();
                
                // Validate course exists
                $course = $this->Flexacademy_courses_model->get(['id' => $course_id]);
                if (!$course) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('course_not_found')]);
                    die();
                }
                
                // Check if already enrolled
                $existing_enrollment = $this->Flexacademy_enrollments_model->get_by_course_student($course_id, $staff_id, 'staff');
                if ($existing_enrollment) {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('already-enrolled')]);
                    die();
                }
                        
                $enrollment_id = $this->Flexacademy_enrollments_model->enroll_student(
                    $course,
                    $staff_id,
                    'staff',
                    [
                        'status' => 'active',
                        'payment_status' => 'paid'
                    ]
                );

                if ($enrollment_id) {
                    log_activity('Staff enrolled in course [Course ID: ' . $course_id . ', Staff ID: ' . $staff_id . ']');
                    echo json_encode(['status' => 'success', 'message' => _flexacademy_lang('successfully-enrolled')]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => _flexacademy_lang('enrollment-failed')]);
                }
                die();
                break;
        }
    }


    //categories
    public function categories()
    {
        $data['title'] = _flexacademy_lang('categories');
        $data['categories'] = $this->Flexacademy_categories_model->all();
        $this->app_css->add('flexacademy-css', module_dir_url('flexacademy', 'assets/css/flexacademy.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexacademy-js', module_dir_url('flexacademy', 'assets/js/flexacademy.js'), 'admin', ['app-js']);
        $this->load->view('categories', $data);
    }

    public function create_edit_category()
    {
        if ($this->input->post()) {
            $data = [
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status') == 1 ? 'active' : 'inactive',
                'parent_id' => $this->input->post('parent_id'),
            ];
            // Validate data
            $this->form_validation->set_rules('title', _flexacademy_lang('category-name'), 'trim|required|max_length[255]');
            $this->form_validation->set_rules('description', _flexacademy_lang('description'), 'trim');
            $this->form_validation->set_rules('status', _flexacademy_lang('status'), 'required|in_list[0,1]');
            $this->form_validation->set_rules('parent_id', _flexacademy_lang('parent-category'), 'trim|numeric');

            if ($this->form_validation->run() == false) {
                set_alert('danger', _flexacademy_lang('category_creation_failed'));
                redirect(admin_url('flexacademy/categories'));
            }

            if ($this->input->post('id') == 0) {
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->Flexacademy_categories_model->add($data)) {
                    set_alert('success', _flexacademy_lang('category_created_successfully'));
                } else {
                    set_alert('danger', _flexacademy_lang('category_creation_failed'));
                }
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->Flexacademy_categories_model->update($data, $this->input->post('id'))) {
                    set_alert('success', _flexacademy_lang('category_updated_successfully'));
                } else {
                    set_alert('danger', _flexacademy_lang('category_update_failed'));
                }
            }
            redirect(admin_url('flexacademy/categories'));
        }
        return redirect(admin_url('flexacademy/categories'));
    }

    public function delete_category($id)
    {
        if (!has_permission('flexacademy', '', 'delete')) {
            access_denied('Flexacademy');
        }
        $category = $this->Flexacademy_categories_model->get(['id' => $id]);
        if (!$category) {
            set_alert('error', _flexacademy_lang('category_not_found'));
            redirect(admin_url('flexacademy/categories'));
        }
        if ($this->Flexacademy_categories_model->delete($id)) {
            set_alert('success', _flexacademy_lang('category_deleted_successfully'));
        } else {
            set_alert('error', _flexacademy_lang('category_deletion_failed'));
        }
        redirect(admin_url('flexacademy/categories'));
    }

    private function handle_settings_file_upload($option_name, $file_input, $remove_flag)
    {
        if ($this->input->post($remove_flag)) {
            $this->delete_setting_file($option_name);
        }

        if (isset($_FILES[$file_input]) && !empty($_FILES[$file_input]['name'])) {
            $uploaded = $this->upload_file($_FILES[$file_input], true, 'settings/');
            if ($uploaded) {
                $this->delete_setting_file($option_name);
                update_option($option_name, $uploaded);
            }
        }
    }

    private function build_setting_file_url($option_name)
    {
        $file = get_option($option_name);
        if (!$file) {
            return '';
        }
        return flexacademy_media_url($file);
    }

    private function delete_setting_file($option_name)
    {
        $existing = get_option($option_name);
        if (!$existing) {
            return;
        }

        $path = FCPATH . FLEXACADEMY_FOLDER . $existing;
        if (file_exists($path)) {
            @unlink($path);
        }

        update_option($option_name, '');
    }

    private function upload_file($file, $is_image = true,$path = "")
    {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($is_image) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                return '';
            }
        }
        $outputFileSaveableName = md5(time() . $file['name']) . '.' . $fileExtension;
        //create directory if not exists
        if ($path != '' && !is_dir(FLEXACADEMY_FOLDER . $path)) {
            mkdir(FLEXACADEMY_FOLDER . $path, 0777, true);
        }
        $outputFileName = FLEXACADEMY_FOLDER . $path . $outputFileSaveableName;
        if (move_uploaded_file($file['tmp_name'], $outputFileName)) {
            return $path . $outputFileSaveableName;
        }
        return '';
    }
}
