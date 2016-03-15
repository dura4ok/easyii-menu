<?php
/* @var yii\web\View $this */
use app\modules\menu\models\Menu;
use yii\helpers\Html;
/* @var array|maddoger\website\common\models\Menu $item */
switch ($item->type) {
    case Menu::TYPE_LINK:
        $itemTypeLabel = Yii::t('easyii/menu', 'Link');
        break;
    default:
        $itemTypeLabel = Yii::t('easyii/menu', 'Custom');
}
$item->scenario = 'updateMenuItems';
?>
<li id="menu-items-<?= $item->id ?>" data-id="<?= $item->id ?>">
    <div class="panel panel-solid panel-default">
        <div class="panel-heading collapsed" role="button" data-toggle="collapse" data-target="#menu-items-collapse-<?= $item->id ?>" aria-expanded="false" aria-controls="menu-items-collapse-<?= $item->id ?>" title="Кликните, чтобы открыть">
            <div class="panel-tools pull-right">
                <?= $itemTypeLabel ?>
            </div>
            <div class="panel-title">
                <?= $item->label ?>
            </div>
        </div>
        <div id="menu-items-collapse-<?= $item->id ?>" class="panel-collapse collapse" aria-labelledby="menu-items-collapse-<?= $item->id ?>">
            <div class="panel-body">
                <?php
                //$fieldPrefix = 'menu-items['.$itemModel->id.']';
                //$idPrefix = 'menu-items-'.$itemModel->id;
                echo Html::hiddenInput('items_sort[]', $item->id);
                echo Html::hiddenInput('items_delete['.$item->id.']', 0, ['class' => 'delete-field']);
                echo Html::activeHiddenInput($item, 'parent_id');
                ?>
                <div class="form-group form-group-sm">
                    <?= Html::activeLabel($item, 'link', ['class' => 'control-label']) ?>
                    <?= Html::activeTextInput($item, 'link', ['class' => 'form-control']) ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'label', ['class' => 'control-label']) ?>
                            <?= Html::activeTextInput($item, 'label', ['class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'title', ['class' => 'control-label']) ?>
                            <?= Html::activeTextInput($item, 'title', ['class' => 'form-control']) ?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'target', ['class' => 'control-label']) ?>
                            <?= Html::activeDropDownList($item, 'target',
                                [
                                    '' => Yii::t('easyii/menu', 'Current window/tab'),
                                    '_blank' => Yii::t('easyii/menu', 'New window/tab'),
                                    '_top' => Yii::t('easyii/menu', 'Top window/tab'),
                                ],
                                ['class' => 'form-control']) ?>
                        </div>
                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'icon_class', ['class' => 'control-label']) ?>
                            <?= Html::activeTextInput($item, 'icon_class', ['class' => 'form-control']) ?>
                            <div class="hint-block"><?= Yii::t('easyii/menu', 'For example: <code>fa fa-home</code> is <i class="fa fa-home"></i>') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">

                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'element_id', ['class' => 'control-label']) ?>
                            <?= Html::activeTextInput($item, 'element_id', ['class' => 'form-control']) ?>
                        </div>
                        <div class="form-group form-group-sm">
                            <?= Html::activeLabel($item, 'css_class', ['class' => 'control-label']) ?>
                            <?= Html::activeTextInput($item, 'css_class', ['class' => 'form-control']) ?>
                            <div class="hint-block"><?= Yii::t('easyii/menu', 'Class name for li element.') ?></div>
                        </div>
                    </div>
                </div>
                <div class="form-group form-group-sm">
                    <?= Html::activeLabel($item, 'preg', ['class' => 'control-label']) ?>
                    <?= Html::activeTextInput($item, 'preg', ['class' => 'form-control']) ?>
                    <div class="hint-block"><?= Yii::t('easyii/menu', 'Custom activity regular expression.') ?></div>
                </div>
                <hr />
                <button type="button" class="btn btn-danger btn-sm pull-right" data-tree-action="delete"><?= Yii::t('easyii/menu', 'Delete') ?></button>
                <span class="btn-group clearfix">
                    <button class="btn btn-primary btn-sm" type="button" data-tree-action="up"><i class="fa fa-arrow-up"></i></button>
                    <button class="btn btn-primary btn-sm" type="button" data-tree-action="down"><i class="fa fa-arrow-down"></i></button>
                    <button class="btn btn-primary btn-sm" type="button" data-tree-action="right"><i class="fa fa-arrow-right"></i></button>
                    <button class="btn btn-primary btn-sm" type="button" data-tree-action="left"><i class="fa fa-arrow-left"></i></button>
                </span>
            </div>
        </div>
    </div>
    <ol>
        <?php
        if (isset($item->children) && !empty($item->children)) {
            foreach ($item->children as $child) {
                echo $this->render('_item', ['item' => $child]);
            }
        }
        ?>
    </ol>
</li>