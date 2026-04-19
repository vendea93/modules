<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_packages_model extends App_Model {
	private $table = 'catering_packages';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get package with items
	 * @param mixed $id package id
	 * @return object
	 */
	public function get($id)
	{
		$this->db->where('id', $id);
		$package = $this->db->get(db_prefix().$this->table)->row();

		if ($package)
		{
			$package->items = $this->get_package_items($id);
		}

		return $package;
	}

	/**
	 * Get all packages
	 * @param array $where conditions
	 * @return array
	 */
	public function get_all($where = [])
	{
		if (isset($where['active']))
		{
			$this->db->where('active', $where['active']);
		}

		$this->db->order_by('package_name', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}

	/**
	 * Get package items
	 * @param mixed $package_id
	 * @return array
	 */
	public function get_package_items($package_id)
	{
		$this->db->select(
			'
            pil.*,
            mi.item_name,
            mi.description,
            mi.unit_cost,
            mi.unit_price,
            mc.name as category_name
        '
		);
		$this->db->from(db_prefix().'catering_package_items_link pil');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = pil.item_id');
		$this->db->join(db_prefix().'catering_menu_categories mc', 'mc.id = mi.category_id', 'left');
		$this->db->where('pil.package_id', $package_id);
		$this->db->order_by('mc.display_order', 'ASC');
		$this->db->order_by('mi.item_name', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add new package
	 * @param array $data package data
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
			// Save package items
			if ( ! empty($items))
			{
				$this->save_package_items($insert_id, $items);
			}

			log_activity('New Package Created [ID: '.$insert_id.', Name: '.$data['package_name'].']');
		}

		return $insert_id;
	}

	/**
	 * Update package
	 * @param mixed $id package id
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
			$this->save_package_items($id, $items);
		}

		log_activity('Package Updated [ID: '.$id.']');

		return TRUE;
	}

	/**
	 * Delete package
	 * @param mixed $id package id
	 * @return array
	 */
	public function delete($id)
	{
		if ($this->is_package_in_use($id))
		{
			return [
				'status' => FALSE,
				'message' => _l('package_in_use_by_events'),
			];
		}

		// Delete package items links
		$this->db->where('package_id', $id);
		$this->db->delete(db_prefix().'catering_package_items_link');

		// Delete package
		$package = $this->get($id);
		$this->db->where('id', $id);
		$this->db->delete(db_prefix().$this->table);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Package Deleted [ID: '.$id.', Name: '.$package->package_name.']');

			return ['status' => TRUE];
		}

		return ['status' => FALSE, 'message' => _l('something_went_wrong')];
	}

	/**
	 * Save package items
	 * @param mixed $package_id
	 * @param array $items array of [item_id, qty_per_guest]
	 * @return void
	 */
	private function save_package_items($package_id, $items)
	{
		// Delete existing
		$this->db->where('package_id', $package_id);
		$this->db->delete(db_prefix().'catering_package_items_link');

		// Insert new
		if ( ! empty($items))
		{
			$data = [];
			foreach ($items as $item)
			{
				if (isset($item['item_id']) && isset($item['qty_per_guest']))
				{
					$data[] = [
						'package_id' => $package_id,
						'item_id' => $item['item_id'],
						'qty_per_guest' => $item['qty_per_guest'],
					];
				}
			}
			if ( ! empty($data))
			{
				$this->db->insert_batch(db_prefix().'catering_package_items_link', $data);
			}
		}
	}

	/**
	 * Add item to package
	 * @param mixed $package_id
	 * @param mixed $item_id
	 * @param float $qty_per_guest
	 * @return mixed
	 */
	public function add_item($package_id, $item_id, $qty_per_guest = 1)
	{
		$data = [
			'package_id' => $package_id,
			'item_id' => $item_id,
			'qty_per_guest' => $qty_per_guest,
		];

		$this->db->insert(db_prefix().'catering_package_items_link', $data);

		return $this->db->insert_id();
	}
    /**
     * Add multiple items to a package
     * @param int $package_id
     * @param array $item_ids
     * @return int Number of items added
     */
    public function add_multiple_items($package_id, $item_ids)
    {
        if (empty($item_ids)) {
            return 0;
        }

        // Get existing item IDs in the package to prevent duplicates
        $existing_items = $this->db->select('item_id')->where('package_id', $package_id)->get(db_prefix().'catering_package_items_link')->result_array();
        $existing_item_ids = array_column($existing_items, 'item_id');

        $items_to_add = [];
        foreach ($item_ids as $item_id) {
            // Add only if it doesn't already exist
            if (!in_array($item_id, $existing_item_ids)) {
                $items_to_add[] = [
                    'package_id'    => $package_id,
                    'item_id'       => $item_id,
                    'qty_per_guest' => 1, // Default quantity
                ];
            }
        }

        if (empty($items_to_add)) {
            return 0;
        }

        $this->db->insert_batch(db_prefix().'catering_package_items_link', $items_to_add);
        return $this->db->affected_rows();
    }


	/**
	 * Remove item from package
	 * @param mixed $link_id
	 * @return boolean
	 */
	public function remove_item($link_id)
	{
		$this->db->where('id', $link_id);
		$this->db->delete(db_prefix().'catering_package_items_link');

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Update item quantity
	 * @param mixed $link_id
	 * @param float $qty_per_guest
	 * @return boolean
	 */
	public function update_item_quantity($link_id, $qty_per_guest)
	{
		$this->db->where('id', $link_id);
		$this->db->update(db_prefix().'catering_package_items_link', ['qty_per_guest' => $qty_per_guest]);

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Check if package is in use
	 * @param mixed $id package id
	 * @return boolean
	 */
	private function is_package_in_use($id)
	{
		$this->db->where('package_id', $id);
		$count = $this->db->count_all_results(db_prefix().'catering_event_menu');

		return $count > 0;
	}

	/**
	 * Calculate package cost per person
	 * @param mixed $id package id
	 * @return float
	 */
	public function calculate_cost_per_person($id)
	{
		$items = $this->get_package_items($id);
		$total_cost = 0;

		foreach ($items as $item)
		{
			$total_cost += $item['unit_cost'] * $item['qty_per_guest'];
		}

		return round($total_cost, 2);
	}

	/**
	 * Duplicate package
	 * @param mixed $id package id to duplicate
	 * @param string $new_name optional new name
	 * @return mixed new package id
	 */
	public function duplicate($id, $new_name = NULL)
	{
		$package = $this->get($id);

		if ( ! $package)
		{
			return FALSE;
		}

		$new_package_data = [
			'package_name' => $new_name ?? ($package->package_name.' (Copy)'),
			'description' => $package->description,
			'price_per_person' => $package->price_per_person,
			'min_guests' => $package->min_guests,
			'max_guests' => $package->max_guests,
			'active' => $package->active,
		];

		$new_package_id = $this->add($new_package_data);

		if ($new_package_id)
		{
			// Copy package items
			$this->db->select('item_id, qty_per_guest');
			$this->db->where('package_id', $id);
			$items = $this->db->get(db_prefix().'catering_package_items_link')->result_array();

		if ( ! empty($items))
			{
				foreach ($items as &$item)
				{
					$item['package_id'] = $new_package_id;
				}
				$this->db->insert_batch(db_prefix().'catering_package_items_link', $items);
			}

			log_activity('Package Duplicated [Original ID: '.$id.', New ID: '.$new_package_id.']');
		}

		return $new_package_id;
	}

	/**
	 * Get packages suitable for guest count
	 * @param int $guest_count
	 * @return array
	 */
	public function get_for_guest_count($guest_count)
	{
		$this->db->where('active', 1);
		$this->db->where('min_guests <=', $guest_count);
		$this->db->group_start();
		$this->db->where('max_guests IS NULL');
		$this->db->or_where('max_guests >=', $guest_count);
		$this->db->group_end();
		$this->db->order_by('package_name', 'ASC');

		return $this->db->get(db_prefix().$this->table)->result_array();
	}
}