<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h3> <?= _l('xml_exports_spain'); ?></h3>
<hr>
<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_spain_series]', 'settings_xml_export_spain_series', get_option('xml_export_spain_series')); ?>
</div>

<div class="col-md-12 row">
    <hr>
    <div class="col-md-12 mbot10">
        <?php render_yes_no_option('xml_export_spain_use_sale_agent_as_seller', 'settings_xml_export_spain_use_sale_agent_as_seller'); ?>
    </div>
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_spain_seller_first_name]', 'settings_xml_export_spain_seller_first_name', get_option('xml_export_spain_seller_first_name')); ?>
    </div>
    <div class="col-md-6 mbot10">
        <?= render_input('settings[xml_export_spain_seller_last_name]', 'settings_xml_export_spain_seller_last_name', get_option('xml_export_spain_seller_last_name')); ?>
    </div>
</div>


<div class="col-md-12 mbot10">
    <hr>
    <?= render_input('settings[xml_export_spain_iban]', 'settings_xml_export_spain_iban', get_option('xml_export_spain_iban')); ?>
</div>

<hr>

<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_spain_bic]', 'settings_xml_export_spain_bic', get_option('xml_export_spain_bic')); ?>
</div>

<hr>

<div class="col-md-12 mbot10">
    <?php if (get_option('xml_export_spain_certificate')) { ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-9">
                    <?= _l('settings_xml_export_certificate') ?>
                    : <?= get_option('xml_export_spain_certificate') ?>
                </div>
                <div class="col-md-3 text-right">
                    <a href="<?php echo admin_url('xml_exports/delete_certificate/' . \Techy4m\XmlExports\Enums\Scheme::Spain->value); ?>"
                       class="_delete text-danger"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    <?php } else { ?>

        <div class="form-group">
            <label for="xml_export_spain_certificate"
                   class="control-label"><?= _l('settings_xml_export_certificate'); ?></label>
            <input id="xml_export_spain_certificate" accept=".p12,.pfx" type="file"
                   name="xml_export_spain_certificate" class="form-control">
        </div>
        <ul>
            <li>- <?= _l('settings_xml_export_certificate_help'); ?></li>
            <li>- <?= _l('settings_xml_export_certificate_help2'); ?></li>
        </ul>
    <?php } ?>
</div>
<hr>
<div class="col-md-12 mbot10">
    <?= render_input('settings[xml_export_spain_certificate_password]', 'settings_xml_export_certificate_password', get_option('xml_export_spain_certificate_password')); ?>
    <small><?= _l('settings_xml_export_certificate_password_help') ?></small>
</div>
<hr>
<div class="col-md-12 mbot10">

    <?php if (get_option('xml_export_spain_private_key')) { ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-9">
                    <?= _l('settings_xml_export_private_key') ?>
                    : <?= get_option('xml_export_spain_private_key') ?>
                </div>
                <div class="col-md-3 text-right">
                    <a href="<?php echo admin_url('xml_exports/delete_private_key/' . \Techy4m\XmlExports\Enums\Scheme::Spain->value); ?>"
                       class="_delete text-danger"><i class="fa fa-remove"></i></a>
                </div>
            </div>
        </div>
    <?php } else { ?>

        <div class="form-group">
            <label for="xml_export_spain_private_key" class="control-label">
                <?= _l('settings_xml_export_private_key'); ?>
            </label>
            <input type="file" accept=".key" name="xml_export_spain_private_key" class="form-control">
        </div>
        <small><?= _l('settings_xml_export_private_key_help') ?></small>
    <?php } ?>
</div>



<?php $spainCustomerFields = get_custom_fields('customers', ['show_on_client_portal' => 1]); ?>

<div class="col-md-12 alert alert-info">
    <?= _l('settings_xml_export_custom_field_help_text', [admin_url('custom_fields'), _l('clients')]) ?>
</div>
<div class="col-md-12 row">
    <div class="col-md-6 row">
        <div class="col-md-12 mbot25">
            <h4 class="bold"><?= _l('settings_xml_organo_gestor') ?></h4>
        </div>
        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_code_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_code_field', get_option('xml_export_organo_gestor_code_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_name_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_name_field', get_option('xml_export_organo_gestor_name_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_address_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_address_field', get_option('xml_export_organo_gestor_address_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_postCode_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_postCode_field', get_option('xml_export_organo_gestor_postCode_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_town_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_town_field', get_option('xml_export_organo_gestor_town_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_organo_gestor_province_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_province_field', get_option('xml_export_organo_gestor_province_field')); ?>
        </div>
    </div>
    <div class="col-md-6 row">
        <div class="col-md-12 mbot25">
            <h4 class="bold"><?= _l('settings_xml_unidad_tramitadora') ?></h4>
        </div>
        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_code_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_code_field', get_option('xml_export_unidad_tramitadora_code_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_name_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_name_field', get_option('xml_export_unidad_tramitadora_name_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_address_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_address_field', get_option('xml_export_unidad_tramitadora_address_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_postCode_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_postCode_field', get_option('xml_export_unidad_tramitadora_postCode_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_town_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_town_field', get_option('xml_export_unidad_tramitadora_town_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_unidad_tramitadora_province_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_province_field', get_option('xml_export_unidad_tramitadora_province_field')); ?>
        </div>
    </div>

    <div class="col-md-6 row">
        <div class="col-md-12 mbot25">
            <h4 class="bold"><?= _l('settings_xml_oficina_contable') ?></h4>
        </div>
        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_code_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_code_field', get_option('xml_export_oficina_contable_code_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_name_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_name_field', get_option('xml_export_oficina_contable_name_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_address_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_address_field', get_option('xml_export_oficina_contable_address_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_postCode_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_postCode_field', get_option('xml_export_oficina_contable_postCode_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_town_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_town_field', get_option('xml_export_oficina_contable_town_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[xml_export_oficina_contable_province_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_province_field', get_option('xml_export_oficina_contable_province_field')); ?>
        </div>
    </div>

    <div class="col-md-6 row">
        <div class="col-md-12 mbot25">
            <h4 class="bold"><?= _l('settings_xml_organo_proponente') ?></h4>
        </div>
        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_code_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_code_field', get_option('settings_xml_organo_proponente_code_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_name_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_name_field', get_option('settings_xml_organo_proponente_name_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_address_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_address_field', get_option('settings_xml_organo_proponente_address_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_postCode_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_postCode_field', get_option('settings_xml_organo_proponente_postCode_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_town_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_town_field', get_option('settings_xml_organo_proponente_town_field')); ?>
        </div>

        <div class="col-md-12 mbot10">
            <?= render_select('settings[settings_xml_organo_proponente_province_field]', $spainCustomerFields, ['id', 'name'], 'settings_xml_export_public_administration_province_field', get_option('settings_xml_organo_proponente_province_field')); ?>
        </div>
    </div>
</div>