<?php


defined('BASEPATH') or exit('No direct script access allowed');

$CI = get_instance();

if ( !$CI->db->field_exists( 'task_sign_index' , db_prefix().'staff' ) )
{
    $CI->db->query('ALTER TABLE `'.db_prefix().'staff` 
                                ADD COLUMN `task_sign_index` tinyint(4) NULL DEFAULT 1 AFTER `default_language`;');
}


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


add_option('ts_complete_task_without_sign',1,0);
add_option('ts_followers_will_sign',1,0);
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



