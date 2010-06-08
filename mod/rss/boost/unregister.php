<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function rss_unregister($module, &$content)
{
    $db = new Core\DB('rssfeeds');
    $db->addWhere('module', $module);
    $result = $db->delete();
    if (Core\Error::isError($result)) {
        Core\Error::log($result);
        $content[] = dgettext('rss', 'An error occurred trying to unregister this module from RSSFeeds.');
        return FALSE;
    } else {
        $content[] = dgettext('rss', 'Module unregistered from RSSFeeds.');
        return TRUE;
    }
}

?>