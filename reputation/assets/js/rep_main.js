(function($) {
  "use strict";

  $('select[name=rep_base_workspace]').on('change', function() {
    requestGetJSON('reputation/set_default_project/' + $(this).val()).done(function(response) {
      if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
        alert_float('success', response.message);
      }
      
      window.location.reload();
    });

  });
})(jQuery);
  