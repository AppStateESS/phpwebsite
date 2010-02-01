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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_Mode_TestCase::main');
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
class XML_Serializer_Option_Mode_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_Mode_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Default mode
    */
    public function testDefault()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => array(1, 2, 3), 'bar' => array(1, 2, 3)));
        $this->assertEquals('<array><foo><XML_Serializer_Tag>1</XML_Serializer_Tag><XML_Serializer_Tag>2</XML_Serializer_Tag><XML_Serializer_Tag>3</XML_Serializer_Tag></foo><bar><XML_Serializer_Tag>1</XML_Serializer_Tag><XML_Serializer_Tag>2</XML_Serializer_Tag><XML_Serializer_Tag>3</XML_Serializer_Tag></bar></array>', $s->getSerializedData());
    }

   /**
    * SimpleXML
    */
    public function testSimpleXML()
    {
        $s = new XML_Serializer($this->options);
        $s->setOption(XML_SERIALIZER_OPTION_MODE, XML_SERIALIZER_MODE_SIMPLEXML);
        $s->serialize(array('foo' => array(1, 2, 3), 'bar' => array(1, 2, 3)));
        $this->assertEquals('<array><foo>1</foo><foo>2</foo><foo>3</foo><bar>1</bar><bar>2</bar><bar>3</bar></array>', $s->getSerializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_Mode_TestCase::main') {
    XML_Serializer_Option_Mode_TestCase::main();
}
?>
