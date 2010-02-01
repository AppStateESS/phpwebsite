<?php
/**
 * Example of usage for HTML_Template_Sigma, building the template from multiple files
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
 * @version     CVS: $Id: example_3.php,v 1.2 2007/05/19 13:31:19 avb Exp $
 * @ignore
 */

/**
 * Template class
 */
require_once 'HTML/Template/Sigma.php';

// various data to substitute
$addBlockAry = array(
    '&lt;!-- INCLUDE --&gt;' => 'Includes a file from within a template',
    'addBlockfile()'         => 'Creates a new block in place of a variable placeholder',
    'replaceBlockfile()'     => 'Replaces the existing block with a new content'
);
$replaceBlockAry = array(
    'foo', 'bar', 'baz', 'quux'
);

// instantiate the template object, templates will be loaded from the
// 'templates' directory, no caching will take place
$tpl =& new HTML_Template_Sigma('./templates');

// No errors are expected to happen here
$tpl->setErrorHandling(PEAR_ERROR_DIE);

// default behaviour is to remove unknown variables and empty blocks 
// from the template
$tpl->loadTemplateFile('example_3.html');

// 2. Using addBlockfile()
// addblockfile placeholder will be gone, new_block block will appear in its place
$tpl->addBlockfile('addblockfile', 'new_block', 'example_3_add.html');
foreach ($addBlockAry as $name => $desc) {
    $tpl->setVariable(array(
        'func_name'        => $name,
        'func_description' => $desc
    ));
    $tpl->parse('added_block');
}

// 3. Using replaceBlockfile()
// 3.1 Keeping the previously parsed contents
for ($i = 0; $i < count($replaceBlockAry); $i++) {
    if (2 == $i) {
        // note the third argument, this is done to prevent clearing the parsed contents
        $tpl->replaceBlockfile('replace_block_1', 'example_3_replace_1.html', true);
    }
    $tpl->setVariable('item_title', $replaceBlockAry[$i]);
    $tpl->parse('replace_block_1');
} // for

// 3.2 Discarding the previously parsed contents
$tpl->setVariable('item_title', 'This will be discarded');
$tpl->parse('replace_block_2');
// default behaviour is to discard parsed contents
$tpl->replaceBlockfile('replace_block_2', 'example_3_replace_2.html');
foreach ($replaceBlockAry as $item) {
    $tpl->setVariable('item_title', $item);
    $tpl->parse('replace_block_2_item');
}

// output the results
$tpl->show();

?>
