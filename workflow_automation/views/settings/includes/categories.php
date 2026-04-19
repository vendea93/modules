<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="_buttons">
	<a href="#" class="btn btn-info pull-left" onclick="new_category(); return false;"><?php echo _l('wa_add_category'); ?></a>
</div>
<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="clearfix"></div>
<table class="table dt-table">
	<thead>
		<th><?php echo _l('id'); ?></th>
		<th><?php echo _l('wa_name'); ?></th>
		<th><?php echo _l('wa_options'); ?></th>
	</thead>
	<tbody>
		<?php if(isset($categories) && count($categories) > 0){ ?>
			<?php foreach($categories as $category){ ?>
				<tr>
					<td><?php echo wa_html_entity_decode($category['id']); ?></td>
					<td><?php echo wa_html_entity_decode($category['name']); ?></td>
					<td>
						<a href="#" onclick="edit_category(this,<?php echo wa_html_entity_decode($category['id']); ?>); return false" data-name="<?php echo wa_html_entity_decode($category['name']); ?>" data-description="<?php echo wa_html_entity_decode($category['description']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square"></i></a>

          				<a href="<?php echo admin_url('workflow_automation/delete_category/' . $category['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>

					</td>
				</tr>		

			<?php } ?>
		<?php } ?>
	</tbody>
</table>

<div class="modal fade" id="category_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog withd_1k" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					<span class="edit-title"><?php echo _l('edit_category'); ?></span>
					<span class="add-title"><?php echo _l('new_category'); ?></span>
				</h4>
			</div>
			<?php echo form_open('workflow_automation/category_form',array('id'=>'category-setting-form')); ?>
			<?php echo form_hidden('category_id'); ?>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<label for="category_name"><span class="text-danger">* </span><?php echo _l('wa_category_name'); ?></label>
						<?php echo render_input('name','','','text', ['required' => 'true']); ?>

						<label for="task_subject"><?php echo _l('wa_description'); ?></label>
						<?php echo render_textarea('description','','',); ?>
						
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