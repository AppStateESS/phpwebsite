<?php

class My_Page {
  var $scripts = NULL;
  var $modules = NULL;

  function main(){
    $this->init();
    $panel = & My_Page::cpanel();

    if (isset($_REQUEST['tab']) && $_REQUEST['tab'] != "my_page")
      $module = $_REQUEST['tab'];
    else
      $module = "users";

    $content = My_Page::userOption($module);
    
    $panel->setContent($content);
    Layout::add(PHPWS_ControlPanel::display($panel->display()));
  }

  function init(){
    PHPWS_Core::initCoreClass("Module.php");
    $db = & new PHPWS_DB("users_my_page_scripts");
    $result = $db->select();

    if (PEAR::isError($result))
      return $result;

    foreach ($result as $script){
      $module = & new PHPWS_Module($script['module']);
      $this->modules[$script['module']] = $module;
      $this->scripts[$script['module']] = $script['filename'];
    }
  }

  function cpanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $link = "index.php?module=users&amp;action=user";

    foreach ($this->modules as $module){
      $link = "index.php?module=users&amp;action=user";
      $tabs[$module->getTitle()] = array("title"=>$module->getProperName(), "link"=>$link);
    }

    $panel = & new PHPWS_Panel("users");
    $panel->quickSetTabs($tabs);
    $panel->setModule("users");
    $panel->setPanel("panel.tpl");
    return $panel;
  }

  function userOption($module_title){
    $module = $this->modules[$module_title];
    $directory = $module->getDirectory();
    $file = $this->scripts[$module->getTitle()];

    $final_file = $directory . "inc/$file";

    if (!is_file($final_file)){
      PHPWS_Error::log(PHPWS_FILE_NOT_FOUND, "users", "userOption", $final_file);
      return _("There was a problem with this module's My Page file.");
    }

    include $final_file;
    if (!function_exists("my_page"))
      exit("Missing my page in userOption My_Page.php");

    $content = my_page();
    return $content;
  }

}

?>