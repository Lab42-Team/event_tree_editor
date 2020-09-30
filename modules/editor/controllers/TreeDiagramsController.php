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
use app\modules\editor\models\Parameter;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\TreeDiagramSearch;
use yii\filters\AccessControl;
use app\components\EventTreeXMLGenerator;
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
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'update', 'delete', 'create', 'add-level', 'add-event', 'add-mechanism',
                    'edit-level', 'edit-event', 'edit-mechanism', 'delete-level', 'delete-event', 'delete-mechanism',
                    'add-relationship', 'delete-relationship'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'create', 'add-level', 'add-event', 'add-mechanism',
                            'edit-level', 'edit-event', 'edit-mechanism', 'delete-level', 'delete-event', 'delete-mechanism',
                            'add-relationship', 'delete-relationship'],
                        'roles' => ['@'],
                    ],
                ],
            ],
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
        if (!Yii::$app->user->isGuest) {
            $searchModel = new TreeDiagramSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else {
            $searchModel = new TreeDiagramSearch();
            $dataProvider = $searchModel->searchPublic(Yii::$app->request->queryParams);
        }

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
        $model->correctness = TreeDiagram::NOT_CHECKED_CORRECT;
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
        $mechanism_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::MECHANISM_TYPE])->all();
        $sequence_model_all = Sequence::find()->where(['tree_diagram' => $id])->all();
        $node_model_all = Node::find()->where(['tree_diagram' => $id])->all();
        $parameter_model_all = Parameter::find()->all();
        $level_model = new Level();
        $node_model = new Node();
        $parameter_model = new Parameter();

        $array_levels = Level::getLevelsArray($id);
        $array_levels_initial_without = Level::getWithoutInitialLevelsArray($id);

        if (Yii::$app->request->isPost) {
            $code_generator = new EventTreeXMLGenerator();
            $code_generator->generateEETDXMLCode($id);
        }

        return $this->render('visual-diagram', [
            'model' => $this->findModel($id),
            'level_model' => $level_model,
            'node_model' => $node_model,
            'parameter_model' => $parameter_model,
            'level_model_all' => $level_model_all,
            'level_model_count' => $level_model_count,
            'initial_event_model_all' =>$initial_event_model_all,
            'event_model_all' => $event_model_all,
            'mechanism_model_all' => $mechanism_model_all,
            'sequence_model_all' => $sequence_model_all,
            'node_model_all' => $node_model_all,
            'parameter_model_all' => $parameter_model_all,
            'array_levels' => $array_levels,
            'array_levels_initial_without' => $array_levels_initial_without,
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
                $data["parent_level"] = $model->parent_level;
            } else
                $data = ActiveForm::validate($model);

            $data["level_count"] = Level::find()->where(['tree_diagram' => $id])->count();

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
            // Определение полей модели уровня и валидация формы
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Условие проверки является ли событие инициирующим
                $i = Node::find()->where(['tree_diagram' => $id, 'type' => Node::INITIAL_EVENT_TYPE])->count();
                // Если инициирующие события есть
                if ($i > '0') {
                    // Тип присваивается константа "EVENT_TYPE" как событие
                    $model->type = Node::EVENT_TYPE;
                } else {
                    // Тип присваивается константа "INITIAL_EVENT_TYPE" как инициирующее событие
                    $model->type = Node::INITIAL_EVENT_TYPE;
                    $level = Level::find()->where(['tree_diagram' => $id, 'parent_level' => null])->one();
                    $model->level_id = $level->id;
                }
                // Успешный ввод данных
                $data["success"] = true;
                // Добавление нового уровня в БД
                $model->save();
                // Формирование данных о новом уровне
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["certainty_factor"] = $model->certainty_factor;
                $data["description"] = $model->description;
                $data["parent_node"] = $model->parent_node;
                $data["type"] = $model->type;

                $sequence = new Sequence();
                $sequence->tree_diagram = $id;
                $sequence->level = $model->level_id;
                $sequence->node = $model->id;
                $sequence_model_count = Sequence::find()->where(['tree_diagram' => $id])->count();
                $sequence->priority = $sequence_model_count;
                $sequence->save();

                $data["id_level"] = $model->level_id;
            } else
                $data = ActiveForm::validate($model);
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
                $data["parent_node"] = $model->parent_node;

                $sequence = new Sequence();
                $sequence->tree_diagram = $id;
                $sequence->level = $model->level_id;
                $sequence->node = $model->id;
                $sequence_model_count = Sequence::find()->where(['tree_diagram' => $id])->count();
                $sequence->priority = $sequence_model_count;
                $sequence->save();

                $data["id_level"] = $model->level_id;
            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionAddRelationship()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id')])->one();
            $model->parent_node = Yii::$app->request->post('parent_node_id');
            $model->updateAttributes(['parent_node']);

            $data["success"] = true;
            $data["n_id"] = $model->id;
            $data["p_n_id"] = $model->parent_node;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteRelationship()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $id_target = Yii::$app->request->post('id_target');

            $model = Node::find()->where(['id' => $id_target])->one();
            $model->parent_node = null;
            $model->updateAttributes(['parent_node']);

            $data["success"] = true;
            $data["id_target"] = $id_target;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionEditLevel()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Level::find()->where(['id' => Yii::$app->request->post('level_id_on_click')])->one();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном событии
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

    public function actionEditEvent()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id_on_click')])->one();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном событии
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["certainty_factor"] = $model->certainty_factor;
                $data["description"] = $model->description;
                $data["type"] = $model->type;
                $data["id_level"] = $model->level_id;
                $data["parent_node"] = $model->parent_node;


                if ($model->level_id != Yii::$app->request->post('level_id_on_click')){
                    $sequence = Sequence::find()->where(['node' => Yii::$app->request->post('node_id_on_click')])->one();
                    $sequence->level = $model->level_id;
                    $sequence->updateAttributes(['level']);

                    //очистить связи в бд-----------------
                    //очистить входящие связи
                    $node = Node::find()->where(['id' => $data["id"]])->one();
                    $node->parent_node = null;
                    $node->updateAttributes(['parent_node']);

                    //очистить выходящие связи
                    $node_out = Node::find()->where(['parent_node' => $data["id"]])->all();
                    foreach ($node_out as $elem){
                        $elem->parent_node = null;
                        $elem->updateAttributes(['parent_node']);
                    }
                }

            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionEditMechanism()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id_on_click')])->one();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном событии
                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
                $data["type"] = $model->type;
                $data["id_level"] = $model->level_id;
                $data["parent_node"] = $model->parent_node;


                if ($model->level_id != Yii::$app->request->post('level_id_on_click')){
                    $sequence = Sequence::find()->where(['node' => Yii::$app->request->post('node_id_on_click')])->one();
                    $sequence->level = $model->level_id;
                    $sequence->updateAttributes(['level']);

                    //очистить связи в бд-----------------
                    //очистить входящие связи
                    $node = Node::find()->where(['id' => $data["id"]])->one();
                    $node->parent_node = null;
                    $node->updateAttributes(['parent_node']);

                    //очистить выходящие связи
                    $node_out = Node::find()->where(['parent_node' => $data["id"]])->all();
                    foreach ($node_out as $elem){
                        $elem->parent_node = null;
                        $elem->updateAttributes(['parent_node']);
                    }
                }

            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteLevel()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $level_id_on_click = Yii::$app->request->post('level_id_on_click');

            //удаляемый уровень
            $level = Level::find()->where(['id' => $level_id_on_click])->one();
            //следующий уровень за удаляемым
            $level_descendent = Level::find()->where(['parent_level' => $level_id_on_click])->one();

            //если удаляется начальный уровень то удаляются механизмы на следующем
            if (($level->parent_level == null)&&($level_descendent != null)){
                $sequence_mas = Sequence::find()->where(['level' => $level_descendent->id])->all();
                foreach ($sequence_mas as $elem){
                    $node = Node::find()->where(['id' => $elem->node, 'type' => Node::MECHANISM_TYPE])->one();
                    if ($node != null){
                        $node_mas = Node::find()->where(['parent_node' => $node->id])->all();
                        foreach ($node_mas as $el){
                                $el->parent_node = null;
                                $el->updateAttributes(['parent_node']);
                        }
                        $node -> delete();
                    }
                }
                $data["initial"] = true;
            }

            //удаляем события и механизмы на удаляемом уровне
            $sequence_mas = Sequence::find()->where(['level' => $level_id_on_click])->all();
            foreach ($sequence_mas as $elem){
                $node_mas = Node::find()->where(['parent_node' => $elem->node])->all();
                foreach ($node_mas as $el){
                    $el->parent_node = null;
                    $el->updateAttributes(['parent_node']);
                }
                $node = Node::find()->where(['id' => $elem->node])->one();
                $node -> delete();
            }

            //удаляем уровень с учетом родительского уровня
            if ($level_descendent != null){
                $level_descendent->parent_level = $level->parent_level;
                $level_descendent->updateAttributes(['parent_level']);
                $data["id_level_descendent"] = $level_descendent->id;
            }
            $level -> delete();

            $data["success"] = true;



            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteEvent()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $node_id_on_click = Yii::$app->request->post('node_id_on_click');

            $node_descendent = Node::find()->where(['parent_node' => $node_id_on_click])->all();
            foreach ($node_descendent as $elem){
                $elem->parent_node = null;
                $elem->updateAttributes(['parent_node']);
            }

            $node = Node::find()->where(['id' => $node_id_on_click])->one();
            $node -> delete();

            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteMechanism()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $node_id_on_click = Yii::$app->request->post('node_id_on_click');

            $node_descendent = Node::find()->where(['parent_node' => $node_id_on_click])->all();
            foreach ($node_descendent as $elem){
                $elem->parent_node = null;
                $elem->updateAttributes(['parent_node']);
            }

            $node = Node::find()->where(['id' => $node_id_on_click])->one();
            $node -> delete();

            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionAddParameter()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели уровня
            $model = new Parameter();

            $model->node = Yii::$app->request->post('node_id_on_click');

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
                $data["operator"] = $model->getOperatorName();
                $data["value"] = $model->value;

            } else
                $data = ActiveForm::validate($model);
            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionEditParameter()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Parameter::find()->where(['id' => Yii::$app->request->post('parameter_id_on_click')])->one();

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;

                $data["id"] = $model->id;
                $data["name"] = $model->name;
                $data["description"] = $model->description;
                $data["operator"] = $model->getOperatorName();
                $data["value"] = $model->value;

            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteParameter()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Parameter::find()->where(['id' => Yii::$app->request->post('parameter_id_on_click')])->one();
            $data["node"] = $model->node;
            $model -> delete();

            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }

    public function actionCorrectness($id)
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = TreeDiagram::find()->where(['id' => $id])->one();

            //поиск несвязанных элементов
            $not_connected = Node::find()->where(['tree_diagram' => $id, 'parent_node' => null])->andwhere(['!=', 'type', Node::INITIAL_EVENT_TYPE])->all();

            //поиск пустых уровней
            $level = Level::find()->where(['tree_diagram' => $id])->all();
            $sequence = Sequence::find()->where(['tree_diagram' => $id])->all();
            $empty_level = array();//массив пустых уровней
            foreach ($level as $l){
                $del = false;
                foreach ($sequence as $s){
                    if ($s->level == $l->id){
                        $del = true;
                    }
                }
                if ($del == false){
                    array_push($empty_level, $l);
                }
            }

            //поиск уровней где нет механизмов
            $del = false;
            $level_without_mechanism = array();//массив уровней где нет механизмов
            if ($model->mode == TreeDiagram::EXTENDED_TREE_MODE){

                foreach ($level as $l) {
                    $with = false;
                    foreach ($empty_level as $e) {
                        if ($l->id == $e->id) {
                            $with = true;
                        }
                    }

                    if (($l->parent_level != null) &&($with == false)) {
                        $del = true;
                        foreach ($sequence as $s) {
                            if ($s->level == $l->id) {
                                $node = Node::find()->where(['id' => $s->node])->one();
                                if ($node->type == Node::MECHANISM_TYPE) {
                                    $del = false;
                                }
                            }
                        }
                    }

                    if (($del == true) && ($with == false)) {
                        array_push($level_without_mechanism, $l);
                    }
                }
            }

            $data["success"] = true;
            $data["not_connected"] = $not_connected;
            $data["empty_level"] = $empty_level;
            $data["level_without_mechanism"] = $level_without_mechanism;


            //изменение
            if (($not_connected != null) || ($empty_level != null) || ($level_without_mechanism != null)){
                $model->correctness = TreeDiagram::INCORRECTLY_CORRECT;
                $model->save();
            } else {
                $model->correctness = TreeDiagram::CORRECTLY_CORRECT;
                $model->save();
            }

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }
}