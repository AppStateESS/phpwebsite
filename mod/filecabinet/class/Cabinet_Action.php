<?php

define("DEFAULT_CABINET_LIST", "image");

class Cabinet_Action {

  function admin(){
    $content = array();
    $panel = & Cabinet_Action::cpanel();

    if (isset($_REQUEST['action']))
      $action = $_REQUEST['action'];
    else
      $action = "main";

    switch ($action){
    case "main":
      if (isset($_REQUEST['tab']))
	$content[] = Cabinet_Action::manager($_REQUEST['tab']);
      else
	$content[] = Cabinet_Action::manager(DEFAULT_CABINET_LIST);
      break;

    case "edit":
      break;

    case "delete":

      break;
    }

    $panel->setContent(implode("", $content));
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function &cpanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $imageLink = "index.php?module=filecabinet&amp;action=main";
    $imageCommand = array ("title"=>_("Images"), "link"=> $imageLink);
	
    $documentLink = "index.php?module=filecabinet&amp;action=main";
    $documentCommand = array ("title"=>_("Documents"), "link"=> $documentLink);

    $tabs['image'] = $imageCommand;
    $tabs['document'] = $documentCommand;

    $panel = & new PHPWS_Panel("filecabinet");
    $panel->quickSetTabs($tabs);

    $panel->setModule("filecabinet");
    //$panel->setPanel("panel.tpl");
    return $panel;
  }

  function listAction($image){
    $links[] = "<a href=\"index.php?action=editImage&amp;id=" . $image->getId() . "\">Edit</a>";
    $links[] = "<a href=\"index.php?action=deleteImage&amp;id=" . $image->getId() . "\">Delete</a>";
    return implode(" | ", $links);
  }

  function manager($type){
    PHPWS_Core::initCoreClass("DBPager.php");
    PHPWS_Core::initCoreClass("Image.php");

    if ($type == "image"){
      $pager = & new DBPager("images", "PHPWS_Image");
      $pager->setModule("filecabinet");
      $pager->setTemplate("imageList.tpl");
      $pager->setLink("index.php?module=filecabinet&amp;action=main&amp;tab=image");

      $pager->setMethod("title", "getJSView");
      $pager->addRowTag("action", "Cabinet_Action", "listAction");

      $pager->addToggle("class=\"toggle1\"");
      $pager->addToggle("class=\"toggle2\"");
      $pager->addToggle("class=\"toggle3\"");

      $tags['PAGE_LABEL'] = _("Page");
      $tags['TITLE']      = _("Title");
      $tags['FILENAME']   = _("Filename");
      $tags['MODULE']     = _("Module");
      $tags['SIZE']       = _("Size");
      $tags['ACTION']     = _("Action");

      $pager->addTags($tags);

      $result = $pager->get();

      if (empty($result))
	return _("No items found.");

      return $result;
    }

  }

}


?>