<script>
(function($) {
"use strict";

	var workflowsServerParams = {
		"categories": "[name='categories[]']",
		"from_date": 'input[name="from_date"]',
        "to_date": 'input[name="to_date"]',
	    };

	var table_workflows = $('.table-workflows');    
	initDataTable('.table-workflows', window.location.href, [], [],
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

function create_flow(){
    "use strict";
    $('.add-title').removeClass('hide');
    $('.edit-title').addClass('hide');

    $('#workflow_modal input[name="name"]').val();
    $('#workflow_modal textarea[name="description"]').val();

    $('#workflow_modal input[name="start_email"]').prop( "checked", true );
    $('#workflow_modal input[name="private"]').prop( "checked", true );


    $('#workflow_modal').modal('show');
    
}

function edit_workflow(el){
    "use strict";
    $('.add-title').addClass('hide');
    $('.edit-title').removeClass('hide');

    $('#workflow_modal input[name="name"]').val($(el).data('name'));
    $('#workflow_modal textarea[name="description"]').val($(el).data('description'));
    $('#workflow_modal input[name="workflow_id"]').val($(el).data('id'));
    $('#workflow_modal select[name="category_id"]').val($(el).data('category_id')).change();

    if($(el).data('start_email') == 1){
        $('#workflow_modal input[name="start_email"]').prop( "checked", true );
    }else{
        $('#workflow_modal input[name="start_email"]').prop( "checked", false );
    }

    if($(el).data('private') == 1){
        $('#workflow_modal input[name="private"]').prop( "checked", true );    
    }else{
        $('#workflow_modal input[name="private"]').prop( "checked", false ); 
    }

     $('#workflow_modal').modal('show');
}

</script>