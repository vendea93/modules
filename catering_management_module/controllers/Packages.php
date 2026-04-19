<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Packages extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_packages_model');
		$this->load->model('catering_menu_items_model');
	}

	public function index()
	{
		if ( ! staff_can('view', 'catering_packages'))
		{
			access_denied('catering_packages');
		}

		$data['title'] = _l('packages');
		$this->load->view('admin/packages/manage', $data);
	}


	public function table()
	{
		if (staff_cant('view', 'catering_packages'))
		{
			ajax_access_denied();
		}
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('catering_management_module', 'admin/tables/packages'));
		} else
		{
			redirect(admin_url());
			exit();
		}
	}

	public function package($id = '')
	{
		if ($id != '' && ! staff_can('view', 'catering_packages'))
		{
			access_denied('catering_packages');
		}
		if ($id == '' && ! staff_can('create', 'catering_packages'))
		{
			access_denied('catering_packages');
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				$id = $this->catering_packages_model->add($data);
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('package')));
					redirect(admin_url('catering_management_module/packages/package/'.$id));
				} else
				{
					set_alert('danger', _l('something_went_wrong'));
					redirect(admin_url('catering_management_module/packages/package'));
				}
			} else
			{
				if ( ! staff_can('edit', 'catering_packages'))
				{
					access_denied('catering_packages');
				}
				$success = $this->catering_packages_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('package')) : _l('something_went_wrong');
				set_alert($success ? 'success' : 'danger', $message);
				redirect(admin_url('catering_management_module/packages/package/'.$id));
			}
		}

		if ($id != '')
		{
			$data['package'] = $this->catering_packages_model->get($id);
			if ( ! $data['package'])
			{
				blank_page(_l('package_not_found'));
			}
		}

		$data['items'] = $this->catering_menu_items_model->get_all(['active' => 1]);
		$data['title'] = ($id == '' ? _l('add_new_package') : _l('edit_package', $data['package']->package_name ?? ''));
		$this->load->view('admin/packages/package', $data);
	}

	public function delete_package($id)
	{
		if ( ! staff_can('delete', 'catering_packages'))
		{
			access_denied('catering_packages');
		}
		if ( ! $id)
		{
			redirect(admin_url('catering_management_module/packages'));
		}
		$response = $this->catering_packages_model->delete($id);
		if ($response == TRUE)
		{
			set_alert('success', _l('deleted', _l('package')));
		} else
		{
			set_alert('warning', _l('problem_deleting', _l('package_lowercase')));
		}
		redirect(admin_url('catering_management_module/packages'));
	}

	public function duplicate_package($id)
	{
		if ( ! staff_can('create', 'catering_packages'))
		{
			access_denied('catering_packages');
		}

		$new_id = $this->catering_packages_model->duplicate($id);

		if ($new_id)
		{
			set_alert('success', _l('package_duplicated_successfully'));
			redirect(admin_url('catering_management_module/packages/package/'.$new_id));
		} else
		{
			set_alert('danger', _l('something_went_wrong'));
			redirect(admin_url('catering_management_module/packages'));
		}
	}

	public function export_package_pdf($id)
	{
		if ( ! staff_can('view', 'catering_packages'))
		{
			access_denied('catering_packages');
		}

		$package = $this->catering_packages_model->get($id);
		if ( ! $package)
		{
			show_404();
		}

		try
		{
			$pdf = $this->catering_packages_model->get_package_pdf($package);
			$pdf->Output(slug_it($package->package_name).'.pdf', 'D');
		} catch (Exception $e)
		{
			log_activity('Failed to export package PDF: '.$e->getMessage());
			set_alert('danger', 'Could not export PDF. Please check system logs.');
			redirect(admin_url('catering_management_module/packages/package/'.$id));
		}
	}

	public function toggle_active($id, $state)
	{
		if ( ! staff_can('edit', 'catering_packages'))
		{
			access_denied('catering_packages');
		}
		$this->catering_packages_model->update($id, ['active' => $state]);
	}

	/* ==================== AJAX HELPERS ==================== */
	public function items_table()
	{
		if ( ! staff_can('view', 'catering_packages'))
		{
			ajax_access_denied();
		}
		$this->app->get_table_data(module_views_path('catering_management_module', 'admin/tables/package_items'));
	}

	public function add_multiple_items()
	{
		if ( ! staff_can('edit', 'catering_packages'))
		{
			ajax_access_denied();
		}
		$package_id = $this->input->post('package_id');
		$item_ids = $this->input->post('item_ids');
		$count = $this->catering_packages_model->add_multiple_items($package_id, $item_ids);

		echo json_encode([
			'success' => $count > 0,
			'message' => _l('added_items_to_package', $count),
		]);
	}

	public function get_package_items($id)
	{
		if ( ! staff_can('view', 'catering_packages'))
		{
			ajax_access_denied();
		}
		$data['items'] = $this->catering_packages_model->get_package_items($id);
		$this->load->view('admin/packages/package_items_list', $data);
	}

	public function calculate_package_cost()
	{
		if ( ! $this->input->is_ajax_request() || ! staff_can('view', 'catering_packages'))
		{
			ajax_access_denied();
		}

		$package_id = $this->input->post('package_id');
		$cost = $this->catering_packages_model->calculate_cost_per_person($package_id);

		echo json_encode(['cost' => $cost]);
	}

	public function get_packages_for_guests()
	{
		if ( ! $this->input->is_ajax_request())
		{
			show_404();
		}

		$guest_count = $this->input->post('guest_count');
		$packages = $this->catering_packages_model->get_for_guest_count($guest_count);

		echo json_encode(['packages' => $packages]);
	}

	public function add_item_to_package()
	{
		if ( ! $this->input->is_ajax_request() || ! staff_can('edit', 'catering_packages'))
		{
			ajax_access_denied();
		}

		$package_id = $this->input->post('package_id');
		$item_id = $this->input->post('item_id');
		$qty_per_guest = $this->input->post('qty_per_guest', 1);

		$link_id = $this->catering_packages_model->add_item($package_id, $item_id, $qty_per_guest);

		echo json_encode([
			'success' => $link_id ? TRUE : FALSE,
			'link_id' => $link_id,
			'message' => $link_id ? _l('item_added_successfully') : _l('something_went_wrong'),
		]);
	}

	public function remove_item_from_package()
	{
		if ( ! $this->input->is_ajax_request() || ! staff_can('edit', 'catering_packages'))
		{
			ajax_access_denied();
		}

		$link_id = $this->input->post('link_id');
		$success = $this->catering_packages_model->remove_item($link_id);

		echo json_encode([
			'success' => $success,
			'message' => $success ? _l('item_removed_successfully') : _l('something_went_wrong'),
		]);
	}

	public function update_package_item_quantity()
	{
		if ( ! $this->input->is_ajax_request() || ! staff_can('edit', 'catering_packages'))
		{
			ajax_access_denied();
		}

		$link_id = $this->input->post('link_id');
		$qty_per_guest = $this->input->post('qty_per_guest');

		$success = $this->catering_packages_model->update_item_quantity($link_id, $qty_per_guest);

		echo json_encode([
			'success' => $success,
			'message' => $success ? _l('updated_successfully') : _l('something_went_wrong'),
		]);
	}
}
