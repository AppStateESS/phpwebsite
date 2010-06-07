<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

Core\Core::initModClass('rss', 'RSS.php');

if (!isset($_REQUEST['module'])) {
    RSS::showFeeds();
}

?>