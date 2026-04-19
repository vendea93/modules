<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Handle catering event attachment upload
 * @param int $event_id Event ID
 * @return array
 */
function handle_catering_event_attachments($event_id)
{
	$CI = &get_instance();
	$path = get_upload_path_by_type('catering_event').$event_id.'/';

	if ( ! is_dir($path))
	{
		mkdir($path, 0755, TRUE);
	}

	$CI->load->library('upload');

	$config['upload_path'] = $path;
	$config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|xls|xlsx|txt';
	$config['max_size'] = 10240; // 10MB
	$config['encrypt_name'] = TRUE;

	$CI->upload->initialize($config);

	if ( ! $CI->upload->do_upload('file'))
	{
		return [
			'success' => FALSE,
			'message' => $CI->upload->display_errors('', ''),
		];
	}

	$data = $CI->upload->data();

	$attachment = [
		'rel_id' => $event_id,
		'rel_type' => 'catering_event',
		'file_name' => $data['file_name'],
		'filetype' => $data['file_type'],
		'visible_to_customer' => 0,
		'attachment_key' => app_generate_hash(),
		'external' => '',
		'external_link' => '',
		'thumbnail_link' => '',
		'staffid' => get_staff_user_id(),
		'contact_id' => 0,
		'task_comment_id' => 0,
		'dateadded' => date('Y-m-d H:i:s'),
	];

	$CI->db->insert(db_prefix().'files', $attachment);

	return [
		'success' => TRUE,
		'message' => _l('file_uploaded_successfully'),
		'file_id' => $CI->db->insert_id(),
	];
}

/**
 * Get catering event status color class
 * @param string $status Status
 * @return string
 */
function get_event_status_color($status)
{
	$colors = [
		'enquiry' => 'info',
		'quoted' => 'primary',
		'confirmed' => 'success',
		'in_progress' => 'warning',
		'completed' => 'default',
		'cancelled' => 'danger',
		'lost' => 'muted',
	];

	return isset($colors[$status]) ? $colors[$status] : 'default';
}

/**
 * Get upload path for catering files
 * @return string
 */
function get_catering_upload_path()
{
	return FCPATH.'uploads/catering_events/';
}

/**
 * Format event date range
 * @param string $start Start datetime
 * @param string $end End datetime
 * @return string
 */
function format_event_date_range($start, $end = NULL)
{
	$start_date = _dt($start);

	if (empty($end))
	{
		return $start_date;
	}

	$end_date = _dt($end);

	// Same day
	if (date('Y-m-d', strtotime($start)) == date('Y-m-d', strtotime($end)))
	{
		return _d($start).' '.date('H:i', strtotime($start)).' - '.date('H:i', strtotime($end));
	}

	return $start_date.' - '.$end_date;
}

/**
 * Check if user can view event costs/margins
 * @return bool
 */
function can_view_event_costs()
{
	return staff_can('view', 'catering_view_costs');
}

/**
 * Get event status badge HTML
 * @param string $status Status
 * @return string
 */
function event_status_badge($status)
{
	$color = get_event_status_color($status);
	$label = _l('event_status_'.$status);

	return '<span class="label label-'.$color.'">'.$label.'</span>';
}

/**
 * Calculate days until event
 * @param string $event_date Event start date
 * @return int
 */
function days_until_event($event_date)
{
	$now = new DateTime();
	$event = new DateTime($event_date);
	$diff = $now->diff($event);

	return $diff->invert ? -$diff->days : $diff->days;
}

/**
 * Check if event is upcoming (within 7 days)
 * @param string $event_date Event start date
 * @return bool
 */
function is_event_upcoming($event_date)
{
	$days = days_until_event($event_date);

	return $days >= 0 && $days <= 7;
}

/**
 * Check if event is past
 * @param string $event_date Event start date
 * @return bool
 */
function is_event_past($event_date)
{
	return days_until_event($event_date) < 0;
}

/**
 * Get event short code for display
 * @param int $event_id Event ID
 * @return string
 */
