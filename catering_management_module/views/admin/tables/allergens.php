<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'label',
	'code',
	'severity',
	'display_order',
	'active',
];

$sIndexColumn = 'id';
$sTable = db_prefix().'catering_allergens';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'icon', 'description', 'created_at']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// Label with icon and severity badge
	$severity_colors = [
		'mild' => 'info',
		'moderate' => 'warning',
		'severe' => 'danger',
	];
	$color = $severity_colors[$aRow['severity']] ?? 'default';

	$label = '<span class="label label-'.$color.'">';
	if ($aRow['icon'])
	{
		$label .= '<i class="'.$aRow['icon'].'"></i> ';
	}
	$label .= htmlspecialchars($aRow['label']);
	$label .= '</span>';

	if ($aRow['description'])
	{
		$label .= '<br><small class="text-muted">'.htmlspecialchars(substr($aRow['description'], 0, 50)).'...</small>';
	}

	$row[] = $label;

	// Code
	$row[] = '<code>'.htmlspecialchars($aRow['code']).'</code>';

	// Severity with badge
	$severity_text = catering_allergen_severity_text($aRow['severity']);
	$row[] = '<span class="label label-'.$color.'">'.$severity_text.'</span>';

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

	if (staff_can('edit', 'catering_allergens'))
	{
		$checked = $aRow['active'] == 1 ? 'checked' : '';
		$active_badge .= '<div class="onoffswitch mtop5" data-toggle="tooltip" title="'._l('toggle_status').'">';
		$active_badge .= '<input type="checkbox" class="onoffswitch-checkbox toggle-allergen-status" data-id="'.$aRow['id'].'" id="active_'.$aRow['id'].'" '.$checked.'>';
		$active_badge .= '<label class="onoffswitch-label" for="active_'.$aRow['id'].'"></label>';
		$active_badge .= '</div>';
	}

	$row[] = $active_badge;

	// Options
	$options = '';

	// Edit button
	if (staff_can('edit', 'catering_allergens'))
	{
		$options .= '<a href="#" class="btn btn-default btn-icon edit-allergen" data-id="'.$aRow['id'].'" data-toggle="tooltip" title="'._l('edit').'">';
		$options .= '<i class="fa fa-pencil"></i>';
		$options .= '</a> ';
	}

	// Delete button
	if (staff_can('delete', 'catering_allergens'))
	{
		$options .= '<a href="#" class="btn btn-danger btn-icon delete-allergen _delete" data-id="'.$aRow['id'].'" data-name="'.htmlspecialchars($aRow['label']).'" data-toggle="tooltip" title="'._l('delete').'">';
		$options .= '<i class="fa fa-trash"></i>';
		$options .= '</a>';
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

echo json_encode($output);
die();