<?php

namespace app\modules\editor\models;

use yii\base\Model;

/**
 * Class OWLFileForm.
 */
class OWLFileForm extends Model
{
    public $owl_file;

    /**
     * @return array the validation rules
     */
    public function rules()
    {
        return array(
            array(['owl_file'], 'required'),
            array(['owl_file'], 'file', 'extensions'=>'owl', 'checkExtensionByMimeType' => false),
        );
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return array(
            'owl_file' => 'Файл онтологии в формате OWL',
        );
    }
}