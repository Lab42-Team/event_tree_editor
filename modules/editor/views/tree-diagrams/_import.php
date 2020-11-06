<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\modules\main\models\ContactForm */

?>

<!-- Модальное окно импорта -->
<?php Modal::begin([
    'id' => 'importModalForm',
    'header' => '<h3>' . Yii::t('app', 'IMPORT_FORM') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#import-button").click(function(e) {
            e.preventDefault();
            var form = $("#import-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/import/' . $model->id ?>",
                type: "post",
                data: form.serialize(),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#importModalForm").modal("hide");


                        console.log(data['tempName']);
                        //document.getElementById('add-event-form').reset();

                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#import-form", data);
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
    'id' => 'import-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($import_model); ?>

<?= $form->field($import_model, 'file_name')->fileInput() ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_IMPORT'),
    'options' => [
        'id' => 'import-button',
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

