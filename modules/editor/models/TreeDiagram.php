<?php

namespace app\modules\editor\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\main\models\User;
use yii\behaviors\TimestampBehavior;

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
 * @property User $user
 */
class TreeDiagram extends \yii\db\ActiveRecord
{
    const EVENT_TREE_TYPE = 0; // Тип диаграммы дерево событий
    const FAULT_TREE_TYPE = 1; // Тип диаграммы дерево отказов

    const PUBLIC_STATUS = 0;   // Публичный статус
    const PRIVATE_STATUS = 1;  // Приватный статус

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

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Получение списка типов диаграмм.
     *
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
     *
     * @return mixed
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getTypesArray(), $this->type);
    }

    /**
     * Получение списка статусов.
     *
     * @return array - массив всех возможных статусов
     */
    public static function getStatusesArray()
    {
        return [
            self::PUBLIC_STATUS => Yii::t('app', 'TREE_DIAGRAM_MODEL_PUBLIC_STATUS'),
            self::PRIVATE_STATUS => Yii::t('app', 'TREE_DIAGRAM_MODEL_PRIVATE_STATUS'),
        ];
    }

    /**
     * Получение названия типа диаграмм.
     *
     * @return mixed
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->type);
    }

    /**
     * Получение имени автора диаграммы.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}