<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class RSS {

    function registerModule($module, $content)
    {
        $reg_file = PHPWS_Core::getConfigFile($module, 'rss.php');
        if ($reg_file == FALSE){
            $content[] = _('No RSS file found.');
            PHPWS_Boost::addLog($module, _('No RSS file found.'));
            return TRUE;
        }

        PHPWS_Core::initModClass('rss', 'Channel.php');
        include $reg_file;

        $oChannel = & new RSS_Channel;
        $oChannel->module = $module;

        if (!isset($channel) || !is_array($channel)) {
            $content[] = _('RSS file found but no channel information.');
            PHPWS_Boost::addLog($module, _('RSS file found but no channel information.'));
        }

        $oModule = & new PHPWS_Module($module);

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
            PHPWS_Boost::addLog($module, _('An error occurred registering to RSS module.'));
            $content[] = _('An error occurred registering to RSS module.');
            return NULL;
        } else {
            $content[] = _('RSS registration successful.');
            return TRUE;
        }
    }

    function viewChannel($module)
    {
        PHPWS_Core::initModClass('rss', 'Channel.php');
        $channel = & new RSS_Channel;
        $db = & new PHPWS_DB('rss_channel');
        $db->addWhere('module', $module);
        $db->loadObject($channel);

        if (empty($channel->id) || $channel->_error) {
            return NULL;
        }

        $channel->loadFeeds();
        echo $channel->view();
        exit();
    }

}

?>