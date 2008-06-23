<?php
require_once 'HTML/Template/Sigma.php';
require_once PHPWS_HOME_DIR . 'config/core/template.php';

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

if (!defined('CACHE_TPL_LOCALLY')) {
    define('CACHE_TPL_LOCALLY', false);
 }

class PHPWS_Template extends HTML_Template_Sigma {
    var $module           = NULL;
    var $error            = NULL;
    var $lastTemplatefile = NULL;

    function PHPWS_Template($module=NULL, $file=NULL)
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
    function getTplDir($module)
    {
        if ($module == 'core') {
            return PHPWS_HOME_DIR . 'templates/core/';
        }

        if (!class_exists('Layout')) {
            return PHPWS_SOURCE_DIR . "mod/$module/templates/";
        }

        $theme = Layout::getThemeDir();
        return sprintf('%stemplates/%s/', $theme, $module);
    }

    function setCache()
    {
        if (!PHPWS_Template::allowSigmaCache()) {
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

    function allowSigmaCache()
    {
        if (defined('ALLOW_SIGMA_CACHE')) {
            return ALLOW_SIGMA_CACHE;
        } else {
            return FALSE;
        }
    }

    /**
     * returns the expected template directory based on settings in 
     * the template.php config file and the existence of files
     */
    function getTemplateDirectory($module, $directory=NULL)
    {
        $theme_dir  = PHPWS_Template::getTplDir($module) . $directory;
        $local_dir  = sprintf('templates/%s/%s', $module, $directory);
        if (PHPWS_Core::isBranch()) {
            $module_dir = sprintf('%smod/%s/templates/%s', PHPWS_SOURCE_DIR, $module, $directory);
        } else {
            $module_dir = sprintf('./mod/%s/templates/%s', $module, $directory);
        }

        if (FORCE_THEME_TEMPLATES || (!FORCE_MOD_TEMPLATES && is_dir($theme_dir))) {
            return $theme_dir;
        } elseif (FORCE_MOD_TEMPLATES && is_dir($module_dir)) {
            return $module_dir;
        } elseif (is_dir($local_dir)) {
            return $local_dir;
        } else {
            return NULL;
        }
    }

    /**
     * Lists the template files in a directory.
     * Can be called statically. 
     */
    function listTemplates($module, $directory=NULL)
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
    function setFile($file, $strict=FALSE)
    {
        $module = $this->getModule();
        $this->setCache();
        if ($strict == TRUE) {
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

    function setModule($module)
    {
        $this->module = $module;
    }

    function getModule()
    {
        return $this->module;
    }

    function setData($data)
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


    function process($template, $module, $file, $strict=FALSE)
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
            $tpl->setFile($file, TRUE);
        } else {
            $tpl = new PHPWS_Template($module, $file);
        }

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

        if (LABEL_TEMPLATES == TRUE){
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

    function processTemplate($template, $module, $file, $defaultTpl=TRUE)
    {
        if ($defaultTpl)
            return PHPWS_Template::process($template, $module, $file);
        else {
            $tpl = new PHPWS_Template($module);
            $tpl->setFile($file, TRUE);
            $tpl->setData($template);
            return $tpl->get();
        }
    }
}

?>
