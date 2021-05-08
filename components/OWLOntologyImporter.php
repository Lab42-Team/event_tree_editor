<?php

namespace app\components;

use app\modules\editor\models\Level;
use app\modules\editor\models\Node;
use app\modules\editor\models\Parameter;
use app\modules\editor\models\Sequence;
use app\modules\editor\models\TreeDiagram;

/**
 * Class OWLOntologyImporter - Класс реализующий импорт OWL-онтологий в классическое дерево событий.
 * @package app\components
 */
class OWLOntologyImporter
{
    /**
     * Очистка диаграммы.
     *
     * @param $id - идентификатор диаграммы
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function cleanDiagram($id)
    {
        $nodes = Node::find()->where(['tree_diagram' => $id])->all();
        foreach ($nodes as $node)
            $node->delete();
    }

    /**
     * Получение комментария (описания) онтологии.
     *
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @return string|null - комментарий (описание) онтологии
     */
    public static function getOntologyDescription($xml_rows)
    {
        $comment = null;
        // Получение всех пространств имен объявленых в XML-документе онтологии
        $namespaces = $xml_rows->getDocNamespaces(true);
        foreach ($namespaces as $prefix => $namespace)
            // Обход всех тегов внутри корневого элемента с учетом пространства имен
            foreach ($xml_rows->children($namespaces[$prefix]) as $element)
                // Если текущий элемент является тегом онтологии
                if ($element->getName() == 'Ontology')
                    foreach ($namespaces as $prefix => $namespace)
                        // Обход всех тегов внутри элемента тега онтологии с учетом пространства имен
                        foreach ($element->children($namespaces[$prefix]) as $child)
                            // Если текущий элемент является комментарием к онтологии
                            if ($child->getName() == 'comment')
                                $comment = (string)$element->comment;

       return $comment;
    }

    /**
     * Получение массива всех классов с комментариями из онтологии.
     *
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @return array - массив всех классов с комментариями (описанием класса), извлеченных из онтологии
     */
    public function getClasses($xml_rows)
    {
        // Массив для хранения извлекаемых классов
        $classes = array();
        // Получение всех пространств имен объявленых в XML-документе онтологии
        $namespaces = $xml_rows->getDocNamespaces(true);
        // Обход всех тегов внутри корневого элемента с учетом пространства имен
        foreach ($namespaces as $prefix => $namespace)
            foreach ($xml_rows->children($namespaces[$prefix]) as $element) {
                $class = null;
                $comment = null;
                // Обход всех атрибутов данного элемента с учетом пространства имен
                foreach ($namespaces as $prefix => $namespace)
                    foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                        // Если текущий элемент является классом с определенным атрибутом
                        if ($element->getName() == 'Class' && $attribute_name == 'about') {
                            // Извлечение значения атрибута у текущего элемента (класса онтологии)
                            $array = explode('#', (string)$attribute_value);
                            $class = $array[1];
                        }
                if ($element->getName() == 'Class')
                    foreach ($namespaces as $prefix => $namespace)
                        // Обход всех тегов внутри элемента класса с учетом пространства имен
                        foreach ($element->children($namespaces[$prefix]) as $child)
                            // Если текущий элемент является комментарием
                            if ($child->getName() == 'comment')
                                $comment = (string)$child;
                // Формирование массива классов с комментариями
                if (isset($class))
                    array_push($classes, [$class, $comment]);
            }

        return $classes;
    }

