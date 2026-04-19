<script type="text/javascript">
	var fnServerParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
    	appValidateForm($('#case-form'), {
        },case_form_handler);

      var addMoreVendorsInputKey = $('.list_approve select[name^="trigger"]').length+1;

      $("body").on('click', '.new_vendor_requests', function() {
        if ($(this).hasClass('disabled')) { return false; }    
        var newattachment = $('.list_approve').find('#item_approve').eq(0).clone().appendTo('.list_approve');
        newattachment.find('button[role="combobox"]').remove();
        newattachment.find('select').selectpicker('refresh');

        newattachment.find('button[data-id="trigger[0]"]').attr('data-id', 'trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="trigger[0]"]').attr('for', 'trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="trigger[0]"]').attr('data-index', addMoreVendorsInputKey);
        newattachment.find('select[name="trigger[0]"]').attr('name', 'trigger[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="trigger[0]"]').attr('id', 'trigger[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('#div_word_0').attr('id', 'div_word_' + addMoreVendorsInputKey).removeClass('hide');
        newattachment.find('label[for="word[0]"]').attr('for', 'word[' + addMoreVendorsInputKey + ']');
        newattachment.find('input[name="word[0]"]').attr('name', 'word[' + addMoreVendorsInputKey + ']');
        newattachment.find('input[id="word[0]"]').attr('id', 'word[' + addMoreVendorsInputKey + ']').val('');

        newattachment.find('#div_sentiment_0').attr('id', 'div_sentiment_' + addMoreVendorsInputKey).addClass('hide');
        newattachment.find('button[data-id="sentiment[0]"]').attr('data-id', 'sentiment[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="sentiment[0]"]').attr('for', 'sentiment[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="sentiment[0]"]').attr('name', 'sentiment[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="sentiment[0]"]').attr('id', 'sentiment[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('#div_sources_0').attr('id', 'div_sources_' + addMoreVendorsInputKey).addClass('hide');
        newattachment.find('button[data-id="sources[0]"]').attr('data-id', 'sources[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="sources[0]"]').attr('for', 'sources[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="sources[0]"]').attr('name', 'sources[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="sources[0]"]').attr('id', 'sources[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('#div_topic_0').attr('id', 'div_topic_' + addMoreVendorsInputKey).addClass('hide');
        newattachment.find('button[data-id="topic[0]"]').attr('data-id', 'topic[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="topic[0]"]').attr('for', 'topic[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="topic[0]"]').attr('name', 'topic[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="topic[0]"]').attr('id', 'topic[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('button[data-id="action[0]"]').attr('data-id', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="action[0]"]').attr('for', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="action[0]"]').attr('data-index', addMoreVendorsInputKey);
        newattachment.find('select[name="action[0]"]').attr('name', 'action[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="action[0]"]').attr('id', 'action[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('#div_staff_0').attr('id', 'div_staff_' + addMoreVendorsInputKey).addClass('hide');
        newattachment.find('button[data-id="staff[0]"]').attr('data-id', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('label[for="staff[0]"]').attr('for', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[name="staff[0]"]').attr('name', 'staff[' + addMoreVendorsInputKey + ']');
        newattachment.find('select[id="staff[0]"]').attr('id', 'staff[' + addMoreVendorsInputKey + ']').selectpicker('refresh');

        newattachment.find('#div_tag_0').attr('id', 'div_tag_' + addMoreVendorsInputKey).addClass('hide');
        newattachment.find('label[for="tag[0]"]').attr('for', 'tag[' + addMoreVendorsInputKey + ']');
        newattachment.find('input[name="tag[0]"]').attr('name', 'tag[' + addMoreVendorsInputKey + ']');
        newattachment.find('input[id="tag[0]"]').attr('id', 'tag[' + addMoreVendorsInputKey + ']').val('');


        newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
        newattachment.find('button[name="add"]').removeClass('new_vendor_requests').addClass('remove_vendor_requests').removeClass('btn-success').addClass('btn-danger');

        $(document).on("change", 'select[name="action[' + addMoreVendorsInputKey + ']"]', function() { 
            $('#div_staff_' + $(this).attr('data-index')).addClass('hide');
            $('#div_tag_' + $(this).attr('data-index')).addClass('hide');
            
            if($(this).val() == 'add_tag'){
              $('#div_tag_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'send_a_push_notification' || $(this).val() == 'send_an_email'){
              $('#div_staff_' + $(this).attr('data-index')).removeClass('hide');
            }
        });

        $(document).on("change", 'select[name^="trigger"]', function() { 
            $('#div_sources_' + $(this).attr('data-index')).addClass('hide');
            $('#div_sentiment_' + $(this).attr('data-index')).addClass('hide');
            $('#div_word_' + $(this).attr('data-index')).addClass('hide');
            $('#div_topic_' + $(this).attr('data-index')).addClass('hide');

            if($(this).val() == 'contains_this_word' || $(this).val() == 'does_not_contain_this_word'){
              $('#div_word_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'topic_is_detected'){
              $('#div_topic_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'sentiment_is_detected'){
              $('#div_sentiment_' + $(this).attr('data-index')).removeClass('hide');
            }else{
              $('#div_sources_' + $(this).attr('data-index')).removeClass('hide');
            }
        });

        addMoreVendorsInputKey++;
    });

        $("body").on('click', '.remove_vendor_requests', function() {
            $(this).parents('#item_approve').remove();
        });

        $(document).on("change", 'select[name^="trigger"]', function() { 
            $('#div_sources_' + $(this).attr('data-index')).addClass('hide');
            $('#div_sentiment_' + $(this).attr('data-index')).addClass('hide');
            $('#div_word_' + $(this).attr('data-index')).addClass('hide');
            $('#div_topic_' + $(this).attr('data-index')).addClass('hide');

            if($(this).val() == 'contains_this_word' || $(this).val() == 'does_not_contain_this_word'){
              $('#div_word_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'topic_is_detected'){
              $('#div_topic_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'sentiment_is_detected'){
              $('#div_sentiment_' + $(this).attr('data-index')).removeClass('hide');
            }else{
              $('#div_sources_' + $(this).attr('data-index')).removeClass('hide');
            }
        });
        

        $(document).on("change", 'select[name^="action"]', function() { 
            $('#div_staff_' + $(this).attr('data-index')).addClass('hide');
            $('#div_tag_' + $(this).attr('data-index')).addClass('hide');
            
            if($(this).val() == 'add_tag'){
              $('#div_tag_' + $(this).attr('data-index')).removeClass('hide');
            }else if($(this).val() == 'send_a_push_notification' || $(this).val() == 'send_an_email'){
              $('#div_staff_' + $(this).attr('data-index')).removeClass('hide');
            }
        });

    });

})(jQuery);


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
        }else {
          alert_float('danger', response.message);
        }
        $('#case-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}
</script>

