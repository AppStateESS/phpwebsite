<?php

/**
 * Master Unit Test Suite file for XML_Parser
 * 
 * This top-level test suite file organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -u". 
 *
 * PHP version 5
 *
 * @category   XML
 * @package    XML_Parser
 * @subpackage UnitTesting
 * @author     Chuck Burgess <ashnazg@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: AllTests.php,v 1.1 2009/01/22 00:23:25 ashnazg Exp $
 * @link       http://pear.php.net/package/XML_Parser
 * @since      1.3.2
 */


/**
 * Check PHP version... PhpUnit v3+ requires at least PHP v5.1.4
 */
if (version_compare(PHP_VERSION, "5.1.4") < 0) {
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
    define('PHPUnit_MAIN_METHOD', 'XML_Parser_AllTests::main');
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
// there are no PhpUnit test files... only PHPTs.. so nothing is listed here

/**
 * directory where PHPT tests are located
 */
define('XML_PARSER_DIR_PHPT', dirname(__FILE__));

/**
 * Master Unit Test Suite class for XML_Parser
 * 
 * This top-level test suite class organizes 
 * all class test suite files, 
 * so that the full suite can be run 
 * by PhpUnit or via "pear run-tests -up xml_util". 
 *
 * @category   XML
 * @package    XML_Parser
 * @subpackage UnitTesting
 * @author     Chuck Burgess <ashnazg@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/XML_Parser
 * @since      1.2.0a1
 */
class XML_Parser_AllTests
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
            'XML_Parser Full Suite of Unit Tests');

        /*
         * You must add each additional class-level test suite name here
         */
        // there are no PhpUnit test files... only PHPTs.. so nothing is listed here

        /*
         * add PHPT tests
         */
        $phpt = new PHPUnit_Extensions_PhptTestSuite(XML_PARSER_DIR_PHPT);
        $suite->addTestSuite($phpt);

        return $suite;
    }
}

/**
 * Call the main method if this file is executed directly
 * @internal PhpUnit would have to rename PHPUnit_MAIN_METHOD to PHPUNIT_MAIN_METHOD
 *           to make this usage meet the PEAR CS... we cannot rename it here.
 */
if (PHPUnit_MAIN_METHOD == 'XML_Parser_AllTests::main') {
    XML_Parser_AllTests::main();
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