function get_event_code($event_id)
{
	return 'CAT-'.date('Y').'-'.str_pad($event_id, 4, '0', STR_PAD_LEFT);
}

function cmm_debug_var($var, $die = TRUE)
{
	echo '<pre style="background:black;color:white; line-height: 1.5em; white-space:pre-wrap;max-width: 100%;width: 100%;">';
	print_r($var);
	echo '</pre>';
	if ($die)
	{
		die();
	}
}


defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get allergen badge HTML
 * @param array $allergen
 * @return string
 */
function catering_allergen_badge($allergen)
{
	$severity_colors = [
		'mild' => 'info',
		'moderate' => 'warning',
		'severe' => 'danger',
	];

	$color = $severity_colors[$allergen['severity']] ?? 'default';
	$icon = $allergen['icon'] ? '<i class="'.$allergen['icon'].'"></i> ' : '';

	return '<span class="label label-'.$color.'" title="'.htmlspecialchars($allergen['description'] ?? '').'">'.
		$icon.htmlspecialchars($allergen['label']).
		'</span>';
}

/**
 * Get dietary type badge HTML
 * @param array $dietary_type
 * @return string
 */
function catering_dietary_badge($dietary_type)
{
	$icon = $dietary_type['icon'] ? '<i class="'.$dietary_type['icon'].'"></i> ' : '';

	return '<span class="label label-success" title="'.htmlspecialchars($dietary_type['description'] ?? '').'">'.
		$icon.htmlspecialchars($dietary_type['label']).
		'</span>';
}

/**
 * Get menu item dietary and allergen tags
 * @param object $item
 * @return string
 */
function catering_item_tags($item)
{
	$tags = '';

	if ( ! empty($item->dietary_types))
	{
		foreach ($item->dietary_types as $dt)
		{
			$tags .= catering_dietary_badge($dt).' ';
		}
	}

	if ( ! empty($item->allergens))
	{
		foreach ($item->allergens as $allergen)
		{
			$tags .= catering_allergen_badge($allergen).' ';
		}
	}

	return $tags;
}

/**
 * Format price
 * @param float $price
 * @param string $currency_symbol
 * @return string
 */
function catering_format_price($price): string
{
	return app_format_money($price, get_base_currency());
}

/**
 * Calculate profit margin percentage
 * @param float $cost
 * @param float $price
 * @return float
 */
function catering_profit_margin($cost, $price)
{
	if ($price == 0)
	{
		return 0;
	}

	return round((($price - $cost) / $price) * 100, 2);
}

/**
 * Get margin badge HTML
 * @param float $margin percentage
 * @return string
 */
function catering_margin_badge($margin)
{
	if ($margin >= 40)
	{
		$class = 'success';
	} elseif ($margin >= 20)
	{
		$class = 'warning';
	} else
	{
		$class = 'danger';
	}

	return '<span class="label label-'.$class.'">'.$margin.'%</span>';
}

/**
 * Check if user can view costs
 * @return boolean
 */
function catering_can_view_costs()
{
	return staff_can('view', 'catering_view_costs') || is_admin();
}

/**
 * Get category color style
 * @param string $color hex color
 * @return string
 */
function catering_category_color($color)
{
	if ( ! $color)
	{
		return '';
	}

	return 'style="background-color: '.htmlspecialchars($color).'; color: #fff;"';
}

/**
 * Get category icon
 * @param string $icon icon class
 * @return string
 */
function catering_category_icon($icon)
{
	if ( ! $icon)
	{
		return '<i class="fa fa-cutlery"></i>';
	}

	return '<i class="'.htmlspecialchars($icon).'"></i>';
}

/**
 * Build hierarchical category select options
 * @param array $categories
 * @param mixed $selected
 * @param int $level
 * @return string
 */
function catering_category_options($categories, $selected = NULL, $level = 0)
{
	$options = '';
	$prefix = str_repeat('&nbsp;&nbsp;&nbsp;', $level);

	foreach ($categories as $category)
	{
		$selected_attr = ($selected == $category['id']) ? 'selected' : '';
		$options .= '<option value="'.$category['id'].'" '.$selected_attr.'>'.
			$prefix.htmlspecialchars($category['name']).
			'</option>';

		if (isset($category['children']))
		{
			$options .= catering_category_options($category['children'], $selected, $level + 1);
		}
	}

	return $options;
}

