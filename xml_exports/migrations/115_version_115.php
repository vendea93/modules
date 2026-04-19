<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        add_option('xml_export_unidad_tramitadora_code_field');
        add_option('xml_export_unidad_tramitadora_name_field');
        add_option('xml_export_unidad_tramitadora_address_field');
        add_option('xml_export_unidad_tramitadora_postCode_field');
        add_option('xml_export_unidad_tramitadora_town_field');
        add_option('xml_export_unidad_tramitadora_province_field');

        add_option('xml_export_oficina_contable_code_field');
        add_option('xml_export_oficina_contable_name_field');
        add_option('xml_export_oficina_contable_address_field');
        add_option('xml_export_oficina_contable_postCode_field');
        add_option('xml_export_oficina_contable_town_field');
        add_option('xml_export_oficina_contable_province_field');

        add_option('xml_export_organo_gestor_code_field');
        add_option('xml_export_organo_gestor_name_field');
        add_option('xml_export_organo_gestor_address_field');
        add_option('xml_export_organo_gestor_postCode_field');
        add_option('xml_export_organo_gestor_town_field');
        add_option('xml_export_organo_gestor_province_field');


        add_option('settings_xml_organo_proponente_code_field');
        add_option('settings_xml_organo_proponente_name_field');
        add_option('settings_xml_organo_proponente_address_field');
        add_option('settings_xml_organo_proponente_postCode_field');
        add_option('settings_xml_organo_proponente_town_field');
        add_option('settings_xml_organo_proponente_province_field');

        add_option('xml_export_spain_iban');
        add_option('xml_export_spain_bic');
    }
}
