
<script type="text/javascript">
var date_filter;
var account_filter;
var social;

(function($) {
    "use strict";

    init_tags_inputs();

    social = $('input[name=social]').val();
    Highcharts.setOptions({
        lang: {
            thousandsSep: ','
        }
    });

   
    load_mention_list();

})(jQuery);

function escapeHtml(text) {
    "use strict";
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function load_mention_list(){
    "use strict";

    const raw = $('textarea[name="description"]').val();
    const escaped = escapeHtml(raw).replace(/\n/g, '<br>');
    $('#rp_description').html(escaped);

    $('#filter-form input[name=page]').val(1);
    var data_filter = {};
    data_filter.search = $('#filter-form input[name=search]').val();
    data_filter.pdf_report = 1; 
    data_filter.visited = $('#filter-form select[name=visited]').val();
    data_filter.sentiment = $('#filter-form select[name=sentiment]').val();

    data_filter.sources = $('#filter-form select[name=sources]').val();
    data_filter.from_date = $('#filter-form input[name=from_date]').val();
    data_filter.to_date = $('#filter-form input[name=to_date]').val();
    data_filter.tags = $('#filter-form input[name=tags]').val();

    if($('input[name="active_top_stats"]').is(':checked') == true){
        $('#top_stats').removeClass('hide');
        $('#top_stats').html('<div class="loader-action"></div>');
        data_filter['type'] = 'analysis_top_stats';
        $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
            $('#top_stats').html(response);
        });
    }else{
        $('#top_stats').addClass('hide');
    }

    if($('input[name="active_mentions_chart"]').is(':checked') == true){
        $('#mentions_chart').removeClass('hide');
        mentions_chart(data_filter);
    }else{
        $('#mentions_chart').addClass('hide');
    }

    if($('input[name="active_social_media_reach_chart"]').is(':checked') == true){
        $('#social_media_reach_chart').removeClass('hide');
        social_media_reach_chart(data_filter);
    }else{
        $('#social_media_reach_chart').addClass('hide');
    }

    if($('input[name="active_summary_stats"]').is(':checked') == true){
        $('#summary_stats').removeClass('hide');
        $('#summary_stats').html('<div class="loader-action"></div>');
        data_filter['type'] = 'summary_stats';
        $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
            $('#summary_stats').html(response);
        });
    }else{
        $('#summary_stats').addClass('hide');
    }
    
    if($('input[name="active_summary_sources"]').is(':checked') == true){
        $('#summary_sources').removeClass('hide');
        $('#summary_sources').html('<div class="loader-action"></div>');
        data_filter['type'] = 'summary_sources';
        $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
            $('#summary_sources').html(response);
        });
    }else{
        $('#summary_sources').addClass('hide');
    }
    
    if($('input[name="active_mentions_by_category_chart"]').is(':checked') == true){
        $('#mentions_by_category_chart').removeClass('hide');
        
        mentions_by_category(data_filter);
    }else{
        $('#mentions_by_category_chart').addClass('hide');
    }

    if($('input[name="active_tag_stats"]').is(':checked') == true){
        $('#tag_stats').removeClass('hide');
        
        $('#tag_stats').html('<div class="loader-action"></div>');
        data_filter['type'] = 'summary_tag_stats';
        $.post(admin_url + 'reputation/get_data_analytics', data_filter).done(function(response){
            $('#tag_stats').html(response);
        });
    }else{
        $('#tag_stats').addClass('hide');
    }
    

    if($('input[name="active_mentions_reach_chart"]').is(':checked') == true){
        $('#mentions_reach_chart').removeClass('hide');
        
        mentions_reach_chart(data_filter);
    }else{
        $('#mentions_reach_chart').addClass('hide');
    }
    
    if($('input[name="active_sentiment_chart"]').is(':checked') == true){
        $('#sentiment_chart').removeClass('hide');
        
        sentiment_chart(data_filter);
    }else{
        $('#sentiment_chart').addClass('hide');
    }

    if($('input[name="active_mention_list"]').is(':checked') == true){
        $('#mention_list').removeClass('hide');

        $('#mention_list').html('<div class="loader-action"></div>');
        $.post(admin_url + 'reputation/get_html_mention_list_pdf', data_filter).done(function(response){
            $('#mention_list').html(response);
        });
    }else{
        $('#mention_list').addClass('hide');
    }

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

