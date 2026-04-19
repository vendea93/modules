<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_active_scheme', 'peppol');
        add_option('xml_export_spain_series', 'FAC2025');

        add_option('xml_export_italy_transmission_format', 'FPR12');

        add_option('xml_export_spain_certificate');
        add_option('xml_export_spain_certificate_password');
        add_option('xml_export_spain_private_key');

        add_option('xml_export_italy_certificate');
        add_option('xml_export_italy_certificate_password');
        add_option('xml_export_italy_private_key');
    }
}
