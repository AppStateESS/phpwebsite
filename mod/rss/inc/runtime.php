<?php

PHPWS_Core::initModClass('rss', 'RSS.php');

if (!isset($_REQUEST['module'])) {
    RSS::showFeeds();
 }

?>