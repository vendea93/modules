<div class="row">
  <div class="col-md-4">
    <div class="tw-flex tw-space-x-4 tw-items-center">
      <h4 class="tw-font-medium tw-text-neutral-600 tw-text-lg">
        <?php echo _l('ga_ecommerce'); ?>
      </h4>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="cartToViewRate" <?php echo (in_array('cartToViewRate', $metrics) ? 'checked' : ''); ?> id="metric_option_cart_to_view_rate" name="dashboard_metrics[cartToViewRate]" onchange="selectMetric(this)">
      <label for="metric_option_cart_to_view_rate"><?php echo _l('cartToViewRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('cartToViewRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="firstTimePurchasers" <?php echo (in_array('firstTimePurchasers', $metrics) ? 'checked' : ''); ?> id="metric_option_first_time_purchasers" name="dashboard_metrics[firstTimePurchasers]" onchange="selectMetric(this)">
      <label for="metric_option_first_time_purchasers"><?php echo _l('firstTimePurchasers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('firstTimePurchasers_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemListClickThroughRate" <?php echo (in_array('itemListClickThroughRate', $metrics) ? 'checked' : ''); ?> id="metric_option_item_list_click_through_rate" name="dashboard_metrics[itemListClickThroughRate]" onchange="selectMetric(this)">
      <label for="metric_option_item_list_click_through_rate"><?php echo _l('itemListClickThroughRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemListClickThroughRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemPromotionClickThroughRate" <?php echo (in_array('itemPromotionClickThroughRate', $metrics) ? 'checked' : ''); ?> id="metric_option_item_promotion_click_through_rate" name="dashboard_metrics[itemPromotionClickThroughRate]" onchange="selectMetric(this)">
      <label for="metric_option_item_promotion_click_through_rate"><?php echo _l('itemPromotionClickThroughRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemPromotionClickThroughRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemRevenue" <?php echo (in_array('itemRevenue', $metrics) ? 'checked' : ''); ?> id="metric_option_item_revenue" name="dashboard_metrics[itemRevenue]" onchange="selectMetric(this)">
      <label for="metric_option_item_revenue"><?php echo _l('itemRevenue'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemRevenue_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsAddedToCart" <?php echo (in_array('itemsAddedToCart', $metrics) ? 'checked' : ''); ?> id="metric_option_items_added_to_cart" name="dashboard_metrics[itemsAddedToCart]" onchange="selectMetric(this)">
      <label for="metric_option_items_added_to_cart"><?php echo _l('itemsAddedToCart'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsAddedToCart_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsCheckedOut" <?php echo (in_array('itemsCheckedOut', $metrics) ? 'checked' : ''); ?> id="metric_option_items_checked_out" name="dashboard_metrics[itemsCheckedOut]" onchange="selectMetric(this)">
      <label for="metric_option_items_checked_out"><?php echo _l('itemsCheckedOut'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsCheckedOut_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsClickedInList" <?php echo (in_array('itemsClickedInList', $metrics) ? 'checked' : ''); ?> id="metric_option_items_clicked_in_list" name="dashboard_metrics[itemsClickedInList]" onchange="selectMetric(this)">
      <label for="metric_option_items_clicked_in_list"><?php echo _l('itemsClickedInList'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsClickedInList_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsClickedInPromotion" <?php echo (in_array('itemsClickedInPromotion', $metrics) ? 'checked' : ''); ?> id="metric_option_items_clicked_in_promotion" name="dashboard_metrics[itemsClickedInPromotion]" onchange="selectMetric(this)">
      <label for="metric_option_items_clicked_in_promotion"><?php echo _l('itemsClickedInPromotion'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsClickedInPromotion_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsPurchased" <?php echo (in_array('itemsPurchased', $metrics) ? 'checked' : ''); ?> id="metric_option_items_purchased" name="dashboard_metrics[itemsPurchased]" onchange="selectMetric(this)">
      <label for="metric_option_items_purchased"><?php echo _l('itemsPurchased'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsPurchased_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsViewed" <?php echo (in_array('itemsViewed', $metrics) ? 'checked' : ''); ?> id="metric_option_items_viewed" name="dashboard_metrics[itemsViewed]" onchange="selectMetric(this)">
      <label for="metric_option_items_viewed"><?php echo _l('itemsViewed'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsViewed_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsViewedInList" <?php echo (in_array('itemsViewedInList', $metrics) ? 'checked' : ''); ?> id="metric_option_items_viewed_in_list" name="dashboard_metrics[itemsViewedInList]" onchange="selectMetric(this)">
      <label for="metric_option_items_viewed_in_list"><?php echo _l('itemsViewedInList'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsViewedInList_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="itemsViewedInPromotion" <?php echo (in_array('itemsViewedInPromotion', $metrics) ? 'checked' : ''); ?> id="metric_option_items_viewed_in_promotion" name="dashboard_metrics[itemsViewedInPromotion]" onchange="selectMetric(this)">
      <label for="metric_option_items_viewed_in_promotion"><?php echo _l('itemsViewedInPromotion'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('itemsViewedInPromotion_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="purchaseToViewRate" <?php echo (in_array('purchaseToViewRate', $metrics) ? 'checked' : ''); ?> id="metric_option_purchase_to_view_rate" name="dashboard_metrics[purchaseToViewRate]" onchange="selectMetric(this)">
      <label for="metric_option_purchase_to_view_rate"><?php echo _l('purchaseToViewRate'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('purchaseToViewRate_note')?>"></i></label>
    </div>
    <div class="checkbox">
      <input type="checkbox" class="widget-visibility" value="totalPurchasers" <?php echo (in_array('totalPurchasers', $metrics) ? 'checked' : ''); ?> id="metric_option_total_purchasers" name="dashboard_metrics[totalPurchasers]" onchange="selectMetric(this)">
      <label for="metric_option_total_purchasers"><?php echo _l('totalPurchasers'); ?> <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('totalPurchasers_note')?>"></i></label>
    </div>
  </div>
</div>