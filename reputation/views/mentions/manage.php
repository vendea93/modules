<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('social', 'includes');?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
        <div class="row _buttons">
          <div class="col-md-6">
  				  <h4 class="no-margin text-bold ptop-15"><i class="fa fa-dashboard menu-icon"></i> <?php echo new_html_entity_decode($title); ?></h4>
          </div>
         
        </div>
        <hr class="mtop-5">
        <div class="clearfix"></div>
        <div class="row mtop40">
            <div class="col-md-9">
                <div id="mentions_reach_chart"></div>
                <div id="sentiment_chart"></div>
                <div id="top_stats">
                </div>
            </div>
            <div class="col-md-3">
                <?php echo form_open(admin_url('accounting/view_report'),array('id'=>'filter-form')); ?>
                <?php 
                 $search = '';

                  if($id != ''){
                    $search = '#'.$id;
                    $from_date = '';
                    $to_date = '';
                  }

                echo render_date_input('from_date','from_date', _d($from_date)); ?>
                <?php echo render_date_input('to_date','to_date', _d($to_date)); ?>
                  <?php 
                   
                        echo render_input('search', 'search', $search);
                      $date_filter_visited = [
                              1 => ['id' => 'all', 'name' => _l('all')],
                              2 => ['id' => '1', 'name' => _l('only_visited')],
                              3 => ['id' => '0', 'name' => _l('only_not_visited')],
                             ];
                      echo render_select('visited', $date_filter_visited, array('id', 'name'),'visited', 'all', array(), array(), '', '', false);
                      ?>
                      <?php 
                        $sources = [
                          ['id' => 'x_twitter', 'name' => _l('x_twitter')],
                          ['id' => 'google_news', 'name' => _l('google_news')],
                          ['id' => 'youtube', 'name' => _l('youtube')],
                          ['id' => 'facebook', 'name' => _l('facebook')],
                          ['id' => 'instagram', 'name' => _l('instagram')],
                        ];
                        ?>
                        <?php echo render_select('sources',$sources,array('id','name'),'sources', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                      <?php 
                      $date_filter_sentiment = [
                              ['id' => 'Neutral', 'name' => _l('neutral')],
                            ['id' => 'Positive', 'name' => _l('positive')],
                              ['id' => 'Negative', 'name' => _l('negative')],
                             ];
                      ?>
                        <?php echo render_select('sentiment',$date_filter_sentiment,array('id','name'),'sentiment', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
                        <div class="form-group">
                            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                            <?php echo _l('tags'); ?></label>
                            <input type="text" class="tagsinput" id="tags" name="tags"
                            value=""
                            data-role="tagsinput">
                        </div>
                    <?php echo form_hidden('page', 1); ?>
                      <a href="javascript:void(0);" onclick="load_mention_list();" class="btn btn-info btn-submit mtop25"><?php echo _l('filter'); ?></a>
                  <?php echo form_close(); ?>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="tag-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('add_tags')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/add_mention_tag'),array('id'=>'mention-tag-form'));?>
         
         <div class="modal-body tag-modal-body">
            
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>

<div class="modal fade" id="delete-mention-modal">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('delete_mention')?></h4>
         </div>
         <?php echo form_open_multipart(admin_url('reputation/delete_mention'),array('id'=>'mention-tag-form'));?>
         
         <div class="modal-body tag-modal-body">
            
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
         </div>
         <?php echo form_close(); ?>  
      </div>
   </div>
</div>
<!-- box loading -->
<div id="box-loading"></div>
<?php init_tail(); ?>
<?php require('modules/reputation/assets/js/mentions/manage_js.php'); ?>
</body>
</html>
