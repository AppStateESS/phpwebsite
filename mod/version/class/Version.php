<?php

class Version {
  var $_error = NULL;

  function getVersion($version_id, $table, $strip=TRUE, $switch_id=TRUE){
    $versionTable = Version::getVersionTable($table);

    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);
    $db->addWhere("id", (int)$version_id);
    $result = $db->select("row");
    if (PEAR::isError($result))
      return $result;

    if ($strip)
      Version::_stripVersionVars($result);

    if ($switch_id)
      Version::_exchangeID($result);

    return $result;
  }

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

  function getUnapproved($table, $id=NULL, $strip=TRUE){
    if (isset($GLOBALS['Unapproved'][$table][$id]))
      $result = $GLOBALS['Unapproved'][$table][$id];
    $versionTable = Version::getVersionTable($table);

    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);

    if (isset($id)){
      $db->addWhere("main_id", (int)$id);
      $mode = "row";
    } else {
      $mode = "all";
    }

    $db->addWhere("approved", 0);
    $result = $db->select($mode);

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

    return empty($result) ? FALSE : $result['id'];
  }

  function isLocked($table, $id){
    $result = Version::getUnapproved($table, $id);
    return isset($result) ? TRUE : FALSE;
  }


  function saveUnapproved($table, $object){
    $result = Version::waitingApproval($table, $object->id);

    if (Version::waitingApproval($table, $object->id)) {
      return Version::updateUnapproved($table, $object);
    }
    else {
      return Version::createUnapproved($table, $object);
    }
  }

  function saveApproved($table, $object){
    if (Version::waitingApproval($table, $object->id))
      return Version::updateApproved($table, $object);
    else
      return Version::createApproved($table, $object);
  }

  function saveVersion($table, $object){
    return Version::createApproved($table, $object);
  }

  function updateUnapproved($table, $object){
    $db = Version::_prepareDB($table, $object);

    if (PEAR::isError($db))
      return $db;

    Version::_prepareUpdate($db, $table, $object, 0);

    if (!isset($db->values['main_id']))
      $db->addValue("main_id", 0);

    return $db->update();
  }

  function createUnapproved($table, $object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    $version_number = Version::_getCurrentVersionNumber($db->getTable(), $object->id);
    Version::_addVersionValues($db, 0, 0, $version_number);

    if (!isset($db->values['main_id']))
      $db->addValue("main_id", 0);

    return $db->insert();
  }

  function updateApproved($table, &$object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    Version::_clearCurrents($db->getTable(), $object->id);
    Version::_prepareUpdate($db, $table, $object, 1);

    if (!isset($db->values['main_id']))
      $db->addValue("main_id", 0);

    $result = $db->update();
  }

  function createApproved($table, &$object){
    $db = Version::_prepareDB($table, $object);
    if (PEAR::isError($db))
      return $db;

    Version::_clearCurrents($db->getTable(), $object->id);

    $version_number = Version::_getCurrentVersionNumber($db->getTable(), $object->id);
    Version::_addVersionValues($db, 1, 1, $version_number);

    if (!isset($db->values['main_id']))
      $db->addValue("main_id", 0);

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


  function _getCurrentVersionNumber($table, $id){
    if ($id == 0)
      return 1;

    $db = & new PHPWS_DB($table);
    $db->addWhere("main_id", $id);
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
    $db->addWhere("main_id", $id);
    $db->addValue("current_version", 0);
    return $db->update();
  }

  function _plugObjectValues(&$object, &$db){
    $object_vars = get_object_vars($object);

    if (empty($object_vars)) {
      return NULL;
    }

    foreach ($object_vars as $column_name => $column_value){
      if (!$db->isTableColumn($column_name)) {
	continue;
      }

      if ($column_name == "id") {
	$db->addValue("main_id", $column_value);
	continue;
      }

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

  function _exchangeID(&$version){
    $version['id'] = $version['main_id'];
    unset($version['main_id']);
  }

  function _prepareUpdate(&$db, $table, &$object, $approved){
    $unapproved = Version::getUnapproved($table, $object->id, FALSE);
    $version_number = $unapproved['version_number'];
    $db->addWhere("main_id", $object->id);
    $db->addWhere("version_number", $version_number);
    Version::_addVersionValues($db, $approved, $approved, $version_number);
  }

  function _stripVersionVars(&$version){
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
      $newColumns[] = $editCol;
      if ($editCol['name'] == "id")
	$newColumns[] = array("table" => $table,
			      "name"  => "main_id",
			      "type"  => "int",
			      "flags" => "NOT NULL"
			      );
    }

    $parsed_columns = $db->parseColumns($newColumns);
    $columns = $parsed_columns['parameters'];
    $columns[] = "last_editor int NOT NULL default 0";
    $columns[] = "version_date int NOT NULL";
    $columns[] = "version_number smallint NOT NULL default 1";
    $columns[] = "current_version smallint NOT NULL default 0";
    $columns[] = "approved smallint NOT NULL default 0";
    $columns[] = "approve_group smallint NOT NULL default 0";
    $columns[] = "locked smallint NOT NULL default 0";

    $sql = "CREATE TABLE " . Version::getVersionTableName($table) .
      " (" . implode(", ", $columns) . ")";

    $result = PHPWS_DB::query($sql);
    if (PEAR::isError($result))
      return $result;

    if (isset($parsed_columns['index']))
      return PHPWS_DB::query($parsed_columns['index']);
  }


  function getAll($id, $table, $class = NULL){
    $versionTable = Version::getVersionTable($table);
    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);
    $db->addWhere("main_id", (int)$id);
    $db->addWhere("approved", 1);
    $db->addWhere("current_version", 0);
    $db->setIndexBy("version_number");
    $db->addOrder("version_number desc");

    if (isset($class) && class_exists($class))
      $result = $db->getObjects($class);
    else
      $result = $db->select();

    return $result;
  }

  function restore(&$object, $order, $table){
    $versionTable = Version::getVersionTable($table);
    if (PEAR::isError($versionTable))
      return $versionTable;

    $db = & new PHPWS_DB($versionTable);
    $db->addWhere("main_id", (int)$object->id);
    $db->addWhere("version_number", $order);
    $db->addWhere("approved", 1);
    $result = $db->select("row");

    if (empty($result))
      return FALSE;

    Version::_stripVersionVars($result);
    foreach ($result as $key => $value) {
      $object->$key = $value;
    }

    return TRUE;
  }

  function loadVersion($table, $version_id, $class_name) {
    $data = Version::getVersion($version_id, $table);
    if (PEAR::isError($data))
      return $data;

    if (!class_exists($class_name))
      return PHPWS_Error::get(PHPWS_CLASS_NOT_EXIST, "core", "Version::loadVersion");

    $object = new $class_name;

    $result = PHPWS_DB::loadObject($object, $data);
    if (PEAR::isError($result))
      return $result;
    else
      return $object;
  }

}

?>