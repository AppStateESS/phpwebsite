<?php

define("CATEGORY_NOT_FOUND", -1);
define("CAT_DB_PROBLEM",     -2);
define("CAT_NO_MOD_TABLE",   -3);

class Categories{

  function delete(&$category){
    $modules = Categories::listModules();

    if (!empty($modules)){
      foreach ($modules as $mod){
	$result = Categories::dropModuleCategory($category, $mod);
	if (PEAR::isError($result))
	  PHPWS_Error::log($result);
      }
    }

    return $category->kill();
  }

  function dropModuleCategory(&$category, $module){
    $tableName = Categories::categoryTableName($module);

    if (!PHPWS_DB::isTable($tableName))
      return FALSE;

    $db = & new PHPWS_DB($tableName);
    $db->addWhere("cat_id", $category->id);
    return $db->delete();
  }

  function getForm($module, $id=NULL, $item_name=NULL){
    if (empty($itemname))
      $itemname = $module;

    $categories = Categories::getCategories("idlist");

    if (PEAR::isError($categories)){
      PHPWS_Error::log($categories);
      return PHPWS_Error::get(CAT_DB_PROBLEM, "categories", "Categories::getForm");
    }
      
    if (empty($categories))
      return _("No categories exist.");

    $multiple = & new Form_Multiple("categories", $categories);
    $multiple->setSize(5);
    $multiple->setWidth("100%");

    if (isset($id)){
      $itemMatch = Categories::getMatch($module, $id, $item_name);
      if (PEAR::isError($itemMatch))
	return $itemMatch;

      if (isset($itemMatch))
	$multiple->setMatch($itemMatch);
    }

    return $multiple->get();
  }


  function getMatch($module, $id, $item_name){
    $tableName = Categories::categoryTableName($module);
    if (!PHPWS_DB::isTable($tableName))
      return PHPWS_Error::get(CAT_NO_MOD_TABLE, "categories", "Categories::getMatch", $tableName);

    $db = & new PHPWS_DB($tableName);
    $db->addWhere("item_id", $id);
    $db->addWhere("item_name", $item_name);
    $db->addColumn("cat_id");
    $result = $db->select("col");

    if (PEAR::isError($result)){
      PHPWS_Error::log($result);
      return PHPWS_Error::get(CAT_DB_PROBLEM, "categories", "Categories::getMatch");
    }

    return $result;
  }

  function categoryTableName($module){
    return $module . "_categories";
  }


  function getCategories($mode){
    $db = & new PHPWS_DB("categories");

    switch ($mode){
    case "idlist":
      $db->addColumn("title");
      $db->setIndexBy("id");
      $result = $db->select("col");
      break;

    case "parent":
      $db->addColumn("title");
      $db->setIndexBy("id");
      $result = $db->select("col");

      if (is_array($result))
	array_unshift($result, "-" . _("Top Level") . "-");
      else
	$result = array(0=>"-" . _("Top Level") . "-");
      break;
    }

    return $result;
  }

  function addModule($module){
    $db = & new PHPWS_DB("categories_modules");
    $db->addValue("mod_title", $module);
    $db->insert();
  }

  function listModules(){
    $db = & new PHPWS_DB("categories_modules");
    $db->addColumn("mod_title");
    return $db->select("col");
  }

  function removeModule($module){
    $db = & new PHPWS_DB("categories_modules");
    $db->addWhere("mod_title", $module);
    $db->delete();
  }


}

?>