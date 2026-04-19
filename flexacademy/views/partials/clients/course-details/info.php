<div id="info" class="flexacademy-tabs-pane">
    <div class="row">
        <!-- Requirements Section -->
        <div class="col-md-6 mb-4">
            <h4 class="tw-text-base tw-font-bold tw-mb-3"><?php echo _flexacademy_lang('requirements'); ?></h4>
            <div class="tw-list-unstyled">
                <?php foreach ($requirements as $requirement): ?>
                    <div class="tw-flex tw-items-start tw-mb-4">
                    <span class="flexacademy-info-icon-wrapper tw-inline-flex tw-items-center tw-justify-center tw-mr-2 tw-flex-shrink-0"><i class="fa fa-check tw-text-white tw-text-xs"></i></span>
                        <span class="tw-text-base tw-text-gray-800"><?php echo $requirement; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Outcomes Section -->
        <div class="col-md-6 mb-4">
            <h4 class="tw-text-base tw-font-bold tw-mb-3"><?php echo _flexacademy_lang('outcomes'); ?></h4>
            <div class="tw-list-unstyled">
                <?php foreach ($outcomes as $outcome): ?>
                    <div class="tw-flex tw-items-start tw-mb-4">
                        <span class="flexacademy-info-icon-wrapper tw-inline-flex tw-items-center tw-justify-center tw-mr-2 tw-flex-shrink-0"><i class="fa fa-check tw-text-white tw-text-xs"></i></span>
                        <span class="tw-text-base tw-text-gray-800"><?php echo $outcome; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>