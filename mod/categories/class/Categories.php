<?php

/**
 * General category administration
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */

PHPWS_Core::configRequireOnce("categories", "errorDefines.php");

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
      $cat->loadImage();
      $cat->loadChildren();
      $children[$cat->id] = $cat;
    }
    return $children;
  }

  function getCategories($mode="sorted"){
    PHPWS_Core::initModClass("categories", "Category.php");
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
      $indexed = Categories::_buildList($list);

      return $indexed;
      break;
    }

    return $result;
  }

  function _buildList($list){
    if (empty($list))
      return NULL;

    foreach ($list as $category){
      $indexed[$category->id] = $category->title;
      if (!empty($category->children)) {
	$sublist = Categories::_buildList($category->children);
	foreach ($sublist as $subkey => $subvalue){
	  $indexed[$subkey] = $category->title . " &gt;&gt; " . $subvalue;
	}
      }
    }
    return $indexed;
  }

}

?>