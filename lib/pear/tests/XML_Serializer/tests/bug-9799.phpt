--TEST--
XML Serializer - Bug #9799:  attributesArray is poorly named
--FILE--
<?php
require_once 'XML/Serializer.php';

/*
 * bug submitter uses a key of '-'
 * for both attributeArray and contentName...
 * these must not be the same...
 * also, attributeArray was set to array('-')
 * when both attributeArray and contentName
 * must be strings...
 * those two problems caused the issues on 
 * this bug report
 *
 * the code below shows the proper use
 * of both attributeArray and contentName,
 * even though the attributeArray value is
 * not actually used in the $color array
 */
$color = array(
    'f'=>array(
        array('id'=>'blue', '-' => 'red'),
        array('id'=>'qqq',  '-' => 'green')
    )
);
$options = array(
    'addDecl' => true,
    'rootName'=>'truecolor',
    'indent'=>'   ',
    'mode'=>'simplexml',
    'scalarAsAttributes'=>array('f'=>array('id')),
    'attributesArray'=>'+',
    'contentName'=>'-',
);

$s = new XML_Serializer($options);
$status = $s->serialize($color);
echo $s->getSerializedData();
?>

--EXPECT--
<?xml version="1.0"?>
<truecolor>
   <f id="blue">red</f>
   <f id="qqq">green</f>
</truecolor>
