<script type="text/javascript">
	var fnServerParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
    		appValidateForm($('#case-form'), {
          type: 'required',
          name: 'required',
        },case_form_handler);

    		fnServerParams = {
          "type": '[name="_type"]',
          "from_date": '[name="from_date"]',
          "to_date": '[name="to_date"]',
        };

    		$('.add-new-case').on('click', function(){
        $('#case-modal').find('button[type="submit"]').prop('disabled', false);
          $('#case-modal').modal('show');

          $('#case-modal input[name="id"]').val('');
          $('#case-modal select[name="type"]').val('').change();
          $('#case-modal input[name="name"]').val('');
          $('#case-modal textarea[name="description"]').val('');
        });

        $('select[name="_type"]').on('change', function() {
          init_case_table();
        });

        $('input[name="from_date"]').on('change', function() {
          init_case_table();
        });

        $('input[name="to_date"]').on('change', function() {
          init_case_table();
        });

        init_case_table();

    	$("input[data-type='currency']").on({
          keyup: function() {
            formatCurrency($(this));
          },
          blur: function() {
            formatCurrency($(this), "blur");
          }
      });
      var addMoreVendorsInputKey = $('.list_approve select[name^="type"]').length+1;

      $("body").on('click', '.new_vendor_requests', function() {
        if ($(this).hasClass('disabled')) { return false; }    
        var newattachment = $('.list_approve').find('#item_approve').eq(0).clone().appendTo('.list_approve');
        newattachment.find('button[role="combobox"]').remove();
        newattachment.find('select').selectpicker('refresh');

        newattachment.find('button[data-id="inbox_trigger[0]"]').attr('data-id', 'inbox_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="inbox_trigger[0]"]').attr('for', 'inbox_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="inbox_trigger[0]"]').attr('name', 'inbox_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="inbox_trigger[0]"]').attr('id', 'inbox_trigger[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[data-id="published_posts_trigger[0]"]').attr('data-id', 'published_posts_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="published_posts_trigger[0]"]').attr('for', 'published_posts_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="published_posts_trigger[0]"]').attr('name', 'published_posts_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="published_posts_trigger[0]"]').attr('id', 'published_posts_trigger[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[data-id="review_trigger[0]"]').attr('data-id', 'review_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="review_trigger[0]"]').attr('for', 'review_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="review_trigger[0]"]').attr('name', 'review_trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="review_trigger[0]"]').attr('id', 'review_trigger[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[data-id="action[0]"]').attr('data-id', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="action[0]"]').attr('for', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="action[0]"]').attr('name', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="action[0]"]').attr('id', 'action[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[data-id="staff[0]"]').attr('data-id', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="staff[0]"]').attr('for', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="staff[0]"]').attr('name', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="staff[0]"]').attr('id', 'staff[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
        newattachment.find('button[name="add"]').removeClass('new_vendor_requests').addClass('remove_vendor_requests').removeClass('btn-success').addClass('btn-danger');

        $('select[name="approver[' + addMoreVendorsInputKey + ']"]').change(function(){
            if($(this).val() == 'specific_personnel'){
              $('#is_staff_' + $(this).attr('data-id')).removeClass('hide');
            }else{
              $('#is_staff_' + $(this).attr('data-id')).addClass('hide');
            }
        });

        addMoreVendorsInputKey++;
      });

        $("body").on('click', '.remove_vendor_requests', function() {
            $(this).parents('#item_approve').remove();
        });
     $(document).on("change", "input[type=radio][name=filter_type]", function() { 
            if($(this).val() === 'lead'){
              $('.div_lead_type').removeClass('hide');
              $('.div_customer_type').addClass('hide');
            }else{
              $('.div_lead_type').addClass('hide');
              $('.div_customer_type').removeClass('hide');
            }
        });

    });

})(jQuery);

function init_case_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-case')) {
    $('.table-case').DataTable().destroy();
  }
  initDataTable('.table-case', admin_url + 'reputation/case_table', [], [], fnServerParams, [0, 'desc']);
}

function case_form_handler(form) {
    "use strict";
    $('#case-modal').find('button[type="submit"]').prop('disabled', true);

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
	 		    init_case_table();
        }else {
          alert_float('danger', response.message);
        }
        $('#case-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}


function edit_case(id) {
  "use strict";
    $('#case-modal').find('button[type="submit"]').prop('disabled', false);

  requestGetJSON(admin_url + 'reputation/get_data_case/'+id).done(function(response) {
      $('#case-modal').modal('show');

      $('#case-modal input[name="id"]').val(id);
      $('#case-modal select[name="type"]').val(response.type).change();
      $('#case-modal input[name="name"]').val(response.name);
      $('#case-modal textarea[name="description"]').val(response.description);

  });
}


</script>

