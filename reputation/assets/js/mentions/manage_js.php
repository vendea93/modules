<script type="text/javascript">

(function($) {
    "use strict";
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

    appValidateForm($('#mention-tag-form'), {
    },mention_tag_form_handler);

    load_mention_list();
})(jQuery);

function load_mention_list(){
    "use strict";

    $('#filter-form input[name=page]').val(1);
    var data_filter = {};
    data_filter.search = $('#filter-form input[name=search]').val();
    data_filter.page = 1; 
    data_filter.visited = $('#filter-form select[name=visited]').val();
    data_filter.sentiment = $('#filter-form select[name=sentiment]').val();

    data_filter.sources = $('#filter-form select[name=sources]').val();
    data_filter.from_date = $('#filter-form input[name=from_date]').val();
    data_filter.to_date = $('#filter-form input[name=to_date]').val();
    data_filter.tags = $('#filter-form input[name=tags]').val();

    $('#top_stats').html('<div class="loader-action"></div>');

    $.post(admin_url + 'reputation/get_html_mention_list', data_filter).done(function(response){
        $('#top_stats').html(response);
    });

    mentions_reach_chart(data_filter);
    sentiment_chart(data_filter);
}

function load_more_mention_list(next_page){
    "use strict";

    var data_filter = {};
    data_filter.search = $('#filter-form input[name=search]').val();
    data_filter.page = next_page;
    data_filter.visited = $('#filter-form select[name=visited]').val();
    data_filter.sentiment = $('#filter-form select[name=sentiment]').val();
    data_filter.sources = $('#filter-form select[name=sources]').val();
    data_filter.from_date = $('#filter-form input[name=from_date]').val();
    data_filter.to_date = $('#filter-form input[name=to_date]').val();
    data_filter.tags = $('#filter-form input[name=tags]').val();
    
    $('#top_stats').html('<div class="loader-action"></div>');

    $.post(admin_url + 'reputation/get_html_mention_list', data_filter).done(function(response){
        $('#filter-form input[name=page]').val(next_page)
        $('#top_stats').html(response);
    });
}

function mentions_reach_chart(data_filter){
    "use strict";
    $('#mentions_reach_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'mentions_reach_chart';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

   Highcharts.chart('mentions_reach_chart', {
        colors: [ '#1877F2','#84c529','#69C9D0','#000000', '#FF0000'],

        title: {
            text: '<?php echo _l("mentions_reach_chart"); ?>'
        },
       
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
        credits: {
              enabled: false
            },
        yAxis: {
            title: {
                text: ''
            }
        },
        xAxis: {
            categories: response.mentions_reach_chart.categories
        },

        series: response.mentions_reach_chart.data,
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
            }
        },
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
    });
}


function sentiment_chart(data_filter){
    "use strict";
    $('#sentiment_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'sentiment_chart';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

   Highcharts.chart('sentiment_chart', {
        colors: [ '#84c529', '#FF0000'],

        title: {
            text: '<?php echo _l("sentiment_chart"); ?>'
        },
        
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
        credits: {
              enabled: false
            },
        yAxis: {
            title: {
                text: ''
            }
        },
        xAxis: {
            categories: response.sentiment_chart.categories
        },

        series: response.sentiment_chart.data,
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
            }
        },
        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
    });
}


function delete_mention(mention_id) {
    "use strict";
    if (confirm_delete()) {
        requestGetJSON('reputation/delete_mention_ajax/' + mention_id).done(function(response) {
            if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
                alert_float('success', response.message);
                $('#mention-card-'+mention_id).remove();
            }
        });
    }
}

function add_tags(mention_id) {
    "use strict";
    $('#tag-modal').find('button[type="submit"]').prop('disabled', false);

    requestGet('reputation/get_mention_tag_modal_ajax/' + mention_id).done(function(response) {
        $('#tag-modal .tag-modal-body').html(response);
        init_tags_inputs();
        $('#tag-modal').modal('show');
    });
}

function add_to_pdf_report(mention_id) {
    "use strict";

    requestGetJSON('reputation/add_to_pdf_report/' + mention_id).done(function(response) {
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
            alert_float('success', response.message);
            reload_metion(mention_id);
        }
    });
}


function mention_tag_form_handler(form) {
    "use strict";
    $('#tag-modal').find('button[type="submit"]').prop('disabled', true);

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
            reload_metion(response.mention_id);
        }
        $('#tag-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}

function mention_sentiment_mark_as(status, mention_id) {
    "use strict";
  var url = "reputation/mention_sentiment_mark_as/" + status + "/" + mention_id;
  requestGetJSON(url).done(function (response) {
    if (response.success === true || response.success == "true") {
        alert_float('success', response.message);
        reload_metion(mention_id);
    }
  });
}

function reload_metion(mention_id) {
    "use strict";
  var url = "reputation/get_html_mention/" + mention_id;
  requestGet(url).done(function (response) {
    $('#mention-card-'+mention_id).html(response);
  });
}

function visit_mention(mention_id, mention_url) {
    "use strict";
  var url = "reputation/visit_mention/" + mention_id;
  requestGet(url).done(function (response) {
    window.open(mention_url, '_blank').focus();
    reload_metion(mention_id);
  });

}

function remove_from_pdf_report(mention_id) {
    "use strict";

    requestGetJSON('reputation/remove_from_pdf_report/' + mention_id).done(function(response) {
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
            alert_float('success', response.message);
            reload_metion(mention_id);
        }
    });
}

</script>