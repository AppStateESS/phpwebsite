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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Arrays_TestCase::main');
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
class XML_Serializer_Arrays_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Arrays_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}


   /**
    * Test serializing a numbered array
    */
    public function testNumberedArray()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('one', 'two', 'three'));
        $this->assertEquals(
            '<array><XML_Serializer_Tag>one</XML_Serializer_Tag><XML_Serializer_Tag>two</XML_Serializer_Tag><XML_Serializer_Tag>three</XML_Serializer_Tag></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test serializing an assoc array
    */
    public function testAssocArray()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('one' => 'foo', 'two' => 'bar'));
        $this->assertEquals(
            '<array><one>foo</one><two>bar</two></array>'
            , $s->getSerializedData()
        );
    }

   /**
    * Test serializing an mixed array
    */
    public function testMixedArray()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('one' => 'foo', 'two' => 'bar', 'three'));
        $this->assertEquals(
            '<array><one>foo</one><two>bar</two><XML_Serializer_Tag>three</XML_Serializer_Tag></array>'
            , $s->getSerializedData()
        );
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Arrays_TestCase::main') {
    XML_Serializer_Arrays_TestCase::main();
}
?>
