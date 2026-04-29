// spipu-ui/spipu-graph-donut.js

class SpipuGraphDonut {
    constructor(
        destinationId,
        data,
        options = {}
    ) {
        this.graph = new GoogleGraphDonut(
            destinationId,
            data,
            options
        );
    }
}

window.SpipuGraphDonut = SpipuGraphDonut;
