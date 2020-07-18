<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\modules\editor\models\TreeDiagram;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAM') . ' - ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<?= $this->render('_modal_form_tree_diagrams', ['model' => $model]) ?>

<div class="tree-diagram-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-blackboard"></span> ' . Yii::t('app', 'BUTTON_OPEN_DIAGRAM'),
            ['visual-diagram', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> ' . Yii::t('app', 'BUTTON_UPDATE'),
            ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-export"></span> ' . Yii::t('app', 'BUTTON_EXPORT'),
            ['visual-diagram', 'id' => $model->id], ['data' => ['method' => 'post'], 'class' => 'btn btn-primary']
        ) ?>
        <?= Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('app', 'BUTTON_DELETE'), ['#'], [
            'class' => 'btn btn-danger',
            'data-toggle'=>'modal',
            'data-target'=>'#removeTreeDiagramModalForm'
        ]) ?>

    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
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
                'filter'=>TreeDiagram::getStatusesArray(),
            ],
            [
                'attribute'=>'author',
                'value' => $model->user->username,
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'description',
        ],
    ]) ?>

</div>
