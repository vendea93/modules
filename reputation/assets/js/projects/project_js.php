<script type="text/javascript">
  var fnServerParams, mentionsParams;
	(function($) {
		"use strict";
    $( document ).ready(function() {
        fnServerParams = {
          "project_id": '[name="project_id"]',
        };

        mentionsParams = {
          "project_id": '[name="project_id"]',
        };

    		appValidateForm($('#project-form'), {
          project_name: 'required',
        });

        appValidateForm($('#mention-form'), {
          title: 'required',
          platform: 'required',
          content: 'required',
          link: 'required',
          time: 'required',
        });

        var addMoreExcludedSitesInputKey = $('.list_excluded_sites input[name^="excluded_sites"]').length+1;
        $("body").on('click', '.new_excluded_sites', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_excluded_sites').find('#item_excluded_sites').eq(0).clone().appendTo('.list_excluded_sites');

            newattachment.find('label[for="excluded_sites[0]"]').attr('for', 'excluded_sites[' + addMoreExcludedSitesInputKey + ']');
            newattachment.find('input[name="excluded_sites[0]"]').attr('name', 'excluded_sites[' + addMoreExcludedSitesInputKey + ']');
            newattachment.find('input[id="excluded_sites[0]"]').attr('id', 'excluded_sites[' + addMoreExcludedSitesInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_excluded_sites').addClass('remove_excluded_sites').removeClass('btn-success').addClass('btn-danger');

            addMoreExcludedSitesInputKey++;
        });

        $("body").on('click', '.remove_excluded_sites', function() {
            $(this).parents('#item_excluded_sites').remove();
        });

        var addMoreExcludedSocialMediaAuthorsInputKey = $('.list_excluded_social_media_authors input[name^="excluded_social_media_authors"]').length+1;
        $("body").on('click', '.new_excluded_social_media_authors', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_excluded_social_media_authors').find('#item_excluded_social_media_authors').eq(0).clone().appendTo('.list_excluded_social_media_authors');

            newattachment.find('label[for="excluded_social_media_authors[0]"]').attr('for', 'excluded_social_media_authors[' + addMoreExcludedSocialMediaAuthorsInputKey + ']');
            newattachment.find('input[name="excluded_social_media_authors[0]"]').attr('name', 'excluded_social_media_authors[' + addMoreExcludedSocialMediaAuthorsInputKey + ']');
            newattachment.find('input[id="excluded_social_media_authors[0]"]').attr('id', 'excluded_social_media_authors[' + addMoreExcludedSocialMediaAuthorsInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_excluded_social_media_authors').addClass('remove_excluded_social_media_authors').removeClass('btn-success').addClass('btn-danger');

            addMoreExcludedSocialMediaAuthorsInputKey++;
        });

        $("body").on('click', '.remove_excluded_social_media_authors', function() {
            $(this).parents('#item_excluded_social_media_authors').remove();
        });


        var addMoreVendorsInputKey = $('.list_approve input[name^="keywords"]').length+1;
        $("body").on('click', '.new_keywords', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_approve').find('#item_approve').eq(0).clone().appendTo('.list_approve');

            newattachment.find('label[for="keywords[0]"]').attr('for', 'keywords[' + addMoreVendorsInputKey + ']');
            newattachment.find('input[name="keywords[0]"]').attr('name', 'keywords[' + addMoreVendorsInputKey + ']');
            newattachment.find('input[id="keywords[0]"]').attr('id', 'keywords[' + addMoreVendorsInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_keywords').addClass('remove_keywords').removeClass('btn-success').addClass('btn-danger');

            $('select[name="approver[' + addMoreVendorsInputKey + ']"]').change(function(){
                if($(this).val() == 'specific_personnel'){
                  $('#is_staff_' + $(this).attr('data-id')).removeClass('hide');
                }else{
                  $('#is_staff_' + $(this).attr('data-id')).addClass('hide');
                }
            });

            addMoreVendorsInputKey++;
        });
        $("body").on('click', '.remove_keywords', function() {
            $(this).parents('#item_approve').remove();
        });


        var addMoreTripadvisorInputKey = $('.list_tripadvisor input[name^="tripadvisor"]').length+1;
        $("body").on('click', '.new_tripadvisor', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_tripadvisor').find('#item_tripadvisor').eq(0).clone().appendTo('.list_tripadvisor');

            newattachment.find('label[for="tripadvisor[0]"]').attr('for', 'tripadvisor[' + addMoreTripadvisorInputKey + ']');
            newattachment.find('input[name="tripadvisor[0]"]').attr('name', 'tripadvisor[' + addMoreTripadvisorInputKey + ']');
            newattachment.find('input[id="tripadvisor[0]"]').attr('id', 'tripadvisor[' + addMoreTripadvisorInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_tripadvisor').addClass('remove_tripadvisor').removeClass('btn-success').addClass('btn-danger');

            addMoreTripadvisorInputKey++;
        });

        $("body").on('click', '.remove_tripadvisor', function() {
            $(this).parents('#item_tripadvisor').remove();
        });

        var addMoreBookingInputKey = $('.list_booking input[name^="booking"]').length+1;
        $("body").on('click', '.new_booking', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_booking').find('#item_booking').eq(0).clone().appendTo('.list_booking');

            newattachment.find('label[for="booking[0]"]').attr('for', 'booking[' + addMoreBookingInputKey + ']');
            newattachment.find('input[name="booking[0]"]').attr('name', 'booking[' + addMoreBookingInputKey + ']');
            newattachment.find('input[id="booking[0]"]').attr('id', 'booking[' + addMoreBookingInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_booking').addClass('remove_booking').removeClass('btn-success').addClass('btn-danger');

            addMoreBookingInputKey++;
        });

        $("body").on('click', '.remove_booking', function() {
            $(this).parents('#item_booking').remove();
        });

        var addMoreAppStoreInputKey = $('.list_booking input[name^="app_store"]').length+1;
        $("body").on('click', '.new_app_store', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_app_store').find('#item_app_store').eq(0).clone().appendTo('.list_app_store');

            newattachment.find('label[for="app_store[0]"]').attr('for', 'app_store[' + addMoreAppStoreInputKey + ']');
            newattachment.find('input[name="app_store[0]"]').attr('name', 'app_store[' + addMoreAppStoreInputKey + ']');
            newattachment.find('input[id="app_store[0]"]').attr('id', 'app_store[' + addMoreAppStoreInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_app_store').addClass('remove_app_store').removeClass('btn-success').addClass('btn-danger');

            addMoreAppStoreInputKey++;
        });

        $("body").on('click', '.remove_app_store', function() {
            $(this).parents('#item_app_store').remove();
        });




        var addMoreGooglePlayInputKey = $('.list_google_play input[name^="google_play"]').length+1;
        $("body").on('click', '.new_google_play', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_google_play').find('#item_google_play').eq(0).clone().appendTo('.list_google_play');

            newattachment.find('label[for="google_play[0]"]').attr('for', 'google_play[' + addMoreGooglePlayInputKey + ']');
            newattachment.find('input[name="google_play[0]"]').attr('name', 'google_play[' + addMoreGooglePlayInputKey + ']');
            newattachment.find('input[id="google_play[0]"]').attr('id', 'google_play[' + addMoreGooglePlayInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_google_play').addClass('remove_google_play').removeClass('btn-success').addClass('btn-danger');

            addMoreGooglePlayInputKey++;
        });

        $("body").on('click', '.remove_google_play', function() {
            $(this).parents('#item_google_play').remove();
        });



        var addMoreTrustpilotInputKey = $('.list_trustpilot input[name^="trustpilot"]').length+1;
        $("body").on('click', '.new_trustpilot', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_trustpilot').find('#item_trustpilot').eq(0).clone().appendTo('.list_trustpilot');

            newattachment.find('label[for="trustpilot[0]"]').attr('for', 'trustpilot[' + addMoreTrustpilotInputKey + ']');
            newattachment.find('input[name="trustpilot[0]"]').attr('name', 'trustpilot[' + addMoreTrustpilotInputKey + ']');
            newattachment.find('input[id="trustpilot[0]"]').attr('id', 'trustpilot[' + addMoreTrustpilotInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_trustpilot').addClass('remove_trustpilot').removeClass('btn-success').addClass('btn-danger');

            addMoreTrustpilotInputKey++;
        });

        $("body").on('click', '.remove_trustpilot', function() {
            $(this).parents('#item_trustpilot').remove();
        });

        var addMoreSpotifyInputKey = $('.list_spotify input[name^="spotify"]').length+1;
        $("body").on('click', '.new_spotify', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_spotify').find('#item_spotify').eq(0).clone().appendTo('.list_spotify');

            newattachment.find('label[for="spotify[0]"]').attr('for', 'spotify[' + addMoreSpotifyInputKey + ']');
            newattachment.find('input[name="spotify[0]"]').attr('name', 'spotify[' + addMoreSpotifyInputKey + ']');
            newattachment.find('input[id="spotify[0]"]').attr('id', 'spotify[' + addMoreSpotifyInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_spotify').addClass('remove_spotify').removeClass('btn-success').addClass('btn-danger');

            addMoreSpotifyInputKey++;
        });

        $("body").on('click', '.remove_spotify', function() {
            $(this).parents('#item_spotify').remove();
        });

        var addMoreAppleItunesInputKey = $('.list_apple_itunes input[name^="apple_itunes"]').length+1;
        $("body").on('click', '.new_apple_itunes', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_apple_itunes').find('#item_apple_itunes').eq(0).clone().appendTo('.list_apple_itunes');

            newattachment.find('label[for="apple_itunes[0]"]').attr('for', 'apple_itunes[' + addMoreAppleItunesInputKey + ']');
            newattachment.find('input[name="apple_itunes[0]"]').attr('name', 'apple_itunes[' + addMoreAppleItunesInputKey + ']');
            newattachment.find('input[id="apple_itunes[0]"]').attr('id', 'apple_itunes[' + addMoreAppleItunesInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_apple_itunes').addClass('remove_apple_itunes').removeClass('btn-success').addClass('btn-danger');

            addMoreAppleItunesInputKey++;
        });

        $("body").on('click', '.remove_apple_itunes', function() {
            $(this).parents('#item_apple_itunes').remove();
        });

        var addMoreYoutubeInputKey = $('.list_youtube input[name^="youtube"]').length+1;
        $("body").on('click', '.new_youtube', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_youtube').find('#item_youtube').eq(0).clone().appendTo('.list_youtube');

            newattachment.find('label[for="youtube[0]"]').attr('for', 'youtube[' + addMoreYoutubeInputKey + ']');
            newattachment.find('input[name="youtube[0]"]').attr('name', 'youtube[' + addMoreYoutubeInputKey + ']');
            newattachment.find('input[id="youtube[0]"]').attr('id', 'youtube[' + addMoreYoutubeInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_youtube').addClass('remove_youtube').removeClass('btn-success').addClass('btn-danger');

            addMoreYoutubeInputKey++;
        });

        $("body").on('click', '.remove_youtube', function() {
            $(this).parents('#item_youtube').remove();
        });

        var addMoreVimeoInputKey = $('.list_vimeo input[name^="vimeo"]').length+1;
        $("body").on('click', '.new_vimeo', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_vimeo').find('#item_vimeo').eq(0).clone().appendTo('.list_vimeo');

            newattachment.find('label[for="vimeo[0]"]').attr('for', 'vimeo[' + addMoreVimeoInputKey + ']');
            newattachment.find('input[name="vimeo[0]"]').attr('name', 'vimeo[' + addMoreVimeoInputKey + ']');
            newattachment.find('input[id="vimeo[0]"]').attr('id', 'vimeo[' + addMoreVimeoInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_vimeo').addClass('remove_vimeo').removeClass('btn-success').addClass('btn-danger');

            addMoreVimeoInputKey++;
        });

        $("body").on('click', '.remove_vimeo', function() {
            $(this).parents('#item_vimeo').remove();
        });

        var addMoreTiktokInputKey = $('.list_tiktok input[name^="tiktok"]').length+1;
        $("body").on('click', '.new_tiktok', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_tiktok').find('#item_tiktok').eq(0).clone().appendTo('.list_tiktok');

            newattachment.find('label[for="tiktok[0]"]').attr('for', 'tiktok[' + addMoreTiktokInputKey + ']');
            newattachment.find('input[name="tiktok[0]"]').attr('name', 'tiktok[' + addMoreTiktokInputKey + ']');
            newattachment.find('input[id="tiktok[0]"]').attr('id', 'tiktok[' + addMoreTiktokInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_tiktok').addClass('remove_tiktok').removeClass('btn-success').addClass('btn-danger');

            addMoreTiktokInputKey++;
        });

        $("body").on('click', '.remove_tiktok', function() {
            $(this).parents('#item_tiktok').remove();
        });

        var addMoreNewsSourceInputKey = $('.list_news_source input[name^="news_source"]').length+1;
        $("body").on('click', '.new_news_source', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_news_source').find('#item_news_source').eq(0).clone().appendTo('.list_news_source');

            newattachment.find('label[for="news_source[0]"]').attr('for', 'news_source[' + addMoreNewsSourceInputKey + ']');
            newattachment.find('input[name="news_source[0]"]').attr('name', 'news_source[' + addMoreNewsSourceInputKey + ']');
            newattachment.find('input[id="news_source[0]"]').attr('id', 'news_source[' + addMoreNewsSourceInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_news_source').addClass('remove_news_source').removeClass('btn-success').addClass('btn-danger');

            addMoreNewsSourceInputKey++;
        });

        $("body").on('click', '.remove_news_source', function() {
            $(this).parents('#item_news_source').remove();
        });

        var addMoreBlogSourceInputKey = $('.list_blog_source input[name^="blog_source"]').length+1;
        $("body").on('click', '.new_blog_source', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_blog_source').find('#item_blog_source').eq(0).clone().appendTo('.list_blog_source');

            newattachment.find('label[for="blog_source[0]"]').attr('for', 'blog_source[' + addMoreBlogSourceInputKey + ']');
            newattachment.find('input[name="blog_source[0]"]').attr('name', 'blog_source[' + addMoreBlogSourceInputKey + ']');
            newattachment.find('input[id="blog_source[0]"]').attr('id', 'blog_source[' + addMoreBlogSourceInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_blog_source').addClass('remove_blog_source').removeClass('btn-success').addClass('btn-danger');

            addMoreBlogSourceInputKey++;
        });

        $("body").on('click', '.remove_blog_source', function() {
            $(this).parents('#item_blog_source').remove();
        });

        var addMoreWebSourceInputKey = $('.list_web_source input[name^="web_source"]').length+1;
        $("body").on('click', '.new_web_source', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_web_source').find('#item_web_source').eq(0).clone().appendTo('.list_web_source');

            newattachment.find('label[for="web_source[0]"]').attr('for', 'web_source[' + addMoreWebSourceInputKey + ']');
            newattachment.find('input[name="web_source[0]"]').attr('name', 'web_source[' + addMoreWebSourceInputKey + ']');
            newattachment.find('input[id="web_source[0]"]').attr('id', 'web_source[' + addMoreWebSourceInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_web_source').addClass('remove_web_source').removeClass('btn-success').addClass('btn-danger');

            addMoreWebSourceInputKey++;
        });

        $("body").on('click', '.remove_web_source', function() {
            $(this).parents('#item_web_source').remove();
        });

        var addMoreTelegramInputKey = $('.list_telegram input[name^="telegram"]').length+1;
        $("body").on('click', '.new_telegram', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_telegram').find('#item_telegram').eq(0).clone().appendTo('.list_telegram');

            newattachment.find('label[for="telegram[0]"]').attr('for', 'telegram[' + addMoreTelegramInputKey + ']');
            newattachment.find('input[name="telegram[0]"]').attr('name', 'telegram[' + addMoreTelegramInputKey + ']');
            newattachment.find('input[id="telegram[0]"]').attr('id', 'telegram[' + addMoreTelegramInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_telegram').addClass('remove_telegram').removeClass('btn-success').addClass('btn-danger');

            addMoreTelegramInputKey++;
        });

        $("body").on('click', '.remove_telegram', function() {
            $(this).parents('#item_telegram').remove();
        });

        var addMoreXTwitterInputKey = $('.list_x_twitter input[name^="x_twitter"]').length+1;
        $("body").on('click', '.new_x_twitter', function() {

             if ($(this).hasClass('disabled')) { return false; }    
            var newattachment = $('.list_x_twitter').find('#item_x_twitter').eq(0).clone().appendTo('.list_x_twitter');

            newattachment.find('label[for="x_twitter[0]"]').attr('for', 'x_twitter[' + addMoreXTwitterInputKey + ']');
            newattachment.find('input[name="x_twitter[0]"]').attr('name', 'x_twitter[' + addMoreXTwitterInputKey + ']');
            newattachment.find('input[id="x_twitter[0]"]').attr('id', 'x_twitter[' + addMoreXTwitterInputKey + ']').val('');

            newattachment.find('button[name="add"] i').removeClass('fa-plus').addClass('fa-minus');
            newattachment.find('button[name="add"]').removeClass('new_x_twitter').addClass('remove_x_twitter').removeClass('btn-success').addClass('btn-danger');

            addMoreXTwitterInputKey++;
        });

        $("body").on('click', '.remove_x_twitter', function() {
            $(this).parents('#item_x_twitter').remove();
        });

        $('.add-new-mention').on('click', function(){
            $('#mention-modal').find('button[type="submit"]').prop('disabled', false);
            $('#mention-modal').modal('show');

            $('#mention-modal input[name="id"]').val('');
            $('#mention-modal input[name="link"]').val('');
            $('#mention-modal input[name="title"]').val('');
            $('#mention-modal textarea[name="content"]').val('');
        });

        $('.add-new-notification').on('click', function(){

          requestGet(admin_url + 'reputation/get_data_notification').done(function(response) {
                $('#notification-modal .modal-body').html(response);
                init_tags_inputs();
                init_selectpicker();

                appValidateForm($('#notification-form'), {
                  email: 'required',
                  frequency: 'required',
                  mention_threshold: 'required',
                }, notification_form_handler);

                $('select[name="frequency"]').on('change', function() {
                $('.frequency_day').addClass('hide');
                $('.frequency_time').addClass('hide');
                $('.frequency_day_of_week').addClass('hide');
                  if ($(this).val() == 'once_a_day') {
                    $('.frequency_time').removeClass('hide');
                  }else if($(this).val() == 'every_week') {
                    $('.frequency_day_of_week').removeClass('hide');
                    $('.frequency_time').removeClass('hide');
                  }else if($(this).val() == 'every_month') {
                    $('.frequency_day').removeClass('hide');
                    $('.frequency_time').removeClass('hide');
                  }
                });

                $('#notification-modal').find('button[type="submit"]').prop('disabled', false);
                $('#notification-modal').modal('show');
          });
        });


        

    init_notification_table();
    init_mention_table();

    });

})(jQuery);


