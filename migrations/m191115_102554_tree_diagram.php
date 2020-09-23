<?php

use yii\db\Migration;

/**
 * Class m191115_102554_tree_diagram
 */
class m191115_102554_tree_diagram extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%tree_diagram}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'mode' => $this->smallInteger()->notNull()->defaultValue(0),
            //'correctness' => $this->smallInteger()->notNull()->defaultValue(0),
            //'tree_view' => $this->smallInteger()->notNull()->defaultValue(0),
            'author' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_tree_diagram_name', '{{%tree_diagram}}', 'name');
        $this->createIndex('idx_tree_diagram_type', '{{%tree_diagram}}', 'type');
        $this->createIndex('idx_tree_diagram_status', '{{%tree_diagram}}', 'status');

        $this->addForeignKey("tree_diagram_user_fk", "{{%tree_diagram}}", "author", "{{%user}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%tree_diagram}}');
    }
}