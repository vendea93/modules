<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('hotel_management_system/bookings'); ?>"
                               class="btn btn-default pull-left display-block mright5">
                                <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_bookings'); ?>
                            </a>
                            <a href="<?php echo admin_url('hotel_management_system/bookings/delete/' . $booking->id); ?>"
                               class="btn btn-danger pull-left display-block mright5 _delete">
                                <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                            </a>
                            <a href="<?php echo admin_url('hotel_management_system/bookings/booking/' . $booking->id); ?>"
                               class="btn btn-info pull-left display-block">
                                <i class="fa fa-pen"></i> <?php echo _l('edit'); ?>
                            </a>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading"/>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="booking-heading">
									<?php echo _l('booking_reference') . ': ' . $booking->booking_reference; ?>
									<?php
									$booking_badge = '';
									$booking_class = '';
									switch ($booking->booking_status)
									{
										case 'confirmed':
											$booking_class = 'success';
											break;
										case 'cancelled':
											$booking_class = 'danger';
											break;
										case 'checked_in':
											$booking_class = 'info';
											break;
										case 'checked_out':
											$booking_class = 'default';
											break;
										default:
											$booking_class = 'warning';
											break;
									}
									$booking_badge = '<span class="label label-' . $booking_class . ' mleft5">' . _l($booking->booking_status) . '</span>';
									echo $booking_badge;
									?>
                                </h4>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="bold"><?php echo _l('property_details'); ?></h4>
                                <address>
                                    <strong><?php echo $booking->property_name; ?></strong><br>
									<?php echo $booking->property_address; ?><br>
									<?php echo $booking->property_city . ', ' . get_country_name($booking->property_country); ?>
                                </address>
                            </div>
                            <div class="col-md-6">
                                <h4 class="bold"><?php echo _l('customer_details'); ?></h4>
                                <address>
                                    <strong><?php echo $booking->guest_name; ?></strong><br>
									<?php echo _l('email') . ': ' . $booking->guest_email; ?><br>
									<?php echo _l('phone') . ': ' . $booking->guest_phone; ?>
                                </address>
                            </div>
                        </div>

                        <hr/>

                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="bold"><?php echo _l('booking_details'); ?></h4>
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <td class="bold"><?php echo _l('check_in_date'); ?></td>
                                        <td><?php echo _d($booking->check_in_date); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo _l('check_out_date'); ?></td>
                                        <td><?php echo _d($booking->check_out_date); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo _l('total_nights'); ?></td>
                                        <td><?php echo $booking->total_nights; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo _l('adults'); ?></td>
                                        <td><?php echo $booking->adults; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?php echo _l('children'); ?></td>
                                        <td><?php echo $booking->children; ?></td>
                                    </tr>
									<?php if ( ! empty($booking->special_requests)) { ?>
                                        <tr>
                                            <td class="bold"><?php echo _l('special_requests'); ?></td>
                                            <td><?php echo $booking->special_requests; ?></td>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="bold"><?php echo _l('room_details'); ?></h4>
								<?php foreach ($booking->rooms as $index => $room) : ?>
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td class="bold"><?php echo _l('room') . ' ' . ($index + 1) ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('hotel_management_system/rooms/room/' . $booking->room_id); ?>">
													<?php echo $room->room_name; ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('room_type'); ?></td>
                                            <td><?php echo _l($room->room_type); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('room_price'); ?></td>
                                            <td><?php echo app_format_money($room->room_price, get_base_currency()); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
								<?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Additional Services Section -->
						<?php if (count($booking->services) > 0) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="bold"><?php echo _l('additional_services'); ?></h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th><?php echo _l('service'); ?></th>
                                                <th><?php echo _l('service_date'); ?></th>
                                                <th><?php echo _l('quantity'); ?></th>
                                                <th><?php echo _l('price'); ?></th>
                                                <th><?php echo _l('total'); ?></th>
                                                <th><?php echo _l('status'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php foreach ($booking->services as $service) { ?>
                                                <tr>
                                                    <td><?php echo $service['service_name']; ?></td>
                                                    <td><?php echo _d($service['service_date']); ?></td>
                                                    <td><?php echo $service['quantity']; ?></td>
                                                    <td><?php echo app_format_money($service['price'], get_base_currency()); ?></td>
                                                    <td><?php echo app_format_money($service['total'], get_base_currency()); ?></td>
                                                    <td>
                                                    <span class="label label-<?php echo $service['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo _l($service['status']); ?>
                                                    </span>
                                                    </td>
                                                </tr>
											<?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
						<?php } ?>

                        <!-- Booking Payment Summary -->
                        <hr/>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="bold"><?php echo _l('payment_summary'); ?></h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td class="bold"><?php echo _l('room_price'); ?></td>
                                            <td class="text-right"><?php echo app_format_money($booking->room_price, get_base_currency()); ?></td>
                                        </tr>
										<?php if ($booking->cleaning_fee > 0) { ?>
                                            <tr>
                                                <td class="bold"><?php echo _l('cleaning_fee'); ?></td>
                                                <td class="text-right"><?php echo app_format_money($booking->cleaning_fee, get_base_currency()); ?></td>
                                            </tr>
										<?php } ?>
										<?php if ($booking->additional_services > 0) { ?>
                                            <tr>
                                                <td class="bold"><?php echo _l('additional_services'); ?></td>
                                                <td class="text-right"><?php echo app_format_money($booking->additional_services, get_base_currency()); ?></td>
                                            </tr>
										<?php } ?>
                                        <tr>
                                            <td class="bold"><?php echo _l('taxes'); ?></td>
                                            <td class="text-right"><?php echo app_format_money($booking->taxes, get_base_currency()); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bold"><?php echo _l('total_amount'); ?></td>
                                            <td class="text-right text-danger bold"><?php echo app_format_money($booking->total_amount, get_base_currency()); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="bold"><?php echo _l('additional_information'); ?></h4>
                                <hr class="hr-panel-heading"/>

                                <div class="row">
                                    <div class="col-md-6">
                                        <p><?php echo _l('datecreated'); ?>:</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-right"><?php echo _dt($booking->datecreated); ?></p>
                                    </div>
									<?php if ($booking->datemodified) { ?>
                                        <div class="col-md-6">
                                            <p><?php echo _l('datemodified'); ?>:</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-right"><?php echo _dt($booking->datemodified); ?></p>
                                        </div>
									<?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Booking Status Panel -->
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('booking_status'); ?></h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open(admin_url('hotel_management_system/bookings/update_status')); ?>
						<?php echo form_hidden('booking_id', $booking->id); ?>
                        <div class="form-group">
                            <label for="status"><?php echo _l('booking_status'); ?></label>
                            <select name="status" id="status" class="form-control selectpicker" data-width="100%">
								<?php foreach ($booking_statuses as $status => $label) { ?>
                                    <option value="<?php echo $status; ?>" <?php if ($booking->booking_status == $status)
									{
										echo 'selected';
									} ?>><?php echo $label; ?></option>
								<?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-info btn-block"><?php echo _l('update_status'); ?></button>
                        </div>
						<?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Payment Status Panel -->
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('payment_status'); ?></h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open(admin_url('hotel_management_system/bookings/update_payment_status')); ?>
						<?php echo form_hidden('booking_id', $booking->id); ?>
                        <div class="form-group">
                            <label for="status"><?php echo _l('payment_status'); ?></label>
                            <select name="status" id="payment_status" class="form-control selectpicker"
                                    data-width="100%">
								<?php foreach ($payment_statuses as $status => $label) { ?>
                                    <option value="<?php echo $status; ?>" <?php if ($booking->payment_status == $status)
									{
										echo 'selected';
									} ?>><?php echo $label; ?></option>
								<?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit"
                                    class="btn btn-info btn-block"><?php echo _l('update_status'); ?></button>
                        </div>
						<?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Invoice Panel -->
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('invoice'); ?></h4>
                        <hr class="hr-panel-heading"/>

						<?php if ($booking->invoice_id) { ?>
                            <div class="text-center mtop20 mbot20">
                                <a href="<?php echo admin_url('invoices/list_invoices/' . $booking->invoice_id); ?>"
                                   class="btn btn-primary">
                                    <i class="fa fa-eye"></i> <?php echo _l('view_invoice'); ?>
                                </a>

                                <div class="mtop10">
									<?php echo format_invoice_status($booking->invoice->status) ?>
                                </div>
                            </div>
						<?php } else { ?>
                            <div class="text-center mtop20 mbot20">
                                <a href="<?php echo admin_url('hotel_management_system/bookings/generate_invoice/' . $booking->id); ?>"
                                   class="btn btn-success">
                                    <i class="fa fa-file-text-o"></i> <?php echo _l('generate_invoice'); ?>
                                </a>
                                <p class="text-muted mtop10"><?php echo _l('no_invoice_generated'); ?></p>
                            </div>
						<?php } ?>
                    </div>
                </div>

                <!-- Client Panel -->
				<?php if (isset($booking->userid) && $booking->userid > 0) { ?>
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="bold"><?php echo _l('client'); ?></h4>
                            <hr class="hr-panel-heading"/>

                            <div class="mtop20 mbot20">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4><?php echo $booking->company ? $booking->company : $booking->firstname . ' ' . $booking->lastname; ?></h4>
                                        <p>
                                            <a href="<?php echo admin_url('clients/client/' . $booking->userid); ?>"
                                               class="btn btn-default">
                                                <i class="fa fa-user"></i> <?php echo _l('view_client_profile'); ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php } ?>

                <!-- Dates and Created Info -->
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        // Initialize selectpicker
        $('.selectpicker').selectpicker();
    });
</script>