function init_notification_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-notification')) {
    $('.table-notification').DataTable().destroy();
  }
  initDataTable('.table-notification', admin_url + 'reputation/notification_table', [0], [0], fnServerParams, [1, 'desc']);
}


function notification_form_handler(form) {
    "use strict";
    $('#notification-modal').find('button[type="submit"]').prop('disabled', true);

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
          init_notification_table();
        }else {
          alert_float('danger', response.message);
        }
        $('#notification-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}


function edit_notification(id) {
  "use strict";
    $('#notification-modal').find('button[type="submit"]').prop('disabled', false);

  requestGet(admin_url + 'reputation/get_data_notification/'+id).done(function(response) {
        $('#notification-modal .modal-body').html(response);
        init_tags_inputs();
        init_selectpicker();

        appValidateForm($('#notification-form'), {
          email: 'required',
          frequency: 'required',
          mention_threshold: 'required',
        }, notification_form_handler);

        $('select[name="frequency"]').on('change', function() {
                $('.frequency_day').addClass('hide');
                $('.frequency_time').addClass('hide');
                $('.frequency_day_of_week').addClass('hide');
                  if ($(this).val() == 'once_a_day') {
                    $('.frequency_time').removeClass('hide');
                  }else if($(this).val() == 'every_week') {
                    $('.frequency_day_of_week').removeClass('hide');
                    $('.frequency_time').removeClass('hide');
                  }else if($(this).val() == 'every_month') {
                    $('.frequency_day').removeClass('hide');
                    $('.frequency_time').removeClass('hide');
                  }
                });
        $('#notification-modal').find('button[type="submit"]').prop('disabled', false);
        $('#notification-modal').modal('show');
  });
}


function init_mention_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-mentions')) {
    $('.table-mentions').DataTable().destroy();
  }
  initDataTable('.table-mentions', admin_url + 'reputation/mentions_table', [], [], mentionsParams, []);
}


function edit_mention(id) {
  "use strict";
    $('#mention-modal').find('button[type="submit"]').prop('disabled', false);

  requestGetJSON(admin_url + 'reputation/get_data_mention/'+id).done(function(response) {
      $('#mention-modal').modal('show');

      $('#mention-modal input[name="id"]').val(id);
      $('#mention-modal input[name="link"]').val(response.link);
      $('#mention-modal input[name="title"]').val(response.title);
      $('#mention-modal textarea[name="content"]').val(response.content);
      $('#mention-modal select[name="platform"]').val(response.platform).change();
      $('#mention-modal select[name="country"]').val(response.country).change();
      $('#mention-modal select[name="sentiment"]').val(response.sentiment).change();

      $('#mention-modal input[name="time"]').val(response.time);
      $('#mention-modal input[name="likes"]').val(response.likes);
      $('#mention-modal input[name="pageviews"]').val(response.pageviews);
      $('#mention-modal input[name="shares"]').val(response.shares);
      $('#mention-modal input[name="comments"]').val(response.comments);

  });
}

</script>

