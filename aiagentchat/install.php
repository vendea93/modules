<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('aiagentchat_openai_api_key', '');
add_option('aiagentchat_bubble_chat_icon_admin', 'fa fa-commenting');
add_option('aiagentchat_bubble_chat_css_json_admin', '');
add_option('aiagentchat_bubble_chat_icon_client', 'fa fa-commenting');
add_option('aiagentchat_bubble_chat_css_json_client', '');

if (!$CI->db->table_exists(db_prefix() . 'aiagentchat_chats')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "aiagentchat_chats` (
  `id` int(11) NOT NULL,
  `chat_name` text,
  `workflow_id` text,
  `settings_json` text,
  `is_enabled` int default 0, 
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'aiagentchat_chats`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'aiagentchat_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->table_exists(db_prefix() . 'aiagentchat_chats_assignments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "aiagentchat_chats_assignments` (
  `id` int(11) NOT NULL,
  `chat_id` text,
  `rel_id` text,
  `rel_type` text,
  `created_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'aiagentchat_chats_assignments`
  ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'aiagentchat_chats_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}