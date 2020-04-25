<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;


class EventTreeController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii event-tree/create' . PHP_EOL;
    }

    /**
     * Команда создания событий по умолчанию.
     */
    public function actionCreate()
    {
        //здесь проверка нужна
        $tree_diagram = new TreeDiagram();
        if ($tree_diagram->find()->where(['name' => 'Дерево событий'])->count() == 0) {
            $tree_diagram = new TreeDiagram();
            $tree_diagram->name = 'Дерево событий';
            $tree_diagram->description = 'Описание дерева событий';
            $tree_diagram->type = 0;
            $tree_diagram->status = 0;
            $tree_diagram->author = 1; /**  Внимание если автор действительно 1*/
            $this->log($tree_diagram->save());


            //первый уровень
            $level = new Level();
            $level->name = 'Уровень 1';
            $level->description = 'Описание';
            $level->parent_level = null;
            $level->tree_diagram = $tree_diagram->id;
            $this->log($level->save());


            $initial_event = new Node();
            $initial_event->name = 'Исходное техническое состояние объекта';
            $initial_event->description = 'Материал – низколегированная сталь; Остаточные макронапряжения; Нагрузка – 
                                            растягивающие механические и термические напряжения; Среда – активная';
            $initial_event->operator = 1;
            $initial_event->type = 0;
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


            $event = new Node();
            $event->name = 'Субмикротрещины';
            $event->description = 'Местоположение – «на поверхности»; длина < 100 нм';
            $event->operator = 1;
            $event->type = 1;
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


            $event = new Node();
            $event->name = 'Питтинги';
            $event->description = 'Местоположение – «на поверхности»; диаметр – 1-2 мм; глубина - 1-2 мм';
            $event->operator = 1;
            $event->type = 1;
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


            $event = new Node();
            $event->name = 'Язвы';
            $event->description = 'Местоположение – «на поверхности»; диаметр – 3-5 мм; глубина - 1-3 мм';
            $event->operator = 1;
            $event->type = 1;
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


            $event = new Node();
            $event->name = 'Микротрещины';
            $event->description = 'Длина < 500 мкм; источник – «питтинги»';
            $event->operator = 1;
            $event->type = 1;
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


            $event = new Node();
            $event->name = 'Макротрещины';
            $event->description = 'Направление –  «поперечные»; длина < 7 мм; глубина < 4 мм';
            $event->operator = 1;
            $event->type = 1;
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


            $event = new Node();
            $event->name = 'Сквозная трещина';
            $event->description = 'Направление – «поперечная»; длина ≈ 80 мм; глубина ≈ 45 мм';
            $event->operator = 1;
            $event->type = 1;
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
        } else
            $this->stdout('Event tree diagram are created!', Console::FG_GREEN, Console::BOLD);
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