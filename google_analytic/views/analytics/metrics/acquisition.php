<div class="row">
  <div class="col-md-4">
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_ecommerce'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="averagePurchaseRevenuePerPayingUser" <?php echo (in_array('averagePurchaseRevenuePerPayingUser', $metrics) ? 'checked' : ''); ?> id="metric_option_arppu" name="dashboard_metrics[averagePurchaseRevenuePerPayingUser]" onchange="selectMetric(this)">
      <label for="metric_option_arppu"><?php echo _l('averagePurchaseRevenuePerPayingUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('averagePurchaseRevenuePerPayingUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="averageRevenuePerUser" <?php echo (in_array('averageRevenuePerUser', $metrics) ? 'checked' : ''); ?> id="metric_option_arpu" name="dashboard_metrics[averageRevenuePerUser]" onchange="selectMetric(this)">
      <label for="metric_option_arpu"><?php echo _l('averageRevenuePerUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('averageRevenuePerUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="addToCarts" <?php echo (in_array('addToCarts', $metrics) ? 'checked' : ''); ?> id="metric_option_add_to_carts" name="dashboard_metrics[addToCarts]" onchange="selectMetric(this)">
      <label for="metric_option_add_to_carts"><?php echo _l('addToCarts'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('addToCarts_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="averagePurchaseRevenue" <?php echo (in_array('averagePurchaseRevenue', $metrics) ? 'checked' : ''); ?> id="metric_option_average_purchase_revenue" name="dashboard_metrics[averagePurchaseRevenue]" onchange="selectMetric(this)">
      <label for="metric_option_average_purchase_revenue"><?php echo _l('averagePurchaseRevenue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('averagePurchaseRevenue_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="averagePurchaseRevenuePerUser" <?php echo (in_array('averagePurchaseRevenuePerUser', $metrics) ? 'checked' : ''); ?> id="metric_option_average_purchase_revenue_per_user" name="dashboard_metrics[averagePurchaseRevenuePerUser]" onchange="selectMetric(this)">
      <label for="metric_option_average_purchase_revenue_per_user"><?php echo _l('averagePurchaseRevenuePerUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('averagePurchaseRevenuePerUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="cartToViewRate" <?php echo (in_array('cartToViewRate', $metrics) ? 'checked' : ''); ?> id="metric_option_cart_to_view_rate" name="dashboard_metrics[cartToViewRate]" onchange="selectMetric(this)">
      <label for="metric_option_cart_to_view_rate"><?php echo _l('cartToViewRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('cartToViewRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="checkouts" <?php echo (in_array('checkouts', $metrics) ? 'checked' : ''); ?> id="metric_option_checkouts" name="dashboard_metrics[checkouts]" onchange="selectMetric(this)">
      <label for="metric_option_checkouts"><?php echo _l('checkouts'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('checkouts_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="ecommercePurchases" <?php echo (in_array('ecommercePurchases', $metrics) ? 'checked' : ''); ?> id="metric_option_ecommerce_purchases" name="dashboard_metrics[ecommercePurchases]" onchange="selectMetric(this)">
      <label for="metric_option_ecommerce_purchases"><?php echo _l('ecommercePurchases'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('ecommercePurchases_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="firstTimePurchasers" <?php echo (in_array('firstTimePurchasers', $metrics) ? 'checked' : ''); ?> id="metric_option_first_time_purchasers" name="dashboard_metrics[firstTimePurchasers]" onchange="selectMetric(this)">
      <label for="metric_option_first_time_purchasers"><?php echo _l('firstTimePurchasers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('firstTimePurchasers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="firstTimePurchaserRate" <?php echo (in_array('firstTimePurchaserRate', $metrics) ? 'checked' : ''); ?> id="metric_option_first_time_purchaser_rate" name="dashboard_metrics[firstTimePurchaserRate]" onchange="selectMetric(this)">
      <label for="metric_option_first_time_purchaser_rate"><?php echo _l('firstTimePurchaserRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('firstTimePurchaserRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="firstTimePurchasersPerNewUser" <?php echo (in_array('firstTimePurchasersPerNewUser', $metrics) ? 'checked' : ''); ?> id="metric_option_first_time_purchasers_per_new_user" name="dashboard_metrics[firstTimePurchasersPerNewUser]" onchange="selectMetric(this)">
      <label for="metric_option_first_time_purchasers_per_new_user"><?php echo _l('firstTimePurchasersPerNewUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('firstTimePurchasersPerNewUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemListClickThroughRate" <?php echo (in_array('itemListClickThroughRate', $metrics) ? 'checked' : ''); ?> id="metric_option_item_list_click_through_rate" name="dashboard_metrics[itemListClickThroughRate]" onchange="selectMetric(this)">
      <label for="metric_option_item_list_click_through_rate"><?php echo _l('itemListClickThroughRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemListClickThroughRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemViewEvents" <?php echo (in_array('itemViewEvents', $metrics) ? 'checked' : ''); ?> id="metric_option_item_view_events" name="dashboard_metrics[itemViewEvents]" onchange="selectMetric(this)">
      <label for="metric_option_item_view_events"><?php echo _l('itemViewEvents'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemViewEvents_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemListClickEvents" <?php echo (in_array('itemListClickEvents', $metrics) ? 'checked' : ''); ?> id="metric_option_item_list_click_events" name="dashboard_metrics[itemListClickEvents]" onchange="selectMetric(this)">
      <label for="metric_option_item_list_click_events"><?php echo _l('itemListClickEvents'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemListClickEvents_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemListViewEvents" <?php echo (in_array('itemListViewEvents', $metrics) ? 'checked' : ''); ?> id="metric_option_item_list_view_events" name="dashboard_metrics[itemListViewEvents]" onchange="selectMetric(this)">
      <label for="metric_option_item_list_view_events"><?php echo _l('itemListViewEvents'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemListViewEvents_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="promotionClicks" <?php echo (in_array('promotionClicks', $metrics) ? 'checked' : ''); ?> id="metric_option_promotion_clicks" name="dashboard_metrics[promotionClicks]" onchange="selectMetric(this)">
      <label for="metric_option_promotion_clicks"><?php echo _l('promotionClicks'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('promotionClicks_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="promotionViews" <?php echo (in_array('promotionViews', $metrics) ? 'checked' : ''); ?> id="metric_option_promotion_views" name="dashboard_metrics[promotionViews]" onchange="selectMetric(this)">
      <label for="metric_option_promotion_views"><?php echo _l('promotionViews'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('promotionViews_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="purchaseRevenue" <?php echo (in_array('purchaseRevenue', $metrics) ? 'checked' : ''); ?> id="metric_option_purchase_revenue" name="dashboard_metrics[purchaseRevenue]" onchange="selectMetric(this)">
      <label for="metric_option_purchase_revenue"><?php echo _l('purchaseRevenue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('purchaseRevenue_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="purchaseToViewRate" <?php echo (in_array('purchaseToViewRate', $metrics) ? 'checked' : ''); ?> id="metric_option_purchase_to_view_rate" name="dashboard_metrics[purchaseToViewRate]" onchange="selectMetric(this)">
      <label for="metric_option_purchase_to_view_rate"><?php echo _l('purchaseToViewRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('purchaseToViewRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="purchaserRate" <?php echo (in_array('purchaserRate', $metrics) ? 'checked' : ''); ?> id="metric_option_purchaser_rate" name="dashboard_metrics[purchaserRate]" onchange="selectMetric(this)">
      <label for="metric_option_purchaser_rate"><?php echo _l('purchaserRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('purchaserRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="shippingAmount" <?php echo (in_array('shippingAmount', $metrics) ? 'checked' : ''); ?> id="metric_option_shipping_amount" name="dashboard_metrics[shippingAmount]" onchange="selectMetric(this)">
      <label for="metric_option_shipping_amount"><?php echo _l('shippingAmount'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('shippingAmount_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="taxAmount" <?php echo (in_array('taxAmount', $metrics) ? 'checked' : ''); ?> id="metric_option_tax_amount" name="dashboard_metrics[taxAmount]" onchange="selectMetric(this)">
      <label for="metric_option_tax_amount"><?php echo _l('taxAmount'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('taxAmount_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="totalPurchasers" <?php echo (in_array('totalPurchasers', $metrics) ? 'checked' : ''); ?> id="metric_option_total_purchasers" name="dashboard_metrics[totalPurchasers]" onchange="selectMetric(this)">
      <label for="metric_option_total_purchasers"><?php echo _l('totalPurchasers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('totalPurchasers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="totalRevenue" <?php echo (in_array('totalRevenue', $metrics) ? 'checked' : ''); ?> id="metric_option_total_revenue" name="dashboard_metrics[totalRevenue]" onchange="selectMetric(this)">
      <label for="metric_option_total_revenue"><?php echo _l('totalRevenue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('totalRevenue_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="transactions" <?php echo (in_array('transactions', $metrics) ? 'checked' : ''); ?> id="metric_option_transactions" name="dashboard_metrics[transactions]" onchange="selectMetric(this)">
      <label for="metric_option_transactions"><?php echo _l('transactions'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('transactions_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="transactionsPerPurchaser" <?php echo (in_array('transactionsPerPurchaser', $metrics) ? 'checked' : ''); ?> id="metric_option_transactions_per_purchaser" name="dashboard_metrics[transactionsPerPurchaser]" onchange="selectMetric(this)">
      <label for="metric_option_transactions_per_purchaser"><?php echo _l('transactionsPerPurchaser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('transactionsPerPurchaser_note')?>"></i></label>
    </div>
  </div>
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
      <input type="checkbox" class="widget-visibility" value="keyEvents" <?php echo (in_array('keyEvents', $metrics) ? 'checked' : ''); ?> id="metric_option_key_events" name="dashboard_metrics[keyEvents]" onchange="selectMetric(this)">
      <label for="metric_option_key_events"><?php echo _l('keyEvents'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('keyEvents_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="publisherAdClicks" <?php echo (in_array('publisherAdClicks', $metrics) ? 'checked' : ''); ?> id="metric_option_publisher_ad_clicks" name="dashboard_metrics[publisherAdClicks]" onchange="selectMetric(this)">
      <label for="metric_option_publisher_ad_clicks"><?php echo _l('publisherAdClicks'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('publisherAdClicks_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="publisherAdImpressions" <?php echo (in_array('publisherAdImpressions', $metrics) ? 'checked' : ''); ?> id="metric_option_publisher_ad_impressions" name="dashboard_metrics[publisherAdImpressions]" onchange="selectMetric(this)">
      <label for="metric_option_publisher_ad_impressions"><?php echo _l('publisherAdImpressions'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('publisherAdImpressions_note')?>"></i></label>
    </div>
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_page_tracking'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="bounceRate" <?php echo (in_array('bounceRate', $metrics) ? 'checked' : ''); ?> id="metric_option_bounce_rate" name="dashboard_metrics[bounceRate]" onchange="selectMetric(this)">
      <label for="metric_option_bounce_rate"><?php echo _l('bounceRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('bounceRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="screenPageViewsPerSession" <?php echo (in_array('screenPageViewsPerSession', $metrics) ? 'checked' : ''); ?> id="metric_option_screen_page_views_per_session" name="dashboard_metrics[screenPageViewsPerSession]" onchange="selectMetric(this)">
      <label for="metric_option_screen_page_views_per_session"><?php echo _l('screenPageViewsPerSession'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('screenPageViewsPerSession_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="screenPageViewsPerUser" <?php echo (in_array('screenPageViewsPerUser', $metrics) ? 'checked' : ''); ?> id="metric_option_screen_page_views_per_user" name="dashboard_metrics[screenPageViewsPerUser]" onchange="selectMetric(this)">
      <label for="metric_option_screen_page_views_per_user"><?php echo _l('screenPageViewsPerUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('screenPageViewsPerUser_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="screenPageViews" <?php echo (in_array('screenPageViews', $metrics) ? 'checked' : ''); ?> id="metric_option_views" name="dashboard_metrics[screenPageViews]" onchange="selectMetric(this)">
      <label for="metric_option_views"><?php echo _l('screenPageViews'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('screenPageViews_note')?>"></i></label>
    </div>
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_session'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="engagedSessions" <?php echo (in_array('engagedSessions', $metrics) ? 'checked' : ''); ?> id="metric_option_engaged_sessions" name="dashboard_metrics[engagedSessions]" onchange="selectMetric(this)">
      <label for="metric_option_engaged_sessions"><?php echo _l('engagedSessions'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('engagedSessions_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="engagementRate" <?php echo (in_array('engagementRate', $metrics) ? 'checked' : ''); ?> id="metric_option_engagement_rate" name="dashboard_metrics[engagementRate]" onchange="selectMetric(this)">
      <label for="metric_option_engagement_rate"><?php echo _l('engagementRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('engagementRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="sessionKeyEventRate" <?php echo (in_array('sessionKeyEventRate', $metrics) ? 'checked' : ''); ?> id="metric_option_session_key_event_rate" name="dashboard_metrics[sessionKeyEventRate]" onchange="selectMetric(this)">
      <label for="metric_option_session_key_event_rate"><?php echo _l('sessionKeyEventRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('sessionKeyEventRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="sessions" <?php echo (in_array('sessions', $metrics) ? 'checked' : ''); ?> id="metric_option_sessions" name="dashboard_metrics[sessions]" onchange="selectMetric(this)">
      <label for="metric_option_sessions"><?php echo _l('sessions'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('sessions_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="sessionsPerUser" <?php echo (in_array('sessionsPerUser', $metrics) ? 'checked' : ''); ?> id="metric_option_sessions_per_user" name="dashboard_metrics[sessionsPerUser]" onchange="selectMetric(this)">
      <label for="metric_option_sessions_per_user"><?php echo _l('sessionsPerUser'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('sessionsPerUser_note')?>"></i></label>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_user'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="active1DayUsers" <?php echo (in_array('active1DayUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_1_day_active_users" name="dashboard_metrics[active1DayUsers]" onchange="selectMetric(this)">
      <label for="metric_option_1_day_active_users"><?php echo _l('active1DayUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('active1DayUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="active28DayUsers" <?php echo (in_array('active28DayUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_28_day_active_users" name="dashboard_metrics[active28DayUsers]" onchange="selectMetric(this)">
      <label for="metric_option_28_day_active_users"><?php echo _l('active28DayUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('active28DayUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="active7DayUsers" <?php echo (in_array('active7DayUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_7_day_active_users" name="dashboard_metrics[active7DayUsers]" onchange="selectMetric(this)">
      <label for="metric_option_7_day_active_users"><?php echo _l('active7DayUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('active7DayUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="activeUsers" <?php echo (in_array('activeUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_active_users" name="dashboard_metrics[activeUsers]" onchange="selectMetric(this)">
      <label for="metric_option_active_users"><?php echo _l('activeUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('activeUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="averageSessionDuration" <?php echo (in_array('averageSessionDuration', $metrics) ? 'checked' : ''); ?> id="metric_option_average_session_duration" name="dashboard_metrics[averageSessionDuration]" onchange="selectMetric(this)">
      <label for="metric_option_average_session_duration"><?php echo _l('averageSessionDuration'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('averageSessionDuration_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="crashAffectedUsers" <?php echo (in_array('crashAffectedUsers', $metrics) ? 'checked' : ''); ?> id="metric_option_crash_affected_users" name="dashboard_metrics[crashAffectedUsers]" onchange="selectMetric(this)">
      <label for="metric_option_crash_affected_users"><?php echo _l('crashAffectedUsers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('crashAffectedUsers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="crashFreeUsersRate" <?php echo (in_array('crashFreeUsersRate', $metrics) ? 'checked' : ''); ?> id="metric_option_crash_free_users_rate" name="dashboard_metrics[crashFreeUsersRate]" onchange="selectMetric(this)">
      <label for="metric_option_crash_free_users_rate"><?php echo _l('crashFreeUsersRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('crashFreeUsersRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="dauPerMau" <?php echo (in_array('dauPerMau', $metrics) ? 'checked' : ''); ?> id="metric_option_dau_vs_mau" name="dashboard_metrics[dauPerMau]" onchange="selectMetric(this)">
      <label for="metric_option_dau_vs_mau"><?php echo _l('dauPerMau'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('dauPerMau_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="dauPerWau" <?php echo (in_array('dauPerWau', $metrics) ? 'checked' : ''); ?> id="metric_option_dau_vs_wau" name="dashboard_metrics[dauPerWau]" onchange="selectMetric(this)">
      <label for="metric_option_dau_vs_wau"><?php echo _l('dauPerWau'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('dauPerWau_note')?>"></i></label>
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
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="wauPerMau" <?php echo (in_array('wauPerMau', $metrics) ? 'checked' : ''); ?> id="metric_option_wau_vs_mau" name="dashboard_metrics[wauPerMau]" onchange="selectMetric(this)">
      <label for="metric_option_wau_vs_mau"><?php echo _l('wauPerMau'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('wauPerMau_note')?>"></i></label>
    </div>
  </div>
</div>