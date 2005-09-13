<?php

PHPWS_Core::initModClass('block', 'Block.php');
require_once PHPWS_SOURCE_DIR . 'mod/block/inc/parse.php';
PHPWS_Text::addTag('block', 'viewBlock');

?>