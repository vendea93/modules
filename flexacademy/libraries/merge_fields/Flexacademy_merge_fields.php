<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Customer Name',
                'key'       => '{customer_name}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-payment-received',
                    'flexacademy-payment-reminder',
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Customer Email',
                'key'       => '{customer_email}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-payment-received',
                    'flexacademy-payment-reminder',
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Course Name',
                'key'       => '{course_name}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-payment-received',
                    'flexacademy-payment-reminder',
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Course URL',
                'key'       => '{course_url}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-payment-received'
                ],
            ],
            [
                'name'      => 'My Courses URL',
                'key'       => '{my_courses_url}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-payment-received'
                ],
            ],
            [
                'name'      => 'Enrollment Date',
                'key'       => '{enrollment_date}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed',
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Total Lessons',
                'key'       => '{total_lessons}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed'
                ],
            ],
            [
                'name'      => 'Course Duration',
                'key'       => '{course_duration}',
                'available' => [],
                'templates' => [
                    'flexacademy-enrollment-confirmed'
                ],
            ],
            [
                'name'      => 'Invoice Number',
                'key'       => '{invoice_number}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-received',
                    'flexacademy-payment-reminder'
                ],
            ],
            [
                'name'      => 'Invoice URL',
                'key'       => '{invoice_url}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-reminder'
                ],
            ],
            [
                'name'      => 'Amount Paid',
                'key'       => '{amount_paid}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-received',
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Total Amount',
                'key'       => '{total_amount}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-reminder'
                ],
            ],
            [
                'name'      => 'Payment Date',
                'key'       => '{payment_date}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-received'
                ],
            ],
            [
                'name'      => 'Payment Status',
                'key'       => '{payment_status}',
                'available' => [],
                'templates' => [
                    'flexacademy-new-enrollment-admin'
                ],
            ],
            [
                'name'      => 'Due Date',
                'key'       => '{due_date}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-reminder'
                ],
            ],
            [
                'name'      => 'Enrolled Courses List',
                'key'       => '{enrolled_courses_list}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-received'
                ],
            ],
            [
                'name'      => 'Pending Courses List',
                'key'       => '{pending_courses_list}',
                'available' => [],
                'templates' => [
                    'flexacademy-payment-reminder'
                ],
            ],
            [
                'name'      => 'Admin Enrollment URL',
                'key'       => '{admin_enrollment_url}',
                'available' => [],
                'templates' => [
                    'flexacademy-new-enrollment-admin'
                ],
            ],
        ];
    }

    /**
     * Format merge fields for email
     * @param array $data
     * @return array
     */
    public function format($data)
    {
        $fields = [
            '{customer_name}'            => $data['customer_name'] ?? '',
            '{customer_email}'           => $data['customer_email'] ?? '',
            '{course_name}'              => $data['course_name'] ?? '',
            '{course_url}'               => $data['course_url'] ?? '',
            '{my_courses_url}'           => $data['my_courses_url'] ?? site_url('flexacademy/my-courses'),
            '{enrollment_date}'          => $data['enrollment_date'] ?? '',
            '{total_lessons}'            => $data['total_lessons'] ?? '',
            '{course_duration}'          => $data['course_duration'] ?? '',
            '{invoice_number}'           => $data['invoice_number'] ?? '',
            '{invoice_url}'              => $data['invoice_url'] ?? '',
            '{amount_paid}'              => $data['amount_paid'] ?? '',
            '{total_amount}'             => $data['total_amount'] ?? '',
            '{payment_date}'             => $data['payment_date'] ?? '',
            '{payment_status}'           => $data['payment_status'] ?? '',
            '{due_date}'                 => $data['due_date'] ?? '',
            '{enrolled_courses_list}'    => $data['enrolled_courses_list'] ?? '',
            '{pending_courses_list}'     => $data['pending_courses_list'] ?? '',
            '{admin_enrollment_url}'     => $data['admin_enrollment_url'] ?? '',
        ];

        return hooks()->apply_filters('flexacademy_merge_fields', $fields, [
            'data' => $data,
        ]);
    }
}

