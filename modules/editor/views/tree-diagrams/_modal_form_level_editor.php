<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $level_model app\modules\editor\models\Level */
?>

<!-- Модальное окно добавления нового уровня -->
<?php Modal::begin([
    'id' => 'addLevelModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_ADD_NEW_LEVEL') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-level-button").click(function(e) {
                var form = $("#add-level-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                        '/tree-diagrams/add-level/' . $model->id ?>",
                    type: "post",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addLevelModalForm").modal("hide");

                            //создание и вывод в <div> нового элемента
                            var visual_diagram_top_layer = document.getElementById('top_layer');

                            var div_level = document.createElement('div');
                            div_level.id = 'level_' + data['id'];
                            div_level.className = 'div-level';
                            visual_diagram_top_layer.append(div_level);

                            var div_level_name = document.createElement('div');
                            div_level_name.id = 'level_name_' + data['id'];
                            div_level_name.className = 'div-level-name';
                            div_level.append(div_level_name);

                            var div_name = document.createElement('div');
                            div_name.id = 'level_title_' + data['id'];
                            div_name.className = 'div-title-name';
                            div_name.title = data['name'];
                            div_name.innerHTML = data['name'];
                            div_level_name.append(div_name);

                            var div_del = document.createElement('div');
                            div_del.id = 'level_del_' + data['id'];
                            div_del.className = 'del-level glyphicon-trash';
                            div_level_name.append(div_del);

                            var div_edit = document.createElement('div');
                            div_edit.id = 'level_edit_' + data['id'];
                            div_edit.className = 'edit-level glyphicon-pencil';
                            div_level_name.append(div_edit);

                            var div_level_description = document.createElement('div');
                            div_level_description.id = 'level_description_' + data['id'];
                            div_level_description.className = 'div-level-description' ;
                            div_level.append(div_level_description);


                            var nav_add_event = document.getElementById('nav_add_event');
                            var nav_add_mechanism = document.getElementById('nav_add_mechanism');
                            if (data['level_count'] > 0){
                                nav_add_event.className = 'enabled';
                                nav_add_event.setAttribute("data-target", "#addEventModalForm");
                            }
                            if (data['level_count'] > 1){
                                nav_add_mechanism.className = 'enabled';
                                nav_add_mechanism.setAttribute("data-target", "#addMechanismModalForm");
                            }

                            document.getElementById('add-level-form').reset();
                            document.getElementById("pjax-event-editor-button").click();

                            var id = data['id'];
                            var parent_level = data['parent_level'];
                            var name = data['name'];
                            var description = data['description'];
                            var removed = level_mas.push([id, parent_level, name, description]);


                            //console.log(mas_data_level);
                            var j = 0;
                            $.each(mas_data_level, function (i, elem) {
                                j = j + 1;
                            });
                            //console.log(j);
                            mas_data_level[j] = {id_level:id, name:name, description:description};
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-level-form", data);
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
        'id' => 'add-level-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($level_model); ?>

        <?= $form->field($level_model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($level_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= Button::widget([
            'label' => Yii::t('app', 'BUTTON_ADD'),
            'options' => [
                'id' => 'add-level-button',
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



<!-- Модальное окно изменения нового уровня -->
<?php Modal::begin([
    'id' => 'editLevelModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_EDIT_LEVEL') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#edit-level-button").click(function(e) {
            var form = $("#edit-level-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/edit-level'?>",
                type: "post",
                data: form.serialize() + "&level_id_on_click=" + level_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#editLevelModalForm").modal("hide");

                        $.each(mas_data_level, function (i, elem) {
                            if (elem.id_level == data['id']){
                                mas_data_level[i].name = data['name'];
                                mas_data_level[i].description = data['description'];
                            }
                        });

                        var div_level_name = document.getElementById('level_title_' + level_id_on_click);
                        div_level_name.innerHTML = data['name'];
                        div_level_name.title = data['name'];

                        document.getElementById('edit-level-form').reset();

                        document.getElementById("pjax-event-editor-button").click();
                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#edit-level-form", data);
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
    'id' => 'edit-level-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($level_model); ?>

<?= $form->field($level_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($level_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'edit-level-button',
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



<!-- Модальное окно удаления уровня -->
<?php Modal::begin([
    'id' => 'deleteLevelModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_DELETE_LEVEL') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#delete-level-button").click(function(e) {
            e.preventDefault();
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/delete-level'?>",
                type: "post",
                data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&level_id_on_click=" + level_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#deleteLevelModalForm").modal("hide");

                    //чистим массивы
                        //--------- убираем из массива удаляемый уровень
                        var temporary_mas_data_level = {};
                        var q = 0;
                        $.each(mas_data_level, function (i, elem_level) {
                            if (level_id_on_click != elem_level.id_level){//убираем элемент
                                temporary_mas_data_level[q] = {
                                    "id_level":elem_level.id_level,
                                    "name":elem_level.name,
                                    "description":elem_level.description,
                                };
                                q = q+1;
                            }
                        });
                        mas_data_level = temporary_mas_data_level;
                        //--------- убираем из массива элементы на удаляемом уровне и их связи
                        var div_level_layer = document.getElementById('level_description_'+ level_id_on_click);
                        //console.log(div_level_layer);
                        var mas_node = div_level_layer.getElementsByClassName("node");

                        $.each(mas_node, function (i, elem) {
                            var id_node = parseInt(elem.getAttribute('id').match(/\d+/));

                            var temporary_mas_data_node = {};
                            var q = 0;
                            $.each(mas_data_node, function (j, elem_node) {
                                if (id_node != elem_node.id){//убираем элемент
                                    //убираем изходящие из удаляемых элементов
                                    if (id_node == elem_node.parent_node){
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
                            //------------------------------

                            //убираем из массива элементы на удаляемом уровне
                            var pos_i = 0;
                            $.each(sequence_mas, function (i, mas) {
                                $.each(mas, function (j, elem) {
                                    //второй элемент это id узла события или механизма
                                    if (j == 1) {
                                        if (elem == id_node){
                                            pos_i = i;
                                        }
                                    };
                                });
                            });
                            sequence_mas.splice(pos_i, 1);
                        });
                        //-------------

                        if (data['id_level_descendent'] != null){
                            //console.log("следующий уровень есть");
                            var div_level_descendent_layer = document.getElementById('level_description_'+ data['id_level_descendent']);//определяем следущий уровень
                            if (data['initial']) {
                                //console.log("начальный уровень");
                                var mas_mechanism = div_level_descendent_layer.getElementsByClassName("div-mechanism");//находим механизмы на следующем уровне

                                $.each(mas_mechanism, function (i, elem) {
                                    var id_mechanism = parseInt(elem.getAttribute('id').match(/\d+/));

                                    var temporary_mas_data_node = {};
                                    var q = 0;
                                    $.each(mas_data_node, function (j, elem_node) {
                                        //убираем входящие
                                        if (id_mechanism != elem_node.id){//убираем элемент
                                            //убираем изходящие из механизмов
                                            if (id_mechanism == elem_node.parent_node){
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
                                    //------------------------------
                                    //убираем из массива механизмы на следующем уровне
                                    var pos_i = 0;
                                    $.each(sequence_mas, function (i, mas) {
                                        $.each(mas, function (j, elem) {
                                            //второй элемент это id узла события или механизма
                                            if (j == 1) {
                                                if (elem == id_mechanism){
                                                    pos_i = i;
                                                }
                                            };
                                        });
                                    });
                                    sequence_mas.splice(pos_i, 1);
                                });
                            }
                        }
                    //------------------------------

                        //удаление элементов со страницы
                        var del_mechanism_node = {};
                        var q = 0;
                        if (data['id_level_descendent'] != null) {
                            //console.log("следующий уровень есть");
                            var div_level_descendent_layer = document.getElementById('level_description_' + data['id_level_descendent']);//определяем следущий уровень
                            if (data['initial']) {
                                //находим механизмы на следующем уровне
                                var mas_mechanism = div_level_descendent_layer.getElementsByClassName("div-mechanism");
                                //удаляем их
                                $.each(mas_mechanism, function (i, elem) {
                                    var id_mechanism = parseInt(elem.getAttribute('id').match(/\d+/));
                                    //var div_del_mechanism = document.getElementById('node_' + id_mechanism);
                                    //div_del_mechanism.remove(); // удаляем старый node
                                    del_mechanism_node[q] = {
                                        "mechanism":id_mechanism,
                                    };
                                    q = q+1;

                                });
                            }
                        }

                        //удаляем механизмы
                        $.each(del_mechanism_node, function (i, elem) {
                            var div_del_mechanism = document.getElementById('node_' + elem.mechanism);
                            div_del_mechanism.remove(); // удаляем node
                        });

                        var div_level = document.getElementById('level_' + level_id_on_click);
                        var g_name = 'group'+ level_id_on_click; //определяем имя группы
                        var grp = instance.getGroup(g_name);//определяем существует ли группа с таким именем
                        if (grp != 0){
                            //если группа существует то
                            instance.removeGroup(g_name, true);//удаляем группу и т.к. true еще и элементы
                        }
                        div_level.remove(); // удаляем визуально уровень

                        //----------удаляем все соединения
                        instance.deleteEveryEndpoint();
                        //----------строим соединения заного
                        $.each(mas_data_node, function (j, elem_node) {
                            if (elem_node.parent_node != null){
                                instance.connect({
                                    source: "node_" + elem_node.parent_node,
                                    target: "node_" + elem_node.id,
                                });
                            }
                        });
                        //-----------------------------

                        //console.log("mas_data_level---------");
                        //console.log(mas_data_level);
                        //console.log("mas_data_node---------");
                        //console.log(mas_data_node);
                        //console.log("sequence_mas---------");
                        //console.log(sequence_mas);
                        document.getElementById("pjax-event-editor-button").click();
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
    'id' => 'delete-level-form',
]); ?>

<div class="modal-body">
    <p style="font-size: 14px">
        <?php echo Yii::t('app', 'DELETE_LEVEL_TEXT'); ?>
    </p>
</div>


<div id="alert_level_initial_level" style="display:none;" class="alert-warning alert">
    <?php echo Yii::t('app', 'ALERT_INITIAL_LEVEL'); ?>
</div>

<div id="alert_level_delete_level" style="display:none;" class="alert-warning alert">
    <?php echo Yii::t('app', 'ALERT_DELETE_LEVEL'); ?>
</div>



<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-level-button',
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
