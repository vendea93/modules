<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
									<?php echo _l('booking_details'); ?>: <?php echo $booking->booking_reference; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="label label-<?php
								$status_class = 'info';
								if ($booking->booking_status == 'confirmed')
								{
									$status_class = 'success';
								} else if ($booking->booking_status == 'cancelled')
								{
									$status_class = 'danger';
								} else if ($booking->booking_status == 'checked_in')
								{
									$status_class = 'primary';
								} else if ($booking->booking_status == 'checked_out')
								{
									$status_class = 'default';
								} else if ($booking->booking_status == 'no_show')
								{
									$status_class = 'warning';
								}
								echo $status_class;
								?>">
                                    <?php echo ucfirst($booking->booking_status); ?>
                                </span>

                                <span class="label label-<?php
								$payment_status_class = 'warning';
								if ($booking->payment_status == 'paid')
								{
									$payment_status_class = 'success';
								} else if ($booking->payment_status == 'partial')
								{
									$payment_status_class = 'info';
								} else if ($booking->payment_status == 'overdue')
								{
									$payment_status_class = 'danger';
								} else if ($booking->payment_status == 'refunded')
								{
									$payment_status_class = 'default';
								}
								echo $payment_status_class;
								?> mleft5">
                                    <?php echo ucfirst($booking->payment_status); ?>
                                </span>
                            </div>
                        </div>

                        <hr class="hr-panel-heading"/>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="bold"><?php echo _l('guest_information'); ?></h5>
                                <address>
                                    <strong><?php echo $booking->guest_name; ?></strong><br>
									<?php echo $booking->guest_email; ?><br>
									<?php if (isset($booking->guest_phone) && ! empty($booking->guest_phone))
									{
										echo $booking->guest_phone;
									} ?>
                                </address>

								<?php if (isset($booking->client_id) && $booking->client_id) { ?>
                                    <p>
                                        <a href="<?php echo admin_url('clients/client/' . $booking->client_id); ?>" class="btn btn-default btn-xs">
                                            <i class="fa fa-user"></i> <?php echo _l('view_client'); ?>
                                        </a>
                                    </p>
								<?php } ?>
                            </div>

                            <div class="col-md-6">
                                <h5 class="bold"><?php echo _l('booking_details'); ?></h5>
                                <p>
                                    <strong><?php echo _l('check_in_date'); ?>:</strong>
									<?php echo date('d/m/Y', strtotime($booking->check_in_date)); ?>
                                </p>
                                <p>
                                    <strong><?php echo _l('check_out_date'); ?>:</strong>
									<?php echo date('d/m/Y', strtotime($booking->check_out_date)); ?>
                                </p>
                                <p>
                                    <strong><?php echo _l('total_nights'); ?>:</strong>
									<?php echo $booking->total_nights; ?>
                                </p>
                                <p>
                                    <strong><?php echo _l('guests'); ?>:</strong>
									<?php echo $booking->adults . ' ' . _l('adults'); ?>
									<?php if ($booking->children > 0)
									{
										echo ' + ' . $booking->children . ' ' . _l('children');
									} ?>
                                </p>
                            </div>
                        </div>

                        <hr/>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="bold"><?php echo _l('room_information'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>
                                            <strong><?php echo _l('property'); ?>:</strong>
                                            <a href="<?php echo admin_url('hotel_management_system/properties/view/' . $booking->property_id); ?>">
												<?php echo $booking->property_name; ?>
                                            </a>
                                        </p>
                                        <p>
                                            <strong><?php echo _l('room'); ?>:</strong>
                                            <a href="<?php echo admin_url('hotel_management_system/rooms/room/' . $booking->room_id); ?>">
												<?php echo $booking->room_name; ?>
                                            </a>
                                        </p>
                                        <p>
                                            <strong><?php echo _l('room_type'); ?>:</strong>
											<?php
											$room_types = get_room_types();
											echo isset($booking->room_type) && isset($room_types[$booking->room_type])
												? $room_types[$booking->room_type]
												: '-';
											?>
                                        </p>
                                    </div>

                                    <div class="col-md-6">
                                        <p>
                                            <strong><?php echo _l('property_address'); ?>:</strong><br>
											<?php echo isset($booking->property_address) ? $booking->property_address : '-'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

						<?php if (isset($booking->special_requests) && ! empty($booking->special_requests)) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="bold"><?php echo _l('special_requests'); ?></h5>
                                    <p><?php echo $booking->special_requests; ?></p>
                                </div>
                            </div>
						<?php } ?>

                        <hr/>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="bold"><?php echo _l('payment_details'); ?></h5>
                                <div class="table-responsive mtop15">
                                    <table class="table items items-preview">
                                        <thead>
                                        <tr>
                                            <th><?php echo _l('item'); ?></th>
                                            <th><?php echo _l('qty'); ?></th>
                                            <th><?php echo _l('rate'); ?></th>
                                            <th><?php echo _l('total'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>
                                                <strong><?php echo _l('accommodation'); ?>: <?php echo $booking->room_name; ?></strong><br>
                                                <span class="text-muted"><?php echo date('d/m/Y', strtotime($booking->check_in_date)) . ' - ' . date('d/m/Y', strtotime($booking->check_out_date)); ?></span>
                                            </td>
                                            <td><?php echo $booking->total_nights; ?><?php echo _l('nights'); ?></td>
                                            <td><?php echo app_format_money($booking->room_price / $booking->total_nights, get_base_currency()); ?></td>
                                            <td><?php echo app_format_money($booking->room_price, get_base_currency()); ?></td>
                                        </tr>

										<?php if ($booking->cleaning_fee > 0) { ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo _l('cleaning_fee'); ?></strong>
                                                </td>
                                                <td>1</td>
                                                <td><?php echo app_format_money($booking->cleaning_fee, get_base_currency()); ?></td>
                                                <td><?php echo app_format_money($booking->cleaning_fee, get_base_currency()); ?></td>
                                            </tr>
										<?php } ?>

										<?php if (isset($booking->services) && count($booking->services) > 0)
										{
											foreach ($booking->services as $service)
											{ ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo _l('additional_service'); ?>: <?php echo $service['service_name']; ?></strong>
														<?php if ( ! empty($service['notes'])) { ?>
                                                            <br><span class="text-muted"><?php echo $service['notes']; ?></span>
														<?php } ?>
                                                    </td>
                                                    <td><?php echo $service['quantity']; ?></td>
                                                    <td><?php echo app_format_money($service['price'], get_base_currency()); ?></td>
                                                    <td><?php echo app_format_money($service['total'], get_base_currency()); ?></td>
                                                </tr>
											<?php }
										} ?>

                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="text-right"><strong><?php echo _l('subtotal'); ?></strong></td>
                                            <td><?php echo app_format_money($booking->room_price + $booking->cleaning_fee + $booking->additional_services, get_base_currency()); ?></td>
                                        </tr>

										<?php if ($booking->taxes > 0)
										{
											$tax_rate = isset($booking->tax_rate) ? $booking->tax_rate : get_option('hotel_management_system_default_tax_rate');
											?>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="text-right"><strong><?php echo _l('tax'); ?> (<?php echo $tax_rate; ?>%)</strong></td>
                                                <td><?php echo app_format_money($booking->taxes, get_base_currency()); ?></td>
                                            </tr>
										<?php } ?>

                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="text-right"><strong><?php echo _l('total'); ?></strong></td>
                                            <td><?php echo app_format_money($booking->total_amount, get_base_currency()); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

						<?php if (isset($booking->invoice) && $booking->invoice) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="bold"><?php echo _l('invoice_information'); ?></h5>
                                    <p>
                                        <strong><?php echo _l('invoice'); ?>:</strong>
                                        <a href="<?php echo admin_url('invoices/list_invoices/' . $booking->invoice_id); ?>">
											<?php echo format_invoice_number($booking->invoice->id); ?>
                                        </a>
                                    </p>
                                    <p>
                                        <strong><?php echo _l('invoice_date'); ?>:</strong>
										<?php echo _d($booking->invoice->date); ?>
                                    </p>
                                    <p>
                                        <strong><?php echo _l('invoice_due_date'); ?>:</strong>
										<?php echo _d($booking->invoice->duedate); ?>
                                    </p>
                                    <p>
                                        <strong><?php echo _l('invoice_status'); ?>:</strong>
										<?php echo format_invoice_status($booking->invoice->status); ?>
                                    </p>
                                </div>
                            </div>
						<?php } ?>

                        <hr/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group">
                                    <a href="<?php echo admin_url('hotel_management_system/bookings/booking/' . $booking->id); ?>" class="btn btn-default">
                                        <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('hotel_management_system/bookings/delete/' . $booking->id); ?>" class="btn btn-danger _delete">
                                        <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                    </a>
                                </div>
                                <div class="btn-group mleft5">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<?php echo _l('change_status'); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
										<?php
										$booking_statuses = hms_get_booking_statuses();
										foreach ($booking_statuses as $status_key => $status_value)
										{
											if ($status_key != $booking->booking_status)
											{
												?>
                                                <li>
                                                    <a href="<?php echo admin_url('hotel_management_system/bookings/change_status/' . $booking->id . '/' . $status_key); ?>">
														<?php echo $status_value; ?>
                                                    </a>
                                                </li>
												<?php
											}
										}
										?>
                                    </ul>
                                </div>
                                <div class="btn-group mleft5">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<?php echo _l('payment_status'); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
										<?php
										$payment_statuses = hms_get_payment_statuses();
										foreach ($payment_statuses as $status_key => $status_value)
										{
											if ($status_key != $booking->payment_status)
											{
												?>
                                                <li>
                                                    <a href="<?php echo admin_url('hotel_management_system/bookings/change_payment_status/' . $booking->id . '/' . $status_key); ?>">
														<?php echo $status_value; ?>
                                                    </a>
                                                </li>
												<?php
											}
										}
										?>
                                    </ul>
                                </div>
								<?php if ($booking->booking_status == 'confirmed') { ?>
                                    <a href="<?php echo admin_url('hotel_management_system/bookings/check_in/' . $booking->id); ?>" class="btn btn-info pull-right">
                                        <i class="fa fa-sign-in"></i> <?php echo _l('check_in'); ?>
                                    </a>
								<?php } ?>

								<?php if ($booking->booking_status == 'checked_in') { ?>
                                    <a href="<?php echo admin_url('hotel_management_system/bookings/check_out/' . $booking->id); ?>" class="btn btn-warning pull-right">
                                        <i class="fa fa-sign-out"></i> <?php echo _l('check_out'); ?>
                                    </a>
								<?php } ?>

								<?php if ( ! $booking->invoice_id) { ?>
                                    <a href="<?php echo admin_url('hotel_management_system/bookings/generate_invoice/' . $booking->id); ?>" class="btn btn-primary pull-right mleft5">
                                        <i class="fa fa-file-text"></i> <?php echo _l('generate_invoice'); ?>
                                    </a>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

				<?php if (isset($booking->services) && count($booking->services) > 0) { ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('booked_services'); ?></h4>
                            <hr class="hr-panel-heading"/>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th><?php echo _l('service'); ?></th>
                                        <th><?php echo _l('assigned_to'); ?></th>
                                        <th><?php echo _l('date'); ?></th>
                                        <th><?php echo _l('time'); ?></th>
                                        <th><?php echo _l('quantity'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('actions'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php foreach ($booking->services as $service) { ?>
                                        <tr>
                                            <td><?php echo $service['service_name']; ?></td>
                                            <td>
												<?php
												if (isset($service['firstname']) && ! empty($service['firstname']))
												{
													echo $service['firstname'] . ' ' . $service['lastname'];
												} else
												{
													echo '-';
												}
												?>
                                            </td>
                                            <td><?php echo isset($service['service_date']) ? date('d/m/Y', strtotime($service['service_date'])) : '-'; ?></td>
                                            <td><?php echo isset($service['service_time']) ? date('H:i', strtotime($service['service_time'])) : '-'; ?></td>
                                            <td><?php echo $service['quantity']; ?></td>
                                            <td>
												<?php
												$service_status_class = 'default';
												if ($service['status'] == 'pending')
												{
													$service_status_class = 'warning';
												} else if ($service['status'] == 'completed')
												{
													$service_status_class = 'success';
												} else if ($service['status'] == 'cancelled')
												{
													$service_status_class = 'danger';
												}
												?>
                                                <span class="label label-<?php echo $service_status_class; ?>"><?php echo ucfirst($service['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
														<?php echo _l('actions'); ?> <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
														<?php if ($service['status'] != 'completed') { ?>
                                                            <li>
                                                                <a href="<?php echo admin_url('hotel_management_system/bookings/update_service_status/' . $service['id'] . '/completed'); ?>">
                                                                    <i class="fa fa-check"></i> <?php echo _l('mark_as_completed'); ?>
                                                                </a>
                                                            </li>
														<?php } ?>

														<?php if ($service['status'] != 'cancelled') { ?>
                                                            <li>
                                                                <a href="<?php echo admin_url('hotel_management_system/bookings/update_service_status/' . $service['id'] . '/cancelled'); ?>">
                                                                    <i class="fa fa-ban"></i> <?php echo _l('mark_as_cancelled'); ?>
                                                                </a>
                                                            </li>
														<?php } ?>

														<?php if ($service['status'] != 'pending') { ?>
                                                            <li>
                                                                <a href="<?php echo admin_url('hotel_management_system/bookings/update_service_status/' . $service['id'] . '/pending'); ?>">
                                                                    <i class="fa fa-clock-o"></i> <?php echo _l('mark_as_pending'); ?>
                                                                </a>
                                                            </li>
														<?php } ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
				<?php } ?>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('booking_information'); ?></h4>
                        <hr class="hr-panel-heading"/>

                        <p><strong><?php echo _l('booking_reference'); ?>:</strong> <?php echo $booking->booking_reference; ?></p>
                        <p><strong><?php echo _l('date_created'); ?>:</strong> <?php echo date('d/m/Y', strtotime($booking->datecreated)); ?></p>

						<?php if (isset($booking->datemodified)) { ?>
                            <p><strong><?php echo _l('date_modified'); ?>:</strong> <?php echo date('d/m/Y', strtotime($booking->datemodified)); ?></p>
						<?php } ?>

						<?php if (isset($booking->created_by) && $booking->created_by)
						{
							$creator = get_staff($booking->created_by);
							?>
                            <p><strong><?php echo _l('created_by'); ?>:</strong> <?php echo $creator->firstname . ' ' . $creator->lastname; ?></p>
						<?php } ?>

                        <hr/>

                        <h5 class="bold"><?php echo _l('calendar'); ?></h5>

                        <div class="mtop15">
                            <div id="bookingCalendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        // Initialize calendar
        $('#bookingCalendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultDate: '<?php echo $booking->check_in_date; ?>',
            editable: false,
            eventLimit: true,
            events: [
                {
                    title: '<?php echo _l('check_in'); ?>: <?php echo $booking->guest_name; ?>',
                    start: '<?php echo $booking->check_in_date; ?>',
                    color: '#28a745'
                },
                {
                    title: '<?php echo _l('check_out'); ?>: <?php echo $booking->guest_name; ?>',
                    start: '<?php echo $booking->check_out_date; ?>',
                    color: '#dc3545'
                }
            ]
        });

        // Add booked services to calendar if any
		<?php if(isset($booking->services) && count($booking->services) > 0) {
		foreach($booking->services as $service) {
		if(isset($service['service_date']) && ! empty($service['service_date'])) {
		$eventColor = '#17a2b8'; // Default cyan
		if ($service['status'] == 'completed')
		{
			$eventColor = '#28a745'; // Green
		} else if ($service['status'] == 'cancelled')
		{
			$eventColor = '#dc3545'; // Red
		}
		?>
        $('#bookingCalendar').fullCalendar('renderEvent', {
            title: '<?php echo $service['service_name']; ?>',
            start: '<?php echo $service['service_date']; ?><?php echo isset($service['service_time']) ? "T" . $service['service_time'] : ""; ?>',
            color: '<?php echo $eventColor; ?>'
        }, true);
		<?php
		}
		}
		} ?>
    });
</script>