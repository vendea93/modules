	var fnServerParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
    		appValidateForm($('#notification-form'), {
          name: 'required',
        },notification_form_handler);


    		$('.add-new-notification').on('click', function(){
        $('#notification-modal').find('button[type="submit"]').prop('disabled', false);
          $('#notification-modal').modal('show');

          $('#notification-modal input[name="id"]').val('');
          $('#notification-modal select[name="vehicle_id"]').val('').change();
          $('#notification-modal select[name="driver_id"]').val('').change();
          $('#notification-modal select[name="notification_type"]').val('').change();
          $('#notification-modal input[name="notification_time"]').val('');
          $('#notification-modal input[name="subject"]').val('');
          $('#notification-modal textarea[name="description"]').val('');
        });

        init_notification_table();
    });

})(jQuery);

function init_notification_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-notifications')) {
    $('.table-notifications').DataTable().destroy();
  }
  initDataTable('.table-notifications', admin_url + 'reputation/notifications_table', [0], [0], fnServerParams, [1, 'desc']);
}

function notification_form_handler(form) {
    "use strict";
    $('#notification-modal').find('button[type="submit"]').prop('disabled', true);

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: $(form).attr('method'),
        data: formData,
        mimeType: $(form).attr('enctype'),
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
          alert_float('success', response.message);
	 		    init_notification_table();
        }else {
          alert_float('danger', response.message);
        }
        $('#notification-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}


function edit_project(id) {
  "use strict";
    $('#project-modal').find('button[type="submit"]').prop('disabled', false);

  requestGetJSON(admin_url + 'reputation/get_data_project/'+id).done(function(response) {
      $('#project-modal').modal('show');

      $('#project-modal input[name="id"]').val(id);
      $('#project-modal select[name="project_type"]').val(response.project_type).change();
      $('#project-modal input[name="subject"]').val(response.subject);
  });
}
