<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bookings extends AdminController {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('hotel_management_system/booking_model');
		$this->load->model('hotel_management_system/room_model');
		$this->load->model('hotel_management_system/property_model');
		$this->load->model('hotel_management_system/service_model');
		$this->load->model('clients_model');
	}

	/**
	 * List all bookings
	 * @return view
	 */
	public function index()
	{
		if ($this->input->is_ajax_request())
		{
			$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/bookings/table'));
		}

		$data['title'] = _l('hms_bookings');
		$this->load->view('hotel_management_system/admin/bookings/manage', $data);
	}

	/**
	 * View booking details
	 * @param integer $id booking id
	 * @return view
	 */
	public function view($id)
	{
		$data['booking'] = $this->booking_model->get_booking_with_details($id);

		if ( ! $data['booking'])
		{
			blank_page(_l('booking_not_found'), 'danger');
		}
		$data['booking_statuses'] = hms_get_booking_statuses();
		$data['payment_statuses'] = hms_get_payment_statuses();
		$data['title'] = _l('booking_details') . ' - ' . $data['booking']->booking_reference;
		$this->load->view('hotel_management_system/admin/bookings/view', $data);
	}

	/**
	 * Add new booking or edit existing
	 * @param integer $id booking id
	 * @return view
	 */
	public function booking($id = '')
	{
		if ($this->input->post())
		{
			$data = $this->input->post();
			$property_id = $data['property_id'];
			unset($data['property_id']);

			// Calculate total nights
			$check_in = new DateTime($data['check_in_date']);
			$check_out = new DateTime($data['check_out_date']);
			$interval = $check_in->diff($check_out);
			$data['total_nights'] = $interval->days;

			// Process services
			$services = [];
			if (isset($data['services']))
			{
				foreach ($data['services'] as $service_id => $service)
				{
					if (isset($service['selected']) && $service['selected'] == 1 && isset($service['quantity']) && $service['quantity'] > 0)
					{
						$services[$service_id] = [
							'service_name' => hms_get_service_name($service_id),
							'price' => hms_get_service_price($service_id),
							'quantity' => intval($service['quantity']),
						];
					}
				}
				$data['services'] = $services;
			}

			$client_id = $data['client_id'];
			if (empty($client_id) && $id)
			{
				set_alert('warning', _l('client_not_found'));
				redirect(admin_url('hotel_management_system/bookings/booking/' . $id));
				exit();
			}
			$price_calculation = $this->calculate_booking_price($data);

			if ( ! $price_calculation['success'])
			{
				set_alert('warning', $price_calculation['message']);
				if ($id)
				{
					redirect(admin_url('hotel_management_system/bookings/booking/' . $id));
				} else
				{
					redirect(admin_url('hotel_management_system/bookings/booking'));
				}
				exit();
			}

			// Set calculated price values
			$data['room_price'] = $price_calculation['data']['room_price'];
			$data['cleaning_fee'] = $price_calculation['data']['cleaning_fee'];
			$data['additional_services'] = $price_calculation['data']['additional_services'];
			$data['taxes'] = $price_calculation['data']['taxes'];
			$data['total_amount'] = $price_calculation['data']['total_amount'];
//			$data['tax_rate'] = $price_calculation['data']['tax_rate'];


			$room_id = $data['room_id'];
			unset($data['room_id']);
			if (is_array($room_id))
			{
				$rooms = array_map(function ($room_id) {
					return $this->room_model->get($room_id, TRUE);
				}, $room_id);
			} else
			{
				$rooms = [$this->room_model->get($room_id, TRUE)];
			}
			$room_general_data = [
				'check_in_date' => $data['check_in_date'],
				'check_out_date' => $data['check_out_date'],
				'total_nights' => $data['total_nights'],
			];
			$rooms_data = array_map(function ($room) use ($room_general_data) {
				$rooms_price = floatval($room->price_per_night);
				$total_room_price = intval($room_general_data['total_nights']) * $rooms_price;
				$room_tax = floatval($total_room_price) * floatval($room->tax_rate) / 100.0;

				return array_merge($room_general_data, [
					'room_id' => $room->id,
					'room_price' => $total_room_price,
					'cleaning_fee' => $room->cleaning_fee,
					'taxes' => $room_tax
				]);
			}, $rooms);
			$data['rooms'] = $rooms_data;

			if ($id == '')
			{
				$customer_email = $data['guest_email'];
				if (empty($client_id) && empty($customer_email))
				{
					set_alert('warning', _l('client_not_found'));
					redirect(admin_url('hotel_management_system/bookings/booking'));
					exit();
				} elseif (empty($client_id))
				{
					$contact = $this->clients_model->get_contact_by_email($customer_email);
					if ($contact)
					{
						$data['client_id'] = $contact->userid;
					} else
					{
						$property = $this->property_model->get($property_id);
						$customer_data = [
							'company' => empty($data['guest_name']) ? ($property->name . '\'s customers') : $data['guest_name'],
							'phonenumber' => $data['guest_phone'],
						];
						$client_id = $this->clients_model->add($customer_data);
						if ($client_id)
						{
							$contact = [
								'firstname' => $data['guest_name'] ?? 'Guest ' . $client_id . ' First Name',
								'lastname' => $data['guest_name'] ?? 'Guest ' . $client_id . ' Last Name',
								'title' => '',
								'email' => $customer_email,
								'phonenumber' => $data['guest_phone'] ?? '',
								'direction' => '',
								'fakeusernameremembered' => '',
								'fakepasswordremembered' => '',
								'password' => $this->generate_random_string(16),
								'donotsendwelcomeemail' => 'on',
							];
							$contact_id = $this->clients_model->add_contact($contact, $client_id);
							$data['client_id'] = $client_id;
						} else
						{
							set_alert('warning', _l('client_not_found'));
							redirect(admin_url('hotel_management_system/bookings/booking'));
							exit();
						}
					}
				}


				$id = $this->booking_model->add($data);
				if ($id)
				{
					set_alert('success', _l('added_successfully', _l('booking')));
					redirect(admin_url('hotel_management_system/bookings/view/' . $id));
				}
			} else
			{
				$success = $this->booking_model->update($data, $id);
				if ($success)
				{
					set_alert('success', _l('updated_successfully', _l('booking')));
				}
				redirect(admin_url('hotel_management_system/bookings/view/' . $id));
			}
		}

		if ($id == '')
		{
			$title = _l('add_new', _l('booking'));
			$data['booking'] = NULL;
		} else
		{
			$booking = $this->booking_model->get_booking_with_details($id);

			if ( ! $booking)
			{
				blank_page(_l('booking_not_found'), 'danger');
			}

			$data['booking'] = $booking;
			$title = _l('edit', _l('booking'));
		}

		// Get properties for dropdown
		$data['properties'] = $this->property_model->get_all(['status' => 'active']);

		// Get all rooms if editing or rooms for specific property if available
		if ($id != '' && isset($booking->room_id))
		{
			$this->db->where('id', $booking->room_id);
			$room = $this->db->get(db_prefix() . 'hms_rooms')->row();

			if ($room)
			{
				$data['rooms'] = $this->room_model->get_by_property($room->property_id);
				$data['property_id'] = $room->property_id;
			} else
			{
				$data['rooms'] = [];
				$data['property_id'] = 0;
			}
		} else
		{
			$data['rooms'] = [];
			$data['property_id'] = 0;
		}

		// Get services
		$data['services'] = $this->service_model->get_all(['status' => 'active']);

		// Get booking statuses and payment statuses
		$data['booking_statuses'] = hms_get_booking_statuses();
		$data['payment_statuses'] = hms_get_payment_statuses();

		// Load clients model for dropdown
		$this->load->model('clients_model');
		$data['clients'] = $this->clients_model->get();

		$data['title'] = $title;
		$this->load->view('hotel_management_system/admin/bookings/booking', $data);
	}

	/**
	 * Delete booking
	 * @param integer $id booking id
	 * @return redirect
	 */
	public function delete($id)
	{
		$response = $this->booking_model->delete($id);

		if (isset($response['success']) && $response['success'])
		{
			set_alert('success', $response['message']);
		} else
		{
			set_alert('warning', $response['message'] ?? _l('problem_deleting', _l('booking_lowercase')));
		}

		redirect(admin_url('hotel_management_system/bookings'));
	}

	/**
	 * Change booking status
	 * @return json
	 */
	public function change_status()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$booking_id = $this->input->post('booking_id');
		$status = $this->input->post('status');

		$success = $this->booking_model->change_status($booking_id, $status);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('booking_status_changed_successfully')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('booking_status_change_failed')
			]);
		}
	}

	/**
	 * Change payment status
	 * @return json
	 */
	public function change_payment_status()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$booking_id = $this->input->post('booking_id');
		$status = $this->input->post('status');

		$success = $this->booking_model->change_payment_status($booking_id, $status);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('payment_status_changed_successfully')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('payment_status_change_failed')
			]);
		}
	}

	/**
	 * Check-in booking
	 * @param integer $id booking id
	 * @return redirect
	 */
	public function check_in($id)
	{
		$success = $this->booking_model->check_in($id);

		if ($success)
		{
			set_alert('success', _l('booking_checked_in_successfully'));
		} else
		{
			set_alert('warning', _l('booking_check_in_failed'));
		}

		redirect(admin_url('hotel_management_system/bookings/view/' . $id));
	}

	/**
	 * Check-out booking
	 * @param integer $id booking id
	 * @return redirect
	 */
	public function check_out($id)
	{
		$success = $this->booking_model->check_out($id);

		if ($success)
		{
			set_alert('success', _l('booking_checked_out_successfully'));
		} else
		{
			set_alert('warning', _l('booking_check_out_failed'));
		}

		redirect(admin_url('hotel_management_system/bookings/view/' . $id));
	}

	/**
	 * Generate invoice for booking
	 * @param integer $id booking id
	 * @return redirect
	 */
	public function generate_invoice($id)
	{
		$invoice_id = hms_generate_invoice($id);

		if ($invoice_id)
		{
			set_alert('success', _l('invoice_generated_successfully'));
			redirect(admin_url('invoices/invoice/' . $invoice_id));
		} else
		{
			set_alert('warning', _l('invoice_generation_failed'));
			redirect(admin_url('hotel_management_system/bookings/view/' . $id));
		}
	}

	/**
	 * Get property rooms
	 * @return json
	 */
	public function get_property_rooms($property_id = '')
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$rooms = $this->property_model->get_property_rooms($property_id);

		echo json_encode([
			'success' => TRUE,
			'rooms' => $rooms
		]);
	}

	/**
	 * Check room availability
	 * @return json
	 */
	public function check_availability()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$room_id = $this->input->post('room_id');
		$check_in = $this->input->post('check_in');
		$check_out = $this->input->post('check_out');
		$booking_id = $this->input->post('booking_id') ? $this->input->post('booking_id') : NULL;

		$is_available = $this->room_model->is_room_available($room_id, $check_in, $check_out, $booking_id);

		echo json_encode([
			'success' => TRUE,
			'available' => $is_available
		]);
	}

	/**
	 * Calculate price - AJAX endpoint
	 * @return json
	 */
	public function calculate_price()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$data = $this->input->post();

		// Format dates correctly
		if (isset($data['check_in']) && ! isset($data['check_in_date']))
		{
			$data['check_in_date'] = $data['check_in'];
			unset($data['check_in']);
		}

		if (isset($data['check_out']) && ! isset($data['check_out_date']))
		{
			$data['check_out_date'] = $data['check_out'];
			unset($data['check_out']);
		}

		$calculation = $this->calculate_booking_price($data);

		if ($calculation['success'])
		{
			echo json_encode([
				'success' => TRUE,
				'calculation' => $calculation['data']
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => $calculation['message']
			]);
		}
	}

	/**
	 * Update booking status
	 */
	public function update_status()
	{
		if ($this->input->is_ajax_request())
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('booking_status_update_failed')
			]);
		}

		$booking_id = $this->input->post('booking_id');
		$status = $this->input->post('status');

		$success = $this->booking_model->update_status($booking_id, $status);
		if ($success)
		{
			set_alert('success', _l('booking_status_updated'));

		} else
		{
			set_alert('warning', _l('booking_status_update_failed'));
		}

		return redirect(admin_url('hotel_management_system/bookings/view/' . $booking_id));

	}

	/**
	 * Update booking status
	 */
	public function update_payment_status()
	{
		if ($this->input->is_ajax_request())
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('booking_status_update_failed')
			]);
		}

		$booking_id = $this->input->post('booking_id');
		$status = $this->input->post('status');

		$success = $this->booking_model->update_payment_status($booking_id, $status);
		if ($success)
		{
			set_alert('success', _l('payment_status_updated'));

		} else
		{
			set_alert('warning', _l('payment_status_update_failed'));
		}

		return redirect(admin_url('hotel_management_system/bookings/view/' . $booking_id));

	}

	/**
	 * Update service status
	 * @return json
	 */
	public function update_service_status()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$service_id = $this->input->post('service_id');
		$status = $this->input->post('status');

		$success = $this->booking_model->update_service_status($service_id, $status);

		if ($success)
		{
			echo json_encode([
				'success' => TRUE,
				'message' => _l('service_status_updated_successfully')
			]);
		} else
		{
			echo json_encode([
				'success' => FALSE,
				'message' => _l('service_status_update_failed')
			]);
		}
	}

	/**
	 * Calendar view
	 * @return view
	 */
	public function calendar()
	{
		$data['properties'] = $this->property_model->get_all();
		$data['title'] = _l('bookings_calendar');
		$this->load->view('hotel_management_system/admin/bookings/calendar', $data);
	}

	/**
	 * Get calendar data
	 * @return json
	 */
	public function get_calendar_data()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$start = $this->input->get('start');
		$end = $this->input->get('end');

		$events = $this->booking_model->get_calendar_data($start, $end);

		echo json_encode($events);
	}

	/**
	 * Dashboard
	 * @return view
	 */
	public function dashboard()
	{
		$data['title'] = _l('hms_dashboard');

		// Get booking statistics
		$start_date = date('Y-m-01'); // First day of current month
		$end_date = date('Y-m-t'); // Last day of current month

		$data['statistics'] = $this->booking_model->get_booking_statistics($start_date, $end_date);

		// Get today's arrivals and departures
		$data['today'] = $this->booking_model->get_today_arrivals_departures();

		// Get recent bookings
		$data['recent_bookings'] = $this->booking_model->get_recent_bookings(5);

		$this->load->view('hotel_management_system/admin/bookings/dashboard', $data);
	}

	/**
	 * Search bookings
	 * @return json
	 */
	public function search()
	{
		if ( ! $this->input->is_ajax_request())
		{
			redirect(admin_url('hotel_management_system/bookings'));
		}

		$q = $this->input->post('q');

		$bookings = $this->booking_model->search($q);

		echo json_encode([
			'success' => TRUE,
			'results' => $bookings
		]);
	}

	/**
	 * Get bookings table
	 * @return DataTable
	 */
	public function table()
	{
		$this->app->get_table_data(module_views_path('hotel_management_system', 'admin/bookings/table'));
	}

	/**
	 * Calculate booking price
	 * @param array $data Booking data
	 * @return array
	 */
	private function calculate_booking_price($data)
	{
		$room_ids = $data['room_id'];
		// Get room details
		$this->db->where_in('id', $room_ids);
		$rooms = $this->db->get(db_prefix() . 'hms_rooms')->result();

		if (empty($rooms))
		{
			return [
				'success' => FALSE,
				'message' => 'Room not found'
			];
		}

		// Calculate total nights
		$check_in = new DateTime($data['check_in_date']);
		$check_out = new DateTime($data['check_out_date']);
		$interval = $check_in->diff($check_out);
		$nights = $interval->days;

		if ($nights < 1)
		{
			return [
				'success' => FALSE,
				'message' => 'Check-out date must be after check-in date'
			];
		}

		// Calculate room cost
		$room_price = $nights * array_sum(array_column($rooms, 'price_per_night'));

		// Add cleaning fee
		$cleaning_fee = array_sum(array_column($rooms, 'cleaning_fee'));

		// Calculate additional services cost
		$additional_services = 0;
		$services_details = [];

		if (isset($data['services']) && is_array($data['services']))
		{
			foreach ($data['services'] as $service_id => $service_data)
			{
				// Get service details
				$this->db->where('id', $service_id);
				$service = $this->db->get(db_prefix() . 'hms_services')->row();

				if ($service && isset($service_data['quantity']) && $service_data['quantity'] > 0)
				{
					$service_total = floatval($service->price) * intval($service_data['quantity']);
					$additional_services += $service_total;

					$services_details[] = [
						'id' => $service->id,
						'name' => $service->name,
						'price' => $service->price,
						'quantity' => $service_data['quantity'],
						'total' => $service_total
					];
				}
			}
		}

		// Calculate taxes
		$tax_rate = get_option('hotel_management_system_default_tax_rate');
		$taxable_amount = $room_price + $additional_services;
		$taxes = ($taxable_amount * floatval($tax_rate)) / 100;

		// Calculate total
		$total_amount = $room_price + $cleaning_fee + $additional_services + $taxes;

		return [
			'success' => TRUE,
			'data' => [
				'nights' => $nights,
				'room_price' => $room_price,
				'cleaning_fee' => $cleaning_fee,
				'additional_services' => $additional_services,
				'services_details' => $services_details,
				'tax_rate' => $tax_rate,
				'taxes' => $taxes,
				'total_amount' => $total_amount
			]
		];
	}

	private function generate_random_string($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}

		return $randomString;
	}
}