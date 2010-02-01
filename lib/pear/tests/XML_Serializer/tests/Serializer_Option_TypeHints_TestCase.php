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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_TypeHints_TestCase::main');
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
class XML_Serializer_Option_TypeHints_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => '',
        XML_SERIALIZER_OPTION_TYPEHINTS  => true
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_TypeHints_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test type
    *
    * @todo add more types
    */
    public function testType()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize('string');
        $this->assertEquals('<string _type="string">string</string>', $s->getSerializedData());
    }

   /**
    * Test original key
    */
    public function testKey()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo bar' => 'bar'));
        $this->assertEquals('<array _type="array"><XML_Serializer_Tag _originalKey="foo bar" _type="string">bar</XML_Serializer_Tag></array>', $s->getSerializedData());
    }

   /**
    * Test class
    */
    public function testClass()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => new stdClass()));
        $this->assertEquals('<array _type="array"><foo _class="stdclass" _type="object" /></array>', strtolower($s->getSerializedData()));
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_TypeHints_TestCase::main') {
    XML_Serializer_Option_TypeHints_TestCase::main();
}
?>
