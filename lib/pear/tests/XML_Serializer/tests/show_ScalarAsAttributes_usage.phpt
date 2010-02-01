--TEST--
XML Serializer - show proper usages of SCALAR_AS_ATTRIBUTES option
--FILE--
<?php
require_once 'XML/Serializer.php';

$ser = &new XML_Serializer();


/**
 * all subtags under tagA will become attributes on tagA...
 * all subtags under tagB will become attributes on tagB...
 */
echo 'TEST:  SCALAR_AS_ATTRIBUTES set TRUE' . PHP_EOL;
$ser->setOption(XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES, true);
$result = $ser->serialize(
    array(
        'tagA' => array(
            'tag2' => 2,
            'tag3' => 'hi'
        ),
        'tagB' => array(
            'tag4' => 4,
            'tag5' => 'bye'
        )
    )
);
echo $ser->getSerializedData() . PHP_EOL . PHP_EOL;


/**
 * all subtags under tagA will become attributes on tagA,
 * but subtags under tagB will remain as subtags.
 */
echo 'TEST:  SCALAR_AS_ATTRIBUTES set to array(\'tagA\' => true)' . PHP_EOL;
$ser->setOption(XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES,
    array(
        'tagA' => true
    )
);
$result = $ser->serialize(
    array(
        'tagA' => array(
            'tag2' => 2,
            'tag3' => 'hi'
        ),
        'tagB' => array(
            'tag4' => 4,
            'tag5' => 'bye'
        )
    )
);
echo $ser->getSerializedData() . PHP_EOL . PHP_EOL;


/**
 * only subtag tag3 will become an attribute on tagA...
 * tag2 will remain a subtag under tagA...
 * nothing affects tagB... tag4 and tag5 remain subtags under it.
 */
echo 'TEST:  SCALAR_AS_ATTRIBUTES set to array(\'tagA\' => array(\'tag3\'))' . PHP_EOL;
$ser->setOption(XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES,
    array(
        'tagA' => array('tag3')
    )
);
$result = $ser->serialize(
    array(
        'tagA' => array(
            'tag2' => 2,
            'tag3' => 'hi'
        ),
        'tagB' => array(
            'tag4' => 4,
            'tag5' => 'bye'
        )
    )
);
echo $ser->getSerializedData() . PHP_EOL . PHP_EOL;

?>
--EXPECT--
TEST:  SCALAR_AS_ATTRIBUTES set TRUE
<array>
<tagA tag2="2" tag3="hi" />
<tagB tag4="4" tag5="bye" />
</array>

TEST:  SCALAR_AS_ATTRIBUTES set to array('tagA' => true)
<array>
<tagA tag2="2" tag3="hi" />
<tagB>
<tag4>4</tag4>
<tag5>bye</tag5>
</tagB>
</array>

TEST:  SCALAR_AS_ATTRIBUTES set to array('tagA' => array('tag3'))
<array>
<tagA tag3="hi">
<tag2>2</tag2>
</tagA>
<tagB>
<tag4>4</tag4>
<tag5>bye</tag5>
</tagB>
</array>
