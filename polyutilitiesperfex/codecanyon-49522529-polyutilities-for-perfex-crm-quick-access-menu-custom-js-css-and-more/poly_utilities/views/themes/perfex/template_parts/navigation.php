<?php defined('BASEPATH') or exit('No direct script access allowed'); 
// Load menu helpers to access menu functions
$this->load->helper('poly_utilities/poly_utilities_menu_sync');
$this->load->helper('poly_utilities/poly_utilities_menu');
$this->load->helper('poly_utilities/poly_utilities_menu_limited');

// Function to recursively filter menu items based on login status
function filter_menu_item_by_login($item) {
    $item_slug = isset($item['slug']) ? strtolower($item['slug']) : '';
    $item_href = isset($item['href']) ? strtolower($item['href']) : '';
    $clients_menu_items = ['invoices', 'projects', 'contracts', 'estimates', 'proposals', 'subscriptions', 'support'];
    
    if (is_client_logged_in()) {
        // Hide login and register when logged in
        if (in_array($item_slug, ['login', 'register'])) {
            return false;
        }
        if (strpos($item_href, '/login') !== false || strpos($item_href, '/register') !== false) {
            return false;
        }
    } else {
        // Hide clients menu items when NOT logged in
        if (in_array($item_slug, $clients_menu_items)) {
            return false;
        }
        if (strpos($item_href, '/clients/') !== false && 
            strpos($item_href, '/clients/login') === false && 
            strpos($item_href, '/clients/register') === false) {
            return false;
        }
    }
    
    return true;
}

