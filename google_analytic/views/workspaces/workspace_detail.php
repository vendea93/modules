<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6">
            <div class="panel_s mtop15">
               <div class="panel-body">
                  <h4 class="customer-profile-group-heading"><?php echo _l('workspace'); ?></h4>
                  <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                      <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                      <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                      <div class="horizontal-tabs">
                          <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                              <li role="presentation" class="active">
                                  <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab"
                                      data-toggle="tab">
                                      <?php echo _l('general_information'); ?>
                                  </a>
                              </li>
                          </ul>
                      </div>
                  </div>
                  <div class="tab-content tw-mt-5">
                      <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">
                 <?php echo form_open_multipart(admin_url('google_analytic/workspace'),array('id'=>'workspace-form'));?>

                 <?php echo form_hidden('id', $workspace->id); ?>


                  <?php if ($workspace->workspace_logo == null) { ?>
                  <?php echo render_input('workspace_logo', 'workspace_logo', '', 'file') ?>
                  <?php } ?>
                  <?php if ($workspace->workspace_logo != null) { ?>
                  <div class="form-group">
                      <div class="row">
                          <div class="col-md-9">
                              <?php echo ga_workspace_logo_html($workspace->id, ['img', 'img-responsive', 'staff-profile-image-thumb'], 'thumb'); ?>
                          </div>
                          <div class="col-md-3 text-right">
                              <a href="<?php echo admin_url('google_analytic/remove_workspace_logo/'.$workspace->id); ?>"><i
                                      class="fa fa-remove"></i></a>
                          </div>
                      </div>
                  </div>
                  <?php } ?>
                  <?php echo render_input('name', 'name', $workspace->name) ?>
                  <?php echo render_select('super_admin', $staffs, array('staffid', array('firstname', 'lastname')), 'super_admin', $workspace->super_admin) ?>
                  <div class="form-group">
                     <label for="timezones" class="control-label"><?php echo _l('settings_localization_default_timezone'); ?></label>
                     <select name="timezone" id="timezones" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                        <?php foreach(get_timezones_list() as $key => $timezones){ ?>
                        <optgroup label="<?php echo e($key); ?>">
                              <?php foreach($timezones as $timezone){ ?>
                           <option value="<?php echo e($timezone); ?>" <?php if($workspace->timezone == $timezone){echo 'selected';} ?>><?php echo e($timezone); ?></option>
                              <?php } ?>
                        </optgroup>
                        <?php } ?>
                     </select>
                  </div>
                  <?php echo render_textarea('notes', 'notes', $workspace->notes) ?>
                  <hr>
                  <?php if(is_admin() || $workspace->super_admin == get_staff_user_id() || $workspace->addedfrom == get_staff_user_id()){ ?>
                  <button type="submit" class="btn btn-primary btn-submit"><?php echo _l('submit'); ?></button>
                    <?php } ?>  
                    <?php echo form_close(); ?>  
               </div>
               </div>
            </div>
         </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s mtop15">
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-12">
                  <?php if(is_admin() || $workspace->super_admin == get_staff_user_id() || $workspace->addedfrom == get_staff_user_id()){ ?>
                     <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add-member-modal"><?php echo _l('add_new_member'); ?></button>
                    <?php } ?>  

                  </div>
                  </div>
                  <hr>
                  <table class="table table-members">
                    <thead>
                      <th><?php echo _l('name'); ?></th>
                      <th><?php echo _l('addedfrom'); ?></th>
                      <th><?php echo _l('dateadded'); ?></th>
                      <th><?php echo _l('action'); ?></th>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                  <hr>
                      <div class="row">
                     <div class="col-md-12">
                  <?php if(is_admin() || $workspace->super_admin == get_staff_user_id() || $workspace->addedfrom == get_staff_user_id()){ ?>

                  <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#add-contact-modal"><?php echo _l('add_contact_client'); ?></button>
                    <?php } ?>  

                      </div>
                      </div>
                  <hr>
                  <table class="table table-contacts">
                    <thead>
                      <th><?php echo _l('name'); ?></th>
                      <th><?php echo _l('addedfrom'); ?></th>
                      <th><?php echo _l('dateadded'); ?></th>
                      <th><?php echo _l('action'); ?></th>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="add-member-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('add_new_member')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('google_analytic/add_workspace_member'),array('id'=>'add-member-form'));?>
         <?php echo form_hidden('workspace_id', $workspace->id); ?>
         <?php echo form_hidden('type', 'staff'); ?>
         <div class="modal-body">
              <?php echo render_select('members[]', $staffs, array('staffid', array('firstname', 'lastname')), 'member','',array('multiple'=>true,'data-actions-box'=>true),array(),'','',false) ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-primary btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>

<div class="modal fade" id="add-contact-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('add_new_contact')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('google_analytic/add_workspace_member'),array('id'=>'add-contact-form'));?>
         <?php echo form_hidden('workspace_id', $workspace->id); ?>
         <?php echo form_hidden('type', 'contact'); ?>
         <div class="modal-body">
              <?php echo render_select('members[]', $contacts, array('id', array('firstname', 'lastname'), 'company'), 'contact','',array('multiple'=>true,'data-actions-box'=>true),array(),'','',false) ?>
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
<?php require 'modules/google_analytic/assets/js/workspace/workspace_detail_js.php';?>