async function exportChartsToPDF(filename = "rep_pdf_report.pdf") {
    "use strict";
    //show box loading
    var html = '';
      html += '<div class="accounting-Box">';
      html += '<span>';
      html += '<span></span>';
      html += '</span>';
      html += '</div>';
      $('#accounting-box-loading').html(html);

  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF({
    orientation: "portrait",
    unit: "mm",
    format: "a4"
  });

  const pageWidth = pdf.internal.pageSize.getWidth();
  const pageHeight = pdf.internal.pageSize.getHeight();

  const padding = 5.3; // tương đương 20px (1px ≈ 0.2645mm)
  const contentWidth = pageWidth - padding * 2;

  const chartIds = [
    "top_stats",
    "mentions_by_category_chart",
    "mentions_chart",
    "social_media_reach_chart",
    "mentions_reach_chart",
    "sentiment_chart",
    "tag_stats",
    "summary_stats",
    "summary_sources",
  ];

  let cursorY = padding; // bắt đầu sau lề trên
    const element = document.getElementById("report_info");

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true
    });

    const imgData = canvas.toDataURL("image/png");
    const imgWidth = contentWidth;
    const imgHeight = canvas.height * imgWidth / canvas.width;

    pdf.addImage(imgData, 'PNG', padding, cursorY, imgWidth, imgHeight);
    pdf.addPage();


  for (let i = 0; i < chartIds.length; i++) {
    const id = chartIds[i];
    const element = document.getElementById(id);
    if (!element) continue;
    if(element.classList.contains('hide')) continue;

    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true
    });

    const imgData = canvas.toDataURL("image/png");
    const imgWidth = contentWidth;
    const imgHeight = canvas.height * imgWidth / canvas.width;

    // Nếu không đủ chỗ trên trang hiện tại → add trang mới
    if (cursorY + imgHeight + padding > pageHeight) {
      pdf.addPage();
      cursorY = padding;
    }

    // Thêm hình ảnh vào vị trí hiện tại (có padding trái/phải)
    pdf.addImage(imgData, 'PNG', padding, cursorY, imgWidth, imgHeight);
    cursorY += imgHeight + padding; // padding dưới giữa các biểu đồ
  }

    const mentionList = document.getElementById("mention_list");
    if (!mentionList.classList.contains("hide")){
        if (cursorY > padding) {
            pdf.addPage();
            cursorY = padding;
        }

        cursorY += 10; // xuống dưới tiêu đề
        pdf.setFontSize(16);
        pdf.setFont(undefined, 'bold');
        pdf.text("Mention List", padding, cursorY);
        cursorY += 5; // xuống dưới tiêu đề
        const mentionCards = mentionList.querySelectorAll(".comment-card");

        for (const card of mentionCards) {
          // Bỏ qua thẻ ẩn (nếu có)
          if (card.classList.contains("hide")) continue;

          const canvas = await html2canvas(card, {
            scale: 2,
            useCORS: true
          });

          const imgData = canvas.toDataURL("image/png");
          const imgWidth = contentWidth;
          const imgHeight = canvas.height * imgWidth / canvas.width;

          // Nếu không đủ chỗ → trang mới
          if (cursorY + imgHeight + padding > pageHeight) {
            pdf.addPage();
            cursorY = padding;
          }

          pdf.addImage(imgData, 'PNG', padding, cursorY, imgWidth, imgHeight);
          cursorY += imgHeight + padding;
        }
    }


  pdf.save(filename);

  //hide boxloading
  $('#accounting-box-loading').html('');
}



</script>