<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if Hotel Management System module is enabled
 * @return boolean
 */
function is_hms_enabled()
{
	return get_option('hotel_management_system_enabled') == 1;
}

/**
 * Check if HMS booking is enabled in client area
 * @return boolean
 */
function is_hms_booking_enabled()
{
	return get_option('hotel_management_system_booking_enabled') == 1;
}

/**
 * Format booking reference number
 * @param integer $id booking ID
 * @return string
 */
function hms_format_booking_reference($id)
{
	return 'BK-' . str_pad($id, 6, '0', STR_PAD_LEFT);
}

/**
 * Get room availability status based on bookings
 * @param integer $room_id Room ID
 * @param date $start_date Check-in date
 * @param date $end_date Check-out date
 * @return boolean
 */
function hms_is_room_available($room_id, $start_date, $end_date)
{
	$CI = &get_instance();
	$CI->db->where('room_id', $room_id);
	$CI->db->where('booking_status !=', 'cancelled');

	// Check if there's any booking that overlaps with requested dates
	$CI->db->where('(
        (check_in_date <= "' . $start_date . '" AND check_out_date > "' . $start_date . '") OR 
        (check_in_date < "' . $end_date . '" AND check_out_date >= "' . $end_date . '") OR 
        (check_in_date >= "' . $start_date . '" AND check_out_date <= "' . $end_date . '")
    )');

	$result = $CI->db->get(db_prefix() . 'hms_bookings')->num_rows();

	return $result === 0;
}

/**
 * Calculate the number of nights between two dates
 * @param date $check_in_date Check-in date
 * @param date $check_out_date Check-out date
 * @return integer
 */
function hms_calculate_nights($check_in_date, $check_out_date)
{
	$start = new DateTime($check_in_date);
	$end = new DateTime($check_out_date);
	$interval = $start->diff($end);

	return $interval->days;
}

/**
 * Calculate booking total amount
 * @param object $data Booking data
 * @return array
 */
function hms_calculate_booking_total($data)
{
	$CI = &get_instance();

	// Get room details
	$CI->db->where('id', $data['room_id']);
	$room = $CI->db->get(db_prefix() . 'hms_rooms')->row();

	if ( ! $room)
	{
		return [
			'success' => FALSE,
			'message' => 'Room not found'
		];
	}

	// Calculate nights
	$nights = hms_calculate_nights($data['check_in_date'], $data['check_out_date']);

	if ($nights < 1)
	{
		return [
			'success' => FALSE,
			'message' => 'Check-out date must be after check-in date'
		];
	}

	// Calculate room cost
	$room_price = $room->price_per_night * $nights;

	// Add cleaning fee
	$cleaning_fee = $room->cleaning_fee;

	// Calculate additional services cost
	$additional_services = 0;
	if (isset($data['services']) && is_array($data['services']))
	{
		$CI->db->where_in('id', array_keys($data['services']));
		$services = $CI->db->get(db_prefix() . 'hms_services')->result_array();

		foreach ($services as $service)
		{
			$quantity = isset($data['services'][$service['id']]['quantity']) ? (int)$data['services'][$service['id']]['quantity'] : 1;
			$additional_services += $service['price'] * $quantity;
		}
	}

	// Calculate taxes
	$tax_rate = $room->tax_rate ?? get_option('hotel_management_system_default_tax_rate');
	$taxable_amount = $room_price + $additional_services;
	$taxes = ($taxable_amount * $tax_rate) / 100;

	// Calculate total
	$total_amount = $room_price + $cleaning_fee + $additional_services + $taxes;

	return [
		'success' => TRUE,
		'data' => [
			'nights' => $nights,
			'room_price' => $room_price,
			'cleaning_fee' => $cleaning_fee,
			'additional_services' => $additional_services,
			'tax_rate' => $tax_rate,
			'taxes' => $taxes,
			'total_amount' => $total_amount
		]
	];
}

/**
 * Generate invoice from booking
 * @param integer $booking_id Booking ID
 * @return integer|boolean
 */
