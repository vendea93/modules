<script type="text/javascript">
var date_filter;
var account_filter;
var social;

(function($) {
    "use strict";
    social = $('input[name=social]').val();
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

    $('select[name="visited"]').on('change', function() {
      load_mention_list();
    });

    $('select[name="sources"]').on('change', function() {
      load_mention_list();
    });

    $('select[name="sentiment"]').on('change', function() {
      load_mention_list();
    });

    $('input[name="from_date"]').on('change', function() {
      load_mention_list();
    });

    $('input[name="to_date"]').on('change', function() {
      load_mention_list();
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
    data_filter['type'] = 'analysis_top_stats';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#top_stats').html(response);
    });

    mentions_chart(data_filter);
    social_media_reach_chart(data_filter);

    $('#summary_stats').html('<div class="loader-action"></div>');
    data_filter['type'] = 'summary_stats';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#summary_stats').html(response);
    });

    $('#summary_sources').html('<div class="loader-action"></div>');
    data_filter['type'] = 'summary_sources';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#summary_sources').html(response);
    });

    $('#the_most_influential_sites').html('<div class="loader-action"></div>');
    data_filter['type'] = 'the_most_influential_sites';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#the_most_influential_sites').html(response);
    });

    mentions_by_category(data_filter);

    $('#tag_stats').html('<div class="loader-action"></div>');
    data_filter['type'] = 'summary_tag_stats';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#tag_stats').html(response);
    });

    $('#keyword_stats').html('<div class="loader-action"></div>');
    data_filter['type'] = 'summary_keyword_stats';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        $('#keyword_stats').html(response);
    });
}

function mentions_by_category(data_filter){
    "use strict";
    $('#mentions_by_category_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'mentions_by_category';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

        Highcharts.chart('mentions_by_category_chart', {
            chart: {
                type: 'pie'
            },
            colors: ['#ef370dc7', '#119EFA', '#DDDF00', '#15f34f', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B'],

            title: {
                text: '<?php echo _l("mentions_by_category"); ?>',
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
function mentions_chart(data_filter){
    "use strict";
    $('#mentions_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'mentions_chart';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

   Highcharts.chart('mentions_chart', {
        colors: [ '#1877F2','#84c529','#69C9D0','#000000', '#FF0000'],

        title: {
            text: '<?php echo _l("mentions_chart"); ?>'
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
            categories: response.mentions_chart.categories
        },

        series: response.mentions_chart.data,
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


function social_media_reach_chart(data_filter){
    "use strict";
    $('#social_media_reach_chart').html('<div class="loader-action"></div>');

    data_filter['type'] = 'social_media_reach_chart';
    $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

   Highcharts.chart('social_media_reach_chart', {
        colors: [ '#84c529', '#FF0000'],

        title: {
            text: '<?php echo _l("social_media_reach_chart"); ?>'
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
            categories: response.social_media_reach_chart.categories
        },

        series: response.social_media_reach_chart.data,
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


</script>