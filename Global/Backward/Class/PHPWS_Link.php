<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class PHPWS_Link extends \Tag\ModuleLink {

    private $rewrite;
    public $salted;

    /**
     * In the remake, label and module are required.
     * @param type $label
     * @param type $module
     * @param type $values
     * @param type $secure
     * @param type $salted
     */
    public function __construct($label, $module, $values = null, $secure = false, $salted = false)
    {
        parent::__construct($label, $module);
        if (is_array($values)) {
            $this->addVariables($values);
        }
        $this->setSecure($secure);
        $this->salted = (bool) $salted;
    }

    public function get()
    {
        return parent::__toString();
    }

    public function setLabel($label)
    {
        $this->setText($label);
    }

    public function setStyle($style)
    {
        $this->addStyle($style);
    }

    public function setClass($class_name)
    {
        $this->addClass($class_name);
    }

    public function setModule($module)
    {
        $this->setModulePath($module);
    }

    /**
     * Receives values to build GET string
     */
    public function addValues(Array $values)
    {
        $this->addVariables($values);
    }

    public function clearValues()
    {
        $this->values = null;
    }

    public function setNoFollow($no_follow = true)
    {
        if ($no_follow) {
            $this->addRel('nofollow');
        } else {
            $this->removeRel('nofollow');
        }
    }

    public function setSalted($salt = true)
    {
        $this->salted = (bool) $salt;
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

    public function setValue($key, $value)
    {
        if (is_null($value)) {
            $this->removeVariable($key);
        } else {
            $this->values[$key] = $value;
        }
    }

    public function removeValue($key)
    {
        $this->removeVariable($key);
    }

    public function isRewrite()
    {
        return $this->rewrite;
    }
/*
    public function buildHref()
    {
        if ($this->rewrite) {
            $assign = $segway = $sep = '/';
        } else {
            $assign = '=';
            $segway = '?';
            $sep = '&';
        }

        if ($this->getSecure()) {
            $this->variables['authkey'] = \User\Current::getAuthKey();
        }
        $variables = $this->getVariables();
        if (!empty($variables)) {
            foreach ($variables as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $ak => $av) {
                        $data[] = $k . '[' . $ak . ']' . $assign . $av;
                    }
                } else {
                    $data[] = "$k$assign$v";
                }
            }
            return $this->getModulePath() . $segway . implode($sep, $data);
        } else {
            return $this->getModulePath();
        }
    }
*/
    public function getAddress()
    {
        $separate = '';

        if (empty($this->module) && empty($this->values)) {
            if ($this->isRewrite()) {
                throw new \Exception('Link is missing a module or values');
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

            if ($this->module) {
                $link[] = '?';
                $vars[] = sprintf('module=%s', $this->module);
            }
        }

        if (is_array($this->values)) {
            if ($this->isRewrite()) {
                foreach ($this->values as $var_name => $value) {
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
                foreach ($this->values as $var_name => $value) {
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

    public function setRewrite($rewrite = true)
    {
        $this->rewrite = (bool) $rewrite;
    }

    public function setAnchor($anchor)
    {
        $this->anchor = preg_replace('/^#/', '', $anchor);
    }

    public function setOnClick($onclick)
    {
        $event = new \Event('onclick', $onclick);
        $this->addEvent($event);
    }

    public function convertAmp($con = true)
    {
        $con ? $this->useEntityAmpersand() : $this->useBaseAmpersand();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

}

?>