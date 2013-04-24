$(function () {

        var colors = Highcharts.getOptions().colors,
            categories = ['Jan Kowaliski', 'Jeck Test', 'Lorem Ipsum', 'Saf Ari', 'Web Kit'],
            name = 'Money raised',
            data = [{
                    y: 55.11,
                    color: colors[0],
                }, {
                    y: 21.63,
                    color: colors[1],
                }, {
                    y: 11.94,
                    color: colors[2],
                }, {
                    y: 7.15,
                    color: colors[3],
                }, {
                    y: 2.14,
                    color: colors[4],
                }];

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
    });
