<?php

define("DEFAULT_CABINET_LIST", "image");

class Cabinet_Action {

  function admin(){
    PHPWS_Core::initCoreClass("Image.php");
    if (!Current_User::allow("filecabinet")){
      Current_User::disallow();
      return;
    }

    $content = $message = $title = NULL;
    $panel = & Cabinet_Action::cpanel();

    if (isset($_REQUEST['tab']))
      $action = $_REQUEST['tab'];
    elseif (isset($_REQUEST['action']))
      $action = $_REQUEST['action'];
    else
      $action = "main";

    switch ($action){
    case "main":
    case "image":
      $title = _("Manage Images");
      $content = Cabinet_Action::manager("image");
      break;

    case "document":
      $title = _("Manage Documents");
      $content = Cabinet_Action::manager("document");
      break;

    case "editImage":
      if (!isset($_REQUEST['image_id'])){
	$title = _("Manage Images");
	$content = Cabinet_Action::manager("image");
	break;
      }
      $image = & new PHPWS_Image((int)$_REQUEST['image_id']);
      $title = _("Edit Image");
      $content = Cabinet_Action::editImage($image);
      break;

    case "delete":
      break;

    case "uploadImage":
      if (!PHPWS_Core::isPosted())
	$result = Cabinet_Action::uploadImage();
      $message = _("Image uploaded!");
      $content = Cabinet_Action::manager($panel->getCurrentTab());
      break;

    default:
      exit($action);
    }
    
    $template['TITLE']   = $title;
    $template['MESSAGE'] = $message;
    $template['CONTENT'] = $content;

    $main = PHPWS_Template::process($template, "filecabinet", "main.tpl");

    $panel->setContent($main);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function &cpanel(){
    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $imageLink = "index.php?module=filecabinet";
    $imageCommand = array ("title"=>_("Images"), "link"=> $imageLink);
	
    $documentLink = "index.php?module=filecabinet";
    $documentCommand = array ("title"=>_("Documents"), "link"=> $documentLink);

    $tabs['image'] = $imageCommand;
    $tabs['document'] = $documentCommand;

    $panel = & new PHPWS_Panel("filecabinet");
    $panel->quickSetTabs($tabs);

    $panel->setModule("filecabinet");
    return $panel;
  }

  function listAction($image){
    $vars['image_id'] = $image->getId();
    $vars['action'] = "editImage";
    $links[] = PHPWS_Text::secureLink(_("Edit"), "filecabinet", $vars);
    $vars['action'] = "deleteImage";
    $links[] = PHPWS_Text::secureLink(_("Delete"), "filecabinet", $vars);
    return implode(" | ", $links);
  }

  function imageManager(){
      $pager = & new DBPager("images", "PHPWS_Image");
      $pager->setModule("filecabinet");
      $pager->setTemplate("imageList.tpl");
      $pager->setLink("index.php?module=filecabinet&amp;action=main&amp;tab=image&amp;authkey=" . Current_User::getAuthKey());

      $pager->setMethod("title", "getJSView");
      $pager->addRowTag("action", "Cabinet_Action", "listAction");

      $pager->addToggle("class=\"toggle1\"");
      $pager->addToggle("class=\"toggle2\"");
      $pager->addToggle("class=\"toggle3\"");

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

  function manager($type){
    PHPWS_Core::initCoreClass("DBPager.php");
    PHPWS_Core::initCoreClass("Image.php");

    $form = & new PHPWS_Form;
    $form->addHidden("module", "filecabinet");
    $form->addFile("upload");

    if ($type == "image"){
      $form->addHidden("action", "uploadImage");
      $form->setTitle("upload", _("Upload Image"));
      $form->addSubmit("upload_go", _("Upload Image"));
      $form->addTplTag('CONTENT', Cabinet_Action::imageManager());
    }

    $template = $form->getTemplate();
    $content = PHPWS_Template::process($template, "filecabinet", "manager.tpl");
    return $content;
  }

  function uploadImage(){
    $image = & new PHPWS_Image;
    $result = $image->importPost("upload");

    if (PEAR::isError($result))
      return $result;
    else {
      $image->directory = "filecabinet/images/";
      $image->module = _("Not Specified");
      return $image->save();
    }
  }

  function editImage(&$image){
    $form = & new PHPWS_Form;
    $form->addHidden("module", "filecabinet");
    $form->addHidden("action", "postImage");
    $form->addHidden("image_id", $image->getId());

    $file_data[] = "<b>" . _("Filename") . "</b> : " . $image->getFilename();
    $file_data[] = "<b>" . _("Directory") . "</b> : ./images/" . $image->getDirectory();
    $file_data[] = "<b>" . _("Image Type") . "</b> : " . $image->getType();
    $file_data[] = "<b>" . _("Width") . "</b> : " . $image->getWidth() . "px";
    $file_data[] = "<b>" . _("Height") . "</b> : " . $image->getHeight() . "px";
    $form->addTplTag("FILE_INFO_LABEL", _("Image Information"));
    $form->addTplTag("FILE_INFO", implode("<br />", $file_data));
    $form->addTplTag("IMAGE_VIEW", $image->getTag());

    $template = $form->getTemplate();
    return PHPWS_Template::process($template, "filecabinet", "editImage.tpl");
  }
}


?>