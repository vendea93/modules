<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo $title; ?>
                            <a href="<?php echo admin_url('hotel_management_system/rooms'); ?>" class="btn btn-default pull-right">
                                <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_rooms'); ?>
                            </a>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'room-form']); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="property_id"><?php echo _l('property'); ?> <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="selectpicker" data-width="100%" data-live-search="true" required>
                                        <option value=""><?php echo _l('select_property'); ?></option>
										<?php foreach ($properties as $property) { ?>
                                            <option value="<?php echo $property['id']; ?>" <?php if (isset($room) && $room->property_id == $property['id'])
											{
												echo 'selected';
											} ?>>
												<?php echo $property['name']; ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"><?php echo _l('name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name" value="<?php if (isset($room))
									{
										echo html_escape($room->name);
									} ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_type"><?php echo _l('room_type'); ?></label>
                                    <select name="room_type" id="room_type" class="selectpicker" data-width="100%">
                                        <option value=""><?php echo _l('select_room_type'); ?></option>
										<?php foreach ($room_types as $key => $type) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (isset($room) && $room->room_type == $key)
											{
												echo 'selected';
											} ?>>
												<?php echo $type; ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?php echo _l('status'); ?></label>
                                    <select name="status" id="status" class="selectpicker" data-width="100%">
										<?php foreach ($room_statuses as $key => $status) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (isset($room) && $room->status == $key)
											{
												echo 'selected';
											} else if ( ! isset($room) && $key == 'available')
											{
												echo 'selected';
											} ?>>
												<?php echo $status; ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="capacity"><?php echo _l('capacity'); ?></label>
                                    <input type="number" class="form-control" name="capacity" id="capacity" min="1" value="<?php if (isset($room))
									{
										echo html_escape($room->capacity);
									} else
									{
										echo '1';
									} ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="room_size"><?php echo _l('room_size'); ?></label>
                                            <input type="number" class="form-control" name="room_size" id="room_size" step="0.01" value="<?php if (isset($room))
											{
												echo html_escape($room->room_size);
											} ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="room_size_unit"><?php echo _l('unit'); ?></label>
                                            <select name="room_size_unit" id="room_size_unit" class="selectpicker" data-width="100%">
                                                <option value="sqm" <?php if (isset($room) && $room->room_size_unit == 'sqm')
												{
													echo 'selected';
												} ?>>sqm
                                                </option>
                                                <option value="sqft" <?php if (isset($room) && $room->room_size_unit == 'sqft')
												{
													echo 'selected';
												} ?>>sqft
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meal_plan"><?php echo _l('meal_plan'); ?></label>
                                    <select name="meal_plan" id="meal_plan" class="selectpicker" data-width="100%">
                                        <option value=""><?php echo _l('select_meal_plan'); ?></option>
										<?php foreach ($meal_plans as $key => $plan) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (isset($room) && $room->meal_plan == $key)
											{
												echo 'selected';
											} ?>>
												<?php echo $plan; ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bed_type"><?php echo _l('bed_type'); ?></label>
                                    <select name="bed_type" id="bed_type" class="selectpicker" data-width="100%">
                                        <option value=""><?php echo _l('select_bed_type'); ?></option>
										<?php foreach ($bed_types as $key => $type) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (isset($room) && $room->bed_type == $key)
											{
												echo 'selected';
											} ?>>
												<?php echo $type; ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="num_beds"><?php echo _l('number_of_beds'); ?></label>
                                    <input type="number" class="form-control" name="num_beds" id="num_beds" min="1" value="<?php if (isset($room))
									{
										echo html_escape($room->num_beds);
									} else
									{
										echo '1';
									} ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="price_per_night"><?php echo _l('price_per_night'); ?> <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="price_per_night" id="price_per_night" min="0" step="0.01" value="<?php if (isset($room))
									{
										echo html_escape($room->price_per_night);
									} ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="cleaning_fee"><?php echo _l('cleaning_fee'); ?></label>
                                    <input type="number" class="form-control" name="cleaning_fee" id="cleaning_fee" min="0" step="0.01" value="<?php if (isset($room))
									{
										echo html_escape($room->cleaning_fee);
									} else
									{
										echo '0.00';
									} ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_rate"><?php echo _l('tax_rate'); ?> (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" id="tax_rate" min="0" step="0.01" value="<?php if (isset($room))
									{
										echo html_escape($room->tax_rate);
									} else
									{
										echo get_option('hotel_management_system_default_tax_rate');
									} ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description"><?php echo _l('description'); ?></label>
                            <textarea name="description" id="description" class="form-control" rows="5"><?php if (isset($room))
								{
									echo html_escape($room->description);
								} ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="amenities"><?php echo _l('amenities'); ?></label>
                            <div class="row">
								<?php
								$selected_amenities = [];
								if (isset($room) && is_array($room->amenities))
								{
									$selected_amenities = $room->amenities;
								}
								$col_size = 3;
								$i = 0;
								foreach ($room_amenities as $key => $amenity)
								{
									if ($i % 4 == 0)
									{
										echo '<div class="clearfix"></div>';
									}
									?>
                                    <div class="col-md-<?php echo $col_size; ?>">
                                        <div class="checkbox">
                                            <input type="checkbox" name="amenities[]" id="amenity_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if (in_array($key, $selected_amenities))
											{
												echo 'checked';
											} ?>>
                                            <label for="amenity_<?php echo $key; ?>"><?php echo $amenity; ?></label>
                                        </div>
                                    </div>
									<?php $i++;
								} ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
						<?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

		<?php if (isset($room)) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('room_images'); ?></h4>
                            <hr class="hr-panel-heading"/>

                            <!-- Upload Images Section -->
                            <div class="form-group">
                                <label for="room_image"><?php echo _l('upload_image'); ?></label>
								<?php echo form_open_multipart(admin_url('hotel_management_system/rooms/upload_image/' . $room->id), ['id' => 'room-images-upload', 'class' => 'dropzone']); ?>
								<?php echo form_close(); ?>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="room-images">
                                        <div class="tw-flex tw-flex-wrap">
											<?php if (isset($room->images) && count($room->images) > 0) { ?>
												<?php foreach ($room->images as $image) { ?>
                                                    <div class="col-md-3 room-image" data-image-id="<?php echo $image['id']; ?>">
                                                        <div class="panel panel-default">
                                                            <div class="panel-heading">
                                                                <div class="pull-right">
                                                                    <a href="#" class="btn btn-danger btn-xs _delete" onclick="deleteRoomImage(<?php echo $image['id']; ?>)"><i class="fa fa-remove"></i></a>
																	<?php if ($image['is_featured'] == 0) { ?>
                                                                        <a href="#" class="btn btn-info btn-xs" onclick="setFeaturedImage(<?php echo $image['id']; ?>)"><i class="fa fa-star"></i></a>
																	<?php } else { ?>
                                                                        <span class="label label-success"><i class="fa fa-star"></i> <?php echo _l('featured'); ?></span>
																	<?php } ?>
                                                                </div>
                                                                <div class="clearfix"></div>
                                                            </div>
                                                            <div class="panel-body">
                                                                <img src="<?php echo base_url($image['path']); ?>" class="img-responsive">
                                                            </div>
                                                        </div>
                                                    </div>
												<?php } ?>
											<?php } else { ?>
                                                <div class="col-md-12 text-center">
                                                    <p><?php echo _l('no_images_found'); ?></p>
                                                </div>
											<?php } ?>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>
                                    <hr/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        appValidateForm($('#room-form'), {
            property_id: 'required',
            name: 'required',
            price_per_night: 'required'
        });
    });

    // Function to delete room image
    function deleteRoomImage(id) {
        if (confirm("<?php echo _l('confirm_action_prompt'); ?>")) {
            $.ajax({
                url: admin_url + 'hotel_management_system/rooms/delete_image/' + id,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Remove image from the gallery
                        $('div.room-image[data-image-id="' + id + '"]').fadeOut(200, function () {
                            $(this).remove();

                            // Show "no images found" message if no images left
                            if ($('#room-images .room-image').length === 0) {
                                $('#room-images .row').html('<div class="col-md-12 text-center"><p><?php echo _l('no_images_found'); ?></p></div>');
                            }
                        });

                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function () {
                    alert_float('danger', '<?php echo _l('error_processing_request'); ?>');
                }
            });
        }
    }

    // Function to set featured image
    function setFeaturedImage(id) {
        $.ajax({
            url: admin_url + 'hotel_management_system/rooms/set_featured_image/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Remove featured labels and restore star icons on all images
                    $('.room-image .panel-heading .label-success').remove();
                    $('.room-image .panel-heading a.btn-info').show();

                    // Add featured label to the selected image
                    var imagePanel = $('div.room-image[data-image-id="' + id + '"] .panel-heading .pull-right');
                    imagePanel.find('a.btn-info').replaceWith('<span class="label label-success"><i class="fa fa-star"></i> <?php echo _l('featured'); ?></span>');

                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function () {
                alert_float('danger', '<?php echo _l('error_processing_request'); ?>');
            }
        });
    }
</script>