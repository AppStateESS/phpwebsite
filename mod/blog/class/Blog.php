<?php

class Blog {
  var $id    = NULL;
  var $title = NULL;
  var $entry = NULL;
  var $date  = NULL;

  function Blog($id=NULL){
    if (isset($id)){
      $this->id = (int)$id;
      $result = $this->init();
      if (PEAR::isError($result))
	PHPWS_Error::log($result);
    }
  }

  function init(){
    if (!isset($this->id))
      return FALSE;

    $db = & new PHPWS_DB("blog_entries");
    $db->addWhere("id", $this->id);
    $result = $db->loadObject($this);
    if (PEAR::isError($result))
      return $result;
  }


  function getEntry($print=FALSE){
    if ($print)
      return PHPWS_Text::parseOutput($this->entry);
    else
      return $this->entry;
  }

  function getId(){
    return $this->id;
  }

  function getTitle($print=FALSE){
    if ($print)
      return PHPWS_Text::parseOutput($this->title);
    else
      return $this->title;
  }

  function getFormatedDate($type="%x"){
    return strftime($type, $this->date);
  }

  function save(){
    PHPWS_Core::initCoreClass("Backup.php");

    $db = & new PHPWS_DB("blog_entries");
    if (isset($this->id))
      $db->addWhere("id", $this->id);

    $this->date = mktime();
    $result = $db->saveObject($this);

    if (PEAR::isError($result))
      return $result;

    Backup::save($this->id, "blog_entries");
  }

  function view(){
    $template['TITLE'] = $this->getTitle(TRUE);
    $template['ENTRY'] = $this->getEntry(TRUE);

    if (Current_User::allow("blog", "edit_blog", $this->getId(), FALSE)){
      $vars['action']  = "admin";
      $vars['blog_id'] = $this->getId();
      $vars['command'] = "edit";
      
      $template['EDIT'] = PHPWS_Text::secureLink(_("Edit"), "blog", $vars);
    }

    return PHPWS_Template::process($template, "blog", "view.tpl");
  }

  function kill(){
    PHPWS_Core::initCoreClass("Backup.php");

    $db = & new PHPWS_DB("blog_entries");
    $db->addWhere("id", $this->id);
    $db->delete();

    Backup::flush($this->id, "blog_entries");
  }
}

?>