// spipu-document-ready.js

class SpipuDocumentReady {
    constructor() {
        this.isReady = false;
        this.callback = [];

        $(document).ready($.proxy(this.execute, this));
    }

    add(callback) {
        if (this.isReady) {
            callback();
        } else {
            this.callback.push(callback);
        }
    }

    execute() {
        if (this.isReady) {
            return;
        }

        this.isReady = true;
        for (let id = 0; id < this.callback.length; id++) {
            this.callback[id]();
        }
    }
}

window.documentReady = new SpipuDocumentReady();
