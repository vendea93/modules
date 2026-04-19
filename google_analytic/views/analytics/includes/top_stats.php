<div class="dashboard-stats">
    <?php 
    $i = 0;
    foreach($top_stats as $name => $value){ ?>
        <div class="stat-card <?php echo ($i == 0 ? 'active' : ''); ?>" id="<?php echo e($name); ?>">
            <div class="stat-header">
                <span class="stat-title"><?php echo _l($name); ?></span>
                <span class="tooltip-icon"><i class="fa fa-question-circle"></i></span>
                <div class="tooltip-text"><?php echo _l($name.'_note'); ?></div>
            </div>
            <div class="stat-value"><?php echo e($value); ?></div>
        </div>
    <?php 
    $i++;
} ?>
</div>