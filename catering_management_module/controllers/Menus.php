<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Catering_menus_model $catering_menus_model
 * @property Catering_menu_categories_model $catering_menu_categories_model
 * @property Catering_menu_sections_model $catering_menu_sections_model
 * @property Catering_menu_items_model $catering_menu_items_model
 */
class Menus extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('catering_menus_model');
		$this->load->model('catering_menu_categories_model');
		$this->load->model('catering_menu_sections_model');
		$this->load->model('catering_menu_items_model');
	}

	/**
	 * List all menus
	 */
	public function index()
	{
		if (staff_cant('view', 'catering_menus'))
		{
			access_denied('catering_menus');
		}

		$data['title'] = _l('menus');
		$this->load->view('admin/menus/manage', $data);
	}

	/**
	 * Get menus data for table
	 */
	public function table()
	{
		if (staff_cant('view', 'catering_menus'))
		{
			ajax_access_denied();
		}


		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path(CATERING_MANAGEMENT_MODULE_NAME, 'admin/tables/menus'));
		} else
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'));
			exit();
		}
	}

	/**
	 * View/Edit menu
	 */
	public function menu($id = '')
	{
		if (staff_cant('view', 'catering_menus'))
		{
			access_denied('catering_menus');
		}

		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				if (staff_cant('create', 'catering_menus'))
				{
					access_denied('catering_menus');
				}
				$id = $this->catering_menus_model->add($data);
				$message = $id ? _l('added_successfully', _l('menu')) : _l('something_went_wrong');

				if ($id)
				{
					set_alert('success', $message);
				} else
				{
					set_alert('danger', $message);
				}
			} else
			{
				if (staff_cant('edit', 'catering_menus'))
				{
					access_denied('catering_menus');
				}
				$success = $this->catering_menus_model->update($id, $data);
				$message = $success ? _l('updated_successfully', _l('menu')) : _l('something_went_wrong');

				set_alert($success ? 'success' : 'danger', $message);
			}
		}

		if ($id != '')
		{
			$data['menu'] = $this->catering_menus_model->get($id);
			if ( ! $data['menu'])
			{
				show_404();
			}
		}

		$data['sections'] = $this->catering_menu_sections_model->get_all(['active' => 1]);
		$data['items'] = $this->catering_menu_items_model->get_all(['active' => 1]);
		$data['title'] = $id == '' ? _l('add_new_menu') : _l('edit_menu');

		if (isset($data['menu']))
		{
			$sections = $data['menu']->items ?? [];
			$items_count = array_sum(array_map(function ($section) {
				return count($section['items']);
			}, $sections));
			$data['menu_items_count'] = $items_count;
		}
		$this->load->view('admin/menus/menu', $data);
	}

	/**
	 * Delete menu
	 */
	public function delete()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'));
			exit();
		}

		if (staff_cant('delete', 'catering_menus'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$menu_id = trim($this->input->post('menu_id'));
		$result = $this->catering_menus_model->delete($menu_id);

		if ($result['status'])
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('deleted', _l('menu')),
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
	 * Duplicate menu
	 */
	public function duplicate()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'));
			exit();
		}

		if (staff_cant('create', 'catering_menus'))
		{
			access_denied('catering_menus');
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$menu_id = trim($this->input->post('menu_id'));
		$new_name = trim($this->input->post('new_name', ''));

		$new_id = $this->catering_menus_model->duplicate($menu_id, $new_name);

		if ($new_id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('menu_duplicated_successfully'),
				'redirect' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/menu/'.$new_id),
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
	 * Toggle menu active status
	 */
	public function toggle_active()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'));
			exit();
		}

		if (staff_cant('edit', 'catering_menus'))
		{
			ajax_access_denied();
		}

		if ( ! $this->input->post())
		{
			ajax_access_denied();
			exit();
		}

		$menu_id = trim($this->input->post('menu_id'));
		$active = trim($this->input->post('active'));

		$success = $this->catering_menus_model->update($menu_id, ['active' => $active]);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('updated_successfully', _l('menu')),
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
	 * AJAX: Add item to menu
	 */
	public function add_item_to_menu()
	{
		if (staff_cant('edit', 'catering_menus'))
		{
			ajax_access_denied();
		}

		$menu_id = $this->input->post('menu_id');
		$item_id = $this->input->post('item_id');
		$section_id = $this->input->post('section_id');
		$position = $this->input->post('position');

		$link_id = $this->catering_menus_model->add_item($menu_id, $item_id, $section_id, $position);

		echo json_encode([
			'success' => $link_id ? TRUE : FALSE,
			'link_id' => $link_id,
			'message' => $link_id ? _l('item_added_successfully') : _l('something_went_wrong'),
		]);
	}

	/**
	 * AJAX: Remove item from menu
	 */
	public function remove_item_from_menu()
	{
		if (staff_cant('edit', 'catering_menus'))
		{
			ajax_access_denied();
		}

		$link_id = $this->input->post('link_id');
		$success = $this->catering_menus_model->remove_item($link_id);

		echo json_encode([
			'success' => $success,
			'message' => $success ? _l('item_removed_successfully') : _l('something_went_wrong'),
		]);
	}

	/**
	 * AJAX: Update item positions
	 */
	public function update_menu_item_positions()
	{
		if (staff_cant('edit', 'catering_menus'))
		{
			ajax_access_denied();
		}

		$positions = $this->input->post('positions');
		$this->catering_menus_model->update_item_positions($positions);

		echo json_encode(['success' => TRUE]);
	}

	/**
	 * Export menu to PDF
	 */
	public function export_pdf($id)
	{
		if (staff_cant('view', 'catering_menus'))
		{
			ajax_access_denied();
		}

		$menu = $this->catering_menus_model->get($id);
		if ( ! $menu)
		{
			show_404();
		}

		$this->load->library('pdf');

		$html = $this->load->view('admin/pdf/menu', ['menu' => $menu], TRUE);

		$this->pdf->loadHtml($html);
		$this->pdf->render();
		$this->pdf->stream('menu_'.$id.'_'.date('Y-m-d').'.pdf');
	}
}