<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Landlords extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_management_system/landlord_model');

	}

	/**
	 * List all landlords
	 * @return view
	 */
	public function index()
	{
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/landlords/table'));
		}

		$data['title'] = _l('hms_landlords');
		$this->load->view('hotel_management_system/admin/landlords/manage', $data);
	}

	/**
	 * View landlord details with properties
	 * @param integer $id landlord id
	 * @return view
	 */
	public function view($id)
	{
		$data['landlord_data'] = $this->landlord_model->get_landlord_with_properties($id);

		if ( ! $data['landlord_data'])
		{
			blank_page(_l('landlord_not_found'), 'danger');
		}

		$data['title'] = _l('landlord_details');
		$this->load->view('hotel_management_system/admin/landlords/view', $data);
	}

	/**
	 * Add new landlord or edit existing
	 * @param integer $id landlord id
	 * @return view
	 */
	public function landlord($id = '')
	{
		if ($this->input->post())
		{
			if ($id == '')
			{
				$id = $this->landlord_model->add($this->input->post());
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('landlord')));
					redirect(admin_url('hotel_management_system/landlords'));
				}
			} else
			{
				$success = $this->landlord_model->update($this->input->post(), $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('landlord')));
				}
				redirect(admin_url('hotel_management_system/landlords/landlord/' . $id));
			}
		}

		if ($id == '')
		{
			$title = _l('add_new', _l('landlord'));
			$data['landlord'] = NULL;
		} else
		{
			$landlord = $this->landlord_model->get($id);

			if ( ! $landlord)
			{
				blank_page(_l('landlord_not_found'), 'danger');
			}

			$data['landlord'] = $landlord;
			$title = _l('edit', _l('landlord_lowercase'));
		}

		$data['title'] = $title;
		$this->load->view('hotel_management_system/admin/landlords/landlord', $data);
	}

	/**
	 * Delete landlord
	 * @param integer $id landlord id
	 * @return redirect
	 */
	public function delete($id)
	{
		$response = $this->landlord_model->delete($id);

		if ($response['success'])
		{
			set_alert('success', $response['message']);
		} else
		{
			set_alert('warning', $response['message']);
		}

		redirect(admin_url('hotel_management_system/landlords'));
	}

	/**
	 * Change landlord status
	 * @param integer $id landlord id
	 * @param integer $status 0/1
	 * @return redirect
	 */
	public function change_status($id, $status)
	{
		$success = $this->landlord_model->change_status($id, $status);

		if ($success)
		{
			set_alert('success', _l('landlord_status_changed_successfully'));
		} else
		{
			set_alert('warning', _l('landlord_status_change_failed'));
		}

		redirect(admin_url('hotel_management_system/landlords'));
	}

	/**
	 * Search landlords
	 * @return json
	 */
	public function search()
	{
		if ($this->input->is_ajax_request())
		{
			$q = $this->input->post('q');

			if ($q)
			{
				$landlords = $this->landlord_model->search($q);
				$result = [];

				foreach ($landlords as $landlord)
				{
					$result[] = [
						'id' => $landlord['id'],
						'name' => $landlord['name'],
						'email' => $landlord['email'],
						'phone' => $landlord['phone'],
						'company' => $landlord['company'],
						'address' => $landlord['address'] . ', ' . $landlord['city'] . ', ' . $landlord['country']
					];
				}

				echo json_encode($result);
			}
		}
	}
}