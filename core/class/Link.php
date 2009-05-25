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
    public  $target      = null;
    public  $title       = null;
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
    public  $no_follow   = false;
    public  $salted      = false;

    public function __construct($label=null, $module=null, $values=null, $secure=false, $salted=false)
    {
        $this->setLabel($label);
        $this->module = $module;
        if (is_array($values)) {
            $this->addValues($values);
        }
        $this->secure = (bool)$secure;
        $this->salted = (bool)$salted;
    }

    public function get()
    {
        $this->loadAddress();
        if (!$this->address || !$this->label ) {
            return null;
        }

        $params = array('title', 'onclick', 'onmouseover', 'onmouseout',
                        'target', 'class_name', 'style', 'id', 'no_follow');

        foreach ($params as $pr) {
            if (!empty($this->$pr)) {
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

    public function setLabel($label)
    {
        if (is_string($label) || is_numeric($label)) {
            $this->label = $label;
        }
    }

    public function setTitle($title)
    {
        $this->title = strip_tags($title);
    }

    public function setId($id)
    {
        $this->id = strip_tags($id);
    }

    public function setSecure($secure=true)
    {
        $this->secure = (bool)$secure;
    }

    public function setStyle($style)
    {
        if (is_array($style)) {
            foreach  ($style as $key=>$var) {
                $newstyle[] = "$key : $var";
            }
            $this->setStyle(implode('; ', $newstyle));
            return;
        }
        $this->style = strip_tags($style);
    }

    public function setClass($class_name)
    {
        $this->class_name = strip_tags($class_name);
    }

    public function setModule($module)
    {
        $this->module = !empty($module) ? $module : null;
    }

    /**
     * Receives values to build GET string
     */
    public function addValues($values)
    {
        if (is_array($values)) {
            foreach($values as $key=>$val) {
                if (is_array($val)) {
                    foreach ($val as $skey=>$sval) {
                        $subindex = sprintf('%s[%s]', $key, $skey);
                        $this->values[$subindex] = $sval;
                    }
                } else {
                    $this->values[$key] = $val;
                }
            }
        }
    }

    public function clearValues()
    {
        $this->values = null;
    }

    public function setNoFollow($no_follow=true)
    {
        $this->no_follow = (bool)$no_follow;
    }

    public function setSalted($salt=true)
    {
        $this->salted = (bool)$salt;
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
            $this->target = null;
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

        case 'no_follow':
            return 'rel="nofollow"';
        }
    }


    public function loadAddress()
    {
        $this->address = $this->getAddress();
    }

    public function getAuthKey()
    {
        // if not secure, authkey irrelevant
        if (!$this->secure || !class_exists('Current_User')) {
            return null;
        }

        if ($this->salted) {
            // Have to make them strings because GET will change them on the
            // other side.
            return Current_User::getAuthKey(PHPWS_Text::saltArray($this->values));
        } else {
            $result = Current_User::getAuthKey();
            return $result;
        }
    }


    public function setValue($key, $value) {
        if (is_null($value)) {
            $this->removeValue($key);
        } else {
            $this->values[$key] = $value;
        }
    }

    public function removeValue($key)
    {
        unset($this->values[$key]);
    }

    public function isRewrite()
    {
        return MOD_REWRITE_ENABLED && $this->rewrite;
    }

    public function getAddress()
    {
        $separate = '';

        if (empty($this->module) && empty($this->values)) {
            if ($this->isRewrite()) {
                return null;
            } else {
                return 'index.php';
            }
        }

        if ($this->secure) {
            $authkey = $this->getAuthKey();
        } else {
            $authkey = null;
        }

        if ($this->full_url == true) {
            $link[] = PHPWS_HOME_HTTP;
        }

        if ($this->isRewrite()) {
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
            if ($this->isRewrite()) {
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
            if (!$this->isRewrite() && !$this->module) {
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

    public function setRewrite($rewrite=MOD_REWRITE_ENABLED)
    {
        $this->rewrite = (bool)$rewrite;
    }

    public function setAnchor($anchor)
    {
        $this->anchor = preg_replace('/^#/', '', $anchor);
    }

    public function setOnClick($onclick)
    {
        $this->onclick = $onclick;
    }

    public function convertAmp($con=true)
    {
        $this->convert_amp = (bool)$con;
    }
}
?>