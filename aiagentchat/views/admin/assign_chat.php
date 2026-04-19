<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url(AIAGENTCHAT_MODULE_NAME, 'assets/css/assign_chat.css'); ?>"/>
<div id="wrapper" class="aiagentchat-assign-modern">
    <div class="content">
        <div class="row">
            <?php echo form_open(admin_url('aiagentchat/assign/' . (int)$chat->id), ['id' => 'aiagentchat-assign-form']); ?>
            <div class="col-md-12">
                <div class="ai-hero">
                    <div class="ai-hero-left">
                        <div class="ai-hero-title">
                            <i class="fa fa-link"></i>
                            <span><?php echo _l('aiagentchat_assign_title', html_escape($chat->chat_name)); ?></span>
                            <span class="ai-badge">#<?php echo (int)$chat->id; ?></span>
                            <?php if ((int)$chat->is_enabled === 1) { ?>
                                <span class="ai-badge ai-badge-green"><?php echo _l('aiagentchat_status_enabled'); ?></span>
                            <?php } else { ?>
                                <span class="ai-badge ai-badge-gray"><?php echo _l('aiagentchat_status_disabled'); ?></span>
                            <?php } ?>
                        </div>
                        <div class="ai-hero-sub">
                            <?php echo _l('aiagentchat_assign_sub'); ?>
                        </div>
                    </div>
                    <div class="ai-hero-right btn-group">
                        <a href="<?php echo admin_url('aiagentchat/create/' . (int)$chat->id); ?>"
                           class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> <?php echo _l('aiagentchat_back_to_chat'); ?>
                        </a>
                        <a href="<?php echo admin_url('aiagentchat'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-list"></i> <?php echo _l('aiagentchat_back_to_manage'); ?>
                        </a>
                    </div>
                </div>

                <div class="row mtop10">
                    <div class="col-md-12">
                        <div class="ai-info-card">
                            <div class="ai-info-row">
                                <div class="ai-info-item">
                                    <span class="ai-info-label"><?php echo _l('aiagentchat_info_chat'); ?></span>
                                    <span class="ai-info-value"><?php echo html_escape($chat->chat_name); ?></span>
                                </div>
                                <div class="ai-info-item">
                                    <span class="ai-info-label"><?php echo _l('aiagentchat_info_workflow'); ?></span>
                                    <span class="ai-info-value"><?php echo html_escape($chat->workflow_id ?: '-'); ?></span>
                                </div>
                                <div class="ai-info-item">
                                    <span class="ai-info-label"><?php echo _l('aiagentchat_info_created'); ?></span>
                                    <span class="ai-info-value"><?php echo _dt($chat->created_at); ?></span>
                                </div>
                                <div class="ai-info-item">
                                    <span class="ai-info-label"><?php echo _l('aiagentchat_info_visibility'); ?></span>
                                    <span class="ai-info-value"><?php echo (int)$chat->is_enabled === 1 ? _l('aiagentchat_status_enabled') : _l('aiagentchat_status_disabled'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mtop10">

                    <div class="col-md-6">
                        <div class="ai-card">
                            <div class="ai-card-head">
                                <div class="ai-card-title">
                                    <i class="fa fa-user-secret"></i> <?php echo _l('aiagentchat_assign_admin_area'); ?>
                                </div>
                                <div class="ai-card-sub"><?php echo _l('aiagentchat_assign_admin_desc'); ?></div>
                            </div>
                            <div class="ai-card-body">

                                <div class="checkbox checkbox-primary">
                                    <input type="hidden" name="admin_all_staff" value="0">
                                    <input type="checkbox" name="admin_all_staff" id="admin_all_staff"
                                           value="1" <?php echo !empty($preselect['admin_all']) ? 'checked' : ''; ?>>
                                    <label for="admin_all_staff"><?php echo _l('aiagentchat_assign_all_staff'); ?></label>
                                </div>

                                <div id="admin_specific_block"
                                     class="<?php echo !empty($preselect['admin_all']) ? 'hide' : ''; ?>">
                                    <div class="form-group">
                                        <label for="admin_staff_ids"><?php echo _l('aiagentchat_assign_staff'); ?></label>
                                        <select id="admin_staff_ids" name="admin_staff_ids[]" class="selectpicker"
                                                data-width="100%" data-live-search="true" multiple
                                                title="<?php echo _l('aiagentchat_pick_staff'); ?>">
                                            <?php foreach ($staff_options as $option) { ?>
                                                <option value="<?php echo (int)$option['id']; ?>" <?php echo in_array((int)$option['id'], $preselect['staff_ids'] ?? []) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($option['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="ai-hint"><?php echo _l('aiagentchat_hint_staff'); ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="admin_department_ids"><?php echo _l('aiagentchat_assign_departments'); ?></label>
                                        <select id="admin_department_ids" name="admin_department_ids[]"
                                                class="selectpicker" data-width="100%" data-live-search="true" multiple
                                                title="<?php echo _l('aiagentchat_pick_departments'); ?>">
                                            <?php foreach ($department_options as $option) { ?>
                                                <option value="<?php echo (int)$option['id']; ?>" <?php echo in_array((int)$option['id'], $preselect['dept_ids'] ?? []) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($option['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="ai-hint"><?php echo _l('aiagentchat_hint_departments'); ?></p>
                                    </div>
                                </div>

                                <div class="ai-summary">
                                    <div class="ai-summary-title"><?php echo _l('aiagentchat_summary_admin'); ?></div>
                                    <div id="admin_summary_chips" class="ai-chip-wrap"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="ai-card">
                            <div class="ai-card-head">
                                <div class="ai-card-title">
                                    <i class="fa fa-users"></i> <?php echo _l('aiagentchat_assign_client_area'); ?>
                                </div>
                                <div class="ai-card-sub"><?php echo _l('aiagentchat_assign_client_desc'); ?></div>
                            </div>
                            <div class="ai-card-body">

                                <div class="checkbox checkbox-primary">
                                    <input type="hidden" name="client_all_customers" value="0">
                                    <input type="checkbox" name="client_all_customers" id="client_all_customers"
                                           value="1" <?php echo !empty($preselect['client_all']) ? 'checked' : ''; ?>>
                                    <label for="client_all_customers"><?php echo _l('aiagentchat_assign_all_clients'); ?></label>
                                </div>

                                <div id="client_specific_block"
                                     class="<?php echo !empty($preselect['client_all']) ? 'hide' : ''; ?>">
                                    <div class="form-group">
                                        <label for="client_customer_ids"><?php echo _l('aiagentchat_assign_customers'); ?></label>
                                        <select id="client_customer_ids" name="client_customer_ids[]"
                                                class="selectpicker" data-width="100%" data-live-search="true" multiple
                                                title="<?php echo _l('aiagentchat_pick_customers'); ?>">
                                            <?php foreach ($customer_options as $option) { ?>
                                                <option value="<?php echo (int)$option['id']; ?>" <?php echo in_array((int)$option['id'], $preselect['customer_ids'] ?? []) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($option['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="ai-hint"><?php echo _l('aiagentchat_hint_customers'); ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="client_group_ids"><?php echo _l('aiagentchat_assign_groups'); ?></label>
                                        <select id="client_group_ids" name="client_group_ids[]" class="selectpicker"
                                                data-width="100%" data-live-search="true" multiple
                                                title="<?php echo _l('aiagentchat_pick_groups'); ?>">
                                            <?php foreach ($group_options as $option) { ?>
                                                <option value="<?php echo (int)$option['id']; ?>" <?php echo in_array((int)$option['id'], $preselect['group_ids'] ?? []) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($option['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="ai-hint"><?php echo _l('aiagentchat_hint_groups'); ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="client_contact_ids"><?php echo _l('aiagentchat_assign_contacts'); ?></label>
                                        <select id="client_contact_ids" name="client_contact_ids[]" class="selectpicker"
                                                data-width="100%" data-live-search="true" multiple
                                                title="<?php echo _l('aiagentchat_pick_contacts'); ?>">
                                            <?php foreach ($contact_options as $option) { ?>
                                                <option value="<?php echo (int)$option['id']; ?>" <?php echo in_array((int)$option['id'], $preselect['contact_ids'] ?? []) ? 'selected' : ''; ?>>
                                                    <?php echo html_escape($option['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <p class="ai-hint"><?php echo _l('aiagentchat_hint_contacts'); ?></p>
                                    </div>
                                </div>

                                <div class="ai-summary">
                                    <div class="ai-summary-title"><?php echo _l('aiagentchat_summary_client'); ?></div>
                                    <div id="client_summary_chips" class="ai-chip-wrap"></div>
                                </div>

                            </div>
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
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
    (function () {
        'use strict';
        var $ = window.jQuery;
        if (!$) return;

        function handleAdminAllToggle() {
            var isChecked = $('#admin_all_staff').prop('checked');
            $('#admin_specific_block')[isChecked ? 'addClass' : 'removeClass']('hide');
            $('#admin_staff_ids, #admin_department_ids')
                .prop('disabled', isChecked)
                .selectpicker(isChecked ? 'deselectAll' : 'refresh')
                .selectpicker('refresh');
        }

        function handleClientAllToggle() {
            var isChecked = $('#client_all_customers').prop('checked');
            $('#client_specific_block')[isChecked ? 'addClass' : 'removeClass']('hide');
            $('#client_customer_ids, #client_group_ids, #client_contact_ids')
                .prop('disabled', isChecked)
                .selectpicker(isChecked ? 'deselectAll' : 'refresh')
                .selectpicker('refresh');
        }

        function getSelectedText(selector, limit) {
            var list = [];
            $(selector).find('option:selected').each(function () {
                list.push($(this).text());
            });
            if (typeof limit === 'number' && list.length > limit) {
                var extra = list.length - limit;
                list = list.slice(0, limit);
                list.push('+' + extra);
            }
            return list;
        }

        function renderAdminSummary() {
            var $wrap = $('#admin_summary_chips').empty();
            if ($('#admin_all_staff').prop('checked')) {
                $wrap.append('<span class="ai-chip ai-chip-green"><i class="fa fa-check"></i><?php echo _l('aiagentchat_chip_all_staff'); ?></span>');
                return;
            }
            var staff = getSelectedText('#admin_staff_ids', 3);
            var depts = getSelectedText('#admin_department_ids', 3);

            if (staff.length) {
                $wrap.append('<span class="ai-chip ai-chip-blue"><i class="fa fa-user"></i><?php echo _l('aiagentchat_chip_staff'); ?>: ' + staff.join(', ') + '</span>');
            }
            if (depts.length) {
                $wrap.append('<span class="ai-chip ai-chip-blue"><i class="fa fa-sitemap"></i><?php echo _l('aiagentchat_chip_departments'); ?>: ' + depts.join(', ') + '</span>');
            }
            if (!staff.length && !depts.length) {
                $wrap.append('<span class="ai-chip ai-chip-gray"><i class="fa fa-info-circle"></i><?php echo _l('aiagentchat_chip_admin_empty'); ?></span>');
            }
        }

        function renderClientSummary() {
            var $wrap = $('#client_summary_chips').empty();
            if ($('#client_all_customers').prop('checked')) {
                $wrap.append('<span class="ai-chip ai-chip-green' > < i

                class

                = "fa fa-check" > < /i><?php echo _l('aiagentchat_chip_all_clients'); ?></s
                pan > ');
                return;
            }
            var customers = getSelectedText('#client_customer_ids', 2);
            var groups = getSelectedText('#client_group_ids', 2);
            var contacts = getSelectedText('#client_contact_ids', 2);

            if (customers.length) {
                $wrap.append('<span class="ai-chip ai-chip-blue"><i class="fa fa-building"></i><?php echo _l('aiagentchat_chip_customers'); ?>: ' + customers.join(', ') + '</span>');
            }
            if (groups.length) {
                $wrap.append('<span class="ai-chip ai-chip-blue"><i class="fa fa-tags"></i><?php echo _l('aiagentchat_chip_groups'); ?>: ' + groups.join(', ') + '</span>');
            }
            if (contacts.length) {
                $wrap.append('<span class="ai-chip ai-chip-blue"><i class="fa fa-address-book"></i><?php echo _l('aiagentchat_chip_contacts'); ?>: ' + contacts.join(', ') + '</span>');
            }
            if (!customers.length && !groups.length && !contacts.length) {
                $wrap.append('<span class="ai-chip ai-chip-gray"><i class="fa fa-info-circle"></i><?php echo _l('aiagentchat_chip_client_empty'); ?></span>');
            }
        }

        function renderSummaries() {
            renderAdminSummary();
            renderClientSummary();
        }

        $('.selectpicker').selectpicker();

        $('#admin_all_staff').on('change', function () {
            handleAdminAllToggle();
            renderSummaries();
        });
        $('#client_all_customers').on('change', function () {
            handleClientAllToggle();
            renderSummaries();
        });

        $('#admin_staff_ids, #admin_department_ids, #client_customer_ids, #client_group_ids, #client_contact_ids')
            .on('changed.bs.select', renderSummaries);

        handleAdminAllToggle();
        handleClientAllToggle();
        renderSummaries();
    })();
</script>
<?php init_tail(); ?>
