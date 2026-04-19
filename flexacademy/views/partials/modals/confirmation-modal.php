<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Confirmation Modal -->
<div class="modal fade" id="flexacademy-confirmation-modal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fa fa-question-circle mr-2"></i>
                    <span id="flexacademy-confirmation-modal-title"><?php echo _flexacademy_lang('confirm-action'); ?></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="flexacademy-confirmation-modal-message" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary flexacademy-btn-secondary" data-dismiss="modal">
                    <?php echo _flexacademy_lang('cancel'); ?>
                </button>
                <button type="button" class="btn btn-primary flexacademy-btn-primary" id="flexacademy-confirmation-modal-confirm">
                    <?php echo _flexacademy_lang('confirm'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

