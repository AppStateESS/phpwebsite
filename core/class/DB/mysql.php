<?php

class PHPWS_SQL {

  function export(&$info){
    switch ($info['type']){
    case "int":
      if (!isset($info['len']) || $info['len'] > 6)
	$setting = "INT";
      else
	$setting = "SMALLINT";
      break;
    
    case "blob":
      $setting = "TEXT";
      $info['flags'] = NULL;
      break;
    
    case "string":
      $setting = "CHAR(" . $info['len'] . ")";
      break;
    
    case "date":
      $setting = "DATE";
      break;
    
    case "real":
      $setting = "FLOAT";
      break;
    
    case "timestamp":
      $setting = "TIMESTAMP";
      $info['flags'] = NULL;
      break;

    }

    return $setting;
  }


  function getLimit($limit){
    $sql[] = "LIMIT " . $limit['total'];
    
    if (isset($limit['offset'])) {
      $sql[] = ", " . $limit['offset'];
    }

    return implode(" ", $sql);
  }

}

?>
