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

                            //тут писать вывод в <div> через JS

                            var visual_diagram_top_layer = document.getElementById('visual-diagram-top-layer');

                            var div_level = document.createElement('div');
                            div_level.className = 'div-level-' + data['id'];
                            visual_diagram_top_layer.append(div_level);

                            var div_level_name = document.createElement('div');
                            div_level_name.className = 'div-level-name' ;
                            div_level_name.innerHTML = data['name'];
                            div_level.append(div_level_name);

                            var div_level_description = document.createElement('div');
                            div_level_description.className = 'div-level-description' ;
                            div_level_description.innerHTML = data['description'];
                            div_level.append(div_level_description);

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