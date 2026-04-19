<?php

/**
 * @property Catering_menu_sections_model $catering_menu_sections_model
 */
class Sections extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_menu_sections_model');
	}

	public function index()
	{
		if (staff_cant('view', 'catering_menu_sections'))
		{
			access_denied('catering_menu_sections');
		}

		if ($this->input->is_ajax_request())
		{

		}

		$data['title'] = _l('menu_sections');
		$data['sections'] = $this->catering_menu_sections_model->get_all(['active' => 1]);
		$this->load->view('admin/sections/manage', $data);
	}


	public function table(): void
	{
		if (staff_cant('view', 'catering_menu_sections'))
		{
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/menu_sections'));
		} else
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/sections'));
			exit();
		}
	}

	public function section($id = '')
	{
		if (staff_cant('view', 'catering_menu_sections'))
		{
			access_denied('catering_menu_sections');
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				if (staff_cant('create', 'catering_menu_sections'))
				{
					access_denied('catering_menu_sections');
				}
				$id = $this->catering_menu_sections_model->add($data);
				$message = $id ? _l('added_successfully', _l('section')) : _l('something_went_wrong');
			} else
			{
				if (staff_cant('edit', 'catering_menu_sections'))
				{
					access_denied('catering_menu_sections');
				}
				$success = $this->catering_menu_sections_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('section')) : _l('something_went_wrong');
			}

			echo json_encode([
				'success' => $id ? TRUE : FALSE,
				'message' => $message,
				'id' => $id,
			]);

			return;
		}

		if ($id != '')
		{
			$data['section'] = $this->catering_menu_sections_model->get($id);
		}

		$data['title'] = $id == '' ? _l('add_new_section') : _l('edit_section');
		$this->load->view('admin/sections/section', $data);
	}

	public function delete_section($id)
	{
		if (staff_cant('delete', 'catering_menu_sections'))
		{
			access_denied('catering_menu_sections');
		}

		$result = $this->catering_menu_sections_model->delete($id);

		if ($result['status'])
		{
			set_alert('success', _l('deleted', _l('section')));
		} else
		{
			set_alert('danger', $result['message']);
		}

		redirect(admin_url('catering/sections'));
	}


	/**
	 * Toggle active status
	 */
	public function toggle_active()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/sections'));
			exit();
		}

		if (staff_cant('edit', 'catering_menu_sections'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('section_id'));
		$active = trim($this->input->post('active'));

		$success = $this->catering_menu_sections_model->update($id, ['active' => $active]);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_successfully', _l('section')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('something_went_wrong'),
			]);
		}
		exit();
	}


	public function update_section_order()
	{
		if (staff_cant('edit', 'catering_menu_sections'))
		{
			ajax_access_denied();
		}

		$orders = $this->input->post('orders');
		$this->catering_menu_sections_model->update_display_orders($orders);

		echo json_encode(['success' => TRUE]);
	}

}