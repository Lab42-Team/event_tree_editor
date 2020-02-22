<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels_initial_without app\modules\editor\controllers\TreeDiagramsController */

?>

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
                            div_mechanism.className = 'div-mechanism';
                            div_mechanism.title = data['name'];
                            div_level_layer.append(div_mechanism);

                            var div_mechanism_m = document.createElement('div');
                            div_mechanism_m.className = 'div-mechanism-m' ;
                            div_mechanism_m.innerHTML = 'M';
                            div_mechanism.append(div_mechanism_m);

                            document.getElementById('add-mechanism-form').reset();
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