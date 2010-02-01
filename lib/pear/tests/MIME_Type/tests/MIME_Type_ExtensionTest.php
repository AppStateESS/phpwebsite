<?php
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "MIME_Type_ExtensionTest::main");
}

require_once 'PHPUnit/Framework.php';

require_once 'MIME/Type/Extension.php';

/**
 * Test class for MIME_Type_Extension.
 *
 * @author Christian Weiske <cweiske@php.net
 */
class MIME_Type_ExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    MIME_Type_Extension
     * @access protected
     */
    protected $mte;



    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("MIME_Type_ExtensionTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->mte = new MIME_Type_Extension;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    public function testGetMIMEType()
    {
        $this->assertEquals('text/plain',
            $this->mte->getMIMEType('a.txt'));
        $this->assertEquals('text/plain',
            $this->mte->getMIMEType('/path/to/a.txt'));
        $this->assertEquals('image/png',
            $this->mte->getMIMEType('a.png'));
        $this->assertEquals('application/vnd.oasis.opendocument.text',
            $this->mte->getMIMEType('a.odt'));
    }



    public function testGetMIMETypeFullPath()
    {
        $this->assertEquals('text/plain',
            $this->mte->getMIMEType('/path/to/a.txt'));
        $this->assertEquals('text/plain',
            $this->mte->getMIMEType('C:\\Programs\\blubbr.txt'));
    }



    public function testGetMIMETypeNoExtension()
    {
        $this->assertType('PEAR_Error',
            $this->mte->getMIMEType('file'));
        $this->assertType('PEAR_Error',
            $this->mte->getMIMEType('blubbr'));
    }



    public function testGetMIMETypeFullPathNoExtension()
    {
        $this->assertType('PEAR_Error',
            $this->mte->getMIMEType('/path/to/file'));
        $this->assertType('PEAR_Error',
            $this->mte->getMIMEType('C:\\Programs\\blubbr'));
    }



    public function testGetMIMETypeUnknownExtension()
    {
        $this->assertType('PEAR_Error',
            $this->mte->getMIMEType('file.ohmygodthatisnoextension'));
    }



    public function testGetExtension()
    {
        $this->assertEquals('txt',
            $this->mte->getExtension('text/plain'));
        $this->assertEquals('csv',
            $this->mte->getExtension('text/csv'));
    }



    public function testGetExtensionFail()
    {
        $this->assertType('PEAR_Error', $this->mte->getExtension(null));
        $this->assertType('PEAR_Error', $this->mte->getExtension(''));
        $this->assertType('PEAR_Error', $this->mte->getExtension('n'));
        $this->assertType('PEAR_Error', $this->mte->getExtension('n/n'));
    }

}

if (PHPUnit_MAIN_METHOD == "MIME_Type_ExtensionTest::main") {
    MIME_Type_ExtensionTest::main();
}
?>
