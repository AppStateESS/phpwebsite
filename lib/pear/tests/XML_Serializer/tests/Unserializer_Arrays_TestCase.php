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
    define('PHPUnit_MAIN_METHOD', 'XML_Unserializer_Arrays_TestCase::main');
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
class XML_Unserializer_Arrays_TestCase extends PHPUnit_Framework_TestCase {

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Unserializer_Arrays_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test unserializing a simple array
    */
    public function testAssoc()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'array');
        $xml = '<xml><foo>bar</foo></xml>';
        $u->unserialize($xml);
        $this->assertEquals(array('foo' => 'bar'), $u->getUnserializedData());
    }

   /**
    * Test unserializing an indexed array
    */
    public function testIndexed()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'array');
        $xml = '<xml><foo>bar</foo><foo>tomato</foo></xml>';
        $u->unserialize($xml);
        $this->assertEquals(array('foo' => array('bar', 'tomato')), $u->getUnserializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Unserializer_Arrays_TestCase::main') {
    XML_Unserializer_Arrays_TestCase::main();
}
?>
