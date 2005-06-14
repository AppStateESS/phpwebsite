<?php
/**
 * Main functionality class for Menu module
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu
 * @version $Id$
 */

class Menu {

  function admin()
  {
    PHPWS_Core::initModClass('menu', 'Menu_Admin.php');
    Menu_Admin::main();
  }

  function showPinned()
  {
    PHPWS_Core::initModClass('menu', 'Menu_Item.php');
    $db = & new PHPWS_DB('menus');
    $db->addWhere('pin_all', 1);
    $result = $db->getObjects('Menu_Item');

    if (PEAR::isError($result)) {
      PHPWS_Error::log($result);
      return;
    }

    $GLOBALS['Pinned_Menus'] = $result;

    foreach ($result as $menu) {
      $menu->view();
    }
  }

  function show($key, $title=NULL, $url=NULL)
  {
    if (!empty($title) || !empty($url)) {
      Menu::readyLink($title, $url);
    }
   
    $tb1 = PHPWS_DB::getPrefix() . 'menus';
    $tb2 = PHPWS_DB::getPrefix() . 'menu_assoc';

    $sql = "SELECT $tb1.* FROM $tb1, $tb2 WHERE $tb2.module='" 
      . $key->getModule() . "' AND $tb2.item_name='" . $key->getItemName() . "'
AND $tb2.item_id='" . $key->getItemId() . "' AND $tb2.menu_id=$tb1.id AND $tb1.pin_all='0'";

    $db = & new PHPWS_DB;
    $db->setSQLQuery($sql);
    $result = $db->getObjects('menu_item');

    if (empty($result) || PEAR::isError($result)) {
      return $result;
    }

    foreach ($result as $menu) {
      $menu->view();
    }
  }

  function getAddLink($menu_id, $parent_id=NULL)
  {
    $direct_link = FALSE;

    if (empty($GLOBALS['Menu_Ready_Link'])) {
      $title = NULL;
      $url = Menu::grabUrl();
    } else {
      if (empty($GLOBALS['Menu_Ready_Link']['title'])) {
	$title = NULL;
      } else {
	$title = $GLOBALS['Menu_Ready_Link']['title'];
      }

      if (!empty($GLOBALS['Menu_Ready_Link']['url'])) {
	$url = str_replace('&amp;', '&', $GLOBALS['Menu_Ready_Link']['url']);
      } else {
	$url = Menu::grabUrl();
      }
    }

    $vars['command'] = 'add_link';
    $vars['menu_id'] = $menu_id;
    if (!empty($parent_id)) {
      $vars['parent'] = $parent_id;
    } else {
      $vars['parent'] = 0;
    }


    if (!empty($title)) {
      $vars['title'] = urlencode($title);
      $direct_link = TRUE;
    }
    
    if (!empty($url)) {
      $vars['url'] = urlencode($url);
    }

    if ($direct_link) {
      return PHPWS_Text::secureLink(_('Add'), 'menu', $vars);
    } else {
      $js['question']   = _('Enter link title');
      $js['address']    = PHPWS_Text::linkAddress('menu', $vars, TRUE);
      $js['link']       = _('Add');
      $js['value_name'] = 'title';
      return javascript('prompt', $js);
    }
  }

  function grabUrl()
  {
    static $url = NULL;

    if (!empty($url)) {
      return $url;
    }

    $get_values = PHPWS_Text::getGetValues();
    if (!empty($get_values)) {
      unset($get_values['authkey']);
    } else {
      return 'index.php';
    }

    foreach ($get_values as $key => $value) {
      $new_link[] = "$key=$value";
    }

    return  'index.php?' . implode('&', $new_link);
  }


  function readyLink($title=NULL, $url=NULL)
  {
    $GLOBALS['Menu_Ready_Link']['title'] = $title;
    $GLOBALS['Menu_Ready_Link']['url']   = $url;
  }

}

?>
