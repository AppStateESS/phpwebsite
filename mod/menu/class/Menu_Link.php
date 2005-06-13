<?php

define('MENU_MISSING_INFO', 1);

class Menu_Link {
  var $id         = 0;
  var $menu_id    = 0;
  var $module     = NULL;
  var $item_name  = NULL;
  var $item_id    = 0;
  var $title      = NULL;
  var $url        = NULL;
  var $parent     = 0;
  var $active     = 1;
  var $link_order = 1;
  var $_error     = NULL;
  var $_children  = NULL;

  function Menu_Link($id=NULL)
  {
    if (empty($id)) {
      return;
    }

    $this->id = (int)$id;
    $result = $this->init();
    if (PEAR::isError($result)) {
      $this->_error = $result;
    }
  }

  function init()
  {
    $db = & new PHPWS_DB('menu_links');
    $db->loadObject($this);
    $this->loadChildren();
  }

  function loadChildren()
  {
    $db = & new PHPWS_DB('menu_links');
    $db->addWhere('parent', $this->id);
    $db->addOrder('link_order');
    $result = $db->getObjects('menu_link');
    if (empty($result)) {
      return;
    }

    foreach ($result as $link) {
      $link->loadChildren();
      $this->_children[$link->id] = $link;
    }
  }

  function setParent($parent)
  {
    $this->parent = (int)$parent;
  }

  function setTitle($title)
  {
    $this->title = strip_tags(trim($title));
  }

  function setUrl($url, $local=TRUE)
  {
    if ($local) {
      PHPWS_Text::makeRelative($url);
    }

    $this->url = trim($url);
  }

  function setKey($key)
  {
    $key->plugKey($this);
  }

  function setMenuId($id)
  {
    $this->menu_id = (int)$id;
  }

  function _getOrder()
  {
    $db = & new PHPWS_DB('menu_links');
    $db->addWhere('menu_id', $this->menu_id);
    $db->addColumn('link_order');
    $current_order = $db->select('max');

    if (empty($current_order)) {
      $current_order = 1;
    } else {
      $current_order++;
    }

    return $current_order;
  }

  function save()
  {
    if (empty($this->menu_id) || empty($this->title) || empty($this->url)) {
      return PHPWS_Error::get(MENU_MISSING_INFO, 'menu', 'Menu_Link::save');
    }
        
    $this->link_order = $this->_getOrder();

    $db = & new PHPWS_DB('menu_links');
    return $db->saveObject($this);
  }

  function view($edit=FALSE)
  {
    $link = sprintf('<a href="%s" title="%s">%s</a>', $this->url, $this->title, $this->title);

    if ($edit && Current_User::allow('menu')) {
      $vars['command'] = 'add_link';
      $vars['menu_id'] = $this->menu_id;
      $vars['parent']  = $this->id;
      $template['ADD_LINK'] = PHPWS_Text::secureLink(_('Add'), 'menu', $vars);
    }

    $template['LINK'] = $link;
    if (!empty($this->_children)) {
      foreach ($this->_children as $kid) {
	$sublinks[] = $kid->view($edit);
      }
      $template['SUBLINK'] = implode("\n", $sublinks);
    }

    return PHPWS_Template::process($template, 'menu', 'links/link.tpl');
  }

}

?>