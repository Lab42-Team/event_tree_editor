<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Sequence;


class TreeDiagramController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii tree-diagram/create' . PHP_EOL;
    }

    /**
     * Команда создания событий по умолчанию.
     */
    public function actionCreate()
    {
        $tree_diagram = new TreeDiagram();
        if($tree_diagram->find()->count() == 0) {
            $tree_diagram = new TreeDiagram();
            $tree_diagram->name = 'Test';
            $tree_diagram->description = 'Test-tree-diagram test-tree-diagram';
            $tree_diagram->type = 0;
            $tree_diagram->status = 0;
            $tree_diagram->author = 1; /**  Внимание если автор действительно 1*/
            $this->log($tree_diagram->save());

            $j = 0;
            for ($i = 1; $i <= 10; $i++) {
                $level = new Level();
                $level->name = 'Level test' . $i;
                $level->description = 'Test-tree-diagram-level test-tree-diagram-level test-tree-diagram-level';
                if ($i==1){
                    $level->parent_level = null;
                } else {
                    $level->parent_level = $i-1;
                }
                $level->tree_diagram = $tree_diagram->id;
                $this->log($level->save());
            }

            $j = 0;
            for ($i = 1; $i <= 10; $i++) {
                if ($i==1){
                    $initial_event = new Node();
                    $initial_event->name = 'Initial event'. $i;
                    $initial_event->description = 'Test-tree-diagram-node test-tree-diagram-node';
                    $initial_event->operator = 1;
                    $initial_event->type = 0;
                    $initial_event->parent_node = null;
                    $initial_event->tree_diagram = $tree_diagram->id;
                    $initial_event->level_id = $i;
                    $this->log($initial_event->save());

                    $sequence =  new Sequence();
                    $sequence->tree_diagram = $tree_diagram->id;
                    $sequence->level = $i;
                    $sequence->node = $initial_event->id;
                    $j = $j + 1;
                    $sequence->priority = $j;
                    $this->log($sequence->save());
                } else {
                    $mechanism = new Node();
                    $mechanism->name = 'Mechanism'. $i;
                    $mechanism->description = 'Test-tree-diagram-mechanism test-tree-diagram-mechanism';
                    $mechanism->operator = 1;
                    $mechanism->type = 2;
                    $mechanism->tree_diagram = $tree_diagram->id;
                    $mechanism->level_id = $i;
                    $this->log($mechanism->save());

                    $sequence =  new Sequence();
                    $sequence->tree_diagram = $tree_diagram->id;
                    $sequence->level = $i;
                    $sequence->node = $mechanism->id;
                    $j = $j + 1;
                    $sequence->priority = $j;
                    $this->log($sequence->save());

                    $event = new Node();
                    $event->name = 'Event'. $i;
                    $event->description = 'Test-tree-diagram-node test-tree-diagram-node';
                    $event->operator = 1;
                    $event->type = 1;
                    $event->tree_diagram = $tree_diagram->id;
                    $event->level_id = $i;
                    $this->log($event->save());

                    $sequence =  new Sequence();
                    $sequence->tree_diagram = $tree_diagram->id;
                    $sequence->level = $i;
                    $sequence->node = $event->id;
                    $j = $j + 1;
                    $sequence->priority = $j;
                    $this->log($sequence->save());
                }
            }
        } else
            $this->stdout('Default tree diagram are created!', Console::FG_GREEN, Console::BOLD);
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