<?php

function categories_register($module, &$content){
  PHPWS_Core::initModClass("categories", "Categories.php");

  $catFile = PHPWS_Core::getConfigFile($module, "categories.php");

  if ($catFile == FALSE){
    PHPWS_Boost::addLog("categories", _("No Categories file found."));
    return NULL;
  }

  include_once $catFile;

  if (!isset($categories) || $categories != TRUE)
    return NULL;

  $tableName = Categories::categoryTableName($module);

  if (PHPWS_DB::isTable($tableName)){
    $content[] = _("This modules category table already exists.");
    return FALSE;
  }

  $db = & new PHPWS_DB($tableName);
  $db->addValue("item_id", "int NOT NULL default '0'");
  $db->addValue("cat_id", "int NOT NULL default '0'");
  $db->addValue("item_name", "varchar(40) NOT NULL default ''");

  $result = $db->createTable();

  if (PEAR::isError($result))
    return $result;

  $content[] = _("Category table added.");

  Categories::addModule($module);

  return TRUE;
}

?>