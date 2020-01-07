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
    ['label' => Yii::t('app', 'NAV_ADD_EVENT'), 'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addEventModalForm']],
    ['label' => Yii::t('app', 'NAV_ADD_MECHANISM'), 'url' => '#',
        'options' => ['data-toggle'=>'modal', 'data-target'=>'#addMechanismModalForm']],
];
?>

<?= $this->render('_modal_form_level_editor', [
        'model' => $model,
        'level_model' => $level_model,
]) ?>

<?= $this->render('_modal_form_event_editor', [
    'model' => $model,
    'node_model' => $node_model,
]) ?>

<?= $this->render('_modal_form_mechanism_editor', [
    'model' => $model,
    'node_model' => $node_model,
]) ?>

<!-- Подключение скрипта для модальных форм -->
<?php
$this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('/css/visual-diagram.css', ['position'=>yii\web\View::POS_HEAD]);
?>

<script type="text/javascript">
    $(document).ready(function() {
        // Обработка закрытия модального окна добавления нового уровня
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

<script type="text/javascript">
    $(document).ready(function() {
        // Обработка закрытия модального окна добавления нового события
        $("#addEventModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-event-form .error-summary").hide();
            $("#add-event-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-event-form .help-block").each(function() {
                $(this).text("");
            });
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // Обработка закрытия модального окна добавления нового механизма
        $("#addMechanismModalForm").on("hidden.bs.modal", function() {
            // Скрытие списка ошибок ввода в модальном окне
            $("#add-mechanism-form .error-summary").hide();
            $("#add-mechanism-form .form-group").each(function() {
                $(this).removeClass("has-error");
                $(this).removeClass("has-success");
            });
            $("#add-mechanism-form .help-block").each(function() {
                $(this).text("");
            });
        });
    });
</script>

<div class="tree-diagram-visual-diagram">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<div id="visual-diagram-field" class="visual-diagram-top-layer col-md-12">

    <!-- Вывод уровней -->
    <!-- Вывод начального уровня -->
    <?php foreach ($level_model_all as $value): ?>
        <?php if ($value->parent_level == null){ ?>
            <div id="div-level-<?= $value->id ?>" class="div-level">
                <div class="div-level-name"><?= $value->name ?></div>
                <div class="div-level-description">
                    <?= $value->description ?>
                    <!-- Вывод инициирующего события -->
                    <?php foreach ($initial_event_model_all as $initial_event_value): ?>
                        <div id="div-initial-event-<?= $initial_event_value->id ?>" class="div-event">
                            <div class="div-event-name"><?= $initial_event_value->name ?></div>
                            <!-- <div class="div-event-description"> $initial_event_value->description ?></div>-->
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php $a = $value->id; }?>
    <?php endforeach; ?>
    <!-- Вывод остальных уровней -->
    <?php if ($level_model_count > 1){ ?>
        <?php $i = 1; ?>
        <?php do { ?>
            <?php foreach ($level_model_all as $level_value): ?>
                <?php if ($level_value->parent_level == $a){ ?>
                    <div id="div-level-<?= $level_value->id ?>" class="div-level">
                        <div class="div-level-name"><?= $level_value->name ?></div>
                        <div class="div-level-description">
                            <?= $level_value->description ?>
                            <?php foreach ($sequence_model_all as $sequence_value): ?>
                                <?php if ($sequence_value->level == $level_value->id){ ?>
                                    <?php $event_id = $sequence_value->node; ?>
                                    <?php foreach ($event_model_all as $event_value): ?>
                                        <?php if ($event_value->id == $event_id){ ?>
                                            <div id="div-event-<?= $event_value->id ?>" class="div-event">
                                                <div class="div-event-name"><?= $event_value->name ?></div>
                                                <!--<div class="div-event-description"> $event_value->description ?></div>-->
                                            </div>
                                        <?php } ?>
                                    <?php endforeach; ?>
                                <?php } ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $a = $level_value->id; ?>
                    <?php break 1; ?>
                <?php } ?>
            <?php endforeach; ?>
            <?php $i = $i + 1; ?>
        <?php } while ($i <> $level_model_count); ?>
    <?php } ?>

    <!-- Вывод механизма -->
    <?php foreach ($mechanism_model_all as $value): ?>
        <div id="div-mechanism-<?= $value->id ?>" class="div-mechanism">
            <div class="div-mechanism-name"><?= $value->name ?></div>
            <div class="div-mechanism-description"><?= $value->description ?></div>
        </div>
    <?php endforeach; ?>
</div>
