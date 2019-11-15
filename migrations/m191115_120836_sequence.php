<?php

use yii\db\Migration;

/**
 * Class m191115_120836_sequence
 */
class m191115_120836_sequence extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%sequence}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'tree_diagram' => $this->integer()->notNull(),
            'level' => $this->integer()->notNull(),
            'node' => $this->integer()->notNull(),
            'priority' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("sequence_tree_diagram_fk", "{{%sequence}}",
            "tree_diagram", "{{%tree_diagram}}", "id", 'CASCADE');
        $this->addForeignKey("sequence_level_fk", "{{%sequence}}", "level", "{{%level}}", "id", 'CASCADE');
        $this->addForeignKey("sequence_node_fk", "{{%sequence}}", "node", "{{%node}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%sequence}}');
    }
}