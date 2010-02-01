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
    define('PHPUnit_MAIN_METHOD', 'XML_Unserializer_Objects_TestCase::main');
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
class XML_Unserializer_Objects_TestCase extends PHPUnit_Framework_TestCase {

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Unserializer_Objects_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test unserializing to a stdClass object
    */
    public function testStdClass()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'object');
        $xml = '<xml><foo>bar</foo></xml>';
        $u->unserialize($xml);
        $result = new stdClass();
        $result->foo = 'bar';
        $this->assertEquals($result, $u->getUnserializedData());
    }

   /**
    * Test unserializing to a custom class
    */
    public function testDefaultClass()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'object');
        $u->setOption(XML_UNSERIALIZER_OPTION_DEFAULT_CLASS, 'Foo');
        $xml = '<xml><foo>bar</foo></xml>';
        $u->unserialize($xml);
        $result = new Foo();
        $result->foo = 'bar';
        $this->assertEquals($result, $u->getUnserializedData());
    }

   /**
    * Test unserializing to a class based on the tag name
    */
    public function testTagnameAsClass()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'object');
        $u->setOption(XML_UNSERIALIZER_OPTION_DEFAULT_CLASS, 'Foo');
        $xml = '<foo><bar><boo>tomato</boo></bar></foo>';
        $u->unserialize($xml);
        $result = new Foo();
        $result->bar = new Bar();
        $result->bar->boo = 'tomato';
        $this->assertEquals($result, $u->getUnserializedData());
    }

   /**
    * Test unserializing with a setter method
    */
    public function testSetterMethod()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_COMPLEXTYPE, 'object');
        $u->setOption(XML_UNSERIALIZER_OPTION_DEFAULT_CLASS, 'Foo');
        $xml = '<SetterExample><foo>tomato</foo></SetterExample>';
        $u->unserialize($xml);
        $result = new SetterExample();
        $result->setFoo('tomato');
        $this->assertEquals($result, $u->getUnserializedData());
    }

}

class Foo {}

class Bar {}

class SetterExample {
    private $_hidden = null;

    public function setFoo($foo)
    {
        $this->_hidden = $foo;
    }
}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Unserializer_Objects_TestCase::main') {
    XML_Unserializer_Objects_TestCase::main();
}
?>
