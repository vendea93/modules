<?php
/**
 * Recursive Client Menu Renderer
 * Supports unlimited menu levels for client area
 * For themes/perfex/template_parts/navigation.php
 * 
 * @param array $item Menu item with children
 * @param int $current_level Current nesting level (1, 2, 3, ...)
 */
defined('BASEPATH') or exit('No direct script access allowed');

$current_level = $current_level ?? 1;
$has_children = !empty($item['children']);

// CSS classes based on level
$li_class = 'customers-nav-item-' . e($item['slug']) . ' nav-level-' . $current_level;
if ($item['href'] === current_full_url()) {
    $li_class .= ' active';
}

// Indentation for deep levels
$indent_style = $current_level > 2 ? 'style="padding-left: ' . (($current_level - 2) * 15) . 'px;"' : '';
?>

<li class="<?= $li_class; ?>"
    <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
    
    <?php if ($has_children) { ?>
    <!-- Dropdown for items with children -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" <?= $indent_style; ?>>
        <?php if (!empty($item['icon'])) { ?>
        <i class="<?= e($item['icon']); ?>"></i>
        <?php } ?>
        <?= e($item['name']); ?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <?php foreach ($item['children'] as $child) { ?>
            <?php 
            // ✨ RECURSIVE CALL - Support unlimited levels!
            echo $this->load->view('poly_utilities/themes/partials/client_menu_recursive', [
                'item' => $child,
                'current_level' => $current_level + 1
            ], true);
            ?>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <!-- Regular link -->
    <a href="<?= e($item['href']); ?>" 
       <?= $indent_style; ?>
       <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
        <?php if (!empty($item['icon'])) { ?>
        <i class="<?= e($item['icon']); ?>"></i>
        <?php } ?>
        <?= e($item['name']); ?>
    </a>
    <?php } ?>
</li>
