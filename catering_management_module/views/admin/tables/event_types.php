<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'etid',
	'name',
	'background_color',
	'text_color',
	'sort_order',
	'editable',
];
$sIndexColumn = 'id';
$sTable = db_prefix().'catering_event_types';

$result = data_tables_init(
	$aColumns,
	$sIndexColumn,
	$sTable
);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];
	$row[] = $aRow['etid'];
	$row[] = '<span class="label" style="background-color:'.$aRow['background_color'].';border:1px solid '.$aRow['background_color'].';color:'.$aRow['text_color'].'">'.$aRow['name'].'</span>';
	$row[] = '<div style="margin-right: 1rem; display: inline-block; min-width: 20px; width: 20px; height: 20px; background-color:'.$aRow['background_color'].';">&nbsp;</div>'.$aRow['background_color'];
	$row[] = '<div style="margin-right: 1rem; display: inline-block; min-width: 20px; width: 20px; height: 20px; background-color:'.$aRow['text_color'].';">&nbsp;</div>'.$aRow['text_color'];
	$row[] = $aRow['sort_order'];

	if ($aRow['editable'])
	{
		$options = '<a class="btn btn-default btn-icon" data-toggle="tooltip" title="'._l(
				'edit'
			).'" data-name="'.$aRow['name'].'" data-background-color="'.$aRow['background_color'].'" data-text-color="'.$aRow['text_color'].'" data-sort-order="'.$aRow['sort_order'].'" onclick="edit_event_type(this,'.$aRow['etid'].')" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>';
		$options .= '<a class="btn btn-danger btn-icon _delete" data-toggle="tooltip" title="'._l(
				'delete'
			).'" onclick="delete_event_type('.$aRow['etid'].')" href="javascript:void(0)"><i class="fa fa-trash"></i ></a>';

		$row[] = $options;
	} else
	{
		$row[] = '';
	}

	$row['DT_RowClass'] = 'has-row-options';
	$output['aaData'][] = $row;
}
