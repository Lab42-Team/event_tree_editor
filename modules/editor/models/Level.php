<?php

namespace app\modules\editor\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "{{%level}}".
 *
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property string $name
 * @property string $description
 * @property int $parent_level
 * @property int $tree_diagram
 *
 * @property TreeDiagram $treeDiagram
 * @property Sequence[] $sequences
 */
class Level extends \yii\db\ActiveRecord
{
    public $movement_level;

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
            [['tree_diagram', 'parent_level'], 'default', 'value' => null],
            [['tree_diagram', 'parent_level', 'movement_level'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 600],
            [['comment'], 'string'],

            // name и tree_diagram вместе должны быть уникальны, но только name будет получать сообщение об ошибке
            ['name', 'unique', 'targetAttribute' => ['name', 'tree_diagram'],
                'message' => Yii::t('app', 'MESSAGE_LEVEL_NAME_ALREADY_ON_DIAGRAM')],

            [['parent_level'], 'exist', 'skipOnError' => true, 'targetClass' => Level::className(),
                'targetAttribute' => ['parent_level' => 'id']],
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
            'parent_level' => Yii::t('app', 'LEVEL_MODEL_PARENT_LEVEL'),
            'tree_diagram' => Yii::t('app', 'LEVEL_MODEL_TREE_DIAGRAM'),
            'movement_level' => Yii::t('app', 'LEVEL_MODEL_MOVEMENT_LEVEL'),
            'comment' => Yii::t('app', 'LEVEL_MODEL_COMMENT'),
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
     */
    public function getTreeDiagram()
    {
        return $this->hasOne(TreeDiagram::className(), ['id' => 'tree_diagram']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSequences()
    {
        return $this->hasMany(Sequence::className(), ['level' => 'id']);
    }

    public static function getLevelsArray($id)
    {
        return ArrayHelper::map(self::find()->where(['tree_diagram' => $id])->orderBy('id')->all(), 'id', 'name');
    }

    public static function getWithoutInitialLevelsArray($id)
    {
        return ArrayHelper::map(self::find()->where(['tree_diagram' => $id])
            ->andWhere(['not', ['parent_level' => null]])->orderBy('id')->all(), 'id', 'name');
    }
}