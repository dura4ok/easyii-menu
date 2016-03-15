<?php
/* @var yii\web\View $this */
/* @var array[] $items */
/* @var maddoger\website\common\models\Menu $menu */
$this->registerJsFile('/theme/js/jquery-ui.min.js');
$this->registerJsFile('/theme/js/jquery.mjs.nestedSortable.js');

$this->registerJs('$("#menu-items-editor > ol").nestedSortable({
    forcePlaceholderSize: true,
    handle: "div.panel-heading",
    helper:	"clone",
    items: "li",
    opacity: .6,
    placeholder: "placeholder",
    revert: 250,
    tabSize: 25,
    tolerance: "pointer",
    toleranceElement: "> div",
    isTree: true,
    expandOnHover: 700,
    startCollapsed: false,
    update: function(event, ui){
        var item = ui.item;
        var id = item.prop("id");
        var field = $("#"+id+"-parent_id");
        var parent = item.parent().parent().eq(0);
        field.val(parent.is("li") ? parent.data("id") : '.$menu->id.');
    }
});
    $("#menu-items-editor").on("click", "[data-tree-action=\"delete\"]", function(){
        if (confirm("'.Yii::t('easyii/menu', 'Are you sure want to delete this item?').'")) {
            var t = $(this);
            var item = t.closest("li");
            item.find(".delete-field").val(1);
            //item.hide();
            var newContainer = $("<div />");
            newContainer.hide();
            newContainer.append(item.children());
            $("#menu-items-editor").after(newContainer);
            item.remove();
        }
        return false;
    }).on("click", "[data-tree-action=\"up\"]", function(){
        var t = $(this);
        var item = t.closest("li");
        var prev = item.prev();
        if (prev.length>0) {
            item.insertBefore(prev);
        }
        return false;
    }).on("click", "[data-tree-action=\"down\"]", function(){
        var t = $(this);
        var item = t.closest("li");
        var next = item.next();
        if (next.length>0) {
            item.insertAfter(next);
        }
        return false;
    }).on("click", "[data-tree-action=\"right\"]", function(){
        var t = $(this);
        var item = t.closest("li");
        var prev = item.prev();
        if (prev.length>0) {
            if (prev.find("ol").length == 0) {
                prev.append("<ol></ol>");
            }
            prev.find("ol").append(item);
        }
        return false;
    }).on("click", "[data-tree-action=\"left\"]", function(){
        var t = $(this);
        var item = t.closest("li");
        var prev = item.closest("ol").parent();
        if (prev.is("li")) {
            item.insertAfter(prev);
        }
        return false;
    });
');
?>
<div id="menu-items-editor">
    <?php
    echo '<ol>';
    if ($items) {
        foreach ($items as $item) {
            echo $this->render('_item', ['item' => $item]);
        }
    }
    echo '</ol>';
    ?>
</div>