<?php

defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn = 'id';
$sTable = db_prefix().'catering_dietary_types';

$aColumns = [
	'label',
	'code',
	'display_order',
	$sTable.'.active as active',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'icon', 'description', 'created_at']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// Label with icon
	$label = '<span class="label label-success">';
	if ($aRow['icon'])
	{
		$label .= '<i class="'.$aRow['icon'].'"></i> ';
	}
	$label .= htmlspecialchars($aRow['label']);
	$label .= '</span>';

	if ($aRow['description'])
	{
		$label .= '<br><small class="text-muted">'.htmlspecialchars(substr($aRow['description'], 0, 60)).'...</small>';
	}

	$row[] = $label;

	// Code
	$row[] = '<code>'.htmlspecialchars($aRow['code']).'</code>';

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

	if (staff_can('edit', 'catering_dietary_types'))
	{
		$checked = $aRow['active'] == 1 ? 'checked' : '';
		$active_badge .= '<div class="onoffswitch mtop5" data-toggle="tooltip" title="'._l('toggle_status').'">';
		$active_badge .= '<input type="checkbox" class="onoffswitch-checkbox toggle-dietary-status" data-id="'.$aRow['id'].'" id="active_'.$aRow['id'].'" '.$checked.'>';
		$active_badge .= '<label class="onoffswitch-label" for="active_'.$aRow['id'].'"></label>';
		$active_badge .= '</div>';
	}

	$row[] = $active_badge;

	// Options
	$options = '';

	// Edit button
	if (staff_can('edit', 'catering_dietary_types'))
	{
		$options .= '<a href="#" class="btn btn-default btn-icon edit-dietary-type" data-id="'.$aRow['id'].'" data-toggle="tooltip" title="'._l('edit').'">';
		$options .= '<i class="fa fa-pencil"></i>';
		$options .= '</a> ';
	}

	// Delete button
	if (staff_can('delete', 'catering_dietary_types'))
	{
		$options .= '<a href="#" class="btn btn-danger btn-icon delete-dietary-type _delete" data-id="'.$aRow['id'].'" data-name="'.htmlspecialchars($aRow['label']).'" data-toggle="tooltip" title="'._l('delete').'">';
		$options .= '<i class="fa fa-trash"></i>';
		$options .= '</a>';
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();