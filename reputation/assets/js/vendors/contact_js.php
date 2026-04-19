<script>
    $(function(){
        "use strict";
        // Guess auto email notifications based on the default contact permissios
        var permInputs = $('input[name="permissions[]"]');
        $.each(permInputs,function(i,input){
            input = $(input);
            if(input.prop('checked') === true){
                $('#contact_email_notifications [data-perm-id="'+input.val()+'"]').prop('checked',true);
            }
        });
    });
</script>