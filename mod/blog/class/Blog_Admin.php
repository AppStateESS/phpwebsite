<?php

class Blog_Admin {

  function main(){
    if (!Current_User::allow("blog")){
      Current_User::disallow(_("User attempted access to Blog administration."));
      return;
    }

    $previous_version = $title = $message = $content = NULL;
    $panel = & Blog_Admin::cpanel();
    PHPWS_Core::initModClass("version", "Version.php");

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
    case "edit":
      $panel->setCurrentTab("list");;
      if (!Current_User::authorized("blog", "edit_blog", $_REQUEST['blog_id'])){
	Current_User::disallow(_("User tried to edit a blog."));
	return;
      }

      $title = _("Update Blog Entry");

      $version = & new Version("blog", "blog_entries");
      $version->setSource($blog);
      $approval_id = $version->isWaitingApproval();

      if (isset($approval_id)) {
	$version->setId($approval_id);
	$version->init();

	if (PEAR::isError($unApproved)){
	  $content = _("An error occurred while loading an unapproved blog.");
	  break;
	}

	if (Current_User::isRestricted("blog")) {
	  $message = _("This version has not been approved.");
	  $content = Blog_Admin::edit($unApproved);
	} else {
	  $link = _("A version of this entry is awaiting approval.");
	  $linkVar['action']     = "admin";
	  $linkVar['command']    = "editUnapproved";
	  $linkVar['version_id'] = $version_id;
	  $message = PHPWS_Text::secureLink($link, "blog", $linkVar);
	  $content = Blog_Admin::edit($blog);
	}
	
      } else {
	$content = Blog_Admin::edit($blog);
      }

      break;

    case "approval":
      $title = _("Blog Entries Awaiting Approval");
      $content = Blog_Admin::approvalList();
      break;

    case "approveBlog":
      if (Current_User::isRestricted("blog")) {
	Current_User::disallow(_("Tried to approve a blog."));
	return;
      }

      Version::approve("blog_entries", $_REQUEST['version_id']);
      $title = _("Blog Entries Awaiting Approval");
      $message = _("Blog entry approved.");
      $content = Blog_Admin::approvalList();
      break;


    case "editUnapproved":
      if (!Current_User::authorized("blog", "edit_blog")){
	Current_User::disallow(_("Tried to edit an unapproved item."));
	return;
      }

      $version = & new Version("blog", "blog_entries", $_REQUEST['version_id']);
      $version->loadObject($blog);

      $title = _("Update Unapproved Blog Entry");
      $content = Blog_Admin::edit($blog, $_REQUEST['version_id']);
      break;

    case "new":
      $title = _("New Blog Entry");
      $content = Blog_Admin::edit($blog);
      break;

    case "delete":
      $title = _("Blog Archive");
      $message = _("Blog entry deleted.");
      $blog->kill();
      $content = Blog_Admin::entry_list();
      break;

    case "list":
      $title = _("Blog Archive");
      $content = Blog_Admin::entry_list();
      break;

    case "restore":
      $title = _("Blog Restore") . " : " . $blog->getTitle();
      $content = Blog_Admin::restoreVersion($blog);
      break;

    case "restorePrevBlog":
      $result = Version::restore($blog, $_REQUEST['replace_order'], "blog_entries");
      $blog->save();
      $title = _("Blog Archive");
      $message = _("Blog entry restored.");
      $content = Blog_Admin::entry_list();
      break;

    case "postEntry":
      $panel->setCurrentTab("list");;
      Blog_Admin::postEntry($blog);

      if (isset($_REQUEST['version_id']))
	$version = & new Version("blog", "blog_entries", $_REQUEST['version_id']);
      else
	$version = & new Version("blog", "blog_entries");

      if (Current_User::isRestricted("blog")){
	$version->setSource($blog);
	$version->setApproved(FALSE);
	$result = $version->save();

	if (PEAR::isError($result)) {
	  PHPWS_Error::log($result);
	  $message = _("An error occurred when trying to save your entry.");
	}
	else {
	  $message = _("Blog entry submitted for approval");
	}
      }
      else {
	// User is unrestricted
	if (isset($version_id)){
	  if (isset($_POST['approve_entry'])) {
	    $message = _("Blog approved.");
	    Version::approve("blog_entries", $version_id);
	    $result = $blog->save();
	    Version::givePermission("blog", "blog_entries", $version_id);
	  }
	  else {
	    $message = _("Unapproved blog updated.");
	    Version::saveUnapproved("blog_entries", $blog, $version_id);
	  }
	} else {
	  $message = _("Blog entry updated.");
	  $result = $blog->save();
	  PHPWS_User::savePermissions("blog", $blog->getId());
	  $version->setSource($blog);
	  $version->setApproved(TRUE);
	  $version->save();
	}
      }

      $title = _("Blog Archive");
      $content = Blog_Admin::entry_list();
      break;
    }

