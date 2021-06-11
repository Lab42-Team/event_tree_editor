<?php

namespace app\modules\editor\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%node}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $description
 * @property int $operator
 * @property int $type
 * @property int $level_id
 * @property string $comment
 * @property int $parent_node
 * @property int $indent_x
 * @property int $indent_y
 * @property int $tree_diagram
 *
 * @property Node $parentNode
 * @property Node[] $nodes
 * @property TreeDiagram $treeDiagram
 * @property Parameter[] $parameters
 * @property Sequence[] $sequences
 */
class Node extends \yii\db\ActiveRecord
{
    public $level_id;

    const NOT_OPERATOR = 0;   // Оператор
    const AND_OPERATOR = 1; // Оператор И
    const OR_OPERATOR = 2; // Оператор ИЛИ
    const XOR_OPERATOR = 3; // Оператор

    const INITIAL_EVENT_TYPE = 0;   // Тип узла инициирующее событие
    const EVENT_TYPE = 1; // Тип узла событие
    const MECHANISM_TYPE = 2; // Тип узла  механизм
    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%node}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'tree_diagram', 'level_id'], 'required'],
            [['operator', 'type', 'parent_node', 'tree_diagram'], 'default', 'value' => null],
            [['indent_x', 'indent_y'], 'default', 'value' => 0],
            [['operator', 'type', 'parent_node', 'tree_diagram', 'level_id', 'indent_x', 'indent_y'], 'integer'],

            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 600],

            [['certainty_factor'],  'number', 'max' => 1, 'min' => 0, 'numberPattern' => '/^[0-9]{1}(\.[0-9]{0,2})?$/',
                'message' => Yii::t('app', 'MESSAGE_PROBABILITY_ALLOWED_ONLY_UP_TO_HUNDREDTHS')],

            // name и tree_diagram вместе должны быть уникальны, но только name будет получать сообщение об ошибке
            ['name', 'unique', 'targetAttribute' => ['name', 'tree_diagram'],
                'message' => Yii::t('app', 'MESSAGE_ELEMENT_NAME_ALREADY_ON_DIAGRAM')],

            [['comment'], 'string'],

            [['parent_node'], 'exist', 'skipOnError' => true, 'targetClass' => Node::className(),
                'targetAttribute' => ['parent_node' => 'id']],
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
            'id' => Yii::t('app', 'NODE_MODEL_ID'),
            'created_at' => Yii::t('app', 'NODE_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'NODE_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'NODE_MODEL_NAME'),
            'certainty_factor' => Yii::t('app', 'NODE_MODEL_CERTAINTY_FACTOR'),
            'description' => Yii::t('app', 'NODE_MODEL_DESCRIPTION'),
            'operator' => Yii::t('app', 'NODE_MODEL_OPERATOR'),
            'type' => Yii::t('app', 'NODE_MODEL_TYPE'),
            'parent_node' => Yii::t('app', 'NODE_MODEL_PARENT_NODE'),
            'tree_diagram' => Yii::t('app', 'NODE_MODEL_TREE_DIAGRAM'),
            'level_id' => Yii::t('app', 'NODE_MODEL_LEVEL_ID'),
            'indent_x' => Yii::t('app', 'NODE_MODEL_INDENT_X'),
            'indent_y' => Yii::t('app', 'NODE_MODEL_INDENT_Y'),
            'comment' => Yii::t('app', 'NODE_MODEL_COMMENT'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка операторов.
     * @return array - массив всех возможных операторов
     */
    public static function getOperatorArray()
    {
        return [
            self::NOT_OPERATOR => Yii::t('app', 'NODE_MODEL_NOT_OPERATOR'),
            self::AND_OPERATOR => Yii::t('app', 'NODE_MODEL_AND_OPERATOR'),
            self::OR_OPERATOR => Yii::t('app', 'NODE_MODEL_OR_OPERATOR'),
            self::XOR_OPERATOR => Yii::t('app', 'NODE_MODEL_XOR_OPERATOR'),
        ];
    }

    /**
     * Получение названия оператора.
     * @return mixed
     */
    public function getOperatorName()
    {
        return ArrayHelper::getValue(self::getOperatorArray(), $this->type);
    }

    /**
     * Получение списка типов узлов.
     * @return array - массив всех возможных типов узлов
     */
    public static function getTypesArray()
    {
        return [
            self::INITIAL_EVENT_TYPE => Yii::t('app', 'TREE_DIAGRAM_MODEL_INITIAL_EVENT_TYPE'),
            self::EVENT_TYPE => Yii::t('app', 'TREE_DIAGRAM_MODEL_EVENT_TYPE'),
            self::MECHANISM_TYPE => Yii::t('app', 'TREE_DIAGRAM_MODEL_MECHANISM_TYPE'),
        ];
    }

    /**
     * Получение названия типа узла.
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

    /**
     * Получение списка типов узлов на английском.
     * @return array - массив всех возможных типов узлов на английском
     */
    public static function getTypesArrayEn()
    {
        return [
            self::INITIAL_EVENT_TYPE => 'Initial event',
            self::EVENT_TYPE => 'Event',
            self::MECHANISM_TYPE => 'Mechanism',
        ];
    }

    /**
     * Получение названия типа узла на английском.
     * @return mixed
     */
    public function getTypeNameEn()
    {
        return ArrayHelper::getValue(self::getTypesArrayEn(), $this->type);
    }

}
