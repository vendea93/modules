<div class="summary-dashboard-stats">
    <div class="stat-card">
      <div class="icon"><i class="fas fa-comments"></i></div>
      <div class="stat-info">
        <h3><?php echo _l('mentions'); ?></h3>
        <?php
              $total = total_rows(db_prefix() . 'rep_mentions', $where);
              ?>
        <p><?php echo number_format($total ?? 0); ?></p>
      </div>
    </div>

    <div class="stat-card">
      <div class="icon"><i class="fas fa-eye"></i></div>
      <div class="stat-info">
        <h3><?php echo _l('sm_reach'); ?></h3>
        <?php
              $total = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'pageviews', 'where' => array($where)));
              ?>
        <p><?php echo number_format($total ?? 0); ?></p>
      </div>
    </div>

    <div class="stat-card">
      <div class="icon"><i class="fas fa-handshake"></i></div>
      <div class="stat-info">
        <h3><?php echo _l('interaction'); ?></h3>
        <?php
              $total= 0;
              $comments = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'comments', 'where' => array($where)));
              $shares = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'shares', 'where' => array($where)));
              $likes = sum_from_table(db_prefix() . 'rep_mentions', array('field' => 'likes', 'where' => array($where)));
              
                if(is_numeric($comments)){
                    $total += $comments;
                }

                if(is_numeric($shares)){
                    $total += $shares;
                }

                if(is_numeric($likes)){
                    $total += $likes;
                }

                
              ?>
        <p><?php echo number_format($total ?? 0); ?></p>
      </div>
      
    </div>

    <div class="stat-card">
      <div class="icon"><i class="fas fa-smile"></i></div>
      <div class="stat-info">
        <h3><?php echo _l('positive'); ?></h3>
        <?php
              $total = total_rows(db_prefix() . 'rep_mentions', 'sentiment = "Positive" AND '. $where);

              ?>
              
        <p><?php echo number_format($total ?? 0); ?></p>
      </div>
      
    </div>

    <div class="stat-card">
      <div class="icon"><i class="fas fa-frown"></i></div>
      <div class="stat-info">
        <h3><?php echo _l('negative'); ?></h3>
        <?php
              $total = total_rows(db_prefix() . 'rep_mentions', 'sentiment = "Negative" AND '. $where);
              ?>
        <p><?php echo number_format($total ?? 0); ?></p>
      </div>
      
    </div>
</div>
