<?php

namespace app\modules\editor\models;

use Yii;
use yii\base\Model;

/**
 * Class OWLFileForm.
 */
class OWLFileForm extends Model
{
    const UPLOAD_OWL_FILE_SCENARIO  = 'upload-owl-file';  // Сценарий загрузки OWL-файла онтологии

    public $owl_file;        // OWL-файл онтологии
    public $subclass_of;     // Отношение наследования (класс-подкласс)
    public $object_property; // Отношение между классами (объектные свойства)

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['owl_file'], 'required', 'on' => self::UPLOAD_OWL_FILE_SCENARIO),
            array(['owl_file'], 'file', 'extensions'=>'owl', 'checkExtensionByMimeType' => false),
            array(['subclass_of', 'object_property'], 'safe'),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'owl_file' => Yii::t('app', 'OWL_FILE_FORM_OWL_FILE'),
            'subclass_of' => Yii::t('app', 'OWL_FILE_FORM_SUBCLASS_OF'),
            'object_property' => Yii::t('app', 'OWL_FILE_FORM_OBJECT_PROPERTY'),
        );
    }
}