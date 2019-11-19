<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */

$this->title = 'Update Tree Diagram: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tree Diagrams', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tree-diagram-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