/**
 * Get allergen severity text
 * @param string $severity
 * @return string
 */
function catering_allergen_severity_text($severity)
{
	$severities = [
		'mild' => _l('allergen_severity_mild'),
		'moderate' => _l('allergen_severity_moderate'),
		'severe' => _l('allergen_severity_severe'),
	];

	return $severities[$severity] ?? $severity;
}

/**
 * Generate allergen summary for items
 * @param array $items
 * @return array grouped allergens
 */
function catering_generate_allergen_summary($items)
{
	$allergens = [];

	foreach ($items as $item)
	{
		if ( ! empty($item['allergens']))
		{
			foreach ($item['allergens'] as $allergen)
			{
				$code = $allergen['code'];
				if ( ! isset($allergens[$code]))
				{
					$allergens[$code] = [
						'allergen' => $allergen,
						'items' => [],
					];
				}
				$allergens[$code]['items'][] = $item['item_name'];
			}
		}
	}

	// Sort by severity (severe first)
	uasort($allergens, function ($a, $b) {
		$severity_order = ['severe' => 1, 'moderate' => 2, 'mild' => 3];
		$a_order = $severity_order[$a['allergen']['severity']] ?? 4;
		$b_order = $severity_order[$b['allergen']['severity']] ?? 4;

		return $a_order - $b_order;
	});

	return $allergens;
}

/**
 * Generate dietary summary for items
 * @param array $items
 * @return array grouped dietary types
 */
function catering_generate_dietary_summary($items)
{
	$dietary = [];

	foreach ($items as $item)
	{
		if ( ! empty($item['dietary_types']))
		{
			foreach ($item['dietary_types'] as $dt)
			{
				$code = $dt['code'];
				if ( ! isset($dietary[$code]))
				{
					$dietary[$code] = [
						'dietary_type' => $dt,
						'items' => [],
					];
				}
				$dietary[$code]['items'][] = $item['item_name'];
			}
		}
	}

	return $dietary;
}

/**
 * Get active status badge
 * @param int $active
 * @return string
 */
function catering_active_badge($active)
{
	if ($active)
	{
		return '<span class="label label-success">'._l('active').'</span>';
	}

	return '<span class="label label-default">'._l('inactive').'</span>';
}

/**
 * Get sortable handle HTML
 * @return string
 */
function catering_sortable_handle()
{
	return '<i class="fa fa-reorder cursor-pointer text-muted sortable-handle"></i>';
}

/**
 * Calculate total cost for menu items
 * @param array $items
 * @param int $guest_count
 * @return float
 */
function catering_calculate_total_cost($items, $guest_count = 1)
{
	$total = 0;

	foreach ($items as $item)
	{
		$portion = $item['portion_per_guest'] ?? $item['qty_per_guest'] ?? 1;
		$cost = $item['unit_cost'] ?? 0;
		$total += $cost * $portion * $guest_count;
	}

	return round($total, 2);
}

/**
 * Calculate total price for menu items
 * @param array $items
 * @param int $guest_count
 * @return float
 */
function catering_calculate_total_price($items, $guest_count = 1)
{
	$total = 0;

	foreach ($items as $item)
	{
		$portion = $item['portion_per_guest'] ?? $item['qty_per_guest'] ?? 1;
		$price = $item['unit_price'] ?? 0;
		$total += $price * $portion * $guest_count;
	}

	return round($total, 2);
}

/**
 * Get menu item version badge
 * @param int $version
 * @return string
 */
function catering_version_badge($version)
{
	return '<span class="label label-default">v'.$version.'</span>';
}

/**
 * Check if menu item has allergens
 * @param object|array $item
 * @return boolean
 */
function catering_has_allergens($item)
{
	$allergens = is_object($item) ? $item->allergens : ($item['allergens'] ?? []);

	return ! empty($allergens);
}

