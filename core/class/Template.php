<?php
require_once "HTML/Template/IT.php";
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

class PHPWS_Template extends HTML_Template_IT {
  var $module = NULL;

  function PHPWS_Template($module=NULL, $file=NULL){
    $this->HTML_Template_IT();
    if (isset($module))
      $this->setModule($module);

    if (isset($file)){
      $result = $this->setFile($file);

      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	$this = $result;
      }
    }
  }

  function getTplDir($module){
    if (!class_exists("Layout"))
      return PHPWS_SOURCE_DIR . "mod/$module/templates/";    
    
    $theme = Layout::getThemeDir();
    return $theme . "templates/" . $module . "/";
  }

  function setFile($file, $strict=FALSE){
    $module = $this->getModule();
    if ($strict == TRUE)
      $result = $this->loadTemplatefile($file);
    else {
      $altFile = PHPWS_Template::getTplDir($module) . $file;

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

    if ($result)
      return $result;
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
    if (!is_array($data))
      return PEAR::raiseError("The submitted data was not an array is was a " . gettype($data));

    foreach($data as $tag=>$content){
      $this->setVariable($tag, $content);
    }

  }

  function getLastTplFile(){
    return PHPWS_File::readFile($this->lastTemplatefile);
  }

  function process($template, $module, $file){
    $tpl = & new PHPWS_Template($module, $file);

    if (PEAR::isError($tpl))
      return $tpl;
    $tpl->setData($template);
    $result = $tpl->get();
    if (!isset($result) && RETURN_BLANK_TEMPLATES)
      return $tpl->getLastTplFile();
    else
      return $result;

  }
}

?>
