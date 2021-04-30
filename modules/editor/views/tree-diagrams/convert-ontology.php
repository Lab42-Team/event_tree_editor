<?php

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $xml_data app\modules\editor\controllers\TreeDiagramsController */

use yii\helpers\Html;

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_CONVERT_ONTOLOGY');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAM') . ' - ' .
    $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tree-diagram-convert-ontology">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= print_r($xml_data); ?>

</div>