<script type="text/javascript">
var date_filter;
var account_filter;
var social;
var selectedMetrics = [];
var totalMetrics = [];
var activeStat;

(function($) {
	"use strict";
    social = $('input[name=social]').val();
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

    $('select[name="account_id"]').on('change', function() {
        reloadDashboard();
    });

    $('input[name="from_date"]').on('change', function() {
        if($(this).val() == ''){
            alert("<?php echo _l('from_date_cannot_be_blank'); ?>");
        }

        if($('input[name=from_date]').val() != '' && $('input[name=to_date]').val() != ''){
            reloadDashboard();
        }
    });

    $('input[name="to_date"]').on('change', function() {
        if($(this).val() == ''){
            alert("<?php echo _l('to_date_cannot_be_blank'); ?>");
        }

        if($('input[name=from_date]').val() != '' && $('input[name=to_date]').val() != ''){
            reloadDashboard();
        }

    });

    reloadMetrics()


    
})(jQuery);

function dashboard_do_filter_active(value) {
    "use strict";
  if (value !== "" && typeof value != "undefined") {
    $('[data-cview="all"]').parents("li").removeClass("active");
    var selector = $('[data-cview="' + value + '"]');
    
    var parent = selector.parents("li");
    if (parent.hasClass("filter-group")) {
      var group = parent.data("filter-group");
      $('[data-filter-group="' + group + '"]')
        .not(parent)
        .removeClass("active");
      $.each($('[data-filter-group="' + group + '"]').not(parent), function () {
        $('input[name="' + $(this).find("a").attr("data-cview") + '"]').val("");
      });
    }
    if (!parent.not(".dropdown-submenu").hasClass("active")) {
      parent.addClass("active");
    } else {
      parent.not(".dropdown-submenu").removeClass("active");
      parent.find("a").blur();
      // Remove active class from the parent dropdown if nothing selected in the child dropdown
      var parents_sub = selector.parents("li.dropdown-submenu");
      if (parents_sub.length > 0) {
        if (parents_sub.find("li.active").length === 0) {
          parents_sub.removeClass("active");
        }
      }
      value = "";
    }
    return value;
  } else {
    $("._filters input").val("");
    $("._filter_data li.active").removeClass("active");
    $('[data-cview="all"]').parents("li").addClass("active");
    return "";
  }
}

// Datatables custom view will fill input with the value
function dashboard_custom_view(value, $lang, custom_input_name, clear_other_filters) {
    "use strict";

    var name =
    typeof custom_input_name == "undefined" ? "custom_view" : custom_input_name;
      if (typeof clear_other_filters != "undefined") {
        var filters = $("._filter_data li.active").not(".clear-all-prevent");
        filters.removeClass("active");
        $.each(filters, function () {
          var input_name = $(this).find("a").attr("data-cview");
          $('._filters input[name="' + input_name + '"]').val("");
        });
      }

      if (isNaN(value)) {
        var _cinput = dashboard_do_filter_active(value);
      }else{
        var _cinput = dashboard_do_filter_active(name);
        if (_cinput != name) {
            value = "";
        }
      }

      $('input[name="' + name + '"]').val(value);

    reloadDashboard();
}

function dashboard_account_custom_view(value, $lang, custom_input_name, clear_other_filters) {
    "use strict";

    account_filter = value;

    $('#btn_account_filter').html('<i class="fa fa-filter" aria-hidden="true"></i> '+$lang);

    dashboard_account_custom_view('last_30_days',"<?php echo _l('last_30_days'); ?>",'last_30_days');
}

function session_per_channel_pie_init(data_filter){
    "use strict";
    $('#session_per_channel').html('<div class="loader-action"></div>');

    data_filter['type'] = 'session_per_channel_pie';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

        $('#session_per_channel').html(response.content_html);
        Highcharts.chart('session_per_channel', {
            chart: {
                type: 'pie',
              
            },
            colors: ['#15f34f', '#ef370dc7',  '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B'],

            title: {
                text: response.session_per_channel.name,
            },
            tooltip: {
                formatter: function () {
                    if(response.session_per_channel.type == 'TYPE_SECONDS'){
                        const seconds = this.y;
                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = Math.floor(seconds % 60);

                        if (hours === 0) {
                            return `${minutes.toString().padStart(2, '0')}m ` +
                                   `${secs.toString().padStart(2, '0')}s`;
                        }

                        return `${hours.toString().padStart(2, '0')}h ` +
                               `${minutes.toString().padStart(2, '0')}m ` +
                               `${secs.toString().padStart(2, '0')}s`;
                    }

                    return this.y;
                }
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
                    name: response.session_per_channel.name,
                    colorByPoint: true,
                    data: response.session_per_channel.data
                }
            ]
        });
    });
}


