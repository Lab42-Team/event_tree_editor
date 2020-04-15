<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;
use app\modules\editor\models\Node;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>

<!-- Модальное окно добавления нового уровня -->
<?php Modal::begin([
    'id' => 'addEventModalForm',
    'header' => '<h3>' . Yii::t('app', 'EVENT_ADD_NEW_EVENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-event-button").click(function(e) {
                var form = $("#add-event-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/tree-diagrams/add-event/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addEventModalForm").modal("hide");

                            if (data['type'] == <?= Node::INITIAL_EVENT_TYPE ?>){
                                var div_level_layer = document.getElementById('level_description_' + data['id_level']);

                                var div_initial_event = document.createElement('div');
                                div_initial_event.id = 'node_' + data['id'];
                                div_initial_event.className = 'div-event node div-initial-event';
                                div_level_layer.append(div_initial_event);

                                var div_ep = document.createElement('div');
                                div_ep.className = 'ep' ;
                                div_initial_event.append(div_ep);

                                var div_initial_event_name = document.createElement('div');
                                div_initial_event_name.id = 'node_name_' + data['id'];
                                div_initial_event_name.className = 'div-event-name' ;
                                div_initial_event_name.innerHTML = data['name'];
                                div_initial_event.append(div_initial_event_name);
                            } else {
                                var div_level_layer = document.getElementById('level_description_' + data['id_level']);

                                var div_event = document.createElement('div');
                                div_event.id = 'node_' + data['id'];
                                div_event.className = 'div-event node';
                                div_level_layer.append(div_event);

                                var div_ep = document.createElement('div');
                                div_ep.className = 'ep' ;
                                div_event.append(div_ep);

                                var div_event_name = document.createElement('div');
                                div_event_name.id = 'node_name_' + data['id'];
                                div_event_name.className = 'div-event-name' ;
                                div_event_name.innerHTML = data['name'];
                                div_event.append(div_event_name);
                            }

                            document.getElementById('add-event-form').reset();
                            document.getElementById("pjax-sequence-mas-button").click();

                            //применяем к новым элементам свойства plumb
                            //находим DOM элемент description уровня (идентификатор div level_description)
                            var div_level_id = document.getElementById('level_description_'+ data['id_level']);
                            var g_name = 'group'+ data['id_level']; //определяем имя группы
                            var grp = instance.getGroup(g_name);//определяем существует ли группа с таким именем
                            if (grp == 0){
                            //если группа не существует то создаем группу с определенным именем group_name
                                instance.addGroup({
                                    el: div_level_id,
                                    id: g_name,
                                    draggable: false, //перетаскивание группы
                                    //constrain: true, //запрет на перетаскивание элементов за группу (false перетаскивать можно)
                                    dropOverride:true,
                                });
                            }
                            //находим DOM элемент node (идентификатор div node)
                            var div_node_id = document.getElementById('node_'+ data['id']);
                            //делаем node перетаскиваемым
                            instance.draggable(div_node_id);
                            //добавляем элемент div_node_id в группу с именем group_name
                            instance.addToGroup(g_name, div_node_id);


                            instance.makeSource(div_node_id, {
                                filter: ".ep",
                                anchor: "Bottom",
                            });

                            instance.makeTarget(div_node_id, {
                                dropOptions: { hoverClass: "dragHover" },
                                anchor: "Top",
                                allowLoopback: false, // Нельзя создать кольцевую связь
                                maxConnections: -1,
                            });


                            var level = parseInt(data['id_level'], 10);
                            var node = data['id'];
                            var removed = sequence_mas.push([level, node]);
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-event-form", data);
                        }
                    },
                    error: function() {
                        alert('Error!');
                    }
                });
            });
        });
    </script>

<?php $form = ActiveForm::begin([
    'id' => 'add-event-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($node_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($node_model, 'level_id')->dropDownList($array_levels) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'add-event-button',
        'class' => 'btn-success',
        'style' => 'margin:5px'
    ]
]); ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_CANCEL'),
    'options' => [
        'class' => 'btn-danger',
        'style' => 'margin:5px',
        'data-dismiss'=>'modal'
    ]
]); ?>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>




