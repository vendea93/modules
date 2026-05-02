<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['module_name'] = 'FlowQuest Office Theme';
$config['module_description'] = 'Zaktualizowany motyw office dla FlowQuest zgodny z najnowszą wersją Perfex CRM 3.4.0+ i kolorystyką marki FlowQuest';
$config['version'] = '1.0.0';
$config['required_versions'] = array(
    'perfex' => '3.4.0'
);

$config['hooks'] = array(
    'pre_head' => 'Flowquest_office_theme::pre_head',
    'pre_footer' => 'Flowquest_office_theme::pre_footer'
);

$config['styles'] = array(
    'flowquest_office_theme/assets/css/flowquest-integration.css',
    'flowquest_office_theme/assets/css/theme_styles.css'
);

$config['scripts'] = array(
    'flowquest_office_theme/assets/js/flowquest-theme.js'
);