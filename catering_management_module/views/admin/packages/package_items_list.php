<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/** @var array $items */
?>
<div id="package-items-hidden-inputs" class="hidden">
	<?php foreach ($items as $index => $item) { ?>
        <input type="hidden" name="items[<?php echo $index; ?>][item_id]" value="<?php echo $item['item_id']; ?>">
        <input type="hidden" name="items[<?php echo $index; ?>][qty_per_guest]" value="<?php echo $item['qty_per_guest']; ?>">
	<?php } ?>
</div>

<div class="table-responsive">
    <table class="table table-package-items">
        <thead>
        <tr>
            <th><?php echo _l('item_name'); ?></th>
            <th class="text-right"><?php echo _l('unit_cost'); ?></th>
            <th class="text-center" width="15%"><?php echo _l('quantity_per_guest'); ?></th>
            <th class="text-right"><?php echo _l('total_cost'); ?></th>
            <th class="text-center"><i class="fa fa-remove"></i></th>
        </tr>
        </thead>
        <tbody>
		<?php if (count($items) > 0) { ?>
			<?php foreach ($items as $item) { ?>
                <tr data-item-id="<?php echo $item['item_id']; ?>">
                    <td><?php echo $item['item_name']; ?></td>
                    <td class="text-right"><?php echo app_format_money($item['unit_cost'], get_base_currency()); ?></td>
                    <td>
                        <input type="number" class="form-control item-qty text-center" data-id="<?php echo $item['id']; ?>" value="<?php echo round($item['qty_per_guest'], 1) ?>" min="0.1" step="0.1">
                    </td>
                    <td class="text-right total-cost">
						<?php echo app_format_money($item['unit_cost'] * $item['qty_per_guest'], get_base_currency()); ?>
                    </td>
                    <td class="text-center">
                        <a href="#" class="btn btn-danger btn-xs remove-item" data-id="<?php echo $item['id']; ?>"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
			<?php } ?>
		<?php } else { ?>
            <tr>
                <td colspan="5" class="text-center text-muted"><?php echo _l('no_items_in_package'); ?></td>
            </tr>
		<?php } ?>
        </tbody>
    </table>
</div>
