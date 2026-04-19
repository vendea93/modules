<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Landlord_model extends App_Model {
	protected string $table = 'hms_landlords';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Add new landlord
	 * @param array $data landlord data
	 */
	public function add(array $data)
	{
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();

		$this->db->insert(db_prefix() . $this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Landlord Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Delete landlord
	 * @param mixed $id landlord id
	 * @return boolean
	 */
	public function delete($id)
	{
		// Check if landlord has properties
		$this->db->where('landlord_id', $id);
		$properties = $this->db->get(db_prefix() . 'hms_properties')->result_array();

		if (count($properties) > 0)
		{
			return [
				'success' => FALSE,
				'message' => _l('landlord_has_properties_assigned'),
			];
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . $this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Landlord Deleted [ID: ' . $id . ']');
			return [
				'success' => TRUE,
				'message' => _l('landlord_deleted_successfully'),
			];
		}

		return [
			'success' => FALSE,
			'message' => _l('unable_to_delete_landlord'),
		];
	}

	/**
	 * Get landlord by ID
	 * @param mixed $id landlord id
	 * @return object
	 */
	public function get($id = '')
	{
		$this->db->select('*');

		if (is_numeric($id))
		{
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . $this->table)->row();
		}

		return $this->db->get(db_prefix() . $this->table)->result_array();
	}

	/**
	 * Get landlords for dropdown
	 * @return array
	 */
	public function get_landlords_for_dropdown()
	{
		$this->db->select('id, name');
		$this->db->where('active', 1);
		$this->db->order_by('name', 'asc');

		$landlords = $this->db->get(db_prefix() . $this->table)->result_array();
		$dropdown = [];

		foreach ($landlords as $landlord)
		{
			$dropdown[$landlord['id']] = $landlord['name'];
		}

		return $dropdown;
	}

	/**
	 * Change landlord status
	 * @param integer $id landlord id
	 * @param integer $status 0/1
	 * @return boolean
	 */
	public function change_status($id, $status)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix() . $this->table, [
			'active' => $status,
			'datemodified' => date('Y-m-d H:i:s'),
			'modified_by' => get_staff_user_id()
		]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Landlord Status Changed [ID: ' . $id . ' - Status: ' . $status . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Update landlord
	 * @param array $data landlord data
	 * @param mixed $id landlord id
	 * @return boolean
	 */
	public function update($data, $id)
	{
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . $this->table, $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Landlord Updated [ID: ' . $id . ']');
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get landlord with properties
	 * @param integer $id landlord id
	 * @return array
	 */
	public function get_landlord_with_properties($id)
	{
		$landlord = $this->get($id);

		if ( ! $landlord)
		{
			return NULL;
		}

		$this->db->where('landlord_id', $id);
		$properties = $this->db->get(db_prefix() . 'hms_properties')->result_array();

		return [
			'landlord' => $landlord,
			'properties' => $properties
		];
	}

	/**
	 * Search landlords
	 * @param string $q search term
	 * @return array
	 */
	public function search($q)
	{
		$this->db->select('*');
		$this->db->like('name', $q);
		$this->db->or_like('email', $q);
		$this->db->or_like('phone', $q);
		$this->db->or_like('company', $q);
		$this->db->or_like('address', $q);
		$this->db->or_like('city', $q);

		return $this->db->get(db_prefix() . $this->table)->result_array();
	}
}