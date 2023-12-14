// spipu-checkbox-tree.js

class SpipuUiCheckboxTree {
    constructor(mainNode) {
        this.mainNode = mainNode
        this.code = mainNode.data('tree-code');
        this.init();
    }

    init () {
        let that = this;

        let inputs = this.mainNode.find('input[type=checkbox]');

        inputs.on('change', function () { that.change($(this)); });
        inputs.each(function() { that.toggleChildren($(this)); });
    }

    change(node) {
        node.closest('li').find('ul li input[type=checkbox]').prop('checked', false);

        this.toggleChildren(node);

    }

    toggleChildren(node) {
        node.closest('li').find('ul').toggleClass('useless-node', node.prop('checked'));
    }
}

window.documentReady.add(function () {
    $("ul.checkbox-tree").each(
        function () {
            new SpipuUiCheckboxTree($(this));
        }
    )
});
