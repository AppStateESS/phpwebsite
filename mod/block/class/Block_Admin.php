<?php

PHPWS_Core::initModClass('block', 'Block.php');

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
      $block = & new Block($_REQUEST['block_id']);
    } else {
      $block = & new Block();
    }

    switch ($action) {
    case 'new':
      $title = _('New Block');
      $content = Block_Admin::edit($block);
      break;

    case 'edit':
      $title = ('Edit Block');
      $content = Block_Admin::edit($block);
      break;

    case 'postBlock':
      Block_Admin::postBlock($block);
      $result = $block->save();

      $message = _('Block saved.');
      $title = _('Block list');
      $content = Block_Admin::blockList();
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
      $editor = & new Editor('htmlarea', 'content', 
			     PHPWS_Text::parseOutput($block->getContent(), FALSE, FALSE));
      $block_content = $editor->get();
      $form->addTplTag('CONTENT', $block_content);
      $form->addTplTag('CONTENT_LABEL', PHPWS_Form::makeLabel('content',_('Content')));
    } else {
      $form->addTextArea('entry',
			 PHPWS_Text::parseOutput($blog->getEntry(), FALSE, FALSE, FALSE));
      $form->setRows('content', '10');
      $form->setWidth('content', '80%');
      $form->setLabel('content', _('Entry'));
    }

    $template = $form->getTemplate();

    $content = PHPWS_Template::process($template, 'block', 'edit.tpl');
    return $content;
  }

  function postBlock(&$block)
  {
    $block->setTitle($_POST['title']);
    $block->setContent($_POST['content']);
    $block->setModule('block');
    return TRUE;
  }

  function _getListAction(&$block){
    $vars['action']   = 'edit';
    $vars['block_id'] = $block->getId();

    $links[] = PHPWS_Text::secureLink(_('Edit'), 'block', $vars);

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

    $pager = & new DBPager('block', 'Block');
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
       
}
?>