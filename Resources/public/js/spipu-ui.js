// spipu-ui.js

// Spipu Ui - Global
function SpipuUi()
{
}

SpipuUi.prototype.init = function () {
    this.initConfirm();
    this.initGrids();
    this.initCheckboxTrees();
}

SpipuUi.prototype.initConfirm = function () {
    $(".confirm-action").click(
        function () {
            let actionName = $(this).data('action-role');
            if (!actionName) {
                actionName = 'make this action on';
            }
            return confirm('Do you really want to ' + actionName + ' this item ?');
        }
    );
};

SpipuUi.prototype.initGrids = function () {
    $("span[data-grid-role=total-rows]").each(
        function () {
            new SpipuUiGrid($(this).data('grid-code'));
        }
    )
};

SpipuUi.prototype.initCheckboxTrees = function () {
    $("ul.checkbox-tree").each(
        function () {
            new SpipuUiCheckboxTree($(this));
        }
    )
};

SpipuUi.prototype.submitForm = function (code) {
    $("form#form_" + code + " :submit").click();
}

// Spipu Ui - Grid
function SpipuUiGrid(code)
{
    this.code = code;
    this.count = 0;
    this.ids = [];

    this.init();
}

SpipuUiGrid.prototype.getElement = function (role) {
    return $("[data-grid-code=" + this.code + "][data-grid-role=" + role + "]");
};

SpipuUiGrid.prototype.getValue = function (target) {
    return parseInt($(target).val());
};

SpipuUiGrid.prototype.init = function () {
    this.initFilters();

    if (this.getElement('checkbox-all')) {
        this.initMassActions();
        this.updateMassAction();
    }
}

SpipuUiGrid.prototype.initFilters = function () {
    this.getElement('filter-cancel').click($.proxy(this.resetFilters, this));
}

SpipuUiGrid.prototype.resetFilters = function () {
    let form = this.getElement('filter-form');

    form.find('input').val('');
    form.find('select').val('');
    form.submit();
}

SpipuUiGrid.prototype.initMassActions = function () {
    let that = this;

    that.getElement('checkbox').prop("checked", false);
    that.getElement('checkbox-all').prop("checked", false);

    that.getElement('checkbox')
        .change(function () { that.checkChange(this); })
        .click(function (e) { e.stopPropagation(); })
        .parent().click(function () { $(this).children().trigger('click'); });

    that.getElement('checkbox-all')
        .change(function () { that.checkAllChange(this); })
        .click(function (e) { e.stopPropagation(); })
        .parent().click(function () { $(this).children().trigger('click'); });

    that.getElement('action').click(function () { that.actionSelected(this) });
};

SpipuUiGrid.prototype.updateMassAction = function () {
    this.count = this.ids.length;
    this.getElement('count').html(this.count);

    if (this.count > 0) {
        this.getElement('action').show();
        this.getElement('no-action').hide();
    } else {
        this.getElement('action').hide();
        this.getElement('no-action').show();
    }
};

SpipuUiGrid.prototype.checkChange = function (target) {
    let value = this.getValue(target);
    let checked = $(target).prop("checked");

    this.ids = $.grep(
        this.ids,
        function (id) {
            return id !== value;
        }
    );

    if (checked) {
        this.ids.push(value);
    }

    this.getElement('checkbox-all').prop("checked", false);

    this.updateMassAction();
};

SpipuUiGrid.prototype.checkAllChange = function (target) {
    let checked = $(target).prop("checked");
    let that = this;

    that.getElement('checkbox').prop("checked", checked);

    that.ids = [];
    if (checked) {
        that.getElement('checkbox').each(
            function () {
                that.ids.push(that.getValue(this));
            }
        );
    }

    that.updateMassAction();
};

SpipuUiGrid.prototype.actionSelected = function (target) {
    let form = $(
        '<form />',
        {
            method: 'post',
            action: $(target).data('grid-href')
        }
    );

    form.append($(
        '<input>',
        {
            type: 'hidden',
            name: 'selected',
            value: JSON.stringify(this.ids)
        }
    ));

    form.appendTo('body').submit();
};

// Spipu Ui - CheckBox Tree
function SpipuUiCheckboxTree(mainNode)
{
    this.mainNode = mainNode
    this.code = mainNode.data('tree-code');
    this.init();
}

SpipuUiCheckboxTree.prototype.init = function () {
    let that = this;

    let inputs = this.mainNode.find('input[type=checkbox]');

    inputs.on('change', function () { that.change($(this)); });
    inputs.each(function() { that.toggleChildren($(this)); });
}

SpipuUiCheckboxTree.prototype.change = function (node) {
    node.closest('li').find('ul li input[type=checkbox]').prop('checked', false);

    this.toggleChildren(node);

}

SpipuUiCheckboxTree.prototype.toggleChildren = function (node) {
    node.closest('li').find('ul').toggleClass('useless-node', node.prop('checked'));
}

window.spipuUi = new SpipuUi();

$(document).ready(
    function () {
        spipuUi.init();
    }
);
