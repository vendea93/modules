<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<a href="<?php echo admin_url('reputation/facebook_connect') ?>"
    class="btn btn-success">
    <i class="fa fa-sign-in"></i> <?php echo _l('add_facebook_instagram_account'); ?>
</a>

<table class="table table-topic scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('name'); ?></th>
         <th><?php echo _l('addedfrom'); ?></th>
         <th><?php echo _l('dateadded'); ?></th>
         <th><?php echo _l('options'); ?></th>
      </tr>
   </thead>
    <tbody>
    	<?php 
            foreach($accounts as $account){ ?>
               <tr>
                  <td><?php echo html_entity_decode($account['name']); ?></td>
                  <td><?php echo get_staff_full_name($account['addedfrom']); ?></td>
                  <td><?php echo _dt($account['dateadded']); ?></td>
                  <td>
                  	<a href="<?php echo admin_url('reputation/facebook_reconnect/' . $account['id']); ?>"
				    class="btn btn-success mright5"  data-toggle="tooltip" data-original-title="<?php echo _l('reconnect'); ?>">
				        <i class="fa fa-sign-in"></i>
				    </a>
				    <a href="<?php echo admin_url('reputation/delete_account/' . $account['id']); ?>"
				    class="btn btn-danger _delete">
				        <i class="fa-regular fa-trash-can fa-lg"></i>
				    </a>
                  </td>
               </tr>
            <?php } ?>
    </tbody>
</table>