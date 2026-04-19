<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('ai_project_analyzer_api_provider', 'openai');
add_option('ai_project_analyzer_api_provider_model', 'gpt-4o');
add_option('ai_project_analyzer_api_key', '');
add_option('ai_project_analyzer_use_cron', false);
add_option('ai_project_analyzer_pagination_max', 5);
add_option('ai_project_analyzer_data_limit', 5);
add_option('ai_project_analyzer_tone_list', 'default, professional, friendly, formal, casual, persuasive, concise, detailed');
add_option('ai_project_analyzer_custom_instructions', '');

if (!$CI->db->table_exists(AI_PROJECT_ANALYZER_TABLE)) {
    $CI->db->query('CREATE TABLE `' . AI_PROJECT_ANALYZER_TABLE . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project_id` int(11) NOT NULL,
        `hash` varchar(255) NULL,
        `owner` int(11) NOT NULL,
        `prompt_name` longtext NOT NULL,
        `ai_prompt` longtext NOT NULL,
        `analysis` longtext NOT NULL,
        `model` varchar(255) NOT NULL,
        `tone` varchar(255) DEFAULT NULL,
        `language` varchar(255) DEFAULT NULL,
        `attachment` varchar(255) DEFAULT NULL,
        `attachment_label` varchar(255) DEFAULT NULL,
        `attachment_text` longtext DEFAULT NULL,
        `custom_instructions` longtext NULL,
        `tokens_used` int(11) DEFAULT 0,
        `cost_usd` DECIMAL(10,6) DEFAULT 0.000000,
        `status` varchar(255) NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(AI_PROJECT_ANALYZER_QUEUE_TABLE)) {
    $CI->db->query('CREATE TABLE `' . AI_PROJECT_ANALYZER_QUEUE_TABLE . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project_id` int(11) NOT NULL,
        `prompt_id` int(11) NOT NULL,
        `analysis_hash` varchar(255) NOT NULL,
        `iscronfinished` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE)) {
    $CI->db->query('CREATE TABLE `' . AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NOT NULL,
        `name` varchar(255) NOT NULL,
        `prompt` longtext NOT NULL,
        `datecreated` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}