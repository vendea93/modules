<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url(AIAGENTCHAT_MODULE_NAME, 'assets/css/manage.css'); ?>"/>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="ac-pagehead">
                    <div class="ac-pagehead-left">
                        <div class="ac-pagehead-icon">
                            <i class="fas fa-robot" aria-hidden="true"></i>
                        </div>
                        <div class="ac-pagehead-text">
                            <h4 class="tw-mt-0 tw-mb-0 tw-font-semibold tw-text-lg tw-text-neutral-800">
                                <?php echo _l('aiagentchat_menu_manage'); ?>
                            </h4>
                            <p class="ac-sub"><?php echo _l('aiagentchat_builder_sub'); ?></p>
                        </div>
                    </div>

                    <div class="ac-toolbar">
                        <a href="<?php echo admin_url('aiagentchat/create'); ?>" class="btn btn-primary ac-btn">
                            <i class="fa fa-plus-circle tw-mr-1" aria-hidden="true"></i>
                            <span><?php echo _l('aiagentchat_create_chat'); ?></span>
                        </a>
                    </div>
                </div>

                <div class="panel_s ac-card">
                    <div class="panel-body panel-table-full">
                        <div class="col-md-12">
                            <?php
                            render_datatable(
                                [
                                    _l('aiagentchat_chat_name'),
                                    _l('aiagentchat_workflow_id'),
                                    _l('aiagentchat_is_enabled'),
                                    _l('aiagentchat_created_at'),
                                    _l('options'),
                                ],
                                'aiagentchat-categories'
                            );
                            ?>
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
        'use strict';
        var dataTableInstance = initDataTable(
            '.table-aiagentchat-categories',
            window.location.href,
            [3],
            [3],
            [],
            [3, 'desc']
        );

        function enhanceRows() {
            var $rows = $('.table-aiagentchat-categories').find('tbody tr');
            $rows.each(function () {
                var $cells = $(this).find('td');
                var workflowId = ($cells.eq(1).text() || '').trim();
                if (workflowId && !$cells.eq(1).data('enhanced')) {
                    var shortened = workflowId.length > 18 ? (workflowId.slice(0, 10) + '…' + workflowId.slice(-6)) : workflowId;
                    var html = ''
                        + '<div class="ac-workflow">'
                        + '  <span class="ac-workflow-code" title="' + _.escape(workflowId) + '">' + _.escape(shortened) + '</span>'
                        + '  <button type="button" class="ac-copy" data-copy="' + _.escape(workflowId) + '"><i class="fa fa-copy"></i><span><?php echo _l('copy'); ?></span></button>'
                        + '</div>';
                    $cells.eq(1).html(html).data('enhanced', true);
                }
            });
        }

        function copyTextToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            }
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
            } catch (e) {
            }
            document.body.removeChild(ta);
            return Promise.resolve();
        }

        $(document).on('click', '.ac-copy', function () {
            var value = $(this).data('copy') || '';
            copyTextToClipboard(String(value)).then(function () {
                alert_float('success', '<?php echo _l('copied'); ?>');
            }).catch(function () {
                alert_float('warning', '<?php echo _l('problem'); ?>');
            });
        });

        window._ = window._ || {};
        _.escape = function (s) {
            return String(s || '').replace(/[&<>"'`=\/]/g, function (c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#x27;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                    '=': '&#x3D;'
                }[c] || c;
            });
        };

        $('.table-aiagentchat-categories').on('draw.dt', enhanceRows);

        setTimeout(function () {
            enhanceRows();
        }, 50);
    });
</script>

