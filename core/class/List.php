<?php

PHPWS_Core::initCoreClass("Pager.php");

/**
 * @version $Id$
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @modified Matthew McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */
class PHPWS_List {

  var $_module        = NULL;
  var $_controller    = NULL;
  var $_class         = NULL;
  var $_table         = NULL;
  var $_ids           = NULL;
  var $_dbColumns     = array();
  var $_listColumns   = array();
  var $_name          = NULL;
  var $_template      = NULL;
  var $_op            = NULL;
  var $_paging        = array();
  var $_where         = NULL;
  var $_order         = NULL;
  var $_overrideOrder = array();
  var $_pager         = NULL;
  var $_anchor        = FALSE;
  var $_idColumn      = "id";
  var $_extraListTags = array();
  var $_extraRowTags  = array();
  var $_lastIds       = NULL;
  var $_dualTemplates = TRUE;

  function getList() {
    if(!isset($this->_module))
      return PHPWS_Error::get(PHPWS_LIST_MODULE_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_class))
      return PHPWS_Error::get(PHPWS_LIST_CLASS_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_table))
      return PHPWS_Error::get(PHPWS_LIST_TABLE_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_dbColumns))
      return PHPWS_Error::get(PHPWS_LIST_DB_COL_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_listColumns)){
      $this->_listColumns = $this->_dbColumns;
      //      return PHPWS_Error::get(PHPWS_LIST_COLUMNS_NOT_SET, "core", "PHPWS_List::getList");
    }

    if(!isset($this->_name))
      return PHPWS_Error::get(PHPWS_LIST_NAME_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_op))
      return PHPWS_Error::get(PHPWS_LIST_OP_NOT_SET, "core", "PHPWS_List::getList");

    if(!isset($this->_template))
      $this->_template = $this->_name;

    if(!isset($this->_controller))
      $this->_controller = $this->_module;

    $theme = Layout::getTheme();

    if ($this->_dualTemplates){
      $themeRowTpl = "themes/$theme/templates/$this->_module/$this->_template/row.tpl";
      $moduleRowTpl = PHPWS_SOURCE_DIR."mod/$this->_module/templates/$this->_template/row.tpl"; 

      $themeListTpl = "themes/$theme/templates/$this->_module/$this->_template/list.tpl";
      $moduleListTpl = PHPWS_SOURCE_DIR."mod/$this->_module/templates/$this->_template/list.tpl"; 

      if(file_exists($themeRowTpl)) $rowTpl = $themeRowTpl;
      else $rowTpl = $moduleRowTpl;

      if(file_exists($themeListTpl)) $listTpl = $themeListTpl;
      else $listTpl = $moduleListTpl;
    } else {
      $listTpl = & new PHPWS_Template($this->_module);
      $result  = $listTpl->setFile($this->getTemplate()); 

      if (PEAR::isError($result))
	return $result;
    }

    if(isset($_REQUEST['list']) && ($this->_name == $_REQUEST['list'])) $this->catchOrder();

    if(isset($this->_paging) && is_array($this->_paging) && (sizeof($this->_paging) > 0)) {
      if(!isset($this->_pager)) {
	$this->_pager = new PHPWS_Pager;
	$this->_pager->setLinkBack("./index.php?module=$this->_controller&amp;$this->_op&amp;list=$this->_name");
	$this->_pager->setLimits($this->_paging['limits']);
	$this->_pager->makeArray(TRUE);
	
	if($this->_anchor) $this->_pager->setAnchor("#$this->_name");
	
	$this->_pager->limit = $this->_paging['limit'];
      }

      if(is_array($this->_ids)) {
	$this->_pager->setData($this->_orderIds($this->_ids));
      } else {
	$this->_pager->setData($this->_getIds());
      }

      if(isset($_REQUEST['list']) && ($this->_name == $_REQUEST['list'])) $this->_pager->pageData();
      else $this->_pager->pageData(FALSE);

      $items = $this->getItems($this->_pager->getData());
      $totalItems = $this->_pager->getNumRows();
    } else {
      $this->_pager = NULL;

      if(is_array($this->_ids))
	$items = $this->getItems($this->_ids);
      else
	$items = $this->getItems();

      $totalItems = sizeof($items);
    }

    /* Begin building main list tags array for processTemplate() */
    $listTags = array();
    if($this->_anchor) $listTags["ANCHOR"] = "<a name=\"$this->_name\" />";

    $columns = 0;

    foreach($this->_dbColumns as $column) {
      $capscolumn = strtoupper($column);
      $key = "{$capscolumn}_ORDER_LINK";
      $listTags[$key] = NULL;

      if($totalItems > 0) {
	$anchor = NULL;
	if($this->_anchor) $anchor = "#$this->_name";
	
	if(isset($this->_overrideOrder[$column][0])) $overRide = $this->_overrideOrder[$column][0];
	else $overRide = "default";

	switch($overRide) {
	case 0: 
	  $listTags[$key] .= "<a href=\"./index.php?module=$this->_controller&amp;$this->_op&amp;list=$this->_name&amp;column=$column&amp;order=1$anchor\">";
	  $listTags[$key] .= "<img src=\"./images/core/list/sort_none.png\" border=\"0\" alt=\"sort_none\" /></a>";
	  break;

	case 1:
	  $listTags[$key] .= "<a href=\"./index.php?module=$this->_controller&amp;$this->_op&amp;list=$this->_name&amp;column=$column&amp;order=2$anchor\">";
	  $listTags[$key] .= "<img src=\"./images/core/list/up_pointer.png\" border=\"0\" alt=\"up_pointer\" /></a>";
	  break;
	  
	case 2:
	  $listTags[$key] .= "<a href=\"./index.php?module=$this->_controller&amp;$this->_op&amp;list=$this->_name&amp;column=$column&amp;order=0$anchor\">";
	  $listTags[$key] .= "<img src=\"./images/core/list/down_pointer.png\" border=\"0\" alt=\"down_pointer\" /></a>";
	  break;
	  
	default:
	  $listTags[$key] .= "<a href=\"./index.php?module=$this->_controller&amp;$this->_op&amp;list=$this->_name&amp;column=$column&amp;order=1$anchor\">";
	  $listTags[$key] .= "<img src=\"./images/core/list/sort_none.png\" border=\"0\" alt=\"sort_none\" /></a>";
	}
      }

      $columns++;
    }

    /* Build each item's row */
    $listTags['LIST_ITEMS'] = array();
    $this->_lastIds = NULL;
    if($totalItems > 0) {
      foreach($items as $item) {
	$this->_lastIds[] = $item[$this->_idColumn];
	$object = NULL;
	if(class_exists($this->_class)) {
	  $object = new $this->_class($item);
	  $classMethods = get_class_methods($this->_class);
	} else
	  return PHPWS_Error::get(PHPWS_LIST_CLASS_NOT_EXISTS, "core", "PHPWS_List::getList()");
	
	PHPWS_List::toggle($row_class, PHPWS_LIST_TOGGLE_CLASS);
	/* Build row tags array for processTemplate() */
	$rowTags = array();
	$rowTags["ROW_CLASS"] = $row_class;

	foreach($this->_listColumns as $column) {
	  $capscolumn = strtoupper($column);
	  $method = strtolower($column);
	  $method = "getlist{$method}";

	  if(is_object($object) && in_array($method, $classMethods)) {
	    $rowTags[$capscolumn] = $object->{$method}();
	  } else {
	    $rowTags[$capscolumn] = "Get method not found.";
	  }
	}

	$rowTags = array_merge($rowTags, $this->_extraRowTags);

	if ($this->_dualTemplates){
	  /* Process this item and concatenate onto the current list of items */
	  $listTags["LIST_ITEMS"][] = PHPWS_Template::processTemplate($rowTags, "core", $rowTpl, FALSE);
	} else {
	  $listTpl->setCurrentBlock("row");
	  $listTpl->setData($rowTags);
	  $listTpl->parseCurrentBlock("row");
	}
      }

      $listTags["LIST_ITEMS"] = implode("\n", $listTags["LIST_ITEMS"]);
      
      if(isset($this->_pager) && is_object($this->_pager)) {
	$listTags['NAV_BACKWARD'] = $this->_pager->getBackLink($this->_paging['back']);
	$listTags['NAV_FORWARD'] = $this->_pager->getForwardLink($this->_paging['forward']);
	if($this->_paging['section']) {
	  $listTags['NAV_SECTIONS'] = $this->_pager->getSectionLinks();
	}
	$listTags['NAV_LIMITS'] = $this->_pager->getLimitLinks();
	$listTags['NAV_INFO'] = $this->_pager->getSectionInfo();
      } else {
	$listTags['TOTAL'] = $totalItems;
      }      

      $listTags = array_merge($listTags, $this->_extraListTags);

      if ($this->_dualTemplates)
	$content = PHPWS_Template::processTemplate($listTags, "core", $listTpl, FALSE);
      else {
	$listTpl->setData($listTags);
	$content = $listTpl->get();
      }
    } else {
      $listTags["LIST_ITEMS"] = "<tr><td colspan=\"$columns\">" . $_SESSION['translate']->it("No items for the current list.") . "</td></tr>";
      $listTags = array_merge($listTags, $this->_extraListTags);

      if ($this->_dualTemplates)
	$content = PHPWS_Template::processTemplate($listTags, "core", $listTpl, FALSE);
      else {
	$listTpl->setData($listTags);
	$content = $listTpl->get();
      }
    }
    
    /* reinitialize where and order before next list */
    $this->setWhere();
    $this->setOrder();

    return $content;
  }// END FUNC getList()

  function getItems($ids=NULL) {
    /* Make sure the table name is set before continuing */
    if(isset($this->_table)) {
      if(is_array($this->_dbColumns)) {

	$db = & new PHPWS_DB($this->_table);
	$db->addColumn($this->_idColumn);

	foreach($this->_dbColumns as $column) {
	  if($column != "id")
	    $db->addColumn($column);
	}
      } else return PHPWS_Error::get(PHPWS_LIST_COLUMNS_NOT_SET, "core", "PHPWS_List::getList()");
    } else return PHPWS_Error::get(PHPWS_LIST_TABLE_NOT_SET, "core", "PHPWS_List::getList()");

    $where = $this->getWhere();

    if(isset($where))
      $db->setQWhere(preg_replace("/where/i", "", $where));

    if(is_array($ids) && (sizeof($ids) > 0)) {
      foreach ($ids as $id){
	$db->addGroup("idlist", "AND");
	$db->addWhere($this->_idColumn, $id, NULL, "OR", "idlist");
      }
    }

    $order = $this->getOrder();

    if(isset($order))
      $db->addOrder(preg_replace("/order by/i", "", $order));

    /* Set associative mode for db and execute query */
    $result = $db->select();

    /* Return result of query */
    return $result;
  }// END FUNC getItems()

  function _getIds() {
    if(isset($this->_table)) {
      $db = & new PHPWS_DB($this->_table);
      $db->addColumn($this->_idColumn);

      $where = $this->getWhere();
      if(isset($where))
	$db->setQWhere($where);
      
      $order = $this->getOrder();
      if(isset($order))
	$db->addOrder($order);

      $result = $db->select("col");
      return $result;
    } else return PHPWS_Error::get(PHPWS_LIST_TABLE_NOT_SET, "core", "PHPWS_List::getList()");
  }

  function _orderIds($ids) {
    if(isset($this->_table)) {
      $db = & new PHPWS_DB($this->_table);
      $db->addColumn($this->_idColumn);

      $where = $this->getWhere();
      if(isset($where))
	$qwhere[] = $where;
      
      if(is_array($ids) && (sizeof($ids) > 0)) {
	foreach($ids as $id)
	  $subwhere[] = "$this->_idColumn='$id'";

	$qwhere[] = implode(" OR ", $subwhere);
      }

      if (isset($qwhere))
	$db->setQWhere(implode(" AND ", $qwhere));
      
      $order = $this->getOrder();
      if(isset($order))
	$db->addOrder($order);

      $result = $db->select("col");
      return $result;
    } else return PHPWS_Error::get(PHPWS_LIST_TABLE_NOT_SET, "core", "PHPWS_List::getList()");
  }

  function _doMassUpdate($column, $value) {
    if(is_array($_REQUEST["items"]) && sizeof($_REQUEST["items"]) > 0) {
      if(isset($this->_table)) {      
	/* Begin sql update statement */
	$sql = array();
	$sql[] = "UPDATE ";
	$sql[] = PHPWS_TBL_PREFIX;
	$sql[] = "$this->_table SET $column='$value' WHERE $this->_idColumn=";
	
	/* Set flag to know when to add sql for checking against extra ids */
	$flag = FALSE;
	foreach($_REQUEST["items"] as $itemId) {
	  if($flag)
	    $sql[] = " OR $this->_idColumn=";
	  
	  $sql[] = "'$itemId'";
	  $flag = TRUE;
	}
	
	/* Execute query and test for failure */
	$result = $GLOBALS["core"]->query(implode("", $sql));

	if($result)
	  return TRUE;
	else
	  return FALSE;
      } else return PHPWS_Error::get(PHPWS_LIST_TABLE_NOT_SET, "core", "PHPWS_List::getList()");
    } else
      return PHPWS_Error::get(PHPWS_LIST_NO_ITEMS_PASSED, "core", "PHPWS_List::getList()");

  } // END FUNC _doMassUpdate()

  function setModule($module) {
    if(is_string($module)) {
      $this->_module = $module;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setModule

  function setController($controller) {
    if(is_string($controller)) {
      $this->_controller = $controller;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setController

  function setTable($table) {
    if(is_string($table)) {
      $this->_table = $table;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setTable()

  function setClass($class) {
    if(class_exists($class)) {
      $this->_class = $class;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setClass()

  function setIdColumn($idColumn) {
    if(is_string($idColumn)) {
      $this->_idColumn = $idColumn;
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function setIds($ids) {
    if(is_array($ids)) {
      $this->_ids = $ids;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setIds()

  function setDbColumns($dbColumns) {
    if(is_array($dbColumns)) {
      $this->_dbColumns = $dbColumns;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setDbColumns()

  function setListColumns($listColumns) {
    if(is_array($listColumns)) {
      $this->_listColumns = $listColumns;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setListColumns()

  function setName($name) {
    if(is_string($name)) {
      $this->_name = $name;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setName()

  function setTemplate($template) {
    if(is_string($template)) {
      $this->_template = $template;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setTemplate()

  function getTemplate(){
    if (isset($this->_template))
      return $this->_template;
    elseif (isset($this->_name))
      return $this->_name . ".tpl";
    else
      return NULL;
  }

  function setOp($op) {
    if(is_string($op)) {
      $this->_op = $op;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setOp()

  function setPaging($paging) {
    if(is_array($paging)) {
      $this->_paging = $paging;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setPaging()

  function setWhere($where = NULL) {
    if(isset($where) && is_string($where)) {
      $this->_where = $where;
      return TRUE;
    } else {
      $this->_where = NULL;
      return FALSE;
    }
  } // END FUNC setWhere()

  function setOrder($order = NULL) {
    if(isset($order) && is_string($order)) {
      $this->_order = $order;
      return TRUE;
    } else {
      $this->_order = NULL;
      return FALSE;
    }
  } // END FUNC setOrder()

  function setExtraListTags($extraListTags) {
    if(is_array($extraListTags)) {
      $this->_extraListTags = $extraListTags;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setExtraListTags()

  function setExtraRowTags($extraRowTags) {
    if(is_array($extraRowTags)) {
      $this->_extraRowTags = $extraRowTags;
      return TRUE;
    } else {
      return FALSE;
    }
  } // END FUNC setExtraRowTags()

  function getModule() {
    return $this->_module;
  }

  function getName() {
    return $this->_name;
  }

  function getWhere() {
    if(isset($this->_where)) {
      $sql = " WHERE $this->_where";
      return $sql;
    } else {
      return NULL;
    }
  } // END FUNC getWhere()

  function getOrder() {
    foreach($this->_dbColumns as $column) {
      if(isset($this->_overrideOrder[$column][1])) {
	$order = $this->_overrideOrder[$column][1];
	break;
      }
    }

    if(isset($order)) return " ORDER BY $order";
    else if(isset($this->_order)) return " ORDER BY $this->_order";
    else return NULL;
  } // END FUNC getOrder()

  function anchorOn() {
    $this->_anchor = TRUE;
  } // END FUNC anchorOn()

  function anchorOff() {
    $this->_anchor = FALSE;
  } // END FUNC anchorOff()

  function catchOrder() {
    if(isset($_REQUEST['column']) && isset($_REQUEST['order'])) {
      unset($this->_overrideOrder);
      $this->_overrideOrder[$_REQUEST['column']][0] = $_REQUEST['order'];
      switch($this->_overrideOrder[$_REQUEST['column']][0]) {
      case 0:
	$this->_overrideOrder[$_REQUEST['column']][1] = NULL;
	break;
	
      case 1:
	$this->_overrideOrder[$_REQUEST['column']][1] = "{$_REQUEST['column']} DESC";
	break;
	
      case 2:
	$this->_overrideOrder[$_REQUEST['column']][1] = "{$_REQUEST['column']} ASC";
	break;
      }
      return TRUE;
    }

    return FALSE;
  } // END FUNC catchOrder()

  function toggle(&$tog, $ret_value=NULL){
    if(!$tog) {
      if($ret_value !== NULL)
	$tog = $ret_value;
      else
	$tog = 1;
    } else $tog = NULL;
  }

  function getLastIds(){
    return $this->_lastIds;
  }

  function setDualTemplate($value){
    $this->_dualTemplates = (bool)$value;
  }

} // END CLASS PHPWS_List

?>