<!-- Модальное окно изменения нового события -->
<?php Modal::begin([
    'id' => 'editEventModalForm',
    'header' => '<h3>' . Yii::t('app', 'EVENT_EDIT_EVENT') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#edit-event-button").click(function(e) {
            var form = $("#edit-event-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/edit-event/' . $model->id ?>",
                type: "post",
                data: form.serialize() + "&node_id_on_click=" + node_id_on_click + "&level_id_on_click=" + level_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#editEventModalForm").modal("hide");

                        console.log(mas_data_node);

                        $.each(mas_data_node, function (i, elem) {
                            if (elem.id == data['id']){
                                mas_data_node[i].name = data['name'];
                                mas_data_node[i].description = data['description'];
                            }
                        });

                        if (level_id_on_click == data['id_level']){
                            var div_event_name = document.getElementById('node_name_' + data['id']);
                            div_event_name.innerHTML = data['name'];
                        } else {
                            var div_event = document.getElementById('node_' + data['id']);
                            var new_div_event = div_event.cloneNode(true); // клонировать сообщение
                            var div_level_layer = document.getElementById('level_description_'+ data['id_level']);
                            instance.removeFromGroup(div_event);

                            instance.deleteConnectionsForElement(div_event);//визуально убираем соединения

                            div_event.remove(); // удаляем старый node

                            div_level_layer.append(new_div_event); // разместить клонированный элемент в новый уровень

                            //делаем новый node перетаскиваемым
                            instance.draggable(new_div_event);

                            //добавляем элемент new_div_event в группу с именем g_name
                            var g_name = 'group'+ data['id_level']; //определяем имя группы
                            instance.addToGroup(g_name, new_div_event);//добавляем в группу

                            instance.makeSource(new_div_event, {
                                filter: ".ep",
                                anchor: "Bottom",
                            });

                            instance.makeTarget(new_div_event, {
                                dropOptions: { hoverClass: "dragHover" },
                                anchor: "Top",
                                allowLoopback: false, // Нельзя создать кольцевую связь
                                maxConnections: -1,
                            });

                            //----------удаляем все соединения
                            instance.deleteEveryEndpoint();
                            //----------восстанавливаем нужные соединения
                            $.each(mas_data_node, function (i, elem_node) {
                                //убираем входящие
                                if (data['id'] == elem_node.id){
                                    mas_data_node[i].parent_node = null;
                                }
                                //убираем изходящие
                                if (data['id'] == elem_node.parent_node){
                                    mas_data_node[i].parent_node = null;
                                }
                            });

                            $.each(mas_data_node, function (j, elem_node) {
                                if (elem_node.parent_node != null){
                                    instance.connect({
                                        source: "node_" + elem_node.parent_node,
                                        target: "node_" + elem_node.id,
                                    });
                                }
                            });
                            //-----------------------------

                            document.getElementById("pjax-sequence-mas-button").click();

                            //заносим изменения в массив sequence_mas
                            var level = parseInt(data['id_level'], 10);
                            var node = data['id'];
                            var pos_i = 0;
                            $.each(sequence_mas, function (i, mas) {
                                $.each(mas, function (j, elem) {
                                    //второй элемент это id узла события или механизма
                                    if (j == 1) {
                                        if (elem == node){
                                            pos_i = i;
                                        }
                                    };
                                });
                            });
                            sequence_mas[pos_i] = [level, node];
                        }
                        document.getElementById('edit-event-form').reset();
                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#edit-event-form", data);
                    }
                },
                error: function() {
                    alert('Error!');
                }
            });
        });
    });
</script>

<?php $form = ActiveForm::begin([
    'id' => 'edit-event-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<?= Alert::widget([
    'options' => ['class' => 'edit-event-alert alert-warning'],
    'closeButton' => false
]); ?>

<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($node_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($node_model, 'level_id')->dropDownList($array_levels)->label(); ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_SAVE'),
    'options' => [
        'id' => 'edit-event-button',
        'class' => 'btn-success',
        'style' => 'margin:5px'
    ]
]); ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_CANCEL'),
    'options' => [
        'class' => 'btn-danger',
        'style' => 'margin:5px',
        'data-dismiss'=>'modal'
    ]
]); ?>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>