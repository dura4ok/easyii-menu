# Модуль меню для Easyii

 1. Модуль закинуть в папку app\modules
 2. Для включения виджета необходимо прописать в шаблоне 
 
 ```
 <?=MenuWidget::widget([
    'menu' => <slug меню или id>,
    'activateParents' => true,
    'labelTemplate' => '<a href="#">{icon}{label}</a>',
    'submenuTemplate' => '<ul class="menu">{items}</ul>',
    'submenuItemClass' => 'treeview',
    'activeCheck' => false,
]);
?>
```
