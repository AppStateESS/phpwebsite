<?php

PHPWS_Core::initCoreClass("File.php");

class Editor {
  var $data       = NULL; // Contains the editor text
  var $name       = NULL;
  var $type       = NULL; // WYSIWYG file
  var $editorList = NULL;
  var $error      = NULL;

  function Editor($type=NULL, $name=NULL, $data=NULL){
    $editorList = PHPWS_File::readDirectory("./javascript/editors/", TRUE);

    if (PEAR::isError($editorList)){
      PHPWS_Error::log($editorList);
      PHPWS_Core::errorPage();
    }

    $this->editorList = $editorList;
    if (isset($type)){
      $result = $this->setType($type);
      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	PHPWS_Core::errorPage();
      }
    }

    if (isset($name))
      $this->setName($name);

    if (isset($data))
      $this->setData($data);

  }

  function get(){
    $formData['NAME'] = $this->name;
    $formData['VALUE'] = $this->data;
    return Layout::getJavascript("editors/" . $this->type, $formData);
  }

  function getError(){
    return $this->error;
  }

  function getName(){
    return $this->name;
  }

  function getType(){
    return $this->type;
  }

  function isType($type_name){
    return in_array($type_name, $this->editorList);
  }

  function setData($data){
    $this->data = $data;
  }

  function setName($name){
    $this->name = $name;
  }

  function setType($type){
    if ($this->isType($type))
      $this->type = $type;
    else
      return PHPWS_Error::get(EDITOR_MISSING_FILE, "core", "Editor::constructor", $type);
  }

}

?>