<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#xml_exports_general" aria-controls="misc" role="tab" data-toggle="tab">
                    <i class="fa fa-cog"></i> <?php echo _l('settings_group_general'); ?></a>
            </li>
            <li role="presentation">
                <a href="#xml_exports_general_peppol" aria-controls="settings_tables" role="tab" data-toggle="tab">
                    <?php echo _l('xml_exports_peppol'); ?></a>
            </li>
            <li role="presentation">
                <a href="#xml_exports_es" aria-controls="inline_create" role="tab" data-toggle="tab">
                    <?php echo _l('xml_exports_spain'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#xml_exports_it" aria-controls="inline_create" role="tab" data-toggle="tab">
                    <?php echo _l('xml_exports_italy'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#xml_exports_de" aria-controls="inline_create" role="tab" data-toggle="tab">
                    <?php echo _l('xml_exports_germany'); ?>
                </a>
            </li>
        </ul>
    </div>

</div>

<div class="tab-content mtop15">
    <div role="tabpanel" class="tab-pane active" id="xml_exports_general">
        <h3> <?= _l('settings_group_general'); ?></h3>
        <hr>
        <div class="row mbot30">
            <div class="col-md-6 mbot10">
                <?php render_yes_no_option('xml_export_attach_xml_to_invoice_emails', 'settings_xml_export_attach_xml_to_invoice_emails'); ?>
            </div>

            <div class="col-md-12 mbot10">
                <?= render_select('settings[xml_export_active_scheme]', \Techy4m\XmlExports\Enums\Scheme::AsSelectArray(), ['id', 'name'], 'settings_xml_export_active_scheme', get_option('xml_export_active_scheme'), include_blank: false); ?>
            </div>
        </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="xml_exports_general_peppol">
        <?php $this->load->view('xml_exports/settings/partials/peppol_tab'); ?>
    </div>

    <div role="tabpanel" class="tab-pane" id="xml_exports_es">
        <?php $this->load->view('xml_exports/settings/partials/spain_tab'); ?>
    </div>

    <div role="tabpanel" class="tab-pane" id="xml_exports_it">
        <?php $this->load->view('xml_exports/settings/partials/italy_tab'); ?>
    </div>

    <div role="tabpanel" class="tab-pane" id="xml_exports_de">
        <?php $this->load->view('xml_exports/settings/partials/german_tab'); ?>
    </div>
</div>

