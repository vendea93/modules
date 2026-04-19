<?php

/**
 * @property Catering_menu_categories_model $catering_menu_categories_model
 * @property Catering_menu_items_model $catering_menu_items_model
 * @property Catering_dietary_types_model $catering_dietary_types_model
 * @property Catering_allergens_model $catering_allergens_model
 */
class Menu_items extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_menu_categories_model');
		$this->load->model('catering_menu_items_model');
		$this->load->model('catering_dietary_types_model');
		$this->load->model('catering_allergens_model');
	}

	/**
	 * Manage all menu items
	 *
	 * @return void
	 */
	public function index(): void
	{
		if (staff_cant('view', 'catering_menu_items'))
		{
			if ($this->input->is_ajax_request())
			{
				ajax_access_denied();
			} else
			{
				access_denied('catering_menu_items');
			}
		}

		$data['title'] = _l('menu_items');
		$data['categories'] = $this->catering_menu_categories_model->get_all(['active' => 1]);
		$this->load->view('admin/menu-items/manage', $data);
	}

	/**
	 * Get menu items data for table
	 */
	public function table(): void
	{
		if (staff_cant('view', 'catering_menu_items'))
		{
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/menu_items'));
		} else
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menu-items'));
			exit();
		}
	}

	/**
	 * Create/edit menu item
	 *
	 * @param $id
	 * @return void
	 */
	public function item($id = ''): void
	{
		if (
			staff_cant('create', 'catering_menu_items')
			&& staff_cant('edit', 'catering_menu_items')
		)
		{
			access_denied('catering_menu_items');
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				if (staff_cant('create', 'catering_menu_items'))
				{
					access_denied('catering_menu_items');
				}
				$id = $this->catering_menu_items_model->add($data);
				$message = $id ? _l('added_successfully', _l('menu_items')) : _l('something_went_wrong');

				if ($id)
				{
					set_alert('success', $message);
				} else
				{
					set_alert('danger', $message);
				}
			} else
			{
				if (staff_cant('edit', 'catering_menu_items'))
				{
					access_denied('catering_menu_items');
				}
				$success = $this->catering_menu_items_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('menu_items')) : _l('something_went_wrong');

				set_alert($success ? 'success' : 'danger', $message);
			}
		}

		if ($id != '')
		{
			$data['item'] = $this->catering_menu_items_model->get($id);
			if ( ! $data['item'])
			{
				show_404();
			}
		}

		$data['categories'] = $this->catering_menu_categories_model->get_all(['active' => 1]);
		$data['dietary_types'] = $this->catering_dietary_types_model->get_all(['active' => 1]);
		$data['allergens'] = $this->catering_allergens_model->get_all(['active' => 1]);
		$data['title'] = $id == '' ? _l('add_new_menu_item') : _l('edit_menu_item');
		$this->load->view('admin/menu-items/item', $data);
	}

	public function delete($id)
	{
		if (staff_cant('delete', 'catering_menu_items'))
		{
			if ($this->input->is_ajax_request())
			{
				ajax_access_denied();
			} else
			{
				access_denied('catering_menu_items');
			}
		}

		$result = $this->catering_menu_items_model->delete($id);

		if ($result['status'])
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('menu_items')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l($result['message']),
			]);
		}
	}

}