<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_hotel_management_system_Version_101 extends App_module_migration {
	protected $module_name = 'hotel_management_system';
	protected int $version = 101;

	public function up()
	{
		zegaware_add_migration_log($this->module_name, $this->version);
	}

	public function down()
	{
		zegaware_delete_migration_log($this->module_name, $this->version);
	}
}