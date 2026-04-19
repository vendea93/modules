<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_task_template(); return false;"><?php echo _l('wa_add_task_template'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('wa_template_name'); ?></th>
		<th><?php echo _l('wa_task_subject'); ?></th>
		<th><?php echo _l('wa_start_date'); ?></th>
		<th><?php echo _l('wa_due_date'); ?></th>
		<th><?php echo _l('wa_priority'); ?></th>
		<th><?php echo _l('wa_related_to'); ?></th>
		<th><?php echo _l('wa_options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($task_templates) && count($task_templates) > 0){ ?>
			<?php foreach($task_templates as $template){ ?>
				<tr>
					<td><?php echo wa_html_entity_decode($template['id']); ?></td>
					<td><?php echo wa_html_entity_decode($template['template_name']); ?></td>
					<td><?php echo wa_html_entity_decode($template['task_subject']); ?></td>
					<td><?php echo wa_html_entity_decode($template['start_date']); ?></td>
					<td><?php echo wa_html_entity_decode($template['due_date']); ?></td>
					<td><?php 
					$outputPriority = '<span style="color:' . e(task_priority_color($template['priority'])) . ';" class="inline-block">' . e(task_priority($template['priority']));
					$outputPriority .= '</span>';
					echo wa_html_entity_decode($outputPriority); ?>
						
					</td>
					<td><?php echo _l($template['rel_type']); ?></td>
					<td>
						<a href="#" onclick="edit_task_template(this,<?php echo wa_html_entity_decode($template['id']); ?>); return false" data-template_name="<?php echo wa_html_entity_decode($template['template_name']); ?>" data-task_subject="<?php echo wa_html_entity_decode($template['task_subject']); ?>" data-start_date="<?php echo wa_html_entity_decode($template['start_date']); ?>" data-due_date="<?php echo wa_html_entity_decode($template['due_date']); ?>" data-priority="<?php echo wa_html_entity_decode($template['priority']); ?>" data-rel_type="<?php echo wa_html_entity_decode($template['rel_type']); ?>" data-assignees="<?php echo wa_html_entity_decode($template['assignees']); ?>" data-followers="<?php echo wa_html_entity_decode($template['followers']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('workflow_automation/delete_task_template/' . $template['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="task_template_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_task_template'); ?></span>
					<span class="add-title"><?php echo _l('new_task_template'); ?></span>
				</h4>
			</div>
			<?php echo form_open('workflow_automation/task_template_form',array('id'=>'task_template-setting-form')); ?>
			<?php echo form_hidden('task_template_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="template_name"><span class="text-danger">* </span><?php echo _l('wa_template_name'); ?></label>
						<?php echo render_input('template_name','','','text', ['required' => 'true']); ?>

						<label for="task_subject"><span class="text-danger">* </span><?php echo _l('wa_task_subject'); ?></label>
						<?php echo render_input('task_subject','','','text', ['required' => 'true']); ?>
						
					</div>
					<div class="col-md-6">
						<?php echo render_date_input('start_date','wa_start_date',''); ?>
					</div>
					<div class="col-md-6">	
						<?php echo render_date_input('due_date','wa_due_date',''); ?>
					</div>
					<div class="col-md-6">
                        <div class="form-group">
                            <label for="priority"
                                class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
                            <select name="priority" class="selectpicker" id="priority" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php foreach (get_tasks_priorities() as $priority) { ?>
                                <option value="<?php echo e($priority['id']); ?>" ><?php echo e($priority['name']); ?></option>
                                <?php } ?>
                               
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rel_type"
                                class="control-label"><?php echo _l('task_related_to'); ?></label>
                            <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <option value="project"><?php echo _l('project'); ?></option>
                                <option value="invoice"><?php echo _l('invoice'); ?></option>
                                <option value="customer"><?php echo _l('client'); ?></option>
                                <option value="estimate"><?php echo _l('estimate'); ?></option>
                                <option value="contract"><?php echo _l('contract'); ?></option>
                                <option value="ticket"><?php echo _l('ticket'); ?></option>
                                <option value="expense"><?php echo _l('expense'); ?></option>
                                <option value="lead"><?php echo _l('lead'); ?></option>
                                <option value="proposal"><?php echo _l('proposal'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group select-placeholder>">
                            <label for="assignees"><?php echo _l('task_single_assignees'); ?></label>
                            <select name="assignees[]" id="assignees" class="selectpicker" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                multiple data-live-search="true">
                                <?php foreach ($members as $member) { ?>
                                <option value="<?php echo e($member['staffid']); ?>" <?php if ((get_option('new_task_auto_assign_current_member') == '1') && get_staff_user_id() == $member['staffid']) {
                        echo 'selected';
                    } ?>>
                                    <?php echo e($member['firstname'] . ' ' . $member['lastname']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php
			             $follower = (get_option('new_task_auto_follower_current_member') == '1') ? [get_staff_user_id()] : '';
			             echo render_select('followers[]', $members, ['staffid', ['firstname', 'lastname']], 'task_single_followers', $follower, ['multiple' => true], [], '', '', false);
			             ?>
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