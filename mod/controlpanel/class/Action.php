<?php
/**
 * 
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class CP_Action {

  function adminAction(){

    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = 'admin_menu';

    switch ($command){
    case 'admin_menu':
      $content = CP_Action::adminMenu();
      break;

    case 'tab_up':
      $tab = & new PHPWS_Panel_Tab($_REQUEST['tab_id']);
      $tab->moveup();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;

    case 'tab_down':
      $tab = & new PHPWS_Panel_Tab($_REQUEST['tab_id']);
      $tab->movedown();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;

    case 'link_up':
      $link = & new PHPWS_Panel_Link($_REQUEST['link_id']);
      $link->moveup();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;

    case 'link_down':
      $link = & new PHPWS_Panel_Link($_REQUEST['link_id']);
      $link->movedown();
      PHPWS_ControlPanel::reset();
      $content = CP_Action::adminMenu();
      break;
    }

    $template['TITLE'] = _('Control Panel Administration');
    $template['CONTENT'] = $content;
    
    $final = PHPWS_Template::process($template, 'controlpanel', 'main.tpl');

    Layout::add(PHPWS_ControlPanel::display($final));
  }


  function adminMenu(){
    $tabs = PHPWS_ControlPanel::getAllTabs();
    $links = PHPWS_ControlPanel::getAllLinks();

   
    $tpl = & new PHPWS_Template('controlpanel');
    $tpl->setFile('panelList.tpl');

    $tvalues['module'] = $lvalues['module'] = 'controlpanel';
    $tvalues['action'] = $lvalues['action'] = 'admin';

    $up_tab_command = _('Move tab order up');
    $down_tab_command = _('Move tab order down');
    $up_tab = sprintf('<img title="%s" alt="%s" src="./images/core/list/up_pointer.png" border="0" />', $up_tab_command, $up_tab_command);
    $down_tab = sprintf('<img title="%s" alt="%s" src="images/core/list/down_pointer.png" border="0" />', $down_tab_command, $down_tab_command);

    $up_link_command = _('Move link order up');
    $down_link_command = _('Move link order down');
    $up_link = sprintf ('<img title="%s" alt="%s" src="images/core/list/up_pointer.png" border="0" />', $up_link_command, $up_link_command);
    $down_link = sprintf('<img title="%s" alt="%s" src="images/core/list/down_pointer.png" border="0" />', $down_link_command, $down_link_command);

    if (count($tabs) > 1)
      $move_tabs = TRUE;
    else
      $move_tabs = FALSE;

    foreach ($tabs as $tab_obj){
      $taction = array();
      if (isset($links[$tab_obj->id])){
	if (count($links[$tab_obj->id]) > 1)
	  $move_links = TRUE;
	else
	  $move_links = FALSE;
	foreach ($links[$tab_obj->id] as $link_obj){
	  $laction = array();
	  if ($move_links){
	    $lvalues['link_id'] = $link_obj->id;
	    $lvalues['command'] = 'link_up';
	    $laction[] = PHPWS_Text::moduleLink($up_link, 'controlpanel', $lvalues);
	  
	    $lvalues['command'] = 'link_down';
	    $laction[] = PHPWS_Text::moduleLink($down_link, 'controlpanel', $lvalues);
	  }

	  $tpl->setCurrentBlock('link-list');
	  $tpl->setData(array('LINK'=>$link_obj->getLabel(), 'LACTION'=>implode('', $laction)));
	  $tpl->parseCurrentBlock();
	}
      }

      if ($move_tabs){
	$tvalues['tab_id'] = $tab_obj->id;
	$tvalues['command'] = 'tab_up';
	$taction[] = PHPWS_Text::secureLink($up_tab, 'controlpanel', $tvalues);
	
	$tvalues['command'] = 'tab_down';
	$taction[] = PHPWS_Text::secureLink($down_tab, 'controlpanel', $tvalues);
      }

      $tpl->setCurrentBlock('tab-list');
      $tpl->setData(array('TAB'=>$tab_obj->getTitle(), 'TACTION'=>implode('', $taction)));
      $tpl->parseCurrentBlock();
    }

    $content = $tpl->get();
    return $content;
  }

}

?>