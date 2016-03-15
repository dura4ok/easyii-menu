<?php
/**
 * @copyright Copyright (c) 2016 Vitaliy Syrchikov
 * @link http://syrchikov.name
 */

namespace app\modules\menu\widgets;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\menu\models\Menu;
use yii\widgets\Menu as BaseMenu;
//use app\modules\menu\widgets\Menu as BaseMenu;


class MenuWidget extends BaseMenu
{
    /**
     * @var \maddoger\website\common\models\Menu for items. You can use Menu object, slug or id.
     */
    public $menu;

    /**
     * @var int minimal menu level for output
     */
    public $minLevel;

    /**
     * @var int maximal menu level for output
     */
    public $maxLevel;

    /**
     * @var string
     */
    public $menuModelClass = 'app\modules\menu\models\Menu';

    /**
     * @inheritdoc
     */
    public $linkTemplate = '<a href="{url}"{target}{title}><span>{icon}{label}</span></a>';

    /**
     * @inheritdoc
     */
    public $labelTemplate = '<span{title}>{icon}{label}</span>';

    /**
     * @var string
     */
    public $iconTemplate = '<i class="{icon}"></i>&nbsp;';

    /**
     * @inheritdoc
     */
    public $submenuTemplate = "\n<ul>\n{items}\n</ul>\n";

    /**
     * @var string
     */
    public $submenuItemClass;

    /**
     * @inheritdoc
     */
    public $activateParents = true;

    /**
     * @var array items before menu items
     * @see \yii\widgets\Menu::$items
     */
    public $beforeItems;

    /**
     * @var array items after menu items
     * @see \yii\widgets\Menu::$items
     */
    public $afterItems;

    /**
     * @var string key name for saving active items to view's params
     */
    public $saveActiveItemsToParam;

    /**
     * @var bool
     */
    public $saveActiveItemsAfterLevelFilter = true;

