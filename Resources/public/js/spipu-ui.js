$(document).ready(
    function () {
        spipuUiInitConfirmAction();
        spipuUiInitGrid();
    }
);

function spipuUiInitConfirmAction()
{
    $(".confirm-action").click(
        function () {
            let actionName = $(this).data('action-role');
            if (!actionName) {
                actionName = 'make this action on';
            }
            return confirm('Do you really want to ' + actionName + ' this item ?');
        }
    );
}

function spipuUiInitGrid()
{
    $("span[data-grid-role=count]").each(
        function () {
            new SpipuUiGrid($(this).data('grid-code'));
        }
    )
}

function SpipuUiGrid(code)
{
    this.code = code;
    this.count = 0;
    this.ids = [];

    this.init();
    this.update();
}

SpipuUiGrid.prototype.getElement = function (role) {
    return $("[data-grid-code=" + this.code + "][data-grid-role=" + role + "]");
};

SpipuUiGrid.prototype.getValue = function (target) {
    return parseInt($(target).val());
};

SpipuUiGrid.prototype.init = function () {
    let that = this;

    that.getElement('checkbox').prop("checked", false);
    that.getElement('checkbox-all').prop("checked", false);

    that.getElement('checkbox').change(
        function () {
            that.checkChange(this)
        }
    );

    that.getElement('checkbox').click(
        function (e) {
            e.stopPropagation();
        }
    );
    that.getElement('checkbox').parent().click(
        function () {
            $(this).children().trigger('click');
        }
    );

    that.getElement('checkbox-all').change(
        function () {
            that.checkAllChange(this);
        }
    );
    that.getElement('checkbox-all').click(
        function (e) {
            e.stopPropagation();
        }
    );
    that.getElement('checkbox-all').parent().click(
        function () {
            $(this).children().trigger('click');
        }
    );

    that.getElement('action').click(
        function () {
            that.actionSelected(this)
        }
    );
};

SpipuUiGrid.prototype.update = function () {
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

    this.update();
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

    that.update();
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