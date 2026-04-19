<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$aColumns = [
	db_prefix() . 'hms_bookings.id as id',
	'booking_reference',
	'guest_name',
	'guest_email',
	db_prefix() . 'hms_bookings.check_in_date as check_in_date',
	db_prefix() . 'hms_bookings.check_out_date as check_out_date',
	db_prefix() . 'hms_bookings.total_amount as total_amount',
	'booking_status',
	'payment_status',
	db_prefix() . 'hms_bookings.datecreated as datecreated',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'hms_bookings';

$join = [
	'LEFT JOIN ' . db_prefix() . 'hms_booking_rooms ON ' . db_prefix() . 'hms_bookings.id = ' . db_prefix() . 'hms_booking_rooms.booking_id',
	'LEFT JOIN ' . db_prefix() . 'hms_rooms ON ' . db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_booking_rooms.room_id',
	'LEFT JOIN ' . db_prefix() . 'hms_properties ON ' . db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id',
];

$additionalSelect = [
//	db_prefix() . 'hms_rooms.name as room_name',
	"GROUP_CONCAT(" . db_prefix() . "hms_rooms.name SEPARATOR ', ') AS room_name",
	db_prefix() . 'hms_properties.id as property_id',
	db_prefix() . 'hms_properties.name as property_name',
];

$groupByColumns = [
	db_prefix() . 'hms_bookings.id',
	'booking_reference',
	'guest_name',
	'guest_email',
	db_prefix() . 'hms_bookings.check_in_date',
	db_prefix() . 'hms_bookings.check_out_date',
	db_prefix() . 'hms_bookings.total_amount',
	'booking_status',
	'payment_status',
	db_prefix() . 'hms_bookings.datecreated',
	db_prefix() . 'hms_properties.id',
	db_prefix() . 'hms_properties.name',
];
$groupBy = 'GROUP BY ' . implode(', ', $groupByColumns);

$where = [];

// Filters
$filter_booking_status = $this->ci->input->post('booking_status');
if ($filter_booking_status && $filter_booking_status != '')
{
	$where[] = 'AND ' . db_prefix() . 'hms_bookings.booking_status = "' . $filter_booking_status . '"';
}

$filter_payment_status = $this->ci->input->post('payment_status');
if ($filter_payment_status && $filter_payment_status != '')
{
	$where[] = 'AND ' . db_prefix() . 'hms_bookings.payment_status = "' . $filter_payment_status . '"';
}

$filter_property = $this->ci->input->post('property');
if ($filter_property && $filter_property != '')
{
	$where[] = 'AND ' . db_prefix() . 'hms_properties.id = ' . $filter_property;
}

$filter_date_from = $this->ci->input->post('date_from');
$filter_date_to = $this->ci->input->post('date_to');
if ($filter_date_from && $filter_date_to)
{
	$where[] = 'AND (' .
		'(check_in_date BETWEEN "' . $filter_date_from . '" AND "' . $filter_date_to . '") OR ' .
		'(check_out_date BETWEEN "' . $filter_date_from . '" AND "' . $filter_date_to . '") OR ' .
		'("' . $filter_date_from . '" BETWEEN check_in_date AND check_out_date) OR ' .
		'("' . $filter_date_to . '" BETWEEN check_in_date AND check_out_date)' .
		')';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect, $groupBy);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// ID
	$row[] = $aRow['id'];

	// Booking Reference
	$bookingOutput = '<a href="' . admin_url('hotel_management_system/bookings/view/' . $aRow['id']) . '">' . $aRow['booking_reference'] . '</a>';

	// Add status label
	$bookingStatus = '';
	switch ($aRow['booking_status'])
	{
		case 'confirmed':
			$bookingStatus = '<span class="label label-info inline-block mleft5">' . _l('confirmed') . '</span>';
			break;
		case 'checked_in':
			$bookingStatus = '<span class="label label-success inline-block mleft5">' . _l('checked_in') . '</span>';
			break;
		case 'checked_out':
			$bookingStatus = '<span class="label label-default inline-block mleft5">' . _l('checked_out') . '</span>';
			break;
		case 'cancelled':
			$bookingStatus = '<span class="label label-danger inline-block mleft5">' . _l('cancelled') . '</span>';
			break;
		case 'no_show':
			$bookingStatus = '<span class="label label-warning inline-block mleft5">' . _l('no_show') . '</span>';
			break;
	}

	$bookingOutput .= $bookingStatus;
	$row[] = $bookingOutput;

	// Room & Property
	$property_url = admin_url('hotel_management_system/properties/property/' . $aRow['property_id']);
	$row[] = $aRow['room_name'] . '<br><small><a href="' . $property_url . '">' . $aRow['property_name'] . '</a></small>';

	// Guest Name
	$row[] = $aRow['guest_name'];

	// Guest Email
	$row[] = $aRow['guest_email'];

	// Check In/Out Dates
	$row[] = _d($aRow['check_in_date']);
	$row[] = _d($aRow['check_out_date']);

	// Total Amount
	$row[] = app_format_money($aRow['total_amount'], get_base_currency());

	// Booking Status
	$statusLabel = '';
	switch ($aRow['booking_status'])
	{
		case 'confirmed':
			$statusLabel = '<span class="label label-info">' . _l('confirmed') . '</span>';
			break;
		case 'checked_in':
			$statusLabel = '<span class="label label-success">' . _l('checked_in') . '</span>';
			break;
		case 'checked_out':
			$statusLabel = '<span class="label label-default">' . _l('checked_out') . '</span>';
			break;
		case 'cancelled':
			$statusLabel = '<span class="label label-danger">' . _l('cancelled') . '</span>';
			break;
		case 'no_show':
			$statusLabel = '<span class="label label-warning">' . _l('no_show') . '</span>';
			break;
		default:
			$statusLabel = '<span class="label label-default">' . $aRow['booking_status'] . '</span>';
	}
	$row[] = $statusLabel;

	// Payment Status
	$paymentStatusLabel = '';
	switch ($aRow['payment_status'])
	{
		case 'paid':
			$paymentStatusLabel = '<span class="label label-success">' . _l('paid') . '</span>';
			break;
		case 'partial':
			$paymentStatusLabel = '<span class="label label-info">' . _l('partial') . '</span>';
			break;
		case 'pending':
			$paymentStatusLabel = '<span class="label label-warning">' . _l('pending') . '</span>';
			break;
		case 'overdue':
			$paymentStatusLabel = '<span class="label label-danger">' . _l('overdue') . '</span>';
			break;
		case 'refunded':
			$paymentStatusLabel = '<span class="label label-default">' . _l('refunded') . '</span>';
			break;
		default:
			$paymentStatusLabel = '<span class="label label-default">' . $aRow['payment_status'] . '</span>';
	}
	$row[] = $paymentStatusLabel;

	// Date Created
	$row[] = _dt($aRow['datecreated']);

	// Options
	$options = '<div class="tw-flex tw-gap-0.5 tw-flex-nowrap">';
	$options .= '<a href="' . admin_url('hotel_management_system/bookings/view/' . $aRow['id']) . '" class="btn btn-default btn-icon" data-toggle="tooltip" title="' . _l('view') . '"><i class="fa fa-eye"></i></a>';
	$options .= '<a href="' . admin_url('hotel_management_system/bookings/booking/' . $aRow['id']) . '" class="btn btn-default btn-icon" data-toggle="tooltip" title="' . _l('edit') . '"><i class="fa fa-pencil"></i></a>';
	$options .= '<a href="' . admin_url('hotel_management_system/bookings/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete" data-toggle="tooltip" title="' . _l('delete') . '"><i class="fa fa-remove"></i></a>';
	$options .= '</div>';
	$row[] = $options;

	$output['aaData'][] = $row;
}