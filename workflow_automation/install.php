<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'wa_workflows')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_workflows` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `enabled` INT(1) NULL,
  `type` TEXT NULL,
  `category_id` INT(11) NULL,
  `description` TEXT NULL,
  `workflow` LONGTEXT NULL,
  `private` INT(1) NULL,
  `start_email` INT(1) NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'wa_task_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_task_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_name` TEXT NOT NULL,
  `task_subject` TEXT NULL,
  `start_date` DATE NULL,
  `due_date` DATE NULL,
  `priority` INT(11) NULL,
  `rel_type` TEXT NULL,
  `assignees` TEXT NULL,
  `followers` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'wa_project_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_project_templates` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_name` TEXT NOT NULL,
  `project_name` TEXT NULL,
  `customer` INT(11) NULL,
  `status` INT(11) NULL,
  `billing_type` INT(11) NULL,
  `estimated_hours` DECIMAL(15,2) NULL,
  `start_date` DATE NULL,
  `deadline` DATE NULL,
  `members` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'wa_flows_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_flows_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `flow_id` INT(11) NOT NULL,
  `node_id` TEXT NULL,
  `start_time` DATETIME NULL,
  `end_time` DATE NULL,
  `output` TEXT NULL,
  `node_type` TEXT NULL,
  `result` TEXT NULL,
  `note` TEXT NULL,
  `rel_type` TEXT NULL,
  `rel_id` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}


if (!$CI->db->table_exists(db_prefix() . 'wa_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` TEXT NULL,
  `description` TEXT NULL,
  `created_at` DATETIME NULL,
  `created_by` INT(11) NULL,
  PRIMARY KEY (`id`));');
}

create_email_template('Workflow Automation', '<span style=\"font-size: 12pt;\"> Dear {first_name} {last_name}</span><br /><br /><span style=\"font-size: 12pt;\">{content}</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</br></br>{email_signature}</span>', 'workflow_automation', 'Workflow Automation (Sent mail action)', 'workflow-automation-task-sent-mail-action');


// task auto create by workflow
if (!$CI->db->field_exists('created_by_workflow' ,db_prefix() . 'tasks')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "tasks`
    ADD COLUMN `created_by_workflow` INT(11) NULL
  ;");
}


if (!$CI->db->field_exists('updated_by_workflow' ,db_prefix() . 'tasks')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "tasks`
    ADD COLUMN `updated_by_workflow` INT(11) NULL
  ;");
}


if (!$CI->db->field_exists('condition' ,db_prefix() . 'wa_flows_logs')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wa_flows_logs`
    ADD COLUMN `condition` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('condition_field' ,db_prefix() . 'wa_flows_logs')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wa_flows_logs`
    ADD COLUMN `condition_field` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('condition_variable' ,db_prefix() . 'wa_flows_logs')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wa_flows_logs`
    ADD COLUMN `condition_variable` TEXT NULL
  ;");
}


if (!$CI->db->field_exists('action' ,db_prefix() . 'wa_flows_logs')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wa_flows_logs`
    ADD COLUMN `action` TEXT NULL
  ;");
}

if (!$CI->db->field_exists('start_case' ,db_prefix() . 'wa_flows_logs')) { 
  $CI->db->query('ALTER TABLE `' . db_prefix() . "wa_flows_logs`
    ADD COLUMN `start_case` TEXT NULL
  ;");
}

if (!$CI->db->table_exists(db_prefix() . 'wa_action_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_action_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `flow_id` INT(11) NULL,
  `node_id` TEXT NULL,
  `action` TEXT NULL,
  `rel_type` TEXT NULL,
  `rel_id` INT(11) NULL,
  `action_relsult` TEXT NULL,
  `action_relsult_id` TEXT NULL,
  `action_relsult_type` TEXT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`));');
}

if (!$CI->db->table_exists(db_prefix() . 'wa_automatic_log')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() .'wa_automatic_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `flow_id` INT(11) NULL,
  `note` TEXT NULL,
  `repeat_every` TEXT NULL,
  `hour_of_day` INT(11) NULL,
  `day_of_week` INT(11) NULL,
  `day_of_month` INT(11) NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`));');
}
