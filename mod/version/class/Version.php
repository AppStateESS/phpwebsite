<?php

class Version {
  var $_error = NULL;

  function getVersionTableName($table){
    return $table . "_version";
  }

  function getVersionTable($table){
    if (!PHPWS_DB::isTable($table))
      return PHPWS_Error::get(PHPWS_DB_NO_TABLE, "core", __CLASS__ . "::" . __FUNCTION__);

    $versionTable = Version::getVersionTableName($table);
    if (!PHPWS_DB::isTable($versionTable)){
      $result = Version::_buildVersionTable($table);

      if (PEAR::isError($result))
	return $result;
    }
    return $versionTable;
  }

  function getUnapproved($table, $id, $strip=TRUE){
    if (isset($GLOBALS['Unapproved'][$table][$id]))
      $result = $GLOBALS['Unapproved'][$table][$id];
    $versionTable = Version::getVersionTable($table);

    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);
    $db->addWhere("id", (int)$id);
    $db->addWhere("approved", 0);
    $result = $db->select("row");
    if (isset($result) && $strip)
      Version::_stripVersionVars($result);
    return $result;
  }

  function waitingApproval($table, $id){
    $result = Version::getUnapproved($table, $id);

    if (PEAR::isError($result))
      return $result;

    if (isset($result))
      $GLOBALS['Unapproved'][$table][$id] = $result;
    return empty($result) ? FALSE : TRUE;
  }

  function isLocked($table, $id){
    $result = Version::getUnapproved($table, $id);
    return isset($result) ? TRUE : FALSE;
  }


  function saveUnapproved($module, $table, $object){
    if (Version::waitingApproval($table, $object->id))
      return Version::updateUnapproved($module, $table, $object);
    else
      return Version::createUnapproved($module, $table, $object);
  }

  function saveApproved($module, $table, $object){
    if (Version::waitingApproval($table, $object->id))
      return Version::updateApproved($module, $table, $object);
    else
      return Version::createApproved($module, $table, $object);
  }

  function saveVersion($module, $table, $object){
    return Version::createApproved($module, $table, $object);
  }

  function updateUnapproved($module, $table, $object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    Version::_prepareUpdate($db, $table, $object, 0);

    if (!isset($db->values['id']))
      $db->addValue("id", 0);

    return $db->update();
  }

  function createUnapproved($module, $table, $object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    $version_number = Version::_getCurrentVersionNumber($db);
    Version::_addVersionValues($db, 0, 0, $version_number);

    if (!isset($db->values['id']))
      $db->addValue("id", 0);

    return $db->insert();
  }

  function updateApproved($module, $table, &$object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    Version::_clearCurrents($db->getTable(), $object->id);
    Version::_prepareUpdate($db, $table, $object, 1);

    if (!isset($db->values['id']))
      $db->addValue("id", 0);

    $result = $db->update();
  }

  function createApproved($module, $table, &$object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    Version::_clearCurrents($db->getTable(), $object->id);

    $version_number = Version::_getCurrentVersionNumber($db);
    Version::_addVersionValues($db, 1, 1, $version_number);

    if (!isset($db->values['id']))
      $db->addValue("id", 0);

    $result = $db->insert();
  }


  function _addVersionValues(&$db, $current, $approved, $version_number){
    $db->addValue("version_date",    mktime());
    $db->addValue("last_editor",     Current_User::getId());
    $db->addValue("version_number",  $version_number);
    $db->addValue("current_version", (int)$current);
    $db->addValue("approved",        (int)$approved);

    // Need to code this portion when workflow is ready!
    $db->addValue("approve_group", 0);
    // Code this for multiple users
    $db->addValue("locked",        0);
  }


  function _getCurrentVersionNumber($db){
    $db->addColumn("version_number");
    $current_version = $db->select("max");
    if (empty($current_version))
      $current_version = 1;
    else
      $current_version++;

    return $current_version;
  }

  function _clearCurrents($versionTable, $id){
    $db = & new PHPWS_DB($versionTable);
    $db->addWhere("id", $id);
    $db->addValue("current_version", 0);
    return $db->update();
  }

  function _plugObjectValues(&$object, &$db){
    $object_vars = get_object_vars($object);

    if (empty($object_vars))
      return NULL;

    foreach ($object_vars as $column_name => $column_value){
      if (!$db->isTableColumn($column_name))
	continue;

      $db->addValue($column_name, $column_value);
    }
  }


  function &_prepareDB($table, &$object){
    $versionTable = Version::getVersionTable($table);
    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);

    Version::_plugObjectValues($object, $db);

    $db->setTable($versionTable);
    return $db;
  }

  function _prepareUpdate(&$db, $table, &$object, $approved){
    $unapproved = Version::getUnapproved($table, $object->id, FALSE);
    $version_number = $unapproved['version_number'];
    $db->addWhere("id", $object->id);
    $db->addWhere("version_number", $version_number);
    Version::_addVersionValues($db, $approved, $approved, $version_number);
  }

  function _stripVersionVars($version){
    unset($version['last_editor']);
    unset($version['version_date']);
    unset($version['version_number']);
    unset($version['current_version']);
    unset($version['approved']);
    unset($version['approve_group']);
    unset($version['locked']);
  }

  function _buildVersionTable($table){
    $db = & new PHPWS_DB($table);
    $allColumns = $db->getTableColumns(TRUE);

    foreach ($allColumns as $editCol){
      if ($editCol['name'] == "id")
	$editCol['flags'] = preg_replace("/primary_key/i", "", $editCol['flags']);
      $newColumns[] = $editCol;
    }

    $columns = PHPWS_DB::parseColumns($newColumns);

    $columns[] = "last_editor int NOT NULL default 0";
    $columns[] = "version_date int NOT NULL";
    $columns[] = "version_number smallint NOT NULL default 1";
    $columns[] = "current_version smallint NOT NULL default 0";
    $columns[] = "approved smallint NOT NULL default 0";
    $columns[] = "approve_group smallint NOT NULL default 0";
    $columns[] = "locked smallint NOT NULL default 0";

    $sql = "CREATE TABLE " . Version::getVersionTableName($table) .
      " (" . implode(", ", $columns) . ")";

    return PHPWS_DB::query($sql);
  }

}

?>