// Function to render menu icon (SVG or icon class)
function render_menu_icon($item, $include_space = false) {
    $icon_html = '';
    
    // Check if SVG exists
    if (!empty($item['svg'])) {
        // Decode HTML entities that were encoded when saving to database
        $decoded_svg = html_entity_decode($item['svg'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $icon_html = '<span class="menu-icon">' . $decoded_svg . '</span>';
    } elseif (!empty($item['icon'])) {
        $icon_html = '<i class="' . e($item['icon']) . '"></i>';
    }
    
    // Add space after icon if needed
    if ($icon_html && $include_space) {
        $icon_html .= '&nbsp;';
    }
    
    return $icon_html;
}
?>
<nav class="navbar navbar-default header">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                data-target="#theme-navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php get_dark_company_logo('', 'navbar-brand logo'); ?>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="theme-navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <?php hooks()->do_action('customers_navigation_start'); ?>
                <?php 
                //  Get ALL menus from database (system + custom merged, all have IDs)
                $all_menus = poly_get_all_menus_from_db('clients', 3);
                
                // Filter menus based on require_login and client permissions
                $CI = &get_instance();
                $CI->load->model('poly_utilities/custom_menu_model');
                $filtered_menus = [];
                
                foreach ($all_menus as $item_id => $item) {
                    // Check require_login
                    $require_login = isset($item['require_login']) ? (int)$item['require_login'] : 0;
                    
                    // If require_login = 1 and not logged in, skip this item
                    if ($require_login == 1 && !is_client_logged_in()) {
                        continue;
                    }
                    
                    // Check children too
                    if (!empty($item['children'])) {
                        $filtered_children = [];
                        foreach ($item['children'] as $child) {
                            $child_require_login = isset($child['require_login']) ? (int)$child['require_login'] : 0;
                            if ($child_require_login == 1 && !is_client_logged_in()) {
                                continue; // Skip child
                            }
                            $filtered_children[] = $child;
                        }
                        $item['children'] = $filtered_children;
                    }
                    
                    $filtered_menus[$item_id] = $item;
                }
                
                foreach ($filtered_menus as $item_id => $item) {
                    // Filter parent menu item by login status
                    if (!filter_menu_item_by_login($item)) {
                        continue;
                    }
                    
                    $is_active = ($item['href'] === current_full_url()) ? ' active' : '';
                    $has_children = !empty($item['children']);
                    $li_class = 'customers-nav-item-' . e($item_id ?? $item['slug']) . $is_active;
                    
                    if ($has_children) {
                        // Hover menu (has-child style)
                        // Check if parent has a valid href (not empty, not '#')
                        $parent_has_valid_href = !empty($item['href']) && $item['href'] !== '#';
                        $parent_href = $parent_has_valid_href ? e($item['href']) : 'javascript:void(0)';
                        ?>
                        <li class="has-child <?= $li_class; ?>" <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
                            <a href="<?= $parent_href; ?>" <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                                <?= render_menu_icon($item); ?>
                                <?= e(_l($item['name'], '', false)); ?>
                            </a>
                            <ul class="nav-second-level" style="display: none;">
                                <?php foreach ($item['children'] as $child) { 
                                    // Filter child menu item by login status
                                    if (!filter_menu_item_by_login($child)) {
                                        continue;
                                    }
                                    
                                    $has_children_l2 = !empty($child['children']);
                                    // Check if child has a valid href (not empty, not '#')
                                    $child_has_valid_href = !empty($child['href']) && $child['href'] !== '#';
                                    $child_href = $child_has_valid_href ? e($child['href']) : 'javascript:void(0)';
                                ?>
                                <li class="customers-nav-item-<?= e($child['slug']); ?> <?= $has_children_l2 ? 'has-child-l2' : ''; ?>" <?= _attributes_to_string($child['li_attributes'] ?? []); ?>>
                                    <a href="<?= $child_href; ?>" <?= _attributes_to_string($child['href_attributes'] ?? []); ?>>
                                        <?= render_menu_icon($child, true); ?>
                                        <?= e(_l($child['name'], '', false)); ?>
                                        <?php if ($has_children_l2) { ?>
                                        <span class="arrow-icon"></span>
                                        <?php } ?>
                                    </a>
                                    
                                    <?php if ($has_children_l2) { // Level 3 ?>
                                    <ul class="nav-third-level" style="display: none;">
                                        <?php foreach ($child['children'] as $child_l3) { 
                                            // Filter level 3 menu item by login status
                                            if (!filter_menu_item_by_login($child_l3)) {
                                                continue;
                                            }
                                        ?>
                                        <li class="customers-nav-item-<?= e($child_l3['slug']); ?>" <?= _attributes_to_string($child_l3['li_attributes'] ?? []); ?>>
                                            <a href="<?= e($child_l3['href']); ?>" <?= _attributes_to_string($child_l3['href_attributes'] ?? []); ?>>
                                                <?= render_menu_icon($child_l3, true); ?>
                                                <?= e(_l($child_l3['name'], '', false)); ?>
                                                <span class="arrow-icon"></span>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                    <?php } ?>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php
                    } else {
                        // Regular link
                        ?>
                        <li class="<?= $li_class; ?>" <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
                            <a href="<?= e($item['href']); ?>" <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
                                <?= render_menu_icon($item); ?>
                                <?= e(_l($item['name'], '', false)); ?>
                            </a>
                        </li>
                        <?php
                    }
                } ?>
                <?php hooks()->do_action('customers_navigation_end'); ?>
                <?php if (is_client_logged_in()) { ?>
                <li class="dropdown customers-nav-item-profile">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                        aria-expanded="false">
                        <img src="<?= e(contact_profile_image_url($contact->id, 'thumb')); ?>"
                             data-toggle="tooltip" 
                             data-title="<?= e($contact->firstname . ' ' . $contact->lastname); ?>"
                             data-placement="bottom" 
                             class="client-profile-image-small">
                    </a>
                    <ul class="dropdown-menu animated fadeIn">
                        <li class="customers-nav-item-edit-profile">
                            <a href="<?= site_url('clients/profile'); ?>">
                                <?= _l('clients_nav_profile'); ?>
                            </a>
                        </li>
                        <?php if ($contact->is_primary == 1) { ?>
                        <?php if (can_loggged_in_user_manage_contacts()) { ?>
                        <li class="customers-nav-item-edit-profile">
                            <a href="<?= site_url('contacts'); ?>">
                                <?= _l('clients_nav_contacts'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="customers-nav-item-company-info">
                            <a href="<?= site_url('clients/company'); ?>">
                                <?= _l('client_company_info'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (can_logged_in_contact_update_credit_card()) { ?>
                        <li class="customers-nav-item-stripe-card">
                            <a href="<?= site_url('clients/credit_card'); ?>">
                                <?= _l('credit_card'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (is_gdpr() && get_option('show_gdpr_in_customers_menu') == '1') { ?>
                        <li class="customers-nav-item-announcements">
                            <a href="<?= site_url('clients/gdpr'); ?>">
                                <?= _l('gdpr_short'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li class="customers-nav-item-announcements">
                            <a href="<?= site_url('clients/announcements'); ?>">
                                <?= _l('announcements'); ?>
                                <?php if ($total_undismissed_announcements != 0) { ?>
                                <span class="badge"><?= e($total_undismissed_announcements); ?></span>
                                <?php } ?>
                            </a>
                        </li>
                        <?php if (! is_language_disabled()) { ?>
                        <li class="dropdown-submenu pull-left customers-nav-item-languages">
                            <a href="#" tabindex="-1">
                                <?= _l('language'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-left">
                                <li class="<?php if (get_contact_language() == '') {
                                    echo 'active';
                                } ?>">
                                    <a href="<?= site_url('clients/change_language'); ?>">
                                        <?= _l('system_default_string'); ?>
                                    </a>
                                </li>
                                <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                                <li <?php if (get_contact_language() == $user_lang) {
                                    echo 'class="active"';
                                } ?>>
                                    <a href="<?= site_url('clients/change_language/' . $user_lang); ?>">
                                        <?= e(ucfirst($user_lang)); ?>
                                    </a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                        <?= hooks()->do_action('customers_navigation_before_logout'); ?>
                        <li class="customers-nav-item-logout">
                            <a href="<?= site_url('authentication/logout'); ?>">
                                <?= _l('clients_nav_logout'); ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php } ?>
                <?php hooks()->do_action('customers_navigation_after_profile'); ?>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

