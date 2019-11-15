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
            'tree_diagram' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_level_name', '{{%level}}', 'name');
        $this->addForeignKey("level_tree_diagram_fk", "{{%level}}", "tree_diagram",
            "{{%tree_diagram}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%level}}');
    }
}