    /**
     * Получение массива свойств-знаечний для всех классов из онтологии.
     *
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @return array - массив свойств-знаечний для всех классов онтологии
     */
    public function getDatatypeProperties($xml_rows)
    {
        // Массив для хранения извлекаемых свойств-значений
        $datatype_properties = array();
        // Получение всех пространств имен объявленых в XML-документе онтологии
        $namespaces = $xml_rows->getDocNamespaces(true);
        // Обход всех тегов внутри корневого элемента с учетом пространства имен
        foreach ($namespaces as $prefix => $namespace)
            foreach ($xml_rows->children($namespaces[$prefix]) as $element) {
                // Обход всех атрибутов данного элемента с учетом пространства имен
                foreach ($namespaces as $prefix => $namespace)
                    foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                        // Если текущий элемент является свойством-значения с определенным атрибутом
                        if ($element->getName() == 'DatatypeProperty' && $attribute_name == 'about') {
                            // Извлечение значения атрибута у текущего элемента (свойства-значения класса)
                            $datatype_property = explode('#', (string)$attribute_value);
                            // Обход всех тегов внутри элемента с учетом пространства имен
                            foreach ($namespaces as $prefix => $namespace)
                                foreach ($element->children($namespaces[$prefix]) as $child)
                                    // Обход всех атрибутов данного элемента с учетом пространства имен
                                    foreach ($namespaces as $prefix => $namespace)
                                        foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value)
                                            // Если совпадает название искомого элемента и его атрибута
                                            if ($child->getName() == 'domain' && $attribute_name == 'resource') {
                                                // Извлечение значения атрибута у текущего элемента (класса онтологии)
                                                $class = explode('#', (string)$attribute_value);
                                                $datatype_properties[$class[1]] = [[$datatype_property[1], null]];
                                            }
                        }
                // Если текущий элемент является классом
                if ($element->getName() == 'Class') {
                    $class = null;
                    foreach ($namespaces as $prefix => $namespace) {
                        // Извлечение значения атрибута у текущего элемента (класса онтологии)
                        foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                            if ($attribute_name == 'about')
                                $class = explode('#', (string)$attribute_value);
                        // Обход всех тегов внутри элемента класса с учетом пространства имен
                        foreach ($element->children($namespaces[$prefix]) as $sub_class)
                            if ($sub_class->getName() == 'subClassOf') {
                                $datatype_property_name = null;
                                $datatype_property_value = null;
                                foreach ($namespaces as $prefix => $namespace)
                                    // Обход всех тегов внутри элемента подкласса с учетом пространства имен
                                    foreach ($sub_class->children($namespaces[$prefix]) as $restriction)
                                        if ($restriction->getName() == 'Restriction')
                                            foreach ($namespaces as $prefix => $namespace)
                                                // Обход всех тегов внутри элемента ограничения подкласса с учетом пространства имен
                                                foreach ($restriction->children($namespaces[$prefix]) as $property) {
                                                    // Определение значения для свойства-значения
                                                    if ($property->getName() == 'hasValue')
                                                        $datatype_property_value = (string)$restriction->hasValue;
                                                    foreach ($namespaces as $prefix => $namespace)
                                                        // Обход всех атрибутов данного элемента с учетом пространства имен
                                                        foreach ($property->attributes($prefix, true) as $attribute_name => $attribute_value) {
                                                            // Определение названия свойства-значения
                                                            if ($property->getName() == 'onProperty' &&
                                                                $attribute_name == 'resource') {
                                                                $array = explode('#', (string)$attribute_value);
                                                                foreach ($datatype_properties as $items)
                                                                    foreach ($items as $item)
                                                                        if ($item[0] == $array[1])
                                                                            $datatype_property_name = $array[1];
                                                            }
                                                            // Определение значения для свойства-значения
                                                            if ($property->getName() == 'hasValue' &&
                                                                $attribute_name == 'resource') {
                                                                $array = explode('#', (string)$attribute_value);
                                                                $datatype_property_value = $array[1];
                                                            }
                                                        }
                                                }
                                // Добавление свойства-значения для класса
                                if (isset($class[1]) && isset($datatype_property_name))
                                    if (isset($datatype_properties[$class[1]])) {
                                        $item = $datatype_properties[$class[1]];
                                        array_push($item, [$datatype_property_name, $datatype_property_value]);
                                        $datatype_properties[$class[1]] = $item;
                                    } else {
                                        $datatype_properties[$class[1]] = [[$datatype_property_name,
                                            $datatype_property_value]];
                                    }
                            }
                    }
                }
            }

