<?php

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
/* @var $node_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $parameter_model_all app\modules\editor\controllers\TreeDiagramsController */
/* @var $parameter_model app\modules\editor\controllers\TreeDiagramsController */
/* @var $the_initial_event_is app\modules\editor\controllers\TreeDiagramsController */

use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\main\models\Lang;
use app\modules\editor\models\TreeDiagram;

$this->title = Yii::t('app', 'TREE_DIAGRAMS_PAGE_VISUAL_DIAGRAM') . ' - ' . $model->name;

$this->params['menu_add'] = [
    ['label' => Yii::t('app', 'NAV_ADD_LEVEL'), 'url' => '#',
        'options' => ['id'=>'nav_add_level', 'class' => 'disabled',
            'data-toggle'=>'modal', 'data-target'=>'']],
    ['label' => Yii::t('app', 'NAV_ADD_EVENT'), 'url' => '#',
        'options' => ['id'=>'nav_add_event', 'class' => 'disabled',
            'data-toggle'=>'modal', 'data-target'=>'']],
    ['label' => Yii::t('app', 'NAV_ADD_MECHANISM'), 'url' => '#',
        'options' => ['id'=>'nav_add_mechanism', 'class' => 'disabled',
            'data-toggle'=>'modal', 'data-target'=>'']],
];

$this->params['menu_diagram'] = [
    ['label' => '<span class="glyphicon glyphicon-import"></span> ' . Yii::t('app', 'NAV_IMPORT'),
        'url' => Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .'/tree-diagrams/import/'. $model->id],

    ['label' => '<span class="glyphicon glyphicon-export"></span> ' . Yii::t('app', 'NAV_EXPORT'),
        'url' => '#', 'linkOptions' => ['data-method' => 'post']],

    ['label' => '<span class="glyphicon glyphicon-check"></span> ' . Yii::t('app', 'NAV_VERIFY'),
        'url' => '#', 'options' => ['id'=>'nav_correctness']],

    ['label' => '<span class="glyphicon glyphicon-object-align-vertical"></span> ' . Yii::t('app', 'NAV_ALIGNMENT'),
        'url' => '#', 'options' => ['id'=>'nav_alignment']],
];
?>


<?php
// создаем массив из соотношения level и node для передачи в jsplumb
$sequence_mas = array();
foreach ($sequence_model_all as $s){
    array_push($sequence_mas, [$s->level, $s->node]);
}

// создаем массив из соотношения id и parent_node для передачи в jsplumb
$node_mas = array();
foreach ($node_model_all as $n){
    array_push($node_mas, [$n->id, $n->parent_node, $n->name, $n->description, $n->certainty_factor, $n->indent_x, $n->indent_y]);
}

$level_mas = array();
foreach ($level_model_all as $l){
    array_push($level_mas, [$l->id, $l->parent_level, $l->name, $l->description]);
}

$parameter_mas = array();
foreach ($parameter_model_all as $p){
    array_push($parameter_mas, [$p->id, $p->name, $p->description, $p->operator, $p->value]);
}

$initial_event_mas = array();
foreach ($initial_event_model_all as $i){
    array_push($initial_event_mas, [$i->id]);
}
?>


<?= $this->render('_modal_form_relationship', [
    'model' => $model,
]) ?>

<?= $this->render('_modal_form_parameter_editor', [
    'parameter_model' => $parameter_model,
]) ?>


<?php Pjax::begin(); ?>

<?= Html::a("Обновить", ['/tree-diagrams/visual-diagram/' . $model->id],
    ['id' => 'pjax-event-editor-button', 'style' => 'display:none']) ?>

<?= $this->render('_modal_form_level_editor', [
    'model' => $model,
    'level_model' => $level_model,
    'array_levels' => $array_levels,
]) ?>

<?= $this->render('_modal_form_event_editor', [
    'model' => $model,
    'node_model' => $node_model,
    'array_levels' => $array_levels,
    'array_levels_initial_without' => $array_levels_initial_without,
    'the_initial_event_is' => $the_initial_event_is,
]) ?>

<?= $this->render('_modal_form_mechanism_editor', [
    'model' => $model,
    'node_model' => $node_model,
    'array_levels_initial_without' => $array_levels_initial_without,
]) ?>

<?php Pjax::end(); ?>

<?= $this->render('_modal_form_event_comment_editor', [
    'model' => $model,
    'node_model' => $node_model,
]) ?>

<?= $this->render('_modal_form_level_comment_editor', [
    'model' => $model,
    'level_model' => $level_model,
]) ?>

<?= $this->render('_modal_form_view_message', [
]) ?>


<!-- Подключение скрипта для модальных форм -->
<?php
$this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('/css/visual-diagram.css', ['position'=>yii\web\View::POS_HEAD]);

$this->registerJsFile('/js/jsplumb.js', ['position'=>yii\web\View::POS_HEAD]);  // jsPlumb 2.12.9
?>


