<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\components\widgets\WLang;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="Content-Type" content="text/html">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        echo "<form class='navbar-form navbar-right'>" . WLang::widget() . "</form>";

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'encodeLabels' => false,
            'items' => array_filter([
                 // условие проверки есть ли visual-diagram в URL
                (preg_match("/visual-diagram/", Url::current([], false)) == 1) ? (
                     //и тогда выводить кнопку меню на экран
                    ['label' => '<span class="glyphicon glyphicon-plus"></span> ' .
                        Yii::t('app', 'NAV_ADD'),
                        'items' => $this->params['menu']                    ]
                ):(""),
                ['label' => '<span class="glyphicon glyphicon-tree-deciduous"></span> ' .
                    Yii::t('app', 'NAV_TREE_DIAGRAMS'), 'url' => ['/editor/tree-diagrams/index']],
                ['label' => '<span class="glyphicon glyphicon-envelope"></span> ' .
                    Yii::t('app', 'NAV_CONTACT_US'), 'url' => ['/main/default/contact']],

                Yii::$app->user->isGuest ? (
                ['label' => '<span class="glyphicon glyphicon-log-in"></span> ' . Yii::t('app', 'NAV_SIGN_IN'),
                    'url' => ['/main/default/sing-in']]
                ) : (
                    '<li>'
                    . Html::beginForm(['/main/default/sing-out'], 'post')
                    . Html::submitButton(
                        '<span class="glyphicon glyphicon-log-out"></span> ' . Yii::t('app', 'NAV_SIGN_OUT') .
                        ' (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>'
                )
            ])
        ]);

        NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left"><?= ' &copy; ' . date('Y') . ' ' . Yii::t('app', 'FOOTER_INSTITUTE') ?></p>
            <p class="pull-right"><?= Yii::t('app', 'FOOTER_POWERED_BY') . ' ' .
                ' <a href="mailto:DorodnyxNikita@gmail.com">'.Yii::$app->params['adminEmail'].'</a>' ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>