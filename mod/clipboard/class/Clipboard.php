<?php

class Clipboard
{
  var $components = NULL;

  function action()
  {
    if (!isset($_REQUEST['action']))
      return;

    switch ($_REQUEST['action']){
    case 'showclip':
      Clipboard::view();
      break;

    case 'drop':
      if (isset($_REQUEST['key'])) {
	unset($_SESSION['Clipboard']->components[$_REQUEST['key']]);
	PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
      }
      break;

    case 'clear':
      unset($_SESSION['Clipboard']);
      PHPWS_Core::reroute($_SERVER['HTTP_REFERER']);
      break;
    }

  }

  function view()
  {
    $clip = $_SESSION['Clipboard']->components[$_REQUEST['key']]->content;
    $clip =  sprintf('<textarea cols="35" rows="4">%s</textarea>', $clip);
   
    $template['TITLE'] = _('Clipboard');
    $template['DIRECTIONS'] = _('Highlight the text below and paste it into the text box.');
    $template['CONTENT'] = $clip;
    
    $button = _('Close Window');
    $template['BUTTON'] = sprintf('<input type="button" onclick="window.close()" value="%s" />', $button);
    Layout::nakedDisplay(PHPWS_Template::process($template, 'clipboard', 'clipboard.tpl'));
  }


  function show()
  {
    if (!isset($_SESSION['Clipboard'])) {
      Clipboard::init();
    }

    if (empty($_SESSION['Clipboard']->components)) {
      Clipboard::clear();
      return NULL;
    }
      

    $data['width'] = '280';
    $data['height'] = '150';

    $clipVars['action'] = 'drop';

    foreach ($_SESSION['Clipboard']->components as $key => $component){
      $clipVars['key'] = $key;
      $drop = PHPWS_Text::moduleLink(_('Drop'), 'clipboard', $clipVars);
      $data['address'] = 'index.php?module=clipboard&action=showclip&key=' . $key;
      $data['label'] = $component->title;
      $content[] = Layout::getJavascript('open_window', $data) . ' ' . $drop;
    }

    $clipVars['action'] = 'clear';
    $template['CLEAR'] = PHPWS_Text::moduleLink(_('Clear'), 'clipboard', $clipVars);
    $template['LINKS'] = implode('<br />', $content);

    $vars['CONTENT'] = PHPWS_Template::process($template, 'clipboard', 'list.tpl');
    $vars['TITLE'] = _('Clipboard');

    Layout::set($vars, 'clipboard', 'clipboard', TRUE);
  }

  function init()
  {
    $_SESSION['Clipboard'] = & new Clipboard;
  }

  function copy($title, $content)
  {
    if (!isset($_SESSION['Clipboard']))
      Clipboard::init();

    $key = md5($title . $content);

    if (!isset($_SESSION['Clipboard']->components[$key]))
      $_SESSION['Clipboard']->components[$key] = & new Clipboard_Component($title, $content);
    Clipboard::show();
  }

  function clear()
  {
    unset($_SESSION['Clipboard']);
  }

}


class Clipboard_Component {
  var $title;
  var $content;

  function Clipboard_Component($title, $content){
    $this->title = strip_tags($title);
    $this->content = htmlspecialchars($content);
  }

}

?>