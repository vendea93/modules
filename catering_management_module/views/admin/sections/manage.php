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
									<?php echo _l('menu_sections'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_menu_sections')) { ?>
                                    <a href="#" class="btn btn-primary pull-left" data-toggle="modal" data-target="#section_modal">
                                        <i class="fa fa-plus"></i>
										<?php echo _l('new_section'); ?>
                                    </a>
								<?php } ?>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <div class="clearfix"></div>
						<?php
						$table_data = [
							_l('section_name'),
							_l('description'),
							_l('display_order'),
							_l('status'),
							_l('options'),
						];

						render_datatable($table_data, 'sections');
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Modal -->
<div class="modal fade" id="section_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('add_new_section'); ?></h4>
            </div>
			<?php echo form_open(admin_url('catering_management_module/sections/section'), ['id' => 'section_form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<?php echo render_input('name', 'section_name', '', 'text', ['required' => TRUE]); ?>
                    </div>
                    <div class="col-md-12">
						<?php echo render_textarea('description', 'description'); ?>
                    </div>
                    <div class="col-md-6">
						<?php echo render_input('display_order', 'display_order', '0', 'number'); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="active" class="control-label">
								<?php echo _l('active'); ?>
                            </label>
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
        initDataTable('.table-sections', admin_url + 'catering_management_module/sections/table', [4], [4], {}, [0, 'asc']);


        // Toggle Active Status
        $('body').on('change', '.toggle-section-status', function () {
            var $checkbox = $(this);
            var section_id = $checkbox.data('id');
            var active = $checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/sections/toggle_active'); ?>',
                type: 'POST',
                data: {
                    section_id: section_id,
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

        // Handle form submission
        $('#section_form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.post(form.attr('action'), data, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#section_modal').modal('hide');
                    $('.table-sections').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        });

        // Edit section
        $('body').on('click', '.edit-section', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            $.get('<?php echo admin_url('catering_management_module/sections/section/'); ?>' + id, function (response) {
                $('#section_modal .modal-body').html(response);
                $('#section_modal .modal-title').text('<?php echo _l('edit_section'); ?>');
                $('#section_form').attr('action', '<?php echo admin_url('catering_management_module/sections/section/'); ?>' + id);
                $('#section_modal').modal('show');
            });
        });

        // Reset modal on close
        $('#section_modal').on('hidden.bs.modal', function () {
            $('#section_form')[0].reset();
            $('#section_form').attr('action', '<?php echo admin_url('catering_management_module/sections/section'); ?>');
            $('#section_modal .modal-title').text('<?php echo _l('add_new_section'); ?>');
        });
    });
</script>
</body>
</html>