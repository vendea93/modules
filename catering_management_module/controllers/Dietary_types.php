<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Catering_dietary_types_model $catering_dietary_types_model
 */
class Dietary_types extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_dietary_types_model');
	}

	/**
	 * List all dietary types
	 */
	public function index()
	{
		if (staff_cant('view', 'catering_dietary_types')) {
			access_denied('catering_dietary_types');
		}

		$data['title'] = _l('dietary_types');
		$this->load->view('admin/dietary_types/manage', $data);
	}

	/**
	 * Get dietary types data for table
	 */
	public function table()
	{
		if (staff_cant('view', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		if ($this->input->is_ajax_request()) {
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/dietary_types'));
		} else {
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME . '/dietary_types'));
			exit();
		}
	}

	/**
	 * Add new dietary type
	 */
	public function add()
	{
		if (!$this->input->is_ajax_request()) {
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME . '/dietary_types'));
			exit();
		}

		if (staff_cant('create', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		if (!$this->input->post()) {
			ajax_access_denied();
			exit();
		}

		$code = trim($this->input->post('code'));
		$label = trim($this->input->post('label'));
		$icon = trim($this->input->post('icon'));
		$description = trim($this->input->post('description'));
		$display_order = trim($this->input->post('display_order'));
		$active = $this->input->post('active');

		$data = [
			'code' => $code,
			'label' => $label,
			'icon' => $icon,
			'description' => $description,
			'display_order' => $display_order,
			'active' => $active
		];

		$id = $this->catering_dietary_types_model->add($data);

		if ($id) {
			echo json_encode([
				'success' => true,
				'message' => _l('added_successfully', _l('dietary_type'))
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('something_went_wrong')
			]);
		}
		exit();
	}

	/**
	 * Update dietary type
	 */
	public function update()
	{
		if (!$this->input->is_ajax_request()) {
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME . '/dietary_types'));
			exit();
		}

		if (staff_cant('edit', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		if (!$this->input->post()) {
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('dietary_type_id'));
		$code = trim($this->input->post('code'));
		$label = trim($this->input->post('label'));
		$icon = trim($this->input->post('icon'));
		$description = trim($this->input->post('description'));
		$display_order = trim($this->input->post('display_order'));
		$active = $this->input->post('active');

		$data = [
			'code' => $code,
			'label' => $label,
			'icon' => $icon,
			'description' => $description,
			'display_order' => $display_order,
			'active' => $active
		];

		$success = $this->catering_dietary_types_model->update($id, $data);

		if ($success) {
			echo json_encode([
				'success' => true,
				'message' => _l('updated_successfully', _l('dietary_type'))
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('something_went_wrong')
			]);
		}
		exit();
	}

	/**
	 * Delete dietary type
	 */
	public function delete()
	{
		if (!$this->input->is_ajax_request()) {
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME . '/dietary_types'));
			exit();
		}

		if (staff_cant('delete', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		if (!$this->input->post()) {
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('dietary_type_id'));
		$result = $this->catering_dietary_types_model->delete($id);

		if ($result['status']) {
			echo json_encode([
				'success' => true,
				'message' => _l('deleted', _l('dietary_type'))
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => $result['message']
			]);
		}
		exit();
	}

	/**
	 * Toggle active status
	 */
	public function toggle_active()
	{
		if (!$this->input->is_ajax_request()) {
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME . '/dietary_types'));
			exit();
		}

		if (staff_cant('edit', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		if (!$this->input->post()) {
			ajax_access_denied();
			exit();
		}

		$id = trim($this->input->post('dietary_type_id'));
		$active = trim($this->input->post('active'));

		$success = $this->catering_dietary_types_model->update($id, ['active' => $active]);

		if ($success) {
			echo json_encode([
				'success' => true,
				'message' => _l('updated_successfully', _l('dietary_type'))
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('something_went_wrong')
			]);
		}
		exit();
	}

	/**
	 * Get dietary type data (for edit)
	 */
	public function get($id)
	{
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		if (staff_cant('view', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		$dietary_type = $this->catering_dietary_types_model->get($id);

		if ($dietary_type) {
			echo json_encode([
				'success' => true,
				'dietary_type' => $dietary_type
			]);
		} else {
			echo json_encode([
				'success' => false,
				'message' => _l('dietary_type_not_found')
			]);
		}
		exit();
	}

	/**
	 * Update display order
	 */
	public function update_order()
	{
		if (staff_cant('edit', 'catering_dietary_types')) {
			ajax_access_denied();
		}

		$orders = $this->input->post('orders');
		$this->catering_dietary_types_model->update_display_orders($orders);

		echo json_encode(['success' => true]);
	}
}