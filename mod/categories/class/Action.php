<?php

PHPWS_CORE::configRequireOnce("categories", "config.php");
PHPWS_Core::initModClass("categories", "Category.php");

class Categories_Action{

  function admin(){
    $panel = & Categories_Action::cpanel();

    if (isset($_REQUEST['subaction']))
      $subaction = $_REQUEST['subaction'];
    else
      $subaction = $panel->getCurrentTab();

    if (isset($_REQUEST['link_id']))
      $category = & new Category($_REQUEST['cat_id']);
    else
      $category = & new Category;

    switch ($subaction){
    case "new":
      $content[] = Categories_Action::edit($category);
      break;

    case "list":
      $content[] = Categories_Action::category_list();
      break;

    case "postCategory":
      $result = Categories_Action::postCategory($category);
      if (is_array($result)){
	$content[] = Categories_Action::edit($category, $result);
      } else {
	$direction = (isset($category->id)) ? "list" : "new";

	$result = $category->save();
	if (PEAR::isError($result)){
	  PHPWS_Error::log($result);
	  $content[] = Categories_Action::affirm(_("Unable to save category.") . " " .  _("Please contact your administrator."), $direction);
	}
	else
	  $content[] = Categories_Action::affirm(_("Category saved successfully."), $direction);
      }

      break;
    }
    $panel->setContent(implode("", $content));
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function affirm($content, $return){
    $template['CONTENT'] = $content;

    $value['action'] = "admin";
    $value['subaction'] = $return;
    $template['LINK'] = PHPWS_Text::moduleLink("Continue", "categories", $value);

    return PHPWS_Template::process($template, "categories", "affirm.tpl");
  }

  function postCategory(&$category){
    PHPWS_Core::initCoreClass("Image.php");
    if (empty($_POST['title']))
      $errors['title'] = _("Your category must have a title.");

    $category->setTitle($_POST['title']);

    if (!empty($_POST['description'])){
      if (CATEGORIES_ALLOW_WYSIWYG && JAVASCRIPT_ON)
	$description = stripslashes($_POST['description']);
      else
	$description = $_POST['description'];

      $category->setDescription($description);
    }

    $category->setParent((int)$_POST['parent']);

    $image = & new PHPWS_Image;
    $result = $image->importFiles("image");

    if (is_array($result)){
      foreach ($result as $message)
	$messages[] = $message->getMessage();

      $errors['image'] = implode("<br />", $messages);
    } else {
      $image->setDirectory("images/categories");
      $result = $image->writeImage();

      if (PEAR::isError($result)){
	PHPWS_Error::log($result);
	$errors['image'] = _("Unable to save the image file." . " " . _("Please contact your administrator."));
      } else
	$category->setImage($image->getFilename());
    }
  
    if (isset($errors))
      return $errors;
    else
      return TRUE;
  }


  function &cpanel(){
    Layout::addStyle("categories");

    PHPWS_Core::initModClass("controlpanel", "Panel.php");
    $newLink = "index.php?module=categories&amp;action=admin";
    $newCommand = array ("title"=>_("New"), "link"=> $newLink);
	
    $listLink = "index.php?module=categories&amp;action=admin";
    $listCommand = array ("title"=>_("List"), "link"=> $listLink);

    $tabs['new'] = $newCommand;
    $tabs['list'] = $listCommand;

    $panel = & new PHPWS_Panel("categories");
    $panel->quickSetTabs($tabs);

    $panel->setModule("categories");
    $panel->setPanel("panel.tpl");
    return $panel;
  }
  
  function user(){

  }

  function edit(&$category, $errors=NULL){
    $template = NULL;

    $form = & new PHPWS_Form('edit_form');
    $form->add("module", "hidden", "categories");
    $form->add("action", "hidden", "admin");		     
    $form->add("subaction", "hidden", "postCategory");

    $cat_id = $category->getId();

    if (isset($cat_id)){
      $form->add("cat_id", "hidden", $cat_id);
      $form->add("submit", "submit", _("Update Category"));
      $template['PAGE_TITLE'] = _("Update Category");
    } else {
      $form->add("submit", "submit", _("Add Category"));
      $template['PAGE_TITLE'] = _("Add Category");
    }

    $category_list = Categories::getCategories("parent");

    $template['PARENT_LBL'] = _("Parent");
    $form->add("parent", "select", $category_list);

    $template['TITLE_LBL'] = _("Title");
    if (isset($errors['title']))
      $template['TITLE_ERROR'] = $errors['title'];
    $form->add("title", "textfield", $category->getTitle());
    $form->setsize("title", 40);

    $template['DESC_LBL'] = _("Description");
    if (CATEGORIES_ALLOW_WYSIWYG && JAVASCRIPT_ON){
      $values['NAME'] = "description";
      $values['VALUE'] = $category->getDescription();
      $template['DESCRIPTION'] = Layout::getJavascript("hypertext", $values);
    } else {
      $form->add("description", "textarea", $category->getDescription());
      $form->setRows("description", "10");
      $form->setWidth("description", "80%");
    }

    $template['IMAGE_LBL'] = _("Image");
    if (isset($errors['image']))
      $template['IMAGE_ERROR'] = $errors['image'];

    $form->add("image", "file");

    $image = $category->getImage();

    if (isset($image)){
      $form->add("current_image", "hidden", $image);
      $template['CURRENT_IMG_LABEL'] = _("Current Image");
      $template['CURRENT_IMG'] = $image;
    }

    $form->mergeTemplate($template);
    $final_template = $form->getTemplate();

    return PHPWS_Template::process($final_template, "categories", "forms/edit.tpl");
  }

  function category_list(){

  }
}

?>