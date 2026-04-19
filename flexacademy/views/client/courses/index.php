<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="flexacademy-courses-page">
<div class="flexacademy-courses-container">

    <?php $this->load->view('flexacademy/partials/clients/courses/mobile-filter-modal', [
        'categories' => $categories,
        'current_category' => $current_category,
        'current_pricing' => $current_pricing,
        'current_search' => $current_search
    ]); ?>

    <div class="flexacademy-courses-layout">
        
        <?php $this->load->view('flexacademy/partials/clients/courses/desktop-sidebar', [
            'categories' => $categories,
            'current_category' => $current_category,
            'current_pricing' => $current_pricing,
            'current_search' => $current_search
        ]); ?>

        <!-- Main Content -->
        <div class="w-100">
            <!-- Mobile Filter Button -->
            <div class="md:tw-hidden mb-3">
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-block"
                    data-toggle="modal"
                    data-target="#flexacademyMobileFilterModal">
                    <i class="fa fa-filter mr-2"></i>
                    <span><?php echo _flexacademy_lang('filters'); ?></span>
                </button>
            </div>

            <!-- Header -->
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                <h1 class="tw-text-xl tw-font-bold"><?php echo _flexacademy_lang('all-courses'); ?></h1>
                
                <!-- View Toggle -->
                <div class="btn-group md:tw-hidden">
                    <button id="flexacademy-grid-view-btn" class="btn btn-outline-secondary active">
                        <i class="fa fa-th"></i>
                    </button>
                    <button id="flexacademy-list-view-btn" class="btn btn-outline-secondary">
                        <i class="fa fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Courses Grid -->
            <div id="flexacademy-courses-grid" class="flexacademy-courses-grid">
                <?php if(!empty($courses)): ?>
                    <?php foreach($courses as $course): ?>
                        <?php $this->load->view('flexacademy/partials/clients/courses/course-card', [
                            'course' => $course,
                            'currency' => $currency
                        ]); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php $this->load->view('flexacademy/partials/clients/courses/empty-state'); ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php $this->load->view('flexacademy/partials/clients/courses/pagination', [
                'total_pages' => $total_pages,
                'current_page' => $current_page,
                'current_category' => $current_category,
                'current_pricing' => $current_pricing,
                'current_search' => $current_search
            ]); ?>
        </div>
    </div>
</div>
</section>

<!-- Modals -->

<!-- Fixed Cart Icon -->
<?php $this->load->view('flexacademy/partials/cart-icon'); ?>
