<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('reputation/project/'.$project->id.'?group=additional_sources'), ['id' => 'project-form']); ?>
<div class="panel_s">
  <div class="panel-body">
    <h3><?php echo _l('forum_source_options'); ?></h3>
    <div class="list_tripadvisor">
      <?php 
        $tripadvisor = json_decode($project->tripadvisor ?? '[""]');
      ?>

        <label class="control-label"><i class="hide fa fa-share-alt"></i> <?php echo _l('tripadvisor'); ?></label>
      <?php foreach ($tripadvisor as $key => $value) { ?>
          <div id="item_tripadvisor">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="tripadvisor[<?php echo new_html_entity_decode($key); ?>]" name="tripadvisor[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_tripadvisor btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_tripadvisor btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>
    <div class="list_booking">
      <?php 
        $booking = json_decode($project->booking ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-share-alt"></i> <?php echo _l('booking'); ?></label>
      <?php foreach ($booking as $key => $value) { ?>
          <div id="item_booking">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="booking[<?php echo new_html_entity_decode($key); ?>]" name="booking[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_booking btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_booking btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <div class="list_app_store">
      <?php 
        $app_store = json_decode($project->app_store ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-apple"></i> <?php echo _l('app_store'); ?></label>
      <?php foreach ($app_store as $key => $value) { ?>
          <div id="item_app_store">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="app_store[<?php echo new_html_entity_decode($key); ?>]" name="app_store[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_app_store btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_app_store btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <div class="list_google_play">
      <?php 
        $google_play = json_decode($project->google_play ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-google" aria-hidden="true"></i> <?php echo _l('google_play'); ?></label>
      <?php foreach ($google_play as $key => $value) { ?>
          <div id="item_google_play">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="google_play[<?php echo new_html_entity_decode($key); ?>]" name="google_play[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_google_play btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_google_play btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <div class="list_trustpilot">
      <?php 
        $trustpilot = json_decode($project->trustpilot ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-star"></i> <?php echo _l('trustpilot'); ?></label>
      <?php foreach ($trustpilot as $key => $value) { ?>
          <div id="item_trustpilot">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="trustpilot[<?php echo new_html_entity_decode($key); ?>]" name="trustpilot[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_trustpilot btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_trustpilot btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('podcast_source_options'); ?></h3>
    <div class="list_spotify">
      <?php 
        $spotify = json_decode($project->spotify ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-spotify"></i> <?php echo _l('spotify'); ?></label>
      <?php foreach ($spotify as $key => $value) { ?>
          <div id="item_spotify">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="spotify[<?php echo new_html_entity_decode($key); ?>]" name="spotify[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_spotify btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_spotify btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>
    <div class="list_apple_itunes">
      <?php 
        $apple_itunes = json_decode($project->apple_itunes ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-apple"></i> <?php echo _l('apple_itunes'); ?></label>
      <?php foreach ($apple_itunes as $key => $value) { ?>
          <div id="item_apple_itunes">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="apple_itunes[<?php echo new_html_entity_decode($key); ?>]" name="apple_itunes[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_apple_itunes btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_apple_itunes btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('video_source_options'); ?></h3>
    <div class="list_youtube">
      <?php 
        $youtube = json_decode($project->youtube ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-youtube"></i> <?php echo _l('youtube'); ?></label>
      <?php foreach ($youtube as $key => $value) { ?>
          <div id="item_youtube">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="youtube[<?php echo new_html_entity_decode($key); ?>]" name="youtube[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_youtube btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_youtube btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>
    <div class="list_vimeo">
      <?php 
        $vimeo = json_decode($project->vimeo ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-vimeo-square"></i> <?php echo _l('vimeo'); ?></label>
      <?php foreach ($vimeo as $key => $value) { ?>
          <div id="item_vimeo">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="vimeo[<?php echo new_html_entity_decode($key); ?>]" name="vimeo[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_vimeo btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_vimeo btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>
    <div class="list_tiktok">
      <?php 
        $tiktok = json_decode($project->tiktok ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-share-alt"></i> <?php echo _l('tiktok'); ?></label>
      <?php foreach ($tiktok as $key => $value) { ?>
          <div id="item_tiktok">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="tiktok[<?php echo new_html_entity_decode($key); ?>]" name="tiktok[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_tiktok btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_tiktok btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('news_source_options'); ?></h3>
    <div class="list_news_source">
      <?php 
        $news_source = json_decode($project->news_source ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-share-alt"></i> <?php echo _l('news_source'); ?></label>
      <?php foreach ($news_source as $key => $value) { ?>
          <div id="item_news_source">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="news_source[<?php echo new_html_entity_decode($key); ?>]" name="news_source[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_news_source btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_news_source btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('blog_source_options'); ?></h3>
    <div class="list_blog_source">
      <?php 
        $blog_source = json_decode($project->blog_source ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-blog"></i> <?php echo _l('blog_source'); ?></label>
      <?php foreach ($blog_source as $key => $value) { ?>
          <div id="item_blog_source">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="blog_source[<?php echo new_html_entity_decode($key); ?>]" name="blog_source[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_blog_source btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_blog_source btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('web_source_options'); ?></h3>
    <div class="list_web_source">
      <?php 
        $web_source = json_decode($project->web_source ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-share-alt"></i> <?php echo _l('web_source'); ?></label>
      <?php foreach ($web_source as $key => $value) { ?>
          <div id="item_web_source">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="web_source[<?php echo new_html_entity_decode($key); ?>]" name="web_source[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_web_source btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_web_source btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('telegram_channels'); ?></h3>
    <div class="list_telegram">
      <?php 
        $telegram = json_decode($project->telegram ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-telegram"></i> <?php echo _l('telegram'); ?></label>
      <?php foreach ($telegram as $key => $value) { ?>
          <div id="item_telegram">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="telegram[<?php echo new_html_entity_decode($key); ?>]" name="telegram[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_telegram btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_telegram btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>

    <h3><?php echo _l('x_twitter'); ?></h3>
    <div class="list_x_twitter">
      <?php 
        $x_twitter = json_decode($project->x_twitter ?? '[""]');
      ?>
        <label class="control-label"><i class="hide fa fa-twitter"></i> <?php echo _l('x_twitter'); ?></label>
      <?php foreach ($x_twitter as $key => $value) { ?>
          <div id="item_x_twitter">                            
            <div class="row">                              
             <div class="col-md-10">                            
              <div class="form-group" app-field-wrapper="name">
                <input type="text" id="x_twitter[<?php echo new_html_entity_decode($key); ?>]" name="x_twitter[<?php echo new_html_entity_decode($key); ?>]" class="form-control" value="<?php echo new_html_entity_decode($value); ?>">
              </div>
           </div>
             <div class="col-md-2">
                <?php if($key != 0){ ?>
                  <button name="add" class="btn remove_x_twitter btn-danger" data-ticket="true" type="button"><i class="fa fa-minus"></i></button>
                <?php }else{ ?>
                  <button name="add" class="btn new_x_twitter btn-success" data-ticket="true" type="button"><i class="fa fa-plus"></i></button>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php } ?>
    </div>
  </div>

    <div class="panel-footer text-right">
        <button type="submit" data-form="#project_form" class="btn btn-primary" autocomplete="off"
            data-loading-text="<?php echo _l('wait_text'); ?>">
            <?php echo _l('submit'); ?>
        </button>
    </div>
</div>
<?php echo form_close(); ?>
