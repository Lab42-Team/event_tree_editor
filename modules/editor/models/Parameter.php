<?php

namespace app\modules\editor\models;

use Yii;

/**
 * This is the model class for table "{{%parameter}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $description
 * @property int $operator
 * @property string $value
 * @property int $node
 *
 * @property Node $node0
 */
class Parameter extends \yii\db\ActiveRecord
{
    const EQUALLY_OPERATOR = 0;   // Оператор равно
    const MORE_OPERATOR = 1; // Оператор больше
    const LESS_OPERATOR = 2; // Оператор меньше
    const MORE_EQUAL_OPERATOR = 3; // Оператор больше или равно
    const LESS_EQUAL_OPERATOR = 4; // Оператор меньше или равно
    const NOT_EQUAL_OPERATOR = 5; // Оператор не равно

    /**
     * @return string table name
     */
    public static function tableName()
    {
        return '{{%parameter}}';
    }

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return [
            [['name', 'value', 'node'], 'required'],
            [['operator', 'node'], 'default', 'value' => null],
            [['operator', 'node'], 'integer'],

            [['name', 'value'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 600],

            [['node'], 'exist', 'skipOnError' => true, 'targetClass' => Node::className(),
                'targetAttribute' => ['node' => 'id']],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'PARAMETER_MODEL_ID'),
            'created_at' => Yii::t('app', 'PARAMETER_MODEL_CREATED_AT'),
            'updated_at' => Yii::t('app', 'PARAMETER_MODEL_UPDATED_AT'),
            'name' => Yii::t('app', 'PARAMETER_MODEL_NAME'),
            'description' => Yii::t('app', 'PARAMETER_MODEL_DESCRIPTION'),
            'operator' => Yii::t('app', 'PARAMETER_MODEL_OPERATOR'),
            'value' => Yii::t('app', 'PARAMETER_MODEL_VALUE'),
            'node' => Yii::t('app', 'PARAMETER_MODEL_NODE'),
        ];
    }

    /**
     * Получение списка операторов.
     * @return array - массив всех возможных операторов
     */
    public static function getOperatorArray()
    {
        return [
            self::EQUALLY_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_EQUALLY_OPERATOR'),
            self::MORE_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_MORE_OPERATOR'),
            self::LESS_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_LESS_OPERATOR'),
            self::MORE_EQUAL_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_MORE_EQUAL_OPERATOR'),
            self::LESS_EQUAL_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_LESS_EQUAL_OPERATOR'),
            self::NOT_EQUAL_OPERATOR => Yii::t('app', 'PARAMETER_MODEL_NOT_EQUAL_OPERATOR'),
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


}
