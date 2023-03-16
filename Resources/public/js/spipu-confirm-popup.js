// spipu-confirm-popup.js

class SpipuConfirmPopup {
    constructor(message, buttonLabel, buttonIcon, buttonLevel, url, callbackName) {
        this.message      = message;
        this.buttonLabel  = buttonLabel;
        this.buttonIcon   = buttonIcon;
        this.buttonLevel  = buttonLevel;
        this.url          = url;
        this.callbackName = callbackName;
        this.onConfirm = false;
        this.onCancel  = false;
        this.callbackConfirms = [];
        this.callbackCancels  = [];

        this.init();
    }

    init() {
        let popup =
            '<div class="modal fade" id="modalWindow" tabindex="-1" role="dialog" aria-hidden="true">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title" id="answerModalLabel">'+ this.message +'</h5>' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        '</div>' +
                        '<div class="modal-footer">' +
                            '<button type="button" id="modalWindow-confirm" class="btn btn-'+ this.buttonLevel +'">' +
                                '<i class="fa fa-'+ this.buttonIcon +' mr-2"></i>'+ this.buttonLabel +
                            '</button>' +
                            '<button type="button" id="modalWindow-cancel" class="btn btn-outline-secondary" data-dismiss="modal">' +
                                '<i class="fa fa-undo-alt mr-2"></i>' + translator.trans('spipu.ui.label.cancel') +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        $("#confirmPopup").html(popup);

        let modal = $("#modalWindow");
        modal.modal();
        modal.on('hide.bs.modal', $.proxy(this.executeCancel, this));
        modal.find("#modalWindow-confirm").on('click', $.proxy(this.executeConfirm, this));
        modal.find("#modalWindow-cancel").on('click', $.proxy(this.close, this));
    }

    close() {
        $("#modalWindow").modal('hide');
    }

    addCallbackCancel(callback) {
        this.callbackCancels.push(callback);

        return this;
    }

    addCallbackConfirm(callback) {
        this.callbackConfirms.push(callback);

        return this;
    }

    executeConfirm() {
        if (this.onConfirm || this.onCancel) {
            return;
        }
        this.onConfirm = true;

        $("#modalWindow #modalWindow-confirm").attr('disabled', true);
        $("#modalWindow #modalWindow-cancel").attr('disabled', true);

        if (this.url) {
            window.location = this.url;
        }

        if (this.callbackName) {
            window[this.callbackName]();
        }

        for (let k in this.callbackConfirms) {
            this.callbackConfirms[k]();
        }
    }

    executeCancel() {
        if (this.onConfirm || this.onCancel) {
            return;
        }

        this.onCancel = true;

        for (let k in this.callbackCancels) {
            this.callbackCancels[k]();
        }
    }
}

class SpipuConfirmPopups {
    constructor() {
    }

    init() {
        let that = this;

        $(".confirm-modal-link").click(function () {
            let tag = $(this);

            that.create(
                tag.data('action-message'),
                tag.data('action-label'),
                tag.data('action-icon'),
                tag.data('action-level'),
                tag.data('action-url'),
                false
            );

            return false;
        });


        $(".confirm-modal-button").click(function () {
            let tag = $(this);

            that.create(
                tag.data('action-message'),
                tag.data('action-label'),
                tag.data('action-icon'),
                tag.data('action-level'),
                false,
                tag.data('action-script')
            );

            return false;
        });
    }

    create(message, buttonLabel, buttonIcon, buttonLevel, url, callbackName) {
        return new SpipuConfirmPopup(message, buttonLabel, buttonIcon, buttonLevel, url, callbackName);
    }
}

window.ConfirmPopups = new SpipuConfirmPopups();

window.documentReady.add(function () {
    window.ConfirmPopups.init();
});
