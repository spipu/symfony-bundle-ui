// spipu-ui/google-graph-plots.js

class GoogleGraphPlots {
    constructor(
        destinationId,
        dateFrom,
        dateTo,
        source,
        forceMinToZero,
        dateFormat = "yyyy-MM-dd HH:mm",
        margeBottom = 60,
    ) {
        this.destinationId  = destinationId;
        this.dateFrom       = dateFrom;
        this.dateTo         = dateTo;
        this.source         = source;
        this.forceMinToZero = forceMinToZero;
        this.dateFormat     = dateFormat;
        this.margeBottom    = margeBottom;

        this.init();
    }

    init() {
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(
            this.initDraw.bind(this)
        );
        return true;
    }

    initDraw() {
        $(window).resize(this.draw.bind(this));

        this.draw();
    }

    draw() {
        let rows = [];
        let headers;
        for (let key in this.source) {
            let values = this.source[key]['v'] ? this.source[key]['v'] : 0.;
            if (typeof values !== 'object') {
                values = {'Value': values};
            }

            let row = [];
            headers = [];
            row.push(this.getDateFromString(this.source[key]['d']));
            for (let keyV in values) {
                row.push(values[keyV]);
                headers.push(keyV);
            }

            rows.push(row);
        }

        let graphChartData = new google.visualization.DataTable();
        graphChartData.addColumn('datetime', 'Date');
        headers.forEach(function(item, index) {
            graphChartData.addColumn('number', item);
        });
        graphChartData.addRows(rows);

        let date_formatter = new google.visualization.DateFormat({pattern: this.dateFormat});
        date_formatter.format(graphChartData, 0);

        // prepare the options
        let graphChartOptions = {
            chartArea: {
                left:   60,
                right:  5,
                top:    5,
                bottom: this.margeBottom
            },
            series: [{targetAxisIndex:0}],
            vAxes: {
                0: {
                    textStyle: { fontSize: 10 },
                    titleTextStyle: { fontSize: 0 },
                    viewWindowMode: 'maximized'
                }
            },
            focusTarget: 'category',
            hAxis: {
                slantedText: true,
                textStyle: { fontSize: 10 },
                format: this.dateFormat,
                gridlines: { count: 10 },
                minorGridlines: { count: 2 },
                minValue: this.getDateFromString(this.dateFrom),
                maxValue: this.getDateFromString(this.dateTo)
            },
            explorer: null,
            legend: { position: 'none' }
        };

        if (this.forceMinToZero) {
            graphChartOptions.vAxes[0].minValue = 0;
        }

        let chart = new google.visualization.LineChart(document.getElementById(this.destinationId));
        chart.draw(graphChartData, graphChartOptions);
    }

    getDateFromString(value) {
        var date = value.replace(/[^0-9]/g, ' ').split(' ');
        if (date.length === 3) {
            return new Date(1, 1, 1, date[0], date[1], date[2]);
        }

        return new Date(date[0], date[1]-1, date[2], date[3], date[4], date[5]);
    }
}

window.GoogleGraphPlots = GoogleGraphPlots;
