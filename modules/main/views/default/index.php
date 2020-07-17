<?php

/* @var $this yii\web\View */

$this->title = 'Extended Event Tree Editor';
?>

<div class="main-default-index">

    <div class="jumbotron">
        <h1><?php echo Yii::t('app', 'WELCOME_TO_EETE') ?></h1>
    </div>

    <div class="body-content">
        <h4>
            <?php echo '<b>' . Yii::t('app', 'EETE_NAME') . '</b> ' .
                Yii::t('app', 'EETE_DEFINITION') ?><br /><br />
            <?php echo Yii::t('app', 'EVENT_TREE_DEFINITION') ?><br /><br />
            <?php echo Yii::t('app', 'ADVANCED_EVENT_TREE_DEFINITION') ?><br /><br />
            <?php echo Yii::t('app', 'YOU_CAN_SEE_THE_CREATED') .
                "<a href='/editor/tree-diagrams/index'>" . Yii::t('app', 'DIAGRAMS') . "</a>." ?><br />
            <?php if (Yii::$app->user->isGuest == true): ?>
                <?php echo Yii::t('app', 'WARNING_FOR_DIAGRAM_CREATION') . '&nbsp;' .
                    Yii::t('app', 'TO_CREATE_DIAGRAM') . "<a href='/main/default/sing-in'>" .
                    Yii::t('app', 'SIGN_IN') . "</a>." ?>
            <?php else: ?>
                <?php echo Yii::t('app', 'YOU_CAN_CREATE') .
                    "<a href='/editor/tree-diagrams/create'>" . Yii::t('app', 'DIAGRAM') . "</a>." ?>
            <?php endif; ?>
        </h4>
    </div>

</div>