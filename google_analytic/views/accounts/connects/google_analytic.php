<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <h4><?php echo _l('properties_list'); ?></h4>
         <?php echo form_open_multipart(admin_url('google_analytic/google_analytic_connect_save'),array('id'=>'workspace-form'));?>
          <?php echo form_hidden('account_id', $account_id); ?>
          <?php echo form_hidden('expires_in', $expires_in); ?>
          <?php echo form_hidden('access_token', $access_token); ?>
          <?php echo form_hidden('refresh_token', $refresh_token); ?>

          <div class="account-list">
            <?php foreach ($accounts as $account) { ?>
              <?php foreach ($account['properties'] as $properties) { 
                $id = explode('/', $properties['name'])[1];
                ?>
                <label class="account-option">
                  <input type="radio" name="page_id" value="<?php echo e($id); ?>">
                  <span class="custom-radio"></span>
                  <?php echo e($properties['displayName']); ?>
                </label>

              <?php } ?>
            <?php } ?>
          </div>
          
          <hr>
          <button type="submit" class="btn btn-primary btn-submit"><?php echo _l('submit'); ?></button>
         <?php echo form_close(); ?>  
          
        </div>
      </div>
    </div>
  </div>
</div>
<!-- box loading -->
<div id="box-loadding"></div>
<?php init_tail(); ?>
