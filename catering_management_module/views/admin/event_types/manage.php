<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 *
 */

?>
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
									<?php echo _l('event_types'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_event_types')): ?>
                                    <a onclick="new_event_type()" href="javascript:void(0)" class="btn btn-primary">
                                        <i class="fa-regular fa-plus tw-mr-1"></i>
										<?php echo _l('new_event_type'); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
						<?php
						$this->load->view(CATERING_MANAGEMENT_MODULE_NAME.'/admin/event_types/event_type');
						render_datatable([
							'#',
							_l('event_type_name'),
							_l('event_type_background_color'),
							_l('event_type_text_color'),
							_l('event_type_sort_order'),
							_l('options'),
						], 'event-types'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="event_type" tabindex="-1" role="dialog">
    <div class="modal-dialog">
		<?= form_open(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/event_types/event_type'), ['id' => 'event_type_form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span
                            class="edit-title"><?= _l('edit_event_type'); ?></span>
                    <span
                            class="add-title"><?= _l('new_event_type'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
						<?= render_input('edit_event_type', '', '', 'hidden', ['type' => 'hidden']); ?>
						<?= render_input('name', _l('event_type_name')); ?>
						<?= render_color_picker('background_color', _l('event_type_background_color'), '#000000'); ?>
						<?= render_color_picker('text_color', _l('event_type_text_color'), '#FFFFFF'); ?>
						<?= render_input('sort_order', _l('event_type_sort_order'), 0, 'number'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"
                        data-loading-text="<?= _l('wait_text'); ?>"
                        data-autocomplete="off"
                        data-form="#event_type_form"><?= _l('submit'); ?></button>
            </div>
        </div>
		<?= form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    appValidateForm(
        $("#event_type_form"),
        {
            name: "required",
            background_color: "required",
            text_color: "required",
            sort_order: "required"
        },
        manage_event_type
    );

    function manage_event_type(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float("success", response.message);
            } else if (response.message) {
                alert_float("danger", response.message);
            }

            $(".table-event-types").DataTable().ajax.reload(null, false)
            $("#event_type").modal("hide")
            $("#event_type_form").find('button[type="submit"]').button("reset")

            $('#event_type_form').trigger('reset')
            $('#event_type_form input[name="label_color"]').trigger('change')
            $('#event_type_form input[name="text_color"]').trigger('change')
        });
        return false;
    }

    function new_event_type() {
        let action = admin_url + "catering_management_module/event_types/add";
        $("#event_type_form").attr('action', action);
        $("#event_type .add-title").removeClass("hide");
        $("#event_type .edit-title").addClass("hide");

        $('#event_type_form').trigger('reset');
        $("#event_type").modal("show");
    }

    function delete_event_type(event_type_id) {
        var result = confirm("Are you sure you want to perform this action?");
        if (result) {
            //Logic to delete the item
            var url = admin_url + "catering_management_module/event_types/destroy";
            var data = {
                event_type_id
            }
            $.post(url, data).done(function (response) {
                response = JSON.parse(response);
                if (response.success === true) {
                    alert_float("success", response.message);
                } else if (response.message) {
                    alert_float("danger", response.message);
                }
                $(".table-event-types").DataTable().ajax.reload(null, false);

                return true;
            });
        }
        return false;
    }

    function edit_event_type(invoker, event_type_id) {
        let action = admin_url + "catering_management_module/event_types/update";
        $("#event_type_form").attr('action', action);

        $('#event_type_form input[name="edit_event_type"]').val(event_type_id);
        $('#event_type_form input[name="name"]').val($(invoker).data("name"));
        $('#event_type_form input[name="sort_order"]').val(parseInt($(invoker).data("sort-order")));
        $('#event_type_form input[name="background_color"]').val($(invoker).data("background-color")).trigger('change');
        $('#event_type_form input[name="text_color"]').val($(invoker).data("text-color")).trigger('change');

        $("#event_type").modal("show");
        $("#event_type .add-title").addClass("hide");
        $("#event_type .edit-title").removeClass("hide");
    }

    $(function () {
        initDataTable(
            ".table-event-types",
            admin_url + "catering_management_module/event_types/table",
            [2, 3, 5],
            [2, 3, 5],
            {},
            [3, "desc"],
        );
    });
</script>
</body>

</html>