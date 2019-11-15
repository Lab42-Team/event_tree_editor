<?php

use yii\db\Migration;

/**
 * Class m191115_111808_level
 */
class m191115_111808_level extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%level}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
        ], $tableOptions);

        $this->createIndex('idx_level_name', '{{%level}}', 'name');
    }

    public function down()
    {
        $this->dropTable('{{%level}}');
    }
}