<div class="row">
  <div class="col-md-4">
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_event_tracking'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="eventCount" <?php echo (in_array('eventCount', $metrics) ? 'checked' : ''); ?> id="metric_option_event_count" name="dashboard_metrics[eventCount]" onchange="selectMetric(this)">
      <label for="metric_option_event_count"><?php echo _l('eventCount'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('eventCount_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="eventCountPerUser" <?php echo (in_array('eventCountPerUser', $metrics) ? 'checked' : ''); ?> id="metric_option_event_count_per_user" name="dashboard_metrics[eventCountPerUser]" onchange="selectMetric(this)">
      <label for="metric_option_event_count_per_user"><?php echo _l('eventCountPerUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('eventCountPerUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="eventValue" <?php echo (in_array('eventValue', $metrics) ? 'checked' : ''); ?> id="metric_option_event_value" name="dashboard_metrics[eventValue]" onchange="selectMetric(this)">
      <label for="metric_option_event_value"><?php echo _l('eventValue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('eventValue_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="eventsPerSession" <?php echo (in_array('eventsPerSession', $metrics) ? 'checked' : ''); ?> id="metric_option_events_per_session" name="dashboard_metrics[eventsPerSession]" onchange="selectMetric(this)">
      <label for="metric_option_events_per_session"><?php echo _l('eventsPerSession'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('eventsPerSession_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="totalRevenue" <?php echo (in_array('totalRevenue', $metrics) ? 'checked' : ''); ?> id="metric_option_total_revenue" name="dashboard_metrics[totalRevenue]" onchange="selectMetric(this)">
      <label for="metric_option_total_revenue"><?php echo _l('totalRevenue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('totalRevenue_note')?>"></i></label>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_user'); ?>
      </h4>
    </div>
    
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="activeUsers" <?php echo (in_array('activeUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_active_users" name="dashboard_metrics[activeUsers]" onchange="selectMetric(this)">
      <label for="metric_option_active_users"><?php echo _l('activeUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('activeUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="newUsers" <?php echo (in_array('newUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_new_users" name="dashboard_metrics[newUsers]" onchange="selectMetric(this)">
      <label for="metric_option_new_users"><?php echo _l('newUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('newUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="totalUsers" <?php echo (in_array('totalUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_total_users" name="dashboard_metrics[totalUsers]" onchange="selectMetric(this)">
      <label for="metric_option_total_users"><?php echo _l('totalUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('totalUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="userEngagementDuration" <?php echo (in_array('userEngagementDuration', $metrics) ? 'checked' : ''); ?> id="metric_option_user_engagement" name="dashboard_metrics[userEngagementDuration]" onchange="selectMetric(this)">
      <label for="metric_option_user_engagement"><?php echo _l('userEngagementDuration'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('userEngagementDuration_note')?>"></i></label>
    </div>
  </div>
</div>