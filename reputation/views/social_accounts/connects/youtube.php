<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <h4><?php echo _l('account_list'); ?></h4>
         <?php echo form_open_multipart(admin_url('reputation/youtube_connect_save'),array('id'=>'workspace-form'));?>
          <?php echo form_hidden('account_id', $account_id); ?>
          <?php echo form_hidden('expires_in', $expires_in); ?>
          <?php echo form_hidden('refresh_token', $refresh_token); ?>
          <?php echo form_hidden('access_token', $access_token); ?>

          <div class="radio-group">
            <?php foreach ($channels as $key => $channel) { ?>
              <input type="radio" id="page_id_<?php echo e($channel['id']); ?>" name="page_id" value="<?php echo e($channel['id']); ?>">
              <label for="page_id_<?php echo e($channel['id']); ?>"><?php echo e($channel['snippet']['title']); ?></label>
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
