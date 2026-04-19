<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Room_model extends App_Model {
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get all rooms with optional filtering
	 * @param array $where Optional where clause
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select(db_prefix() . 'hms_rooms.*, ' . db_prefix() . 'hms_properties.name as property_name');
		$this->db->from(db_prefix() . 'hms_rooms');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');

		if ( ! empty($where))
		{
			$this->db->where($where);
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Get room by ID
	 * @param mixed $id Room ID
	 * @return mixed
	 */
	public function get($id = '')
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . 'hms_rooms');

		if (is_numeric($id))
		{
			$this->db->where('id', $id);
			return $this->db->get()->row();
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Get rooms by property
	 * @param integer $property_id Property ID
	 * @return array
	 */
	public function get_by_property($property_id)
	{
		$this->db->where('property_id', $property_id);
		return $this->db->get(db_prefix() . 'hms_rooms')->result_array();
	}

	/**
	 * Add new room
	 * @param array $data Room data
	 * @return integer|boolean
	 */
	public function add($data)
	{
		// Set default values
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();

		// Format amenities if passed as array
		if (isset($data['amenities']) && is_array($data['amenities']))
		{
			$data['amenities'] = serialize($data['amenities']);
		}

		$this->db->insert(db_prefix() . 'hms_rooms', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Room Created [ID: ' . $insert_id . ', ' . $data['name'] . ']');
			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Get room with details
	 * @param integer $room_id Room ID
	 * @return object
	 */
	public function get_room_with_details($room_id)
	{
		$room = $this->get($room_id);

		if ( ! $room)
		{
			return NULL;
		}

		// Get property details
		$this->db->where('id', $room->property_id);
		$room->property = $this->db->get(db_prefix() . 'hms_properties')->row();

		// Get room images
		$this->db->where('room_id', $room_id);
		$this->db->order_by('is_featured', 'DESC');
		$this->db->order_by('sort_order', 'ASC');
		$room->images = $this->db->get(db_prefix() . 'hms_room_images')->result_array();

		// Get service assignments
		$this->db->select(db_prefix() . 'hms_service_assignments.*, ' . db_prefix() . 'hms_services.name as service_name, ' . db_prefix() . 'staff.firstname, ' . db_prefix() . 'staff.lastname');
		$this->db->from(db_prefix() . 'hms_service_assignments');
		$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
		$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
		$this->db->where(db_prefix() . 'hms_service_assignments.room_id', $room_id);
		$room->service_assignments = $this->db->get()->result_array();

		// Unserialize amenities if stored as serialized data
		if (isset($room->amenities) && is_string($room->amenities) && @unserialize($room->amenities) !== FALSE)
		{
			$room->amenities = unserialize($room->amenities);
		}

		return $room;
	}

	/**
	 * Get rooms for booking
	 * @param array $filters Filter options
	 * @return array
	 */
	public function get_rooms_for_booking($filters = [])
	{
		$this->db->select(db_prefix() . 'hms_rooms.*, ' . db_prefix() . 'hms_properties.name as property_name, ' . db_prefix() . 'hms_properties.address as property_address');
		$this->db->from(db_prefix() . 'hms_rooms');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
		$this->db->where(db_prefix() . 'hms_rooms.status', 'available');
		$this->db->where(db_prefix() . 'hms_properties.status', 'active');

		// Apply filters
		if ( ! empty($filters))
		{
			if (isset($filters['property_id']) && $filters['property_id'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.property_id', $filters['property_id']);
			}

			if (isset($filters['capacity']) && $filters['capacity'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.capacity >=', $filters['capacity']);
			}

			if (isset($filters['room_type']) && $filters['room_type'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.room_type', $filters['room_type']);
			}

			if (isset($filters['price_min']) && $filters['price_min'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.price_per_night >=', $filters['price_min']);
			}

			if (isset($filters['price_max']) && $filters['price_max'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.price_per_night <=', $filters['price_max']);
			}

			// Filter for available dates if check_in and check_out are provided
			if (isset($filters['check_in']) && isset($filters['check_out']) && $filters['check_in'] && $filters['check_out'])
			{
				// Get all rooms that have bookings that overlap with requested dates
				$subquery = "SELECT " . db_prefix() . "hms_booking_rooms.room_id FROM " . db_prefix() . "hms_bookings as bk 
							JOIN " . db_prefix() . "hms_booking_rooms
							ON bk.id = " . db_prefix() . "hms_booking_rooms.booking_id
                            WHERE booking_status != 'cancelled' 
                            AND (
                                (bk.check_in_date <= '" . $filters['check_in'] . "' AND bk.check_out_date > '" . $filters['check_in'] . "') OR 
                                (bk.check_in_date < '" . $filters['check_out'] . "' AND bk.check_out_date >= '" . $filters['check_out'] . "') OR 
                                (bk.check_in_date >= '" . $filters['check_in'] . "' AND bk.check_out_date <= '" . $filters['check_out'] . "')
                            )";

				$this->db->where(db_prefix() . 'hms_rooms.id NOT IN (' . $subquery . ')', NULL, FALSE);
			}
		}

		$rooms = $this->db->get()->result_array();

		// Process each room to add additional details
		foreach ($rooms as &$room)
		{
			// Get featured image
			$this->db->where('room_id', $room['id']);
			$this->db->where('is_featured', 1);
			$this->db->limit(1);
			$featured_image = $this->db->get(db_prefix() . 'hms_room_images')->row();

			if ($featured_image)
			{
				$room['featured_image'] = $featured_image->path;
			} else
			{
				// Get first image if no featured image
				$this->db->where('room_id', $room['id']);
				$this->db->limit(1);
				$first_image = $this->db->get(db_prefix() . 'hms_room_images')->row();

				$room['featured_image'] = $first_image ? $first_image->path : '';
			}

			// Unserialize amenities if stored as serialized data
			if (isset($room['amenities']) && is_string($room['amenities']) && @unserialize($room['amenities']) !== FALSE)
			{
				$room['amenities'] = unserialize($room['amenities']);
			}
		}

		return $rooms;
	}

	/**
	 * Check if room is available for booking
	 * @param integer $room_id Room ID
	 * @param string $check_in_date Check-in date
	 * @param string $check_out_date Check-out date
	 * @param integer $booking_id Booking ID (optional, for updating existing booking)
	 * @return boolean
	 */
	public function is_room_available($room_id, $check_in_date, $check_out_date, $booking_id = NULL)
	{
		$this->db->where('room_id', $room_id);
		$this->db->where('booking_status !=', 'cancelled');

		// Skip current booking when updating
		if ($booking_id)
		{
			$this->db->where('id !=', $booking_id);
		}

		// Check for overlapping bookings
		$this->db->where('(
            (check_in_date <= "' . $check_in_date . '" AND check_out_date > "' . $check_in_date . '") OR 
            (check_in_date < "' . $check_out_date . '" AND check_out_date >= "' . $check_out_date . '") OR 
            (check_in_date >= "' . $check_in_date . '" AND check_out_date <= "' . $check_out_date . '")
        )');

		$count = $this->db->count_all_results(db_prefix() . 'hms_bookings');

		// If count is 0, room is available
		return ($count == 0);
	}

	/**
	 * Add room image
	 * @param array $data Image data
	 * @return integer|boolean
	 */
	public function add_image($data)
	{
		$data['datecreated'] = date('Y-m-d H:i:s');

		$this->db->insert(db_prefix() . 'hms_room_images', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// If marked as featured, unmark other images
			if (isset($data['is_featured']) && $data['is_featured'] == 1)
			{
				$this->db->where('room_id', $data['room_id']);
				$this->db->where('id !=', $insert_id);
				$this->db->update(db_prefix() . 'hms_room_images', ['is_featured' => 0]);
			}

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update room
	 * @param array $data Room data
	 * @param mixed $id Room ID
	 * @return boolean
	 */
	public function update($data, $id)
	{
		// Set update fields
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		// Format amenities if passed as array
		if (isset($data['amenities']) && is_array($data['amenities']))
		{
			$data['amenities'] = serialize($data['amenities']);
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_rooms', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Room Updated [ID: ' . $id . ', ' . $data['name'] . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete room image
	 * @param integer $id Image ID
	 * @return boolean
	 */
	public function delete_image($id)
	{
		$this->db->where('id', $id);
		$image = $this->db->get(db_prefix() . 'hms_room_images')->row();

		if ( ! $image)
		{
			return FALSE;
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_room_images');

		if ($this->db->affected_rows() > 0)
		{
			// Delete file from server
			if (file_exists(HMS_MODULE_UPLOAD_FOLDER . '/rooms/' . $image->file_name))
			{
				@unlink(HMS_MODULE_UPLOAD_FOLDER . '/rooms/' . $image->file_name);
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete room
	 * @param mixed $id Room ID
	 * @return array | boolean
	 */
	public function delete($id)
	{
		// Get room before deleting
		$this->db->where('id', $id);
		$room = $this->db->get(db_prefix() . 'hms_rooms')->row();

		if ( ! $room)
		{
			return FALSE;
		}

		// Check if room has any bookings
		$this->db->where('room_id', $id);
		$bookings = $this->db->get(db_prefix() . 'hms_bookings')->result_array();

		if (count($bookings) > 0)
		{
			return [
				'success' => FALSE,
				'message' => _l('room_has_bookings_cannot_delete')
			];
		}

		// Delete room
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_rooms');

		if ($this->db->affected_rows() > 0)
		{
			// Delete related room images
			$this->db->where('room_id', $id);
			$this->db->delete(db_prefix() . 'hms_room_images');

			// Delete related service assignments
			$this->db->where('room_id', $id);
			$this->db->delete(db_prefix() . 'hms_service_assignments');

			log_activity('Room Deleted [ID: ' . $id . ', ' . $room->name . ']');
			return [
				'success' => TRUE,
				'message' => _l('room_deleted_successfully')
			];
		}

		return [
			'success' => FALSE,
			'message' => _l('problem_deleting_room')
		];
	}

	/**
	 * Set featured image for room
	 * @param integer $id Image ID
	 * @return boolean
	 */
	public function set_featured_image($id)
	{
		$this->db->where('id', $id);
		$image = $this->db->get(db_prefix() . 'hms_room_images')->row();

		if ( ! $image)
		{
			return FALSE;
		}

		// Unmark all images for this room
		$this->db->where('room_id', $image->room_id);
		$this->db->update(db_prefix() . 'hms_room_images', ['is_featured' => 0]);

		// Mark selected image as featured
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_room_images', ['is_featured' => 1]);

		return TRUE;
	}

	/**
	 * Reorder room images
	 * @param array $data Order data
	 * @return boolean
	 */
	public function reorder_images($data)
	{
		foreach ($data as $order => $image_id)
		{
			$this->db->where('id', $image_id);
			$this->db->update(db_prefix() . 'hms_room_images', ['sort_order' => $order]);
		}

		return TRUE;
	}

	/**
	 * Update room status
	 * @param integer $id Room ID
	 * @param string $status New status
	 * @return boolean
	 */
	public function update_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_rooms', [
			'status' => $status,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Room Status Updated [ID: ' . $id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get room booking history
	 * @param integer $room_id Room ID
	 * @return array
	 */
	public function get_booking_history($room_id)
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . 'hms_bookings');
		$this->db->where('room_id', $room_id);
		$this->db->order_by('check_in_date', 'desc');

		return $this->db->get()->result_array();
	}

	/**
	 * Get room occupancy report
	 * @param integer $room_id Room ID
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return array
	 */
	public function get_occupancy_report($room_id, $start_date, $end_date)
	{
		// Calculate total days in the period
		$start = new DateTime($start_date);
		$end = new DateTime($end_date);
		$interval = $start->diff($end);
		$total_days = $interval->days + 1; // Including both start and end dates

		// Get bookings for the room in this period
		$this->db->select('*');
		$this->db->from(db_prefix() . 'hms_bookings');
		$this->db->where('room_id', $room_id);
		$this->db->where('booking_status !=', 'cancelled');
		$this->db->where('(
            (check_in_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR
            (check_out_date BETWEEN "' . $start_date . '" AND "' . $end_date . '") OR
            ("' . $start_date . '" BETWEEN check_in_date AND check_out_date) OR
            ("' . $end_date . '" BETWEEN check_in_date AND check_out_date)
        )');

		$bookings = $this->db->get()->result_array();

		// Calculate occupied days
		$occupied_days = 0;

		foreach ($bookings as $booking)
		{
			$booking_start = max($start_date, $booking['check_in_date']);
			$booking_end = min($end_date, $booking['check_out_date']);

			$booking_start_dt = new DateTime($booking_start);
			$booking_end_dt = new DateTime($booking_end);
			$booking_days = $booking_start_dt->diff($booking_end_dt)->days + 1;

			$occupied_days += $booking_days;
		}

		// Calculate occupancy rate
		$occupancy_rate = $total_days > 0 ? ($occupied_days / $total_days) * 100 : 0;

		return [
			'total_days' => $total_days,
			'occupied_days' => $occupied_days,
			'occupancy_rate' => round($occupancy_rate, 2),
			'bookings' => $bookings
		];
	}

	/**
	 * Get rooms with available staff assignments
	 * @param array $filters Filter options
	 * @return array
	 */
	public function get_rooms_with_staff_assignments($filters = [])
	{
		$this->db->select(db_prefix() . 'hms_rooms.*, ' . db_prefix() . 'hms_properties.name as property_name');
		$this->db->from(db_prefix() . 'hms_rooms');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');

		// Apply filters
		if ( ! empty($filters))
		{
			if (isset($filters['property_id']) && $filters['property_id'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.property_id', $filters['property_id']);
			}

			if (isset($filters['status']) && $filters['status'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.status', $filters['status']);
			}
		}

		$rooms = $this->db->get()->result_array();
		$result = [];

		foreach ($rooms as $room)
		{
			// Get service assignments for this room
			$this->db->select(db_prefix() . 'hms_service_assignments.*, ' .
				db_prefix() . 'hms_services.name as service_name, ' .
				db_prefix() . 'staff.firstname, ' .
				db_prefix() . 'staff.lastname');
			$this->db->from(db_prefix() . 'hms_service_assignments');
			$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
			$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
			$this->db->where(db_prefix() . 'hms_service_assignments.room_id', $room['id']);

			if (isset($filters['day_of_week']) && $filters['day_of_week'] !== '')
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $filters['day_of_week']);
			}

			if (isset($filters['staff_id']) && $filters['staff_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.staff_id', $filters['staff_id']);
			}

			if (isset($filters['service_id']) && $filters['service_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.service_id', $filters['service_id']);
			}

			$room['assignments'] = $this->db->get()->result_array();
			$result[] = $room;
		}

		return $result;
	}
}