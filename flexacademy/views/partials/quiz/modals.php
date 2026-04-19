<div class="modal fade" id="flexacademy-quiz-results-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('add-quiz'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="flexacademy-quiz-results-container">
                   <?php //echo $this->load->view('partials/quiz/quiz-results', [], true); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
            </div>
        </div>
    </div>
</div>