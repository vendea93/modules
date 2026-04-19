(function($) {
	"use strict";  
	var table_vendor = $('.table-vendors');


	var tAPI = initDataTable(table_vendor, admin_url+'reputation/table_vendor',[0], [0]);


})(jQuery);

function vendors_bulk_action(event) {
      "use strict";
       
   var mass_delete = $('#mass_delete').prop('checked');
   var ids = [];
   var data = {};
   if(mass_delete == false || typeof(mass_delete) == 'undefined'){
       
   } else {
       data.mass_delete = true;
   }
   var rows = $('.table-vendors').find('tbody tr');
   $.each(rows, function() {
       var checkbox = $($(this).find('td').eq(0)).find('input');
       if (checkbox.prop('checked') == true) {
           ids.push(checkbox.val());
       }
   });
   data.ids = ids;
   $(event).addClass('disabled');
   setTimeout(function(){
     $.post(admin_url + 'reputation/vendor_bulk_action', data).done(function() {
      window.location.reload();
  });
 },50);       
}
