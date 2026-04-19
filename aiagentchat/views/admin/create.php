<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url(AIAGENTCHAT_MODULE_NAME, 'assets/css/create.css'); ?>"/>
<div id="wrapper" class="aiagentchat-create-modern">
    <div class="content">
        <div class="row">
            <?php
            $isEditing = isset($chat) && !empty($chat->id);
            $requestUrl = $isEditing ? 'aiagentchat/create/' . $chat->id : 'aiagentchat/create';
            echo form_open(admin_url($requestUrl), ['id' => 'aiagentchat-form']);
            ?>

            <div class="col-md-12">
                <?php if ($isEditing) { ?>
                    <div class="ai-hero">
                        <div class="ai-hero-left">
                            <div class="ai-hero-title">
                                <i class="fa fa-comments"></i>
                                <span><?php echo html_escape($title); ?></span>
                                <span class="ai-badge">#<?php echo (int)$chat->id; ?></span>
                            </div>
                            <div class="ai-hero-sub">
                                <?php echo _l('aiagentchat_builder_sub'); ?>
                            </div>
                        </div>
                        <div class="ai-hero-right">
                            <div class="checkbox checkbox-primary ai-toggle-enabled">
                                <input type="checkbox" name="is_enabled" id="is_enabled"
                                       value="1" <?php echo isset($chat) && (int)$chat->is_enabled === 1 ? 'checked' : ''; ?>>
                                <label for="is_enabled"><?php echo _l('aiagentchat_is_enabled'); ?></label>
                            </div>
                            <button type="button" class="btn btn-default btn-sm" id="reset_defaults_button">
                                <i class="fa fa-undo"></i> <?php echo _l('aiagentchat_reset_defaults'); ?>
                            </button>
                            <?php
                            if (staff_can('assign_chat', 'aiagentchat')) {
                                ?>
                                <a href="<?php echo admin_url('aiagentchat/assign/' . (int)$chat->id); ?>"
                                   class="btn btn-default btn-sm">
                                    <i class="fa fa-link"></i> <?php echo _l('aiagentchat_assignments'); ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>
                <div>
                    <div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="ai-card">
                                    <div class="ai-card-head">
                                        <div class="ai-card-title"><i
                                                    class="fa fa-sliders"></i> <?php echo _l('aiagentchat_section_basics'); ?>
                                        </div>
                                    </div>
                                    <div class="ai-card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php echo render_input(
                                                    'chat_name',
                                                    'aiagentchat_chat_name',
                                                    $chat->chat_name ?? '',
                                                    'text',
                                                    ['required' => true, 'autofocus' => true, 'id' => 'chat_name', 'placeholder' => _l('aiagentchat_chat_name_placeholder')]
                                                ); ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?php echo render_input(
                                                    'workflow_id',
                                                    'aiagentchat_workflow_id',
                                                    $chat->workflow_id ?? '',
                                                    'text',
                                                    ['id' => 'workflow_id', 'required' => true, 'placeholder' => _l('aiagentchat_workflow_id_placeholder')]
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card" data-accordion data-acc-id="theme">
                                    <div class="ai-card-head ai-acc-toggle" role="button" tabindex="0"
                                         aria-controls="acc-theme" aria-expanded="false">
                                        <div>
                                            <div class="ai-card-title"><i
                                                        class="fa fa-paint-brush"></i> <?php echo _l('aiagentchat_section_theme'); ?>
                                            </div>
                                            <div class="ai-card-sub"><?php echo _l('aiagentchat_section_theme_sub'); ?></div>
                                        </div>
                                        <i class="fa fa-chevron-right ai-caret" aria-hidden="true"></i>
                                    </div>
                                    <div class="ai-card-body" id="acc-theme">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <?php
                                                echo render_select(
                                                    'theme_colorScheme',
                                                    [
                                                        ['id' => 'light', 'name' => _l('aiagentchat_theme_light')],
                                                        ['id' => 'dark', 'name' => _l('aiagentchat_theme_dark')],
                                                        ['id' => 'auto', 'name' => _l('aiagentchat_theme_auto')],
                                                    ],
                                                    ['id', 'name'],
                                                    'aiagentchat_theme_color_scheme',
                                                    '',
                                                    ['id' => 'theme_colorScheme', 'class' => 'selectpicker', 'data-width' => '100%']
                                                );
                                                ?>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group" app-field-wrapper="theme_color_accent_primary">
                                                    <label for="theme_color_accent_primary" class="control-label">
                                                        <?php echo _l('aiagentchat_theme_accent_color'); ?>
                                                    </label>
                                                    <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                        <input type="text" name="theme_color_accent_primary"
                                                               id="theme_color_accent_primary" class="form-control"
                                                               value="#D7263D" placeholder="#D7263D">
                                                        <span class="input-group-addon"><i></i></span>
                                                    </div>
                                                    <p class="ai-hint"><?php echo _l('aiagentchat_hex_hint'); ?></p>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <?php
                                                echo render_select(
                                                    'theme_radius',
                                                    [
                                                        ['id' => 'square', 'name' => _l('aiagentchat_radius_square')],
                                                        ['id' => 'round', 'name' => _l('aiagentchat_radius_round')],
                                                        ['id' => 'pill', 'name' => _l('aiagentchat_radius_pill')],
                                                    ],
                                                    ['id', 'name'],
                                                    'aiagentchat_theme_radius',
                                                    'round',
                                                    ['id' => 'theme_radius', 'class' => 'selectpicker', 'data-width' => '100%']
                                                );
                                                ?>
                                            </div>

                                            <div class="col-md-3">
                                                <?php
                                                echo render_select(
                                                    'theme_density',
                                                    [
                                                        ['id' => 'compact', 'name' => _l('aiagentchat_density_compact')],
                                                        ['id' => 'normal', 'name' => _l('aiagentchat_density_normal')],
                                                        ['id' => 'comfortable', 'name' => _l('aiagentchat_density_comfortable')],
                                                    ],
                                                    ['id', 'name'],
                                                    'aiagentchat_theme_density',
                                                    'normal',
                                                    ['id' => 'theme_density', 'class' => 'selectpicker', 'data-width' => '100%']
                                                );
                                                ?>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php echo render_input(
                                                    'theme_typography_fontFamily',
                                                    'aiagentchat_typography_font',
                                                    'Open Sans, sans-serif',
                                                    'text',
                                                    ['id' => 'theme_typography_fontFamily', 'placeholder' => 'Open Sans, sans-serif']
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card" data-accordion data-acc-id="header">
                                    <div class="ai-card-head ai-acc-toggle" role="button" tabindex="0"
                                         aria-controls="acc-header" aria-expanded="false">
                                        <div>
                                            <div class="ai-card-title"><i
                                                        class="fa fa-window-maximize"></i> <?php echo _l('aiagentchat_section_header'); ?>
                                            </div>
                                            <div class="ai-card-sub"><?php echo _l('aiagentchat_section_header_sub'); ?></div>
                                        </div>
                                        <i class="fa fa-chevron-right ai-caret" aria-hidden="true"></i>
                                    </div>
                                    <div class="ai-card-body" id="acc-header">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="checkbox checkbox-primary mtop25">
                                                    <input type="checkbox" id="header_enabled">
                                                    <label for="header_enabled"><?php echo _l('aiagentchat_header_enabled'); ?></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <?php echo render_input(
                                                    'header_leftAction_icon',
                                                    'aiagentchat_header_left_icon',
                                                    'fa fa-cog',
                                                    'text',
                                                    ['id' => 'header_leftAction_icon', 'placeholder' => 'fa fa-cog']
                                                ); ?>
                                                <p class="ai-hint"><?php echo _l('aiagentchat_fontawesome_hint'); ?></p>
                                            </div>
                                            <div class="col-md-5">
                                                <?php echo render_input(
                                                    'header_leftAction_actionId',
                                                    'aiagentchat_header_left_action_id',
                                                    'open-settings',
                                                    'text',
                                                    ['id' => 'header_leftAction_actionId']
                                                ); ?>
                                                <p class="ai-hint"><?php echo _l('aiagentchat_hint_action_id'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card" data-accordion data-acc-id="composer">
                                    <div class="ai-card-head ai-acc-toggle" role="button" tabindex="0"
                                         aria-controls="acc-composer" aria-expanded="false">
                                        <div>
                                            <div class="ai-card-title"><i
                                                        class="fa fa-keyboard"></i> <?php echo _l('aiagentchat_section_composer'); ?>
                                            </div>
                                            <div class="ai-card-sub"><?php echo _l('aiagentchat_section_composer_sub'); ?></div>
                                        </div>
                                        <i class="fa fa-chevron-right ai-caret" aria-hidden="true"></i>
                                    </div>
                                    <div class="ai-card-body" id="acc-composer">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <?php echo render_input(
                                                    'composer_placeholder',
                                                    'aiagentchat_composer_placeholder',
                                                    'Type your product feedback…',
                                                    'text',
                                                    ['id' => 'composer_placeholder']
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card" data-accordion data-acc-id="start">
                                    <div class="ai-card-head ai-acc-toggle" role="button" tabindex="0"
                                         aria-controls="acc-start" aria-expanded="false">
                                        <div>
                                            <div class="ai-card-title"><i
                                                        class="fa fa-play-circle"></i> <?php echo _l('aiagentchat_section_start'); ?>
                                            </div>
                                            <div class="ai-card-sub"><?php echo _l('aiagentchat_section_start_sub'); ?></div>
                                        </div>
                                        <i class="fa fa-chevron-right ai-caret" aria-hidden="true"></i>
                                    </div>
                                    <div class="ai-card-body" id="acc-start">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <?php echo render_input(
                                                    'start_greeting',
                                                    'aiagentchat_start_greeting',
                                                    _l('aiagentchat_default_greeting'),
                                                    'text',
                                                    ['id' => 'start_greeting']
                                                ); ?>
                                            </div>
                                        </div>

                                        <div class="row mtop10">
                                            <div class="col-md-12">
                                                <h5 class="bold"><?php echo _l('aiagentchat_start_prompts'); ?></h5>
                                                <div id="promptsRepeaterContainer"></div>
                                                <button type="button" class="btn btn-default" id="addPromptRowButton">
                                                    <i class="fa fa-plus"></i> <?php echo _l('aiagentchat_add_prompt'); ?>
                                                </button>
                                                <p class="ai-hint mtop10"><?php echo _l('aiagentchat_prompts_hint'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card" data-accordion data-acc-id="behavior">
                                    <div class="ai-card-head ai-acc-toggle" role="button" tabindex="0"
                                         aria-controls="acc-behavior" aria-expanded="false">
                                        <div>
                                            <div class="ai-card-title"><i
                                                        class="fa fa-cogs"></i> <?php echo _l('aiagentchat_section_behavior'); ?>
                                            </div>
                                            <div class="ai-card-sub"><?php echo _l('aiagentchat_section_behavior_sub'); ?></div>
                                        </div>
                                        <i class="fa fa-chevron-right ai-caret" aria-hidden="true"></i>
                                    </div>
                                    <div class="ai-card-body" id="acc-behavior">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="checkbox checkbox-primary mtop25">
                                                    <input type="checkbox" id="history_enabled">
                                                    <label for="history_enabled"><?php echo _l('aiagentchat_history_enabled'); ?></label>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group" app-field-wrapper="locale">
                                                    <label for="locale"
                                                           class="control-label"><?php echo _l('aiagentchat_locale'); ?></label>
                                                    <input type="text" name="locale" id="locale" class="form-control"
                                                           list="locale_suggestions" placeholder="en-US" value="en-US">
                                                    <datalist id="locale_suggestions">
                                                        <option value="en-US"></option>
                                                        <option value="en-GB"></option>
                                                        <option value="de-DE"></option>
                                                        <option value="fr-FR"></option>
                                                        <option value="es-ES"></option>
                                                        <option value="it-IT"></option>
                                                        <option value="pt-PT"></option>
                                                        <option value="pt-BR"></option>
                                                        <option value="nl-NL"></option>
                                                        <option value="sv-SE"></option>
                                                        <option value="no-NO"></option>
                                                        <option value="da-DK"></option>
                                                        <option value="fi-FI"></option>
                                                        <option value="pl-PL"></option>
                                                        <option value="tr-TR"></option>
                                                        <option value="ru-RU"></option>
                                                        <option value="ar-SA"></option>
                                                        <option value="he-IL"></option>
                                                        <option value="hi-IN"></option>
                                                        <option value="zh-CN"></option>
                                                        <option value="zh-TW"></option>
                                                        <option value="ja-JP"></option>
                                                        <option value="ko-KR"></option>
                                                        <option value="sq-AL"></option>
                                                    </datalist>
                                                    <p class="ai-hint"><?php echo _l('aiagentchat_locale_hint'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="settings_json" id="settings_json"
                                       value="<?php echo html_escape($chat->settings_json ?? ''); ?>">

                            </div>

                            <div class="col-md-5">
                                <div class="ai-preview-card">
                                    <div class="ai-preview-head">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Preview Mode">
                                            <button type="button" class="btn btn-default active"
                                                    id="preview_mode_start_button">
                                                <i class="fa fa-play"></i> <?php echo _l('aiagentchat_preview_start_screen'); ?>
                                            </button>
                                            <button type="button" class="btn btn-default" id="preview_mode_chat_button">
                                                <i class="fa fa-commenting"></i> <?php echo _l('aiagentchat_preview_active_chat'); ?>
                                            </button>
                                        </div>
                                        <span class="ai-preview-sub" id="preview_meta_badge">
                      <i class="fa fa-history"></i> <?php echo _l('aiagentchat_meta_history_off'); ?> · <?php echo _l('aiagentchat_meta_locale'); ?>: en-US
                    </span>
                                    </div>

                                    <div class="chatkit-preview-root" id="chatkit_preview_root" data-theme="light">
                                        <div class="chatkit-header" id="chatkit_header">
                                            <button type="button" class="chatkit-header-action"
                                                    id="chatkit_header_action">
                                                <i class="fa fa-cog" id="chatkit_header_action_icon"></i>
                                            </button>
                                            <div class="chatkit-header-title">
                                                <span id="chatkit_header_title_text"><?php echo html_escape($chat->chat_name ?? _l('aiagentchat_default_header_title')); ?></span>
                                            </div>
                                        </div>

                                        <div class="chatkit-body">
                                            <div class="chatkit-start-screen" id="chatkit_start_screen">
                                                <div class="chatkit-greeting"
                                                     id="chatkit_greeting"><?php echo _l('aiagentchat_default_greeting'); ?></div>
                                                <div class="chatkit-prompts" id="chatkit_prompts"></div>
                                            </div>

                                            <div class="chatkit-chat-screen" id="chatkit_chat_screen"
                                                 style="display:none;">
                                                <div class="chatkit-message chatkit-message-user">
                                                    <div class="chatkit-bubble"><?php echo _l('aiagentchat_preview_user_sample'); ?></div>
                                                </div>
                                                <div class="chatkit-message chatkit-message-bot">
                                                    <div class="chatkit-bubble"><?php echo _l('aiagentchat_preview_bot_sample'); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="chatkit-composer">
                                            <input type="text" class="chatkit-input" id="chatkit_input"
                                                   placeholder="<?php echo _l('aiagentchat_default_placeholder'); ?>">
                                            <button class="chatkit-send" type="button"><i class="fa fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="ai-card ai-card-glass">
                                    <div class="ai-card-head">
                                        <div class="ai-card-title"><i
                                                    class="fa fa-info-circle"></i> <?php echo _l('aiagentchat_tips_title'); ?>
                                        </div>
                                    </div>
                                    <ul class="ai-list">
                                        <li><?php echo _l('aiagentchat_tip_brand'); ?></li>
                                        <li><?php echo _l('aiagentchat_tip_prompts'); ?></li>
                                        <li><?php echo _l('aiagentchat_tip_header'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('aiagentchat'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        function byId(id) {
            return document.getElementById(id);
        }

        function getVal(id) {
            var el = byId(id);
            return el ? (el.value || '').trim() : '';
        }

        function setVal(id, val) {
            var el = byId(id);
            if (!el) return;
            el.value = val;
            if (window.jQuery) {
                window.jQuery(el).trigger('change');
            }
        }

        function getChecked(id) {
            var el = byId(id);
            return !!(el && el.checked);
        }

        function setChecked(id, val) {
            var el = byId(id);
            if (el) {
                el.checked = !!val;
            }
        }

        function parseJsonSafe(str) {
            try {
                return str ? JSON.parse(str) : {};
            } catch (e) {
                return {};
            }
        }

        function defaultSettings() {
            return {
                theme: {
                    colorScheme: 'light',
                    color: {accent: {primary: '#D7263D', level: 2}},
                    radius: 'round',
                    density: 'normal',
                    typography: {fontFamily: 'Open Sans, sans-serif'},
                },
                header: {enabled: false, leftAction: {icon: 'fa fa-cog', id: 'open-settings'}},
                composer: {placeholder: '<?php echo _l('aiagentchat_default_placeholder'); ?>'},
                startScreen: {
                    greeting: '<?php echo _l('aiagentchat_default_greeting'); ?>',
                    prompts: [{
                        name: 'Account access',
                        prompt: 'Login problem: email, company, what happens on sign-in, any error text.',
                        icon: 'fa fa-lock'
                    }]
                },
                history: {enabled: false},
                locale: 'en-US'
            };
        }

        var promptsContainer = byId('promptsRepeaterContainer');

        function createPromptRow(initial) {
            var defaults = initial || {
                name: 'Bug',
                prompt: '<?php echo _l('aiagentchat_default_prompt_bug'); ?>',
                icon: 'fa fa-bolt'
            };
            var row = document.createElement('div');
            row.className = 'prompt-repeater-row';

            row.innerHTML =
                '<button type="button" class="btn btn-danger btn-xs remove-prompt-row-button" title="<?php echo _l('aiagentchat_remove'); ?>">&times;</button>' +
            <?php
            $inputName = str_replace("\n", "", render_input("_prompt_name[]", "aiagentchat_prompt_name", "", "text", ['placeholder' => _l('aiagentchat_prompt_name_placeholder')]));
            $inputPrompt = str_replace("\n", "", render_input("_prompt_prompt[]", "aiagentchat_prompt_prompt", "", "text", ['placeholder' => _l('aiagentchat_prompt_prompt_placeholder')]));
            $inputIcon = str_replace("\n", "", render_input("_prompt_icon[]", "aiagentchat_prompt_icon", "", "text", ['placeholder' => _l('aiagentchat_prompt_icon_placeholder')]));
            echo json_encode($inputName . $inputPrompt . $inputIcon);
            ?>;

            promptsContainer.appendChild(row);
            var inputs = row.querySelectorAll('input');
            inputs[0].value = defaults.name || '';
            inputs[1].value = defaults.prompt || '';
            inputs[2].value = defaults.icon || 'fa fa-bolt';

            row.querySelector('.remove-prompt-row-button').addEventListener('click', function () {
                row.parentNode && row.parentNode.removeChild(row);
                rebuildSettingsAndPreview();
            });

            Array.prototype.forEach.call(inputs, function (inp) {
                inp.addEventListener('input', rebuildSettingsAndPreview);
                inp.addEventListener('change', rebuildSettingsAndPreview);
                inp.addEventListener('keyup', rebuildSettingsAndPreview);
            });
        }

        byId('addPromptRowButton').addEventListener('click', function () {
            createPromptRow();
            rebuildSettingsAndPreview();
        });

        function collectPrompts() {
            var prompts = [];
            var rows = promptsContainer.querySelectorAll('.prompt-repeater-row');
            Array.prototype.forEach.call(rows, function (row) {
                var fields = row.querySelectorAll('input');
                var name = (fields[0].value || '').trim();
                var prompt = (fields[1].value || '').trim();
                var icon = (fields[2].value || '').trim();
                if (name || prompt || icon) {
                    prompts.push({name: name, prompt: prompt, icon: icon});
                }
            });
            return prompts;
        }

        function getSettingsFromForm() {
            return {
                theme: {
                    colorScheme: getVal('theme_colorScheme') || 'light',
                    color: {accent: {primary: getVal('theme_color_accent_primary') || '#D7263D', level: 2}},
                    radius: getVal('theme_radius') || 'round',
                    density: getVal('theme_density') || 'normal',
                    typography: {fontFamily: getVal('theme_typography_fontFamily') || 'Open Sans, sans-serif'},
                },
                header: {
                    enabled: getChecked('header_enabled'),
                    leftAction: {
                        icon: getVal('header_leftAction_icon') || 'fa fa-cog',
                        id: getVal('header_leftAction_actionId') || 'open-settings'
                    }
                },
                composer: {placeholder: getVal('composer_placeholder') || '<?php echo _l('aiagentchat_default_placeholder'); ?>'},
                startScreen: {
                    greeting: getVal('start_greeting') || '<?php echo _l('aiagentchat_default_greeting'); ?>',
                    prompts: collectPrompts()
                },
                history: {enabled: getChecked('history_enabled')},
                locale: getVal('locale') || 'en-US'
            };
        }

        function setSettingsToHidden() {
            byId('settings_json').value = JSON.stringify(getSettingsFromForm());
        }

        function applySettingsToPreview(settings) {
            var root = byId('chatkit_preview_root');

            var scheme = settings.theme.colorScheme || 'light';
            root.setAttribute('data-theme', scheme === 'auto' ? 'light' : scheme);

            var accent = (settings.theme.color && settings.theme.color.accent && settings.theme.color.accent.primary) || '#D7263D';
            root.style.setProperty('--ck-accent', accent);

            root.setAttribute('data-radius', settings.theme.radius || 'round');

            root.setAttribute('data-density', settings.theme.density || 'normal');

            var fontFamily = (settings.theme.typography && settings.theme.typography.fontFamily) || 'Open Sans, sans-serif';
            root.style.setProperty('--ck-font', fontFamily);

            var header = byId('chatkit_header');
            var headerActionIcon = byId('chatkit_header_action_icon');
            header.style.display = settings.header.enabled ? 'flex' : 'none';
            headerActionIcon.className = (settings.header.leftAction && settings.header.leftAction.icon) ? settings.header.leftAction.icon : 'fa fa-cog';

            byId('chatkit_header_title_text').textContent = getVal('chat_name') || '<?php echo _l('aiagentchat_default_header_title'); ?>';

            byId('chatkit_greeting').textContent = (settings.startScreen && settings.startScreen.greeting) || '<?php echo _l('aiagentchat_default_greeting'); ?>';
            var promptsWrap = byId('chatkit_prompts');
            promptsWrap.innerHTML = '';
            (settings.startScreen && Array.isArray(settings.startScreen.prompts) ? settings.startScreen.prompts : []).forEach(function (p) {
                var chip = document.createElement('span');
                chip.className = 'chatkit-prompt-chip';
                var iconEl = document.createElement('i');
                iconEl.className = (p.icon || 'fa fa-bolt');
                chip.appendChild(iconEl);
                chip.appendChild(document.createTextNode(' ' + (p.name || '<?php echo _l('aiagentchat_prompt_default_name'); ?>')));
                promptsWrap.appendChild(chip);
            });

            byId('chatkit_input').placeholder = settings.composer.placeholder || '<?php echo _l('aiagentchat_default_placeholder'); ?>';

            var meta = (settings.history.enabled ? '<?php echo _l('aiagentchat_meta_history_on'); ?>' : '<?php echo _l('aiagentchat_meta_history_off'); ?>')
                + ' · <?php echo _l('aiagentchat_meta_locale'); ?>: ' + (settings.locale || 'en-US');
            byId('preview_meta_badge').textContent = meta;
        }

        function rebuildSettingsAndPreview() {
            var settings = getSettingsFromForm();
            applySettingsToPreview(settings);
            setSettingsToHidden();
        }

        (function hydrateFromExisting() {
            var settings = parseJsonSafe(byId('settings_json').value || '');
            if (!settings || Object.keys(settings).length === 0) {
                settings = defaultSettings();
            }

            setVal('theme_colorScheme', settings.theme && settings.theme.colorScheme || 'light');
            setVal('theme_color_accent_primary', settings.theme && settings.theme.color && settings.theme.color.accent && settings.theme.color.accent.primary || '#D7263D');
            setVal('theme_radius', settings.theme && settings.theme.radius || 'round');
            setVal('theme_density', settings.theme && settings.theme.density || 'normal');
            setVal('theme_typography_fontFamily', settings.theme && settings.theme.typography && settings.theme.typography.fontFamily || 'Open Sans, sans-serif');

            setChecked('header_enabled', settings.header && !!settings.header.enabled);
            setVal('header_leftAction_icon', settings.header && settings.header.leftAction && settings.header.leftAction.icon || 'fa fa-cog');
            setVal('header_leftAction_actionId', settings.header && settings.header.leftAction && (settings.header.leftAction.id || settings.header.leftAction.actionId) || 'open-settings');

            setVal('composer_placeholder', settings.composer && settings.composer.placeholder || '<?php echo _l('aiagentchat_default_placeholder'); ?>');

            setVal('start_greeting', settings.startScreen && settings.startScreen.greeting || '<?php echo _l('aiagentchat_default_greeting'); ?>');

            var prompts = (settings.startScreen && Array.isArray(settings.startScreen.prompts)) ? settings.startScreen.prompts : [{
                name: 'Bug',
                prompt: '<?php echo _l('aiagentchat_default_prompt_bug'); ?>',
                icon: 'fa fa-bolt'
            }];
            promptsContainer.innerHTML = '';
            prompts.forEach(function (p) {
                createPromptRow(p);
            });

            setChecked('history_enabled', settings.history && !!settings.history.enabled);
            setVal('locale', settings.locale || 'en-US');

            applySettingsToPreview(getSettingsFromForm());
        })();

        var startButton = byId('preview_mode_start_button');
        var chatButton = byId('preview_mode_chat_button');
        startButton.addEventListener('click', function () {
            startButton.classList.add('active');
            chatButton.classList.remove('active');
            byId('chatkit_start_screen').style.display = 'block';
            byId('chatkit_chat_screen').style.display = 'none';
        });
        chatButton.addEventListener('click', function () {
            chatButton.classList.add('active');
            startButton.classList.remove('active');
            byId('chatkit_start_screen').style.display = 'none';
            byId('chatkit_chat_screen').style.display = 'block';
        });

        [
            'chat_name', 'workflow_id', 'is_enabled',
            'theme_colorScheme', 'theme_color_accent_primary', 'theme_radius', 'theme_density', 'theme_typography_fontFamily',
            'header_enabled', 'header_leftAction_icon', 'header_leftAction_actionId',
            'composer_placeholder', 'start_greeting', 'history_enabled', 'locale'
        ].forEach(function (id) {
            var el = byId(id);
            if (!el) return;
            ['input', 'change', 'keyup'].forEach(function (evt) {
                el.addEventListener(evt, rebuildSettingsAndPreview);
            });
        });

        if (window.jQuery) {
            window.jQuery(function () {
                var $accentInput = window.jQuery('#theme_color_accent_primary');
                var $pickerWrap = window.jQuery('[app-field-wrapper="theme_color_accent_primary"] .colorpicker-input');

                var rafId = null, lastVal = $accentInput.val();

                function tick() {
                    var cur = $accentInput.val();
                    if (cur !== lastVal) {
                        lastVal = cur;
                        rebuildSettingsAndPreview();
                    }
                    rafId = requestAnimationFrame(tick);
                }

                function startRaf() {
                    if (!rafId) {
                        lastVal = $accentInput.val();
                        rafId = requestAnimationFrame(tick);
                    }
                }

                function stopRaf() {
                    if (rafId) {
                        cancelAnimationFrame(rafId);
                        rafId = null;
                    }
                }

                $accentInput.on('input change keyup', function () {
                    var v = $accentInput.val().trim();
                    if (/^[0-9a-fA-F]{6}$/.test(v)) {
                        $accentInput.val('#' + v);
                    }
                    rebuildSettingsAndPreview();
                });

                $pickerWrap.on('colorpickerChange changeColor colorpicker:change colorpickerShow colorpickerHide', function (e) {
                    var color = (e && e.color && typeof e.color.toString === 'function') ? e.color.toString() : $accentInput.val();
                    if (color) {
                        $accentInput.val(color);
                    }
                    rebuildSettingsAndPreview();
                });

                $pickerWrap.on('mousedown touchstart', startRaf);
                window.jQuery(document).on('mouseup touchend', stopRaf);

                if (window.jQuery.fn.selectpicker) {
                    window.jQuery('.selectpicker').selectpicker().on('changed.bs.select', rebuildSettingsAndPreview);
                }
            });
        }

        byId('reset_defaults_button') && byId('reset_defaults_button').addEventListener('click', function () {
            var s = defaultSettings();

            setVal('theme_colorScheme', s.theme.colorScheme);
            setVal('theme_color_accent_primary', s.theme.color.accent.primary);
            setVal('theme_radius', s.theme.radius);
            setVal('theme_density', s.theme.density);
            setVal('theme_typography_fontFamily', s.theme.typography.fontFamily);

            setChecked('header_enabled', s.header.enabled);
            setVal('header_leftAction_icon', s.header.leftAction.icon);
            setVal('header_leftAction_actionId', s.header.leftAction.id);

            setVal('composer_placeholder', s.composer.placeholder);
            setVal('start_greeting', s.startScreen.greeting);

            promptsContainer.innerHTML = '';
            s.startScreen.prompts.forEach(function (p) {
                createPromptRow(p);
            });

            setChecked('history_enabled', s.history.enabled);
            setVal('locale', s.locale);

            rebuildSettingsAndPreview();
        });

        byId('aiagentchat-form').addEventListener('submit', function () {
            setSettingsToHidden();
        });

        function initAccordion() {
            var SAVE_KEY = 'aiagentchat_acc_v1';
            var cards = document.querySelectorAll('.ai-card[data-accordion]');
            var state = {};
            try {
                state = JSON.parse(localStorage.getItem(SAVE_KEY) || '{}');
            } catch (_) {
                state = {};
            }

            function setOpen(card, open) {
                card.classList.toggle('open', !!open);
                var toggle = card.querySelector('.ai-acc-toggle');
                if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function persist() {
                var obj = {};
                cards.forEach(function (c) {
                    var id = c.getAttribute('data-acc-id') || '';
                    if (id) obj[id] = c.classList.contains('open');
                });
                try {
                    localStorage.setItem(SAVE_KEY, JSON.stringify(obj));
                } catch (_) {
                }
            }

            function onToggle(e) {
                if (e && e.target && (e.target.closest('a,button,input,select,label'))) return;
                var card = this.closest('.ai-card[data-accordion]');
                var willOpen = !card.classList.contains('open');
                setOpen(card, willOpen);
                persist();

                if (willOpen && window.jQuery && window.jQuery.fn.selectpicker) {
                    setTimeout(function () {
                        window.jQuery(card).find('.selectpicker').selectpicker('refresh');
                    }, 10);
                }
            }

            function onKey(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            }

            cards.forEach(function (card) {
                var id = card.getAttribute('data-acc-id') || '';
                setOpen(card, !!state[id]);
                var head = card.querySelector('.ai-acc-toggle');
                if (head) {
                    head.addEventListener('click', onToggle);
                    head.addEventListener('keydown', onKey);
                }
            });

            if (!Object.keys(state).length) {
                persist();
            }
        }

        initAccordion();

    })();
</script>

<?php init_tail(); ?>
