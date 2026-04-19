<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url(AIAGENTCHAT_MODULE_NAME, 'assets/css/settings.css'); ?>"/>
<div id="wrapper" class="aiagentchat-modern">
    <div class="content">
        <div class="row">
            <?php
            $postedSettings = isset($posted_settings) && is_array($posted_settings) ? $posted_settings : [];

            $optionOpenAiApiKey = isset($postedSettings['aiagentchat_openai_api_key'])
                ? $postedSettings['aiagentchat_openai_api_key']
                : get_option('aiagentchat_openai_api_key');

            $optionIconAdmin = isset($postedSettings['aiagentchat_bubble_chat_icon_admin'])
                ? $postedSettings['aiagentchat_bubble_chat_icon_admin']
                : get_option('aiagentchat_bubble_chat_icon_admin');

            $optionCssJsonAdmin = isset($postedSettings['aiagentchat_bubble_chat_css_json_admin'])
                ? $postedSettings['aiagentchat_bubble_chat_css_json_admin']
                : (get_option('aiagentchat_bubble_chat_css_json_admin') ?: json_encode([
                    "width" => "56px",
                    "height" => "56px",
                    "border-radius" => "50%",
                    "background" => "linear-gradient(135deg, #4f46e5, #06b6d4)",
                    "color" => "#ffffff",
                    "border" => "0",
                    "box-shadow" => "0 10px 25px rgba(0, 0, 0, .18)",
                    "transition" => "all .15s ease",
                    "position" => "fixed",
                    "bottom" => "24px",
                    "right" => "24px",
                    "z-index" => "9999"
                ], JSON_UNESCAPED_SLASHES));

            $optionIconClient = isset($postedSettings['aiagentchat_bubble_chat_icon_client'])
                ? $postedSettings['aiagentchat_bubble_chat_icon_client']
                : get_option('aiagentchat_bubble_chat_icon_client');

            $optionCssJsonClient = isset($postedSettings['aiagentchat_bubble_chat_css_json_client'])
                ? $postedSettings['aiagentchat_bubble_chat_css_json_client']
                : (get_option('aiagentchat_bubble_chat_css_json_client') ?: json_encode([
                    "width" => "56px",
                    "height" => "56px",
                    "border-radius" => "50%",
                    "background" => "linear-gradient(135deg, #4f46e5, #06b6d4)",
                    "color" => "#ffffff",
                    "border" => "0",
                    "box-shadow" => "0 10px 25px rgba(0, 0, 0, .18)",
                    "transition" => "all .15s ease",
                    "position" => "fixed",
                    "bottom" => "24px",
                    "right" => "24px",
                    "z-index" => "9999"
                ], JSON_UNESCAPED_SLASHES));

            echo form_open(admin_url(AIAGENTCHAT_MODULE_NAME . '/settings'), ['id' => AIAGENTCHAT_MODULE_NAME . '-settings-form']);
            ?>

            <div class="col-md-12">
                <div class="ai-hero">
                    <div class="ai-hero-left">
                        <div class="ai-hero-title">
                            <i class="fa fa-rocket"></i>
                            <span><?php echo html_escape(_l('aiagentchat')); ?></span>
                        </div>
                        <div class="ai-hero-sub">
                            <?php echo _l('settings'); ?> · <?php echo _l('aiagentchat_live_preview_hint'); ?>
                        </div>
                    </div>
                    <div class="ai-hero-right">
                        <span class="ai-badge"><?php echo _l('aiagentchat_tab_general'); ?></span>
                        <span class="ai-badge"><?php echo _l('aiagentchat_tab_admin_bubble'); ?></span>
                        <span class="ai-badge"><?php echo _l('aiagentchat_tab_client_bubble'); ?></span>
                        <span class="ai-badge"><?php echo _l('aiagentchat_tab_docs'); ?></span>
                    </div>
                </div>

                <div class="panel_s ai-panel">
                    <div class="panel-body ai-panel-body">

                        <ul class="nav nav-pills ai-pills" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#tab_general" aria-controls="tab_general" role="tab" data-toggle="tab">
                                    <i class="fa fa-key"></i> <?php echo _l('aiagentchat_tab_general'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_admin" aria-controls="tab_admin" role="tab" data-toggle="tab">
                                    <i class="fa fa-user-shield"></i> <?php echo _l('aiagentchat_tab_admin_bubble'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_client" aria-controls="tab_client" role="tab" data-toggle="tab">
                                    <i class="fa fa-users"></i> <?php echo _l('aiagentchat_tab_client_bubble'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_docs" aria-controls="tab_docs" role="tab" data-toggle="tab">
                                    <i class="fa fa-book"></i> <?php echo _l('aiagentchat_tab_docs'); ?>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mtop20">

                            <div role="tabpanel" class="tab-pane active" id="tab_general">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="ai-card">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title">
                                                    <i class="fa fa-plug"></i> <?php echo _l('aiagentchat_openai_api_key'); ?>
                                                </div>
                                                <div class="ai-card-sub"><?php echo _l('aiagentchat_openai_api_key_help'); ?></div>
                                            </div>
                                            <div class="ai-card-body">
                                                <div class="input-group ai-input-icon">
                                                    <?php
                                                    echo render_input(
                                                        'settings[aiagentchat_openai_api_key]',
                                                        '',
                                                        $optionOpenAiApiKey,
                                                        'password',
                                                        ['autocomplete' => 'off', 'placeholder' => 'sk-****************************', 'id' => 'ai_openai_key']
                                                    );
                                                    ?>
                                                    <span class="input-group-addon ai-eye-toggle" id="ai_toggle_key"
                                                          title="Show / Hide">
                            <i class="fa fa-eye"></i>
                          </span>
                                                </div>
                                                <div id="ai_key_status" class="ai-hint muted"><i
                                                            class="fa fa-shield"></i> <?php echo _l('aiagentchat_key_encrypted_hint'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="ai-card ai-card-glass">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title"><i
                                                            class="fa fa-info-circle"></i> <?php echo _l('aiagentchat_tips_title'); ?>
                                                </div>
                                                <div class="ai-card-sub"><?php echo _l('aiagentchat_tips_sub'); ?></div>
                                            </div>
                                            <ul class="ai-list">
                                                <li>
                                                    <i class="fa fa-magic"></i> <?php echo _l('aiagentchat_tips_item_presets'); ?>
                                                </li>
                                                <li>
                                                    <i class="fa fa-arrows"></i> <?php echo _l('aiagentchat_tips_item_sliders'); ?>
                                                </li>
                                                <li>
                                                    <i class="fa fa-bolt"></i> <?php echo _l('aiagentchat_tips_item_live'); ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_admin">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="ai-card">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title">
                                                    <span class="ai-env-badge admin">ADMIN</span>
                                                    <?php echo _l('aiagentchat_section_title_admin'); ?>
                                                </div>
                                                <div class="ai-card-actions">
                                                    <button type="button" class="btn btn-default btn-sm"
                                                            id="admin_reset_defaults_button">
                                                        <i class="fa fa-undo"></i> <?php echo _l('aiagentchat_reset_defaults'); ?>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="ai-card-body">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <?php echo render_input(
                                                            'settings[aiagentchat_bubble_chat_icon_admin]',
                                                            'aiagentchat_bubble_chat_icon_label',
                                                            $optionIconAdmin,
                                                            'text',
                                                            ['placeholder' => 'fa fa-commenting', 'id' => 'admin_icon_class']
                                                        ); ?>
                                                        <p class="ai-hint"><?php echo _l('aiagentchat_bubble_icon_hint'); ?></p>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-magic"></i> <?php echo _l('aiagentchat_presets'); ?>
                                                    </div>
                                                    <div class="ai-preset-row" id="admin_preset_row">
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#4f46e5" data-end="#06b6d4"
                                                                title="Indigo Breeze"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#ff7e5f" data-end="#feb47b"
                                                                title="Sunset"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#00c6ff" data-end="#0072ff"
                                                                title="Ocean"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#7f00ff" data-end="#e100ff"
                                                                title="Orchid"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#11998e" data-end="#38ef7d"
                                                                title="Emerald"></button>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-expand"></i> <?php echo _l('aiagentchat_size'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('admin_width_px', _l('aiagentchat_width_px'), '56', 'number', ['min' => 24, 'id' => 'admin_width_px']); ?>
                                                            <input type="range" min="24" max="160" value="56"
                                                                   class="ai-range" id="admin_width_px_range">
                                                        </div>
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('admin_height_px', _l('aiagentchat_height_px'), '56', 'number', ['min' => 24, 'id' => 'admin_height_px']); ?>
                                                            <input type="range" min="24" max="160" value="56"
                                                                   class="ai-range" id="admin_height_px_range">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-paint-brush"></i> <?php echo _l('aiagentchat_appearance'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('admin_border_radius_value', _l('aiagentchat_border_radius_value'), '50', 'number', ['min' => 0, 'id' => 'admin_border_radius_value']); ?>
                                                            <input type="range" min="0" max="50" value="50"
                                                                   class="ai-range"
                                                                   id="admin_border_radius_value_range">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <?php
                                                            echo render_select(
                                                                'admin_border_radius_unit',
                                                                [
                                                                    ['id' => '%', 'name' => '%'],
                                                                    ['id' => 'px', 'name' => 'px'],
                                                                ],
                                                                ['id', 'name'],
                                                                _l('aiagentchat_unit'),
                                                                '%',
                                                                ['id' => 'admin_border_radius_unit', 'class' => 'selectpicker', 'data-width' => '100%']
                                                            );
                                                            ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="admin_text_color">
                                                                <label for="admin_text_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_icon_text_color'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text" name="admin_text_color"
                                                                           id="admin_text_color" class="form-control"
                                                                           value="#ffffff">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <?php echo render_input('admin_gradient_angle', _l('aiagentchat_gradient_angle_deg'), '135', 'number', ['min' => 0, 'max' => 360, 'id' => 'admin_gradient_angle']); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="admin_gradient_start_color">
                                                                <label for="admin_gradient_start_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_gradient_start'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text" name="admin_gradient_start_color"
                                                                           id="admin_gradient_start_color"
                                                                           class="form-control" value="#4f46e5">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="admin_gradient_end_color">
                                                                <label for="admin_gradient_end_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_gradient_end'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text" name="admin_gradient_end_color"
                                                                           id="admin_gradient_end_color"
                                                                           class="form-control" value="#06b6d4">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-compass"></i> <?php echo _l('aiagentchat_position_behavior'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <?php echo render_input('admin_bottom_offset_px', _l('aiagentchat_bottom_px'), '24', 'number', ['min' => 0, 'id' => 'admin_bottom_offset_px']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('admin_right_offset_px', _l('aiagentchat_right_px'), '24', 'number', ['min' => 0, 'id' => 'admin_right_offset_px']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('admin_z_index', _l('aiagentchat_z_index'), '9999', 'number', ['id' => 'admin_z_index']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('admin_transition', _l('aiagentchat_transition'), 'all .15s ease', 'text', ['id' => 'admin_transition']); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-adjust"></i> <?php echo _l('aiagentchat_shadow'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <?php
                                                            echo render_select(
                                                                'admin_shadow_preset',
                                                                [
                                                                    ['id' => 'none', 'name' => _l('aiagentchat_shadow_none')],
                                                                    ['id' => 'soft', 'name' => _l('aiagentchat_shadow_soft')],
                                                                    ['id' => 'medium', 'name' => _l('aiagentchat_shadow_medium')],
                                                                    ['id' => 'strong', 'name' => _l('aiagentchat_shadow_strong')],
                                                                    ['id' => 'custom', 'name' => _l('aiagentchat_shadow_custom')],
                                                                ],
                                                                ['id', 'name'],
                                                                _l('aiagentchat_shadow'),
                                                                'soft',
                                                                ['id' => 'admin_shadow_preset', 'class' => 'selectpicker', 'data-width' => '100%']
                                                            );
                                                            ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <?php echo render_input('admin_shadow_custom', _l('aiagentchat_shadow_custom_value'), '0 10px 25px rgba(0,0,0,.18)', 'text', ['id' => 'admin_shadow_custom']); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <textarea name="settings[aiagentchat_bubble_chat_css_json_admin]"
                                                          id="admin_css_json"
                                                          class="hidden-field"><?php echo html_escape($optionCssJsonAdmin); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="ai-preview-card">
                                            <div class="ai-preview-head">
                                                <span class="ai-env-badge admin">ADMIN</span>
                                                <span class="ai-preview-title"><?php echo _l('aiagentchat_live_preview_admin'); ?></span>
                                            </div>
                                            <div class="bubble-preview-stage" id="admin_preview_stage">
                                                <button type="button" id="admin_preview_bubble"
                                                        class="agentchat-fab-preview">
                                                    <i id="admin_preview_icon"
                                                       class="<?php echo html_escape($optionIconAdmin ?: 'fa fa-commenting'); ?>"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_client">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="ai-card">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title">
                                                    <span class="ai-env-badge client">CLIENT</span>
                                                    <?php echo _l('aiagentchat_section_title_client'); ?>
                                                </div>
                                                <div class="ai-card-actions">
                                                    <button type="button" class="btn btn-default btn-sm"
                                                            id="client_reset_defaults_button">
                                                        <i class="fa fa-undo"></i> <?php echo _l('aiagentchat_reset_defaults'); ?>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="ai-card-body">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <?php echo render_input(
                                                            'settings[aiagentchat_bubble_chat_icon_client]',
                                                            'aiagentchat_bubble_chat_icon_label',
                                                            $optionIconClient,
                                                            'text',
                                                            ['placeholder' => 'fa fa-commenting', 'id' => 'client_icon_class']
                                                        ); ?>
                                                        <p class="ai-hint"><?php echo _l('aiagentchat_bubble_icon_hint'); ?></p>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-magic"></i> <?php echo _l('aiagentchat_presets'); ?>
                                                    </div>
                                                    <div class="ai-preset-row" id="client_preset_row">
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#4f46e5" data-end="#06b6d4"
                                                                title="Indigo Breeze"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#ff7e5f" data-end="#feb47b"
                                                                title="Sunset"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#00c6ff" data-end="#0072ff"
                                                                title="Ocean"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#7f00ff" data-end="#e100ff"
                                                                title="Orchid"></button>
                                                        <button type="button" class="ai-preset" data-angle="135"
                                                                data-start="#11998e" data-end="#38ef7d"
                                                                title="Emerald"></button>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-expand"></i> <?php echo _l('aiagentchat_size'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('client_width_px', _l('aiagentchat_width_px'), '56', 'number', ['min' => 24, 'id' => 'client_width_px']); ?>
                                                            <input type="range" min="24" max="160" value="56"
                                                                   class="ai-range" id="client_width_px_range">
                                                        </div>
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('client_height_px', _l('aiagentchat_height_px'), '56', 'number', ['min' => 24, 'id' => 'client_height_px']); ?>
                                                            <input type="range" min="24" max="160" value="56"
                                                                   class="ai-range" id="client_height_px_range">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-paint-brush"></i> <?php echo _l('aiagentchat_appearance'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 ai-slider-wrap">
                                                            <?php echo render_input('client_border_radius_value', _l('aiagentchat_border_radius_value'), '50', 'number', ['min' => 0, 'id' => 'client_border_radius_value']); ?>
                                                            <input type="range" min="0" max="50" value="50"
                                                                   class="ai-range"
                                                                   id="client_border_radius_value_range">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <?php
                                                            echo render_select(
                                                                'client_border_radius_unit',
                                                                [
                                                                    ['id' => '%', 'name' => '%'],
                                                                    ['id' => 'px', 'name' => 'px'],
                                                                ],
                                                                ['id', 'name'],
                                                                _l('aiagentchat_unit'),
                                                                '%',
                                                                ['id' => 'client_border_radius_unit', 'class' => 'selectpicker', 'data-width' => '100%']
                                                            );
                                                            ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="client_text_color">
                                                                <label for="client_text_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_icon_text_color'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text" name="client_text_color"
                                                                           id="client_text_color" class="form-control"
                                                                           value="#ffffff">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <?php echo render_input('client_gradient_angle', _l('aiagentchat_gradient_angle_deg'), '135', 'number', ['min' => 0, 'max' => 360, 'id' => 'client_gradient_angle']); ?>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="client_gradient_start_color">
                                                                <label for="client_gradient_start_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_gradient_start'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text"
                                                                           name="client_gradient_start_color"
                                                                           id="client_gradient_start_color"
                                                                           class="form-control" value="#4f46e5">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group"
                                                                 app-field-wrapper="client_gradient_end_color">
                                                                <label for="client_gradient_end_color"
                                                                       class="control-label"><?php echo _l('aiagentchat_gradient_end'); ?></label>
                                                                <div class="input-group mbot15 colorpicker-input colorpicker-element">
                                                                    <input type="text" name="client_gradient_end_color"
                                                                           id="client_gradient_end_color"
                                                                           class="form-control" value="#06b6d4">
                                                                    <span class="input-group-addon"><i></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-compass"></i> <?php echo _l('aiagentchat_position_behavior'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <?php echo render_input('client_bottom_offset_px', _l('aiagentchat_bottom_px'), '24', 'number', ['min' => 0, 'id' => 'client_bottom_offset_px']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('client_right_offset_px', _l('aiagentchat_right_px'), '24', 'number', ['min' => 0, 'id' => 'client_right_offset_px']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('client_z_index', _l('aiagentchat_z_index'), '9999', 'number', ['id' => 'client_z_index']); ?>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <?php echo render_input('client_transition', _l('aiagentchat_transition'), 'all .15s ease', 'text', ['id' => 'client_transition']); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-adjust"></i> <?php echo _l('aiagentchat_shadow'); ?>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <?php
                                                            echo render_select(
                                                                'client_shadow_preset',
                                                                [
                                                                    ['id' => 'none', 'name' => _l('aiagentchat_shadow_none')],
                                                                    ['id' => 'soft', 'name' => _l('aiagentchat_shadow_soft')],
                                                                    ['id' => 'medium', 'name' => _l('aiagentchat_shadow_medium')],
                                                                    ['id' => 'strong', 'name' => _l('aiagentchat_shadow_strong')],
                                                                    ['id' => 'custom', 'name' => _l('aiagentchat_shadow_custom')],
                                                                ],
                                                                ['id', 'name'],
                                                                _l('aiagentchat_shadow'),
                                                                'soft',
                                                                ['id' => 'client_shadow_preset', 'class' => 'selectpicker', 'data-width' => '100%']
                                                            );
                                                            ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <?php echo render_input('client_shadow_custom', _l('aiagentchat_shadow_custom_value'), '0 10px 25px rgba(0,0,0,.18)', 'text', ['id' => 'client_shadow_custom']); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <textarea name="settings[aiagentchat_bubble_chat_css_json_client]"
                                                          id="client_css_json"
                                                          class="hidden-field"><?php echo html_escape($optionCssJsonClient); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="ai-preview-card">
                                            <div class="ai-preview-head">
                                                <span class="ai-env-badge client">CLIENT</span>
                                                <span class="ai-preview-title"><?php echo _l('aiagentchat_live_preview_client'); ?></span>
                                            </div>
                                            <div class="bubble-preview-stage" id="client_preview_stage">
                                                <button type="button" id="client_preview_bubble"
                                                        class="agentchat-fab-preview">
                                                    <i id="client_preview_icon"
                                                       class="<?php echo html_escape($optionIconClient ?: 'fa fa-commenting'); ?>"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_docs">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="ai-card">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title">
                                                    <i class="fa fa-book"></i> <?php echo _l('aiagentchat_docs_title'); ?>
                                                </div>
                                                <div class="ai-card-sub"><?php echo _l('aiagentchat_docs_sub'); ?></div>
                                            </div>

                                            <div class="ai-card-body">
                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-bullseye"></i> <?php echo _l('aiagentchat_docs_overview_title'); ?>
                                                    </div>
                                                    <p class="ai-hint"><?php echo _l('aiagentchat_docs_overview_body'); ?></p>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-clipboard-check"></i> <?php echo _l('aiagentchat_docs_requirements_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li><?php echo _l('aiagentchat_docs_req_item_account'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_req_item_api_key'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_req_item_roles'); ?></li>
                                                    </ul>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-list-ol"></i> <?php echo _l('aiagentchat_docs_steps_title'); ?>
                                                    </div>
                                                    <ol class="ai-docs-ol">
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_agent_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_agent_body'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_workflow_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_workflow_body'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_publish_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_publish_body'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_connect_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_connect_body'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_style_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_style_body'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_step_test_title'); ?></strong><br>
                                                            <span class="ai-hint"><?php echo _l('aiagentchat_docs_step_test_body'); ?></span>
                                                        </li>
                                                    </ol>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-lightbulb-o"></i> <?php echo _l('aiagentchat_docs_best_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li><?php echo _l('aiagentchat_docs_best_item_1'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_best_item_2'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_best_item_3'); ?></li>
                                                    </ul>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-life-ring"></i> <?php echo _l('aiagentchat_docs_troubleshooting_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_trbl_1_q'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_trbl_1_a'); ?></li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_trbl_2_q'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_trbl_2_a'); ?></li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_trbl_3_q'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_trbl_3_a'); ?></li>
                                                    </ul>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-question-circle"></i> <?php echo _l('aiagentchat_docs_faq_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_faq_1_q'); ?></strong><br><span
                                                                    class="ai-hint"><?php echo _l('aiagentchat_docs_faq_1_a'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_faq_2_q'); ?></strong><br><span
                                                                    class="ai-hint"><?php echo _l('aiagentchat_docs_faq_2_a'); ?></span>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_faq_3_q'); ?></strong><br><span
                                                                    class="ai-hint"><?php echo _l('aiagentchat_docs_faq_3_a'); ?></span>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-bookmark"></i> <?php echo _l('aiagentchat_docs_glossary_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_glossary_agent'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_glossary_agent_def'); ?>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_glossary_workflow'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_glossary_workflow_def'); ?>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_glossary_workflow_id'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_glossary_workflow_id_def'); ?>
                                                        </li>
                                                        <li>
                                                            <strong><?php echo _l('aiagentchat_docs_glossary_client_secret'); ?></strong>
                                                            — <?php echo _l('aiagentchat_docs_glossary_client_secret_def'); ?>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="ai-group">
                                                    <div class="ai-group-title"><i
                                                                class="fa fa-check-square-o"></i> <?php echo _l('aiagentchat_docs_checklist_title'); ?>
                                                    </div>
                                                    <ul class="ai-list">
                                                        <li><?php echo _l('aiagentchat_docs_checklist_item_1'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_checklist_item_2'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_checklist_item_3'); ?></li>
                                                        <li><?php echo _l('aiagentchat_docs_checklist_item_4'); ?></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="ai-card ai-card-glass">
                                            <div class="ai-card-head">
                                                <div class="ai-card-title"><i
                                                            class="fa fa-thumbs-up"></i> <?php echo _l('aiagentchat_docs_side_title'); ?>
                                                </div>
                                                <div class="ai-card-sub"><?php echo _l('aiagentchat_docs_side_sub'); ?></div>
                                            </div>
                                            <ul class="ai-list">
                                                <li><?php echo _l('aiagentchat_docs_side_item_1'); ?></li>
                                                <li><?php echo _l('aiagentchat_docs_side_item_2'); ?></li>
                                                <li><?php echo _l('aiagentchat_docs_side_item_3'); ?></li>
                                            </ul>
                                            <div class="ai-docs-badges">
                                                <span class="ai-badge">Branding</span>
                                                <span class="ai-badge">No-Code</span>
                                                <span class="ai-badge">Fast Setup</span>
                                                <span class="ai-badge">Admin & Client</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        'use strict';

        function byId(id) {
            return document.getElementById(id);
        }

        function getValue(id) {
            var el = byId(id);
            return el ? (el.value || '').trim() : '';
        }

        function getNumber(id, fallback) {
            var n = parseInt(getValue(id), 10);
            return isNaN(n) ? fallback : n;
        }

        function setValue(id, val) {
            var el = byId(id);
            if (!el) return;
            el.value = val;
            if (window.jQuery) {
                var $el = window.jQuery(el);
                if ($el.hasClass('selectpicker') && window.jQuery.fn.selectpicker) {
                    $el.selectpicker('val', val).trigger('changed.bs.select').selectpicker('refresh');
                } else {
                    $el.trigger('change');
                }
            }
        }

        function safeJson(str) {
            try {
                return str ? JSON.parse(str) : {};
            } catch (e) {
                return {};
            }
        }

        function shadowFromPreset(preset, customValue) {
            if (preset === 'none') return 'none';
            if (preset === 'soft') return '0 10px 25px rgba(0, 0, 0, .18)';
            if (preset === 'medium') return '0 14px 28px rgba(0, 0, 0, .25)';
            if (preset === 'strong') return '0 19px 38px rgba(0, 0, 0, .30)';
            return customValue || '0 10px 25px rgba(0,0,0,.18)';
        }

        function defaultCss() {
            return {
                "width": "56px", "height": "56px", "border-radius": "50%",
                "background": "linear-gradient(135deg, #4f46e5, #06b6d4)",
                "color": "#ffffff", "border": "0", "box-shadow": "0 10px 25px rgba(0, 0, 0, .18)",
                "transition": "all .15s ease", "position": "fixed", "bottom": "24px", "right": "24px", "z-index": "9999"
            };
        }

        function getSelectValue(selectId) {
            var el = byId(selectId);
            if (el) return (el.value || '').trim();
            if (window.jQuery) {
                var $el = window.jQuery('#' + selectId);
                if ($el.length) return ($el.val() || '').toString().trim();
            }
            return '';
        }

        function buildCss(prefix) {
            var widthPx = getNumber(prefix + '_width_px', 56);
            var heightPx = getNumber(prefix + '_height_px', 56);
            var radiusVal = getNumber(prefix + '_border_radius_value', 50);
            var radiusUnit = getSelectValue(prefix + '_border_radius_unit') || '%';
            var textColor = getValue(prefix + '_text_color') || '#ffffff';
            var zIndex = getValue(prefix + '_z_index') || '9999';
            var bottomOffset = getNumber(prefix + '_bottom_offset_px', 24);
            var rightOffset = getNumber(prefix + '_right_offset_px', 24);
            var transitionVal = getValue(prefix + '_transition') || 'all .15s ease';
            var shadowPreset = getSelectValue(prefix + '_shadow_preset') || 'soft';
            var shadowCustom = getValue(prefix + '_shadow_custom') || '0 10px 25px rgba(0,0,0,.18)';
            var shadowResolved = shadowFromPreset(shadowPreset, shadowCustom);
            var angle = getNumber(prefix + '_gradient_angle', 135);
            var colorStart = getValue(prefix + '_gradient_start_color') || '#4f46e5';
            var colorEnd = getValue(prefix + '_gradient_end_color') || '#06b6d4';
            var backgroundVal = 'linear-gradient(' + angle + 'deg, ' + colorStart + ', ' + colorEnd + ')';
            return {
                "width": widthPx + "px",
                "height": heightPx + "px",
                "border-radius": radiusVal + radiusUnit,
                "background": backgroundVal,
                "color": textColor,
                "border": "0",
                "box-shadow": shadowResolved,
                "transition": transitionVal,
                "position": "fixed",
                "bottom": bottomOffset + "px",
                "right": rightOffset + "px",
                "z-index": String(zIndex)
            };
        }

        function applyCss(cssObj, bubbleId, stageId) {
            var bubble = byId(bubbleId);
            var stage = byId(stageId);
            if (!bubble) return;
            if (stage) stage.style.position = 'relative';
            bubble.style.position = 'absolute';
            Object.keys(cssObj).forEach(function (k) {
                if (k !== 'position') {
                    bubble.style.setProperty(k, cssObj[k]);
                }
            });
        }

        function hydrateFromCss(prefix, cssObj) {
            var m;
            if (m = (cssObj['width'] || '').match(/^(\d+)px$/i)) setValue(prefix + '_width_px', m[1]);
            if (m = (cssObj['height'] || '').match(/^(\d+)px$/i)) setValue(prefix + '_height_px', m[1]);
            if (m = (cssObj['border-radius'] || '').match(/^(\d+)\s*(px|%)$/i)) {
                setValue(prefix + '_border_radius_value', m[1]);
                setValue(prefix + '_border_radius_unit', m[2]);
            }
            if (cssObj['color']) setValue(prefix + '_text_color', cssObj['color']);
            if (m = (cssObj['bottom'] || '').match(/^(\d+)px$/i)) setValue(prefix + '_bottom_offset_px', m[1]);
            if (m = (cssObj['right'] || '').match(/^(\d+)px$/i)) setValue(prefix + '_right_offset_px', m[1]);
            if (cssObj['z-index']) setValue(prefix + '_z_index', cssObj['z-index']);
            if (cssObj['transition']) setValue(prefix + '_transition', cssObj['transition']);
            var shadow = cssObj['box-shadow'] || 'none', preset = 'custom';
            if (shadow === 'none') preset = 'none';
            if (shadow === '0 10px 25px rgba(0, 0, 0, .18)') preset = 'soft';
            if (shadow === '0 14px 28px rgba(0, 0, 0, .25)') preset = 'medium';
            if (shadow === '0 19px 38px rgba(0, 0, 0, .30)') preset = 'strong';
            setValue(prefix + '_shadow_preset', preset);
            setValue(prefix + '_shadow_custom', shadow);
            var g = (cssObj['background'] || '').match(/linear-gradient\(\s*(\d+)\s*deg\s*,\s*([^,]+)\s*,\s*([^)]+)\)/i);
            if (g) {
                setValue(prefix + '_gradient_angle', g[1]);
                setValue(prefix + '_gradient_start_color', g[2].trim());
                setValue(prefix + '_gradient_end_color', g[3].trim());
            }
        }

        function rebuild(prefix) {
            var css = buildCss(prefix);
            applyCss(css, prefix + '_preview_bubble', prefix + '_preview_stage');
            var hidden = byId(prefix + '_css_json');
            if (hidden) hidden.value = JSON.stringify(css);
        }

        function initFromExisting(prefix, jsonString) {
            var obj = safeJson(jsonString);
            if (Object.keys(obj).length === 0) {
                obj = defaultCss();
            }
            hydrateFromCss(prefix, obj);
            rebuild(prefix);
        }

        function bindInput(ids, prefix) {
            ids.forEach(function (id) {
                var el = byId(id);
                if (!el) return;
                ['input', 'change', 'keyup'].forEach(function (evt) {
                    el.addEventListener(evt, function () {
                        rebuild(prefix);
                    });
                });
            });
        }

        function bindColorLive(inputId, prefix) {
            var el = byId(inputId);
            if (!el) return;

            function update() {
                rebuild(prefix);
            }

            ['input', 'change', 'keyup'].forEach(function (evt) {
                el.addEventListener(evt, update);
            });
            if (window.jQuery) {
                var $ = window.jQuery, $wrap = $('[app-field-wrapper="' + inputId + '"] .colorpicker-element'),
                    $in = $('#' + inputId);
                $wrap.on('colorpickerChange changeColor colorpicker:change colorpickerHide colorpickerShow', update);
                $in.on('colorpickerChange changeColor colorpicker:change', update);
                var raf = null;

                function start() {
                    if (raf) return;
                    var last = $in.val();
                    (function tick() {
                        var cur = $in.val();
                        if (cur !== last) {
                            last = cur;
                            update();
                        }
                        raf = requestAnimationFrame(tick);
                    })();
                }

                function stop() {
                    if (raf) {
                        cancelAnimationFrame(raf);
                        raf = null;
                    }
                }

                $wrap.on('mousedown touchstart', start);
                $(document).on('mouseup touchend', stop);
                $wrap.on('colorpickerHide', stop);
            }
        }

        function bindSelectpicker(selectIds, prefix) {
            if (!window.jQuery) return;
            var $ = window.jQuery;
            selectIds.forEach(function (id) {
                var $s = $('#' + id);
                if (!$s.length) return;
                if ($.fn.selectpicker) {
                    $s.addClass('selectpicker').selectpicker().selectpicker('refresh');
                }
                $s.on('changed.bs.select change', function () {
                    if (id.indexOf('shadow_preset') !== -1) {
                        var isCustom = $(this).val() === 'custom';
                        $('#' + (id.indexOf('admin') === 0 ? 'admin' : 'client') + '_shadow_custom').prop('disabled', !isCustom);
                    }
                    rebuild(prefix);
                });
            });
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('href');
                if ($.fn.selectpicker) {
                    $(target).find('.selectpicker').selectpicker('refresh');
                }
            });
        }

        function bindIcon(inputId, fallbackName, previewIconId) {
            var input = byId(inputId) || document.querySelector('input[name="' + fallbackName + '"]');
            var icon = byId(previewIconId);
            if (!input || !icon) return;

            function upd() {
                icon.className = (input.value || 'fa fa-commenting').trim();
            }

            ['input', 'change', 'keyup'].forEach(function (evt) {
                input.addEventListener(evt, upd);
            });
            upd();
        }

        function linkRange(numberId, rangeId, prefix) {
            var numberEl = byId(numberId), rangeEl = byId(rangeId);
            if (!numberEl || !rangeEl) return;
            rangeEl.value = numberEl.value || rangeEl.value;
            numberEl.addEventListener('input', function () {
                rangeEl.value = numberEl.value;
                rebuild(prefix);
            });
            rangeEl.addEventListener('input', function () {
                numberEl.value = rangeEl.value;
                rebuild(prefix);
            });
        }

        function bindPresets(rowId, prefix) {
            var row = byId(rowId);
            if (!row) return;
            row.querySelectorAll('.ai-preset').forEach(function (btn) {
                btn.style.background = 'linear-gradient(135deg, ' + btn.dataset.start + ', ' + btn.dataset.end + ')';
                btn.addEventListener('click', function () {
                    setValue(prefix + '_gradient_angle', btn.dataset.angle || '135');
                    setValue(prefix + '_gradient_start_color', btn.dataset.start || '#4f46e5');
                    setValue(prefix + '_gradient_end_color', btn.dataset.end || '#06b6d4');
                    rebuild(prefix);
                    btn.classList.add('active');
                    row.querySelectorAll('.ai-preset').forEach(function (b) {
                        if (b !== btn) b.classList.remove('active');
                    });
                });
            });
        }

        (function () {
            var toggle = byId('ai_toggle_key');
            var input = byId('ai_openai_key');
            if (toggle && input) {
                toggle.addEventListener('click', function () {
                    var icon = toggle.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        if (icon) icon.className = 'fa fa-eye-slash';
                    } else {
                        input.type = 'password';
                        if (icon) icon.className = 'fa fa-eye';
                    }
                });
            }
        })();
        var initialAdminJsonString = <?php echo json_encode($optionCssJsonAdmin, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        initFromExisting('admin', initialAdminJsonString);
        bindInput([
            'admin_width_px', 'admin_height_px', 'admin_border_radius_value', 'admin_border_radius_unit',
            'admin_text_color', 'admin_z_index', 'admin_bottom_offset_px', 'admin_right_offset_px',
            'admin_transition', 'admin_shadow_preset', 'admin_shadow_custom',
            'admin_gradient_angle', 'admin_gradient_start_color', 'admin_gradient_end_color'
        ], 'admin');
        bindColorLive('admin_text_color', 'admin');
        bindColorLive('admin_gradient_start_color', 'admin');
        bindColorLive('admin_gradient_end_color', 'admin');
        bindSelectpicker(['admin_border_radius_unit', 'admin_shadow_preset'], 'admin');
        bindIcon('admin_icon_class', 'settings[aiagentchat_bubble_chat_icon_admin]', 'admin_preview_icon');
        linkRange('admin_width_px', 'admin_width_px_range', 'admin');
        linkRange('admin_height_px', 'admin_height_px_range', 'admin');
        linkRange('admin_border_radius_value', 'admin_border_radius_value_range', 'admin');
        bindPresets('admin_preset_row', 'admin');
        var adminReset = byId('admin_reset_defaults_button');
        if (adminReset) {
            adminReset.addEventListener('click', function () {
                var css = defaultCss();
                hydrateFromCss('admin', css);
                setValue('admin_gradient_angle', '135');
                setValue('admin_gradient_start_color', '#4f46e5');
                setValue('admin_gradient_end_color', '#06b6d4');
                setValue('admin_shadow_preset', 'soft');
                setValue('admin_shadow_custom', '0 10px 25px rgba(0,0,0,.18)');
                rebuild('admin');
            });
        }
        var initialClientJsonString = <?php echo json_encode($optionCssJsonClient, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        initFromExisting('client', initialClientJsonString);
        bindInput([
            'client_width_px', 'client_height_px', 'client_border_radius_value', 'client_border_radius_unit',
            'client_text_color', 'client_z_index', 'client_bottom_offset_px', 'client_right_offset_px',
            'client_transition', 'client_shadow_preset', 'client_shadow_custom',
            'client_gradient_angle', 'client_gradient_start_color', 'client_gradient_end_color'
        ], 'client');
        bindColorLive('client_text_color', 'client');
        bindColorLive('client_gradient_start_color', 'client');
        bindColorLive('client_gradient_end_color', 'client');
        bindSelectpicker(['client_border_radius_unit', 'client_shadow_preset'], 'client');
        bindIcon('client_icon_class', 'settings[aiagentchat_bubble_chat_icon_client]', 'client_preview_icon');
        linkRange('client_width_px', 'client_width_px_range', 'client');
        linkRange('client_height_px', 'client_height_px_range', 'client');
        linkRange('client_border_radius_value', 'client_border_radius_value_range', 'client');
        bindPresets('client_preset_row', 'client');
        var clientReset = byId('client_reset_defaults_button');
        if (clientReset) {
            clientReset.addEventListener('click', function () {
                var css = defaultCss();
                hydrateFromCss('client', css);
                setValue('client_gradient_angle', '135');
                setValue('client_gradient_start_color', '#4f46e5');
                setValue('client_gradient_end_color', '#06b6d4');
                setValue('client_shadow_preset', 'soft');
                setValue('client_shadow_custom', '0 10px 25px rgba(0,0,0,.18)');
                rebuild('client');
            });
        }
        var settingsForm = byId('<?php echo AIAGENTCHAT_MODULE_NAME; ?>-settings-form');
        if (settingsForm) {
            settingsForm.addEventListener('submit', function () {
                rebuild('admin');
                rebuild('client');
            });
        }
        if (window.jQuery && window.jQuery.fn.selectpicker) {
            window.jQuery('.selectpicker').selectpicker('refresh');
        }
    });
</script>

<?php init_tail(); ?>
