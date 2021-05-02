<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $node_model app\modules\editor\models\Level */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>


<!-- Модальное окно добавления нового комментария в уровень -->
<?php Modal::begin([
    'id' => 'addLevelCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_ADD_NEW_COMMENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#add-level-comment-button").click(function(e) {
                e.preventDefault();
                var form = $("#add-level-comment-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/add-level-comment'?>",
                    type: "post",
                    data: form.serialize() + "&level_id_on_click=" + level_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#addLevelCommentModalForm").modal("hide");

                            var div_level_layer = document.getElementById('level_description_' + level_id_on_click);

                            var div_comment = document.createElement('div');
                            div_comment.id = 'level_comment_' + level_id_on_click;
                            div_comment.className = 'div-level-comment';
                            div_level_layer.append(div_comment);

                            var div_comment_name = document.createElement('div');
                            div_comment_name.id = 'level_comment_name_' + level_id_on_click;
                            div_comment_name.className = 'div-comment-name';
                            div_comment_name.innerHTML = data['comment'];
                            div_comment.append(div_comment_name);

                            var div_edit_comment = document.createElement('div');
                            div_edit_comment.id = 'level_edit_comment_' + level_id_on_click;
                            div_edit_comment.className = 'edit-level-comment glyphicon-pencil';
                            div_edit_comment.title = '<?php echo Yii::t('app', 'BUTTON_EDIT'); ?>' ;
                            div_comment.append(div_edit_comment);

                            var div_del_comment = document.createElement('div');
                            div_del_comment.id = 'level_del_comment_' + level_id_on_click;
                            div_del_comment.className = 'del-level-comment glyphicon-trash';
                            div_del_comment.title = '<?php echo Yii::t('app', 'BUTTON_DELETE'); ?>' ;
                            div_comment.append(div_del_comment);

                            var div_hide_comment = document.createElement('div');
                            div_hide_comment.id = 'level_hide_comment_' + level_id_on_click;
                            div_hide_comment.className = 'hide-level-comment glyphicon-eye-close';
                            div_hide_comment.title = '<?php echo Yii::t('app', 'BUTTON_HIDE'); ?>' ;
                            div_comment.append(div_hide_comment);

                            document.getElementById('add-level-comment-form').reset();

                            //находим DOM элемент comment (идентификатор div comment)
                            var comment = document.getElementById('level_comment_'+ level_id_on_click);

                            var group_name = 'group'+ level_id_on_click; //определяем имя группы

                            //находим DOM элемент description уровня (идентификатор div level_description)
                            var div_level_id = document.getElementById('level_description_'+ level_id_on_click);

                            var grp = instance.getGroup(group_name);//определяем существует ли группа с таким именем
                            if (grp == 0){
                                //если группа не существует то создаем группу с определенным именем group_name
                                instance.addGroup({
                                    el: div_level_id,
                                    id: group_name,
                                    draggable: false, //перетаскивание группы
                                    //constrain: true, //запрет на перетаскивание элементов за группу (false перетаскивать можно)
                                    dropOverride:true,
                                });
                            }

                            //делаем comment перетаскиваемым
                            instance.draggable(comment);

                            //добавляем элемент comment в группу с именем group_name
                            instance.addToGroup(group_name, comment);

                            comment.style.visibility='visible'
                            arrangeLevelComment(level_id_on_click);
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#add-level-comment-form", data);
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
    'id' => 'add-level-comment-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($level_model); ?>

<?= $form->field($level_model, 'comment')->textarea(['maxlength' => false, 'rows'=>6]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_ADD'),
    'options' => [
        'id' => 'add-level-comment-button',
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



<!-- Модальное окно изменения нового комментария в уровне-->
<?php Modal::begin([
    'id' => 'editLevelCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_EDIT_COMMENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#edit-level-comment-button").click(function(e) {
                e.preventDefault();
                var form = $("#edit-level-comment-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/edit-level-comment'?>",
                    type: "post",
                    data: form.serialize() + "&level_id_on_click=" + level_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#editLevelCommentModalForm").modal("hide");

                            var div_comment_name = document.getElementById('level_comment_name_' + level_id_on_click);
                            div_comment_name.innerHTML = data['comment'];

                            document.getElementById('edit-level-comment-form').reset();

                            //находим DOM элемент comment и делаем его видимым
                            var comment = document.getElementById('level_comment_'+ level_id_on_click);
                            comment.style.visibility='visible'

                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#edit-level-comment-form", data);
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
    'id' => 'edit-level-comment-form',
    'enableClientValidation' => true,
]); ?>

<?= $form->errorSummary($level_model); ?>

<?= $form->field($level_model, 'comment')->textarea(['maxlength' => false, 'rows'=>6]) ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_EDIT'),
    'options' => [
        'id' => 'edit-level-comment-button',
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



<!-- Модальное окно удаления нового комментария в уровне-->
<?php Modal::begin([
    'id' => 'deleteLevelCommentModalForm',
    'header' => '<h3>' . Yii::t('app', 'LEVEL_DELETE_COMMENT') . '</h3>',
]); ?>

    <!-- Скрипт модального окна -->
    <script type="text/javascript">
        // Выполнение скрипта при загрузке страницы
        $(document).ready(function() {
            // Обработка нажатия кнопки сохранения
            $("#delete-level-comment-button").click(function(e) {
                e.preventDefault();
                var form = $("#delete-level-comment-form");
                // Ajax-запрос
                $.ajax({
                    //переход на экшен левел
                    url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                    '/tree-diagrams/delete-level-comment'?>",
                    type: "post",
                    data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&level_id_on_click=" + level_id_on_click,
                    dataType: "json",
                    success: function(data) {
                        // Если валидация прошла успешно (нет ошибок ввода)
                        if (data['success']) {
                            // Скрывание модального окна
                            $("#deleteLevelCommentModalForm").modal("hide");

                            var div_comment = document.getElementById('level_comment_' + level_id_on_click);
                            instance.removeFromGroup(div_comment);//удаляем из группы
                            instance.remove(div_comment);// удаляем node
                        } else {
                            // Отображение ошибок ввода
                            viewErrors("#delete-level-comment-form", data);
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
    'id' => 'delete-level-comment-form',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">
            <?php echo Yii::t('app', 'DELETE_COMMENT_TEXT'); ?>
        </p>
    </div>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-level-comment-button',
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