<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_buttons tw-mb-2">
                    <?php if (staff_can('create', 'ai_project_analyzer')) { ?>
                        <a href="<?php echo admin_url('ai_project_analyzer/templates/template'); ?>"
                            class="btn btn-primary pull-left display-block">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('ai_project_analyzer_templates_add'); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s tw-mt-4">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('#'),
                            _l('ai_project_analyzer_name'),
                            _l('ai_project_analyzer_generated_by'),
                            _l('ai_project_analyzer_body'),
                            _l('ai_project_analyzer_dategenerated'),
                        ], 'ai_project_analyzer'); ?>
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
        initDataTable('.table-ai_project_analyzer', window.location.href);
    });
</script>
</body>

</html>