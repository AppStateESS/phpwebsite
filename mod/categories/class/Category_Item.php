<?php

/**
 * Links categories to specific items
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package categories
 */


PHPWS_Core::configRequireOnce("categories", "errorDefines.php");

class Category_Item {
  var $item_id      = 0;
  var $cat_id       = 0;
  var $version_id   = 0;
  var $module       = NULL;
  var $item_name    = NULL;
  var $title        = NULL;
  var $link         = NULL;

  function Category_Item($module=NULL, $item_name=NULL)
  {
    if (!isset($module)) {
      return;
    }

    if (empty($item_name)) {
      $item_name = $module;
    }

    $this->module = $module;
    $this->item_name = $item_name;
  }

  function setModule($module)
  {
    $this->module = $module;
  }

  function getModule()
  {
    return $this->module;
  }

  function getProperName(){
    PHPWS_Core::initCoreClass("Module.php");
    $mod = & new PHPWS_Module($this->module);
    return $mod->getProperName();
  }

  function setItemName($item_name)
  {
    $this->item_name = $item_name;
  }

  function getItemName()
  {
    if (empty($this->item_name)) {
      return $this->module;
    }

    return $this->item_name;
  }

  function setItemId($id)
  {
    $this->item_id = (int)$id;
  }

  function getItemId()
  {
    return $this->item_id;
  }

  function setCatId($id)
  {
    $this->cat_id = (int)$id;
  }

  function getCatId()
  {
    return $this->cat_id;
  }

  function setVersionId($version_id)
  {
    $this->version_id = $version_id;
  }

  function getVersionId()
  {
    return $this->version_id;
  }

  function setTitle($title)
  {
    $this->title = strip_tags($title);
  }

  function getTitle()
  {
    return $this->title;
  }

  function setLink($link)
  {
    PHPWS_Text::makeRelative($link);
    $this->link = $link;
  }

  function getLink($html=FALSE)
  {
    if ($html == TRUE) {
      return "<a href=\"" . $this->link . "\">" . $this->title . "</a>";
    } else {
      return $this->link;
    }
  }


  function savePost(){
    if (!isset($_POST) ||
	!isset($_POST['categories'][$this->module][$this->item_name]) ||
	!$this->_testVars()
	)
      {
	return FALSE;
      }

    $categories = $_POST['categories'][$this->module][$this->item_name];	

    $this->clear();

    foreach ($categories as $cat_id){
      $this->cat_id = $cat_id;
      $result = $this->save();

      if (PEAR::isError($result)) {
	return $result;
      }
    }

    return TRUE;
  }

  function _testVars(){
    if (
	empty($this->module)    || empty($this->item_name) ||
	( empty($this->item_id) && empty($this->version_id) )   ||
	empty($this->title)     || empty($this->link)      
	)
      {
	return FALSE;
      } else {
	return TRUE;
      }
  }

  function clear()
  {
    $db = & new PHPWS_DB("category_items");
    $db->addWhere("version_id", $this->version_id);
    $db->addWhere("item_id",    $this->item_id);
    $db->addWhere("module",     $this->module);
    $db->addWhere("item_name",  $this->item_name);
    return $db->delete();
  }

  function save(){
    if (!$this->_testVars() || empty($this->cat_id))
      {
	return PHPWS_Error::get(CAT_ITEM_MISSING_VAL, "categories", "Category_Item::save");
      }

    $db = & new PHPWS_DB("category_items");
    return $db->saveObject($this);
  }

  function getForm(){
    PHPWS_Core::initModClass("categories", "Categories.php");
    $categories = Categories::getCategories("list");

    if (PEAR::isError($categories)){
      PHPWS_Error::log($categories);
      return PHPWS_Error::get(CAT_DB_PROBLEM, "categories", "Categories::getForm");
    }
      
    if (empty($categories))
      return _("No categories exist.");

    $multiple = & new Form_Multiple("categories[" . $this->getModule() . "][" . $this->getItemName() . "]", $categories);
    $multiple->setSize(5);
    if ($this->item_id || $this->version_id) {
      $cat_items  = $this->getCategoryItems();
      if (!empty($cat_items) && is_array($cat_items)) {
	$multiple->setMatch(array_keys($cat_items));
      }
    }

    //    $multiple->setWidth("100%");

    return $multiple->get();

  }


  function getCategoryItems(){
    PHPWS_Core::initModClass("categories", "Category_Item.php");

    $db = & new PHPWS_DB("category_items");
    $db->addWhere("version_id", $this->getVersionId());
    $db->addWhere("item_id", $this->getItemId());
    $db->addWhere("module", $this->getModule());
    $db->addWhere("item_name", $this->getItemName());
    $db->setIndexBy("cat_id");
    return $db->getObjects("category_item");
  }

  function updateVersion($approved=FALSE)
  {
    if (!$this->_testVars()) {
	return PHPWS_Error::get(CAT_ITEM_MISSING_VAL, "categories", "Category_Item::save");
    }

    $db = & new PHPWS_DB("category_items");

    $db->addWhere("version_id", $this->getVersionId());
    $db->addWhere("module",     $this->getModule());
    $db->addWhere("item_name",  $this->getItemName());

    if (isset($_POST['categories'][$this->getModule()][$this->getItemName()])) {
      if ($approved) {
	$this->version_id = 0;
      }
      $db->delete();
      return $this->savePost();
    }

    if ($approved) {
      if (empty($this->item_id)) {
	return FALSE;
      }
      // item is approved
      $db->addValue("version_id", 0);
    }

    $db->addValue("item_id", $this->getItemId());
    $db->addValue("title",   $this->getTitle());
    $db->addValue("link",    $this->getLink());

    $result = $db->update();

    return $result;
  }
}

?>