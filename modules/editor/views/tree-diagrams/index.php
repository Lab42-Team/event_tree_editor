<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\main\models\User;
use app\modules\editor\models\TreeDiagram;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\editor\models\TreeDiagramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS');

$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$this->registerCssFile('/css/index.css', ['position'=>yii\web\View::POS_HEAD]);
?>

<div class="tree-diagram-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="buttons">
            <?= Html::a('<span class="glyphicon glyphicon-edit"></span> ' . Yii::t('app', 'TREE_DIAGRAMS_PAGE_CREATE_TREE_DIAGRAM'),
                ['create'], ['class' => 'btn btn-success']) ?>

            <?= ButtonDropdown::widget([
                'label' => '<span class="glyphicon glyphicon-share"></span> ' . Yii::t('app', 'TREE_DIAGRAMS_PAGE_CREATE_CHART_FROM_TEMPLATE'),
                'encodeLabel' => false,
                'options' => [
                    'class' => 'btn btn-primary',
                ],
                'dropdown' => [
                    'items' => $array_template,
                ],
            ]); ?>
        </div>

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
                'attribute'=>'mode',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getModesName();
                },
                'filter'=>Yii::$app->user->isGuest ? (''):
                    (TreeDiagram::getModesArray()),
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
                'attribute'=>'tree_view',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getTreeViewName();
                },
                'filter'=>Yii::$app->user->isGuest ? (''):
                    (TreeDiagram::getTreeViewArray()),
            ],
            [
                'attribute'=>'correctness',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getСorrectnessName();
                },
                'filter'=>Yii::$app->user->isGuest ? (''):
                    (TreeDiagram::getСorrectnessArray()),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => Yii::$app->user->isGuest ? ('{visual-diagram} {view}'):
                    ('{visual-diagram} {view} {update} {delete} {export}'),
                'buttons' => [
                    'visual-diagram' => function ($model){
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-blackboard"]);
                        $url = $model;
                        return Html::a($icon, $url,[
                            'title' => Yii::t('app', 'BUTTON_OPEN_DIAGRAM'),
                            'aria-label' => Yii::t('app', 'BUTTON_OPEN_DIAGRAM')
                        ]);
                    },
                    'export' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-export"></span>',
                            ['visual-diagram', 'id' => $model->id], ['data' => ['method' => 'post'],
                                'title' => Yii::t('app', 'BUTTON_EXPORT'),
                                'aria-label' => Yii::t('app', 'BUTTON_EXPORT')
                            ]
                        );
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
                    ('{visual-diagram} {view} {update} {delete} {export}'),
                'buttons' => [
                    'visual-diagram' => function ($model){
                        $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-blackboard"]);
                        $url = $model;
                        return Html::a($icon, $url,[
                            'title' => Yii::t('app', 'BUTTON_OPEN_DIAGRAM'),
                            'aria-label' => Yii::t('app', 'BUTTON_OPEN_DIAGRAM')
                        ]);
                    },
                    'export' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-export"></span>',
                            ['visual-diagram', 'id' => $model->id], ['data' => ['method' => 'post'],
                                'title' => Yii::t('app', 'BUTTON_EXPORT'),
                                'aria-label' => Yii::t('app', 'BUTTON_EXPORT')
                            ]
                        );
                    },
                ],

            ],
        ]),
    ]); ?>

</div>