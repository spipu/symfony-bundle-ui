// spipu-confirm-action.js

class SpipuConfirmAction {
    constructor() {
        $(".confirm-action").click(
            function () {
                let actionName = $(this).data('action-role');
                if (!actionName) {
                    actionName = window.translator.trans('spipu.ui.label.default_confirm_action');
                }

                let message = window.translator.trans('spipu.ui.label.default_confirm_message', [actionName]);

                return confirm(message);
            }
        );
    }
}

window.documentReady.add(
    function () {
        new SpipuConfirmAction();
    }
);