<script type="text/javascript">

    var guest = <?php echo json_encode(Yii::$app->user->isGuest); ?>;//переменная гость определяет пользователь гость или нет

    $(document).ready(function() {

        //скрывание наименование уровня при классическом режиме построения деревьев событий
        if (<?= TreeDiagram::CLASSIC_TREE_MODE ?> == <?= $model->mode ?>){
            var div_level = document.getElementsByClassName("div-level-name");
            $.each(div_level, function (i, level) {
                level.hidden = true;
            });
        }

        if (!guest){
            var nav_add_level = document.getElementById('nav_add_level');
            var nav_add_event = document.getElementById('nav_add_event');
            var nav_add_mechanism = document.getElementById('nav_add_mechanism');

            // Включение переходов на модальные окна
            if (<?= TreeDiagram::CLASSIC_TREE_MODE ?> != <?= $model->mode ?>){
                nav_add_level.className = 'enabled';
                nav_add_level.setAttribute("data-target", "#addLevelModalForm");
                if (('<?php echo $level_model_count; ?>' > 0)&&('<?php echo $the_initial_event_is; ?>' == 0)){
                    nav_add_event.className = 'enabled';
                    nav_add_event.setAttribute("data-target", "#addEventModalForm");
                }
                if ('<?php echo $level_model_count; ?>' > 1){
                    nav_add_event.className = 'enabled';
                    nav_add_event.setAttribute("data-target", "#addEventModalForm");
                    nav_add_mechanism.className = 'enabled';
                    nav_add_mechanism.setAttribute("data-target", "#addMechanismModalForm");
                }
            } else {
                nav_add_event.className = 'enabled';
                nav_add_event.setAttribute("data-target", "#addEventModalForm");
                nav_add_level.hidden = true;
                nav_add_mechanism.hidden = true;
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
            $("#addMechanismModalForm").on("hidden.af.modal", function() {
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

            // Обработка открытия модального окна добавления нового события
            $("#addEventModalForm").on("show.bs.modal", function() {
                //если начальное событие есть тогда
                var initial_event = document.getElementsByClassName("div-initial-event");
                if ((initial_event.length == 0)||(<?= TreeDiagram::CLASSIC_TREE_MODE ?> == <?= $model->mode ?>)){
                    //блокировка изменения левела
                    document.forms["add-event-form"].elements["Node[level_id]"].style.display = "none";
                    document.getElementById('add_label_level').style.display = "none";
                } else {
                    document.forms["add-event-form"].elements["Node[level_id]"].style.display = "";
                    document.getElementById('add_label_level').style.display = "";
                }
            });
        } else {
            // Если гость тогда скрываем кнопки удаления и создание связи
            var ep_node = document.getElementsByClassName("ep");
            $.each(ep_node, function (i, node) {
                node.style = "display:none;";
            });

            var del_node = document.getElementsByClassName("del");
            $.each(del_node, function (i, node) {
                node.style = "display:none;";
            });

            var edit_node = document.getElementsByClassName("edit");
            $.each(edit_node, function (i, node) {
                node.style = "display:none;";
            });

            var param_node = document.getElementsByClassName("param");
            $.each(param_node, function (i, node) {
                node.style = "display:none;";
            });
        }
    });

    var node_id_on_click = 0;
    var level_id_on_click = 0;
    var parameter_id_on_click = 0;

    // Текущая связь между элементами
    var current_connection;

    var id_target;

    var sequence_mas = <?php echo json_encode($sequence_mas); ?>;//прием массива последовательностей из php
    var node_mas = <?php echo json_encode($node_mas); ?>;//прием массива событий из php
    var level_mas = <?php echo json_encode($level_mas); ?>;//прием массива уровней из php
    var parameter_mas = <?php echo json_encode($parameter_mas); ?>;//прием массива параметров из php

    var message_label = "<?php echo Yii::t('app', 'CONNECTION_DELETE'); ?>";

    var mas_data_level = {};
    var q = 0;
    var id_level = "";
    var parent_level = "";
    var name_level = "";
    var description_level = "";
    $.each(level_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            if (j == 0) {id_level = elem;}
            if (j == 1) {parent_level = elem;}
            if (j == 2) {name_level = elem;}
            if (j == 3) {description_level = elem;}
            mas_data_level[q] = {
                "id_level":id_level,
                "parent_level":parent_level,
                "name":name_level,
                "description":description_level,
            }
        });
        q = q+1;
    });

    var mas_data_node = {};
    var q = 0;
    var id_node = "";
    var id_parent_node = "";
    var name_node = "";
    var description_node = "";
    var certainty_factor = "";
    $.each(node_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            //первый элемент это id уровня
            if (j == 0) {id_node = elem;}//записываем id уровня
            //второй элемент это id узла события или механизма
            if (j == 1) {id_parent_node = elem;}//записываем id узла события node или механизма mechanism
            if (j == 2) {name_node = elem;}
            if (j == 3) {description_node = elem;}
            if (j == 4) {certainty_factor = elem;}
            mas_data_node[q] = {
                "id":id_node,
                "parent_node":id_parent_node,
                "name":name_node,
                "description":description_node,
                "certainty_factor":certainty_factor,
            }
        });
        q = q+1;
    });

    var mas_data_parameter = {};
    var q = 0;
    var id_parameter = "";
    var name_parameter = "";
    var description_parameter = "";
    var operator_parameter = "";
    var value_parameter = "";

    $.each(parameter_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            //первый элемент это id уровня
            if (j == 0) {id_parameter = elem;}//записываем id уровня
            //второй элемент это id узла события или механизма
            if (j == 1) {name_parameter = elem;}//записываем id узла события node или механизма mechanism
            if (j == 2) {description_parameter = elem;}
            if (j == 3) {operator_parameter = elem;}
            if (j == 4) {value_parameter = elem;}
            mas_data_parameter[q] = {
                "id":id_parameter,
                "name":name_parameter,
                "description":description_parameter,
                "operator":operator_parameter,
                "value":value_parameter,
            }
        });
        q = q+1;
    });


    var mas_location = {};
    var q = 0;
    var id_node = "";
    var indent_x = "";
    var indent_y = "";
    $.each(node_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            //первый элемент это id уровня
            if (j == 0) {id_node = elem;}//записываем id уровня
            if (j == 5) {indent_x = elem;}
            if (j == 6) {indent_y = elem;}
            mas_location[q] = {
                "id":id_node,
                "indent_x":indent_x,
                "indent_y":indent_y,
            }
        });
        q = q+1;
    });


    var instance = "";
    jsPlumb.ready(function () {
        instance = jsPlumb.getInstance({
            Connector:["Flowchart", {cornerRadius:5}], //стиль соединения линии ломанный с радиусом
            Endpoint:["Dot", {radius:1}], //стиль точки соединения
            EndpointStyle: { fill: '#337ab7' }, //цвет точки соединения
            PaintStyle : { strokeWidth:3, stroke: "#337ab7", "dashstyle": "0 0", fill: "transparent"},//стиль линии
            HoverPaintStyle: {strokeWidth: 4, stroke: "#ff3f48", "dashstyle": "4 2"},//стиль линии пунктирная из за свойства dashstyle
            Overlays:[["PlainArrow", {location:1, width:15, length:15}]], //стрелка
            ConnectionOverlays: [
                [ "Label", {
                    label: message_label,
                    id: "label_connector",
                    cssClass: "aLabel"
                }]
            ],
            Container: "visual_diagram_field"
        });

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

        //находим все элементы с классом div-event-comment
        $(".div-event-comment").each(function(i) {
            var id_comment = $(this).attr('id');
            var comment = document.getElementById(id_comment);
            var level = comment.offsetParent;
            var id_level = parseInt(level.getAttribute('id').match(/\d+/));
            var group_name = 'group'+ id_level; //определяем имя группы
            //делаем comment перетаскиваемым
            instance.draggable(comment);
            //добавляем элемент comment в группу с именем group_name
            instance.addToGroup(group_name, comment);
        });


        //находим все элементы с классом div-level-comment
        $(".div-level-comment").each(function(i) {
            var id_comment = $(this).attr('id');
            var comment = document.getElementById(id_comment);
            var level = comment.offsetParent;
            var id_level = parseInt(level.getAttribute('id').match(/\d+/));
            var group_name = 'group'+ id_level; //определяем имя группы

            //находим DOM элемент description уровня (идентификатор div level_description)
            var div_level_id = document.getElementById('level_description_'+ id_level);

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
            group_name = "";

            //делаем comment перетаскиваемым
            instance.draggable(comment);
            //добавляем элемент comment в группу с именем group_name
            instance.addToGroup(group_name, comment);
        });


        var windows = jsPlumb.getSelector(".node");

        instance.bind("beforeDrop", function (info) {
            var source_node = document.getElementById(info.sourceId);
            var target_node = document.getElementById(info.targetId);

            var source_level = source_node.offsetParent.getAttribute('id');
            var target_level = target_node.offsetParent.getAttribute('id');

            var source_id_level = parseInt(source_level.match(/\d+/));
            var target_id_level = parseInt(target_level.match(/\d+/));


            //длинна массива
            var length = 0
            $.each(mas_data_level, function (i, mas) {
                length = length + 1;
            });

            //построение одномерного массива по порядку следования уровней
            var mas_level_order = {};//одномерный массив
            var next_parent_level = null;
            var q = 0;
            for (let i = 0; i < length; i++) {
                $.each(mas_data_level, function (i, mas) {
                    if (mas.parent_level == next_parent_level){
                        next_parent_level = mas.id_level;
                        mas_level_order[q] = mas.id_level;
                        q = q+1;
                    }
                });
            }


            //определение порядковых номеров source и target
            var n_source = "";
            var n_target = "";
            $.each(mas_level_order, function (i, elem) {
                if (elem == source_id_level) {n_source = i;}//записываем порядковый номер source
                if (elem == target_id_level) {n_target = i;}//записываем порядковый номер target
            });


            // Запреты
            // ------------------------------
            // запрет на соединение механизмов
            if ((source_node.getAttribute("class").search("mechanism") == target_node.getAttribute("class").search("mechanism"))
                && (source_node.getAttribute("class").search("mechanism") != -1)){
                var message = "<?php echo Yii::t('app', 'MECHANISMS_SHOULD_NOT_BE_INTERCONNECTED'); ?>";
                document.getElementById("message-text").lastChild.nodeValue = message;
                $("#viewMessageErrorLinkingItemsModalForm").modal("show");
                return false;
            } else {
                // запрет на соединение c элементами на вышестоящем уровне
                if (n_source > n_target){
                    var message = "<?php echo Yii::t('app', 'ELEMENTS_NOT_BE_ASSOCIATED_WITH_OTHER_ELEMENTS_HIGHER_LEVEL'); ?>";
                    document.getElementById("message-text").lastChild.nodeValue = message;
                    $("#viewMessageErrorLinkingItemsModalForm").modal("show");
                    return false;
                } else {
                    // запрет на соединение c элементами кроме механизмов на нижестоящем уровне
                    if ((n_source < n_target) && (target_node.getAttribute("class").search("mechanism") == -1)){
                        var message = "<?php echo Yii::t('app', 'LEVEL_MUST_BEGIN_WITH_MECHANISM'); ?>";
                        document.getElementById("message-text").lastChild.nodeValue = message;
                        $("#viewMessageErrorLinkingItemsModalForm").modal("show");
                        return false;
                    } else {
                        if(target_node.getAttribute("class").search("div-initial-event") >= 0){
                            var message = "<?php echo Yii::t('app', 'INITIAL_EVENT_SHOULD_NOT_BE_INCOMING_CONNECTIONS'); ?>";
                            document.getElementById("message-text").lastChild.nodeValue = message;
                            $("#viewMessageErrorLinkingItemsModalForm").modal("show");
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            }
        });


        instance.batch(function () {
            for (var i = 0; i < windows.length; i++) {
                //определяет механизм ли. но нужно его вставить в свойство anchor у makeSource и makeTarget
                var cl = windows[i].className;
                var anchor_top = "";
                var anchor_bottom = "";
                var max_con = 1;
                if (cl == "div-mechanism node jtk-managed jtk-draggable") {
                    anchor_top = [ 0.5, 0, 0, -1, 0, 20 ];
                    anchor_bottom = [ 0.5, 1, 0, 1, 0, -20 ];
                } else {
                    anchor_top = "Top";
                    anchor_bottom = "Bottom";
                }

                instance.makeSource(windows[i], {
                    filter: ".ep",
                    anchor: anchor_bottom,
                });

                instance.makeTarget(windows[i], {
                    dropOptions: { hoverClass: "dragHover" },
                    anchor: anchor_top,
                    allowLoopback: false, // Нельзя создать кольцевую связь
                    //anchor: "Top",
                    maxConnections: max_con,
                    onMaxConnections: function (info, e) {
                        var message = "<?php echo Yii::t('app', 'MAXIMUM_CONNECTIONS'); ?>" + info.maxConnections;
                        document.getElementById("message-text").lastChild.nodeValue = message;
                        $("#viewMessageErrorLinkingItemsModalForm").modal("show");
                    }
                });
            }


            $.each(mas_data_node, function (j, elem_node) {
                if (elem_node.parent_node != null){
                    instance.connect({
                        source: "node_" + elem_node.parent_node,
                        target: "node_" + elem_node.id,
                    });
                }
            });
        });

        instance.bind("connection", function(connection) {
            if (!guest) {
                var source_id = connection.sourceId;
                var target_id = connection.targetId;
                var parent_node_id = parseInt(source_id.match(/\d+/));
                var node_id = parseInt(target_id.match(/\d+/));
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/add-relationship'?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" +
                    "&parent_node_id=" + parent_node_id + "&node_id=" + node_id,
                    dataType: "json",
                    success: function (data) {
                        if (data['success']) {
                            $.each(mas_data_node, function (i, elem_node) {
                                //добавляем связь в массив
                                var p_n_id = parseInt(data["p_n_id"], 10);
                                if (data["n_id"] == elem_node.id) {
                                    mas_data_node[i].parent_node = p_n_id;
                                }
                            });
                        }
                    },
                    error: function () {
                        alert('Error!');
                    }
                });
            }
        });


        // Обработка удаления связи
        instance.bind("click", function(connection) {
            if (!guest) {
                //var source_node = connection.sourceId;
                var target_node = connection.targetId;
                id_target = parseInt(target_node.match(/\d+/));
                current_connection = connection;
                $("#deleteRelationshipModalForm").modal("show");
            }
        });
    });


    //функция расширения уровней и их свертывание
    var mousemoveNode = function(x) {
        var node = document.getElementById(x);
        var level = node.offsetParent;

        var width_level = level.clientWidth;
        var height_level = level.clientHeight;

        var top_layer_width = document.getElementById('top_layer').clientWidth;

        var l = node.offsetLeft + node.clientWidth;
        var h = node.offsetTop + node.clientHeight;

        if (l >= width_level){
            document.getElementById('top_layer').style.width = top_layer_width + 5 + 'px';
        }
        if (h >= height_level){
            level.style.height = height_level + 5 + 'px';
        }
        //------------------------------------------
        //автоматическое свертывание по горизонтали
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
                    var w = div_node_id.offsetLeft;
                    var width = width_node + w;

                    if (max_width < width){max_width = width}
                    document.getElementById('top_layer').style.width = max_width + 105 + 'px';
                }
            });
        });
        //------------------------------------------
        //автоматическое свертывание по вертикали
        var mas_data = {};
        var q = 0;
        var id_level = "";
        var id_node = "";
        $.each(sequence_mas, function (i, mas) {
            $.each(mas, function (j, elem) {
                //первый элемент это id уровня
                if (j == 0) {id_level = elem;}//записываем id уровня
                //второй элемент это id узла события или механизма
                if (j == 1) {id_node = elem;}//записываем id узла события node или механизма mechanism
                mas_data[q] = {
                    "level":id_level,
                    "node":id_node,
                }
            });
            q = q+1;
        });

        var mas_otbor = {};
        var q = 0;
        $.each(mas_data, function (i, elem1) {
            var max_height = 0;
            var mas_node = 0;
            var mas_level = 0;
            $.each(mas_data, function (j, elem2) {
                var div_node_2 = document.getElementById('node_'+ elem2.node);
                var height_node = div_node_2.clientHeight;
                var h = div_node_2.offsetTop;
                var height = height_node + h;

                if (elem1.level == elem2.level) {
                    if (max_height < height){
                        max_height = height;
                        mas_node = elem2.node;
                        mas_level = elem2.level;
                        q = q+1;
                    }
                }
            });
            mas_otbor[q] = {
                "level":mas_level,
                "node":mas_node,
            };
        });

        $.each(mas_otbor, function (j, elem) {
            //находим DOM элемент node (идентификатор div node)
            var div_node_id = document.getElementById('node_'+ elem.node);
            var div_level_id = document.getElementById('level_description_'+ elem.level);
            var height_node = div_node_id.clientHeight;
            var h = div_node_id.offsetTop;
            var height = height_node + h;
            div_level_id.style.height = height + 5 + 'px';
        });
    };


    //функция расширения последнего уровня
    var increaseLevel = function() {
        var q = 0;
        $.each(mas_data_level, function (i, elem) {
            q = q + 1;
        });

        var last_level = null;//id последнего уровня
        for (var i = 0; i < q; i++) {
            $.each(mas_data_level, function (j, elem) {
                if (elem.parent_level == last_level){
                    last_level = elem.id_level;
                }
            });
        }

        var div_level = document.getElementById('level_'+ last_level);
        var height_div_level = div_level.clientHeight;

        var visual_diagram = document.getElementById('visual-diagram');
        var h_visual_diagram = visual_diagram.clientHeight;

        var top_layer = document.getElementById('top_layer');
        var h_top_layer = top_layer.clientHeight;

        var div_level_description = document.getElementById('level_description_'+ last_level);
        if (h_top_layer < h_visual_diagram){
            div_level_description.style.height = height_div_level + h_visual_diagram - h_top_layer  + 'px';
        }
    }


    //функция сохранения расположения элемента
    var saveIndent = function(node_id, indent_x, indent_y) {
        $.ajax({
            //переход на экшен левел
            url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
            '/tree-diagrams/save-indent'?>",
            type: "post",
            data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&node_id=" + node_id +
            "&indent_x=" + indent_x + "&indent_y=" + indent_y,
            dataType: "json",
            success: function (data) {
                if (data['success']) {
                    //console.log("x = " + data['indent_x']);
                    //console.log("y = " + data['indent_y']);
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    };


    //функция расположения комментариев событий
    var arrangeEventComment = function(comment_id) {
        var comment = document.getElementById("node_comment_" + comment_id);
        var width_comment = comment.offsetWidth;
        var event = document.getElementById("node_" + comment_id);
        var node_top = event.offsetTop;
        var level  = event.offsetParent;
        var width_level  = level.offsetWidth;

        comment.style.left = width_level - width_comment + 'px';
        comment.style.top = node_top + 'px';
    }


    //функция расположения комментариев событий
    var arrangeLevelComment = function(comment_id) {
        var comment = document.getElementById("level_comment_" + comment_id);
        var width_comment = comment.offsetWidth;

        var level  = document.getElementById("level_description_" + comment_id);
        var width_level  = level.offsetWidth;

        comment.style.left = width_level - width_comment + 'px';
        comment.style.top = 2 + 'px';
    }


    // Равномерное раcпределение всех объектов в виде дерева
    $(document).ready(function() {
        var id_node_any;
        $.each(mas_location, function (j, elem) {
            $(".node").each(function(i) {
                var node = $(this).attr('id');
                var node_id = parseInt(node.match(/\d+/));

                if (elem.id == node_id) {
                    id_node_any = node;
                    $(this).css({
                        left: parseInt(elem.indent_x),
                        top: parseInt(elem.indent_y)
                    });
                }
            });
        });

        // отрисовка
        if (id_node_any != null){
            mousemoveNode(id_node_any);
            increaseLevel();//расширение последнего уровня
            // Обновление формы редактора
            instance.repaintEverything();
        }




        //распределение комментариев событий
        $(".div-event-comment").each(function(i) {
            var elem = $(this).attr('id');
            var comment_id = parseInt(elem.match(/\d+/));

            arrangeEventComment(comment_id);
        });

        //распределение комментариев уровней
        $(".div-level-comment").each(function(i) {
            var elem = $(this).attr('id');
            var comment_id = parseInt(elem.match(/\d+/));

            arrangeLevelComment(comment_id);
        });


    });


    //сохранение расположения элемента
    $(document).on('mouseup', '.node', function() {
        if (!guest) {
            var node = $(this).attr('id');
            var node_id = parseInt(node.match(/\d+/));
            var indent_x = $(this).position().left;
            var indent_y = $(this).position().top;

            //если отступ элемента не отрицательный
            if (indent_y >= 0){
                saveIndent(node_id, indent_x, indent_y);
            } else {
                //если отступ элемента недостаточный чтобы вернуться в свой ровень (стоит на границе)
                //тогда indent_y делаем нулевым
                if (indent_y >= (($(this).height() + 3)*-1 )) {
                    saveIndent(node_id, indent_x, 0);
                }
            }
        }
    });


    $(document).on('mousemove', '.div-event', function() {
        var id_node = $(this).attr('id');
        mousemoveNode(id_node);
        increaseLevel();//расширение последнего уровня
        //------------------------------------------
        // Обновление формы редактора
        instance.repaintEverything();
    });


    $(document).on('mousemove', '.div-mechanism', function() {
        var id_node = $(this).attr('id');
        mousemoveNode(id_node);
        increaseLevel();//расширение последнего уровня
        //------------------------------------------
        // Обновление формы редактора
        instance.repaintEverything();
    });


    $(document).on('mouseout', '.div-event', function() {
        // Обновление формы редактора
        instance.repaintEverything();
    });


    // редактирование события
    $(document).on('click', '.edit-event', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));

            var div_node = document.getElementById("node_" + node_id_on_click);

            var level = div_node.offsetParent.getAttribute('id');
            level_id_on_click = parseInt(level.match(/\d+/));

            var alert = document.getElementById('alert_event_level_id');
            alert.style = style = "display:none;";

            //если событие инициирующее
            if ((div_node.getAttribute("class").search("div-initial-event") >= 0) || (<?= TreeDiagram::CLASSIC_TREE_MODE ?> == <?= $model->mode ?>)) {
                $.each(mas_data_node, function (i, elem) {
                    if (elem.id == node_id_on_click) {
                        document.forms["edit-event-form"].reset();
                        document.forms["edit-event-form"].elements["Node[name]"].value = elem.name;
                        document.forms["edit-event-form"].elements["Node[certainty_factor]"].value = elem.certainty_factor;
                        document.forms["edit-event-form"].elements["Node[description]"].value = elem.description;
                        document.forms["edit-event-form"].elements["Node[level_id]"].value = level_id_on_click;
                        //блокировка изменения левела
                        document.forms["edit-event-form"].elements["Node[level_id]"].style.display = "none";

                        document.getElementById('edit_label_level').style.display = "none";

                        $("#editEventModalForm").modal("show");
                    }
                });
            } else {
                $.each(mas_data_node, function (i, elem) {
                    if (elem.id == node_id_on_click) {
                        document.forms["edit-event-form"].reset();
                        document.forms["edit-event-form"].elements["Node[name]"].value = elem.name;
                        document.forms["edit-event-form"].elements["Node[certainty_factor]"].value = elem.certainty_factor;
                        document.forms["edit-event-form"].elements["Node[description]"].value = elem.description;
                        document.forms["edit-event-form"].elements["Node[level_id]"].value = level_id_on_click;
                        //разблокировка изменения левела
                        document.forms["edit-event-form"].elements["Node[level_id]"].style.display = "";

                        document.getElementById('edit_label_level').style.display = "";

                        $("#editEventModalForm").modal("show");
                    }
                });
            }
        }
    });
    // редактирование события на даблклик
    $(document).on('dblclick', '.div-event', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));
            document.getElementById("node_edit_" + node_id_on_click).click();
        }
    });


    // редактирование механизма
    $(document).on('click', '.edit-mechanism', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));

            var div_node = document.getElementById("node_" + node_id_on_click);

            var level = div_node.offsetParent.getAttribute('id');
            level_id_on_click = parseInt(level.match(/\d+/));

            var alert = document.getElementById('alert_mechanism_level_id');
            alert.style = style = "display:none;";

            $.each(mas_data_node, function (i, elem) {
                if (elem.id == node_id_on_click) {
                    document.forms["edit-mechanism-form"].reset();
                    document.forms["edit-mechanism-form"].elements["Node[name]"].value = elem.name;
                    document.forms["edit-mechanism-form"].elements["Node[description]"].value = elem.description;
                    document.forms["edit-mechanism-form"].elements["Node[level_id]"].value = level_id_on_click;
                    //разблокировка изменения левела
                    document.forms["edit-mechanism-form"].elements["Node[level_id]"].style.display = "";

                    $("#editMechanismModalForm").modal("show");
                }
            });
        }
    });
    // редактирование механизма на даблклик
    $(document).on('dblclick', '.div-mechanism', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));
            document.getElementById("node_edit_" + node_id_on_click).click();
        }
    });


    // редактирование уровня
    $(document).on('click', '.edit-level', function() {
        if (!guest) {
            level_id_on_click = parseInt($(this).attr('id').match(/\d+/));
            $.each(mas_data_level, function (i, elem) {
                if (elem.id_level == level_id_on_click) {
                    document.forms["edit-level-form"].reset();
                    document.forms["edit-level-form"].elements["Level[name]"].value = elem.name;
                    document.forms["edit-level-form"].elements["Level[description]"].value = elem.description;

                    $("#editLevelModalForm").modal("show");
                }
            });
        }
    });
    // редактирование уровня на даблклик
    $(document).on('dblclick', '.div-level-name', function() {
        if (!guest) {
            level_id_on_click = parseInt($(this).attr('id').match(/\d+/));
            document.getElementById("level_edit_" + level_id_on_click).click();
        }
    });


    // удаление события
    $(document).on('click', '.del-event', function() {
        if (!guest) {
            var del = $(this).attr('id');
            node_id_on_click = parseInt(del.match(/\d+/));
            $("#deleteEventModalForm").modal("show");
        }
    });


    // удаление механизма
    $(document).on('click', '.del-mechanism', function() {
        if (!guest) {
            var del = $(this).attr('id');
            node_id_on_click = parseInt(del.match(/\d+/));
            $("#deleteMechanismModalForm").modal("show");
        }
    });


    // удаление уровня
    $(document).on('click', '.del-level', function() {
        if (!guest) {
            var del = $(this).attr('id');
            level_id_on_click = parseInt(del.match(/\d+/));

            var number;
            var mas_level = document.getElementsByClassName("div-level");
            $.each(mas_level, function (i, elem) {
                var id_level = parseInt(elem.getAttribute('id').match(/\d+/));
                if (level_id_on_click == id_level) {
                    number = i;
                }
            });
            // Если уровень начальный то выводим сообщение
            if (number == 0) {
                var alert_initial_level = document.getElementById('alert_level_initial_level');
                alert_initial_level.style = "";
            } else {
                var alert_initial_level = document.getElementById('alert_level_initial_level');
                alert_initial_level.style = "display:none;";
            }

            var del_level = document.getElementById('level_' + level_id_on_click);
            var mas_node = del_level.getElementsByClassName("node");
            // Если на уровне есть элементы то выводим сообщение
            if (mas_node.length != 0) {
                var alert_delete_level = document.getElementById('alert_level_delete_level');
                alert_delete_level.style = "";
            } else {
                var alert_delete_level = document.getElementById('alert_level_delete_level');
                alert_delete_level.style = "display:none;";
            }

            $("#deleteLevelModalForm").modal("show");
        }
    });


    // перемещение уровня
    $(document).on('click', '.move-level', function() {
        if (!guest) {
            var del = $(this).attr('id');
            level_id_on_click = parseInt(del.match(/\d+/));

            //ид уровня предшествующего level_id_on_click
            var parent_level_id;

            //список уровней содержащихся в dropdownlist
            var dropdownlist = document.getElementById("level-movement_level");

            //поиск уровня предшествующего level_id_on_click
            $.each(mas_data_level, function (i, elem) {
                if (elem.id_level == level_id_on_click){
                    parent_level_id = elem.parent_level;
                }
            });

            //делаем весь список уровней видимым
            for (let i = 0; i < dropdownlist.length; i++) {
                dropdownlist.options[i].style.display = ""
            }

            for (let i = 0; i < dropdownlist.length; i++) {
                //скрываем перемещаеммый уровень
                if(dropdownlist.options[i].value == level_id_on_click){
                    dropdownlist.options[i].style.display = "none"
                }
                //скрываем уровень до перемещаемого
                if(dropdownlist.options[i].value == parent_level_id){
                    dropdownlist.options[i].style.display = "none"
                }
            }

            //очищаем уровень выбранный по умолчанию
            dropdownlist.options.selectedIndex = -1;

            $("#moveLevelModalForm").modal("show");
        }
    });


    // добавление параметра
    $(document).on('click', '.add-parameter', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));
            $("#addParameterModalForm").modal("show");
        }
    });


    // изменение параметра
    $(document).on('click', '.edit-parameter', function() {
        if (!guest) {
            var parameter = $(this).attr('id');
            parameter_id_on_click = parseInt(parameter.match(/\d+/));
            $.each(mas_data_parameter, function (i, elem) {
                if (elem.id == parameter_id_on_click) {
                    document.forms["edit-parameter-form"].reset();
                    document.forms["edit-parameter-form"].elements["Parameter[name]"].value = elem.name;
                    document.forms["edit-parameter-form"].elements["Parameter[description]"].value = elem.description;
                    document.forms["edit-parameter-form"].elements["Parameter[operator]"].value = elem.operator;
                    document.forms["edit-parameter-form"].elements["Parameter[value]"].value = elem.value;
                    $("#editParameterModalForm").modal("show");
                }
            });
        }
    });


    // удаление параметра
    $(document).on('click', '.del-parameter', function() {
        if (!guest) {
            var parameter = $(this).attr('id');
            parameter_id_on_click = parseInt(parameter.match(/\d+/));
            $("#deleteParameterModalForm").modal("show");
            // Обновление формы редактора
            instance.repaintEverything();
        }
    });


    //проверка диаграммы на корректность
    $('#nav_correctness').on('click', function() {
        $.ajax({
            //переход на экшен левел
            url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
            '/tree-diagrams/correctness/' . $model->id ?>",
            type: "post",
            data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>",
            dataType: "json",
            success: function (data) {
                if (data['success']) {
                    var not_connected = data['not_connected'];
                    var empty_level = data['empty_level'];
                    var level_without_mechanism = data['level_without_mechanism'];

                    var message = "";

                    $.each(not_connected, function (i, elem) {
                        message = message + '<p style="font-size: 14px">' +
                            "<?php echo Yii::t('app', 'TEXT_NODE'); ?>" + '<b>' + elem.name + '</b>'
                            + "<?php echo Yii::t('app', 'TEXT_IS_NOT_LINKED_TO_ANY_OTHER_NODES'); ?>" + '</p>';
                    });

                    $.each(empty_level, function (i, elem) {
                        message = message + '<p style="font-size: 14px">' +
                            "<?php echo Yii::t('app', 'TEXT_LEVEL'); ?>" + '<b>' + elem.name + '</b>'
                            + "<?php echo Yii::t('app', 'TEXT_DOES_NOT_CONTAIN_ANY_ITEMS'); ?>" + '</p>';
                    });

                    $.each(level_without_mechanism, function (i, elem) {
                        message = message + '<p style="font-size: 14px">' +
                            "<?php echo Yii::t('app', 'TEXT_LEVEL'); ?>" + '<b>' + elem.name + '</b>'
                            + "<?php echo Yii::t('app', 'TEXT_DOES_NOT_CONTAIN_ANY_MECHANISM'); ?>" + '</p>';
                    });

                    if (message == ""){
                        var result = '<h4>' + "<?php echo Yii::t('app', 'NO_ERRORS_WERE_FOUND'); ?>" + '</h4>';;
                    } else {
                        var result = '<h4>' + "<?php echo Yii::t('app', 'TEXT_WHEN_CHECKING_THE_CORRECTNESS'); ?>" + '</h4>';
                    }

                    var div = document.getElementById('message-verification-text');
                    div.innerHTML = result + message;
                    $("#viewMessageErrorsWhenCheckingTheChartModalForm").modal("show");
                }
            },
            error: function () {
                alert('Error!');
            }
        });
    });


    //равномерное распределение и выравнивание элементов по диаграмме
    $('#nav_alignment').on('click', function() {
        var id_node_any; //любой id узла для выравнивания в конце

        var id_initial_node = 0; //id начального события
        //поиск начального события
        $(".div-initial-event").each(function(i) {
            id_initial_node = $(this).attr('id');
            $(this).css({
                left: 20,
                top: 0
            });
        });

        //высота начального node
        var height_initial_node;

        // ширина и высота элемента + отступ
        var width_node = 200 + 20;
        var height_node = 200 + 60;

        //переменные отступа
        var left = 0;
        var top = 0;

        var indent_mechanism = 0;

        if (id_initial_node != 0){
            //размещение начального события и его потомков
            $(".div-level-description").each(function(i) {
                var id_level = $(this).attr('id');

                left = 0;
                top = 0;

                $(".node").each(function(i) {
                    var id_node = $(this).attr('id');
                    id_node_any = id_node;

                    var node = document.getElementById(id_node);
                    var n_initial_node = parseInt(id_initial_node.match(/\d+/));

                    var id_parent_node = node.getAttribute("parent_node");

                    //поиск родительского элемента
                    var parent_node = document.getElementById("node_" + id_parent_node);
                    if (parent_node != null){
                        var parent_node_left = parent_node.offsetLeft;
                        var parent_node_top = parent_node.offsetTop;
                    }

                    var level_parent = node.offsetParent;
                    var id_level_parent = level_parent.getAttribute('id');

                    // curent_ первоначальное положение элемента + 20 отступ от края
                    var current_left = 20 + $(this).position().left;
                    var current_top = 20 + $(this).position().top;

                    //если поле уровней совпадает
                    if (id_level_parent == id_level){
                        //если начальное событие
                        if (id_node == id_initial_node){
                            height_initial_node = node.offsetHeight;
                            $(this).css({
                                left: current_left + left,
                                top: current_top + top
                            });
                            top = top + height_node + height_initial_node;

                            //если это потомки начального события
                        } else if (id_parent_node == n_initial_node){
                            var cl = node.className.indexOf('div-mechanism');
                            if (cl == -1) {
                                indent_mechanism = 0;
                            } else {
                                indent_mechanism = 68;
                            }

                            $(this).css({
                                left: parent_node_left + left + indent_mechanism,
                                top: parent_node_top + top
                            });
                            left = left + width_node;

                            var classList = node.classList;
                            classList.add('current'); // добавить класс для потомка
                        }
                    }
                });
            });

            top = top + height_node;

            var col = 0;
            var sum_col = 0;

            //нахождение максимальной высоты node
            var max_height_node = 0;
            $(".node").each(function(i) {
                var id_node = $(this).attr('id');
                var node = document.getElementById(id_node);

                if ((id_node != id_initial_node) && (max_height_node < node.offsetHeight)){
                    max_height_node = node.offsetHeight;
                }
            });

            do {
                sum_col = 0;
                var count = 0;

                //просматриваем всех потомков
                $(".current").each(function(i) {
                    var id_current = $(this).attr('id');
                    var n_current = parseInt(id_current.match(/\d+/));
                    var current = document.getElementById(id_current);

                    col = 0;
                    count++;
                    left = 0;

                    //нахождение количества потомков у выбранного потомка
                    $(".node").each(function(i) {
                        var id_node = $(this).attr('id');
                        var node = document.getElementById(id_node);
                        var id_parent_node = node.getAttribute("parent_node");
                        var level_parent = node.offsetParent;
                        var id_level_parent = level_parent.getAttribute('id');
                        if (id_parent_node == n_current){
                            col = col + 1;
                        }
                    });

                    if (count == 1){
                        sum_col = col;
                    } else if (col == 0){
                        sum_col = sum_col - 1;
                    }

                    var sdvig;

                    if (sum_col < 1){
                        sdvig = 0;
                    } else {
                        sdvig = sum_col - 1;
                    }

                    if (count > 1){
                        left = left + width_node * sdvig;
                    }

                    if ((count > 1)&&(col > 1)){
                        sum_col = sum_col + col - 1;
                    }
                    col = 0;


                    $(".div-level-description").each(function(i) {
                        var id_level = $(this).attr('id');

                        $(".node").each(function(i) {
                            var id_node = $(this).attr('id');
                            id_node_any = id_node;
                            var node = document.getElementById(id_node);
                            var id_parent_node = node.getAttribute("parent_node");

                            //поиск родительского элемента и его параметров
                            var parent_node = document.getElementById("node_" + id_parent_node);
                            if (parent_node != null){
                                var pn = parent_node.className.indexOf('div-mechanism');
                                if (pn == -1) {
                                    indent_mechanism = 0;
                                } else {
                                    indent_mechanism = 68;
                                }

                                var parent_node_left = parent_node.offsetLeft - indent_mechanism;
                                var parent_node_top = parent_node.offsetTop;
                                var parent_node_level_parent = parent_node.offsetParent;
                                var parent_node_id_level_parent = parent_node_level_parent.getAttribute('id');
                            }

                            var level_parent = node.offsetParent;
                            var id_level_parent = level_parent.getAttribute('id');

                            var current_top = 20 + $(this).position().top;

                            if (id_level_parent == id_level) {
                                if (id_parent_node == n_current){
                                    var cl = node.className.indexOf('div-mechanism');
                                    if (cl == -1) {
                                        indent_mechanism = 0;
                                    } else {
                                        indent_mechanism = 68;
                                    }

                                    //если уровень родительского элемента равен уровню в кот.находится элемент
                                    if (parent_node_id_level_parent == id_level_parent){
                                        $(this).css({
                                            left: parent_node_left + left + indent_mechanism,
                                            top: parent_node_top + height_node + max_height_node,
                                        });
                                        left = left + width_node;
                                        //иначе уровни разные
                                    } else {
                                        $(this).css({
                                            left: parent_node_left + left + indent_mechanism,
                                            top: current_top,
                                        });
                                        left = left + width_node;
                                    }
                                    // присваиваем класс у дочернего
                                    var classNode = node.classList;
                                    classNode.add('current'); // добавить класс
                                }
                                // удаляем класс у родителя
                                var classCurrent = current.classList;
                                classCurrent.remove('current'); // удалить класс
                            }
                        });
                    });
                });
                //длина массива
                var a = $(".current").length;
                //повторять до тех пор пока не кончатся .current
            } while ( a != 0 );
        }


        //максимальный отступ
        var max_left = 0;
        //поиск самого максимального отступа
        $(".node").each(function(i) {
            var id_node = $(this).attr('id');
            var node = document.getElementById(id_node);
            var node_left = node.offsetLeft;
            if (node_left > max_left){
                max_left = node_left;
            }
        });

        //если начального события нет
        if (max_left == 0){
            left = 0;
        } else {
            left = max_left + width_node;
        }
        top = 0;

        //размещение не связанных элементов (без родительского)
        $(".div-level-description").each(function(i) {
            var id_level = $(this).attr('id');

            $(".node").each(function(i) {
                var id_node = $(this).attr('id');
                id_node_any = id_node;
                var node = document.getElementById(id_node);
                var id_parent_node = node.getAttribute("parent_node");
                var parent_node = document.getElementById("node_" + id_parent_node);

                var level_parent = node.offsetParent;
                var id_level_parent = level_parent.getAttribute('id');

                // curent_ первоначальное положение элемента + 20 отступ от края
                var current_left = 20 + $(this).position().left;
                var current_top = 20 + $(this).position().top;

                if (id_level_parent == id_level){
                    //если родителя нет
                    if (id_parent_node == ""){
                        $(this).css({
                            left: current_left + left,
                            top: current_top + top
                        });
                        left = left + width_node;

                        var classList = node.classList;
                        classList.add('zero'); // добавить класс
                    }
                }
            });
        });

        top = top + height_node;

        var col = 0;
        var sum_col = 0;

        do {
            sum_col = 0;
            var count = 0;

            $(".zero").each(function(i) {
                var id_current = $(this).attr('id');
                var n_current = parseInt(id_current.match(/\d+/));
                var current = document.getElementById(id_current);

                col = 0;
                count++;
                left = 0;

                $(".node").each(function(i) {
                    var id_node = $(this).attr('id');
                    var node = document.getElementById(id_node);
                    var id_parent_node = node.getAttribute("parent_node");
                    var level_parent = node.offsetParent;
                    var id_level_parent = level_parent.getAttribute('id');
                    if (id_parent_node == n_current){
                        col = col + 1;
                    }
                });

                if (count == 1){
                    sum_col = col;
                } else if (col == 0){
                    sum_col = sum_col - 1;
                }

                var sdvig;

                if (sum_col < 1){
                    sdvig = 0;
                } else {
                    sdvig = sum_col - 1;
                }

                if (count > 1){
                    left = left + width_node * sdvig;
                }
                if ((count > 1)&&(col > 1)){
                    sum_col = sum_col + col - 1;
                }
                col = 0;

                $(".div-level-description").each(function(i) {
                    var id_level = $(this).attr('id');

                    $(".node").each(function(i) {
                        var id_node = $(this).attr('id');
                        id_node_any = id_node;
                        var node = document.getElementById(id_node);
                        var id_parent_node = node.getAttribute("parent_node");
                        var parent_node = document.getElementById("node_" + id_parent_node);
                        if (parent_node != null){
                            var parent_node_left = parent_node.offsetLeft;
                            var parent_node_top = parent_node.offsetTop;
                            var parent_node_level_parent = parent_node.offsetParent;
                            var parent_node_id_level_parent = parent_node_level_parent.getAttribute('id');
                        }

                        var level_parent = node.offsetParent;
                        var id_level_parent = level_parent.getAttribute('id');

                        // curent_ первоначальное положение элемента + 20 отступ от края
                        var current_top = 20 + $(this).position().top;

                        if (id_level_parent == id_level) {
                            if (id_parent_node == n_current){
                                if (parent_node_id_level_parent == id_level_parent){
                                    $(this).css({
                                        left: parent_node_left + left,
                                        top: parent_node_top + top,
                                    });
                                    left = left + width_node;
                                } else {
                                    $(this).css({
                                        left: parent_node_left + left,
                                        top: current_top,
                                    });
                                    left = left + width_node;
                                }
                                // присваиваем класс у дочернему
                                var classNode = node.classList;
                                classNode.add('zero'); // добавить класс
                            }
                            // удаляем класс у родителя
                            var classCurrent = current.classList;
                            classCurrent.remove('zero'); // удалить класс
                        }
                    });
                });
            });
            var a = $(".zero").length;
        } while ( a != 0 );

        // отрисовка
        if (id_node_any != null){
            mousemoveNode(id_node_any);
            increaseLevel();//расширение последнего уровня
            // Обновление формы редактора
            instance.repaintEverything();
        }

        //сохранение местоположения
        $(".node").each(function(i) {
            var node = $(this).attr('id');
            var node_id = parseInt(node.match(/\d+/));
            var indent_x = $(this).position().left;
            var indent_y = $(this).position().top;

            saveIndent(node_id, indent_x, indent_y);
        });

    });


    //отображение комментария или создание если его нет
    $(document).on('click', '.show-event-comment', function() {
        var node = $(this).attr('id');
        node_id_on_click = parseInt(node.match(/\d+/));

        //Поиск комментария
        var comment = document.getElementById("node_comment_" + node_id_on_click);

        //если комментария нет то добавляем его
        if (comment == null){
            $("#addEventCommentModalForm").modal("show");
        } else {
            if (comment.style.visibility == 'hidden'){
                comment.style.visibility='visible'
                arrangeEventComment(node_id_on_click);
            } else {
                comment.style.visibility='hidden'
            }
        }
    });


    //изменение комментария
    $(document).on('click', '.edit-event-comment', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));

            //Поиск комментария
            var comment = document.getElementById("node_comment_name_" + node_id_on_click);

            document.forms["edit-event-comment-form"].reset();
            document.forms["edit-event-comment-form"].elements["Node[comment]"].value = comment.innerHTML;
            $("#editEventCommentModalForm").modal("show");
        }
    });


    //удаление комментария
    $(document).on('click', '.del-event-comment', function() {
        if (!guest) {
            var node = $(this).attr('id');
            node_id_on_click = parseInt(node.match(/\d+/));

            $("#deleteEventCommentModalForm").modal("show");
        }
    });

    //скрытие комментария
    $(document).on('click', '.hide-event-comment', function() {
        var node = $(this).attr('id');
        var node_id = parseInt(node.match(/\d+/));

        //Поиск комментария
        var comment = document.getElementById("node_comment_" + node_id);
        comment.style.visibility='hidden'
    });


    //отображение комментария или создание если его нет
    $(document).on('click', '.show-level-comment', function() {
        var level = $(this).attr('id');
        level_id_on_click = parseInt(level.match(/\d+/));

        //Поиск комментария
        var comment = document.getElementById("level_comment_" + level_id_on_click);

        //если комментария нет то добавляем его
        if (comment == null){
            $("#addLevelCommentModalForm").modal("show");
        } else {
            if (comment.style.visibility == 'hidden'){
                comment.style.visibility='visible'
                arrangeLevelComment(level_id_on_click);
            } else {
                comment.style.visibility='hidden'
            }
        }
    });


    //изменение комментария
    $(document).on('click', '.edit-level-comment', function() {
        if (!guest) {
            var level = $(this).attr('id');
            level_id_on_click = parseInt(level.match(/\d+/));

            //Поиск комментария
            var comment = document.getElementById("level_comment_name_" + level_id_on_click);

            document.forms["edit-level-comment-form"].reset();
            document.forms["edit-level-comment-form"].elements["Level[comment]"].value = comment.innerHTML;
            $("#editLevelCommentModalForm").modal("show");
        }
    });


    //удаление комментария
    $(document).on('click', '.del-level-comment', function() {
        if (!guest) {
            var level = $(this).attr('id');
            level_id_on_click = parseInt(level.match(/\d+/));

            $("#deleteLevelCommentModalForm").modal("show");
        }
    });


    //скрытие комментария
    $(document).on('click', '.hide-level-comment', function() {
        var level = $(this).attr('id');
        var level_id = parseInt(level.match(/\d+/));

        //Поиск комментария
        var comment = document.getElementById("level_comment_" + level_id);
        comment.style.visibility='hidden'
    });

