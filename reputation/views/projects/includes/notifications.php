<div class="panel_s">
  <div class="panel-body">
    <div>
      <?php if(is_admin() || has_permission('reputation_project', '', 'create')){ ?>
      <a href="#" class="btn btn-info add-new-notification"><?php echo _l('add_new', _l('notification')); ?></a>
      <?php } ?>
    </div>
    <hr>
    <table class="table table-notification scroll-responsive">
     <thead>
        <tr>
           <th><?php echo _l('user'); ?></th>
           <th><?php echo _l('frequency'); ?></th>
           <th><?php echo _l('visited'); ?></th>
           <th><?php echo _l('sources'); ?></th>
           <th><?php echo _l('sentiment'); ?></th>
           <th><?php echo _l('tags'); ?></th>
        </tr>
     </thead>
  </table>
  </div>
</div>


<div class="modal fade" id="notification-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('notification')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/notification'),array('id'=>'notification-form'));?>
         <?php echo form_hidden('project_id', $project->id); ?>
         
         <div class="modal-body">
            
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>