<?php

function categories_unregister($module, &$content){
  PHPWS_Core::initModClass("categories", "Categories.php");

  Categories::removeModule($module);
}

?>