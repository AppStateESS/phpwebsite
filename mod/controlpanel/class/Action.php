<?php

class CP_Action {

  function adminAction(){
    $content =  "hi!";

    $tabs = PHPWS_ControlPanel::getAllTabs();
    $links = PHPWS_ControlPanel::getAllLinks();
    
    $tpl = & new PHPWS_Template("controlpanel");
    $tpl->setFile("main.tpl");


    foreach ($tabs as $tab_obj){
      if (isset($links[$tab_obj->getId()])){
	foreach ($links[$tab_obj->getId()] as $link_obj){
	  $tpl->setCurrentBlock("link-list");
	  $tpl->setData(array("LINK"=>$link_obj->getLabel()));
	  $tpl->parseCurrentBlock();
	}
      }
      $tpl->setCurrentBlock("tab-list");
      $tpl->setData(array("TAB"=>$tab_obj->getTitle()));
      $tpl->parseCurrentBlock();
    }

    $content = $tpl->get();
    
    Layout::add(PHPWS_ControlPanel::display($content));
  }

}

?>