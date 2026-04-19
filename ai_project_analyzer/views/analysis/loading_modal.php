<div class="modal fade" id="ai-loading-modal" tabindex="-1" role="dialog" aria-labelledby="ai-loading-modal"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog ai-flex ai-items-center ai-justify-center" style="min-height: 60vh;" role="document">
        <div class="modal-content ai-mx-auto ai-w-full ai-max-w-md ai-rounded-xl">
            <div class="modal-body">
                <div class="ai-flex ai-flex-col ai-items-center ai-justify-center ai-h-52">
                    <div class="ai-relative">
                        <div id="modal-icon-wrapper"
                            class="ai-w-20 ai-h-20 ai-flex ai-items-center ai-justify-center ai-bg-gradient-to-br ai-from-green-400 ai-to-green-600 ai-rounded-full ai-transition-all ai-duration-500">
                            <i id="modal-icon"
                                class="fa-solid fa-wand-magic-sparkles ai-text-white ai-text-3xl ai-animate-wand-hit"></i>
                        </div>
                    </div>
                    <h3 id="modal-title" class="ai-mt-4 ai-text-lg ai-font-semibold ai-text-gray-800 ai-text-center">
                        <?= _l('ai_project_analyzer_analysis_loading_head') ?>
                    </h3>
                    <p id="modal-message" class="ai-text-normal ai-text-gray-500 ai-text-center">
                        <?= _l('ai_project_analyzer_analysis_loading_description') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>