function hms_generate_invoice($booking_id)
{
	$CI = &get_instance();
	$CI->load->model('invoices_model');
	$CI->load->model('payment_modes_model');
	$CI->load->model('room_model');

	$payment_modes = $CI->payment_modes_model->get('', [
		'expenses_only !=' => 1,
		'active =' => 1,
	]);
	$payment_modes = array_map(function ($payment_mode) {
		return $payment_mode['id'];
	}, $payment_modes);


	// Get booking details
	$CI->db->where('id', $booking_id);
	$booking = $CI->db->get(db_prefix() . 'hms_bookings')->row();

	if ( ! $booking)
	{
		return FALSE;
	}

	if ($booking->invoice_id)
	{
		return $booking->invoice_id;

	}

	// Get room details
	$CI->db->where('id', $booking->room_id);
	$room = $CI->db->get(db_prefix() . 'hms_rooms')->row();

	if ( ! $room)
	{
		return FALSE;
	}

	// Get property details
	$CI->db->where('id', $room->property_id);
	$property = $CI->db->get(db_prefix() . 'hms_properties')->row();

	if ( ! $property)
	{
		return FALSE;
	}

	// Find or create a client
	$client_id = $booking->client_id;

	if ( ! $client_id)
	{
		// Check if client exists with this email
		$CI->db->join(db_prefix() . 'contacts', db_prefix() . 'clients.userid = ' . db_prefix() . 'contacts.userid');
		$CI->db->where('email', $booking->guest_email);
		$client = $CI->db->get(db_prefix() . 'clients')->row();

		if ($client)
		{
			$client_id = $client->userid;
		} else
		{
			// Create new client
			$name_parts = explode(' ', $booking->guest_name);
			$client_data = [
				'company' => $booking->guest_name,
				'firstname' => explode(' ', $booking->guest_name)[0],
				'lastname' => count(explode(' ', $booking->guest_name)) > 1 ? end($name_parts) : '',
				'email' => $booking->guest_email,
				'phonenumber' => $booking->guest_phone,
				'datecreated' => date('Y-m-d H:i:s'),
				'active' => 1
			];

			$client_id = $CI->clients_model->add($client_data);

			// Update booking with new client ID
			$CI->db->where('id', $booking_id);
			$CI->db->update(db_prefix() . 'hms_bookings', ['client_id' => $client_id]);
		}
	}

	if ( ! $client_id)
	{
		return FALSE;
	}

	$invoice_number = sprintf('%d%d', $booking->id, $booking->room_id);
	$tax_rate = get_tax_by_name('Booking Tax');
	if ($tax_rate)
	{
		$tax_name = sprintf('%s|%s', $tax_rate->name, $tax_rate->taxrate);
		$tax_rate_value = $tax_rate->taxrate;
	} else
	{
		$tax_name = '';
		$tax_rate_value = 0;
	}

	// Prepare invoice data
	$invoice_data = [
		'number' => $invoice_number,
		'clientid' => $client_id,
		'date' => date('Y-m-d'),
		'duedate' => $booking->check_in_date,
		'status' => 1, // Unpaid
		'billing_street' => '',
		'billing_city' => '',
		'billing_state' => '',
		'billing_zip' => '',
		'billing_country' => '',
		'show_shipping_on_invoice' => 0,
		'shipping_street' => '',
		'shipping_city' => '',
		'shipping_state' => '',
		'shipping_zip' => '',
		'shipping_country' => '',
		'number_format' => get_option('invoice_number_format'),
		'currency' => get_base_currency()->id,
		'allowed_payment_modes' => array_values($payment_modes),
	];

	// Get booking rooms
	$CI->db->where('booking_id', $booking_id);
	$booking_rooms = $CI->db->get(db_prefix() . 'hms_booking_rooms')->result();

	$sub_total = floatval($booking->room_price);
	$invoice_items = [];
	foreach ($booking_rooms as $booking_room)
	{
		$room_details = $CI->room_model->get_room_with_details($booking_room->room_id);
		$invoice_items[] = [
			'description' => 'Room booking: ' . $room_details->property->name . ' - ' . $room_details->name . ' - ' . _l($room_details->room_type),
			'long_description' => 'Check-in: ' . $booking->check_in_date . ', Check-out: ' . $booking->check_out_date . ' (' . $booking->total_nights . ' nights)',
			'qty' => $booking_room->total_nights,
			'unit' => 'nights',
			'order' => $booking_room->total_nights,
			'rate' => $booking_room->room_price / $booking_room->total_nights,
			'taxname' => [$tax_name]
		];

		if ($room->cleaning_fee > 0)
		{
			$invoice_items[] = [
				'description' => 'Cleaning Fee (' . $room_details->property->name . ' - ' . $room_details->name . ' - ' . _l($room_details->room_type) . ')',
				'long_description' => 'One-time cleaning fee for room',
				'qty' => 1,
				'unit' => '',
				'order' => 1,
				'rate' => $booking_room->cleaning_fee,
				'taxname' => [$tax_name]
			];

			$sub_total += floatval($booking->cleaning_fee);
		}
	}

	$invoice_data['newitems'] = $invoice_items;

	// Add additional services
	if ($booking->additional_services > 0)
	{
		// Get booked services
		$CI->db->where('booking_id', $booking_id);
		$booked_services = $CI->db->get(db_prefix() . 'hms_booking_services')->result_array();

		foreach ($booked_services as $service)
		{
			$invoice_data['newitems'][] = [
				'description' => 'Additional Service: ' . hms_get_service_name($service['service_id']),
				'long_description' => $service['notes'] ?? '',
				'qty' => $service['quantity'],
				'unit' => '',
				'rate' => $service['price'],
				'order' => $booking->total_nights,
				'taxname' => [$tax_name]
			];

			$sub_total += floatval($service['price']);
		}
	}

	$invoice_data['subtotal'] = $sub_total;

	$taxes = ($sub_total * floatval($tax_rate_value)) / 100;
	$invoice_data['total'] = $sub_total + $taxes;

	// Create invoice
	$invoice_id = $CI->invoices_model->add($invoice_data);

	if ($invoice_id)
	{
		// Update booking with invoice ID
		$CI->db->where('id', $booking_id);
		$CI->db->update(db_prefix() . 'hms_bookings', ['invoice_id' => $invoice_id]);

		// Send invoice email if enabled
		if (get_option('hotel_management_system_send_invoice_email') == 1)
		{
			$CI->invoices_model->send_invoice_to_client($invoice_id);
		}
	}

	return $invoice_id;
}

