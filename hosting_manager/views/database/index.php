<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class=" tw-mx-auto">
            <div class="sm:tw-flex sm:tw-justify-between sm:tw-items-center tw-mb-3 -tw-mt-px">
                <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-max-w-xl tw-truncate tw-space-x-1.5"
                    title="<?= isset($hosting) ? e($hosting->title) : ''; ?>">
                    <span>
                        <?= isset($hosting) ? e($hosting->title) : _l('hosting_information') ?>
                    </span>
                </h4>

            </div>
            <?php include(module_dir_path('hosting_manager').'views/tabs.php') ?>
            
            <div class="panel_s">
                <div class="panel-body">
                    <div class="tab-content">
                      
                        <div role="tabpanel" class="tab-pane active" id="tab_database">
                            <div id="database_list">
                                <a href="#" class="btn btn-primary pull-left mright5 new-task-relation" onclick="show_database_modal(); return false;">
                                    <i class="fa-regular fa-plus tw-mr-1"></i><?= _l('hosting_manager_add_database')?>
                                </a>
                                <div class="clearfix"></div>
                                <hr>
                                <div class="">
                                    <div class="panel-table-full">
                                    <?php render_datatable([
                                        _l('hosting_manager_title'),
                                        _l('hosting_manager_database_name'),
                                        _l('hosting_manager_username'),
                                        _l('hosting_manager_status'),
                                        ], 'database_manager'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('create_edit_modal.php') ?>

<?php init_tail(); ?>
<script>
    var hosting_id = '<?=$hosting->id?>';
    $(function() {
        initDataTable('.table-database_manager','<?=admin_url('hosting_manager/database/list?hosting_id='.$hosting_id)?>',[1]);
    });
    function show_database_modal(){
        $("body").find("#databse_modal").modal({
                    show: true,
                    backdrop: "static",
                });
    }

    function get_database_modal(id){
        var url = '<?=admin_url('hosting_manager/database/edit/')?>'+id;
        requestGet(url).done(function (response) {
                $("#database_modal_edit").html(response);
                $("body").find("#database_modal_edit").modal({
                    show: true,
                    backdrop: "static",
                });
                init_selectpicker();

        }).fail(function (error) {
            alert_float("danger", error.responseText);
        });

    }
</script>
</body>

</html>



























