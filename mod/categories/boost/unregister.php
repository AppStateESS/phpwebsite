<?php

function categories_unregister($module, &$content){
  PHPWS_Core::initModClass("categories", "Categories.php");

  $tableName = Categories::categoryTableName($module);

  $db = & new PHPWS_DB($tableName);
  $db->dropTable();

  Categories::removeModule($module);
}

?>