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
 * @version     CVS: $Id: Sigma_api_testcase.php,v 1.11 2008/07/22 19:17:17 avb Exp $
 * @link        http://pear.php.net/package/HTML_Template_Sigma
 * @ignore
 */

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Test case for class API
 *
 * @category    HTML
 * @package     HTML_Template_Sigma
 * @author      Alexey Borzov <avb@php.net>
 * @version     1.2.0
 * @ignore
 */
class Sigma_api_TestCase extends PHPUnit_Framework_TestCase
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
    * Tests a setTemplate method
    *
    */
    function testSetTemplate()
    {
        $result = $this->tpl->setTemplate('A template', false, false);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error setting template: '. $result->getMessage());
        }
        $this->assertEquals('A template', $this->tpl->get());
    }

   /**
    * Tests a loadTemplatefile method
    *
    */
    function testLoadTemplatefile()
    {
        $result = $this->tpl->loadTemplatefile('loadtemplatefile.html', false, false);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->assertEquals('A template', trim($this->tpl->get()));
    }

   /**
    * Tests a setVariable method
    *
    */
    function testSetVariable()
    {
        $result = $this->tpl->setTemplate('{placeholder1} {placeholder2} {placeholder3}', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error setting template: '. $result->getMessage());
        }
        // "scalar" call
        $this->tpl->setVariable('placeholder1', 'var1');
        // array call
        $this->tpl->setVariable(array(
            'placeholder2' => 'var2',
            'placeholder3' => 'var3'
        ));
        $this->assertEquals('var1 var2 var3', $this->tpl->get());
    }

   /**
    * Tests a setVariable method with array handles
    *
    */
    function testExtendedSetVariale()
    {
        $result = $this->tpl->setTemplate('{index1.0} {index1.subindex1} {index1.subindex2.0}', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error setting template: '. $result->getMessage());
        }
        $this->tpl->setVariable('index1', array(
            'var1',
            'subindex1' => 'var2',
            'subindex2' => array(
                'var3'
            )
        ));
        $this->assertEquals('var1 var2 var3', $this->tpl->get());
    }

   /**
    * Tests the <!-- INCLUDE --> functionality
    *
    */
    function testInclude()
    {
        $result = $this->tpl->loadTemplateFile('include.html', false, false);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->assertEquals('Master file; Included file', trim($this->tpl->get()));
    }

    function testCurrentBlock()
    {
        $result = $this->tpl->loadTemplateFile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable('outer', 'a');
        $this->tpl->setCurrentBlock('inner_block');
        for ($i = 0; $i < 5; $i++) {
            $this->tpl->setVariable('inner', $i + 1);
            $this->tpl->parseCurrentBlock();
        } // for
        $this->assertEquals('a|1|2|3|4|5#', $this->_stripWhitespace($this->tpl->get()));
    }

    function testRemovePlaceholders()
    {
        $result = $this->tpl->setTemplate('{placeholder1},{placeholder2},{placeholder3},{placeholder4.index}', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error setting template: '. $result->getMessage());
        }
        // we do not set {placeholder3}
        $this->tpl->setVariable(array(
            'placeholder1' => 'var1',
            'placeholder2' => 'var2'
        ));
        $this->assertEquals('var1,var2,,', $this->tpl->get());

        // Default behaviour is to remove {stuff} from data as well
        $result = $this->tpl->setTemplate('{placeholder1},{placeholder2},{placeholder3}', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error setting template: '. $result->getMessage());
        }
        $this->tpl->setVariable(array(
            'placeholder1' => 'var1',
            'placeholder2' => 'var2',
            'placeholder3' => 'var3{stuff}'
        ));
        $this->assertEquals('var1,var2,var3', $this->tpl->get());
    }

    function testTouchBlock()
    {
        $result = $this->tpl->loadTemplateFile('blockiteration.html', false, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable('outer', 'data');
        // inner_block should be preserved in output, even if empty
        $this->tpl->touchBlock('inner_block');
        $this->assertEquals('data|{inner}#', $this->_stripWhitespace($this->tpl->get()));
    }

    function testHideBlock()
    {
        if (!$this->_methodExists('hideBlock')) {
            return;
        }
        $result = $this->tpl->loadTemplateFile('blockiteration.html', false, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable(array(
            'outer' => 'data',
            'inner' => 'stuff'
        ));
        // inner_block is not empty, but should be removed nonetheless
        $this->tpl->hideBlock('inner_block');
        $this->assertEquals('data#', $this->_stripWhitespace($this->tpl->get()));
    }

    function testSetGlobalVariable()
    {
        if (!$this->_methodExists('setGlobalVariable')) {
            return;
        }
        $result = $this->tpl->loadTemplateFile('globals.html', false, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setGlobalVariable('glob', 'glob');
        $this->tpl->setGlobalVariable('globArray', array('glob1', 'glob2'));
        // {var2} is not, block_two should be removed
        $this->tpl->setVariable(array(
            'var1' => 'one',
            'var3' => 'three'
        ));
        for ($i = 0; $i < 3; $i++) {
            $this->tpl->setVariable('var4', $i + 1);
            $this->tpl->parse('block_four');
        } // for
        $this->assertEquals('glob:glob1:one#glob:glob1:three|glob:glob2:1|glob:glob2:2|glob:glob2:3#', $this->_stripWhitespace($this->tpl->get()));
    }

    function testOptionPreserveData()
    {
        if (!$this->_methodExists('setOption')) {
            return;
        }
        $this->tpl->setTemplate('{placeholder1},{placeholder2},{placeholder3}', true, true);
        $this->tpl->setOption('preserve_data', true);
        $this->tpl->setVariable(array(
            'placeholder1' => 'var1',
            'placeholder2' => 'var2',
            'placeholder3' => 'var3{stuff}'
        ));
        $this->assertEquals('var1,var2,var3{stuff}', $this->tpl->get());
    }

    function testPlaceholderExists()
    {
        // simple vars
        $this->tpl->setTemplate('{var}');
        $this->assertEquals('__global__', $this->tpl->placeholderExists('var'), 'Existing placeholder \'var\' reported as nonexistant');
        $this->assertEquals('', $this->tpl->placeholderExists('foobar'), 'Nonexistant placeholder \'foobar\' reported as existing');
        $this->assertEquals('__global__', $this->tpl->placeholderExists('var', '__global__'), 'Existing in block \'__global__\' placeholder \'var\' reported as nonexistant');
        $this->assertEquals('', $this->tpl->placeholderExists('foobar', '__global__'), 'Nonexistant in block \'__global__\' placeholder \'foobar\' reported as existing');

        // extended vars
        $this->tpl->setTemplate('{arrayVar.0}');
        $this->assertEquals('__global__', $this->tpl->placeholderExists('arrayVar.0'), 'Existing placeholder \'arrayVar.0\' reported as nonexistant');
        $this->assertEquals('', $this->tpl->placeholderExists('arrayVar'), 'Nonexistant placeholder \'arrayVar\' reported as existing');
        $this->assertEquals('__global__', $this->tpl->placeholderExists('arrayVar.0', '__global__'), 'Existing in block \'__global__\' placeholder \'arrayVar.0\' reported as nonexistant');
        $this->assertEquals('', $this->tpl->placeholderExists('arrayVar', '__global__'), 'Nonexistant in block \'__global__\' placeholder \'arrayVar\' reported as existing');
    }

    function testBlockExists()
    {
        $this->tpl->setTemplate('{var}');
        $this->assertTrue($this->tpl->blockExists('__global__'), 'Existing block \'__global__\' reported as nonexistant');
        $this->assertTrue(!$this->tpl->blockExists('foobar'), 'Nonexistant block \'foobar\' reported as existing');
    }

    function testAddBlock()
    {
        $result = $this->tpl->loadTemplatefile('blocks.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->addBlock('var', 'added', 'added:{new_var}');
        $this->assertTrue($this->tpl->blockExists('added'), 'The new block seems to be missing');
        $this->assertTrue(!$this->tpl->placeholderExists('var'), 'The old variable seems to be still present in the template');
        $this->tpl->setVariable('new_var', 'new_value');
        $this->assertEquals('added:new_value', $this->_stripWhitespace($this->tpl->get()));
    }

    function testAddBlockfile()
    {
        $result = $this->tpl->loadTemplatefile('blocks.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $result = $this->tpl->addBlockfile('var', 'added', 'addblock.html');
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error adding block from file: '. $result->getMessage());
        }
        $this->assertTrue($this->tpl->blockExists('added'), 'The new block seems to be missing');
        $this->assertTrue(!$this->tpl->placeholderExists('var'), 'The old variable seems to be still present in the template');
        $this->tpl->setVariable('new_var', 'new_value');
        $this->assertEquals('added:new_value', $this->_stripWhitespace($this->tpl->get()));
    }

    function testReplaceBlock()
    {
        $result = $this->tpl->loadTemplatefile('blocks.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable('old_var', 'old_value');
        $this->tpl->parse('old_block');
        // old_block's contents should be discarded
        $this->tpl->replaceBlock('old_block', 'replaced:{replaced_var}#', false);
        $this->assertTrue(!$this->tpl->blockExists('old_inner_block') && !$this->tpl->placeholderExists('old_var'),
                          'The replaced block\'s contents seem to be still present');
        $this->tpl->setVariable('replaced_var', 'replaced_value');
        $this->tpl->parse('old_block');
        // this time old_block's contents should be preserved
        $this->tpl->replaceBlock('old_block', 'replaced_again:{brand_new_var}', true);
        $this->tpl->setVariable('brand_new_var', 'brand_new_value');
        $this->assertEquals('replaced:replaced_value#replaced_again:brand_new_value', $this->_stripWhitespace($this->tpl->get()));
    }

    function testReplaceBlockfile()
    {
        $result = $this->tpl->loadTemplatefile('blocks.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable('old_var', 'old_value');
        $this->tpl->parse('old_block');
        // old_block's contents should be discarded
        $result = $this->tpl->replaceBlockfile('old_block', 'replaceblock.html', false);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error replacing block from file: '. $result->getMessage());
        }
        $this->assertTrue(!$this->tpl->blockExists('old_inner_block') && !$this->tpl->placeholderExists('old_var'),
                          'The replaced block\'s contents seem to be still present');
        $this->tpl->setVariable(array(
            'replaced_var'       => 'replaced_value',
            'replaced_inner_var' => 'inner_value'
        ));
        $this->tpl->parse('old_block');
        // this time old_block's contents should be preserved
        $result = $this->tpl->replaceBlockfile('old_block', 'addblock.html', true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error replacing block from file: '. $result->getMessage());
        }
        $this->tpl->setVariable('new_var', 'again');
        $this->assertEquals('replaced:replaced_value|inner_value#added:again', $this->_stripWhitespace($this->tpl->get()));
    }

    function testCallback()
    {
        $result = $this->tpl->loadTemplatefile('callback.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->tpl->setVariable('username', 'luser');
        $this->tpl->setCallbackFunction('uppercase', 'strtoupper');
        $this->tpl->setCallbackFunction('russian', array(&$this, '_doRussian'), true);
        $this->tpl->setCallbackFunction('lowercase', 'strtolower');
        $this->tpl->setCallBackFunction('noarg', array(&$this, '_doCallback'));
        $this->assertEquals('callback#word#HELLO,LUSER!#Привет,luser!', $this->_stripWhitespace($this->tpl->get()));
    }

    function _doCallback()
    {
        return 'callback';
    }

    function _doRussian($arg)
    {
        $ary = array('Hello, {username}!' => 'Привет, {username}!');
        return isset($ary[$arg])? $ary[$arg]: $arg;
    }

    function testGetBlockList()
    {
        // expected tree...
        $tree = array(
            'name'     => '__global__',
            'children' => array(
                array(
                    'name'     => 'outer_block',
                    'children' => array(
                        array('name' => 'inner_block')
                    )
                )
            )
        );

        $result = $this->tpl->loadTemplatefile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->assertEquals($tree, $this->tpl->getBlockList('__global__', true));
        $this->assertEquals(array('inner_block'), $this->tpl->getBlockList('outer_block'));
    }

    function testGetPlaceholderList()
    {
        $result = $this->tpl->loadTemplatefile('blockiteration.html', true, true);
        if (PEAR::isError($result)) {
            $this->assertTrue(false, 'Error loading template file: '. $result->getMessage());
        }
        $this->assertEquals(array('outer'), $this->tpl->getPlaceholderList('outer_block'));
    }

    function testCallbackShorthand()
    {
        $this->tpl->setTemplate('{var}|{var:h}|{var:u}|{var:j}|{var:r}|{var:e}|{var:uppercase}|{arrayVar.0:j}|{arrayVar.index:uppercase}', true, true);
        $this->tpl->setCallbackFunction('uppercase', 'strtoupper');
        $this->tpl->setVariable('var', '"m&m" ');
        $this->tpl->setVariable('arrayVar', array('"m&m"', 'index' => '"m&m"'));
        $this->assertEquals('"m&m" |&quot;m&amp;m&quot; |%22m%26m%22+|\\"m&m\\" |%22m%26m%22%20|&quot;m&amp;m&quot; |"M&M" |\\"m&m\\"|"M&M"', $this->tpl->get());
    }

    function testClearVariables()
    {
        if (!$this->_methodExists('clearVariables')) {
            return;
        }
        $this->tpl->setTemplate('<!-- BEGIN block -->{var_1}:<!-- END block -->{var_2}', true, true);
        $this->tpl->setVariable(array(
            'var_1' => 'a',
            'var_2' => 'b'
        ));
        $this->tpl->parse('block');
        $this->tpl->clearVariables();
        $this->assertEquals('a:', $this->_stripWhitespace($this->tpl->get()));
    }

    function testCallbackParametersQuoting()
    {
        $this->tpl->setTemplate(
            '|func_fake(\' foo \')|func_fake( foo )|func_fake(<a href="javascript:foo(bar,baz)">foo</a>)' .
            '|func_fake("O\'really")|func_fake(\'\\\\O\\\'really\\\\\')|func_fake("\\\\O\\"really\\\\")|'
        );
        $this->assertEquals('| foo |foo|<a href="javascript:foo(bar,baz)">foo</a>|O\'really|\\O\'really\\|\\O"really\\|', $this->tpl->get());
    }

    function testComments()
    {
        $this->tpl->setTemplate('A template<!-- COMMENT -->with comment<!-- /COMMENT -->');
        $this->assertEquals('A template', $this->tpl->get());
    }

    function testOptionCharset()
    {
        $this->tpl->setOption('charset', 'windows-1251');
        $this->tpl->setTemplate('{var:e}');
        $this->tpl->setVariable('var', 'Тестируем');
        $this->assertEquals('&#1058;&#1077;&#1089;&#1090;&#1080;&#1088;&#1091;&#1077;&#1084;', $this->tpl->get());
    }
}

?>
