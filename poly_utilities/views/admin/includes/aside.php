<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<aside id="menu" class="sidebar">
    <?php $isSidebarDark = function_exists('is_admin_sidebar_background_light') ?
            is_admin_sidebar_background_light() :
            false; ?>
    <div class="dropdown sidebar-user-profile tw-mt-[80px] tw-mx-1.5 ">
        <a href="#"
            class="dropdown-toggle profile -tw-mt-1 tw-font-medium tw-border tw-border-solid tw-rounded-lg tw-bg-white tw-py-2 tw-px-2.5 tw-block tw-shadow-xs <?= $isSidebarDark ? 'tw-text-white tw-border-white/10 hover:tw-border-white/30 focus:tw-border-white/30 hover:tw-text-white focus:tw-text-white hover:tw-bg-neutral-900/10 focus:tw-bg-neutral-900/10' : 'tw-border-neutral-300 tw-text-neutral-700 hover:tw-text-neutral-800 focus:tw-text-neutral-800 hover:tw-bg-neutral-900/5 focus:tw-bg-neutral-900/5'; ?>"
            data-toggle="dropdown" aria-expanded="false">
            <span class="tw-inline-flex tw-items-center tw-gap-x-3 tw-pt-0.5">
                <?= staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-small']); ?>
                <span>
                    <span
                        class="tw-truncate tw-block tw-w-[140px] tw-font-semibold"><?= get_staff_full_name(); ?></span>
                    <span
                        class="tw-font-normal tw-truncate tw-block tw-w-[140px] tw-text-sm <?= $isSidebarDark ? 'tw-text-neutral-300' : 'tw-text-neutral-500' ?>">
                        <?= get_staff()->email; ?>
                    </span>
                </span>
            </span>
        </a>
        <ul class="dropdown-menu tw-w-full">
            <li class="header-my-profile"><a
                    href="<?= admin_url('profile'); ?>"><?= _l('nav_my_profile'); ?></a>
            </li>
            <li class="header-my-timesheets"><a
                    href="<?= admin_url('staff/timesheets'); ?>"><?= _l('my_timesheets'); ?></a>
            </li>
            <li class="header-edit-profile"><a
                    href="<?= admin_url('staff/edit_profile'); ?>"><?= _l('nav_edit_profile'); ?>
                </a>
            </li>
            <?php if (! is_language_disabled()) { ?>
            <li class="dropdown-submenu pull-left header-languages">
                <a href="#"
                    tabindex="-1"><?= _l('language'); ?></a>
                <ul class="dropdown-menu dropdown-menu">
                    <li
                        class="<?= $current_user->default_language == '' ? 'active' : ''; ?>">
                        <a
                            href="<?= admin_url('staff/change_language'); ?>">
                            <?= _l('system_default_string'); ?>
                        </a>
                    </li>
                    <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                    <li
                        class="<?= $current_user->default_language == $user_lang ? 'active' : ''; ?>">
                        <a
                            href="<?= admin_url('staff/change_language/' . $user_lang); ?>">
                            <?= e(ucfirst($user_lang)); ?>
                        </a>
                        <?php } ?>
                </ul>
            </li>
            <?php } ?>
            <li class="header-logout">
                <a href="#"
                    onclick="logout(); return false;"><?= _l('nav_logout'); ?></a>
            </li>
        </ul>
    </div>
    <ul class="nav metis-menu tw-mt-[15px]" id="side-menu">

        <?php
 hooks()->do_action('before_render_aside_menu');

//  Get ALL menus from database (system + custom merged, all have IDs)
// This ensures parent_id relationships work correctly
$all_menus = poly_get_all_menus_from_db('sidebar', 3);

// Check if user has access to PolyUtilities module
// If users_access is configured and user is not in the list, hide Poly Utilities menu entirely
// Ensure helper is loaded before calling function
if (!function_exists('staff_can_poly_utilities')) {
    $module_name = defined('POLY_UTILITIES_MODULE_NAME') ? POLY_UTILITIES_MODULE_NAME : 'poly_utilities';
    $this->load->helper($module_name . '/poly_utilities_menu');
}

if (function_exists('staff_can_poly_utilities') && !staff_can_poly_utilities()) {
    // Filter out Poly Utilities menu and all its children
    $all_menus = array_filter($all_menus, function($item) {
        return $item['slug'] !== 'poly_utilities';
    });
}

// Filter menu items based on user permissions (users/roles) for each menu item
$staff_id = get_staff_user_id();
$all_menus = poly_filter_menu_items_by_permission($all_menus, $staff_id);

