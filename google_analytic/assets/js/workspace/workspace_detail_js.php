<script type="text/javascript">
    var fnServerParams = {
      "workspace_id": '[name="workspace_id"]',
    };
    (function($) {
    	"use strict";

        appValidateForm($('#add-member-form'), {
          'members[]': 'required',
        }, add_member_form_handler);

        appValidateForm($('#add-contact-form'), {
          'members[]': 'required',
        }, add_contact_form_handler);

        init_member_table();
        init_contact_table();
    })(jQuery);

    function init_member_table() {
      "use strict";

      if ($.fn.DataTable.isDataTable('.table-members')) {
        $('.table-members').DataTable().destroy();
      }
      initDataTable('.table-members', admin_url + 'google_analytic/workspace_members_table', false, false, fnServerParams);
    }

    function init_contact_table() {
      "use strict";

      if ($.fn.DataTable.isDataTable('.table-contacts')) {
        $('.table-contacts').DataTable().destroy();
      }
      initDataTable('.table-contacts', admin_url + 'google_analytic/workspace_members_table/contact', false, false, fnServerParams);
    }

    function add_member_form_handler(form) {
        "use strict";
        $('#add-member-modal').find('button[type="submit"]').prop('disabled', true);

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
              init_member_table();
            }
            $('#add-member-modal').modal('hide');
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.mesage));
        });

        return false;
    }

    function add_contact_form_handler(form) {
        "use strict";
        $('#add-contact-modal').find('button[type="submit"]').prop('disabled', true);

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
              init_contact_table();
            }
            $('#add-contact-modal').modal('hide');
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.mesage));
        });

        return false;
    }
</script>