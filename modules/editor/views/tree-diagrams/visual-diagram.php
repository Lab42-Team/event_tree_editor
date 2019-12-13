<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $level_model app\modules\editor\models\Level */
/* @var $level_model_all app\modules\editor\controllers\TreeDiagramsController */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_VISUAL_DIAGRAM') . ' - ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menu'] = [
    ['label' => Yii::t('app', 'NAV_ADD_LEVEL'), 'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addLevelModalForm']],
    ['label' => Yii::t('app', 'NAV_ADD_INITIAL_EVENT'), 'url' => '#'],
    ['label' => Yii::t('app', 'NAV_ADD_EVENT'), 'url' => '#'],
    ['label' => Yii::t('app', 'NAV_ADD_MECHANISM'), 'url' => '#'],
];
?>

<?= $this->render('_modal_form_level_editor', [
        'model' => $model,
        'level_model' => $level_model,
]) ?>

<!-- Подключение скрипта для модальных форм -->
<?php $this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]) ?>

<script type="text/javascript">
    $(document).ready(function() {
        // Обработка закрытия модального окна добавления нового шаблона факта
        $("#addLevelModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-level-form .error-summary").hide();
            $("#add-level-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-level-form .help-block").each(function() {
                $(this).text("");
            });
        });
    });
</script>

<div class="tree-diagram-visual-diagram">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<!-- Вывод уровней -->
<div id="visual-diagram-top-layer" class="col-md-10">
    <?php foreach ($level_model_all as $value): ?>
        <div class="div-level-<?= $value->id ?>">
            <div class="div-level-name"><?= $value->name ?></div>
            <div class="div-level-description"><?= $value->description ?></div>
        </div>
    <?php endforeach; ?>
</div>