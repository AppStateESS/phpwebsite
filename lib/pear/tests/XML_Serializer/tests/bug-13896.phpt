--TEST--
XML Serializer - Bug #13896:  Bad info in the RSS feed tutorial
--FILE--
<?php

require_once 'XML/Serializer.php';

$options = array(
    "indent"         => "    ",
    "linebreak"      => "\n",
    "typeHints"      => false,
    "addDecl"        => true,
    "encoding"       => "UTF-8",
    "rootName"        => "rdf:RDF",
    "defaultTagName" => "item"
);

$stories[] = array(
    'title'       => 'First Article',
    'link'        => 'http://freedomink.org/node/view/55',
    'description' => 'Short blurb about article........'
);
$stories[] = array(
    'title'       => 'Second Article',
    'link'        => 'http://freedomink.org/node/view/11',
    'description' => 'This article shows you how ......'
);

$data['channel'] = array(
    "title" => "Freedom Ink",
    "link"  => "http://freedomink.org/",
    $stories
);


/**
 * might later considering rewriting it 
 * to show the option setting this way
$ser= new XML_Serializer();
$ser->setOption(XML_SERIALIZER_OPTION_INDENT, '    ');
$ser->setOption(XML_SERIALIZER_OPTION_XML_DECL_ENABLED, true);
$ser->setOption(XML_SERIALIZER_OPTION_XML_ENCODING, 'UTF-8');
$ser->setOption(XML_SERIALIZER_OPTION_ROOT_NAME, 'rdf:RDF');
$ser->setOption(XML_SERIALIZER_OPTION_DEFAULT_TAG, 'item');
**/

$ser = new XML_Serializer($options);
if ($ser->serialize($data)) {
    header('Content-type: text/xml');
    echo $ser->getSerializedData();
}

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF>
    <channel>
        <title>Freedom Ink</title>
        <link>http://freedomink.org/</link>
        <item>
            <item>
                <title>First Article</title>
                <link>http://freedomink.org/node/view/55</link>
                <description>Short blurb about article........</description>
            </item>
            <item>
                <title>Second Article</title>
                <link>http://freedomink.org/node/view/11</link>
                <description>This article shows you how ......</description>
            </item>
        </item>
    </channel>
</rdf:RDF>
