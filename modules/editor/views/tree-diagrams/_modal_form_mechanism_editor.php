<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels_initial_without app\modules\editor\controllers\TreeDiagramsController */

?>

<script type="text/javascript">
    $(document).on('change', '#node-level_id', function() {
        var alert = document.getElementById('alert_mechanism_level_id');
        alert.style = "";
    });
</script>

    <!-- Модальное окно добавления нового уровня -->
<?php Modal::begin([
    'id' => 'addMechanismModalForm',
    'header' => '<h3>' . Yii::t('app', 'MECHANISM_ADD_NEW_MECHANISM') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-mechanism-button").click(function(e) {
                var form = $("#add-mechanism-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/add-mechanism/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addMechanismModalForm").modal("hide");

                            //создание и вывод в <div> нового элемента
                            var div_level_layer = document.getElementById('level_description_' + data['id_level']);

                            var div_mechanism = document.createElement('div');
                            div_mechanism.id = 'node_' + data['id'];
                            div_mechanism.className = 'div-mechanism node';
                            div_mechanism.title = data['name'];
                            div_level_layer.append(div_mechanism);

                            var div_mechanism_m = document.createElement('div');
                            div_mechanism_m.className = 'div-mechanism-m' ;
                            div_mechanism_m.innerHTML = 'M';
                            div_mechanism.append(div_mechanism_m);

                            var div_ep = document.createElement('div');
                            div_ep.className = 'ep ep-mechanism glyphicon-share-alt' ;
                            div_ep.title = '<?php echo Yii::t('app', 'BUTTON_CONNECTION'); ?>' ;
                            div_mechanism.append(div_ep);

                            var div_del = document.createElement('div');
                            div_del.id = 'node_del_' + data['id'];
                            div_del.className = 'del-mechanism glyphicon-trash' ;
                            div_del.title = '<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>' ;
                            div_mechanism.append(div_del);

                            var div_edit = document.createElement('div');
                            div_edit.id = 'node_edit_' + data['id'];
                            div_edit.className = 'edit-mechanism glyphicon-pencil' ;
                            div_edit.title = '<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>' ;
                            div_mechanism.append(div_edit);

                            document.getElementById('add-mechanism-form').reset();

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
                                anchor: [ "Perimeter", { shape: "Triangle", rotation: 90 }],
                            });

                            instance.makeTarget(div_node_id, {
                                dropOptions: { hoverClass: "dragHover" },
                                anchor: [ "Perimeter", { shape: "Triangle", rotation: 90 }],
                                allowLoopback: false, // Нельзя создать кольцевую связь
                                maxConnections: 1,
                                onMaxConnections: function (info, e) {
                                    var message = "<?php echo Yii::t('app', 'MAXIMUM_CONNECTIONS'); ?>" + info.maxConnections;
                                    alert (message);
                                }
                            });


                            var level = parseInt(data['id_level'], 10);
                            var node = data['id'];
                            var name = data['name'];
                            var parent_node = data['parent_node'];
                            var description = data['description'];
                            var removed = sequence_mas.push([level, node]);

                            var j = 0;
                            $.each(mas_data_node, function (i, elem) {
                                j = j + 1;
                            });
                            mas_data_node[j] = {id:node, parent_node:parent_node, name:name, description:description};

                            //console.log("Массив после добавления механизма = " + data['id']);
                            //console.log(mas_data_node);
                            //console.log("--------------------");

                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-mechanism-form", data);
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
    'id' => 'add-mechanism-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($node_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($node_model, 'level_id')->dropDownList($array_levels_initial_without) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'add-mechanism-button',
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




