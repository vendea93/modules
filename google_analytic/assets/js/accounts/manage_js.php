<script type="text/javascript">
    var fnServerParams = {
        'type': '[name=type]'
    };
    (function($) {
    	"use strict";

        appValidateForm($('#account-form'), {
          name: 'required',
          super_admin: 'required',
        }, account_form_handler);

        init_facebook_table();
    })(jQuery);

    function init_facebook_table() {
      "use strict";

      if ($.fn.DataTable.isDataTable('.table-accounts')) {
        $('.table-accounts').DataTable().destroy();
      }
      initDataTable('.table-accounts', admin_url + 'google_analytic/accounts_table', false, false, fnServerParams);
    }

    function add_account() {
      "use strict";
        $('.add-title').removeClass('hide');
        $('.edit-title').addClass('hide');

        $('input[name=id]').val('');
        $('input[name=name]').val('');
        $('textarea[name=description]').val('');

        $('#account-modal').find('button[type="submit"]').prop('disabled', false);
        $('#account-modal').modal('show');
    }

    function edit_account(invoker) {
      "use strict";
        var id = $(invoker).data('id');
        var name = $(invoker).data('name');
        var description = $(invoker).data('description');

        $('.edit-title').removeClass('hide');
        $('.add-title').addClass('hide');

        $('input[name=id]').val(id);
        $('input[name=name]').val(name);
        $('textarea[name=description]').val(description);

        $('#account-modal').find('button[type="submit"]').prop('disabled', false);
        $('#account-modal').modal('show');
    }


    function account_form_handler(form) {
        "use strict";
        $('#account-modal').find('button[type="submit"]').prop('disabled', true);

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
              init_facebook_table();
            }
            $('#account-modal').modal('hide');
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.mesage));
        });

        return false;
    }
</script>