<?php

class oldDB{

  function sqlInsert ($db_array, $table_name, $check_dup=FALSE, $returnId=FALSE, $show_sql=FALSE, $autoIncrement=TRUE) {
    $db = & new PHPWS_DB($table_name);
    $db->addValue($db_array);
    $result = $db->insert();

    if ($show_sql)
      echo $db->lastQuery();

    return $result;
  }

  function sqlUpdate($db_array, $table_name, $match_column=NULL, $match_value=NULL, $compare="=", $and_or="and") {
    $db = & new PHPWS_DB($table_name);
    $db->addValue($db_array);
    oldDB::addWhere($db, $match_column, $match_value, $compare, $and_or);
    return $db->update();
  }

  function sqlImport($filename, $write=TRUE, $suppress_error=FALSE){
    PHPWS_Core::initCoreClass("File.php");
    $text = PHPWS_File::readFile($filename);
    return PHPWS_DB::import($text, TRUE);
  }

  function sqlDelete($table_name, $match_column=NULL, $match_value=NULL, $compare="=", $and_or="and") {
    $db = & new PHPWS_DB($table_name);
    oldDB::addWhere($db, $match_column, $match_value, $compare, $and_or);
    return $db->delete();
  }

  function sqlSelect($table_name, $match_column=NULL, $match_value=NULL, $order_by=NULL, $compare=NULL, $and_or=NULL, $limit=NULL, $mode=NULL, $test=FALSE) {
    $db = & new PHPWS_DB($table_name);
    oldDB::addWhere($db, $match_column, $match_value, $compare, $and_or);
    return $db->select();
  }

  function addWhere(&$db, $match_column, $match_value, $compare, $and_or){
    if (isset($match_column)){
      if (is_array($match_column)){
	foreach ($match_column as $columnName=>$columnValue){
	  $operator = $conj = NULL;

	  if (is_array($compare) && isset($compare[$columnName]))
	    $operator = $compare[$columnName];
	  
	  if (is_array($and_or) && isset($and_or[$columnName]))
	    $conj = $and_or[$columnName];
	  
	  $db->addWhere($columnName, $columnValue, $operator, $conj);
	}
      } else {
	$db->addWhere($match_column, $match_value, $compare, $and_or);
      }
    }
  }

  function getCol($sql){
    return PHPWS_DB::select("col", $sql);
  }

  function getAll($sql){
    return PHPWS_DB::select("all", $sql);
  }
}



?>