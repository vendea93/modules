<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Catering_events_model $catering_events_model
 * @property Clients_model $clients_model
 * @property Staff_model $staff_model
 * @property Catering_event_types_model $catering_event_types_model
 * @property Catering_event_menu_model $catering_event_menu_model
 * @property Catering_menus_model $catering_menus_model
 * @property Catering_packages_model $catering_packages_model
 * @property Catering_menu_items_model $catering_menu_items_model
 * @property Catering_menu_sections_model $catering_menu_sections_model
 * @property Catering_event_staff_model $catering_event_staff_model
 * @property Catering_event_notes_model $catering_event_notes_model
 */
class Events extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_events_model');
		$this->load->model('clients_model');
		$this->load->model('catering_event_types_model');
		$this->load->model('staff_model');
		$this->load->model('catering_event_menu_model');
		$this->load->model('catering_menus_model');
		$this->load->model('catering_packages_model');
		$this->load->model('catering_menu_items_model');
		$this->load->model('catering_menu_sections_model');
		$this->load->model('catering_event_staff_model');
		$this->load->model('catering_event_notes_model');
	}

	/**
	 * Events list view with table/kanban/timeline modes
	 */
	public function index()
	{
		if (staff_cant('view', 'catering_events'))
		{
			access_denied('catering_events');
		}

		// Handle view mode
		$view_mode = $this->input->get('view') ?: 'table';

		$data['view_mode'] = $view_mode;
		$data['title'] = _l('catering_events');
		$data['statuses'] = $this->catering_events_model->get_statuses();
		$data['statuses_display'] = $this->catering_events_model->get_statuses_for_display();
		$data['statistics'] = $this->catering_events_model->get_statistics();

		if ($view_mode == 'kanban')
		{
			$data['kanban_data'] = $this->catering_events_model->get_kanban_data();
		} elseif ($view_mode == 'timeline')
		{
			$data['timeline_data'] = $this->catering_events_model->get_timeline_data();
		}

		add_moment_js_assets();
		add_calendar_assets();
		$this->load->view('admin/events/manage', $data);
	}


	public function table()
	{
		if (staff_cant('view', 'catering_events'))
		{
			ajax_access_denied();
		}
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('catering_management_module', 'admin/tables/events'));
		} else
		{
			redirect(admin_url());
			exit();
		}
	}

	public function events_kanban()
	{
		$data['statuses_display'] = $this->catering_events_model->get_statuses_for_display();

		echo $this->load->view('admin/events/kanban_wrapper', $data, TRUE);
	}

	/**
	 * @return void
	 */
	public function update_order()
	{
		$this->catering_events_model->update_order($this->input->post());

		echo json_encode([
			'success' => TRUE,
			'message' => _l('events_updated_successfully'),
		]);
	}

	/**
	 * View single event
	 * @param int $id Event ID
	 */
	public function view($id = '')
	{
		if (staff_cant('view', 'catering_events'))
		{
			access_denied('catering');
		}

		if ( ! $id)
		{
			redirect(admin_url('catering_management_module/events'));
		}

		$data['event'] = $this->catering_events_model->get($id);

		if ( ! $data['event'])
		{
			show_404();
		}

		// Load menu data
		$data['event_menu'] = $this->catering_event_menu_model->get_event_menu($id);
		$data['event_menu_summary'] = $this->catering_event_menu_model->get_event_menu_summary($id);

		$data['menus'] = $this->catering_menus_model->get_all(['active' => 1]);
		$data['packages'] = $this->catering_packages_model->get_all(['active' => 1]);
		$data['menu_items'] = $this->catering_menu_items_model->get_all(['active' => 1]);
		$data['menu_sections'] = $this->catering_menu_sections_model->get_all(['active' => 1]);

		// Load staff data
		$data['event_staff'] = $this->catering_event_staff_model->get_event_staff($id);
		$data['staff_summary'] = $this->catering_event_staff_model->get_staff_summary($id);
		$data['staff_roles'] = $this->catering_event_staff_model->get_staff_roles();
		$data['available_staff'] = $this->staff_model->get('', ['active' => 1]);

		// Load finance data
		$data['estimate'] = NULL;
		$data['invoice'] = NULL;
		$data['payments'] = [];
		$data['financials'] = NULL;

		if ($data['event']->estimate_id)
		{
			$this->load->model('estimates_model');
			$data['estimate'] = $this->estimates_model->get($data['event']->estimate_id);
		}

		if ($data['event']->invoice_id)
		{
			$this->load->model('invoices_model');
			$this->load->model('payments_model');
			$data['invoice'] = $this->invoices_model->get($data['event']->invoice_id);

			// Get invoice payments
			$data['payments'] = $this->payments_model->get_invoice_payments($data['event']->invoice_id);

			// Calculate payment totals
			$data['total_paid'] = array_sum(array_column($data['payments'], 'amount'));

			// Calculate balance due
			if ($data['invoice'])
			{
				$data['balance_due'] = $data['invoice']->total - $data['total_paid'];
				$data['payment_percentage'] = $data['invoice']->total > 0 ? ($data['total_paid'] / $data['invoice']->total) * 100 : 0;
			}
		}

		// Calculate event financials (costs, revenue, profit)
		$data['financials'] = $this->catering_events_model->calculate_event_financials($id);
		$data['financials_summary'] = $data['financials']['summary'];

		// Load notes data
		$data['notes'] = $this->catering_event_notes_model->get_event_notes($id);
		$data['notes_stats'] = $this->catering_event_notes_model->get_notes_stats($id);

		$data['title'] = $data['event']->event_name;
		$data['event_statuses'] = $this->catering_events_model->get_statuses();

//		cmm_debug_var($data);
		$this->load->view('admin/events/view', $data);
	}

	/**
	 * Create/Edit event
	 * @param int $id Event ID (for edit)
	 */
	public function event($id = '')
	{
		if ($this->input->post())
		{
			$data = $this->input->post();

			// Handle date formatting
			if (isset($data['event_start']))
			{
				$data['event_start'] = to_sql_date($data['event_start'], TRUE);
			}
			if ( ! empty($data['event_end']))
			{
				$data['event_end'] = to_sql_date($data['event_end'], TRUE);
			} else
			{
				$data['event_end'] = NULL;
			}

			// Handle numeric fields
			$data['guest_count_expected'] = (int)($data['guest_count_expected'] ?? 0);
			$data['guest_count_final'] = ! empty($data['guest_count_final']) ? (int)$data['guest_count_final'] : NULL;

			if ($id)
			{
				// Update
				if (staff_cant('edit', 'catering_events'))
				{
					access_denied('catering');
				}

				$success = $this->catering_events_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('catering_event')) : _l('update_failed');
			} else
			{
				// Create
				if (staff_cant('create', 'catering_events'))
				{
					access_denied('catering');
				}

				$id = $this->catering_events_model->add($data);
				$success = (bool)$id;
				$message = $success ? _l('added_successfully', _l('catering_event')) : _l('add_failed');
			}

			if ($success)
			{
				set_alert('success', $message);
				redirect(admin_url('catering_management_module/events/view/'.$id));
			} else
			{
				set_alert('danger', $message);
			}
		}

		if ($id)
		{
			if (staff_cant('view', 'catering_events'))
			{
				access_denied('catering');
			}

			$data['event'] = $this->catering_events_model->get($id);
			if ( ! $data['event'])
			{
				show_404();
			}
		}

		$data['clients'] = $this->clients_model->get();
		$data['event_types'] = $this->catering_event_types_model->get();
		$data['staff'] = $this->staff_model->get();
		$data['title'] = $id ? _l('edit_event') : _l('new_event');

		$this->load->view('admin/events/event', $data);
	}

	/**
	 * Delete event
	 * @param int $id Event ID
	 */
	public function delete_event($id)
	{
		if (staff_cant('delete', 'catering_events'))
		{
			access_denied('catering');
		}

		if ( ! $id)
		{
			redirect(admin_url('catering_management_module/events'));
		}

		$response = $this->catering_events_model->delete($id);

		if ($response)
		{
			set_alert('success', _l('deleted', _l('catering_event')));
		} else
		{
			set_alert('danger', _l('problem_deleting', _l('catering_event')));
		}

		redirect(admin_url('catering_management_module/events'));
	}


	public function update_event_status()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');
		$status = $this->input->post('status');

		if ( ! $event_id || ! $status)
		{
			echo json_encode(['success' => FALSE, 'message' => _l('missing_parameters')]);

			return;
		}

		// Validate status
		$valid_statuses = $this->catering_events_model->get_statuses();
		if ( ! in_array($status, $valid_statuses))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('invalid_status'),
			]);

			return;
		}

		$success = $this->catering_events_model->change_status($event_id, $status);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('status_changed_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('status_change_failed'),
			]);
		}
	}

	/**
	 * Get event types (helper)
	 * @return array
	 */
	private function get_event_types()
	{
		$this->db->select('etid as id, name');
		$this->db->from(db_prefix().'catering_event_types');
		$this->db->where('active', 1);

		return $this->db->get()->result_array();
	}

	/**
	 * Upload event attachment
	 * @param int $id Event ID
	 */
	public function upload_attachment($id)
	{
		if (staff_cant('edit', 'catering_events'))
		{
			access_denied('catering');
		}

		handle_catering_event_attachments($id);
	}

	/**
	 * Delete event attachment
	 * @param int $id Attachment ID
	 */
	public function delete_attachment($id)
	{
		if (staff_cant('delete', 'catering_events'))
		{
			access_denied('catering');
		}

		echo json_encode([
			'success' => $this->catering_events_model->delete_attachment($id),
		]);
	}

	/**
	 * Get events for calendar (AJAX)
	 */
	public function get_calendar_events()
	{
		if ($this->input->is_ajax_request())
		{
			$start = $this->input->get('start');
			$end = $this->input->get('end');

			$events = $this->catering_events_model->get_timeline_data($start, $end);
			$calendar_events = [];

			foreach ($events as $event)
			{
				$calendar_events[] = [
					'id' => $event['eventid'],
					'title' => $event['event_name'],
					'start' => $event['event_start'],
					'end' => $event['event_end'],
					'className' => 'event-status-'.$event['status'],
					'url' => admin_url('catering_management_module/events/view/'.$event['eventid']),
				];
			}

			echo json_encode($calendar_events);
		}
	}

	/**
	 * @return void
	 */
	public function events_kanban_load_more()
	{
		$status = $this->input->get('status');
		$page = $this->input->get('page');

		$events = (new Events_kanban($status))
			->search($this->input->get('search'))
			->sortBy(
				$this->input->get('sort_by'),
				$this->input->get('sort')
			)
			->page($page)->get();


		foreach ($events as $event)
		{
			$this->load->view('admin/events/_kan_ban_card', [
				'event' => $event,
				'status' => $status,
			]);
		}
	}

	/**
	 * Save event menu
	 */
	public function save_event_menu()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');
		$menu_id = $this->input->post('menu_id');
		$menu_type = $this->input->post('menu_type');
		$pricing_mode = $this->input->post('pricing_mode');
		$price_per_person = $this->input->post('price_per_person');

		if ( ! $event_id || ! $menu_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$data = [
			'price_per_person' => $price_per_person,
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ($pricing_mode === 'package' && $menu_type === 'package')
		{
			$data['menu_id'] = NULL;
			$data['package_id'] = $menu_id;
		} else
		{
			$pricing_mode = ($pricing_mode === 'package' ? 'per_person' : $pricing_mode);
			$data['pricing_mode'] = $pricing_mode;
			$data['menu_id'] = $menu_id;
			$data['package_id'] = NULL;
		}

		$success = $this->catering_event_menu_model->save_event_menu($event_id, $data);

		if ($success)
		{
			$this->catering_event_menu_model->load_menu_or_package($event_id, $pricing_mode, $menu_id);
			echo json_encode([
				'success' => TRUE,
				'message' => _l('menu_saved_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('menu_save_failed'),
			]);
		}
	}

	/**
	 * Add item to event menu
	 */
	public function add_menu_item()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');
		$item_id = $this->input->post('item_id');
		$section_id = $this->input->post('section_id');
		$portion_per_guest = $this->input->post('portion_per_guest', 1.0);
		$unit_cost = $this->input->post('unit_cost');
		$unit_price = $this->input->post('unit_price');

		if ( ! $event_id || ! $item_id || ! $section_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$event_menu = $this->catering_event_menu_model->get_event_menu($event_id);
		$item_id = $this->catering_event_menu_model->add_menu_item(
			$event_menu->id,
			$event_id,
			$item_id,
			$section_id,
			$portion_per_guest,
			$unit_cost,
			$unit_price
		);

		if ($item_id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('item_added_successfully'),
				'item_id' => $item_id,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('item_add_failed'),
			]);
		}
	}

	/**
	 * Remove item from event menu
	 */
	public function remove_menu_item()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_item_id = $this->input->post('event_item_id');

		if ( ! $event_item_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_menu_model->remove_menu_item($event_item_id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('item_removed_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('item_remove_failed'),
			]);
		}
	}

	/**
	 * Update menu item
	 */
	public function update_menu_item()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_item_id = $this->input->post('event_item_id');
		$data = [
			'portion_per_guest' => $this->input->post('portion_per_guest'),
			'unit_cost' => $this->input->post('unit_cost'),
			'unit_price' => $this->input->post('unit_price'),
			'updated_at' => date('Y-m-d H:i:s'),
		];

		if ( ! $event_item_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_menu_model->update_menu_item($event_item_id, $data);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('item_updated_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('item_update_failed'),
			]);
		}
	}

	/**
	 * Update menu item positions
	 */
	public function update_menu_item_positions()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$positions = $this->input->post('positions');

		if ( ! $positions || ! is_array($positions))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_menu_model->update_item_positions($positions);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('menu_order_updated'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('menu_order_update_failed'),
			]);
		}
	}

	/**
	 * Get menu items for selection
	 */
	public function get_menu_items()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		$search = $this->input->get('search');

		$this->db->select('mi.id, mi.item_name, mi.description, mi.unit_cost, mi.unit_price, mc.name as category_name');
		$this->db->from(db_prefix().'catering_menu_items mi');
		$this->db->join(db_prefix().'catering_menu_categories mc', 'mc.id = mi.category_id', 'left');
		$this->db->where('mi.active', 1);

		if ($search)
		{
			$this->db->like('mi.item_name', $search);
		}

		$this->db->order_by('mi.item_name', 'ASC');
		$this->db->limit(50);

		$items = $this->db->get()->result_array();

		echo json_encode([
			'success' => TRUE,
			'items' => $items,
		]);
	}

	/**
	 * Add staff assignment to event
	 */
	public function add_staff_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');
		$staff_id = $this->input->post('staff_id');
		$role = $this->input->post('role');
		$shift_start = $this->input->post('shift_start');
		$shift_end = $this->input->post('shift_end');
		$hourly_rate = $this->input->post('hourly_rate');
		$notes = $this->input->post('notes');

		if ( ! $event_id || ! $staff_id || ! $role || ! $shift_start || ! $shift_end)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		// Check for scheduling conflicts
		$conflicts = $this->catering_event_staff_model->check_scheduling_conflicts($staff_id, $shift_start, $shift_end);
		if ( ! empty($conflicts))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_scheduling_conflict'),
				'conflicts' => $conflicts,
			]);

			return;
		}

		$data = [
			'event_id' => $event_id,
			'staff_id' => $staff_id,
			'role' => $role,
			'shift_start' => to_sql_date($shift_start, TRUE),
			'shift_end' => to_sql_date($shift_end, TRUE),
			'hourly_rate' => $hourly_rate ?: NULL,
			'notes' => $notes ?: NULL,
			'status' => 'pending',
		];

		$assignment_id = $this->catering_event_staff_model->add_staff_assignment($data);

		if ($assignment_id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('staff_assignment_added_successfully'),
				'assignment_id' => $assignment_id,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_assignment_add_failed'),
			]);
		}
	}

	/**
	 * Update staff assignment
	 */
	public function update_staff_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$assignment_id = $this->input->post('assignment_id');
		$data = [
			'role' => $this->input->post('role'),
			'shift_start' => to_sql_date($this->input->post('shift_start'), TRUE),
			'shift_end' => to_sql_date($this->input->post('shift_end'), TRUE),
			'hourly_rate' => $this->input->post('hourly_rate') ?: NULL,
			'notes' => $this->input->post('notes') ?: NULL,
		];

		if ( ! $assignment_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		// Check for scheduling conflicts (excluding current assignment)
		$assignment = $this->catering_event_staff_model->get_staff_assignment($assignment_id);
		$conflicts = $this->catering_event_staff_model->check_scheduling_conflicts(
			$assignment->staff_id,
			$data['shift_start'],
			$data['shift_end'],
			$assignment_id
		);

		if ( ! empty($conflicts))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_scheduling_conflict'),
				'conflicts' => $conflicts,
			]);

			return;
		}

		$success = $this->catering_event_staff_model->update_staff_assignment($assignment_id, $data);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('staff_assignment_updated_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_assignment_update_failed'),
			]);
		}
	}

	/**
	 * Remove staff assignment
	 */
	public function remove_staff_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$assignment_id = $this->input->post('assignment_id');

		if ( ! $assignment_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_staff_model->remove_staff_assignment($assignment_id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('staff_assignment_removed_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_assignment_remove_failed'),
			]);
		}
	}

	/**
	 * Update staff assignment status
	 */
	public function update_staff_status()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$assignment_id = $this->input->post('assignment_id');
		$status = $this->input->post('status');

		if ( ! $assignment_id || ! $status)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_staff_model->update_assignment_status($assignment_id, $status);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('staff_status_updated_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_status_update_failed'),
			]);
		}
	}

	/**
	 * Get staff assignment by ID
	 */
	public function get_staff_assignment($assignment_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		if ( ! $assignment_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$assignment = $this->catering_event_staff_model->get_staff_assignment($assignment_id);

		if ($assignment)
		{
			// Format datetime fields for frontend
			$assignment->shift_start = _dt($assignment->shift_start, FALSE, 'Y-m-d H:i');
			$assignment->shift_end = _dt($assignment->shift_end, FALSE, 'Y-m-d H:i');

			echo json_encode([
				'success' => TRUE,
				'assignment' => $assignment,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_assignment_not_found'),
			]);
		}
	}

	/**
	 * Get available staff for time slot
	 */
	public function get_available_staff()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		$event_id = $this->input->get('event_id');
		$shift_start = $this->input->get('shift_start');
		$shift_end = $this->input->get('shift_end');

		if ( ! $event_id || ! $shift_start || ! $shift_end)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$available_staff = $this->catering_event_staff_model->get_available_staff($event_id, $shift_start, $shift_end);

		echo json_encode([
			'success' => TRUE,
			'staff' => $available_staff,
		]);
	}

	/**
	 * Get event staff for AJAX updates
	 */
	public function get_event_staff($event_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$staff_assignments = $this->catering_event_staff_model->get_event_staff($event_id);

		// Format datetime fields for frontend
		foreach ($staff_assignments as &$assignment)
		{
			$assignment['shift_start'] = _dt($assignment['shift_start'], FALSE, 'Y-m-d H:i');
			$assignment['shift_end'] = _dt($assignment['shift_end'], FALSE, 'Y-m-d H:i');
		}

		echo json_encode([
			'success' => TRUE,
			'staff' => $staff_assignments,
		]);
	}

	/**
	 * Get staff summary for AJAX updates
	 */
	public function get_staff_summary($event_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$summary = $this->catering_event_staff_model->get_staff_summary($event_id);

		echo json_encode([
			'success' => TRUE,
			'summary' => $summary,
		]);
	}

	/**
	 * Get staffing content for AJAX reload (only the content part)
	 */
	public function get_staffing_content($event_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		// Load all data needed for staffing tab
		$data['event'] = $this->catering_events_model->get($event_id);
		$data['event_staff'] = $this->catering_event_staff_model->get_event_staff($event_id);
		$data['staff_summary'] = $this->catering_event_staff_model->get_staff_summary($event_id);
		$data['staff_roles'] = $this->catering_event_staff_model->get_staff_roles();
		$data['available_staff'] = $this->staff_model->get('', ['active' => 1]);

		// Render only the content part (without wrapper, modal, and script)
		$html = $this->load->view('admin/events/tabs/staffing_content_partial', $data, TRUE);

		echo json_encode([
			'success' => TRUE,
			'html' => $html,
		]);
	}

	/**
	 * Notify all staff about event
	 */
	public function notify_all_staff()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');

		if ( ! $event_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		// Get event details
		$event = $this->catering_events_model->get($event_id);
		if ( ! $event)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('event_not_found'),
			]);

			return;
		}

		// Get staff assignments for this event
		$staff_assignments = $this->catering_event_staff_model->get_event_staff($event_id);

		if (empty($staff_assignments))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('no_staff_to_notify'),
			]);

			return;
		}

		$notified_count = 0;
		$errors = [];

		foreach ($staff_assignments as $assignment)
		{
			// Here you would typically send email/notification to staff
			// For now, we'll just log the notification
			log_activity('Staff Notification Sent [Staff: '.$assignment['staff_name'].', Event: '.$event->event_name.']');
			$notified_count++;
		}

		if ($notified_count > 0)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => sprintf(_l('staff_notifications_sent'), $notified_count),
				'notified_count' => $notified_count,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('staff_notification_failed'),
			]);
		}
	}

	/**
	 * Generate estimate from event
	 * @param int $id Event ID
	 */
	public function generate_estimate($id)
	{
		if (staff_cant('create', 'estimates'))
		{
			access_denied('estimates');
		}

		$event = $this->catering_events_model->get($id);

		if ( ! $event)
		{
			set_alert('danger', _l('event_not_found'));
			redirect(admin_url('catering_management_module/events'));
		}

		// Check if event already has an estimate
		if ($event->estimate_id)
		{
			set_alert('info', _l('event_estimate_already_exists'));
			redirect(admin_url('estimates/list_estimates/'.$event->estimate_id));

			return;
		}

		// Check if event has client (estimates require client, not lead)
		if ( ! $event->client_id)
		{
			set_alert('danger', _l('event_must_have_client'));
			redirect(admin_url('catering_management_module/events/view/'.$id));

			return;
		}

		// Generate the estimate
		$estimate_id = $this->catering_events_model->generate_estimate($id);

		if ($estimate_id)
		{
			set_alert('success', _l('estimate_generated_successfully'));
			redirect(admin_url('estimates/list_estimates/'.$estimate_id));
		} else
		{
			set_alert('danger', _l('estimate_generation_failed'));
			redirect(admin_url('catering_management_module/events/view/'.$id));
		}
	}

	/**
	 * Regenerate estimate from event (update existing estimate)
	 * @param int $id Event ID
	 */
	public function regenerate_estimate($id)
	{
		if (staff_cant('edit', 'estimates'))
		{
			access_denied('estimates');
		}

		$event = $this->catering_events_model->get($id);

		if ( ! $event)
		{
			set_alert('danger', _l('event_not_found'));
			redirect(admin_url('catering_management_module/events'));

			return;
		}

		// Check if event has an estimate
		if ( ! $event->estimate_id)
		{
			set_alert('warning', _l('event_has_no_estimate'));
			redirect(admin_url('catering_management_module/events/view/'.$id));

			return;
		}

		// Regenerate the estimate
		$success = $this->catering_events_model->regenerate_estimate($id);

		if ($success)
		{
			set_alert('success', _l('estimate_regenerated_successfully'));
			redirect(admin_url('estimates/list_estimates/'.$event->estimate_id));
		} else
		{
			set_alert('danger', _l('estimate_regeneration_failed'));
			redirect(admin_url('catering_management_module/events/view/'.$id));
		}
	}

	/**
	 * Unlink estimate from event
	 * @param int $id Event ID
	 */
	public function unlink_estimate($id)
	{
		if (staff_cant('edit', 'estimates'))
		{
			access_denied('estimates');
		}

		$event = $this->catering_events_model->get($id);

		if ( ! $event)
		{
			set_alert('danger', _l('event_not_found'));
			redirect(admin_url('catering_management_module/events'));

			return;
		}

		// Check if event has an estimate
		if ( ! $event->estimate_id)
		{
			set_alert('warning', _l('event_has_no_estimate'));
			redirect(admin_url('catering_management_module/events/view/'.$id));

			return;
		}

		// Unlink the estimate
		$success = $this->catering_events_model->unlink_estimate($id);

		if ($success)
		{
			set_alert('success', _l('estimate_unlinked_successfully'));
		} else
		{
			set_alert('danger', _l('estimate_unlink_failed'));
		}

		redirect(admin_url('catering_management_module/events/view/'.$id));
	}

	/**
	 * Convert estimate to invoice
	 * @param int $id Event ID
	 */
	public function convert_to_invoice($id)
	{
		if (staff_cant('create', 'invoices'))
		{
			access_denied('invoices');
		}

		$event = $this->catering_events_model->get($id);

		if ( ! $event)
		{
			set_alert('danger', _l('event_not_found'));
			redirect(admin_url('catering_management_module/events'));

			return;
		}

		// Check if event has an estimate
		if ( ! $event->estimate_id)
		{
			set_alert('warning', _l('event_has_no_estimate'));
			redirect(admin_url('catering_management_module/events/view/'.$id));

			return;
		}

		// Check if event already has an invoice
		if ($event->invoice_id)
		{
			set_alert('info', _l('event_invoice_already_exists'));
			redirect(admin_url('invoices/list_invoices/'.$event->invoice_id));

			return;
		}

		// Load Estimates_model
		$this->load->model('estimates_model');

		// Convert estimate to invoice using PerfexCRM's built-in method
		$invoice_id = $this->estimates_model->convert_to_invoice($event->estimate_id);

		if ($invoice_id)
		{
			// Update event with invoice_id
			$this->db->where('eventid', $id);
			$this->db->update(db_prefix().'catering_events', ['invoice_id' => $invoice_id]);

			// Log activity
			log_activity('Invoice Created from Event [Event ID: '.$id.', Invoice ID: '.$invoice_id.']');

			set_alert('success', _l('invoice_created_successfully'));
			redirect(admin_url('invoices/list_invoices/'.$invoice_id));
		} else
		{
			set_alert('danger', _l('invoice_creation_failed'));
			redirect(admin_url('catering_management_module/events/view/'.$id));
		}
	}

	/**
	 * Upload attachment for event
	 * @param int $id Event ID
	 */
	public function upload_document_attachment($id)
	{
		if (staff_cant('create', 'catering_events'))
		{
			ajax_access_denied();
		}

		$event = $this->catering_events_model->get($id);

		if ( ! $event)
		{
			header('Content-Type: application/json');
			echo json_encode(['success' => FALSE, 'message' => _l('event_not_found')]);

			return;
		}

		// Handle file upload
		$result = handle_catering_event_attachments($id);

		header('Content-Type: application/json');
		echo json_encode($result);
	}

	/**
	 * Delete attachment
	 * @param int $attachment_id Attachment ID
	 */
	public function delete_document_attachment($attachment_id)
	{
		if (staff_cant('delete', 'catering_events'))
		{
			ajax_access_denied();
		}

		// Get attachment info
		$this->db->where('id', $attachment_id);
		$this->db->where('rel_type', 'catering_event');
		$attachment = $this->db->get(db_prefix().'files')->row();

		if ( ! $attachment)
		{
			header('Content-Type: application/json');
			echo json_encode(['success' => FALSE, 'message' => _l('file_not_found')]);

			return;
		}

		// Get file path
		$path = get_upload_path_by_type('catering_event').$attachment->rel_id.'/'.$attachment->file_name;

		// Delete from database
		$this->db->where('id', $attachment_id);
		$this->db->delete(db_prefix().'files');

		// Delete physical file
		if (file_exists($path))
		{
			unlink($path);
		}

		// Log activity
		log_activity('File Deleted from Event [Event ID: '.$attachment->rel_id.', File: '.$attachment->file_name.']');

		header('Content-Type: application/json');
		echo json_encode(['success' => TRUE, 'message' => _l('file_deleted_successfully')]);
	}

	public function download_attachment($event_id = '')
	{
		$file_id = $this->input->get('file_id');

		if (empty($event_id) || empty($file_id))
		{
			blank_page('File not found');
		}

		$this->db->where('rel_id', $event_id);
		$this->db->where('id', $file_id);
		$this->db->where('rel_type', 'catering_event');
		$file = $this->db->get(db_prefix().'files')->row();

		if ( ! $file)
		{
			blank_page('File not found');
		}

		$this->load->helper('download');
		$path = catering_event_get_document_download_path($event_id, $file->file_name);
		force_download($path, NULL);
	}

	/**
	 * Get event notes (AJAX)
	 * @param int $event_id
	 */
	public function get_event_notes($event_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$notes = $this->catering_event_notes_model->get_event_notes($event_id);
		$stats = $this->catering_event_notes_model->get_notes_stats($event_id);

		echo json_encode([
			'success' => TRUE,
			'notes' => $notes,
			'stats' => $stats,
		]);
	}

	/**
	 * Add note to event
	 */
	public function add_note()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('create', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$event_id = $this->input->post('event_id');
		$description = $this->input->post('description');
		$visible_to_client = $this->input->post('visible_to_client') ? 1 : 0;

		if ( ! $event_id || ! $description)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$data = [
			'event_id' => $event_id,
			'description' => $description,
			'visible_to_client' => $visible_to_client,
		];

		$note_id = $this->catering_event_notes_model->add($data);

		if ($note_id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('note_added_successfully'),
				'note_id' => $note_id,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('note_add_failed'),
			]);
		}
	}

	/**
	 * Get single note (AJAX)
	 * @param int $note_id
	 */
	public function get_note($note_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$note = $this->catering_event_notes_model->get($note_id);

		if ($note)
		{
			echo json_encode([
				'success' => TRUE,
				'note' => $note,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('note_not_found'),
			]);
		}
	}

	/**
	 * Update note
	 */
	public function update_note()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('edit', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		$note_id = $this->input->post('note_id');
		$description = $this->input->post('description');
		$visible_to_client = $this->input->post('visible_to_client') ? 1 : 0;

		if ( ! $note_id || ! $description)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$data = [
			'description' => $description,
			'visible_to_client' => $visible_to_client,
		];

		$success = $this->catering_event_notes_model->update($note_id, $data);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('note_updated_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('note_update_failed'),
			]);
		}
	}

	/**
	 * Delete note
	 * @param int $note_id
	 */
	public function delete_note($note_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('delete', 'catering_events'))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('access_denied'),
			]);

			return;
		}

		if ( ! $note_id)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('missing_parameters'),
			]);

			return;
		}

		$success = $this->catering_event_notes_model->delete($note_id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('note_deleted_successfully'),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('note_delete_failed'),
			]);
		}
	}
}