<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();


        if ( !$CI->db->table_exists(db_prefix().'task_signature_info') )
        {

            $CI->db->query('
                    CREATE TABLE `'.db_prefix().'task_signature_info` (
                        `task_id` int(11) NOT NULL DEFAULT 0,
                        `staff_id` int(11) DEFAULT 0,
                        `ip_address` varchar(50) DEFAULT NULL,
                        `datetime` datetime DEFAULT NULL,
                      KEY `task_id` (`task_id`),
                      KEY `staff_id` (`staff_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
                   ');

        }

    }
}
