<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_menus_model extends App_Model {
	private $table = 'catering_menus';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get menu with items
	 * @param mixed $id menu id
	 * @return object
	 */
	public function get($id)
	{
		$this->db->where('id', $id);
		$menu = $this->db->get(db_prefix().$this->table)->row();

		if ($menu)
		{
			$menu->items = $this->get_menu_items($id);
		}

		return $menu;
	}

	/**
	 * Get all menus
	 * @param array $where conditions
	 * @return array
	 */
	public function get_all($where = [])
	{
		if (isset($where['active']))
		{
			$this->db->where('active', $where['active']);
		}

		$this->db->order_by('menu_name', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}

	/**
	 * Get menu items grouped by section
	 * @param mixed $menu_id
	 * @return array
	 */
	public function get_menu_items($menu_id)
	{
		$this->db->select(
			'
            mil.id as link_id,
            mil.section_id,
            mil.position,
            ms.name as section_name,
            ms.display_order as section_order,
            mi.*,
            mc.name as category_name
        '
		);
		$this->db->from(db_prefix().'catering_menu_items_link mil');
		$this->db->join(db_prefix().'catering_menu_sections ms', 'ms.id = mil.section_id');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = mil.item_id');
		$this->db->join(db_prefix().'catering_menu_categories mc', 'mc.id = mi.category_id', 'left');
		$this->db->where('mil.menu_id', $menu_id);
		$this->db->order_by('ms.display_order', 'ASC');
		$this->db->order_by('mil.position', 'ASC');

		$items = $this->db->get()->result_array();

		// Group by section
		$grouped = [];
		foreach ($items as $item)
		{
			$section_id = $item['section_id'];
			if ( ! isset($grouped[$section_id]))
			{
				$grouped[$section_id] = [
					'section_id' => $section_id,
					'section_name' => $item['section_name'],
					'section_order' => $item['section_order'],
					'items' => [],
				];
			}
			$grouped[$section_id]['items'][] = $item;
		}

		return array_values($grouped);
	}

	/**
	 * Add new menu
	 * @param array $data menu data
	 * @return mixed
	 */
	public function add($data)
	{
		$items = $data['items'] ?? [];
		unset($data['items']);

		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		$this->db->insert(db_prefix().$this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Save menu items
			if ( ! empty($items))
			{
				$this->save_menu_items($insert_id, $items);
			}

			log_activity('New Menu Created [ID: '.$insert_id.', Name: '.$data['menu_name'].']');
		}

		return $insert_id;
	}

	/**
	 * Update menu
	 * @param mixed $id menu id
	 * @param array $data update data
	 * @return boolean
	 */
	public function update($id, $data)
	{
		$items = $data['items'] ?? NULL;
		unset($data['items']);

		$data['updated_at'] = date('Y-m-d H:i:s');

		$this->db->where('id', $id);
		$this->db->update(db_prefix().$this->table, $data);

		// Update items if provided
		if ($items !== NULL)
		{
			$this->save_menu_items($id, $items);
		}

		log_activity('Menu Updated [ID: '.$id.']');

		return TRUE;
	}

	/**
	 * Delete menu
	 * @param mixed $id menu id
	 * @return array
	 */
	public function delete($id)
	{
		try
		{
			if ($this->is_menu_in_use($id))
			{
				return [
					'status' => FALSE,
					'message' => _l('menu_in_use_by_events'),
				];
			}

			// Delete menu items links
			$this->db->where('menu_id', $id);
			$this->db->delete(db_prefix().'catering_menu_items_link');

			// Delete menu
			$menu = $this->get($id);
			$this->db->where('id', $id);
			$this->db->delete(db_prefix().$this->table);

			log_activity('Menu Deleted [ID: '.$id.', Name: '.$menu->menu_name.']');

			return [
				'status' => TRUE,
			];
		} catch (Exception $exception)
		{
			error_log($exception->getMessage());

			return [
				'status' => FALSE,
				'message' => _l('something_went_wrong'),
			];
		}
	}

	/**
	 * Save menu items
	 * @param mixed $menu_id
	 * @param array $items array of [section_id, item_id, position]
	 * @return void
	 */
	private function save_menu_items($menu_id, $items)
	{
		// Delete existing
		$this->db->where('menu_id', $menu_id);
		$this->db->delete(db_prefix().'catering_menu_items_link');

		// Insert new
		if ( ! empty($items))
		{
			$data = [];
			foreach ($items as $item)
			{
				if (isset($item['item_id']) && isset($item['section_id']))
				{
					$data[] = [
						'menu_id' => $menu_id,
						'item_id' => $item['item_id'],
						'section_id' => $item['section_id'],
						'position' => $item['position'] ?? 0,
					];
				}
			}
			if ( ! empty($data))
			{
				$this->db->insert_batch(db_prefix().'catering_menu_items_link', $data);
			}
		}
	}

	/**
	 * Add item to menu
	 * @param mixed $menu_id
	 * @param mixed $item_id
	 * @param mixed $section_id
	 * @param int $position
	 * @return mixed
	 */
	public function add_item($menu_id, $item_id, $section_id, $position = NULL)
	{
		if ($position === NULL)
		{
			$position = $this->get_max_position($menu_id, $section_id) + 1;
		}

		$data = [
			'menu_id' => $menu_id,
			'item_id' => $item_id,
			'section_id' => $section_id,
			'position' => $position,
		];

		$this->db->insert(db_prefix().'catering_menu_items_link', $data);

		return $this->db->insert_id();
	}

	/**
	 * Remove item from menu
	 * @param mixed $link_id
	 * @return boolean
	 */
	public function remove_item($link_id)
	{
		$this->db->where('id', $link_id);
		$this->db->delete(db_prefix().'catering_menu_items_link');

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Update item positions in menu
	 * @param array $positions array of link_id => position
	 * @return boolean
	 */
	public function update_item_positions($positions)
	{
		foreach ($positions as $link_id => $position)
		{
			$this->db->where('id', $link_id);
			$this->db->update(db_prefix().'catering_menu_items_link', ['position' => $position]);
		}

		return TRUE;
	}

	/**
	 * Get max position for a section
	 * @param mixed $menu_id
	 * @param mixed $section_id
	 * @return int
	 */
	private function get_max_position($menu_id, $section_id)
	{
		$this->db->select_max('position');
		$this->db->where('menu_id', $menu_id);
		$this->db->where('section_id', $section_id);
		$result = $this->db->get(db_prefix().'catering_menu_items_link')->row();

		return $result->position ?? 0;
	}

	/**
	 * Check if menu is in use
	 * @param mixed $id menu id
	 * @return boolean
	 */
	private function is_menu_in_use($id)
	{
		$this->db->where('menu_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_event_menu');

		return $count > 0;
	}

	/**
	 * Duplicate menu
	 * @param mixed $id menu id to duplicate
	 * @param string $new_name optional new name
	 * @return mixed new menu id
	 */
	public function duplicate($id, $new_name = NULL)
	{
		$menu = $this->get($id);

		if ( ! $menu)
		{
			return FALSE;
		}

		$new_menu_data = [
			'menu_name' => $new_name ?? ($menu->menu_name.' (Copy)'),
			'description' => $menu->description,
			'base_price_per_person' => $menu->base_price_per_person,
			'active' => $menu->active,
		];

		$new_menu_id = $this->add($new_menu_data);

		if ($new_menu_id)
		{
			// Copy menu items
			$this->db->select('item_id, section_id, position');
			$this->db->where('menu_id', $id);
			$items = $this->db->get(db_prefix().'catering_menu_items_link')->result_array();

			if ( ! empty($items))
			{
				foreach ($items as &$item)
				{
					$item['menu_id'] = $new_menu_id;
				}
				$this->db->insert_batch(db_prefix().'catering_menu_items_link', $items);
			}

			log_activity('Menu Duplicated [Original ID: '.$id.', New ID: '.$new_menu_id.']');
		}

		return $new_menu_id;
	}
}