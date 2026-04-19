<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Desktop Sidebar -->
<div class="flexacademy-desktop-sidebar hidden-sm hidden-xs">
    <aside class="panel_s">
        <div class="panel-body flexacademy-filter-card">
            <div class="form-group">
                <label for="flexacademy-course-search" class="control-label text-uppercase small text-muted">
                    <?php echo _flexacademy_lang('search'); ?>
                </label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input
                        type="text"
                        id="flexacademy-course-search"
                        class="form-control"
                        placeholder="<?php echo _flexacademy_lang('search'); ?>"
                        value="<?php echo htmlspecialchars($current_search ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label text-uppercase small text-muted">
                    <?php echo _flexacademy_lang('categories'); ?>
                </label>
                <div class="flexacademy-filter-list list-group">
                    <label class="list-group-item">
                        <input
                            type="radio"
                            name="category"
                            value=""
                            <?php echo empty($current_category) ? 'checked' : ''; ?>
                            class="flexacademy-filter-category flexacademy-filter-input">
                        <span><?php echo _flexacademy_lang('all'); ?></span>
                    </label>

                    <?php foreach ($categories as $category): ?>
                        <?php if (empty($category['parent_id'])): ?>
                            <label class="list-group-item">
                                <input
                                    type="radio"
                                    name="category"
                                    value="<?php echo $category['id']; ?>"
                                    <?php echo $current_category == $category['id'] ? 'checked' : ''; ?>
                                    class="flexacademy-filter-category flexacademy-filter-input">
                                <span><?php echo htmlspecialchars($category['title']); ?></span>
                            </label>

                            <?php foreach ($categories as $sub_category): ?>
                                <?php if ($sub_category['parent_id'] == $category['id']): ?>
                                    <label class="list-group-item flexacademy-filter-sublabel">
                                        <input
                                            type="radio"
                                            name="category"
                                            value="<?php echo $sub_category['id']; ?>"
                                            <?php echo $current_category == $sub_category['id'] ? 'checked' : ''; ?>
                                            class="flexacademy-filter-category flexacademy-filter-input">
                                        <span><?php echo htmlspecialchars($sub_category['title']); ?></span>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label text-uppercase small text-muted">
                    <?php echo _flexacademy_lang('price'); ?>
                </label>
                <div class="flexacademy-filter-list list-group">
                    <label class="list-group-item">
                        <input
                            type="radio"
                            name="pricing"
                            value=""
                            <?php echo empty($current_pricing) ? 'checked' : ''; ?>
                            class="flexacademy-filter-pricing flexacademy-filter-input">
                        <span><?php echo _flexacademy_lang('all'); ?></span>
                    </label>
                    <label class="list-group-item">
                        <input
                            type="radio"
                            name="pricing"
                            value="free"
                            <?php echo $current_pricing === 'free' ? 'checked' : ''; ?>
                            class="flexacademy-filter-pricing flexacademy-filter-input">
                        <span><?php echo _flexacademy_lang('free'); ?></span>
                    </label>
                    <label class="list-group-item">
                        <input
                            type="radio"
                            name="pricing"
                            value="paid"
                            <?php echo $current_pricing === 'paid' ? 'checked' : ''; ?>
                            class="flexacademy-filter-pricing flexacademy-filter-input">
                        <span><?php echo _flexacademy_lang('paid'); ?></span>
                    </label>
                </div>
            </div>
        </div>
    </aside>
</div>

