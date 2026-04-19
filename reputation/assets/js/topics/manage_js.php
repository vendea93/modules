<script type="text/javascript">
	var fnServerParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
    		appValidateForm($('#topic-form'), {
          type: 'required',
          content: 'required',
        },topic_form_handler);

    		fnServerParams = {
          "type": '[name="_type"]',
          "from_date": '[name="from_date"]',
          "to_date": '[name="to_date"]',
        };

    		$('.add-new-topic').on('click', function(){
        $('#topic-modal').find('button[type="submit"]').prop('disabled', false);
          $('#topic-modal').modal('show');

          $('#topic-modal input[name="id"]').val('');
          $('#topic-modal input[name="content"]').val('');
          $('#topic-modal input[name="scales"]').val(0);
        });

        $('select[name="_type"]').on('change', function() {
          init_topic_table();
        });

        $('input[name="from_date"]').on('change', function() {
          init_topic_table();
        });

        $('input[name="to_date"]').on('change', function() {
          init_topic_table();
        });

        init_topic_table();

    	$("input[data-type='currency']").on({
          keyup: function() {
            formatCurrency($(this));
          },
          blur: function() {
            formatCurrency($(this), "blur");
          }
      });
    });

})(jQuery);

function init_topic_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-topic')) {
    $('.table-topic').DataTable().destroy();
  }
  initDataTable('.table-topic', admin_url + 'reputation/topic_table', [], [], fnServerParams, [4, 'desc']);
}

function topic_form_handler(form) {
    "use strict";
    $('#topic-modal').find('button[type="submit"]').prop('disabled', true);

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
	 		    init_topic_table();
        }else {
          alert_float('danger', response.message);
        }
        $('#topic-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}


function edit_topic(id) {
  "use strict";
    $('#topic-modal').find('button[type="submit"]').prop('disabled', false);

  requestGetJSON(admin_url + 'reputation/get_data_topic/'+id).done(function(response) {
      $('#topic-modal').modal('show');

      $('#topic-modal input[name="id"]').val(id);
      $('#topic-modal select[name="type"]').val(response.type).change();
      $('#topic-modal input[name="content"]').val(response.content);
      $('#topic-modal input[name="scales"]').val(response.scales);

  });
}


</script>

