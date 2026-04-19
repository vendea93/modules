<?php

$parsedown = new Parsedown();
$data = get_analyses_with_pagination($project->id);

if (!empty($data['ai_analyses'])) { ?>
    <button
        class="ai-inline-flex ai-items-center ai-gap-2 ai-rounded-lg ai-bg-indigo-600 ai-px-4 ai-py-2 ai-font-medium ai-text-white ai-shadow hover:ai-bg-indigo-700 focus-visible:ai-outline-none focus-visible:ai-ring-2 focus-visible:ai-ring-indigo-500 focus-visible:ai-ring-offset-2"
        data-toggle="modal" data-target="#generateModal">
        <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-6 ai-w-6 ai-fill-current ai-text-white" fill="current"
            viewBox="0 0 256 256">
            <path
                d="M48,64a8,8,0,0,1,8-8H72V40a8,8,0,0,1,16,0V56h16a8,8,0,0,1,0,16H88V88a8,8,0,0,1-16,0V72H56A8,8,0,0,1,48,64ZM184,192h-8v-8a8,8,0,0,0-16,0v8h-8a8,8,0,0,0,0,16h8v8a8,8,0,0,0,16,0v-8h8a8,8,0,0,0,0-16Zm56-48H224V128a8,8,0,0,0-16,0v16H192a8,8,0,0,0,0,16h16v16a8,8,0,0,0,16,0V160h16a8,8,0,0,0,0-16ZM219.31,80,80,219.31a16,16,0,0,1-22.62,0L36.68,198.63a16,16,0,0,1,0-22.63L176,36.69a16,16,0,0,1,22.63,0l20.68,20.68A16,16,0,0,1,219.31,80Zm-54.63,32L144,91.31l-96,96L68.68,208ZM208,68.69,187.31,48l-32,32L176,100.69Z">
            </path>
        </svg>
        <?= _l('ai_project_analyzer_generate') ?>
    </button>
    <div id="analysis_grid" class="ai-space-y-6 ai-mt-4">
        <?php foreach ($data['ai_analyses'] as $ai_analysis) { ?>
            <div
                class="ai-rounded-2xl ai-bg-white ai-shadow-lg ai-ring-1 ai-ring-slate-200 ai-overflow-hidden ai-flex ai-flex-col">
                <div class="ai-h-2 ai-bg-gradient-to-r ai-from-indigo-500 ai-via-violet-500 ai-to-fuchsia-500"></div>
                <div class="ai-p-8 ai-flex ai-flex-col ai-grow">
                    <div class="ai-flex ai-flex-col ai-gap-4 md:ai-flex-row md:ai-justify-between md:ai-items-start">
                        <div>
                            <h2 class="ai-text-2xl ai-font-semibold ai-text-slate-800 ai-flex ai-items-center ai-gap-3">
                                <?= $ai_analysis->prompt_name ?>
                                <?php if ($ai_analysis->status == 'processing') { ?>
                                    <span
                                        class="ai-ml-2 ai-bg-yellow-100 ai-text-yellow-800 ai-text-xs ai-font-semibold ai-px-2.5 ai-py-1 ai-rounded-full ai-flex ai-items-center"
                                        data-title="<?= _l('ai_project_analyzer_analysis_processing_head') ?>"
                                        data-toggle="tooltip">
                                        <i class="fa-regular fa-clock ai-mr-1"></i> <?= _l('ai_project_analyzer_queued') ?>
                                    </span>
                                <?php } ?>
                            </h2>
                            <p class="ai-mt-1 ai-text-sm ai-text-slate-500">
                                <?= _l('ai_project_analyzer_generated_by') ?>
                                <a href="<?= admin_url('profile/' . $ai_analysis->owner) ?>"
                                    class="ai-font-medium ai-text-indigo-600">
                                    <?= get_staff_full_name($ai_analysis->owner) ?>
                                </a> ·
                                <time datetime="<?= $ai_analysis->created_at ?>"><?= $ai_analysis->created_at ?></time>
                            </p>
                        </div>

                        <div class="ai-flex ai-flex-col ai-gap-2 sm:ai-flex-row sm:ai-gap-3">
                            <div
                                class="ai-inline-flex ai-mx-auto ai-overflow-hidden ai-rounded-lg ai-bg-slate-50 ai-divide-x ai-divide-solid ai-divide-slate-200 ai-ring-1 ai-ring-slate-200 ai-text-xs ai-font-medium ai-text-slate-700">
                                <?php if ($ai_analysis->language) { ?>
                                    <span class="ai-px-2.5 ai-py-1 ai-flex ai-items-center ai-gap-1"
                                        data-title="<?= _l('language') ?>" data-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="ai-h-4 ai-w-4 ai-text-slate-400 ai-fill-current" viewBox="0 0 256 256">
                                            <path
                                                d="M128,24h0A104,104,0,1,0,232,128,104.12,104.12,0,0,0,128,24Zm88,104a87.61,87.61,0,0,1-3.33,24H174.16a157.44,157.44,0,0,0,0-48h38.51A87.61,87.61,0,0,1,216,128ZM102,168H154a115.11,115.11,0,0,1-26,45A115.27,115.27,0,0,1,102,168Zm-3.9-16a140.84,140.84,0,0,1,0-48h59.88a140.84,140.84,0,0,1,0,48ZM40,128a87.61,87.61,0,0,1,3.33-24H81.84a157.44,157.44,0,0,0,0,48H43.33A87.61,87.61,0,0,1,40,128ZM154,88H102a115.11,115.11,0,0,1,26-45A115.27,115.27,0,0,1,154,88Zm52.33,0H170.71a135.28,135.28,0,0,0-22.3-45.6A88.29,88.29,0,0,1,206.37,88ZM107.59,42.4A135.28,135.28,0,0,0,85.29,88H49.63A88.29,88.29,0,0,1,107.59,42.4ZM49.63,168H85.29a135.28,135.28,0,0,0,22.3,45.6A88.29,88.29,0,0,1,49.63,168Zm98.78,45.6a135.28,135.28,0,0,0,22.3-45.6h35.66A88.29,88.29,0,0,1,148.41,213.6Z">
                                            </path>
                                        </svg>
                                        <?= $ai_analysis->language ?>
                                    </span>
                                <?php } ?>
                                <?php if ($ai_analysis->tone && $ai_analysis->tone !== 'default') { ?>
                                    <span class="ai-px-2.5 ai-py-1 ai-bg-slate-50 ai-flex ai-items-center ai-gap-1"
                                        data-title="<?= _l('ai_project_analyzer_analysis_tone') ?>" data-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="ai-h-4 ai-w-4 ai-text-slate-400 ai-fill-current" fill="#000000"
                                            viewBox="0 0 256 256">
                                            <path
                                                d="M248,124a56.11,56.11,0,0,0-32-50.61V72a48,48,0,0,0-88-26.49A48,48,0,0,0,40,72v1.39a56,56,0,0,0,0,101.2V176a48,48,0,0,0,88,26.49A48,48,0,0,0,216,176v-1.41A56.09,56.09,0,0,0,248,124ZM88,208a32,32,0,0,1-31.81-28.56A55.87,55.87,0,0,0,64,180h8a8,8,0,0,0,0-16H64A40,40,0,0,1,50.67,86.27,8,8,0,0,0,56,78.73V72a32,32,0,0,1,64,0v68.26A47.8,47.8,0,0,0,88,128a8,8,0,0,0,0,16,32,32,0,0,1,0,64Zm104-44h-8a8,8,0,0,0,0,16h8a55.87,55.87,0,0,0,7.81-.56A32,32,0,1,1,168,144a8,8,0,0,0,0-16,47.8,47.8,0,0,0-32,12.26V72a32,32,0,0,1,64,0v6.73a8,8,0,0,0,5.33,7.54A40,40,0,0,1,192,164Zm16-52a8,8,0,0,1-8,8h-4a36,36,0,0,1-36-36V80a8,8,0,0,1,16,0v4a20,20,0,0,0,20,20h4A8,8,0,0,1,208,112ZM60,120H56a8,8,0,0,1,0-16h4A20,20,0,0,0,80,84V80a8,8,0,0,1,16,0v4A36,36,0,0,1,60,120Z">
                                            </path>
                                        </svg>
                                        <?= ucfirst($ai_analysis->tone) ?>
                                    </span>
                                <?php } ?>
                            </div>

                            <?php if (staff_can('view_analytics', 'ai_project_analyzer')) { ?>
                                <div
                                    class="ai-inline-flex ai-mx-auto ai-overflow-hidden ai-rounded-lg ai-bg-slate-50 ai-divide-x ai-divide-solid ai-divide-slate-200 ai-ring-1 ai-ring-slate-200 ai-text-xs ai-font-medium ai-text-slate-700">
                                    <?php if ($ai_analysis->model) { ?>
                                        <span class="ai-px-2.5 ai-py-1 ai-flex ai-items-center ai-gap-1"
                                            data-title="<?= _l('ai_project_analyzer_model') ?>" data-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="ai-h-4 ai-w-4 ai-text-slate-400 ai-fill-current" fill="#000000"
                                                viewBox="0 0 256 256">
                                                <path
                                                    d="M200,48H136V16a8,8,0,0,0-16,0V48H56A32,32,0,0,0,24,80V192a32,32,0,0,0,32,32H200a32,32,0,0,0,32-32V80A32,32,0,0,0,200,48Zm16,144a16,16,0,0,1-16,16H56a16,16,0,0,1-16-16V80A16,16,0,0,1,56,64H200a16,16,0,0,1,16,16Zm-52-56H92a28,28,0,0,0,0,56h72a28,28,0,0,0,0-56Zm-24,16v24H116V152ZM80,164a12,12,0,0,1,12-12h8v24H92A12,12,0,0,1,80,164Zm84,12h-8V152h8a12,12,0,0,1,0,24ZM72,108a12,12,0,1,1,12,12A12,12,0,0,1,72,108Zm88,0a12,12,0,1,1,12,12A12,12,0,0,1,160,108Z">
                                                </path>
                                            </svg>
                                            <?= get_model_name_from_id($ai_analysis->model) ?>
                                        </span>
                                    <?php } ?>
                                    <?php if ($ai_analysis->tokens_used) { ?>
                                        <span class="ai-px-2.5 ai-py-1 ai-bg-slate-50 ai-flex ai-items-center ai-gap-1"
                                            data-title="<?= _l('ai_project_analyzer_analytics_tokens_used') ?>" data-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="ai-h-4 ai-w-4 ai-text-slate-400 ai-fill-current" fill="#000000"
                                                viewBox="0 0 256 256">
                                                <path
                                                    d="M184,89.57V84c0-25.08-37.83-44-88-44S8,58.92,8,84v40c0,20.89,26.25,37.49,64,42.46V172c0,25.08,37.83,44,88,44s88-18.92,88-44V132C248,111.3,222.58,94.68,184,89.57ZM232,132c0,13.22-30.79,28-72,28-3.73,0-7.43-.13-11.08-.37C170.49,151.77,184,139,184,124V105.74C213.87,110.19,232,122.27,232,132ZM72,150.25V126.46A183.74,183.74,0,0,0,96,128a183.74,183.74,0,0,0,24-1.54v23.79A163,163,0,0,1,96,152,163,163,0,0,1,72,150.25Zm96-40.32V124c0,8.39-12.41,17.4-32,22.87V123.5C148.91,120.37,159.84,115.71,168,109.93ZM96,56c41.21,0,72,14.78,72,28s-30.79,28-72,28S24,97.22,24,84,54.79,56,96,56ZM24,124V109.93c8.16,5.78,19.09,10.44,32,13.57v23.37C36.41,141.4,24,132.39,24,124Zm64,48v-4.17c2.63.1,5.29.17,8,.17,3.88,0,7.67-.13,11.39-.35A121.92,121.92,0,0,0,120,171.41v23.46C100.41,189.4,88,180.39,88,172Zm48,26.25V174.4a179.48,179.48,0,0,0,24,1.6,183.74,183.74,0,0,0,24-1.54v23.79a165.45,165.45,0,0,1-48,0Zm64-3.38V171.5c12.91-3.13,23.84-7.79,32-13.57V172C232,180.39,219.59,189.4,200,194.87Z">
                                                </path>
                                            </svg>
                                            <?= number_format((int) $ai_analysis->tokens_used) ?>
                                        </span>
                                    <?php } ?>
                                    <?php if ($ai_analysis->cost_usd && $ai_analysis->cost_usd > '0.000000') { ?>
                                        <span class="ai-px-2.5 ai-py-1 ai-bg-slate-50 ai-flex ai-items-center ai-gap-1"
                                            data-title="<?= _l('ai_project_analyzer_analytics_estimated_cost') ?>"
                                            data-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="ai-h-4 ai-w-4 ai-text-slate-400 ai-fill-current" fill="#000000"
                                                viewBox="0 0 256 256">
                                                <path
                                                    d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm40-68a28,28,0,0,1-28,28h-4v8a8,8,0,0,1-16,0v-8H104a8,8,0,0,1,0-16h36a12,12,0,0,0,0-24H116a28,28,0,0,1,0-56h4V72a8,8,0,0,1,16,0v8h16a8,8,0,0,1,0,16H116a12,12,0,0,0,0,24h24A28,28,0,0,1,168,148Z">
                                                </path>
                                            </svg>
                                            $<?= number_format((float) $ai_analysis->cost_usd, 4) ?>
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ai-mt-6 ai-leading-relaxed ai-text-slate-700">
                        <?= $ai_analysis->status == 'processing' ? _l('ai_project_analyzer_analysis_processing') : $ai_analysis->analysis; ?>
                    </div>

                    <?php if ($ai_analysis->attachment) { ?>
                        <div
                            class="ai-mt-6 ai-p-2 ai-flex ai-items-center ai-gap-2 ai-text-sm ai-text-slate-500 ai-w-full ai-rounded-lg ai-ring-1 ai-ring-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-5 ai-w-5 ai-fill-current" fill="#000000"
                                viewBox="0 0 256 256">
                                <path
                                    d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Zm-32-80a8,8,0,0,1-8,8H96a8,8,0,0,1,0-16h64A8,8,0,0,1,168,136Zm0,32a8,8,0,0,1-8,8H96a8,8,0,0,1,0-16h64A8,8,0,0,1,168,168Z">
                                </path>
                            </svg>
                            <?= _l('ai_project_analyzer_file_analyzed') ?>
                            <a href="<?= site_url('uploads/projects/' . $project->id . '/analysis/' . $ai_analysis->attachment) ?>"
                                target="_blank" class="ai-text-slate-800 hover:ai-underline hover:ai-text-indigo-600">
                                <?= $ai_analysis->attachment_label ?>
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (staff_can('delete', 'ai_project_analyzer') || staff_can('download', 'ai_project_analyzer') || staff_can('send_to_email', 'ai_project_analyzer')) { ?>
                        <div class="ai-mt-4 ai-flex ai-flex-col sm:ai-flex-row sm:ai-justify-between ai-gap-4">
                            <?php if ($ai_analysis->status === 'generated') { ?>
                                <div class="ai-flex ai-items-center ai-gap-3">
                                    <?php if (staff_can('download', 'ai_project_analyzer')) { ?>
                                        <?= form_open(admin_url('ai_project_analyzer/download_pdf')); ?>
                                        <input type="hidden" name="analysis_id" value="<?= $ai_analysis->id ?>" />
                                        <button type="submit"
                                            class="ai-inline-flex ai-items-center ai-gap-2 ai-rounded-lg ai-bg-indigo-600 ai-px-4 ai-py-2 ai-font-medium ai-text-white ai-shadow hover:ai-bg-indigo-700 focus-visible:ai-outline-none focus-visible:ai-ring-2 focus-visible:ai-ring-indigo-500 focus-visible:ai-ring-offset-2"
                                            data-title="<?= _l('ai_project_analyzer_download_pdf') ?>" data-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-5 ai-w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>
                                            </svg>
                                            <?= _l('download') ?>
                                        </button>
                                        <?= form_close(); ?>
                                    <?php } ?>

                                    <?php if (staff_can('send_to_email', 'ai_project_analyzer')) { ?>
                                        <button
                                            class="ai-inline-flex ai-items-center ai-gap-2 ai-rounded-lg ai-bg-slate-100 ai-px-4 ai-py-2 ai-font-medium ai-text-slate-700 ai-shadow hover:ai-bg-slate-200 focus-visible:ai-outline-none focus-visible:ai-ring-2 focus-visible:ai-ring-slate-300 focus-visible:ai-ring-offset-2"
                                            data-toggle="modal" data-target="#sendToEmail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-5 ai-w-5 ai-text-slate-700 ai-fill-current"
                                                fill="current" viewBox="0 0 256 256">
                                                <path
                                                    d="M128,24a104,104,0,0,0,0,208c21.51,0,44.1-6.48,60.43-17.33a8,8,0,0,0-8.86-13.33C166,210.38,146.21,216,128,216a88,88,0,1,1,88-88c0,26.45-10.88,32-20,32s-20-5.55-20-32V88a8,8,0,0,0-16,0v4.26a48,48,0,1,0,5.93,65.1c6,12,16.35,18.64,30.07,18.64,22.54,0,36-17.94,36-48A104.11,104.11,0,0,0,128,24Zm0,136a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z">
                                                </path>
                                            </svg>
                                            <?= _l('send_to_email') ?>
                                        </button>
                                        <?php $this->load->view('ai_project_analyzer/analysis/send_to_email', [
                                            'analysis_id' => $ai_analysis->id,
                                            'project_id' => $project->id
                                        ]); ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <?php if (staff_can('delete', 'ai_project_analyzer')) { ?>
                                <?= form_open(admin_url('ai_project_analyzer/delete')); ?>
                                <input type="hidden" name="project_id" value="<?= $project->id ?>" />
                                <input type="hidden" name="analysis_id" value="<?= $ai_analysis->id ?>" />
                                <button type="submit" onclick="return confirm('<?= _l('ai_project_analyzer_delete_confirmation') ?>')"
                                    class="ai-inline-flex ai-items-center ai-gap-2 ai-self-start sm:ai-self-auto ai-rounded-lg ai-bg-rose-50 ai-px-4 ai-py-2 ai-font-medium ai-text-rose-600 ai-shadow hover:ai-bg-rose-100 focus-visible:ai-outline-none focus-visible:ai-ring-2 focus-visible:ai-ring-rose-400 focus-visible:ai-ring-offset-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-5 ai-w-5 ai-text-rose-600 ai-fill-current"
                                        viewBox="0 0 256 256">
                                        <path
                                            d="M216,48H176V40a24,24,0,0,0-24-24H104A24,24,0,0,0,80,40v8H40a8,8,0,0,0,0,16h8V208a16,16,0,0,0,16,16H192a16,16,0,0,0,16-16V64h8a8,8,0,0,0,0-16ZM96,40a8,8,0,0,1,8-8h48a8,8,0,0,1,8,8v8H96Zm96,168H64V64H192ZM112,104v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Zm48,0v64a8,8,0,0,1-16,0V104a8,8,0,0,1,16,0Z">
                                        </path>
                                    </svg>
                                    <?= _l('delete') ?>
                                </button>
                                <?= form_close(); ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($data['pagination_links'])) { ?>
            <div class="ai-mt-6 ai-flex ai-justify-center">
                <?= $data['pagination_links'] ?>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div class="ai-bg-white ai-rounded-xl ai-shadow-md ai-border ai-border-gray-100 ai-overflow-hidden">
        <div class="ai-p-8 ai-flex ai-flex-col ai-items-center ai-justify-center">
            <div class="ai-bg-empty-illustration ai-bg-no-repeat ai-bg-center ai-h-[300px] ai-w-full"></div>

            <h2 class="ai-text-xl ai-font-semibold ai-text-gray-800 ai-mt-4">
                <?= _l('ai_project_analyzer_no_analysis_found_heading') ?>
            </h2>
            <p class="ai-text-gray-500 ai-text-sm ai-text-center ai-max-w-md ai-mt-2 ai-mb-6">
                <?= _l('ai_project_analyzer_no_analysis_found_desc') ?>
            </p>

            <?php if (staff_can('create', 'ai_project_analyzer')) { ?>
                <div class="ai-flex ai-flex-col sm:ai-flex-row ai-gap-4">
                    <button
                        class="ai-inline-flex ai-items-center ai-gap-2 ai-rounded-lg ai-bg-indigo-600 ai-px-4 ai-py-2 ai-font-medium ai-text-white ai-shadow hover:ai-bg-indigo-700 focus-visible:ai-outline-none focus-visible:ai-ring-2 focus-visible:ai-ring-indigo-500 focus-visible:ai-ring-offset-2"
                        data-toggle="modal" data-target="#generateModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="ai-h-6 ai-w-6 ai-fill-current ai-text-white"
                            fill="current" viewBox="0 0 256 256">
                            <path
                                d="M48,64a8,8,0,0,1,8-8H72V40a8,8,0,0,1,16,0V56h16a8,8,0,0,1,0,16H88V88a8,8,0,0,1-16,0V72H56A8,8,0,0,1,48,64ZM184,192h-8v-8a8,8,0,0,0-16,0v8h-8a8,8,0,0,0,0,16h8v8a8,8,0,0,0,16,0v-8h8a8,8,0,0,0,0-16Zm56-48H224V128a8,8,0,0,0-16,0v16H192a8,8,0,0,0,0,16h16v16a8,8,0,0,0,16,0V160h16a8,8,0,0,0,0-16ZM219.31,80,80,219.31a16,16,0,0,1-22.62,0L36.68,198.63a16,16,0,0,1,0-22.63L176,36.69a16,16,0,0,1,22.63,0l20.68,20.68A16,16,0,0,1,219.31,80Zm-54.63,32L144,91.31l-96,96L68.68,208ZM208,68.69,187.31,48l-32,32L176,100.69Z">
                            </path>
                        </svg>
                        <?= _l('ai_project_analyzer_generate') ?>
                    </button>
                </div>
            <?php } ?>
        </div>
    </div>
<?php }