<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'name',
	'description',
	'display_order',
	'active',
];

$sIndexColumn = 'id';
$sTable = db_prefix().'catering_menu_sections';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'created_at']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// Name
	$name = '<strong>'.htmlspecialchars($aRow['name']).'</strong>';
	$row[] = $name;

	// Description
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
	$row[] = '<small class="text-muted">'.htmlspecialchars($description).'</small>';

	// Display Order
	$row[] = '<span class="badge">'.$aRow['display_order'].'</span>';

	// Active Status with toggle
	$active_badge = '';
	if ($aRow['active'] == 1)
	{
		$active_badge = '<span class="label label-success">'._l('active').'</span>';
	} else
	{
		$active_badge = '<span class="label label-default">'._l('inactive').'</span>';
	}

	if (staff_can('edit', 'menu_sections'))
	{
		$checked = $aRow['active'] == 1 ? 'checked' : '';
		$active_badge .= '<div class="onoffswitch mtop5" data-toggle="tooltip" title="'._l('toggle_status').'">';
		$active_badge .= '<input type="checkbox" class="onoffswitch-checkbox toggle-section-status" data-id="'.$aRow['id'].'" id="active_'.$aRow['id'].'" '.$checked.'>';
		$active_badge .= '<label class="onoffswitch-label" for="active_'.$aRow['id'].'"></label>';
		$active_badge .= '</div>';
	}

	$row[] = $active_badge;

	// Options
	$options = '';

	// Edit button
	if (staff_can('edit', 'menu_sections'))
	{
		$options .= '<a href="#" class="btn btn-default btn-icon edit-section" data-id="'.$aRow['id'].'" data-toggle="tooltip" title="'._l('edit').'">';
		$options .= '<i class="fa fa-pencil"></i>';
		$options .= '</a> ';
	}

	// Delete button
	if (staff_can('delete', 'menu_sections'))
	{
		$options .= '<a href="#" class="btn btn-danger btn-icon delete-section _delete" data-id="'.$aRow['id'].'" data-name="'.htmlspecialchars($aRow['name']).'" data-toggle="tooltip" title="'._l('delete').'">';
		$options .= '<i class="fa fa-trash"></i>';
		$options .= '</a>';
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();