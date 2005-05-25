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

}

?>
