<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

class RSS_Channel {
    var $id              = 0;
    var $module          = NULL;
    var $title           = NULL;
    var $link            = NULL;
    var $description     = NULL;
    var $pub_date        = 0;
    var $last_build_date = 0;
    var $category        = NULL;
    var $ttl             = 0;
    var $image           = 0;
    var $text_input      = NULL;
    var $active          = 1;

    var $_feeds          = NULL;
    var $_error          = NULL;


    function RSS_Channel($id=NULL)
    {
        if (!$id) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = & new PHPWS_DB('rss_channel');
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
            $errors[] = _('You must enter a title.');
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

    function save()
    {
        $db = & new PHPWS_DB('rss_channel');
        return $db->saveObject($this);
    }

    function getActionLinks()
    {
        $vars['channel_id'] = $this->id;

        $vars['command'] = 'edit_channel';
        $links[] = PHPWS_Text::secureLink(_('Edit'), 'rss', $vars);

        return $links;
    }


    function loadFeeds()
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('active', 1);
        $db->addWhere('restricted', 0);
        $db->addOrder('create_date desc');
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

    function view()
    {
        $tpl = & new PHPWS_Template('rss');
        $tpl->setFile('rss20.tpl');
        
        $channel_data['TITLE'] = $this->title;
        $channel_data['ADDRESS'] = PHPWS_Core::getHomeHttp();
        $channel_data['DESCRIPTION'] = $this->description;
        $channel_data['LANGUAGE'] = 'en-us'; // change later
        //        $channel_data['LAST_BUILD_DATE'] = $this->last_build_date;

        foreach ($this->_feeds as $key) {
            $itemTpl['ITEM_TITLE']        = $key->title;
            $itemTpl['ITEM_GUID']         = PHPWS_Core::getHomeHttp() . $key->url;
            $itemTpl['ITEM_LINK']         = PHPWS_Core::getHomeHttp() . $key->url;
            $itemTpl['ITEM_DESCRIPTION']  = $key->summary;
            $itemTpl['ITEM_AUTHOR']       = $key->creator;
            $itemTpl['ITEM_PUBDATE']      = $key->getCreateDate('%a, %d %b %Y %T GMT');
            $itemTpl['ITEM_SOURCE']       = PHPWS_Core::getHomeHttp() . 'index.php?module=rss&mod_title=' . $this->module;
            $itemTpl['ITEM_SOURCE_TITLE'] = $this->title;

            $tpl->setCurrentBlock('items');
            $tpl->setData($itemTpl);
            $tpl->parseCurrentBlock();
        }

        $tpl->setData($channel_data);
        $content = $tpl->get();

        echo $content;
        exit();
    }

}

?>


