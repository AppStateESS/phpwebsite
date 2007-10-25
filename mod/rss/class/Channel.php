<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::requireConfig('rss');

class RSS_Channel {
    var $id              = 0;
    var $module          = NULL;
    var $title           = NULL;
    //    var $link            = NULL;
    var $description     = NULL;
    var $pub_date        = 0;
    //    var $category        = NULL;
    var $ttl             = 0;
    var $image_id        = 0;
    //    var $text_input      = NULL;
    var $active          = 1;

    var $_last_build_date = 0;
    var $_feeds          = NULL;
    var $_error          = NULL;


    function RSS_Channel($id=NULL)
    {
        $this->_last_build_date = gmstrftime('%a, %d %b %Y %R GMT', mktime());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('rss_channel');
        $result = $db->loadObject($this);

        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }
    }

    function post()
    {
        if (isset($_POST['title'])) {
            $this->title = $_POST['title'];
        } else {
            $errors[] = dgettext('rss', 'You must enter a title.');
        }

        if (isset($_POST['description'])) {
            $this->description = strip_tags($_POST['description']);
        } else {
            $this->description = NULL;
        }
        if (isset($errors)) {
            return $errors;
        } else {
            return TRUE;
        }
    }

    function getTitle($linkable=true)
    {
        if ($linkable) {
            $vars['mod_title'] = $this->module;
            return PHPWS_Text::moduleLink($this->title, 'rss', $vars);
        } else {
            return $this->title;
        }
    }

    function save()
    {
        $db = new PHPWS_DB('rss_channel');
        return $db->saveObject($this);
    }

    function getActionLinks()
    {
        $vars['channel_id'] = $this->id;
        $vars['command'] = 'edit_channel';
        $links[] = PHPWS_Text::secureLink(dgettext('rss', 'Edit'), 'rss', $vars);

        return $links;
    }

    function getAddress($include_http=TRUE)
    {
        if ($include_http) {
            return PHPWS_Core::getHomeHttp() . 'index.php?module=rss&amp;mod_title=' . $this->module;
        } else {
            return 'index.php?module=rss&amp;mod_title=' . $this->module;
        }
    }

    function loadFeeds()
    {
        $db = new PHPWS_DB('phpws_key');
        $db->addWhere('module', $this->module);
        $db->addWhere('restricted', 0);

        $db->addOrder('create_date desc');
        // rss limit is 15
        $db->setLimit('15');

        $result = $db->getObjects('Key');

        if (PEAR::isError($result)) {
            $this->_feeds = NULL;
            $this->_error = $result;
            return $result;
        } else {
            $this->_feeds = $result;
            return TRUE;
        }

    }

    /**
     * Returns a RSS feed. Cached result is returned if exists.
     */
    function view()
    {
        $cache_key = $this->module . '_cache_key';
        $content = PHPWS_Cache::get($cache_key);

        if (!empty($content)) {
            return $content;
        }

        $home_http = PHPWS_Core::getHomeHttp();
        $template['CHANNEL_TITLE']       = $this->title;
        $template['CHANNEL_ADDRESS']     = $this->getAddress();
        $template['HOME_ADDRESS']        = $home_http;
        $template['CHANNEL_DESCRIPTION'] = $this->description;
        $template['LANGUAGE']            = CURRENT_LANGUAGE; // change later
        $template['SEARCH_LINK'] = sprintf('%sindex.php?module=search&amp;mod_title=%s&amp;user=search',
                                           $home_http, $this->module);
        $template['SEARCH_DESCRIPTION'] = sprintf('Search in %s', $this->title);
        $template['SEARCH_NAME'] = 'search';

        $template['COPYRIGHT'] = PHPWS_Settings::get('rss', 'copyright');
        $template['WEBMASTER'] = PHPWS_Settings::get('rss', 'webmaster');
        $template['MANAGING_EDITOR'] = PHPWS_Settings::get('rss', 'editor');

        $template['LAST_BUILD_DATE'] = $this->_last_build_date;

        if ($this->_feeds) {
            foreach ($this->_feeds as $key) {
                $itemTpl = NULL;
                $url = preg_replace('/^\.\//', '', $key->url);
                $itemTpl['ITEM_LINK']         = $home_http .  preg_replace('/&(?!amp;)/', '&amp;', $url);
                $itemTpl['ITEM_TITLE']        = $key->title;
                $itemTpl['ITEM_GUID']         = $home_http . preg_replace('/&(?!amp;)/', '&amp;', $key->url);
                $itemTpl['ITEM_LINK']         = $home_http . preg_replace('/&(?!amp;)/', '&amp;', $key->url);
                $itemTpl['ITEM_SOURCE']       = sprintf('%sindex.php?module=rss&amp;mod_title=%s', $home_http, $this->module);

                $itemTpl['ITEM_DESCRIPTION']  = strip_tags(trim($key->summary));
                $itemTpl['ITEM_AUTHOR']       = $key->creator;
                $itemTpl['ITEM_PUBDATE']      = $key->getCreateDate('%a, %d %b %Y %T GMT');

                $itemTpl['ITEM_DC_DATE']      = $key->getCreateDate('%Y-%m-%dT%H:%M');
                $itemTpl['ITEM_DC_TYPE']      = 'Text'; //pull from db later
                $itemTpl['ITEM_DC_CREATOR']   = $key->creator;

                $itemTpl['ITEM_SOURCE_TITLE'] = $this->title;

                $template['item-listing'][] = $itemTpl;
            }
        }

        if (PHPWS_Settings::get('rss', 'rssfeed') == 2) {
            $tpl_file = 'rss20.tpl';
        } else {
            $tpl_file = 'rss10.tpl';
        }

        $content = PHPWS_Template::process($template, 'rss', $tpl_file);
        PHPWS_Cache::save($cache_key, $content, RSS_CACHE_TIMEOUT);
        return $content;
    }

}

?>