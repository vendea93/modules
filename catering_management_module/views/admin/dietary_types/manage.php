<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
									<?php echo _l('dietary_types'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_dietary_types')) { ?>
                                    <a href="#" class="btn btn-primary pull-left" data-toggle="modal" data-target="#dietary_type_modal">
                                        <i class="fa fa-plus"></i>
										<?php echo _l('new_dietary_type'); ?>
                                    </a>
								<?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <div class="clearfix"></div>
						<?php
						$table_data = [
							_l('dietary_type_label'),
							_l('dietary_type_code'),
							_l('display_order'),
							_l('status'),
							_l('options'),
						];

						render_datatable($table_data, 'dietary-types');
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dietary Type Modal -->
<div class="modal fade" id="dietary_type_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('add_new_dietary_type'); ?></h4>
            </div>
			<?php echo form_open('#', ['id' => 'dietary_type_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="dietary_type_id" id="dietary_type_id">
                <div class="row">
                    <div class="col-md-6">
						<?php echo render_input('code', 'dietary_type_code', '', 'text', ['required' => TRUE]); ?>
                    </div>
                    <div class="col-md-6">
						<?php echo render_input('label', 'dietary_type_label', '', 'text', ['required' => TRUE]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
						<?php echo render_input('icon', 'icon_class', '', 'text', ['placeholder' => 'fa fa-leaf']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
						<?php echo render_textarea('description', 'description', '', ['rows' => 3]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
						<?php echo render_input('display_order', 'display_order', '0', 'number', ['min' => '0']); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="active"><?php echo _l('active'); ?></label>
                            <select name="active" id="active" class="selectpicker" data-width="100%">
                                <option value="1" selected><?php echo _l('active'); ?></option>
                                <option value="0"><?php echo _l('inactive'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
			<?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        'use strict';

        // Initialize DataTable
        initDataTable('.table-dietary-types', '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/table'); ?>', [4], [4], {}, [2, 'asc']);

        // Toggle Active Status
        $('body').on('change', '.toggle-dietary-status', function () {
            var $checkbox = $(this);
            var dietary_type_id = $checkbox.data('id');
            var active = $checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/toggle_active'); ?>',
                type: 'POST',
                data: {
                    dietary_type_id: dietary_type_id,
                    active: active
                },
                success: function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                        $checkbox.prop('checked', !$checkbox.is(':checked'));
                    }
                },
                error: function () {
                    alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                    $checkbox.prop('checked', !$checkbox.is(':checked'));
                }
            });
        });

        // Add/Edit Form Submission
        $('#dietary_type_form').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var dietary_type_id = $('#dietary_type_id').val();
            var url = dietary_type_id ? '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/update'); ?>' : '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/add'); ?>';

            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#dietary_type_modal').modal('hide');
                        $('.table-dietary-types').DataTable().ajax.reload();
                    } else {
                        alert_float('danger', response.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function () {
                    alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                    $btn.prop('disabled', false);
                }
            });
        });

        // Edit Dietary Type
        $('body').on('click', '.edit-dietary-type', function (e) {
            e.preventDefault();
            var dietary_type_id = $(this).data('id');

            $.get('<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/get/'); ?>' + dietary_type_id, function (response) {
                if (response.success && response.dietary_type) {
                    var dt = response.dietary_type;
                    $('#dietary_type_id').val(dt.id);
                    $('#code').val(dt.code);
                    $('#label').val(dt.label);
                    $('#icon').val(dt.icon);
                    $('#description').val(dt.description);
                    $('#display_order').val(dt.display_order);
                    $('#active').val(dt.active).selectpicker('refresh');

                    $('#dietary_type_modal .modal-title').text('<?php echo _l('edit_dietary_type'); ?>');
                    $('#dietary_type_modal').modal('show');
                }
            }, 'json');
        });

        // Delete Dietary Type
        $('body').on('click', '.delete-dietary-type', function (e) {
            e.preventDefault();
            var dietary_type_id = $(this).data('id');
            var dietary_type_name = $(this).data('name');

            if (confirm('<?php echo _l('confirm_delete'); ?> "' + dietary_type_name + '"?')) {
                $.ajax({
                    url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types/delete'); ?>',
                    type: 'POST',
                    data: {
                        dietary_type_id: dietary_type_id
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            $('.table-dietary-types').DataTable().ajax.reload();
                        } else {
                            alert_float('danger', response.message);
                        }
                    },
                    error: function () {
                        alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                    }
                });
            }
        });

        // Reset modal on close
        $('#dietary_type_modal').on('hidden.bs.modal', function () {
            $('#dietary_type_form')[0].reset();
            $('#dietary_type_id').val('');
            $('#dietary_type_modal .modal-title').text('<?php echo _l('add_new_dietary_type'); ?>');
            $('#dietary_type_form button[type="submit"]').prop('disabled', false);
            $('.selectpicker').selectpicker('refresh');
        });
    });
</script>
</body>
</html>