/**
 * Check if menu item is suitable for dietary type
 * @param object|array $item
 * @param string $dietary_code
 * @return boolean
 */
function catering_is_suitable_for_diet($item, $dietary_code)
{
	$dietary_types = is_object($item) ? $item->dietary_types : ($item['dietary_types'] ?? []);

	foreach ($dietary_types as $dt)
	{
		$code = is_array($dt) ? $dt['code'] : $dt->code;
		if ($code === $dietary_code)
		{
			return TRUE;
		}
	}

	return FALSE;
}

/**
 * Get price range for package
 * @param object $package
 * @return string
 */
function catering_package_price_range($package)
{
	$min_price = catering_format_price($package->price_per_person * $package->min_guests);
	$max_price = $package->max_guests ?
		catering_format_price($package->price_per_person * $package->max_guests) :
		_l('unlimited');

	return $min_price.' - '.$max_price;
}

/**
 * Get guest count range for package
 * @param object $package
 * @return string
 */
function catering_package_guest_range($package)
{
	$min = $package->min_guests;
	$max = $package->max_guests ?: _l('unlimited');

	return $min.' - '.$max.' '._l('guests');
}

/**
 * Validate menu item data
 * @param array $data
 * @return array errors
 */
function catering_validate_menu_item($data)
{
	$errors = [];

	if (empty($data['item_name']))
	{
		$errors[] = _l('field_is_required', _l('item_name'));
	}

	if (empty($data['category_id']))
	{
		$errors[] = _l('field_is_required', _l('category'));
	}

	if ( ! isset($data['unit_cost']) || $data['unit_cost'] < 0)
	{
		$errors[] = _l('invalid_field', _l('unit_cost'));
	}

	if ( ! isset($data['unit_price']) || $data['unit_price'] < 0)
	{
		$errors[] = _l('invalid_field', _l('unit_price'));
	}

	return $errors;
}

/**
 * Get portion size display
 * @param string $portion_size
 * @return string
 */
function catering_portion_display($portion_size)
{
	if ( ! $portion_size)
	{
		return _l('per_person');
	}

	return htmlspecialchars($portion_size);
}

/**
 * Get prep time display
 * @param int $minutes
 * @return string
 */
function catering_prep_time_display($minutes)
{
	if ( ! $minutes)
	{
		return '-';
	}

	if ($minutes < 60)
	{
		return $minutes.' '._l('minutes');
	}

	$hours = floor($minutes / 60);
	$mins = $minutes % 60;

	if ($mins > 0)
	{
		return $hours.'h '.$mins.'m';
	}

	return $hours.' '._l('hours');
}

/**
 * Generate unique hash for public links
 * @return string
 */
function catering_generate_hash()
{
	return md5(uniqid(rand(), TRUE));
}

/**
 * Format quantity with unit
 * @param float $qty
 * @param string $unit
 * @return string
 */
function catering_format_quantity($qty, $unit)
{
	$formatted_qty = number_format($qty, 3);
	$formatted_qty = rtrim(rtrim($formatted_qty, '0'), '.');

	return $formatted_qty.' '.htmlspecialchars($unit);
}

/**
 * Get item cost breakdown
 * @param object $item
 * @return array
 */
function catering_item_cost_breakdown($item)
{
	$breakdown = [
		'ingredients' => 0,
		'items' => [],
	];

	if ( ! empty($item->ingredients))
	{
		foreach ($item->ingredients as $ing)
		{
			$cost = $ing['qty_per_portion'] * $ing['avg_cost_per_unit'];
			$breakdown['ingredients'] += $cost;
			$breakdown['items'][] = [
				'name' => $ing['name'],
				'quantity' => $ing['qty_per_portion'],
				'unit' => $ing['unit'],
				'cost' => $cost,
			];
		}
	}

	return $breakdown;
}

/**
 * Check if menu has items
 * @param object $menu
 * @return boolean
 */
function catering_menu_has_items($menu)
{
	return ! empty($menu->items) && count($menu->items) > 0;
}

