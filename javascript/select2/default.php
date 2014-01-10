<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
function select2Script()
{
    $script = '<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'javascript/select2/select2.min.js"></script>';
    \Layout::addJSHeader($script);
    \Layout::addToStyleList('javascript/select2/select2.css');
    \Layout::addToStyleList('javascript/select2/select2-bootstrap.css');
}
select2Script();


?>
