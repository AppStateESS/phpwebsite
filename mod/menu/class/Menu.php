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

  function showPinned($key=NULL, $title=NULL, $url=NULL)
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
      $menu->view($key, $title, $url);
    }
  }

  function show($key, $title=NULL, $url=NULL)
  {
    if (isset($title) && isset($url)) {
      Menu::showPinned($key, $title, $url);
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
      $menu->view($title, $url);
    }

  }

}

?>
