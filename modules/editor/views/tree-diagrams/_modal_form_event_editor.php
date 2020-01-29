<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;
use app\modules\editor\models\Node;
use app\modules\editor\models\Level;
use yii\widgets\Pjax;
use yii\bootstrap\Html;

/* @var $node_model app\modules\editor\models\Node */
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
                                var div_level_layer = document.getElementById('div-level-' + data['id_level']);
                                var div_initial_event = document.createElement('div');
                                div_initial_event.id = 'div-initial-event-' + data['id'];
                                div_initial_event.className = 'div-event';
                                div_level_layer.append(div_initial_event);

                                var div_initial_event_name = document.createElement('div');
                                div_initial_event_name.className = 'div-event-name' ;
                                div_initial_event_name.innerHTML = data['name'];
                                div_initial_event.append(div_initial_event_name);

                                //var div_initial_event_description = document.createElement('div');
                                //div_initial_event_description.className = 'div-event-description' ;
                                //div_initial_event_description.innerHTML = data['description'];
                                //div_initial_event.append(div_initial_event_description);
                            } else {
                                var div_level_layer = document.getElementById('div-level-' + data['id_level']);
                                var div_event = document.createElement('div');
                                div_event.id = 'div-event-' + data['id'];
                                div_event.className = 'div-event';
                                div_level_layer.append(div_event);

                                var div_event_name = document.createElement('div');
                                div_event_name.className = 'div-event-name' ;
                                div_event_name.innerHTML = data['name'];
                                div_event.append(div_event_name);

                                //var div_event_description = document.createElement('div');
                                //div_event_description.className = 'div-event-description' ;
                                //div_event_description.innerHTML = data['description'];
                                //div_event.append(div_event_description);

                                // /tree-diagrams/visual-diagram/
                            }
                            document.getElementById('add-event-form').reset();
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