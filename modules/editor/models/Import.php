<?php

namespace app\modules\editor\models;

use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;


class Import extends Model
{
    public $file_name;       // Имя файла

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file_name'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xml'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'file_name' => Yii::t('app', 'IMPORT_FORM_FILE_NAME'),
        ];
    }


    public function upload()
    {
        if ($this->validate()) {
            $this->file_name->saveAs('uploads/' . 'temp.' . $this->file_name->extension);
            return true;
        } else {
            return false;
        }
    }


    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

}