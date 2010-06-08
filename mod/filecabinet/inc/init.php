<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

Core\Text::addTag('filecabinet', array('document'));
Core\Core::requireInc('filecabinet', 'parse.php');
Core\Core::requireInc('filecabinet', 'defines.php');
Core\Core::initModClass('filecabinet', 'Cabinet.php');

?>