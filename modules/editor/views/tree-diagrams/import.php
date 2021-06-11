<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_IMPORT_TREE_DIAGRAM');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAM') . ' - ' . $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tree-diagram-import">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'import-tree-diagram-form', 'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($import_model, 'file_name')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> ' . Yii::t('app', 'BUTTON_IMPORT'),
            ['class' => 'btn btn-success', 'name'=>'import-tree-diagram-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
