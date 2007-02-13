<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

class Backup {
  var $table         = NULL;
  var $current_id    = NULL;
  var $_past_items   = NULL;


  function setCurrentId($id){
    if (empty($id))
      return  PHPWS_Error::get(PHPWS_INVALID_VALUE, 'core', __CLASS__ . '::' . __FUNCTION__);

    $this->current_id = $id;
  }

  function setTable($table){
    if (!PHPWS_DB::isTable($table))
      return PHPWS_Error::get(PHPWS_DB_NO_TABLE, 'core', __CLASS__ . '::' . __FUNCTION__);

    $this->table = $table;
    return TRUE;
  }

  function getBackupTableName($table){
    return $table . '_backup';
  }

  function buildPastItems(){
    if (!PHPWS_DB::isTable($this->getBackupTableName())){
      $result = $this->_buildBackupTable();

      if (PEAR::isError($result))
	return $result;
    }

    $current_item = $this->getPastItems();
    $this->current_item = &$current_item;
  }

  function getPastItems(){
    $db = & new PHPWS_DB($this->table);
    $db->addWhere('id', $this->current_id);
    return $db->select('row');
  }

  function _buildBackupTable($table){
    $db = & new PHPWS_DB($table);
    $result = $db->getTableColumns(TRUE);

    foreach ($result as $col){
      if ($col['name'] == 'id')
	continue;
      $allColumns[] = $col;
    }
    
    $columns = PHPWS_DB::parseColumns($allColumns);
    $columns[] = 'backup_id int NOT NULL';
    $columns[] = 'backup_order smallint NOT NULL';

    $sql = 'CREATE TABLE ' . Backup::getBackupTableName($table) .
      ' (' . implode(', ', $columns) . ')';

    return PHPWS_DB::query($sql);
  }

  function create($main_id, $backup_id, $item_order){
    $db = & new PHPWS_DB(Backup::getBackupTableName());
    $db->addValue('main_id', $main_id);
    $db->addValue('backup_id', $backup_id);
    $db->addValue('item_order', $item_order);
    $db->insert();
  }


  function save($item_id, $table, $total_backups=5){
    if (!PHPWS_DB::isTable($table))
      return FALSE;

    $backupTable = Backup::getBackupTable($table);
      if (PEAR::isError($backupTable))
	return $backupTable;

    $db = & new PHPWS_DB($table);
    $db->addWhere('id', $item_id);
    $source_row = $db->select('row');

    $db2 = & new PHPWS_DB($backupTable);
    $db2->addWhere('backup_id', $source_row['id']);
    $db2->addOrder('backup_order');
    $past_rows = $db2->select();

    $past_row_count = count($past_rows);

    if ( empty($past_rows) || ($past_row_count < $total_backups) ){
      $db2->reset();
      $source_row['backup_id'] = $source_row['id'];
      unset($source_row['id']);
      $source_row['backup_order'] = $past_row_count + 1;
      $db2->addValue($source_row);
      $result = $db2->insert();
    } else {
      $db2->delete();
      $db2->reset();

      unset($past_rows[0]);
      $source_row['backup_id'] = $source_row['id'];
      unset($source_row['id']);
      $past_rows[] = $source_row;

      foreach ($past_rows as $key=>$row){
	$row['backup_order'] = $key;
	$db2->addValue($row);
	$db2->insert();
	$db2->resetValues();
      }
    }
  }

  function getBackupTable($table){
    if (!PHPWS_DB::isTable($table))
      return FALSE;

    $backupTable = Backup::getBackupTableName($table);
    if (!PHPWS_DB::isTable($backupTable)){
      $result = Backup::_buildBackupTable($table);

      if (PEAR::isError($result))
	return $result;
    }
    return $backupTable;
  }

  function get($item_id, $table){
    $backupTable = Backup::getBackupTable($table);
    if (PEAR::isError($backupTable))
      return $backupTable;

    $db = & new PHPWS_DB($backupTable);
    $db->addOrder('backup_order desc');
    return $db->select();
  }

  function flush($item_id, $table){
    $backupTable = Backup::getBackupTable($table);
      if (PEAR::isError($backupTable))
	return $backupTable;

    $db = & new PHPWS_DB($backupTable);
    $db->addWhere('backup_id', $item_id);
    return $db->delete();
  }

}

?>