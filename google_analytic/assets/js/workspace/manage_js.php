<script type="text/javascript">
    var fnServerParams = {};
    (function($) {
    	"use strict";

        $.each($('._hidden_inputs._filters input'),function(){
            fnServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
        });

        appValidateForm($('#workspace-form'), {
          name: 'required',
          super_admin: 'required',
        }, workspace_form_handler);

        init_workspace_table();
    })(jQuery);

    // custom view will fill input with the value
    function dt_workspace_custom_view(value, table, custom_input_name, clear_other_filters) {
    "use strict";
        var name = typeof (custom_input_name) == 'undefined' ? 'custom_view' : custom_input_name;
        if (typeof (clear_other_filters) != 'undefined') {
            var filters = $('._filter_data li.active').not('.clear-all-prevent');
            filters.removeClass('active');
            $.each(filters, function () {
                var input_name = $(this).find('a').attr('data-cview');
                $('._filters input[name="' + input_name + '"]').val('');
            });
        }
        var _cinput = do_filter_active(name);
        if (_cinput != name) {
            value = "";
        }
        $('input[name="' + name + '"]').val(value);

        $(table).DataTable().ajax.reload();
    }

    function init_workspace_table() {
      "use strict";

      if ($.fn.DataTable.isDataTable('.table-workspaces')) {
        $('.table-workspaces').DataTable().destroy();
      }
      initDataTable('.table-workspaces', admin_url + 'google_analytic/workspaces_table', false, false, fnServerParams);
    }

    function add_workspace() {
      "use strict";

      $('#workspace-modal').modal('show');
    }


    function workspace_form_handler(form) {
        "use strict";
        $('#workspace-modal').find('button[type="submit"]').prop('disabled', true);

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
              init_workspace_table();
            }
            $('#workspace-modal').modal('hide');
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.mesage));
        });

        return false;
    }

    function set_default(workspace_id) {
        "use strict";

        requestGetJSON('google_analytic/set_default_workspace/' + workspace_id).done(function(response) {
            if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
              alert_float('success', response.message);
              init_workspace_table();
            }
        });
    }
</script>