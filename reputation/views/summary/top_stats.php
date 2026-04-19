<div class="dashboard-overview">
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('mentions'); ?>
    </div>
      <?php $mention_total = total_rows(db_prefix() . 'rep_mentions', $where); ?>
    <div class="dashboard-card-value"><?php echo number_format($mention_total ?? 0); ?></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('social_media_mentions'); ?>
    </div>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform != "google_news" AND '.$where); ?>
    <div class="dashboard-card-value"><?php echo number_format($total ?? 0); ?></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('non_social_mentions'); ?>
    </div>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "google_news" AND '. $where); ?>
    <div class="dashboard-card-value"><?php echo number_format($total ?? 0); ?></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('social_media_interactions'); ?>
    </div>
      <?php $likes = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'likes', 'where' => array('platform != "google_news" AND '. $where))); ?>
      <?php $comments = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'comments', 'where' => array('platform != "google_news" AND '. $where))); ?>
      <?php $shares = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'shares', 'where' => array('platform != "google_news" AND '. $where))); ?>
    <div class="dashboard-card-value"><?php echo number_format($shares + $comments + $likes); ?></div>
  </div>

  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('sm_reach'); ?>
    </div>
    <?php $total = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array($where)));?>
    <div class="dashboard-card-value"><?php echo number_format($total ?? 0); ?></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('social_media_likes'); ?>
    </div>
    <div class="dashboard-card-value"><?php echo number_format($likes ?? 0); ?></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('video'); ?>
    </div>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "youtube" AND '. $where); ?>
    <div class="dashboard-card-value"><?php echo number_format($total ?? 0); ?></div>
  </div>

  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('positive_mentions'); ?>
    </div>
      <?php $Positive = total_rows(db_prefix() . 'rep_mentions', 'sentiment = "Positive" AND '. $where); ?>
    <div class="dashboard-card-value"><?php echo number_format($Positive ?? 0); ?> <span class="dashboard-percent dashboard-percent-positive">(<?php echo round(($Positive/$mention_total) * 100, 2); ?>%)</span></div>
  </div>
  <div class="dashboard-card">
    <div class="dashboard-card-title">
      <i class="dashboard-icon"></i> <?php echo _l('negative_mentions'); ?>
    </div>
      <?php $Negative = total_rows(db_prefix() . 'rep_mentions', 'sentiment = "Negative" AND '. $where); ?>
    <div class="dashboard-card-value"><?php echo number_format($Negative ?? 0); ?> <span class="dashboard-percent dashboard-percent-negative">(<?php echo round(($Negative/$mention_total) * 100, 2); ?>%)</span></div>
  </div>
 
</div>