/**
 * Get staff availability for service assignments
 * @param integer $staff_id Staff ID
 * @param integer $day_of_week Day of week (0=Sunday, 1=Monday, etc.)
 * @return array
 */
function hms_get_staff_availability($staff_id, $day_of_week)
{
	$CI = &get_instance();

	$CI->db->where('staff_id', $staff_id);
	$CI->db->where('day_of_week', $day_of_week);
	$CI->db->where('status', 'active');

	$assignments = $CI->db->get(db_prefix() . 'hms_service_assignments')->result_array();

	return $assignments;
}

/**
 * Get days of the week for dropdown
 * @return array
 */
function hms_get_days_of_week()
{
	return [
		0 => _l('sunday'),
		1 => _l('monday'),
		2 => _l('tuesday'),
		3 => _l('wednesday'),
		4 => _l('thursday'),
		5 => _l('friday'),
		6 => _l('saturday')
	];
}

/**
 * Get booking statuses for dropdown
 * @return array
 */
function hms_get_booking_statuses()
{
	return [
		'confirmed' => _l('confirmed'),
		'pending' => _l('pending'),
		'cancelled' => _l('cancelled'),
		'checked_in' => _l('checked_in'),
		'checked_out' => _l('checked_out'),
		'no_show' => _l('no_show')
	];
}

/**
 * Get payment statuses for dropdown
 * @return array
 */
function hms_get_payment_statuses()
{
	return [
		'pending' => _l('pending'),
		'partial' => _l('partial'),
		'paid' => _l('paid'),
		'overdue' => _l('overdue'),
		'refunded' => _l('refunded')
	];
}

/**
 * Get property types for dropdown
 * @return array
 */
function hms_get_property_types()
{
	return [
		'hotel' => _l('hotel'),
		'apartment' => _l('apartment'),
		'house' => _l('house'),
		'villa' => _l('villa'),
		'cottage' => _l('cottage'),
		'cabin' => _l('cabin'),
		'resort' => _l('resort'),
		'other' => _l('other')
	];
}

/**
 * Get room types for dropdown
 * @return array
 */
function hms_get_room_types()
{
	return [
		'single' => _l('single'),
		'double' => _l('double'),
		'twin' => _l('twin'),
		'triple' => _l('triple'),
		'quad' => _l('quad'),
		'queen' => _l('queen'),
		'king' => _l('king'),
		'suite' => _l('suite'),
		'studio' => _l('studio'),
		'apartment' => _l('apartment'),
		'other' => _l('other')
	];
}

/**
 * Get service types for dropdown
 * @return array
 */
