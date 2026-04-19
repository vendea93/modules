<div id="faqs" class="flexacademy-tabs-pane">
    <h4 class="tw-text-lg tw-font-bold tw-ml-3"><?php echo _flexacademy_lang('faqs'); ?></h4>
    <?php if (!empty($faqs)): ?>
        <div class="tw-flex tw-flex-col">
            <?php foreach ($faqs as $index => $faq): ?>
                <div id="flexacademy-faq" class="tw-p-2">
                    <button class="btn outline-0 tw-text-lg btn-default btn-block tw-flex tw-justify-between tw-items-center"
                        data-faq="faq-<?php echo $index; ?>">
                        <span class="tw-text-base tw-font-semibold tw-text-gray-800 tw-truncate"><?php echo htmlspecialchars($faq['question']); ?></span>
                        <i class="fa fa-chevron-down tw-text-gray-400 tw-text-sm flexacademy-accordion-icon tw-flex-shrink-0"></i>
                    </button>

                    <div class="flexacademy-faq-content mtop10" id="faq-<?php echo $index; ?>">
                        <div class="tw-pb-4 tw-pr-4">
                            <p class="tw-text-sm tw-text-gray-600 tw-mb-0 tw-leading-relaxed"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="tw-text-center tw-py-6 tw-text-gray-500">
            <i class="fa fa-question-circle tw-text-3xl tw-mb-3 tw-text-gray-300"></i>
            <p class="tw-text-sm tw-mb-0"><?php echo _flexacademy_lang('no-faqs-available'); ?></p>
        </div>
    <?php endif; ?>
</div>