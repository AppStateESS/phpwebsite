<?php

/**
 * General category administration
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

PHPWS_Core::configRequireOnce("categories", "errorDefines.php");
PHPWS_Core::initModClass("categories", "Category.php");

define("CAT_LINK_DIVIDERS", "&gt;&gt;");

class Categories{

  /**
   * This function and the next were for testing.
   * though not used now, may be expanded in the future
   */
  function getCSS(){
    $result = Categories::getCategories();
    $list = Categories::_makeLink($result);
    Layout::addStyle("categories");
    return $list;
  }

  /**
   * Ditto the above
   */
  function _makeLink($list){
    $tpl = & new PHPWS_Template("categories");
    $tpl->setFile("list.tpl");

    $vars['action'] = "add_category";
    $tpl->setCurrentBlock("link_row");

    foreach ($list as $category){
      $vars['id'] = $category->id;
      $link = PHPWS_Text::moduleLink($category->title, "category", $vars);

      if (!empty($category->children)) {
	$link .= Categories::_makeLink($category->children);
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
    PHPWS_Core::initModClass("categories", "Category_Item.php");
    if (empty($module) || empty($item_id))
      return NULL;

    $cat = & new Category_Item($module, $item_name);
    $cat->setItemId($item_id);
    $result = $cat->getCategoryItems();

    if (empty($result))
      return NULL;

    $cat_list = array_keys($result);

    $db = & new PHPWS_DB("categories");
    $db->addWhere("id", $cat_list);
    $cat_result = $db->getObjects("Category");

    return $cat_result;
  }

  function getExtendedLinks($module, $item_id, $item_name=NULL){
    $cat_result = Categories::_getItemsCategories($module, $item_id, $item_name);

    if (empty($cat_result))
      return NULL;

    foreach ($cat_result as $cat){
      $link[] = Categories::_createExtendedLink($cat, "extended");
    }

    return $link;
  }

  function getSimpleLinks($module, $item_id, $item_name=NULL){
    $cat_result = Categories::_getItemsCategories($module, $item_id, $item_name);

    if (empty($cat_result))
      return NULL;

    foreach ($cat_result as $cat){
      $link[] = $cat->getViewLink();
    }

    return $link;

  }


  function showCategoryLinks($module, $item_id, $item_name=NULL){
    $links = Categories::getExtendedLinks($module, $item_id, $item_name);

    if (empty($links))
      return NULL;
    
    $tpl = & new PHPWS_Template("categories");
    $tpl->setFile("minilist.tpl");
    $tpl->setCurrentBlock("link-list");

    foreach ($links as $link){
      $tpl->setData(array("LINK"=>$link));
      $tpl->parseCurrentBlock();
    }

    $data['CONTENT'] = $tpl->get();
    $data['TITLE'] = _("Categories");

    Layout::add($data, "categories", "category_box");

  }

  function _createExtendedLink($category, $mode){
    $link[] = $category->getViewLink();

    if ($mode == "extended") {
      if ($category->parent){
	$parent = & new Category($category->parent);
	$link[] = Categories::_createExtendedLink($parent, "extended");
      }
    }

    return implode(" " . CAT_LINK_DIVIDERS . " ", array_reverse($link));
  }


  function getCategories($mode="sorted", $drop=NULL){
    $db = & new PHPWS_DB("categories");

    switch ($mode){
    case "sorted":
      $db->addWhere("parent", 0);
      $db->addOrder("title");
      $cats = $db->getObjects("Category");
      if (empty($cats))
	return NULL;
      $result = Categories::initList($cats);
      return $result;
      break;

    case "idlist":
      $db->addColumn("title");
      $db->setIndexBy("id");
      $result = $db->select("col");
      break;

    case "list":
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
	    $indexed[$subkey] = $category->title . " " . CAT_LINK_DIVIDERS . " " . $subvalue;
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

}

?>