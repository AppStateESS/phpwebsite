<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class RSS {

    public function registerModule($module, &$content)
    {
        $reg_file = PHPWS_Core::getConfigFile($module, 'rss.php');

        if ($reg_file == FALSE) {
            PHPWS_Boost::addLog($module, dgettext('rss', 'No RSS file found.'));
            return FALSE;
        }

        PHPWS_Core::initModClass('rss', 'Channel.php');
        include $reg_file;

        $oChannel = new RSS_Channel;
        $oChannel->module = $module;

        if (!isset($channel) || !is_array($channel)) {
            $content[] = dgettext('rss', 'RSS file found but no channel information.');
            PHPWS_Boost::addLog($module, dgettext('rss', 'RSS file found but no channel information.'));
        }

        $oModule = new PHPWS_Module($module);

        if (!empty($channel['title'])) {
            $oChannel->title = strip_tags($channel['title']);
        } else {
            $oChannel->title = $oModule->proper_name;
        }

        if (!empty($channel['description'])) {
            $oChannel->description = strip_tags($channel['description']);
        }

        if (!empty($channel['link'])) {
            $oChannel->link = strip_tags($channel['link']);
        } else {
            $oChannel->link = PHPWS_Core::getHomeHttp();
        }

        $result = $oChannel->save();
        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            PHPWS_Boost::addLog($module, dgettext('rss', 'An error occurred registering to RSS module.'));
            $content[] = dgettext('rss', 'An error occurred registering to RSS module.');
            return NULL;
        } else {
            $content[] = sprintf(dgettext('rss', 'RSS registration to %s module successful.'), $oModule->proper_name);
            return TRUE;
        }
    }

    public function showFeeds()
    {
        PHPWS_Core::initModClass('rss', 'Feed.php');
        $db = new PHPWS_DB('rss_feeds');

        $db->addWhere('display', 1);
        $result = $db->getObjects('RSS_Feed');
        if (empty($result)) {
            return;
        }

        foreach ($result as $feed) {
            $listing[] = $feed->view();
        }

        Layout::add(implode('', $listing), 'rss', 'feeds');
    }

    public function viewChannel($module)
    {
        PHPWS_Core::initModClass('rss', 'Channel.php');
        $channel = new RSS_Channel;
        $db = new PHPWS_DB('rss_channel');
        $db->addWhere('module', $module);
        $db->loadObject($channel);

        if (empty($channel->id) || $channel->_error) {
            return NULL;
        }

        header('Content-type: text/xml');
        echo $channel->view();
        exit();
    }

    public function showIcon($key)
    {
        PHPWS_Core::initModClass('rss', 'Channel.php');
        $channel = new RSS_Channel;
        $db = new PHPWS_DB('rss_channel');
        $db->addWhere('module', $key->module);
        $db->loadObject($channel);

        if (empty($channel->id) || $channel->_error) {
            return FALSE;
        }

        Layout::addLink(sprintf('<link rel="alternate" type="application/rss+xml" title="%s" href="%s" />',
        $channel->title, $channel->getAddress(FALSE)));
    }

}

?>