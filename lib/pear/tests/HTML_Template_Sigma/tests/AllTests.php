<?php
/**
 * Unit tests for HTML_Template_Sigma
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_Template_Sigma
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: AllTests.php,v 1.1 2008/07/22 15:07:48 avb Exp $
 * @link        http://pear.php.net/package/HTML_Template_Sigma
 * @ignore
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'HTML_Template_Sigma_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

/**
 * Class for file / directory manipulation from PEAR package
 */
require_once 'System.php';

$Sigma_cache_dir = System::mktemp('-d sigma');

// What class are we going to test?
// It is possible to also use the unit tests to test HTML_Template_ITX, which
// also implements Integrated Templates API
$IT_class = 'Sigma';
// $IT_class = 'ITX';

chdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

require_once dirname(__FILE__) . '/Sigma_api_testcase.php';
require_once dirname(__FILE__) . '/Sigma_cache_testcase.php';
require_once dirname(__FILE__) . '/Sigma_usage_testcase.php';
require_once dirname(__FILE__) . '/Sigma_bug_testcase.php';

require_once 'HTML/Template/' . $IT_class . '.php';

class HTML_Template_Sigma_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('HTML_Template_Sigma package');

        $suite->addTestSuite('Sigma_api_testcase');
        $suite->addTestSuite('Sigma_cache_testcase');
        $suite->addTestSuite('Sigma_usage_testcase');
        $suite->addTestSuite('Sigma_bug_testcase');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'HTML_Template_Sigma_AllTests::main') {
    HTML_Template_Sigma_AllTests::main();
}
?>
