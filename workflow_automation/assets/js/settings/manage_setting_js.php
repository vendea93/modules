<script>


function new_task_template() {
	"use strict";
	$('.add-title').removeClass('hide');
	$('.edit-title').addClass('hide');

	$('#task_template_modal input[name="task_template_id"]').val('');
	$('#task_template_modal input[name="template_name"]').val('');
	$('#task_template_modal input[name="task_subject"]').val('');
	$('#task_template_modal input[name="start_date"]').val('');
	$('#task_template_modal input[name="due_date"]').val('');

	$('#task_template_modal select[name="priority"]').val('').change();
	$('#task_template_modal select[name="assignees[]"]').val('').change();
	$('#task_template_modal select[name="followers[]"]').val('').change();
	$('#task_template_modal select[name="rel_type"]').val('').change();
	
	$('#task_template_modal').modal('show');


}	


function edit_task_template( invoker, id){
	"use strict";

	var assignees = $(invoker).data('assignees');
	var followers =  $(invoker).data('followers');

	$('#task_template_modal input[name="task_template_id"]').val(id);

	$('#task_template_modal input[name="template_name"]').val($(invoker).data('template_name'));
	$('#task_template_modal input[name="task_subject"]').val($(invoker).data('task_subject'));
	$('#task_template_modal input[name="start_date"]').val($(invoker).data('start_date'));
	$('#task_template_modal input[name="due_date"]').val($(invoker).data('due_date'));


	if(typeof(assignees) == "string"){
	    $('#task_template_modal select[name="assignees[]"]').val( assignees.split(',')).change();
	}else{
	    $('#task_template_modal select[name="assignees[]"]').val(assignees).change();
	}


	if(typeof(followers) == "string"){
	    $('#task_template_modal select[name="followers[]"]').val( followers.split(',')).change();
	}else{
	    $('#task_template_modal select[name="followers[]"]').val(followers).change();
	}

	$('#task_template_modal select[name="priority"]').val($(invoker).data('priority')).change();
	$('#task_template_modal select[name="rel_type"]').val($(invoker).data('rel_type')).change();

	$('#task_template_modal').modal('show');

}


function new_category() {
	"use strict";
	$('.add-title').removeClass('hide');
	$('.edit-title').addClass('hide');

	$('#category_modal input[name="category_id"]').val('');
	$('#category_modal input[name="name"]').val('');
	$('#category_modal textarea[name="description"]').val('');
	$('#category_modal').modal('show');
}	


function edit_category( invoker, id){
	"use strict";

	$('#category_modal input[name="category_id"]').val(id);

	$('#category_modal input[name="name"]').val($(invoker).data('name'));
	$('#category_modal textarea[name="description"]').val($(invoker).data('description'));


	$('#category_modal').modal('show');

}

</script>