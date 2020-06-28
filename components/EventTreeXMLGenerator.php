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

    //public function generateEETDXMLCode($id)
    //{
        // Определение наименования файла
    //    $file = 'exported_file.eetd';
        // Создание и открытие данного файла на запись, если он не существует
    //    if (!file_exists($file))
    //        fopen($file, 'w');
        // Начальное описание файла базы знаний
    //    $content = ";***************************************\r\n";
    //    $content .= "id = " . $id;
        // Выдача CLP-файла пользователю для скачивания
    //    header("Content-type: application/octet-stream");
    //    header('Content-Disposition: filename="'.$file.'"');
    //    echo $content;
    //    exit;
    //}




    public function generateEETDXMLCode($id)
    {



        // Определение наименования файла
        $file = 'eetd_file.xml';
        if (!file_exists($file))
            fopen($file, 'w');


        // Создание документа DOM с кодировкой UTF8
        //$xml = new DomDocument('1.0', 'UTF-8');
        $xml = new DomDocument('1.0', 'windows-1251');
        $diagram = TreeDiagram::find()->where(['id' => $id])->one();
        // Создание корневого узла Diagram
        $diagram_element = $xml->createElement('Diagram');
        $diagram_element->setAttribute('id', $diagram->id);
        $diagram_element->setAttribute('type', $diagram->getTypeName());
        $diagram_element->setAttribute('name', $diagram->name);
        $diagram_element->setAttribute('description', $diagram->description);
        // Добавление корневого узла Diagram в XML-документ
        $xml->appendChild($diagram_element);

        //iconv("Windows-1251", "UTF-8//IGNORE", $diagram->name)







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


                // Поиск "Node" принадлежащих "Level" через "Sequence"
                $sequence_elements = Sequence::find()->where(['level' => $l_elem->id])->all();
                foreach ($sequence_elements as $s_elem) {

                    //количество дочерних элементов
                    $count_child_elements = 0;

                    $p_n_element = Node::find()->where(['id' => $s_elem->node])->one();
                    if ($p_n_element != null) {

                        $id_parent_node = $p_n_element->id;
                        $sequence_parent_node = Sequence::find()->where(['node' => $p_n_element->parent_node])->one();

                        if (($p_n_element->parent_node == null)||(($sequence_parent_node!= null) && ($sequence_parent_node->level != $l_elem->id))){
                            // Создание "Event"
                            $parent_node_element = $xml->createElement('Event');
                            $parent_node_element->setAttribute('id', $p_n_element->id);
                            if (($sequence_parent_node!= null) && ($sequence_parent_node->level != $l_elem->id)){
                                $parent_node_element->setAttribute('event-id', $p_n_element->parent_node);
                            }
                            $parent_node_element->setAttribute('type', $p_n_element->getTypeName());
                            $parent_node_element->setAttribute('name', $p_n_element->name);
                            $parent_node_element->setAttribute('description', $p_n_element->description);
                            $level_element->appendChild($parent_node_element);


                            //определение количества дочерних элементов
                            foreach ($sequence_elements as $s_n_elem) {
                                $p_n_element = Node::find()->where(['id' => $s_n_elem->node, 'parent_node' => $id_parent_node])->one();
                                if ($p_n_element != null){
                                    $count_child_elements = $count_child_elements + 1;
                                }
                            }

                            if ($count_child_elements >= 2){
                                // Создание "Operator"
                                $operator_element = $xml->createElement('Operator');
                                $operator_element->setAttribute('id', "logop-" . $id_parent_node);
                                $operator_element->setAttribute('name', "AND");
                                $parent_node_element->appendChild($operator_element);

                                $node_elements = Node::find()->where(['parent_node' => $id_parent_node])->all();
                                foreach ($node_elements as $n_elem) {
                                    // Создание дочерних "Event"
                                    $node_element = $xml->createElement('Event');
                                    $node_element->setAttribute('id', $n_elem->id);
                                    $node_element->setAttribute('type', $n_elem->getTypeName());
                                    $node_element->setAttribute('name', $n_elem->name);
                                    $node_element->setAttribute('description', $n_elem->description);
                                    $operator_element->appendChild($node_element);

                                    //подбор всех Parameter
                                    $parameter_elements = Parameter::find()->where(['node' => $n_elem->id])->all();
                                    if ($parameter_elements != null){
                                        foreach ($parameter_elements as $p_elem){
                                            // Создание "parameter"
                                            $parameter_element = $xml->createElement('Parameter');
                                            $parameter_element->setAttribute('id', $p_elem->id);
                                            $parameter_element->setAttribute('name', $p_elem->name);
                                            $parameter_element->setAttribute('value', $p_elem->value);
                                            $parameter_element->setAttribute('description', $p_elem->description);
                                            $node_element->appendChild($parameter_element);
                                        }
                                    }
                                }
                            } elseif ( $count_child_elements == 1){
                                $n_elem = Node::find()->where(['parent_node' => $id_parent_node])->one();
                                // Создание дочерних "Event"
                                $node_element = $xml->createElement('Event');
                                $node_element->setAttribute('id', $n_elem->id);
                                $node_element->setAttribute('type', $n_elem->getTypeName());
                                $node_element->setAttribute('name', $n_elem->name);
                                $node_element->setAttribute('description', $n_elem->description);
                                $parent_node_element->appendChild($node_element);

                                //подбор всех Parameter
                                $parameter_elements = Parameter::find()->where(['node' => $n_elem->id])->all();
                                if ($parameter_elements != null){
                                    foreach ($parameter_elements as $p_elem){
                                        // Создание "parameter"
                                        $parameter_element = $xml->createElement('Parameter');
                                        $parameter_element->setAttribute('id', $p_elem->id);
                                        $parameter_element->setAttribute('name', $p_elem->name);
                                        $parameter_element->setAttribute('value', $p_elem->value);
                                        $parameter_element->setAttribute('description', $p_elem->description);
                                        $node_element->appendChild($parameter_element);
                                    }
                                }

                            }
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



/**
    public function generateRDFXMLCode()
    {
        // Создание документа DOM с кодировкой UTF8
        $xml = new DomDocument('1.0', 'UTF-8');
        // Создание корневого узла RDF с определением пространства имен
        $rdf_element = $xml->createElement('rdf:RDF');
        $rdf_element->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $rdf_element->setAttribute('xmlns:dbo', self::DBPEDIA_ONTOLOGY_SECTION);
        $rdf_element->setAttribute('xmlns:db', self::DBPEDIA_RESOURCE_SECTION);
        $rdf_element->setAttribute('xmlns:dbp', self::DBPEDIA_PROPERTY_SECTION);
        // Добавление корневого узла в XML-документ
        $xml->appendChild($rdf_element);
        // Создание узла триплета "Number"
        $number_element = $xml->createElement('rdf:Description');
        $number_element->setAttribute('rdf:about', 'http://dbpedia.org/resource/Number');
        $number_flag = false;
        // Создание узла триплета "Date"
        $date_element = $xml->createElement('rdf:Description');
        $date_element->setAttribute('rdf:about', 'http://dbpedia.org/resource/Date');
        $date_flag = false;
        // Цикл по всем найденным кандидатам для столбца DATA
        foreach($this->data_entities as $key => $value) {
            if ($value == 'http://dbpedia.org/resource/Number') {
                // Добавление узла триплета "Number" в корневой узел RDF, если он не добавлен
                if (!$number_flag) {
                    $rdf_element->appendChild($number_element);
                    $number_flag = true;
                }
                // Добавление объектов для триплета "Number"
                $node_element = $xml->createElement('dbp:titleNumber', $key);
                $number_element->appendChild($node_element);
            }
            if ($value == 'http://dbpedia.org/resource/Date') {
                // Добавление узла триплета "Date" в корневой узел RDF, если он не добавлен
                if (!$date_flag) {
                    $rdf_element->appendChild($date_element);
                    $date_flag = true;
                }
                // Добавление объектов для триплета "Date"
                $node_element = $xml->createElement('dbp:title', $key);
                $date_element->appendChild($node_element);
            }
        }
        // Сохранение RDF-файла
        $xml->formatOutput = true;
        $xml->save('example.rdf');
    }

 */
}


