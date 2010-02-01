<?php
/**
 * Example of usage for HTML_Template_Sigma, basic variables and blocks
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
 * @version     CVS: $Id: example_1.php,v 1.2 2007/05/19 13:31:19 avb Exp $
 * @ignore
 */

/**
 * Template class
 */
require_once 'HTML/Template/Sigma.php';

$listAry = array(
    array('foo', 'bar'),
    'stuff',
    array('baz', 'quux'),
    'more stuff'
);

// instantiate the template object, templates will be loaded from the
// 'templates' directory, no caching will take place
$tpl =& new HTML_Template_Sigma('./templates');

// No errors are expected to happen here
$tpl->setErrorHandling(PEAR_ERROR_DIE);

// default behaviour is to remove unknown variables and empty blocks 
// from the template
$tpl->loadTemplateFile('example_1.html');

// 1. Variable substitution
// you can pass a name and a value to setVariable()
$tpl->setVariable('var1', 'Value 1');
// you can also pass an associative array
$tpl->setVariable(array(
    'var2' => 'Value 2',
    'var3' => 'Value 3'
));
// setGlobalVariable works the same
$tpl->setGlobalVariable('glob', 'I am global');

// 2. Empty/nonempty blocks
// 2.1 Non-empty blocks
$tpl->setVariable(array(
    'var_ne_1' => 'Value for block 1',
    'var_ne_2' => 'Value for subblock 2'
));
// 2.2 Empty blocks
$tpl->setVariable('var_e_2', 'Value for parent block');

// 3. Local/global difference
$tpl->setCurrentBlock('list');
foreach ($listAry as $item) {
    if (is_array($item)) {
        $tpl->setVariable(array(
            'local_1' => $item[0],
            'local_2' => $item[1]
        ));
    } else {
        $tpl->setVariable('local_1', $item);
    }
    $tpl->parseCurrentBlock();
}

// output the results
$tpl->show();

?>
