<?php
/**
 * Object class for a menu
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

PHPWS_Core::initModClass('menu', 'Menu_Link.php');

class Menu_Item {
  var $id         = 0;
  var $title      = NULL;
  var $template   = NULL;
  var $pin_all    = 0;
  var $_error     = NULL;

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

  function setTitle($title)
  {
    $this->title = strip_tags($title);
  }

  function setTemplate($template)
  {
    $this->template = $template;
  }

  function getTemplate($full_directory=FALSE)
  {
  }

  function getTemplateList()
  {
    require_once 'Compat/Function/scandir.php';
    $result = PHPWS_Template::listTemplates('menu', 'menu_layout');
    return $result;
  }

  function post()
  {
    if (empty($_POST['title'])) {
      $errors[] = _('Missing menu title.');
    } else {
      $this->setTitle($_POST['title']);
    }

    $this->setTemplate($_POST['template']);

    if (isset($errors)) {
      return $errors;
    } else {
      $result = $this->save();
      if (PEAR::isError($result)) {
	PHPWS_Error::log($result);
	return array(_('Unable to save menu. Please check error logs.'));
      }
      return TRUE;
    }
  }

  function save()
  {
    if (empty($this->title)) {
      return FALSE;
    }
    $db = & new PHPWS_DB('menus');
    return $db->saveObject($this);
  }

  function getLinks()
  {
    $content = NULL;
    return $content;
  }

  function getRowTags()
  {
    $vars['menu_id'] = $this->id;
    $vars['command'] = 'edit_menu';
    $links[] = PHPWS_Text::secureLink(_('Edit'), 'menu', $vars);

    $vars['command'] = 'clip';
    $links[] = PHPWS_Text::secureLink(_('Clip'), 'menu', $vars);

    $vars['command'] = 'pin_all';
    if ($this->pin_all == 0) {
      $link_title = _('Pin All');
      $vars['hook'] = 1;
    } else {
      $link_title = _('Unpin All');
      $vars['hook'] = 0;
    }
    $links[] = PHPWS_Text::secureLink($link_title, 'menu', $vars);

    $tpl['ACTION'] = implode(' | ', $links);
    return $tpl;
  }

  function kill()
  {
    Layout::purgeBox('menu_' . $id);
  }

  function addLink($title, $url)
  {
    $link = & new Menu_Link;
    $link->setTitle($title);
    $link->setUrl($url);
    $link->setMenuId($this->id);
       return $link->save();
  }

  function loadLink($title, $url)
  {
    $_SESSION['Last_Link'][$this->id]['title'] = strip_tags(trim($title));
    $_SESSION['Last_Link'][$this->id]['url']   = $url;
  }

  function view($title=NULL, $url=NULL)
  {
    $tpl['TITLE'] = $this->title;
    $tpl['LINKS'] = $this->getLinks();

    $file = 'menu_layout/' . $this->template;

    $content_var = 'menu_' . $this->id;

    if (isset($title) && isset($url)) {
      $this->loadLink($title, $url);
      $vars['command'] = 'add_link';
      $vars['menu_id'] = $this->id;
      $tpl['ADD_LINK'] = PHPWS_Text::secureLink(_('Add'), 'menu', $vars);
    }

    $content = PHPWS_Template::process($tpl, 'menu', $file, $content_var);
    Layout::set($content, 'menu', $content_var);
  }

}

?>