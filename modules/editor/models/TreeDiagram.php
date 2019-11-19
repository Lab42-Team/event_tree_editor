<?php

namespace app\modules\editor\models;

use Yii;
use app\modules\main\models\User;


/**
 * This is the model class for table "{{%tree_diagram}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $description
 * @property int $type
 * @property int $status
 * @property int $author
 *
 * @property Level[] $levels
 * @property Node[] $nodes
 * @property Sequence[] $sequences
 * @property User $author0
 */
class TreeDiagram extends \yii\db\ActiveRecord
{
    const EVENT_TREE_TYPE = 0;   // Тип диаграммы дерево событий
    const FAULT_TREE_TYPE = 1; // Тип диаграммы дерево отказов

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%tree_diagram}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'author'], 'required'],
            [['type', 'status', 'author'], 'default', 'value' => null],
            [['type', 'status', 'author'], 'integer'],

            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 600],

            [['author'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(),
                'targetAttribute' => ['author' => 'id']],

        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'TREE_DIAGRAM_MODEL_ID'),
            'created_at' => Yii::t('app', 'TREE_DIAGRAM_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'TREE_DIAGRAM_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'TREE_DIAGRAM_MODEL_NAME'),
            'description' => Yii::t('app', 'TREE_DIAGRAM_MODEL_DESCRIPTION'),
            'type' => Yii::t('app', 'TREE_DIAGRAM_MODEL_TYPE'),
            'status' => Yii::t('app', 'TREE_DIAGRAM_MODEL_STATUS'),
            'author' => Yii::t('app', 'TREE_DIAGRAM_MODEL_AUTHOR'),
        ];
    }

    /**
     * Получение списка типов диаграмм.
     * @return array - массив всех возможных типов диаграмм
     */
    public static function getTypesArray()
    {
        return [
            self::EVENT_TREE_TYPE => Yii::t('app', 'TREE_DIAGRAM_MODEL_EVENT_TREE_TYPE'),
            self::FAULT_TREE_TYPE => Yii::t('app', 'TREE_DIAGRAM_MODEL_FAULT_TREE_TYPE'),
        ];
    }

    /**
     * Получение названия типа диаграмм.
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

}
