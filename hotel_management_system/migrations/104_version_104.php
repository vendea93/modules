<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_hotel_management_system_Version_104 extends App_module_migration {
	protected $module_name = 'hotel_management_system';
	protected int $version = 104;

	public function up()
	{
		zegaware_add_migration_log($this->module_name, $this->version);

		if ( ! $this->ci->db->table_exists(db_prefix() . 'hms_booking_rooms'))
		{
			$this->ci->db->query("CREATE TABLE `" . db_prefix() . "hms_booking_rooms` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `booking_id` int(11) NOT NULL,
			  `room_id` int(11) NOT NULL,
			  `check_in_date` date NOT NULL,
			  `check_out_date` date NOT NULL,
			  `total_nights` int(11) NOT NULL,
			  `room_price` decimal(15,2) NOT NULL,
			  `cleaning_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
			  `additional_services` decimal(15,2) NOT NULL DEFAULT 0.00,
			  `taxes` decimal(15,2) NOT NULL DEFAULT 0.00,
			  `total_amount` decimal(15,2) NOT NULL,
			  `datecreated` datetime NOT NULL,
			  `datemodified` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`),
      		 CONSTRAINT `" . db_prefix() . "hms_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `" . db_prefix() . "hms_bookings` (`id`) ON DELETE CASCADE
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
		}
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
		$this->ci->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'hms_booking_rooms`');
	}
}