<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Catering_event_types_model|null $catering_event_types_model
 */
class Event_types extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_event_types_model');
	}

	/* List all announcements */
	public function index()
	{
		if (staff_cant('view', 'catering_event_types'))
		{
			access_denied('catering_event_types');
		}
		$this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');
		$data['title'] = _l('event_types');
		$this->load->view('admin/event_types/manage', $data);
	}

	public function table()
	{
		if (staff_cant('view', 'catering_event_types'))
		{
			ajax_access_denied();
		}
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('catering_management_module', 'admin/tables/event_types'));
		} else
		{
			redirect(admin_url());
			exit();
		}
	}

	public function add()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url());
			exit();
		}
		if (staff_cant('view', 'catering_event_types'))
		{
			ajax_access_denied();
		}
		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$type_name = trim($this->input->post('name'));
		$background_color = trim($this->input->post('background_color'));
		$text_color = trim($this->input->post('text_color'));
		$sort_order = trim($this->input->post('sort_order'));

		$event_type = $this->catering_event_types_model->get_by_name($type_name);
		if ( ! empty($event_type))
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('event_type_exists'),
			]);
			exit();
		}

		$data = [
			'name' => $type_name,
			'background_color' => $background_color,
			'text_color' => $text_color,
			'sort_order' => $sort_order,
		];

		$this->catering_event_types_model->create($data);

		echo json_encode([
			'success' => TRUE,
			'message' => _l('added_successfully', _l('event_type')),
		]);
		exit();
	}

	public function update()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url());
			exit();
		}
		if (staff_cant('view', 'catering_event_types'))
		{
			ajax_access_denied();
		}
		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$event_type_id = trim($this->input->post('edit_event_type'));
		$type_name = trim($this->input->post('name'));
		$background_color = trim($this->input->post('background_color'));
		$text_color = trim($this->input->post('text_color'));
		$sort_order = trim($this->input->post('sort_order'));

		$data = [
			'name' => $type_name,
			'background_color' => $background_color,
			'text_color' => $text_color,
			'sort_order' => $sort_order,
		];

		$result = $this->catering_event_types_model->update($event_type_id, $data);

		if ($result)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_successfully', _l('event_type')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('update_event_type_failed'),
			]);
		}
	}

	public function destroy()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url());
			exit();
		}
		if (staff_cant('view', 'catering_event_types'))
		{
			ajax_access_denied();
		}
		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}
		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$type_id = trim($this->input->post('event_type_id'));

		$event_type = $this->catering_event_types_model->get($type_id);
		if ($event_type && $event_type['editable'] == 0)
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('can_not_delete_this_event_type'),
			]);
			exit();
		}

		$this->catering_event_types_model->delete($type_id);

		echo json_encode([
			'success' => TRUE,
			'message' => _l('deleted', _l('event_type')),
		]);
		exit();
	}

}