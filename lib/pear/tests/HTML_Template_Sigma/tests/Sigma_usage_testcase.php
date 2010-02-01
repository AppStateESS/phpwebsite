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
 * @version     CVS: $Id: Sigma_usage_testcase.php,v 1.5 2008/07/22 15:07:48 avb Exp $
 * @link        http://pear.php.net/package/HTML_Template_Sigma
 * @ignore
 */

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Test case for common package usage patterns
 *
 * @category    HTML
 * @package     HTML_Template_Sigma
 * @author      Alexey Borzov <avb@php.net>
 * @version     1.2.0
 * @ignore
 */
class Sigma_Usage_TestCase extends PHPUnit_Framework_TestCase
{
   /**
    * A template object
    * @var object
    */
    var $tpl;

    function setUp()
    {
        $className = 'HTML_Template_' . $GLOBALS['IT_class'];
        $this->tpl =& new $className(dirname(__FILE__) . '/templates');
    }

    function tearDown()
    {
        unset($this->tpl);
    }

    function _stripWhitespace($str)
    {
        return preg_replace('/\\s+/', '', $str);
    }

    function _methodExists($name)
    {
        if (in_array(strtolower($name), array_map('strtolower', get_class_methods($this->tpl)))) {
            return true;
        }
        $this->assertTrue(false, 'method '. $name . ' not implemented in ' . get_class($this->tpl));
        return false;
    }


   /**
    * Tests iterations over two blocks
    *
    */
    function testBlockIteration()
    {
        $data = array(
            'a',
            array('b', array('1', '2', '3', '4')),
            'c',
            array('d', array('5', '6', '7'))
        );

        $result = $this->tpl->loadTemplateFile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                $this->tpl->setVariable('outer', $value[0]);
                foreach ($value[1] as $v) {
                    $this->tpl->setVariable('inner', $v);
                    $this->tpl->parse('inner_block');
                }
            } else {
                $this->tpl->setVariable('outer', $value);
            }
            $this->tpl->parse('outer_block');
        }
        $this->assertEquals('a#b|1|2|3|4#c#d|5|6|7#', $this->_stripWhitespace($this->tpl->get()));
    }

   /**
    *
    *
    */
    function testTouchBlockIteration()
    {
        $data = array('a','b','c','d','e');
        $result = $this->tpl->loadTemplateFile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        for ($i = 0; $i < count($data); $i++) {
            $this->tpl->setVariable('outer', $data[$i]);
            // the inner_block is empty and should be removed
            if (0 == $i % 2) {
                $this->tpl->touchBlock('inner_block');
            }
            $this->tpl->parse('outer_block');
        }
        $this->assertEquals('a|#b#c|#d#e|#', $this->_stripWhitespace($this->tpl->get()));
    }

   /**
    *
    */
    function testHideBlockIteration()
    {
        if (!$this->_methodExists('hideBlock')) {
            return;
        }
        $data = array('a','b','c','d','e');
        $result = $this->tpl->loadTemplateFile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        for ($i = 0; $i < count($data); $i++) {
            $this->tpl->setVariable(array(
                'inner' => $i + 1,
                'outer' => $data[$i]
            ));
            // the inner_block is not empty, but should be removed
            if (0 == $i % 2) {
                $this->tpl->hideBlock('inner_block');
            }
            $this->tpl->parse('outer_block');
        }
        $this->assertEquals('a#b|2#c#d|4#e#', $this->_stripWhitespace($this->tpl->get()));
    }
}
?>
