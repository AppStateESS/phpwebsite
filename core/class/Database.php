<?php
/**
 * A database class
 *
 * @version $Id$
 * @author  Matt McNaney <matt at tux dot appstate dot edu>
 * @package Core
 */
require_once "DB.php";

define ("DEFAULT_MODE", DB_FETCHMODE_ASSOC);

class PHPWS_DB {

  var $_table    = NULL;
  var $_where    = array();
  var $_order    = array();
  var $_value    = array();
  var $_mode     = DEFAULT_MODE;
  var $_limit    = NULL;
  var $_index    = NULL;
  var $_column   = NULL;
  var $_qwhere   = NULL;

  function PHPWS_DB($table=NULL){
    PHPWS_DB::touchDB();
    if (isset($table))
      $this->setTable($table);

    $this->setMode("assoc");
  }
  
  function touchDB(){
    if (!PHPWS_DB::isConnected())
      PHPWS_DB::loadDB();
  }

  function isConnected(){
    if (isset($GLOBALS['PEAR_DB']))
      return TRUE;
    else
      return FALSE;
  }

  function loadDB($dsn=NULL){
    if (PHPWS_DB::isConnected())
      PHPWS_DB::disconnect();

    if (isset($dsn))
      $GLOBALS['PEAR_DB'] = DB::connect($dsn);
    else
      $GLOBALS['PEAR_DB'] = DB::connect(PHPWS_DSN);

    if (PEAR::isError($GLOBALS['PEAR_DB'])){
      PHPWS_Error::log($GLOBALS['PEAR_DB']);
      PHPWS_Core::errorPage();
    }

    if (defined(TABLE_PREFIX))
      PHPWS_DB::setPrefix(TABLE_PREFIX);
    else
      PHPWS_DB::setPrefix(NULL);
  }

  function query($sql){
    PHPWS_DB::touchDB();
    return $GLOBALS['PEAR_DB']->query($sql);
  }

  function isTableColumn($columnName){
    $table = & $this->getTable();
    if (!isset($table))
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::isTableColumn");

    $columns =  $GLOBALS['PEAR_DB']->tableInfo($table);
    if (PEAR::isError($columns))
      return $columns;

    foreach ($columns as $colInfo)
      if (ereg($colInfo['name'], $columnName) !== FALSE)
	return TRUE;

    return FALSE;
  }

  function setMode($mode){
    switch (strtolower($mode)){
    case "ordered":
      $this->_mode = DB_FETCHMODE_ORDERED;
      break;

    case "object":
      $this->_mode = DB_FETCHMODE_OBJECT;
      break;

    case "assoc":
      $this->_mode = DB_FETCHMODE_ASSOC;
      break;
    }

  }

  function getMode(){
    return $this->_mode;
  }

  function isTable($tableName){
    static $tables;

    if (count($tables) < 1){
      PHPWS_DB::touchDB();
      $tables = PHPWS_DB::listTables();
    }
    return in_array(PHPWS_DB::getPrefix() . $tableName, $tables);
  }

  function listTables(){
    return $GLOBALS['PEAR_DB']->getlistOf("tables");
  }

  function listDatabases(){
    return $GLOBALS['PEAR_DB']->getlistOf("databases");
  }


  function setPrefix($prefix){
    $GLOBALS['PEAR_DB']->prefix = $prefix;
  }

  function getPrefix(){
    return $GLOBALS['PEAR_DB']->prefix;
  }

  function setTable($table){
    $this->_table = $table;
  }

  function setIndex($index){
    $this->_index = $index;
  }

  function getIndex($checkTable=FALSE){
    if (isset($this->_index))
      return $this->_index;

    if ($checkTable && $this->getTable()){
      $columns =  $GLOBALS['PEAR_DB']->tableInfo($this->getTable());

      if (PEAR::isError($columns))
	return $columns;

      foreach ($columns as $colInfo)
	if (preg_match("/primary/", $colInfo['flags']) && preg_match("/int/", $colInfo['type']))
	  return $colInfo['name'];
    }

    return NULL;
  }

  function getTable($prefix=TRUE){
    if ($prefix == TRUE)
      return $this->getPrefix() . $this->_table;
    else
      return $this->_table;
  }

  function resetTable(){
    $this->_table = NULL;
  }

  function addGroup($group, $conj){
    $this->_where[$group]['conj'] = $conj;
  }

