<?php

/**
 * Class for the administrative settings for demographics
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */

class User_Demographic {
  var $_label         = NULL;
  var $_input_type    = NULL;
  var $_special_info  = NULL;
  var $_proper_name   = NULL;
  var $_required      = NULL;
  var $_active        = NULL;

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
    $this->_label = preg_replace("/([a-z]+)([a-z0-9_])/i", "\\1\\2", $label);
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

  function setSpecialInfo($special_info){
    $this->_special_info = $special_info;
  }

  function getSpecialInfo(){
    return $this->_special_info;
  }

  function addSpecialInfo(&$form){
    $inputType = $this->getInputType();
    $specialInfo = $this->getSpecialInfo();
    if (!isset($specialInfo))
      return TRUE;

    if (!isset($inputType))
      return PHPWS_Error::get(USER_UNKNOWN_INPUT. "users", "getSpecialInfo");

    switch($inputType){
    case "textfield":
      $form->setSize($this->getLabel(), $specialInfo);
      break;

    case "select":
      $final = User_Demographic::buildSpecialArray($specialInfo);
      $form->setValue($this->getLabel(), $final);
      break;

    case "radio":
      $answers = User_Demographic::buildSpecialArray($specialInfo);
      $count = 1;
      foreach($answers as $key=>$value){
	$final[] = $key;
	$label = strtoupper($this->getLabel() . "_" . $count . "_" . "lbl");
	$template[$label] = $value;
	$count++;
      }
      $form->mergeTemplate($template);
      $form->setValue($this->getLabel(), $final);
      break;
    }
  }

  function buildSpecialArray($specialInfo){
    $selects = explode("\n", $specialInfo);
    foreach ($selects as $item){
      $sub = explode(",", trim($item));
      if (isset($sub[0]) && isset($sub[1]))
	$final[$sub[0]] = $sub[1];
    }
    return $final;
  }

  function setProperName($proper_name){
    $this->_proper_name = $proper_name;
  }

  function getProperName($useLabel=FALSE){
    if ($useLabel == TRUE && !isset($this->_proper_name))
      return ucwords(str_replace("_", " ", $this->getLabel()));
    else
      return $this->_proper_name;
  }

  function setActive($active){
    (bool)$active ?  $this->_active = 1 : $this->_active = 0;
  }

  function getActive(){
    return $this->_active;
  }

  function isActive(){
    return (bool)$this->_active;
  }

  function setRequired($required){
    (bool)$required ?  $this->_required = 1 : $this->_required = 0;
  }

  function getRequired(){
    return $this->_required;
  }

  function isRequired(){
    return (bool)$this->_required;
  }

}

?>