    $template['TITLE']   = $title;
    $template['MESSAGE'] = $message;
    $template['CONTENT'] = $content;
    $final = PHPWS_Template::process($template, "blog", "main.tpl");

    $panel->setContent($final);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));

  }


  function &cpanel()
  {
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $newLink = "index.php?module=blog&amp;action=admin";
    $newCommand = array ("title"=>_("New"), "link"=> $newLink);
	
    $listLink = "index.php?module=blog&amp;action=admin";
    $listCommand = array ("title"=>_("Archive"), "link"=> $listLink);

    $approvalLink = "index.php?module=blog&amp;action=admin";
    $approvalCommand = array ("title"=>_("Approval"), "link"=> $approvalLink);

    $tabs['new'] = $newCommand;

    if (Current_User::allow("blog", "edit_blog")) {
      $tabs['list'] = $listCommand;
      $tabs['approval'] = $approvalCommand;
    }

    $panel = & new PHPWS_Panel("categories");
    $panel->quickSetTabs($tabs);

    $panel->setModule("blog");
    $panel->setPanel("panel.tpl");
    return $panel;
  }

  function edit(&$blog, $version_id=NULL){
    PHPWS_Core::initCoreClass("Editor.php");
    $form = & new PHPWS_Form;
    $form->addHidden("module", "blog");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postEntry");

    if (isset($version_id)) {
      $form->addHidden("version_id", $version_id);
	
      if (Current_User::isUnrestricted("blog")) {
	$form->addSubmit("approve_entry", _("Save Changes and Approve"));
      }
    }

    if (isset($blog->id) || isset($version_id)){
      $form->addHidden("blog_id", $blog->id);
      $form->addSubmit("submit", _("Update Entry"));
    } else
      $form->addSubmit("submit", _("Add Entry"));

    if (Editor::willWork()){
      $editor = & new Editor("htmlarea", "entry", PHPWS_Text::parseOutput($blog->getEntry(), FALSE, FALSE));
      $entry = $editor->get();
      $form->addTplTag("ENTRY", $entry);
      $form->addTplTag("ENTRY_LABEL", PHPWS_Form::makeLabel("entry",_("Entry")));
    } else {
      $form->addTextArea("entry", PHPWS_Text::parseOutput($blog->getEntry(), FALSE, FALSE, FALSE));
      $form->setRows("entry", "10");
      $form->setWidth("entry", "80%");
      $form->setLabel("entry", _("Entry"));
    }

    $form->addText("title", $blog->title);
    $form->setSize("title", 40);
    $form->setLabel("title", _("Title"));

    $template = $form->getTemplate();

    if (Current_User::isUnrestricted("blog") && empty($version_id)){
      $assign = PHPWS_User::assignPermissions("blog", $blog->getId());
      $template = array_merge($assign, $template);
    }

    return PHPWS_Template::process($template, "blog", "edit.tpl");
  }

  function getListAction(&$blog){
    $link['action'] = "admin";
    $link['blog_id'] = $blog->getId();

    if (Current_User::allow("blog", "edit_blog", $blog->getId())){
      $link['command'] = "edit";
      $list[] = PHPWS_Text::secureLink(_("Edit"), "blog", $link);
    }
    
    if (Current_User::allow("blog", "delete_blog")){
      $link['command'] = "delete";
      $list[] = PHPWS_Text::secureLink(_("Delete"), "blog", $link);
    }

    if (Current_User::isUnrestricted("blog")){
      $link['command'] = "restore";
      $list[] = PHPWS_Text::secureLink(_("Restore"), "blog", $link);
    }

    if (isset($list))
      return implode(" | ", $list);
    else
      return _("No action");
  }

  function getListEntry(&$blog){
    return substr(strip_tags(PHPWS_Text::parseOutput($blog->entry)), 0, 30) . " . . .";
  }

  function entry_list(){
    PHPWS_Core::initCoreClass("DBPager.php");

    $pageTags['TITLE']  = _("Title");
    $pageTags['ENTRY']  = _("Entry");
    $pageTags['DATE']   = _("Creation Date");
    $pageTags['ACTION'] = _("Action");

    $pager = & new DBPager("blog_entries", "Blog");
    $pager->setModule("blog");
    $pager->setTemplate("list.tpl");
    $pager->setLink("index.php?module=blog&amp;action=admin&amp;authkey=" . Current_User::getAuthKey());
    $pager->addToggle("class=\"toggle1\"");
    $pager->addToggle("class=\"toggle2\"");
    $pager->setMethod("date", "getFormatedDate");
    $pager->addTags($pageTags);
    $pager->setSearch("title");
    $pager->addRowTag("entry", "Blog_Admin", "getListEntry");
    $pager->addRowTag("action", "Blog_Admin", "getListAction");
    $content = $pager->get();
    if (empty($content))
      return _("No entries made.");
    else
      return $content;
  }

  function postEntry(&$blog){
    $blog->title = PHPWS_Text::parseInput($_POST['title']);
    $blog->entry = PHPWS_Text::parseInput($_POST['entry']);
    $blog->date  = mktime();
    return TRUE;
  }

  
  function restoreVersion(&$blog){
    PHPWS_Core::initCoreClass("Version.php");

    $result = Version::getAll($blog->id, "blog_entries", "blog");

    $tpl = & new PHPWS_Template("blog");
    $tpl->setFile("version.tpl");

    $tpl->setCurrentBlock("repeat_row");

    $count = 0;

    $vars['action'] = "admin";
    $vars['command'] = "restorePrevBlog";
    $vars['blog_id'] = $blog->id;

    foreach ($result as $order=>$oldBlog){
      $count++;
      if ($count%2)
	$template['TOGGLE'] = "class=\"toggle1\"";
      else
	$template['TOGGLE'] = "class=\"toggle2\"";

      $vars['replace_order'] = $order;
      $template['BLOG'] = $oldBlog->view(FALSE);
      $template['RESTORE_LINK'] = PHPWS_Text::secureLink(_("Restore this blog"), "blog", $vars);
      $tpl->setData($template);
      $tpl->parseCurrentBlock();
    }

    $tpl->setData(array("INSTRUCTION"=>_("Choose the blog entry you want to restore.")));
    return $tpl->get();
  }
  
  function approvalList(){
    $version = & new Version("blog", "blog_entries");

    $approvalList = $version->getUnapproved(Current_User::isRestricted("blog"));

    if (empty($approvalList))
      return _("No entries awaiting approval.");

    $tpl = & new PHPWS_Template("blog");
    $tpl->setFile("approval_list.tpl");

    foreach ($approvalList as $vr_blog){
      $mini_tpl = NULL;
      $blog = & new Blog;
      $vr_blog->loadObject($blog);

      $mini_tpl['ENTRY']     = $blog->view(FALSE);
      $linkVar['action']     = "admin";
      $linkVar['version_id'] = $vr_blog->getVersionId();

      $linkVar['command'] = "editUnapproved";
      $links[0] = PHPWS_Text::secureLink(_("Edit"), "blog", $linkVar);

      if (Current_User::isUnrestricted("blog")) {
	$linkVar['command'] = "approveBlog";
	$links[1] = PHPWS_Text::secureLink(_("Approve"), "blog", $linkVar);
      }

      $linkVar['command'] = "disapproveBlog";
      $links[2] = PHPWS_Text::secureLink(_("Remove"), "blog", $linkVar);

      $mini_tpl['BLOG_LINKS'] = implode(" | ", $links);
      $creator = & new PHPWS_User($vr_blog->getCreator());
      $mini_tpl['CREATOR_LABEL'] = _("Creator");
      $mini_tpl['CREATOR'] = $creator->getEmail(TRUE);

      $editor_id = $vr_blog->getEditor();

      if ($editor_id) {
	$editor = & new PHPWS_User($editor_id);
	$mini_tpl['EDITOR_LABEL'] = _("Editor");
	$mini_tpl['EDITOR'] = $editor->getEmail(TRUE);
      }

      if ($vr_blog->getSourceId() > 0) {
	$mini_tpl['DATE'] = $vr_blog->getEdittedDate(TRUE);
	$mini_tpl['DATE_LABEL'] = _("Updated");
	$tpl->setCurrentBlock("update-approval");
      } else {
	$mini_tpl['DATE'] = $vr_blog->getCreationDate(TRUE);
	$mini_tpl['DATE_LABEL'] = _("Created");
	$tpl->setCurrentBlock("new-approval");
      }

      $tpl->setData($mini_tpl);
      $tpl->parseCurrentBlock();
    }

    $template['NEW_LABEL'] = _("New Blog Entries");
    $template['UPDATED_LABEL'] = _("Updated Blog Entries");
    $tpl->setData($template);

    $content = $tpl->get();
    return $content;
  }

}

?>