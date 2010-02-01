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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Objects_TestCase::main');
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
class XML_Serializer_Objects_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Objects_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test serializing an object without any properties
    */
    public function testEmptyObject()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(new stdClass());
        $this->assertEquals('<stdClass />', $s->getSerializedData());
    }

   /**
    * Test serializing a simple object
    */
    public function testSimpleObject()
    {
        $obj = new stdClass();
        $obj->foo = 'bar';
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<stdClass><foo>bar</foo></stdClass>', $s->getSerializedData());
    }

   /**
    * Test serializing a nested object
    */
    public function testNestedObject()
    {
        $obj = new stdClass();
        $obj->foo = new stdClass();
        $obj->foo->bar = 'nested';
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<stdClass><foo><bar>nested</bar></foo></stdClass>', $s->getSerializedData());
    }

   /**
    * Test serializing an object, that supports __sleep
    */
    public function testSleep()
    {
        $obj = new MyClass('foo', 'bar');
        $s = new XML_Serializer($this->options);
        $s->serialize($obj);
        $this->assertEquals('<MyClass><foo>foo</foo></MyClass>', $s->getSerializedData());
    }

}

class MyClass
{
    var $foo;
    var $bar;

    public function MyClass($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function __sleep()
    {
        return array('foo');
    }
}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Objects_TestCase::main') {
    XML_Serializer_Objects_TestCase::main();
}
?>
