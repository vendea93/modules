<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('reputation/project/'.$project->id.'?group=sources'), ['id' => 'project-form']); ?>
<?php echo form_hidden('tab', 'sources'); ?>
<?php 
  $active_sources_x_twitter = $project->active_sources_x_twitter;
  $active_sources_news = $project->active_sources_news;
  $active_sources_web = $project->active_sources_web;
  $active_sources_blogs = $project->active_sources_blogs;
  $active_sources_videos = $project->active_sources_videos;
  $active_sources_podcast = $project->active_sources_podcast;
  $active_sources_forums = $project->active_sources_forums;
  $active_sources_instagram = $project->active_sources_instagram;
  $active_sources_facebook = $project->active_sources_facebook;
  $active_sources_youtube = $project->active_sources_youtube;
?>
<div class="panel_s">
  <div class="panel-body">
      <label for="" class="control-label"><?php echo _l('active_sources'); ?>:</label>
      <div class="form-group">
      <div class="row">
        <div class="col-md-2">
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="active_sources_x_twitter" <?php if($active_sources_x_twitter == '1'){echo 'checked';} ?> id="active_sources_x_twitter" value="1">
            <label for="active_sources_x_twitter"><?php echo _l('x_twitter'); ?></label>
          </div>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="active_sources_news" <?php if($active_sources_news == '1'){echo 'checked';} ?> id="active_sources_news" value="1">
            <label for="active_sources_news"><?php echo _l('google_news'); ?></label>
          </div>
          <div class="checkbox checkbox-primary ">
            <input type="checkbox" name="active_sources_youtube" <?php if($active_sources_youtube == '1'){echo 'checked';} ?> id="active_sources_youtube" value="1">
            <label for="active_sources_youtube"><?php echo _l('youtube'); ?></label>
          </div>
       
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="active_sources_facebook" <?php if($active_sources_facebook== '1'){echo 'checked';} ?> id="active_sources_facebook" value="1">
            <label for="active_sources_facebook"><?php echo _l('facebook'); ?></label>
          </div>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="active_sources_instagram" <?php if($active_sources_instagram == '1'){echo 'checked';} ?> id="active_sources_instagram" value="1">
            <label for="active_sources_instagram"><?php echo _l('instagram'); ?></label>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12 hide">
        <h4><?php echo _l('excluded_sites'); ?></h4>
        <div class="list_excluded_sites">
          <?php 
            $excluded_sites = json_decode($project->excluded_sites ?? '[""]');
          ?>

          <?php foreach ($excluded_sites as $key => $value) { ?>
              <div id="item_excluded_sites">                            
                <div class="row">                              
                 <div class="col-md-10">                            
                  <div class="form-group" app-field-wrapper="name">
                    <input type="text" id="excluded_sites[<?php echo new_html_entity_decode($key); ?>]" name="excluded_sites[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
                  </div>
               </div>
                 <div class="col-md-2">
                    <?php if($key != 0){ ?>
                      <button name="add" class="btn remove_excluded_sites btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                    <?php }else{ ?>
                      <button name="add" class="btn new_excluded_sites btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                    <?php } ?>
                </div>
              </div>
            </div>
        <?php } ?>
        </div>
      </div>
      <div class="col-md-12 hide">
        <h4><?php echo _l('excluded_social_media_authors'); ?></h4>
        <div class="list_excluded_social_media_authors">
          <?php 
            $excluded_social_media_authors = json_decode($project->excluded_social_media_authors ?? '[""]');
          ?>

          <?php foreach ($excluded_social_media_authors as $key => $value) { ?>
              <div id="item_excluded_social_media_authors">                            
                <div class="row">                              
                 <div class="col-md-10">                            
                  <div class="form-group" app-field-wrapper="name">
                    <input type="text" id="excluded_social_media_authors[<?php echo new_html_entity_decode($key); ?>]" name="excluded_social_media_authors[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
                  </div>
               </div>
                 <div class="col-md-2">
                    <?php if($key != 0){ ?>
                      <button name="add" class="btn remove_excluded_social_media_authors btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                    <?php }else{ ?>
                      <button name="add" class="btn new_excluded_social_media_authors btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                    <?php } ?>
                </div>
              </div>
            </div>
        <?php } ?>
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
      <div class="col-md-12 mtop25">
            <?php if(is_admin() || has_permission('reputation_project', '', 'create')){ ?>
              <label for="" class="control-label"><?php echo _l('add_mention_manually'); ?>:</label>
              <a href="#" class="add-new-mention mbot15"><?php echo _l('add_mention_form'); ?></a>
            <?php } ?>

        <table class="table table-mentions scroll-responsive">
         <thead>
            <tr>
               <th><?php echo _l('title'); ?></th>
               <th><?php echo _l('category'); ?></th>
               <th><?php echo _l('sentiment'); ?></th>
               <th><?php echo _l('address'); ?></th>
            </tr>
         </thead>
      </table>
      </div>
    </div>
  </div>
</div>
<?php echo form_close(); ?>

<div class="modal fade" id="mention-modal">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('mention')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/add_mention_form'),array('id'=>'mention-form'));?>
         <?php echo form_hidden('project_id', $project->id); ?>
         <?php echo form_hidden('id'); ?>
         
         <div class="modal-body">
            <?php echo render_input('link', 'entry_address'); ?>
            <?php echo render_input('title', 'entry_title'); ?>
            <?php echo render_textarea('content', 'mention_content'); ?>
            <?php 
                $entry_category = [
                  ['id' => 'x_twitter', 'name' => _l('x_twitter')],
                  ['id' => 'google_news', 'name' => _l('google_news')],
                  ['id' => 'youtube', 'name' => _l('youtube')],
                  ['id' => 'facebook', 'name' => _l('facebook')],
                  ['id' => 'instagram', 'name' => _l('instagram')],
                ];
                echo render_select('platform', $entry_category, array('id', 'name'), 'entry_category');
                ?>
                <?php 
               $countries = get_all_countries();
               echo render_select( 'country',$countries,array( 'country_id',array( 'short_name')), 'country','',array('data-none-selected-text'=>_l('dropdown_non_selected_tex'))); ?>
            <?php 
                $sentiment = [
                  ['id' => 'Neutral', 'name' => _l('neutral')],
                  ['id' => 'Positive', 'name' => _l('positive')],
                  ['id' => 'Negative', 'name' => _l('negative')],
                ];
                echo render_select('sentiment', $sentiment, array('id', 'name'), 'sentiment', 'Neutral', array(), array(), '', '', false);
                ?>
            <?php echo render_input('scales', 'scales', '0', 'number'); ?>
            <?php echo render_input('keyword', 'keyword'); ?>

            <?php echo render_datetime_input('time', 'date_that_entry_was_created'); ?>
            <?php echo render_input('likes', 'likes', '', 'number'); ?>
            <?php echo render_input('pageviews', 'pageviews', '', 'number'); ?>
            <?php echo render_input('shares', 'shares', '', 'number'); ?>
            <?php echo render_input('comments', 'comments', '', 'number'); ?>

         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>