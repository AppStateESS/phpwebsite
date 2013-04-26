<?php

namespace Tag;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class ModuleLink extends Link {

    private $secure;
    private $mod_vars;
    private $module_path;

    public function __construct($label, $module_path, Array $variables = null, $secure = false)
    {
        parent::__construct($label);
        //$this->addIgnoreVariables('secure', 'variables', 'module_path');
        $this->setModulePath($module_path);

        if (!empty($variables)) {
            $this->addVariables($variables);
        }
        $this->setSecure($secure);
    }

    public function setModulePath($module_path)
    {
        $this->module_path = $module_path;
    }

    public function getModulePath()
    {
        return $this->module_path;
    }

    public function setSecure($secure)
    {
        $this->secure = (bool) $secure;
    }

    public function getSecure()
    {
        return $this->secure;
    }

    public function addVariables(Array $variables)
    {
        if (empty($this->mod_vars)) {
            $this->mod_vars = $variables;
        } else {
            foreach ($variables as $k => $v) {
                $this->mod_vars[$k] = $v;
            }
        }
    }

    public function removeVariable($key)
    {
        unset($this->mod_vars[$key]);
    }

    public function getVariables()
    {
        return $this->mod_vars;
    }

    public function buildHref()
    {
        if ($this->secure) {
            $this->mod_vars['authkey'] = \User\Current::getAuthKey();
        }
        if (!empty($this->mod_vars)) {
            foreach ($this->mod_vars as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $ak => $av) {
                        $data[] = $k . '[' . $ak . ']=' . $av;
                    }
                } else {
                    $data[] = "$k=$v";
                }
            }
            // Ampersand will be changed to &amp; in Tag class
            return $this->module_path . '?' . implode('&', $data);
        } else {
            return $this->module_path;
        }
    }

    public function __toString()
    {
        $href = $this->buildHref();
        $this->setHref($href);
        return parent::__toString();
    }

}

?>
