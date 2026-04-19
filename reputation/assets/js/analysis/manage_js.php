<script type="text/javascript">

(function($) {
    "use strict";
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

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

    $('#top_stats').html('<div class="loader-action"></div>');
    data_filter.type = 'analysis_top_stats';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#top_stats').html(response);
    });

    $('#the_most_popular_mentions').html('<div class="loader-action"></div>');
    data_filter.type = 'the_most_popular_mentions';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#the_most_popular_mentions').html(response);
    });

    mentions_by_category(data_filter);
}

function mentions_by_category(data_filter){
    "use strict";
    $('#mentions_by_category_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'mentions_by_category';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);
        $('#mentions_by_category').html(response.content_html);
        init_progress_bars();

        Highcharts.chart('mentions_by_category_chart', {
            chart: {
                type: 'pie'
            },
            colors: ['#ef370dc7', '#119EFA', '#DDDF00', '#15f34f',],

            title: {
                text: '',
            },
            tooltip: {
                valueSuffix: '%'
            },
            credits: {
              enabled: false
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [
                {
                    name: '<?php echo _l('percentage'); ?>',
                    colorByPoint: true,
                    data: response.mentions_by_category_chart.data
                }
            ]
        });
    });
}

</script>