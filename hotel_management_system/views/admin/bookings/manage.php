<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-8">
								<h4 class="no-margin"><?php echo _l('hms_bookings'); ?></h4>
							</div>
							<div class="col-md-4 text-right">
                                <a href="<?php echo admin_url('hotel_management_system/bookings/booking'); ?>"
                                   class="btn btn-primary">
                                    <i class="fa fa-plus"></i> <?php echo _l('new_booking'); ?>
                                </a>
								<a href="<?php echo admin_url('hotel_management_system/bookings/calendar'); ?>" class="btn btn-default mleft5">
									<i class="fa fa-calendar"></i> <?php echo _l('calendar_view'); ?>
								</a>
							</div>
						</div>
						<hr />

						<div class="row">
							<div class="col-md-12">
								<div class="panel-heading filter-heading">
									<h4 class="panel-title">
										<a href="#" class="font-weight-bold" data-toggle="collapse" data-target="#filters">
											<i class="fa fa-filter"></i> <?php echo _l('filters'); ?>
										</a>
									</h4>
								</div>
								<div id="filters" class="panel-collapse collapse">
									<div class="panel-body">
										<div class="row">
											<div class="col-md-3">
												<div class="form-group">
													<label for="booking_status"><?php echo _l('booking_status'); ?></label>
													<select name="booking_status" id="booking_status" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
														<option value=""><?php echo _l('all'); ?></option>
														<?php foreach (hms_get_booking_statuses() as $status => $label) { ?>
															<option value="<?php echo $status; ?>"><?php echo $label; ?></option>
														<?php } ?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="payment_status"><?php echo _l('payment_status'); ?></label>
													<select name="payment_status" id="payment_status" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
														<option value=""><?php echo _l('all'); ?></option>
														<?php foreach (hms_get_payment_statuses() as $status => $label) { ?>
															<option value="<?php echo $status; ?>"><?php echo $label; ?></option>
														<?php } ?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="property"><?php echo _l('property'); ?></label>
													<select name="property" id="property" class="form-control selectpicker" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
														<option value=""><?php echo _l('all'); ?></option>
														<?php
														$this->db->where('status', 'active');
														$properties = $this->db->get(db_prefix() . 'hms_properties')->result_array();
														foreach ($properties as $property) {
															echo '<option value="' . $property['id'] . '">' . $property['name'] . '</option>';
														}
														?>
													</select>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="date_range"><?php echo _l('date_range'); ?></label>
													<div class="input-group">
														<input type="text" name="date_from" id="date_from" class="form-control datepicker" placeholder="<?php echo _l('from_date'); ?>" autocomplete="off">
														<div class="input-group-addon">
															<i class="fa fa-calendar calendar-icon"></i>
														</div>
														<input type="text" name="date_to" id="date_to" class="form-control datepicker" placeholder="<?php echo _l('to_date'); ?>" autocomplete="off">
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<button type="button" id="apply_filters" class="btn btn-primary"><?php echo _l('apply_filters'); ?></button>
												<button type="button" id="clear_filters" class="btn btn-default"><?php echo _l('clear_filters'); ?></button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row mtop15">
							<div class="col-md-12">
								<?php
								$table_data = [
									_l('id'),
									_l('booking_reference'),
									_l('room_property'),
									_l('guest_name'),
									_l('guest_email'),
									_l('check_in_date'),
									_l('check_out_date'),
									_l('total_amount'),
									_l('booking_status'),
									_l('payment_status'),
									_l('datecreated'),
									_l('options'),
								];

								render_datatable($table_data, 'bookings', [], [
									'data-last-order-identifier' => 'bookings',
									'data-default-order' => get_table_last_order('bookings'),
								]);
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {
        // Initialize datatables
        var bookingsServerParams = {};

        // Add filters to datatable params
        $.each($('._hidden_inputs._filters input'), function() {
            bookingsServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });

        initDataTable('.table-bookings', admin_url + 'hotel_management_system/bookings/table', undefined, undefined, bookingsServerParams, [0, 'desc']);

        // Initialize datepickers
        $('.datepicker').datetimepicker({
            format: 'YYYY-MM-DD',
            icons: {
                up: "fa fa-chevron-up",
                down: "fa fa-chevron-down",
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right'
            }
        });

        // Apply filters
        $('#apply_filters').on('click', function() {
            $('.table-bookings').DataTable().ajax.reload();
        });

        // Clear filters
        $('#clear_filters').on('click', function() {
            $('#booking_status').val('').selectpicker('refresh');
            $('#payment_status').val('').selectpicker('refresh');
            $('#property').val('').selectpicker('refresh');
            $('#date_from').val('');
            $('#date_to').val('');
            $('.table-bookings').DataTable().ajax.reload();
        });

        // Add filters to datatable
        $('.table-bookings').on('draw.dt', function() {
            var bookingStatus = $('#booking_status').val();
            var paymentStatus = $('#payment_status').val();
            var property = $('#property').val();
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();

            var urlParams = '';
            if (bookingStatus) urlParams += '&booking_status=' + bookingStatus;
            if (paymentStatus) urlParams += '&payment_status=' + paymentStatus;
            if (property) urlParams += '&property=' + property;
            if (dateFrom) urlParams += '&date_from=' + dateFrom;
            if (dateTo) urlParams += '&date_to=' + dateTo;

            $('._filter_data').append('<input type="hidden" name="booking_status" value="' + bookingStatus + '">');
            $('._filter_data').append('<input type="hidden" name="payment_status" value="' + paymentStatus + '">');
            $('._filter_data').append('<input type="hidden" name="property" value="' + property + '">');
            $('._filter_data').append('<input type="hidden" name="date_from" value="' + dateFrom + '">');
            $('._filter_data').append('<input type="hidden" name="date_to" value="' + dateTo + '">');
        });
    });
</script>