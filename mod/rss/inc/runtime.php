<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
\phpws\PHPWS_Core::initModClass('rss', 'RSS.php');

if (!isset($_REQUEST['module'])) {
    \Layout::addStyle('rss');
    RSS::showFeeds();
}
