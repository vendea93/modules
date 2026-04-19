<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'email',
	'phone',
	'company',
	'city',
	'country',
	'commission_rate',
	'datecreated',
	'active'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'hms_landlords';

$join = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	$row[] = $aRow['id'];

	// Name
	$nameOutput = '<a href="' . admin_url('hotel_management_system/landlords/view/' . $aRow['id']) . '">' . $aRow['name'] . '</a>';
	$row[] = $nameOutput;

	// Email
	$row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';

	// Phone
	$row[] = $aRow['phone'];

	// Company
	$row[] = $aRow['company'];

	// City
	$row[] = $aRow['city'];

	// Country
	$row[] = $aRow['country'];

	// Commission Rate
	$row[] = $aRow['commission_rate'] . '%';

	// Date Created
	$row[] = _dt($aRow['datecreated']);

	// Status
	$status_label = '<span class="label label-' . ($aRow['active'] ? 'success' : 'warning') . '">' . _l($aRow['active']) ? _l('active') : _l('inactive') . '</span>';

	$row[] = $status_label;

	// Options
	$options = '<div class="tw-flex tw-gap-0.5 tw-no-wrap">';
	$options .= '<a href="' . admin_url('hotel_management_system/landlords/view/' . $aRow['id']) . '" class="btn btn-default btn-icon" data-toggle="tooltip" title="' . _l('view') . '"><i class="fa fa-eye"></i></a> ';
	$options .= '<a href="' . admin_url('hotel_management_system/landlords/landlord/' . $aRow['id']) . '" class="btn btn-default btn-icon" data-toggle="tooltip" title="' . _l('edit') . '"><i class="fa fa-pencil"></i></a> ';
	$options .= '<a href="' . admin_url('hotel_management_system/landlords/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete" data-toggle="tooltip" title="' . _l('delete') . '"><i class="fa fa-remove"></i></a>';
	$options .= '</div>';
	$row[] = $options;

	$output['aaData'][] = $row;
}