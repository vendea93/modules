<div class="row">
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
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="userKeyEventRate" <?php echo (in_array('userKeyEventRate', $metrics) ? 'checked' : ''); ?> id="metric_option_user_key_event_rate" name="dashboard_metrics[userKeyEventRate]" onchange="selectMetric(this)">
      <label for="metric_option_user_key_event_rate"><?php echo _l('userKeyEventRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('userKeyEventRate_note')?>"></i></label>
    </div>
  </div>
</div>