  function addWhere($column, $value, $operator=NULL, $conj=NULL, $group=NULL){
    if (is_array($value)){
      foreach ($value as $newVal)
	$this->addWhere($column, $newVal, $operator, $conj, $group);
      return;
    }

    if (!isset($operator))
      $operator = "=";

    if (!isset($conj))
      $conj = "AND";

    if (isset($group))
      $this->_where[$group]['values'][] = array('column'=>$column, 'value'=>$value, 'operator'=>$operator, 'conj'=>$conj);
    else
      $this->_where[0]['values'][] = array('column'=>$column, 'value'=>$value, 'operator'=>$operator, 'conj'=>$conj);
  }

  function setQWhere($where){
    $this->_qwhere = $where;
  }

  function getWhere($dbReady=FALSE){
    $extra = FALSE;

    if (!count($this->_where)){
      if (isset($this->_qwhere))
	return "WHERE " . $this->_qwhere;
      return NULL;
    }

    $startMain = FALSE;
    if ($dbReady){
      foreach ($this->_where as $groups){
	if (!isset($groups['values']))
	  continue;
	$startSub = FALSE;

	if ($startMain == TRUE) $sql[] = $groups['conj'];
	$sql[] = "(";


	foreach ($groups['values'] as $value){
	  if ($startSub == TRUE) $sql[] = $value['conj'];

	  $sql[] = $value['column'] . " " . $value['operator'] . " '" . $value['value'] . "'";
	  $startSub = TRUE;
	}

	$sql[] = ")";
	$startMain = TRUE;
      }

      if (isset($this->_qwhere))
	$sql[] = " AND " . $this->_qwhere;

    } else
      return $this->_where;

    return "WHERE " . implode(" ", $sql);
  }

  function resetWhere(){
    $this->_where = array();
  }

  function addColumn($column, $distinct=FALSE, $count=FALSE){
    if ($distinct == TRUE)
      $column = "DISTINCT " .  $column;

    if ($count)
      $column = "COUNT($column)";

    $this->_column[] = $column;

  }

  function getColumn(){
    return $this->_column;
  }


  function addOrder($order){
    if (is_array($order))
      foreach ($order as $value)
	    $this->_order[] = $value;
    else
      $this->_order[] = $order;
  }

  function getOrder($dbReady=FALSE){
    if (!count($this->_order))
      return NULL;

    if ($dbReady)
      return "ORDER BY " . implode(", ", $this->_order);
    else
      return $this->_order;
  }

  function resetOrder(){
    $this->_order = array();
  }

  function addValue($column, $value=NULL){
    if (is_array($column)){
      if (isset($this->_values))
	$this->_values = $this->_values + $column;
      else
	$this->_values = $column;
    } else
      $this->_values[$column] = $value;
  }

  function getValue($column){
    if (!count($this->_values) || !isset($this->_values[$column]))
      return NULL;

    return $this->_values[$column];
  }

  function resetValues(){
    $this->_values = array();
  }

  function getAllValues(){
    if (!count($this->_values))
      return NULL;

    return $this->_values;
  }


  function setLimit($limit){
    $this->_limit = $limit;
  }

  function getLimit($dbReady=FALSE){
    if (empty($this->_limit))
      return NULL;

    if ($dbReady)
      return "LIMIT " . $this->_limit;
    else
      return $this->_limit;
  }

  function resetLimit(){
    $this->_limit = "";
  }

  function affectedRows(){
    $query =  PHPWS_DB::lastQuery();
    $process = strtolower(substr($query, 0, strpos($query, " ")));

    if ($process == "select"){
      $rows = $GLOBALS['PEAR_DB']->num_rows; 
      return array_pop($rows);
    }
    else
      return $GLOBALS['PEAR_DB']->affectedRows();
  }

  function reset(){
    $this->resetWhere();
    $this->resetValues();
    $this->resetLimit();
    $this->resetOrder();
  }

  function lastQuery(){
    return $GLOBALS['PEAR_DB']->last_query;
  }


