<script>
(function($) {
"use strict";

	var workflowsServerParams = {
		"workflow": "[name='workflow[]']",
		"from_date": 'input[name="from_date"]',
        "to_date": 'input[name="to_date"]',
	    };

	var table_workflows = $('.table-histories');    
	initDataTable('.table-histories', window.location.href, [], [],
	        workflowsServerParams, [1, 'desc']);

	 $.each(workflowsServerParams, function(i, obj) {
        $('select' + obj).on('change', function() {  
            table_workflows.DataTable().ajax.reload()
                .columns.adjust();
        });
    });

	 $('input[name="from_date"]').on('change', function() {
        table_workflows.DataTable().ajax.reload()
                .columns.adjust();
    });
    $('input[name="to_date"]').on('change', function() {
        table_workflows.DataTable().ajax.reload()
                .columns.adjust();
    });


})(jQuery);

</script>