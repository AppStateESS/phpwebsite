<?php

/**
 * General category administration
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

PHPWS_Core::configRequireOnce('categories', 'errorDefines.php');
PHPWS_Core::initModClass('categories', 'Category.php');

define('CAT_LINK_DIVIDERS', '&gt;&gt;');

class Categories{
  /**
   * Returns a list of category links
   */
  function getCategoryList($module){
    $result = Categories::getCategories();

    $list = Categories::_makeLink($result, $module);
    Layout::addStyle("categories");
    return $list;
  }

  /**
   * Creates the links based on categories sent to it
   */
  function _makeLink($list, $module){
    $tpl = & new PHPWS_Template("categories");
    $tpl->setFile("simple_list.tpl");

    $vars['action'] = 'view';

    if (!empty($module)) {
      $vars['ref_mod'] = $module;
      $db = & new PHPWS_DB("category_items");
    }

    $tpl->setCurrentBlock("link_row");



    foreach ($list as $category){
      if (!empty($module)) {
	$db->addWhere('module', $module);
	$db->addWhere('cat_id', $category->id);
	list($count) = $db->select('count');
	$db->resetWhere();
	$items = ' (' . $count['count'] . ' ' . _('items') . ')';
      } else {
	$items = NULL;
      }

      $vars['id'] = $category->id;

      $title = $category->title . $items;

      $link = PHPWS_Text::moduleLink($title, "categories", $vars);

      if (!empty($category->children)) {
	$link .= Categories::_makeLink($category->children, $module);
      }

      $tpl->setData(array("LINK" => $link));
      $tpl->parseCurrentBlock();
    }

    $links = $tpl->get();
    return $links;
  }

  function initList($list){
    foreach ($list as $cat){
      $cat->loadIcon();
      $cat->loadChildren();
      $children[$cat->id] = $cat;
    }
    return $children;
  }


  function _getItemsCategories($module, $item_id, $item_name){
    PHPWS_Core::initModClass('categories', 'Category_Item.php');
    if (empty($module) || empty($item_id))
      return NULL;

    $cat = & new Category_Item($module, $item_name);
    $cat->setItemId($item_id);
    $cat_list = $cat->getCategoryItemIds();

    if (empty($cat_list))
      return NULL;

    $db = & new PHPWS_DB('categories');
    $db->addWhere('id', $cat_list);
    $cat_result = $db->getObjects('Category');

    return $cat_result;
  }


  /* Needed ?
  function getExtendedLinks($module, $item_id, $item_name=NULL){
    $cat_result = Categories::_getItemsCategories($module, $item_id, $item_name);

    if (empty($cat_result))
      return NULL;

    foreach ($cat_result as $cat){
      $link[] = Categories::_createExtendedLink($cat, 'extended');
    }

    return $link;
  }
  */

  function getSimpleLinks($module, $item_id, $item_name=NULL){
    $cat_result = Categories::_getItemsCategories($module, $item_id, $item_name);

    if (empty($cat_result))
      return NULL;

    foreach ($cat_result as $cat){
      $link[] = $cat->getViewLink($module);
    }

    return $link;
  }

  /* Not sure if needed anymore 

  function showCategoryLinks($module, $item_id, $item_name=NULL){
    $links = Categories::getExtendedLinks($module, $item_id, $item_name);

    if (empty($links))
      return NULL;
    
    $tpl = & new PHPWS_Template('categories');
    $tpl->setFile('minilist.tpl');
    $tpl->setCurrentBlock('link-list');

    foreach ($links as $link){
      $tpl->setData(array('LINK'=>$link));
      $tpl->parseCurrentBlock();
    }

    $data['CONTENT'] = $tpl->get();
    $data['TITLE'] = _('Categories');

    Layout::add($data, 'categories', 'category_box');

  }
  */


  function _createExtendedLink($category, $mode){
    $link[] = $category->getViewLink();

    if ($mode == 'extended') {
      if ($category->parent){
	$parent = & new Category($category->parent);
	$link[] = Categories::_createExtendedLink($parent, 'extended');
      }
    }

    return implode(' ' . CAT_LINK_DIVIDERS . ' ', array_reverse($link));
  }


  function getCategories($mode='sorted', $drop=NULL){
    $db = & new PHPWS_DB('categories');

    switch ($mode){
    case 'sorted':
      $db->addWhere('parent', 0);
      $db->addOrder('title');
      $cats = $db->getObjects('Category');
      if (empty($cats))
	return NULL;
      $result = Categories::initList($cats);
      return $result;
      break;

    case 'idlist':
      $db->addColumn('title');
      $db->setIndexBy('id');
      $result = $db->select('col');
      break;

    case 'list':
      $list = Categories::getCategories();
      $indexed = Categories::_buildList($list, $drop);

      return $indexed;
      break;
    }

    return $result;
  }

  function _buildList($list, $drop=NULL){
    if (empty($list))
      return NULL;

    foreach ($list as $category){
      if ($category->id == $drop) {
	continue;
      }
      $indexed[$category->id] = $category->title;
      if (!empty($category->children)) {
	$sublist = Categories::_buildList($category->children, $drop);
	if (isset($sublist)) {
	  foreach ($sublist as $subkey => $subvalue){
	    $indexed[$subkey] = $category->title . ' ' . CAT_LINK_DIVIDERS . ' ' . $subvalue;
	  }
	}
      }
    }

    if (isset($indexed)) {
      return $indexed;
    } else {
      return NULL;
    }
  }

  function getTopLevel(){
    $db = & new PHPWS_DB('categories');
    $db->addWhere('parent', 0);
    return $db->getObjects('Category');
  }

  function cookieCrumb($category=NULL, $module=NULL){
    Layout::addStyle('categories');

    $top_level = Categories::getTopLevel();


    $tpl = & new PHPWS_Template('categories');
    $tpl->setFile('list.tpl');

    foreach ($top_level as $top_cats) {
      $tpl->setCurrentBlock('child-row');
      $tpl->setData(array('CHILD' => $top_cats->getViewLink($module)));
      $tpl->parseCurrentBlock();
    }

    $vars['action'] = 'view';
    if (isset($module)) {
      $vars['ref_mod'] = $module;
    }

    $tpl->setCurrentBlock('parent-row');
    $tpl->setData(array('PARENT' => PHPWS_Text::moduleLink( _('Top Level'), 'categories', $vars)));
    $tpl->parseCurrentBlock();

    if (!empty($category)){
      $family_list = $category->getFamily();

      foreach ($family_list as $parent){
	if (isset($parent->children)) {
	  foreach ($parent->children as $child) {
	    $tpl->setCurrentBlock('child-row');
	    $tpl->setData(array('CHILD' => $child->getViewLink($module)));
	    $tpl->parseCurrentBlock();
	  }
	}
	
	$tpl->setCurrentBlock('parent-row');
	$tpl->setData(array('PARENT' => $parent->getViewLink($module)));
	$tpl->parseCurrentBlock();
      }
    }

    $content = $tpl->get();
    return $content;
  }

  function getModuleListing($cat_id){
    PHPWS_Core::initCoreClass('Module.php');
    $db = & new PHPWS_DB('category_items');
    $db->addColumn('module');
    $db->addWhere('cat_id' , (int)$cat_id);
    $result = $db->select('count');

    if (empty($result))
      return NULL;
    foreach ($result as $mod_results){
      $module = & new PHPWS_Module($mod_results['module']);
      $mod_list[$module->getTitle()] = $module->getProperName() 
	. ' (' . $mod_results['count'] . ' ' . _('items') . ')';
    }

    return $mod_list;
  }

  function listModuleItems(&$category){
    $module_list = Categories::getModuleListing($category->getId());

    if (empty($module_list)) {
      return _('No items available in this category.');
    }

    $vars['action'] = 'view';
    $vars['id'] = $category->getId();

    $tpl = & new PHPWS_Template("categories");
    $tpl->setFile('module_list.tpl');

    $tpl->setCurrentBlock('module-row');
    foreach ($module_list as $mod_key => $module){
      $vars['ref_mod'] = $mod_key;
      $link['MODULE_ROW'] = PHPWS_Text::moduleLink($module, "categories", $vars);
      $tpl->setData($link);
      $tpl->parseCurrentBlock();
    }

    return $tpl->get();
  }

  function removeModule($module){
    $db = & new PHPWS_DB("category_items");
    $db->addWhere("module", $module);
    $db->delete();
  }

  function delete($category){
    $category->kill();
  }

}

?>