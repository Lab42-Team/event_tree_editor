<?php

namespace app\modules\editor\models;

use Yii;

/**
 * This is the model class for table "{{%level}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $description
 * @property int $tree_diagram
 *
 * @property TreeDiagram $treeDiagram
 * @property Sequence[] $sequences
 */
class Level extends \yii\db\ActiveRecord
{
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%level}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'tree_diagram'], 'required'],
            [['tree_diagram'], 'default', 'value' => null],
            [['tree_diagram'], 'integer'],

            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 600],

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
            'id' => Yii::t('app', 'LEVEL_MODEL_ID'),
            'created_at' => Yii::t('app', 'LEVEL_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'LEVEL_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'LEVEL_MODEL_NAME'),
            'description' => Yii::t('app', 'LEVEL_MODEL_DESCRIPTION'),
            'tree_diagram' => Yii::t('app', 'LEVEL_MODEL_TREE_DIAGRAM'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery

    public function getTreeDiagram()
    {
        return $this->hasOne(TreeDiagram::className(), ['id' => 'tree_diagram']);
    }

    /**
     * @return \yii\db\ActiveQuery
     *
    public function getSequences()
    {
        return $this->hasMany(Sequence::className(), ['level' => 'id']);
    }
    */
}
