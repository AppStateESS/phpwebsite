<?php

class CP_Action {

  function adminAction(){

    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = "admin_menu";

    switch ($command){
    case "admin_menu":
      $content = CP_Action::adminMenu();
      break;

    case "tab_up":
      $tab = & new PHPWS_Panel_Tab($_REQUEST['tab_id']);
      $tab->moveup();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;

    case "tab_down":
      $tab = & new PHPWS_Panel_Tab($_REQUEST['tab_id']);
      $tab->movedown();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;
    }

    $template['TITLE'] = _("Control Panel Administration");
    $template['CONTENT'] = $content;
    
    $final = PHPWS_Template::process($template, "controlpanel", "main.tpl");

    Layout::add(PHPWS_ControlPanel::display($final));
  }


  function adminMenu(){
    $tabs = PHPWS_ControlPanel::getAllTabs();
    $links = PHPWS_ControlPanel::getAllLinks();
    
    $tpl = & new PHPWS_Template("controlpanel");
    $tpl->setFile("panelList.tpl");

    $values['module'] = "controlpanel";
    $values['action'] = "admin";

    $up_command = _("Move tab order up");
    $down_command = _("Move tab order down");

    $up = "<img title=\"$up_command\" alt=\"$up_command\" src=\"images/core/list/up_pointer.png\" border=\"0\" />";
    $down = "<img title=\"$down_command\" alt=\"$down_command\" src=\"images/core/list/down_pointer.png\" border=\"0\" />";


    foreach ($tabs as $tab_obj){
      $action = array();
      if (isset($links[$tab_obj->getId()])){
	foreach ($links[$tab_obj->getId()] as $link_obj){
	  $tpl->setCurrentBlock("link-list");
	  $tpl->setData(array("LINK"=>$link_obj->getLabel()));
	  $tpl->parseCurrentBlock();
	}
      }
      $values['tab_id'] = $tab_obj->getId();
      $values['command'] = "tab_up";
      $action[] = PHPWS_Text::moduleLink($up, "controlpanel", $values);

      $values['command'] = "tab_down";
      $action[] = PHPWS_Text::moduleLink($down, "controlpanel", $values);


      $tpl->setCurrentBlock("tab-list");
      $tpl->setData(array("TAB"=>$tab_obj->getTitle(), "ACTION"=>implode("", $action)));
      $tpl->parseCurrentBlock();
    }

    $content = $tpl->get();
    return $content;
  }

}

?>