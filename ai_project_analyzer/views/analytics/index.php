<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-6 tw-mb-2">
                    <div
                        class="tw-gap-4 tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                        <div
                            class="tw-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-bg-primary-100 tw-text-primary-600 tw-rounded-lg">
                            <i class="fa-solid fa-magnifying-glass tw-text-xl"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-text-neutral-500">
                                <?= _l('ai_project_analyzer_analytics_total_analyses') ?>
                            </p>
                            <p class="tw-text-2xl tw-font-semibold tw-text-neutral-800">
                                <?php echo total_rows(AI_PROJECT_ANALYZER_TABLE); ?>
                            </p>
                        </div>
                    </div>

                    <div
                        class="tw-gap-4 tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                        <div
                            class="tw-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-bg-success-100 tw-text-success-600 tw-rounded-lg">
                            <i class="fa-solid fa-microchip tw-text-xl"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-text-neutral-500">
                                <?= _l('ai_project_analyzer_analytics_tokens_used') ?>
                            </p>
                            <p class="tw-text-2xl tw-font-semibold tw-text-neutral-800">
                                <?php echo number_format(sum_from_table(AI_PROJECT_ANALYZER_TABLE, ['field' => 'tokens_used']) ?? 0); ?>
                            </p>
                        </div>
                    </div>

                    <div
                        class="tw-gap-4 tw-border-neutral-300/80 tw-shadow-sm tw-text-sm tw-border tw-border-solid tw-rounded-lg tw-px-4 tw-py-3 text-sm tw-flex-1 tw-flex tw-items-center tw-font-medium tw-bg-white">
                        <div
                            class="tw-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-bg-danger-100 tw-text-danger-500 tw-rounded-lg">
                            <i class="fa-solid fa-dollar-sign tw-text-xl"></i>
                        </div>
                        <div>
                            <p class="tw-text-sm tw-text-neutral-500">
                                <?= _l('ai_project_analyzer_analytics_total_estimated_cost') ?>
                            </p>
                            <p class="tw-text-2xl tw-font-semibold tw-text-neutral-800">
                                $<?php echo number_format(sum_from_table(AI_PROJECT_ANALYZER_TABLE, ['field' => 'cost_usd']) ?? 0, 4); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s tw-mt-4">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('ai_project_analyzer_analytics_staff'),
                            _l('ai_project_analyzer_analytics_total_analyses'),
                            _l('ai_project_analyzer_analytics_tokens_used'),
                            _l('ai_project_analyzer_analytics_total_estimated_cost'),
                            _l('ai_project_analyzer_analytics_last_generated'),
                        ], 'ai_project_analyzer_staff_analytics'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
<style>
    .tw-h-12 {
        height: 3rem;
    }

    .tw-w-12 {
        width: 3rem;
    }
</style>
<script>
    $(function () {
        initDataTable('.table-ai_project_analyzer_staff_analytics', window.location.href);
    });
</script>
</body>

</html>