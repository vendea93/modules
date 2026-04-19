<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Create necessary database tables for the Hotel Management System module
$CI = &get_instance();

// Add module permissions
//$CI->db->query("INSERT INTO `" . db_prefix() . "permissions` (`name`, `shortname`) VALUES
//('Hotel Management System', 'hotel_management_system'),
//('HMS Landlords', 'HMS_LANDLORD'),
//('HMS Properties', 'HMS_PROPERTY'),
//('HMS Rooms', 'HMS_ROOM'),
//('HMS Services', 'HMS_SERVICE'),
//('HMS Bookings', 'HMS_BOOKING')
//");

// If we directly execute the SQL, we should check if table exists first to avoid errors
// Landlords table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_landlords'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_landlords` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `company` varchar(100) DEFAULT NULL,
      `contact_person` varchar(100) DEFAULT NULL,
      `email` varchar(100) NOT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `address` varchar(250) DEFAULT NULL,
      `city` varchar(100) DEFAULT NULL,
      `state` varchar(100) DEFAULT NULL,
      `postal_code` varchar(20) DEFAULT NULL,
      `country` varchar(100) DEFAULT NULL,
      `tax_id` varchar(100) DEFAULT NULL,
      `payment_details` text DEFAULT NULL,
      `commission_rate` decimal(5,2) DEFAULT NULL, 
      `contact_notes` text DEFAULT NULL,
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `modified_by` int(11) DEFAULT NULL,
      `active` tinyint(1) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`),
      KEY `email` (`email`),
      KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Properties table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_properties'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_properties` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `landlord_id` int(11) NOT NULL,
      `name` varchar(150) NOT NULL,
      `address` varchar(250) NOT NULL,
      `city` varchar(100) NOT NULL,
      `state` varchar(100) DEFAULT NULL,
      `postal_code` varchar(20) DEFAULT NULL,
      `country` varchar(100) NOT NULL,
      `property_type` varchar(50) DEFAULT NULL,
      `description` text DEFAULT NULL,
      `amenities` text DEFAULT NULL,
      `rules` text DEFAULT NULL,
      `check_in_time` time DEFAULT NULL,
      `check_out_time` time DEFAULT NULL,
      `status` varchar(20) NOT NULL DEFAULT 'active',
      `featured` tinyint(1) NOT NULL DEFAULT 0,
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `modified_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `landlord_id` (`landlord_id`),
      CONSTRAINT `" . db_prefix() . "hms_property_landlord_id` FOREIGN KEY (`landlord_id`) REFERENCES `" . db_prefix() . "hms_landlords` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Rooms table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_rooms'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_rooms` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `property_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `room_type` varchar(50) DEFAULT NULL,
      `description` text DEFAULT NULL,
      `capacity` int(11) DEFAULT 1,
      `bed_type` varchar(50) DEFAULT NULL,
      `num_beds` int(11) DEFAULT 1,
      `room_size` decimal(10,2) DEFAULT NULL,
      `room_size_unit` varchar(10) DEFAULT 'sqm',
      `amenities` text DEFAULT NULL,
      `price_per_night` decimal(15,2) NOT NULL,
      `cleaning_fee` decimal(15,2) DEFAULT 0.00,
      `tax_rate` decimal(5,2) DEFAULT NULL,
      `status` varchar(20) NOT NULL DEFAULT 'available',
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `modified_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `property_id` (`property_id`),
      CONSTRAINT `" . db_prefix() . "hms_room_property_id` FOREIGN KEY (`property_id`) REFERENCES `" . db_prefix() . "hms_properties` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Services table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_services'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_services` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `description` text DEFAULT NULL,
      `service_type` varchar(50) NOT NULL,
      `price` decimal(15,2) DEFAULT 0.00,
      `duration_minutes` int(11) DEFAULT NULL,
      `status` varchar(20) NOT NULL DEFAULT 'active',
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `modified_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Service Assignments (Staff shifts) table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_service_assignments'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_service_assignments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `room_id` int(11) NOT NULL,
      `service_id` int(11) NOT NULL,
      `day_of_week` tinyint(1) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
      `start_time` time NOT NULL,
      `end_time` time NOT NULL,
      `status` varchar(20) NOT NULL DEFAULT 'active',
      `notes` text DEFAULT NULL,
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) NOT NULL,
      `modified_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `room_id` (`room_id`),
      KEY `service_id` (`service_id`),
      CONSTRAINT `" . db_prefix() . "hms_assignment_room_id` FOREIGN KEY (`room_id`) REFERENCES `" . db_prefix() . "hms_rooms` (`id`) ON DELETE CASCADE,
      CONSTRAINT `" . db_prefix() . "hms_assignment_service_id` FOREIGN KEY (`service_id`) REFERENCES `" . db_prefix() . "hms_services` (`id`) ON DELETE CASCADE,
      CONSTRAINT `" . db_prefix() . "hms_assignment_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `" . db_prefix() . "staff` (`staffid`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Bookings table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_bookings'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_bookings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `room_id` int(11) NOT NULL,
      `client_id` int(11) DEFAULT NULL,
      `invoice_id` int(11) DEFAULT NULL,
      `booking_reference` varchar(50) NOT NULL,
      `guest_name` varchar(100) NOT NULL,
      `guest_email` varchar(100) NOT NULL,
      `guest_phone` varchar(50) DEFAULT NULL,
      `check_in_date` date NOT NULL,
      `check_out_date` date NOT NULL,
      `adults` int(11) NOT NULL DEFAULT 1,
      `children` int(11) NOT NULL DEFAULT 0,
      `special_requests` text DEFAULT NULL,
      `total_nights` int(11) NOT NULL,
      `room_price` decimal(15,2) NOT NULL,
      `cleaning_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
      `additional_services` decimal(15,2) NOT NULL DEFAULT 0.00,
      `taxes` decimal(15,2) NOT NULL DEFAULT 0.00,
      `total_amount` decimal(15,2) NOT NULL,
      `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
      `booking_status` varchar(20) NOT NULL DEFAULT 'confirmed',
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      `created_by` int(11) DEFAULT NULL,
      `modified_by` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `booking_reference` (`booking_reference`),
      KEY `room_id` (`room_id`),
      KEY `client_id` (`client_id`),
      KEY `invoice_id` (`invoice_id`),
      KEY `check_in_date` (`check_in_date`),
      KEY `check_out_date` (`check_out_date`),
      CONSTRAINT `" . db_prefix() . "hms_booking_room_id` FOREIGN KEY (`room_id`) REFERENCES `" . db_prefix() . "hms_rooms` (`id`) ON DELETE CASCADE,
      CONSTRAINT `" . db_prefix() . "hms_booking_client_id` FOREIGN KEY (`client_id`) REFERENCES `" . db_prefix() . "clients` (`userid`) ON DELETE SET NULL,
      CONSTRAINT `" . db_prefix() . "hms_booking_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `" . db_prefix() . "invoices` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Booking Services table (for additional services booked)
