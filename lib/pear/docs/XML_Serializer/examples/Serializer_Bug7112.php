<?php
require_once 'XML/Serializer.php';

$citation =  array('book' =>
                array(
                    'author' => array(
                         array('John Doe', 'attributes' => array('id' => 1)),
                         array('Bob Jones', 'attributes' =>array('id' => 2))
                    ),
                    'title' => 'Title of the book'
                  )
               );

$s = new XML_Serializer();
$s->setOption(XML_SERIALIZER_OPTION_INDENT, '    ');
$s->setOption(XML_SERIALIZER_OPTION_ATTRIBUTES_KEY, 'attributes');
$s->setOption(XML_SERIALIZER_OPTION_MODE, XML_SERIALIZER_MODE_SIMPLEXML);
$s->serialize($citation);

echo '<pre>';
echo htmlentities($s->getSerializedData());
echo '</pre>';
?>