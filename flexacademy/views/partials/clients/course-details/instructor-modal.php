<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade"
    id="flexacademy-instructor-details-<?php echo $instructor['id']; ?>"
    tabindex="-1"
    role="dialog"
    aria-labelledby="flexacademy-instructor-details-label-<?php echo $instructor['id']; ?>"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="flexacademy-instructor-details-label-<?php echo $instructor['id']; ?>">
                    <?php echo $instructor['name']; ?>
                </span>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _flexacademy_lang('close'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
                    <div class="tw-w-16 tw-h-16 tw-rounded-full tw-overflow-hidden tw-bg-gray-200">
                        <img class="tw-w-full tw-h-full tw-object-cover"
                            src="<?php echo flexacademy_instructor_image($instructor); ?>"
                            alt="<?php echo $instructor['name']; ?>">
                    </div>
                    <div>
                        <p class="tw-font-semibold tw-mb-1"><?php echo $instructor['name']; ?></p>
                        <?php if (!empty($instructor['job_title'])) { ?>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-0"><?php echo $instructor['job_title']; ?></p>
                        <?php } ?>
                        <?php if (!empty($instructor['email'])) { ?>
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-0"><?php echo $instructor['email']; ?></p>
                        <?php } ?>
                    </div>
                </div>
                <?php if (!empty($instructor['bio'])) { ?>
                    <div class="tw-space-y-1">
                        <span class="tw-text-sm tw-font-semibold tw-text-gray-700"><?php echo _flexacademy_lang('bio'); ?></span>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-0 tw-whitespace-pre-line">
                            <?php echo nl2br(htmlspecialchars($instructor['bio'])); ?>
                        </p>
                    </div>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <?php echo _flexacademy_lang('close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

