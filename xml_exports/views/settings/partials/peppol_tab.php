<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="alert alert-info">
    <p><?= _l('settings_xml_exports_help') ?></p>
    <hr>
    <p><strong>Note</strong>: Only use for other eu countries without a dedicated Scheme pre-configured.</p>
</div>
<div class="row mbot30">
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_electronic_address]', 'settings_xml_export_electronic_address', get_option('xml_export_electronic_address')); ?>
    </div>
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_electronic_address_scheme]', 'settings_xml_export_electronic_address_scheme', get_option('xml_export_electronic_address_scheme')); ?>
        <small><?= _l('settings_xml_export_help_link', "https://docs.peppol.eu/poacc/billing/3.0/codelist/eas") ?></small>
    </div>
    <div class="col-md-12">
        <hr/>
    </div>
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_company_id]', 'settings_xml_export_company_id', get_option('xml_export_company_id')); ?>
        <small><?= _l('settings_xml_export_help_link', "https://docs.peppol.eu/poacc/billing/3.0/syntax/ubl-invoice/cac-AccountingSupplierParty/cac-Party/cac-PartyLegalEntity/cbc-CompanyID/") ?></small>
    </div>
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_company_id_scheme]', 'settings_xml_export_company_id_scheme', get_option('xml_export_company_id_scheme')); ?>
        <small><?= _l('settings_xml_export_help_link', "https://docs.peppol.eu/edelivery/codelists/v7.4/Peppol%20Code%20Lists%20-%20Participant%20identifier%20schemes%20v7.4.html") ?></small>
    </div>
    <div class="col-md-12">
        <hr/>
    </div>
    <div class="col-md-12 mbot10">
        <?php
        $customFields = get_custom_fields('customers', ['show_on_client_portal' => 1]);
        echo render_select('settings[xml_export_customer_electronic_address_field]', $customFields, ['id', 'name'], 'settings_xml_export_customer_electronic_address_field', get_option('xml_export_customer_electronic_address_field'), [], [], '', '', false);
        ?>
        <small><?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('clients')]) ?></small>
    </div>
    <div class="col-md-12">
        <hr/>
    </div>
    <div class="col-md-12 mbot10">
        <?php
        $customFields = get_custom_fields('customers', ['show_on_client_portal' => 1]);
        echo render_select('settings[xml_export_customer_electronic_address_scheme_field]', $customFields, ['id', 'name'], 'settings_xml_export_customer_electronic_address_scheme_field', get_option('xml_export_customer_electronic_address_scheme_field'), [], [], '', '', false);
        ?>
        <small><?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('clients')]) ?></small>
    </div>
    <div class="col-md-12">
        <hr/>
    </div>
    <div class="col-md-12 mbot10">
        <?php
        $customFields = get_custom_fields('invoice', ['show_on_client_portal' => 1]);
        echo render_select('settings[xml_export_invoice_buyer_reference_field]', $customFields, ['id', 'name'], 'settings_xml_export_invoice_buyer_reference_field', get_option('xml_export_invoice_buyer_reference_field'), [], [], '', '', false);
        ?>
        <small><?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('invoices')]) ?></small>
        <hr />
    </div>


    <div class="col-md-12 mbot10">
        <?= render_input('settings[xml_export_peppol_account_number]', 'settings_xml_export_peppol_account_number', get_option('xml_export_peppol_account_number')); ?>
    </div>

    <div class="col-md-12 mbot10">
        <?= render_input('settings[xml_export_peppol_account_name]', 'settings_xml_export_peppol_account_name', get_option('xml_export_peppol_account_name')); ?>
    </div>

    <div class="col-md-12 mbot10">
        <?= render_input('settings[xml_export_peppol_bank_name]', 'settings_xml_export_peppol_bank_name', get_option('xml_export_peppol_bank_name')); ?>
    </div>
</div>
