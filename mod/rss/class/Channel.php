<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @modified Olivier Sannier
 * @version $Id$
 */

Core\Core::requireConfig('rss');

class RSS_Channel {
    public $id              = 0;
    public $module          = NULL;
    public $title           = NULL;
    //    public $link            = NULL;
    public $description     = NULL;
    public $pub_date        = 0;
    //    public $category        = NULL;
    public $ttl             = 0;
    public $image_id        = 0;
    //    public $text_input      = NULL;
    public $active          = 1;

    public $_last_build_date = 0;
    public $_feeds          = NULL;
    public $_error          = NULL;


    public function __construct($id=NULL)
    {
        $this->_last_build_date = gmstrftime('%a, %d %b %Y %R GMT', time());
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('rss_channel');
        $result = $db->loadObject($this);

        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
            return $result;
        }
    }

    public function post()
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

    public function getTitle($linkable=true)
    {
        if ($linkable) {
            $vars['mod_title'] = $this->module;
            return PHPWS_Text::moduleLink($this->title, 'rss', $vars);
        } else {
            return $this->title;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('rss_channel');
        return $db->saveObject($this);
    }

    public function getActionLinks()
    {
        $vars['channel_id'] = $this->id;
        $vars['command'] = 'edit_channel';
        $links[] = PHPWS_Text::secureLink(dgettext('rss', 'Edit'), 'rss', $vars);

        return $links;
    }

    public function getAddress($include_http=TRUE)
    {
        Core\Core::initCoreClass('Link.php');
        $link = new PHPWS_Link;
        $link->full_url = $include_http;
        $link->setRewrite();
        $link->setModule('rss');
        $link->addValues(array('mod_title'=>$this->module));
        return $link->getAddress();
    }

    public function loadFeeds()
    {
        $db = new PHPWS_DB('phpws_key');
        $db->addWhere('module', $this->module);
        $db->addWhere('active', 1);
        $db->addWhere('restricted', 0);
        $db->addWhere('show_after', time(), '<');
        $db->addWhere('hide_after', time(), '>');

        $db->addOrder('create_date desc');
        // rss limit is 15
        $db->setLimit('15');

        $result = $db->getObjects('Key');

        if (PHPWS_Error::isError($result)) {
            $this->_feeds = NULL;
            $this->_error = $result;
            return $result;
        } else {
            $this->_feeds = $result;
            return TRUE;
        }

    }

    /**
     * @author Olivier Sannier
     */
    function EncodeString($str)
    {
        // decode all UTF8 to avoid having it reencoded later on
        $str = utf8_decode($str);

        // decode all HTML entities, they are not supported by RSS readers. This might create accented characters
        $str = html_entity_decode($str, ENT_QUOTES);

        // restore the line breaks, ensuring they are escaped for XML
        $str = htmlspecialchars(nl2br($str));

        return $str;
    }

    /**
     * Returns a RSS feed. Cached result is returned if exists.
     */
    public function view()
    {
        $cache_key = $this->module . '_cache_key';
        $content = PHPWS_Cache::get($cache_key, RSS_CACHE_TIMEOUT);

        if (!empty($content)) {
            return $content;
        }

        if (empty($this->_feeds)) {
            $this->loadFeeds();
        }

        $home_http = Core\Core::getHomeHttp();
        $template['CHANNEL_TITLE']       = $this->EncodeString($this->title);
        $template['CHANNEL_ADDRESS']     = $this->getAddress();
        $template['HOME_ADDRESS']        = $home_http;
        $template['CHANNEL_DESCRIPTION'] = $this->EncodeString($this->description);
        $template['LANGUAGE']            = CURRENT_LANGUAGE; // change later
        $template['SEARCH_LINK'] = sprintf('%sindex.php?module=search&amp;mod_title=%s&amp;user=search',
        $home_http, $this->module);
        $template['SEARCH_DESCRIPTION'] = sprintf('Search in %s', $this->title);
        $template['SEARCH_NAME'] = 'search';

        $template['COPYRIGHT'] = PHPWS_Settings::get('rss', 'copyright');
        $template['WEBMASTER'] = PHPWS_Settings::get('rss', 'webmaster');
        $template['MANAGING_EDITOR'] = PHPWS_Settings::get('rss', 'editor');

        $template['LAST_BUILD_DATE'] = $this->_last_build_date;

        $timezone = strftime('%z');
        $timezone = substr($timezone, 0, 3) . ':' . substr($timezone, 3, 2);
        if ($this->_feeds) {
            foreach ($this->_feeds as $key) {
                $itemTpl = NULL;
                $url = preg_replace('/^\.\//', '', $key->url);
                $url = $home_http . preg_replace('/&(?!amp;)/', '&amp;', $url);
                $itemTpl['ITEM_LINK']         = $url;
                $itemTpl['ITEM_TITLE']        = $this->EncodeString($key->title);
                $itemTpl['ITEM_GUID']         = $url;
                $itemTpl['ITEM_LINK']         = $url;
                $itemTpl['ITEM_SOURCE']       = sprintf('%sindex.php?module=rss&amp;mod_title=%s', $home_http, $this->module);

                $itemTpl['ITEM_DESCRIPTION']  = strip_tags(trim($this->EncodeString($key->summary)));
                $itemTpl['ITEM_AUTHOR']       = $key->creator;
                $itemTpl['ITEM_PUBDATE']      = $key->getCreateDate('%Y-%m-%dT%H:%M');

                $itemTpl['ITEM_DC_DATE']      = $key->getCreateDate('%Y-%m-%dT%H:%M:%S') . $timezone;
                $itemTpl['ITEM_DC_TYPE']      = 'Text'; //pull from db later
                $itemTpl['ITEM_DC_CREATOR']   = $key->creator;

                $itemTpl['ITEM_SOURCE_TITLE'] = $this->EncodeString($this->title);

                $template['item-listing'][] = $itemTpl;
            }
        }

        if (PHPWS_Settings::get('rss', 'rssfeed') == 2) {
            $tpl_file = 'rss20.tpl';
        } else {
            $tpl_file = 'rss10.tpl';
        }
        $content = PHPWS_Template::process($template, 'rss', $tpl_file);
        $content = utf8_encode($content);
        PHPWS_Cache::save($cache_key, $content);
        return $content;
    }

}

?>