<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

core\Core::initModClass('block', 'Block.php');
require_once PHPWS_SOURCE_DIR . 'mod/block/inc/parse.php';
core\Text::addTag('block', 'view');

?>