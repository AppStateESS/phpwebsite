<?php
/**
 * Object class for a menu
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

class Menu_Item {
  var $id         = 0;
  var $title      = NULL;
  var $template   = NULL;
  var $menu_order = 1;
  var $_error     =  NULL;

  function Menu_Item($id=NULL)
  {
    if (empty($id)) {
      return;
    }

    $this->id = (int)$id;
    $result = $this->init();
    if (PEAR::isError($result)) {
      $this->_error = $result;
      PHPWS_Error::log($result);
    }
  }

  function init()
  {
    if (!isset($this->id)) {
      return FALSE;
    }

    $db = & new PHPWS_DB('menus');
    $result = $db->loadObject($this);
    if (PEAR::isError($result)) {
      return $result;
    }
  }

  function getTemplateList()
  {
    require_once 'Compat/Function/scandir.php';
    $result = PHPWS_Template::listTemplates('menu', 'menu_layout');
    return $result;
  }

}

?>