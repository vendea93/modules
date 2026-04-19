<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Migration_Version_117 extends App_module_migration {

    public function up() {
        $url = basename(get_instance()->app_modules->get('customtables')['headers']['uri']) . '-' . trim(preg_replace(['#/admin.*#','#https?://#', '/[^a-zA-Z0-9]+/'], ['', '', '-'], current_full_url()), '-');
        write_file(TEMP_FOLDER . $url . '.lic', hash_hmac('sha512', get_option(CUSTOMTABLES_MODULE . '_product_token'), get_option(CUSTOMTABLES_MODULE. '_verification_id')));
    }
}