  function insert(){
    $maxID = TRUE;
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::insert");

    $values = $this->getAllValues();

    if (!isset($values))
      return PHPWS_Error::get(PHPWS_DB_NO_VALUES, "core", "PHPWS_DB::insert");

    $idColumn = PHPWS_DB::getIndex(TRUE);

    if (PEAR::isError($idColumn))
      return $idColumn;
    elseif(isset($idColumn)) {
      $maxID = $GLOBALS['PEAR_DB']->nextId($table);
      $values[$idColumn] = $maxID;
    }

    foreach ($values as $index=>$entry){
      $columns[] = $index;
      $set[] = PHPWS_DB::dbReady($entry);
    }

    $query = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $set) . ")";

    $result = PHPWS_DB::query($query);

    if (DB::isError($result))
      return $result;
    else
      return $maxID;
  }

  function update(){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::update");

    $values = $this->getAllValues();
    $where = $this->getWhere(TRUE);

    foreach ($values as $index=>$data)
      $columns[] = "$index = " . PHPWS_DB::dbReady($data);

    $query = "UPDATE $table SET " . implode(", ", $columns) ." $where";

    $result = PHPWS_DB::query($query);

    if (DB::isError($result))
      return $result;
    else
      return TRUE;
  }

  function select($type=NULL, $sql=NULL){
    PHPWS_DB::touchDB();
    if (!isset($sql)){
      if (isset($type))
	$type = strtolower($type);
      
      $columnList = $this->getColumn();
      
      $mode = $this->getMode();
      
      $table = $this->getTable();
    
      if (!$table)
	return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::select");

      $where = $this->getWhere(TRUE);
      $order = $this->getOrder(TRUE);
      $limit = $this->getLimit(TRUE);
      
      if (isset($columnList)){
	if ($type == "max" || $type == "min")
	  $columns = implode("", array($type . "(", array_shift($columnList), ")"));
	else
	  $columns = implode(", ", $columnList);
      }
      else
	$columns = "*";

      $sql = "SELECT $columns FROM $table $where $order $limit";
    } else
      $mode = DB_FETCHMODE_ASSOC;
    // assoc does odd things if the resultant return is two items or less
    // not sure why it is coded that way. Use the default instead
    switch ($type){
    case "assoc":
      return PHPWS_DB::autoTrim($GLOBALS['PEAR_DB']->getAssoc($sql, NULL,NULL, $mode), $type);
      break;

    case "col":
      return PHPWS_DB::autoTrim($GLOBALS['PEAR_DB']->getCol($sql), $type);
      break;

    case "min":
    case "max":
      $result = $GLOBALS['PEAR_DB']->query($sql);
      if (DB::isError($result))
	return $result;
      elseif ($result && $this->affectedRows()){
	$result->fetchInto($row);
	return $row[0];
      }
      break;

    case "one":
      $value = $GLOBALS['PEAR_DB']->getOne($sql, NULL, $mode);
      db_trim($value);
      return $value;
      break;

    case "row":
      return PHPWS_DB::autoTrim($GLOBALS['PEAR_DB']->getRow($sql, array(), $mode), $type);
      break;

    case "all":
    default:
      return PHPWS_DB::autoTrim($GLOBALS['PEAR_DB']->getAll($sql, NULL, $mode), $type);
      break;
    }
  }

  function delete(){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::delete");

    $where = $this->getWhere(TRUE);

    $sql = "DELETE from $table $where";

    return PHPWS_DB::query($sql);
  }

  

  function dropTable(){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::dropTable");

    $sql = "DROP TABLE $table";

    return PHPWS_DB::query($sql);
  }

  function createTable(){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::createTable");

    $values = $this->getAllValues();

    foreach ($values as $column=>$value)
      $parameters[] = $column . " " . $value;

    $sql = "CREATE TABLE $table ( " . implode(", ", $parameters) . " )";

    return PHPWS_DB::query($sql);
  }

  function addTableColumn($column, $parameter, $after=NULL){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::addColumn");

    if (isset($after)){
      if ($after == strtolower("first"))
	$location = "FIRST";
      else
	$location = "AFTER $after";
    } else
      $location = NULL;

    $sql = "ALTER TABLE $table ADD $column $parameter $location";

    return PHPWS_DB::query($sql);
  }


  function dropTableColumn($column){
    $table = $this->getTable();
    if (!$table)
      return PHPWS_Error::get(PHPWS_DB_ERROR_TABLE, "core", "PHPWS_DB::dropColumn");

    $sql = "ALTER TABLE $table DROP $column";

    return PHPWS_DB::query($sql);
  }


  function parseValue($value=NULL) {
    if (is_array($value) || is_object($value))
      return PHPWS_DB::dbReady(serialize($value));
    elseif (is_string($value)){
      if (PHPWS_Text::checkUnslashed($value)){
	$value = PHPWS_Text::stripSlashQuotes($value);
	$value = addslashes($value);
      }
      return "'$value'";
    }
    elseif (is_null($value))
      return "NULL";
    else
      return $value;
  }



  function disconnect(){
    if (PHPWS_DB::isConnected())
      $GLOBALS['PEAR_DB']->disconnect();
  }


  function import($text){
    PHPWS_DB::touchDB();

    $prefix = PHPWS_DB::getPrefix();

    $sqlArray = PHPWS_Text::sentence($text);

    foreach ($sqlArray as $sqlRow){
      if (empty($sqlRow) || preg_match("/^[^\w\d\s\\(\)]/i", $sqlRow))
	continue;

      $sqlCommand[] = $sqlRow;

      if (preg_match("/;$/", $sqlRow)){
	$query = implode(" ", $sqlCommand);

	if (isset($prefix)){
	  $tableName = PHPWS_DB::extractTableName($query);
	  $query = str_replace($tableName, $prefix . $tableName, $query);
	}
	$sqlCommand = array();

	$result = PHPWS_DB::query($query);
	if (DB::isError($result))
	  $errors[] = $result;
      }
    }

    if (isset($errors))
      return $errors;
    else
      return TRUE;
  }

  function export($tableName, $structure=TRUE, $contents=TRUE){
    PHPWS_DB::touchDB();
    $dbfile = PHPWS_SOURCE_DIR . "core/class/dbexport/" . $GLOBALS['PEAR_DB']->dbsyntax . ".php";

    if (!is_file($dbfile))
      return NULL;

    include_once($dbfile);

    if ($structure == TRUE){      
      $columns =  $GLOBALS['PEAR_DB']->tableInfo($tableName);

      foreach ($columns as $info){
	$setting = export($info);
	if (isset($info['flags'])){
	  if (stristr($info['flags'], "multiple_key")){
	    $createIndex[] = "CREATE INDEX " .  $info['name'] . " on " . $info['table'] . "(" . $info['name'] . ")";
	    $info['flags'] = str_replace(" multiple_key", "", $info['flags']);
	  }
	  $preFlag = array("/not_null/", "/primary_key/", "/default_(.*)?/");
	  $postFlag = array("NOT NULL", "PRIMARY KEY", "DEFAULT '\\1'");
	  $multipleFlag = array("multiple_key", "");
	  $flags = " " . preg_replace($preFlag, $postFlag, $info['flags']);
	}
	else
	  $flags = NULL;

	$parameters[] = $info['name'] . " $setting" . $flags; 
      }

      $index = PHPWS_DB::getIndex();

      if ($prefix = PHPWS_DB::getPrefix())
	$tableName = str_replace("", $prefix, $tableName);

      $sql[] = "CREATE TABLE $tableName ( " .  implode(", ", $parameters) ." );";
      if (isset($createIndex))
	$sql = array_merge($sql, $createIndex);
    }

    if ($contents == TRUE){
      $DB = new PHPWS_DB($tableName);

      if ($rows = $DB->select()){
	if (PEAR::isError($rows))
	  return $rows;
	foreach ($rows as $dataRow){
	  foreach ($dataRow as $key=>$value){
	    $allKeys[] = $key;
	    $allValues[] = PHPWS_DB::quote($value);
	  }
	  
	  $sql[] = "INSERT INTO $tableName (" . implode(", ", $allKeys) . ") VALUES (" . implode(", ", $allValues) . ");";
	  $allKeys = $allValues = array();
	}
      }
    }

    return implode("\n", $sql);
  }

  function quote($text){
    return $GLOBALS['PEAR_DB']->quote($text);
  }

  function extractTableName($sql_value){
    require_once PHPWS_SOURCE_DIR . "core/Array.php";
    $temp = explode(" ", trim($sql_value));
    PHPWS_Array::dropNulls($temp);
    if (!is_array($temp))
      return NULL;
    foreach ($temp as $whatever)
      $format[] = $whatever;

      switch (trim(strtolower($format[0]))) {
      case "insert":
      if (stristr($format[1], "into"))
	return preg_replace("/\(+.*$/", "", str_replace("`", "", $format[2]));
      else
	return preg_replace("/\(+.*$/", "", str_replace("`", "", $format[1]));
      break;
	
      case "update":
	return preg_replace("/\(+.*$/", "", str_replace("`", "", $format[1]));
      break;
      
      case "select":
      case "show":
	return preg_replace("/\(+.*$/", "", str_replace("`", "", $format[3]));
      break;

      case "drop":
      case "alter":
	return preg_replace("/;/", "", str_replace("`", "", $format[2]));
      break;

      default:
	return preg_replace("/\(+.*$/", "", str_replace("`", "", $format[2]));
      break;
      }
  }// END FUNC extractTableName


  /**
   * Prepares a value for database writing or reading
   *
   * @author Matt McNaney <matt at NOSPAM dot tux dot appstate dot edu>
   * @param  mixed $value The value to prepare for the database.
   * @return mixed $value The prepared value
   * @access public
   */
  function dbReady($value=NULL) {
    if (is_array($value) || is_object($value))
      return PHPWS_DB::dbReady(serialize($value));
    elseif (is_string($value)){
      if (PHPWS_Text::checkUnslashed($value)){
	$value = PHPWS_Text::stripSlashQuotes($value);
	$value = addslashes($value);
      }
      return "'$value'";
    }
    elseif (is_null($value))
      return "NULL";
    elseif (is_bool($value))
      return ($value ? 1 : 0);
    else
      return $value;
  }// END FUNC dbReady()

  function loadObjects($className, $indexby=NULL, $onlyOne=NULL){
    if (!class_exists($className))
      return PHPWS_Error::get(PHPWS_CLASS_NOT_EXIST, "core", "PHPWS_DB::loadObjects");

    $items = NULL;
    $result = $this->select();

    $classVars = get_class_vars($className);

    if (PEAR::isError($result) || !isset($result))
      return $result;

    foreach ($result as $itemResult){
      $genClass = new $className;
      if(is_array($classVars)) {
	foreach($classVars as $key => $value) {
	  $column = $key;
	  if($column[0] == "_")
	    $column = substr($column, 1, strlen($column));
	  
	  if(isset($itemResult[$column])){
	    if (preg_match("/^[aO]:\d+:/", $itemResult[$column]))
	      $genClass->$key = unserialize($itemResult[$column]);
	    else
	      $genClass->$key = $itemResult[$column];
	  }
	}
      }

      if (isset($indexby) && isset($itemResult[$indexby]))
	$items[$itemResult[$indexby]] = $genClass;
      elseif (isset($itemResult['id']) && isset($items[$itemResult['id']]))
	$items[$itemResult['id']] = $genClass;
      else
	$items[] = $genClass;
    }

    if ((bool)$onlyOne == TRUE && isset($items[0]))
      return array_shift($items);
    else
      return $items;
  }

  function saveObject($object, $stripChar=FALSE){

    if (!is_object($object))
      return PHPWS_Error::get(PHPWS_WRONG_TYPE, "core", "PHPWS_DB::saveObject", _("Type") . ": " . gettype($object));

    $object_vars = get_object_vars($object);

    if (!is_array($object_vars))
      return PHPWS_Error::get(PHPWS_DB_NO_OBJ_VARS, "core", "PHPWS_DB::saveObject");

    foreach ($object_vars as $column => $value){
      if ($stripChar == TRUE)
	$column = substr($column, 1);
      if (!$this->isTableColumn($column))
	continue;

      $this->addValue($column, $value);
    }

    if (isset($this->_where) && count($this->_where))
      $result = $this->update();
    else
      $result = $this->insert();

    $this->resetValues();

    return $result;
  }


  function autoTrim($sql, $type){
    if (PEAR::isError($sql) || !is_array($sql))
      return $sql;

    if (!count($sql))
      return NULL;

    if ($GLOBALS['PEAR_DB']->phptype != 'pgsql')
      return $sql;

    switch ($type){
    case "col":
      array_walk($sql, 'db_trim');
      break;

    default:
      array_walk($sql, 'db_trim');
      break;
    }

    return $sql;
  }

}

function db_trim(&$value){
  if (PEAR::isError($value) || !isset($value))
    return;

  if (is_array($value)){
    array_walk($value, 'db_trim');
    return;
  }

  $value = rtrim($value);
}

?>