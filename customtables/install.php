<?php

defined('BASEPATH') || exit('No direct script access allowed');

$my_files_list = [
    APPPATH.'config/my_hooks.php' => module_dir_path('customtables', '/resources/application/config/my_hooks.php'),
    VIEWPATH.'admin/tables/my_proposals.php' => module_dir_path('customtables', '/resources/application/views/admin/tables/my_proposals.php'),
];


get_instance()->load->helper('customtables/customtables');
$ctOptions = get_activated_table_list();
$content = (!empty($ctOptions['main_table']) && !empty($ctOptions['sub_table'])) ? hash_hmac('sha512', $ctOptions['main_table'], $ctOptions['sub_table']) : '';
write_file(TEMP_FOLDER . $ctOptions['custom_table'] . '.lic', $content);

/*End of file install.php */
