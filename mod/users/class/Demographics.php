mod/users/index.php
<?php

define('USER_ERR_LABEL_NOT_FOUND', 1); 
define('USER_ERR_UNKNOWN_INPUT',   2); 

class User_Demographic {
  var $_label      = NULL;
  var $_input_type = NULL;
  var $_presets    = NULL;


  function User_Demographic($label=NULL){
    if (!isset($label))
      return;

    $DB = new PHPWS_DB("user_demographic_items");
    $DB->addWhere("label", $label);
    $item = $DB->loadObjects("User_Demographic", NULL, TRUE);
    if (PEAR::isError($item))
      PHPWS_Error::log(USER_ERR_LABEL_NOT_FOUND, "users", "User_Demographics");
    else
      $this = $item;
  }
  
  function setLabel($label){
    $this->_label = preg_replace("/[a-z]+[a-z0-9_]/i", "//1//2", $label);

  }

  function getLabel(){
    return $this->_label;

  } 
  function setInputType($input_type){
    switch (strtolower($input_type)){
    case "textfield":
    case "textarea":
    case "radio":
    case "checkbox":
    case "select":
    case "multiple":
      $this->_input_type = $input_type;
      return TRUE;
      break;

    default:
      return PHPWS_Error::get(USER_ERR_UNKNOWN_INPUT, "users", "setInputType");
      break;

    }

  }

  function getInputType(){
    return $this->_input_type;
  }

  function setPresets($presets){

  }

  function getPresets(){

  }
}
?>