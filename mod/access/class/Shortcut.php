<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
PHPWS_Core::requireConfig('access');

class Access_Shortcut {

    public $id = 0;
    public $keyword = null;
    public $url = null;
    public $active = 1;
    public $_error = null;

    public function Access_Shortcut($id = 0)
    {
        if ($id == 0) {
            return;
        }

        $this->id = (int) $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('access_shortcuts');
        $result = $db->loadObject($this);
        if (PHPWS_Error::isError($result)) {
            $this->_error = $result;
            $this->id = 0;
        } elseif (!$result) {
            $this->id = 0;
        }
        return $result;
    }

    public function loadGet()
    {
        $url = explode(':', $this->url);
        $request = \Server::getCurrentRequest();
        $module = array_shift($url);
        $request->setVar('module', $module);
        $request->setModule($module);
        $_REQUEST['module'] = $_GET['module'] = $module;

        $url_count = count($url);

        if ($url_count == 1) {
            $request->setVar('id', $url[0]);
            $_REQUEST['id'] = $_GET['id'] = $url[0];
        } else {
            for ($i = 0; $i < $url_count; $i++) {
                $key = $url[$i];
                $i++;
                if (!isset($url[$i])) {
                    break;
                }
                $val = $url[$i];
                if (preg_match('/\[\]/', $key)) {
                    $key = preg_replace('/[\[\]]/', '', $key);
                    $_REQUEST[$key][] = $_GET[$key][] = $val;
                    $keyed_array[$key][] = $val;
                } else {
                    $_REQUEST[$key] = $_GET[$key] = $val;
                    $request->setVar($key, $val);
                }
            }
            if (!empty($keyed_array)) {
                foreach ($keyed_array as $key => $vals) {
                    $request->setVar($key, $vals);
                }
            }
        }
    }

    public function postShortcut()
    {
        $keyword = filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING);

        if ($keyword === false) {
            throw new \Exception('Access shortcut submission missing keyword', 1);
        }

        $key_id = filter_input(INPUT_POST, 'key_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$this->id) {
            if ($key_id === false) {
                throw new \Exception('Access shortcut missing a key id', 2);
            } else {
                $key = new \Key($key_id);
                $this->setUrl($key->module, $key->url);
            }
        }
        $keyword = $this->shortenKeyword($keyword);

        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('keyword', $keyword);
        $db->addWhere('id', $this->id, '!=');
        $result = $db->select();
        if ($result) {
            throw new Exception('Shortcut keyword already in use', 3);
        }

        $this->setKeyword($keyword, false);

        return TRUE;
    }

    public function shortenKeyword($keyword)
    {
        $keyword = preg_replace('/[^\w\s\-]/', '', strtolower(trim($keyword)));
        return preg_replace('/\s/', '-', $keyword);
    }

    public function setUrl($module, $url)
    {
        // mod_rewrite link
        $url = preg_replace('@^./@', '', $url);
        if (preg_match('@/@', $url)) {
            $aUrl = explode('/', $url);
            $this->url = implode(':', $aUrl);
        } else {
            $url = preg_replace('/index.php\??|module=/i', '', $url);
            $this->url = preg_replace('/&amp;|[&=]/', ':', $url);
        }
    }

    public function getUrl()
    {
        return sprintf('<a href="%s">%s</a>', $this->keyword, $this->keyword);
    }

    public function setKeyword($keyword, $shorten = true)
    {
        if ($shorten) {
            $keyword = $this->shortenKeyword($keyword);
        }

        if (empty($keyword)) {
            throw new \Exception('Bad keyword used in Access shortcut');
        }

        $this->keyword = substr($keyword, 0, 254);
        return TRUE;
    }

    public function rowTags()
    {
        $js['QUESTION'] = dgettext('access',
                'Are you sure you want to delete this shortcut?');
        $js['ADDRESS'] = sprintf('index.php?module=access&amp;command=delete_shortcut&amp;shortcut_id=%s&amp;authkey=%s',
                $this->id, Current_User::getAuthKey());
        $js['LINK'] = '<button class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i> ' . dgettext('access',
                        'Delete') . '</button>';
        $tags[] = javascript('confirm', $js);

        $tags[] = '<a class="btn btn-success btn-sm edit-shortcut" data-authkey="' . \Current_User::getAuthKey() .
                '" data-schid="' . $this->id . '"><i class="fa fa-edit"></i> ' . dgettext('access',
                        'Edit') . '</a>';

        $template['URL'] = $this->getUrl();

        if ($this->active) {
            $template['ACTIVE'] = dgettext('access', 'Yes');
        } else {
            $template['ACTIVE'] = dgettext('access', 'No');
        }

        $template['ACTION'] = implode(' ', $tags);
        $template['CHECKBOX'] = sprintf('<input type="checkbox" name="shortcut[]" value="%s" />',
                $this->id);

        return $template;
    }

    public function save()
    {
        if (empty($this->keyword)) {
            return PHPWS_Error::get(SHORTCUT_MISSING_KEYWORD, 'access',
                            'Shortcut::save');
        }

        if (empty($this->url)) {
            return PHPWS_Error::get(SHORTCUT_MISSING_URL, 'access',
                            'Shortcut::save');
        }

        $db = new PHPWS_DB('access_shortcuts');
        return $db->saveObject($this);
    }

    public function getRewrite($full = FALSE, $linkable = TRUE)
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

    public function delete()
    {
        $db = new PHPWS_DB('access_shortcuts');
        $db->addWhere('id', $this->id);
        return $db->delete();
    }

}

?>