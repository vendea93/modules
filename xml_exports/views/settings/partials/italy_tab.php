<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h3> <?= _l('xml_exports_italy'); ?></h3>

<hr>


<div class="col-md-12 mbot10">
    <?php
    $customFields = get_custom_fields('customers', ['show_on_client_portal' => 1]);
    echo render_select('settings[xml_export_customer_recipient_code_field]', $customFields, ['id', 'name'], 'settings_xml_export_customer_recipient_code_field', get_option('xml_export_customer_recipient_code_field'), [], [], '', '', true);
    ?>
    <small><?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('clients')]) ?></small>
</div>

<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_italy_transmission_format]', 'settings_xml_export_italy_transmission_format', get_option('xml_export_italy_transmission_format')); ?>
    <small><?= _l('settings_xml_export_italy_transmission_format_help') ?></small>
    <hr/>
</div>

<div class="col-md-12 mbot10">
    <?php if (get_option('xml_export_italy_certificate')) { ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-9">
                    <?= _l('settings_xml_export_certificate') ?>
                    : <?= get_option('xml_export_italy_certificate') ?>
                </div>
                <div class="col-md-3 text-right">
                    <a href="<?php echo admin_url('xml_exports/delete_certificate/' . \Techy4m\XmlExports\Enums\Scheme::Italy->value); ?>"
                       class="_delete text-danger"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    <?php } else { ?>

        <div class="form-group">
            <label for="xml_export_italy_certificate"
                   class="control-label"><?= _l('settings_xml_export_certificate'); ?></label>
            <input id="xml_export_italy_certificate" accept=".p12,.pfx" type="file"
                   name="xml_export_italy_certificate" class="form-control">
        </div>
        <ul>
            <li>- <?= _l('settings_xml_export_certificate_help'); ?></li>
            <li>- <?= _l('settings_xml_export_certificate_help2'); ?></li>
        </ul>
    <?php } ?>

</div>

<hr>

<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_italy_certificate_password]', 'settings_xml_export_certificate_password', get_option('xml_export_italy_certificate_password')); ?>
    <small><?= _l('settings_xml_export_certificate_password_help') ?></small>
</div>
<hr>
<div class="col-md-12 mbot10">

    <?php if (get_option('xml_export_italy_private_key')) { ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-9">
                    <?= _l('settings_xml_export_private_key') ?>
                    : <?= get_option('xml_export_italy_private_key') ?>
                </div>
                <div class="col-md-3 text-right">
                    <a href="<?php echo admin_url('xml_exports/delete_private_key/' . \Techy4m\XmlExports\Enums\Scheme::Italy->value); ?>"
                       class="_delete text-danger"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    <?php } else { ?>

        <div class="form-group">
            <label for="xml_export_italy_private_key" class="control-label">
                <?= _l('settings_xml_export_private_key'); ?>
            </label>
            <input type="file" accept=".key" name="xml_export_italy_private_key" class="form-control">
        </div>
        <small><?= _l('settings_xml_export_private_key_help') ?></small>
    <?php } ?>
</div>