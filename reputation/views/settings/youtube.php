<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
  	$client_id = get_option('rep_youtube_client_id');
  	$client_secret = get_option('rep_youtube_client_secret');
?>

<?php echo form_open(admin_url('reputation/update_setting'),array('id'=>'general-settings-form')); ?>
    <?php echo form_hidden('type', $group);?>
	<?php echo render_input('rep_youtube_client_id', 'client_id', $client_id); ?>
	<?php echo render_input('rep_youtube_client_secret', 'client_secret', $client_secret, 'password'); ?>
	<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
<?php echo form_close(); ?>
	           