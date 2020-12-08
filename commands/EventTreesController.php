<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;
use app\modules\editor\models\Parameter;
use app\modules\main\models\User;


class EventTreesController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii event-trees/create' . PHP_EOL;
    }

    /**
     * Команда создания событий по умолчанию.
     */
    public function actionCreate()
    {
        $user = User::find()->where(['username' => 'admin'])->one();
        if ($user != null){
            //Элемент "деталь" из блока надежность
            $tree_diagram = new TreeDiagram();
            if ($tree_diagram->find()->where(['name' => 'Элемент "деталь" из блока надежность'])->count() == 0) {
                $tree_diagram = new TreeDiagram();
                $tree_diagram->name = 'Элемент "деталь" из блока надежность';
                $tree_diagram->description = 'Элемент "деталь" из блока надежность';
                $tree_diagram->type = TreeDiagram::EVENT_TREE_TYPE;
                $tree_diagram->status = TreeDiagram::PUBLIC_STATUS;
                $tree_diagram->author = $user->id;
                $tree_diagram->mode = TreeDiagram::CLASSIC_TREE_MODE;
                $tree_diagram->correctness = TreeDiagram::NOT_CHECKED_CORRECT;
                $tree_diagram->tree_view = TreeDiagram::ORDINARY_TREE_VIEW;
                $this->log($tree_diagram->save());

                //первый уровень
                $level = new Level();
                $level->name = 'Деталь';
                $level->description = 'Описание';
                $level->parent_level = null;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());


                $initial_event = new Node();
                $initial_event->name = 'Исходное техническое состояние объекта';
                $initial_event->certainty_factor = 0;
                $initial_event->description = 'Материал – низколегированная сталь; Остаточные макронапряжения; Нагрузка – 
                                            растягивающие механические и термические напряжения; Среда – активная';
                $initial_event->operator = Node::AND_OPERATOR;
                $initial_event->type = Node::INITIAL_EVENT_TYPE;;
                $initial_event->parent_node = null;
                $initial_event->tree_diagram = $tree_diagram->id;
                $initial_event->level_id = $level->id;
                $this->log($initial_event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $initial_event->id;
                $sequence->priority = 0;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Материал';
                    $parameter->description = 'Материал – низколегированная сталь;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'Низколегированная сталь';
                    $parameter->node = $initial_event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Макронапряжения';
                    $parameter->description = 'Остаточные макронапряжения;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'Остаточные';
                    $parameter->node = $initial_event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Нагрузка';
                    $parameter->description = 'Нагрузка – растягивающие механические и термические напряжения;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'Растягивающие механические и термические напряжения';
                    $parameter->node = $initial_event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Среда';
                    $parameter->description = 'Среда – активная;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'Активная';
                    $parameter->node = $initial_event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Субмикротрещины';
                $event->certainty_factor = 0;
                $event->description = 'Местоположение – «на поверхности»; длина < 100 нм';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $initial_event->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Местоположение';
                    $parameter->description = 'Местоположение – «на поверхности»';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'на поверхности';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Длина';
                    $parameter->description = 'Длина < 100 нм';
                    $parameter->operator = Parameter::LESS_OPERATOR;
                    $parameter->value = '100 нм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Питтинги';
                $event->certainty_factor = 0;
                $event->description = 'Местоположение – «на поверхности»; диаметр – 1-2 мм; глубина - 1-2 мм';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $initial_event->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Местоположение';
                    $parameter->description = 'Местоположение – «на поверхности»;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'на поверхности';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Диаметр';
                    $parameter->description = 'Диаметр – 1-2 мм;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '1-2 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Глубина';
                    $parameter->description = 'Глубина - 1-2 мм;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '1-2 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Язвы';
                $event->certainty_factor = 0;
                $event->description = 'Местоположение – «на поверхности»; диаметр – 3-5 мм; глубина - 1-3 мм';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $initial_event->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Местоположение';
                    $parameter->description = 'Местоположение – «на поверхности»;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'на поверхности';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Диаметр';
                    $parameter->description = 'Диаметр – 3-5 мм;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '3-5 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Глубина';
                    $parameter->description = 'Глубина - 1-3 мм;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '1-3 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Микротрещины';
                $event->certainty_factor = 0;
                $event->description = 'Длина < 500 мкм; источник – «питтинги»';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $initial_event->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Длина';
                    $parameter->description = 'Длина < 500 мкм;';
                    $parameter->operator = Parameter::LESS_OPERATOR;
                    $parameter->value = '500 мкм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Источник';
                    $parameter->description = 'Источник – «питтинги»;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '«питтинги»';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Макротрещины';
                $event->certainty_factor = 0;
                $event->description = 'Направление –  «поперечные»; длина < 7 мм; глубина < 4 мм';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $initial_event->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());
                $event1 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Направление';
                    $parameter->description = 'Направление –  «поперечные»;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '«поперечные»';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Длина';
                    $parameter->description = 'Длина < 7 мм;';
                    $parameter->operator = Parameter::LESS_OPERATOR;
                    $parameter->value = '7 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Глубина';
                    $parameter->description = 'Глубина < 4 мм;';
                    $parameter->operator = Parameter::LESS_OPERATOR;
                    $parameter->value = '4 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                $event = new Node();
                $event->name = 'Сквозная трещина';
                $event->certainty_factor = 0;
                $event->description = 'Направление – «поперечная»; длина ≈ 80 мм; глубина ≈ 45 мм';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event1;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level->id;
                $this->log($event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level->id;
                $sequence->node = $event->id;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Направление';
                    $parameter->description = 'Направление –  «поперечные»;';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = '«поперечные»';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Длина';
                    $parameter->description = 'Длина ≈ 80 мм;';
                    $parameter->operator = Parameter::APPROXIMATELY_EQUAL_OPERATOR;
                    $parameter->value = '80 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Глубина';
                    $parameter->description = 'Глубина ≈ 45 мм;';
                    $parameter->operator = Parameter::APPROXIMATELY_EQUAL_OPERATOR;
                    $parameter->value = '45 мм';
                    $parameter->node = $event->id;
                    $this->log($parameter->save());
            } else {
                $this->stdout('The event tree of the part element from the reliability block is created. - - - - - - - -', Console::FG_GREEN, Console::BOLD);
            }


            //Последствия в результате разрушения емкости
            $tree_diagram = new TreeDiagram();
            if ($tree_diagram->find()->where(['name' => 'Последствия в результате разрушения емкости'])->count() == 0) {
                $tree_diagram = new TreeDiagram();
                $tree_diagram->name = 'Последствия в результате разрушения емкости';
                $tree_diagram->description = 'Последствия в результате разрушения емкости «16/1 цеха 71-75»';
                $tree_diagram->type = TreeDiagram::EVENT_TREE_TYPE;
                $tree_diagram->status = TreeDiagram::PUBLIC_STATUS;
                $tree_diagram->author = $user->id;
                $tree_diagram->mode = TreeDiagram::EXTENDED_TREE_MODE;
                $tree_diagram->correctness = TreeDiagram::NOT_CHECKED_CORRECT;
                $tree_diagram->tree_view = TreeDiagram::ORDINARY_TREE_VIEW;
                $this->log($tree_diagram->save());


                //первый уровень
                $level = new Level();
                $level->name = 'Аварийная ситуация (первый этап)';
                $level->description = 'Поддерево событий стадии нежелательного процесса «аварийная ситуация»';
                $level->parent_level = null;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level1 = $level->id;

                $initial_event = new Node();
                $initial_event->name = 'Отказ детали «емкость 16/1»';
                $initial_event->certainty_factor = null;
                $initial_event->description = 'Отказ детали «емкость 16/1»';
                $initial_event->operator = Node::AND_OPERATOR;
                $initial_event->type = Node::INITIAL_EVENT_TYPE;;
                $initial_event->parent_node = null;
                $initial_event->tree_diagram = $tree_diagram->id;
                $initial_event->level_id = $level1;
                $this->log($initial_event->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $initial_event->id;
                $sequence->priority = 0;
                $this->log($sequence->save());


                //второй уровень
                $level = new Level();
                $level->name = 'Аварийная ситуация (второй этап)';
                $level->description = 'Поддерево событий стадии нежелательного процесса «аварийная ситуация»';
                $level->parent_level = $level1;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level2 = $level->id;

                $mechanism = new Node();
                $mechanism->name = 'Mechanism 1';
                $mechanism->certainty_factor = null;
                $mechanism->description = 'Test-tree-diagram-mechanism';
                $mechanism->operator = Node::AND_OPERATOR;
                $mechanism->type = Node::MECHANISM_TYPE;
                $mechanism->parent_node = $initial_event->id;
                $mechanism->tree_diagram = $tree_diagram->id;
                $mechanism->level_id = $level2;
                $this->log($mechanism->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level2;
                $sequence->node = $mechanism->id;
                $sequence->priority = 1;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Разлив «СДЯВ»';
                $event->certainty_factor = null;
                $event->description = 'Количество выброшенного вещества - Q; Площадь разлива в поддон/обваловку - S;';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $mechanism->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level2;
                $this->log($event->save());
                $event1 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level2;
                $sequence->node = $event1;
                $sequence->priority = 2;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Образование первичного облака';
                $event->certainty_factor = null;
                $event->description = 'Эквивалентное количество вещества - Q ; Глубина заражения первичным облаком - Г ; Площадь возможного заражения для первичного облака - S ';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event1;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level2;
                $this->log($event->save());
                $event2 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level2;
                $sequence->node = $event2;
                $sequence->priority = 3;
                $this->log($sequence->save());


                //третий уровень
                $level = new Level();
                $level->name = 'Авария';
                $level->description = 'Поддерево событий стадии нежелательного процесса «авария»';
                $level->parent_level = $level2;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level3 = $level->id;

                $mechanism = new Node();
                $mechanism->name = 'Mechanism 2';
                $mechanism->certainty_factor = null;
                $mechanism->description = 'Test-tree-diagram-mechanism';
                $mechanism->operator = Node::AND_OPERATOR;
                $mechanism->type = Node::MECHANISM_TYPE;
                $mechanism->parent_node = $event2;
                $mechanism->tree_diagram = $tree_diagram->id;
                $mechanism->level_id = $level3;
                $this->log($mechanism->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level3;
                $sequence->node = $mechanism->id;
                $sequence->priority = 4;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Образование вторичного облака';
                $event->certainty_factor = null;
                $event->description = 'Эквивалентное количество вещества - Q; Глубина заражения вторичным облаком - Г;';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $mechanism->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level3;
                $this->log($event->save());
                $event4 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level3;
                $sequence->node = $event4;
                $sequence->priority = 5;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Заражение территории';
                $event->certainty_factor = null;
                $event->description = 'Полная глубина заражения - Г; Предельно возможная глубина переноса воздушных масс - Гп; 
                                    Глубина заражения - ; Площадь зоны фактического заражения - Sф;';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event4;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level3;
                $this->log($event->save());
                $event5 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level3;
                $sequence->node = $event5;
                $sequence->priority = 6;
                $this->log($sequence->save());


                //четвертый уровень
                $level = new Level();
                $level->name = 'ЧС';
                $level->description = 'Поддерево событий стадии нежелательного процесса «ЧС»';
                $level->parent_level = $level3;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level4 = $level->id;

                $mechanism = new Node();
                $mechanism->name = 'Mechanism 3';
                $mechanism->certainty_factor = null;
                $mechanism->description = 'Test-tree-diagram-mechanism';
                $mechanism->operator = Node::AND_OPERATOR;
                $mechanism->type = Node::MECHANISM_TYPE;
                $mechanism->parent_node = $event5;
                $mechanism->tree_diagram = $tree_diagram->id;
                $mechanism->level_id = $level4;
                $this->log($mechanism->save());

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level4;
                $sequence->node = $mechanism->id;
                $sequence->priority = 7;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Воздействие на персонал';
                $event->certainty_factor = null;
                $event->description = 'Количество погибших; Количество пострадавших; Продолжительность поражающего действия – T;';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $mechanism->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level4;
                $this->log($event->save());
                $event6 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level4;
                $sequence->node = $event6;
                $sequence->priority = 8;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Воздействие на население';
                $event->certainty_factor = null;
                $event->description = 'Количество погибших; Количество пострадавших; Продолжительность поражающего действия – T;';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $mechanism->id;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level4;
                $this->log($event->save());
                $event7 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level4;
                $sequence->node = $event7;
                $sequence->priority = 9;
                $this->log($sequence->save());

            } else
                $this->stdout('Consequences of destruction tree diagram are created!', Console::FG_GREEN, Console::BOLD);


            //Тестовая
            $tree_diagram = new TreeDiagram();
            if ($tree_diagram->find()->where(['name' => 'Тестовая'])->count() == 0) {
                $tree_diagram = new TreeDiagram();
                $tree_diagram->name = 'Тестовая';
                $tree_diagram->description = 'Тестовая диаграмма';
                $tree_diagram->type = TreeDiagram::EVENT_TREE_TYPE;
                $tree_diagram->status = TreeDiagram::PUBLIC_STATUS;
                $tree_diagram->author = $user->id;
                $tree_diagram->mode = TreeDiagram::EXTENDED_TREE_MODE;
                $tree_diagram->correctness = TreeDiagram::NOT_CHECKED_CORRECT;
                $tree_diagram->tree_view = TreeDiagram::ORDINARY_TREE_VIEW;
                $this->log($tree_diagram->save());


                //первый уровень
                $level = new Level();
                $level->name = 'Уровень1';
                $level->description = 'Тестовый уровень 1';
                $level->parent_level = null;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level1 = $level->id;

                $initial_event = new Node();
                $initial_event->name = 'Событие1';
                $initial_event->certainty_factor = null;
                $initial_event->description = 'Описание события 1';
                $initial_event->operator = Node::AND_OPERATOR;
                $initial_event->type = Node::INITIAL_EVENT_TYPE;;
                $initial_event->parent_node = null;
                $initial_event->tree_diagram = $tree_diagram->id;
                $initial_event->level_id = $level1;
                $this->log($initial_event->save());
                $event1 = $initial_event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event1;
                $sequence->priority = 0;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Событие2';
                $event->certainty_factor = null;
                $event->description = 'Описание события 2';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event1;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level1;
                $this->log($event->save());
                $event2 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event2;
                $sequence->priority = 1;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Событие3';
                $event->certainty_factor = null;
                $event->description = 'Описание события 3';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event1;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level1;
                $this->log($event->save());
                $event3 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event3;
                $sequence->priority = 2;
                $this->log($sequence->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр1';
                    $parameter->description = 'Описание параметра 1';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр2';
                    $parameter->description = 'Описание параметра 2';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр3';
                    $parameter->description = 'Описание параметра 3';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр4';
                    $parameter->description = 'Описание параметра 4';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр5';
                    $parameter->description = 'Описание параметра 5';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());

                    $parameter = new Parameter();
                    $parameter->name = 'Параметр6';
                    $parameter->description = 'Описание параметра 6';
                    $parameter->operator = Parameter::EQUALLY_OPERATOR;
                    $parameter->value = 'значение';
                    $parameter->node = $event3;
                    $this->log($parameter->save());


                $event = new Node();
                $event->name = 'Событие4';
                $event->certainty_factor = null;
                $event->description = 'Описание события 4';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event2;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level1;
                $this->log($event->save());
                $event4 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event4;
                $sequence->priority = 3;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Событие5';
                $event->certainty_factor = null;
                $event->description = 'Описание события 5';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event2;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level1;
                $this->log($event->save());
                $event5 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event5;
                $sequence->priority = 4;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Событие6';
                $event->certainty_factor = null;
                $event->description = 'Описание события 6';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $event2;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level1;
                $this->log($event->save());
                $event6 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level1;
                $sequence->node = $event6;
                $sequence->priority = 5;
                $this->log($sequence->save());



                //второй уровень
                $level = new Level();
                $level->name = 'Уровень2';
                $level->description = 'Тестовый уровень 2';
                $level->parent_level = $level1;
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
                $level2 = $level->id;

                $mechanism = new Node();
                $mechanism->name = 'Механизм1';
                $mechanism->certainty_factor = null;
                $mechanism->description = 'Описание механизма 1';
                $mechanism->operator = Node::AND_OPERATOR;
                $mechanism->type = Node::MECHANISM_TYPE;
                $mechanism->parent_node = $event4;
                $mechanism->tree_diagram = $tree_diagram->id;
                $mechanism->level_id = $level2;
                $this->log($mechanism->save());
                $mechanism1 = $mechanism->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level2;
                $sequence->node = $mechanism->id;
                $sequence->priority = 6;
                $this->log($sequence->save());


                $event = new Node();
                $event->name = 'Событие7';
                $event->certainty_factor = null;
                $event->description = 'Описание события 7';
                $event->operator = Node::AND_OPERATOR;
                $event->type = Node::EVENT_TYPE;
                $event->parent_node = $mechanism1;
                $event->tree_diagram = $tree_diagram->id;
                $event->level_id = $level2;
                $this->log($event->save());
                $event7 = $event->id;

                $sequence =  new Sequence();
                $sequence->tree_diagram = $tree_diagram->id;
                $sequence->level = $level2;
                $sequence->node = $event7;
                $sequence->priority = 7;
                $this->log($sequence->save());

            } else
                $this->stdout('Test tree diagram are created!', Console::FG_GREEN, Console::BOLD);

        } else
            $this->stdout('Create a user "admin"', Console::FG_GREEN, Console::BOLD);
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