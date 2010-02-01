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
    define('PHPUnit_MAIN_METHOD', 'XML_Unserializer_Option_Encodings_TestCase::main');
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
class XML_Unserializer_Option_Encodings_TestCase extends PHPUnit_Framework_TestCase {

    public static function main() {
        $suite  = new PHPUnit_Framework_TestSuite('XML_Unserializer_Option_Encodings_TestCase');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    protected function setUp() {}

    protected function tearDown() {}

   /**
    * Test unserializing from UTF-8 to ISO-8859-1
    */
    public function testUtf8ToIso()
    {
        $u = new XML_Unserializer();
        $u->setOption(XML_UNSERIALIZER_OPTION_ENCODING_SOURCE, 'UTF-8');
        $u->setOption(XML_UNSERIALIZER_OPTION_ENCODING_TARGET, 'ISO-8859-1');
        $xml = '<xml>'.utf8_encode('A string containing ü ä Ãê').'</xml>';
        $u->unserialize($xml);
        $this->assertEquals('A string containing ü ä Ãê', $u->getUnserializedData());
    }

}

/**
 * PHPUnit main() hack
 * "Call class::main() if this source file is executed directly."
 */
if (PHPUnit_MAIN_METHOD == 'XML_Unserializer_Option_Encodings_TestCase::main') {
    XML_Unserializer_Option_Encodings_TestCase::main();
}
?>
