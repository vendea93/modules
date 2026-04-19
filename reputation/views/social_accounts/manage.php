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
            <div class="horizontal-tabs mb-5">
              <ul class="nav nav-tabs nav-tabs-horizontal mb-10">
              <?php echo form_hidden('type', $type);?>
              <?php
              foreach($tab as $gr){ ?> 
                <li<?php if($type == $gr){echo " class='active'"; } ?>>
                <a href="<?php echo admin_url('reputation/social_accounts?type='.$gr); ?>" data-group="<?php echo html_entity_decode($gr); ?>">
                  <img src="<?php echo base_url('modules/reputation/assets/images/'.$gr.'_icon.png'); ?>" alt="Girl in a jacket" width="20" height="20">
                   <?php echo _l($gr); ?>
                  </a>
                </li>
                <?php 
              } ?>
              </ul>
              </div>
              <?php $this->load->view($tabs['view']); ?>
              <br>
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
            <h4 class="modal-title add-title"><?php echo _l('sa_add_new', _l('account'))?></h4>
            <h4 class="modal-title edit-title"><?php echo _l('sa_edit', _l('account'))?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/social_account'),array('id'=>'account-form'));?>
         <?php echo form_hidden('id'); ?>
         <?php echo form_hidden('type', $type); ?>
         
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
<?php require 'modules/reputation/assets/js/social_accounts/manage_js.php';?>