function hms_get_service_types($keyValue = FALSE)
{
	if ($keyValue)
	{
		return [
			[
				'key' => 'cleaning',
				'value' => _l('cleaning')
			],
			[
				'key' => 'maintenance',
				'value' => _l('maintenance')
			],
			[
				'key' => 'food',
				'value' => _l('food')
			],
			[
				'key' => 'laundry',
				'value' => _l('laundry')
			],
			[
				'key' => 'spa',
				'value' => _l('spa')
			],
			[
				'key' => 'transfer',
				'value' => _l('transfer')
			],
			[
				'key' => 'tour',
				'value' => _l('tour')
			],
			[
				'key' => 'other',
				'value' => _l('other')
			],
		];
	} else
	{
		return [
			'cleaning' => _l('cleaning'),
			'maintenance' => _l('maintenance'),
			'food' => _l('food'),
			'laundry' => _l('laundry'),
			'spa' => _l('spa'),
			'transfer' => _l('transfer'),
			'tour' => _l('tour'),
			'other' => _l('other')
		];
	}

}


defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get property types as array
 * @return array
 */
function get_property_types()
{
	return [
		'hotel' => _l('hotel'),
		'apartment' => _l('apartment'),
		'villa' => _l('villa'),
		'resort' => _l('resort'),
		'house' => _l('house'),
		'other' => _l('other')
	];
}

/**
 * Get property statuses as array
 * @return array
 */
function get_property_statuses()
{
	return [
		'active' => _l('active'),
		'inactive' => _l('inactive'),
		'maintenance' => _l('maintenance')
	];
}

/**
 * Get common amenities as array
 * @return array
 */
function get_common_amenities()
{
	return [
		'wifi' => _l('wifi'),
		'parking' => _l('parking'),
		'pool' => _l('swimming_pool'),
		'air_conditioning' => _l('air_conditioning'),
		'heating' => _l('heating'),
		'gym' => _l('gym'),
		'spa' => _l('spa'),
		'restaurant' => _l('restaurant'),
		'bar' => _l('bar'),
		'business_center' => _l('business_center'),
		'laundry' => _l('laundry'),
		'room_service' => _l('room_service'),
		'shuttle' => _l('shuttle_service'),
		'disabled_access' => _l('disabled_access'),
		'elevator' => _l('elevator'),
		'security' => _l('security')
	];
}

/**
 * Format property address for display
 * @param object $property Property object
 * @return string
 */
function format_property_address($property)
{
	$address_parts = [];

	if ( ! empty($property->address))
	{
		$address_parts[] = $property->address;
	}

	$location_parts = [];

	if ( ! empty($property->city))
	{
		$location_parts[] = $property->city;
	}

	if ( ! empty($property->state))
	{
		$location_parts[] = $property->state;
	}

	if ( ! empty($property->postal_code))
	{
		$location_parts[] = $property->postal_code;
	}

	if ( ! empty($location_parts))
	{
		$address_parts[] = implode(', ', $location_parts);
	}

	if ( ! empty($property->country))
	{
		$address_parts[] = $property->country;
	}

	return implode('<br>', $address_parts);
}

/**
 * Get property featured image
 * @param mixed $property Property object or ID
 * @return string
 */
function get_property_featured_image($property)
{
	$CI = &get_instance();

	if ( ! is_object($property))
	{
		$CI->db->where('id', $property);
		$property = $CI->db->get(db_prefix() . 'hms_properties')->row();
	}

	if ( ! $property)
	{
		return '';
	}

	// Get featured image
	$CI->db->where('property_id', $property->id);
	$CI->db->where('is_featured', 1);
	$CI->db->limit(1);
	$featured_image = $CI->db->get(db_prefix() . 'hms_property_images')->row();

	if ($featured_image)
	{
		return $featured_image->path;
	}

	// If no featured image, get first image
	$CI->db->where('property_id', $property->id);
	$CI->db->limit(1);
	$first_image = $CI->db->get(db_prefix() . 'hms_property_images')->row();

	if ($first_image)
	{
		return $first_image->path;
	}

	// Return default image if no images found
	return base_url('modules/hotel_management_system/assets/images/default-property.jpg');
}

/**
 * Get property amenities as array or HTML list
 * @param mixed $property Property object or amenities array
 * @param boolean $as_html Whether to return as HTML list
 * @return mixed
 */
function get_property_amenities($property, $as_html = FALSE)
{
	$amenities = [];

	if (is_object($property) && isset($property->amenities))
	{
		if (is_string($property->amenities) && ! empty($property->amenities))
		{
			$amenities = @unserialize($property->amenities) !== FALSE ? unserialize($property->amenities) : [];
		} elseif (is_array($property->amenities))
		{
			$amenities = $property->amenities;
		}
	} elseif (is_array($property))
	{
		$amenities = $property;
	}

	if ($as_html && ! empty($amenities))
	{
		$all_amenities = get_common_amenities();
		$html = '<ul class="amenities-list">';

		foreach ($amenities as $amenity)
		{
			$label = isset($all_amenities[$amenity]) ? $all_amenities[$amenity] : $amenity;
			$html .= '<li><i class="fa fa-check-circle"></i> ' . $label . '</li>';
		}

		$html .= '</ul>';
		return $html;
	}

	return $amenities;
}

