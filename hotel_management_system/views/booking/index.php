<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mbot30"><?php echo _l('hotel_booking'); ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel_s">
            <div class="panel-body">
				<?php echo form_open('hotel_management_system/booking/get_available_rooms', ['id' => 'booking-search-form']); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hotel_id"><?php echo _l('hotel'); ?></label>
                            <select name="hotel_id" id="hotel_id" class="form-control selectpicker" data-live-search="true">
                                <option value=""><?php echo _l('all_hotels'); ?></option>
								<?php foreach ($properties as $property) { ?>
                                    <option value="<?php echo $property['id']; ?>"><?php echo $property['name']; ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="check_in"><?php echo _l('check_in_date'); ?></label>
                            <div class="input-group date">
                                <input type="text" id="check_in" name="check_in" class="form-control datepicker" autocomplete="off" required value="<?php echo date('Y-m-d') ?>">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar calendar-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="check_out"><?php echo _l('check_out_date'); ?></label>
                            <div class="input-group date">
                                <input type="text" id="check_out" name="check_out" class="form-control datepicker" autocomplete="off" required
                                       value="<?php echo ((new DateTime())->add(DateInterval::createFromDateString('1 day')))->format('Y-m-d') ?>">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar calendar-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-info btn-block"><?php echo _l('search_available_rooms'); ?></button>
                        </div>
                    </div>
                </div>
				<?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<div class="row mbot30">
    <div class="col-md-12">
        <div id="availability-message" class="alert alert-info" style="display: none;"></div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div id="available-rooms-container"></div>
    </div>
</div>

<script>
    $(function () {
        $(document).ready(function () {
            const currencySymbol = '<?php echo get_base_currency()->symbol; ?>';
            // Initialize datepickers
            // var dateFormat = app.options.date_format;
            // $('.datepicker').datepicker({
            //     format: dateFormat,
            //     autoclose: true,
            //     startDate: new Date(),
            //     todayHighlight: true
            // });
            //
            // // Set default check-in and check-out dates
            // var today = new Date();
            // var tomorrow = new Date(today);
            // tomorrow.setDate(tomorrow.getDate() + 1);
            //
            // $('#check_in').datepicker('setDate', today);
            // $('#check_out').datepicker('setDate', tomorrow);
            //
            // // Ensure check-out date is after check-in date
            $('#check_in').on('changeDate', function (e) {
                var checkInDate = $('#check_in').datepicker('getDate');
                var checkOutDate = $('#check_out').datepicker('getDate');
                console.log(checkInDate);
                if (checkOutDate <= checkInDate) {
                    var newCheckOut = new Date(checkInDate);
                    newCheckOut.setDate(newCheckOut.getDate() + 1);
                    $('#check_out').datepicker('setDate', newCheckOut);
                }

                $('#check_out').datepicker('setStartDate', checkInDate);
            });

            // Check dates availability when changed
            $('#hotel_id, #check_in, #check_out').change(function () {
                var hotelId = $('#hotel_id').val();
                var checkIn = $('#check_in').val();
                var checkOut = $('#check_out').val();

                if (hotelId && checkIn && checkOut) {
                    checkDatesAvailability(hotelId, checkIn, checkOut);
                }
            });

            // Handle form submission
            $('#booking-search-form').on('submit', function (e) {
                e.preventDefault();
                var hotelId = $('#hotel_id').val() || '';
                var checkIn = $('#check_in').val();
                var checkOut = $('#check_out').val();

                if (checkIn && checkOut) {
                    getAvailableRooms(hotelId, checkIn, checkOut);
                }
            });

            function checkDatesAvailability(hotelId, checkIn, checkOut) {
                $.ajax({
                    url: site_url + 'hotel_management_system/booking/check_dates_availability',
                    type: 'POST',
                    data: {
                        hotel_id: hotelId,
                        check_in: checkIn,
                        check_out: checkOut
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#availability-message').removeClass('alert-danger').addClass('alert-info').html(response.message).show();
                        } else {
                            $('#availability-message').removeClass('alert-info').addClass('alert-danger').html(response.message).show();
                        }
                    }
                });
            }

            function getAvailableRooms(hotelId, checkIn, checkOut) {
                $.ajax({
                    url: site_url + 'hotel_management_system/booking/get_available_rooms',
                    type: 'POST',
                    data: {
                        hotel_id: hotelId,
                        check_in: checkIn,
                        check_out: checkOut
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#available-rooms-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
                    },
                    success: function (response) {
                        if (response.success && response.html) {
                            $('#available-rooms-container').html(response.html);
                        } else {
                            $('#available-rooms-container').html('<div class="alert alert-warning"><?php echo _l("no_rooms_available_for_selected_dates"); ?></div>');
                        }
                    },
                    error: function () {
                        $('#available-rooms-container').html('<div class="alert alert-danger"><?php echo _l("error_loading_rooms"); ?></div>');
                    }
                });
            }
        })
    });
</script>