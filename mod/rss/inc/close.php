<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$key = \core\Key::getCurrent();

if (empty($key) || $key->isDummy() || $key->restricted) {
    return;
}

core\Core::initModClass('rss', 'RSS.php');
RSS::showIcon($key);

?>