/**
 * Count property rooms
 * @param integer $property_id Property ID
 * @return integer
 */
function count_property_rooms($property_id)
{
	$CI = &get_instance();
	$CI->db->where('property_id', $property_id);
	return $CI->db->count_all_results(db_prefix() . 'hms_rooms');
}

/**
 * Count available rooms for property
 * @param integer $property_id Property ID
 * @return integer
 */
function count_available_rooms($property_id)
{
	$CI = &get_instance();
	$CI->db->where('property_id', $property_id);
	$CI->db->where('status', 'available');
	return $CI->db->count_all_results(db_prefix() . 'hms_rooms');
}

/**
 * Get property landlord
 * @param integer $property_id Property ID
 * @return object
 */
function get_property_landlord($property_id)
{
	$CI = &get_instance();

	$CI->db->select(db_prefix() . 'hms_landlords.*');
	$CI->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.landlord_id = ' . db_prefix() . 'hms_landlords.id', 'left');
	$CI->db->where(db_prefix() . 'hms_properties.id', $property_id);

	return $CI->db->get(db_prefix() . 'hms_landlords')->row();
}

/**
 * Check if property has active bookings
 * @param integer $property_id Property ID
 * @return boolean
 */
function property_has_active_bookings($property_id)
{
	$CI = &get_instance();

	$CI->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_bookings.room_id', 'left');
	$CI->db->where(db_prefix() . 'hms_rooms.property_id', $property_id);
	$CI->db->where(db_prefix() . 'hms_bookings.booking_status !=', 'cancelled');
	$CI->db->where(db_prefix() . 'hms_bookings.booking_status !=', 'checked_out');
	$CI->db->where(db_prefix() . 'hms_bookings.check_out_date >=', date('Y-m-d'));

	return $CI->db->count_all_results(db_prefix() . 'hms_bookings') > 0;
}

/**
 * Get property room types as array
 * @return array
 */
function get_room_types()
{
	return [
		'single' => _l('single_room'),
		'double' => _l('double_room'),
		'twin' => _l('twin_room'),
		'triple' => _l('triple_room'),
		'quad' => _l('quad_room'),
		'queen' => _l('queen_room'),
		'king' => _l('king_room'),
		'suite' => _l('suite'),
		'apartment' => _l('apartment'),
		'dormitory' => _l('dormitory'),
		'other' => _l('other')
	];
}

/**
 * Get room statuses as array
 * @return array
 */
function get_room_statuses()
{
	return [
		'available' => _l('available'),
		'occupied' => _l('occupied'),
		'maintenance' => _l('maintenance'),
		'reserved' => _l('reserved'),
		'inactive' => _l('inactive')
	];
}

/**
 * Get bed types as array
 * @return array
 */
function get_bed_types()
{
	return [
		'single' => _l('single_bed'),
		'double' => _l('double_bed'),
		'queen' => _l('queen_bed'),
		'king' => _l('king_bed'),
		'twin' => _l('twin_beds'),
		'bunk' => _l('bunk_beds'),
		'sofa' => _l('sofa_bed'),
		'other' => _l('other')
	];
}


/**
 * Get meal plans as array
 * @return array
 */
function get_meal_plans()
{
	return [
		'not' => _l('not_including'),
		'all_inclusive' => _l('all_inclusive'),
		'full_board' => _l('full_board'),
		'half_board' => _l('half_board'),
		'bed' => _l('bed'),
		'breakfast' => _l('breakfast'),
	];
}


/**
 * Get room amenities as array
 * @return array
 */
function get_room_amenities()
{
	return [
		'tv' => _l('tv'),
		'air_conditioning' => _l('air_conditioning'),
		'heating' => _l('heating'),
		'wifi' => _l('wifi'),
		'phone' => _l('phone'),
		'minibar' => _l('minibar'),
		'refrigerator' => _l('refrigerator'),
		'coffee_maker' => _l('coffee_maker'),
		'safe' => _l('safe'),
		'desk' => _l('desk'),
		'bathtub' => _l('bathtub'),
		'shower' => _l('shower'),
		'hairdryer' => _l('hairdryer'),
		'toiletries' => _l('toiletries'),
		'balcony' => _l('balcony'),
		'iron' => _l('iron'),
		'microwave' => _l('microwave'),
		'kitchenette' => _l('kitchenette')
	];
}

