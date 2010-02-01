<?php
/**
 * Master Unit Test Suite file for XML_Serializer
 * 
 * This top-level test suite file organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -u". 
 *
 * PHP version 5
 *
 * @category   XML
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Chuck Burgess <ashnazg@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: AllTests.php,v 1.1 2008/09/17 16:57:07 ashnazg Exp $
 * @link       http://pear.php.net/package/XML_Serializer
 * @since      0.19.1
 */


/**
 * Check PHP version... PhpUnit v3+ requires at least PHP v5.1.4
 */
if (version_compare(PHP_VERSION, '5.1.4') < 0) {
    // Cannnot run test suites
    echo 'Cannot run test suite via PhpUnit... requires at least PHP v5.1.4.' . PHP_EOL;
    echo 'Use "pear run-tests -p xml_util" to run the PHPT tests directly.' . PHP_EOL
;
    exit(1);
}


/**
 * Derive the "main" method name
 * @internal PhpUnit would have to rename PHPUnit_MAIN_METHOD to PHPUNIT_MAIN_METHOD
 *           to make this usage meet the PEAR CS... we cannot rename it here.
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'XML_Serializer_AllTests::main');
}


/*
 * Files needed by PhpUnit
 */
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Extensions/PhptTestSuite.php';

/*
 * You must add each additional class-level test suite file here
 */
require_once 'Serializer_Arrays_TestCase.php';
require_once 'Serializer_Objects_TestCase.php';
require_once 'Serializer_Option_AttributesContent_TestCase.php';
require_once 'Serializer_Option_CDataSections_TestCase.php';
require_once 'Serializer_Option_ClassName_TestCase.php';
require_once 'Serializer_Option_Comment_TestCase.php';
require_once 'Serializer_Option_DefaultTag_TestCase.php';
require_once 'Serializer_Option_DocType_TestCase.php';
require_once 'Serializer_Option_EncodeFunc_TestCase.php';
require_once 'Serializer_Option_IgnoreNull_TestCase.php';
require_once 'Serializer_Option_Indent_TestCase.php';
require_once 'Serializer_Option_Linebreaks_TestCase.php';
require_once 'Serializer_Option_Mode_TestCase.php';
require_once 'Serializer_Option_Namespace_TestCase.php';
require_once 'Serializer_Option_ReturnResult_TestCase.php';
require_once 'Serializer_Option_RootAttributes_TestCase.php';
require_once 'Serializer_Option_RootName_TestCase.php';
require_once 'Serializer_Option_TagMap_TestCase.php';
require_once 'Serializer_Option_TypeHints_TestCase.php';
require_once 'Serializer_Option_XmlDeclaration_TestCase.php';
require_once 'Serializer_Scalars_TestCase.php';
require_once 'Unserializer_Arrays_TestCase.php';
require_once 'Unserializer_Objects_TestCase.php';
require_once 'Unserializer_Option_Encodings_TestCase.php';
require_once 'Unserializer_Option_GuessTypes_TestCase.php';
require_once 'Unserializer_Option_Whitespace_TestCase.php';
require_once 'Unserializer_Scalars_TestCase.php';

/**
 * directory where PHPT tests are located
 */
define('XML_SERIALIZER_DIR_PHPT', dirname(__FILE__));

/**
 * Master Unit Test Suite class for XML_Serializer
 * 
 * This top-level test suite class organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -up xml_util". 
 *
 * @category   XML
 * @package    XML_Serializer
 * @subpackage tests
 * @author     Chuck Burgess <ashnazg@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: 0.19.2
 * @link       http://pear.php.net/package/XML_Serializer
 * @since      0.19.1
 */
class XML_Serializer_AllTests
{

    /**
     * Launches the TextUI test runner
     *
     * @return void
     * @uses PHPUnit_TextUI_TestRunner
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }


    /**
     * Adds all class test suites into the master suite
     *
     * @return PHPUnit_Framework_TestSuite a master test suite
     *                                     containing all class test suites
     * @uses PHPUnit_Framework_TestSuite
     */ 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite(
            'XML_Serializer Full Suite of Unit Tests');

        /*
         * You must add each additional class-level test suite name here
         */
        $suite->addTestSuite('XML_Serializer_Arrays_TestCase');
        $suite->addTestSuite('XML_Serializer_Objects_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_AttributesContent_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_CDataSections_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_ClassName_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_Comment_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_DefaultTag_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_DocType_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_EncodeFunc_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_IgnoreNull_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_Indent_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_Linebreaks_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_Mode_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_Namespace_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_ReturnResult_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_RootAttributes_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_RootName_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_TagMap_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_TypeHints_TestCase');
        $suite->addTestSuite('XML_Serializer_Option_XmlDeclaration_TestCase');
        $suite->addTestSuite('XML_Serializer_Scalars_TestCase');
        $suite->addTestSuite('XML_Unserializer_Arrays_TestCase');
        $suite->addTestSuite('XML_Unserializer_Objects_TestCase');
        $suite->addTestSuite('XML_Unserializer_Option_Encodings_TestCase');
        $suite->addTestSuite('XML_Unserializer_Option_GuessTypes_TestCase');
        $suite->addTestSuite('XML_Unserializer_Option_Whitespace_TestCase');
        $suite->addTestSuite('XML_Unserializer_Scalars_TestCase');


        /*
         * add PHPT tests
         */
        $phpt = new PHPUnit_Extensions_PhptTestSuite(XML_SERIALIZER_DIR_PHPT);
        $suite->addTestSuite($phpt);

        return $suite;
    }
}

/**
 * Call the main method if this file is executed directly
 * @internal PhpUnit would have to rename PHPUnit_MAIN_METHOD to PHPUNIT_MAIN_METHOD
 *           to make this usage meet the PEAR CS... we cannot rename it here.
 */
if (PHPUnit_MAIN_METHOD == 'XML_Serializer_AllTests::main') {
    XML_Serializer_AllTests::main();
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
