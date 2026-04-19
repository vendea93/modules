<script type="text/javascript">
	var fnServerParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
    		appValidateForm($('#project-form'), {
          name: 'required',
        },project_form_handler);

    		fnServerParams = {
          "type": '[name="_type"]',
          "from_date": '[name="from_date"]',
          "to_date": '[name="to_date"]',
        };

    		$('.add-new-project').on('click', function(){
        $('#project-modal').find('button[type="submit"]').prop('disabled', false);
          $('#project-modal').modal('show');

          $('#project-modal input[name="id"]').val('');
          $('#project-modal select[name="vehicle_id"]').val('').change();
          $('#project-modal select[name="driver_id"]').val('').change();
          $('#project-modal select[name="project_type"]').val('').change();
          $('#project-modal input[name="project_time"]').val('');
          $('#project-modal input[name="subject"]').val('');
          $('#project-modal textarea[name="description"]').val('');
        });

        $('select[name="_type"]').on('change', function() {
          init_project_table();
        });

        $('input[name="from_date"]').on('change', function() {
          init_project_table();
        });

        $('input[name="to_date"]').on('change', function() {
          init_project_table();
        });

        init_project_table();


        var addMoreVendorsInputKey = $('.list_approve select[name^="type"]').length+1;
        $("body").on('click', '.new_keywords', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_approve').find('#item_approve').eq(0).clone().appendTo('.list_approve');

            newattachment.find('label[for="keywords[0]"]').attr('for', 'keywords[' + addMoreVendorsInputKey + ']');
            newattachment.find('input[name="keywords[0]"]').attr('name', 'keywords[' + addMoreVendorsInputKey + ']');
            newattachment.find('input[id="keywords[0]"]').attr('id', 'keywords[' + addMoreVendorsInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_keywords').addClass('remove_keywords').removeClass('btn-success').addClass('btn-danger');

            $('select[name="approver[' + addMoreVendorsInputKey + ']"]').change(function(){
                if($(this).val() == 'specific_personnel'){
                  $('#is_staff_' + $(this).attr('data-id')).removeClass('hide');
                }else{
                  $('#is_staff_' + $(this).attr('data-id')).addClass('hide');
                }
            });

            addMoreVendorsInputKey++;
        });
        $("body").on('click', '.remove_keywords', function() {
            $(this).parents('#item_approve').remove();
        });
    });

})(jQuery);

function init_project_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-project')) {
    $('.table-project').DataTable().destroy();
  }
  initDataTable('.table-project', admin_url + 'reputation/project_table', [0], [0], fnServerParams, [1, 'desc']);
}

function project_form_handler(form) {
    "use strict";
    $('#project-modal').find('button[type="submit"]').prop('disabled', true);

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
	 		    init_project_table();
        }else {
          alert_float('danger', response.message);
        }
        $('#project-modal').modal('hide');
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
      $('#project-modal textarea[name="description"]').val(response.description);

  });
}

function set_default(workspace_id) {
    "use strict";

    requestGetJSON('reputation/set_default_project/' + workspace_id).done(function(response) {
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
          alert_float('success', response.message);
          init_project_table();
        }
    });
}
</script>

