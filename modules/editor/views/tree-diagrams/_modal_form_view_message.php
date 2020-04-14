<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;

/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>

<!-- Модальное окно для вывода сообщения -->
<?php Modal::begin([
    'id' => 'viewMessageModalForm',
    'header' => '<h3>' . Yii::t('app', 'ERROR_LINKING_ITEMS') . '</h3>',
]); ?>

<div class="modal-body">
    <p id="message-text" style="font-size: 14px">
    </p>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'view-message-model-form',
]); ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_OK'),
    'options' => [
        'class' => 'btn-success',
        'style' => 'margin:5px',
        'data-dismiss'=>'modal'
    ]
]); ?>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>
