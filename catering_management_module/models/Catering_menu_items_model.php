<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_menu_items_model extends App_Model {
	private $table = 'catering_menu_items';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get menu item with related data
	 * @param mixed $id item id
	 * @return object
	 */
	public function get($id)
	{
		$this->db->where('id', $id);
		$item = $this->db->get(db_prefix().$this->table)->row();

		if ($item)
		{
			// Get dietary types
			$item->dietary_types = $this->get_item_dietary_types($id);

			// Get allergens
			$item->allergens = $this->get_item_allergens($id);

			// Get ingredients
			$item->ingredients = $this->get_item_ingredients($id);

			// Get category details
			if ($item->category_id)
			{
				$this->db->where('id', $item->category_id);
				$item->category = $this->db->get(db_prefix().'catering_menu_categories')->row();
			}
		}

		return $item;
	}

	/**
	 * Get all menu items with filters
	 * @param array $where conditions
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select('i.*, c.name as category_name, c.color as category_color');
		$this->db->from(db_prefix().$this->table.' as i');
		$this->db->join(db_prefix().'catering_menu_categories c', 'c.id = i.category_id', 'left');

		if (isset($where['active']))
		{
			$this->db->where('i.active', $where['active']);
		}

		if (isset($where['category_id']))
		{
			$this->db->where('i.category_id', $where['category_id']);
		}

		if (isset($where['search']))
		{
			$this->db->group_start();
			$this->db->like('i.item_name', $where['search']);
			$this->db->or_like('i.description', $where['search']);
			$this->db->group_end();
		}

		$this->db->order_by('i.item_name', 'ASC');

		$items = $this->db->get()->result_array();

		foreach ($items as &$item)
		{
			$item['dietary_types'] = $this->get_item_dietary_types($item['id']);
			$item['allergens'] = $this->get_item_allergens($item['id']);
		}

		return $items;
	}

	/**
	 * Add new menu item
	 * @param array $data item data
	 * @return mixed
	 */
	public function add($data)
	{
		$data['created_by'] = get_staff_user_id();
		$dietary_types = $data['dietary_types'] ?? [];
		$allergens = $data['allergens'] ?? [];
		$ingredients = $data['ingredients'] ?? [];

		unset($data['dietary_types'], $data['allergens'], $data['ingredients']);

		$this->db->insert(db_prefix().$this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Save dietary types
			$this->save_item_dietary_types($insert_id, $dietary_types);

			// Save allergens
			$this->save_item_allergens($insert_id, $allergens);

			// Save ingredients
			$this->save_item_ingredients($insert_id, $ingredients);

			log_activity('New Menu Item Created [ID: '.$insert_id.', Name: '.$data['item_name'].']');
		}

		return $insert_id;
	}

	/**
	 * Update menu item
	 * @param mixed $id item id
	 * @param array $data update data
	 * @return boolean
	 */
	public function update($id, $data)
	{
		$dietary_types = $data['dietary_types'] ?? [];
		$allergens = $data['allergens'] ?? [];
		$ingredients = $data['ingredients'] ?? [];

		unset($data['dietary_types'], $data['allergens'], $data['ingredients']);

		// Increment version if significant changes
		$old_item = $this->get($id);
		if ($old_item && ($old_item->unit_cost != $data['unit_cost'] ||
				$old_item->unit_price != $data['unit_price']))
		{
			$data['version'] = ($old_item->version ?? 1) + 1;
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix().$this->table, $data);

		// Update related data
		$this->save_item_dietary_types($id, $dietary_types);
		$this->save_item_allergens($id, $allergens);
		$this->save_item_ingredients($id, $ingredients);

		log_activity('Menu Item Updated [ID: '.$id.']');

		return TRUE;
	}

	/**
	 * Delete menu item
	 * @param mixed $id item id
	 * @return array
	 */
	public function delete($id)
	{
		if ($this->is_item_in_use($id))
		{
			return [
				'status' => FALSE,
				'message' => _l('menu_item_in_use'),
			];
		}

		// Delete related data
		$this->db->where('item_id', $id);
		$this->db->delete(db_prefix().'catering_menu_item_dietary');

		$this->db->where('item_id', $id);
		$this->db->delete(db_prefix().'catering_menu_item_allergens');

		$this->db->where('item_id', $id);
		$this->db->delete(db_prefix().'catering_menu_item_ingredients_link');

		// Delete main item
		$item = $this->get($id);
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().$this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Menu Item Deleted [ID: '.$id.', Name: '.$item->item_name.']');

			return ['status' => TRUE];
		}

		return ['status' => FALSE, 'message' => _l('something_went_wrong')];
	}

	/**
	 * Get item dietary types
	 * @param mixed $item_id
	 * @return array
	 */
	private function get_item_dietary_types($item_id)
	{
		$this->db->select('dt.*');
		$this->db->from(db_prefix().'catering_menu_item_dietary mid');
		$this->db->join(db_prefix().'catering_dietary_types dt', 'dt.id = mid.dietary_type_id');
		$this->db->where('mid.item_id', $item_id);
		$this->db->order_by('dt.display_order', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get item allergens
	 * @param mixed $item_id
	 * @return array
	 */
	private function get_item_allergens($item_id)
	{
		$this->db->select('a.*');
		$this->db->from(db_prefix().'catering_menu_item_allergens mia');
		$this->db->join(db_prefix().'catering_allergens a', 'a.id = mia.allergen_id');
		$this->db->where('mia.item_id', $item_id);
		$this->db->order_by('a.display_order', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get item ingredients
	 * @param mixed $item_id
	 * @return array
	 */
	private function get_item_ingredients($item_id)
	{
		$this->db->select('i.*, l.qty_per_portion, l.id as link_id');
		$this->db->from(db_prefix().'catering_menu_item_ingredients_link l');
		$this->db->join(db_prefix().'catering_ingredients i', 'i.id = l.ingredient_id');
		$this->db->where('l.item_id', $item_id);

		return $this->db->get()->result_array();
	}

	/**
	 * Save item dietary types
	 * @param mixed $item_id
	 * @param array $dietary_types array of dietary type ids
	 * @return void
	 */
	private function save_item_dietary_types($item_id, $dietary_types)
	{
		// Delete existing
		$this->db->where('item_id', $item_id);
		$this->db->delete(db_prefix().'catering_menu_item_dietary');

		// Insert new
		if ( ! empty($dietary_types))
		{
			$data = [];
			foreach ($dietary_types as $dt_id)
			{
				$data[] = [
					'item_id' => $item_id,
					'dietary_type_id' => $dt_id,
				];
			}
			$this->db->insert_batch(db_prefix().'catering_menu_item_dietary', $data);
		}
	}

	/**
	 * Save item allergens
	 * @param mixed $item_id
	 * @param array $allergens array of allergen ids
	 * @return void
	 */
	private function save_item_allergens($item_id, $allergens)
	{
		// Delete existing
		$this->db->where('item_id', $item_id);
		$this->db->delete(db_prefix().'catering_menu_item_allergens');

		// Insert new
		if ( ! empty($allergens))
		{
			$data = [];
			foreach ($allergens as $allergen_id)
			{
				$data[] = [
					'item_id' => $item_id,
					'allergen_id' => $allergen_id,
				];
			}
			$this->db->insert_batch(db_prefix().'catering_menu_item_allergens', $data);
		}
	}

	/**
	 * Save item ingredients
	 * @param mixed $item_id
	 * @param array $ingredients array of [ingredient_id, qty_per_portion]
	 * @return void
	 */
	private function save_item_ingredients($item_id, $ingredients)
	{
		// Delete existing
		$this->db->where('item_id', $item_id);
		$this->db->delete(db_prefix().'catering_menu_item_ingredients_link');

		// Insert new
		if ( ! empty($ingredients))
		{
			$data = [];
			foreach ($ingredients as $ing)
			{
				if (isset($ing['ingredient_id']) && isset($ing['qty_per_portion']))
				{
					$data[] = [
						'item_id' => $item_id,
						'ingredient_id' => $ing['ingredient_id'],
						'qty_per_portion' => $ing['qty_per_portion'],
					];
				}
			}
			if ( ! empty($data))
			{
				$this->db->insert_batch(db_prefix().'catering_menu_item_ingredients_link', $data);
			}
		}
	}

	/**
	 * Check if item is in use
	 * @param mixed $id item id
	 * @return boolean
	 */
	private function is_item_in_use($id)
	{
		// Check in menus
		$this->db->where('item_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_menu_items_link');

		if ($count > 0)
		{
			return TRUE;
		}

		// Check in packages
		$this->db->where('item_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_package_items_link');

		if ($count > 0)
		{
			return TRUE;
		}

		// Check in event menus
		$this->db->where('item_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_event_menu_items');

		return $count > 0;
	}

	/**
	 * Calculate item cost from ingredients
	 * @param mixed $id item id
	 * @return float
	 */
	public function calculate_cost($id)
	{
		$ingredients = $this->get_item_ingredients($id);
		$total_cost = 0;

		foreach ($ingredients as $ing)
		{
			$total_cost += $ing['qty_per_portion'] * $ing['avg_cost_per_unit'];
		}

		return round($total_cost, 2);
	}

	/**
	 * Get items by category
	 * @param mixed $category_id
	 * @return array
	 */
	public function get_by_category($category_id)
	{
		$this->db->where('category_id', $category_id);
		$this->db->where('active', 1);
		$this->db->order_by('item_name', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}
}