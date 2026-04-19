<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-4">
    <div class="panel_s">
        <div class="panel-body">
            <h5 class="no-margin"><?php echo _flexacademy_lang('completion-statistics'); ?></h5>
            <hr class="hr-panel-separator" />
            <?php if (!empty($completion_stats)) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center">
                            <h3 class="text-success"><?php echo $completion_stats['completed']; ?></h3>
                            <p><?php echo _flexacademy_lang('enrollment-completed'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <h3 class="text-info"><?php echo $completion_stats['in_progress']; ?></h3>
                            <p><?php echo _flexacademy_lang('enrollment-in-progress'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center">
                            <h3 class="text-warning"><?php echo $completion_stats['enrolled']; ?></h3>
                            <p><?php echo _flexacademy_lang('enrollment-enrolled'); ?></p>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <p class="text-muted"><?php echo _flexacademy_lang('no-enrollments-found'); ?></p>
            <?php } ?>
        </div>
    </div>
</div>

