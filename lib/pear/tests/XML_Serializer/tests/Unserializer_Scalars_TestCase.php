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
    define('PHPUnit_MAIN_METHOD', 'XML_Unserializer_Scalars_TestCase::main');
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
class XML_Unserializer_Scalars_TestCase extends PHPUnit_Framework_TestCase {

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Unserializer_Scalars_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test unserializing simple data
    */
    public function testData()
    {
        $u = new XML_Unserializer();
        $xml = '<xml>data</xml>';
        $u->unserialize($xml);
        $this->assertEquals('data', $u->getUnserializedData());
    }

   /**
    * Test extracting the root name
    */
    public function testRootName()
    {
        $u = new XML_Unserializer();
        $xml = '<xml>data</xml>';
        $u->unserialize($xml);
        $this->assertEquals('xml', $u->getRootName());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Unserializer_Scalars_TestCase::main') {
    XML_Unserializer_Scalars_TestCase::main();
}
?>
