<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;

/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>

<!-- Модальное окно для вывода сообщений об ошибках при связывании элементов -->
<?php Modal::begin([
    'id' => 'viewMessageErrorLinkingItemsModalForm',
    'header' => '<h3>' . Yii::t('app', 'ERROR_LINKING_ITEMS') . '</h3>',
]); ?>

<div class="modal-body">
    <p id="message-text" style="font-size: 14px">
    </p>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'view-message-modal-form',
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



<!-- Модальное окно для вывода сообщений об ошибках при проверке диаграммы -->
<?php Modal::begin([
    'id' => 'viewMessageErrorsWhenCheckingTheChartModalForm',
    'header' => '<h3>' . Yii::t('app', 'VALIDATION') . '</h3>',
]); ?>

<div class="modal-body">
    <div id="message-verification-text">
    </div>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'view-message-modal-form',
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
