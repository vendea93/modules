<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Catering_allergens_model $catering_allergens_model
 */
class Allergens extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_allergens_model');
	}

	/**
	 * List all allergens
	 */
	public function index()
	{
		if (staff_cant('view', 'catering_allergens'))
		{
			access_denied('catering_allergens');
		}

		$data['title'] = _l('allergens');
		$this->load->view('admin/allergens/manage', $data);
	}

	/**
	 * Get allergens data for table
	 */
	public function table()
	{
		if (staff_cant('view', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/allergens'));
		} else
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'));
			exit();
		}
	}

	/**
	 * Add new allergen
	 */
	public function add()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'));
			exit();
		}

		if (staff_cant('create', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$code = trim($this->input->post('code'));
		$label = trim($this->input->post('label'));
		$severity = trim($this->input->post('severity'));
		$icon = trim($this->input->post('icon'));
		$description = trim($this->input->post('description'));
		$display_order = trim($this->input->post('display_order'));
		$active = $this->input->post('active');

		$data = [
			'code' => $code,
			'label' => $label,
			'severity' => $severity,
			'icon' => $icon,
			'description' => $description,
			'display_order' => $display_order,
			'active' => $active,
		];

		$id = $this->catering_allergens_model->add($data);

		if ($id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('added_successfully', _l('allergen')),
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

	/**
	 * Update allergen
	 */
	public function update()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'));
			exit();
		}

		if (staff_cant('edit', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('allergen_id'));
		$code = trim($this->input->post('code'));
		$label = trim($this->input->post('label'));
		$severity = trim($this->input->post('severity'));
		$icon = trim($this->input->post('icon'));
		$description = trim($this->input->post('description'));
		$display_order = trim($this->input->post('display_order'));
		$active = $this->input->post('active');

		$data = [
			'code' => $code,
			'label' => $label,
			'severity' => $severity,
			'icon' => $icon,
			'description' => $description,
			'display_order' => $display_order,
			'active' => $active,
		];

		$success = $this->catering_allergens_model->update($id, $data);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_successfully', _l('allergen')),
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

	/**
	 * Delete allergen
	 */
	public function delete()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'));
			exit();
		}

		if (staff_cant('delete', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('allergen_id'));
		$result = $this->catering_allergens_model->delete($id);

		if ($result['status'])
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('allergen')),
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => $result['message'],
			]);
		}
		exit();
	}

	/**
	 * Toggle active status
	 */
	public function toggle_active()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'));
			exit();
		}

		if (staff_cant('edit', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('allergen_id'));
		$active = trim($this->input->post('active'));

		$success = $this->catering_allergens_model->update($id, ['active' => $active]);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_successfully', _l('allergen')),
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

	/**
	 * Get allergen data (for edit)
	 */
	public function get($id)
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		if (staff_cant('view', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		$allergen = $this->catering_allergens_model->get($id);

		if ($allergen)
		{
			echo json_encode([
				'success' => TRUE,
				'allergen' => $allergen,
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('allergen_not_found'),
			]);
		}
		exit();
	}

	/**
	 * Update display order
	 */
	public function update_order()
	{
		if (staff_cant('edit', 'catering_allergens'))
		{
			ajax_access_denied();
		}

		$orders = $this->input->post('orders');
		$this->catering_allergens_model->update_display_orders($orders);

		echo json_encode(['success' => TRUE]);
	}
}