function session_per_channel_column_init(data_filter){
    "use strict";
    $('#session_per_channel').html('<div class="loader-action"></div>');
    data_filter['type'] = 'session_per_channel';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);
        const getRandomColor = () => '#' + Math.floor(Math.random() * 16777215).toString(16);

        Highcharts.chart('session_per_channel', {
            chart: {
                type: 'column'
            },
          colors: ['#119EFA', '#DDDF00', '#15f34f', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B'],
            title: {
                text: response.session_per_channel.name,            
            },
            xAxis: {
                categories: response.session_per_channel.header
            },
            yAxis: {
                min: 0,
                title: {
                    text: ''
                },
            },
            legend: {
                  enabled: false
            },

            tooltip: {
                formatter: function () {
                    if(response.session_per_channel.type == 'TYPE_SECONDS'){
                        const seconds = this.y;
                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = Math.floor(seconds % 60);

                        if (hours === 0) {
                            return `${minutes.toString().padStart(2, '0')}m ` +
                                   `${secs.toString().padStart(2, '0')}s`;
                        }

                        return `${hours.toString().padStart(2, '0')}h ` +
                               `${minutes.toString().padStart(2, '0')}m ` +
                               `${secs.toString().padStart(2, '0')}s`;
                    }

                    return this.y;
                }
            },
            
            credits: {
                      enabled: false
                    },
            series: [{
                name: response.session_per_channel.name,
                data: response.session_per_channel.data_total.map(value => ({
                    y: value,
                    color: getRandomColor()
                }))
            }]
        });

    //hide boxloading
    $('#box-loading').html('');
    });
}

function session_per_day_init(data_filter){
    "use strict";
    $('#session_per_day').html('<div class="loader-action"></div>');

    data_filter['type'] = 'session_per_day';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);

    Highcharts.chart('session_per_day', {
        colors: ['#ef370dc7'],

        title: {
            text: response.session_per_day.name
        },
        legend: {
            enabled: false 
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
            categories: response.session_per_day.categories
        },

        series: response.session_per_day.data,
        
        tooltip: {
            formatter: function () {
                if(response.session_per_day.type == 'TYPE_SECONDS'){
                    const seconds = this.y;
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = Math.floor(seconds % 60);

                    if (hours === 0) {
                        return `${minutes.toString().padStart(2, '0')}m ` +
                               `${secs.toString().padStart(2, '0')}s`;
                    }

                    return `${hours.toString().padStart(2, '0')}h ` +
                           `${minutes.toString().padStart(2, '0')}m ` +
                           `${secs.toString().padStart(2, '0')}s`;
                }

                return this.y;
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

    //hide boxloading
    $('#box-loading').html('');
    });
}

function ga_tab_active(tab, sub_tab){
    "use strict";

    $('input[name=ga_tab_active]').val(tab);
    $('input[name=ga_sub_tab_active]').val(sub_tab);

    document.querySelectorAll('#tab_'+tab+' li.active').forEach(tab => {
        tab.classList.remove('active');
    });

    document.querySelectorAll('#tab_'+tab+' .ga-tab-content.active').forEach(content => {
        content.classList.remove('active');
    });

    $('div[id=tab_' + sub_tab+']').addClass('active');
    $('a[aria-controls=tab_' + sub_tab+']').parent().addClass('active');

    reloadMetrics();
}

function selectMetric(checkbox) {
    "use strict";
  if (checkbox.checked) {
    if (selectedMetrics.length >= 10) {
      checkbox.checked = false;
      alert("<?php echo _l('ga_metric_limit_note'); ?>");
      return;
    }
    selectedMetrics.push(checkbox.value);
  } else {
    selectedMetrics = selectedMetrics.filter(metric => metric !== checkbox.value);
  }
  
  reloadDashboard();
}

function reloadDashboard() {
    "use strict";
    if(selectedMetrics.length == 0){
        return false;
    }

    if($('select[name=account_id]').val() == '' || $('select[name=account_id]').val() == null){
        return false;
    }
    if($('input[name=from_date]').val() == '' || $('input[name=from_date]').val() == null){
        return false;
    }

    if($('input[name=to_date]').val() == '' || $('input[name=from_date]').val() == null){
        return false;
    }

    var data_filter = {};
    data_filter['social'] = social;
    data_filter['account_id'] = $('select[name=account_id]').val();
    data_filter['ga_tab_active'] = $('input[name=ga_tab_active]').val();
    data_filter['ga_sub_tab_active'] = $('input[name=ga_sub_tab_active]').val();
    data_filter['from_date'] = $('input[name=from_date]').val();
    data_filter['to_date'] = $('input[name=to_date]').val();
    data_filter['metrics'] = selectedMetrics;
    data_filter['total_metrics'] = totalMetrics;

    $('#top_stats').html('<div class="loader-action"></div>');
    data_filter['type'] = 'top_stats';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        $('#top_stats').html(response);
        const stats = document.querySelectorAll('.stat-card');
        stats.forEach(stat => {
            if(stat.classList.contains('active')){
                activeStat = stat.id;
            }
            stat.addEventListener('click', function() {
                stats.forEach(s => s.classList.remove('active'));
                stat.classList.add('active');
                activeStat = stat.id;
                reloadChart();
            });
        });

        reloadChart();
    });

    if(data_filter['ga_tab_active'] == 'audience' && (data_filter['ga_sub_tab_active'] == 'age' || data_filter['ga_sub_tab_active'] == 'gender')){
        $('#table_data').html('');
        return false;
    }

    $('#table_data').html('<div class="loader-action"></div>');
    data_filter['type'] = 'table_data';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        $('#table_data').html(response);
    });

}

