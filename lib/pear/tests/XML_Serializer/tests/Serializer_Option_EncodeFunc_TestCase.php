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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_EncodeFunc_TestCase::main');
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
class XML_Serializer_Option_EncodeFunc_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT               => '',
        XML_SERIALIZER_OPTION_LINEBREAKS           => '',
        XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => true,
        XML_SERIALIZER_OPTION_ENCODE_FUNC          => 'strtoupper'
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_EncodeFunc_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test encode function with cdata
    */
    public function testCData()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize('a string');
        $this->assertEquals('<string>A STRING</string>', $s->getSerializedData());
    }

   /**
    * Test encode function with attributes
    */
    public function testAttributes()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => 'bar'));
        $this->assertEquals('<array foo="BAR" />', $s->getSerializedData());
    }

   /**
    * Test encode function with cdata
    */
    public function testMixed()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => 'bar', 'tomato'));
        $this->assertEquals('<array foo="BAR"><XML_Serializer_Tag>TOMATO</XML_Serializer_Tag></array>', $s->getSerializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_EncodeFunc_TestCase::main') {
    XML_Serializer_Option_EncodeFunc_TestCase::main();
}
?>
