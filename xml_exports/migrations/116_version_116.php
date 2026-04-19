<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_spain_use_sale_agent_as_seller', 0);
        add_option('xml_export_spain_seller_first_name');
        add_option('xml_export_spain_seller_last_name');
    }
}
