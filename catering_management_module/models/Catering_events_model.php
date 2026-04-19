<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Catering_events_model extends App_Model {
	private array $statuses = [
		'enquiry',
		'quoted',
		'confirmed',
		'in_progress',
		'completed',
		'cancelled',
		'lost',
	];

	private array $statuses_display = [
		'enquiry' => [
			'id' => 'enquiry',
			'label' => 'Enquiry',
			'background-color' => 'red',
			'text-color' => 'white',
		],
		'quoted' => [
			'id' => 'quoted',
			'label' => 'Quoted',
			'background-color' => 'green',
			'text-color' => 'white',
		],
		'confirmed' => [
			'id' => 'confirmed',
			'label' => 'Confirmed',
			'background-color' => 'green',
			'text-color' => 'white',
		],
		'in_progress' => [
			'id' => 'in_progress',
			'label' => 'In Progress',
			'background-color' => 'red',
			'text-color' => 'white',
		],
		'completed' => [
			'id' => 'completed',
			'label' => 'Completed',
			'background-color' => 'red',
			'text-color' => 'white',
		],
		'cancelled' => [
			'id' => 'cancelled',
			'label' => 'Cancelled',
			'background-color' => 'red',
			'text-color' => 'white',
		],
		'lost' => [
			'id' => 'lost',
			'label' => 'Lost',
			'background-color' => 'red',
			'text-color' => 'white',
		],
	];

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get event by ID
	 * @param int $id Event ID
	 * @return object|null
	 */
	public function get($id)
	{
		$this->db->select(
			db_prefix().'catering_events.*,'
			.db_prefix().'clients.company as client_company,'
			.db_prefix().'leads.name as lead_name,'.
			db_prefix().'catering_event_types.name as event_type_name,'.
			db_prefix().'projects.name as project_name,
            CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as created_by_name'
		);
		$this->db->from(db_prefix().'catering_events');
		$this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id', 'left');
		$this->db->join(db_prefix().'leads', db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id', 'left');
		$this->db->join(db_prefix().'catering_event_types', db_prefix().'catering_event_types.etid = '.db_prefix().'catering_events.event_type_id', 'left');
		$this->db->join(db_prefix().'projects', db_prefix().'projects.id = '.db_prefix().'catering_events.project_id', 'left');
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'catering_events.created_by', 'left');
		$this->db->where('eventid', $id);

		$event = $this->db->get()->row();

		if ($event)
		{
			$event->attachments = $this->get_attachments($id);
		}

		return $event;
	}

	/**
	 * Get all events with filtering and sorting
	 * @param array $where Additional where conditions
	 * @return array
	 */
	public function get_all($where = [])
	{
		$this->db->select(
			db_prefix().'catering_events.*, 
            '.db_prefix().'clients.company as client_company,'.
			db_prefix().'leads.name as lead_name,'.
			db_prefix().'catering_event_types.name as event_type_name,
            CONCAT('.db_prefix().'staff.firstname, " ", '.db_prefix().'staff.lastname) as created_by_name'
		);
		$this->db->from(db_prefix().'catering_events');
		$this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id', 'left');
		$this->db->join(db_prefix().'leads', db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id', 'left');
		$this->db->join(db_prefix().'catering_event_types', db_prefix().'catering_event_types.etid = '.db_prefix().'catering_events.event_type_id', 'left');
		$this->db->join(db_prefix().'staff', db_prefix().'staff.staffid = '.db_prefix().'catering_events.created_by', 'left');

		if ( ! empty($where))
		{
			$this->db->where($where);
		}

		$this->db->order_by('event_start', 'DESC');

		return $this->db->get()->result_array();
	}

	/**
	 * Get events for Kanban view grouped by status
	 * @return array
	 */
	public function get_kanban_data()
	{
		$kanban = [];

		foreach ($this->statuses as $status)
		{
			$this->db->select(
				db_prefix().'catering_events.*,'.
				db_prefix().'clients.company as client_company,'.
				db_prefix().'leads.name as lead_name'
			);
			$this->db->from(db_prefix().'catering_events');
			$this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id', 'left');
			$this->db->join(db_prefix().'leads', db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id', 'left');
			$this->db->where(db_prefix().'catering_events.status', $status);
			$this->db->order_by('event_start', 'ASC');

			$kanban[$status] = $this->db->get()->result_array();
		}

		return $kanban;
	}

	/**
	 * Get events for timeline view
	 * @return array
	 */
	public function get_timeline_data($start_date = NULL, $end_date = NULL)
	{
		$this->db->select(
			db_prefix().'catering_events.*,'.
			db_prefix().'clients.company as client_company,'.
			db_prefix().'leads.name as lead_name,'.
			db_prefix().'catering_event_types.name as event_type_name'
		);
		$this->db->from(db_prefix().'catering_events');
		$this->db->join(db_prefix().'clients', db_prefix().'clients.userid = '.db_prefix().'catering_events.client_id', 'left');
		$this->db->join(db_prefix().'leads', db_prefix().'leads.id = '.db_prefix().'catering_events.lead_id', 'left');
		$this->db->join(db_prefix().'catering_event_types', db_prefix().'catering_event_types.etid = '.db_prefix().'catering_events.event_type_id', 'left');

		if ($start_date)
		{
			$this->db->where('event_start >=', $start_date);
		}
		if ($end_date)
		{
			$this->db->where('event_start <=', $end_date);
		}

		$this->db->where_in(db_prefix().'catering_events.status', ['confirmed', 'in_progress', 'completed']);
		$this->db->order_by('event_start', 'ASC');

		return $this->db->get()->result_array();
	}

	/**
	 * Add new event
	 * @param array $data Event data
	 * @return int|bool Event ID or false
	 */
	public function add($data)
	{
		$data['hash'] = app_generate_hash();
		$data['created_by'] = get_staff_user_id();
		$data['created_at'] = date('Y-m-d H:i:s');

		$create_project = boolval($data['create_project'] ?? 0);
		unset($data['create_project']);
//		cmm_debug_var($data);

		$this->db->insert(db_prefix().'catering_events', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Log activity
			log_activity('New Catering Event Created [ID: '.$insert_id.', Name: '.$data['event_name'].']');

			// Create project if requested
			if ($create_project)
			{
				$this->create_linked_project($insert_id);
			}

			return $insert_id;
		}

		return FALSE;
	}

	/**
	 * Update event
	 * @param int $id Event ID
	 * @param array $data Event data
	 * @return bool
	 */
	public function update($id, $data)
	{
		$this->db->where('eventid', $id);
		$this->db->update(db_prefix().'catering_events', $data);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Catering Event Updated [ID: '.$id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Delete event
	 * @param int $id Event ID
	 * @return bool
	 */
	public function delete($id)
	{
		$event = $this->get($id);

		if ( ! $event)
		{
			return FALSE;
		}

		try
		{
			$this->db->trans_begin();
			$this->db->where('event_id', $id);
			$this->db->delete(db_prefix().'catering_event_menu_items');

			$this->db->where('eventid', $id);
			$this->db->delete(db_prefix().'catering_events');

			$this->db->trans_commit();
			$this->delete_event_attachments($id);

			log_activity('Catering Event Deleted [ID: '.$id.', Name: '.$event->event_name.']');

			return TRUE;
		} catch (Exception $exception)
		{
			$this->db->trans_rollback();

			return FALSE;
		}
	}

	/**
	 * Change event status
	 * @param int $id Event ID
	 * @param string $status New status
	 * @return bool
	 */
	public function change_status($id, $status)
	{
		if ( ! in_array($status, $this->statuses))
		{
			return FALSE;
		}

		$this->db->where('eventid', $id);
		$this->db->update(db_prefix().'catering_events', [db_prefix().'catering_events.status' => $status]);

		if ($this->db->affected_rows() > 0)
		{
			log_activity('Catering Event Status Changed [ID: '.$id.', Status: '.$status.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get event attachments
	 * @param int $id Event ID
	 * @return array
	 */
	public function get_attachments($id)
	{
		$this->db->where('rel_id', $id);
		$this->db->where('rel_type', 'catering_event');

		return $this->db->get(db_prefix().'files')->result_array();
	}

	/**
	 * Delete event attachments
	 * @param int $id Event ID
	 * @return void
	 */
	private function delete_event_attachments($id)
	{
		$attachments = $this->get_attachments($id);

		foreach ($attachments as $attachment)
		{
			delete_file(get_upload_path_by_type('catering_event').$id.'/'.$attachment['file_name']);
		}

		$this->db->where('rel_id', $id);
		$this->db->where('rel_type', 'catering_event');
		$this->db->delete(db_prefix().'files');
	}

	/**
	 * Create linked project for event
	 * @param int $event_id Event ID
	 * @return int|bool Project ID or false
	 */
	private function create_linked_project($event_id): bool|int
	{
		$event = $this->get($event_id);

		if ( ! $event)
		{
			return FALSE;
		}

		$this->load->model('projects_model');

		$project_data = [
			'name' => $event->event_name,
			'description' => 'Catering Event: '.$event->event_name,
			'clientid' => $event->client_id,
			'start_date' => date('Y-m-d', strtotime($event->event_start)),
			'deadline' => $event->event_end ? date('Y-m-d', strtotime($event->event_end)) : NULL,
			'status' => 2, // In Progress
		];

		$project_id = $this->projects_model->add($project_data);

		if ($project_id)
		{
			$this->update($event_id, ['project_id' => $project_id]);

			return $project_id;
		}

		return FALSE;
	}

	/**
	 * Get statuses list
	 * @return array
	 */
	public function get_statuses()
	{
		return $this->statuses;
	}

	/**
	 * @return array
	 */
	public function get_statuses_for_display()
	{
		return $this->statuses_display;
	}

	/**
	 * Get event statistics
	 * @return array
	 */
	public function get_statistics()
	{
		$stats = [];

		foreach ($this->statuses as $status)
		{
			$this->db->where(db_prefix().'catering_events.status', $status);
			$this->db->from(db_prefix().'catering_events');
			$stats[$status] = $this->db->count_all_results();
		}

		return $stats;
	}

	/**
	 * @param $data
	 * @return void
	 */
	public function update_order($data): void
	{
		Events_kanban::updateOrder($data['order'], 'kanban_order', 'catering_events', $data['status'], 'status', 'eventid');
	}

	/**
	 * Generate estimate from event
	 * @param int $event_id Event ID
	 * @return int|bool Estimate ID or false on failure
	 */
	public function generate_estimate($event_id)
	{
		$event = $this->get($event_id);

		if ( ! $event)
		{
			return FALSE;
		}

		// Check if event has client (estimates require client, not lead)
		if ( ! $event->client_id)
		{
			return FALSE;
		}

		// Load required models
		$this->load->model('estimates_model');
		$this->load->model('catering_management_module/catering_event_menu_model');
		$this->load->model('catering_management_module/catering_event_staff_model');

		// Get event menu and items
		$event_menu = $this->catering_event_menu_model->get_event_menu($event_id);
		$menu_items = $event_menu ? $event_menu->items : [];

		// Get staff assignments
		$staff_assignments = $this->catering_event_staff_model->get_event_staff($event_id);

		// Generate estimate note/content
		$estimate_notes = $this->_generate_estimate_notes($event, $event_menu, $menu_items, $staff_assignments);

		// Prepare estimate data
		$estimate_data = [
			'clientid' => $event->client_id,
			'number' => get_option('next_estimate_number'),
			'date' => date('Y-m-d'),
			'expirydate' => date('Y-m-d', strtotime($event->event_start.' -7 days')), // 7 days before event
			'currency' => get_base_currency()->id,
			'status' => 1, // Draft status
			'adminnote' => $estimate_notes,
			'addedfrom' => get_staff_user_id(),
		];

		// Prepare estimate items from menu items
		$estimate_items = [];
		$subtotal = 0;

		if ($event_menu && ! empty($menu_items))
		{
			$guest_count = $event->guest_count_expected ?: 1;

			foreach ($menu_items as $section)
			{
				// Add section header as item
				$estimate_items[] = [
					'description' => '',
					'long_description' => '<strong>'._l('section').': '.$section['section_name'].'</strong>',
					'qty' => 0,
					'rate' => 0,
					'unit' => '',
					'order' => count($estimate_items) + 1,
				];

				foreach ($section['items'] as $item)
				{
					$unit_price = $item['unit_price'] ?: 0;
					$quantity = ($event_menu->pricing_mode === 'per_person') ? $guest_count : 1;
					$item_total = $unit_price * $quantity;

					$description = $item['item_name'];
					if ($item['description'])
					{
						$description .= "\n".$item['description'];
					}

					$estimate_items[] = [
						'description' => $description,
						'long_description' => $item['description'] ?: '',
						'qty' => $quantity,
						'rate' => $unit_price,
						'unit' => ($event_menu->pricing_mode === 'per_person') ? 'per person' : 'item',
						'order' => count($estimate_items) + 1,
					];

					$subtotal += $item_total;
				}
			}
		}

		// Add labor costs as estimate items
		if ( ! empty($staff_assignments))
		{
			// Add labor section header
			$estimate_items[] = [
				'description' => '',
				'long_description' => '<strong>Labor & Staffing</strong>',
				'qty' => 0,
				'rate' => 0,
				'unit' => '',
				'order' => count($estimate_items) + 1,
			];

			foreach ($staff_assignments as $staff)
			{
				$hours = $staff['hours'] ?: 0;
				$rate = $staff['hourly_rate'] ?: 0;
				$labor_cost = $hours * $rate;

				$description = $staff['staff_name'].' ('.$staff['role'].')';
				$long_description = 'Shift: '._dt($staff['shift_start']).' - '._dt($staff['shift_end']);

				$estimate_items[] = [
					'description' => $description,
					'long_description' => $long_description,
					'qty' => $hours,
					'rate' => $rate,
					'unit' => 'hour',
					'order' => count($estimate_items) + 1,
				];

				$subtotal += $labor_cost;
			}
		}

		$estimate_data['subtotal'] = $subtotal;
		$estimate_data['total'] = $subtotal;
		$estimate_data['total_tax'] = 0;
		$estimate_data['newitems'] = $estimate_items;

		// Create the estimate
		$estimate_id = $this->estimates_model->add($estimate_data);

		if ($estimate_id)
		{
			// Update event with estimate_id
			$this->db->where('eventid', $event_id);
			$this->db->update(db_prefix().'catering_events', ['estimate_id' => $estimate_id]);

			// Log activity
			log_activity('Estimate Generated from Event [Event ID: '.$event_id.', Estimate ID: '.$estimate_id.']');

			return $estimate_id;
		}

		return FALSE;
	}

	/**
	 * Generate estimate notes/content
	 * @param object $event
	 * @param object|null $event_menu
	 * @param array $menu_items
	 * @param array $staff_assignments
	 * @return string
	 */
	private function _generate_estimate_notes($event, $event_menu, $menu_items, $staff_assignments)
	{
		$content = '<div style="font-family: Arial, sans-serif; line-height: 1.6;">';

		// Event Overview
		$content .= '<h2 style="color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px;">Event Overview</h2>';
		$content .= '<table style="width: 100%; margin-bottom: 20px;">';
		$content .= '<tr><td style="padding: 5px; width: 150px;"><strong>Event Name:</strong></td><td style="padding: 5px;">'.htmlspecialchars($event->event_name).'</td></tr>';

		if ($event->event_type_name)
		{
			$content .= '<tr><td style="padding: 5px;"><strong>Event Type:</strong></td><td style="padding: 5px;">'.htmlspecialchars($event->event_type_name).'</td></tr>';
		}

		$content .= '<tr><td style="padding: 5px;"><strong>Date:</strong></td><td style="padding: 5px;">'._dt($event->event_start);
		if ($event->event_end)
		{
			$content .= ' - '._dt($event->event_end);
		}
		$content .= '</td></tr>';

		if ($event->venue_name)
		{
			$content .= '<tr><td style="padding: 5px;"><strong>Venue:</strong></td><td style="padding: 5px;">'.htmlspecialchars($event->venue_name).'</td></tr>';
		}

		if ($event->venue_address)
		{
			$content .= '<tr><td style="padding: 5px;"><strong>Address:</strong></td><td style="padding: 5px;">'.nl2br(htmlspecialchars($event->venue_address)).'</td></tr>';
		}

		if ($event->guest_count_expected)
		{
			$content .= '<tr><td style="padding: 5px;"><strong>Expected Guests:</strong></td><td style="padding: 5px;">'.$event->guest_count_expected.'</td></tr>';
		}

		$content .= '</table>';

		// Menu Details
		if ($event_menu && ! empty($menu_items))
		{
			$content .= '<h2 style="color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; margin-top: 30px;">Menu Details</h2>';

			if ($event_menu->menu_name)
			{
				$content .= '<p><strong>Selected Menu:</strong> '.htmlspecialchars($event_menu->menu_name).'</p>';
			}

			if ($event_menu->package_name)
			{
				$content .= '<p><strong>Package:</strong> '.htmlspecialchars($event_menu->package_name).'</p>';
			}

			foreach ($menu_items as $section)
			{
				$content .= '<h3 style="color: #555; margin-top: 20px;">'.htmlspecialchars($section['section_name']).'</h3>';
				$content .= '<ul style="list-style-type: disc; margin-left: 20px;">';

				foreach ($section['items'] as $item)
				{
					$content .= '<li style="margin-bottom: 8px;">';
					$content .= '<strong>'.htmlspecialchars($item['item_name']).'</strong>';

					if ($item['description'])
					{
						$content .= '<br><span style="color: #666; font-size: 0.9em;">'.htmlspecialchars($item['description']).'</span>';
					}

					if ($item['portion_per_guest'])
					{
						$content .= '<br><span style="color: #888; font-size: 0.85em;">Portion: '.htmlspecialchars($item['portion_per_guest']).'</span>';
					}

					$content .= '</li>';
				}

				$content .= '</ul>';
			}
		}

		// Dietary Information
		if ($event_menu && ( ! empty($event_menu->dietary_summary) || ! empty($event_menu->allergen_summary)))
		{
			$content .= '<h2 style="color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; margin-top: 30px;">Dietary Information</h2>';

			if ( ! empty($event_menu->dietary_summary))
			{
				$content .= '<p><strong>Dietary Options:</strong></p>';
				$content .= '<ul style="list-style-type: disc; margin-left: 20px;">';
				foreach ($event_menu->dietary_summary as $dietary)
				{
					$content .= '<li>'.htmlspecialchars($dietary['label']).' ('.$dietary['item_count'].' items)</li>';
				}
				$content .= '</ul>';
			}

			if ( ! empty($event_menu->allergen_summary))
			{
				$content .= '<p><strong>Allergen Information:</strong></p>';
				$content .= '<ul style="list-style-type: disc; margin-left: 20px;">';
				foreach ($event_menu->allergen_summary as $allergen)
				{
					$content .= '<li>'.htmlspecialchars($allergen['label']).' - '.ucfirst($allergen['severity']).' ('.$allergen['item_count'].' items)</li>';
				}
				$content .= '</ul>';
			}

			if ($event->dietary_notes)
			{
				$content .= '<p><strong>Special Dietary Notes:</strong><br>'.nl2br(htmlspecialchars($event->dietary_notes)).'</p>';
			}
		}

		// Staffing
		if ( ! empty($staff_assignments))
		{
			$content .= '<h2 style="color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; margin-top: 30px;">Staffing Requirements</h2>';
			$content .= '<table style="width: 100%; border-collapse: collapse;">';
			$content .= '<thead><tr style="background-color: #f5f5f5;">';
			$content .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Staff Member</th>';
			$content .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Role</th>';
			$content .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Shift Time</th>';
			$content .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Hours</th>';
			$content .= '</tr></thead><tbody>';

			foreach ($staff_assignments as $staff)
			{
				$content .= '<tr>';
				$content .= '<td style="padding: 8px; border: 1px solid #ddd;">'.htmlspecialchars($staff['staff_name']).'</td>';
				$content .= '<td style="padding: 8px; border: 1px solid #ddd;">'.htmlspecialchars($staff['role']).'</td>';
				$content .= '<td style="padding: 8px; border: 1px solid #ddd;">'._dt($staff['shift_start']).' - '._dt($staff['shift_end']).'</td>';
				$content .= '<td style="padding: 8px; border: 1px solid #ddd; text-align: right;">'.number_format($staff['hours'], 2).'</td>';
				$content .= '</tr>';
			}

			$content .= '</tbody></table>';
		}

		// Terms & Conditions
		$content .= '<h2 style="color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; margin-top: 30px;">Terms & Conditions</h2>';
		$content .= '<ul style="list-style-type: disc; margin-left: 20px; line-height: 1.8;">';
		$content .= '<li>Final guest count must be confirmed 48 hours before the event.</li>';
		$content .= '<li>A deposit of 30% is required to confirm the booking.</li>';
		$content .= '<li>Balance payment is due 7 days before the event date.</li>';
		$content .= '<li>Cancellations made less than 14 days before the event are subject to a cancellation fee.</li>';
		$content .= '<li>We will accommodate reasonable dietary requirements with advance notice.</li>';
		$content .= '<li>Setup and breakdown time is included in staff hours.</li>';
		$content .= '</ul>';

		$content .= '</div>';

		return $content;
	}

	/**
	 * Regenerate estimate from event (update existing estimate with new data)
	 * @param int $event_id Event ID
	 * @return bool Success or failure
	 */
	public function regenerate_estimate($event_id)
	{
		$event = $this->get($event_id);

		if ( ! $event || ! $event->estimate_id)
		{
			return FALSE;
		}

		// Load required models
		$this->load->model('estimates_model');
		$this->load->model('catering_management_module/catering_event_menu_model');
		$this->load->model('catering_management_module/catering_event_staff_model');

		// Get event menu and items
		$event_menu = $this->catering_event_menu_model->get_event_menu($event_id);
		$menu_items = $event_menu ? $event_menu->items : [];

		// Get staff assignments
		$staff_assignments = $this->catering_event_staff_model->get_event_staff($event_id);

		// Generate estimate note/content
		$estimate_notes = $this->_generate_estimate_notes($event, $event_menu, $menu_items, $staff_assignments);

		// Prepare estimate items from menu items
		$estimate_items = [];
		$subtotal = 0;

		if ($event_menu && ! empty($menu_items))
		{
			$guest_count = $event->guest_count_expected ?: 1;

			foreach ($menu_items as $section)
			{
				// Add section header as item
				$estimate_items[] = [
					'description' => '',
					'long_description' => '<strong>'._l('section').': '.$section['section_name'].'</strong>',
					'qty' => 0,
					'rate' => 0,
					'unit' => '',
					'order' => count($estimate_items) + 1,
				];

				foreach ($section['items'] as $item)
				{
					$unit_price = $item['unit_price'] ?: 0;
					$quantity = ($event_menu->pricing_mode === 'per_person') ? $guest_count : 1;
					$item_total = $unit_price * $quantity;

					$description = $item['item_name'];
					if ($item['description'])
					{
						$description .= "\n".$item['description'];
					}

					$estimate_items[] = [
						'description' => $description,
						'long_description' => $item['description'] ?: '',
						'qty' => $quantity,
						'rate' => $unit_price,
						'unit' => ($event_menu->pricing_mode === 'per_person') ? 'per person' : 'item',
						'order' => count($estimate_items) + 1,
					];

					$subtotal += $item_total;
				}
			}
		}

		// Add labor costs as estimate items
		if ( ! empty($staff_assignments))
		{
			// Add labor section header
			$estimate_items[] = [
				'description' => '',
				'long_description' => '<strong>Labor & Staffing</strong>',
				'qty' => 0,
				'rate' => 0,
				'unit' => '',
				'order' => count($estimate_items) + 1,
			];

			foreach ($staff_assignments as $staff)
			{
				$hours = $staff['hours'] ?: 0;
				$rate = $staff['hourly_rate'] ?: 0;
				$labor_cost = $hours * $rate;

				$description = $staff['staff_name'].' ('.$staff['role'].')';
				$long_description = 'Shift: '._dt($staff['shift_start']).' - '._dt($staff['shift_end']);

				$estimate_items[] = [
					'description' => $description,
					'long_description' => $long_description,
					'qty' => $hours,
					'rate' => $rate,
					'unit' => 'hour',
					'order' => count($estimate_items) + 1,
				];

				$subtotal += $labor_cost;
			}
		}

		// Update estimate basic fields directly
		$this->db->where('id', $event->estimate_id);
		$this->db->update(db_prefix().'estimates', [
			'expirydate' => date('Y-m-d', strtotime($event->event_start.' -7 days')),
			'adminnote' => $estimate_notes,
			'subtotal' => $subtotal,
			'total' => $subtotal,
			'total_tax' => 0,
		]);

		// Delete old items first
		$this->db->where('rel_id', $event->estimate_id);
		$this->db->where('rel_type', 'estimate');
		$this->db->delete(db_prefix().'itemable');

		// Delete old item taxes
		$this->db->where('rel_id', $event->estimate_id);
		$this->db->where('rel_type', 'estimate');
		$this->db->delete(db_prefix().'item_tax');

		// Now add new items using the model's item handling
		if ( ! empty($estimate_items))
		{
			foreach ($estimate_items as $item)
			{
				$item_data = [
					'description' => $item['description'],
					'long_description' => $item['long_description'],
					'qty' => $item['qty'],
					'rate' => $item['rate'],
					'unit' => $item['unit'],
					'item_order' => $item['order'],
				];

				$this->db->insert(db_prefix().'itemable', [
					'rel_id' => $event->estimate_id,
					'rel_type' => 'estimate',
					'description' => $item_data['description'],
					'long_description' => $item_data['long_description'],
					'qty' => $item_data['qty'],
					'rate' => $item_data['rate'],
					'unit' => $item_data['unit'],
					'item_order' => $item_data['item_order'],
				]);
			}
		}

		// Log activity
		log_activity('Estimate Regenerated from Event [Event ID: '.$event_id.', Estimate ID: '.$event->estimate_id.']');

		return TRUE;
	}

	/**
	 * Unlink estimate from event (does not delete the estimate)
	 * @param int $event_id Event ID
	 * @return bool Success or failure
	 */
	public function unlink_estimate($event_id)
	{
		$event = $this->get($event_id);

		if ( ! $event || ! $event->estimate_id)
		{
			return FALSE;
		}

		$estimate_id = $event->estimate_id;

		// Remove estimate_id from event
		$this->db->where('eventid', $event_id);
		$this->db->update(db_prefix().'catering_events', ['estimate_id' => NULL]);

		if ($this->db->affected_rows() > 0)
		{
			// Log activity
			log_activity('Estimate Unlinked from Event [Event ID: '.$event_id.', Estimate ID: '.$estimate_id.']');

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get event menu costs
	 * @param int $event_id Event ID
	 * @return array Cost breakdown for menu items
	 */
	public function get_event_menu_costs($event_id)
	{
		$event = $this->get($event_id);
		if ( ! $event)
		{
			return ['total_cost' => 0, 'total_revenue' => 0, 'items' => []];
		}

		$guest_count = $event->guest_count_expected ?: 0;

		$this->db->select(
			'
			cemi.*,
			mi.item_name,
			mi.unit_cost as menu_item_cost
		'
		);
		$this->db->from(db_prefix().'catering_event_menu_items cemi');
		$this->db->join(db_prefix().'catering_menu_items mi', 'mi.id = cemi.item_id');
		$this->db->join(db_prefix().'catering_event_menu cem', 'cem.id = cemi.event_menu_id');
		$this->db->where('cem.event_id', $event_id);

		$items = $this->db->get()->result_array();

		$total_cost = 0;
		$total_revenue = 0;
		$item_breakdown = [];

		foreach ($items as $item)
		{
			$portion = $item['portion_per_guest'];
			$unit_cost = $item['menu_item_cost'];
			$unit_price = $item['unit_price'];

			$item_total_cost = $unit_cost * $portion * $guest_count;
			$item_total_revenue = $unit_price * $portion * $guest_count;

			$total_cost += $item_total_cost;
			$total_revenue += $item_total_revenue;

			$item_breakdown[] = [
				'name' => $item['item_name'],
				'portion' => $portion,
				'unit_cost' => $unit_cost,
				'unit_price' => $unit_price,
				'total_cost' => $item_total_cost,
				'total_revenue' => $item_total_revenue,
			];
		}

		return [
			'total_cost' => round($total_cost, 2),
			'total_revenue' => round($total_revenue, 2),
			'items' => $item_breakdown,
		];
	}

	/**
	 * Get event labor costs
	 * @param int $event_id Event ID
	 * @return array Cost breakdown for staff/labor
	 */
	public function get_event_labor_costs($event_id)
	{
		$this->db->select(
			'
			ces.*,
			CONCAT(s.firstname, " ", s.lastname) as staff_name
		'
		);
		$this->db->from(db_prefix().'catering_event_staff ces');
		$this->db->join(db_prefix().'staff s', 's.staffid = ces.staff_id', 'left');
		$this->db->where('ces.event_id', $event_id);

		$staff = $this->db->get()->result_array();

		$total_cost = 0;
		$staff_breakdown = [];

		foreach ($staff as $member)
		{
			$hours = $member['hours'] ?? 0;
			$rate = $member['hourly_rate'] ?? 0;
			$cost = $hours * $rate;

			$total_cost += $cost;

			$staff_breakdown[] = [
				'name' => $member['staff_name'] ?? '',
				'role' => $member['role'] ?? '',
				'hours' => $hours,
				'rate' => $rate,
				'cost' => $cost,
			];
		}

		return [
			'total_cost' => round($total_cost, 2),
			'total_hours' => array_sum(array_column($staff_breakdown, 'hours')),
			'staff_count' => count($staff),
			'staff' => $staff_breakdown,
		];
	}

	/**
	 * Get event additional expenses
	 * @param int $event_id Event ID
	 * @return array Expense breakdown
	 */
	public function get_event_expenses($event_id)
	{
		// For now, return empty structure
		// This will be implemented when expense tracking is added
		return [
			'total_cost' => 0,
			'expenses' => [],
		];
	}

	/**
	 * Calculate complete event financials
	 * @param int $event_id Event ID
	 * @return array Complete financial breakdown
	 */
	public function calculate_event_financials($event_id)
	{
		$menu_costs = $this->get_event_menu_costs($event_id);
		$labor_costs = $this->get_event_labor_costs($event_id);
		$expenses = $this->get_event_expenses($event_id);

		$total_cost = $menu_costs['total_cost'] + $labor_costs['total_cost'] + $expenses['total_cost'];
		$total_revenue = $menu_costs['total_revenue'];
		$net_profit = $total_revenue - $total_cost;
		$profit_margin = $total_revenue > 0 ? ($net_profit / $total_revenue) * 100 : 0;

		return [
			'menu' => $menu_costs,
			'labor' => $labor_costs,
			'expenses' => $expenses,
			'summary' => [
				'total_cost' => round($total_cost, 2),
				'total_revenue' => round($total_revenue, 2),
				'net_profit' => round($net_profit, 2),
				'profit_margin' => round($profit_margin, 2),
			],
		];
	}

}