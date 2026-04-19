<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('hotel_management_system/properties/property'); ?>"
                               class="btn btn-primary pull-left display-block">
                                <i class="fa fa-plus"></i> <?php echo _l('new_property'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
						<?php render_datatable([
							_l('id'),
							_l('property'),
							_l('landlord'),
							_l('property_address'),
							_l('property_city'),
							_l('property_postal_code'),
							_l('property_country'),
							_l('property_status'),
							_l('options')
						], 'properties'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-properties', window.location.href + '/table', [0], [0], undefined, [1, 'asc']);
    });
</script>
</body>
</html>