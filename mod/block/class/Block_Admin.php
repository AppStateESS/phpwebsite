<?php

class Block_Admin {

  function action()
  {
    $panel = & Block_Admin::cpanel();
    if (isset($_REQUEST['action'])) {
      $action = $_REQUEST['action'];
    }
    else {
      $tab = $panel->getCurrentTab();
      if (empty($tab)) {
	$action = 'new';
      } else {
	$action = &$tab;
      }
    }

    $content = Block_Admin::route($action);

    $panel->setContent($content);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function &cpanel()
  {
    PHPWS_Core::initModClass('controlpanel', 'Panel.php');
    $linkBase = 'index.php?module=block';
    $tabs['new']  = array ('title'=>_('New'),  'link'=> $linkBase);
    $tabs['list'] = array ('title'=>_('List'), 'link'=> $linkBase);

    $panel = & new PHPWS_Panel('categories');
    $panel->quickSetTabs($tabs);

    $panel->setModule('block');
    return $panel;
  }

  function route($action)
  {
    if (isset($_REQUEST['block_id'])) {
      $block = & new Block_Item($_REQUEST['block_id']);
    } else {
      $block = & new Block_Item();
    }

    switch ($action) {
    case 'new':
      $title = _('New Block');
      $content = Block_Admin::edit($block);
      break;

    case 'delete':
      $block->kill();
      $title = _('Block list');
      $content = Block_Admin::blockList();
      $message = _('Block deleted.');
      break;

    case 'edit':
      $title = ('Edit Block');
      $content = Block_Admin::edit($block);
      break;

    case 'store':
      Block_Admin::storeBlock($block);
      $title = _('Block list');
      $content = Block_Admin::blockList();
      $message = _('Block stored.');
      break;

    case 'remove':
      Block_Admin::removeBlock();
      PHPWS_Core::goBack();
      break;

    case 'copy':
      Block_Admin::copyBlock($block);
      PHPWS_Core::goBack();
      break;

    case 'postBlock':
      Block_Admin::postBlock($block);
      $result = $block->save();

      $message = _('Block saved.');
      $title = _('Block list');
      $content = Block_Admin::blockList();
      break;

    case 'pin':
      Block_Admin::pinBlock();
      PHPWS_Core::goBack();
      break;

    case 'list':
      $title = _('Block list');
      $content = Block_Admin::blockList();
      break;
    }

    $template['TITLE'] = &$title;
    if (isset($message)) {
      $template['MESSAGE'] = &$message;
    }
    $template['CONTENT'] = &$content;

    return PHPWS_Template::process($template, 'block', 'admin.tpl');
  }

  function removeBlock()
  {
    if (!isset($_GET['mod']) ||
	!isset($_GET['item']) ||
	!isset($_GET['itname']) ||
	!isset($_GET['block_id'])
	)
      return;

    $db = & new PHPWS_DB('block_pinned');
    $db->addWhere('block_id', $_GET['block_id']);
    $db->addWhere('module', $_GET['mod']);
    $db->addWhere('item_id', $_GET['item']);
    $db->addWhere('itemname', $_GET['itname']);
    $result = $db->delete();
    if (PEAR::isError($result)) {
      PHPWS_Error::log($result);
    }

  }

  function edit(&$block)
  {
    PHPWS_Core::initCoreClass('Editor.php');
    $form = & new PHPWS_Form;
    $form->addHidden('module', 'block');
    $form->addHidden('action', 'postBlock');

    $form->addText('title', $block->getTitle());
    $form->setLabel('title', _('Title'));
    $form->setSize('title', 50);

    if (empty($block->id)) {
      $form->addSubmit('submit', _('Save New Block'));
    } else {
      $form->addHidden('block_id', $block->getId());
      $form->addSubmit('submit', _('Update Current Block'));
    }

    if (Editor::willWork()){
      $editor = & new Editor('block_content', $block->getContent());
      $block_content = $editor->get();
      $form->addTplTag('BLOCK_CONTENT', $block_content);
      $form->addTplTag('BLOCK_CONTENT_LABEL', PHPWS_Form::makeLabel('block_content',_('Content')));
    } else {
      $form->addTextArea('block_content', $block->getContent());
      $form->setRows('block_content', '10');
      $form->setWidth('block_content', '80%');
      $form->setLabel('block_content', _('Entry'));
    }

    $template = $form->getTemplate();

    $content = PHPWS_Template::process($template, 'block', 'edit.tpl');
    return $content;
  }

  function postBlock(&$block)
  {
    $block->setTitle($_POST['title']);
    $block->setContent($_POST['block_content']);
    return TRUE;
  }

  function _getListAction(&$block){
    $vars['block_id'] = $block->getId();

    $vars['action'] = 'edit';
    $links[] = PHPWS_Text::secureLink(_('Edit'), 'block', $vars);

    $vars['action'] = 'store';
    $links[] = PHPWS_Text::secureLink(_('Store'), 'block', $vars);

    $vars['action'] = 'copy';
    $links[] = PHPWS_Text::secureLink(_('Copy'), 'block', $vars);

    $vars['action'] = 'delete';
    $confirm_vars['QUESTION'] = _('Are you sure you want to permanently delete this block?');
    $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('block', $vars, TRUE);
    $confirm_vars['LINK'] = _('Delete');
    $links[] = Layout::getJavascript('confirm', $confirm_vars);

    return implode(' | ', $links);
  }

  function blockList()
  {
    PHPWS_Core::initCoreClass('DBPager.php');
    
    $pageTags['TITLE']   = _('Title');
    $pageTags['CONTENT'] = _('Content');
    $pageTags['ACTION']  = _('Action');

    $link = 'index.php?module=block&amp;action=list&amp;authkey='
      . Current_User::getAuthKey();

    $pager = & new DBPager('block', 'Block_Item');
    $pager->setModule('block');
    $pager->setTemplate('list.tpl');
    $pager->setLink($link);
    $pager->addToggle('class="toggle1"');
    $pager->addToggle('class="toggle2"');
    $pager->addTags($pageTags);
    $pager->setMethod('content', 'summarize');
    $pager->addRowTag('action', 'Block_Admin', '_getListAction');
    
    $content = $pager->get();

    return $content;
  }

  function storeBlock(&$block)
  {
    $_SESSION['Stored_Blocks'][$block->getID()] = $block;
  }
  
  function pinBlock()
  {
    $block_id = (int)$_GET['block_id'];

    unset($_SESSION['Stored_Blocks'][$block_id]);

    $values['block_id'] = $block_id;
    $values['module']   = $_GET['mod'];
    $values['item_id']  = $_GET['item'];
    $values['itemname'] = $_GET['itname'];

    $db = & new PHPWS_DB('block_pinned');
    $db->addWhere($values);
    $result = $db->delete();
    $db->addValue($values);
    $result = $db->insert();
  }
  
  function copyBlock(&$block)
  {
    Clipboard::copy($block->getTitle(), $block->getTag());
  }
}
?>