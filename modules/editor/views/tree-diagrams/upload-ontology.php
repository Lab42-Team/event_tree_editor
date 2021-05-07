<?php

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $owl_file_form app\modules\editor\models\OWLFileForm */
/* @var $xml_data app\modules\editor\controllers\TreeDiagramsController */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_UPLOAD_ONTOLOGY');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAM') . ' - ' .
    $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tree-diagram-upload-ontology">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'upload-ontology-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

        <?= $form->field($owl_file_form, 'owl_file')->fileInput() ?>

        <div class="form-group">
            <?= Html::submitButton('<span class="glyphicon glyphicon-download-alt"></span> ' .
                Yii::t('app', 'BUTTON_UPLOAD'),
                ['class' => 'btn btn-success', 'name'=>'upload-ontology-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>