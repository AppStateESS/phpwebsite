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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_AttributesContent_TestCase::main');
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
class XML_Serializer_Option_AttributesContent_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT         => '',
        XML_SERIALIZER_OPTION_LINEBREAKS     => '',
        XML_SERIALIZER_OPTION_ATTRIBUTES_KEY => 'atts',
        XML_SERIALIZER_OPTION_CONTENT_KEY    => 'content'
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_AttributesContent_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}


   /**
    * Test attributes
    */
    public function testAttribs()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foo' => array(
                'atts' => array('one' => 1),
                'bar'  => 'bar'
            )
        );
        $s->serialize($data);
        $this->assertEquals(
            '<array><foo one="1"><bar>bar</bar></foo></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test content
    */
    public function testContent()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foo' => array(
                'atts'    => array('one' => 1),
                'content' => 'some data',
            )
        );
        $s->serialize($data);
        $this->assertEquals(
            '<array><foo one="1">some data</foo></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test both
    */
    public function testMixed()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foo' => array(
                'atts'    => array('one' => 1),
                'content' => 'some data',
                'bar'     => 'bar'
            )
        );
        $s->serialize($data);
        $this->assertEquals(
            '<array><foo one="1">some data<bar>bar</bar></foo></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test indexed
    */
    public function testNumbered()
    {
        $s = new XML_Serializer($this->options);
        $data = array(
            'foo' => array(
                'atts'    => array('one' => 1),
                'content' => 'some data',
                'bar', 'foo'
            )
        );
        $s->serialize($data);
        $this->assertEquals(
            '<array><foo one="1">some data<XML_Serializer_Tag>bar</XML_Serializer_Tag><XML_Serializer_Tag>foo</XML_Serializer_Tag></foo></array>'
            , $s->getSerializedData()
        );
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_AttributesContent_TestCase::main') {
    XML_Serializer_Option_AttributesContent_TestCase::main();
}
?>
