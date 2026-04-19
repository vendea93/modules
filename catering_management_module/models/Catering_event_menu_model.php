<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_event_menu_model extends App_Model {
	private $table = 'catering_event_menu';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get event menu data
	 * @param int $event_id
	 * @return object|null
	 */
	public function get_event_menu($event_id)
	{
		$this->db->select(
			'
            cem.*,
            cm.menu_name,
            cm.description as menu_description,
            cm.base_price_per_person as menu_base_price,
            cp.package_name,
            cp.description as package_description,
            cp.price_per_person as package_price,
            cp.min_guests,
            cp.max_guests
        '
		);
		$this->db->from(db_prefix().$this->table.' cem');
		$this->db->join(db_prefix().'catering_menus cm', 'cm.id = cem.menu_id', 'left');
		$this->db->join(db_prefix().'catering_packages cp', 'cp.id = cem.package_id', 'left');
		$this->db->where('cem.event_id', $event_id);

		$event_menu = $this->db->get()->row();

		if ($event_menu)
		{
			$event_menu->allergen_summary = $this->get_allergen_summary($event_id);
			$event_menu->dietary_summary = $this->get_dietary_summary($event_id);

			$items = $this->get_event_menu_items($event_id);

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

			$event_menu->items = array_values($grouped);
		}

		return $event_menu;
	}

	/**
	 * Get event menu items
	 * @param int $event_id
	 * @return array
	 */
	public function get_event_menu_items($event_id)
	{
		$this->db->select(
			'
            cemi.*,
            mi.item_name,
            mi.description,
            mi.unit_cost as menu_item_cost,
            mi.unit_price as default_unit_price,
            mi.prep_time_minutes,
            mc.name as category_name,
            ms.name as section_name,
            ms.display_order as section_order
        '
		);
		$this->db->from(db_prefix().'catering_event_menu_items cemi');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = cemi.item_id');
		$this->db->join(db_prefix().'catering_event_menu cem', 'cem.id = cemi.event_menu_id');
		$this->db->join(db_prefix().'catering_menu_categories mc', 'mc.id = mi.category_id', 'left');
		$this->db->join(db_prefix().'catering_menu_sections ms', 'ms.id = cemi.section_id', 'left');
		$this->db->where('cem.event_id', $event_id);
		$this->db->order_by('ms.display_order', 'ASC');
		$this->db->order_by('cemi.position', 'ASC');

		$items = $this->db->get()->result_array();

		// Add dietary and allergen info
		foreach ($items as &$item)
		{
			$item['dietary_types'] = $this->get_item_dietary_types($item['item_id']);
			$item['allergens'] = $this->get_item_allergens($item['item_id']);
		}

		return $items;
	}

	/**
	 * Get allergen summary for event
	 * @param int $event_id
	 * @return array
	 */
	public function get_allergen_summary($event_id)
	{
		$this->db->select(
			'
            ca.label,
            ca.code,
            ca.severity,
            COUNT(cemi.id) as item_count
        '
		);
		$this->db->from(db_prefix().'catering_event_menu_items cemi');
		$this->db->join(db_prefix().'catering_menu_item_allergens mia', 'mia.item_id = cemi.item_id');
		$this->db->join(db_prefix().'catering_allergens ca', 'ca.id = mia.allergen_id');
		$this->db->where('cemi.event_id', $event_id);
		$this->db->group_by('ca.id');
		$this->db->order_by('ca.label', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get dietary summary for event
	 * @param int $event_id
	 * @return array
	 */
	public function get_dietary_summary($event_id)
	{
		$this->db->select(
			'
            cdt.label,
            cdt.code,
            COUNT(cemi.id) as item_count
        '
		);
		$this->db->from(db_prefix().'catering_event_menu_items cemi');
		$this->db->join(db_prefix().'catering_menu_item_dietary midt', 'midt.item_id = cemi.item_id');
		$this->db->join(db_prefix().'catering_dietary_types cdt', 'cdt.id = midt.dietary_type_id');
		$this->db->where('cemi.event_id', $event_id);
		$this->db->group_by('cdt.id');
		$this->db->order_by('cdt.label', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get item dietary types
	 * @param int $item_id
	 * @return array
	 */
	private function get_item_dietary_types($item_id)
	{
		$this->db->select('cdt.label, cdt.code');
		$this->db->from(db_prefix().'catering_menu_item_dietary midt');
		$this->db->join(db_prefix().'catering_dietary_types cdt', 'cdt.id = midt.dietary_type_id');
		$this->db->where('midt.item_id', $item_id);

		return $this->db->get()->result_array();
	}

	/**
	 * Get item allergens
	 * @param int $item_id
	 * @return array
	 */
	private function get_item_allergens($item_id)
	{
		$this->db->select('ca.label, ca.code, ca.severity');
		$this->db->from(db_prefix().'catering_menu_item_allergens mia');
		$this->db->join(db_prefix().'catering_allergens ca', 'ca.id = mia.allergen_id');
		$this->db->where('mia.item_id', $item_id);

		return $this->db->get()->result_array();
	}

	/**
	 * Save event menu
	 * @param int $event_id
	 * @param array $data
	 * @return bool
	 */
	public function save_event_menu($event_id, $data)
	{
		// Check if event menu exists
		$existing = $this->get_event_menu($event_id);

		if ($existing)
		{
			// Update existing
			$this->db->where('event_id', $event_id);
			$this->db->update(db_prefix().$this->table, $data);
		} else
		{
			// Create new
			$data['event_id'] = $event_id;
			$data['created_by'] = get_staff_user_id();
			$this->db->insert(db_prefix().$this->table, $data);
		}

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Add item to event menu
	 * @param int $event_id
	 * @param int $item_id
	 * @param int $section_id
	 * @param float $portion_size
	 * @param float $unit_price
	 * @return int|bool
	 */
	public function add_menu_item($event_menu_id, $event_id, $item_id, $section_id, $portion_size = 1.0, $cost_price = 0, $unit_price = NULL)
	{
		// Get item details if unit_price not provided
		if ($unit_price === NULL)
		{
			$this->db->select('unit_price');
			$this->db->where('id', $item_id);
			$item = $this->db->get(db_prefix().'catering_menu_items')->row();
			$unit_price = $item ? $item->unit_price : 0;
		}

		// Get next position
		$this->db->select_max('position');
		$this->db->where('event_id', $event_id);
		$this->db->where('section_id', $section_id);
		$result = $this->db->get(db_prefix().'catering_event_menu_items')->row();
		$position = ($result->position ?? 0) + 1;

		$data = [
			'event_menu_id' => $event_menu_id,
			'event_id' => $event_id,
			'item_id' => $item_id,
			'section_id' => $section_id,
			'portion_per_guest' => $portion_size,
			'unit_cost' => $cost_price,
			'unit_price' => $unit_price,
			'position' => $position,
			'created_by' => get_staff_user_id(),
			'created_at' => date('Y-m-d H:i:s'),
		];


		$this->db->insert(db_prefix().'catering_event_menu_items', $data);

		return $this->db->insert_id();
	}

	/**
	 * Remove item from event menu
	 * @param int $event_item_id
	 * @return bool
	 */
	public function remove_menu_item($event_item_id)
	{
		$this->db->where('id', $event_item_id);
		$this->db->delete(db_prefix().'catering_event_menu_items');

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Update menu item
	 * @param int $event_item_id
	 * @param array $data
	 * @return bool
	 */
	public function update_menu_item($event_item_id, $data)
	{
		$this->db->where('id', $event_item_id);
		$this->db->update(db_prefix().'catering_event_menu_items', $data);

		return $this->db->affected_rows() > 0;
	}

	/**
	 * Update item positions
	 * @param array $positions
	 * @return bool
	 */
	public function update_item_positions($positions)
	{
		foreach ($positions as $item_id => $position)
		{
			$this->db->where('id', $item_id);
			$this->db->update(db_prefix().'catering_event_menu_items', ['position' => $position]);
		}

		return TRUE;
	}

	/**
	 * Calculate total menu cost
	 * @param int $event_id
	 * @param int $guest_count
	 * @return float
	 */
	public function calculate_total_cost($event_id, $guest_count)
	{
		$this->db->select('SUM(portion_size * unit_price * '.$guest_count.') as total_cost');
		$this->db->where('event_id', $event_id);
		$result = $this->db->get(db_prefix().'catering_event_menu_items')->row();

		return $result->total_cost ?? 0;
	}

	/**
	 * Load menu/package to event
	 * @param int $event_id
	 * @param string $type (menu|package)
	 * @param int $id
	 * @return bool
	 */
	public function load_menu_or_package(int $event_id, string $type, $id)
	{
		$existing = $this->get_event_menu($event_id);

		// Clear existing items
		$this->db->where('event_menu_id', $existing->id);
		$this->db->delete(db_prefix().'catering_event_menu_items');

		if ($type === 'package')
		{
			return $this->load_package_items($event_id, $id);
		} else
		{
			return $this->load_menu_items($event_id, $id);
		}
	}

	/**
	 * Load menu items to event
	 * @param int $event_id
	 * @param int $menu_id
	 * @return bool
	 */
	private function load_menu_items($event_id, $menu_id)
	{
		$this->db->select('mil.item_id, mil.section_id, mil.position, mi.unit_price');
		$this->db->from(db_prefix().'catering_menu_items_link mil');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = mil.item_id');
		$this->db->where('mil.menu_id', $menu_id);

		$items = $this->db->get()->result_array();
		$event_menu = $this->get_event_menu($event_id);
		$event_menu_id = $event_menu->id;

		foreach ($items as $item)
		{
			$this->add_menu_item($event_menu_id, $event_id, $item['item_id'], $item['section_id'], 1.0, $item['cost_price'], $item['unit_price']);
		}

		return TRUE;
	}

	/**
	 * Load package items to event
	 * @param int $event_id
	 * @param int $package_id
	 * @return bool
	 */
	private function load_package_items($event_id, $package_id)
	{
		$this->db->select('pil.item_id, pil.qty_per_guest, mi.unit_price');
		$this->db->from(db_prefix().'catering_package_items_link pil');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = pil.item_id');
		$this->db->where('pil.package_id', $package_id);

		$items = $this->db->get()->result_array();
		$event_menu = $this->get_event_menu($event_id);
		$event_menu_id = $event_menu->id;


		$this->db->select('id');
		$this->db->limit(1);
		$section = $this->db->get(db_prefix().'catering_menu_sections')->row_array();
		if (empty($section))
		{
			return FALSE;
		}
		$section_id = $section['id'];
		foreach ($items as $item)
		{
			// Use default section (you might want to modify this)
			$this->add_menu_item($event_menu_id, $event_id, $item['item_id'], $section_id, $item['qty_per_guest'], $item['cost_price'], $item['unit_price']);
		}

		return TRUE;
	}

	public function get_event_menu_summary($event_id)
	{
		$data = [
			'total_sections' => 0,
			'total_items' => 0,
			'total_costs' => 0,
			'total_prices' => 0,
		];
		$menu = $this->get_event_menu($event_id);
		if (empty($menu))
		{
			return $data;
		}
		$sections = $menu->items;
		$data['total_sections'] = count($sections);

		foreach ($sections as $section)
		{
			$section_items = $section['items'];

			$data['total_items'] += count($section_items);
			$data['total_costs'] += array_sum(array_column($section_items, 'unit_cost'));
			$data['total_prices'] += array_sum(array_column($section_items, 'unit_price'));
		}

		return $data;
	}
}
