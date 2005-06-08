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
  var $module     = NULL;
  var $item_name  = NULL;
  var $item_id    = 0;
  var $pin_all    = 0;
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

  function view()
  {

    $tpl['TITLE'] = $this->title;
    $tpl['LINKS'] = $this->getLinks();

    $file = 'menu_layout/' . $this->template;
    $content = PHPWS_Template::process($tpl, 'menu', $file);

    $content_var = 'menu_' . $this->id;
    Layout::add($content, 'menu', $content_var);
  }

}

?>