<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;
use app\modules\editor\models\Parameter;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>

<!-- Модальное окно добавления нового параметра -->
<?php Modal::begin([
    'id' => 'addParameterModalForm',
    'header' => '<h3>' . Yii::t('app', 'PARAMETER_ADD_NEW_PARAMETER') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-parameter-button").click(function(e) {
                e.preventDefault();
                var form = $("#add-parameter-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/add-parameter'?>",
                    type: "post",
                    data: form.serialize() + "&node_id_on_click=" + node_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addParameterModalForm").modal("hide");

                            var div_event = document.getElementById('node_' + node_id_on_click);

                            var div_parameter = document.createElement('div');
                            div_parameter.id = 'parameter_' + data['id'];
                            div_parameter.className = 'div-parameter';
                            div_event.append(div_parameter);

                            var div_parameter_name = document.createElement('div');
                            div_parameter_name.id = 'parameter_name_' + data['id'];
                            div_parameter_name.className = 'div-parameter-name';
                            div_parameter_name.innerHTML = data['name'] + " " + data['operator'] + " " + data['value'];
                            div_parameter.append(div_parameter_name);

                            var div_button_parameter = document.createElement('div');
                            div_button_parameter.className = 'button-parameter';
                            div_parameter.append(div_button_parameter);

                            var div_edit_parameter = document.createElement('div');
                            div_edit_parameter.id = 'edit_parameter_' + data['id'];
                            div_edit_parameter.className = 'edit edit-parameter glyphicon-pencil';
                            div_edit_parameter.title = '<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>' ;
                            div_button_parameter.append(div_edit_parameter);

                            var div_del_parameter = document.createElement('div');
                            div_del_parameter.id = 'del_parameter_' + data['id'];
                            div_del_parameter.className = 'del del-parameter glyphicon-trash';
                            div_del_parameter.title = '<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>' ;
                            div_button_parameter.append(div_del_parameter);


                            var id = data['id'];
                            var name = data['name'];
                            var description = data['description'];
                            var operator = data['operator'];
                            var value = data['value'];

                            var j = 0;
                            $.each(mas_data_parameter, function (i, elem) {
                                j = j + 1;
                            });
                            mas_data_parameter[j] = {id:id, name:name, description:description, operator:operator, value:value,};


                            document.getElementById('add-parameter-form').reset();

                            var id_node = 'node_' + node_id_on_click;
                            mousemoveNode(id_node);
                            // Обновление формы редактора
                            instance.repaintEverything();
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-parameter-form", data);
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
    'id' => 'add-parameter-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($parameter_model); ?>

<?= $form->field($parameter_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($parameter_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($parameter_model, 'operator')->dropDownList(Parameter::getOperatorArray()) ?>

<?= $form->field($parameter_model, 'value')->textInput(['maxlength' => true]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'add-parameter-button',
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






<!-- Модальное окно изменения параметра -->
<?php Modal::begin([
    'id' => 'editParameterModalForm',
    'header' => '<h3>' . Yii::t('app', 'PARAMETER_EDIT_PARAMETER') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#edit-parameter-button").click(function(e) {
            e.preventDefault();
            var form = $("#edit-parameter-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/edit-parameter'?>",
                type: "post",
                data: form.serialize() + "&parameter_id_on_click=" + parameter_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#editParameterModalForm").modal("hide");

                        $.each(mas_data_parameter, function (i, elem) {
                            if (elem.id == data['id']){
                                mas_data_parameter[i].name = data['name'];
                                mas_data_parameter[i].description = data['description'];
                                mas_data_parameter[i].operator = data['operator'];
                                mas_data_parameter[i].value = data['value'];
                            }
                        });

                        var div_parameter_name = document.getElementById('parameter_name_' + parameter_id_on_click);
                        div_parameter_name.innerHTML = data['name'] + " " + data['operator'] + " " + data['value'];

                        document.getElementById('edit-parameter-form').reset();
                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#edit-parameter-form", data);
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
    'id' => 'edit-parameter-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($parameter_model); ?>

<?= $form->field($parameter_model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($parameter_model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

<?= $form->field($parameter_model, 'operator')->dropDownList(Parameter::getOperatorArray()) ?>

<?= $form->field($parameter_model, 'value')->textInput(['maxlength' => true]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_SAVE'),
    'options' => [
        'id' => 'edit-parameter-button',
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






<!-- Модальное окно удаления параметра -->
<?php Modal::begin([
    'id' => 'deleteParameterModalForm',
    'header' => '<h3>' . Yii::t('app', 'PARAMETER_DELETE_PARAMETER') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#delete-parameter-button").click(function(e) {
                e.preventDefault();
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/delete-parameter'?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&parameter_id_on_click=" + parameter_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            $("#deleteParameterModalForm").modal("hide");

                            var temporary_mas_data_parameter = {};
                            var q = 0;
                            $.each(mas_data_parameter, function (i, elem) {
                                if (parameter_id_on_click != elem.id){
                                    temporary_mas_data_parameter[q] = {
                                        "id":elem.id,
                                        "name":elem.name,
                                        "description":elem.description,
                                        "operator":elem.operator,
                                        "value":elem.value,
                                    };
                                    q = q+1;
                                }
                            });
                            mas_data_parameter = temporary_mas_data_parameter;

                            var div_parameter = document.getElementById('parameter_' + parameter_id_on_click);
                            div_parameter.remove(); // удаляем

                            var id_node = 'node_' + data['node'];
                            mousemoveNode(id_node);
                            // Обновление формы редактора
                            instance.repaintEverything();
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#delete-parameter-form", data);
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
    'id' => 'delete-parameter-form',
]); ?>

<div class="modal-body">
    <p style="font-size: 14px">
        <?php echo Yii::t('app', 'DELETE_PARAMETER_TEXT'); ?>
    </p>
</div>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-parameter-button',
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