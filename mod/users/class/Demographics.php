<?php

PHPWS_Core::initModClass("users", "User_Demographic.php");

class Demographics {

  function import($file){
    $file = PHPWS_Core::getConfigFile("users", $file);

    if (PEAR::isError($file))
      return $file;

    $db = new PHPWS_DB("users_demographics");

    $demo = file($file);

    foreach ($demo as $item){
      if (preg_match("/^[a-z]/i", $item)){
	$newdemo = explode(":", trim($item));
	$db->addValue("label", trim($newdemo[0]));
	$db->addValue("input_type", trim($newdemo[1]));
	if (isset($newdemo[2])){
	  if (preg_match("/^file/", trim($newdemo[2]))){
	    $temp = explode(",", trim($newdemo[2]));
	    $filename = $temp[1];
	    $special = Demographics::getSpecialFile($filename);
	    if (PEAR::isError($special))
	      return $special;

	    $db->addValue("special_info", $special);
	  } else {
	    $db->addValue("special_info", $newdemo[2]);
	  }

	}
	if (isset($newdemo[3]))
	  $db->addValue("proper_name", $newdemo[3]);
	else
	  $db->addValue("proper_name", ucwords(str_replace("_", " ", $newdemo[0])));
      }

      $db->insert();
      $db->resetValues();
    }
  }

  function getDemographics($mode=NULL, $activeOnly=FALSE){
    $db = new PHPWS_DB("users_demographics");
    if ($activeOnly)
      $db->addWhere("active", 1);

    switch ($mode){
    case "label":
      $db->addColumn("label");
      $result = $db->select("col");
      break;

    case "object":
    default:
      $result = $db->getObjects("User_Demographic");
      break;
    }
    return $result;
  }

  function getSpecialFile($file){
    $file = PHPWS_Core::getConfigFile("users", $file);
    if (PEAR::isError($file))
      return $file;

    $special = file($file);
    return implode("", $special);
  }

  //Maybe Delete
  function allDemographicsTpl(&$template){
    $labels = Demographics::getDemographics("label", TRUE);

    foreach ($labels as $item){
      if (!isset($template[strtoupper($item)]))
	continue;

      $itemLbl = strtoupper($item) . "_LBL";

      if (isset($template[$itemLbl]))
	$demoRow['LABEL'] = $template[$itemLbl];

      $demoRow['INPUT'] = $template[strtoupper($item)];
      $rows[] = PHPWS_Template::process($demoRow, "users", "forms/demoRow.tpl");
    }

    return implode("\n", $rows);
  }


  function setFormValue($value, &$item, &$form){
    switch ($item->getInputType()){
    case "textfield":
    case "textarea":
      $form->setValue($item->getFormLabel(), $value);
      break;
    }
  }

  function form(&$form, &$user){
    if (!isset($user))
      $user = new PHPWS_User;

    $demo = Demographics::getDemographics(NULL, TRUE);

    if (!isset($demo))
      return NULL;

    foreach ($demo as $item){
      $result = $form->add($item->getFormLabel(), $item->getInputType());
      $formValue = $user->getVar($item->getLabel(), "demographics");

      if (isset($formValue))
	Demographics::setFormValue($formValue, $item, $form);

      $form->setTag($item->getFormLabel(), $item->getLabel());
      $item->addSpecialInfo($form);
      $template[strtoupper($item->getLabel()) . "_LBL"] = $item->getProperName(TRUE);
    }
    $form->mergeTemplate($template);
  }

  function setDemographic(&$user, $name, $value, $label){
    $user->setVar($name, $value, "demographics");
  }


  function post(&$user){
    $demo = & $_POST['demographic'];

    foreach ($demo as $name => $value)
      Demographics::setDemographic($user, $name, $value, "demographics");
  }

}
?>