/**
 * Get icon for dietary type code
 * @param string $code
 * @return string
 */
function catering_dietary_icon($code)
{
	$icons = [
		'vegan' => 'fa fa-leaf',
		'vegetarian' => 'fa fa-envira',
		'gluten_free' => 'fa fa-wheat',
		'halal' => 'fa fa-certificate',
		'kosher' => 'fa fa-star-o',
		'nut_free' => 'fa fa-ban',
		'dairy_free' => 'fa fa-times-circle',
		'low_carb' => 'fa fa-line-chart',
		'keto' => 'fa fa-fire',
	];

	return $icons[$code] ?? 'fa fa-check';
}

/**
 * Get icon for allergen code
 * @param string $code
 * @return string
 */
function catering_allergen_icon($code)
{
	$icons = [
		'nuts' => 'fa fa-warning',
		'gluten' => 'fa fa-exclamation-triangle',
		'milk' => 'fa fa-tint',
		'eggs' => 'fa fa-circle-o',
		'fish' => 'fa fa-anchor',
		'shellfish' => 'fa fa-dot-circle-o',
		'soy' => 'fa fa-leaf',
		'sesame' => 'fa fa-circle',
	];

	return $icons[$code] ?? 'fa fa-exclamation';
}

/**
 * Get catering permission label
 * @param string $permission
 * @return string
 */
function catering_permission_label($permission)
{
	$labels = [
		'catering_view' => _l('view_catering'),
		'catering_create' => _l('create_catering'),
		'catering_edit' => _l('edit_catering'),
		'catering_delete' => _l('delete_catering'),
		'catering_view_costs' => _l('view_costs_and_margins'),
		'catering_manage_settings' => _l('manage_catering_settings'),
	];

	return $labels[$permission] ?? $permission;
}

/**
 * Sanitize menu item data for JSON storage
 * @param array $item
 * @return array
 */
function catering_sanitize_item_for_snapshot($item)
{
	return [
		'item_id' => $item['id'] ?? NULL,
		'item_name' => $item['item_name'] ?? $item['name'] ?? '',
		'description' => $item['description'] ?? '',
		'unit_cost' => $item['unit_cost'] ?? 0,
		'unit_price' => $item['unit_price'] ?? 0,
		'version' => $item['version'] ?? 1,
	];
}

/**
 * Get table data config for datatables
 * @param string $table_name
 * @return array
 */
function catering_get_table_config($table_name)
{
	$configs = [
		'catering_allergens' => [
			'columns' => ['label', 'code', 'severity', 'display_order', 'active'],
			'sortable' => ['label', 'severity', 'display_order'],
			'searchable' => ['label', 'code', 'description'],
		],
		'catering_dietary_types' => [
			'columns' => ['label', 'code', 'display_order', 'active'],
			'sortable' => ['label', 'display_order'],
			'searchable' => ['label', 'code', 'description'],
		],
		'catering_menu_categories' => [
			'columns' => ['name', 'parent_id', 'display_order', 'active'],
			'sortable' => ['name', 'display_order'],
			'searchable' => ['name'],
		],
		'catering_menu_items' => [
			'columns' => ['item_name', 'category_id', 'unit_cost', 'unit_price', 'active'],
			'sortable' => ['item_name', 'unit_cost', 'unit_price'],
			'searchable' => ['item_name', 'description'],
		],
		'catering_menus' => [
			'columns' => ['menu_name', 'base_price_per_person', 'active'],
			'sortable' => ['menu_name', 'base_price_per_person'],
			'searchable' => ['menu_name', 'description'],
		],
		'catering_packages' => [
			'columns' => ['package_name', 'price_per_person', 'min_guests', 'max_guests', 'active'],
			'sortable' => ['package_name', 'price_per_person', 'min_guests'],
			'searchable' => ['package_name', 'description'],
		],
	];

	return $configs[$table_name] ?? [];
}

function catering_event_get_document_download_path($event_id, $file_name)
{
	return get_upload_path_by_type('catering_event').$event_id.'/'.$file_name;
}
