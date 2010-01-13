<?php
/**
 * Controls templates
 *
 * An extention of Pear's HTML_Template_IT class.
 * Fills in information specific to phpWebSite
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

require_once 'HTML/Template/Sigma.php';
require_once 'core/conf/template.php';

if (!defined('CACHE_TPL_LOCALLY')) {
    define('CACHE_TPL_LOCALLY', false);
}

class PHPWS_Template extends HTML_Template_Sigma {
    public $module           = NULL;
    public $error            = NULL;
    public $lastTemplatefile = NULL;
    public $ignore_cache     = false;

    public function __construct($module=NULL, $file=NULL)
    {
        $this->HTML_Template_Sigma();
        if (isset($module))
        $this->setModule($module);

        if (isset($file)){
            $result = $this->setFile($file);

            if (PEAR::isError($result)){
                PHPWS_Error::log($result);
                $this->error = $result;
            }
        }
    }

    /**
     * Grabs the THEME template directory unless layout is not
     * operational
     */
    public function getTplDir($module)
    {
        if ($module == 'core') {
            return PHPWS_SOURCE_DIR . 'core/templates/';
        }

        if (!class_exists('Layout')) {
            return PHPWS_SOURCE_DIR . "mod/$module/templates/";
        }

        $theme = Layout::getThemeDir();
        return sprintf('%stemplates/%s/', $theme, $module);
    }

    public function setIgnoreCache($ignore=true)
    {
        $this->ignore_cache = (bool)$ignore;
    }

    public function setCache()
    {
        if ($this->ignore_cache || !PHPWS_Template::allowSigmaCache()) {
            return;
        }

        static $root_dir = null;

        if (empty($root_dir) && defined('CACHE_LIFETIME')) {
            if (CACHE_TPL_LOCALLY) {
                $root_dir = 'templates/cache/';
            } elseif(defined('CACHE_DIRECTORY')) {
                $root_dir = CACHE_DIRECTORY;
            } else {
                $root_dir = 'unusable';
                return;
            }

            if (!is_writable($root_dir)) {
                $root_dir = 'unusable';
                return;
            }
        } elseif ($root_dir == 'unusable') {
            return;
        }

        $this->setCacheRoot($root_dir);
    }

    public function allowSigmaCache()
    {
        if (defined('ALLOW_SIGMA_CACHE')) {
            return ALLOW_SIGMA_CACHE;
        } else {
            return false;
        }
    }

    /**
     * returns the expected template directory based on settings in
     * the template.php config file and the existence of files
     */
    public function getTemplateDirectory($module, $directory=NULL)
    {
        $theme_dir  = PHPWS_Template::getTplDir($module) . $directory;
        $module_dir = sprintf('%smod/%s/templates/%s', PHPWS_SOURCE_DIR, $module, $directory);

        if (FORCE_THEME_TEMPLATES && is_dir($theme_dir)) {
            return $theme_dir;
        } elseif (is_dir($module_dir)) {
            return $module_dir;
        } else {
            return NULL;
        }
    }

    /**
     * Lists the template files in a directory.
     * Can be called statically.
     */
    public function listTemplates($module, $directory=NULL)
    {
        $tpl_dir = PHPWS_Template::getTemplateDirectory($module, $directory);

        if (!$result = scandir($tpl_dir)) {
            return NULL;
        }

        foreach ($result as $file) {
            if (!preg_match('/(^\.)|(~$)/iU', $file) && preg_match('/\.tpl$/i', $file)) {
                $file_list[$file] = $file;
            }
        }

        return $file_list;
    }

    /**
     * Sets the template file specified. Function decides which file to use based
     * on template.php settings and file availability
     */
    public function setFile($file, $strict=false)
    {
        $module = $this->getModule();
        $this->setCache();
        if ($strict == true) {
            $result = $this->loadTemplateFile($file);
            $used_tpl = &$file;
        } else {
            $theme_tpl    = PHPWS_Template::getTplDir($module) . $file;
            $mod_tpl      = PHPWS_SOURCE_DIR . "mod/$module/templates/$file";
            $template_tpl = "templates/$module/$file";

            if (PEAR::isError($theme_tpl)) {
                return $theme_tpl;
            }

            if ( FORCE_THEME_TEMPLATES || (!FORCE_MOD_TEMPLATES && is_file($theme_tpl)) ) {
                $result = $this->loadTemplateFile($theme_tpl);
                $used_tpl = &$theme_tpl;
            } elseif ( FORCE_MOD_TEMPLATES || !is_file($template_tpl) ) {
                $result = $this->loadTemplateFile($mod_tpl);
                $used_tpl = &$mod_tpl;
            } else {
                $result = $this->loadTemplateFile($template_tpl);
                $used_tpl = &$template_tpl;
            }
        }

        if ($result) {
            $this->lastTemplatefile = $used_tpl;
            return $result;
        } else {
            return $this->err[0];
        }
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setData($data)
    {
        if (PEAR::isError($data)){
            PHPWS_Error::log($data);
            return NULL;
        }

        if (!is_array($data) || empty($data)) {
            return NULL;
        }

        foreach($data as $tag=>$content) {
            if ( (is_string($tag) || is_numeric($tag)) &&
            (is_string($content) || is_numeric($content)) ) {
                $this->setVariable($tag, $content);
            }
        }
    }


    public function process($template, $module, $file, $strict=false, $ignore_cache=false)
    {
        if (!is_array($template)) {
            return PHPWS_Error::log(PHPWS_VAR_TYPE, 'core',
                                    'PHPWS_Template::process',
                                    'template=' . gettype($template));
            return NULL;
        }

        if (PEAR::isError($template)){
            PHPWS_Error::log($template);
            return NULL;
        }

        if ($strict) {
            $tpl = new PHPWS_Template;
            $tpl->setFile($file, true);
        } else {
            $tpl = new PHPWS_Template($module, $file);
        }

        $tpl->ignore_cache = (bool)$ignore_cache;

        if (PEAR::isError($tpl->error)) {
            PHPWS_Error::log($tpl->error);
            return _('Template error.');
        }

        foreach ($template as $key => $value) {
            if (!is_array($value) || !isset($tpl->_blocks[$key])) {
                continue;
            }

            foreach ($value as $content) {
                $tpl->setCurrentBlock($key);
                $tpl->setData($content);
                $tpl->parseCurrentBlock();
            }
            unset($template[$key]);
        }

        $tpl->setData($template);

        $result = $tpl->get();

        if (PEAR::isError($result)) {
            return $result;
        }

        if (LABEL_TEMPLATES == true){
            $start = "\n<!-- START TPL: " . $tpl->lastTemplatefile . " -->\n";
            $end = "\n<!-- END TPL: " . $tpl->lastTemplatefile . " -->\n";
        } else {
            $start = $end = NULL;
        }

        if (!isset($result) && RETURN_BLANK_TEMPLATES) {
            return $start . $tpl->lastTemplatefile . $end;
        } else {
            return $start . $result . $end;
        }
    }

    public function processTemplate($template, $module, $file, $defaultTpl=true)
    {
        if ($defaultTpl)
        return PHPWS_Template::process($template, $module, $file);
        else {
            $tpl = new PHPWS_Template($module);
            $tpl->setFile($file, true);
            $tpl->setData($template);
            return $tpl->get();
        }
    }
}

?>
