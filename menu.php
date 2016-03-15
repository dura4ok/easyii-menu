<?php

namespace app\modules\menu;

class menu extends \yii\easyii\components\Module
{

    public static $installConfig = [
        'title' => [
            'en' => 'Menu',
            'ru' => 'Меню',
        ],
        'icon' => 'menu-hamburger',
        'order_num' => 200,
    ];

}
