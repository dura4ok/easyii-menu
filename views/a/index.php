<?php
/* @var yii\web\View $this */
/* @var app\modules\menu\models\Menu[] $menus */
/* @var app\modules\menu\models\Menu[] $items */
/* @var app\modules\menu\models\Menu $menu */
/* @var app\modules\menu\models\Menu $newItem */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use app\modules\menu\models\Menu;

//BackendAsset::register($this);
$this->title = Yii::t('easyii/menu', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
$leavePageMessage = Yii::t('easyii/menu', 'Changes are not saved. Are you sure want to leave this page?');
$this->registerJs(
    <<<JS
    $('.ajax-add').click(function(){
        var form = $(this).closest('form');
        var data = form.serialize();
        var panel = form.closest('.panel');
        panel.find('.overlay, .loading-img').show();
        $.post(form.prop('action'), data, function(html){
            $('#menu-items-editor > ol').append(html);
            $("[data-widget='collapse']").collapse();
            form[0].reset();
            form.find('.select2-container').select2("val", "");
            panel.find('.overlay, .loading-img').hide();
            form.find('input[type="text"]').first().focus();
        });
        return false;
    });
    var onBeforeUploadBind = false;
    $('#menu-editor-main-container').on('change', '*', function(){
        if (!onBeforeUploadBind) {
            onBeforeUploadBind = true;
            window.onbeforeunload = function(e) {
              return '{$leavePageMessage}';
            };
        }
    }).find('form').submit(function(){
        window.onbeforeunload = null;
        onBeforeUploadBind = false;
    });
JS
);
?>
<div class="menu-editor">

    <div class="panel panel-default">
        <div class="panel-body">
            <?php
            if (count($menus) > 1) {
                echo Html::beginForm([''], 'get', ['class' => 'form-inline']);
                echo Yii::t('easyii/menu', 'Choose menu for editing'), ': ';
                echo Html::dropDownList('id', $menu->id, ArrayHelper::map($menus, 'id', 'label'),
                    ['id' => 'menu-id-select', 'class' => 'form-control input-sm']), '&nbsp;';
                echo Html::submitButton(Yii::t('easyii/menu', 'Choose'),
                    ['class' => 'btn btn-sm btn-default']), '&nbsp;';
                echo Yii::t('easyii/menu', ' or <a href="{url}">create a new menu</a>.',
                    ['url' => Url::to(['', 'id' => 0])]);
                echo Html::endForm();
            } else {
                echo Yii::t('easyii/menu', 'Update menu bellow');
                echo Yii::t('easyii/menu', ' or <a href="{url}">create a new menu</a>.',
                    ['url' => Url::to(['', 'id' => 0])]);
            }
            ?>
        </div>
    </div>

    <div class="row" id="menu-editor-main-container">

        <div class="col-md-8">
            <?php $form = ActiveForm::begin([
                //'layout' => 'horizontal',
            ]) ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-right">
                        <?= Html::submitButton($menu->isNewRecord ? Yii::t('easyii/menu',
                            'Create menu') : Yii::t('easyii/menu', 'Save menu'),
                            ['class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                    <?= Yii::t('easyii/menu', 'Menu name: ') ?> <?= Html::activeTextInput($menu, 'label',
                        ['required' => 'required', 'class' => 'form-control', 'style' => 'width: auto; display: inline-block;']) ?>
                </div>
                <div class="panel-body">
                    <?php
                    if ($menu->isNewRecord) {
                        echo Yii::t('easyii/menu', 'Give your menu a name above, then click Create menu.');
                    } else {
                        ?>
                        <h4><?php
                            echo Yii::t('easyii/menu', 'Menu structure');
                            if ($menu->getChildren()->andWhere(['status' => Menu::STATUS_DRAFT])->count() > 0) {
                                echo ' ', Html::tag('small', Yii::t('easyii/menu', 'unsaved changes'),
                                    ['class' => 'text-danger']);
                            } ?></h4>
                        <?= Yii::t('easyii/menu', 'Add menu items from the column on the left.') ?>
                        <br/>
                        <?= $this->render('_items',
                            ['menu' => $menu, 'items' => $menu->getChildren()->with(['children'])->all()]); ?>
                        <br/>
                        <hr/>
                        <h4><?= Yii::t('easyii/menu', 'Menu settings') ?></h4>
                        <?= $form->field($menu, 'slug',
                            ['options' => ['class' => 'form-group form-group-sm']])->textInput()->hint(Yii::t('easyii/menu',
                            'You insert the menu to a template using it.')) ?>

                        <?= $form->field($menu, 'css_class',
                            ['options' => ['class' => 'form-group form-group-sm']])->textInput()->hint(Yii::t('easyii/menu',
                            'The menu UL element will receive this class.')) ?>
                        <?= $form->field($menu, 'element_id',
                            ['options' => ['class' => 'form-group form-group-sm']])->textInput()->hint(Yii::t('easyii/menu',
                            'The menu UL element will receive this ID.')) ?>
                    <?php } ?>
                </div>
                <div class="panel-footer clearfix">
                    <?php if (!$menu->isNewRecord) {
                        echo Html::a(Yii::t('easyii/menu', 'Delete menu'), ['delete', 'id' => $menu->id], [
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('easyii/menu', 'Are you sure want to delete this menu?'),
                            'class' => 'text-danger'
                        ]);
                    } ?>
                    <div class="pull-right">
                        <?= Html::submitButton($menu->isNewRecord ? Yii::t('easyii/menu',
                            'Create menu') : Yii::t('easyii/menu', 'Save menu'),
                            ['class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
            </div>
            <?php $form->end() ?>
        </div>
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="panel-title"><?= Yii::t('easyii/menu', 'Links') ?></div>
                </div>
                <div class="panel-body">
                    <?php if ($menu->isNewRecord) {
                        echo Yii::t('easyii/menu', 'Create menu first.');
                    } else {
                        $customLinkForm = ActiveForm::begin([
                            'action' => '#',
                            'id' => 'link-form',
                        ]);
                        echo Html::activeHiddenInput($newItem, 'type', ['value' => Menu::TYPE_LINK]);
                        echo $customLinkForm->field($newItem,'link')
                            ->hint('Начинается с / для относительных ссылок и http:// для абсолютных.')
                            ->textInput(['value' => empty($newItem->link) ? '/' : $newItem->link]);
                        echo $customLinkForm->field($newItem, 'label')->textInput();
                        echo Html::submitButton(Yii::t('easyii/menu', 'Add to menu'),
                            ['class' => 'btn btn-default ajax-add']);
                        ActiveForm::end();
                    }
                    ?>
                </div>
                <div class="overlay" style="display: none;"></div>
                <div class="loading-img" style="display: none;"></div>
            </div>

        </div>
    </div>