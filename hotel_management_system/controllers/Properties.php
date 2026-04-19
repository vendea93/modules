<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Properties extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('property_model');
		$this->load->model('landlord_model');
	}

	/* List all properties */
	public function index()
	{
		$data['title'] = _l('hms_properties');
		$this->load->view('admin/properties/manage', $data);
	}

	/* Add new property or edit existing */
	public function property($id = '')
	{
		if ($this->input->post())
		{
			$data = $this->input->post();

			if ($id == '')
			{
				$id = $this->property_model->add($data);
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('hms_property')));
					redirect(admin_url('hotel_management_system/properties/property/' . $id));
				}
			} else
			{
				$success = $this->property_model->update($data, $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('hms_property')));
				}
				redirect(admin_url('hotel_management_system/properties/property/' . $id));
			}
		}

		if ($id == '')
		{
			$title = _l('add_new', _l('hms_property'));

			// Get all landlords for dropdown
			$data['landlords'] = $this->landlord_model->get();
		} else
		{
			$property = $this->property_model->get_property_with_rooms($id);
			if ( ! $property)
			{
				blank_page(_l('hms_property_not_found'), 'danger');
			}

			$data['property'] = $property;
			$data['landlords'] = $this->landlord_model->get();

			$title = $property->name;
		}

		$data['title'] = $title;
		$this->load->view('admin/properties/property', $data);
	}

	/* Delete property */
	public function delete($id)
	{
		if ( ! $id)
		{
			redirect(admin_url('properties'));
		}

		$response = $this->property_model->delete($id);
		if ($response)
		{
			set_alert('success', _l('deleted', _l('hms_property')));
		} else
		{
			set_alert('warning', _l('problem_deleting', _l('hms_property_lowercase')));
		}

		redirect(admin_url('properties'));
	}

	/* Upload property image */
	public function upload_image($property_id)
	{
		handle_property_image_upload($property_id);
	}

	/* Delete property image */
	public function delete_image($id)
	{
		$response = $this->property_model->delete_image($id);

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

	/* Set featured image */
	public function set_featured_image($id)
	{
		$response = $this->property_model->set_featured_image($id);

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

	/* Reorder images */
	public function reorder_images()
	{
		$this->property_model->reorder_images($this->input->post());
	}

	/* Get properties table */
	public function table()
	{
		$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/properties/table'));
	}

	/* Get property rooms table */
	public function rooms_table($property_id)
	{
		$this->app->get_table_data(module_views_path('hotel_management_system', 'tables/rooms'), [
			'property_id' => $property_id
		]);
	}
}

/**
 * Handle property image upload
 * @param integer $property_id Property ID
 * @return void
 */
function handle_property_image_upload($property_id)
{
	$CI = &get_instance();

	if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '')
	{
		$path = HMS_MODULE_UPLOAD_FOLDER . '/properties/';

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
		$filename = 'property_' . $property_id . '_' . md5(uniqid(rand(), TRUE)) . '.' . $extension;

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
			'property_id' => $property_id,
			'file_name' => $filename,
			'file_type' => $uploaded_file_data['file_type'],
			'path' => '/modules/' . HMS_MODULE_NAME . '/uploads/properties/' . $filename,
			'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
			'sort_order' => 999 // Set a high value to place it at the end by default
		];

		// Create thumbnail if it's a supported image
		$file_type = $uploaded_file_data['file_ext'];
		if (in_array($file_type, ['.jpg', '.jpeg', '.png']))
		{
			create_img_thumb($path, $filename);
		}

		$image_id = $CI->property_model->add_image($image_data);

		if ($image_id)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('image_uploaded_successfully'),
				'image_id' => $image_id,
				'image' => [
					'id' => $image_id,
					'property_id' => $property_id,
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