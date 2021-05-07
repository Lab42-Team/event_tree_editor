<?php

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $owl_file_form app\modules\editor\models\OWLFileForm */
/* @var $classes app\modules\editor\controllers\TreeDiagramsController */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_CONVERT_ONTOLOGY');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAM') . ' - ' .
    $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<script type="text/javascript">
    function toggle(source) {
        let checkboxes = document.getElementsByClassName("class-list");
        for(let i = 0, n = checkboxes.length; i < n; i++)
            checkboxes[i].checked = source.checked;
    }
</script>

<div class="tree-diagram-convert-ontology">

    <h1><?= Html::encode($this->title) ?></h1><br />

    <?php $form = ActiveForm::begin([
        'id' => 'convert-ontology-form'
    ]); ?>

        <h4><?= Yii::t('app', 'CONVERT_ONTOLOGY_PAGE_RELATIONSHIP_INTERPRETATION') ?>:</h4>
        <?= $form->field($owl_file_form, 'subclass_of')->checkbox([
            'labelOptions' => ['style' => 'margin-left:15px;']
        ]) ?>

        <?= $form->field($owl_file_form, 'object_property')->checkbox([
            'labelOptions' => ['style' => 'margin-left:15px;']
        ]) ?>

        <h4><?= Yii::t('app', 'CONVERT_ONTOLOGY_PAGE_CLASS_LIST') ?>:</h4>
        <div class="well">
            <label><input type="checkbox" onClick="toggle(this)" />
                <?= Yii::t('app', 'CONVERT_ONTOLOGY_PAGE_SELECT_ALL_CLASSES') ?>
            </label><br/>
            <?php
                $index = 0;
                foreach($classes as $item) {
                    echo '<input type="checkbox" id="ontology-class-' . $index . '" name="ontology-class-' . $index .
                        '" class="class-list"> ' . $item[0] . '<br />';
                    $index++;
                }
            ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('<span class="glyphicon glyphicon-transfer"></span> ' .
                Yii::t('app', 'BUTTON_CONVERT'),
                ['class' => 'btn btn-success', 'name'=>'convert-ontology-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>