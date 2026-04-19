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

                        <div class="row">
                            <div class="col-md-4">
                                <p><strong><?php echo _l('wa_name'); ?></strong></p>
                                <span><?php echo wa_html_entity_decode($workflow->name); ?></span>
                            </div>

                            <div class="col-md-4">
                                <p><strong><?php echo _l('wa_date_created'); ?></strong></p>
                                <span><?php echo wa_html_entity_decode($workflow->created_at); ?></span>
                                
                            </div>

                            <div class="col-md-4">
                                <p><strong><?php echo _l('wa_description'); ?></strong></p>
                                 <span><?php echo wa_html_entity_decode($workflow->description); ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel_s panel-table-full">
                    <div class="panel-body">

                        <div class="horizontal-scrollable-tabs preview-tabs-top">
                            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                            <div class="horizontal-tabs">
                                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#workflow" aria-controls="workflow" role="tab" data-toggle="tab">
                                         <?php echo _l('wa_workflow'); ?>
                                        </a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#history_logs" aria-controls="history_logs" role="tab" data-toggle="tab">
                                         <?php echo _l('wa_history_logs'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="workflow">
                                <div class="wrapper">
                                    <div class="col-md-12">
                                      <div id="drawflow" ondrop="drop(event)" ondragover="allowDrop(event)">
                                        <div class="btn-export" onclick="builder(); return false;"><?php echo _l('builder'); ?></div>
                                      </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane " id="history_logs">

                                <table class="table dt-table" id="received_vouchers_table">
                                    <thead>
                                        <th><?php echo _l('wa_date_time'); ?></th>
                                        <th><?php echo _l('wa_node_name'); ?></th>
                                        <th><?php echo _l('wa_relation_type'); ?></th>
                                        <th><?php echo _l('wa_related_to'); ?></th>
                                        <th><?php echo _l('wa_condition_field'); ?></th>
                                        <th><?php echo _l('wa_condition'); ?></th>
                                        
                                        <th><?php echo _l('wa_action'); ?></th>
                                        <th><?php echo _l('wa_output'); ?></th>
                                        <th><?php echo _l('wa_result'); ?></th>
                                         
                                    </thead>
                                    <tbody>
                                        <?php if(count($history_logs) > 0){ ?>
                                            <?php foreach($history_logs as $log){ ?>
                                                <tr>
                                                    <td><?php echo _dt($log['created_at']); ?></td>
                                                    <td><?php echo ($log['node_type'] != '' ? _l('wa_'.$log['node_type']).' - '.$log['node_id'] : ''); ?></td>
                                                    <td><?php echo _l('wa_'.$log['rel_type']); ?></td>
                                                    <td>
                                                        <?php 
                                                            echo wa_get_related_to_info($log['rel_type'], $log['rel_id']);
                                                        ?>
                                                    </td>
                                                    <td><?php echo ($log['node_type'] == 'condition') ? _l($log['condition_field']) : '';  ?></td>
                                                    <td><?php echo ($log['node_type'] == 'condition') ? _l('wa_'.$log['condition']) : '';  ?></td>
                                                   
                                                    <td><?php echo  ($log['node_type'] == 'action') ? _l('wa_'.$log['action']) : '';  ?></td>
                                                    <td>
                                                        <?php 
                                                        echo ($log['node_type'] == 'condition') ? _l('wa_'.$log['output']) : '';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if($log['result'] == 'success'){
                                                            echo  ($log['node_type'] == 'action' || $log['node_type'] == 'flow_start') ? '<span class="label label-success">'._l('wa_'.$log['result']).'</span>' : '';
                                                        }else if($log['result'] == 'fail'){
                                                            echo  ($log['node_type'] == 'action' || $log['node_type'] == 'flow_start') ?  '<span class="label label-danger">'._l('wa_'.$log['result']).'</span>' : '';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>

                                            <?php } ?>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>

                    </div>
                </div>
                
            </div>

            <?php echo form_hidden('workflow_id',(isset($workflow) ? $workflow->id : '') ); ?>
            <?php echo form_hidden('workflow',(isset($workflow) ? $workflow->workflow : '')); ?>
            <?php echo form_hidden('workflow_type',(isset($workflow) ? $workflow->type : '')); ?>

        </div>
    </div>
</div>
<?php init_tail(); ?>

<?php require 'modules/workflow_automation/assets/js/workflow/workflow_builder_js.php';?>
