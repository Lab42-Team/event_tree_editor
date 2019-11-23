<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_CREATE_TREE_DIAGRAM');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tree-diagram-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
