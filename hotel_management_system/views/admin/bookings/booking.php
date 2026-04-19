<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .panel-title {
        font-size: 16px;
        font-weight: 600;
    }

    #availability_warning {
        margin-top: 10px;
    }

    .service-row {
        transition: background-color 0.2s ease;
    }

    .service-row:hover {
        background-color: #f9f9f9;
    }

    #price_summary .row {
        padding: 5px 0;
    }

    #price_summary hr {
        margin: 10px 0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .panel {
        margin-bottom: 20px;
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo $title; ?>
							<?php if (isset($booking)) { ?>
                                <a href="<?php echo admin_url('hotel_management_system/bookings/delete/' . $booking->id); ?>" class="btn btn-danger pull-right _delete">
                                    <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                </a>
							<?php } ?>
                        </h4>
                        <hr/>
						<?php echo form_open($this->uri->uri_string(), ['id' => 'booking-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('room_details'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label for="property_id"><?php echo _l('property'); ?> *</label>
                                            <select name="property_id" id="property_id" class="form-control selectpicker" data-live-search="true" required>
                                                <option value=""><?php echo _l('select_property'); ?></option>
												<?php foreach ($properties as $property) { ?>
                                                    <option value="<?php echo $property['id']; ?>" <?php if (isset($booking) && $booking->property_id == $property['id']) echo 'selected'; ?>><?php echo $property['name']; ?></option>
												<?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
											<?php
											$booked_rooms = isset($booking) ? $booking->rooms : [];
											if ( ! empty($booked_rooms))
											{
												$booked_rooms = array_map(function ($room) {
													return $room->room_id;
												}, $booked_rooms);
											}
											?>
                                            <label for="room_id"><?php echo _l('room'); ?> *</label>
                                            <select name="room_id[]" id="room_id" class="form-control selectpicker" multiple data-live-search="true" required>
                                                <option value=""><?php echo _l('select_room'); ?></option>
												<?php if (isset($booking) && isset($rooms)) { ?>
													<?php foreach ($rooms as $room) { ?>
                                                        <option value="<?php echo $room['id']; ?>" <?php if (in_array($room['id'], $booked_rooms)) echo 'selected'; ?>><?php echo $room['name']; ?></option>
													<?php } ?>
												<?php } ?>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="check_in_date"><?php echo _l('check_in_date'); ?> *</label>
                                                    <div class="input-group date">
                                                        <input type="text" name="check_in_date" id="check_in_date" class="form-control datepicker" autocomplete="off" value="<?php echo isset($booking) ? $booking->check_in_date : ''; ?>"
                                                               required>
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar calendar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="check_out_date"><?php echo _l('check_out_date'); ?> *</label>
                                                    <div class="input-group date">
                                                        <input type="text" name="check_out_date" id="check_out_date" class="form-control datepicker" autocomplete="off" value="<?php echo isset($booking) ? $booking->check_out_date : ''; ?>"
                                                               required>
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar calendar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="availability_warning" class="alert alert-warning hide">
											<?php echo _l('room_not_available_for_selected_dates'); ?>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="adults"><?php echo _l('adults'); ?> *</label>
                                                    <input type="number" name="adults" id="adults" class="form-control" min="1" value="<?php echo isset($booking) ? $booking->adults : 1; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="children"><?php echo _l('children'); ?></label>
                                                    <input type="number" name="children" id="children" class="form-control" min="0" value="<?php echo isset($booking) ? $booking->children : 0; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="special_requests"><?php echo _l('special_requests'); ?></label>
                                            <textarea name="special_requests" id="special_requests" class="form-control" rows="3"><?php echo isset($booking) ? $booking->special_requests : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('guest_information'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="client_id"><?php echo _l('client'); ?></label>
                                                    <select name="client_id" id="client_id" class="form-control selectpicker" data-live-search="true">
                                                        <option value=""><?php echo _l('no_client_selected'); ?></option>
														<?php foreach ($clients as $client) { ?>
                                                            <option value="<?php echo $client['userid']; ?>" <?php if (isset($booking) && $booking->client_id == $client['userid']) echo 'selected'; ?>><?php echo $client['company']; ?></option>
														<?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_name"><?php echo _l('guest_name'); ?> *</label>
                                                    <input type="text" name="guest_name" id="guest_name" class="form-control" value="<?php echo isset($booking) ? $booking->guest_name : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_email"><?php echo _l('guest_email'); ?> *</label>
                                                    <input type="email" name="guest_email" id="guest_email" class="form-control" value="<?php echo isset($booking) ? $booking->guest_email : ''; ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_phone"><?php echo _l('guest_phone'); ?></label>
                                            <input type="text" name="guest_phone" id="guest_phone" class="form-control" value="<?php echo isset($booking) ? $booking->guest_phone : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('booking_status'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="booking_status"><?php echo _l('booking_status'); ?></label>
                                                    <select name="booking_status" id="booking_status" class="form-control selectpicker">
														<?php foreach ($booking_statuses as $status => $label) { ?>
                                                            <option value="<?php echo $status; ?>" <?php if (isset($booking) && $booking->booking_status == $status) echo 'selected'; ?>><?php echo $label; ?></option>
														<?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="payment_status"><?php echo _l('payment_status'); ?></label>
                                                    <select name="payment_status" id="payment_status" class="form-control selectpicker">
														<?php foreach ($payment_statuses as $status => $label) { ?>
                                                            <option value="<?php echo $status; ?>" <?php if (isset($booking) && $booking->payment_status == $status) echo 'selected'; ?>><?php echo $label; ?></option>
														<?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('services'); ?></h4>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="services_table">
                                                <thead>
                                                <tr>
                                                    <th width="5%"></th>
                                                    <th width="35%"><?php echo _l('service'); ?></th>
                                                    <th width="15%"><?php echo _l('quantity'); ?></th>
                                                    <th width="15%"><?php echo _l('price'); ?></th>
                                                    <th width="15%"><?php echo _l('total'); ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
												<?php foreach ($services as $index => $service) { ?>
                                                    <tr class="service-row">
                                                        <td>
                                                            <input type="checkbox" name="services[<?php echo $service['id']; ?>][selected]" value="1" class="service-checkbox" data-service-id="<?php echo $service['id']; ?>"
                                                                   data-price="<?php echo $service['price']; ?>"
																<?php
																if (isset($booking) && isset($booking->services))
																{
																	foreach ($booking->services as $booked_service)
																	{
																		if ($booked_service['service_id'] == $service['id'])
																		{
																			echo ' checked';
																			break;
																		}
																	}
																}
																?>>
                                                        </td>
                                                        <td><?php echo $service['name']; ?></td>
                                                        <td>
                                                            <input type="number" name="services[<?php echo $service['id']; ?>][quantity]" class="form-control service-quantity" min="1" value="<?php
															if (isset($booking) && !empty($booking->services))
															{
																foreach ($booking->services as $booked_service)
																{
																	if ($booked_service['service_id'] == $service['id'])
																	{
																		echo $booked_service['quantity'];
																		break;
																	}
																}
															} else
															{
																echo '1';
															}
															?>"
																<?php
																if (isset($booking) && isset($booking->services))
																{
																	$found = FALSE;
																	foreach ($booking->services as $booked_service)
																	{
																		if ($booked_service['service_id'] == $service['id'])
																		{
																			$found = TRUE;
																			break;
																		}
																	}
																	if ( ! $found) echo 'disabled';
																} else
																{
																	echo 'disabled';
																}
																?>>
                                                        </td>
                                                        <td><?php echo app_format_money($service['price'], get_base_currency()); ?></td>
                                                        <td class="service-total">
															<?php
															if (isset($booking) && isset($booking->services))
															{
																foreach ($booking->services as $booked_service)
																{
																	if ($booked_service['service_id'] == $service['id'])
																	{
																		echo app_format_money($booked_service['total'], get_base_currency());
																		break;
																	}
																}
															} else
															{
																echo app_format_money(0, get_base_currency());
															}
															?>
                                                        </td>
                                                    </tr>
												<?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title"><?php echo _l('price_summary'); ?></h4>
                                    </div>
                                    <div class="panel-body" id="price_summary">
										<?php if (isset($booking)) { ?>
                                            <div class="row">
                                                <div class="col-md-6"><?php echo _l('room_price'); ?>:</div>
                                                <div class="col-md-6 text-right"><?php echo app_format_money($booking->room_price, get_base_currency()); ?></div>
                                            </div>
											<?php if ($booking->cleaning_fee > 0) { ?>
                                                <div class="row">
                                                    <div class="col-md-6"><?php echo _l('cleaning_fee'); ?>:</div>
                                                    <div class="col-md-6 text-right"><?php echo app_format_money($booking->cleaning_fee, get_base_currency()); ?></div>
                                                </div>
											<?php } ?>
                                            <div class="row">
                                                <div class="col-md-6"><?php echo _l('additional_services'); ?>:</div>
                                                <div class="col-md-6 text-right"><?php echo app_format_money($booking->additional_services, get_base_currency()); ?></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><?php echo _l('taxes'); ?>:</div>
                                                <div class="col-md-6 text-right"><?php echo app_format_money($booking->taxes, get_base_currency()); ?></div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6"><strong><?php echo _l('total'); ?>:</strong></div>
                                                <div class="col-md-6 text-right"><strong><?php echo app_format_money($booking->total_amount, get_base_currency()); ?></strong></div>
                                            </div>
										<?php } else { ?>
                                            <div class="text-center text-muted"><?php echo _l('select_room_and_dates_to_see_price'); ?></div>
										<?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info pull-right"><?php echo _l('save'); ?></button>
                                    <a href="<?php echo admin_url('hotel_management_system/bookings'); ?>" class="btn btn-default pull-right mright5"><?php echo _l('cancel'); ?></a>
                                </div>
                            </div>
                        </div>

						<?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        const currencySymbol = '<?php echo get_base_currency()->symbol; ?>';
        // Property change - load rooms
        $('#property_id').on('change', function () {
            var propertyId = $(this).val();
            if (!propertyId) {
                $('#room_id').html('<option value="">Select Room</option>').selectpicker('refresh');
                return;
            }

            $.ajax({
                url: admin_url + 'hotel_management_system/bookings/get_property_rooms/' + propertyId,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var options = '<option value="">Select Room</option>';
                    $.each(response.rooms, function (i, room) {
                        console.log(room);
                        options += '<option value="' + room.id + '">' + room.name + '</option>';
                    });
                    $('#room_id').html(options).selectpicker('refresh');
                }
            });
        });

        // Client change - populate guest info
        $('#client_id').on('change', function () {
            var clientId = $(this).val();
            if (!clientId) return;

            $.ajax({
                url: admin_url + 'clients/get_contact/' + clientId,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        $('#guest_name').val(response.firstname + ' ' + response.lastname);
                        $('#guest_email').val(response.email);
                        $('#guest_phone').val(response.phonenumber);
                    }
                }
            });
        });

        // Check room availability when dates change
        $('#check_in_date, #check_out_date, #room_id').on('change', function () {
            var roomId = $('#room_id').val();
            var checkIn = $('#check_in_date').val();
            var checkOut = $('#check_out_date').val();

            if (!roomId || !checkIn || !checkOut) return;

            // Check if dates are valid
            var checkInDate = new Date(checkIn);
            var checkOutDate = new Date(checkOut);

            if (checkOutDate <= checkInDate) {
                alert('Check-out date must be after check-in date');
                $('#check_out_date').val('');
                return;
            }

            // Check room availability
            $.ajax({
                url: admin_url + 'hotel_management_system/bookings/check_availability',
                type: 'POST',
                data: {
                    room_id: roomId,
                    check_in: checkIn,
                    check_out: checkOut,
                    booking_id: bookingId // This would be defined elsewhere as a global variable
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        if (response.available) {
                            $('#availability_warning').addClass('hide');
                            updatePriceSummary();
                        } else {
                            $('#availability_warning').removeClass('hide');
                        }
                    }
                }
            });
        });

        // Service checkboxes
        $('.service-checkbox').on('change', function () {
            var serviceId = $(this).data('service-id');
            var quantityInput = $('input[name="services[' + serviceId + '][quantity]"]');

            if ($(this).is(':checked')) {
                quantityInput.prop('disabled', false);
            } else {
                quantityInput.prop('disabled', true);
            }

            updateServiceTotals();
        });

        // Service quantity change
        $('.service-quantity').on('change', function () {
            updateServiceTotals();
        });

        // Update service totals
        function updateServiceTotals() {
            var totalServices = 0;

            $('.service-checkbox:checked').each(function () {
                var serviceId = $(this).data('service-id');
                var price = parseFloat($(this).data('price'));
                var quantity = parseInt($('input[name="services[' + serviceId + '][quantity]"]').val());
                var total = price * quantity;

                // Update row total
                $(this).closest('tr').find('.service-total').text(currencySymbol + total.toFixed(2));

                totalServices += total;
            });

            // If we need to update the price summary, do so here
            if (totalServices > 0) {
                updatePriceSummary();
            }
        }

        // Update price summary
        function updatePriceSummary() {
            var roomId = $('#room_id').val();
            var checkIn = $('#check_in_date').val();
            var checkOut = $('#check_out_date').val();

            if (!roomId || !checkIn || !checkOut) return;

            // Get all selected services
            var services = {};
            $('.service-checkbox:checked').each(function () {
                var serviceId = $(this).data('service-id');
                var quantity = parseInt($('input[name="services[' + serviceId + '][quantity]"]').val());

                services[serviceId] = {
                    selected: true,
                    quantity: quantity
                };
            });

            $.ajax({
                url: admin_url + 'hotel_management_system/bookings/calculate_price',
                type: 'POST',
                data: {
                    room_id: roomId,
                    check_in: checkIn,
                    check_out: checkOut,
                    services: services
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        var calc = response.calculation;
                        var html = '';

                        html += '<div class="row">';
                        html += '<div class="col-md-6">Room Price:</div>';
                        html += '<div class="col-md-6 text-right">' + currencySymbol + calc.room_price.toFixed(2) + '</div>';
                        html += '</div>';

                        if (calc.cleaning_fee > 0) {
                            html += '<div class="row">';
                            html += '<div class="col-md-6">Cleaning Fee:</div>';
                            html += '<div class="col-md-6 text-right">' + currencySymbol + calc.cleaning_fee.toFixed(2) + '</div>';
                            html += '</div>';
                        }

                        if (calc.additional_services > 0) {
                            html += '<div class="row">';
                            html += '<div class="col-md-6">Additional Services:</div>';
                            html += '<div class="col-md-6 text-right">' + currencySymbol + calc.additional_services.toFixed(2) + '</div>';
                            html += '</div>';
                        }

                        html += '<div class="row">';
                        html += '<div class="col-md-6">Taxes:</div>';
                        html += '<div class="col-md-6 text-right">' + currencySymbol + calc.taxes.toFixed(2) + '</div>';
                        html += '</div>';

                        html += '<hr>';

                        html += '<div class="row">';
                        html += '<div class="col-md-6"><strong>Total:</strong></div>';
                        html += '<div class="col-md-6 text-right"><strong>' + currencySymbol + calc.total_amount.toFixed(2) + '</strong></div>';
                        html += '</div>';

                        $('#price_summary').html(html);
                    }
                }
            });
        }

        // Form validation
        $('#booking-form').on('submit', function (e) {
            var roomId = $('#room_id').val();
            var checkIn = $('#check_in_date').val();
            var checkOut = $('#check_out_date').val();

            if (!roomId || !checkIn || !checkOut) {
                alert('Please select room and dates');
                e.preventDefault();
                return false;
            }

            var checkInDate = new Date(checkIn);
            var checkOutDate = new Date(checkOut);

            if (checkOutDate <= checkInDate) {
                alert('Check-out date must be after check-in date');
                e.preventDefault();
                return false;
            }

            // If room availability warning is shown, confirm proceeding
            if (!$('#availability_warning').hasClass('hide')) {
                if (!confirm('Room is not available for selected dates. Do you want to continue anyway?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Initialize the services table events
        initializeServicesTable();

        // Function to handle service events
        function initializeServicesTable() {
            // Enable/disable service quantity inputs based on checkboxes
            $('.service-checkbox').on('change', function () {
                var serviceId = $(this).data('service-id');
                var quantityInput = $('input[name="services[' + serviceId + '][quantity]"]');

                if ($(this).is(':checked')) {
                    quantityInput.prop('disabled', false);
                } else {
                    quantityInput.prop('disabled', true);
                }

                calculateServiceTotals();
            });

            // Update service totals when quantities change
            $('.service-quantity').on('change', function () {
                calculateServiceTotals();
            });
        }

        // Calculate service totals
        function calculateServiceTotals() {
            var totalServicesAmount = 0;

            $('.service-checkbox:checked').each(function () {
                var serviceId = $(this).data('service-id');
                var price = parseFloat($(this).data('price'));
                var quantity = parseInt($('input[name="services[' + serviceId + '][quantity]"]').val());
                var total = price * quantity;

                $(this).closest('tr').find('.service-total').text(currencySymbol + total.toFixed(2));
                totalServicesAmount += total;
            });

            // If we have room and dates selected, update the price summary
            var roomId = $('#room_id').val();
            var checkIn = $('#check_in_date').val();
            var checkOut = $('#check_out_date').val();

            if (roomId && checkIn && checkOut) {
                updatePriceSummary();
            }
        }
    });
</script>
