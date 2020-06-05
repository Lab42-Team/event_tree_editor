<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\modules\main\models\Lang;

/* @var $node_model app\modules\editor\models\Node */
/* @var $array_levels app\modules\editor\controllers\TreeDiagramsController */

?>


<!-- Модальное окно удаления связи -->
<?php Modal::begin([
    'id' => 'deleteRelationshipModalForm',
    'header' => '<h3>' . Yii::t('app', 'DELETE_RELATIONSHIP') . '</h3>',
]); ?>

<!-- Скрипт модального окна -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки сохранения
        $("#delete-relationship-button").click(function(e) {
            e.preventDefault();
            // Ajax-запрос
            $.ajax({
                //переход на экшен левел
                url: "<?= Yii::$app->request->baseUrl . '/' . Lang::getCurrent()->url .
                '/tree-diagrams/delete-relationship'?>",
                type: "post",
                data: "YII_CSRF_TOKEN=<?= Yii::$app->request->csrfToken ?>" + "&id_target=" + id_target,
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data['success']) {
                        // Скрывание модального окна
                        $("#deleteRelationshipModalForm").modal("hide");

                        //----------удаляем все соединения
                        //instance.deleteEveryEndpoint();

                        instance.deleteConnection(current_connection);

                        //deleteConnectionsForElement

                        // Обновление формы редактора
                        //instance.repaintEverything();


                        //----------восстанавливаем нужные соединения
                        $.each(mas_data_node, function (i, elem_node) {
                            //убираем удаленную связь
                            if (data['id_target'] == elem_node.id){
                                mas_data_node[i].parent_node = null;
                            }
                        });

                        //$.each(mas_data_node, function (j, elem_node) {
                        //    if (elem_node.parent_node != null){
                        //        instance.connect({
                        //            source: "node_" + elem_node.parent_node,
                        //            target: "node_" + elem_node.id,
                        //        });
                        //    }
                        //});
                        //-----------------------------

                        //console.log("Массив после удаления связи где тарджет = " + data['id']);
                        //console.log(mas_data_node);
                        //console.log("--------------------");

                    }
                },
                error: function() {
                    alert('Error!');
                }
            });
        });
    });
</script>

<div class="modal-body">
    <p style="font-size: 14px">
        <?php echo Yii::t('app', 'RELATIONSHIP_PAGE_DELETE_CONNECTION_TEXT'); ?>
    </p>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'delete-relationship-model-form',
]); ?>

<?= Button::widget([
    'label' => Yii::t('app', 'BUTTON_DELETE'),
    'options' => [
        'id' => 'delete-relationship-button',
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













