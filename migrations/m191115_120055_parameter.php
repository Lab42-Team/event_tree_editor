<?php

use yii\db\Migration;

/**
 * Class m191115_120055_parameter
 */
class m191115_120055_parameter extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%parameter}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string(),
            'operator' => $this->smallInteger()->notNull()->defaultValue(0),
            'value' => $this->string()->notNull(),
            'node' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_parameter_name', '{{%parameter}}', 'name');

        $this->addForeignKey("parameter_node_fk", "{{%parameter}}", "node", "{{%node}}", "id", 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%parameter}}');
    }
}