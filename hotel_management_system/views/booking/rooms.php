<?php
/** @var string $check_in */
/** @var string $check_out */
/** @var array $rooms */
/** @var array $services */
/** @var float $rooms_price_per_night */
/** @var float $rooms_cleaning_fee */
/** @var float $total */
/** @var int $nights */

?>
<div class="row">
    <div class="col-md-8">
        <div class="panel_s">
            <div class="panel-body">
                <h2>Your Rooms</h2>
                <ul class="nav nav-tabs" id="rooms_list" role="tablist">
					<?php foreach ($rooms as $index => $room) : ?>
                        <li class="nav-item <?php echo($index === 0 ? 'active' : '') ?>" role="presentation" data-index="<?php echo $index ?>">
                            <button class="nav-link"
                                    id="room-<?php echo $room->id ?>-tab"
                                    data-toggle="tab" data-target="#room-<?php echo $room->id ?>"
                                    type="button" role="tab"
                                    aria-controls="room-<?php echo $room->id ?>" aria-selected="<?php echo($index === 0 ? 'true' : 'false') ?>">
								<?php echo $room->name ?>
                            </button>
                        </li>
					<?php endforeach; ?>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
					<?php foreach ($rooms as $room) : ?>
                        <div class="tab-pane active" id="room-<?php echo $room->id ?>" role="tabpanel" aria-labelledby="room-<?php echo $room->id ?>-tab">
                            <h3 class="text-center mbot30"><?php echo $room->name; ?></h3>
                            <div class="room-price-large mbot30">
                                <span class="price"><?php echo app_format_money($room->price_per_night, get_base_currency()); ?></span>
                                <span class="price-label"><?php echo _l('per_night'); ?></span>
                            </div>

                            <div class="room-details">
                                <h3><?php echo _l('room_details'); ?></h3>
                                <p><?php echo $room->description; ?></p>

								<?php if ( $room->cleaning_fee > 0) { ?>
                                    <h4><?php echo _l('cleaning_fee'); ?>:&nbsp<?php echo app_format_money($room->cleaning_fee, get_base_currency()); ?></h4>
								<?php } ?>

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
                                                    <span class="feature-value"><?php echo(empty($room->meal_plan) ? _l('not_including') : _l($room->meal_plan)) ?></span>
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
                                <p><?php echo $room->property->name; ?></p>
                                <p><?php echo $room->property->address; ?></p>
								<?php if ($room->property->description) { ?>
                                    <p><?php echo $room->property->description; ?></p>
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
					<?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel_s">
            <div class="panel-body">
                <div class="booking-sidebar">
                    <h3><?php echo _l('book_these_rooms'); ?></h3>

                    <div class="room-price-large">
                        <span class="price"><?php echo app_format_money($rooms_price_per_night, get_base_currency()); ?></span>
                        <span class="price-label"><?php echo _l('per_night'); ?></span>
                    </div>

					<?php if ($this->session->flashdata('error')) { ?>
                        <div class="alert alert-danger">
							<?php echo $this->session->flashdata('error'); ?>
                        </div>
					<?php } ?>

					<?php echo form_open('hotel_management_system/booking/process', ['id' => 'booking-form']); ?>
                    <input type="hidden" name="check_in" value="<?php echo $this->input->get('check_in') ?>">
                    <input type="hidden" name="check_out" value="<?php echo $this->input->get('check_out') ?>">
					<?php foreach ($rooms as $room): ?>
                        <input type="hidden" name="room_id[]" value="<?php echo $room->id; ?>">
					<?php endforeach; ?>
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
                            <span class="summary-value"><?php echo app_format_money($rooms_price_per_night, get_base_currency()); ?></span>
                        </div>
                        <div class="summary-line">
                            <span class="summary-label"><?php echo _l('cleaning_fee'); ?>:</span>
                            <span class="summary-value"><?php echo app_format_money($rooms_cleaning_fee, get_base_currency()); ?></span>
                        </div>
                        <div class="summary-line total-line" data-total="<?php echo $total ?>">
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
        appValidateForm($('#booking-form'), {
            first_name: 'required',
            last_name: 'required',
            email: 'required|email',
            phone: 'required'
        });


        // Calculate and update booking summary
        function updateBookingSummary() {
            var checkInDate = $('#check_in').datepicker('getDate');
            var checkOutDate = $('#check_out').datepicker('getDate');

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

        // updateBookingSummary();


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