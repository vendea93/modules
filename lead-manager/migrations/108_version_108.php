<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_108 extends App_module_migration
{
  public function up()
  {
    $CI = &get_instance();

    if (!$CI->db->field_exists('content_variables', db_prefix() . 'lead_manager_whatsapp_templates')) {
      $CI->db->query("ALTER TABLE `" . db_prefix() . "lead_manager_whatsapp_templates` ADD `content_variables` TEXT NULL DEFAULT NULL");
    }
  }
}
