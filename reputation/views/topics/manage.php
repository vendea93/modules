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
            <?php if(is_admin() || has_permission('reputation_topic', '', 'create')){ ?>
            <a href="#" class="btn btn-info add-new-topic mbot15"><?php echo _l('add_new', _l('topic')); ?></a>
            <a href="<?php echo admin_url('reputation/topic_import'); ?>" class="btn btn-info mright5 test pull-left display-block">
                     <?php echo _l('import_topics'); ?></a>
            <?php } ?>

          </div>
          <div class="row">
            <div class="col-md-3">
              <?php 
                $topic_type = [
                  ['id' => 'positive', 'name' => _l('positive')],
                  ['id' => 'negative', 'name' => _l('negative')],
                ];
                echo render_select('_type', $topic_type, array('id', 'name'), 'type');
                ?>
            </div>
          </div>
          <hr>
          <table class="table table-topic scroll-responsive">
           <thead>
              <tr>
                 <th><?php echo _l('content'); ?></th>
                 <th><?php echo _l('type'); ?></th>
                 <th><?php echo _l('scales'); ?></th>
                 <th><?php echo _l('active'); ?></th>
                 <th><?php echo _l('datecreated'); ?></th>
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
<div class="modal fade" id="topic-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('topic')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/topic'),array('id'=>'topic-form'));?>
         <?php echo form_hidden('id'); ?>
         
         <div class="modal-body">
            <?php echo render_input('content', 'content'); ?>
            <?php echo render_select('type', $topic_type, array('id', 'name'), 'type', '', [], [], '', '', false); ?>
            <?php echo render_input('scales', 'scales', '0', 'number'); ?>
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
<?php require 'modules/reputation/assets/js/topics/manage_js.php'; ?>
