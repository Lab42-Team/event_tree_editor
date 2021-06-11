<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\editor\models\TreeDiagram;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tree-diagram-form">

    <?php $form = ActiveForm::begin([
        'id' => $model->isNewRecord ? 'create-tree-diagram-form' : 'update-tree-diagram-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 8]) ?>

    <?= $form->field($model, 'type')->dropDownList(TreeDiagram::getTypesArray()) ?>

    <?= $form->field($model, 'status')->dropDownList(TreeDiagram::getStatusesArray()) ?>

    <?= $model->isNewRecord ? $form->field($model, 'mode')->dropDownList(TreeDiagram::getModesArray()) : false    ?>

    <?= $form->field($model, 'tree_view')->dropDownList(TreeDiagram::getTreeViewArray()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '<span class="glyphicon glyphicon-floppy-disk"></span> ' .
            Yii::t('app', 'BUTTON_SAVE') : '<span class="glyphicon glyphicon-refresh"></span> ' .
            Yii::t('app', 'BUTTON_UPDATE'), ['class' => 'btn btn-success',
            'name'=>$model->isNewRecord ? 'create-tree-diagram-button' : 'update-tree-diagram-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>