<?php

namespace app\modules\editor\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\bootstrap\ActiveForm;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\TreeDiagramSearch;

/**
 * TreeDiagramsController implements the CRUD actions for TreeDiagram model.
 */
class TreeDiagramsController extends Controller
{
    public $layout = '@app/modules/main/views/layouts/main';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all TreeDiagram models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TreeDiagramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TreeDiagram model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TreeDiagram model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TreeDiagram();
        $model->author = Yii::$app->user->identity->getId();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'TREE_DIAGRAMS_PAGE_MESSAGE_CREATE_TREE_DIAGRAM'));

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TreeDiagram model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TreeDiagram model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TreeDiagram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TreeDiagram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TreeDiagram::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    /**
     * Страница визуального редактора деревьев.
     *
     * @param $id - id дерева событий
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionVisualDiagram($id)
    {
        $level_model_all = Level::find()->where(['tree_diagram' => $id])->all();
        $level_model_count = Level::find()->where(['tree_diagram' => $id])->count();
        $initial_event_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::INITIAL_EVENT_TYPE])->all();
        $event_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::EVENT_TYPE])->all();
        $sequence_model_all = Sequence::find()->where(['tree_diagram' => $id])->all();
        $mechanism_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::MECHANISM_TYPE])->all();
        $level_model = new Level();
        $node_model = new Node();

        return $this->render('visual-diagram', [
            'model' => $this->findModel($id),
            'level_model' => $level_model,
            'node_model' => $node_model,
            'level_model_all' => $level_model_all,
            'level_model_count' => $level_model_count,
            'initial_event_model_all' =>$initial_event_model_all,
            'event_model_all' => $event_model_all,
            'mechanism_model_all' => $mechanism_model_all,
            'sequence_model_all' => $sequence_model_all,
        ]);
    }

    /**
     * Добавление нового уровня в дерево событий.
     *
     * @param $id - id дерева событий
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddLevel($id)
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели уровня
            $model = new Level();
            // Задание id диаграммы
            $model->tree_diagram = $id;

            // Задание parent_level уровня
            $mas = Level::find()->where(['tree_diagram' => $id, 'parent_level' => null ])->one();;
            if ($mas <> null){
                $a = $mas->id;
                do {
                    $b = $a;
                    $mas = Level::find()->where(['tree_diagram' => $id, 'parent_level' => $b ])->one();;
                    if ($mas <> null)
                        $a = $mas->id;
                } while ($mas <> null);
                $model->parent_level = $b;
            } else {
                $model->parent_level = null;
            }

            // Определение полей модели уровня и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
               // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового уровня в БД
                $model->save();
                // Формирование данных о новом уровне
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового события в дерево событий.
     *
     * @param $id - id дерева событий
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddEvent($id)
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели уровня
            $model = new Node();
            // Задание id диаграммы
            $model->tree_diagram = $id;
            // Задание AND_OPERATOR для оператора по умолчанию
            $model->operator = Node::AND_OPERATOR;

            // Условие проверки является ли событие инициирующим
            $i = Node::find()->where(['tree_diagram' => $id, 'type' => 0])->count();
            // Если инициирующие события есть
            if ($i > '0') {
                // Тип присваивается константа "EVENT_TYPE" как событие
                $model->type = Node::EVENT_TYPE;
            } else {
                // Тип присваивается константа "INITIAL_EVENT_TYPE" как инициирующее событие
                $model->type = Node::INITIAL_EVENT_TYPE;
            }
            // Определение полей модели уровня и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового уровня в БД
                $model->save();
                // Формирование данных о новом уровне
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
                $data["type"] = $model->type;
            } else
                $data = ActiveForm::validate($model);

            $sequence = new Sequence();
            $sequence->tree_diagram = $id;
            $sequence->level = $model->level_id;
            $sequence->node = $model->id;
            $sequence_model_count = Sequence::find()->where(['tree_diagram' => $id])->count();
            $sequence->priority = $sequence_model_count;
            $sequence->save();

            $data["id_level"] = $sequence->level;

            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Добавление нового механизма в дерево событий.
     *
     * @param $id - id дерева событий
     * @return bool|\yii\console\Response|Response
     */
    public function actionAddMechanism($id)
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели уровня
            $model = new Node();
            // Задание id диаграммы
            $model->tree_diagram = $id;
            // Задание AND_OPERATOR для оператора по умолчанию
            $model->operator = Node::AND_OPERATOR;
            // Задание константы "MECHANISM_TYPE" типа узла механизма
            $model->type = Node::MECHANISM_TYPE;
            // Определение полей модели уровня и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового уровня в БД
                $model->save();
                // Формирование данных о новом уровне
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
            } else
                $data = ActiveForm::validate($model);

            $sequence = new Sequence();
            $sequence->tree_diagram = $id;
            $mas_level = Level::find()->where(['tree_diagram' => $id, 'parent_level' => null ])->one();;
            if ($mas_level <> null){
                $a = $mas_level->id;
                do {
                    $b = $a;
                    $mas_level = Level::find()->where(['tree_diagram' => $id, 'parent_level' => $b ])->one();;
                    if ($mas_level <> null)
                        $a = $mas_level->id;
                } while ($mas_level <> null);
                $sequence->level = $b;
            }
            $sequence->node = $model->id;
            $sequence_model_count = Sequence::find()->where(['tree_diagram' => $id])->count();
            $sequence->priority = $sequence_model_count;
            $sequence->save();

            $data["id_level"] = $sequence->level;

            // Возвращение данных
            $response->data = $data;

            return $response;
        }
        return false;
    }
}