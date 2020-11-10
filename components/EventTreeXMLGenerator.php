<?php

namespace app\components;

use Yii;
use DOMDocument;
use app\modules\editor\models\TreeDiagram;
use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Parameter;
use app\modules\editor\models\Sequence;




class EventTreeXMLGenerator
{
    public  $node_element;
    public  $count_child;

    public static function drawingParameter($xml, $id_event, $xml_element)
    {
        //подбор всех Parameter
        $parameter_elements = Parameter::find()->where(['node' => $id_event])->all();
        if ($parameter_elements != null){
            foreach ($parameter_elements as $p_elem){
                //отрисовка "Parameter"
                $parameter_element = $xml->createElement('Parameter');
                $parameter_element->setAttribute('id', $p_elem->id);
                $parameter_element->setAttribute('name', $p_elem->name);
                $parameter_element->setAttribute('description', $p_elem->description);
                $parameter_element->setAttribute('operator', $p_elem->getOperatorName());
                $parameter_element->setAttribute('value', $p_elem->value);
                $xml_element->appendChild($parameter_element);
            }
        }

    }


    public static function drawingEvent($xml, $event, $xml_element, $id_level)
    {
        $count_child = 0;
        //Проверка на том ли уровне событие
        $sequence_parent_node = Sequence::find()->where(['node' => $event->id])->one();
        if (($sequence_parent_node!= null) && ($sequence_parent_node->level == $id_level)){

            // добавление "Event"
            $node_element = $xml->createElement('Event');
            $node_element->setAttribute('id', $event->id);
            if ($event->parent_node != null){
                $node_element->setAttribute('parent_node', $event->parent_node);
            }
            $node_element->setAttribute('type', $event->getTypeName());
            $node_element->setAttribute('name', $event->name);
            $node_element->setAttribute('description', $event->description);
            $node_element->setAttribute('certainty_factor', $event->certainty_factor);
            $xml_element->appendChild($node_element);

            //отрисовка "Parameter"
            self::drawingParameter($xml, $event->id, $node_element);

            //определение сколько дочек элемента $event->id на уровне $id_level
            $nodes = Node::find()->where(['parent_node' => $event->id])->all();
            foreach ($nodes as $n) {
                $sequence = Sequence::find()->where(['node' => $n->id, 'level' => $id_level])->one();
                if ($sequence != null){
                    $count_child = $count_child + 1;
                }
            }

            if ($count_child >= 2){
                // Создание "Operator"
                $operator_element = $xml->createElement('Operator');
                $operator_element->setAttribute('id', "logop-" . $event->id);
                $operator_element->setAttribute('name', "AND");
                $node_element->appendChild($operator_element);

                //добавление дочки "Event"
                $node_elements = Node::find()->where(['parent_node' => $event->id])->all();
                foreach ($node_elements as $n_elem) {
                    self::drawingEvent($xml, $n_elem, $operator_element, $id_level);
                }

            } else {
                //добавление дочки "Event"
                $node_elements = Node::find()->where(['parent_node' => $event->id])->all();
                foreach ($node_elements as $n_elem) {
                    self::drawingEvent($xml, $n_elem, $node_element, $id_level);
                }
            }
        }
    }


    public function generateEETDXMLCode($id)
    {
        // Определение наименования файла
        $file = 'eetd_file.xml';
        if (!file_exists($file))
            fopen($file, 'w');


        // Создание документа DOM с кодировкой UTF8
        $xml = new DomDocument('1.0', 'UTF-8');
        $diagram = TreeDiagram::find()->where(['id' => $id])->one();
        // Создание корневого узла Diagram
        $diagram_element = $xml->createElement('Diagram');
        $diagram_element->setAttribute('id', $diagram->id);
        $diagram_element->setAttribute('type', $diagram->getTypeName());
        $diagram_element->setAttribute('name', $diagram->name);
        $diagram_element->setAttribute('description', $diagram->description);
        $diagram_element->setAttribute('mode', $diagram->getModesName()); // Расширенное дерево // Классическое дерево
        // Добавление корневого узла Diagram в XML-документ
        $xml->appendChild($diagram_element);

        //подбор всех Level
        $level_elements = Level::find()->where(['tree_diagram' => $id])->all();
        if ($level_elements != null) {
            foreach ($level_elements as $l_elem) {

                // Создание "Level"
                $level_element = $xml->createElement('Level');
                $level_element->setAttribute('id', $l_elem->id);
                $level_element->setAttribute('name', $l_elem->name);
                $level_element->setAttribute('description', $l_elem->description);
                $diagram_element->appendChild($level_element);


                //выводим event сначало с пустым 'parent_node'
                $event_elements = Node::find()->where(['tree_diagram' => $id, 'parent_node' => null])->all();
                foreach ($event_elements as $e_elem) {
                    $sequence_element = Sequence::find()->where(['node' => $e_elem->id, 'level' => $l_elem->id])->one();
                    if ($sequence_element!= null){
                        $event = Node::find()->where(['id' => $e_elem->id])->one();
                        self::drawingEvent($xml, $event, $level_element, $l_elem->id);
                    }
                }


                $mas = array(); //массив $mas со значениями 'parent_node' уровня

                //заполнение массива $mas значениями 'parent_node' если родитель на другом уровне
                $event_elements = Node::find()->where(['tree_diagram' => $id])->andWhere(['not', ['parent_node' => null]])->all();
                foreach ($event_elements as $e_elem) {
                    $sequence_element = Sequence::find()->where(['node' => $e_elem->id, 'level' => $l_elem->id])->one();

                    if ($sequence_element!= null) {
                        $event = Node::find()->where(['id' => $e_elem->id])->one();
                        $sequence_parent_node = Sequence::find()->where(['node' => $event->parent_node])->one();

                        if (($sequence_parent_node!= null) && ($sequence_parent_node->level != $l_elem->id)){
                            $i = false;
                            foreach ($mas as $m) {
                                if ($m == $event->parent_node){
                                    $i = true;
                                }
                            }
                            if ($i == false){
                                array_push($mas, $event->parent_node);
                            }
                        }
                    }
                }


                // просмотр каждого элемента 'parent_node' из $mas и определение их количества на рабочем уровне
                foreach ($mas as $m) {
                    $count = 0;
                    $event_elements = Node::find()->where(['parent_node' => $m])->all();
                    foreach ($event_elements as $e_elem) {
                        $sequence_element = Sequence::find()->where(['node' => $e_elem->id, 'level' => $l_elem->id])->one();
                        if ($sequence_element!= null){
                            $count = $count + 1;
                        }
                    }

                    if ($count >= 2){
                        // Создание "Operator"
                        $operator_element = $xml->createElement('Operator');
                        $operator_element->setAttribute('id', "logop-" . $m);
                        $operator_element->setAttribute('name', "AND");
                        $level_element->appendChild($operator_element);

                        //добавление "Event"
                        $node_elements = Node::find()->where(['parent_node' => $m])->all();
                        foreach ($node_elements as $n_elem) {
                            self::drawingEvent($xml, $n_elem, $operator_element, $l_elem->id);
                        }

                    } else {
                        //добавление "Event"
                        $node_elements = Node::find()->where(['parent_node' => $m])->all();
                        foreach ($node_elements as $n_elem) {
                            self::drawingEvent($xml, $n_elem, $level_element, $l_elem->id);
                        }
                    }
                }
            }
        }

        // Сохранение RDF-файла
        $xml->formatOutput = true;
        header("Content-type: application/octet-stream");
        header('Content-Disposition: filename="'.$file.'"');
        echo $xml->saveXML();
        exit;
    }

}


