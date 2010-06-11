<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function rss_unregister($module, &$content)
{
    $db = new PHPWS_DB('rssfeeds');
    $db->addWhere('module', $module);
    $result = $db->delete();
    if (PHPWS_Error::isError($result)) {
        PHPWS_Error::log($result);
        $content[] = dgettext('rss', 'An error occurred trying to unregister this module from RSSFeeds.');
        return FALSE;
    } else {
        $content[] = dgettext('rss', 'Module unregistered from RSSFeeds.');
        return TRUE;
    }
}

?>