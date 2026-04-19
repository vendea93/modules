<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_germany_seller_contact_person');
        add_option('xml_export_germany_seller_contact_phone');
        add_option('xml_export_germany_seller_contact_email');
    }
}
