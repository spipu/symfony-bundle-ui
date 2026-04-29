// spipu-ui/spipu-graph-donut.js

class SpipuGraphDonut {
    constructor(
        destinationId,
        data,
        options = {}
    ) {
        this.graph = new GoogleGraphDonut(
            destinationId,
            SpipuGraphDonut.toGoogleData(data),
            options
        );
    }

    static toGoogleData(data) {
        let total = 0;
        for (let i = 0; i < data.length; i++) {
            total += Number(data[i].value) || 0;
        }
        if (total <= 0) {
            total = 1;
        }

        let rows = [['Label', 'Value']];
        for (let i = 0; i < data.length; i++) {
            let value = Number(data[i].value) || 0;
            let percent = (100 * value / total).toFixed(1);
            rows.push([
                data[i].label + ' (' + value + ' - ' + percent + '%)',
                value
            ]);
        }

        return rows;
    }
}

window.SpipuGraphDonut = SpipuGraphDonut;
