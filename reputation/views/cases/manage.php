<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="panel_s">
        <div class="panel-body">
          <h4 class="no-margin font-bold"><?php echo _l($title); ?></h4>
          <hr />
          <div>
            <?php if(is_admin() || has_permission('reputation_case', '', 'create')){ ?>
              <a href="javascript:void(0)" class="btn btn-info add-new-case mbot15"><?php echo _l('add_new', _l('case')); ?></a>
            <?php } ?>
          </div>
          <table class="table table-case scroll-responsive">
           <thead>
              <tr>
                 <th><?php echo _l('name'); ?></th>
                 <th><?php echo _l('description'); ?></th>
                 <th><?php echo _l('addedfrom'); ?></th>
                 <th><?php echo _l('datecreated'); ?></th>
                 <th><?php echo _l('active'); ?></th>
              </tr>
           </thead>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $arrAtt = array();
      $arrAtt['data-type']='currency';
?>
<div class="modal fade" id="case-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('case')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/case'),array('id'=>'case-form'));?>
         <?php echo form_hidden('id'); ?>
         
         <div class="modal-body">
            <?php echo render_input('name', 'name'); ?>
            <?php echo render_textarea('description', 'description'); ?>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>
<?php init_tail(); ?>
</body>
</html>
<?php require 'modules/reputation/assets/js/cases/manage_js.php'; ?>
