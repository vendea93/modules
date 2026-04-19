<?php
/** @var string $check_in */
/** @var string $check_out */
/** @var float $price_per_night */
/** @var float $total */
/** @var int $nights */

?>
<div class="row">
    <div class="col-md-12">
        <h1 class="text-center mbot30"><?php echo $room->name; ?></h1>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="panel_s">
            <div class="panel-body">
                <div class="room-details">
                    <h3><?php echo _l('room_details'); ?></h3>
                    <p><?php echo $room->description; ?></p>

					<?php if ( ! empty($room->amenities)) { ?>
                        <h4><?php echo _l('amenities'); ?></h4>
                        <ul class="amenities-list">
							<?php
							$amenities = $room->amenities;
							?>
							<?php foreach ($amenities as $amenity): ?>
                                <li><i class="fa fa-check"></i> <?php echo _l($amenity); ?></li>
							<?php endforeach; ?>
                        </ul>
					<?php } ?>

                    <div class="room-features-large">
                        <div class="row">
							<?php if ($room->capacity) { ?>
                                <div class="col-md-4">
                                    <div class="feature-box">
                                        <i class="fa fa-user feature-icon"></i>
                                        <div class="feature-text">
                                            <span class="feature-label"><?php echo _l('max_guests'); ?></span>
                                            <span class="feature-value"><?php echo $room->capacity; ?></span>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>

							<?php if ($room->room_size) { ?>
                                <div class="col-md-4">
                                    <div class="feature-box">
                                        <i class="fa fa-expand feature-icon"></i>
                                        <div class="feature-text">
                                            <span class="feature-label"><?php echo _l('room_size'); ?></span>
                                            <span class="feature-value"><?php echo sprintf('%s %s', $room->room_size, $room->room_size_unit) ?></span>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>
                            <div class="col-md-4">
                                <div class="feature-box">
                                    <i class="fa fa-cutlery feature-icon"></i>
                                    <div class="feature-text">
                                        <span class="feature-label"><?php echo _l('meal_plan'); ?></span>
                                        <span class="feature-value"><?php echo _l($room->meal_plan ?? 'not_including') ?></span>
                                    </div>
                                </div>
                            </div>
							<?php if ($room->bed_type) { ?>
                                <div class="col-md-4">
                                    <div class="feature-box">
                                        <i class="fa fa-bed feature-icon"></i>
                                        <div class="feature-text">
                                            <span class="feature-label"><?php echo _l('bed_type'); ?></span>
                                            <span class="feature-value"><?php echo _l($room->bed_type); ?></span>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>

							<?php if ($room->num_beds) { ?>
                                <div class="col-md-4">
                                    <div class="feature-box">
                                        <i class="fa fa-bed feature-icon"></i>
                                        <div class="feature-text">
                                            <span class="feature-label"><?php echo _l('num_beds'); ?></span>
                                            <span class="feature-value"><?php echo $room->num_beds; ?></span>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>

                        </div>
                    </div>

                    <h4><?php echo _l('hotel_information'); ?></h4>
                    <p><?php echo $hotel->name; ?></p>
                    <p><?php echo $hotel->address; ?></p>
					<?php if ($hotel->description) { ?>
                        <p><?php echo $hotel->description; ?></p>
					<?php } ?>
                </div>
                <div class="room-details room-images">
                    <h3><?php echo _l('room_photos'); ?></h3>
					<?php
					$images = $room->images;
					?>
					<?php if (count($images)) : ?>
						<?php foreach ($images as $image): ?>
                            <div class="room-image-container-large">
                                <img src="<?php echo site_url($image['path']) ?>" class="img-responsive" alt="<?php echo $room->name; ?>"/>
                            </div>
						<?php endforeach; ?>
					<?php else: ?>
                        <h2><?php _l('no_images_found') ?></h2>
					<?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <div class="booking-sidebar">
                    <h3><?php echo _l('book_this_room'); ?></h3>

                    <div class="room-price-large">
                        <span class="price"><?php echo app_format_money($room->price_per_night, get_base_currency()); ?></span>
                        <span class="price-label"><?php echo _l('per_night'); ?></span>
                    </div>

					<?php if ($this->session->flashdata('error')) { ?>
                        <div class="alert alert-danger">
							<?php echo $this->session->flashdata('error'); ?>
                        </div>
					<?php } ?>

					<?php echo form_open('hotel_management_system/booking/process', ['id' => 'booking-form']); ?>
                    <input type="hidden" name="room_id" value="<?php echo $room->id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo $this->input->get('check_in') ?>">
                    <input type="hidden" name="check_out" value="<?php echo $this->input->get('check_out') ?>">
                    <div class="booking-summary">
                        <h4><?php echo _l('booking_summary'); ?></h4>
                        <div class="summary-line">
                            <span class="summary-label"><?php echo _l('check_in_date'); ?>:</span>
                            <span class="summary-value"><?php echo date('l, j F Y', strtotime($check_in)) ?></span>
                        </div>
                        <div class="summary-line">
                            <span class="summary-label"><?php echo _l('check_out_date'); ?>:</span>
                            <span class="summary-value"><?php echo date('l, j F Y', strtotime($check_out)) ?></span>
                        </div>
                        <div class="summary-line">
                            <span class="summary-label"><?php echo _l('nights'); ?>:</span>
                            <span class="summary-value"><?php echo $nights ?></span>
                        </div>
                        <div class="summary-line">
                            <span class="summary-label"><?php echo _l('price_per_night'); ?>:</span>
                            <span class="summary-value"><?php echo app_format_money($price_per_night, get_base_currency()); ?></span>
                        </div>
                        <div class="summary-line total-line">
                            <span class="summary-label"><?php echo _l('total_price'); ?>:</span>
                            <span class="summary-value"><?php echo app_format_money($total, get_base_currency()); ?></span>
                        </div>
                    </div>

                    <hr>

                    <h4><?php echo _l('guest_information'); ?></h4>

                    <div class="form-group">
                        <label for="first_name"><?php echo _l('first_name'); ?></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name"><?php echo _l('last_name'); ?></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><?php echo _l('email'); ?></label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="phone"><?php echo _l('phone'); ?></label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-block btn-lg"><?php echo _l('continue_to_payment'); ?></button>
                    </div>

					<?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        // Make sure jQuery UI datepicker is available
        if (typeof $.fn.datepicker === 'undefined') {
            // If PerfexCRM datepicker isn't available, load it dynamically
            $.getScript(site_url + 'assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js', function () {
                initDatepickers();
            });
        } else {
            initDatepickers();
        }

        function initDatepickers() {
            // Initialize datepickers
            var dateFormat = app.options.date_format;
            $('.datepicker').datepicker({
                format: dateFormat,
                autoclose: true,
                startDate: new Date(),
                todayHighlight: true
            });

            // Set default check-in and check-out dates if not provided in URL
            if (!$('#check_in').val()) {
                var today = new Date();
                $('#check_in').datepicker('setDate', today);
            }

            if (!$('#check_out').val()) {
                var checkInDate = $('#check_in').datepicker('getDate');
                var tomorrow = new Date(checkInDate);
                tomorrow.setDate(tomorrow.getDate() + 1);
                $('#check_out').datepicker('setDate', tomorrow);
            }

            // Update booking summary
            updateBookingSummary();
        }

        // Ensure check-out date is after check-in date
        $('#check_in').on('changeDate', function (e) {
            var checkInDate = $('#check_in').datepicker('getDate');
            var checkOutDate = $('#check_out').datepicker('getDate');

            if (checkOutDate <= checkInDate) {
                var newCheckOut = new Date(checkInDate);
                newCheckOut.setDate(newCheckOut.getDate() + 1);
                $('#check_out').datepicker('setDate', newCheckOut);
            }

            $('#check_out').datepicker('setStartDate', checkInDate);
            updateBookingSummary();
        });

        $('#check_out').on('changeDate', function () {
            updateBookingSummary();
        });

        // Calculate and update booking summary
        function updateBookingSummary() {
            var checkInDate = $('#check_in').datepicker('getDate');
            var checkOutDate = $('#check_out').datepicker('getDate');

            if (checkInDate && checkOutDate) {
                // Calculate the number of nights
                var timeDiff = Math.abs(checkOutDate.getTime() - checkInDate.getTime());
                var nights = Math.ceil(timeDiff / (1000 * 3600 * 24));

                // Update nights count
                $('#nights-count').text(nights);

                // Calculate and update total price
                var pricePerNight = <?php echo $room->price_per_night; ?>;
                var totalPrice = nights * pricePerNight;

                // Format the price
                var formattedPrice = app.options.currency_symbol + totalPrice.toFixed(2);
                $('#total-price').text(formattedPrice);

                // Show booking summary
                $('.booking-summary').slideDown();
            }
        }

        // Initialize booking summary on page load
        updateBookingSummary();

        // Form validation
        $('#booking-form').on('submit', function (e) {
            var checkInDate = $('#check_in').datepicker('getDate');
            var checkOutDate = $('#check_out').datepicker('getDate');

            if (checkOutDate <= checkInDate) {
                e.preventDefault();
                alert('<?php echo _l("check_out_date_must_be_after_check_in_date"); ?>');
                return false;
            }

            return true;
        });
    });
</script>

<style>
    .room-image-container-large {
        height: 400px;
        overflow: hidden;
        margin-bottom: 30px;
        position: relative;
    }

    .room-image-container-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .room-details {
        margin-bottom: 30px;
    }

    .amenities-list {
        list-style-type: none;
        padding: 0;
        margin: 20px 0;
        columns: 2;
    }

    .amenities-list li {
        padding: 5px 0;
        break-inside: avoid;
    }

    .amenities-list li i {
        color: #03a9f4;
        margin-right: 10px;
    }

    .room-features-large {
        margin: 30px 0;
    }

    .feature-box {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .feature-icon {
        font-size: 24px;
        margin-right: 15px;
        color: #03a9f4;
    }

    .feature-text {
        display: flex;
        flex-direction: column;
    }

    .feature-label {
        font-size: 14px;
        color: #777;
    }

    .feature-value {
        font-size: 16px;
        font-weight: bold;
    }

    .booking-sidebar {
        padding: 15px 0;
    }

    .room-price-large {
        text-align: center;
        margin-bottom: 30px;
    }

    .room-price-large .price {
        font-size: 36px;
        font-weight: bold;
        color: #03a9f4;
    }

    .room-price-large .price-label {
        display: block;
        font-size: 16px;
        color: #777;
    }

    .booking-summary {
        background-color: #f9f9f9;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .total-line {
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px solid #ddd;
        font-weight: bold;
        font-size: 18px;
    }

    .summary-label {
        color: #777;
    }

    .summary-value {
        font-weight: bold;
    }
</style>