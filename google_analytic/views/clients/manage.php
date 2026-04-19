<?php echo form_hidden('social', 'includes');?>
<div id="wrapper">
  <div class="screen-options-area" style="display: none;">
    <div id="dashboard-options">
      
    </div>
    </div>
        <div class="screen-options-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-mr-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg> <?php echo _l('ga_metrics'); ?></div>
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">

        <div class="row _buttons ">
          <div class="col-md-6">
  				  <h4 class="no-margin text-bold ptop-15"><i class="fa fa-dashboard menu-icon"></i> <?php echo e($title); ?></h4>
          </div>

          <div class="col-md-6">
            
          <div class="_hidden_inputs _filters _tasks_filters">
              <?php

                $google_accounts = ga_get_contact_account_ids_by_base_workspace('google_analytic');

                echo form_hidden('ga_tab_active', 'acquisition');
                echo form_hidden('ga_sub_tab_active', 'all');

              ?>
          </div>
          <div class="col-md-4">
            <label for="account_id" class="control-label"><?php echo _l('property'); ?></label>
            <select name="account_id" id="account_id" class="selectpicker" data-width="100%">
              <?php foreach ($google_accounts as $key => $value) {
                echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
              } ?>
            </select>
          </div>
          <div class="col-md-4">
            <?php echo render_date_input('from_date','from_date', _d(date('Y-m-01'))); ?>
          </div>
          <div class="col-md-4">
            <?php echo render_date_input('to_date','to_date', _d(date('Y-m-d', strtotime('today')))); ?>
          </div>
          </div>
        </div>
          <div class="horizontal-scrollable-tabs panel-full-width-tabs mtop40 ga_tab">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
              <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                <li role="presentation" class="active">
                  <a href="#tab_acquisition" aria-controls="tab_acquisition" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'all'); return false;">
                    <?php echo _l('ga_acquisition'); ?>
                  </a>
                </li>
                <li role="presentation">
                  <a href="#tab_audience" aria-controls="tab_audience" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'location'); return false;">
                    <?php echo _l('ga_audience'); ?>
                  </a>
                </li>
                <li role="presentation">
                  <a href="#tab_conversions" aria-controls="tab_conversions" role="tab" data-toggle="tab"  onclick="ga_tab_active('conversions', 'campaign'); return false;">
                    <?php echo _l('ga_conversions'); ?>
                  </a>
                </li>
                <li role="presentation">
                  <a href="#tab_pages" aria-controls="tab_pages" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'all'); return false;">
                    <?php echo _l('ga_pages'); ?>
                  </a>
                </li>
                <li role="presentation">
                  <a href="#tab_events" aria-controls="tab_events" role="tab" data-toggle="tab"  onclick="ga_tab_active('events', 'all'); return false;">
                    <?php echo _l('ga_events'); ?>
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <div class="tab-content tw-mt-5">
            <div role="tabpanel" class="tab-pane ga-tab-content active" id="tab_acquisition">
              <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                  <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active">
                      <a href="#tab_all" aria-controls="tab_all" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'all'); return false;">
                        <?php echo _l('ga_all'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_organic_search" aria-controls="tab_organic_search" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'organic_search'); return false;">
                        <?php echo _l('ga_organic_search'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_paid_search" aria-controls="tab_paid_search" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'paid_search'); return false;">
                        <?php echo _l('ga_paid_search'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_direct" aria-controls="tab_direct" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'direct'); return false;">
                        <?php echo _l('ga_direct'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_social" aria-controls="tab_social" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'social'); return false;">
                        <?php echo _l('ga_social'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_referral" aria-controls="tab_referral" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'referral'); return false;">
                        <?php echo _l('ga_referral'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_display" aria-controls="tab_display" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'display'); return false;">
                        <?php echo _l('ga_display'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_email" aria-controls="tab_email" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'email'); return false;">
                        <?php echo _l('ga_email'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_video" aria-controls="tab_video" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'video'); return false;">
                        <?php echo _l('ga_video'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_paid_social" aria-controls="tab_paid_social" role="tab" data-toggle="tab" onclick="ga_tab_active('acquisition', 'paid_social'); return false;">
                        <?php echo _l('ga_paid_social'); ?>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane ga-tab-content" id="tab_audience">
              <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                  <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active">
                      <a href="#tab_location" aria-controls="tab_location" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'location'); return false;">
                        <?php echo _l('ga_location'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_language" aria-controls="tab_language" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'language'); return false;">
                        <?php echo _l('ga_language'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_age" aria-controls="tab_age" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'age'); return false;">
                        <?php echo _l('ga_age'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_devices" aria-controls="tab_devices" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'devices'); return false;">
                        <?php echo _l('ga_devices'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_gender" aria-controls="tab_gender" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'gender'); return false;">
                        <?php echo _l('ga_gender'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_browser" aria-controls="tab_browser" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'browser'); return false;">
                        <?php echo _l('ga_browser'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_operating_system" aria-controls="tab_operating_system" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'operating_system'); return false;">
                        <?php echo _l('ga_operating_system'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_interests" aria-controls="tab_interests" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'interests'); return false;">
                        <?php echo _l('ga_interests'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_new_vs_returning" aria-controls="tab_new_vs_returning" role="tab" data-toggle="tab"  onclick="ga_tab_active('audience', 'new_vs_returning'); return false;">
                        <?php echo _l('ga_new_vs_returning'); ?>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane ga-tab-content" id="tab_conversions">
              <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                  <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active">
                      <a href="#tab_campaign" aria-controls="tab_campaign" role="tab" data-toggle="tab"  onclick="ga_tab_active('conversions', 'campaign'); return false;">
                        <?php echo _l('ga_campaigns'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_ecommerce" aria-controls="tab_ecommerce" role="tab" data-toggle="tab"  onclick="ga_tab_active('conversions', 'ecommerce'); return false;">
                        <?php echo _l('ga_ecommerce'); ?>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane ga-tab-content" id="tab_pages">
              <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                <div class="horizontal-tabs">
                  <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="active">
                      <a href="#tab_all" aria-controls="tab_all" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'all'); return false;">
                        <?php echo _l('ga_all'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_landing_pages" aria-controls="tab_landing_pages" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'landing_pages'); return false;">
                        <?php echo _l('ga_landing_pages'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_path" aria-controls="tab_path" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'path'); return false;">
                        <?php echo _l('ga_path'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_title" aria-controls="tab_title" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'title'); return false;">
                        <?php echo _l('ga_title'); ?>
                      </a>
                    </li>
                    <li role="presentation">
                      <a href="#tab_content_group" aria-controls="tab_content_group" role="tab" data-toggle="tab"  onclick="ga_tab_active('pages', 'content_group'); return false;">
                        <?php echo _l('ga_content_group'); ?>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane ga-tab-content" id="tab_events">
              
            </div>
          </div>
        <hr class="mtop-5">
        <div class="clearfix"></div>
        
        <div class="row mtop40">
          <div class="col-md-6">
            <div id="session_per_day"></div>
          </div>
          <div class="col-md-6">
            <div id="session_per_channel"></div>
          </div>
        </div>
        <div id="top_stats" class="mtop40"></div>
        <div id="table_data" class="mtop40"></div>

      </div>
    </div>
  </div>
</div>
<?php require('modules/google_analytic/assets/js/clients/manage_js.php'); ?>
