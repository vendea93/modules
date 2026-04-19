<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_germany_use_sale_agent_as_seller', 0);
    }
}
