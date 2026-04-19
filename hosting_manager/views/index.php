<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="col-md-12 px-1">
            <div class="tw-mb-2 sm:tw-mb-4">
                <div class="_buttons">
                <?php if(has_permission('hosting_manager', get_staff_user_id(), 'create')){ ?>
                    <a  class="btn btn-primary pull-left display-block new-proposal-btn" href="<?=admin_url('hosting_manager/create')?>">
                        <i class="fa-regular fa-plus tw-mr-1"></i><?php echo _l('hosting_manager_add') ?> 
                    </a>
                <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="row">
                <div class="col-md-12" id="small-table">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="panel-table-full">
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
                </div>
            </div>
        </div>
    </div>
</div>



<?php init_tail(); ?>



<script>
$(function() {
    initDataTable('.table-hosting_manager', window.location.href);
});

</script>
</body>
</html>