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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_RootName_TestCase::main');
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
class XML_Serializer_Option_RootName_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
        XML_SERIALIZER_OPTION_ROOT_NAME  => 'root'
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_RootName_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Array
    */
    public function testString()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize('string');
        $this->assertEquals('<root>string</root>', $s->getSerializedData());
    }

   /**
    * Array
    */
    public function testArray()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => 'bar'));
        $this->assertEquals('<root><foo>bar</foo></root>', $s->getSerializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_RootName_TestCase::main') {
    XML_Serializer_Option_RootName_TestCase::main();
}
?>
