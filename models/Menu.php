<?php

namespace app\modules\menu\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\DbDependency;
use yii\easyii\behaviors\CacheFlush;
use yii\easyii\behaviors\SortableModel;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $type
 * @property string $label
 * @property string $title
 * @property string $language
 * @property string $slug
 * @property string $link
 * @property string $preg
 * @property string $target
 * @property string $css_class
 * @property string $icon_class
 * @property string $element_id
 * @property integer $status
 * @property integer $sort
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Menu $parent
 * @property Menu[] $children
 */
class Menu extends \yii\easyii\components\ActiveRecord
{
    const TYPE_MENU = 0;
    const TYPE_LINK = 1;
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 10;

    const STATUS_OFF = 0;
    const STATUS_ON = 1;
    const CACHE_KEY = 'menu';

    /**
     * @var string Key name for caching menu structure
     */
    public static $cacheKey = 'MENU';
    /**
     * @var int Menu structure cache duration
     */
    public static $cacheDuration = 3600;
    /**
     * @var \yii\caching\Dependency
     */
    public static $cacheDependencyQuery = 'SELECT MAX([[updated_at]]) FROM {{%menu}}';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
            CacheFlush::className(),
            SortableModel::className(),
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'parent_id',
                    'type',
                    'status',
                    'sort',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by'
                ],
                'integer'
            ],
            [['label'], 'required'],
            [['label', 'title', 'link', 'preg', 'slug'], 'string', 'max' => 150],
            [['target'], 'string', 'max' => 50],
            [['css_class', 'icon_class', 'element_id'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10],
            [['title', 'link', 'language', 'css_class', 'icon_class', 'element_id', 'slug'], 'default', 'value' => null]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('easyii/menu','ID'),
            'parent_id' => Yii::t('easyii/menu','Parent ID'),
            'type' => Yii::t('easyii/menu','Type'),
            'slug' => Yii::t('easyii/menu','Identifier for templates'),
            'label' => Yii::t('easyii/menu','Label'),
            'title' => Yii::t('easyii/menu','Title attribute'),
            'language' => Yii::t('easyii/menu','Language'),
            'link' => Yii::t('easyii/menu','Link'),
            'preg' => Yii::t('easyii/menu','Preg'),
            'target' => Yii::t('easyii/menu','Target'),
            'css_class' => Yii::t('easyii/menu','CSS Class'),
            'icon_class' => Yii::t('easyii/menu','Icon Class'),
            'element_id' => Yii::t('easyii/menu','Element ID'),
            'status' => Yii::t('easyii/menu','status'),
            'sort' => Yii::t('easyii/menu','Sort'),
            'created_at' => Yii::t('easyii/menu','Created At'),
            'created_by' => Yii::t('easyii/menu','Created By'),
            'updated_at' => Yii::t('easyii/menu','Updated At'),
            'updated_by' => Yii::t('easyii/menu','Updated By'),
        ];
    }
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['newItem'] = $scenarios['default'];
        $scenarios['updateMenuItems'] = $scenarios['default'];
        return $scenarios;
    }
    public function formName()
    {
        if ($this->scenario == 'newItem') {
            return 'MenuNewItem';
        } elseif ($this->scenario == 'updateMenuItems') {
            return 'menu-items[' . $this->id . ']';
        } else {
            return parent::formName();
        }
    }
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if ($this->type === null) {
            $this->type = self::TYPE_LINK;
        }
        return parent::beforeValidate();
    }
    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert && $this->sort === null) {
            $this->sort = Menu::find()->where(['parent_id' => $this->parent_id])->max('sort') + 1;
        }
        return parent::beforeSave($insert);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Menu::className(), ['id' => 'parent_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Menu::className(), ['parent_id' => 'id'])->orderBy([
            'sort' => SORT_ASC,
            'id' => SORT_ASC
        ])->with(['children']);
    }
    /**
     * @return \array[]
     */
    public function getItems()
    {
        return static::getItemsByParentId($this->id);
    }
    /**
     * Status sting representation
     * @return string
     */
    public function getStatusDescription()
    {
        static $list = null;
        if ($list === null) {
            $list = static::getStatusList();
        }
        return (isset($list[$this->status])) ? $list[$this->status] : $this->status;
    }
    /**
     * List of all possible statuses
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => Yii::t('Draft'),
            self::STATUS_ACTIVE => Yii::t('Active'),
        ];
    }
    /**
     * @return array|static[]
     */
    public static function findMenus()
    {
        return static::find()->where(['type' => self::TYPE_MENU])->orderBy(['sort' => SORT_ASC])->indexBy('id')->all();
    }
    /**
     * Returns array tree from parentTitle (without itself).
     * Children are in children field.
     * @param int $parentId
     * @param int|bool $cacheDuration
     * @return null|array
     */
    public static function getItemsByParentId($parentId = 0, $cacheDuration = null)
    {
        $cacheDuration = $cacheDuration !== null ? $cacheDuration : static::$cacheDuration;
        $items = Yii::$app->cache->get(static::$cacheKey);
        //$items = null;
        if (true || !$items || $cacheDuration === false) {
            $items = static::find()->
            orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])->
            indexBy('id')->
            asArray()->
            all();
            $items[0] = ['items' => [], 'level' => -1];
            array_walk($items, function(&$item, $id) use (&$items) {
                if (isset($item['id'])) {
                    if ($item['parent_id'] === null) {
                        $item['parent_id'] = 0;
                    }
                    $item['url'] = $item['link'];
                    $item['options'] = [];
                    $item['items'] = [];
                    if (!empty($item['css_class'])) {
                        $item['options']['class'] = $item['css_class'];
                    }
                    if (!empty($item['element_id'])) {
                        $item['options']['id'] = $item['element_id'];
                    }
                }
            });
            foreach ($items as $id=>$item) {
                if (!isset($item['id'])) {
                    continue;
                }
                if (isset($items[$item['parent_id']])) {
                    $parent = &$items[$item['parent_id']];
                    if (!isset($parent['items'])) {
                        $parent['items'] = [];
                    }
                    $parent['items'][$id] = &$items[$id];
                }
            }
            if ($items && $cacheDuration !== false) {
                $dependency = static::$cacheDependencyQuery ?
                    new DbDependency(['sql' => static::$cacheDependencyQuery]) :
                    null;
                Yii::$app->cache->set(static::$cacheKey, $items, $cacheDuration, $dependency);
            } else {
                Yii::$app->cache->delete(static::$cacheKey);
            }
        }
        if (isset($items[$parentId])) {
            return $items[$parentId]['items'];
        } else {
            return null;
        }
    }
    /**
     * Returns array tree from parentTitle (without itself).
     * Children are in children field.
     *
     * @param string $slug
     * @param string $language
     * @return null|static
     */
    public static function findBySlug($slug, $language = null)
    {
        $menuQuery = static::find()->where(['slug' => $slug]);
        if ($language === null) {
            $language = Yii::$app->language;
        }
        $menuQuery->andWhere(['or', ['language' => $language], ['language' => null]]);
        $menu = $menuQuery->limit(1)->one();
        return $menu;
    }
    /**
     * @param int $parentId
     * @param int $maxLevel
     * @param string $levelDelimiter
     * @return array
     */
    public static function getList($parentId = 0, $maxLevel = null, $levelDelimiter = '- ')
    {
        $tree = static::getItemsByParentId($parentId);
        if (!$tree) {
            return [];
        }
        $res = [];
        $func = function ($items, $level = 0) use (&$res, &$func, &$levelDelimiter, &$maxLevel) {
            foreach ($items as $item) {
                $res[$item['id']] = str_repeat($levelDelimiter, $level) . $item['label'];
                if (isset($item['items']) && !empty($item['items'])) {
                    if ($maxLevel === null || $level < $maxLevel) {
                        $func($item['items'], $level + 1);
                    }
                }
            }
        };
        $func($tree);
        return $res;
    }
}
