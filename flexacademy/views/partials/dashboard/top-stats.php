<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-3 col-xs-6 tw-mb-2 sm:tw-mb-0">
        <div class="top_stats_wrapper">
            <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                <span class="tw-font-semibold tw-inline-flex tw-items-center">
                    <?php echo _flexacademy_lang('total-courses'); ?>
                </span>
                <i class="fa fa-graduation-cap tw-text-neutral-500"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-semibold tw-text-neutral-800"><?php echo $total_courses; ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-xs-6 tw-mb-2 sm:tw-mb-0">
        <div class="top_stats_wrapper">
            <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                <span class="tw-font-semibold tw-inline-flex tw-items-center">
                    <?php echo _flexacademy_lang('active-courses'); ?>
                </span>
                <i class="fa fa-check-circle tw-text-neutral-500"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-semibold tw-text-neutral-800"><?php echo $total_active_courses; ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-xs-6 tw-mb-2 sm:tw-mb-0">
        <div class="top_stats_wrapper">
            <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                <span class="tw-font-semibold tw-inline-flex tw-items-center">
                    <?php echo _flexacademy_lang('total-enrollments'); ?>
                </span>
                <i class="fa fa-user-check tw-text-neutral-500"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-semibold tw-text-neutral-800"><?php echo $total_enrollments; ?></h3>
        </div>
    </div>
    <div class="col-md-3 col-xs-6 tw-mb-2 sm:tw-mb-0">
        <div class="top_stats_wrapper">
            <div class="tw-text-neutral-800 mtop5 tw-flex tw-items-center tw-justify-between">
                <span class="tw-font-semibold tw-inline-flex tw-items-center">
                    <?php echo _flexacademy_lang('total-certificates'); ?>
                </span>
                <i class="fa fa-certificate tw-text-neutral-500"></i>
            </div>
            <h3 class="tw-text-2xl tw-font-semibold tw-text-neutral-800"><?php echo $total_certificates; ?></h3>
        </div>
    </div>
</div>

