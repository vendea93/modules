<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_menu_sections_model extends App_Model {
	private $table = 'catering_menu_sections';

	public function __construct()
	{
		parent::__construct();
	}

	public function get($id)
	{
		$this->db->where('id', $id);

		return $this->db->get(db_prefix().$this->table)->row();
	}

	public function get_all($where = [])
	{
		if (isset($where['active']))
		{
			$this->db->where('active', $where['active']);
		}

		$this->db->order_by('display_order', 'ASC');
		$this->db->order_by('name', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}

	public function add($data)
	{
		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		if ( ! isset($data['display_order']))
		{
			$data['display_order'] = $this->get_max_display_order() + 1;
		}

		$this->db->insert(db_prefix().$this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Menu Section Created [ID: '.$insert_id.', Name: '.$data['name'].']');
		}

		return $insert_id;
	}

	public function update($id, $data)
	{
		$this->db->where('id', $id);
		$this->db->update(db_prefix().$this->table, $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Menu Section Updated [ID: '.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	public function delete($id)
	{
		if ($this->is_section_in_use($id))
		{
			return [
				'status' => FALSE,
				'message' => _l('section_in_use_by_menu_items'),
			];
		}

		$section = $this->get($id);
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().$this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Menu Section Deleted [ID: '.$id.', Name: '.$section->name.']');

			return ['status' => TRUE];
		}

		return ['status' => FALSE, 'message' => _l('something_went_wrong')];
	}

	private function is_section_in_use($id)
	{
		$this->db->where('section_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_menu_items_link');

		return $count > 0;
	}

	private function get_max_display_order()
	{
		$this->db->select_max('display_order');
		$result = $this->db->get(db_prefix().$this->table)->row();

		return $result->display_order ?? 0;
	}

	public function update_display_orders($orders)
	{
		foreach ($orders as $id => $order)
		{
			$this->db->where('id', $id);
			$this->db->update(db_prefix().$this->table, ['display_order' => $order]);
		}

		return TRUE;
	}
}