<?php

namespace app\modules\menu\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\easyii\actions\ChangeStatusAction;
use yii\easyii\actions\DeleteAction;
use yii\easyii\actions\SortByNumAction;
use yii\widgets\ActiveForm;
use yii\easyii\components\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\modules\menu\models\Menu;


class AController extends Controller
{
    public $modelClass = 'app\modules\menu\models\Menu';

    public function actions()
    {
        return [
            'delete' => [
                'class' => DeleteAction::className(),
                'successMessage' => Yii::t('easyii/menu', 'Carousel item deleted')
            ],
            'up' => SortByNumAction::className(),
            'down' => SortByNumAction::className(),
            'on' => ChangeStatusAction::className(),
            'off' => ChangeStatusAction::className(),
        ];
    }

    public function actionIndex($id = null)
    {
        $menus = Menu::findMenus();
        /**
         * @var Menu $menu
         */
        $menu = null;
        if ($id === null) {
            if (count($menus) > 0) {
                reset($menus);
                $id = key($menus);
                $menu = $menus[$id];
            }
        } elseif ($id) {
            if (isset($menus[$id])) {
                $menu = $menus[$id];
            } else {
                throw new NotFoundHttpException(Yii::t('easyii/menu', 'Menu not found.'));
            }
        }
        //New menu
        if (!$menu) {
            $menu = new Menu();
            $menu->type = Menu::TYPE_MENU;
            $menu->status = Menu::STATUS_DRAFT;
        }
        //New item
        $newItem = new Menu();
        $newItem->scenario = 'newItem';
        $newItem->status = Menu::STATUS_DRAFT;
        if (Yii::$app->request->isPost) {
            if (!$menu->isNewRecord) {
                //New item
                if ($newItem->load(Yii::$app->request->post())) {
                    $newItem->parent_id = $menu->id;
                    $newItem->language = $menu->language;
                    if ($newItem->save()) {
                        if (Yii::$app->request->isAjax) {
                            return $this->renderPartial('_item', ['item' => $newItem]);
                        } else {
                            return $this->refresh();
                        }
                    }
                }
                //Items
                $itemsSort = @array_flip(Yii::$app->request->post('items_sort'));
                $itemsDelete = Yii::$app->request->post('items_delete');
                $items = Yii::$app->request->post('menu-items');
                if ($items) {
                    foreach ($items as $id => $itemArray) {
                        /** @var Menu $item */
                        $item = Menu::findOne($id);
                        if (!$item) {
                            continue;
                        }
                        if (isset($itemsDelete[$id]) && $itemsDelete[$id]) {
                            $item->delete();
                            continue;
                        }
                        $item->scenario = 'updateMenuItems';
                        $item->setAttributes($itemArray);
                        $item->language = $menu->language;
                        $item->sort = @$itemsSort[$item->id] + 1;
                        $item->status = Menu::STATUS_ACTIVE;
                        $item->save();
                    }
                }
            }
            if ($menu->load(Yii::$app->request->post()) && $menu->save()) {
                Yii::$app->session->addFlash('success', Yii::t('easyii/menu', 'Menu successfully saved.'));
                return $this->redirect(['', 'id' => $menu->id]);
            }
        }
        return $this->render('index', [
            'menus' => $menus,
            'menu' => $menu,
            'newItem' => $newItem,
        ]);
    }

    public function actionDelete($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            $model->delete();
        } else {
            throw new NotFoundHttpException(Yii::t('easyii/menu', 'The requested menu does not exist.'));
        }
    }

}
