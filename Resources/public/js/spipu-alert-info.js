// spipu-alert-info.js

class SpipuAlertInfo {
    constructor() {
    }

    init() {
        this.container = $('#globalAlertInfo');
        this.container.addClass('alert').addClass('text-center').addClass('fixed-top').addClass('m-3');
        this.hide();
    }

    hide() {
        this.container
            .removeClass('alert-success')
            .removeClass('alert-danger')
            .removeClass('alert-secondary')
            .text('')
            .hide();
    }

    display(message, type, autoHide) {
        this.hide();

        this.container
            .addClass('alert-' + type)
            .text(message)
            .show();

        let height = $('header').outerHeight();
        if ($('header nav.navbar')) {
            height = $('header nav.navbar').outerHeight();
        }

        this.container.css('top', height);

        if (autoHide) {
            this.container.delay(5000).fadeOut('slow');
        }
    }

    displaySuccess(message) {
        this.display(message, 'success', true);
    }

    displayError(message, autoHide = true) {
        this.display(message, 'danger', autoHide);
    }

    displayInfo(message) {
        this.display(message, 'secondary', true);
    }
}

window.AlertInfo = new SpipuAlertInfo();

window.documentReady.add(function () {
    window.AlertInfo.init();
});
