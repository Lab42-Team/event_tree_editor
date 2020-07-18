<?php

use yii\db\Migration;

/**
 * Class m191115_115223_node
 */
class m191115_115223_node extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%node}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'operator' => $this->smallInteger()->notNull()->defaultValue(0),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'parent_node' => $this->integer(),
            'tree_diagram' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_node_name', '{{%node}}', 'name');

        $this->addForeignKey("node_parent_node_fk", "{{%node}}", "parent_node",
            "{{%node}}", "id", 'CASCADE');
        $this->addForeignKey("node_tree_diagram_fk", "{{%node}}", "tree_diagram",
            "{{%tree_diagram}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%node}}');
    }
}