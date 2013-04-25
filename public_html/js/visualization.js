$(function () {
  var displayChart = function (dataRaw) {
        var colors = Highcharts.getOptions().colors,
            categories = [],
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
          categories.push(element.firstname + " " + element.lastname);
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

        $.ajax({
            dataType: "json",
            url: '/data/list.json',
            success: function (data) {
              displayChart(data);
            }
        });
    });
