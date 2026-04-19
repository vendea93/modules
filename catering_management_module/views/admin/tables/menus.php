<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn = 'id';
$sTable = db_prefix().'catering_menus';

$aColumns = [
	'menu_name',
	'description',
	'base_price_per_person',
	'active',
	'created_at',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'updated_at']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];
	$row[] = $aRow['id'];

	// Menu Name with link
	$name = '<a href="'.admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/menu/'.$aRow['id']).'" class="bold">';
	$name .= htmlspecialchars($aRow['menu_name']);
	$name .= '</a>';
	$row[] = $name;

	// Description (truncated)
	$description = '';
	if ( ! empty($aRow['description']))
	{
		$desc_text = strip_tags($aRow['description']);
		if (strlen($desc_text) > 100)
		{
			$description = substr($desc_text, 0, 100).'...';
		} else
		{
			$description = $desc_text;
		}
	}
	$row[] = htmlspecialchars($description);

	// Base Price
	$price = '-';
	if ($aRow['base_price_per_person'])
	{
		$price = app_format_money($aRow['base_price_per_person'], get_base_currency());
	}
	$row[] = $price;

	// Items Count
	$CI = &get_instance();
	$CI->db->where('menu_id', $aRow['id']);
	$items_count = $CI->db->count_all_results(db_prefix().'catering_menu_items_link');

	$items_badge = '<span class="badge badge-info">'.$items_count.'</span>';
	$row[] = $items_badge;

	// Active Status
	$active_badge = '';
	if ($aRow['active'] == 1)
	{
		$active_badge = '<span class="label label-success">'._l('active').'</span>';
	} else
	{
		$active_badge = '<span class="label label-default">'._l('inactive').'</span>';
	}

	// Toggle active button
	if (staff_can('edit', 'catering_menus'))
	{
		$checked = $aRow['active'] == 1 ? 'checked' : '';
		$active_badge .= '<div class="onoffswitch mtop5" data-toggle="tooltip" title="'._l('toggle_status').'">';
		$active_badge .= '<input type="checkbox" class="onoffswitch-checkbox toggle-menu-status" data-id="'.$aRow['id'].'" id="active_'.$aRow['id'].'" '.$checked.'>';
		$active_badge .= '<label class="onoffswitch-label" for="active_'.$aRow['id'].'"></label>';
		$active_badge .= '</div>';
	}

	$row[] = $active_badge;

	// Created Date
	$row[] = '<span class="text-muted">'._dt($aRow['created_at']).'</span>';

	// Options
	$options = '';

	// View/Edit button
	if (staff_can('view', 'catering_menus'))
	{
		$options .= '<a href="'.admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/menu/'.$aRow['id']).'" class="btn btn-default btn-icon" data-toggle="tooltip" title="'._l('edit').'">';
		$options .= '<i class="fa fa-pencil"></i>';
		$options .= '</a> ';
	}

	// Duplicate button
	if (staff_can('create', 'catering_menus'))
	{
		$options .= '<a href="#" class="btn btn-default btn-icon duplicate-menu" data-id="'.$aRow['id'].'" data-name="'.htmlspecialchars($aRow['menu_name']).'" data-toggle="tooltip" title="'._l('duplicate').'">';
		$options .= '<i class="fa fa-copy"></i>';
		$options .= '</a> ';
	}

	// Delete button
	if (staff_can('delete', 'catering_menus'))
	{
		$options .= '<a href="#" class="btn btn-danger btn-icon delete-menu" data-id="'.$aRow['id'].'" data-name="'.htmlspecialchars($aRow['menu_name']).'" data-toggle="tooltip" title="'._l('delete').'">';
		$options .= '<i class="fa fa-trash"></i>';
		$options .= '</a>';
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();