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
    define('PHPUnit_MAIN_METHOD', 'XML_Unserializer_Option_Whitespace_TestCase::main');
}
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'XML/Unserializer.php';

/**
 * Unit Tests for serializing arrays
 *
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Stephan Schmidt <schst@php-tools.net>
 * @author     Chuck Burgess <ashnazg@php.net>
 */
class XML_Unserializer_Option_Whitespace_TestCase extends PHPUnit_Framework_TestCase {

    private $xml = '<xml>
   <string>

    This XML
    document
    contains
    line breaks.

   </string>
 </xml>';

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Unserializer_Option_Whitespace_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test trim behaviour
    */
    public function testTrim()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_WHITESPACE, XML_UNSERIALIZER_WHITESPACE_TRIM);
        $u->unserialize($this->xml);
        $expected = array('string' => 'This XML
    document
    contains
    line breaks.');
        $this->assertEquals($expected, $u->getUnserializedData());
    }

   /**
    * Test normalize behaviour
    */
    public function testNormalize()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_WHITESPACE, XML_UNSERIALIZER_WHITESPACE_NORMALIZE);
        $u->unserialize($this->xml);
        $expected = array('string' => 'This XML document contains line breaks.');
        $this->assertEquals($expected, $u->getUnserializedData());
    }

   /**
    * Test keep behaviour
    */
    public function testKeep()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_WHITESPACE, XML_UNSERIALIZER_WHITESPACE_KEEP);
        $u->unserialize($this->xml);
        $expected = array('string' => '

    This XML
    document
    contains
    line breaks.

   ');
        $this->assertEquals($expected, $u->getUnserializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Unserializer_Option_Whitespace_TestCase::main') {
    XML_Unserializer_Option_Whitespace_TestCase::main();
}
?>
