<?php

PHPWS_CORE::configRequireOnce('categories', 'config.php');
PHPWS_Core::initModClass('categories', 'Category.php');

class Categories_Action{

  function admin(){
    if (!Current_User::authorized('categories')) {
      Current_User::disallow(_('You are not authorized to administrate categories.'));
      return;
    }
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
    case 'deleteCategory':
      Categories::delete($category);
      $title = _('Manage Categories');
      $content[] = Categories_Action::category_list();
      break;

    case 'edit':
      if ($category->id)
	$title = _('Update Category');
      else
	$title = _('Add Category');

      $content[] = Categories_Action::edit($category);
      break;

    case 'list':
      $title = _('Manage Categories');
      $content[] = Categories_Action::category_list();
      break;

    case 'new':
      $title = _('Add Category');
      $content[] = Categories_Action::edit($category);
      break;

    case 'postCategory':
      $title = _('Manage Categories');
      $result = Categories_Action::postCategory($category);
      if (is_array($result)){
	$content[] = Categories_Action::edit($category, $result);
      } else {
	$direction = (isset($category->id)) ? 'list' : 'new';

	$result = $category->save();
	if (PEAR::isError($result)){
	  PHPWS_Error::log($result);
	  $content[] = Categories_Action::affirm(_('Unable to save category.') . ' ' .  _('Please contact your administrator.'), $direction);
	}
	else
	  $content[] = Categories_Action::affirm(_('Category saved successfully.'), $direction);
      }

      break;
    }

    $template['TITLE']   = $title;
    $template['CONTENT'] = implode('', $content);

    $final = PHPWS_Template::process($template, 'categories', 'menu.tpl');

