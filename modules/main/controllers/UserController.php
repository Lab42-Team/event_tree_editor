<?php

namespace app\modules\main\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\main\models\User;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**public $layout = 'admin';*/

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays a single User model.
     * @return mixed
     */
    public function actionProfile()
    {
        return $this->render('profile', [
            'model' => $this->findModel(Yii::$app->user->identity->getId()),
        ]);
    }

    /**
     * Displays a user biography.
     * @return mixed
     */
    public function actionBiography()
    {
        return $this->render('biography', [
            'model' => $this->findModel(Yii::$app->user->identity->getId()),
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdateProfile()
    {
        $model = $this->findModel(Yii::$app->user->identity->getId());

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_MODEL_MESSAGE_UPDATED_YOUR_DETAILS'));

            return $this->redirect('profile');
        } else {
            return $this->render('update-profile', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing user biography.
     * If update is successful, the browser will be redirected to the 'account' page.
     * @return mixed
     */
    public function actionUpdateBiography()
    {
        $model = $this->findModel(Yii::$app->user->identity->getId());

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_ADMIN_PAGE_MESSAGE_UPDATED_BIOGRAPHY'));

            return $this->redirect('biography');
        } else {
            return $this->render('update-biography', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing user password hash.
     * If update is successful, the browser will be redirected to the 'account' page.
     * @return mixed
     */
    public function actionUpdatePassword()
    {
        $id = Yii::$app->user->identity->getId();
        $model = $this->findModel($id);
        $model->scenario = 'create_and_update_password_hash';

        if (Yii::$app->request->post('User')) {
            $model->attributes = Yii::$app->request->post('User');
            $model->setPassword($model->password);
            if ($model->update())
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'USER_ADMIN_PAGE_MESSAGE_UPDATE_PASSWORD'));

            return $this->redirect('profile');
        } else {
            return $this->render('update-password', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'ERROR_MESSAGE_PAGE_NOT_FOUND'));
    }
}