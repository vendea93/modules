<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * Create email templates for FlexAcademy
     */
    public function create_email_templates()
    {
        // Email template for course enrollment confirmation
        create_email_template(
            'Course Enrollment Confirmed - {course_name}',
            'Hi {customer_name}, <br /><br /> 
            Your enrollment in <strong>{course_name}</strong> has been confirmed!
            <br /><br />
            <strong>Course:</strong> {course_name} <br />
            <strong>Enrollment Date:</strong> {enrollment_date} <br />
            <strong>Total Lessons:</strong> {total_lessons} <br />
            <strong>Estimated Duration:</strong> {course_duration} <br />
            <br />
            <a href="{course_url}">Start Learning Now</a>
            <br /><br />
            We\'re excited to have you join this course! You can start learning immediately by clicking the link above.
            <br /><br />
            Regards.',
            'client',
            'Course Enrollment Confirmed',
            'flexacademy-enrollment-confirmed'
        );

        // Email template for payment received
        create_email_template(
            'Payment Received - Course Access Granted',
            'Hi {customer_name}, <br /><br /> 
            Your payment has been received and your course access has been granted!
            <br /><br />
            <strong>Invoice Number:</strong> {invoice_number} <br />
            <strong>Amount Paid:</strong> {amount_paid} <br />
            <strong>Payment Date:</strong> {payment_date} <br />
            <strong>Courses Unlocked:</strong> <br />
            {enrolled_courses_list}
            <br /><br />
            <a href="{my_courses_url}">Go to My Courses</a>
            <br /><br />
            Thank you for your payment! All your courses are now accessible.
            <br /><br />
            Regards.',
            'client',
            'Payment Received - Courses Unlocked',
            'flexacademy-payment-received'
        );

        // Email template for pending payment reminder
        create_email_template(
            'Complete Your Course Payment - {course_name}',
            'Hi {customer_name}, <br /><br /> 
            You have pending course enrollments waiting for payment.
            <br /><br />
            <strong>Invoice Number:</strong> {invoice_number} <br />
            <strong>Total Amount:</strong> {total_amount} <br />
            <strong>Due Date:</strong> {due_date} <br />
            <strong>Pending Courses:</strong> <br />
            {pending_courses_list}
            <br /><br />
            <a href="{invoice_url}">Complete Payment</a>
            <br /><br />
            Complete your payment to gain instant access to these courses.
            <br /><br />
            Regards.',
            'client',
            'Payment Reminder - Course Enrollment',
            'flexacademy-payment-reminder'
        );

        // Email template for admin notification of new enrollment
        create_email_template(
            'New Course Enrollment - {course_name}',
            'A new student has enrolled in a course.
            <br /><br />
            <strong>Student:</strong> {customer_name} ({customer_email}) <br />
            <strong>Course:</strong> {course_name} <br />
            <strong>Enrollment Date:</strong> {enrollment_date} <br />
            <strong>Payment Status:</strong> {payment_status} <br />
            <strong>Amount:</strong> {amount_paid} <br />
            <br />
            <a href="{admin_enrollment_url}">View Enrollment</a>
            <br /><br />
            Regards.',
            'staff',
            'New Course Enrollment',
            'flexacademy-new-enrollment-admin'
        );
    }

    /**
     * Send enrollment confirmation email to customer
     * @param array $enrollment_data
     * @return bool
     */
    public function send_enrollment_confirmation($enrollment_data)
    {
        $customer_email = $enrollment_data['customer_email'];
        $template_name = 'Flexacademy_enrollment_confirmed';
        
        try {
            $template = mail_template($template_name, "flexacademy", $customer_email, $enrollment_data);
            return $template->send();
        } catch (Exception $e) {
            log_activity('FlexAcademy: Failed to send enrollment confirmation email - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment received email to customer
     * @param array $payment_data
     * @return bool
     */
    public function send_payment_received($payment_data)
    {
        $customer_email = $payment_data['customer_email'];
        $template_name = 'Flexacademy_payment_received';
        
        try {
            $template = mail_template($template_name, "flexacademy", $customer_email, $payment_data);
            return $template->send();
        } catch (Exception $e) {
            log_activity('FlexAcademy: Failed to send payment received email - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment reminder email to customer
     * @param array $reminder_data
     * @return bool
     */
    public function send_payment_reminder($reminder_data)
    {
        $customer_email = $reminder_data['customer_email'];
        $template_name = 'Flexacademy_payment_reminder';
        
        try {
            $template = mail_template($template_name, "flexacademy", $customer_email, $reminder_data);
            return $template->send();
        } catch (Exception $e) {
            log_activity('FlexAcademy: Failed to send payment reminder email - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new enrollment notification to admin/staff
     * @param array $enrollment_data
     * @return bool
     */
    public function send_admin_enrollment_notification($enrollment_data)
    {
        // Get admin emails
        $this->ci->load->model('staff_model');
        $admins = $this->ci->staff_model->get('', ['active' => 1, 'admin' => 1]);
        
        $template_name = 'Flexacademy_new_enrollment_admin';
        $sent = false;
        
        foreach ($admins as $admin) {
            try {
                $template = mail_template($template_name, "flexacademy", $admin['email'], $enrollment_data);
                if ($template->send()) {
                    $sent = true;
                }
            } catch (Exception $e) {
                log_activity('FlexAcademy: Failed to send admin notification email - ' . $e->getMessage());
            }
        }
        
        return $sent;
    }

    /**
     * Send email based on type
     * @param array $data
     * @param string $type
     * @return bool
     */
    public function send_email($data, $type)
    {
        switch ($type) {
            case 'enrollment_confirmed':
                return $this->send_enrollment_confirmation($data);
                
            case 'payment_received':
                return $this->send_payment_received($data);
                
            case 'payment_reminder':
                return $this->send_payment_reminder($data);
                
            case 'admin_notification':
                return $this->send_admin_enrollment_notification($data);
                
            default:
                return false;
        }
    }
}

