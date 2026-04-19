<?php

defined('BASEPATH') or exit('No direct script access allowed');

class License extends AdminController {
	protected const BASE_PATH = '/license';

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if ($this->input->post())
		{
			$posted_data = $this->input->post();
			$type = $posted_data['license_type'] ?? '';

			switch ($type)
			{
				case 'remove':
					Zegaware_License::remove_license(HMS_MODULE_NAME);

					set_alert('success', _l('zegaware_removed_success'));
					redirect(admin_url(HMS_MODULE_NAME . self::BASE_PATH . '/index?remove=1'));
					exit();

				case 'active':
					Zegaware_License::activate_license(HMS_MODULE_NAME, $posted_data);

					redirect(admin_url(HMS_MODULE_NAME . self::BASE_PATH . '/index?active=1'));
					exit();
			}
		}

		$data = [
			'title' => 'Hotel Management System License',
			'is_activated' => Zegaware_License::is_activated(HMS_MODULE_NAME),
			'activated_date' => Zegaware_License::get_activated_date(HMS_MODULE_NAME),
			'license_name' => get_option(HMS_MODULE_NAME . '_customer_name'),
			'license_email' => get_option(HMS_MODULE_NAME . '_customer_email'),
			'license_key' => Zegaware_License::get_license_key(HMS_MODULE_NAME),
		];

		$this->load->view(static::BASE_PATH . '/index', $data);
	}
}
