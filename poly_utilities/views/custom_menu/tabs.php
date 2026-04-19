<div class="horizontal-scrollable-tabs panel-full-width-tabs">
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal">
            <li class="<?php echo ($active == 'sidebar') ? 'active' : '' ?>"><a href="<?php echo admin_url('poly_utilities/custom_menu'); ?>">
                    <?php echo _l('poly_utilities_custom_sidebar_menu_extend'); ?>
                </a></li>
            <li class="<?php echo ($active == 'setup') ? 'active' : '' ?>"><a href="<?php echo admin_url('poly_utilities/custom_menu?menu=setup'); ?>">
                    <?php echo _l('poly_utilities_custom_setup_menu_extend'); ?>
                </a></li>
            <li class="<?php echo ($active == 'clients') ? 'active' : '' ?>"><a href="<?php echo admin_url('poly_utilities/custom_menu?menu=clients'); ?>">
                    <?php echo _l('poly_utilities_custom_clients_menu_extend'); ?>
                </a></li>
        </ul>
    </div>
</div>
<div class="tw-items-center tw-mb-2">
    <div>
        <span class="cursor btn-poly-reset-menu tw-mr-5"><i class="fa-solid fa-rotate-right fa-fw"></i>&nbsp;<span @click.stop="handleResetCustomMenu('sidebar')"><?php echo _l('poly_utilities_custom_menu_reset_sidebar') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_reset_sidebar_help') ?>"></i>
        <span class="cursor btn-poly-reset-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-rotate-right fa-fw"></i>&nbsp;<span @click.stop="handleResetCustomMenu('setup')"><?php echo _l('poly_utilities_custom_menu_reset_setup') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_reset_setup_help') ?>"></i>
        <span class="cursor btn-poly-reset-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-rotate-right fa-fw"></i>&nbsp;<span @click.stop="handleResetCustomMenu('clients')"><?php echo _l('poly_utilities_custom_menu_reset_clients') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_reset_clients_help') ?>"></i>
        <span class="cursor btn-poly-reset-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-rotate-right fa-fw"></i>&nbsp;<span @click.stop="handleResetCustomMenu('all')"><?php echo _l('poly_utilities_custom_menu_reset_all') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_reset_all_help') ?>"></i>
    </div>

    <div><span class="cursor btn-poly-delete-menu tw-mr-5"><i class="fa-solid fa-circle-xmark fa-fw"></i>&nbsp;<span @click.stop="handleDeleteCustomMenu('sidebar')"><?php echo _l('poly_utilities_custom_menu_delete_sidebar') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_delete_sidebar_help') ?>"></i><span class="cursor btn-poly-delete-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-circle-xmark fa-fw"></i>&nbsp;<span @click.stop="handleDeleteCustomMenu('setup')"><?php echo _l('poly_utilities_custom_menu_delete_setup') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_delete_setup_help') ?>"></i><span class="cursor btn-poly-delete-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-circle-xmark fa-fw"></i>&nbsp;<span @click.stop="handleDeleteCustomMenu('clients')"><?php echo _l('poly_utilities_custom_menu_delete_clients') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_delete_clients_help') ?>"></i><span class="cursor btn-poly-delete-menu tw-ml-5 tw-mr-5"><i class="fa-solid fa-circle-xmark fa-fw"></i>&nbsp;<span @click.stop="handleDeleteCustomMenu('all')"><?php echo _l('poly_utilities_custom_menu_delete_all') ?></span></span><i class="fa-regular fa-circle-question tw-ml-1 hidden-xs" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_custom_menu_delete_all_help') ?>"></i></div>
</div>

<div class="tw-mb-2 tw-mt-2"><i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1"></i><?php echo _l('poly_utilities_custom_menu_route_message_help_1') ?> "<span class="cursor btn-poly-flush-rewrite-url"><i class="fa-solid fa-rotate-right fa-fw"></i>&nbsp;<span @click.stop="handleReactivationModule()"><?php echo _l('poly_utilities_custom_menu_route_message_help_2') ?></span></span>" <?php echo _l('poly_utilities_custom_menu_route_message_help_3') ?></div>
<hr>