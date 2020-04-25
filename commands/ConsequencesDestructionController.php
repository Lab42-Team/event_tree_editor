<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;


class ConsequencesDestructionController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii consequences-destruction/create' . PHP_EOL;
    }

    /**
     * Команда создания событий по умолчанию.
     */
    public function actionCreate()
    {
        //здесь проверка нужна
        $tree_diagram = new TreeDiagram();
        if ($tree_diagram->find()->where(['name' => 'Последствия в результате разрушения емкости'])->count() == 0) {
            $tree_diagram = new TreeDiagram();
            $tree_diagram->name = 'Последствия в результате разрушения емкости';
            $tree_diagram->description = 'Последствия в результате разрушения емкости «16/1 цеха 71-75»';
            $tree_diagram->type = 0;
            $tree_diagram->status = 0;
            $tree_diagram->author = 1; /**  Внимание если автор действительно 1*/
            $this->log($tree_diagram->save());


            //первый уровень
            $level = new Level();
            $level->name = 'Аварийная ситуация';
            $level->description = 'Поддерево событий стадии нежелательного процесса «аварийная ситуация»';
            $level->parent_level = null;
            $level->tree_diagram = $tree_diagram->id;
            $this->log($level->save());
            $level1 = $level->id;

            $initial_event = new Node();
            $initial_event->name = 'Отказ детали «емкость 16/1»';
            $initial_event->description = 'Отказ детали «емкость 16/1»';
            $initial_event->operator = 1;
            $initial_event->type = 0;
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
            $level->name = 'Аварийная ситуация';
            $level->description = 'Поддерево событий стадии нежелательного процесса «аварийная ситуация»';
            $level->parent_level = $level1;
            $level->tree_diagram = $tree_diagram->id;
            $this->log($level->save());
            $level2 = $level->id;

            $mechanism = new Node();
            $mechanism->name = 'Mechanism 1';
            $mechanism->description = 'Test-tree-diagram-mechanism';
            $mechanism->operator = 1;
            $mechanism->type = 2;
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
            $event->description = 'Количество выброшенного вещества - Q; Площадь разлива в поддон/обваловку - S;';
            $event->operator = 1;
            $event->type = 1;
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
            $event->description = 'Эквивалентное количество вещества - Q ; Глубина заражения первичным облаком - Г ; Площадь возможного заражения для первичного облака - S ';
            $event->operator = 1;
            $event->type = 1;
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
            $mechanism->description = 'Test-tree-diagram-mechanism';
            $mechanism->operator = 1;
            $mechanism->type = 2;
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
            $event->description = 'Эквивалентное количество вещества - Q; Глубина заражения вторичным облаком - Г;';
            $event->operator = 1;
            $event->type = 1;
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
            $event->description = 'Полная глубина заражения - Г; Предельно возможная глубина переноса воздушных масс - Гп; 
                                    Глубина заражения - ; Площадь зоны фактического заражения - Sф;';
            $event->operator = 1;
            $event->type = 1;
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
            $mechanism->description = 'Test-tree-diagram-mechanism';
            $mechanism->operator = 1;
            $mechanism->type = 2;
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
            $event->description = 'Количество погибших; Количество пострадавших; Продолжительность поражающего действия – T;';
            $event->operator = 1;
            $event->type = 1;
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
            $event->description = 'Количество погибших; Количество пострадавших; Продолжительность поражающего действия – T;';
            $event->operator = 1;
            $event->type = 1;
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