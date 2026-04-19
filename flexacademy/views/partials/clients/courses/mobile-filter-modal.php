<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Mobile Filter Modal -->
<div
    class="modal fade flexacademy-mobile-filter-modal"
    id="flexacademyMobileFilterModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="flexacademyMobileFilterLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="flexacademyMobileFilterLabel">
                    <i class="fa fa-filter mright5"></i> <?php echo _flexacademy_lang('filters'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="flexacademy-mobile-course-search" class="control-label"><?php echo _flexacademy_lang('search'); ?></label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input
                            type="text"
                            id="flexacademy-mobile-course-search"
                            class="form-control"
                            placeholder="<?php echo _flexacademy_lang('search'); ?>"
                            value="<?php echo htmlspecialchars($current_search ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label text-uppercase small text-muted"><?php echo _flexacademy_lang('categories'); ?></label>
                    <div class="flexacademy-mobile-filter-list list-group">
                        <label class="list-group-item">
                            <input
                                type="radio"
                                name="mobile-category"
                                value=""
                                <?php echo empty($current_category) ? 'checked' : ''; ?>
                                class="flexacademy-mobile-filter-category flexacademy-mobile-filter-input">
                            <span><?php echo _flexacademy_lang('all'); ?></span>
                        </label>

                        <?php foreach ($categories as $category): ?>
                            <?php if (empty($category['parent_id'])): ?>
                                <label class="list-group-item">
                                    <input
                                        type="radio"
                                        name="mobile-category"
                                        value="<?php echo $category['id']; ?>"
                                        <?php echo $current_category == $category['id'] ? 'checked' : ''; ?>
                                        class="flexacademy-mobile-filter-category flexacademy-mobile-filter-input">
                                    <span><?php echo htmlspecialchars($category['title']); ?></span>
                                </label>

                                <?php foreach ($categories as $sub_category): ?>
                                    <?php if ($sub_category['parent_id'] == $category['id']): ?>
                                        <label class="list-group-item flexacademy-mobile-filter-sublabel">
                                            <input
                                                type="radio"
                                                name="mobile-category"
                                                value="<?php echo $sub_category['id']; ?>"
                                                <?php echo $current_category == $sub_category['id'] ? 'checked' : ''; ?>
                                                class="flexacademy-mobile-filter-category flexacademy-mobile-filter-input">
                                            <span><?php echo htmlspecialchars($sub_category['title']); ?></span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label text-uppercase small text-muted"><?php echo _flexacademy_lang('price'); ?></label>
                    <div class="flexacademy-mobile-filter-list list-group">
                        <label class="list-group-item">
                            <input
                                type="radio"
                                name="mobile-pricing"
                                value=""
                                <?php echo empty($current_pricing) ? 'checked' : ''; ?>
                                class="flexacademy-mobile-filter-pricing flexacademy-mobile-filter-input">
                            <span><?php echo _flexacademy_lang('all'); ?></span>
                        </label>
                        <label class="list-group-item">
                            <input
                                type="radio"
                                name="mobile-pricing"
                                value="free"
                                <?php echo $current_pricing === 'free' ? 'checked' : ''; ?>
                                class="flexacademy-mobile-filter-pricing flexacademy-mobile-filter-input">
                            <span><?php echo _flexacademy_lang('free'); ?></span>
                        </label>
                        <label class="list-group-item">
                            <input
                                type="radio"
                                name="mobile-pricing"
                                value="paid"
                                <?php echo $current_pricing === 'paid' ? 'checked' : ''; ?>
                                class="flexacademy-mobile-filter-pricing flexacademy-mobile-filter-input">
                            <span><?php echo _flexacademy_lang('paid'); ?></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
