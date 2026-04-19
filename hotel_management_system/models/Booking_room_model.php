<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Booking_room_model extends App_Model {
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
		$data['datecreated'] = date('Y-m-d H:i:s');
		$this->db->insert(db_prefix() . 'hms_booking_rooms', $data);
		return $this->db->insert_id();
	}
}