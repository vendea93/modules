<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <h4><?php echo _l('account_list'); ?></h4>
         <?php echo form_open_multipart(admin_url('reputation/instagram_connect_save'),array('id'=>'workspace-form'));?>
          <?php echo form_hidden('account_id', $account_id); ?>
          <?php echo form_hidden('access_token', $access_token); ?>
          <?php echo form_hidden('pages', json_encode($pages)); ?>
          <?php echo form_hidden('expires_in', $expires_in); ?>

          <div class="radio-group">
            <?php foreach ($pages as $key => $value) { ?>
              <input type="radio" id="page_id_<?php echo e($value['id']); ?>" name="page_id" value="<?php echo e($value['id']); ?>">
              <label for="page_id_<?php echo e($value['id']); ?>"><?php echo e($value['name']); ?>(<?php echo e($value['username']); ?>)</label>
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
