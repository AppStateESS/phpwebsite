<?php

class Blog_Admin {

  function main(){
    if (!Current_User::allow('blog')){
      Current_User::disallow(_('User attempted access to Blog administration.'));
      return;
    }

    $previous_version = $title = $message = $content = NULL;
    $panel = & Blog_Admin::cpanel();
    PHPWS_Core::initModClass('version', 'Version.php');

    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = $panel->getCurrentTab();

    if (isset($_REQUEST['blog_id'])) {
      $blog = & new Blog((int)$_REQUEST['blog_id']);
    } else {
      $blog = & new Blog();
    }

    switch ($command){
    case 'disapproveBlog':
      if (!Current_User::authorized('blog') ||
	  Current_User::isRestricted('blog')||
	  !isset($_REQUEST['version_id'])) {
	$title = _('Sorry');
	$content = _('Action not allowed.');
	break;
      }

      $result = Blog_Admin::disapprove($_REQUEST['version_id']);
      if (PEAR::isError($result)) {
	$message = _('There was a problem removing the approval item.');
      }
      else {
	$message = _('Blog removed from approval queue.');
      }

      $title = _('Blog Entries Awaiting Approval');
      $content = Blog_Admin::approvalList();

      break;

    case 'edit':
      $panel->setCurrentTab('list');;
      if (!Current_User::authorized('blog', 'edit_blog', $_REQUEST['blog_id'])){
	Current_User::disallow(_('User tried to edit a blog.'));
	return;
      }

      $title = _('Update Blog Entry');

      $version = & new Version('blog_entries');
      $version->setSource($blog);
      $approval_id = $version->isWaitingApproval();

      if (isset($approval_id)) {
	$version->setId($approval_id);
	$version->init();

	$unapproved_blog = & new Blog;
	$version->loadObject($unapproved_blog);

	if (Current_User::isRestricted('blog')) {
	  $message = _('This version has not been approved.');
	  $content = Blog_Admin::edit($unapproved_blog, $version->getId());
	} else {
	  $link = _('A version of this entry is awaiting approval.');
	  $linkVar['action']     = 'admin';
	  $linkVar['command']    = 'editUnapproved';
	  $linkVar['version_id'] = $approval_id;
	  $message = PHPWS_Text::secureLink($link, 'blog', $linkVar);
	  $content = Blog_Admin::edit($blog);
	}
	
      } else {
	$content = Blog_Admin::edit($blog);
      }

      break;

    case 'approval':
      $title = _('Blog Entries Awaiting Approval');
      $content = Blog_Admin::approvalList();
      break;

    case 'approveBlog':
      if (!Current_User::authorized('blog') ||
	  Current_User::isRestricted('blog')      ||
	  !isset($_REQUEST['version_id'])) {
	$title = _('Sorry');
	$content = _('Action not allowed.');
	break;
      }

      Blog_Admin::approveBlog($_REQUEST['version_id']);
      $title = _('Blog Entries Awaiting Approval');
      $message = _('Blog entry approved.');
      $content = Blog_Admin::approvalList();
      break;


    case 'editUnapproved':
      if (!Current_User::authorized('blog', 'edit_blog')){
	Current_User::disallow(_('Tried to edit an unapproved item.'));
	return;
      }

      $version = & new Version('blog_entries', $_REQUEST['version_id']);
      $version->loadObject($blog);

      $title = _('Update Unapproved Blog Entry');
      $content = Blog_Admin::edit($blog, $_REQUEST['version_id']);
      break;

    case 'new':
      $title = _('New Blog Entry');
      $content = Blog_Admin::edit($blog);
      break;

    case 'delete':
      $title = _('Blog Archive');
      $message = _('Blog entry deleted.');
      $blog->kill();
      $content = Blog_Admin::entry_list();
      break;

    case 'list':
      $title = _('Blog Archive');
      $content = Blog_Admin::entry_list();
      break;

    case 'restore':
      $title = _('Blog Restore') . ' : ' . $blog->getTitle();
      $content = Blog_Admin::restoreVersionList($blog);
      break;

    case 'restorePrevBlog':
      if (Current_User::isRestricted('blog') || !Current_User::authorized('blog')) {
	Current_User::disallow();
	return;
      }
	
      Blog_Admin::restoreBlog($_REQUEST['version_id']);
      $title = _('Blog Archive');
      $message = _('Blog entry restored.');
      $content = Blog_Admin::entry_list();
      break;

    case 'removePrevBlog':
      if (!Current_User::isDeity()) {
	Current_User::disallow();
	return;
      }
      
      Blog_Admin::removePrevBlog($_REQUEST['version_id']);
      $title = _('Blog Restore');
      $message = _('Blog entry removed.');
      $content = Blog_Admin::restoreVersionList($blog);
      break;

    case 'postEntry':
      $title = _('Blog Archive');

      $panel->setCurrentTab('list');
      /*
      if (PHPWS_Core::isPosted()) {
	$message = _('Ignoring repeat post.');
	$content = Blog_Admin::entry_list();
	break;
      }
      */
      $result = Blog_Admin::postEntry($blog);

      if ($result == FALSE) {
	$message = _('An error occurred when trying to save your entry.');
      } elseif (is_array($result)) {
	$message = implode('<br />', $result);
	if (empty($blog->id)) {
	  $panel->setCurrentTab('new');
	}
	$content = Blog_Admin::edit($blog);
	break;
      } else {
	$message = $result;
      }

      $content = Blog_Admin::entry_list();
      break;
    }

    $template['TITLE']   = $title;
    $template['MESSAGE'] = $message;
    $template['CONTENT'] = $content;
    $final = PHPWS_Template::process($template, 'blog', 'main.tpl');

    $panel->setContent($final);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));

  }

  function &cpanel()
  {
    PHPWS_Core::initModClass('controlpanel', 'Panel.php');
    $newLink = 'index.php?module=blog&amp;action=admin';
    $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
	
    $listLink = 'index.php?module=blog&amp;action=admin';
    $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

    $approvalLink = 'index.php?module=blog&amp;action=admin';
    $approvalCommand = array ('title'=>_('Approval'), 'link'=> $approvalLink);

    $tabs['new'] = $newCommand;

    if (Current_User::allow('blog', 'edit_blog')) {
      $tabs['list'] = $listCommand;
      $tabs['approval'] = $approvalCommand;
    }

    $panel = & new PHPWS_Panel('categories');
    $panel->quickSetTabs($tabs);

    $panel->setModule('blog');
    $panel->setPanel('panel.tpl');
    return $panel;
  }

  function edit(&$blog, $version_id=NULL)
  {
    PHPWS_Core::initCoreClass('Editor.php');
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    $form = & new PHPWS_Form;
    $form->addHidden('module', 'blog');
    $form->addHidden('action', 'admin');
    $form->addHidden('command', 'postEntry');

    $cat_item = & new Category_Item('blog');
    $cat_item->setItemId($blog->id);
    
    if (isset($version_id)) {
      $form->addHidden('version_id', $version_id);
      $cat_item->setVersionId($version_id);
      if (Current_User::isUnrestricted('blog')) {
	$form->addSubmit('approve_entry', _('Save Changes and Approve'));
      }
    }

    if (isset($blog->id) || isset($version_id)){
      $form->addHidden('blog_id', $blog->id);
      $form->addSubmit('submit', _('Update Entry'));
    } else
      $form->addSubmit('submit', _('Add Entry'));

    if (Editor::willWork()){
      $editor = & new Editor('entry', $blog->getEntry(TRUE));
      $entry = $editor->get();
      $form->addTplTag('ENTRY', $entry);
      $form->addTplTag('ENTRY_LABEL', PHPWS_Form::makeLabel('entry',_('Entry')));
    } else {
      $form->addTextArea('entry', $blog->getEntry(TRUE));
      $form->setRows('entry', '10');
      $form->setWidth('entry', '80%');
      $form->setLabel('entry', _('Entry'));
    }

    $form->addText('title', $blog->title);
    $form->setSize('title', 40);
    $form->setLabel('title', _('Title'));

    if (Current_User::isUnrestricted('blog') && empty($version_id)) {
      $viewable_opts[] = 0;
      $viewable_opts[] = 1;
      $viewable_opts[] = 2;
      $viewable_label[] = _('Unrestricted');
      $viewable_label[] = _('Only logged in users');
      $viewable_label[] = _('Only certain groups');
      
      $form->addRadio('viewable', $viewable_opts);
      $form->setLabel('viewable', $viewable_label);
      $form->setMatch('viewable', $blog->getRestricted());
    }

    $template = $form->getTemplate();

    $template['CATEGORIES_LABEL']    = _('Category');
    $template['CATEGORIES']          = $cat_item->getForm();
    $template['VIEWABLE_MAIN_LABEL'] = _('Viewing Permissions');

    if (Current_User::isUnrestricted('blog') && empty($version_id)){
      $assign = PHPWS_User::assignPermissions('blog', $blog->getId());
      $template = array_merge($assign, $template);
    }

    return PHPWS_Template::process($template, 'blog', 'edit.tpl');
  }

  function getListAction(&$blog){
    $link['action'] = 'admin';
    $link['blog_id'] = $blog->getId();

    if (Current_User::allow('blog', 'edit_blog', $blog->getId())){
      $link['command'] = 'edit';
      $list[] = PHPWS_Text::secureLink(_('Edit'), 'blog', $link);
    }
    
    if (Current_User::allow('blog', 'delete_blog')){
      $link['command'] = 'delete';
      $confirm_vars['QUESTION'] = _('Are you sure you want to permanently delete this blog entry?');
      $confirm_vars['ADDRESS'] = PHPWS_Text::linkAddress('blog', $link, TRUE);
      $confirm_vars['LINK'] = _('Delete');
      $list[] = Layout::getJavascript('confirm', $confirm_vars);
    }

    if (Current_User::isUnrestricted('blog')){
      $link['command'] = 'restore';
      $list[] = PHPWS_Text::secureLink(_('Restore'), 'blog', $link);
    }

    if (isset($list))
      return implode(' | ', $list);
    else
      return _('No action');
  }

  function getListEntry(&$blog){
    return substr(strip_tags($blog->getEntry(TRUE)), 0, 30) . ' . . .';
  }

  function entry_list(){
    PHPWS_Core::initCoreClass('DBPager.php');

    $pageTags['TITLE']  = _('Title');
    $pageTags['ENTRY']  = _('Entry');
    $pageTags['DATE']   = _('Creation Date');
    $pageTags['ACTION'] = _('Action');

    $pager = & new DBPager('blog_entries', 'Blog');
    $pager->setModule('blog');
    $pager->setTemplate('list.tpl');
    $pager->setLink('index.php?module=blog&amp;action=admin&amp;authkey=' . Current_User::getAuthKey());
    $pager->addToggle('class="toggle1"');
    $pager->addToggle('class="toggle2"');
    $pager->setMethod('date', 'getFormatedDate');
    $pager->addTags($pageTags);
    $pager->setSearch('title');
    $pager->addRowTag('entry', 'Blog_Admin', 'getListEntry');
    $pager->addRowTag('action', 'Blog_Admin', 'getListAction');
    $content = $pager->get();
    return $content;
  }

  function postEntry(&$blog){
    if (!Current_User::authorized('blog', 'edit_blog')) {
      Current_User::disallow();
      return FALSE;
    }

    if (empty($_POST['title'])) {
      return array(_('Missing title.'));
    } else {
      $blog->title = strip_tags($_POST['title']);
    }

    $blog->setEntry($_POST['entry']);

    $blog->restricted = (int)$_POST['viewable'];

    if (isset($_REQUEST['version_id'])) {
      $version = & new Version('blog_entries', $_REQUEST['version_id']);
    }
    else {
      $version = & new Version('blog_entries');
    }

    if (Current_User::isRestricted('blog')) {
      $result = Blog_Admin::saveVersion($blog, $version, FALSE);
      
      if (PEAR::isError($result)) {
	PHPWS_Error::log($result);
	return FALSE;
      }
      else {
	return _('Blog entry submitted for approval');
      }
    }
    else {
      // User is unrestricted
      if ((bool)$version->getId()){
	if (isset($_POST['approve_entry'])){
	  $result = $blog->save();
	  if (PEAR::isError($result)) {
	    PHPWS_Error::log($result);
	    return FALSE;
	  }

	  $result = Blog_Admin::saveVersion($blog, $version, TRUE);
	  $version->authorizeCreator('blog');
	  
	  if (PEAR::isError($result)) {
	    PHPWS_Error::log($result);
	    return FALSE;
	  }
	  return _('Blog updated and approved.');
	} else {
	  $result = Blog_Admin::saveVersion($blog, $version, FALSE);
	  if (PEAR::isError($result)) {
	    PHPWS_Error::log($result);
	    return FALSE;
	  }
	  return _('Unapproved blog updated.');
	}
      } else {
	$blog->save();
	$result = Blog_Admin::saveVersion($blog, $version, TRUE);
	if (PEAR::isError($result)) {
	  PHPWS_Error::log($result);
	  return FALSE;
	}
	$key = $blog->getKey();
	PHPWS_User::savePermissions($key);
	return _('Blog entry saved.');
      }
    }
  }

  function _loadCategory(&$cat_item, &$blog, $version=NULL)
  {
    $cat_item->setItemId($blog->getId());
    $cat_item->setTitle($blog->getTitle() . ' - ' . $blog->getFormatedDate());
    if (MOD_REWRITE_ENABLED) {
      $link = 'blog/view/' . $blog->getId();
    } else {
      $link = 'index.php?module=blog&amp;action=view&amp;id=' . $blog->getId();
    }
    
    $cat_item->setLink($link);

    if (isset($version)) {
      $cat_item->setVersionId($version->getId());
    }
  }


  function saveVersion(&$blog, &$version, $approved)
  {
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    if (empty($blog->date)) {
      $blog->date = mktime();
    }

    $version->setSource($blog);
    $version->setApproved($approved);
    $result = $version->save();

    if (PEAR::isError($result)) {
      return $result;
    }

    $cat_item = & new Category_Item('blog');
    $cat_item->setApproved($approved);
    Blog_Admin::_loadCategory($cat_item, $blog, $version);
    $cat_item->savePost(TRUE);
  }
  
  function restoreVersionList(&$blog)
  {
    $version = & new Version('blog_entries');
    $version->setSource($blog);
    $version_list = $version->getBackupList();

    $count = 0;

    $vars['action'] = 'admin';
    if (empty($version_list)) {
      $tpl['INSTRUCTION'] = _('No backups of this blog entry exist.');
      return PHPWS_Template::processTemplate($tpl, 'blog', 'version.tpl');
    }
    foreach ($version_list as $backup_id => $backup){
      $count++;
      if ($count%2)
	$template['TOGGLE'] = 'class="toggle1"';
      else
	$template['TOGGLE'] = 'class="toggle2"';

      $blog = & new Blog;
      $backup->loadObject($blog);

      $vars['version_id'] = $backup->getId();
      $template['CREATED'] = $backup->getCreationDate(TRUE);
      $template['BLOG'] = $blog->view(FALSE);

      $vars['command'] = 'restorePrevBlog';
      $template['RESTORE_LINK'] = PHPWS_Text::secureLink(_('Restore'), 'blog', $vars);

      if (Current_User::isDeity()) {
	$vars['command'] = 'removePrevBlog';
	$vars['blog_id'] = $blog->getId();
	$confirm['QUESTION'] = _('Are you sure you want to purge this backup copy?');
	$confirm['ADDRESS'] = PHPWS_Text::linkAddress('blog', $vars, TRUE);
	$confirm['LINK'] = _('Remove');
	$template['REMOVE_LINK'] = Layout::getJavascript('confirm', $confirm);
      }
      $tpl['repeat_row'][] = $template;
    }
    $tpl['INSTRUCTION'] = _('Choose the blog entry you want to restore.');
    return PHPWS_Template::processTemplate($tpl, 'blog', 'version.tpl');
  }
  
  function approvalList(){
    $version = & new Version('blog_entries');

    $approvalList = $version->getUnapproved(Current_User::isRestricted('blog'));

    if (empty($approvalList))
      return _('No entries awaiting approval.');

    $tpl = & new PHPWS_Template('blog');
    $tpl->setFile('approval_list.tpl');

    foreach ($approvalList as $vr_blog){
      $mini_tpl = NULL;
      $blog = & new Blog;
      $vr_blog->loadObject($blog);

      $mini_tpl['ENTRY']     = $blog->view(FALSE, FALSE);
      $linkVar['action']     = 'admin';
      $linkVar['version_id'] = $vr_blog->getVersionId();

      $linkVar['command'] = 'editUnapproved';
      $links[0] = PHPWS_Text::secureLink(_('Edit'), 'blog', $linkVar);

      if (Current_User::isUnrestricted('blog')) {
	$linkVar['command'] = 'approveBlog';
	$links[1] = PHPWS_Text::secureLink(_('Approve'), 'blog', $linkVar);
      }

      $linkVar['command'] = 'disapproveBlog';
      $links[2] = PHPWS_Text::secureLink(_('Remove'), 'blog', $linkVar);

      $mini_tpl['BLOG_LINKS'] = implode(' | ', $links);
      $creator = & new PHPWS_User($vr_blog->getCreator());
      $mini_tpl['CREATOR_LABEL'] = _('Creator');
      $mini_tpl['CREATOR'] = $creator->getEmail(TRUE);

      $editor_id = $vr_blog->getEditor();

      if (!empty($editor_id)) {
	$editor = & new PHPWS_User($editor_id);
	$mini_tpl['EDITOR_LABEL'] = _('Editor');
	$mini_tpl['EDITOR'] = $editor->getEmail(TRUE);
      }

      if ($vr_blog->getSourceId() > 0) {
	$edit_approves = TRUE;
	$mini_tpl['DATE'] = $vr_blog->getEditedDate(TRUE);
	$mini_tpl['DATE_LABEL'] = _('Updated');
	$tpl->setCurrentBlock('update-approval');
      } else {
	$new_approves = TRUE;
	$mini_tpl['DATE'] = $vr_blog->getCreationDate(TRUE);
	$mini_tpl['DATE_LABEL'] = _('Created');
	$tpl->setCurrentBlock('new-approval');
      }
      $tpl->setData($mini_tpl);
      $tpl->parseCurrentBlock();
    }

    if (isset($new_approves))
      $template['NEW_LABEL'] = _('New Blog Entries');

    if (isset($edit_approves))
      $template['UPDATED_LABEL'] = _('Updated Blog Entries');

    $tpl->setData($template);

    $content = $tpl->get();
    return $content;
  }

  function disapprove($version_id){
    $version = & new Version('blog_entries', $version_id);
    return $version->kill();
  }

  function approveBlog($version_id){
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    $version = & new Version('blog_entries', $version_id);
    $blog = & new Blog;
    $version->loadObject($blog);
    $blog->save();

    $cat_item = & new Category_Item('blog');
    Blog_Admin::_loadCategory($cat_item, $blog, $version);
    $cat_item->setApproved(TRUE);
    $cat_item->saveVersion();

    $version->setSourceId($blog->id);
    $version->setApproved(TRUE);
    $version->save();
    $version->authorizeCreator('blog');
  }

  function restoreBlog($version_id) {
    $version = & new Version('blog_entries', $version_id);
    $version->restore();
  }

  function removePrevBlog($version_id){
    $version = & new Version('blog_entries', $version_id);
    $version->kill();
  }
}

?>