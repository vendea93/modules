<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
    <div class="panel_s">
     <div class="panel-body">
      <div class="_buttons">
        <a href="#" onclick="add_workspace(); return false;" class="btn btn-primary mbot10"><?php echo _l('ga_add_new', _l('workspace')); ?></a>
        <?php echo form_hidden('csrf_token_name', $this->security->get_csrf_token_name()); ?>
        <?php echo form_hidden('csrf_token_hash', $this->security->get_csrf_hash()); ?>
        
      </div>
     
      <hr class="hr-panel-heading" />
      <table class="table table-workspaces">
        <thead>
          <th><?php echo _l('workspace_logo'); ?></th>
          <th><?php echo _l('name'); ?></th>
          <th><?php echo _l('super_admin'); ?></th>
          <th><?php echo _l('timezone'); ?></th>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>


<div class="modal fade" id="workspace-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('workspaces')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('google_analytic/workspace'),array('id'=>'workspace-form'));?>
         <?php echo form_hidden('id'); ?>
         
         <div class="modal-body">
              <?php echo render_input('workspace_logo', 'workspace_logo', '', 'file') ?>
              <?php echo render_input('name', 'name') ?>
              <?php echo render_select('super_admin', $staffs, array('staffid', array('firstname', 'lastname')), 'super_admin') ?>
              <div class="form-group">
                <label for="timezones" class="control-label"><?php echo _l('settings_localization_default_timezone'); ?></label>
                <select name="timezone" id="timezones" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                    <?php foreach(get_timezones_list() as $key => $timezones){ ?>
                    <optgroup label="<?php echo e($key); ?>">
                        <?php foreach($timezones as $timezone){ ?>
                        <option value="<?php echo e($timezone); ?>" <?php if(get_option('default_timezone') == $timezone){echo 'selected';} ?>><?php echo e($timezone); ?></option>
                        <?php } ?>
                    </optgroup>
                    <?php } ?>
                </select>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-primary btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>

<?php init_tail(); ?>
<?php require 'modules/google_analytic/assets/js/workspace/manage_js.php';?>