/**
 * Calculate room bookings revenue for a period
 * @param integer $room_id Room ID
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return float
 */
function calculate_room_revenue($room_id, $start_date, $end_date)
{
	$CI = &get_instance();

	$CI->db->select_sum('total_amount');
	$CI->db->where('room_id', $room_id);
	$CI->db->where('booking_status !=', 'cancelled');
	$CI->db->where('check_in_date >=', $start_date);
	$CI->db->where('check_in_date <=', $end_date);

	$result = $CI->db->get(db_prefix() . 'hms_bookings')->row();

	return $result ? $result->total_amount : 0;
}

/**
 * Calculate property bookings revenue for a period
 * @param integer $property_id Property ID
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return float
 */
function calculate_property_revenue($property_id, $start_date, $end_date)
{
	$CI = &get_instance();

	$CI->db->select('SUM(' . db_prefix() . 'hms_bookings.total_amount) as total');
	$CI->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_bookings.room_id', 'left');
	$CI->db->where(db_prefix() . 'hms_rooms.property_id', $property_id);
	$CI->db->where(db_prefix() . 'hms_bookings.booking_status !=', 'cancelled');
	$CI->db->where(db_prefix() . 'hms_bookings.check_in_date >=', $start_date);
	$CI->db->where(db_prefix() . 'hms_bookings.check_in_date <=', $end_date);

	$result = $CI->db->get(db_prefix() . 'hms_bookings')->row();

	return $result ? $result->total : 0;
}

/**
 * Get property occupancy rate for a period
 * @param integer $property_id Property ID
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return float
 */
function get_property_occupancy_rate($property_id, $start_date, $end_date)
{
	$CI = &get_instance();

	// Get total number of rooms for the property
	$CI->db->where('property_id', $property_id);
	$total_rooms = $CI->db->count_all_results(db_prefix() . 'hms_rooms');

	if ($total_rooms == 0)
	{
		return 0;
	}

	// Calculate the number of days in the period
	$start = new DateTime($start_date);
	$end = new DateTime($end_date);
	$interval = $start->diff($end);
	$days = $interval->days + 1; // Including both start and end dates

	// Get all bookings for this property in the period
	$CI->db->select(db_prefix() . 'hms_bookings.*, ' . db_prefix() . 'hms_rooms.property_id');
	$CI->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_bookings.room_id', 'left');
	$CI->db->where(db_prefix() . 'hms_rooms.property_id', $property_id);
	$CI->db->where(db_prefix() . 'hms_bookings.booking_status !=', 'cancelled');
	$CI->db->where('(' .
		'(' . db_prefix() . 'hms_bookings.check_in_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR ' .
		'(' . db_prefix() . 'hms_bookings.check_out_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR ' .
		'("' . $start_date . '" BETWEEN ' . db_prefix() . 'hms_bookings.check_in_date AND ' . db_prefix() . 'hms_bookings.check_out_date) OR ' .
		'("' . $end_date . '" BETWEEN ' . db_prefix() . 'hms_bookings.check_in_date AND ' . db_prefix() . 'hms_bookings.check_out_date)' .
		')');

	$bookings = $CI->db->get(db_prefix() . 'hms_bookings')->result_array();

	// Calculate total room-days and booked room-days
	$total_room_days = $total_rooms * $days;
	$booked_room_days = 0;

	foreach ($bookings as $booking)
	{
		$booking_start = max($start_date, $booking['check_in_date']);
		$booking_end = min($end_date, $booking['check_out_date']);

		$booking_start_dt = new DateTime($booking_start);
		$booking_end_dt = new DateTime($booking_end);
		$booking_days = $booking_start_dt->diff($booking_end_dt)->days + 1;

		$booked_room_days += $booking_days;
	}

	// Calculate occupancy rate
	$occupancy_rate = ($booked_room_days / $total_room_days) * 100;

	return round($occupancy_rate, 2);
}

/**
 * Check if staff member is available for assignment
 * @param integer $staff_id Staff ID
 * @param integer $day_of_week Day of week (0=Sunday, 1=Monday, etc.)
 * @param string $start_time Start time (HH:MM:SS)
 * @param string $end_time End time (HH:MM:SS)
 * @param integer $exclude_id Assignment ID to exclude (for updates)
 * @return boolean
 */
