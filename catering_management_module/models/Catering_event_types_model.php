<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_event_types_model extends App_Model {
	/**
	 * @param string $id
	 * @return array|null
	 */
	public function get(string $id = '')
	{
		if ( ! empty($id))
		{
			$this->db->where('etid', $id);

			return $this->db->get(db_prefix().'catering_event_types')->row_array();
		} else
		{
			return $this->db->get(db_prefix().'catering_event_types')->result_array();
		}

	}

	/**
	 * @param $event_type_name
	 * @return array|null
	 */
	public function get_by_name($event_type_name)
	{
		$event_type_name = strtolower($event_type_name);

		return $this->db->where('LOWER(name)', $event_type_name)
			->get(db_prefix().'catering_event_types')
			->row_array();
	}

	/**
	 * @param array $data
	 * @return int
	 */
	public function create(array $data): int
	{
		if ( ! isset($data['created_by']))
		{
			$data['created_by'] = get_staff_user_id();
		}
		$this->db->insert(db_prefix().'catering_event_types', $data);

		return $this->db->insert_id();
	}


	/**
	 * @param $event_type_id
	 * @param $data
	 * @return bool
	 */
	public function update($event_type_id, $data): bool
	{
		$this->db->where('etid', $event_type_id);

		return $this->db->update(db_prefix().'catering_event_types', $data);
	}

	/**
	 * @param string $old_name
	 * @param array $data
	 * @return bool
	 */
	public function update_by_name(string $old_name, array $data): bool
	{
		$this->db->where('name', $old_name);
		$this->db->update(db_prefix().'catering_event_types', $data);

		return $this->db->affected_rows() > 0;
	}

	/**
	 * @param $project_id
	 * @param $event_type_id
	 * @return false|mixed|string
	 */
	public function delete($event_type_id): mixed
	{
		$this->db->where('etid', $event_type_id);

		return $this->db->delete(db_prefix().'catering_event_types');
	}
}