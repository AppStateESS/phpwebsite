<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::requireConfig('access');

class Access_Shortcut {
    var $id       = 0;
    var $keyword  = null;
    var $url      = null;
    var $active   = 1;
    var $_error   = null;

    function Access_Shortcut($id=0)
    {
        if ($id == 0) {
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    function init()
    {
        $db = new PHPWS_DB('access_shortcuts');
        $result = $db->loadObject($this);
        if (PEAR::isError($result)) {
            $this->_error = $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
        return $result;
    }

    function loadGet()
    {
        $url = explode(':', $this->url);
        $_REQUEST['module'] = $_GET['module'] = array_shift($url);
        if (!empty($url)) {
            $count = 1;
            foreach ($url as $var) {
                $_GET['var' . $count] = $var;
                $count++;
            }
        }
    }

    function postShortcut()
    {
        if (!isset($_POST['keyword'])) {
            return PHPWS_Error::get(SHORTCUT_MISSING_KEYWORD, 'access', 'Shortcut::postShortcut');
        }

        if (!$this->id) {
            if (empty($_POST['key_id'])) {
                return PHPWS_Error::get(SHORTCUT_MISSING_KEY, 'access', 'Shortcut::postShortcut');
            } else {
                $key = new Key((int)$_POST['key_id']);
                $this->setUrl($key->module, $key->item_id);
            }
        }
        
        $result = $this->setKeyword($_POST['keyword']);
        if (PEAR::isError($result) || $result == FALSE) {
            return $result;
        }

        return TRUE;
    }

    function setUrl($module, $id)
    {
        $this->url = sprintf('%s:%s', $module, $id);
    }

    function getUrl()
    {
        return sprintf('<a href="%s">%s</a>', $this->keyword, $this->keyword);
    }

    function setKeyword($keyword)
    {
        $keyword = preg_replace('/[^\w\s\-]/', '', strtolower($keyword));
        $keyword = preg_replace('/\s/', '-', $keyword);

        if (empty($keyword)) {
            return PHPWS_Error::get(SHORTCUT_BAD_KEYWORD, 'access', 'Shortcut::setKeyword');
        }

        if (!$this->id) {
            $db = new PHPWS_DB('access_shortcuts');
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
        }
        $this->keyword = $keyword;

        return TRUE;
    }

    function rowTags()
    {
        $js['QUESTION'] = dgettext('access', 'Are you sure you want to delete this shortcut?');
        $js['ADDRESS']  = sprintf('index.php?module=access&amp;command=delete_shortcut&amp;shortcut_id=%s&amp;authkey=%s',
                                  $this->id,
                                  Current_User::getAuthKey());
        $js['LINK'] = dgettext('access', 'Delete');
        $tags[] = javascript('confirm', $js);

        $vars['command'] = 'edit_shortcut';
        $vars['sc_id'] = $this->id;
        $link = PHPWS_Text::linkAddress('access', $vars, true);
        $js_vars['address'] = $link;
        $js_vars['label'] = dgettext('access', 'Edit');
        $js_vars['height'] = '200';
        $js_link = javascript('open_window', $js_vars);

        $tags[] = $js_link;

        $template['URL'] = $this->getUrl();

        if ($this->active) {
            $template['ACTIVE'] = dgettext('access', 'Yes');
        } else {
            $template['ACTIVE'] = dgettext('access', 'No');
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

        $db = new PHPWS_DB('access_shortcuts');
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

    function delete()
    {
        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

}

?>