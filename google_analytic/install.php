<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('ga_google_app_id');
add_option('ga_google_app_secret');


if (!$CI->db->table_exists(db_prefix() . 'ga_workspaces')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "ga_workspaces (
  	  `id` INT(11) NOT NULL AUTO_INCREMENT,
	  `name` TEXT NOT NULL,
      `timezone` VARCHAR(255) NULL,
      `super_admin` INT(11) NULL,
      `is_default` TINYINT(1) NOT NULL DEFAULT 0,
      `workspace_logo` TEXT NULL,
      `notes` TEXT NULL,
      `display_charts` TEXT NULL,
      `addedfrom` INT(11) NULL,
      `dateadded` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'ga_workspace_members')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "ga_workspace_members (
  	  `id` INT(11) NOT NULL AUTO_INCREMENT,
      `workspace_id` INT(11) NULL,
	  `type` VARCHAR(25) NOT NULL,
      `member_id` INT(11) NULL,
      `addedfrom` INT(11) NULL,
      `dateadded` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('ga_base_workspace_id' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
  ADD COLUMN `ga_base_workspace_id` INT(11) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->field_exists('ga_base_workspace_id' ,db_prefix() . 'contacts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contacts`
  ADD COLUMN `ga_base_workspace_id` INT(11) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->table_exists(db_prefix() . 'ga_accounts')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "ga_accounts (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `workspace_id` INT(11) NULL,
      `name` TEXT NOT NULL,
      `type` VARCHAR(25) NULL,
      `status` TINYINT(1) NOT NULL DEFAULT 0,
      `access_token` TEXT NULL,
      `expires_in` TEXT NULL,
      `refresh_token` TEXT NULL,
      `page_id` TEXT NULL,
      `user_id` TEXT NULL,
      `active` TINYINT(1) NOT NULL DEFAULT 0,
      `description` TEXT NULL,
      `addedfrom` INT(11) NULL,
      `dateadded` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'ga_analytics')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "ga_analytics (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `account_id` INT(11) NULL,
      `time` DATETIME NOT NULL,
      `type` VARCHAR(255) NULL,
      `value` VARCHAR(255) NULL,
      `import` TINYINT(1) NOT NULL DEFAULT 0,
      `addedfrom` INT(11) NULL,
      `dateadded` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('channel' ,db_prefix() . 'ga_analytics')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'ga_analytics`
  ADD COLUMN `channel` VARCHAR(50) NULL,
  ADD COLUMN `language` VARCHAR(50) NULL,
  ADD COLUMN `country` VARCHAR(255) NULL,
  ADD COLUMN `region` VARCHAR(255) NULL,
  ADD COLUMN `city` VARCHAR(255) NULL,
  ADD COLUMN `campaign` TEXT NULL,
  ADD COLUMN `item` TEXT NULL,
  ADD COLUMN `age` VARCHAR(15) NULL,
  ADD COLUMN `gender` VARCHAR(15) NULL,
  ADD COLUMN `device` TEXT NULL,
  ADD COLUMN `browser` TEXT NULL,
  ADD COLUMN `operating_system` TEXT NULL,
  ADD COLUMN `interests` TEXT NULL,
  ADD COLUMN `new_vs_returning` TEXT NULL,
  ADD COLUMN `page` TEXT NULL,
  ADD COLUMN `event` TEXT NULL
  ;');
}

add_option('ga_analytic_metrics','totalPurchasers,itemRevenue,itemsPurchased,itemsViewed,eventCount,keyEvents,screenPageViews,sessions,activeUsers,newUsers,totalUsers,userEngagementDuration');

if (!$CI->db->field_exists('ga_analytic_metrics' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
  ADD COLUMN `ga_analytic_metrics` TEXT
  ;');
}

if (!$CI->db->field_exists('ga_analytic_metrics' ,db_prefix() . 'contacts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'contacts`
  ADD COLUMN `ga_analytic_metrics` TEXT
  ;');
}
