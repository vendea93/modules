<h4 class="country-title"><?php echo _l('the_most_influential_sites'); ?></h4>
<ul class="country-list">

      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "google_news" AND '. $where); ?>
      <?php $pageviews = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array('platform = "google_news" AND '.$where))); ?>
      <li>
          <span class="country-name"><?php echo _l('google_news'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?> <?php echo _l('mentions'); ?></span>
          <span class="country-count"><?php echo number_format($pageviews ?? 0); ?> <?php echo _l('visits'); ?></span>
      </li>

      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "facebook" AND '. $where); ?>
      <?php $pageviews = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array('platform = "facebook" AND '.$where))); ?>
      <li>
          <span class="country-name"><?php echo _l('facebook'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?> <?php echo _l('mentions'); ?></span>
          <span class="country-count"><?php echo number_format($pageviews ?? 0); ?> <?php echo _l('visits'); ?></span>
      </li>

      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "instagram" AND '. $where); ?>
      <?php $pageviews = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array('platform = "instagram" AND '.$where))); ?>
      <li>
          <span class="country-name"><?php echo _l('instagram'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?> <?php echo _l('mentions'); ?></span>
          <span class="country-count"><?php echo number_format($pageviews ?? 0); ?> <?php echo _l('visits'); ?></span>
      </li>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "youtube" AND '. $where); ?>
      <?php $pageviews = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array('platform = "youtube" AND '.$where))); ?>
      <li>
          <span class="country-name"><?php echo _l('youtube'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?> <?php echo _l('mentions'); ?></span>
          <span class="country-count"><?php echo number_format($pageviews ?? 0); ?> <?php echo _l('visits'); ?></span>
      </li>

      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "x_twitter" AND '. $where); ?>
      <?php $pageviews = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array('platform = "x_twitter" AND '.$where))); ?>
      <li>
          <span class="country-name"><?php echo _l('x_twitter'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?> <?php echo _l('mentions'); ?></span>
          <span class="country-count"><?php echo number_format($pageviews ?? 0); ?> <?php echo _l('visits'); ?></span>
      </li>
</ul>