<!-- Модальное окно изменения нового механизма -->
<?php Modal::begin([
    'id' => 'editMechanismModalForm',
    'header' => '<h3>' . Yii::t('app', 'MECHANISM_EDIT_MECHANISM') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#edit-mechanism-button").click(function(e) {
            var form = $("#edit-mechanism-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/edit-mechanism'?>",
                type: "post",
                data: form.serialize() + "&node_id_on_click=" + node_id_on_click + "&level_id_on_click=" + level_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#editMechanismModalForm").modal("hide");

                        $.each(mas_data_node, function (i, elem) {
                            if (elem.id == data['id']){
                                mas_data_node[i].name = data['name'];
                                mas_data_node[i].description = data['description'];
                            }
                        });

                        if (level_id_on_click == data['id_level']){
                            var div_mechanism = document.getElementById('node_' + data['id']);
                            div_mechanism.title = data['name'];
                        } else {
                            var div_mechanism = document.getElementById('node_' + data['id']);
                            var new_div_mechanism = div_mechanism.cloneNode(true); // клонировать сообщение
                            var div_level_layer = document.getElementById('level_description_'+ data['id_level']);
                            instance.removeFromGroup(div_mechanism);

                            instance.deleteConnectionsForElement(div_mechanism);//визуально убираем соединения

                            div_mechanism.remove(); // удаляем старый node

                            div_level_layer.append(new_div_mechanism); // разместить клонированный элемент в новый уровень

                            new_div_mechanism.title = data['name'];

                            //делаем новый node перетаскиваемым
                            instance.draggable(new_div_mechanism);

                            //добавляем элемент new_div_mechanism в группу с именем g_name
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
                            instance.addToGroup(g_name, new_div_mechanism);//добавляем в группу

                            instance.makeSource(new_div_mechanism, {
                                filter: ".ep",
                                anchor: [ "Perimeter", { shape: "Triangle", rotation: 90 }],
                            });

                            instance.makeTarget(new_div_mechanism, {
                                dropOptions: { hoverClass: "dragHover" },
                                anchor: [ "Perimeter", { shape: "Triangle", rotation: 90 }],
                                allowLoopback: false, // Нельзя создать кольцевую связь
                                maxConnections: 1,
                                onMaxConnections: function (info, e) {
                                    var message = "<?php echo Yii::t('app', 'MAXIMUM_CONNECTIONS'); ?>" + info.maxConnections;
                                    alert (message);
                                }
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

                            //console.log("Массив после изменения механизма = " + data['id']);
                            //console.log(mas_data_node);
                            //console.log("--------------------");

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
                        document.getElementById('edit-mechanism-form').reset();
                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#edit-mechanism-form", data);
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
    'id' => 'edit-mechanism-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>


<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($node_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($node_model, 'level_id')->dropDownList($array_levels_initial_without)->label(); ?>

<div id="alert_mechanism_level_id" style="display:none;" class="alert-warning alert">
    <?php echo Yii::t('app', 'ALERT_CHANGE_LEVEL'); ?>
</div>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_SAVE'),
    'options' => [
        'id' => 'edit-mechanism-button',
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




<!-- Модальное окно удаления механизма -->
<?php Modal::begin([
    'id' => 'deleteMechanismModalForm',
    'header' => '<h3>' . Yii::t('app', 'MECHANISM_DELETE_MECHANISM') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#delete-mechanism-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/delete-mechanism'?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&node_id_on_click=" + node_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#deleteMechanismModalForm").modal("hide");

                            var div_mechanism = document.getElementById('node_' + node_id_on_click);
                            instance.removeFromGroup(div_mechanism);//удаляем из группы
                            instance.deleteConnectionsForElement(div_mechanism);//визуально убираем соединения
                            div_mechanism.remove(); // удаляем старый node

                            //----------удаляем все соединения
                            instance.deleteEveryEndpoint();
                            //----------восстанавливаем нужные соединения
                            //убираем соединения от удаляемого элемента
                            var temporary_mas_data_node = {};
                            var q = 0;
                            $.each(mas_data_node, function (i, elem_node) {
                                //убираем входящие
                                if (node_id_on_click != elem_node.id){//убираем элемент
                                    //убираем изходящие
                                    if (node_id_on_click == elem_node.parent_node){
                                        temporary_mas_data_node[q] = {
                                            "id":elem_node.id,
                                            "parent_node":null,
                                            "name":elem_node.name,
                                            "description":elem_node.description,
                                        };
                                        q = q+1;
                                    } else {
                                        temporary_mas_data_node[q] = {
                                            "id":elem_node.id,
                                            "parent_node":elem_node.parent_node,
                                            "name":elem_node.name,
                                            "description":elem_node.description,
                                        };
                                        q = q+1;
                                    }
                                }
                            });
                            mas_data_node = temporary_mas_data_node;
                            //-----------------------------
                            $.each(mas_data_node, function (j, elem_node) {
                                if (elem_node.parent_node != null){
                                    instance.connect({
                                        source: "node_" + elem_node.parent_node,
                                        target: "node_" + elem_node.id,
                                    });
                                }
                            });
                            //-----------------------------

                            //console.log("Массив после удаления механизма = " + data['id']);
                            //console.log(mas_data_node);
                            //console.log("--------------------");

                            //заносим изменения в массив sequence_mas
                            var pos_i = 0;
                            $.each(sequence_mas, function (i, mas) {
                                $.each(mas, function (j, elem) {
                                    //второй элемент это id узла события или механизма
                                    if (j == 1) {
                                        if (elem == node_id_on_click){
                                            pos_i = i;
                                        }
                                    };
                                });
                            });
                            sequence_mas.splice(pos_i, 1);
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
    'id' => 'delete-mechanism-form',
]); ?>

<div class="modal-body">
    <p style="font-size: 14px">
        <?php echo Yii::t('app', 'DELETE_MECHANISM_TEXT'); ?>
    </p>
</div>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-mechanism-button',
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