<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();?>
<?php 
    $sources = [
      ['id' => 'x_twitter', 'name' => _l('x_twitter')],
      ['id' => 'google_news', 'name' => _l('google_news')],
      ['id' => 'youtube', 'name' => _l('youtube')],
      ['id' => 'facebook', 'name' => _l('facebook')],
      ['id' => 'instagram', 'name' => _l('instagram')],
    ];
  $sentiment = [
          ['id' => 'Neutral', 'name' => _l('neutral')],
        ['id' => 'Positive', 'name' => _l('positive')],
          ['id' => 'Negative', 'name' => _l('negative')],
         ];
  ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mtop15">
               <div class="panel-body">
                  <h4 class="customer-profile-group-heading"><?php echo _l('case'); ?></h4>
                  <h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800"><?php echo _l('general_info'); ?></h4>
                  <div class="row">
                    <div class="col-md-6">
                      <?php echo form_hidden('timezone', date_default_timezone_get()); ?>
                      <?php echo form_hidden('case_id',$case->id); ?>
                      <table class="table table-striped no-margin">
                        <tbody>
                            <tr class="project-overview">
                              <td class="bold" width="30%"><?php echo _l('name'); ?></td>
                              <td><?php echo html_entity_decode($case->name); ?></td>
                           </tr>
                           <tr class="project-overview">
                              <?php $value = (($case->active == 1) ? _l('ma_yes') : _l('ma_no')); ?>
                              <?php $text_class = (($case->active == 1) ? 'text-success' : 'text-danger'); ?>
                              <td class="bold"><?php echo _l('active'); ?></td>
                              <td class="<?php echo html_entity_decode($text_class) ; ?>"><?php echo html_entity_decode($value) ; ?></td>
                           </tr>
                           <tr class="project-overview">
                              <td class="bold"><?php echo _l('addedfrom'); ?></td>
                              <td><?php echo get_staff_full_name($case->addedfrom) ; ?></td>
                           </tr>
                           <tr class="project-overview">
                              <td class="bold" width="30%"><?php echo _l('datecreated'); ?></td>
                              <td><?php echo _dt($case->datecreated) ; ?></td>
                           </tr>
                          </tbody>
                    </table>
                  </div>
                  <div class="col-md-6">
                      <table class="table table-striped  no-margin">
                        <tbody>
                          
                           <tr class="project-overview">
                              <td class="bold"><?php echo _l('description'); ?></td>
                              <td><?php echo html_entity_decode($case->description) ; ?></td>
                           </tr>
                          </tbody>
                    </table>
                  </div>
                </div>

                <h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800 mtop20"><?php echo _l('workflow'); ?></h4>
                <?php 

          $trigger = [ 
            ['id' => 'contains_this_word', 'name' => _l('contains_this_word')],
            ['id' => 'does_not_contain_this_word', 'name' => _l('does_not_contain_this_word')],
            ['id' => 'topic_is_detected', 'name' => _l('topic_is_detected')],
            ['id' => 'sentiment_is_detected', 'name' => _l('sentiment_is_detected')],
            ['id' => 'matches_source', 'name' => _l('matches_source')],
          ]; 

          $action = [ 
            ['id' => 'hide_mentions', 'name' => _l('hide_mentions')],
            ['id' => 'send_a_push_notification', 'name' => _l('send_a_push_notification')],
            ['id' => 'send_an_email', 'name' => _l('send_an_email')],
            ['id' => 'add_tag', 'name' => _l('add_tag')],
            ['id' => 'add_to_pdf_report', 'name' => _l('add_to_pdf_report')],
          ]; 

          ?>
         <?php echo form_open(admin_url('reputation/case'),array('id'=>'case-form'));?>

         <?php echo form_hidden('id', $case->id); ?>


            <div class="list_approve">
              <?php if($case->workflow != ''){ 
                $workflow = json_decode($case->workflow, true);
                ?>

              <?php foreach($workflow['trigger'] as $key => $value){ ?>
                <div id="item_approve" class="border mtop10 padding-10">
                  <div class="row">
                    <div class="col-md-11">
                  <div class="row">
                    <div class="col-md-3 div_trigger">
                      <div class="select-placeholder form-group">
                          <label for="trigger[<?php echo html_entity_decode($key); ?>]"><?php echo _l('if'); ?></label>
                          <select name="trigger[<?php echo html_entity_decode($key); ?>]" id="trigger[<?php echo html_entity_decode($key); ?>]" data-index="<?php echo html_entity_decode($key); ?>" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                            <?php foreach($trigger as $val){
                             $selected = '';
                             if($val['id'] == $value){
                                $selected = 'selected';
                              }
                             ?>
                             <option value="<?php echo html_entity_decode($val['id']); ?>" <?php echo html_entity_decode($selected); ?>>
                               <?php echo html_entity_decode($val['name']); ?>
                             </option>
                           <?php } ?>
                         </select>
                       </div> 
                    </div>
                    <div class="col-md-3 <?php if($workflow['trigger'][$key] != 'sentiment_is_detected') {echo 'hide';} ?>" id="div_sentiment_<?php echo html_entity_decode($key); ?>">  
                      <div class="select-placeholder form-group ">
                        <label for="sentiment[<?php echo html_entity_decode($key); ?>]"><?php echo _l('sentiment'); ?></label>
                        <select name="sentiment[<?php echo html_entity_decode($key); ?>]" id="sentiment[<?php echo html_entity_decode($key); ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($sentiment as $val){
                           $selected = '';
                           if($val['id'] == $workflow['sentiment'][$key]){
                                $selected = 'selected';
                              }
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>" <?php echo html_entity_decode($selected); ?>>
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                      </div> 
                      <div class="col-md-3 <?php if($workflow['trigger'][$key] != 'topic_is_detected') {echo 'hide';} ?>" id="div_topic_<?php echo html_entity_decode($key); ?>">  
                      <div class="select-placeholder form-group ">
                        <label for="topic[<?php echo html_entity_decode($key); ?>]"><?php echo _l('topic'); ?></label>
                        <select name="topic[<?php echo html_entity_decode($key); ?>]" id="topic[<?php echo html_entity_decode($key); ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($topics as $val){
                           $selected = '';
                           if($val['id'] == $workflow['topic'][$key]){
                                $selected = 'selected';
                              }
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>" <?php echo html_entity_decode($selected); ?>>
                             <?php echo html_entity_decode($val['content']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                      </div> 
                    <div class="col-md-3 <?php if($workflow['trigger'][$key] != 'matches_source') {echo 'hide';} ?>" id="div_sources_<?php echo html_entity_decode($key); ?>">  
                      <div class="select-placeholder form-group ">
                        <label for="sources[<?php echo html_entity_decode($key); ?>]"><?php echo _l('sources'); ?></label>
                        <select name="sources[<?php echo html_entity_decode($key); ?>]" id="sources[<?php echo html_entity_decode($key); ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($sources as $val){
                           $selected = '';
                           if($val['id'] == $workflow['sources'][$key]){
                                $selected = 'selected';
                              }
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>" <?php echo html_entity_decode($selected); ?>>
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                      </div> 
                      <div class="col-md-3 <?php if($workflow['trigger'][$key] != 'contains_this_word' && $workflow['trigger'][$key] != 'does_not_contain_this_word') {echo 'hide';} ?>" id="div_word_<?php echo html_entity_decode($key); ?>">              
                        <div class="form-group" app-field-wrapper="name">
                          <label for="word[<?php echo html_entity_decode($key); ?>]" class="control-label"><?php echo _l('word'); ?></label>
                          <input type="word" id="word[<?php echo html_entity_decode($key); ?>]" name="word[<?php echo html_entity_decode($key); ?>]" class="form-control" value="<?php echo e($workflow['word'][$key]); ?>">
                        </div>
                     </div>
                   <div class="col-md-3">                            
                      <div class="select-placeholder form-group">
                        <label for="action[<?php echo html_entity_decode($key); ?>]"><?php echo _l('then'); ?></label>
                        <select name="action[<?php echo html_entity_decode($key); ?>]" id="action[<?php echo html_entity_decode($key); ?>]" class="selectpicker" data-index="<?php echo html_entity_decode($key); ?>" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($action as $val){
                           $selected = '';
                           if($val['id'] == $workflow['action'][$key]){
                                $selected = 'selected';
                              }
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>" <?php echo html_entity_decode($selected); ?>>
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                   </div>
                   <div class="col-md-3 <?php if($workflow['action'][$key] != 'send_a_push_notification' && $workflow['action'][$key] != 'send_an_email') {echo 'hide';} ?>" id="div_staff_<?php echo html_entity_decode($key); ?>">  
                      <div class="select-placeholder form-group ">
                        <label for="staff[<?php echo html_entity_decode($key); ?>]"><?php echo _l('staff'); ?></label>
                        <select name="staff[<?php echo html_entity_decode($key); ?>]" id="staff[<?php echo html_entity_decode($key); ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($staff as $val){
                           $selected = '';
                           if($val['staffid'] == $workflow['staff'][$key]){
                                $selected = 'selected';
                              }
                           ?>
                           <option value="<?php echo html_entity_decode($val['staffid']); ?>" <?php echo html_entity_decode($selected); ?>>
                             <?php echo html_entity_decode($val['firstname'] .' '. $val['lastname']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                      </div> 
                      <div class="col-md-3 <?php if($workflow['action'][$key] != 'add_tag') {echo 'hide';} ?>" id="div_tag_<?php echo html_entity_decode($key); ?>">              
                        <div class="form-group" app-field-wrapper="name">
                          <label for="tag[<?php echo html_entity_decode($key); ?>]" class="control-label"><?php echo _l('tag'); ?></label>
                          <input type="tag" id="tag[<?php echo html_entity_decode($key); ?>]" name="tag[<?php echo html_entity_decode($key); ?>]" class="form-control" value="<?php echo e($workflow['tag'][$key]); ?>">
                        </div>
                     </div>
                      </div> 
                      </div> 
                   <div class="col-md-1">
                      <?php if($key != 0){ ?>
                    <button name="add" class="btn remove_vendor_requests btn-danger mtop20" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                    <button name="add" class="btn new_vendor_requests btn-success mtop20" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
                  </div>
                </div>
                </div>
              <?php } ?>
              <?php }else{ ?>
                <div id="item_approve" class="border mtop10 padding-10">
                  <div class="row">
                  <div class="col-md-11">
                  <div class="row">
                    <div class="col-md-3 div_trigger ">
                      <div class="select-placeholder form-group">
                          <label for="trigger[0]"><?php echo _l('if'); ?></label>
                          <select name="trigger[0]" id="trigger[0]" data-index="0" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                            <?php foreach($trigger as $val){
                             $selected = '';
                             ?>
                             <option value="<?php echo html_entity_decode($val['id']); ?>">
                               <?php echo html_entity_decode($val['name']); ?>
                             </option>
                           <?php } ?>
                         </select>
                       </div> 
                    </div>
                    <div class="col-md-3 hide" id="div_sentiment_0">                            
                      <div class="select-placeholder form-group ">
                        <label for="sentiment[0]"><?php echo _l('sentiment'); ?></label>
                        <select name="sentiment[0]" id="sentiment[0]" class="selectpicker" data-index="0" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($sentiment as $val){
                           $selected = '';
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>">
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                   </div>
                   <div class="col-md-3 hide" id="div_topic_0">                            
                      <div class="select-placeholder form-group ">
                        <label for="topic[0]"><?php echo _l('topic'); ?></label>
                        <select name="topic[0]" id="topic[0]" class="selectpicker" data-index="0" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($topics as $val){
                           $selected = '';
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>">
                             <?php echo html_entity_decode($val['content']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                   </div>
                    <div class="col-md-3 hide" id="div_sources_0">                            
                      <div class="select-placeholder form-group ">
                        <label for="sources[0]"><?php echo _l('sources'); ?></label>
                        <select name="sources[0]" id="sources[0]" class="selectpicker" data-index="0" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($sources as $val){
                           $selected = '';
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>">
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                   </div>
                    <div class="col-md-3" id="div_word_0">                            
                      <div class="form-group" app-field-wrapper="name">
                        <label for="word[0]" class="control-label"><?php echo _l('word'); ?></label>
                        <input type="word" id="word[0]" name="word[0]" class="form-control" value="">
                      </div>
                   </div>
                   
                   <div class="col-md-3">                            
                      <div class="select-placeholder form-group">
                        <label for="action[0]"><?php echo _l('then'); ?></label>
                        <select name="action[0]" id="action[0]" class="selectpicker" data-index="0" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($action as $val){
                           $selected = '';
                           ?>
                           <option value="<?php echo html_entity_decode($val['id']); ?>">
                             <?php echo html_entity_decode($val['name']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div> 
                   </div>
                   <div class="col-md-3 hide" id="div_staff_0">  
                      <div class="select-placeholder form-group ">
                        <label for="staff[0]"><?php echo _l('staff'); ?></label>
                        <select name="staff[0]" id="staff[0]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" data-live-search="true">
                          <?php foreach($staff as $val){
                           $selected = '';
                           ?>
                           <option value="<?php echo html_entity_decode($val['staffid']); ?>">
                             <?php echo html_entity_decode($val['firstname'] .' '. $val['lastname']); ?>
                           </option>
                         <?php } ?>
                        </select>
                      </div>                          
                   </div>
                   <div class="col-md-3 hide"  id="div_tag_0">                            
                      <div class="form-group" app-field-wrapper="name">
                        <label for="tag[0]" class="control-label"><?php echo _l('tag'); ?></label>
                        <input type="tag" id="tag[0]" name="tag[0]" class="form-control" value="">
                      </div>
                   </div>
                   </div>
                   </div>
                   <div class="col-md-1">
                      <button name="add" class="btn new_vendor_requests btn-success mtop20" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                  </div>
                </div>
                </div>
              <?php } ?>
            </div>
              <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
              <button class="btn btn-primary only-save" type="submit">
                <?php echo _l( 'submit'); ?>
              </button>
           </div>
              <?php echo form_close(); ?>  
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>
<?php require 'modules/reputation/assets/js/cases/case_detail_js.php'; ?>
