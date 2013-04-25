$(function () {
  var displayChart = function (categories, data) {
        var colors = Highcharts.getOptions().colors,
            name = 'Money raised';

        _.each(data, function (element, index) {
          element.color = colors[index];
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
data = [{
        y: 531,
    }, {
        y: 12,
    }, {
        y: 1,
    }, {
        y: 0.15,
    }, {
        y: 0.14,
    }];
displayChart(categories, data);
    });
