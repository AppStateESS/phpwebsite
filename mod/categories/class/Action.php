<?php

PHPWS_CORE::configRequireOnce("categories", "config.php");
PHPWS_Core::initModClass("categories", "Category.php");

class Categories_Action{

  function admin(){
    $content = array();
    $panel = & Categories_Action::cpanel();

    if (isset($_REQUEST['subaction']))
      $subaction = $_REQUEST['subaction'];
    else
      $subaction = $panel->getCurrentTab();

    if (isset($_REQUEST['category_id']))
      $category = & new Category($_REQUEST['category_id']);
    else
      $category = & new Category;

    switch ($subaction){
    case "deleteCategory":
      Categories::delete($category);
      $content[] = Categories_Action::category_list();
      break;

    case "edit":
      if ($category->id)
	$title = _("Update Category");
      else
	$title = _("Add Category");

      $content[] = Categories_Action::edit($category);
      break;

    case "list":
      $title = _("Manage Categories");
      $content[] = Categories_Action::category_list();
      break;

    case "new":
      $title = _("Add Category");
      $content[] = Categories_Action::edit($category);
      break;

    case "postCategory":
      $title = _("Manage Categories");
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

    $template['TITLE']   = $title;
    $template['CONTENT'] = implode("", $content);

    $final = PHPWS_Template::process($template, "categories", "menu.tpl");

    $panel->setContent($final);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function user(){
    echo "w00t!";
  }

  function affirm($content, $return){
    $template['CONTENT'] = $content;

    $value['action'] = "admin";
    $value['subaction'] = $return;
    $template['LINK'] = PHPWS_Text::moduleLink("Continue", "categories", $value);

    return PHPWS_Template::process($template, "categories", "affirm.tpl");
  }


  function postCategory(&$category){
    PHPWS_Core::initCoreClass("File.php");

    if (empty($_POST['title']))
      $errors['title'] = _("Your category must have a title.");

    $category->setTitle($_POST['title']);

    if (!empty($_POST['cat_description'])){
      $description = $_POST['cat_description'];

      $category->setDescription($description);
    }

    $category->setParent((int)$_POST['parent']);

    $image = PHPWS_Form::postImage("image", "categories");

    if (PEAR::isError($image)){
      PHPWS_Error::log($image);
      $errors['image'] = _("There was a problem saving your image to the server.");
    } elseif (is_array($image)){
      foreach ($image as $message)
	$messages[] = $message->getMessage();

      $errors['image'] = implode("<br />", $messages);
    } elseif (get_class($image) == "phpws_image")
	$category->setIcon($image);
    
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
  

  function edit(&$category, $errors=NULL){
    $template = NULL;
    PHPWS_Core::initCoreClass("Editor.php");

    $form = & new PHPWS_Form('edit_form');
    $form->add("module", "hidden", "categories");
    $form->add("action", "hidden", "admin");		     
    $form->add("subaction", "hidden", "postCategory");

    $cat_id = $category->getId();

    if (isset($cat_id)){
      $form->add("category_id", "hidden", $cat_id);
      $form->add("submit", "submit", _("Update Category"));
    } else
      $form->add("submit", "submit", _("Add Category"));

    $category_list = Categories::getCategories("list");

    if (is_array($category_list)) {
      $reverse = array_reverse($category_list, TRUE);
      $reverse[0] = "-" . _("Top Level") . "-";
      $category_list = array_reverse($reverse, TRUE);
    }
    else {
      $category_list = array(0=>"-" . _("Top Level") . "-");
    }

    
    $form->add("parent", "select", $category_list);
    $form->setLabel("parent", _("Parent"));


    if (isset($errors['title']))
      $template['TITLE_ERROR'] = $errors['title'];
    $form->add("title", "textfield", $category->getTitle());
    $form->setsize("title", 40);
    $form->setLabel("title", _("Title"));

    if (Editor::willWork()){
      $editor = & new Editor("htmlarea", "cat_description", $category->getDescription());
      $description = $editor->get();
      $form->addTplTag("CAT_DESCRIPTION", $description);
      $form->addTplTag("CAT_DESCRIPTION_LABEL", _("Description"));
    } else {
      $form->addTextArea("cat_description", $category->getDescription());
      $form->setRows("cat_description", "10");
      $form->setWidth("cat_description", "80%");
      $form->setLabel("cat_description", _("Description"));
    }

    $form->addTplTag("IMAGE_TITLE_LABEL", _("Icon Title"));

    if (isset($errors['image']))
      $template['IMAGE_ERROR'] = $errors['image'];

    $image = $category->getIcon();

    if (!empty($image)){
      $image_id = $image->getId();
      $form->add("current_image", "hidden", $image->getId());
      $template['CURRENT_IMG_LABEL'] = _("Current Icon");
      $template['CURRENT_IMG'] = $image->getTitle();
    }
    else
      $image_id = NULL;

    $result = $form->addImage("image", "categories", $image_id);

    $template['IMAGE_LABEL'] = _("Icon");

    $form->mergeTemplate($template);
    $final_template = $form->getTemplate();

    return PHPWS_Template::process($final_template, "categories", "forms/edit.tpl");
  }

  function category_list(){
    PHPWS_Core::initCoreClass("DBPager.php");

    $pageTags['TITLE_LABEL'] = _("Title");
    $pageTags['PARENT_LABEL'] = _("Parent");
    $pageTags['ACTION_LABEL'] = _("Action");

    $pager = & new DBPager("categories", "Category");
    $pager->setModule("categories");
    $pager->setTemplate("category_list.tpl");
    $pager->setLink("index.php?module=categories&amp;action=admin&amp;tab=list");
    $pager->addTags($pageTags);
    $pager->addToggle("class=\"toggle1\"");
    $pager->addToggle("class=\"toggle2\"");
    $pager->setMethod("description", "getDescription");
    $pager->setMethod("parent", "getParentTitle");
    $pager->addRowTag("action", "Categories_Action", "getListAction");
    $content = $pager->get();
    if (empty($content)) {
      return _("No categories found.");
    }
    else {
      return $content;
    }
  }

  function getListAction($category){
    $vars['module']      = "categories";
    $vars['action']      = "admin";
    $vars['category_id'] = $category->getId();

    $vars['subaction'] = "edit";
    $links[] = PHPWS_Text::moduleLink(_("Edit"), "categories", $vars);

    if (javascriptEnabled()){
      $question['QUESTION'] = "Are you sure you want to delete this category:\\n" . $category->getTitle();
      Layout::loadModuleJavascript("categories", "category_list.js", $question);
      $links[] = "<a href=\"javascript:void(0)\" onclick=\"confirmDelete(" . $category->getId() . ")\">" . _("Delete") . "</a>";
    } else {
      $vars['subaction'] = "delete";
      $links[] = PHPWS_Text::moduleLink(_("Delete"), "categories", $vars);
    }

    return implode(" | ", $links);
    
  }
}

?>