if ( ! $CI->db->table_exists(db_prefix() . 'hms_booking_services'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_booking_services` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `booking_id` int(11) NOT NULL,
      `service_id` int(11) NOT NULL,
      `staff_id` int(11) DEFAULT NULL,
      `service_date` date NOT NULL,
      `service_time` time DEFAULT NULL,
      `quantity` int(11) NOT NULL DEFAULT 1,
      `price` decimal(15,2) NOT NULL,
      `total` decimal(15,2) NOT NULL,
      `status` varchar(20) NOT NULL DEFAULT 'pending',
      `notes` text DEFAULT NULL,
      `datecreated` datetime NOT NULL,
      `datemodified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `booking_id` (`booking_id`),
      KEY `service_id` (`service_id`),
      KEY `staff_id` (`staff_id`),
      CONSTRAINT `" . db_prefix() . "hms_booking_service_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `" . db_prefix() . "hms_bookings` (`id`) ON DELETE CASCADE,
      CONSTRAINT `" . db_prefix() . "hms_booking_service_service_id` FOREIGN KEY (`service_id`) REFERENCES `" . db_prefix() . "hms_services` (`id`) ON DELETE CASCADE,
      CONSTRAINT `" . db_prefix() . "hms_booking_service_staff_id` FOREIGN KEY (`staff_id`) REFERENCES `" . db_prefix() . "staff` (`staffid`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Room Images table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_room_images'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_room_images` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `room_id` int(11) NOT NULL,
      `file_name` varchar(191) NOT NULL,
      `file_type` varchar(50) DEFAULT NULL,
      `path` varchar(255) NOT NULL,
      `is_featured` tinyint(1) NOT NULL DEFAULT 0,
      `sort_order` int(11) NOT NULL DEFAULT 0,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `room_id` (`room_id`),
      CONSTRAINT `" . db_prefix() . "hms_room_image_room_id` FOREIGN KEY (`room_id`) REFERENCES `" . db_prefix() . "hms_rooms` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Property Images table
if ( ! $CI->db->table_exists(db_prefix() . 'hms_property_images'))
{
	$CI->db->query("CREATE TABLE `" . db_prefix() . "hms_property_images` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `property_id` int(11) NOT NULL,
      `file_name` varchar(191) NOT NULL,
      `file_type` varchar(50) DEFAULT NULL,
      `path` varchar(255) NOT NULL,
      `is_featured` tinyint(1) NOT NULL DEFAULT 0,
      `sort_order` int(11) NOT NULL DEFAULT 0,
      `datecreated` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `property_id` (`property_id`),
      CONSTRAINT `" . db_prefix() . "hms_property_image_property_id` FOREIGN KEY (`property_id`) REFERENCES `" . db_prefix() . "hms_properties` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// Add a specific options table for our module settings
// Or use the existing options table
$CI->db->query("INSERT INTO `" . db_prefix() . "options` (`name`, `value`, `autoload`) VALUES 
('hotel_management_system_enabled', '1', 1),
('hotel_management_system_booking_enabled', '1', 1),
('hotel_management_system_send_invoice_email', '1', 1),
('hotel_management_system_default_tax_rate', '10', 1),
('hotel_management_system_terms_and_conditions', 'Standard terms and conditions for hotel bookings.', 1)
");

// Create necessary directories for file uploads
if ( ! file_exists(HMS_MODULE_UPLOAD_FOLDER))
{
	mkdir(HMS_MODULE_UPLOAD_FOLDER, 0755, TRUE);
}

if ( ! file_exists(HMS_MODULE_UPLOAD_FOLDER . '/properties'))
{
	mkdir(HMS_MODULE_UPLOAD_FOLDER . '/properties', 0755);
}

if ( ! file_exists(HMS_MODULE_UPLOAD_FOLDER . '/rooms'))
{
	mkdir(HMS_MODULE_UPLOAD_FOLDER . '/rooms', 0755);
}