function hms_is_staff_available($staff_id, $day_of_week, $start_time, $end_time, $exclude_id = NULL)
{
	$CI = &get_instance();

	$CI->db->where('staff_id', $staff_id);
	$CI->db->where('day_of_week', $day_of_week);
	$CI->db->where('status', 'active');

	if ($exclude_id)
	{
		$CI->db->where('id !=', $exclude_id);
	}

	// Check for overlapping time slots
	$CI->db->where('(
        (start_time <= "' . $start_time . '" AND end_time > "' . $start_time . '") OR 
        (start_time < "' . $end_time . '" AND end_time >= "' . $end_time . '") OR 
        (start_time >= "' . $start_time . '" AND end_time <= "' . $end_time . '")
    )');

	$count = $CI->db->count_all_results(db_prefix() . 'hms_service_assignments');

	// If count is 0, staff is available
	return ($count == 0);
}

/**
 * Get staff's service assignments for a specific day
 * @param integer $staff_id Staff ID
 * @param integer $day_of_week Day of week (0=Sunday, 1=Monday, etc.)
 * @return array
 */
function hms_get_staff_assignments_by_day($staff_id, $day_of_week)
{
	$CI = &get_instance();

	$CI->db->select(db_prefix() . 'hms_service_assignments.*, ' .
		db_prefix() . 'hms_services.name as service_name, ' .
		db_prefix() . 'hms_rooms.name as room_name, ' .
		db_prefix() . 'hms_properties.name as property_name');
	$CI->db->from(db_prefix() . 'hms_service_assignments');
	$CI->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
	$CI->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
	$CI->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
	$CI->db->where(db_prefix() . 'hms_service_assignments.staff_id', $staff_id);
	$CI->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $day_of_week);
	$CI->db->where(db_prefix() . 'hms_service_assignments.status', 'active');
	$CI->db->order_by(db_prefix() . 'hms_service_assignments.start_time', 'asc');

	return $CI->db->get()->result_array();
}

/**
 * Get room's service assignments
 * @param integer $room_id Room ID
 * @return array
 */
function hms_get_room_service_assignments($room_id)
{
	$CI = &get_instance();

	$CI->db->select(db_prefix() . 'hms_service_assignments.*, ' .
		db_prefix() . 'hms_services.name as service_name, ' .
		db_prefix() . 'staff.firstname, ' .
		db_prefix() . 'staff.lastname');
	$CI->db->from(db_prefix() . 'hms_service_assignments');
	$CI->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
	$CI->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
	$CI->db->where(db_prefix() . 'hms_service_assignments.room_id', $room_id);
	$CI->db->where(db_prefix() . 'hms_service_assignments.status', 'active');
	$CI->db->order_by(db_prefix() . 'hms_service_assignments.day_of_week', 'asc');
	$CI->db->order_by(db_prefix() . 'hms_service_assignments.start_time', 'asc');

	return $CI->db->get()->result_array();
}

/**
 * Format service assignment time
 * @param string $start_time Start time
 * @param string $end_time End time
 * @return string
 */
function hms_format_service_time($start_time, $end_time)
{
	return date('H:i', strtotime($start_time)) . ' - ' . date('H:i', strtotime($end_time));
}

/**
 * Get services for booking options
 * @param string $service_type Optional service type filter
 * @return array
 */
function hms_get_bookable_services($service_type = '')
{
	$CI = &get_instance();

	$CI->db->where('status', 'active');

	if ($service_type != '')
	{
		$CI->db->where('service_type', $service_type);
	}

	$CI->db->order_by('name', 'asc');
	return $CI->db->get(db_prefix() . 'hms_services')->result_array();
}

/**
 * Get service price
 * @param integer $service_id Service ID
 * @return float
 */
function hms_get_service_price($service_id)
{
	$CI = &get_instance();

	$CI->db->select('price');
	$CI->db->where('id', $service_id);
	$result = $CI->db->get(db_prefix() . 'hms_services')->row();

	return $result ? $result->price : 0;
}


/**
 * Get service price
 * @param integer $service_id Service ID
 * @return float
 */
function hms_get_service_name($service_id)
{
	$CI = &get_instance();

	$CI->db->select('name');
	$CI->db->where('id', $service_id);
	$result = $CI->db->get(db_prefix() . 'hms_services')->row();

	return $result ? $result->name : '';
}


/**
 * Calculate total price for services
 * @param array $services Array of service IDs with quantities
 * @return float
 */
function hms_calculate_services_total($services)
{
	$CI = &get_instance();
	$total = 0;

	if ( ! empty($services))
	{
		foreach ($services as $service_id => $service_data)
		{
			$quantity = isset($service_data['quantity']) ? (int)$service_data['quantity'] : 1;

			$CI->db->select('price');
			$CI->db->where('id', $service_id);
			$result = $CI->db->get(db_prefix() . 'hms_services')->row();

			if ($result)
			{
				$total += $result->price * $quantity;
			}
		}
	}

	return $total;
}

