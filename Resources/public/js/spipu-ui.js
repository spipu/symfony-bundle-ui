// spipu-ui.js

class SpipuUi {
    constructor() {
        this.initConfirm();
    }

    initConfirm() {
        $(".confirm-action").click(
            function () {
                let actionName = $(this).data('action-role');
                if (!actionName) {
                    actionName = 'make this action on';
                }
                let message = 'Do you really want to ' + actionName + ' this item ?'

                return confirm(message);
            }
        );
    }

    submitForm(code) {
        $("form#form_" + code + " :submit").click();
    }
}

window.documentReady.add(
    function () {
        new SpipuUi();
    }
);
