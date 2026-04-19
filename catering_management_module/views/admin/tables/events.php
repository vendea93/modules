<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'eventid',
	'event_name',
	'COALESCE('.db_prefix().'clients.company, '.db_prefix().'leads.name) as client_name',
	db_prefix().'catering_event_types.name as event_type_name',
	'event_start',
	'venue_name',
	'guest_count_expected',
	db_prefix().'catering_events.status as status',
];

$sIndexColumn = 'eventid';
$sTable = db_prefix().'catering_events';

$join = [
	'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id',
	'LEFT JOIN '.db_prefix().'leads ON '.db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id',
	'LEFT JOIN '.db_prefix().'catering_event_types ON '.db_prefix().'catering_event_types.etid = '.db_prefix().'catering_events.event_type_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
	db_prefix().'catering_events.hash as hash',
	db_prefix().'clients.userid as client_id',
	'event_end',
	'guest_count_final',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// Event ID
	$row[] = $aRow['eventid'];

	// Event Name with link
	$eventUrl = admin_url('catering_management_module/events/view/'.$aRow['eventid']);
	$row[] = '<a href="'.$eventUrl.'" class="tw-font-medium">'.$aRow['event_name'].'</a>';

	// Client/Lead
	$clientName = $aRow['client_name'] ?: '-';
	if ($aRow['client_id'])
	{
		$clientUrl = admin_url('clients/client/'.$aRow['client_id']);
		$clientName = '<a href="'.$clientUrl.'">'.$clientName.'</a>';
	}
	$row[] = $clientName;

	// Event Type
	$row[] = $aRow['event_type_name'] ?: '-';

	// Event Start Date & Time
	$row[] = '<span data-toggle="tooltip" title="'._dt($aRow['event_start']).'">'
		._d($aRow['event_start']).'</span>';

	// Venue
	$row[] = $aRow['venue_name'] ?: '-';

	// Guest Count
	$guestDisplay = $aRow['guest_count_expected'];
	if ($aRow['guest_count_final'])
	{
		$guestDisplay .= ' <i class="fa fa-arrow-right"></i> <strong>'.$aRow['guest_count_final'].'</strong>';
	}
	$row[] = $guestDisplay;

	// Status Badge
	$statusColors = [
		'enquiry' => 'info',
		'quoted' => 'primary',
		'confirmed' => 'success',
		'in_progress' => 'warning',
		'completed' => 'default',
		'cancelled' => 'danger',
		'lost' => 'muted',
	];

	$statusColor = $statusColors[$aRow['status']] ?? 'default';
	$statusLabel = _l('event_status_'.$aRow['status']);

	$row[] = '<span class="label label-'.$statusColor.'">'.$statusLabel.'</span>';

	// Actions
	$options = '';

	if (staff_can('view', 'catering'))
	{
		$options .= '<a href="'.$eventUrl.'" class="btn btn-default btn-icon" data-toggle="tooltip" title="'._l('view').'">
            <i class="fa fa-eye"></i>
        </a> ';
	}

	if (staff_can('edit', 'catering'))
	{
		$options .= '<a href="'.admin_url('catering_management_module/events/event/'.$aRow['eventid']).'" class="btn btn-default btn-icon" data-toggle="tooltip" title="'._l('edit').'">
            <i class="fa fa-pencil"></i>
        </a> ';
	}

	if (staff_can('delete', 'catering'))
	{
		$options .= '<a href="'.admin_url('catering_management_module/events/delete_event/'.$aRow['eventid']).'" class="btn btn-danger btn-icon _delete" data-toggle="tooltip" title="'._l('delete').'">
            <i class="fa fa-trash"></i>
        </a>';
	}

	$row[] = $options;

	$output['aaData'][] = $row;

}