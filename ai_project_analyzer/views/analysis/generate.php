<div class="modal fade" id="generateModal" tabindex="-1" role="dialog" aria-labelledby="generateModal">
    <div class="modal-dialog" role="document">
        <?= form_open('', ['id' => 'generateAnalysisForm', 'enctype' => 'multipart/form-data']); ?>
        <input type="hidden" name="project_id" value="<?= $project->id ?>">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="ai-text-xl ai-font-semibold ai-text-left">
                    <?= _l('ai_project_analyzer_generate') ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        $templates = $this->db->get(AI_PROJECT_ANALYZER_PROMPT_TEMPLATES_TABLE)->result_array();
                        echo render_select('prompt_id', $templates, ['id', 'name'], 'ai_project_analyzer_template', include_blank: false);
                        $raw_tones = get_option('ai_project_analyzer_tone_list');
                        $tone_options = [];

                        foreach (explode(',', $raw_tones) as $tone) {
                            $tone = trim($tone);
                            if ($tone !== '') {
                                $tone_options[] = ['id' => $tone, 'name' => ucfirst($tone)];
                            }
                        }
                        echo render_select(
                            'tone',
                            $tone_options,
                            ['id', 'name'],
                            'ai_project_analyzer_tone',
                            'default',
                            include_blank: false
                        );
                        echo render_input(
                            'language',
                            'language',
                            'English',
                            'text',
                            [],
                            [],
                            'ai-mt-4'
                        );
                        ?>
                        <div class="form-group">
                            <label for="attachment"
                                class="control-label"><?php echo _l('ai_project_analyzer_attach_file'); ?></label>
                            <input type="file" id="attachment" extension="pdf,txt,docx,csv"
                                filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachment">
                            <div class="ai-text-sm ai-text-gray-500 ai-mt-2">
                                <?= _l('ai_project_analyzer_attach_file_help') ?>
                            </div>
                        </div>
                        <?= render_textarea('custom_instructions', 'ai_project_analyzer_custom_instructions', '', [], [], '', ''); ?>
                        <div class="ai-text-sm ai-text-gray-500">
                            <?= _l('ai_project_analyzer_custom_instructions_template_help') ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" autocomplete="off" class="btn btn-primary">
                    <?= _l('generate'); ?>
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>