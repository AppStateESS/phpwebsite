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

    foreach ($result as $menu) {
      $menu->view();
    }

  }

}

?>
