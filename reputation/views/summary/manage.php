<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('social', 'includes');?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="screen-options-area">
        <?php echo form_open(admin_url('accounting/view_report'),array('id'=>'filter-form')); ?>
        <div class="row">
            <div class="col-md-4 hide">
                <?php echo render_input('search', 'search'); ?>
            </div>
            <div class="col-md-4">
                <?php echo render_date_input('from_date','from_date', _d($from_date)); ?>
            </div>
            <div class="col-md-4">
                <?php echo render_date_input('to_date','to_date', _d($to_date)); ?>
            </div>
            <div class="col-md-4">
              <?php 
                    
                  $date_filter_visited = [
                          1 => ['id' => 'all', 'name' => _l('all')],
                          2 => ['id' => '1', 'name' => _l('only_visited')],
                          3 => ['id' => '0', 'name' => _l('only_not_visited')],
                         ];
                  echo render_select('visited', $date_filter_visited, array('id', 'name'),'visited', 'all', array(), array(), '', '', false);
                  ?>
            </div>
            <div class="col-md-4">
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
            </div>
            <div class="col-md-4">
              <?php 
              $date_filter_sentiment = [
                      ['id' => 'Neutral', 'name' => _l('neutral')],
                    ['id' => 'Positive', 'name' => _l('positive')],
                      ['id' => 'Negative', 'name' => _l('negative')],
                     ];
              ?>
                <?php echo render_select('sentiment',$date_filter_sentiment,array('id','name'),'sentiment', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>

            </div>
            <div class="col-md-12">
            <?php echo form_hidden('page', 1); ?>
              <a href="javascript:void(0);" onclick="load_mention_list();" class="btn btn-info btn-submit pull-right hide"><?php echo _l('filter'); ?></a>
            </div>
        </div>
          <?php echo form_close(); ?>
    </div>
        <div class="screen-options-btn"><i class="fa fa-filter" aria-hidden="true"></i> <?php echo _l('filter'); ?></div>
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
        <div class="_buttons">
  				  <h4 class="no-margin text-bold ptop-15"><i class="fa fa-dashboard menu-icon"></i> <?php echo new_html_entity_decode($title); ?></h4>
        </div>
        <hr class="mtop-5">
        <div class="clearfix"></div>
        <div class="row ">
            <div class="col-md-6">
                <div id="mentions_chart"></div>
                <div id="social_media_reach_chart"></div>
            </div>
            <div class="col-md-6">
                <div id="top_stats"></div>
                <div id="mentions_by_category_chart"></div>
            </div>
        </div>
        <div class="row mtop40">
            <div class="col-md-4">
                <div id="tag_stats"></div>
            </div>
            <div class="col-md-4">
                <div id="summary_stats"></div>
            </div>
            <div class="col-md-4">
                <div id="summary_sources"></div>
            </div>
        </div>
        <div class="row mtop40">
            <div class="col-md-4 hide">
                <div id="keyword_stats"></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- box loading -->
<div id="box-loading"></div>
<?php init_tail(); ?>
<?php require('modules/reputation/assets/js/summary/manage_js.php'); ?>
</body>
</html>
