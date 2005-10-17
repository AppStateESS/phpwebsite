<?php

/**
 * Small class to help modules plug in features from other modules
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_REQUEST['module'])) {
    $GLOBALS['PHPWS_Key'] = Key::getHomeKey();
} else {
    $GLOBALS['PHPWS_Key'] = NULL;
}

class Key {
    var $id           = 0;
    var $module       = NULL;
    var $item_name    = NULL;
    var $item_id      = NULL;
    var $title        = NULL;
    var $url          = NULL;
    var $active       = 1;
    var $restricted   = 0;
    var $_error       = NULL;
  
    function Key($id=NULL)
    {
        if ($id == 0) {
            $this->module = $this->item_name = 'home';
            $this->item_id = 0;
            $this->setTitle(_('Home'));
            $this->setUrl('index.php');
            return;
        }

        if (empty($id)) {
            return NULL;
        }

        $this->id = (int)$id;
        $result = $this->init();
        if (PEAR::isError($result)) {
            $this->error = $result;
        }
    }

    function isKey($key)
    {
        if (is_object($key) && strtolower(get_class($key)) == 'key') {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    function init()
    {
        $db = & new PHPWS_DB('phpws_key');
        return $db->loadObject($this);
    }

    function save()
    {
        if (empty($this->module) ||
            empty($this->item_id)
            ) {
            return false;
        }

        if (empty($this->item_name)) {
            $this->item_name = $this->module;
        }

        $db = & new PHPWS_DB('phpws_key');
        $result = $db->saveObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            return $result;
        }
        return TRUE;
    }

    function setModule($module)
    {
        $this->module = $module;
    }

    function setItemName($item_name)
    {
        $this->item_name = $item_name;
    }

    function setItemId($item_id)
    {
        $this->item_id = $item_id;
    }

    function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    function setUrl($url, $local=TRUE)
    {
        if ($local) {
            PHPWS_Text::makeRelative($url);
        }
        $this->url = str_replace('&amp;', '&', trim($url));
        $this->url = preg_replace('/&?authkey=\w{32}/', '', $this->url);
    }

    /*
    function getUrl($basic=FALSE)
    {
        if ($basic && $this->rewrite) {
            $url = preg_replace('/(\w+)(\d+)(_\d+)/Ui', '', $this->url);
        } else {
            return $this->url;
        }
    }
    */

    function isActive()
    {
        return (bool)$this->active;
    }

    function isRestricted()
    {
        return (bool)$this->restricted;
    }

    function isHomeKey()
    {
        return ($this->module == 'home' ? TRUE : FALSE);
    }

    function &getHomeKey()
    {
        $key = & new Key(0);
        return $key;
    }

    function flag()
    {
        $GLOBALS['Current_Flag'] = &$this;
    }

    function drop($key_id)
    {
        $db = & new PHPWS_DB('phpws_key');
        $db->addWhere('id', (int)$key_id);
        return $db->delete();
    }

    function getCurrent()
    {
        if (!isset($GLOBALS['Current_Flag'])) {
            if (isset($_REQUEST['module'])) {
                return NULL;
            } else {
                return Key::getHomeKey();
            }
        } else {
            return $GLOBALS['Current_Flag'];
        }
    }
}

?>