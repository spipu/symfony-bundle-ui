// spipu-form.js

class SpipuUiForms {
    init() {
        $("div[data-form-role=form]").each(
            function () {
                new SpipuUiForm($(this).data('form-code'));
            }
        )
    }

    submitForm(code) {
        $("form#form_" + code + " :submit").click();
    }
}

class SpipuUiForm {
    constructor(code) {
        this.code = code;
        this.fields = {};

        this.init();
    }

    init() {
        let uiForm = this;

        $("div[data-form-code=" + this.code + "][data-form-role=field]").each(
            function () {
                uiForm.addField(this);
            }
        );

        for (let fieldCode in this.fields) {
            if (!this.fields.hasOwnProperty(fieldCode)) {
                continue;
            }

            this.initField(this.fields[fieldCode]);
        }
    }

    addField(divNode) {
        let fieldCode = $(divNode).data('field-code');
        let fieldNode = $(divNode).find('input');
        if (fieldNode.length === 0) {
            fieldNode = $(divNode).find('select');
        }
        if (fieldNode.length === 0) {
            fieldNode = $(divNode).find('textarea');
        }
        if (fieldNode.length === 0) {
            return;
        }

        this.fields[fieldCode] = {
            'code': fieldCode,
            'node': fieldNode[0],
            'constraints': $(divNode).data('field-constraints')
        };
    }

    initField(field) {
        if (field.constraints.length === 0) {
            return;
        }

        for (let constraintId in field.constraints) {
            this.initFieldConstraint(field, field.constraints[constraintId]);
        }
    }

    initFieldConstraint(field, constraint) {
        let askedFieldCode = constraint.field;
        if (this.fields[askedFieldCode] === undefined) {
            return;
        }

        this.updateFieldConstraint(field, constraint);
        $(this.fields[askedFieldCode].node).on(
            'change',
            $.proxy(function () { this.updateFieldConstraint(field, constraint); }, this)
        )
    }

    updateFieldConstraint(field, constraint) {
        let askedFieldCode = constraint.field;

        let askedField = this.fields[askedFieldCode];
        let askedNode  = askedField.node;
        let askedValue = constraint.value;

        let currentValue = askedNode.value;
        if (askedNode.tagName.toLowerCase() === 'input' && askedNode.type.toLowerCase() === 'checkbox' && !askedNode.checked) {
            currentValue = '';
        }
        let isGoodValue = (askedValue === currentValue);

        $(field.node).prop('disabled', !isGoodValue);
    }
}

window.spipuUiForms = new SpipuUiForms();
window.documentReady.add(function () { window.spipuUiForms.init() });
