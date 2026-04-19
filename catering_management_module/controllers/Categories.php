<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Catering_menu_categories_model $catering_menu_categories_model
 */
class Categories extends AdminController {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_menu_categories_model');
	}

	public function index(): void
	{
		if (staff_cant('view', 'catering_categories'))
		{
			access_denied('catering_categories');
		}

		$data['title'] = _l('menu_categories');
		$data['categories'] = $this->catering_menu_categories_model->get_all(['active' => 1], TRUE);
		$this->load->view('admin/categories/manage', $data);
	}

	public function table(): void
	{
		if (staff_cant('view', 'catering_categories'))
		{
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/categories'));
		} else
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/categories'));
			exit();
		}
	}

	public function category($id = '')
	{
		if (
			staff_cant('create', 'catering_categories')
			&& staff_cant('edit', 'catering_categories')
		)
		{
			access_denied('catering_categories');
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				if (staff_cant('create', 'catering_categories'))
				{
					access_denied('catering_categories');
				}
				$id = $this->catering_menu_categories_model->add($data);
				$message = $id ? _l('added_successfully', _l('category')) : _l('something_went_wrong');

				if ($id)
				{
					set_alert('success', $message);
					redirect(admin_url('catering_management_module/categories'));
				} else
				{
					set_alert('danger', $message);
				}
			} else
			{
				if (staff_cant('edit', 'catering_categories'))
				{
					access_denied('catering_categories');
				}
				$success = $this->catering_menu_categories_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('category')) : _l('something_went_wrong');

				set_alert($success ? 'success' : 'danger', $message);
				redirect(admin_url('catering_management_module/categories'));
			}
		}

		if ($id != '')
		{
			$data['category'] = $this->catering_menu_categories_model->get($id);
		}

		$data['parent_categories'] = $this->catering_menu_categories_model->get_parent_categories();
		$data['title'] = $id == '' ? _l('add_new_category') : _l('edit_category');
		$this->load->view('admin/categories/category', $data);
	}

	public function delete_category($id)
	{
		if (staff_cant('delete', 'catering_categories'))
		{
			access_denied('catering_categories');
		}

		$result = $this->catering_menu_categories_model->delete($id);

		if ($result['status'])
		{
			set_alert('success', _l('deleted', _l('category')));
		} else
		{
			set_alert('danger', $result['message']);
		}

		redirect(admin_url('catering_management_module/categories'));
	}

	public function update_category_order()
	{
		if (staff_cant('edit', 'catering_categories'))
		{
			ajax_access_denied();
		}

		$orders = $this->input->post('orders');
		$this->catering_menu_categories_model->update_display_orders($orders);

		echo json_encode(['success' => TRUE]);
	}

}