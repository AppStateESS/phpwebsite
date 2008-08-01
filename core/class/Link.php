<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class PHPWS_Link {
    public  $label       = null;
    public  $module      = null;
    public  $address     = null;
    public  $values      = null;
    private $target      = null;
    private $title       = null;
    public  $class_name  = null;
    public  $style       = null;
    public  $id          = null;
    public  $onmouseover = null;
    public  $onmouseout  = null;
    public  $onclick     = null;
    public  $anchor      = null;

    public  $rewrite     = false;
    public  $secure      = false;
    public  $full_url    = false;
    public  $convert_amp = true;

    public function __construct($label=null, $module=null, $values=null)
    {
        $this->label = $label;
        $this->module = $module;
        if (is_array($values)) {
            $this->values = $values;
        }
    }

    public function get()
    {
        $this->loadAddress();
        if (!$this->address || !$this->label ) {
            return null;
        }

        $params = array('title', 'onclick', 'onmouseover', 'onmouseout',
                        'target', 'class_name', 'style', 'id');

        foreach ($params as $pr) {
            if (isset($this->$pr)) {
                $url_params[] = $this->getParameter($pr);
            }
        }

        if (isset($url_params)) {
            $param_list = ' ' . implode(' ', $url_params);
        } else {
            $param_list = null;
        }

        return sprintf('<a href="%s"%s>%s</a>',
                       $this->address, $param_list, $this->label);
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setId($id)
    {
        $this->id = strip_tags($id);
    }

    public function setClass($class_name)
    {
        $this->class_name = $class_name;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setValues($values)
    {
        if (is_array($values)) {
            $this->values = $values;
        }
    }

    public function setTarget($target)
    {
        $target = strtolower($target);
        switch ($target) {
        case 'index':
        case 'blank':
            $target = '_blank';
            break;
        case '_blank':
        case '_parent':
        case '_self':
        case '_top':
            break;

        default:
            return;
        }
        $this->target = $target;
    }

    public function getParameter($parameter)
    {
        switch ($parameter) {
        case 'title':
            return sprintf('title="%s"', $this->title);

        case 'onclick':
            return sprintf('onclick="%s"', $this->onclick);

        case 'onmouseover':
            return sprintf('onmouseover="%s"', $this->onmouseover);

        case 'onmouseout':
            return sprintf('onmouseout="%s"', $this->onmouseout);

        case 'target':
            return sprintf('target="%s"', $this->target);

        case 'class_name':
            return sprintf('class="%s"', $this->class_name);

        case 'style':
            return sprintf('style="%s"', $this->style);

        case 'id':
            return sprintf('id="%s"', $this->id);

        }
    }


    public function loadAddress()
    {
        $this->address = $this->getAddress();
    }

    public function getAuthKey()
    {
        static $authkey = null;

        // if not secure, authkey irrelevant
        if (!$this->secure || !class_exists('Current_User') || !Current_User::isLogged() ) {
            return null;
        }

        if (!$authkey) {
            $authkey = Current_User::getAuthKey();
        }

        return $authkey;
    }

    public function addValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function removeValue($key)
    {
        unset($this->values[$key]);
    }

    public function getAddress()
    {
        $separate = '';

        if (empty($this->module) && empty($this->values)) {
            if ($this->rewrite) {
                return null;
            } else {
                return 'index.php';
            }
        }

        $authkey = $this->getAuthKey();

        if ($this->full_url == true) {
            $link[] = PHPWS_HOME_HTTP;
        }

        if (MOD_REWRITE_ENABLED && $this->rewrite) {
            if ($this->module) {
                $link[] = $this->module . '/';
            }
        } else {
            $link[] = 'index.php';

            if ($this->module){
                $link[] = '?';
                $vars[] = sprintf('module=%s', $this->module);
            }
        }

        if (is_array($this->values)) {
            if (MOD_REWRITE_ENABLED && $this->rewrite) {
                foreach ($this->values as $var_name=>$value) {
                    if ($var_name == '#') {
                        $this->anchor = $value;
                        continue;
                    }
                    if (!empty($value)) {
                        $vars[] = $var_name . '/' . $value;
                    }
                }
                if ($authkey) {
                    $vars[] = 'authkey/' . $authkey;
                }

                $separate = '/';
            } else {
                if (!$this->module) {
                    $link[] = '?';
                }

                foreach ($this->values as $var_name=>$value) {
                    if ($var_name == '#') {
                        $this->anchor = $value;
                        continue;
                    }

                    $vars[] = $var_name . '=' . $value;
                }

                if ($authkey) {
                    $vars[] = 'authkey=' . $authkey;
                }

                if ($this->convert_amp) {
                    $separate = '&amp;';
                } else {
                    $separate = '&';
                }
            }
        }


        if (isset($vars)) {
            if (!MOD_REWRITE_ENABLED && $this->rewrite && !$this->module) {
                $link[] = '?';
            }
            $link[] = implode($separate, $vars);
        }

        $final_link = implode('', $link);
        if ($this->anchor) {
            return $final_link . '#' . $this->anchor;
        } else {
            return $final_link;
        }

    }
}

?>