<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\editor\models\TreeDiagramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tree Diagrams';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tree-diagram-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Tree Diagram', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'updated_at',
            'name',
            'description',
            //'type',
            //'status',
            //'author',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
