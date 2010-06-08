<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$key = Core\Key::getCurrent();

if (empty($key) || $key->isDummy() || $key->restricted) {
    return;
}

Core\Core::initModClass('rss', 'RSS.php');
RSS::showIcon($key);

?>