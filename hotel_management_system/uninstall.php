<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Clean up database tables for the Hotel Management System module
$CI = &get_instance();

// Drop module tables in the correct order to handle foreign key constraints
$tables = [
	'hms_booking_services',
	'hms_bookings',
	'hms_service_assignments',
	'hms_room_images',
	'hms_property_images',
	'hms_rooms',
	'hms_properties',
	'hms_services',
	'hms_landlords'
];

foreach ($tables as $table)
{
	if ($CI->db->table_exists(db_prefix() . $table))
	{
		$CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . $table . '`');
	}
}

// Remove module permissions
$CI->db->where('name', 'Hotel Management System');
$CI->db->or_where('name', 'HMS Landlords');
$CI->db->or_where('name', 'HMS Properties');
$CI->db->or_where('name', 'HMS Rooms');
$CI->db->or_where('name', 'HMS Services');
$CI->db->or_where('name', 'HMS Bookings');
$CI->db->delete(db_prefix() . 'permissions');

// Remove module settings from options table
$CI->db->where('name LIKE', 'hotel_management_system%');
$CI->db->delete(db_prefix() . 'options');

// Delete module upload directory
if (is_dir(HMS_MODULE_UPLOAD_FOLDER))
{
	// Helper function to recursively delete directory
	function delete_directory($dir): bool
	{
		if ( ! file_exists($dir))
		{
			return TRUE;
		}

		if ( ! is_dir($dir))
		{
			return unlink($dir);
		}

		foreach (scandir($dir) as $item)
		{
			if ($item == '.' || $item == '..')
			{
				continue;
			}

			if ( ! delete_directory($dir . DIRECTORY_SEPARATOR . $item))
			{
				return FALSE;
			}
		}

		return rmdir($dir);
	}

	delete_directory(HMS_MODULE_UPLOAD_FOLDER);
}