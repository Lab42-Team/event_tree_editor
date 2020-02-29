<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model app\modules\editor\models\TreeDiagram */
/* @var $level_model app\modules\editor\models\Level */
/* @var $node_model app\modules\editor\models\Node */
/* @var $level_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $level_model_count app\modules\editor\controllers\TreeDiagramsController */
/* @var $initial_event_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $sequence_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $event_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $mechanism_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */
/* @var $array_levels_initial_without app\modules\editor\controllers\TreeDiagramsController */

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_VISUAL_DIAGRAM') . ' - ' . $model->name;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'TREE_DIAGRAMS_PAGE_TREE_DIAGRAMS'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$this->params['menu'] = [
    ['label' => Yii::t('app', 'NAV_ADD_LEVEL'), 'url' => '#',
        'options' => ['id'=>'nav_add_level', 'class' => 'enabled',
            'data-toggle'=>'modal', 'data-target'=>'#addLevelModalForm']],
    ['label' => Yii::t('app', 'NAV_ADD_EVENT'), 'url' => '#',
        'options' => ['id'=>'nav_add_event', 'class' => 'disabled',
            'data-toggle'=>'modal', 'data-target'=>'']],
    ['label' => Yii::t('app', 'NAV_ADD_MECHANISM'), 'url' => '#',
        'options' => ['id'=>'nav_add_mechanism', 'class' => 'disabled',
            'data-toggle'=>'modal', 'data-target'=>'']],
];
?>

<?= $this->render('_modal_form_level_editor', [
    'model' => $model,
    'level_model' => $level_model,
]) ?>

<?php Pjax::begin(); ?>

<?= Html::a("Обновить", ['/tree-diagrams/visual-diagram/' . $model->id],
    ['id' => 'pjax-event-editor-button', 'style' => 'display:none']) ?>

<?= $this->render('_modal_form_event_editor', [
    'model' => $model,
    'node_model' => $node_model,
    'array_levels' => $array_levels,
]) ?>

<?= $this->render('_modal_form_mechanism_editor', [
    'model' => $model,
    'node_model' => $node_model,
    'array_levels_initial_without' => $array_levels_initial_without,
]) ?>

<?php Pjax::end(); ?>

<?php
    // создаем массив из соотношения level и node для передачи в jsplumb
    $sequence_mas = array();
    foreach ($sequence_model_all as $s){
        array_push($sequence_mas, [$s->level, $s->node]);
    }
?>

<!-- Подключение скрипта для модальных форм -->
<?php
$this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('/css/visual-diagram.css', ['position'=>yii\web\View::POS_HEAD]);

$this->registerJsFile('/js/jsplumb.js', ['position'=>yii\web\View::POS_HEAD]);  // jsPlumb 2.12.9
//$this->registerJsFile('/js/visual-diagram.js', ['position'=>yii\web\View::POS_HEAD]);
?>

