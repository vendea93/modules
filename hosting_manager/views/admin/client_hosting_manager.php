<?php
defined('BASEPATH') or exit('No direct script access allowed');
 defined('BASEPATH') or exit('No direct script access allowed'); 
$isGridView = 0;

?>
<?php if (isset($client)) { ?>
<h4 class="customer-profile-group-heading">
    <?= _l('hosting_manager'); ?>

</h4>
<div class="row">
    <div class="col-md-12">
        <div class="panel-body">

            <div class="row" id="hosting_manager-table">
                    <?php render_datatable([
                        _l('id'),
                        _l('hosting_manager_title'),
                        _l('hosting_manager_provider'),
                        _l('hosting_manager_client'),
                        _l('hosting_manager_project'),
                        _l('hosting_manager_start_date'),
                        _l('hosting_manager_expiry_date'),
                        _l('hosting_manager_status'),
                        ], 'hosting_manager'); ?>
                </div>
            </div>
        </div>
                        <?php }?>
        <?php init_tail(); ?>
        <script>
        $(function() {
                initDataTable('.table-hosting_manager', '<?=admin_url('hosting_manager?client='.$client->userid)?>');
            });
        </script>