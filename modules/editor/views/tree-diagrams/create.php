<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */

$this->title = 'Create Tree Diagram';
$this->params['breadcrumbs'][] = ['label' => 'Tree Diagrams', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tree-diagram-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
