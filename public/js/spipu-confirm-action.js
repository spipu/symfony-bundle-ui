// spipu-confirm-action.js

class SpipuConfirmAction {
    constructor() {
        $(".confirm-action").click(
            function () {
                // Full message already translated by the button macros; attr() keeps it as a raw string.
                let message = $(this).attr('data-action-message');
                if (!message) {
                    let actionName = $(this).data('action-role');
                    if (!actionName) {
                        actionName = window.translator.trans('spipu.ui.label.default_confirm_action');
                    }
                    message = window.translator.trans('spipu.ui.label.default_confirm_message', [actionName]);
                }

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
