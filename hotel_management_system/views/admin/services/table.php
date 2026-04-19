<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'service_type',
	'price',
	'duration_minutes',
	'status'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'hms_services';

$join = [];
$where = [];

// If status filter provided
if ($this->ci->input->post('status') && $this->ci->input->post('status') != '')
{
	array_push($where, 'AND status = "' . $this->ci->db->escape_str($this->ci->input->post('status')) . '"');
}

// Get service types for translating
$service_types = hms_get_service_types();

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// ID
	$row[] = $aRow['id'];

	// Name with link to edit
	$nameOutput = '<a href="' . admin_url('hotel_management_system/services/service/' . $aRow['id']) . '">' . $aRow['name'] . '</a>';
	$row[] = $nameOutput;

	// Service type
	$service_type_name = isset($service_types[$aRow['service_type']]) ? $service_types[$aRow['service_type']] : $aRow['service_type'];
	$row[] = $service_type_name;

	// Price
	$row[] = app_format_money($aRow['price'], get_base_currency());

	// Duration
	$row[] = $aRow['duration_minutes'] ? $aRow['duration_minutes'] . ' ' . _l('minutes') : '-';

	// Status
	$statusOutput = '<span class="label label-' . ($aRow['status'] == 'active' ? 'success' : 'danger') . '">' . _l($aRow['status']) . '</span>';
	$row[] = $statusOutput;

	// Options
	$options = '';

	$options .= '<a href="' . admin_url('hotel_management_system/services/service/' . $aRow['id']) . '" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>';

	// Toggle active/inactive status
	if ($aRow['status'] == 'active')
	{
		$options .= '<a href="' . admin_url('hotel_management_system/services/change_status/' . $aRow['id'] . '/inactive') . '" class="btn btn-icon" data-toggle="tooltip" title="' . _l('deactivate') . '"><i class="fa fa-toggle-on fa-2x"></i></a>';
	} else
	{
		$options .= '<a href="' . admin_url('hotel_management_system/services/change_status/' . $aRow['id'] . '/active') . '" class="btn btn-icon" data-toggle="tooltip" title="' . _l('activate') . '"><i class="fa fa-toggle-off fa-2x"></i></a>';
	}

	$options .= '<a href="' . admin_url('hotel_management_system/services/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon" onclick="return confirm(\'' . _l('confirm_delete_service') . '\');" data-toggle="tooltip" title="' . _l('delete') . '"><i class="fa fa-remove"></i></a>';

	$row[] = $options;

	$output['aaData'][] = $row;
}