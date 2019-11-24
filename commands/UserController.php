<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\main\models\User;

/**
 * UserController реализует консольные команды для работы с пользователем.
 */
class UserController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii user/create-default-user' . PHP_EOL;
    }

    /**
     * Команда создания пользователя (администратора) по умолчанию.
     */
    public function actionCreateDefaultUser()
    {
        $model = new User();
        $model->username = 'admin';
        $model->setPassword('admin');
        $model->generateAuthKey();
        $model->full_name = 'Администратор';
        $model->email = 'primer@gmail.com';
        $model->status = 1;
        $this->log($model->save());
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}