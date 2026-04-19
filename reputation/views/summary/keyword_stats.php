<h4 class="country-title"><?php echo _l('keywords'); ?></h4>
<ul class="country-list">

      <?php 
      foreach ($keyword_stats as $count) { ?>
      <li>
          <span class="country-name"><?php echo e($count['keyword']); ?></span>
          <span class="country-count"><?php echo number_format($count['usage_count'] ?? 0); ?></span>
      </li>
      <?php } ?>

</ul>
