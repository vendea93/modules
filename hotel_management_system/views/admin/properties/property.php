<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="<?php echo ( isset($property) ? 'col-md-8' : 'col-md-12') ?>">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading"/>
						<?php echo form_open_multipart($this->uri->uri_string(), ['id' => 'property-form']); ?>

                        <!-- Landlord Selection -->
                        <div class="form-group">
                            <label for="landlord_id" class="control-label"><?php echo _l('landlord'); ?> <span
                                        class="text-danger">*</span></label>
                            <select name="landlord_id" id="landlord_id" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                                <option value=""></option>
								<?php foreach ($landlords as $landlord) { ?>
                                    <option value="<?php echo $landlord['id']; ?>" <?php if (isset($property) && $property->landlord_id == $landlord['id']) {
										echo 'selected';
									} ?>><?php echo $landlord['name']; ?></option>
								<?php } ?>
                            </select>
                        </div>

                        <!-- Property Name -->
                        <div class="form-group">
                            <label for="name" class="control-label"><?php echo _l('property_name'); ?> <span
                                        class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="<?php echo(isset($property) ? $property->name : ''); ?>" required>
                        </div>

                        <!-- Property Type -->
                        <div class="form-group">
                            <label for="property_type" class="control-label"><?php echo _l('property_type'); ?></label>
                            <select name="property_type" id="property_type" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <option value="hotel" <?php if (isset($property) && $property->property_type == 'hotel') {
									echo 'selected';
								} ?>><?php echo _l('hotel'); ?></option>
                                <option value="apartment" <?php if (isset($property) && $property->property_type == 'apartment') {
									echo 'selected';
								} ?>><?php echo _l('apartment'); ?></option>
                                <option value="villa" <?php if (isset($property) && $property->property_type == 'villa') {
									echo 'selected';
								} ?>><?php echo _l('villa'); ?></option>
                                <option value="resort" <?php if (isset($property) && $property->property_type == 'resort') {
									echo 'selected';
								} ?>><?php echo _l('resort'); ?></option>
                                <option value="house" <?php if (isset($property) && $property->property_type == 'house') {
									echo 'selected';
								} ?>><?php echo _l('house'); ?></option>
                                <option value="other" <?php if (isset($property) && $property->property_type == 'other') {
									echo 'selected';
								} ?>><?php echo _l('other'); ?></option>
                            </select>
                        </div>

                        <!-- Property Address -->
                        <div class="form-group">
                            <label for="address" class="control-label"><?php echo _l('property_address'); ?> <span
                                        class="text-danger">*</span></label>
                            <textarea id="address" name="address" class="form-control" rows="4"
                                      required><?php echo(isset($property) ? $property->address : ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- City -->
                                <div class="form-group">
                                    <label for="city" class="control-label"><?php echo _l('property_city'); ?></label>
                                    <input type="text" id="city" name="city" class="form-control"
                                           value="<?php echo(isset($property) ? $property->city : ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- State/Province -->
                                <div class="form-group">
                                    <label for="state" class="control-label"><?php echo _l('property_state'); ?></label>
                                    <input type="text" id="state" name="state" class="form-control"
                                           value="<?php echo(isset($property) ? $property->state : ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Postal Code -->
                                <div class="form-group">
                                    <label for="postal_code"
                                           class="control-label"><?php echo _l('property_postal_code'); ?></label>
                                    <input type="text" id="postal_code" name="postal_code" class="form-control"
                                           value="<?php echo(isset($property) ? $property->postal_code : ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
								<?php
								$countries = get_all_countries();
								$customer_default_country = get_option('customer_default_country');
								$selected = (isset($property) ? $property->country : $customer_default_country);
								?>

								<?php
								echo render_select('country', $countries, ['country_id', ['short_name']], 'property_country', $selected, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
								?>
                            </div>
                        </div>

                        <!-- Property Description -->
                        <div class="form-group">
                            <label for="description"
                                   class="control-label"><?php echo _l('property_description'); ?></label>
							<?php echo render_textarea('description', '', (isset($property) ? $property->description : ''), [], [], '', 'tinymce'); ?>
                        </div>

                        <!-- Check-in/Check-out Times -->
                        <div class="row">
                            <div class="col-md-3">
                                <!-- Check-in Time -->
                                <div class="form-group">
                                    <label for="check_in_time"
                                           class="control-label"><?php echo _l('check_in_time'); ?></label>
                                    <div class="input-group">
                                        <input type="text" id="check_in_time" name="check_in_time"
                                               class="form-control timepicker"
                                               value="<?php echo(isset($property) ? $property->check_in_time : '14:00'); ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <!-- Check-out Time -->
                                <div class="form-group">
                                    <label for="check_out_time"
                                           class="control-label"><?php echo _l('check_out_time'); ?></label>
                                    <div class="input-group">
                                        <input type="text" id="check_out_time" name="check_out_time"
                                               class="form-control timepicker"
                                               value="<?php echo(isset($property) ? $property->check_out_time : '11:00'); ?>">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Amenities -->
                        <div class="form-group">
                            <label for="amenities" class="control-label"><?php echo _l('property_amenities'); ?></label>
							<?php
							$amenities = [
								'wifi' => _l('wifi'),
								'parking' => _l('parking'),
								'pool' => _l('swimming_pool'),
								'air_conditioning' => _l('air_conditioning'),
								'heating' => _l('heating'),
								'gym' => _l('gym'),
								'spa' => _l('spa'),
								'restaurant' => _l('restaurant'),
								'bar' => _l('bar'),
								'business_center' => _l('business_center'),
								'laundry' => _l('laundry'),
								'room_service' => _l('room_service'),
								'shuttle' => _l('shuttle_service'),
								'elevator' => _l('elevator'),
								'security' => _l('security')
							];
                            sort($amenities);

							$property_amenities = [];
							if (isset($property) && ! empty($property->amenities))
							{
								$property_amenities = is_array($property->amenities) ? $property->amenities : unserialize($property->amenities);
							}
							?>
                            <div class="row">
								<?php foreach ($amenities as $key => $value) { ?>
                                    <div class="col-md-3">
                                        <div class="checkbox">
                                            <input type="checkbox" id="amenity_<?php echo $key; ?>" name="amenities[]"
                                                   value="<?php echo $key; ?>" <?php if (in_array($key, $property_amenities)) {
												echo 'checked';
											} ?>>
                                            <label for="amenity_<?php echo $key; ?>"><?php echo $value; ?></label>
                                        </div>
                                    </div>
								<?php } ?>
                            </div>
                        </div>

                        <!-- Property Rules -->
                        <div class="form-group">
                            <label for="rules" class="control-label"><?php echo _l('property_rules'); ?></label>
							<?php echo render_textarea('rules', '', (isset($property) ? $property->rules : ''), [], [], '', 'tinymce'); ?>
                        </div>

                        <!-- Property Status -->
                        <div class="form-group">
                            <label for="status" class="control-label"><?php echo _l('property_status'); ?></label>
                            <select name="status" id="status" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value="active" <?php if (isset($property) && $property->status == 'active') {
									echo 'selected';
								} ?>><?php echo _l('active'); ?></option>
                                <option value="inactive" <?php if (isset($property) && $property->status == 'inactive') {
									echo 'selected';
								} ?>><?php echo _l('inactive'); ?></option>
                                <option value="maintenance" <?php if (isset($property) && $property->status == 'maintenance') {
									echo 'selected';
								} ?>><?php echo _l('maintenance'); ?></option>
                            </select>
                        </div>

                        <!-- Featured Property -->
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" id="featured"
                                       name="featured" <?php if (isset($property) && $property->featured == 1) {
									echo 'checked';
								} ?>>
                                <label for="featured"><?php echo _l('featured_property'); ?></label>
                            </div>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>
						<?php echo form_close(); ?>
                    </div>
                </div>
            </div>
			<?php if (isset($property)) { ?>
            <div class="col-md-4">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('property_images'); ?></h4>
                            <hr class="hr-panel-heading"/>

                            <!-- Upload Images Section -->
                            <div class="form-group">
                                <label for="property_image"><?php echo _l('upload_image'); ?></label>
								<?php echo form_open_multipart(admin_url('hotel_management_system/properties/upload_image/' . $property->id), ['id' => 'property-images-upload', 'class' => 'dropzone']); ?>
								<?php echo form_close(); ?>
                            </div>

                            <hr/>

                            <!-- Display Existing Images -->
                            <div class="property-images tw-flex tw-flex-col tw-gap-4 tw-px-2" id="property-images-wrapper">
								<?php
								if (isset($property->images) && ! empty($property->images))
								{
									$property_images = $property->images;
									foreach ($property_images as $image)
									{
										?>
                                        <div class="property-image-container"
                                             data-image-id="<?php echo $image['id']; ?>">
                                            <div class="property-image-actions tw-flex tw-justify-between tw-mb-2">
												<?php if ($image['is_featured']) { ?>
                                                    <span class="label label-success featured-badge"><i
                                                                class="fa fa-star"></i> <?php echo _l('featured'); ?></span>
												<?php } else { ?>
                                                    <button type="button" class="btn btn-xs btn-info set-featured"
                                                            data-id="<?php echo $image['id']; ?>">
                                                        <i class="fa fa-star"></i> <?php echo _l('set_as_featured'); ?>
                                                    </button>
												<?php } ?>
                                                <button type="button" class="btn btn-xs btn-danger delete-image"
                                                        data-id="<?php echo $image['id']; ?>">
                                                    <i class="fa fa-remove"></i>
                                                </button>
                                            </div>
                                            <a href="<?php echo base_url($image['path']); ?>" target="_blank" data-lightbox="property-images" class="">
                                                <img src="<?php echo base_url($image['path']); ?>"
                                                     alt="<?php echo $property->name; ?>" class="img img-responsive">
                                            </a>


                                        </div>
                                        <hr/>
										<?php
									}
								} else
								{
									echo '<p class="text-muted">' . _l('no_images_found') . '</p>';
								}
								?>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms Section for existing property -->
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="clearfix">
                                <h4 class="pull-left"><?php echo _l('property_rooms'); ?></h4>
                                <a href="<?php echo admin_url('hotel_management_system/rooms/room'); ?>"
                                   class="btn btn-primary pull-right">
                                    <i class="fa fa-plus"></i> <?php echo _l('add_room'); ?>
                                </a>
                            </div>
                            <hr class="hr-panel-heading"/>

                            <div class="clearfix"></div>
							<?php if (isset($property->rooms) && ! empty($property->rooms)) { ?>
                                <div class="rooms-list">
									<?php foreach ($property->rooms as $room) { ?>
                                        <div class="room-item">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h5>
                                                        <a href="<?php echo admin_url('hotel_management_system/rooms/room/' . $room['id']); ?>"><?php echo $room['name']; ?></a>
                                                    </h5>
                                                    <small class="text-muted">
														<?php echo _l('room_type') . ': ' . $room['room_type']; ?> |
														<?php echo _l('capacity') . ': ' . $room['capacity']; ?> |
														<?php echo _l('price_per_night') . ': ' . app_format_money($room['price_per_night'], get_base_currency()); ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                            <span class="label label-<?php echo $room['status'] === 'available' ? 'success' : 'warning'; ?>">
                                                <?php echo _l($room['status']); ?>
                                            </span>
                                                </div>
                                            </div>
                                        </div>
									<?php } ?>
                                </div>
							<?php } else { ?>
                                <p class="text-muted"><?php echo _l('no_rooms_found'); ?></p>
							<?php } ?>
                        </div>
                    </div>
            </div>
			<?php } ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        appValidateForm($('#property-form'), {
            name: 'required',
            landlord_id: 'required',
            address: 'required'
        });

        // Initialize timepicker
        $('.timepicker').datetimepicker({
            datepicker: false,
            format: 'H:i'
        });

		<?php if (isset($property)) { ?>
        // Initialize dropzone for image uploads
        Dropzone.options.propertyImagesUpload = {
            paramName: "property_image",
            maxFilesize: 5, // MB
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictRemoveFile: "<?php echo _l('remove_file'); ?>",
            dictFileTooBig: "<?php echo _l('file_too_big'); ?>",
            success: function (file, response) {
                response = JSON.parse(response);
                if (response.success) {
                    // Add the image to the list
                    var imageHtml = `
                    <div class="property-image-container" data-image-id="${response.image.id}">
                        <img src="${response.image.path}" alt="<?php echo $property->name; ?>" class="img-responsive">
                        <div class="property-image-actions">
                            ${response.image.is_featured ?
                        '<span class="label label-success featured-badge"><i class="fa fa-star"></i> <?php echo _l('featured'); ?></span>' :
                        '<button type="button" class="btn btn-xs btn-info set-featured" data-id="' + response.image.id + '">' +
                        '<i class="fa fa-star"></i> <?php echo _l('set_as_featured'); ?>' +
                        '</button>'}
                            <button type="button" class="btn btn-xs btn-danger delete-image" data-id="${response.image.id}">
                                <i class="fa fa-remove"></i>
                            </button>
                        </div>
                    </div>
                    `;

                    $('#property-images-wrapper').append(imageHtml);

                    // Remove the "no images found" message if it exists
                    $('#property-images-wrapper p.text-muted').remove();

                    // Remove the file from dropzone
                    this.removeFile(file);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function (file, response) {
                alert_float('danger', response);
                this.removeFile(file);
            }
        };

        // Delete image
        $(document).on('click', '.delete-image', function () {
            var imageId = $(this).data('id');
            var container = $(this).closest('.property-image-container');

            if (confirm("<?php echo _l('confirm_delete_image'); ?>")) {
                $.post('<?php echo admin_url('hotel_management_system/properties/delete_image/'); ?>' + imageId, function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        container.fadeOut(500, function () {
                            $(this).remove();
                            alert_float('success', response.message);

                            // If no images left, add the "no images found" message
                            if ($('#property-images-wrapper .property-image-container').length === 0) {
                                $('#property-images-wrapper').html('<p class="text-muted"><?php echo _l('no_images_found'); ?></p>');
                            }
                        });
                    } else {
                        alert_float('danger', response.message);
                    }
                });
            }
        });

        // Set featured image
        $(document).on('click', '.set-featured', function () {
            var imageId = $(this).data('id');
            var button = $(this);

            $.post('<?php echo admin_url('hotel_management_system/properties/set_featured_image/'); ?>' + imageId, function (response) {
                response = JSON.parse(response);
                if (response.success) {
                    // Remove any existing featured badges
                    $('.featured-badge').remove();

                    // Show all set as featured buttons
                    $('.set-featured').show();

                    // Replace the button with a featured badge
                    button.replaceWith('<span class="label label-success featured-badge"><i class="fa fa-star"></i> <?php echo _l('featured'); ?></span>');

                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
            });
        });
		<?php } ?>
    });
</script>
</body>
</html>