     /**
     * @var bool
     */
    public $activeCheck = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->items) {

            if (!$this->menu) {
                throw new InvalidParamException('Items or Menu property must be set.');
            }

            $class = $this->menuModelClass;
            if (is_int($this->menu)) {
                $this->menu = $class::findOne($this->menu);
            } elseif (is_string($this->menu)) {
                $this->menu = $class::findBySlug($this->menu);
            }

            if ($this->menu instanceof $class) {
                //Get items
                $this->items = $this->menu->getItems();
                if ($this->beforeItems) {
                    $this->items = array_merge($this->beforeItems, $this->items);
                }
                if ($this->afterItems) {
                    $this->items = array_merge($this->items, $this->afterItems);
                }

                if (!empty($this->menu->element_id) && !isset($this->options['id'])) {
                    $this->options['id'] = $this->menu->element_id;
                }
                if (!empty($this->menu->css_class)) {
                    Html::addCssClass($this->options, $this->menu->css_class);
                }
            }
        }
    }

    /**
     * Renders the menu.
     */
    public function run()
    {
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = Yii::$app->request->getQueryParams();
        }
        $items = $this->normalizeItems($this->items, $hasActiveChild);

        if ($this->saveActiveItemsToParam && !$this->saveActiveItemsAfterLevelFilter) {
            $this->view->params[$this->saveActiveItemsToParam] = $this->filterActiveItems($items);
        }
        $items = $this->normalizeItemsByLevel($items);

        if ($this->saveActiveItemsToParam && $this->saveActiveItemsAfterLevelFilter) {
            $this->view->params[$this->saveActiveItemsToParam] = $this->filterActiveItems($items);
        }

        if (!empty($items)) {
            $options = $this->options;
            $tag = ArrayHelper::remove($options, 'tag', 'ul');

            if ($tag !== false) {
                echo Html::tag($tag, $this->renderItems($items), $options);
            } else {
                echo $this->renderItems($items);
            }
        }
    }

    /**
     * @param $items
     * @param int $level
     * @return array
     */
    protected function normalizeItemsByLevel($items, $level = 0)
    {
        if ($this->minLevel || $this->maxLevel) {
            $res = [];
            foreach ($items as $item) {
                if ($level >= $this->minLevel && $level <= $this->maxLevel) {
                    //Right level
                    if (!empty($item['items'])) {
                        $item['items'] = $this->normalizeItemsByLevel($item['items'], $level + 1);
                    }
                    $res[] = $item;
                } elseif ($level < $this->minLevel) {
                    //Below min. Add subitems from active items
                    if ($item['active'] && !empty($item['items'])) {
                        $res = $this->normalizeItemsByLevel($item['items'], $level + 1);
                    }
                } else {
                    //Above maximum
                    continue;
                }
            }
            return $res;
        } else {
            return $items;
        }
    }

    /**
     * @param $items
     * @return array
     */
    protected function filterActiveItems($items)
    {
        $res = array_filter($items, function ($a) {
            return $a['active'];
        });
        foreach ($res as $k=>$a) {
            if (!empty($a['items'])) {
                $res[$k]['items'] = $this->filterActiveItems($a['items']);
            }
        }
        return array_values($res);
    }

    /**
     * @inheritdoc
     */
    protected function normalizeItems($items, &$active)
    {
        foreach ($items as $i => $item) {

            if (isset($item['roles'])) {
                $item['visible'] = false;
                foreach ($item['roles'] as $role) {
                    if (Yii::$app->user->can($role)) {
                        $item['visible'] = true;
                        break;
                    }
                }
            }
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (!isset($item['label'])) {
                $item['label'] = '';
            }
            if (!isset($items[$i]['options'])) {
                $items[$i]['options'] = [];
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $items[$i]['label'] = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $hasActiveChild = false;
            if (isset($item['items'])) {
                $items[$i]['items'] = $this->normalizeItems($item['items'], $hasActiveChild);
                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
                if ($this->submenuItemClass) {
                    Html::addCssClass($items[$i]['options'], $this->submenuItemClass);
                }
            }
            if (!isset($item['active'])) {
                if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item)) {
                    $active = $items[$i]['active'] = true;
                } else {
                    $items[$i]['active'] = false;
                }
            } elseif ($item['active']) {
                $active = true;
            }
        }

        return array_values($items);
    }


    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {
        $icon = ArrayHelper::getValue($item, 'icon_class');
        if ($icon) {
            $icon = '<i class="' . $icon . '"></i>&nbsp;';
        }

        $template = isset($item['url']) ?
            ArrayHelper::getValue($item, 'template', $this->linkTemplate) :
            ArrayHelper::getValue($item, 'template', $this->labelTemplate);

        return strtr($template, [
            '{url}' => isset($item['url']) ? Url::to($item['url']) : null,
            '{icon}' => $icon,
            '{label}' => $item['label'],
            '{target}' =>
                (isset($item['target']) && !empty($item['target'])) ?
                    ' target="' . Html::encode($item['target']) . '"' :
                    '',
            '{title}' =>
                (isset($item['title']) && !empty($item['title'])) ?
                    ' title="' . Html::encode($item['title']) . '"' :
                    '',

        ]);
    }


    /**
     * Checks whether a menu item is active.
     * This is done by checking if [[route]] and [[params]] match that specified in the `url` option of the menu item.
     * When the `url` option of a menu item is specified in terms of an array, its first element is treated
     * as the route for the item and the rest of the elements are the associated parameters.
     * Only when its route and parameters match [[route]] and [[params]], respectively, will a menu item
     * be considered active.
     * @param array $item the menu item to be checked
     * @return boolean whether the menu item is active
     */
    protected function isItemActive($item)
    {

        if ($activeCheck === true ) 
        {
           $res = parent::isItemActive($item);
            if (!$res) {

                $preg = null;
                if (isset($item['preg']) && !empty($item['preg'])) {
                    $preg = $item['preg'];
                    //$preg = '/^' . str_replace('*', '(.*?)', str_replace('/', '\/', $preg)) . '$/is';
                } elseif (isset($item['url']) && is_string($item['url'])) {
                    $preg = $item['url'] . ($item['url'] != '/' ? '(/|\?)(.*?)' : '');
                }
                if (!empty($preg)) {
                    $preg = '`^' . $preg . '$`is';
                    $res = (preg_match($preg, Yii::$app->request->url) || preg_match($preg, Yii::$app->request->url . '/'));
                }
            }
            return $res;
        }        
    }
}