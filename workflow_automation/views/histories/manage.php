<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><i class="fa fa-tasks" aria-hidden="true"></i> <?php echo wa_html_entity_decode($title); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row mbot15"> 
                            <div class="col-md-3">
                                <?php 
                                echo render_select('workflow[]',$workflows,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('wa_filter_by_workflow'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                
                            </div>

                            <div class="col-md-2">
                                <?php echo render_date_input('from_date','','', ['placeholder' => _l('lg_from_date')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?php echo render_date_input('to_date','','', ['placeholder' => _l('lg_to_date')]); ?>
                            </div>
                            <?php if(is_admin()){ ?>
                            <div class="col-md-2">
                                <a href="<?php echo admin_url('workflow_automation/clear_logs'); ?>" class="btn btn-danger _delete"><i class="fa fa-trash"></i><?php echo ' '._l('wa_clear_logs'); ?></a>
                            </div>
                            <?php } ?>
                        </div>

                        <div class="clearfix"></div>
                        <?php
                        $table_data = [];
                                

                               $table_data = array_merge($table_data, [
                                _l('wa_date_time'),
                                _l('wa_workflow'),
                                _l('wa_node_name'),
                                _l('wa_relation_type'),
                                _l('wa_related_to'),
                                _l('wa_condition_field'),
                                _l('wa_condition'),
                                _l('wa_action'),
                                _l('wa_output'),
                                _l('wa_result'),
                              ]);

                           echo render_datatable($table_data, 'histories', [],['id' => 'table-histories']); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php require 'modules/workflow_automation/assets/js/histories/manage_js.php';?>