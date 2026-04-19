<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Migration_Version_103 extends App_module_migration
{

    public function up()
    {

        $CI = &get_instance();

        add_option('ts_not_complete_without_customer_sign',1,0);


        if ( !$CI->db->field_exists('is_signature_required' , db_prefix().'tasks' ) )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'tasks`
                            ADD COLUMN `is_signature_required` tinyint NULL DEFAULT 1 AFTER `visible_to_client`;');

        }


        if ( !$CI->db->table_exists(db_prefix().'task_client_signature_info') )
        {

            $CI->db->query('
                    CREATE TABLE `'.db_prefix().'task_client_signature_info` ( 
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `task_id` int(11) DEFAULT NULL,
                      `client_id` int(11) DEFAULT NULL,
                      `signed` tinyint(4) DEFAULT 0,
                      `request_date` datetime DEFAULT NULL,
                       `contact_id` int(11) DEFAULT NULL,
                      `signature_date` datetime DEFAULT NULL,
                      `signature` varchar(255) DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `task_id` (`task_id`),
                      KEY `client_id` (`client_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
                   ');

        }



        if ( !$CI->db->field_exists('ip_address' , db_prefix().'task_client_signature_info' ) )
        {

            $CI->db->query('ALTER TABLE `'.db_prefix().'task_client_signature_info`
                            ADD COLUMN `ip_address` varchar(50) NULL AFTER `signature`;');

        }

    }

}
