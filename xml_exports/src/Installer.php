<?php

namespace Techy4m\XmlExports;
defined('BASEPATH') or exit('No direct script access allowed');

class Installer
{
    public function activate(): void
    {
        add_option('xml_export_electronic_address', 'your-company-email-address');
        add_option('xml_export_electronic_address_scheme', '9901');
        add_option('xml_export_company_id', 'your-company-vat-number');
        add_option('xml_export_company_id_scheme', '0007');
        add_option('xml_export_customer_electronic_address_field');
        add_option('xml_export_customer_electronic_address_scheme_field', '9901');
        add_option('xml_export_invoice_buyer_reference_field');
        add_option('xml_export_attach_xml_to_invoice_emails', 0);
        add_option('xml_export_active_scheme', 'peppol');
        add_option('xml_export_spain_series', 'FAC2025');
        add_option('xml_export_italy_transmission_format', 'FPR12');
        add_option('xml_export_spain_certificate');
        add_option('xml_export_spain_certificate_password');
        add_option('xml_export_spain_private_key');
        add_option('xml_export_italy_certificate');
        add_option('xml_export_italy_certificate_password');
        add_option('xml_export_italy_private_key');
        add_option('xml_export_germany_seller_contact_person');
        add_option('xml_export_germany_seller_contact_phone');
        add_option('xml_export_germany_seller_contact_email');
        add_option('xml_export_germany_use_sale_agent_as_seller', 0);

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

        add_option('xml_export_spain_use_sale_agent_as_seller', 1);
        add_option('xml_export_spain_seller_first_name');
        add_option('xml_export_spain_seller_last_name');
    }

    public function uninstall(): void
    {
        delete_option('xml_export_electronic_address');
        delete_option('xml_export_electronic_address_scheme');
        delete_option('xml_export_company_id');
        delete_option('xml_export_company_id_scheme');
        delete_option('xml_export_customer_electronic_address_field');
        delete_option('xml_export_customer_electronic_address_scheme_field');
        delete_option('xml_export_invoice_buyer_reference_field');
        delete_option('xml_export_attach_xml_to_invoice_emails');
        delete_option('xml_export_active_scheme');
        delete_option('xml_export_spain_series');
        delete_option('xml_export_italy_transmission_format');
        delete_option('xml_export_spain_certificate');
        delete_option('xml_export_spain_certificate_password');
        delete_option('xml_export_spain_private_key');
        delete_option('xml_export_italy_certificate');
        delete_option('xml_export_italy_certificate_password');
        delete_option('xml_export_italy_private_key');

        delete_option('xml_export_germany_seller_contact_person');
        delete_option('xml_export_germany_seller_contact_phone');
        delete_option('xml_export_germany_seller_contact_email');
        delete_option('xml_export_germany_use_sale_agent_as_seller', 0);

        delete_option('xml_export_unidad_tramitadora_code_field');
        delete_option('xml_export_unidad_tramitadora_name_field');
        delete_option('xml_export_unidad_tramitadora_address_field');
        delete_option('xml_export_unidad_tramitadora_postCode_field');
        delete_option('xml_export_unidad_tramitadora_town_field');
        delete_option('xml_export_unidad_tramitadora_province_field');

        delete_option('xml_export_oficina_contable_code_field');
        delete_option('xml_export_oficina_contable_name_field');
        delete_option('xml_export_oficina_contable_address_field');
        delete_option('xml_export_oficina_contable_postCode_field');
        delete_option('xml_export_oficina_contable_town_field');
        delete_option('xml_export_oficina_contable_province_field');

        delete_option('xml_export_organo_gestor_code_field');
        delete_option('xml_export_organo_gestor_name_field');
        delete_option('xml_export_organo_gestor_address_field');
        delete_option('xml_export_organo_gestor_postCode_field');
        delete_option('xml_export_organo_gestor_town_field');
        delete_option('xml_export_organo_gestor_province_field');


        delete_option('settings_xml_organo_proponente_code_field');
        delete_option('settings_xml_organo_proponente_name_field');
        delete_option('settings_xml_organo_proponente_address_field');
        delete_option('settings_xml_organo_proponente_postCode_field');
        delete_option('settings_xml_organo_proponente_town_field');
        delete_option('settings_xml_organo_proponente_province_field');

        delete_option('xml_export_spain_iban');
        delete_option('xml_export_spain_bic');

        delete_option('xml_export_spain_use_sale_agent_as_seller', 1);
        delete_option('xml_export_spain_seller_first_name');
        delete_option('xml_export_spain_seller_last_name');
    }
}
