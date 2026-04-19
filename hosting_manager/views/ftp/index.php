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
                      
                        <div role="tabpanel" class="tab-pane active" id="tab_ftp">
                            <div id="ftp_list">
                                <a href="#" class="btn btn-primary pull-left mright5 new-task-relation" onclick="show_ftp_modal(); return false;">
                                    <i class="fa-regular fa-plus tw-mr-1"></i><?= _l('hosting_manager_add_ftp')?>
                                </a>
                                <div class="clearfix"></div>
                                <hr>
                                <div class="">
                                    <div class="panel-table-full">
                                    <?php render_datatable([
                                        _l('hosting_manager_account_name'),
                                        _l('hosting_manager_hostname'),
                                        _l('hosting_manager_username'),
                                        _l('hosting_manager_port'),
                                        _l('hosting_manager_protocol'),
                                        _l('hosting_manager_root_directory'),
                                        _l('hosting_manager_status'),
                                        ], 'ftp_manager'); ?>
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
        initDataTable('.table-ftp_manager','<?=admin_url('hosting_manager/ftp/list?hosting_id='.$hosting_id)?>',[1]);
    });
    function show_ftp_modal(){
        $("body").find("#ftp_modal").modal({
                    show: true,
                    backdrop: "static",
                });
    }

    function get_ftp_modal(id){
        var url = '<?=admin_url('hosting_manager/ftp/edit/')?>'+id;
        requestGet(url).done(function (response) {
                $("#ftp_modal_edit").html(response);
                $("body").find("#ftp_modal_edit").modal({
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



























