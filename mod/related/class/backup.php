<?php

PHPWS_Core::initModClass("related", "Main.php");

class Related_Action {

  function getLink($id, $module, $item_name=NULL){
    $related_main = & new Related_Main;
    $related_main->setModule($module);
    $related_main->setMainId($id);

    if (!isset($item_name))
      $related_main->setItemName($module);
    else
      $related_main->setItemName($item_name);

    $key = md5(rand());
    unset($_SESSION['Related_Bank']);
    $_SESSION['Related_Bank'][$key] = serialize($related_main);

    // Should return an alternate add link if session is already started

    return "<a href=\"index.php?module=related&amp;action=store&amp;key=$key\">" . 
      _("Relate Item") . "</a>";
  }

  function start(){
    if (!isset($_REQUEST['key']))
      return _("Missing key variable.");

    $key = $_REQUEST['key'];

    if (preg_match("/\W/", $key) ||	!isset($_SESSION['Related_Bank'][$key]))
      return _("Faulty key.");

    $main = unserialize($_SESSION['Related_Bank'][$key]);

    Related::bank($main);

    echo phpws_debug::testobject($main);
  }


  function bank($main){
    $content = NULL;



    Layout::add($content, "related", "bankBox");
    
  }

}


?>