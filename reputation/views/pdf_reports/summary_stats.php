<h4 class="country-title"><?php echo _l('stats'); ?></h4>
<ul class="country-list">
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "google_news" AND '. $where); ?>
      <li>
          <span class="country-name"><?php echo _l('non_social_mentions'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?></span>
      </li>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform != "google_news" AND '. $where); ?>
      <li>
          <span class="country-name"><?php echo _l('social_media_mentions'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?></span>
      </li>
      <?php $total = total_rows(db_prefix() . 'rep_mentions', 'platform = "youtube" AND '. $where); ?>
      <li>
          <span class="country-name"><?php echo _l('video'); ?></span>
          <span class="country-count"><?php echo number_format($total ?? 0); ?></span>
      </li>
      <?php $shares = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'shares', 'where' => array('platform != "google_news" AND '. $where))); ?>
      <li>
          <span class="country-name"><?php echo _l('social_media_shares'); ?></span>
          <span class="country-count"><?php echo number_format($shares ?? 0); ?></span>
      </li>

      <?php $likes = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'likes', 'where' => array('platform != "google_news" AND '. $where))); ?>
      <li>
          <span class="country-name"><?php echo _l('social_media_likes'); ?></span>
          <span class="country-count"><?php echo number_format($likes ?? 0); ?></span>
      </li>
</ul>
