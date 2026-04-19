<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rooms extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_management_system/room_model');
		$this->load->model('hotel_management_system/property_model');
	}

	/**
	 * List all rooms
	 * @return view
	 */
	public function index()
	{
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/rooms/table'));
		}

		$data['title'] = _l('hms_rooms');
		$this->load->view('hotel_management_system/admin/rooms/manage', $data);
	}

	/**
	 * View room details
	 * @param integer $id room id
	 * @return view
	 */
	public function view($id)
	{
		$data['room'] = $this->room_model->get_room_with_details($id);

		if ( ! $data['room'])
		{
			blank_page(_l('room_not_found'), 'danger');
		}

		$data['booking_history'] = $this->room_model->get_booking_history($id);
		$data['title'] = $data['room']->name;
		$this->load->view('hotel_management_system/admin/rooms/view', $data);
	}

	/**
	 * Add new room or edit existing
	 * @param integer $id room id
	 * @return view
	 */
	public function room($id = '')
	{
		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				$id = $this->room_model->add($data);
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('room')));
					redirect(admin_url('hotel_management_system/rooms/room/' . $id));
				}
			} else
			{
				$success = $this->room_model->update($data, $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('room')));
				}
				redirect(admin_url('hotel_management_system/rooms/room/' . $id));
			}
		}

		if ($id == '')
		{
			$title = _l('add_new', _l('room'));
			$data['room'] = NULL;
		} else
		{
			$room = $this->room_model->get_room_with_details($id);

			if ( ! $room)
			{
				blank_page(_l('room_not_found'), 'danger');
			}

			$data['room'] = $room;
			$title = _l('edit', _l('room'));
		}

		// Get all properties for dropdown
		$data['properties'] = $this->property_model->get_all(['status' => 'active']);

		// Get room types, bed types, and amenities for dropdowns
		$data['room_types'] = get_room_types();
		$data['bed_types'] = get_bed_types();
		$data['meal_plans'] = get_meal_plans();
		$data['room_amenities'] = get_room_amenities();
		$data['room_statuses'] = get_room_statuses();

		$data['title'] = $title;
		$this->load->view('hotel_management_system/admin/rooms/room', $data);
	}

	/**
	 * Update room status
	 * @param integer $id room id
	 * @param string $status new status
	 * @return redirect
	 */
	public function change_status($id, $status)
	{
		$success = $this->room_model->update_status($id, $status);

		if ($success)
		{
			set_alert('success', _l('room_status_changed_successfully'));
		} else
		{
			set_alert('warning', _l('room_status_change_failed'));
		}

		redirect(admin_url('hotel_management_system/rooms/view/' . $id));
	}

	/**
	 * Upload room image
	 * @param integer $room_id room id
	 * @return json
	 */
	public function upload_image($room_id)
	{
		return $this->handle_room_image_upload($room_id);
	}

	/**
	 * Handle room image upload
	 * @param integer $room_id Room ID
	 * @return string
	 */
	private function handle_room_image_upload($room_id)
	{
		$CI = &get_instance();

		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '')
		{
			$path = HMS_MODULE_UPLOAD_FOLDER . '/rooms/';

			// Check if the upload folder exists, if not create it
			if ( ! file_exists($path))
			{
				mkdir($path, 0755, TRUE);
			}

			// Get file extension
			$extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

			// Allowed extensions
			$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

			if ( ! in_array($extension, $allowed_extensions))
			{
				echo json_encode([
					'success' => FALSE,
					'message' => _l('file_extension_not_allowed')
				]);
				die;
			}

			// Get unique filename
			$filename = 'room_' . $room_id . '_' . md5(uniqid(rand(), TRUE)) . '.' . $extension;

			// Upload configuration
			$config['upload_path'] = $path;
			$config['allowed_types'] = 'jpg|jpeg|png|gif';
			$config['max_size'] = '5120'; // 5MB
			$config['file_name'] = $filename;

			$CI->load->library('upload', $config);

			if ( ! $CI->upload->do_upload('file'))
			{
				echo json_encode([
					'success' => FALSE,
					'message' => $CI->upload->display_errors()
				]);
				die;
			}

			$uploaded_file_data = $CI->upload->data();

			// Save image to database
			$image_data = [
				'room_id' => $room_id,
				'file_name' => $filename,
				'file_type' => $uploaded_file_data['file_type'],
				'path' => '/modules/' . HMS_MODULE_NAME . '/uploads/rooms/' . $filename,
				'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
				'sort_order' => 999 // Set a high value to place it at the end by default
			];

			// Create thumbnail if it's a supported image
			$file_type = $uploaded_file_data['file_ext'];
			if (in_array($file_type, ['.jpg', '.jpeg', '.png']))
			{
				create_img_thumb($path, $filename);
			}

			$image_id = $CI->room_model->add_image($image_data);

			if ($image_id)
			{
				echo json_encode([
					'success' => TRUE,
					'message' => _l('image_uploaded_successfully'),
					'image_id' => $image_id,
					'image' => [
						'id' => $image_id,
						'room_id' => $room_id,
						'file_name' => $filename,
						'path' => base_url($path . $filename),
						'is_featured' => $image_data['is_featured']
					]
				]);
			} else
			{
				echo json_encode([
					'success' => FALSE,
					'message' => _l('image_upload_failed')
				]);
			}
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('no_image_selected')
			]);
		}
	}

	/**
	 * Delete room image
	 * @param integer $id image id
	 * @return json
	 */
	public function delete_image($id)
	{
		$response = $this->room_model->delete_image($id);

		if ($response)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('image'))
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_deleting', _l('image_lowercase'))
			]);
		}
	}

	/**
	 * Set featured image
	 * @param integer $id image id
	 * @return json
	 */
	public function set_featured_image($id)
	{
		$response = $this->room_model->set_featured_image($id);

		if ($response)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('featured_image_set')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('problem_setting_featured_image')
			]);
		}
	}

	/**
	 * Reorder room images
	 * @return void
	 */
	public function reorder_images()
	{
		$this->room_model->reorder_images($this->input->post());
	}

	/**
	 * Get rooms for property
	 * @param integer $property_id property id
	 * @return json
	 */
	public function get_property_rooms($property_id)
	{
		$rooms = $this->room_model->get_by_property($property_id);

		echo json_encode($rooms);
	}

	/**
	 * Get room availability
	 * @return json
	 */
	public function check_availability()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/rooms'));
		}

		$room_id = $this->input->post('room_id');
		$check_in = $this->input->post('check_in');
		$check_out = $this->input->post('check_out');
		$booking_id = $this->input->post('booking_id') ? $this->input->post('booking_id') : NULL;

		$is_available = $this->room_model->is_room_available($room_id, $check_in, $check_out, $booking_id);

		echo json_encode([
			'success' => TRUE,
			'available' => $is_available
		]);
	}

	/**
	 * Get occupancy report
	 * @param integer $room_id room id
	 * @return json
	 */
	public function occupancy_report($room_id)
	{
		$start_date = $this->input->post('start_date') ? $this->input->post('start_date') : date('Y-m-01');
		$end_date = $this->input->post('end_date') ? $this->input->post('end_date') : date('Y-m-t');

		$report = $this->room_model->get_occupancy_report($room_id, $start_date, $end_date);

		echo json_encode([
			'success' => TRUE,
			'data' => $report
		]);
	}

	/**
	 * Manage service assignments for room
	 * @param integer $room_id room id
	 * @return view
	 */
	public function service_assignments($room_id)
	{
		$data['room'] = $this->room_model->get_room_with_details($room_id);

		if ( ! $data['room'])
		{
			blank_page(_l('room_not_found'), 'danger');
		}

		// Get all staff for dropdown
		$this->load->model('staff_model');
		$data['staff'] = $this->staff_model->get('', ['active' => 1]);

		// Get all services for dropdown
		$this->load->model('hotel_management_system/service_model');
		$data['services'] = $this->service_model->get_all(['status' => 'active']);

		// Get days of week
		$data['days_of_week'] = hms_get_days_of_week();

		$data['title'] = _l('service_assignments_for_room', $data['room']->name);
		$this->load->view('hotel_management_system/admin/rooms/service_assignments', $data);
	}

	/**
	 * Add service assignment for room
	 * @return json
	 */
	public function add_service_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/rooms'));
		}

		$data = $this->input->post();

		// Add created info
		$data['datecreated'] = date('Y-m-d H:i:s');
		$data['created_by'] = get_staff_user_id();

		$this->db->insert(db_prefix() . 'hms_service_assignments', $data);
		$insert_id = $this->db->insert_id();

		if ($insert_id)
		{
			// Get staff and service info for response
			$this->db->select(db_prefix() . 'hms_service_assignments.*, ' .
				db_prefix() . 'hms_services.name as service_name, ' .
				db_prefix() . 'staff.firstname, ' .
				db_prefix() . 'staff.lastname');
			$this->db->from(db_prefix() . 'hms_service_assignments');
			$this->db->join(db_prefix() . 'hms_services', db_prefix() . 'hms_services.id = ' . db_prefix() . 'hms_service_assignments.service_id', 'left');
			$this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
			$this->db->where(db_prefix() . 'hms_service_assignments.id', $insert_id);
			$assignment = $this->db->get()->row_array();

			echo json_encode([
				'success' => TRUE,
				'message' => _l('service_assignment_added_successfully'),
				'assignment' => $assignment
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
	public function update_service_assignment()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/rooms'));
		}

		$id = $this->input->post('id');
		$data = $this->input->post();

		// Remove id from data
		unset($data['id']);

		// Add modified info
		$data['datemodified'] = date('Y-m-d H:i:s');
		$data['modified_by'] = get_staff_user_id();

		$this->db->where('id', $id);
		$this->db->update(db_prefix() . 'hms_service_assignments', $data);

		if ($this->db->affected_rows() > 0)
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
	public function delete_service_assignment($id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/rooms'));
		}

		$this->db->where('id', $id);
		$this->db->delete(db_prefix() . 'hms_service_assignments');

		if ($this->db->affected_rows() > 0)
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
	 * Delete room
	 * @param integer $id room id
	 * @return redirect
	 */
	public function delete($id)
	{
		$response = $this->room_model->delete($id);

		if (isset($response['success']) && $response['success'])
		{
			set_alert('success', $response['message']);
		} else
		{
			set_alert('warning', $response['message'] ?? _l('problem_deleting', _l('room_lowercase')));
		}

		redirect(admin_url('hotel_management_system/rooms'));
	}

	/**
	 * Get rooms table
	 * @return DataTable
	 */
	public function table()
	{
		$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/rooms/table'));
	}
}