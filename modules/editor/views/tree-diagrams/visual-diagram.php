<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_VISUAL_DIAGRAM') . ' - ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<div class="tree-diagram-visual-diagram">

    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Сама диаграмма</h2>



</div>