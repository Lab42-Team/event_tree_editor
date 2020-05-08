<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\main\models\User;
use app\modules\editor\models\TreeDiagram;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\editor\models\TreeDiagramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS');

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tree-diagram-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isGuest): ?>
        <p><?= Html::a('<span class="glyphicon glyphicon-edit"></span> ' . Yii::t('app', 'TREE_DIAGRAMS_PAGE_CREATE_TREE_DIAGRAM'),
                ['create'], ['class' => 'btn btn-success']) ?></p>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => !Yii::$app->user->isGuest ? ([
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'attribute'=>'type',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTypeName();
                },
                'filter'=>TreeDiagram::getTypesArray(),
            ],
            [

                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getStatusName();
                },
                'filter'=>Yii::$app->user->isGuest ? (''):
                    (TreeDiagram::getStatusesArray()),

            ],
            [
                'attribute'=>'author',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->user->username;
                },
                'filter'=>User::getAllUsersArray(),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => Yii::$app->user->isGuest ? ('{visual-diagram} {view}'):
                    ('{visual-diagram} {view} {update} {delete}'),
                'buttons' => [
                    'visual-diagram' => function ($model){
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-tree-deciduous"]);
                        $url = $model;
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ]):([
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            [
                'attribute'=>'type',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTypeName();
                },
                'filter'=>TreeDiagram::getTypesArray(),
            ],
            [
                'attribute'=>'author',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->user->username;
                },
                'filter'=>User::getAllUsersArray(),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => Yii::$app->user->isGuest ? ('{visual-diagram} {view}'):
                    ('{visual-diagram} {view} {update} {delete}'),
                'buttons' => [
                    'visual-diagram' => function ($model){
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-tree-deciduous"]);
                        $url = $model;
                        return Html::a($icon, $url);
                    },
                ],

            ],
        ]),
    ]); ?>

</div>