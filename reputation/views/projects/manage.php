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
            <?php if(is_admin() || has_permission('reputation_project', '', 'create')){ ?>

            <a href="#" class="btn btn-info add-new-project mbot15"><?php echo _l('add_new', _l('project')); ?></a>
            <?php } ?>

          </div>
          
          <table class="table table-project scroll-responsive">
           <thead>
              <tr>
                 <th><?php echo _l('project_name'); ?></th>
                 <th><?php echo _l('new_mentions'); ?></th>
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

<div class="modal fade" id="project-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('project')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/project'),array('id'=>'project-form'));?>
         <?php echo form_hidden('id'); ?>
         
         <div class="modal-body">
            <?php echo render_input('project_name', 'project_name'); ?>
            <div class="list_approve">
              <div id="item_approve">
                <div class="row">
                  <div class="col-md-10">                            
                    <div class="form-group" app-field-wrapper="name">
                      <label for="keywords[0]" class="control-label"><small class="req text-danger">* </small> <?php echo _l('keyword'); ?></label>
                      <input type="text" id="keywords[0]" name="keywords[0]" class="form-control" value="" required>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <button name="add" class="btn new_keywords btn-success mtop20" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                  </div>
                </div>
              </div>
            </div>
            <?php echo render_input('excluded_keywords', 'excluded_keywords'); ?>
            <div class="form-group select-placeholder hide">
                  <label for="language"
                      class="control-label"><?php echo _l('language'); ?>
                  </label>
                  <select name="language" id="language"
                      class="form-control selectpicker">
                      <option value=""><?php echo _l('all_languages'); ?></option>
                      <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                        ?>
                      <option value="<?php echo e(ucfirst($availableLanguage)); ?>">
                          <?php echo e(ucfirst($availableLanguage)); ?></option>
                      <?php
                      } ?>
                  </select>
              </div>
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
<?php require 'modules/reputation/assets/js/projects/manage_js.php'; ?>
