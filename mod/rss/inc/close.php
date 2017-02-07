<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$key = \Canopy\Key::getCurrent();

if (empty($key) || $key->isDummy() || $key->restricted) {
    return;
}

\phpws\PHPWS_Core::initModClass('rss', 'RSS.php');
RSS::showIcon($key);