        return $datatype_properties;
    }

    /**
     * Получение массива объектных свойств для всех классов из онтологии.
     *
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @return array - массив объектных свойств
     */
    public function getObjectProperties($xml_rows)
    {
        $object_property_names = array();
        // Массив для хранения извлекаемых объектных свойств
        $object_properties = array();
        // Получение всех пространств имен объявленых в XML-документе онтологии
        $namespaces = $xml_rows->getDocNamespaces(true);
        // Обход всех тегов внутри корневого элемента с учетом пространства имен
        foreach ($namespaces as $prefix => $namespace)
            foreach ($xml_rows->children($namespaces[$prefix]) as $element) {
                // Обход всех атрибутов данного элемента с учетом пространства имен
                foreach ($namespaces as $prefix => $namespace)
                    foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                        // Если текущий элемент является объектным свойством с определенным атрибутом
                        if ($element->getName() == 'ObjectProperty' && $attribute_name == 'about') {
                            $domain_class = null;
                            $range_class = array();
                            // Извлечение значения атрибута у текущего элемента (объектного свойства)
                            $array = explode('#', (string)$attribute_value);
                            //
                            array_push($object_property_names, $array[1]);
                            // Обход всех тегов внутри элемента с учетом пространства имен
                            foreach ($namespaces as $prefix => $namespace)
                                foreach ($element->children($namespaces[$prefix]) as $child)
                                    // Обход всех атрибутов данного элемента с учетом пространства имен
                                    foreach ($namespaces as $prefix => $namespace)
                                        foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value) {
                                            // Определение класса "слева" в отношении
                                            if ($child->getName() == 'domain' && $attribute_name == 'resource') {
                                                $array = explode('#', (string)$attribute_value);
                                                $domain_class = $array[1];
                                            }
                                            // Определение классов "справа" в отношении
                                            if ($child->getName() == 'range' && $attribute_name == 'resource') {
                                                $array = explode('#', (string)$attribute_value);
                                                array_push($range_class, $array[1]);
                                            }
                                        }
                            // Формирование массива объектных свойств (отношений между классами)
                            if (isset($domain_class))
                                array_push($object_properties, [$domain_class, $range_class]);
                        }
                // Если текущий элемент является классом
                if ($element->getName() == 'Class') {
                    $domain_class = null;
                    $range_class = array();
                    foreach ($namespaces as $prefix => $namespace) {
                        // Извлечение значения атрибута у текущего элемента (класса онтологии)
                        foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                            if ($attribute_name == 'about') {
                                $array = explode('#', (string)$attribute_value);
                                $domain_class = $array[1];
                            }
                        // Обход всех тегов внутри элемента класса с учетом пространства имен
                        foreach ($element->children($namespaces[$prefix]) as $sub_class)
                            if ($sub_class->getName() == 'subClassOf')
                                foreach ($namespaces as $prefix => $namespace)
                                    // Обход всех тегов внутри элемента подкласса с учетом пространства имен
                                    foreach ($sub_class->children($namespaces[$prefix]) as $restriction)
                                        if ($restriction->getName() == 'Restriction') {
                                            $is_obj_prop = false;
                                            foreach ($namespaces as $prefix => $namespace)
                                                // Обход всех тегов внутри элемента ограничения подкласса с учетом пространства имен
                                                foreach ($restriction->children($namespaces[$prefix]) as $property)
                                                    foreach ($namespaces as $prefix => $namespace)
                                                        // Обход всех атрибутов данного элемента с учетом пространства имен
                                                        foreach ($property->attributes($prefix, true) as $attribute_name => $attribute_value) {
                                                            // Определение названия объектного свойства
                                                            if ($property->getName() == 'onProperty' &&
                                                                $attribute_name == 'resource') {
                                                                $array = explode('#', (string)$attribute_value);
                                                                foreach ($object_property_names as $object_property_name)
                                                                    if ($object_property_name == $array[1])
                                                                        $is_obj_prop = true;
                                                            }
                                                            // Определение значения для свойства-значения
                                                            if ($property->getName() == 'someValuesFrom' &&
                                                                $attribute_name == 'resource' && $is_obj_prop) {
                                                                $array = explode('#', (string)$attribute_value);
                                                                array_push($range_class, $array[1]);
                                                            }
                                                        }
                                        }
                        // Добавление в массив нового отношения
                        if (!empty($range_class)) {
                            $index = null;
                            $new_item = null;
                            foreach ($object_properties as $key => $object_property)
                                if ($object_property[0] == $domain_class) {
                                    $item = $object_property[1];
                                    $range_class_exist = false;
                                    foreach ($range_class as $class) {
                                        foreach ($item as $value)
                                            if ($class == $value)
                                                $range_class_exist = true;
                                        if ($range_class_exist == false)
                                            array_push($item, $class);
                                    }
                                    $new_item = [$object_property[0], $item];
                                    $index = $key;
                                }
                            if (isset($new_item) && isset($index))
                                $object_properties[$index] = $new_item;
                            else
                                array_push($object_properties, [$domain_class, $range_class]);
                        }
                    }
                }
            }

        return $object_properties;
    }

    /**
     * Получение массива иерархических отношений между классами из онтологии.
     *
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @return array - массив иерархических отношений между классами
     */
    public function getSubClasses($xml_rows)
    {
        // Массив для хранения извлекаемых иерархических отношений между классами
        $subclasses = array();
        // Получение всех пространств имен объявленых в XML-документе онтологии
        $namespaces = $xml_rows->getDocNamespaces(true);
        foreach ($namespaces as $prefix => $namespace)
            // Обход всех тегов внутри корневого элемента с учетом пространства имен
            foreach ($xml_rows->children($namespaces[$prefix]) as $element) {
                $class = null;
                $subclass = null;
                foreach ($namespaces as $prefix => $namespace) {
                    // Обход всех атрибутов данного элемента с учетом пространства имен
                    foreach ($element->attributes($prefix, true) as $attribute_name => $attribute_value)
                        // Если текущий элемент является классом сопределенным атрибутом
                        if ($element->getName() == 'Class' && $attribute_name == 'about') {
                            // Извлечение значения атрибута у текущего элемента (класса онтологии)
                            $array = explode('#', (string)$attribute_value);
                            $subclass = $array[1];
                        }
                    // Обход всех тегов внутри элемента класса с учетом пространства имен
                    foreach ($element->children($namespaces[$prefix]) as $child)
                        foreach ($namespaces as $prefix => $namespace)
                            // Обход всех атрибутов данного элемента с учетом пространства имен
                            foreach ($child->attributes($prefix, true) as $attribute_name => $attribute_value)
                                // Если текущий элемент является подклассом сопределенным атрибутом
                                if ($child->getName() == 'subClassOf' && $attribute_name == 'resource') {
                                    // Извлечение значения атрибута у текущего элемента (класса онтологии)
                                    $array = explode('#', (string)$attribute_value);
                                    $class = $array[1];
                                }
                }
                // Формирование массива с классами и их подклассами
                if (isset($class)) {
                    if (isset($subclasses[$class])) {
                        $item = $subclasses[$class];
                        array_push($item, $subclass);
                        $subclasses[$class] = $item;
                    } else
                        $subclasses[$class] = [$subclass];
                } else
                    if (isset($subclass))
                        $subclasses[$subclass] = null;
            }

        return $subclasses;
    }

    /**
     * Конвертация OWL-онтологии в классическую диаграмму дерева событий.
     *
     * @param $id - идентификатор диаграммы
     * @param $xml_rows - XML-строки из OWL-файла онтологии
     * @param $selected_classes - массив классов, выбранных пользователем
     * @param $hierarchy - индикатор интерпретации иерархических связей
     * @param $relation - индикатор интерпретации связеймежду классами
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function convertOWLOntology($id, $xml_rows, $selected_classes, $hierarchy, $relation)
    {
        // Удаление всех узлов на диаграмме дерева событий
        self::cleanDiagram($id);

        // Получение комментария (описания) онтологии
        $comment = self::getOntologyDescription($xml_rows);
        // Обновление описания дерева событий
        $tree_diagram = TreeDiagram::findOne($id);
        $tree_diagram->description = $comment;
        $tree_diagram->save();

        // Поиск уровня у данной диаграммы дерева событий
        $level = Level::find()->where(['tree_diagram' => $tree_diagram->id])->one();

        $number = 1;
        // Получение массива всех классов с комментариями из онтологии
        $classes = self::getClasses($xml_rows);
        // Получение массива свойств-знаечний для всех классов из онтологии
        $datatype_properties = self::getDatatypeProperties($xml_rows);
        // Обход выбранных пользователем классов
        foreach ($selected_classes as $selected_class) {
            // Обход всех классов, извлеченных из онтологии
            foreach ($classes as $item)
                // Если имена классов совпадают
                if ($selected_class == $item[0]) {
                    // Поиск улов (событий) в данном дереве событий
                    $nodes = Node::find()->where(['tree_diagram' => $tree_diagram->id])->all();
                    // Создание нового узла (события) дерева событий
                    $node_model = new Node();
                    $node_model->name = $item[0];
                    $node_model->description = $item[1];
                    $node_model->type = empty($nodes) ? Node::INITIAL_EVENT_TYPE : Node::EVENT_TYPE;
                    $node_model->operator = Node::AND_OPERATOR;
                    $node_model->indent_x = 0;
                    $node_model->indent_y = 0;
                    $node_model->level_id = $level->id;
                    $node_model->tree_diagram = $tree_diagram->id;
                    $node_model->save();
                    // Создание новой модели Sequence
                    $sequence_model = new Sequence();
                    $sequence_model->node = $node_model->id;
                    $sequence_model->level = $level->id;
                    $sequence_model->tree_diagram = $tree_diagram->id;
                    $sequence_model->priority = $number;
                    $sequence_model->save();
                    $number++;
                    // Обход всех свойств-значений классов, извлеченных из онтологии
                    foreach ($datatype_properties as $class => $items)
                        if ($selected_class == $class)
                            foreach ($items as $datatype_property) {
                                // Создание нового параметра для события дерева событий
                                $parameter_model = new Parameter();
                                $parameter_model->name = $datatype_property[0];
                                $parameter_model->value = $datatype_property[1];
                                $parameter_model->operator = Parameter::EQUALLY_OPERATOR;
                                $parameter_model->node = $node_model->id;
                                $parameter_model->save();
                            }
                }
        }

        $object_properties = array();
        // Если пользователь указал интерпретацию иерархических отношений
        if ($hierarchy) {
            // Получение массива иерархических отношений между классами из онтологии
            $subclasses = self::getSubClasses($xml_rows);
            // Обход всех иерархических отношений между классами
            foreach ($subclasses as $class => $current_subclasses)
                if (isset($current_subclasses))
                    foreach ($current_subclasses as $subclass) {
                        // Поиск классов имеющих иерархические отношения среди выбранных пользователем классов
                        $class_exists = false;
                        $subclass_exists = false;
                        foreach ($selected_classes as $selected_class) {
                            if ($selected_class == $class)
                                $class_exists = true;
                            if ($selected_class == $subclass)
                                $subclass_exists = true;
                        }
                        // Если такие классы есть
                        if ($class_exists && $subclass_exists) {
                            // Поиск событий в БД
                            $parent_node = Node::find()->where(['name' => $class])->one();
                            $child_node = Node::find()->where(['name' => $subclass])->one();
                            // Если события найдены
                            if (!empty($parent_node) && !empty($child_node)) {
                                // Задание родительского события (отношения между событиями)
                                $child_node->parent_node = $parent_node->id;
                                $child_node->updateAttributes(['parent_node']);
                            }
                        }
                    }
        }

        // Если пользователь указал интерпретацию отношений между классами
        if ($relation) {
            // Получение массива объектных свойств для всех классов из онтологии
            $object_properties = self::getObjectProperties($xml_rows);
            // Обход всех отношений (объектных свойств) между классами
            foreach ($object_properties as $object_property) {
                // Поиск "левого" класса из отношения среди выбранных пользователем классов
                $domain_class_exists = false;
                foreach ($selected_classes as $selected_class) {
                    if ($selected_class == $object_property[0])
                        $domain_class_exists = true;
                }
                // Обход всех "правых" классов
                foreach ($object_property[1] as $range_class) {
                    // Поиск "правого" класса из отношения среди выбранных пользователем классов
                    $range_class_exists = false;
                    foreach ($selected_classes as $selected_class) {
                        if ($selected_class == $range_class)
                            $range_class_exists = true;
                    }
                    // Если такие классы есть
                    if ($domain_class_exists && $range_class_exists) {
                        // Поиск событий в БД
                        $parent_node = Node::find()->where(['name' => $object_property[0]])->one();
                        $child_node = Node::find()->where(['name' => $range_class])->one();
                        // Если события найдены
                        if (!empty($parent_node) && !empty($child_node)) {
                            // Задание родительского события (отношения между событиями)
                            $child_node->parent_node = $parent_node->id;
                            $child_node->updateAttributes(['parent_node']);
                        }
                    }
                }
            }
        }

        return $object_properties;
    }
}