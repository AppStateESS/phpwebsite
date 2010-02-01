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
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_Option_Linebreaks_TestCase::main');
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
class XML_Serializer_Option_Linebreaks_TestCase extends PHPUnit_Framework_TestCase {

    private $options = array(
        XML_SERIALIZER_OPTION_INDENT     => '',
        XML_SERIALIZER_OPTION_LINEBREAKS => "\n",
    );

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Serializer_Option_Linebreaks_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test a simple string
    */
    public function testUnixLinebreak()
    {
        $s = new XML_Serializer($this->options);
        $s->serialize(array('foo' => 'bar'));
        $this->assertEquals('<array>
<foo>bar</foo>
</array>', $s->getSerializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_Option_Linebreaks_TestCase::main') {
    XML_Serializer_Option_Linebreaks_TestCase::main();
}
?>
