<?php

PHPWS_Core::requireConfig('access');

class Access_Shortcut {
    var $id       = 0;
    var $keyword  = NULL;
    var $url      = NULL;
    var $active   = 0;
    var $_error   = NULL;

    function Access_Shortcut($id=0)
    {
        if ($id == 0) {
            return;
        }

        $this->id = (int)$id;
    }

    function init()
    {
        $db = & new PHPWS_DB('access_shortcuts');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
        }
        return $result;
    }

    function postShortcut()
    {
        if (!isset($_POST['keyword'])) {
            return PHPWS_Error::get(SHORTCUT_MISSING_KEYWORD, 'access', 'Shortcut::postShortcut');
        }
        if (!isset($_POST['url'])) {
            return PHPWS_Error::get(SHORTCUT_MISSING_URL, 'access', 'Shortcut::postShortcut');
        }
        
        $result = $this->setKeyword($_POST['keyword']);
        if (PEAR::isError($result) || $result == FALSE) {
            return $result;
        }

        $this->setUrl(urldecode($_POST['url']));
        return TRUE;
    }

    function setUrl($url)
    {
        $this->url = strip_tags($url);
    }

    function setKeyword($keyword)
    {
        $keyword = str_replace(' ', '_', $keyword);

        if (preg_match('/\W/', $keyword)) {
            return PHPWS_Error::get(SHORTCUT_BAD_KEYWORD, 'access', 'Shortcut::setKeyword');
        }

        $db = & new PHPWS_DB('access_shortcuts');
        $db->addWhere('keyword', $keyword);
        $result = $db->select();
        if (!empty($result)) {
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                return FALSE;
            } else {
                return PHPWS_Error::get(SHORTCUT_WORD_IN_USE, 'access', 'Shortcut::setKeyword');
            }
        }

        $this->keyword = $keyword;

        return TRUE;
    }

    function rowTags()
    {
        $js['QUESTION'] = _('Are you sure you want to delete this shortcut?');
        $js['ADDRESS']  = sprintf('index.php?module=access&amp;command=delete_shortcut&amp;shortcut_id=%s&amp;authkey=%s',
                                  $this->id,
                                  Current_User::getAuthKey());
        $js['LINK'] = _('Delete');
        $tags[] = javascript('confirm', $js);

        $link_vars['shortcut_id'] = $this->id;
        $link_vars['command']     = 'edit_shortcut';

        $tags[] = PHPWS_Text::secureLink(_('Edit'), 'access', $link_vars);

        $template['URL'] = sprintf('<a href="%s">%s</a>', $this->url, $this->url);
        if ($this->active) {
            $template['ACTIVE'] = _('Yes');
        } else {
            $template['ACTIVE'] = _('No');
        }

        $template['ACTION'] = implode(' | ', $tags);
        $template['CHECKBOX'] = sprintf('<input type="checkbox" name="shortcut[]" value="%s" />', $this->id);

        return $template;
    }

    function save()
    {
        if (empty($this->keyword)) {
            return PHPWS_Error::get(SHORTCUT_MISSING_KEYWORD, 'access', 'Shortcut::save');
        }

        if (empty($this->url)) {
            return PHPWS_Error::get(SHORTCUT_MISSING_URL, 'access', 'Shortcut::save');
        }

        if (PHPWS_Settings::get('access', 'allow_file_update')) {
            $this->active = 1;
        }

        $db = & new PHPWS_DB('access_shortcuts');
        return $db->saveObject($this);
    }

    function getRewrite($full=FALSE, $linkable=TRUE)
    {
        if ($full) {
            $address[] = PHPWS_Core::getHomeHttp();
        }
        $address[] = $this->keyword;

        $url = implode('', $address);
        if ($linkable) {
            return sprintf('<a href="%s">%s</a>', $url, $url);
        } else {
            return $url;
        }
    }

    function getHtaccess()
    {
        return sprintf('RewriteRule ^%s$ %s [L]', $this->keyword, $this->url);
    }
    
    function delete()
    {
        $db = & new PHPWS_DB('access_shortcuts');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

}

?>