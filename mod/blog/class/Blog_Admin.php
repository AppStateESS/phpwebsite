<?php

class Blog_Admin {

  function main(){
    $title = $message = $content = NULL;
    $panel = & Blog_Admin::cpanel();

    if (isset($_REQUEST['blog_id']))
      $blog = & new Blog((int)$_REQUEST['blog_id']);
    else
      $blog = & new Blog;

    if (isset($_REQUEST['command']))
      $command = $_REQUEST['command'];
    else
      $command = $panel->getCurrentTab();

    switch ($command){
    case "edit":
      $panel->setCurrentTab("list");;
      $title = _("Update Blog Entry");
      $content = Blog_Admin::edit($blog);
      break;

    case "new":
      $title = _("New Blog Entry");
      $content = Blog_Admin::edit($blog);
      break;

    case "delete":
      $title = _("Blog Archive");
      $message = _("Blog entry Deleted");
      $blog->kill();
      $content = Blog_Admin::entry_list();
      break;

    case "list":
      $title = _("Blog Archive");
      $content = Blog_Admin::entry_list();
      break;

    case "restore":
      $title = _("Blog Restore");
      $content = Blog_Admin::restoreBackup($blog);
      break;

    case "postEntry":
      $panel->setCurrentTab("list");;
      Blog_Admin::postEntry($blog);
      $blog->save();
      PHPWS_User::savePermissions("blog", $blog->getId());
      $title = _("Blog Archive");
      $message = _("Blog entry updated.");
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

  function &cpanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $newLink = "index.php?module=blog&amp;action=admin";
    $newCommand = array ("title"=>_("New"), "link"=> $newLink);
	
    $listLink = "index.php?module=blog&amp;action=admin";
    $listCommand = array ("title"=>_("Archive"), "link"=> $listLink);

    $tabs['new'] = $newCommand;
    $tabs['list'] = $listCommand;

    $panel = & new PHPWS_Panel("categories");
    $panel->quickSetTabs($tabs);

    $panel->setModule("blog");
    $panel->setPanel("panel.tpl");
    return $panel;
  }

  function edit(&$blog){
    PHPWS_Core::initCoreClass("Editor.php");
    $form = & new PHPWS_Form;
    $form->addHidden("module", "blog");
    $form->addHidden("action", "admin");
    $form->addHidden("command", "postEntry");

    if (isset($blog->id)){
      $form->addHidden("blog_id", $blog->id);
      $submit = _("Update Entry");
    } else
      $submit = _("Add Entry");

    if (Editor::willWork()){
      $editor = & new Editor("htmlarea", "entry", $blog->getEntry(TRUE));
      $entry = $editor->get();
      $form->addTplTag("ENTRY", $entry);
      $form->addTplTag("ENTRY_LABEL", PHPWS_Form::makeLabel("entry",_("Entry")));
    } else {
      $form->addTextArea("entry", $blog->getEntry(TRUE));
      $form->setRows("entry", "10");
      $form->setWidth("entry", "80%");
      $form->setLabel("entry", _("Entry"));
    }

    $form->addText("title", $blog->title);
    $form->setSize("title", 40);
    $form->setLabel("title", _("Title"));

    $form->addSubmit("submit", $submit);

    $template = $form->getTemplate();
    $assign = PHPWS_User::assignPermissions("blog", $blog->getId());
    $template = array_merge($assign, $template);

    return PHPWS_Template::process($template, "blog", "edit.tpl");
  }

  function getListAction(&$blog){
    $link['action'] = "admin";
    $link['blog_id'] = $blog->getId();

    $link['command'] = "edit";
    $list[] = PHPWS_Text::secureLink(_("Edit"), "blog", $link);
    
    $link['command'] = "delete";
    $list[] = PHPWS_Text::secureLink(_("Delete"), "blog", $link);

    $link['command'] = "restore";
    $list[] = PHPWS_Text::secureLink(_("Restore"), "blog", $link);

    return implode(" | ", $list);
  }

  function getListEntry(&$blog){
    return substr(strip_tags(PHPWS_Text::parseOutput($blog->entry)), 0, 30) . " . . .";
  }

  function entry_list(){
    PHPWS_Core::initCoreClass("DBPager.php");

    $pager = & new DBPager("blog_entries", "Blog");
    $pager->setModule("blog");
    $pager->setTemplate("list.tpl");
    $pager->setLink("index.php?module=blog&amp;action=admin&amp;authkey=" . Current_User::getAuthKey());
    $pager->addToggle("class=\"toggle1\"");
    $pager->addToggle("class=\"toggle2\"");
    $pager->setMethod("date", "getFormatedDate");
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
    return TRUE;
  }

  function restoreBackup(&$blog){
    PHPWS_Core::initCoreClass("Backup.php");

    $result = Backup::get($blog->id, "blog_entries");
    tesT($result);
  }
}

?>