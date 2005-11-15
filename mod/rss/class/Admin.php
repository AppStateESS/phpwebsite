<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class RSS_Admin {

    function main()
    {
        if (!Current_User::allow('rss')) {
            Current_User::disallow();
        }

        $panel = & RSS_Admin::adminPanel();

        if (isset($_REQUEST['command'])) {
            $command = $_REQUEST['command'];
        } elseif (isset($_REQUEST['tab'])) {
            $command = $_REQUEST['tab'];
        } else {
            $command = $panel->getCurrentTab();
        }

        switch ($command) {
        case 'export':
            $tpl = RSS_Admin::admin();
            break;
        }

        $content = PHPWS_Template::process($tpl, 'rss', 'main.tpl');

        $panel->setContent($content);
        $content = $panel->display();
        Layout::add(PHPWS_ControlPanel::display($content));
    }


    function &adminPanel()
    {

        $opt['link'] = 'index.php?module=rss';

        $opt['title'] = _('Export'); 
        $tab['export'] = $opt;

        $opt['title'] = _('Import');
        $tab['import'] = $opt;

        $panel = & new PHPWS_Panel('rss_admin');
        $panel->quickSetTabs($tab);
        return $panel;
    }

    function admin()
    {
        PHPWS_Core::initModClass('rss', 'Feed.php');

        $final_tpl['TITLE'] = _('Administrate RSS Feeds');
        $db = & new PHPWS_DB('modules');
        $db->setDistinct(TRUE);
        $db->addWhere('title', 'phpws_key.module');
        $db->addWhere('phpws_key.url', null, 'is not');
        $db->addColumn('title');
        $db->addColumn('proper_name');
        $db->setIndexBy('title');
        $modules = $db->select('col');

        if (empty($modules)) {
            $tpl['CONTENT'] = _('No keys available for feeds. Come back after you have created some content.');
            return $tpl;
        }

        $db->reset();
        $db->setTable('rssfeeds');
        $rssfeeds = $db->getObjects('RSS_Feed');

        if (empty($rssfeeds)) {
            $rssfeeds = RSS_Admin::createFeeds(array_keys($modules));
        }

        if (empty($rssfeeds)) {
            $tpl['CONTENT'] = _('An error occurred trying to create or access your feeds. Please check your error logs.');
            return $tpl;
        }

        foreach ($rssfeeds as $feed) {
            $rowtpl['MODULE']         = $feed->module;
            $rowtpl['TIMES_ACCESSED'] = $feed->times_accessed;
            $rowtpl['ACTIONS']        = 'nothing';
            if ($feed->active) {
                $rowtpl['ACTIVE']     = _('Yes');
            } else {
                $rowtpl['ACTIVE']     = _('No');
            }
            $template['feed-list'][]  = $rowtpl;
        }

        $template['ACTIVE_LABEL']         = _('Active');
        $template['MODULE_LABEL']         = _('Module');
        $template['TIMES_ACCESSED_LABEL'] = _('Times Accessed');
        $template['ACTION_LABEL']         = _('Action');
        
        $content = PHPWS_Template::process($template, 'rss', 'rssfeeds.tpl');
        $final_tpl['CONTENT'] = $content;

        return $final_tpl;
    }

    function createFeeds($modules)
    {
        $feeds = NULL;
        foreach ($modules as $mod) {
            $feed = & new RSS_Feed;
            $feed->module = $mod;
            $result = $feed->save(TRUE);

            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
            } else {
                $feeds[] = $feed;
            }
        }

        return $feeds;
    }
}

?>