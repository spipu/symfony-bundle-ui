// spipu-checkbox-tree.js

class SpipuUiCheckboxTree {
    constructor(mainNode) {
        this.mainNode = mainNode
        this.code = mainNode.data('tree-code');
        this.init();
    }

    init () {
        let that = this;

        let inputs = this.mainNode.find('input[type=checkbox],input[type=radio]');

        inputs.on('change', function () { that.change($(this)); });
        inputs.each(function() { that.toggleChildren($(this)); });
    }

    change(node) {
        this.toggleChildren(node);
    }

    toggleChildren(node) {
        let selector = this.getFlatChildrenSelector(node);
        let form = node.closest('form');
        this.toogleUselessNode(form.find('input'), false);
        this.toogleUselessNode(form.find(selector), true)
        this.toogleUselessNode(form.find('input:checked:not(:indeterminate) ~ ul > li > input'), true);
    }

    getFlatChildrenSelector(node) {
        let selectors = [], list = [];
        node.closest('form').find('input[data-children]:checked').each(function (index, checkbox) {
            let flat = $(checkbox).attr('data-children');
            if (!flat || flat.length === 0) {
                return;
            }
            list = [...list, ...flat.split(',')];
        });
        for (let i in list) {
            selectors.push('input[value="' + list[i] + '"]');
        }
        return selectors.join(',');
    }

    toogleUselessNode(node, checked) {
        node.prop('indeterminate', checked)
            .prop('disabled', checked)
            .each(function (index, checkbox) {
                $(checkbox).closest('li').find('ul input')
                    .prop('disabled', checked)
                    .prop('indeterminate', checked);
            })
    }
}

window.documentReady.add(function () {
    $("ul.checkbox-tree").each(
        function () {
            new SpipuUiCheckboxTree($(this));
        }
    )
});
