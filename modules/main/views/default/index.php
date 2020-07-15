<?php

/* @var $this yii\web\View */

$this->title = 'Extended Event Tree Editor';
?>

<div class="main-default-index">

    <div class="jumbotron">
        <h2><?php echo Yii::t('app', 'WELCOME_TO') ?></h2>

        <h2><?php echo Yii::t('app', 'YOU_CAN_SEE_THE_CREATED').
                "<a href='/editor/tree-diagrams/index'>".Yii::t('app', 'DIAGRAMS')."</a>" ?></h2>

        <?php if (Yii::$app->user->isGuest == true){ ?>
            <h2><?php echo Yii::t('app', 'TO_CREATE_DIAGRAM').
                    "<a href='/main/default/sing-in'>".Yii::t('app', 'SIGN_IN')."</a>" ?></h2>
        <?php } else { ?>
            <h2><?php echo Yii::t('app', 'YOU_CAN_CREATE').
                    "<a href='/editor/tree-diagrams/create'>".Yii::t('app', 'DIAGRAM')."</a>" ?></h2>
        <?php } ?>
    </div>

    <div class="body-content"></div>

</div>