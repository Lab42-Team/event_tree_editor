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
            //[['file_name'], 'required'],
            [['file_name'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xml'],

            //последнее что предложил Никитос
            //[['file_name'], 'required'],
            //[['file_name'], 'file', 'extensions' => 'xml', 'checkExtensionByMimeType' => true],

            //[['file_name'], 'required'],
            //[['file_name'], 'file', 'extensions' => 'xml', 'checkExtensionByMimeType' => false],
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





    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param  string  $email the target email address
     * @return boolean whether the model passes validation
     */
    //public function contact($file_name)
    //{
    //    if ($this->validate()) {
    //        Yii::$app->mailer->compose()
    //            ->setTo($file_name)
    //            ->setFrom([$this->file_name])
    //            ->send();

    //        return true;
    //    }
    //    return false;
    //}
}