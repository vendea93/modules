<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Services extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_management_system/service_model');
		$this->load->model('hotel_management_system/room_model');
	}

	/**
	 * List all services
	 * @return view
	 */
	public function index()
	{
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/services/table'));
		}

		$data['title'] = _l('hms_services');
		$this->load->view('hotel_management_system/admin/services/manage', $data);
	}

	/**
	 * Add new service or edit existing
	 * @param integer $id service id
	 * @return view
	 */
	public function service($id = '')
	{
		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				$id = $this->service_model->add($data);
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('service')));
					redirect(admin_url('hotel_management_system/services'));
				}
			} else
			{
				$success = $this->service_model->update($data, $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('service')));
				}
				redirect(admin_url('hotel_management_system/services/service/' . $id));
			}
		}

		if ($id == '')
		{
			$title = _l('add_new', _l('service'));
			$data['service'] = NULL;
		} else
		{
			$service = $this->service_model->get($id);

			if ( ! $service)
			{
				blank_page(_l('service_not_found'), 'danger');
			}

			$data['service'] = $service;
			$title = _l('edit', _l('service'));
		}

		// Get service types for dropdown
		$service_types = hms_get_service_types(TRUE);
		$data['service_types'] = $service_types;
		$data['title'] = $title;
		$this->load->view('hotel_management_system/admin/services/service', $data);
	}

	/**
	 * Delete service
	 * @param integer $id service id
	 * @return redirect
	 */
	public function delete($id)
	{
		$response = $this->service_model->delete($id);

		if (isset($response['success']) && $response['success'])
		{
			set_alert('success', $response['message']);
		} else
		{
			set_alert('warning', $response['message'] ?? _l('problem_deleting', _l('service_lowercase')));
		}

		redirect(admin_url('hotel_management_system/services'));
	}

	/**
	 * Change service status
	 * @param integer $id service id
	 * @param string $status new status
	 * @return redirect
	 */
	public function change_status($id, $status)
	{
		$success = $this->service_model->change_status($id, $status);

		if ($success)
		{
			set_alert('success', _l('service_status_changed_successfully'));
		} else
		{
			set_alert('warning', _l('service_status_change_failed'));
		}

		redirect(admin_url('hotel_management_system/services'));
	}

	/**
	 * Get service assignments
	 * @return DataTable
	 */
	public function assignments()
	{
		$data['title'] = _l('service_assignments');

		// Load staff model
		$this->load->model('staff_model');
		$data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

		// Get room model
		$this->load->model('hotel_management_system/room_model');

		// Get property model
		$this->load->model('hotel_management_system/property_model');
		$data['properties'] = $this->property_model->get_all(['status' => 'active']);

		// Get services
		$data['services'] = $this->service_model->get_all(['status' => 'active']);

		// Get days of week
		$data['days_of_week'] = hms_get_days_of_week();

		$data['assignments'] = $this->service_model->get_service_assignments();

		$this->load->view('hotel_management_system/admin/services/assignments', $data);
	}

	/**
	 * View staff assignments
	 * @param integer $staff_id staff id
	 * @return view
	 */
	public function staff_assignments($staff_id)
	{
		// Load staff model
		$this->load->model('staff_model');
		$staff = $this->staff_model->get($staff_id);

		if ( ! $staff)
		{
			blank_page(_l('staff_member_not_found'), 'danger');
		}

		$data['staff'] = $staff;
		$data['assignments'] = $this->service_model->get_assignments_by_staff($staff_id);
		$data['days_of_week'] = hms_get_days_of_week();

		$data['title'] = _l('service_assignments_for_staff', $staff->firstname . ' ' . $staff->lastname);
		$this->load->view('hotel_management_system/admin/services/staff_assignments', $data);
	}

	public function edit_assignment($id)
	{
		$assignment = $this->service_model->get_assignment($id);

		// Load staff model
		$this->load->model('staff_model');
		$assignment['staff_list'] = $this->staff_model->get('', ['active' => 1]);

		// Get room model
		$this->load->model('hotel_management_system/room_model');
		$assignment['rooms'] = $this->room_model->get_by_property($assignment['property_id']);

		// Get services
		$assignment['services'] = $this->service_model->get_all(['status' => 'active']);

		// Get days of week
		$assignment['days_of_week'] = hms_get_days_of_week();

		$data['assignment'] = $assignment;
		echo json_encode([
			'success' => TRUE,
			'assignment' => $assignment,
			'html' => $this->load->view('hotel_management_system/admin/services/edit_assignment', $data, TRUE)
		]);
		die();
	}

	/**
	 * Add new service assignment
	 * @return json
	 */
	public function add_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$data = $this->input->post();
		// Check for overlapping assignments
		if ( ! $this->service_model->is_staff_available($data['staff_id'], $data['day_of_week'], $data['start_time'], $data['end_time']))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('service_assignment_overlaps_with_existing')
			]);
			die();
		}

		unset($data['property_id']);
		$assignment_id = $this->service_model->add_assignment($data);

		if ($assignment_id)
		{
			// Get room details
			$this->load->model('hotel_management_system/room_model');
			$room = $this->room_model->get($data['room_id']);

			// Get service details
			$service = $this->service_model->get($data['service_id']);

			// Get staff details
			$this->load->model('staff_model');
			$staff = $this->staff_model->get($data['staff_id']);

			// Get day name
			$days_of_week = hms_get_days_of_week();

			echo json_encode([
				'success' => TRUE,
				'message' => _l('service_assignment_added_successfully'),
				'assignment' => [
					'id' => $assignment_id,
					'room_name' => $room ? $room->name : _l('unknown'),
					'service_name' => $service ? $service->name : _l('unknown'),
					'staff_name' => $staff ? $staff->firstname . ' ' . $staff->lastname : _l('unknown'),
					'day_name' => isset($days_of_week[$data['day_of_week']]) ? $days_of_week[$data['day_of_week']] : _l('unknown'),
					'start_time' => $data['start_time'],
					'end_time' => $data['end_time']
				]
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_adding_service_assignment')
			]);
		}
	}

	/**
	 * Update service assignment
	 * @return json
	 */
	public function update_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$id = $this->input->post('id');
		$data = $this->input->post();

		// Check for overlapping assignments
		if ( ! $this->service_model->is_staff_available($data['staff_id'], $data['day_of_week'], $data['start_time'], $data['end_time'], $id))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('service_assignment_overlaps_with_existing')
			]);
			die();
		}

		$success = $this->service_model->update_assignment($data, $id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('service_assignment_updated_successfully')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('no_changes_made')
			]);
		}
	}

	/**
	 * Delete service assignment
	 * @param integer $id assignment id
	 * @return json
	 */
	public function delete_assignment($id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$success = $this->service_model->delete_assignment($id);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('service_assignment_deleted_successfully')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting_service_assignment')
			]);
		}
	}

	/**
	 * Get staff assignments for specific day
	 * @return json
	 */
	public function get_staff_day_assignments()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$staff_id = $this->input->post('staff_id');
		$day_of_week = $this->input->post('day_of_week');

		$assignments = $this->service_model->get_staff_day_assignments($staff_id, $day_of_week);

		echo json_encode([
			'success' => TRUE,
			'assignments' => $assignments
		]);
	}

	/**
	 * Get property rooms
	 * @return json
	 */
	public function get_property_rooms()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$property_id = $this->input->post('property_id');

		$this->load->model('hotel_management_system/room_model');
		$rooms = $this->room_model->get_by_property($property_id);

		echo json_encode([
			'success' => TRUE,
			'rooms' => $rooms
		]);
		die();
	}

	/**
	 * Get services table
	 * @return DataTable
	 */
	public function table()
	{
		$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/services/table'));
	}

	public function get_rooms_by_property($property_id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/services/assignments'));
		}

		$rooms = $this->room_model->get_by_property($property_id);

		echo json_encode([
			'success' => TRUE,
			'rooms' => $rooms
		]);
	}
}