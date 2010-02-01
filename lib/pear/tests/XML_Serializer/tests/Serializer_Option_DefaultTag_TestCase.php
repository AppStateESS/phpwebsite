<?php
/**
 * Unit Tests for serializing arrays
 *
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Stephan Schmidt <schst@php-tools.net>
 * @author     Chuck Burgess <ashnazg@php.net>
 */

/**
 * PHPUnit main() hack
 * 
 * "Call class::main() if this source file is executed directly."
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_DefaultTag_TestCase::main');
}
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'XML/Serializer.php';

/**
 * Unit Tests for serializing arrays
 *
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Stephan Schmidt <schst@php-tools.net>
 * @author     Chuck Burgess <ashnazg@php.net>
 */
class XML_Serializer_Option_DefaultTag_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_DefaultTag_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test setting a global default tag
    */
    public function testSimple()
    {
        $s = new XML_Serializer($this->options);
        $s->setOption(XML_SERIALIZER_OPTION_DEFAULT_TAG, 'tag');
        $s->serialize(array('one', 'two', 'three'));
        $this->assertEquals(
            '<array><tag>one</tag><tag>two</tag><tag>three</tag></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test setting context sensitive default tags
    */
    public function testContext()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foos' => array(1,2),
            'bars' => array(1,2),
        );
        $s->setOption(XML_SERIALIZER_OPTION_DEFAULT_TAG, array('foos' => 'foo', 'bars' => 'bar'));
        $s->serialize($data);
        $this->assertEquals(
            '<array><foos><foo>1</foo><foo>2</foo></foos><bars><bar>1</bar><bar>2</bar></bars></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test setting mixed default tags
    */
    public function testMixed()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foos' => array(1,2),
            'bars' => array(1,2),
            'test'
        );
        $s->setOption(XML_SERIALIZER_OPTION_DEFAULT_TAG, array('foos' => 'foo', '#default' => 'tag'));
        $s->serialize($data);
        $this->assertEquals(
            '<array><foos><foo>1</foo><foo>2</foo></foos><bars><tag>1</tag><tag>2</tag></bars><tag>test</tag></array>'
            , $s->getSerializedData()
        );
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_DefaultTag_TestCase::main') {
    XML_Serializer_Option_DefaultTag_TestCase::main();
}
?>
