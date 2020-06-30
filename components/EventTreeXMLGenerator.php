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


    public  $node_element;



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
                $parameter_element->setAttribute('value', $p_elem->value);
                $parameter_element->setAttribute('description', $p_elem->description);
                $xml_element->appendChild($parameter_element);
            }
        }

    }



    public static function drawingEvent($xml, $event, $xml_element, $id_level)
    {

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
            $xml_element->appendChild($node_element);

            //отрисовка "Parameter"
            //self::drawingParameter($xml, $event->id, $node_element);

            //добавление дочки "Event"
            $node_elements = Node::find()->where(['parent_node' => $event->id])->all();
            foreach ($node_elements as $n_elem) {
                self::drawingEvent($xml, $n_elem, $node_element, $id_level);
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


                // Поиск "Node" принадлежащих "Level" через "Sequence"
                $sequence_elements = Sequence::find()->where(['level' => $l_elem->id])->all();
                foreach ($sequence_elements as $s_elem) {

                    $event = Node::find()->where(['id' => $s_elem->node])->one();

                    //$id_parent_node = $event->id;
                    $sequence_parent_node = Sequence::find()->where(['node' => $event->parent_node])->one();

                    if (($event->parent_node == null)||(($sequence_parent_node!= null) && ($sequence_parent_node->level != $l_elem->id))){
                        self::drawingEvent($xml, $event, $level_element, $l_elem->id);
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