function reloadChart() {
    "use strict";
    var data_filter = {};
    data_filter['social'] = social;
    data_filter['account_id'] = $('select[name=account_id]').val();
    data_filter['activeStat'] = activeStat;
    data_filter['from_date'] = $('input[name=from_date]').val();
    data_filter['to_date'] = $('input[name=to_date]').val();
    data_filter['ga_tab_active'] = $('input[name=ga_tab_active]').val();
    data_filter['ga_sub_tab_active'] = $('input[name=ga_sub_tab_active]').val();

        session_per_day_init(data_filter);
    

    if((data_filter['ga_tab_active'] == 'acquisition' && (data_filter['ga_sub_tab_active'] == 'all' || data_filter['ga_sub_tab_active'] == 'organic_search' || data_filter['ga_sub_tab_active'] == 'paid_search' || data_filter['ga_sub_tab_active'] == 'paid_social')) || (data_filter['ga_tab_active'] == 'audience' && (data_filter['ga_sub_tab_active'] == 'devices' || data_filter['ga_sub_tab_active'] == 'gender' || data_filter['ga_sub_tab_active'] == 'new_vs_returning')) || (data_filter['ga_tab_active'] == 'conversions' && data_filter['ga_sub_tab_active'] == 'ecommerce')){
        session_per_channel_pie_init(data_filter);
    }else{
        session_per_channel_column_init(data_filter);
    }

}

function reloadMetrics() {
    "use strict";
  
    var Dashboard_Filters = $("._hidden_inputs._filters._tasks_filters input");
    var data_filter = {};
    data_filter['social'] = social;
    
    $.each(Dashboard_Filters, function () {
        if($('[name="' + $(this).attr("name") + '"]').val() != ''){

      data_filter[$(this).attr("name")] =
        $('[name="' + $(this).attr("name") + '"]').val();
        }
    });

    $('#dashboard-options').html('<div class="loader-action"></div>');
    $.post(admin_url + 'google_analytic/get_metrics_list', data_filter).done(function(response){
        $('#dashboard-options').html(response);

        var checkedBoxes = document.querySelectorAll('input[name^="dashboard_metrics"]');
        totalMetrics = Array.from(checkedBoxes).map(box => box.value);

        var checkedBoxes = document.querySelectorAll('input[name^="dashboard_metrics"]:checked');

        checkedBoxes.forEach((element, index) => {
            if(index >= 10){
                element.checked = false;
            }
        });

        var checkedBoxes = document.querySelectorAll('input[name^="dashboard_metrics"]:checked');
        selectedMetrics = Array.from(checkedBoxes).map(box => box.value);

        reloadDashboard();
    });
}


function map_chart_init(data_filter){
    "use strict";
    $('#session_per_day').html('<div class="loader-action"></div>');

    (async () => {

    const topology = await fetch(
        'https://code.highcharts.com/mapdata/custom/world.topo.json'
    ).then(response => response.json());

    data_filter['type'] = 'map_chart_init';
    $.post(admin_url + 'google_analytic/get_data_analytics', data_filter).done(function(response){
        response = JSON.parse(response);
         const text = "Dòng 1\nDòng 2\nDòng 3";
        const element = document.getElementById("map_csv");

        element.innerText = text;
        Highcharts.mapChart('session_per_day', {
            chart: {
                map: topology
            },

            title: {
                text: response.map_chart.name,
            },

            credits: {
              enabled: false
            },

            colorAxis: {
               enabled: false
            },

            data: {
                csv: document.getElementById('map_csv').innerText,
                seriesMapping: [{
                    code: 0,
                    value: 1
                }]
            },

            tooltip: {
                valueDecimals: 1,
                valueSuffix: ' years'
            },

            series: [{
                name: 'Life expectancy',
                joinBy: ['iso-a3', 'code'],
                dataLabels: {
                    enabled: true,
                    format: '{point.value:.0f}',
                    filter: {
                        operator: '>',
                        property: 'labelrank',
                        value: 250
                    },
                    style: {
                        fontWeight: 'normal'
                    }
                }
            }]
        });
        
    });
    })();
}
</script>