// spareka-core/google-graph-donut.js

class GoogleGraphDonut {
    constructor(destinationId, data, options = {}) {
        this.destinationId = destinationId;
        this.data    = data;
        this.options = Object.assign({
            pieHole: 0.4,
            pieSliceText: 'percent',
            sliceVisibilityThreshold: 0,
            chartArea: {
                left:   10,
                right:  10,
                top:    10,
                bottom: 10
            },
        }, options);

        this.resizeCallback = null;

        this.init();
    }

    init() {
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(this.initDraw.bind(this));
    }

    initDraw() {
        this.resizeCallback = this.draw.bind(this);

        $(window).on('resize', this.resizeCallback);
        setTimeout(this.resizeCallback, 100);
    }

    draw() {
        let element = document.getElementById(this.destinationId);
        if (!element) {
            return;
        }

        let data = google.visualization.arrayToDataTable(this.data);

        let chart = new google.visualization.PieChart(element);
        chart.draw(data, this.options);
    }

    destroy() {
        if (this.resizeCallback) {
            $(window).off('resize', this.resizeCallback);
            this.resizeCallback = null;
        }
    }
}

window.GoogleGraphDonut = GoogleGraphDonut;
