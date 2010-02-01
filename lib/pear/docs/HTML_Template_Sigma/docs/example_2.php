<?php
/**
 * Example of usage for HTML_Template_Sigma, block iteration
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
 * @version     CVS: $Id: example_2.php,v 1.3 2007/05/19 13:31:19 avb Exp $
 * @ignore
 */

/**
 * Template class
 */
require_once 'HTML/Template/Sigma.php';

// various data to substitute
$simpleAry  = array('foo', 'bar', 'baz', 'quux');
$complexAry = array(
    array('Error code', 'Error message', 'Reason', 'Solution'),
    array('SIGMA_OK', '&nbsp;', 'Everything went OK', '&nbsp;'),
    array('SIGMA_BLOCK_NOT_FOUND', 'Cannot find block \'%s\'', 'Tried to access block that does not exist', 'Either add the block or fix the block name'),
    array('SIGMA_BLOCK_DUPLICATE', 'The name of a block must be unique within a template. Block \'%s\' found twice.', 'Tried to load a template with several blocks sharing the same name', 'Get rid of one of the blocks or rename it')
);
$menuAry    = array(
    'foo'  => 'First menu element',
    'bar'  => 'Second menu element',
    'baz'  => 'Another menu element',
    'quux' => 'Yet another menu element'
);
$menuSelected = 'bar';
$touchAry     = array(
    array('apples', 10),
    false,
    array('oranges', 20)
);
$hideAry      = array(
    array('restricted' => false, 'data' => array('item_id' => 'foo', 'item_title' => 'Some data')),
    array('restricted' => true,  'data' => array('item_id' => 'bar', 'item_title' => 'More data')),
    array('restricted' => true,  'data' => array('item_id' => 'baz', 'item_title' => 'Even more data')),
    array('restricted' => false, 'data' => array('item_id' => 'quux', 'item_title' => 'Still even more data'))
);

// instantiate the template object, templates will be loaded from the
// 'templates' directory, no caching will take place
$tpl =& new HTML_Template_Sigma('./templates');

// No errors are expected to happen here
$tpl->setErrorHandling(PEAR_ERROR_DIE);

// default behaviour is to remove unknown variables and empty blocks 
// from the template
$tpl->loadTemplateFile('example_2.html');

// 1. Simple block iteration
$tpl->setCurrentBlock('list');
foreach ($simpleAry as $value) {
    $tpl->setVariable('list_item', $value);
    $tpl->parseCurrentBlock();
}

// 2. Nested block iteration
foreach ($complexAry as $inner) {
    foreach ($inner as $value) {
        $tpl->setVariable('table_item', $value);
        // first we parse the innermost block
        $tpl->parse('table_cell');
    }
    // then we parse the outer block
    $tpl->parse('table_row');
}

// 3. Menu-like structures
foreach ($menuAry as $url => $title) {
    // please note that only one inner block will be shown
    // the other one will be considered empty and automatically removed
    if ($menuSelected == $url) {
        // we don't set menu_url to prevent menu_normal from appearing
        // another possible approach here is to use hideBlock()
        $tpl->setVariable('menu_title', $title);
        $tpl->parse('menu_selected');
    } else {
        $tpl->setVariable(array(
            'menu_url'   => $url,
            'menu_title' => $title
        ));
        $tpl->parse('menu_normal');
    }
    // once again, the outer block is parsed after the inner
    $tpl->parse('menu');
}

// 4. Methods to manually control showing/removal of blocks
// touchBlock() example
foreach ($touchAry as $item) {
    if (is_array($item)) {
        $tpl->setVariable(array(
            'touch_stuff'    => $item[0],
            'touch_quantity' => $item[1]
        ));
    } else {
        $tpl->touchBlock('empty_row');
    }
    $tpl->parse('touch_row');
}
// hideBlock() example
foreach ($hideAry as $item) {
    $tpl->setVariable($item['data']);
    if ($item['restricted']) {
        $tpl->hideBlock('edit_link');
    }
    $tpl->parse('hide_item');
}

// 5. Using get() to move blocks around
// This one is pretty simple. Note that by default get() does not clear the 
// original block, so a copy will be created. If you want to move the block,
// pass true as a second parameter to get()
$tpl->parse('list_block');
$tpl->setVariable('duplicate', $tpl->get('list_block'));

// output the results
$tpl->show();

?>
