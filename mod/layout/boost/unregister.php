<?php

function layout_unregister($module, &$content){
  PHPWS_Core::initModClass("layout", "Box.php");
  $content[] = _("Removing old layout components.");
  $db = & new PHPWS_DB("layout_box");
  $db->addWhere("module", $module);
  $moduleBoxes = $db->loadObjects("Layout_Box");

  if (empty($moduleBoxes))
    return;

  if (PEAR::isError($moduleBoxes))
    return $moduleBoxes;

  foreach ($moduleBoxes as $box)
    $box->kill();

}

?>