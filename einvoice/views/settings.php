<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$templates = einvoice_module_get_templates();
$environments = einvoice_module_get_ksef_environment_options();
?>

<div class="com-md-12">
    <?= render_select('settings[einvoice_default_invoice_template]', $templates, ['id', 'name'], 'settings_einvoice_default_template', get_option('einvoice_default_invoice_template')); ?>
</div>

<?php render_yes_no_option('einvoice_send_as_invoice_email_attachment', 'settings_einvoice_send_as_invoice_email_attachment'); ?>
<hr />


<div class="com-md-12">
    <?= render_select('settings[einvoice_default_credit_note_template]', $templates, ['id', 'name'], 'settings_einvoice_default_credit_note_template', get_option('einvoice_default_credit_note_template')); ?>
</div>
<?php render_yes_no_option('einvoice_send_as_credit_note_email_attachment', 'settings_einvoice_send_as_credit_note_email_attachment'); ?>

<hr />

<h4 class="mbot15">
    <?= _l('settings_einvoice_templates') ?>
</h4>

<hr />

<h4 class="mbot15">
    <?= _l('einvoice_ksef_heading'); ?>
</h4>
<p class="text-muted">
    <?= _l('einvoice_ksef_help'); ?>
</p>

<?php render_yes_no_option('einvoice_ksef_enabled', 'einvoice_ksef_enabled'); ?>
<?php render_yes_no_option('einvoice_ksef_auto_sync', 'einvoice_ksef_auto_sync'); ?>
<?php render_yes_no_option('einvoice_ksef_include_pdf_link', 'einvoice_ksef_include_pdf_link'); ?>

<div class="row">
    <div class="col-md-6">
        <?= render_select('settings[einvoice_ksef_environment]', $environments, ['id', 'name'], 'einvoice_ksef_environment', get_option('einvoice_ksef_environment')); ?>
    </div>
    <div class="col-md-6">
        <?= render_input('settings[einvoice_ksef_company_nip]', 'einvoice_ksef_company_nip', get_option('einvoice_ksef_company_nip')); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= render_input('settings[einvoice_ksef_api_url]', 'einvoice_ksef_api_url', get_option('einvoice_ksef_api_url')); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= render_input('settings[einvoice_ksef_api_token]', 'einvoice_ksef_api_token', get_option('einvoice_ksef_api_token'), 'password'); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= render_select('settings[einvoice_ksef_invoice_template]', $templates, ['id', 'name'], 'einvoice_ksef_invoice_template', einvoice_module_get_ksef_template_id()); ?>
    </div>
</div>

<hr />

<?php $templates = einvoice_module_get_templates() ?>
<div class="tw-mb-6">
    <a href="<?= admin_url('einvoice/template') ?>"
        class="btn btn-primary">
        <i class="fa-regular fa-plus tw-mr-1"></i>
        <?= _l('settings_einvoice_templates'); ?>
    </a>
</div>
<table class="table dt-table">
    <thead>
        <tr>
            <th><?= _l('template_name') ?></th>
            <th><?= _l('template_type') ?>
            </th>
            <th><?= _l('options') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($templates as $template) { ?>

        <tr>
            <td><?= $template['name'] ?></td>
            <td><?= strtoupper($template['content_type']) ?>
            </td>
            <td>
                <div class="tw-flex tw-items-center tw-space-x-2">
                    <a href="<?= admin_url("einvoice/template/{$template['id']}") ?>"
                        class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                        <i class="fa-regular fa-pen-to-square fa-lg"></i>
                    </a>
                    <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"
                        onclick="delete_template(this,'einvoice_invoice','<?= $template['id'] ?>'); return false;">
                        <i class="fa-regular fa-trash-can"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
