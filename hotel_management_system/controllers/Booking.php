<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Booking extends ClientsController {
	public function __construct()
	{
		parent::__construct();

		// Load required models
		$this->load->model('property_model');
		$this->load->model('room_model');
		$this->load->model('booking_model');

		// Load helpers
		$this->load->helper('form');
		$this->load->helper('url');

		// Load libraries
		$this->load->library('form_validation');
	}

	/**
	 * Index page for the booking system
	 */
	public function index()
	{
		$data['title'] = _l('hotel_booking');
		$data['properties'] = $this->property_model->get_properties_for_public();

		$this->data($data);
		$this->view('booking/index');
		$this->layout();
	}

	/**
	 * Get available rooms based on selected hotel and dates
	 */
	public function get_available_rooms()
	{
		// Validate the request
		$this->form_validation->set_rules('hotel_id', 'Hotel', 'numeric');
		$this->form_validation->set_rules('check_in', 'Check-in Date', 'required|callback_validate_date');
		$this->form_validation->set_rules('check_out', 'Check-out Date', 'required|callback_validate_date|callback_validate_date_range');

		if ($this->form_validation->run() === FALSE)
		{
			$errors = validation_errors();
			echo json_encode(['success' => FALSE, 'message' => $errors]);
			return;
		}

		$hotel_id = $this->input->post('hotel_id');
		$check_in = $this->input->post('check_in');
		$check_out = $this->input->post('check_out');

		// Get available rooms
		$filters = [
			'property_id' => $hotel_id,
			'check_in' => $check_in,
			'check_out' => $check_out
		];
		$rooms = $this->room_model->get_rooms_for_booking($filters);
		$room_groups = [];
		foreach ($rooms as $room)
		{
			$room_groups[$room['property_name']][] = $room;
		}

		$data = [
			'rooms' => $rooms,
			'room_groups' => $room_groups,
			'check_in' => $check_in,
			'check_out' => $check_out
		];

		$html = $this->load->view('booking/available_rooms', $data, TRUE);

		echo json_encode([
			'success' => TRUE,
			'rooms' => $rooms,
			'html' => $html
		]);
	}

	/**
	 * Validate date callback for form validation
	 */
	public function validate_date($date)
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		if ($d && $d->format('Y-m-d') === $date)
		{
			return TRUE;
		} else
		{
			$this->form_validation->set_message('validate_date', 'The {field} field must contain a valid date in YYYY-MM-DD format.');
			return FALSE;
		}
	}

	/**
	 * Validate date range callback for form validation
	 */
	public function validate_date_range($check_out)
	{
		$check_in = $this->input->post('check_in');

		$check_in_date = new DateTime($check_in);
		$check_out_date = new DateTime($check_out);

		// Check if check-out is after check-in
		if ($check_out_date <= $check_in_date)
		{
			$this->form_validation->set_message('validate_date_range', 'Check-out date must be after check-in date.');
			return FALSE;
		}

		// Check if check-in date is today or in the future
		$today = new DateTime('today');
		if ($check_in_date < $today)
		{
			$this->form_validation->set_message('validate_date_range', 'Check-in date cannot be in the past.');
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Room details and booking form
	 */
	public function room($room_id)
	{
		// Validate room_id
		if ( ! is_numeric($room_id))
		{
			show_404();
		}

		$room = $this->room_model->get_room_with_details($room_id);
		$check_in = $this->input->get('check_in');
		$check_out = $this->input->get('check_out');

		if ( ! $room)
		{
			show_404();
		}

		if (empty($check_in) || empty($check_out))
		{
			redirect(site_url('hotel_management_system/booking'));
			exit();
		}

		$check_available = $this->room_model->is_room_available($room_id, $check_in, $check_out);
		if ( ! $check_available)
		{
			$this->session->set_flashdata('error', _l('room_not_available_for_selected_dates'));
			redirect('hotel_management_system/booking/');
			return;
		}

		$data['title'] = $room->name . ' - ' . _l('booking');
		$data['check_in'] = $check_in;
		$data['check_out'] = $check_out;
		$data['room'] = $room;
		$data['hotel'] = $this->property_model->get($room->property_id);

		$check_in = $this->input->get('check_in');
		$check_out = $this->input->get('check_out');
		$check_in_date = new DateTime($check_in);
		$check_out_date = new DateTime($check_out);
		$interval = $check_in_date->diff($check_out_date);
		$data['nights'] = $interval->days;
		$data['price_per_night'] = $room->price_per_night;
		$data['total'] = floatval($data['price_per_night']) * intval($data['nights']);

		$this->data($data);
		$this->view('booking/room');
		$this->layout();
	}

	/**
	 * Room details and booking form
	 */
	public function rooms()
	{
		$rooms = $this->input->get('rooms');
		$check_in = $this->input->get('check_in');
		$check_out = $this->input->get('check_out');

		if (empty($rooms) || empty($check_in) || empty($check_out))
		{
			redirect(site_url('hotel_management_system/booking'));
			exit();
		}
		// Validate room_id
		$rooms = array_map(function ($room_id) {
			return $this->room_model->get_room_with_details($room_id);
		}, $rooms);

		$rooms_name = implode(', ', array_map(function ($room) {
			return $room->name;
		}, $rooms));
		$data['title'] = $rooms_name . ' - ' . _l('booking');
		$data['check_in'] = $check_in;
		$data['check_out'] = $check_out;
		$data['rooms'] = $rooms;

		$check_in_date = new DateTime($check_in);
		$check_out_date = new DateTime($check_out);
		$interval = $check_in_date->diff($check_out_date);
		$data['nights'] = $interval->days;

		$price_per_night = array_sum(array_column($rooms, 'price_per_night'));
		$rooms_cleaning_fee = array_sum(array_column($rooms, 'cleaning_fee'));

		$data['rooms_price_per_night'] = $price_per_night;
		$data['rooms_cleaning_fee'] = $rooms_cleaning_fee;
		$data['total'] = floatval($price_per_night) * $data['nights'] + floatval($rooms_cleaning_fee);

		$this->data($data);
		$this->view('booking/rooms');
		$this->layout();
	}

	/**
	 * Process the booking
	 */
	public function process()
	{
		// Validate booking form
		$this->form_validation->set_rules('check_in', 'Check-in Date', 'required|callback_validate_date');
		$this->form_validation->set_rules('check_out', 'Check-out Date', 'required|callback_validate_date|callback_validate_date_range');
		$this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
		$this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim');

		if ($this->form_validation->run() === FALSE)
		{
			// If validation fails, redirect back to the room booking page
			$room_id = $this->input->post('room_id');
			$this->session->set_flashdata('error', validation_errors());

			redirect('hotel_management_system/booking/' . ($room_id ?: ''));
			return;
		}

		// Check room availability for selected dates
		$room_id = $this->input->post('room_id');
		$check_in = $this->input->post('check_in');
		$check_out = $this->input->post('check_out');

		if (empty($room_id) || empty($check_in) || empty($check_out))
		{
			$this->session->set_flashdata('error', _l('room_not_available_for_selected_dates'));
			redirect('hotel_management_system/booking');
			return;
		}

		if (is_array($room_id))
		{
			$rooms = array_map(function ($room_id) {
				return $this->room_model->get($room_id, TRUE);
			}, $room_id);
		} else
		{
			$rooms = [$this->room_model->get($room_id, TRUE)];
		}

		if (empty($rooms))
		{
			show_404();
		}

		foreach ($rooms as $room)
		{
			$check_available = $this->room_model->is_room_available($room->id, $check_in, $check_out);
			if ( ! $check_available)
			{
				$this->session->set_flashdata('error', _l('room_not_available_for_selected_dates'));
				redirect('hotel_management_system/booking');
				return;
			}
		}

		// Calculate total price
		$check_in_date = new DateTime($check_in);
		$check_out_date = new DateTime($check_out);
		$interval = $check_in_date->diff($check_out_date);
		$total_nights = $interval->days;
		$room_general_data = [
			'check_in_date' => $check_in,
			'check_out_date' => $check_out,
			'total_nights' => $total_nights,
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

		$total_price = 0.0;
		$total_cleaning_fee = 0.0;
		$total_tax = 0.0;
		foreach ($rooms_data as $room)
		{
			$total_price += floatval($room['room_price']);
			$total_cleaning_fee += floatval($room['cleaning_fee']);
			$total_tax += floatval($room['taxes']);
		}

		$booking_data = [
			'room_id' => is_array($room_id) ? array_shift($room_id) : $room_id,
			'guest_name' => $this->input->post('first_name') . ' ' . $this->input->post('last_name'),
			'guest_email' => $this->input->post('email'),
			'guest_phone' => $this->input->post('phone'),
			'check_in_date' => $check_in,
			'check_out_date' => $check_out,
			'total_nights' => $total_nights,
			'room_price' => $total_price,
			'cleaning_fee' => $total_cleaning_fee,
			'taxes' => $total_tax,
			'payment_status' => 'pending',
			'datecreated' => date('Y-m-d H:i:s'),
			'rooms' => $rooms_data
		];

		// Add booking to database
		$booking_id = $this->booking_model->add($booking_data);

		if ($booking_id)
		{
			// Redirect to payment page
			redirect('hotel_management_system/booking/payment/' . $booking_id);
		} else
		{
			// If booking creation fails
			$this->session->set_flashdata('error', _l('booking_creation_failed'));
			redirect('hotel_management_system/booking/room/' . $room_id);
		}
	}

	/**
	 * Payment page
	 */
	public function payment($booking_id)
	{
		// Validate booking_id
		if ( ! is_numeric($booking_id))
		{
			show_404();
		}

		$booking = $this->booking_model->get($booking_id);
		if ( ! $booking || $booking->payment_status !== 'pending')
		{
			show_404();
		}

		$data['title'] = _l('payment') . ' - ' . _l('booking');
		$data['booking'] = $booking;

		$rooms = $booking->rooms;
		$rooms = array_map(function ($room) {
			return $this->room_model->get_room_with_details($room->room_id);
		}, $rooms);
		$data['rooms'] = $rooms;

		$this->data($data);
		$this->view('booking/payment');
		$this->layout();
	}

	/**
	 * Process payment (mock implementation)
	 */
	public function process_payment()
	{
		// Validate payment form
		$this->form_validation->set_rules('booking_id', 'Booking', 'required|numeric');
		if ($this->form_validation->run() === FALSE)
		{
			// If validation fails, redirect back to the payment page
			$booking_id = $this->input->post('booking_id');
			$this->session->set_flashdata('error', validation_errors());
			redirect('hotel_management_system/booking/payment/' . $booking_id);
			return;
		}

		$booking_id = $this->input->post('booking_id');
		$booking = $this->booking_model->get($booking_id);

		if ( ! $booking)
		{
			show_404();
		}

		$this->booking_model->update_status($booking_id, 'confirmed');

		// Record payment details (in a real implementation, store these securely)
		// Here, we're not actually storing card details for security reasons
//		$payment_data = [
//			'booking_id' => $booking_id,
//			'amount' => $booking->total_price,
//			'payment_method' => 'card',
//			'status' => 'completed',
//			'transaction_id' => 'TRANS-' . uniqid(),
//			'created_at' => date('Y-m-d H:i:s')
//		];
//
//		$this->booking_model->add_payment($payment_data);
		hms_generate_invoice($booking_id);
		// Redirect to confirmation page
		redirect('hotel_management_system/booking/confirmation/' . $booking_id);
	}

	/**
	 * Booking confirmation page
	 */
	public function confirmation($booking_id)
	{
		// Validate booking_id
		if ( ! is_numeric($booking_id))
		{
			show_404();
		}

		$booking = $this->booking_model->get($booking_id);
		if ( ! $booking || $booking->booking_status !== 'confirmed')
		{
			show_404();
		}

		$data['title'] = _l('booking_confirmation');
		$data['booking'] = $booking;
		$rooms = $booking->rooms;
		$rooms = array_map(function ($room) {
			return $this->room_model->get_room_with_details($room->room_id);
		}, $rooms);
		$data['rooms'] = $rooms;


		// Send confirmation email to customer
		$this->send_confirmation_email($booking_id);

		$this->data($data);
		$this->view('booking/confirmation');
		$this->layout();
	}

	/**
	 * Ajax endpoint to check date availability
	 */
	public function check_dates_availability()
	{
		// Validate the request
		$this->form_validation->set_rules('hotel_id', 'Hotel', 'required|numeric');
		$this->form_validation->set_rules('check_in', 'Check-in Date', 'required|callback_validate_date');
		$this->form_validation->set_rules('check_out', 'Check-out Date', 'required|callback_validate_date|callback_validate_date_range');

		if ($this->form_validation->run() === FALSE)
		{
			$errors = validation_errors();
			echo json_encode(['success' => FALSE, 'message' => $errors]);
			return;
		}

		$hotel_id = $this->input->post('hotel_id');
		$check_in = $this->input->post('check_in');
		$check_out = $this->input->post('check_out');

		$args = [
			'property_id' => $hotel_id,
			'check_in' => $check_in,
			'check_out' => $check_out,
		];
		$available_rooms = $this->room_model->get_rooms_for_booking($args);
		$available_rooms_count = count($available_rooms);

		echo json_encode([
			'success' => TRUE,
			'available' => ($available_rooms_count > 0),
			'message' => ($available_rooms_count > 0)
				? sprintf(_l('rooms_available_for_selected_dates'), $available_rooms_count)
				: _l('no_rooms_available_for_selected_dates')
		]);
	}

	/**
	 * Send booking confirmation email
	 */
	private function send_confirmation_email($booking_id)
	{
		$booking = $this->booking_model->get($booking_id);
		$room = $this->room_model->get($booking->room_id);
		$room = $this->room_model->get_room_with_details($booking->room_id);;
		$hotel = $this->property_model->get($room->property_id);

		$email_template = 'booking-confirmation';
		$slug = $email_template;

		// Check if email template exists
		if (total_rows(db_prefix() . 'emailtemplates', ['slug' => $slug]) == 0)
		{
			// Create email template if it doesn't exist
			$this->create_booking_email_template();
		}

		$merge_fields = [
			'{booking_reference}' => $booking->booking_reference,
			'{customer_name}' => $booking->guest_name,
			'{hotel_name}' => $hotel->name,
			'{room_name}' => $room->name,
			'{check_in_date}' => date('F j, Y', strtotime($booking->check_in_date)),
			'{check_out_date}' => date('F j, Y', strtotime($booking->check_out_date)),
			'{nights}' => $booking->total_nights,
			'{total_price}' => app_format_money($booking->total_amount, get_base_currency()),
		];

		// Send email
		$this->load->model('emails_model');
		$this->emails_model->send_email_template($email_template, $booking->guest_email, $merge_fields);
	}

	/**
	 * Create email template for booking confirmation
	 */
	private function create_booking_email_template()
	{
		$this->load->model('emails_model');

		$template_data = [
			'slug' => 'booking-confirmation',
			'type' => 'booking',
			'language' => 'english',
			'name' => 'Booking Confirmation',
			'subject' => 'Booking Confirmation - {booking_reference}',
			'fromname' => '{companyname}',
			'active' => 1,
			'plaintext' => 0,
			'message' => '
                <p>Dear {customer_name},</p>
                <p>Thank you for your booking. Your reservation has been confirmed!</p>
                <p><strong>Booking Details:</strong></p>
                <p>Reference: {booking_reference}<br>
                Hotel: {hotel_name}<br>
                Room: {room_name}<br>
                Check-in: {check_in_date}<br>
                Check-out: {check_out_date}<br>
                Nights: {nights}<br>
                Total: {total_price}</p>
                <p>If you have any questions regarding your booking, please contact us.</p>
                <p>We look forward to welcoming you!</p>
                <p>Best regards,<br>
                {companyname}</p>
            '
		];

		$this->emails_model->add_template($template_data);
	}
}