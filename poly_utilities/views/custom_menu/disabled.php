<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div class="tw-flex tw-justify-center tw-items-center tw-min-h-[300px] tw-mt-6">
  <div class="panel_s tw-max-w-3xl tw-w-full">
    <div class="panel-body tw-text-center">
      <div class="tw-text-3xl tw-mb-4">
        <i class="fa-solid fa-circle-info tw-text-sky-600"></i>
      </div>
      <h3 class="tw-text-xl tw-font-semibold tw-mb-3">
        <?php echo _l('poly_custom_menu_disabled_title'); ?>
      </h3>
      <p class="tw-text-neutral-600 tw-mb-4">
        <?php echo _l('poly_custom_menu_disabled_message'); ?>
      </p>
      <a href="<?php echo htmlspecialchars($settings_url); ?>" class="btn btn-primary">
        <?php echo _l('poly_custom_menu_disabled_button'); ?>
      </a>
    </div>
  </div>
</div>
<?php init_tail(); ?>

