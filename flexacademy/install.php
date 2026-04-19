<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Create database tables
$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_courses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text,
    `faq` text,
    `requirements` text,
    `outcomes` text,
    `short_description` varchar(500),
    `image` varchar(255),
    `category_id` int(11) DEFAULT NULL,
    `creator_id` int(11) NOT NULL,
    `price` decimal(10,2) DEFAULT 0.00,
    `discount_price` decimal(10,2) DEFAULT 0.00,
    `pricing_type` enum('free','paid') DEFAULT 'free',
    `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
    `status` varchar(100),
    `language` varchar(100),
    `max_students` int(11) DEFAULT 0,
    `expiry_type` varchar(100),
    `expiry_period` int(11) DEFAULT 0,
    `privacy` varchar(100),
    `access` enum('clients','staffs','everyone') DEFAULT 'everyone',
    `created_at` datetime NOT NULL,   
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `creator_id` (`creator_id`),
    KEY `category_id` (`category_id`),
    KEY `status` (`status`),
    KEY `access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text,
    `parent_id` int(11) DEFAULT NULL,
    `sort_order` int(11) DEFAULT 0,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_sections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `course_id` int(11) NOT NULL,
    `sort_order` int(11) DEFAULT 0,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_lessons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `section_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `lesson_type` varchar(100),
    `text_lesson` longtext,
    `summary` longtext,
    `file_source` varchar(255),
    `file_url` text,
    `file_path` text,
    `duration` int(11) DEFAULT 0,
    `sort_order` int(11) DEFAULT 0,
    `is_free` tinyint(1) DEFAULT 0,
    `quiz_id` int(11) DEFAULT 0,
    `status` varchar(100),
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    KEY `section_id` (`section_id`),
    KEY `sort_order` (`sort_order`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_quizzes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `course_id` int(11) NOT NULL,
    `section_id` int(11) NOT NULL,
    `total_marks` int(11) NOT NULL,
    `pass_marks` int(11) NOT NULL,
    `retake_limit` int(11) NOT NULL,
    `time_limit` int(11) NOT NULL,
    `description` text,
    `status` enum('draft','published') DEFAULT 'draft',
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    KEY `section_id` (`section_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_quiz_questions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `quiz_id` int(11) NOT NULL,
    `question` text NOT NULL,
    `question_type` varchar(100),
    `options` text,
    `correct_answer` text,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    KEY `quiz_id` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

//instructor
$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_instructors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `image` varchar(255) NOT NULL,
    `signature` varchar(255) DEFAULT NULL,
    `email` varchar(255) NOT NULL,
    `job_title` varchar(255) NOT NULL,
    `bio` text NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `course_id` (`course_id`),
    KEY `name` (`name`),
    KEY `email` (`email`),
    KEY `job_title` (`job_title`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

if (!$CI->db->field_exists('signature', db_prefix() . 'flexacademy_instructors')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "flexacademy_instructors` ADD `signature` varchar(255) DEFAULT NULL AFTER `image`;");
}

// Enrollment and Progress Tracking Tables
$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_enrollments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `course_id` int(11) NOT NULL,
    `student_id` int(11) NOT NULL,
    `student_type` enum('client','staff') NOT NULL DEFAULT 'client',
    `enrolled_at` datetime NOT NULL,
    `enrollment_date` datetime DEFAULT NULL,
    `completion_date` datetime DEFAULT NULL,
    `expires_at` datetime DEFAULT NULL,
    `progress` decimal(5,2) DEFAULT 0.00,
    `status` enum('enrolled','in_progress','completed','dropped','active') DEFAULT 'active',
    `grade` decimal(5,2) DEFAULT NULL,
    `certificate_id` int(11) DEFAULT NULL,
    `invoice_id` int(11) DEFAULT NULL,
    `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
    `amount_paid` decimal(10,2) DEFAULT 0.00,
    `payment_date` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `course_student` (`course_id`,`student_id`,`student_type`),
    KEY `student_id` (`student_id`),
    KEY `student_type` (`student_type`),
    KEY `status` (`status`),
    KEY `invoice_id` (`invoice_id`),
    KEY `payment_status` (`payment_status`),
    KEY `enrolled_at` (`enrolled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

if (!$CI->db->field_exists('expires_at', db_prefix() . 'flexacademy_enrollments')) {
    $CI->db->query("ALTER TABLE `" . db_prefix() . "flexacademy_enrollments` ADD `expires_at` datetime DEFAULT NULL AFTER `completion_date`;");
}

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_lesson_progress` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `enrollment_id` int(11) NOT NULL,
    `lesson_id` int(11) NOT NULL,
    `completion_date` datetime DEFAULT NULL,
    `time_spent` int(11) DEFAULT 0,
    `score` decimal(5,2) DEFAULT NULL,
    `notes` text,
    `last_accessed` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `enrollment_lesson` (`enrollment_id`,`lesson_id`),
    KEY `lesson_id` (`lesson_id`),
    KEY `last_accessed` (`last_accessed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_quiz_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `quiz_id` int(11) NOT NULL,
    `enrollment_id` int(11) NOT NULL,
    `student_id` int(11) NOT NULL,
    `score` decimal(5,2) DEFAULT NULL,
    `answers` text DEFAULT NULL COMMENT 'JSON encoded answers',
    `time_taken` int(11) DEFAULT 0,
    `attempt_number` int(11) DEFAULT 1,
    `status` varchar(50) NOT NULL DEFAULT 'in_progress' COMMENT 'in_progress, completed, abandoned',
    `start_time` datetime NOT NULL,
    `end_time` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `quiz_id` (`quiz_id`),
    KEY `enrollment_id` (`enrollment_id`),
    KEY `student_id` (`student_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_certificates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `enrollment_id` int(11) NOT NULL,
    `certificate_number` varchar(50) NOT NULL,
    `issue_date` datetime NOT NULL,
    `expiry_date` datetime DEFAULT NULL,
    `status` enum('active','expired','revoked') DEFAULT 'active',
    `template_id` int(11) DEFAULT 1,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `certificate_number` (`certificate_number`),
    KEY `enrollment_id` (`enrollment_id`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_certificate_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `template_html` longtext NOT NULL,
    `template_css` text,
    `is_default` tinyint(1) DEFAULT 0,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "flexacademy_orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_number` varchar(50) NOT NULL,
    `client_id` int(11) NOT NULL,
    `contact_id` int(11) NOT NULL,
    `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `currency` varchar(10) NOT NULL,
    `status` enum('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
    `invoice_id` int(11) DEFAULT NULL,
    `order_items` longtext,
    `order_date` datetime NOT NULL,
    `notes` text,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `client_id` (`client_id`),
    KEY `contact_id` (`contact_id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `status` (`status`),
    KEY `order_date` (`order_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

// Add module settings

add_option('flexacademy_enable_progress_tracking', 1);
add_option('flexacademy_enable_enrollment_approval', 0);
add_option('flexacademy_default_course_image', '');
add_option('flexacademy_certificate_logo', '');
add_option('flexacademy_certificate_prefix', 'FLEX');
add_option('flexacademy_certificate_issuer_signature', '');

flexacademy_create_storage_directory();
flexacademy_create_email_templates();
