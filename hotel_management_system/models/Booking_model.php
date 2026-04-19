<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking_model extends App_Model {
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Add new booking
	 * @param array $data Booking data
	 * @return integer|boolean
	 */
	public function add($data)
	{
		// Calculate total nights
		$check_in = new DateTime($data['check_in_date']);
		$check_out = new DateTime($data['check_out_date']);
		$interval = $check_in->diff($check_out);
		$data['total_nights'] = $interval->days;

		// Generate booking reference
		$data['booking_reference'] = $this->generate_booking_reference();

		// Set default values
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();

		// Initialize additional services total
		$additional_services_total = 0;

		// Process additional services if any
		$services_data = [];
		if (isset($data['services']) && is_array($data['services']))
		{
			foreach ($data['services'] as $service_id => $service_data)
			{
				if ( ! empty($service_data['quantity']))
				{
					// Get service details
					$this->db->where('id', $service_id);
					$service = $this->db->get(db_prefix() . 'hms_services')->row();

					if ($service)
					{
						$service_total = $service->price * $service_data['quantity'];
						$additional_services_total += $service_total;

						$service_booking = [
							'service_id' => $service_id,
							'staff_id' => isset($service_data['staff_id']) ? $service_data['staff_id'] : NULL,
							'service_date' => isset($service_data['date']) ? $service_data['date'] : $data['check_in_date'],
							'service_time' => isset($service_data['time']) ? $service_data['time'] : NULL,
							'quantity' => $service_data['quantity'],
							'price' => $service->price,
							'total' => $service_total,
							'status' => 'pending',
							'notes' => isset($service_data['notes']) ? $service_data['notes'] : NULL,
							'datecreated' => date('Y-m-d H:i:s')
						];

						$services_data[] = $service_booking;
					}
				}
			}
		}

		// Remove services from booking data to avoid DB insertion errors
		unset($data['services']);

		// Booking rooms
		$booking_rooms = $data['rooms'];
		unset($data['rooms']);

		// Calculate total amount
		$data['additional_services'] = $additional_services_total;
		// Make sure we have numeric values for proper calculation
		$room_price = floatval($data['room_price']);
		$cleaning_fee = floatval($data['cleaning_fee']);
		$additional_services = floatval($data['additional_services']);
		$taxes = floatval($data['taxes']);

		// Calculate total amount
		$data['total_amount'] = $room_price + $cleaning_fee + $additional_services + $taxes;

		// Save booking
		$this->db->insert(db_prefix() . 'hms_bookings', $data);
		$booking_id = $this->db->insert_id();

		if ($booking_id)
		{
			// Add services to booking
			foreach ($services_data as $service)
			{
				$service['booking_id'] = $booking_id;
				$this->db->insert(db_prefix() . 'hms_booking_services', $service);
			}

			foreach ($booking_rooms as $room)
			{
				$room['booking_id'] = $booking_id;
				$this->db->insert(db_prefix() . 'hms_booking_rooms', $room);
			}

			// Generate invoice if enabled
			if (get_option('hotel_management_system_send_invoice_email') == 1)
			{
				$invoice_id = hms_generate_invoice($booking_id);

				if ($invoice_id)
				{
					// Update booking with invoice ID
					$this->db->where('id', $booking_id);
					$this->db->update(db_prefix() . 'hms_bookings', ['invoice_id' => $invoice_id]);
				}
			}

			log_activity('New Booking Created [ID: ' . $booking_id . ', Reference: ' . $data['booking_reference'] . ']');
			return $booking_id;
		}

		return FALSE;
	}

	/**
	 * Generate unique booking reference
	 * @return string
	 */
	public function generate_booking_reference()
	{
		$prefix = 'BK-';
		$unique = FALSE;
		$reference = '';

		while ( ! $unique)
		{
			$reference = $prefix . strtoupper(substr(md5(mt_rand()), 0, 8));

			// Check if reference already exists
			$this->db->where('booking_reference', $reference);
			$count = $this->db->count_all_results(db_prefix() . 'hms_bookings');

			if ($count == 0)
			{
				$unique = TRUE;
			}
		}

		return $reference;
	}

	/**
	 * Get booking by ID
	 * @param mixed $id Booking ID
	 * @return mixed
	 */
	public function get($id = '')
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . 'hms_bookings');

		if (is_numeric($id))
		{
			$this->db->where('id', $id);
			$booking = $this->db->get()->row();
			$booking->rooms = $this->get_booking_rooms($id);

			return $booking;
		}

		$bookings = $this->db->get()->result_array();
		return array_map(function ($booking) {
			$booking['rooms'] = $this->get_booking_rooms($booking['id']);

			return $booking;
		}, $bookings);
	}

	public function get_booking_rooms($id, $as_array = FALSE)
	{
		$this->db->select('room_id');
		$this->db->from(db_prefix() . 'hms_booking_rooms');
		$this->db->where('booking_id', $id);

		return $as_array ? $this->db->get()->result_array() : $this->db->get()->result();
	}

	/**
	 * Update booking
	 * @param array $data Booking data
	 * @param mixed $id Booking ID
	 * @return boolean
	 */
	public function update($data, $id)
	{
		// Calculate total nights
		$check_in = new DateTime($data['check_in_date']);
		$check_out = new DateTime($data['check_out_date']);
		$interval = $check_in->diff($check_out);
		$data['total_nights'] = $interval->days;

		// Set update fields
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		// Initialize additional services total
		$additional_services_total = 0;

		// Handle services separately
		$services_data = [];
		if (isset($data['services']) && is_array($data['services']))
		{
			foreach ($data['services'] as $service_id => $service_data)
			{
				if ( ! empty($service_data['quantity']))
				{
					// Get service details
					$this->db->where('id', $service_id);
					$service = $this->db->get(db_prefix() . 'hms_services')->row();

					if ($service)
					{
						$service_total = $service->price * $service_data['quantity'];
						$additional_services_total += $service_total;

						$service_booking = [
							'service_id' => $service_id,
							'staff_id' => isset($service_data['staff_id']) ? $service_data['staff_id'] : NULL,
							'service_date' => isset($service_data['date']) ? $service_data['date'] : $data['check_in_date'],
							'service_time' => isset($service_data['time']) ? $service_data['time'] : NULL,
							'quantity' => $service_data['quantity'],
							'price' => $service->price,
							'total' => $service_total,
							'status' => 'pending',
							'notes' => isset($service_data['notes']) ? $service_data['notes'] : NULL,
							'datecreated' => date('Y-m-d H:i:s')
						];

						$services_data[] = $service_booking;
					}
				}
			}
		}

		// Remove services from booking data to avoid DB insertion errors
		unset($data['services']);

		// Booking rooms
		$booking_rooms = $data['rooms'];
		unset($data['rooms']);

		// Calculate total amount
		$data['additional_services'] = $additional_services_total;

		// Make sure we have numeric values for proper calculation
		$room_price = floatval($data['room_price']);
		$cleaning_fee = floatval($data['cleaning_fee']);
		$additional_services = floatval($data['additional_services']);
		$taxes = floatval($data['taxes']);

		// Calculate total amount
		$data['total_amount'] = $room_price + $cleaning_fee + $additional_services + $taxes;

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_bookings', $data);

		$updated = ($this->db->affected_rows() > 0);

		// Process additional services if any
		if ( ! empty($services_data))
		{
			// First delete existing services
			$this->db->where('booking_id', $id);
			$this->db->delete(db_prefix() . 'hms_booking_services');

			// Then add new services
			foreach ($services_data as $service)
			{
				$service['booking_id'] = $id;
				$this->db->insert(db_prefix() . 'hms_booking_services', $service);
				$updated = TRUE;
			}
		}

		if ($updated)
		{
			log_activity('Booking Updated [ID: ' . $id . ']');
			$this->db->where('booking_id', $id);
			$this->db->delete(db_prefix() . 'hms_booking_rooms');
			$this->db->reset_query();

			foreach ($booking_rooms as $room)
			{
				$room['booking_id'] = $id;
				$this->db->insert(db_prefix() . 'hms_booking_rooms', $room);
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete booking
	 * @param mixed $id Booking ID
	 * @return boolean
	 */
	public function delete($id)
	{
		// Get booking before deleting
		$this->db->where('id', $id);
		$booking = $this->db->get(db_prefix() . 'hms_bookings')->row();

		if ( ! $booking)
		{
			return [
				'success' => FALSE,
				'message' => _l('booking_not_found')
			];
		}

		// Check if booking is in the past or has invoice
		if (strtotime($booking->check_in_date) < strtotime(date('Y-m-d')) &&
			$booking->booking_status != 'cancelled')
		{
			return [
				'success' => FALSE,
				'message' => _l('cannot_delete_past_booking')
			];
		}

		// Start transaction
		$this->db->trans_start();

		// Delete booking services
		$this->db->where('booking_id', $id);
		$this->db->delete(db_prefix() . 'hms_booking_services');

		// Delete booking
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_bookings');

		// Complete transaction
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			return [
				'success' => FALSE,
				'message' => _l('problem_deleting_booking')
			];
		}

		log_activity('Booking Deleted [ID: ' . $id . ', Reference: ' . $booking->booking_reference . ']');

		return [
			'success' => TRUE,
			'message' => _l('booking_deleted_successfully')
		];
	}

	/**
	 * Change booking status
	 * @param integer $id Booking ID
	 * @param string $status New status
	 * @return boolean
	 */
	public function change_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'booking_status' => $status,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Booking Status Changed [ID: ' . $id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Change payment status
	 * @param integer $id Booking ID
	 * @param string $status New status
	 * @return boolean
	 */
	public function change_payment_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'payment_status' => $status,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Booking Payment Status Changed [ID: ' . $id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get bookings by client
	 * @param integer $client_id Client ID
	 * @return array
	 */
	public function get_client_bookings($client_id)
	{
		return $this->get_all(['client_id' => $client_id]);
	}

	/**
	 * Get all bookings with optional filtering
	 * @param array $where Optional where clause
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select(db_prefix() . 'hms_bookings.*, ' .
			db_prefix() . 'hms_rooms.name as room_name, ' .
			db_prefix() . 'hms_properties.name as property_name, ' .
			db_prefix() . 'clients.company as client_company');
		$this->db->from(db_prefix() . 'hms_bookings');
		$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_bookings.room_id', 'left');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
		$this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'hms_bookings.client_id', 'left');

		if ( ! empty($where))
		{
			$this->db->where($where);
		}

		$this->db->order_by('check_in_date', 'desc');

		return $this->db->get()->result_array();
	}

	/**
	 * Get bookings by room
	 * @param integer $room_id Room ID
	 * @return array
	 */
	public function get_room_bookings($room_id)
	{
		return $this->get_all(['room_id' => $room_id]);
	}

	/**
	 * Get booking statistics
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return array
	 */
	public function get_booking_statistics($start_date = '', $end_date = '')
	{
		if ( ! $start_date)
		{
			$start_date = date('Y-m-01'); // First day of current month
		}

		if ( ! $end_date)
		{
			$end_date = date('Y-m-t'); // Last day of current month
		}

		// Get bookings for the period
		$bookings = $this->get_bookings_by_date_range($start_date, $end_date);

		// Get total number of rooms
		$this->db->where('status', 'available');
		$total_rooms = $this->db->count_all_results(db_prefix() . 'hms_rooms');

		// Calculate statistics
		$total_bookings = count($bookings);
		$total_revenue = 0;
		$total_nights = 0;
		$booking_statuses = [
			'confirmed' => 0,
			'pending' => 0,
			'cancelled' => 0,
			'checked_in' => 0,
			'checked_out' => 0,
			'no_show' => 0
		];

		$payment_statuses = [
			'pending' => 0,
			'partial' => 0,
			'paid' => 0,
			'overdue' => 0,
			'refunded' => 0
		];

		foreach ($bookings as $booking)
		{
			$total_revenue += $booking['total_amount'];
			$total_nights += $booking['total_nights'];

			// Count booking statuses
			if (isset($booking_statuses[$booking['booking_status']]))
			{
				$booking_statuses[$booking['booking_status']]++;
			}

			// Count payment statuses
			if (isset($payment_statuses[$booking['payment_status']]))
			{
				$payment_statuses[$booking['payment_status']]++;
			}
		}

		// Calculate occupancy rate
		$days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
		$total_room_days = $total_rooms * $days;
		$occupancy_rate = $total_room_days > 0 ? ($total_nights / $total_room_days) * 100 : 0;

		return [
			'total_bookings' => $total_bookings,
			'total_revenue' => $total_revenue,
			'total_nights' => $total_nights,
			'occupancy_rate' => round($occupancy_rate, 2),
			'booking_statuses' => $booking_statuses,
			'payment_statuses' => $payment_statuses
		];
	}

	/**
	 * Get bookings by date range
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return array
	 */
	public function get_bookings_by_date_range($start_date, $end_date)
	{
		$this->db->where('(
            (check_in_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR
            (check_out_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR
            ("' . $start_date . '" BETWEEN check_in_date AND check_out_date) OR
            ("' . $end_date . '" BETWEEN check_in_date AND check_out_date)
        )');

		return $this->get_all();
	}

	/**
	 * Get recent bookings
	 * @param integer $limit Limit number of bookings
	 * @return array
	 */
	public function get_recent_bookings($limit = 10)
	{
		$this->db->limit($limit);
		$this->db->order_by('datecreated', 'desc');
		return $this->get_all();
	}

	/**
	 * Get bookings calendar data
	 * @param string $start Start date
	 * @param string $end End date
	 * @return array
	 */
	public function get_calendar_data($start, $end)
	{
		$bookings = $this->get_bookings_by_date_range($start, $end);
		$calendar_data = [];

		foreach ($bookings as $booking)
		{
			$room_name = isset($booking['room_name']) ? $booking['room_name'] : 'Room';
			$property_name = isset($booking['property_name']) ? $booking['property_name'] : 'Property';

			// Set color based on booking status
			$color = '#3a87ad'; // Default blue

			switch ($booking['booking_status'])
			{
				case 'confirmed':
					$color = '#28a745'; // Green
					break;
				case 'pending':
					$color = '#ffc107'; // Yellow
					break;
				case 'cancelled':
					$color = '#dc3545'; // Red
					break;
				case 'checked_in':
					$color = '#17a2b8'; // Cyan
					break;
				case 'checked_out':
					$color = '#6c757d'; // Gray
					break;
				case 'no_show':
					$color = '#343a40'; // Dark
					break;
			}

			$calendar_data[] = [
				'id' => $booking['id'],
				'title' => $booking['guest_name'] . ' - ' . $room_name,
				'start' => $booking['check_in_date'],
				'end' => date('Y-m-d', strtotime($booking['check_out_date'] . ' +1 day')), // Add a day for proper display
				'color' => $color,
				'url' => admin_url('hotel_management_system/bookings/view/' . $booking['id']),
				'description' => $property_name . ' - ' . $room_name . '<br>' .
					'Status: ' . ucfirst($booking['booking_status']) . '<br>' .
					'Payment: ' . ucfirst($booking['payment_status']) . '<br>' .
					'Amount: ' . app_format_money($booking['total_amount'], get_base_currency())
			];
		}

		return $calendar_data;
	}

	/**
	 * Get today's arrivals and departures
	 * @return array
	 */
	public function get_today_arrivals_departures()
	{
		$today = date('Y-m-d');

		// Get today's arrivals
		$this->db->where('check_in_date', $today);
		$this->db->where('booking_status !=', 'cancelled');
		$arrivals = $this->get_all();

		// Get today's departures
		$this->db->where('check_out_date', $today);
		$this->db->where('booking_status', 'checked_in');
		$departures = $this->get_all();

		return [
			'arrivals' => $arrivals,
			'departures' => $departures
		];
	}

	/**
	 * Check-in booking
	 * @param integer $id Booking ID
	 * @return boolean
	 */
	public function check_in($id)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'booking_status' => 'checked_in',
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			// Update room status to occupied
			$this->db->select('room_id');
			$this->db->where('id', $id);
			$booking = $this->db->get(db_prefix() . 'hms_bookings')->row();

			if ($booking)
			{
				$this->db->where('id', $booking->room_id);
				$this->db->update(db_prefix() . 'hms_rooms', [
					'status' => 'occupied'
				]);
			}

			log_activity('Booking Checked In [ID: ' . $id . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Check-out booking
	 * @param integer $id Booking ID
	 * @return boolean
	 */
	public function check_out($id)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'booking_status' => 'checked_out',
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			// Update room status to available
			$this->db->select('room_id');
			$this->db->where('id', $id);
			$booking = $this->db->get(db_prefix() . 'hms_bookings')->row();

			if ($booking)
			{
				$this->db->where('id', $booking->room_id);
				$this->db->update(db_prefix() . 'hms_rooms', [
					'status' => 'available'
				]);
			}

			log_activity('Booking Checked Out [ID: ' . $id . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Update booking service status
	 * @param integer $service_id Service ID
	 * @param string $status New status
	 * @return boolean
	 */
	public function update_service_status($service_id, $status)
	{
		$this->db->where('id', $service_id);
		$this->db->update(db_prefix() . 'hms_booking_services', [
			'status' => $status
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Booking Service Status Updated [ID: ' . $service_id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	public function update_status($booking_id, $status)
	{
		$this->db->where('id', $booking_id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'booking_status' => $status
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Booking Status Updated [ID: ' . $booking_id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	public function update_payment_status($booking_id, $status)
	{
		$this->db->where('id', $booking_id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'payment_status' => $status
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Booking Payment Status Updated [ID: ' . $booking_id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Search bookings
	 * @param string $q Search query
	 * @return array
	 */
	public function search($q)
	{
		$this->db->group_start();
		$this->db->like('booking_reference', $q);
		$this->db->or_like('guest_name', $q);
		$this->db->or_like('guest_email', $q);
		$this->db->or_like('guest_phone', $q);
		$this->db->group_end();

		return $this->get_all();
	}

	/**
	 * Get booking by reference
	 * @param string $reference Booking reference
	 * @return object
	 */
	public function get_by_reference($reference)
	{
		$this->db->where('booking_reference', $reference);
		$booking = $this->db->get(db_prefix() . 'hms_bookings')->row();

		if ($booking)
		{
			return $this->get_booking_with_details($booking->id);
		}

		return NULL;
	}

	/**
	 * Get booking with details
	 * @param integer $id Booking ID
	 * @return object
	 */
	public function get_booking_with_details($id)
	{
		$this->db->select(db_prefix() . 'hms_bookings.*, ' .
			db_prefix() . 'hms_rooms.name as room_name, ' .
			db_prefix() . 'hms_rooms.room_type, ' .
			db_prefix() . 'hms_properties.name as property_name, ' .
			db_prefix() . 'hms_properties.id as property_id, ' .
			db_prefix() . 'hms_properties.address as property_address, ' .
			db_prefix() . 'hms_properties.city as property_city, ' .
			db_prefix() . 'hms_properties.country as property_country, ' .
			db_prefix() . 'clients.company as client_company');
		$this->db->from(db_prefix() . 'hms_bookings');
		$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_bookings.room_id', 'left');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
		$this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'hms_bookings.client_id', 'left');
		$this->db->where(db_prefix() . 'hms_bookings.id', $id);

		$booking = $this->db->get()->row();

		if ($booking)
		{
			// Get booked additional services
			$this->db->select(db_prefix() . 'hms_booking_services.*, ' .
				db_prefix() . 'hms_services.name as service_name, ' .
				db_prefix() . 'staff.firstname, ' .
				db_prefix() . 'staff.lastname');
			$this->db->from(db_prefix() . 'hms_booking_services');
			$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_booking_services.service_id', 'left');
			$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_booking_services.staff_id', 'left');
			$this->db->where(db_prefix() . 'hms_booking_services.booking_id', $id);

			$booking->services = $this->db->get()->result_array();

			// Get booked rooms
			$this->db->select(db_prefix() . 'hms_booking_rooms.*, ' .
				db_prefix() . 'hms_rooms.name as room_name, ' .
				db_prefix() . 'hms_rooms.room_type, ');
			$this->db->from(db_prefix() . 'hms_booking_rooms');
			$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_booking_rooms.room_id', 'left');
			$this->db->where(db_prefix() . 'hms_booking_rooms.booking_id', $id);

			$booking->rooms = $this->db->get()->result();

			// Get invoice details if exists
			if ($booking->invoice_id)
			{
				$this->db->where('id', $booking->invoice_id);
				$booking->invoice = $this->db->get(db_prefix() . 'invoices')->row();
			}

		}

		return $booking;
	}

	/**
	 * Calculate booking price
	 * Get room details and calculate the total price of the booking
	 * @param array $booking_data Booking data
	 * @return array
	 */
	public function calculate_booking_price($booking_data)
	{
		// Get room details
		$this->db->where('id', $booking_data['room_id']);
		$room = $this->db->get(db_prefix() . 'hms_rooms')->row();

		if ( ! $room)
		{
			return [
				'success' => FALSE,
				'message' => 'Room not found'
			];
		}

		// Calculate total nights
		$check_in = new DateTime($booking_data['check_in_date']);
		$check_out = new DateTime($booking_data['check_out_date']);
		$interval = $check_in->diff($check_out);
		$nights = $interval->days;

		if ($nights < 1)
		{
			return [
				'success' => FALSE,
				'message' => 'Check-out date must be after check-in date'
			];
		}

		// Calculate room cost
		$room_price = floatval($room->price_per_night) * $nights;

		// Add cleaning fee
		$cleaning_fee = floatval($room->cleaning_fee);

		// Calculate additional services cost
		$additional_services = 0;
		$services_details = [];

		if (isset($booking_data['services']) && is_array($booking_data['services']))
		{
			foreach ($booking_data['services'] as $service_id => $service_data)
			{
				if ( ! empty($service_data['quantity']))
				{
					// Get service details
					$this->db->where('id', $service_id);
					$service = $this->db->get(db_prefix() . 'hms_services')->row();

					if ($service)
					{
						$quantity = intval($service_data['quantity']);
						$service_price = floatval($service->price);
						$service_total = $service_price * $quantity;
						$additional_services += $service_total;

						$services_details[] = [
							'id' => $service->id,
							'name' => $service->name,
							'price' => $service_price,
							'quantity' => $quantity,
							'total' => $service_total
						];
					}
				}
			}
		}

		// Calculate taxes
		$tax_rate = get_tax_by_name('Booking Tax');
		if ($tax_rate)
		{
			$tax_rate = $tax_rate->taxrate;
		} else
		{
			$tax_rate = 0;
		}
		$taxable_amount = $room_price + $additional_services;
		$taxes = ($taxable_amount * floatval($tax_rate)) / 100;

		// Calculate total
		$total_amount = $room_price + $cleaning_fee + $additional_services + $taxes;

		return [
			'success' => TRUE,
			'data' => [
				'room' => $room,
				'nights' => $nights,
				'room_price' => $room_price,
				'cleaning_fee' => $cleaning_fee,
				'additional_services' => $additional_services,
				'services_details' => $services_details,
				'tax_rate' => $tax_rate,
				'taxes' => $taxes,
				'total_amount' => $total_amount
			]
		];
	}

	/**
	 * Recalculate booking total
	 * Updates the booking with the correct totals after service changes
	 * @param integer $booking_id Booking ID
	 * @return boolean
	 */
	public function recalculate_booking_total($booking_id)
	{
		// Get booking
		$booking = $this->get($booking_id);
		if ( ! $booking)
		{
			return FALSE;
		}

		// Calculate room price
		$room_price = floatval($booking->room_price);

		// Get cleaning fee
		$cleaning_fee = floatval($booking->cleaning_fee);

		// Calculate additional services total
		$this->db->select_sum('total');
		$this->db->where('booking_id', $booking_id);
		$services_query = $this->db->get(db_prefix() . 'hms_booking_services');

		$additional_services = 0;
		if ($services_query && $services_query->num_rows() > 0)
		{
			$result = $services_query->row();
			$additional_services = $result->total ? floatval($result->total) : 0;
		}

		// Get taxes
		$taxes = floatval($booking->taxes);

		// Recalculate tax if needed
		if (isset($booking->tax_rate) && $booking->tax_rate > 0)
		{
			$taxable_amount = $room_price + $additional_services;
			$taxes = ($taxable_amount * floatval($booking->tax_rate)) / 100;
		}

		// Calculate total amount
		$total_amount = $room_price + $cleaning_fee + $additional_services + $taxes;

		// Update booking
		$this->db->where('id', $booking_id);
		$this->db->update(db_prefix() . 'hms_bookings', [
			'additional_services' => $additional_services,
			'taxes' => $taxes,
			'total_amount' => $total_amount,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		return ($this->db->affected_rows() > 0);
	}
}