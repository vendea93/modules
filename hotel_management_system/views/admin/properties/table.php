<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	db_prefix() . 'hms_properties.id as property_id',
	db_prefix() . 'hms_properties.name as name',
	db_prefix() . 'hms_landlords.name as landlord_name',
	db_prefix() . 'hms_properties.address as address',
	db_prefix() . 'hms_properties.city as city',
	db_prefix() . 'hms_properties.postal_code as postal_code',
	db_prefix() . 'hms_properties.country as country',
	'status',
];

$sIndexColumn = 'property_id';
$sTable = db_prefix() . 'hms_properties';

$join = [
	'LEFT JOIN ' . db_prefix() . 'hms_landlords ON ' . db_prefix() . 'hms_landlords.id = ' . db_prefix() . 'hms_properties.landlord_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['featured']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// ID
	$row[] = $aRow['property_id'];

	// Property Name
	$property_name = '<a href="' . admin_url('hotel_management_system/properties/property/' . $aRow['property_id']) . '">' . $aRow['name'] . '</a>';
	if ($aRow['featured'] == 1)
	{
		$property_name .= ' <span class="label label-success">' . _l('featured') . '</span>';
	}
	$row[] = $property_name;

	// Landlord Name
	$row[] = $aRow['landlord_name'];

	// Address
	$row[] = $aRow['address'];

	// City
	$row[] = $aRow['city'];

	// Postal Code
	$row[] = $aRow['postal_code'];

	// Country
	$row[] = $aRow['country'];

	// Status
	$status_classes = [
		'active' => 'success',
		'inactive' => 'warning',
		'maintenance' => 'danger',
	];
	$status_class = isset($status_classes[$aRow['status']]) ? $status_classes[$aRow['status']] : 'default';
	$row[] = '<span class="label label-' . $status_class . '">' . _l($aRow['status']) . '</span>';

	// Options
	$options = '<div class="tw-flex tw-gap-0.5 tw-no-wrap">';

	// View rooms button
	$options .= '<a href="' . admin_url('hotel_management_system/rooms/index/' . $aRow['property_id']) . '" class="btn btn-default btn-icon tw-whitespace-nowrap" title="' . _l('view_rooms') . '"><i class="fa fa-bed"></i></a> ';
	$options .= '<a href="' . admin_url('hotel_management_system/properties/property/' . $aRow['property_id']) . '" class="btn btn-default btn-icon" title="' . _l('edit') . '"><i class="fa fa-pencil"></i></a> ';
	$options .= '<a href="' . admin_url('hotel_management_system/properties/delete/' . $aRow['property_id']) . '" class="btn btn-danger btn-icon _delete" title="' . _l('delete') . '"><i class="fa fa-remove"></i></a>';

	$options .= '</div>';

	$row[] = $options;

	$output['aaData'][] = $row;
}