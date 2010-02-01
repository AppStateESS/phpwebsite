<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'MIME_Type_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__) . '/../');

require_once 'TypeTest.php';
require_once 'MIME_Type_ExtensionTest.php';


class MIME_Type_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('MIME_Type tests');
        /** Add testsuites, if there is. */
        $suite->addTestSuite('MIME_TypeTest');
        $suite->addTestSuite('MIME_Type_ExtensionTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'MIME_Type_AllTests::main') {
    MIME_Type_AllTests::main();
}
?>