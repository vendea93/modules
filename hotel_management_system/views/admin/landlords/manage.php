<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-3">
                    <h4 class="tw-my-0 tw-font-bold tw-text-xl">
						<?= _l('landlords'); ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('hotel_management_system/landlords/landlord'); ?>"
                               onclick="new_source(); return false;" class="btn btn-primary">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
								<?= _l('new_landlord'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>

						<?php render_datatable([
							_l('id'),
							_l('name'),
							_l('client_email'),
							_l('client_phonenumber'),
							_l('clients_list_company'),
							_l('client_city'),
							_l('clients_country'),
							_l('hms_landlord_commission_rate'),
							_l('date_created'),
							_l('hms_landlord_status'),
							_l('options')
						], 'landlords'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(function () {
        initDataTable('.table-landlords', '<?php echo admin_url('hotel_management_system/landlords/index'); ?>', [0], [0], undefined, [8, 'desc']);
    });
</script>