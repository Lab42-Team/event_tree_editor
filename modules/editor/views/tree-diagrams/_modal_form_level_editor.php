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
                            div_level_name.className = 'div-level-name';
                            div_level_name.id = 'level_name_' + data['id'];
                            div_level.append(div_level_name);

                            var div_name = document.createElement('div');
                            div_name.innerHTML = data['name'];
                            div_name.title = data['name'];
                            div_level_name.append(div_name);

                            var div_level_description = document.createElement('div');
                            div_level_description.className = 'div-level-description' ;
                            div_level_description.id = 'level_description_' + data['id'];
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


                            console.log(mas_data_level);
                            var j = 0;
                            $.each(mas_data_level, function (i, elem) {
                                j = j + 1;
                            });
                            console.log(j);
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



<!-- Модальное окно добавления нового уровня -->
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
                '/tree-diagrams/edit-level/' . $model->id ?>",
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

                        var div_level_name = document.getElementById('level_name_' + data['id']);
                        div_level_name.innerHTML = "<div title=" + data['name'] + ">" + data['name'] +"</div>";

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
