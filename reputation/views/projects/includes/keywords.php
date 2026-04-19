<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('reputation/project/'.$project->id.'?group=keywords'), ['id' => 'project-form']); ?>

<div class="panel_s">
  <div class="panel-body">
    <?php $value = (isset($project) ? $project->project_name : ''); ?>
    <?php echo render_input('project_name', 'project_name', $value); ?>
    <label class="control-label"><small class="req text-danger">* </small> <?php echo _l('keywords'); ?></label>
    <div class="list_approve">
      <?php if(!isset($project)) { ?>
      <div id="item_approve">
        <div class="row">
          <div class="col-md-10">                            
            <div class="form-group" app-field-wrapper="name">
              <input type="text" id="keywords[0]" name="keywords[0]" class="form-control" value="" required>
            </div>
          </div>
          <div class="col-md-2">
            <button name="add" class="btn new_keywords btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
          </div>
        </div>
      </div>
      <?php }else{ 
        $keywords = json_decode($project->keywords);
      ?>
      <?php foreach ($keywords as $key => $value) { ?>
          <div id="item_approve">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
            
                <input type="text" id="keywords[<?php echo new_html_entity_decode($key); ?>]" name="keywords[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>" required>
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_keywords btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_keywords btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php }
    } ?>
    </div>
    <?php $value = (isset($project) ? $project->excluded_keywords : ''); ?>
    <?php echo render_input('excluded_keywords', 'excluded_keywords', $value); ?>
    <div class="form-group select-placeholder hide">
          <label for="language"
              class="control-label"><?php echo _l('language'); ?>
          </label>
          <select name="language" id="language"
              class="form-control selectpicker">
              <option value=""><?php echo _l('all_languages'); ?></option>
              <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                $selected = '';
                 if(isset($project)){
                    if($project->language == ucfirst($availableLanguage)){
                       $selected = 'selected';
                    }
                 }
                 ?>
                ?>
              <option value="<?php echo e(ucfirst($availableLanguage)); ?>" <?php echo new_html_entity_decode($selected); ?>>
                  <?php echo e(ucfirst($availableLanguage)); ?></option>
              <?php
              } ?>
          </select>
    </div>
  </div>

    <div class="panel-footer text-right">
            <?php if(is_admin() || has_permission('reputation_project', '', 'edit')){ ?>
            <button type="submit" data-form="#project_form" class="btn btn-primary" autocomplete="off"
                data-loading-text="<?php echo _l('wait_text'); ?>">
                <?php echo _l('submit'); ?>
            </button>

            <?php } ?>

    </div>
</div>
<?php echo form_close(); ?>
