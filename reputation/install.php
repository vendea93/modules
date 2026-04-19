<?php
defined('BASEPATH') or exit('No direct script access allowed');


if (!$CI->db->table_exists(db_prefix() . 'pur_vendor')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pur_vendor` (
      `userid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `company` varchar(200) NULL,
      `vat` varchar(200) NULL,
      `phonenumber` varchar(30) NULL,
      `country` int(11) NOT NULL DEFAULT '0',
      `city` varchar(100) NULL,
      `zip` varchar(15) NULL,
      `state` varchar(50) NULL,
      `address` varchar(100) NULL,
      `website` varchar(150) NULL,
      `datecreated` DATETIME NOT NULL,
      `active` INT(11) NOT NULL DEFAULT '1',
      `leadid` INT(11) NULL,
      `billing_street` varchar(200) NULL,
      `billing_city` varchar(100) NULL,
      `billing_state` varchar(100) NULL,
      `billing_zip` varchar(100) NULL,
      `billing_country` int(11) NULL DEFAULT '0',
      `shipping_street` varchar(200) NULL,
      `shipping_city` varchar(100) NULL,
      `shipping_state` varchar(100) NULL,
      `shipping_zip` varchar(100) NULL,
      `shipping_country` int(11) NULL DEFAULT '0',
      `longitude` varchar(191) NULL,
      `latitude` varchar(191) NULL,
      `default_language` varchar(40) NULL,
      `default_currency` INT(11) NOT NULL DEFAULT '0',
      `show_primary_contact` INT(11) NOT NULL DEFAULT '0',
      `stripe_id` varchar(40) NULL,
      `registration_confirmed` INT(11) NOT NULL DEFAULT '1',
      `addedfrom` INT(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`userid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('category' ,db_prefix() . 'pur_vendor')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_vendor`
      ADD COLUMN `category` TEXT  NULL
  ;");
}

if (!$CI->db->field_exists('bank_detail' ,db_prefix() . 'pur_vendor')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_vendor`
      ADD COLUMN `bank_detail` TEXT  NULL
  ;");
}

if (!$CI->db->field_exists('payment_terms' ,db_prefix() . 'pur_vendor')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_vendor`
      ADD COLUMN `payment_terms` TEXT  NULL
  ;");
}

if (!$CI->db->field_exists('vendor_code' ,db_prefix() . 'pur_vendor')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_vendor`
      ADD COLUMN `vendor_code` VARCHAR(100)  NULL
  ;");
}

if ($CI->db->field_exists('address' ,db_prefix() . 'pur_vendor')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "pur_vendor`
      CHANGE COLUMN `address` `address` TEXT NULL DEFAULT NULL
  ;");
}

if (!$CI->db->field_exists('return_within_day' ,db_prefix() . 'pur_vendor')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'pur_vendor`
  ADD COLUMN `return_within_day` INT(11) NULL
  ');
}

if (!$CI->db->field_exists('return_order_fee' ,db_prefix() . 'pur_vendor')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'pur_vendor`
  ADD COLUMN `return_order_fee` DECIMAL(15,2) NULL
  ');
}

if (!$CI->db->field_exists('return_policies' ,db_prefix() . 'pur_vendor')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'pur_vendor`
  ADD COLUMN `return_policies` TEXT NULL
  ');
}

if (!$CI->db->table_exists(db_prefix() . 'rep_topics')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_topics (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `content` TEXT NOT NULL,
      `scales` INT(11) NOT NULL,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `type` TEXT NOT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'rep_projects')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_projects (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `project_name` TEXT NOT NULL,
      `language` VARCHAR(40) NULL,
      `keywords` TEXT NULL,
      `excluded_keywords` TEXT NULL,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('active_sources_x_twitter' ,db_prefix() . 'rep_projects')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_projects`
  ADD COLUMN `active_sources_x_twitter` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_news` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_web` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_blogs` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_videos` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_podcast` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_forums` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_instagram` TINYINT(1) NOT NULL DEFAULT 1
  ');
}


if (!$CI->db->table_exists(db_prefix() . 'rep_notifications')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_notifications (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `project_id` INT(11) NULL,
      `email` VARCHAR(40) NULL,
      `frequency` TEXT NULL,
      `frequency_day_of_week` VARCHAR(10) NULL,
      `frequency_day` VARCHAR(10) NULL,
      `frequency_time` TIME NULL,
      `filter_id` INT(11) NULL,
      `mention_threshold` INT(11) NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'rep_filters')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_filters (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NULL,
      `sentiment` VARCHAR(40) NULL,
      `influencer_score` INT(11) NULL,
      `country` INT(11) NULL,
      `interactions` VARCHAR(40) NULL,
      `visited` VARCHAR(40) NULL,
      `category` TEXT NULL,
      `domain` TEXT NULL,
      `tags` TEXT NULL,
      `date` DATE NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}



if (!$CI->db->field_exists('tripadvisor' ,db_prefix() . 'rep_projects')) {
  $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_projects`
  ADD COLUMN `tripadvisor` TEXT NULL,
  ADD COLUMN `booking` TEXT NULL,
  ADD COLUMN `app_store` TEXT NULL,
  ADD COLUMN `google_play` TEXT NULL,
  ADD COLUMN `trustpilot` TEXT NULL,
  ADD COLUMN `spotify` TEXT NULL,
  ADD COLUMN `apple_itunes` TEXT NULL,
  ADD COLUMN `youtube` TEXT NULL,
  ADD COLUMN `vimeo` TEXT NULL,
  ADD COLUMN `tiktok` TEXT NULL,
  ADD COLUMN `news_source` TEXT NULL,
  ADD COLUMN `blog_source` TEXT NULL,
  ADD COLUMN `web_source` TEXT NULL,
  ADD COLUMN `telegram` TEXT NULL,
  ADD COLUMN `x_twitter` TEXT NULL
  ');
}


if (!$CI->db->field_exists('excluded_sites' ,db_prefix() . 'rep_projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_projects`
  ADD COLUMN `excluded_sites` TEXT NULL,
  ADD COLUMN `excluded_social_media_authors` TEXT NULL
  ;');
}



add_option('rep_facebook_app_id');
add_option('rep_facebook_app_secret');
add_option('rep_facebook_graph_version', 'v21.0');

add_option('rep_instagram_app_id');
add_option('rep_instagram_app_secret');
add_option('rep_instagram_graph_version', 'v21.0');

add_option('rep_tiktok_client_key');
add_option('rep_tiktok_client_secret');

add_option('rep_youtube_client_id');
add_option('rep_youtube_client_secret');

add_option('rep_twitter_client_id');
add_option('rep_twitter_client_secret');


if (!$CI->db->field_exists('rep_base_project_id' ,db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff`
  ADD COLUMN `rep_base_project_id` INT(11) NOT NULL DEFAULT 0
  ;');
}


if (!$CI->db->table_exists(db_prefix() . 'rep_accounts')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_accounts (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `client_id` INT(11) NULL,
      `type` VARCHAR(25) NULL,
      `status` VARCHAR(25) NULL,
      `addedfrom` INT(11) NULL,
      `dateadded` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


if (!$CI->db->field_exists('access_token' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `access_token` TEXT NULL,
  ADD COLUMN `page_id` TEXT NULL,
  ADD COLUMN `project_id` INT(11) NULL,
  ADD COLUMN `user_id` TEXT NULL
  ;');
}

if (!$CI->db->field_exists('category' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `category` TEXT NULL 
  ;');
}


if (!$CI->db->field_exists('avatar_url' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `avatar_url` TEXT NULL, 
  ADD COLUMN `expires_in` TEXT NULL, 
  ADD COLUMN `refresh_token` TEXT NULL 
  ;');
}

if (!$CI->db->field_exists('description' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `description` TEXT NULL
  ;');
}

if (!$CI->db->field_exists('status' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->field_exists('active' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 0
  ;');
}


if (!$CI->db->field_exists('auto_sync' ,db_prefix() . 'rep_accounts')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_accounts`
  ADD COLUMN `auto_sync` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `sync_every_minutes` INT(11) NULL,
  ADD COLUMN `last_sync_time` DATETIME NULL
  ;');
}


if (!$CI->db->table_exists(db_prefix() . 'rep_mentions')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_mentions (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `project_id` INT(11) NOT NULL,
      `account_id` INT(11) NULL,
      `title` TEXT NULL,
      `link` TEXT NULL,
      `content` TEXT NULL,
      `platform` VARCHAR(50) NULL,
      `source_id` TEXT NULL,
      `site` TEXT NULL,
      `sentiment` VARCHAR(50) NULL,
      `author_name` TEXT NULL,
      `author_id` TEXT NULL,
      `post_id` TEXT NULL,
      `comment_id` TEXT NULL,
      `tags` TEXT NULL,
      `status` VARCHAR(50) NULL,
      `country` VARCHAR(50) NULL,
      `time` DATETIME NULL,
      `likes` INT(11) NULL,
      `pageviews` INT(11) NULL,
      `shares` INT(11) NULL,
      `comments` INT(11) NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'rep_pdf_reports')) {
  $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_pdf_reports (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `project_id` INT(11) NOT NULL,
      `mention_id` INT(11) NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('visit' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `visit` TINYINT(1) NOT NULL DEFAULT 0
  ;');
}
 
if (!$CI->db->field_exists('add_to_pdf' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `add_to_pdf` TINYINT(1) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->table_exists(db_prefix() . 'rep_cases')) {
    $CI->db->query('CREATE TABLE ' . db_prefix() . "rep_cases (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` TEXT NOT NULL,
      `description` TEXT NULL,
      `active` TINYINT(1) NOT NULL DEFAULT 1,
      `workflow` TEXT NOT NULL,
      `datecreated` DATETIME NULL,
      `addedfrom` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->field_exists('keyword' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `keyword` TEXT NULL
  ;');
}

if (!$CI->db->field_exists('active_sources_youtube' ,db_prefix() . 'rep_projects')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_projects`
  ADD COLUMN `active_sources_youtube` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `active_sources_facebook` TINYINT(1) NOT NULL DEFAULT 1
  ;');
}

if (!$CI->db->field_exists('add_manually' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `add_manually` TINYINT(1) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->field_exists('visited' ,db_prefix() . 'rep_notifications')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_notifications`
      ADD COLUMN `visited` VARCHAR(40) NULL,
      ADD COLUMN `sources` VARCHAR(100) NULL,
      ADD COLUMN `sentiment` VARCHAR(100) NULL,
      ADD COLUMN `tags` TEXT NULL
  ;');
}

if (!$CI->db->field_exists('last_send_time' ,db_prefix() . 'rep_notifications')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_notifications`
      ADD COLUMN `last_send_time` TEXT NULL
  ;');
}

if (!$CI->db->field_exists('scales' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `scales` INT(11) NOT NULL DEFAULT 0
  ;');
}

if (!$CI->db->field_exists('topic' ,db_prefix() . 'rep_mentions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'rep_mentions`
  ADD COLUMN `topic` INT(11) NOT NULL DEFAULT 0
  ;');
}


if (!$CI->db->table_exists(db_prefix() . 'pur_contacts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "pur_contacts` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `userid` int(11) NOT NULL,
      `is_primary` int(11) NOT NULL DEFAULT '1',
      `firstname` varchar(191) NOT NULL,
      `lastname` VARCHAR(191) NOT NULL,
      `email` varchar(100) NOT NULL,
      `phonenumber` varchar(100) NOT NULL,
      `title` varchar(100) NULL,
      `datecreated` datetime NOT NULL,
      `password` varchar(255) NULL,
      `new_pass_key` varchar(32) NULL,
      `new_pass_key_requested` datetime NULL,
      `email_verified_at` datetime NULL,
      `email_verification_key` varchar(32) NULL,
      `email_verification_sent_at` DATETIME NULL,
      `last_ip` varchar(40) NULL,
      `last_login` DATETIME NULL,
      `last_password_change` DATETIME NULL,
      `active` TINYINT(1) NOT NULL DEFAULT '1',
      `profile_image` varchar(191) NULL,
      `direction` varchar(3) NULL,
      `invoice_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `estimate_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `credit_note_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `contract_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `task_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `project_emails` TINYINT(1) NOT NULL DEFAULT '1',
      `ticket_emails` TINYINT(1) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'pur_vendor_admin')) {
    $CI->db->query('CREATE TABLE `'.db_prefix()."pur_vendor_admin` (
  `staff_id` INT(11) NOT NULL,
  `vendor_id` INT(11) NOT NULL,
  `date_assigned` DATETIME NOT NULL);");
}