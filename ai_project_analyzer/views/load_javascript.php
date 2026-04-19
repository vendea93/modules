<script>
    // Form validation
    appValidateForm('form', {
        'settings[ai_project_analyzer_api_provider]': {
            required: true,
        },
        'settings[ai_project_analyzer_api_provider_model]': {
            required: true,
        },
        'settings[ai_project_analyzer_api_key]': {
            required: true,
        },
        'settings[ai_project_analyzer_pagination_max]': {
            required: true,
            min: 1
        },
        'settings[ai_project_analyzer_data_limit]': {
            required: true,
            min: 1
        },
        'settings[ai_project_analyzer_tone_list]': {
            required: true,
        },
    });

    const modelOptions = {
        openai: [
            { id: 'gpt-4.1', name: 'GPT-4.1' },
            { id: 'gpt-4.1-mini', name: 'GPT-4.1-mini' },
            { id: 'gpt-4.5-preview', name: 'GPT-4.5-preview' },
            { id: 'gpt-4o', name: 'GPT-4o' },
            { id: 'gpt-4o-mini', name: 'GPT-4o-mini' },
            { id: 'o1', name: 'GPT-o1' },
            { id: 'o1-mini', name: 'GPT-o1-mini' },
            { id: 'o3', name: 'GPT-o3' },
            { id: 'o3-mini', name: 'GPT-o3-mini' },
            { id: 'o4-mini', name: 'GPT-o4-mini' },
        ],
        deepseek: [
            { id: 'deepseek-chat', name: 'DeepSeek Chat (DeepSeek-V3)' },
            { id: 'deepseek-reasoner', name: 'DeepSeek Reasoner (DeepSeek-R1)' },
        ],
        gemini: [
            { id: 'gemini-2.5-flash-preview-05-20', name: 'Gemini 2.5 Flash' },
            { id: 'gemini-2.5-pro-preview-06-05', name: 'Gemini 2.5 Pro' },
            { id: 'gemini-2.0-flash', name: 'Gemini 2.0 Flash' },
            { id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash' },
            { id: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro' },
        ],
        claude: [
            { id: 'claude-sonnet-4-20250514', name: 'Claude 4 Sonnet' },
            { id: 'claude-opus-4-20250514', name: 'Claude 4 Opus' },
            { id: 'claude-3-7-sonnet-20250219', name: 'Claude 3.7 Sonnet' },
            { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
        ],
    };

    const savedModel = "<?php echo get_option('ai_project_analyzer_api_provider_model'); ?>";
    const savedProvider = "<?php echo get_option('ai_project_analyzer_api_provider'); ?>";

    function populateModelSelect(provider, selectedValue = null) {
        const $modelSelect = $('select[name="settings[ai_project_analyzer_api_provider_model]"]');
        $modelSelect.empty();

        if (modelOptions[provider]) {
            modelOptions[provider].forEach(option => {
                const isSelected = selectedValue === option.id ? 'selected' : '';
                $modelSelect.append(`<option value="${option.id}" ${isSelected}>${option.name}</option>`);
            });
        }

        $modelSelect.selectpicker('refresh');
    }

    $(function () {
        populateModelSelect(savedProvider, savedModel);
        $('select[name="settings[ai_project_analyzer_api_provider]"]').on('changed.bs.select', function () {
            var selectedProvider = $(this).val();
            populateModelSelect(selectedProvider);
        });
    });
</script>