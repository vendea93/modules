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
									<?php echo _l('allergens'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_allergens')): ?>
                                    <a href="#" class="btn btn-primary pull-left" data-toggle="modal" data-target="#allergen_modal">
                                        <i class="fa fa-plus"></i>
										<?php echo _l('new_allergen'); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <div class="clearfix"></div>
						<?php
						$table_data = [
							_l('allergen_label'),
							_l('allergen_code'),
							_l('allergen_severity'),
							_l('display_order'),
							_l('status'),
							_l('options'),
						];

						render_datatable($table_data, 'allergens');
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Allergen Modal -->
<div class="modal fade" id="allergen_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('add_new_allergen'); ?></h4>
            </div>
			<?php echo form_open('#', ['id' => 'allergen_form']); ?>
            <div class="modal-body">
                <input type="hidden" name="allergen_id" id="allergen_id">
                <div class="row">
                    <div class="col-md-6">
						<?php echo render_input('code', 'allergen_code', '', 'text', ['required' => TRUE]); ?>
                    </div>
                    <div class="col-md-6">
						<?php echo render_input('label', 'allergen_label', '', 'text', ['required' => TRUE]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="severity"><?php echo _l('allergen_severity'); ?></label>
                            <select name="severity" id="severity" class="selectpicker" data-width="100%" required>
                                <option value="mild"><?php echo _l('allergen_severity_mild'); ?></option>
                                <option value="moderate" selected><?php echo _l('allergen_severity_moderate'); ?></option>
                                <option value="severe"><?php echo _l('allergen_severity_severe'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
						<?php echo render_input('icon', 'icon_class', '', 'text', ['placeholder' => 'fa fa-warning']); ?>
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
        initDataTable('.table-allergens', '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/table'); ?>', [5], [5], {}, [3, 'asc']);

        // Toggle Active Status
        $('body').on('change', '.toggle-allergen-status', function () {
            var $checkbox = $(this);
            var allergen_id = $checkbox.data('id');
            var active = $checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/toggle_active'); ?>',
                type: 'POST',
                data: {
                    allergen_id: allergen_id,
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
        $('#allergen_form').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var allergen_id = $('#allergen_id').val();
            var url = allergen_id ? '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/update'); ?>' : '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/add'); ?>';

            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: $form.serialize(),
                success: function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#allergen_modal').modal('hide');
                        $('.table-allergens').DataTable().ajax.reload();
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

        // Edit Allergen
        $('body').on('click', '.edit-allergen', function (e) {
            e.preventDefault();
            var allergen_id = $(this).data('id');

            $.get('<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/get/'); ?>' + allergen_id, function (response) {
                if (response.success && response.allergen) {
                    var allergen = response.allergen;
                    $('#allergen_id').val(allergen.id);
                    $('#code').val(allergen.code);
                    $('#label').val(allergen.label);
                    $('#severity').val(allergen.severity).selectpicker('refresh');
                    $('#icon').val(allergen.icon);
                    $('#description').val(allergen.description);
                    $('#display_order').val(allergen.display_order);
                    $('#active').val(allergen.active).selectpicker('refresh');

                    $('#allergen_modal .modal-title').text('<?php echo _l('edit_allergen'); ?>');
                    $('#allergen_modal').modal('show');
                }
            }, 'json');
        });

        // Delete Allergen
        $('body').on('click', '.delete-allergen', function (e) {
            e.preventDefault();
            var allergen_id = $(this).data('id');
            var allergen_name = $(this).data('name');

            if (confirm('<?php echo _l('confirm_delete'); ?> "' + allergen_name + '"?')) {
                $.ajax({
                    url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens/delete'); ?>',
                    type: 'POST',
                    data: {
                        allergen_id: allergen_id
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            $('.table-allergens').DataTable().ajax.reload();
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
        $('#allergen_modal').on('hidden.bs.modal', function () {
            $('#allergen_form')[0].reset();
            $('#allergen_id').val('');
            $('#allergen_modal .modal-title').text('<?php echo _l('add_new_allergen'); ?>');
            $('#allergen_form button[type="submit"]').prop('disabled', false);
            $('.selectpicker').selectpicker('refresh');
        });
    });
</script>
</body>
</html>