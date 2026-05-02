<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_hotel_management_system_Version_103 extends App_module_migration {
	protected $module_name = 'hotel_management_system';
	protected int $version = 103;

	public function up()
	{
		zegaware_add_migration_log($this->module_name, $this->version);
		if ( ! $this->ci->db->field_exists('meal_plan', db_prefix() . 'hms_rooms'))
		{
			$this->ci->db->query("ALTER TABLE " . db_prefix() . "hms_rooms ADD COLUMN `meal_plan` VARCHAR(50) DEFAULT NULL AFTER `capacity` ;");
		}
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
		if ($this->ci->db->field_exists('meal_plan', db_prefix() . 'hms_rooms'))
		{
			$this->ci->db->query('ALTER TABLE ' . db_prefix() . 'hms_rooms DROP COLUMN `meal_plan`');
		}
	}
}