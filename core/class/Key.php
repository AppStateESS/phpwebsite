<?php

class Key {
  var $module       = NULL;
  var $item_id      = NULL;
  var $item_name    = NULL;
  var $_table       = NULL;
  var $_column_name = NULL;
  
  function Key($module, $item_name, $item_id)
  {
    $this->module  = $module;
    $this->item_name = $item_name;
    $this->item_id = $item_id;
  }

  function getHash()
  {
    return md5($this->module . $this->item_id . $item_name);
  }

  function getModule()
  {
    return $this->module;
  }

  function getItemId()
  {
    return $this->item_id;
  }

  function getItemName()
  {
    return $this->item_name;
  }

  function setColumnName($column_name)
  {
    $this->_column_name = $column_name;
  }

  function setTable($table)
  {
    $this->_table = $table;
  }

  function isEqual($key)
  {
    if ($key->module    == $this->module    &&
	$key->item_name == $this->item_name &&
	$key->item_id   == $this->item_id
	)
      return TRUE;
    else
      return FALSE;
  }
  
  function getMatches($all_columns=FALSE)
  {
    if (!isset($this->_table) || !isset($this->_column_name)) {
      // error here
      return NULL;
    }
  
    $db = & new PHPWS_DB($this->_table);
    $db->addWhere('module',   $this->module);
    $db->addWhere('itemname', $this->item_name);
    $db->addWhere('item_id',  $this->item_id);
    if ($all_columns) {
      return $db->select();
    } else {
      $db->addColumn($this->_column_name);
      return $db->select('col');
    }
  }

}

?>