    $panel->setContent($final);
    $finalPanel = $panel->display();
    Layout::add(PHPWS_ControlPanel::display($finalPanel));
  }

  function user(){
    $mod = $id = NULL;
    $action = & $_REQUEST['action'];
    switch ($action) {
    case 'view':
      if (isset($_REQUEST['id'])) {
	$id = &$_REQUEST['id'];
      }

      if (isset($_REQUEST['ref_mod'])) {
	$mod = $_REQUEST['ref_mod'];
      }

      $content = Categories_Action::viewCategory($id, $mod);
      break;
    }

    Layout::add($content);
  }

  function affirm($content, $return){
    $template['CONTENT'] = $content;

    $value['action'] = 'admin';
    $value['subaction'] = $return;
    $template['LINK'] = PHPWS_Text::secureLink('Continue', 'categories', $value);

    return PHPWS_Template::process($template, 'categories', 'affirm.tpl');
  }


  function postCategory(&$category){
    PHPWS_Core::initCoreClass('File.php');

    if (empty($_POST['title']))
      $errors['title'] = _('Your category must have a title.');

    $category->setTitle($_POST['title']);

    if (!empty($_POST['cat_description'])){
      $description = $_POST['cat_description'];

      $category->setDescription($description);
    }

    $category->setParent((int)$_POST['parent']);

    $image = PHPWS_Form::postImage('image', 'categories');

    if (PEAR::isError($image)){
      PHPWS_Error::log($image);
      $errors['image'] = _('There was a problem saving your image to the server.');
    } elseif (is_array($image)){
      foreach ($image as $message)
	$messages[] = $message->getMessage();

      $errors['image'] = implode('<br />', $messages);
    } elseif (get_class($image) == 'phpws_image')
	$category->setIcon($image);
    
    if (isset($errors))
      return $errors;
    else
      return TRUE;
  }


  function &cpanel(){
    Layout::addStyle('categories');

    PHPWS_Core::initModClass('controlpanel', 'Panel.php');
    $newLink = 'index.php?module=categories&amp;action=admin';
    $newCommand = array ('title'=>_('New'), 'link'=> $newLink);
	
    $listLink = 'index.php?module=categories&amp;action=admin';
    $listCommand = array ('title'=>_('List'), 'link'=> $listLink);

    $tabs['new'] = $newCommand;
    $tabs['list'] = $listCommand;

    $panel = & new PHPWS_Panel('categories');
    $panel->quickSetTabs($tabs);

    $panel->setModule('categories');
    $panel->setPanel('panel.tpl');
    return $panel;
  }
  

  function edit(&$category, $errors=NULL){
    $template = NULL;
    PHPWS_Core::initCoreClass('Editor.php');

    $form = & new PHPWS_Form('edit_form');
    $form->add('module', 'hidden', 'categories');
    $form->add('action', 'hidden', 'admin');		     
    $form->add('subaction', 'hidden', 'postCategory');

    $cat_id = $category->getId();

    if (isset($cat_id)){
      $form->add('category_id', 'hidden', $cat_id);
      $form->add('submit', 'submit', _('Update Category'));
    } else
      $form->add('submit', 'submit', _('Add Category'));

    $category_list = Categories::getCategories('list', $category->getId());

    if (is_array($category_list)) {
      $reverse = array_reverse($category_list, TRUE);
      $reverse[0] = '-' . _('Top Level') . '-';
      $category_list = array_reverse($reverse, TRUE);
    }
    else {
      $category_list = array(0=>'-' . _('Top Level') . '-');
    }


    $form->add('parent', 'select', $category_list);
    $form->setMatch('parent', $category->getParent());
    $form->setLabel('parent', _('Parent'));

    if (isset($errors['title']))
      $template['TITLE_ERROR'] = $errors['title'];
    $form->add('title', 'textfield', $category->getTitle());
    $form->setsize('title', 40);
    $form->setLabel('title', _('Title'));

    if (Editor::willWork()){
      $editor = & new Editor('htmlarea', 'cat_description', $category->getDescription());
      $description = $editor->get();
      $form->addTplTag('CAT_DESCRIPTION', $description);
      $form->addTplTag('CAT_DESCRIPTION_LABEL', _('Description'));
    } else {
      $form->addTextArea('cat_description', $category->getDescription());
      $form->setRows('cat_description', '10');
      $form->setWidth('cat_description', '80%');
      $form->setLabel('cat_description', _('Description'));
    }

    $form->addTplTag('IMAGE_TITLE_LABEL', _('Icon Title'));

    if (isset($errors['image']))
      $template['IMAGE_ERROR'] = $errors['image'];

    $image = $category->getIcon();

    if (!empty($image)){
      $image_id = $image->getId();
      $form->add('current_image', 'hidden', $image->getId());
      $template['CURRENT_IMG_LABEL'] = _('Current Icon');
      $template['CURRENT_IMG'] = $image->getTitle();
    }
    else
      $image_id = NULL;

    $result = $form->addImage('image', 'categories', $image_id);

    $template['IMAGE_LABEL'] = _('Icon');

    $form->mergeTemplate($template);
    $final_template = $form->getTemplate();

    return PHPWS_Template::process($final_template, 'categories', 'forms/edit.tpl');
  }

  function category_list(){
    PHPWS_Core::initCoreClass('DBPager.php');

    $pageTags['TITLE_LABEL'] = _('Title');
    $pageTags['PARENT_LABEL'] = _('Parent');
    $pageTags['ACTION_LABEL'] = _('Action');

    $pager = & new DBPager('categories', 'Category');
    $pager->setModule('categories');
    $pager->setDefaultLimit(10);
    $pager->setTemplate('category_list.tpl');
    $pager->setLink('index.php?module=categories&amp;action=admin&amp;tab=list');
    $pager->addTags($pageTags);
    $pager->addToggle('class="toggle1"');
    $pager->addToggle('class="toggle2"');
    $pager->setMethod('description', 'getDescription');
    $pager->setMethod('parent', 'getParentTitle');
    $pager->addRowTag('action', 'Categories_Action', 'getListAction');
    $content = $pager->get();

    if (empty($content)) {
      return _('No categories found.');
    }
    else {
      return $content;
    }
  }

  function getListAction($category){
    $vars['module']      = 'categories';
    $vars['action']      = 'admin';
    $vars['category_id'] = $category->getId();

    $vars['subaction'] = 'edit';
    $links[] = PHPWS_Text::secureLink(_('Edit'), 'categories', $vars);

    if (javascriptEnabled()){
      $js_vars['QUESTION'] = _('Are you sure you want to delete this category?');
      $js_vars['ADDRESS']  = 'index.php?module=categories&amp;action=admin&amp;subaction=deleteCategory&amp;category_id=' . $category->getId() . '&amp;authkey=' . Current_User::getAuthKey();
      $js_vars['LINK']     = _('Delete');
      $links[] = Layout::getJavascript('confirm', $js_vars);
    } else {
      $vars['subaction'] = 'delete';
      $links[] = PHPWS_Text::moduleLink(_('Delete'), 'categories', $vars);
    }

    return implode(' | ', $links);
    
  }

  function viewCategory($id=NULL, $module=NULL) {
    $category = NULL;

    if (!isset($id)) {
      $content = Categories::getCategoryList($module);
      $template['TITLE'] = _('All Categories');
    } else {
      $category = & new Category((int)$id);
      
      if (isset($module) && $module != '0') {
	PHPWS_Core::initCoreClass('Module.php');
	$mod = & new PHPWS_Module($module);
	$template['TITLE'] = _('Module') . ':' . $mod->getProperName();
	$content = Categories_Action::getAllItems($category, $module);
      } else {
	$template['TITLE'] = _('Module Listing');
	$content = Categories::listModuleItems($category);
      }
    }

    $family_list = Categories::cookieCrumb($category, $module);

    $template['FAMILY'] = $family_list;
    $template['CONTENT'] = &$content;

    $content = PHPWS_Template::process($template, 'categories', 'view_categories.tpl');
    return $content;
  }


  /**
   * Listing of all items within a category
   */
  function getAllItems(&$category, $module) {
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    PHPWS_Core::initCoreClass('DBPager.php');

    $pageTags['TITLE_LABEL'] = _('Item Title');

    $mod_list = Categories::getModuleListing($category->getId());    
    if (!empty($mod_list)) {
      array_unshift($mod_list, _('All Modules'));
    } else {
      $mod_list[0] = _('All Modules');
    }

    $form = & new PHPWS_Form;
    $form->setMethod('get');
    $form->addHidden('module', 'categories');
    $form->addHidden('action', 'view');
    $form->addHidden('id', $category->getId());
    $form->addSelect('ref_mod', $mod_list);

    if (isset($_REQUEST['ref_mod'])) {
      $form->setMatch('ref_mod', $_REQUEST['ref_mod']);
    }

    $form->addSubmit("submit", _('View Module'));

    $form_tpl = $form->getTemplate();

    $pageTags['MODULE_LIST'] = implode('', $form_tpl);

    $pager = & new DBPager('category_items', 'Category_Item');
    $pager->addWhere('cat_id', $category->id);
    $pager->addWhere('version_id', 0);

    if (isset($module)) {
      $pager->addWhere('module', $module);
    }
    $pager->setModule('categories');
    $pager->setDefaultLimit(10);
    $pager->setTemplate('category_item_list.tpl');
    $pager->setLink('index.php?module=categories&amp;action=view&amp;id=' . $category->getId());
    $pager->addTags($pageTags);
    $pager->addToggle('class="toggle1"');
    $pager->addToggle('class="toggle2"');
    $pager->setMethod('title', 'getLink', TRUE);
    $content = $pager->get();

    if (empty($content)) {
      return _('No items found in this category.');
    }
    else {
      return $content;
    }
  }


}

?>