<?php

class PHPWS_User_ModSetting extends PHPWS_Item {

  function getUserVar($varName, $module){
    if (!$this->getID())
      return FALSE;

    if (!PHPWS_Core::moduleExists($module))
      exit("getUserVar error: <b>$module</b> is malformed or does not exist.<br /><b>varName</b> = $varName.");
    
    return (isset($this->modSettings[$module][$varName])) ? $this->modSettings[$module][$varName] : NULL;
  }


  function setUserVar($varName, $varValue, $module){
    PHPWS_Core::initCoreMod("Text.php");
    if (!($id = $this->getID()))
      return FALSE;

    $currentVar = $this->getUserVar($varName, $module);
    
    if (is_array($currentVar) && is_array($varValue)){
      foreach ($varValue as $key=>$value)
	$currentVar[$key] = $value;

      $varValue = $currentVar;
    }

    if (!PHPWS_Core::moduleExists($module))
      exit("setUserVar error: <b>$module</b> is malformed or does not exist.");

    if (!PHPWS_Text::isValidInput($varName))
      exit("setUserVar error: <b>$varName</b> is not a valid variable name.");

    $DB = new PHPWS_DB("user_uservar");
    $DB->addValue("module", $module);
    $DB->addValue("user_id", $id);
    $DB->addValue("varName", $varName);
    $DB->addValue("varValue", $varValue);

    $this->dropUserVar($varName, $module);
    $result = $DB->insert();
    if (PHPWS_DB::isError($result))
      return FALSE;
    else
      return TRUE;
  }

  function dropUserVar($varName, $module){

    if (!$this->getID())
      return;

    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("dropUserVar error: <b>$module</b> is malformed or does not exist.");

    if (!($GLOBALS["core"]->isValidInput($varName)))
      exit("dropUserVar error: <b>$varName</b> is not a valid variable name.");

    $DB = new PHPWS_DB("user_uservar");
    $DB->addWhere("module", $module);
    $DB->addWhere("user_id", $user->getID());
    $DB->addWhere("varName", $varName);
    $DB->delete();

    if (!$user_id){
      if (isset($this->modSettings[$module][$varName]))
	unset($this->modSettings[$module][$varName]);
    }

  }

  function dropUserModule($module){
    if (!$GLOBALS["core"]->moduleExists($module))
      exit("dropUserModule error: <b>$module</b> is malformed or does not exist.");

    $DB = new PHPWS_DB("user_uservar");
    $DB->addWhere("module", $module);
    $DB->delete();
  }

  function dropUser(){
    $DB = new PHPWS_DB("user_uservar");
    $DB->addWhere("user_id", $this->getID());
    $DB->delete();
  }

  /*---------- Group module variables -------------------*/

  function loadUserGroupVars($user_id=null){
    if ($user_id)
      $user = new PHPWS_User((int)$user_id);
    else
      $user = $this;

    if (!$user->user_id)
      return;

    if (!$user->groups)
      return NULL;

    foreach ($user->groups as $group_id=>$group_name){
      $group = new PHPWS_User_Groups($group_id);
      if ($group->modSettings)
	$rights[$group_id] = $group->modSettings;
    }
    if (isset($rights))
      return $rights;
    else
      return NULL;
  }

  function listUserGroupVars($user_id=NULL, $module){
    if ($user_id)
      $user = new PHPWS_User((int)$user_id);
    else
      $user = $this;

    if (!$user->user_id)
      return;

    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("listUserGroupVars error: <b>$module</b> is malformed or does not exist.");

    if ($user->groupModSettings)
      foreach ($user->groupModSettings as $group_id=>$modVars)
	$groupVars[$group_id] = $modVars[$module];

    return $groupVars;
  }

  function getGroupVar($varName, $group_id=NULL, $module){
    if ($group_id)
      $group = new PHPWS_User_Groups($group_id);
    else
      $group = $this;

    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("getGroupVar error: <b>$module</b> is malformed or does not exist.");
    
    return $group->modSettings[$module][$varName];
  }


  function setGroupVar($varName, $varValue, $group_id=NULL, $module){
    if ($group_id)
      $group = new PHPWS_User_Groups($group_id);
    else
      $group = $this;


    $currentVar = $this->getGroupVar($varName);
    
    if (is_array($currentVar) && is_array($varValue)){
      foreach ($varValue as $key=>$value)
	$currentVar[$key] = $value;

      $varValue = $currentVar;
    }


    if (!$group->group_id)
      exit("setGroupVar error: Group ID ($group_id) is not registered in the system.<br />");

    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("setGroupVar error: <b>$module</b> is malformed or does not exist.");

    if (!($GLOBALS["core"]->isValidInput($varName)))
      exit("setGroupVar error: <b>$varName</b> is not a valid variable name.");

    $insert["module"] = $module;
    $insert["group_id"]     = $group->group_id;
    $insert["varName"]      = $varName;
    $insert["varValue"]     = $varValue;

    $group->dropGroupVar($varName, NULL, $module);

    if($GLOBALS["core"]->sqlInsert($insert, "user_groupvar", 1)){
      if (!$group_id)
	$this->modSettings[$module][$varName] = $varValue;
      return TRUE;
    } else
      return FALSE;
  }

  function dropGroupVar($varName, $group_id=NULL, $module){
    if ($group_id)
      $group = new PHPWS_User_Groups($group_id);
    else
      $group = $this;

    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("dropGroupVar error: <b>$module</b> is malformed or does not exist.");

    if (!($GLOBALS["core"]->isValidInput($varName)))
      exit("dropGroupVar error: <b>$varName</b> is not a valid variable name.");

    $where["module"] = $module;
    $where["group_id"]      = $group->group_id;
    $where["varName"]      = $varName;

    $GLOBALS["core"]->sqlDelete("user_groupvar", $where);
    if (!$group_id){
      if (isset($this->modSettings[$module][$varName]))
	unset($this->modSettings[$module][$varName]);
    }
   
  }

  function dropGroupModule($module){
    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("dropGroupModule error: <b>$module</b> is malformed or does not exist.");

    $where["module"] = $module;
    $GLOBALS["core"]->sqlDelete("user_groupvar", $where);
  }

  function dropGroup($group_id, $module){
    if (!($GLOBALS["core"]->moduleExists($module)))
      if (!($module = $GLOBALS["core"]->current_mod))
	exit("dropGroupModule error: <b>$module</b> is malformed or does not exist.");

    $where["group_id"]      = $group_id;
    $GLOBALS["core"]->sqlDelete("user_groupvar", $where);
  }

}
?>