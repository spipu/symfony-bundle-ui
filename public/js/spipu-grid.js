// spipu-grid.js

class SpipuUiGrid {
    constructor(code) {
        this.code = code;
        this.count = 0;
        this.ids = [];
        this.config = [];
        this.personalizeSorting = {
            'dragging': null,
            'draggedOver': null
        };

        this.init();
    }

    init() {
        if (this.getElement('checkbox-all')) {
            this.massActionsInit();
            this.massActionsUpdate();
        }

        if (this.getElement('filter-open')) {
            this.filtersInit();
        }

        if (this.getElement('config-form')) {
            this.personalizeInit();
        }
    }

    getElement(role) {
        return $("[data-grid-code=" + this.code + "][data-grid-role=" + role + "]");
    };

    getValue(target) {
        return parseInt($(target).val());
    }

    filtersInit() {
        this.getElement('filter-cancel').click($.proxy(this.filtersReset, this));
        this.getElement('filter-open').click($.proxy(this.filtersOpen, this));
    }

    filtersOpen() {
        this.getElement('search-header-collapse').collapse('hide');
        this.getElement('config-columns-collapse').collapse('hide');
        this.getElement('config-create-collapse').collapse('hide');
        this.getElement('filter-collapse').collapse('show');
    }

    personalizeInit() {
        this.getElement('config-select').on('change', $.proxy(this.personalizeSelect, this));
        this.getElement('config-create').click($.proxy(this.personalizeCreate, this));
        this.getElement('config-delete').click($.proxy(this.personalizeDelete, this));
        this.getElement('config-configure').click($.proxy(this.personalizeConfigure, this));
        this.getElement('config-cancel').click($.proxy(this.personalizeCancel, this));

        this.personalizeSortColumnsInit();
    }

    personalizeCancel() {
        window.location.reload();
    }

    personalizeDelete() {
        this.getElement('config-form-action').val('delete');
    }

    personalizeCreate() {
        this.getElement('filter-collapse').collapse('hide');
        this.getElement('search-header-collapse').collapse('hide');
        this.getElement('config-select-collapse').collapse('hide');
        this.getElement('config-columns-collapse').collapse('hide');
        this.getElement('config-create-collapse').collapse('show');
    }

    personalizeConfigure() {
        this.getElement('filter-collapse').collapse('hide');
        this.getElement('search-header-collapse').collapse('hide');
        this.getElement('config-select-collapse').collapse('hide');
        this.getElement('config-create-collapse').collapse('hide');
        this.getElement('config-columns-collapse').collapse('show');
    }

    personalizeSelect() {
        this.getElement('config-select-form').submit();
    }

    personalizeSortColumnsInit() {
        let items;

        items = this.getElement('config-form-columns-hide').find('li');
        this.personalizeSortColumnsInitReset(items);
        items.find('.list-action-show').show();

        items = this.getElement('config-form-columns-show').find('li');
        this.personalizeSortColumnsInitReset(items);
        items.find('.list-action-hide').show();
        items.find('.list-action-sort').show();

        items = items.slice(0, -1);
        items.on('dragover', $.proxy(this.personalizeSortColumnsDragOver, this));
        items.on('drop',     $.proxy(this.personalizeSortColumnsDrop, this));
        items = items.slice(1);
        items.on('drag',     $.proxy(this.personalizeSortColumnsDrag, this));
        items.on('dragend',  $.proxy(this.personalizeSortColumnsDrop, this));
        items.attr('draggable', true);
        items.css('cursor', 'move');

        this.personalizeSortColumnsStart();
        this.personalizeSortColumnsStyle();
    }

    personalizeSortColumnsInitReset(items) {
        items.attr('draggable', false);
        items.css('cursor', '');
        items.off();
        items.find('.list-action-sort').off().hide();
        items.find('.list-action-show').off().hide().on('click', $.proxy(this.personalizeSortColumnsShow, this));
        items.find('.list-action-hide').off().hide().on('click', $.proxy(this.personalizeSortColumnsHide, this));
    }

    personalizeSortColumnsShow(event) {
        event.preventDefault();

        let item = $(event.target).closest('li');
        item.find('.list-action-show').hide();
        item.find('.list-action-hide').show();
        item.find('.list-action-sort').show();

        this.getElement('config-form-columns-show').find('li.list-fake-row').before(item);

        this.personalizeSortColumnsInit();
    }

    personalizeSortColumnsHide(event) {
        event.preventDefault();

        let item = $(event.target).closest('li');
        item.find('.list-action-show').show();
        item.find('.list-action-hide').hide();
        item.find('.list-action-sort').hide();

        this.getElement('config-form-columns-hide').append(item);

        this.personalizeSortColumnsInit();
    }

    personalizeSortColumnsDrag(event) {
        this.personalizeSortColumnsStart();
        this.personalizeSorting.dragging = $(event.target).closest('li');
    }

    personalizeSortColumnsDragOver(event) {
        event.preventDefault();

        this.personalizeSortColumnsStyle();
        if (!this.personalizeSorting.dragging) {
            return;
        }

        let draggedOver = $(event.target).closest('li');
        let posMiddle = parseInt(draggedOver.offset().top + 0.5 * draggedOver.height() + 10);
        let posCurrent = event.pageY;

        if (posCurrent < posMiddle && draggedOver.prev('li')) {
            draggedOver = draggedOver.prev('li');
        }

        this.personalizeSorting.draggedOver = draggedOver;
        this.personalizeSorting.draggedOver.addClass('border-primary');
        this.personalizeSorting.dragging.addClass('list-group-item-secondary');
    }

    personalizeSortColumnsDrop(event) {
        if (this.personalizeSorting.dragging && this.personalizeSorting.draggedOver) {
            this.personalizeSorting.draggedOver.after(this.personalizeSorting.dragging);
        }

        this.personalizeSortColumnsStyle();
        this.personalizeSortColumnsStart();
    }

    personalizeSortColumnsStyle() {
        let items;

        items = this.getElement('config-form-columns-show').find('li');
        items.removeClass('border-primary');
        items.removeClass('list-group-item-secondary');

        items = this.getElement('config-form-columns-hide').find('li');
        items.removeClass('border-primary');
        items.removeClass('list-group-item-secondary');
    }

    personalizeSortColumnsStart() {
        this.personalizeSorting = {
            'dragging': null,
            'draggedOver': null
        };
    }

    filtersReset() {
        let form = this.getElement('filter-form');

        form.find('input').val('');
        form.find('select').val('');
        form.submit();
    }

    massActionsInit() {
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

    massActionsUpdate() {
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

    checkChange(target) {
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

        this.massActionsUpdate();
    };

    checkAllChange(target) {
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

        that.massActionsUpdate();
    };

    actionSelected(target) {
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
}

window.documentReady.add(function () {
    $("span[data-grid-role=total-rows]").each(
        function () {
            new SpipuUiGrid($(this).data('grid-code'));
        }
    )
});