/**
 * Get services by room
 * Returns services that are assigned to a specific room
 * @param integer $room_id Room ID
 * @return array
 */
function hms_get_services_by_room($room_id)
{
	$CI = &get_instance();

	$CI->db->select('DISTINCT ' . db_prefix() . 'hms_services.*');
	$CI->db->from(db_prefix() . 'hms_services');
	$CI->db->join(db_prefix() . 'hms_service_assignments', db_prefix() . 'hms_service_assignments.service_id = ' . db_prefix() . 'hms_services.id', 'left');
	$CI->db->where(db_prefix() . 'hms_service_assignments.room_id', $room_id);
	$CI->db->where(db_prefix() . 'hms_services.status', 'active');
	$CI->db->order_by(db_prefix() . 'hms_services.name', 'asc');

	return $CI->db->get()->result_array();
}

/**
 * Get services for a specific booking date
 * Returns services that have staff assigned for the specified date
 * @param integer $room_id Room ID
 * @param string $date Booking date (YYYY-MM-DD)
 * @return array
 */
function hms_get_available_services_for_date($room_id, $date)
{
	$CI = &get_instance();

	// Get day of week from date (0=Sunday, 1=Monday, etc.)
	$day_of_week = date('w', strtotime($date));

	$CI->db->select('DISTINCT ' . db_prefix() . 'hms_services.*');
	$CI->db->from(db_prefix() . 'hms_services');
	$CI->db->join(db_prefix() . 'hms_service_assignments', db_prefix() . 'hms_service_assignments.service_id = ' . db_prefix() . 'hms_services.id', 'left');
	$CI->db->where(db_prefix() . 'hms_service_assignments.room_id', $room_id);
	$CI->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $day_of_week);
	$CI->db->where(db_prefix() . 'hms_service_assignments.status', 'active');
	$CI->db->where(db_prefix() . 'hms_services.status', 'active');
	$CI->db->order_by(db_prefix() . 'hms_services.name', 'asc');

	return $CI->db->get()->result_array();
}

/**
 * Add service to booking
 * @param array $data Service booking data
 * @return integer|boolean
 */
function hms_add_booking_service($data)
{
	$CI = &get_instance();

	$data['datecreated'] = date('Y-m-d H:i:s');

	// Calculate total
	if ( ! isset($data['total']) && isset($data['price']) && isset($data['quantity']))
	{
		$data['total'] = $data['price'] * $data['quantity'];
	}

	$CI->db->insert(db_prefix() . 'hms_booking_services', $data);
	$insert_id = $CI->db->insert_id();

	if ($insert_id)
	{
		// Update booking total amount
		hms_update_booking_amount($data['booking_id']);

		return $insert_id;
	}

	return FALSE;
}

/**
 * Delete booking service
 * @param integer $id Booking service ID
 * @param integer $booking_id Booking ID
 * @return boolean
 */
function hms_delete_booking_service($id, $booking_id)
{
	$CI = &get_instance();

	$CI->db->where('id', $id);
	$CI->db->delete(db_prefix() . 'hms_booking_services');

	if ($CI->db->affected_rows() > 0)
	{
		// Update booking total amount
		hms_update_booking_amount($booking_id);

		return TRUE;
	}

	return FALSE;
}

/**
 * Update booking amount
 * Recalculates booking total amount including all services
 * @param integer $booking_id Booking ID
 * @return boolean
 */
function hms_update_booking_amount($booking_id)
{
	$CI = &get_instance();

	// Get booking
	$CI->db->where('id', $booking_id);
	$booking = $CI->db->get(db_prefix() . 'hms_bookings')->row();

	if ( ! $booking)
	{
		return FALSE;
	}

	// Calculate sum of all booking services
	$CI->db->select_sum('total');
	$CI->db->where('booking_id', $booking_id);
	$services_total = $CI->db->get(db_prefix() . 'hms_booking_services')->row()->total;

	// Update total_amount and additional_services fields
	$update_data = [
		'additional_services' => $services_total ? $services_total : 0,
		'total_amount' => ($booking->room_price + $booking->cleaning_fee + ($services_total ? $services_total : 0) + $booking->taxes)
	];

	$CI->db->where('id', $booking_id);
	$CI->db->update(db_prefix() . 'hms_bookings', $update_data);

	return $CI->db->affected_rows() > 0;
}