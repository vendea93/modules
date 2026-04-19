<?php
/** @var array $rooms */
/** @var array $room_groups */
/** @var string $check_in */
/** @var string $check_out */
?>
<?php if (empty($room_groups)) : ?>
    <div class="alert alert-warning"><?php echo _l("no_rooms_available_for_selected_dates"); ?></div>
<?php else: ?>
    <div class="panel-body">
		<?php echo form_open('hotel_management_system/booking/rooms', ['id' => 'booking-rooms', 'method' => 'get']); ?>
        <input type="hidden" id="book_check_in" name="check_in" value="<?php echo $check_in ?>"/>
        <input type="hidden" id="book_check_out" name="check_out" value="<?php echo $check_out ?>">
        <div class="mbot15" id="rooms_booking">
            <h3><?php echo _l('selected_rooms') ?>&nbsp;<span id="selected_rooms_count">(0):&nbsp;</span><strong id="selected_rooms"></strong></h3>
            <div class="alert alert-danger hidden"><?php echo _l('only_in_one_property') ?></div>
            <button id="book_rooms" disabled type="submit" class="btn btn-info"><?php echo _l('book_rooms') ?></button>
        </div>
		<?php foreach ($room_groups as $property_name => $rooms) : ?>
            <div class="row">
                <h4><?php echo sprintf('%s: %s', _l('property'), $property_name) ?></h4>
                <div class="row">
					<?php foreach ($rooms as $room) : ?>
                        <label class="col-md-4 mbot30 available-room">
                            <input data-property-id="<?php echo $room['property_id'] ?>" name="rooms[]" value="<?php echo $room['id'] ?>" type="checkbox" style="width: 0; height: 0; opacity: 0">
                            <div class="panel_s">
                                <div class="panel-body"><h4 class="room-name"><?php echo $room['name'] ?></h4>
									<?php if (isset($room['featured_image'])) : ?>
                                        <div class="room-image-container">
                                            <img src="<?php echo site_url($room['featured_image']) ?>" class="img-responsive" alt="<?php echo $room['name'] ?>">
                                        </div>
									<?php endif ?>
                                    <p><?php echo $room['description'] ?></p>
                                    <div class="room-features row">
										<?php if (isset($room['capacity'])) : ?>
                                            <div class="col-md-6"><i class="fa fa-user"></i>&nbsp;<?php echo $room['capacity'] ?>&nbsp;<?php echo($room['capacity'] > 1 ? _l("guests") : _l("guest")) ?></div>
										<?php endif ?>
										<?php if (isset($room['room_size'])) : ?>
                                            <div class="col-md-6"><i class="fa fa-expand"></i>&nbsp;<?php echo sprintf('%s %s', $room['room_size'], _l($room['room_size_unit'])) ?></div>
										<?php endif ?>
										<?php if (isset($room['bed_type']) && isset($room['num_beds'])) : ?>
                                            <div class="col-md-6"><i class="fa fa-bed"></i>&nbsp;<?php echo sprintf('%s x %s', $room['num_beds'], _l($room['bed_type'])) ?></div>
										<?php endif ?>
                                        <div class="col-md-6"><i class="fa fa-cutlery"></i>&nbsp;<?php echo _l($room['meal_plan'] ?? 'not_including') ?></div>
										<?php if (isset($room['amenities']) && count($room['amenities'])) : ?>
											<?php
											$amenities = $room['amenities'];
											$amenities = array_map(function ($amenity) {
												return _l($amenity);
											}, $amenities);

											sort($amenities);
											?>
                                            <div class="col-md-12"><i class="fa fa-list-ol"></i>&nbsp;<?php echo implode(', ', $amenities) ?></div>
										<?php endif ?>
                                    </div>
                                    <div class="room-price"><span class="price">$123.00</span><span class="price-label">Per Night</span></div>
                                    <div class="text-center"><a href="<?php echo site_url(sprintf('hotel_management_system/booking/room/%s?check_in=%s&check_out=%s', $room['id'], $check_in, $check_out)) ?>" class="btn btn-info">Book now</a>
                                    </div>
                                </div>
                            </div>
                        </label>
					<?php endforeach; ?>
                </div>
                <hr/>
            </div>
		<?php endforeach; ?>
    </div>
	<?php echo form_close(); ?>
    </div>
<?php endif ?>
<script>
    $(document).ready(function () {
        const roomsBookingBlock = $('#rooms_booking');
        let selectedProperties = [];
        $('input[name="rooms[]"]').each(function () {
            $(this).on('change', function () {
                selectedProperties = [];
                $(this).parent().find('.panel_s').toggleClass('active');

                const selectedRooms = $('input[name="rooms[]"]:checked');
                $('#selected_rooms_count').text('(' + selectedRooms.length + ')');
                if (selectedRooms.length === 0) {
                    $('#book_rooms').attr('disabled', 'true');
                } else {
                    $('#book_rooms').removeAttr('disabled');
                }

                let roomsName = '';
                selectedRooms.each(function () {
                    selectedProperties.push($(this).data('property-id'));
                    const roomName = $(this).parent().find('.room-name').text();
                    if (roomName.length === 0) {
                        roomsName = roomName;
                    } else {
                        roomsName += ', ' + roomName;
                    }
                });
                $('#selected_rooms').text(roomsName);
                console.log(selectedProperties);
                selectedProperties = [...new Set(selectedProperties)]
                if (selectedProperties.length === 1 || selectedProperties.length === 1) {
                    $(roomsBookingBlock).find('button').removeAttr('disabled');
                    $(roomsBookingBlock).find('.alert').hide();
                } else {
                    $(roomsBookingBlock).find('button').attr('disabled', 'true');
                    $(roomsBookingBlock).find('.alert').show();
                }
                console.log(selectedProperties);
            })
        })
    })
</script>
