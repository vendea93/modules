
(function($) {
    "use strict";

    $(document).ready(function(){

        if( window.location.href.indexOf("admin/staff/member") !== -1 )
        {

            if( $('#phonenumber').length > 0 && $('#tab_staff_profile').length > 0 )
            {

                var inputMemberID = 0;

                if ( $('input[name="memberid"]').length > 0 )

                    inputMemberID = $('input[name="memberid"]').val();


                $.post(admin_url + "task_signing/member_detail", { 'memberid' : inputMemberID }).done(function (response) {

                    response = JSON.parse(response);

                    var phonenumbertag = $('#phonenumber').parent('div.form-group');

                    phonenumbertag.after( response.content );


                });




            }

        }

    });


})(jQuery);


function request_customer_signature( task_id )
{

    if ( confirm( lang_ts_send_signature_notification ) )
    {

        $.post(admin_url + "task_signing/request_customer_signature", { task_id : task_id }).done(function (response) {

            response = JSON.parse(response);

            if ( response.success )
            {
                alert_float('success' , response.message );

                init_task_modal( task_id );
            }
            else
            {
                alert_float('message' , response.message );
            }

        });
    }

}