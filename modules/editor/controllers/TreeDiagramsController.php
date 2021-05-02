<?php

namespace app\modules\editor\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\bootstrap\ActiveForm;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;
use app\modules\editor\models\Parameter;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\OWLFileForm;
use app\modules\editor\models\TreeDiagramSearch;
use app\modules\editor\models\Import;
use yii\filters\AccessControl;
use app\components\EventTreeXMLGenerator;
use app\components\EventTreeXMLImport;

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
                    'add-relationship', 'delete-relationship', 'add-parameter', 'edit-parameter', 'delete-parameter',
                    'correctness', 'creation-template'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'create', 'add-level', 'add-event', 'add-mechanism',
                            'edit-level', 'edit-event', 'edit-mechanism', 'delete-level', 'delete-event', 'delete-mechanism',
                            'add-relationship', 'delete-relationship', 'add-parameter', 'edit-parameter', 'delete-parameter',
                            'correctness', 'creation-template'],
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
            $templates = TreeDiagram::find()->where(['tree_view' => TreeDiagram::TEMPLATE_TREE_VIEW])->all();

            $array_template = array();
            $i = 0;
            if ($templates != null){
                foreach ($templates as $elem){
                    $array_template[$i]['label'] = $elem->name;
                    $array_template[$i]['url'] = 'creation-template/' . $elem->id;
                    $i = $i + 1;
                }
            } else {
                $array_template[0]['label'] = Yii::t('app', 'TEMPLATES_DIAGRAMS_NOT_FOUND');
                $array_template[0]['url'] = '';
            }
        } else {
            $searchModel = new TreeDiagramSearch();
            $dataProvider = $searchModel->searchPublic(Yii::$app->request->queryParams);
            $array_template = array();
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'array_template' => $array_template,
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

                if ($model->mode == TreeDiagram::CLASSIC_TREE_MODE){
                    // Создание пустого уровня
                    $level = new Level();
                    $level->tree_diagram = $model->id;
                    $level->name = "Only";
                    $level->description = "";
                    $level->parent_level = null;
                    $level->save();
                }

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
     *
     * @param $id
     * @return TreeDiagram|null the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TreeDiagram::findOne($id)) !== null)
            return $model;

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
        $level_model_count = Level::find()->where(['tree_diagram' => $id])->count();//количество уровней
        $the_initial_event_is = Node::find()->where(['tree_diagram' => $id, 'type' => Node::INITIAL_EVENT_TYPE])->count();//переменная определяющая наличие начального события
        $initial_event_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::INITIAL_EVENT_TYPE])->all();
        $event_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::EVENT_TYPE])->all();
        $mechanism_model_all = Node::find()->where(['tree_diagram' => $id, 'type' => Node::MECHANISM_TYPE])->all();
        $sequence_model_all = Sequence::find()->where(['tree_diagram' => $id])->all();
        $node_model_all = Node::find()->where(['tree_diagram' => $id])->all();

        $parameter_all = Parameter::find()->all();
        $parameter_model_all = array();//массив пустых уровней
        foreach ($parameter_all as $p){
            foreach ($node_model_all as $n){
                if ($p->node == $n->id){
                    array_push($parameter_model_all, $p);
                }
            }
        }

        $level_model = new Level();
        $node_model = new Node();
        $parameter_model = new Parameter();
        $import_model = new Import();

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
            'import_model' => $import_model,
            'level_model_all' => $level_model_all,
            'level_model_count' => $level_model_count,
            'the_initial_event_is' => $the_initial_event_is,
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
                $data["level_count"] = Level::find()->where(['tree_diagram' => $id])->count();
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
                $data["level_count"] = Level::find()->where(['tree_diagram' => $id])->count();

                $diagram = TreeDiagram::find()->where(['id' => $id])->one();
                $data["mode"] = $diagram->mode;

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

                    //очистить связи в бд
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


    public function actionDeleteLevel($id)
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

            $data["level_count"] = Level::find()->where(['tree_diagram' => $id])->count();
            $data["the_initial_event_is"] = Node::find()->where(['tree_diagram' => $id, 'type' => Node::INITIAL_EVENT_TYPE])->count();
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
            $data["type"] = $node->type;
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

    public function actionMoveLevel($id)
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            //перемещаемый уровень
            $movable_level = Level::find()->where(['id' => Yii::$app->request->post('level_id_on_click')])->one();
            $parent_level_movable_level = $movable_level->parent_level;//id родительский уровень перемещаемого уровня

            //id уровня местонахождения
            $id_location_level = new Level;
            $id_location_level->name = "Test";
            $id_location_level->tree_diagram = $id;

            if ( $id_location_level->load(Yii::$app->request->post()) && $id_location_level->validate()) {

                // Успешный ввод данных
                $data["success"] = true;

                $level_after_movable_level = null;

                $level_after_location = Level::find()->where(['parent_level' => $id_location_level->movement_level])->one();
                if ($level_after_location != null){
                    $id_level_after_location = $level_after_location->id;
                } else {
                    $id_level_after_location = null;
                }

                if ( $id_level_after_location != $movable_level->id){
                    //меняем parent_level у уровня следующего после перемещаемого (убираем перемещаемый уровень)
                    $level_after_movable = Level::find()->where(['parent_level' => $movable_level->id])->one();
                    if ($level_after_movable != null){
                        $level_after_movable_level = $level_after_movable->id;
                        $level_after_movable->parent_level = $movable_level->parent_level;
                        $level_after_movable->updateAttributes(['parent_level']);
                    }

                    //меняем parent_level у перемещаемого уровня (перемещаем уровень)
                    $movable_level->parent_level = $id_location_level->movement_level;
                    $movable_level->updateAttributes(['parent_level']);

                    //меняем parent_level у уровня следующего после перемещенного (после перемещения )
                    if ($level_after_location != null){
                        $level_after_location->parent_level = $movable_level->id;
                        $level_after_location->updateAttributes(['parent_level']);
                    }
                }


                //Убираем связи элементов у затронутых уровней

                //удаляем связи идущие в узлы на перемещаемом уовне с предыдущего уровня
                $sequence_mas = Sequence::find()->where(['level' => $movable_level->id])->all();//все узлы на перемещаемом уровне
                foreach ($sequence_mas as $elem){
                    $node = Node::find()->where(['id' => $elem->node])->one();

                    //условие проверки что родительский узел находится на предыдущем уровне
                    $sequence = Sequence::find()->where(['node' => $node->parent_node])->one();
                    if ($sequence != null){
                        if ($sequence->level == $parent_level_movable_level){
                            $node->parent_node = null;
                            $node->updateAttributes(['parent_node']);
                        }
                    }
                }

                //удаляем связи идущие в узлы от перемещаемого уовня в следующий
                $sequence_mas = Sequence::find()->where(['level' => $movable_level->id])->all();//все узлы на перемещаемом уровне
                foreach ($sequence_mas as $elem){

                    $node_mas = Node::find()->where(['parent_node' => $elem->node])->all();//все узлы чьи parent_node на перемещаемом уровне
                    foreach ($node_mas as $el){

                        $seq = Sequence::find()->where(['node' => $el->id])->one();
                        if ($seq != null){
                            //исключаем узлы на том же уровне
                            if ($seq->level != $movable_level->id){
                                $el->parent_node = null;
                                $el->updateAttributes(['parent_node']);
                            }
                        }
                    }
                }

                //если уровень до которого нужно разместить существует
                if ($id_level_after_location != null){
                    //удаляем связи идущие в уровень до которого нужно разместить с предыдущего уровня
                    $sequence_mas = Sequence::find()->where(['level' => $id_level_after_location])->all();//все узлы на уровне до которого нужно разместить
                    foreach ($sequence_mas as $elem){
                        $node = Node::find()->where(['id' => $elem->node])->one();

                        //условие проверки что родительский узел находится на предыдущем уровне
                        $sequence = Sequence::find()->where(['node' => $node->parent_node])->one();
                        if ($sequence != null){
                            if ($sequence->level == $id_location_level->movement_level){
                                $node->parent_node = null;
                                $node->updateAttributes(['parent_node']);
                            }
                        }
                    }
                }

                $data["parent_level_movable_level"] = $parent_level_movable_level;//уровень до перемещаемого уровня
                $data["movable_level"] = $movable_level->id;//перемещаемый уровень
                $data["level_after_movable_level"] = $level_after_movable_level;//уровень после перемещаемого уровня
                $data["location_level"] = $id_location_level->movement_level;//уровень после которого нужно разместить
                $data["level_after_location"] =  $id_level_after_location;//уровень до которого нужно разместить

            } else
                $data = ActiveForm::validate($id_location_level);

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
                $data["operator_name"] = $model->getOperatorName();
                $data["operator"] = $model->operator;
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
                $data["operator_name"] = $model->getOperatorName();
                $data["operator"] = $model->operator;
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


    public function actionCreationTemplate($id)
    {
        //поиск TreeDiagram шаблона
        $template_treediagram = TreeDiagram::find()->where(['id' => $id])->one();

        //создание новой tree diagram из шаблона
        $model = new TreeDiagram();
        $model->author = Yii::$app->user->identity->getId();
        $model->correctness = TreeDiagram::NOT_CHECKED_CORRECT;
        $model->name =  Yii::t('app', 'TREE_DIAGRAMS_CREATED_FROM') . $template_treediagram->name;
        $model->description = $template_treediagram->description;
        $model->type = $template_treediagram->type;
        $model->status = $template_treediagram->status;
        $model->mode = $template_treediagram->mode;
        $model->tree_view = TreeDiagram::ORDINARY_TREE_VIEW;
        $model->save();

        //массив node (для копирования связей)
        $array_nodes = array();
        $j = 0;


        $template_level_count = Level::find()->where(['tree_diagram' => $id])->count();
        $template_parent_level = null;
        $parent_level = null;
        for ($i = 1; $i <= $template_level_count; $i++) {
            $template_level = Level::find()->where(['parent_level' => $template_parent_level, 'tree_diagram' => $id])->one();

            //создание нового level из шаблона
            $level = new Level();
            $level->name = $template_level->name;
            $level->description = $template_level->description;
            $level->parent_level = $parent_level;
            $level->tree_diagram = $model->id;
            $level->save();

            $template_parent_level = $template_level->id;
            $parent_level = $level->id;

            $template_sequences = Sequence::find()->where(['level' => $template_parent_level, 'tree_diagram' => $id])->all();
            foreach ($template_sequences as $s){

                $template_node = Node::find()->where(['id' => $s->node])->one();
                //создание нового node из шаблона
                $node = new Node();
                $node->name = $template_node->name;
                $node->certainty_factor = $template_node->certainty_factor;
                $node->description = $template_node->description;
                $node->operator = $template_node->operator;
                $node->type = $template_node->type;
                $node->parent_node = $template_node->parent_node;
                $node->tree_diagram = $model->id;
                $node->level_id = $parent_level;
                $node->save();

                $array_nodes[$j]['node_template'] = $template_node->id;
                $array_nodes[$j]['node'] = $node->id;
                $j = $j+1;

                //поиск всех parameter из шаблона по id node
                $template_parameters = Parameter::find()->where(['node' => $template_node->id])->all();
                foreach ($template_parameters as $p){
                    //создание нового parameter из шаблона
                    $parameter = new Parameter();
                    $parameter->name = $p->name;
                    $parameter->description = $p->description;
                    $parameter->operator = $p->operator;
                    $parameter->value = $p->value;
                    $parameter->node = $node->id;
                    $parameter->save();
                }

                //создание нового sequence из шаблона
                $sequence = new Sequence();
                $sequence->tree_diagram = $model->id;
                $sequence->level = $parent_level;
                $sequence->node = $node->id;
                $sequence_model_count = Sequence::find()->where(['tree_diagram' => $model->id])->count();
                $sequence->priority = $sequence_model_count;
                $sequence->save();
            }
        }

        $nodes = Node::find()->where(['tree_diagram' => $model->id])->all();
        foreach ($nodes as $n){
            for ($i = 0; $i < $j; $i++) {
                if ($n->parent_node == $array_nodes[$i]['node_template']){
                    $n->parent_node = $array_nodes[$i]['node'];
                    $n->updateAttributes(['parent_node']);
                }
            }
        }

        Yii::$app->getSession()->setFlash('success',
                Yii::t('app', 'TREE_DIAGRAMS_PAGE_MESSAGE_CREATE_TREE_DIAGRAM'));

        return $this->redirect(['view', 'id' => $model->id]);
    }



    public function actionImport($id)
    {
        $model = $this->findModel($id);
        $import_model = new Import();

        //вывод сообщения об очистки если диаграмма не пуста
        $tree_diagram = TreeDiagram::find()->where(['id' => $id])->one();
        if ($tree_diagram->mode == TreeDiagram::EXTENDED_TREE_MODE){
            $count = Level::find()->where(['tree_diagram' => $id])->count();
            if ($count > 0){
                Yii::$app->getSession()->setFlash('warning',
                    Yii::t('app', 'MESSAGE_CLEANING'));
            }
        }
        if ($tree_diagram->mode == TreeDiagram::CLASSIC_TREE_MODE){
            $count = Node::find()->where(['tree_diagram' => $id])->count();
            if ($count > 0){
                Yii::$app->getSession()->setFlash('warning',
                    Yii::t('app', 'MESSAGE_CLEANING'));
            }
        }


        if (Yii::$app->request->isPost) {
            $import_model->file_name = UploadedFile::getInstance($import_model, 'file_name');

            if ($import_model->upload()) {

                $file = simplexml_load_file('uploads/temp.xml');

                //выявление расширенного или классического дерева
                if (((string) $file["mode"] == "Расширенное дерево") or ((string) $file["mode"] == "Extended tree")){
                    $mode = TreeDiagram::EXTENDED_TREE_MODE;
                }
                if (((string) $file["mode"] == "Классическое дерево") or ((string) $file["mode"] == "Classic tree")){
                    $mode = TreeDiagram::CLASSIC_TREE_MODE;
                }

                if ($tree_diagram->mode == $mode) {
                    //импорт xml файла
                    $generator = new EventTreeXMLImport();
                    $generator->importXMLCode($id, $file);

                    //удаление файла
                    unlink('uploads/temp.xml');

                    Yii::$app->getSession()->setFlash('success',
                        Yii::t('app', 'TREE_DIAGRAMS_PAGE_MESSAGE_IMPORT_TREE_DIAGRAM'));

                    return $this->render('view', [
                        'model' => $this->findModel($id),
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error',
                        Yii::t('app', 'MESSAGE_IMPORT_ERROR_INCOMPATIBLE_MODE'));

                    return $this->render('import', [
                        'model' => $model,
                        'import_model' => $import_model,
                    ]);
                }
            }
        }

        return $this->render('import', [
            'model' => $model,
            'import_model' => $import_model,
        ]);
    }


    public function actionSaveIndent()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $node = Node::find()->where(['id' => Yii::$app->request->post('node_id')])->one();
            $node->indent_x = Yii::$app->request->post('indent_x');
            $node->indent_y = Yii::$app->request->post('indent_y');
            $node->updateAttributes(['indent_x']);
            $node->updateAttributes(['indent_y']);

            $data["indent_x"] = $node->indent_x;
            $data["indent_y"] = $node->indent_y;
            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionAddEventComment()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id_on_click')])->one();

            $sequence = Sequence::find()->where(['node' => $model->id])->one();

            $model->level_id = $sequence->level;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном событии
                $data["id"] = $model->id;
                $data["comment"] = $model->comment;
                $data["level_id"] = $model->level_id;
            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionEditEventComment()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id_on_click')])->one();

            $sequence = Sequence::find()->where(['node' => $model->id])->one();

            $model->level_id = $sequence->level;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                // Успешный ввод данных
                $data["success"] = true;
                // Формирование данных об измененном событии
                $data["id"] = $model->id;
                $data["comment"] = $model->comment;
            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteEventComment()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Node::find()->where(['id' => Yii::$app->request->post('node_id_on_click')])->one();

            $model->comment = null;
            $model->updateAttributes(['comment']);
            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionAddLevelComment()
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
                $data["comment"] = $model->comment;

            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionEditLevelComment()
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
                $data["comment"] = $model->comment;
            } else
                $data = ActiveForm::validate($model);

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    public function actionDeleteLevelComment()
    {
        //Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $model = Level::find()->where(['id' => Yii::$app->request->post('level_id_on_click')])->one();

            $model->comment = null;
            $model->updateAttributes(['comment']);
            $data["success"] = true;

            // Возвращение данных
            $response->data = $data;
            return $response;
        }
        return false;
    }


    /**
     * Загрузка онтологии вформате OWL на сервер во временную папку.
     *
     * @param $id - идентификатор диаграммы дерева событий
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUploadOntology($id)
    {
        // Поиск модели диаграммы дерева событий по id
        $model = $this->findModel($id);
        // Создание формы файла OWL-онтологии
        $file_form = new OWLFileForm();

        // Если POST-запрос
        if (Yii::$app->request->isPost) {
            $file_form->owl_file = UploadedFile::getInstance($file_form, 'owl_file');
            if ($file_form->validate()) {
                // Временное сохранение загруженного файла онтологии
                $file_form->owl_file->saveAs('uploads/uploaded-ontology.owl');
                // Вывод сообщения об успехной загрузке файла онтологии
                Yii::$app->getSession()->setFlash('success',
                    Yii::t('app', 'TREE_DIAGRAMS_PAGE_MESSAGE_UPLOAD_ONTOLOGY'));

                return $this->redirect(['convert-ontology', 'id' => $model->id]);
            }
        }

        return $this->render('upload-ontology', [
            'model' => $model,
            'file_form' => $file_form
        ]);
    }

    /**
     * Преобразование OWL-онтологии в классическую диаграмму деревьев событий.
     *
     * @param $id - идентификатор диаграммы дерева событий
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionConvertOntology($id)
    {
        // Если существует файл с OWL-онтологией
        if (file_exists(Yii::$app->basePath . '/web/uploads/uploaded-ontology.owl')) {
            // Получение данных из OWL-онтологии
            $xml_data = simplexml_load_file('uploads/uploaded-ontology.owl');
        } else
            $xml_data = null;

        return $this->render('convert-ontology', [
            'model' => $this->findModel($id),
            'xml_data' => $xml_data
        ]);
    }
}