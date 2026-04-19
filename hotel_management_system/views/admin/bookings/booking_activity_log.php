<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$aColumns = [
	'date',
	'description',
	db_prefix() . 'staff.firstname as staff_firstname',
	db_prefix() . 'staff.lastname as staff_lastname',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'activity_log';

$join = [
	'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'activity_log.staff_id',
];

$where = [
	'AND (' . db_prefix() . 'activity_log.description LIKE "%' . $this->ci->db->escape_like_str('booking') . '%" OR ' . db_prefix() . 'activity_log.description LIKE "%' . $this->ci->db->escape_like_str('Booking') . '%")',
];

// Filter for specific booking
if (isset($booking_id)) {
	array_push($where, 'AND (' . db_prefix() . 'activity_log.description LIKE "%ID: ' . $booking_id . '%" OR ' . db_prefix() . 'activity_log.description LIKE "%Reference: ' . $this->ci->db->escape_like_str(hms_format_booking_reference($booking_id)) . '%")');
}

$additionalSelect = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
	$row = [];

	// Date
	$row[] = _dt($aRow['date']);

	// Description
	$row[] = $aRow['description'];

	// Staff
	$row[] = $aRow['staff_firstname'] . ' ' . $aRow['staff_lastname'];

	$output['aaData'][] = $row;
}