<script type="text/javascript">
    $(document).ready(function() {
        // Включение переходов на модальные окна
        var nav_add_event = document.getElementById('nav_add_event');
        var nav_add_mechanism = document.getElementById('nav_add_mechanism');
        if ('<?php echo $level_model_count; ?>' > 0){
            nav_add_event.className = 'enabled';
            nav_add_event.setAttribute("data-target", "#addEventModalForm");
        }
        if ('<?php echo $level_model_count; ?>' > 1){
            nav_add_mechanism.className = 'enabled';
            nav_add_mechanism.setAttribute("data-target", "#addMechanismModalForm");
        }

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


    $(document).ready(function() {

        $(".div-event").mousemove(function(){
            var id_node = $(this).attr('id');
            var event = document.getElementById(id_node);
            var level = event.offsetParent;

            var width_level = level.clientWidth;
            var height_level = level.clientHeight;

            var top_layer_width = document.getElementById('top_layer').clientWidth;

            var l = event.offsetLeft;
            var h = event.offsetTop;

            if (l + 140 >= width_level){
                document.getElementById('top_layer').style.width = top_layer_width + 5 + 'px';
                //var s = $(".visual-diagram-top-layer").scrollLeft();
                //$(".visual-diagram-top-layer").scrollLeft(s+10);
            }

            if (h + 80 >= height_level){
                level.style.height = height_level + 5 + 'px';
                //var s = $(".visual-diagram-top-layer").scrollTop();
                //$(".visual-diagram-top-layer").scrollTop(s+10);
            }

            var sequence_mas = <?php echo json_encode($sequence_mas); ?>;//прием массива из php
            var max_width = 0;
            //разбор полученного массива
            $.each(sequence_mas, function (i, mas) {

                $.each(mas, function (j, elem) {
                    //второй элемент это id узла события или механизма
                    if (j == 1) {
                        var id_node = elem;//записываем id узла события node или механизма mechanism
                        //находим DOM элемент node (идентификатор div node)
                        var div_node_id = document.getElementById('node_'+ elem);

                        var width_node = div_node_id.clientWidth;
                        var height_node = div_node_id.clientHeight;
                        var w = div_node_id.offsetLeft;
                        var h = div_node_id.offsetTop;

                        var width = width_node + w;
                        var height = height_node + h;

                        if (max_width < width){max_width = width}
                        document.getElementById('top_layer').style.width = max_width + 105 + 'px';
                    }
                });
            });
        });

        $(".div-mechanism").mousemove(function(){
            var id_node = $(this).attr('id');
            var event = document.getElementById(id_node);
            var level = event.offsetParent;

            var width_level = level.clientWidth;
            var height_level = level.clientHeight;

            var top_layer_width = document.getElementById('top_layer').clientWidth;

            var l = event.offsetLeft;
            var h = event.offsetTop;

            if (l + 69 >= width_level){
                document.getElementById('top_layer').style.width = top_layer_width + 5 + 'px';
                //var s = $(".visual-diagram-top-layer").scrollLeft();
                //$(".visual-diagram-top-layer").scrollLeft(s+10);
            }

            if (h + 80 >= height_level){
                level.style.height = height_level + 5 + 'px';
                //var s = $(".visual-diagram-top-layer").scrollTop();
                //$(".visual-diagram-top-layer").scrollTop(s+10);
            }

            var sequence_mas = <?php echo json_encode($sequence_mas); ?>;//прием массива из php
            var max_width = 0;
            //разбор полученного массива
            $.each(sequence_mas, function (i, mas) {

                $.each(mas, function (j, elem) {
                    //второй элемент это id узла события или механизма
                    if (j == 1) {
                        var id_node = elem;//записываем id узла события node или механизма mechanism
                        //находим DOM элемент node (идентификатор div node)
                        var div_node_id = document.getElementById('node_'+ elem);

                        var width_node = div_node_id.clientWidth;
                        var height_node = div_node_id.clientHeight;
                        var w = div_node_id.offsetLeft;
                        var h = div_node_id.offsetTop;

                        var width = width_node + w;
                        var height = height_node + h;

                        if (max_width < width){max_width = width}
                        document.getElementById('top_layer').style.width = max_width + 105 + 'px';
                    }
                });
            });

        });



    });


    // работаю над редактированием элемента
    //$(document).on('dblclick', '.div-event', function() {
    //    var id_dblclick = $(this).attr('id');
    //    alert(id_dblclick);
    //});



    var instance = "";
    jsPlumb.ready(function () {
        instance = jsPlumb.getInstance({
            Container: visual_diagram_field,
            Connector:"StateMachine",
            Endpoint:["Dot", {radius:3}], Anchor:"Center"});

        var sequence_mas = <?php echo json_encode($sequence_mas); ?>;//прием массива из php

        var group_name = "";
        //разбор полученного массива
        $.each(sequence_mas, function (i, mas) {
            $.each(mas, function (j, elem) {
                //первый элемент это id уровня
                if (j == 0) {
                    id_level = elem;//записываем id уровня
                    //находим DOM элемент description уровня (идентификатор div level_description)
                    var div_level_id = document.getElementById('level_description_'+ id_level);
                    group_name = 'group'+ id_level; //определяем имя группы
                    var grp = instance.getGroup(group_name);//определяем существует ли группа с таким именем
                    if (grp == 0){
                        //если группа не существует то создаем группу с определенным именем group_name
                        instance.addGroup({
                            el: div_level_id,
                            id: group_name,
                            draggable: false, //перетаскивание группы
                            //constrain: true, //запрет на перетаскивание элементов за группу (false перетаскивать можно)
                            dropOverride:true,
                        });
                    }
                }
                //второй элемент это id узла события или механизма
                if (j == 1) {
                    var id_node = elem;//записываем id узла события node или механизма mechanism
                    //находим DOM элемент node (идентификатор div node)
                    var div_node_id = document.getElementById('node_'+ elem);
                    //делаем node перетаскиваемым
                    instance.draggable(div_node_id);
                    //добавляем элемент div_node_id в группу с именем group_name
                    instance.addToGroup(group_name, div_node_id);
                }
            });
        });
    });
</script>


<div class="tree-diagram-visual-diagram">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<div id="visual_diagram_field" class="visual-diagram-top-layer col-md-12">
    <div id="top_layer" class="top">
    <!-- Вывод уровней -->
    <!-- Вывод начального уровня -->
    <?php foreach ($level_model_all as $value): ?>
    <?php if ($value->parent_level == null){ ?>
        <div id="level_<?= $value->id ?>" class="div-level">
            <div class="div-level-name"><?= $value->name ?></div>
            <div class="div-level-description" id="level_description_<?= $value->id ?>">
                <!--?= $level_value->description ?>-->
                <!-- Вывод инициирующего события -->
                <?php foreach ($initial_event_model_all as $initial_event_value): ?>
                    <div id="node_<?= $initial_event_value->id ?>" class="div-event">
                        <div class="div-event-name"><?= $initial_event_value->name ?></div>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($sequence_model_all as $sequence_value): ?>
                    <?php if ($sequence_value->level == $value->id){ ?>
                        <?php $event_id = $sequence_value->node; ?>
                        <?php foreach ($event_model_all as $event_value): ?>
                            <?php if ($event_value->id == $event_id){ ?>
                                <div id="node_<?= $event_value->id ?>" class="div-event">
                                    <div class="div-event-name"><?= $event_value->name ?></div>
                                </div>
                            <?php } ?>
                        <?php endforeach; ?>
                    <?php } ?>
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
                    <div id="level_<?= $level_value->id ?>" class="div-level">
                        <div class="div-level-name"><?= $level_value->name ?></div>
                        <div class="div-level-description" id="level_description_<?= $level_value->id ?>">
                            <!--?= $level_value->description ?>-->
                            <?php foreach ($sequence_model_all as $sequence_value): ?>
                                <?php if ($sequence_value->level == $level_value->id){ ?>
                                    <?php $node_id = $sequence_value->node; ?>
                                    <!-- Вывод механизма -->
                                    <?php foreach ($mechanism_model_all as $mechanism_value): ?>
                                        <?php if ($mechanism_value->id == $node_id){ ?>
                                            <div id="node_<?= $mechanism_value->id ?>"
                                                 class="div-mechanism" title="<?= $mechanism_value->name ?>">
                                                <div class="div-mechanism-m">M</div>
                                            </div>
                                        <?php } ?>
                                    <?php endforeach; ?>
                                    <!-- Вывод событий -->
                                    <?php foreach ($event_model_all as $event_value): ?>
                                        <?php if ($event_value->id == $node_id){ ?>
                                            <div id="node_<?= $event_value->id ?>" class="div-event">
                                                <div class="div-event-name"><?= $event_value->name ?></div>
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
    </div>
<?php } ?>
</div>