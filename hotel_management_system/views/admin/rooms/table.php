<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$aColumns = [
	db_prefix() . 'hms_rooms.id as id',
	db_prefix() . 'hms_rooms.name as room_name',
	'property_id',
	'room_type',
	'capacity',
	'price_per_night',
	db_prefix() . 'hms_rooms.status as status',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'hms_rooms';

$join = [
	'LEFT JOIN ' . db_prefix() . 'hms_properties ON ' . db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id',
];

$additionalSelect = [
	db_prefix() . 'hms_properties.name as property_name',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow)
{
	$row = [];

	// ID
	$row[] = $aRow['id'];

	// Get room images
	$CI = &get_instance();
	$CI->db->where('room_id', $aRow['id']);
	$CI->db->order_by('is_featured', 'DESC');
	$CI->db->order_by('sort_order', 'ASC');
	$room_pictures = $CI->db->get(db_prefix() . 'hms_room_images')->result_array();

	$pictures_class = 'room-pictures-' . $aRow['id'];
	$picture_elements = [];
	if (count($room_pictures) > 3)
	{
		$room_pictures = array_slice($room_pictures, 0, 3);
	}
	foreach ($room_pictures as $picture)
	{
		$picture_url = base_url($picture['path']);
		$picture_element = '<a href="' . $picture_url . '" target="_blank" data-lightbox="' . $pictures_class . '" class="">';
		$picture_element .= '<img style="width: 40px; height: auto;" src="' . $picture_url . '" class="img img-responsive"></a>';
		$picture_elements[] = $picture_element;
	}

	$row[] = '<div class="tw-flex tw-gap-2">' . implode('', $picture_elements) . '</div>';

	// Name
	$nameOutput = '<a href="' . admin_url('hotel_management_system/rooms/room/' . $aRow['id']) . '">' . $aRow['room_name'] . '</a>';
	$row[] = $nameOutput;

	// Property
	$row[] = '<a href="' . admin_url('hotel_management_system/properties/view/' . $aRow['property_id']) . '">' . $aRow['property_name'] . '</a>';

	// Room Type
	$roomTypes = get_room_types();
	$roomTypeText = isset($roomTypes[$aRow['room_type']]) ? $roomTypes[$aRow['room_type']] : '-';
	$row[] = $roomTypeText;

	// Capacity
	$row[] = $aRow['capacity'];

	// Price Per Night
	$row[] = app_format_money($aRow['price_per_night'], get_base_currency());

	// Status
	$statusClass = 'info';
	if ($aRow['status'] == 'available')
	{
		$statusClass = 'success';
	} else if ($aRow['status'] == 'occupied')
	{
		$statusClass = 'danger';
	} else if ($aRow['status'] == 'maintenance')
	{
		$statusClass = 'warning';
	} else if ($aRow['status'] == 'inactive')
	{
		$statusClass = 'default';
	}

	$statusHtml = '<span class="label label-' . $statusClass . '">' . ucfirst($aRow['status']) . '</span>';
	$row[] = $statusHtml;

	// Options
	$options = icon_btn('hotel_management_system/rooms/room/' . $aRow['id'], 'fa fa-pencil');
	$options .= icon_btn('hotel_management_system/rooms/delete/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete');

	$row[] = $options;

	$output['aaData'][] = $row;
}