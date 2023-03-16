// spipu-bootstrap-tabs.js

class SpipuBootstrapTabs {
    constructor() {
        this.hash = window.location.hash;
    }

    display() {
        if (!this.hash) {
            return false;
        }

        $('ul.nav a[href="' + this.hash + '"]').tab('show');
    }

    getCurrent() {
        return this.hash;
    }
}

window.bootstrapTabs = new SpipuBootstrapTabs();

window.documentReady.add(function () {
    window.bootstrapTabs.display();
});
