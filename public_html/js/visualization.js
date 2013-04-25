$(function () {
  var displayChart = function (categories, dataRaw) {
        var colors = Highcharts.getOptions().colors,
            name = 'Money raised';

        var data = _.toArray(dataRaw);

        _.each(data, function (element, index) {
          element.y = element.points;
        });

        data = _.sortBy(data, function (element) {
          return -element.y;
        });

        data = _.first(data, 5);

        _.each(data, function (element, index) {
          element.color = colors[index + 1];
        });

        var chart = $('#visualizationContainer').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'The most successful networks'
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                title: {
                    text: 'Total money raised'
                }
            },
            plotOptions: {
                column: {
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: colors[0],
                        style: {
                            fontWeight: 'bold'
                        },
                        formatter: function() {
                            return this.y +' €';
                        }
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    var point = this.point,
                        s = this.x +':<b>'+ this.y +'€ </b> raise together with network';
                    return s;
                }
            },
            series: [{
                name: name,
                data: data,
                color: 'white'
            }],
            exporting: {
                enabled: false
            }
        })
        .highcharts(); // return chart
};

var categories = ['Jan Kowaliski', 'Jeck Test', 'Lorem Ipsum', 'Saf Ari', 'Web Kit'];

        $.ajax({
            dataType: "json",
            url: '/donate/list',
            success: function (data) {
              displayChart(categories, data);
            }
        });
    });
