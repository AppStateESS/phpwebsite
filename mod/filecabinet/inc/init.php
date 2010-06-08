<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

core\Text::addTag('filecabinet', array('document'));
core\Core::requireInc('filecabinet', 'parse.php');
core\Core::requireInc('filecabinet', 'defines.php');
core\Core::initModClass('filecabinet', 'Cabinet.php');

?>