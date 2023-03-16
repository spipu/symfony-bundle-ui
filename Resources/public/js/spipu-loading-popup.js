// spipu-loading-popup.js

class SpipuLoadingPopup {
    constructor(message) {
        this.message = message;
        this.open();
    }

    open() {
        let popup =
            '<div class="modal" id="modalWindow" tabindex="-1" role="dialog" aria-hidden="true">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content text-center ">' +
                        '<div class="modal-title d-flex justify-content-center">' +
                            '<span class="spinner-border text-success m-3"><span class="sr-only"></span></span>' +
                            '<span class="mt-3 h4">' + this.message + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $("#confirmPopup").html(popup);
        $("#modalWindow").modal({keyboard: false, backdrop: 'static', show: true });
    }

    close() {
        $("#modalWindow").modal('hide');
        $("#confirmPopup").html('');
    }
}

class SpipuLoadingPopups {
    constructor() {
    }

    init() {
        let that = this;
        $(".loading-popup").click(function () {
            let tag = $(this);
            let message = tag.data('loading-message');

            that.create(message);
            return true;
        });
    }

    create(message) {
        if (message === undefined) {
            message = window.translator.trans('spipu.ui.label.please_wait');
        }

        return new SpipuLoadingPopup(message);
    }

    disableButton(element) {
        let className = element.attr('class');
        className = className.replace('primary', 'secondary');
        className = className.replace('success', 'secondary');
        className = className.replace('danger', 'secondary');
        className = className.replace('warning', 'secondary');
        className = className.replace('info', 'secondary');

        element.removeAttr("href");
        element.off('click');
        element.attr( "class", className);
        element.html('<i class="fa fa-spinner"></i>');
    }
}

window.LoadingPopups = new SpipuLoadingPopups();

window.documentReady.add(function () {
    window.LoadingPopups.init();
});
