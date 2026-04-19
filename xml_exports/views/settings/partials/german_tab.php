<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="alert alert-warning">
    <h5>To ensure that your EInvoice passes required validation, You should;</h5>
    <ul>
        <li>- Complete your company profile at <a href="<?= admin_url('settings?group=company') ?>">Setup->Settings->Company</a>.
        </li>
        <li>- Your customers are providing their VAT and completing their profile.</li>
        <li>- You provide a fallback contact details for your company below.</li>
    </ul>
</div>

<h3> <?= _l('xml_exports_germany'); ?></h3>

<div class="col-md-12 mbot10">
    <?php render_yes_no_option('xml_export_germany_use_sale_agent_as_seller', 'settings_xml_export_germany_use_sale_agent_as_seller'); ?>
</div>

<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_germany_seller_contact_person]', 'settings_xml_export_germany_seller_contact_person', get_option('xml_export_germany_seller_contact_person')); ?>
</div>


<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_germany_seller_contact_phone]', 'settings_xml_export_germany_seller_contact_phone', get_option('xml_export_germany_seller_contact_phone')); ?>
</div>

<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_germany_seller_contact_email]', 'settings_xml_export_germany_seller_contact_email', get_option('xml_export_germany_seller_contact_email')); ?>
</div>


<hr/>


<div class="col-md-12 mbot10">
    <?php
    $germanBuyerRefFields = get_custom_fields('invoice', ['show_on_client_portal' => 1]);
    array_unshift($germanBuyerRefFields, ['id' => '', 'name' => _l('settings_xml_invoice_number')]);
    echo render_select('settings[xml_export_invoice_buyer_reference_field]', $germanBuyerRefFields, ['id', 'name'], 'settings_xml_export_invoice_buyer_reference_field', get_option('xml_export_invoice_buyer_reference_field'), include_blank: false);
    ?>
    <small><?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('invoices')]) ?></small>
    <hr />
</div>
