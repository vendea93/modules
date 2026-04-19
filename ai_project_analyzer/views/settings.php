<?php defined('BASEPATH') or exit('No direct script access allowed');
echo render_select('settings[ai_project_analyzer_api_provider]', [
    ['id' => 'openai', 'name' => 'OpenAI'],
    ['id' => 'deepseek', 'name' => 'DeepSeek'],
    ['id' => 'gemini', 'name' => 'Gemini'],
    ['id' => 'claude', 'name' => 'Claude'],
], ['id', 'name'], 'ai_project_analyzer_api_provider', get_option('ai_project_analyzer_api_provider'), include_blank: false);

echo render_select(
    'settings[ai_project_analyzer_api_provider_model]',
    [],
    ['id', 'name'],
    'ai_project_analyzer_api_provider_model',
    get_option('ai_project_analyzer_api_provider_model'),
);
echo render_input(
    'settings[ai_project_analyzer_api_key]',
    'ai_project_analyzer_api_key',
    get_option('ai_project_analyzer_api_key'),
    'text',
    [],
    [],
    'tw-mt-4'
);
echo "
<hr />";

render_yes_no_option('ai_project_analyzer_use_cron', 'ai_project_analyzer_use_cron');
echo '<div class="tw-mb-4 tw-text-sm tw-text-neutral-500">' . _l('ai_project_analyzer_use_cron_helper') . '</div>';
echo render_input(
    'settings[ai_project_analyzer_pagination_max]',
    'ai_project_analyzer_pagination_max',
    get_option('ai_project_analyzer_pagination_max'),
    'number',
    ['min' => 1],
    [],
    'tw-mt-4'
);
echo render_input(
    'settings[ai_project_analyzer_data_limit]',
    'ai_project_analyzer_data_limit',
    get_option('ai_project_analyzer_data_limit'),
    'number',
    ['min' => 1],
    [],
    'tw-mt-4 tw-mb-0'
); ?>
<div class="tw-mt-2 tw-text-sm tw-text-neutral-500"><?= _l('ai_project_analyzer_data_limit_helper') ?></div>
<?= render_textarea(
    'settings[ai_project_analyzer_tone_list]',
    'ai_project_analyzer_tone_list',
    get_option('ai_project_analyzer_tone_list'),
    [],
    [],
    'tw-mt-4 tw-mb-0'
); ?>
<div class="tw-mt-2 tw-text-sm tw-text-neutral-500"><?= _l('ai_project_analyzer_tone_list_help') ?></div>
<?= render_textarea('settings[ai_project_analyzer_custom_instructions]', 'ai_project_analyzer_custom_instructions', get_option('ai_project_analyzer_custom_instructions'), [], [], 'tw-mt-4', ''); ?>
<div class="tw-text-sm tw-text-neutral-500"><?= _l('ai_project_analyzer_custom_instructions_help') ?></div>