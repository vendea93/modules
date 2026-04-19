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
                            <div class="col-md-12"> 

                                <?php if(has_permission('workflow_automation', '', 'create')){ ?>
                                    <a href="javascript:void(0);" onclick="create_flow(); return false;" class="btn btn-primary pull-left mright5"><?php echo _l('wa_create_work_flow'); ?></a>

                                <?php } ?>
                                    
                            </div>
                        </div>

                        <div class="row mbot15"> 
                            <div class="col-md-3">
                                           
                                        <?php 
                                       
                                      
                                        echo render_select('categories[]',$categories,array('id','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('wa_filter_by_category'),'multiple'=>true,'data-actions-box'=>true),array(),'no-mbot','',false); ?>
                        
                                    </div>

                                    <div class="col-md-2">
                                        <?php echo render_date_input('from_date','','', ['placeholder' => _l('lg_from_date')]); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?php echo render_date_input('to_date','','', ['placeholder' => _l('lg_to_date')]); ?>
                                    </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php
                        $table_data = [];
                                

                               $table_data = array_merge($table_data, [
                                _l('wa_name'),
                                _l('wa_owner'),
                                _l('wa_category'),
                                _l('wa_created_date'),
                                _l('wa_enabled'),
                                _l('wa_options'),
                              ]);

                           echo render_datatable($table_data, 'workflows', [],['id' => 'table-workflows']); ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="workflow_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog withd_1k" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('edit_workflow'); ?></span>
                    <span class="add-title"><?php echo _l('new_workflow'); ?></span>
                </h4>
            </div>
            <?php echo form_open('workflow_automation/workflow_form',array('id'=>'workflow-form')); ?>
            <?php echo form_hidden('workflow_id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <label for="name"><span class="text-danger">* </span><?php echo _l('wa_name'); ?></label>
                        <?php echo render_input('name','','','text', ['required' => 'true']); ?>

                        <div class="checkbox checkbox-primary hide">
                            <input type="checkbox" id="start_email" name="start_email" value="1">
                            <label for="start_email"><?php echo _l('email_forkflow_owner_once_when_worrkflow_starts'); ?>
                            </label>
                        </div>

                        <?php echo render_select('category_id', $categories, ['id', 'name'], 'wa_category'); ?>

                        <?php echo render_textarea('description','description'); ?>
                        

                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" id="private" name="private" checked value="1">
                            <label for="private"><?php echo _l('is_private_workflow'); ?>
                            </label>
                        </div>
                       
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<?php require 'modules/workflow_automation/assets/js/workflow/manage_js.php';?>