// Render menu items
foreach ($all_menus as $key => $item) {
    if ((isset($item['collapse']) && $item['collapse']) && count($item['children'] ?? []) === 0) {
        continue;
    }
    
    $has_children = !empty($item['children']);
    $li_class = 'menu-item-' . e($item['slug']);
    ?>
        <li class="<?= $li_class; ?>" <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
            <a href="<?= $has_children ? '#' : e($item['href']); ?>"
               aria-expanded="false"
               <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                
                <?php if (!empty($item['icon'])) { ?>
                <i class="<?= e($item['icon']); ?> menu-icon"></i>
                <?php } ?>
                
                <span class="menu-text">
                    <?= e(_l($item['name'], '', false)); ?>
                </span>
                
                <?php if ($has_children) { ?>
                <span class="fa arrow pleft5 fa-sm tw-mt-1.5"></span>
                <?php } ?>
                
                <?php if (isset($item['badge'], $item['badge']['value']) && !empty($item['badge'])) {?>
                <span class="badge pull-right <?= isset($item['badge']['type']) && $item['badge']['type'] != '' ? "bg-{$item['badge']['type']}" : 'bg-info' ?>"
                      <?= (isset($item['badge']['type']) && $item['badge']['type'] == '') || isset($item['badge']['color']) ? "style='background-color: {$item['badge']['color']}'" : '' ?>>
                    <?= e($item['badge']['value']) ?>
                </span>
                <?php } ?>
            </a>
            
            <?php if ($has_children) { ?>
            <ul class="nav nav-second-level collapse" aria-expanded="false">
                <?php foreach ($item['children'] as $submenu) { 
                    $has_children_l2 = !empty($submenu['children']);
                ?>
                <li class="sub-menu-item-<?= e($submenu['slug']); ?>" <?= _attributes_to_string($submenu['li_attributes'] ?? []); ?>>
                    <a href="<?= $has_children_l2 ? '#' : e($submenu['href']); ?>"
                       <?= _attributes_to_string($submenu['href_attributes'] ?? []); ?>>
                        <?php if (!empty($submenu['icon'])) { ?>
                        <i class="<?= e($submenu['icon']); ?> menu-icon"></i>
                        <?php } ?>
                        <span class="sub-menu-text">
                            <?= _l($submenu['name'], '', false); ?>
                        </span>
                        <?php if ($has_children_l2) { ?>
                        <span class="fa arrow pleft5 fa-sm"></span>
                        <?php } ?>
                    </a>
                    
                    <?php if ($has_children_l2) { // Level 3 ?>
                    <ul class="nav nav-third-level collapse" aria-expanded="false">
                        <?php foreach ($submenu['children'] as $submenu_l3) { ?>
                        <li class="sub-menu-item-l3-<?= e($submenu_l3['slug']); ?>" <?= _attributes_to_string($submenu_l3['li_attributes'] ?? []); ?>>
                            <a href="<?= e($submenu_l3['href']); ?>"
                               <?= _attributes_to_string($submenu_l3['href_attributes'] ?? []); ?>>
                                <?php if (!empty($submenu_l3['icon'])) { ?>
                                <i class="<?= e($submenu_l3['icon']); ?> menu-icon"></i>
                                <?php } ?>
                                <span class="sub-menu-text-l3">
                                    <?= _l($submenu_l3['name'], '', false); ?>
                                </span>
                            </a>
                            <?php if (isset($submenu_l3['badge'], $submenu_l3['badge']['value']) && !empty($submenu_l3['badge'])) {?>
                            <span class="badge pull-right mright5 <?= isset($submenu_l3['badge']['type']) && $submenu_l3['badge']['type'] != '' ? "bg-{$submenu_l3['badge']['type']}" : 'bg-info' ?>"
                                  <?= (isset($submenu_l3['badge']['type']) && $submenu_l3['badge']['type'] == '') || isset($submenu_l3['badge']['color']) ? "style='background-color: {$submenu_l3['badge']['color']}'" : '' ?>>
                                <?= e($submenu_l3['badge']['value']) ?>
                            </span>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                    
                    <?php if (isset($submenu['badge'], $submenu['badge']['value']) && !empty($submenu['badge'])) {?>
                    <span class="badge pull-right mright5 <?= isset($submenu['badge']['type']) && $submenu['badge']['type'] != '' ? "bg-{$submenu['badge']['type']}" : 'bg-info' ?>"
                          <?= (isset($submenu['badge']['type']) && $submenu['badge']['type'] == '') || isset($submenu['badge']['color']) ? "style='background-color: {$submenu['badge']['color']}'" : '' ?>>
                        <?= e($submenu['badge']['value']) ?>
                    </span>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
            <?php } ?>
        </li>
        <?php hooks()->do_action('after_render_single_aside_menu', $item); ?>
        <?php
        } ?>
        <?php if ($this->app->show_setup_menu() == true && (is_staff_member() || is_admin())) { ?>
        <li<?php if (get_option('show_setup_menu_item_only_on_hover') == 1) {
            echo ' style="display:none;"';
        } ?> id="setup-menu-item">
            <a href="#" class="open-customizer"><i class="fa fa-cog menu-icon"></i>
                <span class="menu-text">
                    <?= _l('setting_bar_heading'); ?>
                    <?php
               if ($modulesNeedsUpgrade = $this->app_modules->number_of_modules_that_require_database_upgrade()) {
                   echo '<span class="badge menu-badge !tw-bg-warning-600">' . $modulesNeedsUpgrade . '</span>';
               }
            ?>
                </span>
            </a>
            <?php } ?>
            </li>
            <?php hooks()->do_action('after_render_aside_menu'); ?>
            <?php $this->load->view('admin/projects/pinned'); ?>
    </ul>
</aside>
