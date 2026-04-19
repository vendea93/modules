<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

add_option('hosting_manager_purchase_code', '1');
add_option('hosting_manager_purchase_is_valid', 1);


    $sql_query = "CREATE TABLE IF NOT EXISTS `" .db_prefix() . "hosting_account` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `provider` VARCHAR(255) DEFAULT NULL,
        `provider_url` VARCHAR(255) DEFAULT NULL,
        `provider_user` VARCHAR(255) DEFAULT NULL,
        `provider_password` VARCHAR(255) DEFAULT NULL,
        `plan` VARCHAR(255) DEFAULT NULL,
        `price` VARCHAR(255) DEFAULT NULL,
        `start_date` DATE DEFAULT NULL,
        `expiry_date` DATE DEFAULT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'active',
        `client_id` INT(11) DEFAULT NULL,  -- Links to clients table
        `project_id` INT(11) DEFAULT NULL, -- Links to projects table
        `description` TEXT DEFAULT NULL,  -- Additional notes
        `created_by` INT(11) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    );";

    $CI->db->query($sql_query);

    $sql_query = "CREATE TABLE IF NOT EXISTS `" .db_prefix() . "hm_domains` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `hosting_id` INT(11) DEFAULT NULL,  -- Links to hosting table
        `title` VARCHAR(255) NOT NULL,
        `ssl_active` VARCHAR(255) NOT NULL DEFAULT 'enable',
        `price` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'active',
        `description` TEXT DEFAULT NULL,  -- Additional notes
        `created_by` INT(11) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    );";
    $CI->db->query($sql_query);

    $sql_query = "CREATE TABLE IF NOT EXISTS `" .db_prefix() . "hm_database` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `hosting_id` INT(11) DEFAULT NULL,  -- Links to hosting table
        `title` VARCHAR(255)  DEFAULT NULL,
        `access_url` VARCHAR(255)  DEFAULT NULL,
        `database_name` VARCHAR(255)  DEFAULT NULL,
        `database_username` VARCHAR(255)  DEFAULT NULL,
        `database_password` VARCHAR(255)  DEFAULT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'enable',
        `description` TEXT DEFAULT NULL,  -- Additional notes
        `created_by` INT(11) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    );";
    $CI->db->query($sql_query);



    $sql_query = "CREATE TABLE IF NOT EXISTS  `" .db_prefix() . "ftp_accounts` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `hosting_id` INT(11) DEFAULT NULL,  -- Links to hosting table
        `account_name` VARCHAR(255) NOT NULL,
        `hostname` VARCHAR(255) DEFAULT NULL,
        `username` VARCHAR(255) DEFAULT NULL,
        `password` VARCHAR(255) DEFAULT NULL,
        `port` VARCHAR(255) DEFAULT NULL,
        `protocol` VARCHAR(255) NOT NULL DEFAULT 'ftp',
        `root_directory` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'active',
        `description` TEXT DEFAULT NULL,  -- Additional notes
        `created_by` INT(11) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    );";
    $CI->db->query($sql_query);
