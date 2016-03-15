<?php

use yii\db\Migration;
use yii\db\Schema;

class m160313_061122_menu extends Migration
{
     public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%menu}}', [
            'id' => Schema::TYPE_PK,
            'parent_id' => Schema::TYPE_INTEGER,
            'type' => Schema::TYPE_SMALLINT,
            'label' => Schema::TYPE_STRING . '(150) NOT NULL',
            'title' => Schema::TYPE_STRING . '(150)',
            'language' => Schema::TYPE_STRING . '(10)',
            'slug' => Schema::TYPE_STRING . '(150)',
            'link' => Schema::TYPE_STRING . '(150)',
            'preg' => Schema::TYPE_STRING . '(150)',
            'target' => Schema::TYPE_STRING . '(50)',
            'css_class' => Schema::TYPE_STRING . '(255)',
            'icon_class' => Schema::TYPE_STRING . '(255)',
            'element_id' => Schema::TYPE_STRING . '(255)',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'sort' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_INTEGER,
            'created_by' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
            'updated_by' => Schema::TYPE_INTEGER,
        ], $tableOptions);
        $this->createIndex($this->db->tablePrefix . 'menu_slug_ix',
            '{{%menu}}', 'slug');
        $this->addForeignKey($this->db->tablePrefix . 'menu_parent_fk',
            '{{%menu}}', 'parent_id',
            '{{%menu}}', 'id',
            'CASCADE', 'CASCADE');
    }
    public function safeDown()
    {
        $this->dropForeignKey($this->db->tablePrefix . 'menu_parent_fk', '{{%menu}}');
        $this->dropIndex($this->db->tablePrefix . 'menu_slug_ix', '{{%menu}}');
        $this->dropTable('{{%menu}}');
    }
}
