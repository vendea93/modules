<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                    <?= $template->name ?? _l('ai_project_analyzer_templates_add'); ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open($this->uri->uri_string()); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?= render_input('name', 'ai_project_analyzer_name', $template->name ?? '', 'text', []); ?>
                                <?= render_textarea('prompt', 'ai_project_analyzer_body', $template->prompt ?? '', ['rows' => 15]); ?>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
                                </div>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 lg:tw-sticky lg:tw-top-2">
                <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                    <?= _l('ai_project_analyzer_variables'); ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row available_merge_fields_container">
                            <div class="col-md-12 merge_fields_col">
                                <p>
                                    <?= _l('project_name'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_name}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_customer'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_customer}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_status'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_status}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_description'); ?>
                                    <a href="#"
                                        class="pull-right project_ai_analyzer_variable">{project_description}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_start_date'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_start_date}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_deadline'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_deadline}</a>
                                </p>
                                <hr />
                                <p>
                                    <?= _l('project') . ' ' . _l('project_members'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_members}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('tasks'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_tasks}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_milestones'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_milestones}</a>
                                </p>
                                <p>
                                    <?= _l('project') . ' ' . _l('project_activity'); ?>
                                    <a href="#" class="pull-right project_ai_analyzer_variable">{project_activity}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        appValidateForm($('form'), {
            name: 'required',
            prompt: 'required',
        });

        // Add prompt variable
        $('form').on('focus', 'textarea', function () {
            let activeTextarea = this;
            $('.project_ai_analyzer_variable').off('click').on('click', function (e) {
                e.preventDefault();
                let variableText = $(this).text().trim();

                activeTextarea.focus();

                if (document.queryCommandSupported('insertText')) {
                    document.execCommand('insertText', false, variableText);
                } else {
                    let start = activeTextarea.selectionStart;
                    let end = activeTextarea.selectionEnd;
                    let currentValue = activeTextarea.value;

                    activeTextarea.value = currentValue.substring(0, start) + variableText + currentValue.substring(end);
                    activeTextarea.selectionStart = activeTextarea.selectionEnd = start + variableText.length;
                }
            });
        });
    });
</script>
</body>

</html>