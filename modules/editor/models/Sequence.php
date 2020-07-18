<?php

namespace app\modules\editor\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sequence}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $tree_diagram
 * @property int $level
 * @property int $node
 * @property int $priority
 *
 * @property Level $level0
 * @property Node $node0
 * @property TreeDiagram $treeDiagram
 */
class Sequence extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%sequence}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['tree_diagram', 'level', 'node', 'priority'], 'required'],
            [['tree_diagram', 'level', 'node', 'priority'], 'default', 'value' => null],
            [['tree_diagram', 'level', 'node', 'priority'], 'integer'],

            [['level'], 'exist', 'skipOnError' => true, 'targetClass' => Level::className(),
                'targetAttribute' => ['level' => 'id']],
            [['node'], 'exist', 'skipOnError' => true, 'targetClass' => Node::className(),
                'targetAttribute' => ['node' => 'id']],
            [['tree_diagram'], 'exist', 'skipOnError' => true, 'targetClass' => TreeDiagram::className(),
                'targetAttribute' => ['tree_diagram' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'SEQUENCE_MODEL_ID'),
            'created_at' => Yii::t('app', 'SEQUENCE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'SEQUENCE_MODEL_UPDATED_AT'),
            'tree_diagram' => Yii::t('app', 'SEQUENCE_MODEL_TREE_DIAGRAM'),
            'level' => Yii::t('app', 'SEQUENCE_MODEL_LEVEL'),
            'node' => Yii::t('app', 'SEQUENCE_MODEL_NODE'),
            'priority' => Yii::t('app', 'SEQUENCE_MODEL_PRIORITY'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }



    /**
     * @return \yii\db\ActiveQuery

    public function getLevel0()
    {
        return $this->hasOne(Level::className(), ['id' => 'level']);
    }

    /**
     * @return \yii\db\ActiveQuery

    public function getNode0()
    {
        return $this->hasOne(Node::className(), ['id' => 'node']);
    }

    /**
     * @return \yii\db\ActiveQuery

    public function getTreeDiagram()
    {
        return $this->hasOne(TreeDiagram::className(), ['id' => 'tree_diagram']);
    }
     * */

}
