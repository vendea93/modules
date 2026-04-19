<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_menu_categories_model extends App_Model {
	private $table = 'catering_menu_categories';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get category by id
	 * @param mixed $id category id
	 * @return object
	 */
	public function get($id)
	{
		$this->db->where('id', $id);

		return $this->db->get(db_prefix().$this->table)->row();
	}

	/**
	 * Get all categories (hierarchical or flat)
	 * @param array $where conditions
	 * @param boolean $hierarchical return as tree
	 * @return array
	 */
	public function get_all($where = [], $hierarchical = FALSE)
	{
		if (isset($where['active']))
		{
			$this->db->where('active', $where['active']);
		}

		$this->db->order_by('display_order', 'ASC');
		$this->db->order_by('name', 'ASC');

		$categories = $this->db->get(db_prefix().$this->table)->result_array();

		if ($hierarchical)
		{
			return $this->build_category_tree($categories);
		}

		return $categories;
	}

	/**
	 * Build hierarchical tree from flat array
	 * @param array $categories
	 * @param mixed $parent_id
	 * @return array
	 */
	private function build_category_tree($categories, $parent_id = NULL)
	{
		$branch = [];

		foreach ($categories as $category)
		{
			if ($category['parent_id'] == $parent_id)
			{
				$children = $this->build_category_tree($categories, $category['id']);
				if ($children)
				{
					$category['children'] = $children;
				}
				$branch[] = $category;
			}
		}

		return $branch;
	}

	/**
	 * Get parent categories only
	 * @return array
	 */
	public function get_parent_categories()
	{
		$this->db->where('parent_id IS NULL');
		$this->db->where('active', 1);
		$this->db->order_by('display_order', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}

	/**
	 * Get children of a category
	 * @param mixed $parent_id
	 * @return array
	 */
	public function get_children($parent_id)
	{
		$this->db->where('parent_id', $parent_id);
		$this->db->where('active', 1);
		$this->db->order_by('display_order', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}

	/**
	 * Add new category
	 * @param array $data category data
	 * @return mixed
	 */
	public function add($data)
	{
		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		if ( ! isset($data['display_order']))
		{
			$data['display_order'] = $this->get_max_display_order($data['parent_id'] ?? NULL) + 1;
		}

		// Ensure parent_id is null if empty
		if (isset($data['parent_id']) && empty($data['parent_id']))
		{
			$data['parent_id'] = NULL;
		}

		$this->db->insert(db_prefix().$this->table, $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			log_activity('New Menu Category Created [ID: '.$insert_id.', Name: '.$data['name'].']');
		}

		return $insert_id;
	}

	/**
	 * Update category
	 * @param mixed $id category id
	 * @param array $data update data
	 * @return boolean
	 */
	public function update($id, $data)
	{
		// Prevent circular reference
		if (isset($data['parent_id']) && $data['parent_id'] == $id)
		{
			return FALSE;
		}

		// Check if making parent would create circular reference
		if (isset($data['parent_id']) && $data['parent_id'])
		{
			if ($this->would_create_circular_reference($id, $data['parent_id']))
			{
				return FALSE;
			}
		}

		// Ensure parent_id is null if empty
		if (isset($data['parent_id']) && empty($data['parent_id']))
		{
			$data['parent_id'] = NULL;
		}

		$this->db->where('id', $id);
		$this->db->update(db_prefix().$this->table, $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Menu Category Updated [ID: '.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete category
	 * @param mixed $id category id
	 * @return array
	 */
	public function delete($id)
	{
		// Check if category has children
		if ($this->has_children($id))
		{
			return [
				'status' => FALSE,
				'message' => _l('category_has_children'),
			];
		}

		// Check if category is in use by menu items
		if ($this->is_category_in_use($id))
		{
			return [
				'status' => FALSE,
				'message' => _l('category_in_use_by_items'),
			];
		}

		$category = $this->get($id);
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().$this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Menu Category Deleted [ID: '.$id.', Name: '.$category->name.']');

			return ['status' => TRUE];
		}

		return ['status' => FALSE, 'message' => _l('something_went_wrong')];
	}

	/**
	 * Check if category has children
	 * @param mixed $id category id
	 * @return boolean
	 */
	private function has_children($id)
	{
		$this->db->where('parent_id', $id);
		$count = $this->db->count_all_results(db_prefix().$this->table);

		return $count > 0;
	}

	/**
	 * Check if category is in use by menu items
	 * @param mixed $id category id
	 * @return boolean
	 */
	private function is_category_in_use($id)
	{
		$this->db->where('category_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_menu_items');

		return $count > 0;
	}

	/**
	 * Check if setting parent would create circular reference
	 * @param mixed $id category id
	 * @param mixed $parent_id proposed parent id
	 * @return boolean
	 */
	private function would_create_circular_reference($id, $parent_id)
	{
		$current = $parent_id;
		$visited = [];

		while ($current)
		{
			if ($current == $id)
			{
				return TRUE;
			}

			if (in_array($current, $visited))
			{
				return TRUE;
			}

			$visited[] = $current;
			$category = $this->get($current);
			$current = $category ? $category->parent_id : NULL;
		}

		return FALSE;
	}

	/**
	 * Get max display order for a parent
	 * @param mixed $parent_id
	 * @return int
	 */
	private function get_max_display_order($parent_id = NULL)
	{
		$this->db->select_max('display_order');

		if ($parent_id)
		{
			$this->db->where('parent_id', $parent_id);
		} else
		{
			$this->db->where('parent_id IS NULL');
		}

		$result = $this->db->get(db_prefix().$this->table)->row();

		return $result->display_order ?? 0;
	}

	/**
	 * Update display orders
	 * @param array $orders array of id => order
	 * @return boolean
	 */
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