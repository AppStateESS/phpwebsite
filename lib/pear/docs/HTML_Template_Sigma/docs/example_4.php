<?php
/**
 * Example of usage for HTML_Template_Sigma, callbacks
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
 * @version     CVS: $Id: example_4.php,v 1.3 2007/05/19 13:31:19 avb Exp $
 * @ignore
 */

/**
 * Template class
 */
require_once 'HTML/Template/Sigma.php';

function toggle($item1, $item2)
{
    static $i = 1;

    return $i++ % 2? $item1: $item2;
}

// remember, this is an example only. there are lots of more advanced i18n solutions to use! :]
function translate($str)
{
    global $lang, $aryI18n;

    return isset($aryI18n[$lang][$str])? $aryI18n[$lang][$str]: $str;
}

function letters($str)
{
    return preg_replace('/[^\\w\\s]/', '', $str);
}

$ary = array(
    array('code' => 'SIGMA_OK', 'message' => '&nbsp;', 'reason' => 'Everything went OK', 'solution' => '&nbsp;'),
    array('code' => 'SIGMA_BLOCK_NOT_FOUND', 'message' => 'Cannot find block <i>\'blockname\'</i>', 'reason' => 'Tried to access block that does not exist', 'solution' => 'Either add the block or fix the block name'),
    array('code' => 'SIGMA_BLOCK_DUPLICATE', 'message' => 'The name of a block must be unique within a template. Block <i>\'blockname\'</i> found twice.', 'reason' => 'Tried to load a template with several blocks sharing the same name', 'solution' => 'Get rid of one of the blocks or rename it'),
    array('code' => 'SIGMA_INVALID_CALLBACK', 'message' => 'Callback does not exist', 'reason' => 'A callback function you wanted to use does not exist', 'solution' => 'Pass a name of an existing function to setCallbackfunction()')
);

// I speak neither German, nor French. The strings are from phpBB translations (http://www.phpbb.com/)
$aryI18n = array(
    'de' => array(
        'Send private message' => 'Private Nachricht senden',
        'Username' => 'Benutzername',
        'Find all posts by {username}' => 'Alle Beiträge von {username} anzeigen'
    ),
    'fr' => array(
        'Send private message' => 'Envoyer un message privé',
        'Username' => 'Nom d\'utilisateur',
        'Find all posts by {username}' => 'Trouver tous les messages de {username}'
    )
);
$langsAry = array('de' => 'German', 'fr' => 'French');

// instantiate the template object, templates will be loaded from the
// 'templates' directory, no caching will take place
$tpl =& new HTML_Template_Sigma('./templates');

// No errors are expected to happen here
$tpl->setErrorHandling(PEAR_ERROR_DIE);

// default behaviour is to remove unknown variables and empty blocks 
// from the template
$tpl->loadTemplateFile('example_4.html');

// 1. Using callbacks for minor presentation changes
$tpl->setCallbackFunction('bgcolor', 'toggle');

foreach ($ary as $item) {
    $tpl->setVariable($item);
    $tpl->parse('table_row');
}

// 2. Using callbacks for i18n
// We don't set a callback function, thus the function call will be replaced
// by function's first argument (better than to throw an error, I think)
$tpl->setVariable(array(
    'language' => 'English (default)',
    'username' => 'Luser'
));
$tpl->parse('i18n_block');

// Now we set a callback function. Please note the third argument, we
// want to process the strings with no variable substitutions done, they
// should be done *after* the translation.
$tpl->setCallbackFunction('translate', 'translate', true);
foreach (array_keys($aryI18n) as $lang) {
    $tpl->setVariable(array(
        'language' => $langsAry[$lang],
        'username' => 'Luser'
    ));
    $tpl->parse('i18n_block');
}

// 3. Shorthand for callbacks, built-in callbacks
// We add a variable that cannot be safely displayed either in HTML,
// in URLs or inside JavaScript string constants without appropriate encoding
$tpl->setVariable('escaped', '"Foo & Bar"');
$tpl->setCallbackFunction('letters', 'letters');

// output the results
$tpl->show();

?>
