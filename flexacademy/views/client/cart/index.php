<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="tw-bg-gray-50 tw-min-h-screen">
<div class="tw-container tw-py-8 tw-px-4">
    <?php if (!empty($cart_items)): ?>
        <div class="row">
        <p class="tw-mb-4 tw-text-xl tw-font-bold ml-3 md:tw-mb-6"><?php echo _flexacademy_lang('cart-items'); ?></p>
            <!-- Cart Items -->
            <div class="col-lg-8 col-md-7 tw-mb-6"> 
                <div class="tw-space-y-3 md:tw-space-y-4">
                    <?php foreach($cart_items as $course_id => $item): ?>
                        <?php 
                        $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                        $course_url = site_url('flexacademy/course/details/' . $item['slug']);
                        ?>
                        <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm tw-border tw-border-gray-100 hover:tw-shadow-md tw-transition-shadow">
                            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3 md:tw-gap-4">
                                <!-- Course Image - Clickable -->
                                <a href="<?php echo $course_url; ?>" class="flexacademy-cart-course-image">
                                    <img src="<?php echo flexacademy_media_url($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="tw-w-full tw-h-full tw-object-cover">
                                </a>
                                
                                <!-- Course Details -->
                                <div class="tw-flex-1 tw-min-w-0">
                                    <!-- Course Title - Clickable -->
                                    <a class="btn-link tw-text-lg flexacademy-text-truncate tw-font-bold mbot10" href="<?php echo $course_url; ?>">
                                        <?php echo $item['title']; ?>
                                    </a>
                                    <p class="small tw-text-gray-600 tw-leading-relaxed tw-mb-2 md:tw-mb-3 tw-hidden sm:tw-block">
                                        <?php 
                                        echo flexacademy_truncate($item['description'], 120);
                                        ?>
                                    </p>
                                    
                                    <div class="tw-flex tw-items-center tw-justify-between sm:tw-justify-start tw-gap-2">
                                        <div class="tw-flex tw-text-lg tw-items-center tw-gap-2 tw-flex-wrap">
                                            <span class="tw-text-gray-900 tw-font-bold">  
                                                <?php echo $currency->symbol . number_format($price, 2); ?>
                                            </span>
                                            <?php if ($item['discount_price'] > 0 && $item['discount_price'] < $item['price']): ?>
                                                <span class="text-muted tw-text-base tw-line-through">
                                                    <?php echo $currency->symbol . number_format($item['price'], 2); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Remove Button - Mobile -->
                                        <button id="flexacademy-remove-from-cart-btn" class="btn btn-icon btn-link tw-block md:tw-hidden tw-text-gray-400 hover:tw-text-red-500 tw-transition-colors" 
                                                data-course-id="<?php echo $course_id; ?>"
                                                data-confirm-message="<?php echo _flexacademy_lang('confirm-remove-from-cart'); ?>"
                                                title="<?php echo _flexacademy_lang('remove-from-cart'); ?>">
                                            <i class="fa-trash text-danger fa-solid tw-text-base"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Remove Button - Desktop -->
                                <button id="flexacademy-remove-from-cart-btn" class="btn btn-icon btn-link tw-hidden md:tw-block tw-text-gray-400 hover:tw-text-red-500 tw-transition-colors tw-flex-shrink-0 tw-self-start" 
                                        data-course-id="<?php echo $course_id; ?>"
                                        data-confirm-message="<?php echo _flexacademy_lang('confirm-remove-from-cart'); ?>"
                                        title="<?php echo _flexacademy_lang('remove-from-cart'); ?>">
                                    <i class="fa-trash fa-solid tw-text-lg text-danger"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="col-lg-4 col-md-5">
                <div class="tw-bg-white tw-rounded-lg tw-p-4 sm:tw-p-6 tw-shadow-md tw-border tw-border-gray-100 lg:tw-sticky lg:tw-top-8">
                    <h2 class="tw-text-lg tw-font-bold tw-mb-4 sm:tw-mb-6 tw-text-gray-900"><?php echo _flexacademy_lang('payment-summary'); ?></h2>
                    
                    <div class="tw-space-y-2.5 sm:tw-space-y-3 tw-mb-4 sm:tw-mb-6">
                        <!-- Sub total -->
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <span class="tw-text-gray-600"><?php echo _flexacademy_lang('sub-total'); ?></span>
                            <span class="tw-text-gray-900"><?php echo number_format($cart_total, 2); ?> <?php echo $currency->symbol; ?></span>
                        </div>
                        
                        <!-- Total -->
                        <div class="tw-border-t tw-text-lg tw-border-gray-200 tw-pt-2.5 sm:tw-pt-3 tw-mt-2 sm:tw-mt-3">
                            <div class="tw-flex tw-justify-between tw-items-center">
                                <span class="tw-text-gray-900 tw-font-bold"><?php echo _flexacademy_lang('total'); ?></span>
                                <span class="tw-text-gray-900 tw-font-bold"><?php echo number_format($cart_total, 2); ?> <?php echo $currency->symbol; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkout Notice -->
                    <?php if ($cart_total > 0): ?>
                        <div class="alert alert-info tw-mb-4 tw-text-sm">
                            <i class="fa fa-info-circle"></i> 
                            <?php echo _flexacademy_lang('checkout-creates-invoice-message'); ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success tw-mb-4 tw-text-sm">
                            <i class="fa fa-check-circle"></i> 
                            <?php echo _flexacademy_lang('free-courses-checkout-message'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Checkout Button -->
                    <button id="flexacademy-checkout-btn" 
                            class="btn btn-primary btn-block btn-lg flexacademy-btn-primary" 
                            data-processing-text="<?php echo _flexacademy_lang('processing-checkout'); ?>"
                            data-error-try-again="<?php echo _flexacademy_lang('error-occurred-try-again'); ?>">
                        <i class="fa fa-shopping-cart"></i> 
                        <?php echo _flexacademy_lang('checkout'); ?>
                    </button>
                    
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-3 tw-text-center">
                        <i class="fa fa-lock"></i> <?php echo _flexacademy_lang('secure-checkout'); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="tw-text-center tw-py-12 md:tw-py-16">
            <div class="tw-w-20 tw-h-20 md:tw-w-24 md:tw-h-24 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 md:tw-mb-6">
                <i class="fa-shopping-cart fa-solid tw-text-3xl md:tw-text-4xl tw-text-gray-400"></i>
            </div>
            <h3 class="tw-font-bold tw-text-xl md:tw-text-2xl tw-mb-2"><?php echo _flexacademy_lang('your-cart-is-empty'); ?></h3>
            <p class="tw-text-gray-600 tw-text-sm md:tw-text-base tw-mb-6"><?php echo _flexacademy_lang('add-courses-to-get-started'); ?></p>
            <a href="<?php echo site_url('flexacademy/courses'); ?>" 
               class="btn btn-primary flexacademy-btn-primary btn-lg tw-px-5 md:tw-px-6 tw-py-2.5 md:tw-py-3">
                <?php echo _flexacademy_lang('browse-courses'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
</section>

<!-- Modals -->

