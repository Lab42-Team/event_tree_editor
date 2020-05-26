<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\main\models\Lang;

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


<?php
// создаем массив из соотношения level и node для передачи в jsplumb
$sequence_mas = array();
foreach ($sequence_model_all as $s){
    array_push($sequence_mas, [$s->level, $s->node]);
}

// создаем массив из соотношения id и parent_node для передачи в jsplumb
$node_mas = array();
foreach ($node_model_all as $n){
    array_push($node_mas, [$n->id, $n->parent_node, $n->name, $n->description]);
}

$level_mas = array();
foreach ($level_model_all as $l){
    array_push($level_mas, [$l->id, $l->parent_level, $l->name, $l->description]);
}

$parameter_mas = array();
foreach ($parameter_model_all as $p){
    array_push($parameter_mas, [$p->id, $p->name, $p->description, $p->operator, $p->value]);
}
?>


<?= $this->render('_modal_form_level_editor', [
    'model' => $model,
    'level_model' => $level_model,
]) ?>

<?= $this->render('_modal_form_relationship', [
    'model' => $model,
]) ?>

<?= $this->render('_modal_form_parameter_editor', [
    'parameter_model' => $parameter_model,
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

<?= $this->render('_modal_form_view_message', [
]) ?>


<!-- Подключение скрипта для модальных форм -->
<?php
$this->registerJsFile('/js/modal-form.js', ['position' => yii\web\View::POS_HEAD]);
$this->registerCssFile('/css/visual-diagram.css', ['position'=>yii\web\View::POS_HEAD]);

$this->registerJsFile('/js/jsplumb.js', ['position'=>yii\web\View::POS_HEAD]);  // jsPlumb 2.12.9
//$this->registerJsFile('/js/visual-diagram.js', ['position'=>yii\web\View::POS_HEAD]);
?>




<?php
$initial_event_mas = array();
foreach ($initial_event_model_all as $i){
    array_push($initial_event_mas, [$i->id]);
}
?>





<script type="text/javascript">

    var guest = <?php echo json_encode(Yii::$app->user->isGuest); ?>;//переменная гость определяет пользователь гость или нет

    $(document).ready(function() {
        if (!guest){
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
                if (initial_event.length == 0){
                    //блокировка изменения левела
                    document.forms["add-event-form"].elements["Node[level_id]"].style.display = "none";
                    document.getElementById('label_level').style.display = "none";
                } else {
                    document.forms["add-event-form"].elements["Node[level_id]"].style.display = "";
                    document.getElementById('label_level').style.display = "";
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
        }
    });

    var node_id_on_click = 0;
    var level_id_on_click = 0;
    var parameter_id_on_click = 0;

    var id_target;

    var sequence_mas = <?php echo json_encode($sequence_mas); ?>;//прием массива последовательностей из php
    var node_mas = <?php echo json_encode($node_mas); ?>;//прием массива событий из php
    var level_mas = <?php echo json_encode($level_mas); ?>;//прием массива уровней из php
    var parameter_mas = <?php echo json_encode($parameter_mas); ?>;//прием массива параметров из php

    var message_label = "<?php echo Yii::t('app', 'CONNECTION_DELETE'); ?>";

    var mas_data_level = {};
    var q = 0;
    var id_level = "";
    var name_level = "";
    var description_level = "";
    $.each(level_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            if (j == 0) {id_level = elem;}
            if (j == 2) {name_level = elem;}
            if (j == 3) {description_level = elem;}
            mas_data_level[q] = {
                "id_level":id_level,
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
    $.each(node_mas, function (i, mas) {
        $.each(mas, function (j, elem) {
            //первый элемент это id уровня
            if (j == 0) {id_node = elem;}//записываем id уровня
            //второй элемент это id узла события или механизма
            if (j == 1) {id_parent_node = elem;}//записываем id узла события node или механизма mechanism
            if (j == 2) {name_node = elem;}
            if (j == 3) {description_node = elem;}
            mas_data_node[q] = {
                "id":id_node,
                "parent_node":id_parent_node,
                "name":name_node,
                "description":description_node,
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


        var windows = jsPlumb.getSelector(".node");

        instance.bind("beforeDrop", function (info) {
            var source_node = document.getElementById(info.sourceId);
            var target_node = document.getElementById(info.targetId);

            var source_level = source_node.offsetParent.getAttribute('id');
            var target_level = target_node.offsetParent.getAttribute('id');

            var source_id_level = parseInt(source_level.match(/\d+/));
            var target_id_level = parseInt(target_level.match(/\d+/));


            //построение одномерного массива по порядку следования уровней
            var mas_level_order = {};
            var q = 0;
            var id_l = "";
            var id_p_l = "";
            var next_parent_level = "";
            $.each(level_mas, function (i, mas) {
                $.each(mas, function (j, elem) {
                    //первый элемент это id уровня
                    if (j == 0) {id_l = elem;}//записываем id уровня
                    //второй элемент это id родительского уровня
                    if (j == 1) {id_p_l = elem;}//записываем id узла события node или механизма mechanism
                    if (id_p_l == null){
                        mas_level_order[q] = id_l;
                        next_parent_level = id_l;
                        q = q+1;
                    }
                });
                id_l = "";
                id_p_l = "";
            });
            for (let i = 1; i < level_mas.length; i++) {
                $.each(level_mas, function (i, mas) {
                    $.each(mas, function (j, elem) {
                        //первый элемент это id уровня
                        if (j == 0) {id_l = elem;}//записываем id уровня
                        //второй элемент это id родительского уровня
                        if (j == 1) {id_p_l = elem;}//записываем id узла события node или механизма mechanism
                        if (id_p_l == next_parent_level){
                            mas_level_order[q] = id_l;
                            next_parent_level = id_l;
                            q = q+1;
                        }
                    });
                    id_l = "";
                    id_p_l = "";
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
                $("#viewMessageModalForm").modal("show");
                return false;
            } else {
                // запрет на соединение c элементами на вышестоящем уровне
                if (n_source > n_target){
                    var message = "<?php echo Yii::t('app', 'ELEMENTS_NOT_BE_ASSOCIATED_WITH_OTHER_ELEMENTS_HIGHER_LEVEL'); ?>";
                    document.getElementById("message-text").lastChild.nodeValue = message;
                    $("#viewMessageModalForm").modal("show");
                    return false;
                } else {
                    // запрет на соединение c элементами кроме механизмов на нижестоящем уровне
                    if ((n_source < n_target) && (target_node.getAttribute("class").search("mechanism") == -1)){
                        var message = "<?php echo Yii::t('app', 'LEVEL_MUST_BEGIN_WITH_MECHANISM'); ?>";
                        document.getElementById("message-text").lastChild.nodeValue = message;
                        $("#viewMessageModalForm").modal("show");
                        return false;
                    } else {
                        if(target_node.getAttribute("class").search("div-initial-event") >= 0){
                            var message = "<?php echo Yii::t('app', 'INITIAL_EVENT_SHOULD_NOT_BE_INCOMING_CONNECTIONS'); ?>";
                            document.getElementById("message-text").lastChild.nodeValue = message;
                            $("#viewMessageModalForm").modal("show");
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
                    anchor_top = [ "Perimeter", { shape: "Triangle", rotation: 90 }];
                    anchor_bottom = [ "Perimeter", { shape: "Triangle", rotation: 90 }];
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
                        $("#viewMessageModalForm").modal("show");
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

        // Обработка при наведении на связь показывает заголовок связи "удалить"
        //instance.bind("mouseover", function(connection) {
        //    if (!guest) {
        //        var target_id = connection.targetId;
        //        if (target_id.search("node") >= 0) {
                    //connection.addOverlay(["Label", {
                    //    label: message_label,
                    //    location: 0.5,
                    //    id: "label_connector",
                    //    cssClass: "bLabel"
                    //}]);
        //        }
        //    }
        //});

        // Обработка при отведении от связи скрывает заголовок
        //instance.bind("mouseout", function(connection) {
        //    if (!guest) {
            //    connection.removeOverlay("label_connector");
        //    }
        //});

        // Обработка удаления связи
        instance.bind("click", function(connection) {
            if (!guest) {
                //var source_node = connection.sourceId;
                var target_node = connection.targetId;
                //id_source = parseInt(source_node.match(/\d+/));
                id_target = parseInt(target_node.match(/\d+/));
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


    // Равномерное раcпределение всех объектов в виде дерева
    $(document).ready(function() {
        var id_initial_node;
        //поиск начального
        $(".div-initial-event").each(function(i) {
            id_initial_node = $(this).attr('id');
        });
        //console.log(id_initial_node);

        // ширина и высота элемента + отступ
        var width_node = 200 + 40;
        var height_node = 200 + 80;


        var left = 0;
        var top = 0;

        $(".div-level-description").each(function(i) {
            var id_level = $(this).attr('id');

            left = 0;
            top = 0;

            $(".node").each(function(i) {

                var id_node = $(this).attr('id');

                //console.log("-------");
                //console.log(id_node);
                //console.log("-------");

                var node = document.getElementById(id_node);
                var n_initial_node = parseInt(id_initial_node.match(/\d+/));

                //console.log(node);

                var id_parent_node = node.getAttribute("parent_node");
                //console.log(id_parent_node);
                var parent_node = document.getElementById("node_" + id_parent_node);
                //console.log(parent_node);
                if (parent_node != null){
                    var parent_node_left = parent_node.offsetLeft;
                    var parent_node_top = parent_node.offsetTop;
                    //console.log("left = " + parent_node_left + ":" + "top = " + parent_node_top);
                }
                //console.log("-------");


                var level_parent = node.offsetParent;
                var id_level_parent = level_parent.getAttribute('id');



                // curent_ первоначальное положение элемента + 20 отступ от края
                var current_left = 20 + $(this).position().left;
                var current_top = 20 + $(this).position().top;

                //console.log("-------");
                //console.log(node);
                //console.log(id_parent_node);

                //console.log(id_level_parent);
                //console.log(id_level);

                if (id_level_parent == id_level){
                    //console.log("равно");
                    if (id_node == id_initial_node){
                        $(this).css({
                            left: current_left + left,
                            top: current_top + top
                        });
                        left = left + width_node;
                        top = top + height_node;

                    } else if (id_parent_node == ""){
                        $(this).css({
                            left: current_left + left,
                            top: current_top + top
                        });

                        console.log(node);

                        left = left + width_node;
                        //top = top + height_node;

                        var classList = node.classList;
                        classList.add('current'); // добавить класс


                    } else if (id_parent_node == n_initial_node){
                        $(this).css({
                            left: parent_node_left + left,
                            top: parent_node_top + top
                        });
                        left = left + width_node;

                        var classList = node.classList;
                        classList.add('current'); // добавить класс
                    }
                }


            });

        });

        top = top + height_node;




        do {
            left = 0;
            $(".current").each(function(i) {
                var id_current = $(this).attr('id');
                var n_current = parseInt(id_current.match(/\d+/));
                //console.log(n_current);
                var current = document.getElementById(id_current);



                $(".div-level-description").each(function(i) {
                    var id_level = $(this).attr('id');




                    $(".node").each(function(i) {
                        var id_node = $(this).attr('id');

                        var node = document.getElementById(id_node);

                        var id_parent_node = node.getAttribute("parent_node");

                        var parent_node = document.getElementById("node_" + id_parent_node);
                        //console.log(parent_node);
                        if (parent_node != null){
                            var parent_node_left = parent_node.offsetLeft;
                            var parent_node_top = parent_node.offsetTop;
                            //console.log("left = " + parent_node_left + ":" + "top = " + parent_node_top);
                        }





                        var level_parent = node.offsetParent;
                        var id_level_parent = level_parent.getAttribute('id');


                        // curent_ первоначальное положение элемента + 20 отступ от края
                        //var current_left = 20 + $(this).position().left;
                        //var current_top = 20 + $(this).position().top;

                        if (id_level_parent == id_level) {
                            if (id_parent_node == n_current){
                                $(this).css({
                                    left: parent_node_left +  left,
                                    top: parent_node_top + top
                                });
                                left = left + width_node;





                                // присваиваем класс у дочернему
                                var classNode = node.classList;
                                classNode.add('current'); // добавить класс
                                //console.log("------------");
                                //console.log(node);
                                //console.log("current");
                            }
                            // удаляем класс у родителя
                            var classCurrent = current.classList;
                            classCurrent.remove('current'); // удалить класс
                            //console.log("------------");
                            //console.log(current);
                            //console.log("нету");
                        }

                    });

                    //var classList = node.classList;
                    //classList.add('post'); // добавить класс
                });


            });


                var a = $(".current").length;
                //console.log("--------------------------------");
                //console.log(a);

                //$(".current").each(function(i) {
                //    var id_current = $(this).attr('id');
                //    var n_current = parseInt(id_current.match(/\d+/));
                //    //console.log(n_current);
                //    var current = document.getElementById(id_current);
                //    console.log(current);
                //});
        } while ( a != 0 );





    });







    // Равномерное размещение всех объектов на рабочей области редактора
    /*
    $(document).ready(function() {
        var id_node_any;
        $(".div-level-description").each(function(i) {
            var id_level = $(this).attr('id');

            var left = 0;
            var top = 0;

            //счетчик элементов в линии
            var line_quantity = 0;

            var height_mechanism = 0;

            $(".div-mechanism").each(function(i) {
                var id_node = $(this).attr('id');
                var node = document.getElementById(id_node);
                id_node_any = id_node;

                var level_parent = node.offsetParent;
                var id_level_parent = level_parent.getAttribute('id');

                // ширина и высота элемента + отступ
                //var width_node = node.clientWidth + 40;
                //var height_node = node.clientHeight + 80;
                var width_node = 200 + 40;
                var height_node = 100 + 80;

                // curent_ первоначальное положение элемента + 20 отступ от края
                var current_left = 20 + $(this).position().left;
                var current_top = 20 + $(this).position().top;

                if (id_level_parent == id_level){
                    $(this).css({
                        left: current_left + left,
                        top: current_top + top
                    });
                    left = left + width_node;
                    line_quantity = line_quantity + 1;

                    //если счетчик эементов равен числу то перенос на новую строку
                    if (line_quantity == 10) {
                        top = top + height_node;
                        left = 0;
                        line_quantity = 0;
                        height_mechanism = top;
                    } else {
                        height_mechanism = height_node;
                    }
                }
            });

            left = 0;
            line_quantity = 0;

            $(".div-event").each(function(i) {
                var id_node = $(this).attr('id');
                var node = document.getElementById(id_node);
                id_node_any = id_node;

                var level_parent = node.offsetParent;
                var id_level_parent = level_parent.getAttribute('id');

                // ширина и высота элемента + отступ
                var width_node = 200 + 40;
                var height_node = 100 + 80;

                // curent_ первоначальное положение элемента + 20 отступ от края
                var current_left = 20 + $(this).position().left;
                var current_top = 20 + $(this).position().top;

                if (id_level_parent == id_level){
                    if (node.getAttribute("class").search("div-initial-event") >= 0){
                        $(this).css({
                            left: current_left + left,
                            top: current_top + top
                        });
                        left = 0;
                        top = top + height_node;
                    } else {
                        $(this).css({
                            left: current_left + left,
                            top: height_mechanism + current_top + top
                        });
                        left = left + width_node;
                        line_quantity = line_quantity + 1;

                        //если счетчик эементов равен числу то перенос на новую строку
                        if (line_quantity == 10) {
                            top = top + height_node;
                            left = 0;
                            line_quantity = 0;
                        }
                    }
                }
            });
        });
        // отрисовка
        if (id_node_any != null){
            mousemoveNode(id_node_any);
            // Обновление формы редактора
            instance.repaintEverything();
        }
    });
    */


    $(document).on('mousemove', '.div-event', function() {
        var id_node = $(this).attr('id');
        mousemoveNode(id_node);
        //------------------------------------------
        // Обновление формы редактора
        instance.repaintEverything();
    });

    $(document).on('mousemove', '.div-mechanism', function() {
        var id_node = $(this).attr('id');
        mousemoveNode(id_node);
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
            if (div_node.getAttribute("class").search("div-initial-event") >= 0) {
                $.each(mas_data_node, function (i, elem) {
                    if (elem.id == node_id_on_click) {
                        document.forms["edit-event-form"].reset();
                        document.forms["edit-event-form"].elements["Node[name]"].value = elem.name;
                        document.forms["edit-event-form"].elements["Node[description]"].value = elem.description;
                        document.forms["edit-event-form"].elements["Node[level_id]"].value = level_id_on_click;
                        //блокировка изменения левела
                        document.forms["edit-event-form"].elements["Node[level_id]"].style.display = "none";

                        document.getElementById('label_level').style.display = "none";

                        $("#editEventModalForm").modal("show");
                    }
                });
            } else {
                $.each(mas_data_node, function (i, elem) {
                    if (elem.id == node_id_on_click) {
                        document.forms["edit-event-form"].reset();
                        document.forms["edit-event-form"].elements["Node[name]"].value = elem.name;
                        document.forms["edit-event-form"].elements["Node[description]"].value = elem.description;
                        document.forms["edit-event-form"].elements["Node[level_id]"].value = level_id_on_click;
                        //разблокировка изменения левела
                        document.forms["edit-event-form"].elements["Node[level_id]"].style.display = "";

                        document.getElementById('label_level').style.display = "";

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

    //
    //$(document).on('contextmenu', '.node', function() {
    //    node = $(this).attr('id');
    //    console.log(node);
    //    console.log("------------");
    //    console.log("текущее значение массива");
    //    console.log(mas_data_node);
    //    console.log("--------------------");
    //});



    //$(document).on('contextmenu', '.div-level', function() {
    //    id_level = parseInt($(this).attr('id').match(/\d+/));
    //    console.log("уровень----" + id_level);
    //    var div_level_layer = document.getElementById('level_description_'+ id_level);
    //    //console.log(div_level_layer);
    //    mas_node = div_level_layer.getElementsByClassName("node");
    //
    //    $.each(mas_node, function (i, elem) {
    //        id_node = parseInt(elem.getAttribute('id').match(/\d+/));
    //        console.log(id_node);
    //    });
    //    console.log("mas_data_level----первоначальное----");
    //    console.log(mas_data_level);
    //    console.log("mas_data_node----первоначальное----");
    //    console.log(mas_data_node);
    //    console.log("sequence_mas----первоначальное----");
    //    console.log(sequence_mas);
    //});




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

</script>


<div class="tree-diagram-visual-diagram">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<div class="visual-diagram col-md-12">
<div id="visual_diagram_field" class="visual-diagram-top-layer">
    <div id="top_layer" class="top">
            <!-- Вывод уровней -->
            <!-- Вывод начального уровня -->
            <?php foreach ($level_model_all as $value): ?>
            <?php if ($value->parent_level == null){ ?>
                <div id="level_<?= $value->id ?>" class="div-level">
                    <div id="level_name_<?= $value->id ?>" class="div-level-name">
                        <div id="level_title_<?= $value->id ?>" class="div-title-name" title="<?= $value->name ?>"><?= $value->name ?></div>
                        <div id="level_del_<?= $value->id ?>" class="del-level glyphicon-trash"></div>
                        <div id="level_edit_<?= $value->id ?>" class="edit-level glyphicon-pencil"></div>
                    </div>
                    <div id="level_description_<?= $value->id ?>" class="div-level-description">
                        <!--?= $level_value->description ?>-->
                        <!-- Вывод инициирующего события -->
                        <?php foreach ($initial_event_model_all as $initial_event_value): ?>
                            <div id="node_<?= $initial_event_value->id ?>" class="div-event node div-initial-event">
                                <div class="content-event">
                                    <div id="node_name_<?= $initial_event_value->id ?>" class="div-event-name"><?= $initial_event_value->name ?></div>
                                    <div class="ep ep-event glyphicon-share-alt"></div>
                                    <div id="node_del_<?= $initial_event_value->id ?>" class="del-event glyphicon-trash"></div>
                                    <div id="node_edit_<?= $initial_event_value->id ?>" class="edit-event glyphicon-pencil"></div>
                                    <div id="node_add_parameter_<?= $initial_event_value->id ?>" class="add-parameter glyphicon-plus"></div>
                                </div>

                                <?php foreach ($parameter_model_all as $parameter_value): ?>
                                    <?php if ($parameter_value->node == $initial_event_value->id){ ?>
                                        <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                            <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                            <div class="button-parameter">
                                                <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit-parameter glyphicon-pencil"></div>
                                                <div id="del_parameter_<?= $parameter_value->id ?>" class="del-parameter glyphicon-trash"></div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php endforeach; ?>

                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($sequence_model_all as $sequence_value): ?>
                            <?php if ($sequence_value->level == $value->id){ ?>
                                <?php $event_id = $sequence_value->node; ?>
                                <?php foreach ($event_model_all as $event_value): ?>
                                    <?php if ($event_value->id == $event_id){ ?>
                                        <div id="node_<?= $event_value->id ?>" class="div-event node" parent_node="<?= $event_value->parent_node ?>">
                                            <div class="content-event">
                                                <div id="node_name_<?= $event_value->id ?>" class="div-event-name"><?= $event_value->name ?></div>
                                                <div class="ep ep-event glyphicon-share-alt"></div>
                                                <div id="node_del_<?= $event_value->id ?>" class="del-event glyphicon-trash"></div>
                                                <div id="node_edit_<?= $event_value->id ?>" class="edit-event glyphicon-pencil"></div>
                                                <div id="node_add_parameter_<?= $event_value->id ?>" class="add-parameter glyphicon-plus"></div>
                                            </div>

                                            <?php foreach ($parameter_model_all as $parameter_value): ?>
                                                <?php if ($parameter_value->node == $event_value->id){ ?>
                                                    <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                                        <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                                        <div class="button-parameter">
                                                            <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit-parameter glyphicon-pencil"></div>
                                                            <div id="del_parameter_<?= $parameter_value->id ?>" class="del-parameter glyphicon-trash"></div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php endforeach; ?>

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
                            <div id="level_name_<?= $level_value->id ?>" class="div-level-name">
                                <div id="level_title_<?= $level_value->id ?>" class="div-title-name" title="<?= $level_value->name ?>"><?= $level_value->name ?></div>
                                <div id="level_del_<?= $level_value->id ?>" class="del-level glyphicon-trash"></div>
                                <div id="level_edit_<?= $level_value->id ?>" class="edit-level glyphicon-pencil"></div>
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
                                                    class="div-mechanism node" title="<?= $mechanism_value->name ?>">
                                                    <div class="div-mechanism-m">M</div>
                                                    <div class="ep ep-mechanism glyphicon-share-alt"></div>
                                                    <div id="node_del_<?= $mechanism_value->id ?>" class="del-mechanism glyphicon-trash"></div>
                                                    <div id="node_edit_<?= $mechanism_value->id ?>" class="edit-mechanism glyphicon-pencil"></div>
                                                </div>
                                            <?php } ?>
                                        <?php endforeach; ?>
                                        <!-- Вывод событий -->
                                        <?php foreach ($event_model_all as $event_value): ?>
                                            <?php if ($event_value->id == $node_id){ ?>
                                                <div id="node_<?= $event_value->id ?>" class="div-event node" parent_node = "<?= $event_value->parent_node ?>">
                                                    <div class="content-event">
                                                        <div id="node_name_<?= $event_value->id ?>" class="div-event-name"><?= $event_value->name ?></div>
                                                        <div class="ep ep-event glyphicon-share-alt"></div>
                                                        <div id="node_del_<?= $event_value->id ?>" class="del-event glyphicon-trash"></div>
                                                        <div id="node_edit_<?= $event_value->id ?>" class="edit-event glyphicon-pencil"></div>
                                                        <div id="node_add_parameter_<?= $event_value->id ?>" class="add-parameter glyphicon-plus"></div>
                                                    </div>

                                                    <?php foreach ($parameter_model_all as $parameter_value): ?>
                                                        <?php if ($parameter_value->node == $event_value->id){ ?>
                                                            <div id="parameter_<?= $parameter_value->id ?>" class="div-parameter">
                                                                <div id="parameter_name_<?= $parameter_value->id ?>" class="div-parameter-name"><?= $parameter_value->name ?> <?= $parameter_value->getOperatorName() ?> <?= $parameter_value->value ?></div>
                                                                <div class="button-parameter">
                                                                    <div id="edit_parameter_<?= $parameter_value->id ?>" class="edit-parameter glyphicon-pencil"></div>
                                                                    <div id="del_parameter_<?= $parameter_value->id ?>" class="del-parameter glyphicon-trash"></div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php endforeach; ?>

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
    </div>
</div>
</div>