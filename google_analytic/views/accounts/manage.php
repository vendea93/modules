<?php defined('BASEPATH') or exit('No direct script access allowed');?>

<?php init_head();?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
              <div class="panel-body">
                <div class="border-right">
                  <h4 class="no-margin font-bold"><?php echo _l($title); ?></h4>
                  <hr />
                </div>
               
               <div class="row">
                 <div class="col-md-6">
                   <?php if($check_config != 1){ 
                    echo '<p class="text-danger">'._l('please_enter_the_application_code_first').': <a href="'.admin_url('google_analytic/setting').'">Link</a> </p>';
                   } ?>
                   </div>
                   <div class="col-md-6">
                       <a href="#" onclick="add_account(); return false;" class="btn btn-primary mbot10 pull-right"><?php echo _l('ga_add_new', _l('property')); ?></a>
                   </div>
               </div>

               <hr class="hr-panel-heading" />
               <table class="table table-accounts">
                 <thead>
                   <th><?php echo _l('name'); ?></th>
                   <th><?php echo _l('description'); ?></th>
                   <th><?php echo _l('status'); ?></th>
                   <th><?php echo _l('active'); ?></th>
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

<div class="modal fade" id="account-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title add-title"><?php echo _l('ga_add_new', _l('property'))?></h4>
            <h4 class="modal-title edit-title"><?php echo _l('ga_edit', _l('property'))?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('google_analytic/account'),array('id'=>'account-form'));?>
         <?php echo form_hidden('id'); ?>
         <?php echo form_hidden('type', 'google_analytic'); ?>
         
         <div class="modal-body">
              <?php echo render_input('name', 'name') ?>
              <?php echo render_textarea('description', 'description') ?>
             
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-primary btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>
<!-- box loading -->
<div id="box-loading"></div>
<?php init_tail();?>
<?php require 'modules/google_analytic/assets/js/accounts/manage_js.php';?>
