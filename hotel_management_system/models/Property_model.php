<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Property_model extends App_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get property by ID
	 * @param  mixed $id Property ID
	 * @return mixed
	 */
	public function get($id = '')
	{
		$this->db->select('*');
		$this->db->from(db_prefix() . 'hms_properties');

		if (is_numeric($id)) {
			$this->db->where('id', $id);
			return $this->db->get()->row();
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Get all properties with optional filtering
	 * @param  array  $where Optional where clause
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select(db_prefix() . 'hms_properties.*, ' . db_prefix() . 'hms_landlords.name as landlord_name');
		$this->db->from(db_prefix() . 'hms_properties');
		$this->db->join(db_prefix() . 'hms_landlords', db_prefix() . 'hms_landlords.id = ' . db_prefix() . 'hms_properties.landlord_id', 'left');

		if (!empty($where)) {
			$this->db->where($where);
		}

		return $this->db->get()->result_array();
	}

	/**
	 * Get properties by landlord
	 * @param  integer $landlord_id Landlord ID
	 * @return array
	 */
	public function get_by_landlord($landlord_id)
	{
		$this->db->where('landlord_id', $landlord_id);
		return $this->db->get(db_prefix() . 'hms_properties')->result_array();
	}

	/**
	 * Add new property
	 * @param array $data Property data
	 * @return integer|boolean
	 */
	public function add($data)
	{
		// Set default values
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();
		$data['featured'] = isset($data['featured']) ? 1 : 0;

		// Format amenities if passed as array
		if (isset($data['amenities']) && is_array($data['amenities'])) {
			$data['amenities'] = serialize($data['amenities']);
		}

		// Format rules if passed as array
		if (isset($data['rules']) && is_array($data['rules'])) {
			$data['rules'] = serialize($data['rules']);
		}

		$this->db->insert(db_prefix() . 'hms_properties', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			log_activity('New Property Created [ID: ' . $insert_id . ', ' . $data['name'] . ']');
			return $insert_id;
		}

		return false;
	}

	/**
	 * Update property
	 * @param  array $data Property data
	 * @param  mixed $id   Property ID
	 * @return boolean
	 */
	public function update($data, $id)
	{
		// Set update fields
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();
		$data['featured'] = isset($data['featured']) ? 1 : 0;

		// Format amenities if passed as array
		if (isset($data['amenities']) && is_array($data['amenities'])) {
			$data['amenities'] = serialize($data['amenities']);
		}

		// Format rules if passed as array
		if (isset($data['rules']) && is_array($data['rules'])) {
			$data['rules'] = serialize($data['rules']);
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_properties', $data);

		if ($this->db->affected_rows() > 0) {
			log_activity('Property Updated [ID: ' . $id . ', ' . $data['name'] . ']');
			return true;
		}

		return false;
	}

	/**
	 * Delete property
	 * @param  mixed $id Property ID
	 * @return boolean
	 */
	public function delete($id)
	{
		// Get property before deleting
		$this->db->where('id', $id);
		$property = $this->db->get(db_prefix() . 'hms_properties')->row();

		// Delete property
		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_properties');

		if ($this->db->affected_rows() > 0) {
			// Delete related property images
			$this->db->where('property_id', $id);
			$this->db->delete(db_prefix() . 'hms_property_images');

			log_activity('Property Deleted [ID: ' . $id . ', ' . $property->name . ']');
			return true;
		}

		return false;
	}

	/**
	 * Get property rooms
	 * @param  integer $property_id Property ID
	 * @return array
	 */
	public function get_property_rooms($property_id)
	{
		$this->db->where('property_id', $property_id);
		return $this->db->get(db_prefix() . 'hms_rooms')->result_array();
	}

	/**
	 * Get property with rooms
	 * @param  integer $property_id Property ID
	 * @return object
	 */
	public function get_property_with_rooms($property_id)
	{
		$property = $this->get($property_id);

		if ($property) {
			$property->rooms = $this->get_property_rooms($property_id);

			// Get property images
			$this->db->where('property_id', $property_id);
			$this->db->order_by('is_featured', 'DESC');
			$this->db->order_by('sort_order', 'ASC');
			$property->images = $this->db->get(db_prefix() . 'hms_property_images')->result_array();

			// Unserialize amenities and rules if stored as serialized data
			if (isset($property->amenities) && is_string($property->amenities) && @unserialize($property->amenities) !== false) {
				$property->amenities = unserialize($property->amenities);
			}

			if (isset($property->rules) && is_string($property->rules) && @unserialize($property->rules) !== false) {
				$property->rules = unserialize($property->rules);
			}
		}

		return $property;
	}

	/**
	 * Get properties for public listing
	 * @return array
	 */
	public function get_properties_for_public()
	{
		$this->db->where('status', 'active');
		$properties = $this->db->get(db_prefix() . 'hms_properties')->result_array();

		$result = [];

		foreach ($properties as $property) {
			// Get property rooms
			$this->db->where('property_id', $property['id']);
			$this->db->where('status', 'available');
			$property['rooms'] = $this->db->get(db_prefix() . 'hms_rooms')->result_array();

			// Get property featured image
			$this->db->where('property_id', $property['id']);
			$this->db->where('is_featured', 1);
			$this->db->limit(1);
			$featured_image = $this->db->get(db_prefix() . 'hms_property_images')->row();

			if ($featured_image) {
				$property['featured_image'] = $featured_image->path;
			} else {
				// Get first image if no featured image
				$this->db->where('property_id', $property['id']);
				$this->db->limit(1);
				$first_image = $this->db->get(db_prefix() . 'hms_property_images')->row();

				$property['featured_image'] = $first_image ? $first_image->path : '';
			}

			// Unserialize amenities and rules if stored as serialized data
			if (isset($property['amenities']) && is_string($property['amenities']) && @unserialize($property['amenities']) !== false) {
				$property['amenities'] = unserialize($property['amenities']);
			}

			if (isset($property['rules']) && is_string($property['rules']) && @unserialize($property['rules']) !== false) {
				$property['rules'] = unserialize($property['rules']);
			}

			$result[] = $property;
		}

		return $result;
	}

	/**
	 * Check if property exists
	 * @param  string  $name       Property name
	 * @param  integer $landlord_id Landlord ID
	 * @param  integer $id         Property ID (optional, for update)
	 * @return boolean
	 */
	public function property_exists($name, $landlord_id, $id = '')
	{
		$this->db->where('name', $name);
		$this->db->where('landlord_id', $landlord_id);

		if ($id != '') {
			$this->db->where('id !=', $id);
		}

		$query = $this->db->get(db_prefix() . 'hms_properties');

		if ($query->num_rows() > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Get available rooms for booking
	 * @param  string $check_in_date  Check-in date
	 * @param  string $check_out_date Check-out date
	 * @return array
	 */
	public function get_available_rooms($check_in_date, $check_out_date)
	{
		// Get all active properties
		$this->db->where('status', 'active');
		$properties = $this->db->get(db_prefix() . 'hms_properties')->result_array();

		$available_rooms = [];

		foreach ($properties as $property) {
			// Get all active rooms for this property
			$this->db->where('property_id', $property['id']);
			$this->db->where('status', 'available');
			$rooms = $this->db->get(db_prefix() . 'hms_rooms')->result_array();

			foreach ($rooms as $room) {
				// Check if room is available for booking
				$this->db->where('room_id', $room['id']);
				$this->db->where('(
                    (check_in_date BETWEEN "' . $check_in_date . '" AND "' . $check_out_date . '") OR
                    (check_out_date BETWEEN "' . $check_in_date . '" AND "' . $check_out_date . '") OR
                    ("' . $check_in_date . '" BETWEEN check_in_date AND check_out_date) OR
                    ("' . $check_out_date . '" BETWEEN check_in_date AND check_out_date)
                )');
				$this->db->where('booking_status !=', 'cancelled');
				$bookings = $this->db->get(db_prefix() . 'hms_bookings')->result_array();

				if (count($bookings) == 0) {
					$room['property_name'] = $property['name'];
					$room['property_address'] = $property['address'];
					$available_rooms[] = $room;
				}
			}
		}

		return $available_rooms;
	}

	/**
	 * Add property image
	 * @param array $data Image data
	 * @return integer|boolean
	 */
	public function add_image($data)
	{
		$data['datecreated'] = date('Y-m-d H:i:s');

		$this->db->insert(db_prefix() . 'hms_property_images', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id) {
			// If marked as featured, unmark other images
			if (isset($data['is_featured']) && $data['is_featured'] == 1) {
				$this->db->where('property_id', $data['property_id']);
				$this->db->where('id !=', $insert_id);
				$this->db->update(db_prefix() . 'hms_property_images', ['is_featured' => 0]);
			}

			return $insert_id;
		}

		return false;
	}

	/**
	 * Delete property image
	 * @param  integer $id Image ID
	 * @return boolean
	 */
	public function delete_image($id)
	{
		$this->db->where('id', $id);
		$image = $this->db->get(db_prefix() . 'hms_property_images')->row();

		if (!$image) {
			return false;
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_property_images');

		if ($this->db->affected_rows() > 0) {
			// Delete file from server
			if (file_exists(HMS_MODULE_UPLOAD_FOLDER . '/properties/' . $image->file_name)) {
				@unlink(HMS_MODULE_UPLOAD_FOLDER . '/properties/' . $image->file_name);
			}

			return true;
		}

		return false;
	}

	/**
	 * Set featured image
	 * @param  integer $id Image ID
	 * @return boolean
	 */
	public function set_featured_image($id)
	{
		$this->db->where('id', $id);
		$image = $this->db->get(db_prefix() . 'hms_property_images')->row();

		if (!$image) {
			return false;
		}

		// Unmark all images for this property
		$this->db->where('property_id', $image->property_id);
		$this->db->update(db_prefix() . 'hms_property_images', ['is_featured' => 0]);

		// Mark selected image as featured
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_property_images', ['is_featured' => 1]);

		return true;
	}

	/**
	 * Reorder images
	 * @param  array $data Order data
	 * @return boolean
	 */
	public function reorder_images($data)
	{
		foreach ($data as $order => $image_id) {
			$this->db->where('id', $image_id);
			$this->db->update(db_prefix() . 'hms_property_images', ['sort_order' => $order]);
		}

		return true;
	}
}