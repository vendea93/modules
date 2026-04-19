<script>
    // Form validation
    appValidateForm('#generateAnalysisForm', {
        'prompt_id': {
            required: true,
        },
        'tone': 'required',
        'language': 'required',
    });
    appValidateForm('#regenerateAnalysisForm', {
        'tone': 'required',
        'language': 'required',
    });
    appValidateForm('#sendToEmailForm', {
        'send_to[]': 'required',
        'body': 'required',
    });

    $(document).ready(function () {
        // Handle generate analysis
        $('#generateAnalysisForm').on('submit', function (e) {
            e.preventDefault();

            const LANG = {
                generated_head: "<?= _l('ai_project_analyzer_analysis_generated_head') ?>",
                generated_description: "<?= _l('ai_project_analyzer_analysis_generated_description') ?>",
                processing_head: "<?= _l('ai_project_analyzer_analysis_processing_head') ?>",
                processing_description: "<?= _l('ai_project_analyzer_analysis_processing_description') ?>",
                failed_head: "<?= _l('ai_project_analyzer_analysis_failed_head') ?>",
                failed_description: "<?= _l('ai_project_analyzer_analysis_failed_description') ?>"
            };

            const form = $(this)[0];
            const formData = new FormData(form);

            $('#generateModal').modal('hide');
            $('#ai-loading-modal').modal('show');

            $('#modal-icon-wrapper').attr('class', 'ai-w-20 ai-h-20 ai-flex ai-items-center ai-justify-center ai-bg-gradient-to-br ai-from-blue-400 ai-to-blue-600 ai-rounded-full ai-transition-all ai-duration-500 ai-animate-wand-hit');
            $('#modal-icon').attr('class', 'fa-solid fa-wand-magic-sparkles ai-text-white ai-text-2xl');
            $('#modal-title').text('<?= _l('ai_project_analyzer_analysis_loading_head') ?>');
            $('#modal-message').text('<?= _l('ai_project_analyzer_analysis_loading_description') ?>');

            $.ajax({
                url: `<?= admin_url('ai_project_analyzer/generate') ?>`,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (res) {
                    const isProcessing = res.status === 'processing';

                    if (!isProcessing) {
                        $('#modal-icon-wrapper')
                            .removeClass('ai-animate-wand-hit ai-from-blue-400 ai-to-blue-600')
                            .addClass('ai-bg-gradient-to-br ai-from-green-400 ai-to-green-600 ai-animate-success-pulse');
                        $('#modal-icon').attr('class', 'fa-solid fa-check ai-text-white ai-text-4xl');
                        $('#modal-title').text(LANG.generated_head);
                        $('#modal-message').text(LANG.generated_description);
                    } else {
                        $('#modal-icon-wrapper')
                            .removeClass('ai-animate-wand-hit ai-from-blue-400 ai-to-blue-600')
                            .addClass('ai-bg-gradient-to-br ai-from-yellow-400 ai-to-yellow-600 ai-animate-success-pulse');
                        $('#modal-icon').attr('class', 'fa-regular fa-clock ai-text-white ai-text-3xl');
                        $('#modal-title').text(LANG.processing_head);
                        $('#modal-message').text(LANG.processing_description);
                    }
                    setTimeout(() => {
                        $('#ai-loading-modal').modal('hide');
                        location.reload();
                    }, 2000);

                },
                error: function (err) {
                    $('#modal-icon-wrapper').removeClass('ai-from-green-400 ai-to-green-600 ai-from-blue-400 ai-to-blue-600 ai-animate-wand-hit')
                        .addClass('ai-bg-gradient-to-br ai-from-red-400 ai-to-red-600 ai-animate-success-pulse');
                    $('#modal-icon').attr('class', 'fa-solid fa-x ai-text-white ai-text-2xl');
                    $('#modal-title').text(LANG.failed_head);
                    $('#modal-message').text(LANG.failed_description);

                    setTimeout(() => {
                        $('#ai-loading-modal').modal('hide');
                    }, 2000);
                }
            });
        });
    });
</script>