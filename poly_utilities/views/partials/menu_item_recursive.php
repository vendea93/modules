<?php 
/**
 * Recursive Menu Item Renderer
 * Supports unlimited menu levels for admin sidebar/setup menus
 * 
 * @param array $item Menu item with children
 * @param int $current_level Current nesting level (1, 2, 3, ...)
 * @param string $menu_type Menu type: sidebar, setup, clients
 */
defined('BASEPATH') or exit('No direct script access allowed');

$current_level = $current_level ?? 1;
$menu_type = $menu_type ?? 'sidebar';
$has_children = !empty($item['children']);

// CSS classes based on level
$li_class = $current_level === 1 ? 'menu-item-' . e($item['slug']) : 'sub-menu-item-' . e($item['slug']);
$ul_class = 'nav ' . ($current_level === 1 ? 'nav-second-level' : 'nav-level-' . ($current_level + 1)) . ' collapse';

// Indentation for deep levels (optional)
$indent_style = $current_level > 2 ? 'style="padding-left: ' . (($current_level - 2) * 15) . 'px;"' : '';
?>
<li class="<?= $li_class; ?> menu-level-<?= $current_level; ?>"
    <?= _attributes_to_string($item['li_attributes'] ?? []); ?>>
    
    <a href="<?= $has_children ? '#' : e($item['href']); ?>"
       aria-expanded="false"
       <?= $indent_style; ?>
       <?= _attributes_to_string($item['href_attributes'] ?? []); ?>>
        
        <?php if (!empty($item['icon'])) { ?>
        <i class="<?= e($item['icon']); ?> menu-icon"></i>
        <?php } ?>
        
        <span class="menu-text">Z 
            <?= e(_l($item['name'], '', false)); ?>
        </span>
        
        <?php if ($has_children) { ?>
        <span class="fa arrow pleft5 fa-sm tw-mt-1.5"></span>
        <?php } ?>
        
        <?php if (isset($item['badge'], $item['badge']['value']) && !empty($item['badge'])) { ?>
        <span class="badge pull-right <?= isset($item['badge']['type']) && $item['badge']['type'] != '' ? "bg-{$item['badge']['type']}" : 'bg-info' ?>"
              <?= (isset($item['badge']['type']) && $item['badge']['type'] == '') || isset($item['badge']['color']) ? "style='background-color: {$item['badge']['color']}'" : '' ?>>
            <?= e($item['badge']['value']) ?>
        </span>
        <?php } ?>
    </a>
    
    <?php if ($has_children) { ?>
    <ul class="<?= $ul_class; ?>" aria-expanded="false">
        <?php foreach ($item['children'] as $child) { ?>
            <?php 
            // ✨ RECURSIVE CALL - Support unlimited levels!
            echo $this->load->view('poly_utilities/partials/menu_item_recursive', [
                'item' => $child,
                'current_level' => $current_level + 1,
                'menu_type' => $menu_type
            ], true); 
            ?>
        <?php } ?>
    </ul>
    <?php } ?>
</li>
