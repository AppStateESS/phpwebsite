<?php
require_once "HTML/Template/Sigma.php";
require_once "config/core/template.php";

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

class PHPWS_Template extends HTML_Template_Sigma {
  var $module           = NULL;
  var $error            = NULL;
  var $lastTemplatefile = NULL;

  function PHPWS_Template($module=NULL, $file=NULL){
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

  function getTplDir($module){
    if (!class_exists("Layout"))
      return PHPWS_SOURCE_DIR . "mod/$module/templates/";    
    
    $theme = Layout::getThemeDir();
    return $theme . "templates/" . $module . "/";
  }

  function setCache(){
    if (!PHPWS_Template::allowSigmaCache() ||
	!defined("CACHE_DIRECTORY")        ||
	!defined("CACHE_LIFETIME")         ||
	!is_writable(CACHE_DIRECTORY)
	)
      return;

    $this->setCacheRoot(CACHE_DIRECTORY);
  }

  function allowSigmaCache(){
    if (defined("ALLOW_SIGMA_CACHE"))
      return ALLOW_SIGMA_CACHE;
    else
      return FALSE;
  }

  function setFile($file, $strict=FALSE){
    $module = $this->getModule();
    $this->setCache();
    if ($strict == TRUE) {
      $result = $this->loadTemplatefile($file);
    } else {
      $altFile = PHPWS_Template::getTplDir($module) . $file;

      if (PEAR::isError($altFile))
	return $altFile;

      if (FORCE_THEME_TEMPLATES || is_file($altFile))
	$result = $this->loadTemplatefile($altFile);
      elseif (FORCE_MOD_TEMPLATES){
	$file = PHPWS_SOURCE_DIR . "mod/$module/templates/$file";
	$result = $this->loadTemplatefile($file);
      }
      else {
	$file = "templates/$module/$file";
	$result = $this->loadTemplatefile($file);
      }
    }

    if ($result) {
      $this->lastTemplatefile = $file;
      return $result;
    }
    else 
      return $this->err[0];
  }

  function setModule($module){
    $this->module = $module;
  }

  function getModule(){
    return $this->module;
  }

  function setData($data){
    if (PEAR::isError($data)){
      PHPWS_Error::log($data);
      return NULL;
    }

    foreach($data as $tag=>$content)
      $this->setVariable($tag, $content);
  }

  function getLastTplFile(){
    return $this->getfile($this->lastTemplatefile);
  }

  function process($template, $module, $file){
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
      
    $tpl = & new PHPWS_Template($module, $file);

    if (PEAR::isError($tpl->error)){
      return _("Template error.");
    }

    foreach ($template as $key => $value) {
      if (!is_array($value)) {
	continue;
      }

      foreach ($value as $content) {
	$tpl->setCurrentBlock($key);
	$tpl->setData($content);
	$tpl->parseCurrentBlock();
      }
    }

    $tpl->setData($template);

    $result = $tpl->get();

    if (PEAR::isError($result))
      return $result;

    if (LABEL_TEMPLATES == TRUE){
      $start = "\n<!-- START TPL: " . $tpl->lastTemplatefile . " -->\n";
      $end = "\n<!-- END TPL: " . $tpl->lastTemplatefile . " -->\n";
    }
    else
      $start = $end = NULL;

    if (!isset($result) && RETURN_BLANK_TEMPLATES)
      return $start . $tpl->getLastTplFile() . $end;
    else
      return $start . $result . $end;
  }

  function processTemplate($template, $module, $file, $defaultTpl=TRUE){
    if ($defaultTpl)
      return PHPWS_Template::process($template, $module, $file);
    else {
      $tpl = & new PHPWS_Template($module);
      $tpl->setFile($file, TRUE);
      $tpl->setData($template);
      return $tpl->get();
    }
  }
}

?>
