<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Service_model extends App_Model {
	protected $table = 'hms_services';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get all services with optional filtering
	 * @param array $where Optional where clause
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . $this->table);

		if ( ! empty($where))
		{
			$this->db->where($where);
		}

		$this->db->order_by('name', 'asc');
		return $this->db->get()->result_array();
	}

	/**
	 * Get service by ID
	 * @param mixed $id Service ID
	 * @return mixed
	 */
	public function get($id = '')
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . $this->table);

		if (is_numeric($id))
		{
			$this->db->where('id', $id);
			return $this->db->get()->row();
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Add new service
	 * @param array $data Service data
	 * @return integer|boolean
	 */
	public function add($data)
	{
		// Set default values
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();

		$this->db->insert(db_prefix() . $this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Service Created [ID: ' . $insert_id . ', ' . $data['name'] . ']');
			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Change service status
	 * @param integer $id Service ID
	 * @param string $status New status
	 * @return boolean
	 */
	public function change_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . $this->table, [
			'status' => $status,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Service Status Changed [ID: ' . $id . ', Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Update service
	 * @param array $data Service data
	 * @param mixed $id Service ID
	 * @return boolean
	 */
	public function update($data, $id)
	{
		// Set update fields
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . $this->table, $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Service Updated [ID: ' . $id . ', ' . $data['name'] . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get services for dropdown
	 * @param string $status Filter by status
	 * @return array
	 */
	public function get_services_for_dropdown($status = 'active')
	{
		$this->db->select('id, name');

		if ($status)
		{
			$this->db->where('status', $status);
		}

		$this->db->order_by('name', 'asc');
		$services = $this->db->get(db_prefix() . $this->table)->result_array();

		$dropdown = [];
		foreach ($services as $service)
		{
			$dropdown[$service['id']] = $service['name'];
		}

		return $dropdown;
	}

	/**
	 * Get services by type
	 * @param string $type Service type
	 * @param string $status Filter by status
	 * @return array
	 */
	public function get_by_type($type, $status = 'active')
	{
		$this->db->where('service_type', $type);

		if ($status)
		{
			$this->db->where('status', $status);
		}

		$this->db->order_by('name', 'asc');
		return $this->db->get(db_prefix() . $this->table)->result_array();
	}

	/**
	 * Get service assignments
	 * @param array $filters Filter options (staff_id, room_id, day_of_week)
	 * @return array
	 */
	public function get_service_assignments($filters = [])
	{
		$this->db->select(db_prefix() . 'hms_service_assignments.*, ' .
			db_prefix() . 'hms_services.name as service_name, ' .
			db_prefix() . 'hms_services.service_type, ' .
			db_prefix() . 'staff.firstname, ' .
			db_prefix() . 'staff.lastname, ' .
			db_prefix() . 'hms_rooms.name as room_name, ' .
			db_prefix() . 'hms_properties.name as property_name');
		$this->db->from(db_prefix() . 'hms_service_assignments');
		$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
		$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
		$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');

		// Apply filters
		if ( ! empty($filters))
		{
			if (isset($filters['staff_id']) && $filters['staff_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.staff_id', $filters['staff_id']);
			}

			if (isset($filters['room_id']) && $filters['room_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.room_id', $filters['room_id']);
			}

			if (isset($filters['day_of_week']) && $filters['day_of_week'] !== '')
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $filters['day_of_week']);
			}

			if (isset($filters['service_id']) && $filters['service_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.service_id', $filters['service_id']);
			}

			if (isset($filters['status']) && $filters['status'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.status', $filters['status']);
			}

			if (isset($filters['property_id']) && $filters['property_id'])
			{
				$this->db->where(db_prefix() . 'hms_rooms.property_id', $filters['property_id']);
			}
		}

		$this->db->order_by(db_prefix() . 'hms_service_assignments.day_of_week', 'asc');
		$this->db->order_by(db_prefix() . 'hms_service_assignments.start_time', 'asc');

		return $this->db->get()->result_array();
	}

	public function get_assignment($id)
	{
		$this->db->select(db_prefix() . 'hms_service_assignments.*, ' .
			db_prefix() . 'hms_services.name as service_name, ' .
			db_prefix() . 'hms_services.service_type, ' .
			db_prefix() . 'staff.firstname, ' .
			db_prefix() . 'staff.lastname, ' .
			db_prefix() . 'hms_rooms.name as room_name, ' .
			db_prefix() . 'hms_properties.name as property_name, ' .
			db_prefix() . 'hms_properties.id as property_id');
		$this->db->from(db_prefix() . 'hms_service_assignments');
		$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
		$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
		$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
		$this->db->where(db_prefix() . 'hms_service_assignments.id', $id);
		return $this->db->get()->row_array();
	}

	/**
	 * Add service assignment
	 * @param array $data Assignment data
	 * @return integer|boolean
	 */
	public function add_assignment($data)
	{
		// Set default values
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();
		$data['status'] = isset($data['status']) ? $data['status'] : 'active';

		$this->db->insert(db_prefix() . 'hms_service_assignments', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Service Assignment Created [ID: ' . $insert_id . ', Room ID: ' . $data['room_id'] . ', Staff ID: ' . $data['staff_id'] . ']');
			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update service assignment
	 * @param array $data Assignment data
	 * @param integer $id Assignment ID
	 * @return boolean
	 */
	public function update_assignment($data, $id)
	{
		// Set update fields
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_service_assignments', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Service Assignment Updated [ID: ' . $id . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete service assignment
	 * @param integer $id Assignment ID
	 * @return boolean
	 */
	public function delete_assignment($id)
	{
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_service_assignments');

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Service Assignment Deleted [ID: ' . $id . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete service
	 * @param mixed $id Service ID
	 * @return array
	 */
	public function delete($id)
	{
		// Check if service is assigned to any room
		$this->db->where('service_id', $id);
		$assignments = $this->db->get(db_prefix() . 'hms_service_assignments')->result_array();

		if (count($assignments) > 0)
		{
			return [
				'success' => FALSE,
				'message' => _l('service_has_assignments_cannot_delete')
			];
		}

		// Check if service is used in any booking
		$this->db->where('service_id', $id);
		$bookings = $this->db->get(db_prefix() . 'hms_booking_services')->result_array();

		if (count($bookings) > 0)
		{
			return [
				'success' => FALSE,
				'message' => _l('service_has_bookings_cannot_delete')
			];
		}

		// Get service before deleting
		$this->db->where('id', $id);
		$service = $this->db->get(db_prefix() . $this->table)->row();

		if ( ! $service)
		{
			return [
				'success' => FALSE,
				'message' => _l('service_not_found')
			];
		}

		// Delete service
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . $this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Service Deleted [ID: ' . $id . ', ' . $service->name . ']');
			return [
				'success' => TRUE,
				'message' => _l('service_deleted_successfully')
			];
		}

		return [
			'success' => FALSE,
			'message' => _l('problem_deleting_service')
		];
	}

	/**
	 * Check if staff member is available for assignment
	 * @param integer $staff_id Staff ID
	 * @param integer $day_of_week Day of week
	 * @param string $start_time Start time
	 * @param string $end_time End time
	 * @param integer $exclude_id Assignment ID to exclude (for updates)
	 * @return boolean
	 */
	public function is_staff_available($staff_id, $day_of_week, $start_time, $end_time, $exclude_id = NULL)
	{
		$this->db->where('staff_id', $staff_id);
		$this->db->where('day_of_week', $day_of_week);
		$this->db->where('status', 'active');

		// Exclude current assignment for updates
		if ($exclude_id)
		{
			$this->db->where('id !=', $exclude_id);
		}

		// Check for overlapping time slots
		$this->db->where('(
            (start_time <= "' . $start_time . '" AND end_time > "' . $start_time . '") OR 
            (start_time < "' . $end_time . '" AND end_time >= "' . $end_time . '") OR 
            (start_time >= "' . $start_time . '" AND end_time <= "' . $end_time . '")
        )');

		$count = $this->db->count_all_results(db_prefix() . 'hms_service_assignments');

		// If count is 0, staff is available
		return ($count == 0);
	}

	/**
	 * Get staff schedule
	 * @param integer $staff_id Staff ID
	 * @param string $date Date to check (YYYY-MM-DD)
	 * @return array
	 */
	public function get_staff_schedule($staff_id, $date = '')
	{
		if (empty($date))
		{
			$date = date('Y-m-d');
		}

		// Get day of week from date (0 = Sunday, 1 = Monday, etc.)
		$day_of_week = date('w', strtotime($date));

		// Get all assignments for this staff member on this day of the week
		$this->db->select(db_prefix() . 'hms_service_assignments.*, ' .
			db_prefix() . 'hms_services.name as service_name, ' .
			db_prefix() . 'hms_rooms.name as room_name, ' .
			db_prefix() . 'hms_properties.name as property_name');
		$this->db->from(db_prefix() . 'hms_service_assignments');
		$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
		$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
		$this->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
		$this->db->where(db_prefix() . 'hms_service_assignments.staff_id', $staff_id);
		$this->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $day_of_week);
		$this->db->where(db_prefix() . 'hms_service_assignments.status', 'active');
		$this->db->order_by(db_prefix() . 'hms_service_assignments.start_time', 'asc');

		return $this->db->get()->result_array();
	}

	/**
	 * Get all staff working on a specific day
	 * @param integer $day_of_week Day of week (0-6)
	 * @param array $filters Additional filters
	 * @return array
	 */
	public function get_staff_by_day($day_of_week, $filters = [])
	{
		$this->db->select('DISTINCT ' . db_prefix() . 'staff.staffid, ' .
			db_prefix() . 'staff.firstname, ' .
			db_prefix() . 'staff.lastname, ' .
			db_prefix() . 'staff.email');
		$this->db->from(db_prefix() . 'hms_service_assignments');
		$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
		$this->db->where(db_prefix() . 'hms_service_assignments.day_of_week', $day_of_week);
		$this->db->where(db_prefix() . 'hms_service_assignments.status', 'active');

		// Apply additional filters
		if ( ! empty($filters))
		{
			if (isset($filters['property_id']) && $filters['property_id'])
			{
				$this->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
				$this->db->where(db_prefix() . 'hms_rooms.property_id', $filters['property_id']);
			}

			if (isset($filters['service_id']) && $filters['service_id'])
			{
				$this->db->where(db_prefix() . 'hms_service_assignments.service_id', $filters['service_id']);
			}
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Get bookable services for customer
	 * @return array
	 */
	public function get_bookable_services()
	{
		$this->db->where('status', 'active');
		$this->db->order_by('name', 'asc');
		return $this->db->get(db_prefix() . $this->table)->result_array();
	}
}