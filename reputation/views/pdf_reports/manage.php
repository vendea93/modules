<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
                <div class="_buttons">
          			<h4 class="no-margin text-bold ptop-15"><i class="fa fa-dashboard menu-icon"></i> <?php echo new_html_entity_decode($title); ?></h4>
                </div>
                <hr>
                <?php echo form_open(admin_url('accounting/view_report'),array('id'=>'filter-form')); ?>
                <div class="row">
                    <div class="col-md-6">
                    <h3><?php echo _l('filter'); ?></h3>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_date_input('from_date','from_date', _d($from_date)); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_date_input('to_date','to_date', _d($to_date)); ?>
                    </div>
                    <div class="col-md-6">
                        <?php 
                            
                          $date_filter_visited = [
                                  1 => ['id' => 'all', 'name' => _l('all')],
                                  2 => ['id' => '1', 'name' => _l('only_visited')],
                                  3 => ['id' => '0', 'name' => _l('only_not_visited')],
                                 ];
                          echo render_select('visited', $date_filter_visited, array('id', 'name'),'visited', 'all', array(), array(), '', '', false);
                          ?>
                    </div>
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                      <?php 
                      $date_filter_sentiment = [
                              ['id' => 'Neutral', 'name' => _l('neutral')],
                            ['id' => 'Positive', 'name' => _l('positive')],
                              ['id' => 'Negative', 'name' => _l('negative')],
                             ];
                      ?>
                        <?php echo render_select('sentiment',$date_filter_sentiment,array('id','name'),'sentiment', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                            <?php echo _l('tags'); ?></label>
                            <input type="text" class="tagsinput" id="tags" name="tags"
                            value=""
                            data-role="tagsinput">
                        </div>
                    </div>
                    </div>
                    </div>

                    <div class="col-md-6">
                        <h3><?php echo _l('report_overview'); ?></h3>
                        <?php echo render_textarea('description', 'description'); ?>
                    </div>

                    
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <h3><?php echo _l('chart'); ?></h3>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mentions_pie_chart') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_mentions_by_category_chart" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_mentions_by_category_chart">
                              <label class="onoffswitch-label" for="active_mentions_by_category_chart"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mentions_chart') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_mentions_chart" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_mentions_chart">
                              <label class="onoffswitch-label" for="active_mentions_chart"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mentions_reach_chart') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_mentions_reach_chart" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_mentions_reach_chart">
                              <label class="onoffswitch-label" for="active_mentions_reach_chart"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('sentiment_chart') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_sentiment_chart" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_sentiment_chart">
                              <label class="onoffswitch-label" for="active_sentiment_chart"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('social_media_reach_chart') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_social_media_reach_chart" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_social_media_reach_chart">
                              <label class="onoffswitch-label" for="active_social_media_reach_chart"></label>
                          </div>
                      </div>
                    </div>
                    
                    </div>
                    <div class="col-md-6">
                    <h3><?php echo _l('summary'); ?></h3>
                        <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mentions') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_top_stats" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_top_stats">
                              <label class="onoffswitch-label" for="active_top_stats"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mention_types') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_summary_stats" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_summary_stats">
                              <label class="onoffswitch-label" for="active_summary_stats"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('sources') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_summary_sources" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_summary_sources">
                              <label class="onoffswitch-label" for="active_summary_sources"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('tags') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_tag_stats" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_tag_stats">
                              <label class="onoffswitch-label" for="active_tag_stats"></label>
                          </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 border-right">
                        <h5 class="title mbot5"><?php echo _l('mention_list') ?></h5>
                      </div>
                      <div class="col-md-6 mtop5">
                          <div class="onoffswitch">
                              <input type="checkbox" id="active_mention_list" data-perm-id="3" class="onoffswitch-checkbox" checked  value="1" name="active_mention_list">
                              <label class="onoffswitch-label" for="active_mention_list"></label>
                          </div>
                      </div>
                    </div>
                    </div>
                </div>
                    <hr>
                    <?php echo form_hidden('project_id', rep_get_base_workspace_id()); ?>
                  <a href="javascript:void(0);" onclick="load_mention_list();" class="btn btn-info btn-submit pull-right"><?php echo _l('filter'); ?></a>
                <?php echo form_close(); ?>

                

            </div>


        </div>
        <div class="row rep_rp_header">
        <div class="col-md-6 no-padding">
            <h3 class="no-margin"><?php echo _l('preview'); ?></h3>
        </div>
        <div class="col-md-6 no-padding">
            <a href="javascript:void(0);" onclick="exportChartsToPDF();" class="btn btn-default pull-right"><i class="fa fa-file"></i> <?php echo _l('print_pdf'); ?></a>
        </div>
        </div>
        <div id="myDiv" class="a4-page">
            <div class="row ">
                <div class="col-md-12">
                    <div id="report_info" class="text-center mbot30">
                        <div class="rp-company-logo mtop40">
                            <?php echo get_company_logo(); ?>
                        </div>
                        <h2 class="mtop40"><?php echo get_option('companyname'); ?></h2>
                      <div id="rp_date_range" class="mtop20">
                            <?php echo date('d M Y', strtotime($from_date)) .' - '. date('d M Y', strtotime($to_date)); ?>
                      </div>
                        <div id="rp_description" class="mtop40 mbot30 bold">
                            
                        </div>
                    </div>
                    <div id="top_stats" class="mtop40"></div>
                    <div id="mentions_by_category_chart" class="mtop40"></div>
                    <div id="mentions_chart" class="mtop40"></div>
                    <div id="mentions_reach_chart" class="mtop40"></div>
                    <div id="sentiment_chart" class="mtop40"></div>
                    <div id="social_media_reach_chart" class="mtop40"></div>
                    <div id="tag_stats" class="mtop40"></div>
                    <div id="summary_stats" class="mtop40"></div>
                    <div id="summary_sources" class="mtop40"></div>
                    <div id="mention_list" class="mtop40"></div>
                </div>
            </div>
           
        </div>
    </div>
</div>

<div id="accounting-box-loading"></div>
<?php init_tail(); ?>
<?php require('modules/reputation/assets/js/pdf_reports/manage_js.php'); ?>
</body>
</html>
