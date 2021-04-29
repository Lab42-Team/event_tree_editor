<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>


<!-- Модальное окно добавления нового комментария -->
<?php Modal::begin([
    'id' => 'addCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'EVENT_ADD_NEW_COMMENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-comment-button").click(function(e) {
                e.preventDefault();
                var form = $("#add-comment-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/add-comment'?>",
                    type: "post",
                    data: form.serialize() + "&node_id_on_click=" + node_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addCommentModalForm").modal("hide");

                            var div_level_layer = document.getElementById('level_description_' + data['level_id']);

                            var div_comment = document.createElement('div');
                            div_comment.id = 'comment_' + data['id'];
                            div_comment.className = 'div-comment';
                            div_level_layer.append(div_comment);

                            var div_comment_name = document.createElement('div');
                            div_comment_name.id = 'comment_name_' + data['id'];
                            div_comment_name.className = 'div-comment-name';
                            div_comment_name.innerHTML = data['comment'];
                            div_comment.append(div_comment_name);

                            var div_edit_comment = document.createElement('div');
                            div_edit_comment.id = 'edit_comment_' + data['id'];
                            div_edit_comment.className = 'edit-comment glyphicon-pencil';
                            div_edit_comment.title = '<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>' ;
                            div_comment.append(div_edit_comment);

                            var div_del_comment = document.createElement('div');
                            div_del_comment.id = 'del_comment_' + data['id'];
                            div_del_comment.className = 'del-comment glyphicon-trash';
                            div_del_comment.title = '<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>' ;
                            div_comment.append(div_del_comment);

                            document.getElementById('add-comment-form').reset();

                            //находим DOM элемент comment (идентификатор div comment)
                            var comment = document.getElementById('comment_'+ data['id']);
                            //делаем node перетаскиваемым
                            instance.draggable(comment);

                            var group_name = 'group'+ id_level; //определяем имя группы

                            //добавляем элемент div_node_id в группу с именем group_name
                            instance.addToGroup(group_name, comment);

                            comment.style.visibility='visible'
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-comment-form", data);
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
    'id' => 'add-comment-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'comment')->textarea(['maxlength' => false, 'rows'=>6]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'add-comment-button',
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



<!-- Модальное окно изменения нового комментария -->
<?php Modal::begin([
    'id' => 'editCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'EVENT_EDIT_COMMENT') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#edit-comment-button").click(function(e) {
            e.preventDefault();
            var form = $("#edit-comment-form");
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/edit-comment'?>",
                type: "post",
                data: form.serialize() + "&node_id_on_click=" + node_id_on_click,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#editCommentModalForm").modal("hide");

                        var div_comment_name = document.getElementById('comment_name_' + data['id']);
                        div_comment_name.innerHTML = data['comment'];

                        document.getElementById('edit-comment-form').reset();

                        //находим DOM элемент comment и делаем его видимым
                        var comment = document.getElementById('comment_'+ data['id']);
                        comment.style.visibility='visible'
                    } else {
                        // Отображение ошибок ввода
                        viewErrors("#edit-comment-form", data);
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
    'id' => 'edit-comment-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($node_model); ?>

<?= $form->field($node_model, 'comment')->textarea(['maxlength' => false, 'rows'=>6]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_EDIT'),
    'options' => [
        'id' => 'edit-comment-button',
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



<!-- Модальное окно удаления нового комментария -->
<?php Modal::begin([
    'id' => 'deleteCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'EVENT_DELETE_COMMENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#delete-comment-button").click(function(e) {
                e.preventDefault();
                var form = $("#delete-comment-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/delete-comment'?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&node_id_on_click=" + node_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#deleteCommentModalForm").modal("hide");

                            var div_comment = document.getElementById('comment_' + node_id_on_click);
                            instance.removeFromGroup(div_comment);//удаляем из группы
                            instance.remove(div_comment);// удаляем node
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#delete-comment-form", data);
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
    'id' => 'delete-comment-form',
]); ?>

<div class="modal-body">
    <p style="font-size: 14px">
        <?php echo Yii::t('app', 'DELETE_COMMENT_TEXT'); ?>
    </p>
</div>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-comment-button',
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