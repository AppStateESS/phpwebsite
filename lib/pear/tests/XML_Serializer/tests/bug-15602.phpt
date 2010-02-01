--TEST--
XML Serializer - Bug #15602:  attributes don't get escaped sometimes
--FILE--
<?php
require_once 'XML/Serializer.php';

define('XML_ATTR', 'XML_Attributes_Array');

$options = array(
    XML_SERIALIZER_OPTION_INDENT           => '  ',
    XML_SERIALIZER_OPTION_LINEBREAKS       => "\n",
    XML_SERIALIZER_OPTION_ROOT_NAME        => 'FooTag',
    XML_SERIALIZER_OPTION_MODE             => XML_SERIALIZER_MODE_SIMPLEXML,
    XML_SERIALIZER_OPTION_ATTRIBUTES_KEY   => XML_ATTR,
    XML_SERIALIZER_OPTION_XML_ENCODING     => 'UTF-8',
    XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
    XML_SERIALIZER_OPTION_ENTITIES         => XML_SERIALIZER_ENTITIES_XML,
);

$v = 'I say: "A", B & C, \'d\'!';
$a = array('attr1' => $v);

$xml = array(
    XML_ATTR => $a,
    'tag1'   => array(XML_ATTR => $a, 'tag2' => $v),
    'tag3'   => array(XML_ATTR => $a, $v),

);

$serializer = new XML_Serializer($options);
$serializer->serialize($xml);
echo $serializer->getSerializedData();
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<FooTag attr1="I say: &quot;A&quot;, B &amp; C, &apos;d&apos;!">
  <tag1 attr1="I say: &quot;A&quot;, B &amp; C, &apos;d&apos;!">
    <tag2>I say: &quot;A&quot;, B &amp; C, &apos;d&apos;!</tag2>
  </tag1>
  <tag3 attr1="I say: &quot;A&quot;, B &amp; C, &apos;d&apos;!">I say: &quot;A&quot;, B &amp; C, &apos;d&apos;!</tag3>
</FooTag>