</script>


<div class="tree-diagram-visual-diagram">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<div id="visual-diagram" class="visual-diagram col-md-12">
<div id="visual_diagram_field" class="visual-diagram-top-layer">
    <div id="top_layer" class="top">
            <!-- Вывод уровней -->
            <!-- Вывод начального уровня -->
            <?php foreach ($level_model_all as $value): ?>
            <?php if ($value->parent_level == null){ ?>
                <div id="level_<?= $value->id ?>" class="div-level">
                    <div id="level_name_<?= $value->id ?>" class="div-level-name">
                        <div id="level_title_<?= $value->id ?>" class="div-title-name" title="<?= $value->name ?>"><?= $value->name ?></div>
                        <div id="level_del_<?= $value->id ?>" class="del del-level glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                        <div id="level_edit_<?= $value->id ?>" class="edit edit-level glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                        <div id="level_show_comment_<?= $value->id ?>" class="show-level-comment glyphicon-paperclip" title="<?php echo Yii::t('app', 'BUTTON_COMMENT'); ?>"></div>
                    </div>
                    <div id="level_description_<?= $value->id ?>" class="div-level-description">
                        <!--?= $level_value->description ?>-->
                        <!-- Вывод инициирующего события -->
                        <?php foreach ($initial_event_model_all as $initial_event_value): ?>
                            <div id="node_<?= $initial_event_value->id ?>" class="div-event node div-initial-event">
                                <div class="content-event">
                                    <div id="node_name_<?= $initial_event_value->id ?>" class="div-event-name"><?= $initial_event_value->name ?>
                                        <?php if ($initial_event_value->certainty_factor != null){ ?>
                                            (<?= $initial_event_value->certainty_factor ?>)
                                        <?php } ?>
                                    </div>
                                    <div class="ep ep-event glyphicon-share-alt" title="<?php echo Yii::t('app', 'BUTTON_CONNECTION'); ?>"></div>
                                    <div id="node_del_<?= $initial_event_value->id ?>" class="del del-event glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                    <div id="node_edit_<?= $initial_event_value->id ?>" class="edit edit-event glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                    <div id="node_add_parameter_<?= $initial_event_value->id ?>" class="param add-parameter glyphicon-plus" title="<?php echo Yii::t('app', 'BUTTON_ADD'); ?>"></div>
                                    <div id="node_show_comment_<?= $initial_event_value->id ?>" class="show-event-comment glyphicon-paperclip" title="<?php echo Yii::t('app', 'BUTTON_COMMENT'); ?>"></div>
                                </div>

                                <?php foreach ($parameter_model_all as $parameter_value): ?>
                                    <?php if ($parameter_value->node == $initial_event_value->id){ ?>
                                        <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                            <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                            <div class="button-parameter">
                                                <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit edit-parameter glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                <div id="del_parameter_<?= $parameter_value->id ?>" class="del del-parameter glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($initial_event_value->comment != null){ ?>
                                <div id="node_comment_<?= $initial_event_value->id ?>" class="div-event-comment" style="visibility:hidden;">
                                    <div id="node_comment_name_<?= $initial_event_value->id ?>" class="div-comment-name"><?= $initial_event_value->comment ?></div>
                                    <div id="node_edit_comment_<?= $initial_event_value->id ?>" class="edit-event-comment glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                    <div id="node_del_comment_<?= $initial_event_value->id ?>" class="del-event-comment glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                    <div id="node_hide_comment_<?= $initial_event_value->id ?>" class="hide-event-comment glyphicon-eye-close" title="<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>"></div>
                                </div>
                            <?php } ?>
                        <?php endforeach; ?>

                        <?php foreach ($sequence_model_all as $sequence_value): ?>
                            <?php if ($sequence_value->level == $value->id){ ?>
                                <?php $event_id = $sequence_value->node; ?>
                                <?php foreach ($event_model_all as $event_value): ?>
                                    <?php if ($event_value->id == $event_id){ ?>
                                        <div id="node_<?= $event_value->id ?>" class="div-event node" parent_node="<?= $event_value->parent_node ?>">
                                            <div class="content-event">
                                                <div id="node_name_<?= $event_value->id ?>" class="div-event-name"><?= $event_value->name ?>
                                                    <?php if ($event_value->certainty_factor != null){ ?>
                                                        (<?= $event_value->certainty_factor ?>)
                                                    <?php } ?>
                                                </div>
                                                <div class="ep ep-event glyphicon-share-alt" title="<?php echo Yii::t('app', 'BUTTON_CONNECTION'); ?>"></div>
                                                <div id="node_del_<?= $event_value->id ?>" class="del del-event glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                <div id="node_edit_<?= $event_value->id ?>" class="edit edit-event glyphicon-pencil"  title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                <div id="node_add_parameter_<?= $event_value->id ?>" class="param add-parameter glyphicon-plus" title="<?php echo Yii::t('app', 'BUTTON_ADD'); ?>"></div>
                                                <div id="node_show_comment_<?= $event_value->id ?>" class="show-event-comment glyphicon-paperclip" title="<?php echo Yii::t('app', 'BUTTON_COMMENT'); ?>"></div>
                                            </div>

                                            <?php foreach ($parameter_model_all as $parameter_value): ?>
                                                <?php if ($parameter_value->node == $event_value->id){ ?>
                                                    <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                                        <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                                        <div class="button-parameter">
                                                            <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit edit-parameter glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                            <div id="del_parameter_<?= $parameter_value->id ?>" class="del del-parameter glyphicon-trash"  title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if ($event_value->comment != null){ ?>
                                            <div id="node_comment_<?= $event_value->id ?>" class="div-event-comment"  style="visibility:hidden;">
                                                <div id="node_comment_name_<?= $event_value->id ?>" class="div-comment-name"><?= $event_value->comment ?></div>
                                                <div id="node_edit_comment_<?= $event_value->id ?>" class="edit-event-comment glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                <div id="node_del_comment_<?= $event_value->id ?>" class="del-event-comment glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                <div id="node_hide_comment_<?= $event_value->id ?>" class="hide-event-comment glyphicon-eye-close" title="<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>"></div>
                                            </div>
                                        <?php } ?>

                                    <?php } ?>
                                <?php endforeach; ?>
                            <?php } ?>
                        <?php endforeach; ?>

                        <?php if ($value->comment != null){ ?>
                            <div id="level_comment_<?= $value->id ?>" class="div-level-comment" style="visibility:hidden;">
                                <div id="level_comment_name_<?= $value->id ?>" class="div-comment-name"><?= $value->comment ?></div>
                                <div id="level_edit_comment_<?= $value->id ?>" class="edit-level-comment glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                <div id="level_del_comment_<?= $value->id ?>" class="del-level-comment glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                <div id="level_hide_comment_<?= $value->id ?>" class="hide-level-comment glyphicon-eye-close" title="<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>"></div>
                            </div>
                        <?php } ?>

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
                            <div id="level_name_<?= $level_value->id ?>" class="div-level-name">
                                <div id="level_title_<?= $level_value->id ?>" class="div-title-name" title="<?= $level_value->name ?>"><?= $level_value->name ?></div>
                                <div id="level_del_<?= $level_value->id ?>" class="del del-level glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                <div id="level_edit_<?= $level_value->id ?>" class="edit edit-level glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                <div id="level_move_<?= $level_value->id ?>" class="move move-level glyphicon-transfer" title="<?php echo Yii::t('app', 'BUTTON_MOVE'); ?>"></div>
                                <div id="level_show_comment_<?= $level_value->id ?>" class="show-level-comment glyphicon-paperclip" title="<?php echo Yii::t('app', 'BUTTON_COMMENT'); ?>"></div>
                            </div>
                            <div id="level_description_<?= $level_value->id ?>" class="div-level-description">
                                <!--?= $level_value->description ?>-->
                                <?php foreach ($sequence_model_all as $sequence_value): ?>
                                    <?php if ($sequence_value->level == $level_value->id){ ?>
                                        <?php $node_id = $sequence_value->node; ?>
                                        <!-- Вывод механизма -->
                                        <?php foreach ($mechanism_model_all as $mechanism_value): ?>
                                            <?php if ($mechanism_value->id == $node_id){ ?>
                                                <div id="node_<?= $mechanism_value->id ?>" parent_node="<?= $mechanism_value->parent_node ?>"
                                                    class="div-mechanism node" title="<?= $mechanism_value->description ?>">
                                                    <div id="node_name_<?= $mechanism_value->id ?>" class="div-mechanism-name"><?= $mechanism_value->name ?></div>
                                                    <div class="div-mechanism-m">M</div>
                                                    <div class="ep ep-mechanism glyphicon-share-alt" title="<?php echo Yii::t('app', 'BUTTON_CONNECTION'); ?>"></div>
                                                    <div id="node_del_<?= $mechanism_value->id ?>" class="del del-mechanism glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                    <div id="node_edit_<?= $mechanism_value->id ?>" class="edit edit-mechanism glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                </div>
                                            <?php } ?>
                                        <?php endforeach; ?>
                                        <!-- Вывод событий -->
                                        <?php foreach ($event_model_all as $event_value): ?>
                                            <?php if ($event_value->id == $node_id){ ?>
                                                <div id="node_<?= $event_value->id ?>" class="div-event node" parent_node = "<?= $event_value->parent_node ?>">
                                                    <div class="content-event">
                                                        <div id="node_name_<?= $event_value->id ?>" class="div-event-name"><?= $event_value->name ?>
                                                            <?php if ($event_value->certainty_factor != null){ ?>
                                                                (<?= $event_value->certainty_factor ?>)
                                                            <?php } ?>
                                                        </div>
                                                        <div class="ep ep-event glyphicon-share-alt"  title="<?php echo Yii::t('app', 'BUTTON_CONNECTION'); ?>"></div>
                                                        <div id="node_del_<?= $event_value->id ?>" class="del del-event glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                        <div id="node_edit_<?= $event_value->id ?>" class="edit edit-event glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                        <div id="node_add_parameter_<?= $event_value->id ?>" class="param add-parameter glyphicon-plus" title="<?php echo Yii::t('app', 'BUTTON_ADD'); ?>"></div>
                                                        <div id="node_show_comment_<?= $event_value->id ?>" class="show-event-comment glyphicon-paperclip" title="<?php echo Yii::t('app', 'BUTTON_COMMENT'); ?>"></div>
                                                    </div>

                                                    <?php foreach ($parameter_model_all as $parameter_value): ?>
                                                        <?php if ($parameter_value->node == $event_value->id){ ?>
                                                            <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                                                <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                                                <div class="button-parameter">
                                                                    <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit edit-parameter glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                                    <div id="del_parameter_<?= $parameter_value->id ?>" class="del del-parameter glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php endforeach; ?>
                                                </div>

                                                <?php if ($event_value->comment != null){ ?>
                                                    <div id="node_comment_<?= $event_value->id ?>" class="div-event-comment"  style="visibility:hidden;">
                                                        <div id="node_comment_name_<?= $event_value->id ?>" class="div-comment-name"><?= $event_value->comment ?></div>
                                                        <div id="node_edit_comment_<?= $event_value->id ?>" class="edit-event-comment glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                                        <div id="node_del_comment_<?= $event_value->id ?>" class="del-event-comment glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                                        <div id="node_hide_comment_<?= $event_value->id ?>" class="hide-event-comment glyphicon-eye-close" title="<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>"></div>
                                                    </div>
                                                <?php } ?>

                                            <?php } ?>
                                        <?php endforeach; ?>
                                    <?php } ?>
                                <?php endforeach; ?>

                                <?php if ($level_value->comment != null){ ?>
                                    <div id="level_comment_<?= $level_value->id ?>" class="div-level-comment" style="visibility:hidden;">
                                        <div id="level_comment_name_<?= $level_value->id ?>" class="div-comment-name"><?= $level_value->comment ?></div>
                                        <div id="level_edit_comment_<?= $level_value->id ?>" class="edit-level-comment glyphicon-pencil" title="<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>"></div>
                                        <div id="level_del_comment_<?= $level_value->id ?>" class="del-level-comment glyphicon-trash" title="<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>"></div>
                                        <div id="level_hide_comment_<?= $level_value->id ?>" class="hide-level-comment glyphicon-eye-close" title="<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>"></div>
                                    </div>
                                <?php } ?>

                            </div>
                        </div>
                        <?php $a = $level_value->id; ?>
                        <?php break 1; ?>
                    <?php } ?>
                <?php endforeach; ?>
                <?php $i = $i + 1; ?>
            <?php } while ($i <> $level_model_count); ?>
        <?php } ?>
    </div>
</div>
</div>