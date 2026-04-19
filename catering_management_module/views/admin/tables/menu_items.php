<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('catering_menu_items_model');

$sTable = db_prefix().'catering_menu_items';

$aColumns = [
	$sTable.'.id as id',
	'item_name',
	'category_id',
	$sTable.'.active as active',
	$sTable.'.created_at as created_at',
	'unit_cost',
	'unit_price',
];

$sIndexColumn = 'id';

$join = ['LEFT JOIN '.db_prefix().'catering_menu_categories ON '.db_prefix().'catering_menu_categories.id = '.db_prefix().'catering_menu_items.category_id'];

$additionalSelect = [db_prefix().'catering_menu_categories.name as category_name'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];
	$row[] = $aRow['id'];

	$row[] = '<a href="'.admin_url('catering_management_module/menu_items/item/'.$aRow['id']).'"><strong>'.htmlspecialchars($aRow['item_name']).'</strong></a>';
	$row[] = htmlspecialchars($aRow['category_name'] ?? '');

	$row[] = catering_active_badge($aRow['active']);
	$row[] = '<span class="text-muted">'._d($aRow['created_at']).'</span>';

	if (catering_can_view_costs())
	{
		$row[] = catering_format_price($aRow['unit_cost']);
		$row[] = catering_format_price($aRow['unit_price']);
		$margin = catering_profit_margin($aRow['unit_cost'], $aRow['unit_price']);
		$row[] = catering_margin_badge($margin);
	} else
	{
		$row[] = catering_format_price($aRow['unit_price']);
	}

	// Dietary/Allergen info
	$item = $CI->catering_menu_items_model->get($aRow['id']);
	$row[] = catering_item_tags($item);

	$options = '';
	if (staff_can('edit', 'catering_menu_items'))
	{
		$options .= '<a href="'.admin_url('catering_management_module/menu_items/item/'.$aRow['id']).'" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a> ';
	}
	if (staff_can('delete', 'catering_menu_items'))
	{
		$options .= '<a data-id="'.$aRow['id'].'" data-name="'.html_purify($aRow['item_name']).'" href="javascript:void(0)" class="btn btn-danger btn-icon delete-menu-item"><i class="fa fa-trash"></i></a>';
	}
	$row[] = $options;

	$output['aaData'][] = $row;
}


echo json_encode($output);
die();
