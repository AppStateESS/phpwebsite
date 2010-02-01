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
 * @version     CVS: $Id: Sigma_cache_testcase.php,v 1.4 2008/07/22 15:07:48 avb Exp $
 * @link        http://pear.php.net/package/HTML_Template_Sigma
 * @ignore
 */

/**
 * Test case for class API
 */
require_once dirname(__FILE__) . '/Sigma_api_testcase.php';

/**
 * Test case for cache functionality
 *
 * The class builds upon API tests, checking that methods that should produce
 * cache files actually do this.
 *
 * @category    HTML
 * @package     HTML_Template_Sigma
 * @author      Alexey Borzov <avb@php.net>
 * @version     1.2.0
 * @ignore
 */
class Sigma_cache_TestCase extends Sigma_api_TestCase
{
    function setUp()
    {
        global $Sigma_cache_dir;

        $className = 'HTML_Template_' . $GLOBALS['IT_class'];
        $this->tpl =& new $className(dirname(__FILE__) . '/templates', $Sigma_cache_dir);
    }

    function _removeCachedFiles($filename)
    {
        if (!is_array($filename)) {
            $filename = array($filename);
        }
        foreach ($filename as $file) {
            $cachedName = $this->tpl->_cachedName($file);
            if (@file_exists($cachedName)) {
                @unlink($cachedName);
            }
        }
    }

    function assertCacheExists($filename)
    {
        if (!is_array($filename)) {
            $filename = array($filename);
        }
        foreach ($filename as $file) {
            $cachedName = $this->tpl->_cachedName($file);
            if (!@file_exists($cachedName)) {
                $this->assertTrue(false, "File '$file' is not cached");
            }
        }
    }

    function testLoadTemplatefile()
    {
        if (!$this->_methodExists('_isCached')) {
            return;
        }
        $this->_removeCachedFiles('loadtemplatefile.html');
        parent::testLoadTemplateFile();
        $this->assertCacheExists('loadtemplatefile.html');
        parent::testLoadTemplateFile();
    }

    function testAddBlockfile()
    {
        if (!$this->_methodExists('_isCached')) {
            return;
        }
        $this->_removeCachedFiles(array('blocks.html', 'addblock.html'));
        parent::testAddBlockfile();
        $this->assertCacheExists(array('blocks.html', 'addblock.html'));
        parent::testAddBlockfile();
    }

    function testReplaceBlockFile()
    {
        if (!$this->_methodExists('_isCached')) {
            return;
        }
        $this->_removeCachedFiles(array('blocks.html', 'replaceblock.html'));
        parent::testReplaceBlockfile();
        $this->assertCacheExists(array('blocks.html', 'replaceblock.html'));
        parent::testReplaceBlockfile();
    }

    function testInclude()
    {
        if (!$this->_methodExists('_isCached')) {
            return;
        }
        $this->_removeCachedFiles(array('include.html', '__include.html'));
        parent::testInclude();
        $this->assertCacheExists(array('include.html', '__include.html'));
        parent::testInclude();
    }

    function testCallback()
    {
        if (!$this->_methodExists('_isCached')) {
            return;
        }
        $this->_removeCachedFiles('callback.html');
        parent::testCallback();
        $this->assertCacheExists('callback.html');
        parent::testCallback();
    }
}
?>
